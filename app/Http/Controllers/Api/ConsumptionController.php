<?php
// app/Http/Controllers/Api/ConsumptionController.php

namespace App\Http\Controllers\Api;

use App\Models\Sensor;
use App\Models\Measurement;
use App\Models\Consumption;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ConsumptionController extends Controller
{
    /**
     * Listar consumos con filtros opcionales.
     * Si no hay consumos calculados, los calcula automáticamente para todos los sensores del usuario.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Obtener sensores del usuario (propios o compartidos)
        $sensors = Sensor::where(function ($query) use ($user) {
            $query->whereHas('group', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->orWhereHas('group.sharedAccess', function ($q) use ($user) {
                $q->where('shared_with', $user->id);
            });
        })->with(['group', 'group.template'])->get();

        // Filtrar por sensor si se proporciona
        if ($request->has('sensor_id') && $request->sensor_id) {
            $sensors = $sensors->where('id', $request->sensor_id);
        }

        // Filtrar por comunidad
        if ($request->has('is_community')) {
            $isCommunity = filter_var($request->is_community, FILTER_VALIDATE_BOOLEAN);
            $sensors = $sensors->where('is_community', $isCommunity);
        }

        // Filtrar por identificador si se proporciona
        if ($request->has('identifier') && $request->identifier) {
            $identifierFilter = strtolower($request->identifier);
            $sensors = $sensors->filter(function ($sensor) use ($identifierFilter) {
                return str_contains(strtolower($sensor->identifier), $identifierFilter);
            });
        }

        // Calcular consumos para cada sensor si no existen
        $allConsumptions = collect();
        foreach ($sensors as $sensor) {
            // Obtener consumos existentes para este sensor
            $existingConsumptions = Consumption::where('sensor_id', $sensor->id)
                ->with(['sensor', 'startMeasurement', 'endMeasurement', 'sensor.group', 'sensor.group.user'])
                ->get();

            // Si no hay consumos existentes o se fuerza el recálculo, calcularlos
            if ($existingConsumptions->isEmpty() || $request->has('recalculate')) {
                $newConsumptions = $this->calculateConsumptionsForSensor($sensor, $user);
                $allConsumptions = $allConsumptions->concat($newConsumptions);
            } else {
                // Usar los consumos existentes
                foreach ($existingConsumptions as $consumption) {
                    $consumptionArray = $consumption->toArray();
                    $daysBetween = $consumption->days_between;

                    // Redondear días a 2 decimales
                    $daysBetween = round($daysBetween, 2);
                    $consumptionArray['days_between'] = $daysBetween;

                    if ($daysBetween > 0) {
                        $consumptionArray['daily_average'] = round((float) $consumption->value / $daysBetween, 2);
                    } else {
                        $consumptionArray['daily_average'] = 0;
                    }

                    // Asegurar que el sensor y grupo estén cargados
                    $consumptionArray['sensor'] = [
                        'id' => $sensor->id,
                        'name' => $sensor->name,
                        'identifier' => $sensor->identifier,
                        'is_community' => $sensor->is_community,
                        'group' => [
                            'id' => $sensor->group->id,
                            'name' => $sensor->group->name,
                            'template' => [
                                'type' => ($sensor->group && $sensor->group->template) ? $sensor->group->template->type : 'Desconocido'
                            ]
                        ]
                    ];

                    $allConsumptions->push($consumptionArray);
                }
            }
        }

        // Aplicar filtros de fecha si existen
        if ($request->has('start_date') || $request->has('end_date')) {
            $allConsumptions = $allConsumptions->filter(function ($consumption) use ($request) {
                $startOk = !$request->has('start_date') ||
                    Carbon::parse($consumption['period_end'])->gte(Carbon::parse($request->start_date));
                $endOk = !$request->has('end_date') ||
                    Carbon::parse($consumption['period_start'])->lte(Carbon::parse($request->end_date));
                return $startOk && $endOk;
            });
        }

        // Ordenar por fecha de fin descendente
        $allConsumptions = $allConsumptions->sortByDesc('period_end')->values();

        // Variables de paginación
        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 15;
        $total = $allConsumptions->count();

        // Extraer porción de la página
        $items = $allConsumptions->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json([
            'success' => true,
            'message' => 'Consumos obtenidos correctamente',
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage()
            ]
        ]);
    }

    /**
     * Calcular consumos para un sensor específico.
     */
    private function calculateConsumptionsForSensor($sensor, $user)
    {
        // Obtener todas las mediciones del sensor ordenadas por fecha
        $measurements = Measurement::where('sensor_id', $sensor->id)
            ->orderBy('measured_at', 'asc')
            ->get();

        if ($measurements->count() < 2) {
            return collect();
        }

        // Obtener unidad desde la plantilla
        $unit = 'unidades';
        $mainField = 'consumo_m3'; // Fallback
        if ($sensor->group && $sensor->group->template) {
            foreach ($sensor->group->template->schema['campos'] ?? [] as $campo) {
                if (($campo['tipo'] ?? '') === 'numero' && ($campo['requerido'] ?? false)) {
                    $mainField = $campo['nombre'];
                    $unit = $campo['unidad'] ?? 'unidades';
                    break;
                }
            }
        }

        // Calcular consumos entre mediciones consecutivas
        $consumptions = [];
        for ($i = 0; $i < $measurements->count() - 1; $i++) {
            $start = $measurements[$i];
            $end = $measurements[$i + 1];

            // Validar que la medición final sea posterior a la inicial
            if ($end->measured_at <= $start->measured_at) {
                continue;
            }

            $startValRaw = $start->data[$mainField] ?? $start->data['consumo_m3'] ?? $start->data['valor'] ?? 0;
            $endValRaw = $end->data[$mainField] ?? $end->data['consumo_m3'] ?? $end->data['valor'] ?? 0;

            // Validar que el valor final sea mayor al inicial
            if ((float) $endValRaw <= (float) $startValRaw) {
                continue;
            }

            $startValue = (float) $startValRaw;
            $endValue = (float) $endValRaw;
            $consumptionValue = round($endValue - $startValue, 2);
            $daysBetween = Carbon::parse($end->measured_at)->diffInDays(Carbon::parse($start->measured_at));

            if ($daysBetween < 0) {
                $daysBetween = abs($daysBetween);
            }

            $consumption = Consumption::updateOrCreate(
                [
                    'start_measurement_id' => $start->id,
                    'end_measurement_id' => $end->id,
                ],
                [
                    'sensor_id' => $sensor->id,
                    'value' => $consumptionValue,
                    'unit' => $unit,
                    'period_start' => $start->measured_at,
                    'period_end' => $end->measured_at,
                    'days_between' => $daysBetween,
                    'created_by' => $user->id,
                ]
            );

            // Agregar daily_average al array de respuesta
            $consumptionArray = $consumption->toArray();
            if ($daysBetween > 0) {
                $consumptionArray['daily_average'] = round($consumptionValue / $daysBetween, 2);
            } else {
                $consumptionArray['daily_average'] = 0;
            }

            // Asegurar que el sensor y grupo estén cargados (Requerido para la vista frontend)
            $consumptionArray['sensor'] = [
                'id' => $sensor->id,
                'name' => $sensor->name,
                'identifier' => $sensor->identifier,
                'is_community' => $sensor->is_community,
                'group' => [
                    'id' => $sensor->group ? $sensor->group->id : null,
                    'name' => $sensor->group ? $sensor->group->name : 'Sin grupo',
                    'template' => [
                        'type' => ($sensor->group && $sensor->group->template) ? $sensor->group->template->type : 'Desconocido'
                    ]
                ]
            ];

            $consumptions[] = $consumptionArray;
        }

        return collect($consumptions);
    }

    /**
     * Calcular consumo entre dos mediciones.
     */
    public function calculate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sensor_id' => 'required|exists:sensors,id',
            'start_measurement_id' => 'required|exists:measurements,id',
            'end_measurement_id' => 'required|exists:measurements,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $sensor = Sensor::findOrFail($request->sensor_id);
        $startMeasurement = Measurement::findOrFail($request->start_measurement_id);
        $endMeasurement = Measurement::findOrFail($request->end_measurement_id);

        // Verificar que las mediciones pertenezcan al sensor
        if ($startMeasurement->sensor_id !== $sensor->id || $endMeasurement->sensor_id !== $sensor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Las mediciones no pertenecen al sensor especificado',
            ], 400);
        }

        // Verificar permisos
        $canAccess = $user->hasRole('admin') ||
            ($sensor->group && $sensor->group->user_id === $user->id) ||
            ($sensor->group && $sensor->group->sharedAccess()->where('shared_with', $user->id)->exists());

        if (!$canAccess) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para acceder a este sensor',
            ], 403);
        }

        // Verificar que la medición final sea posterior a la inicial
        if ($endMeasurement->measured_at <= $startMeasurement->measured_at) {
            return response()->json([
                'success' => false,
                'message' => 'La medición final debe ser posterior a la inicial',
            ], 400);
        }

        // Verificar que el valor final sea mayor al inicial
        if ((float) $endMeasurement->data['valor'] <= (float) $startMeasurement->data['valor']) {
            return response()->json([
                'success' => false,
                'message' => 'El valor de la medición final debe ser mayor al inicial',
            ], 400);
        }

        // Calcular consumo
        $startValue = (float) $startMeasurement->data['valor'];
        $endValue = (float) $endMeasurement->data['valor'];
        $consumptionValue = round($endValue - $startValue, 2);
        $daysBetween = Carbon::parse($endMeasurement->measured_at)
            ->diffInDays(Carbon::parse($startMeasurement->measured_at));

        if ($daysBetween < 0) {
            $daysBetween = abs($daysBetween);
        }

        // Obtener unidad desde la plantilla del sensor
        $unit = 'unidades';
        if ($sensor->group && $sensor->group->template) {
            $firstField = collect($sensor->group->template->schema['campos'] ?? [])->first();
            $unit = $firstField['unidad'] ?? 'unidades';
        }

        // Crear o actualizar el consumo
        $consumption = Consumption::updateOrCreate(
            [
                'start_measurement_id' => $startMeasurement->id,
                'end_measurement_id' => $endMeasurement->id,
            ],
            [
                'sensor_id' => $sensor->id,
                'value' => $consumptionValue,
                'unit' => $unit,
                'period_start' => $startMeasurement->measured_at,
                'period_end' => $endMeasurement->measured_at,
                'days_between' => $daysBetween,
                'created_by' => $user->id,
            ]
        );

        // Agregar daily_average al objeto de respuesta
        $consumptionArray = $consumption->toArray();
        if ($daysBetween > 0) {
            $consumptionArray['daily_average'] = round($consumptionValue / $daysBetween, 2);
        } else {
            $consumptionArray['daily_average'] = 0;
        }

        return response()->json([
            'success' => true,
            'message' => 'Consumo calculado correctamente',
            'data' => $consumptionArray,
        ], 201);
    }

    /**
     * Calcular consumos automáticamente para un sensor.
     */
    public function calculateForSensor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sensor_id' => 'required|exists:sensors,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $sensor = Sensor::with(['group', 'group.template'])->findOrFail($request->sensor_id);

        // Verificar permisos
        $canAccess = $user->hasRole('admin') ||
            ($sensor->group && $sensor->group->user_id === $user->id) ||
            ($sensor->group && $sensor->group->sharedAccess()->where('shared_with', $user->id)->exists());

        if (!$canAccess) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para acceder a este sensor',
            ], 403);
        }

        // Obtener todas las mediciones del sensor ordenadas por fecha
        $measurements = Measurement::where('sensor_id', $sensor->id)
            ->orderBy('measured_at', 'asc')
            ->get();

        if ($measurements->count() < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Se necesitan al menos dos mediciones para calcular consumos',
            ], 400);
        }

        // Calcular consumos para este sensor
        $consumptions = $this->calculateConsumptionsForSensor($sensor, $user);

        return response()->json([
            'success' => true,
            'message' => 'Consumos calculados automáticamente para el sensor',
            'data' => $consumptions,
        ], 201);
    }

    /**
     * Mostrar un consumo específico.
     */
    public function show($id)
    {
        $id = (int) $id;
        $user = request()->user();

        try {
            $consumption = Consumption::with(['sensor', 'startMeasurement', 'endMeasurement', 'sensor.group', 'sensor.group.user'])
                ->find($id);

            if (!$consumption) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el consumo con ID: ' . $id,
                ], 404);
            }

            // Verificar permisos
            $canAccess = $user->hasRole('admin') ||
                ($consumption->sensor->group && $consumption->sensor->group->user_id === $user->id) ||
                ($consumption->sensor->group && $consumption->sensor->group->sharedAccess()->where('shared_with', $user->id)->exists());

            if (!$canAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para acceder a este consumo',
                ], 403);
            }

            // Calcular daily_average
            $consumptionArray = $consumption->toArray();
            $daysBetween = $consumption->days_between;

            if ($daysBetween < 0) {
                $daysBetween = abs($daysBetween);
                $consumptionArray['days_between'] = $daysBetween;
            }

            if ($daysBetween > 0) {
                $consumptionArray['daily_average'] = round((float) $consumption->value / $daysBetween, 2);
            } else {
                $consumptionArray['daily_average'] = 0;
            }

            return response()->json([
                'success' => true,
                'message' => 'Consumo obtenido correctamente',
                'data' => $consumptionArray,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el consumo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar un consumo.
     */
    public function destroy(Consumption $consumption)
    {
        $user = request()->user();

        // Verificar permisos
        $canDelete = $user->hasRole('admin') ||
            ($consumption->sensor->group && $consumption->sensor->group->user_id === $user->id);

        if (!$canDelete) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar este consumo',
            ], 403);
        }

        $consumption->delete();

        return response()->json([
            'success' => true,
            'message' => 'Consumo eliminado correctamente',
        ]);
    }

    /**
     * Recalcular todos los consumos para el usuario.
     */
    public function calculateAll(Request $request)
    {
        $user = $request->user();

        // Obtener todos los sensores del usuario
        $sensors = Sensor::where(function ($query) use ($user) {
            $query->whereHas('group', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->orWhereHas('group.sharedAccess', function ($q) use ($user) {
                $q->where('shared_with', $user->id);
            });
        })->with(['group', 'group.template'])->get();

        $totalConsumptions = 0;

        foreach ($sensors as $sensor) {
            // Calcular consumos para este sensor
            $consumptions = $this->calculateConsumptionsForSensor($sensor, $user);
            $totalConsumptions += $consumptions->count();
        }

        return response()->json([
            'success' => true,
            'message' => "Se recalcularon $totalConsumptions consumos para {$sensors->count()} sensores",
            'data' => [
                'sensors_processed' => $sensors->count(),
                'consumptions_calculated' => $totalConsumptions
            ]
        ]);
    }

    /**
     * Exportar consumos a Excel.
     */
    public function export(Request $request)
    {
        $user = $request->user();

        // Obtener sensores del usuario
        $sensors = Sensor::where(function ($query) use ($user) {
            $query->whereHas('group', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->orWhereHas('group.sharedAccess', function ($q) use ($user) {
                $q->where('shared_with', $user->id);
            });
        })->pluck('id');

        // Filtrar por sensor si se proporciona
        if ($request->has('sensor_id')) {
            $sensors = [$request->sensor_id];
        }

        // Obtener consumos
        $query = Consumption::whereIn('sensor_id', $sensors)
            ->with(['sensor', 'sensor.group', 'sensor.group.user']);

        // Filtrar por fecha de inicio
        if ($request->has('start_date')) {
            $query->where('period_start', '>=', Carbon::parse($request->start_date));
        }

        // Filtrar por fecha de fin
        if ($request->has('end_date')) {
            $query->where('period_end', '<=', Carbon::parse($request->end_date));
        }

        $consumptions = $query->orderBy('period_end', 'desc')->get();

        // Calcular daily_average para cada consumo
        $consumptions = $consumptions->map(function ($consumption) {
            $consumptionArray = $consumption->toArray();
            $daysBetween = $consumption->days_between;

            if ($daysBetween < 0) {
                $daysBetween = abs($daysBetween);
                $consumptionArray['days_between'] = $daysBetween;
            }

            if ($daysBetween > 0) {
                $consumptionArray['daily_average'] = round((float) $consumption->value / $daysBetween, 2);
            } else {
                $consumptionArray['daily_average'] = 0;
            }

            return $consumptionArray;
        });

        // Crear el archivo Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Sensor');
        $sheet->setCellValue('C1', 'Grupo');
        $sheet->setCellValue('D1', 'Consumo');
        $sheet->setCellValue('E1', 'Unidad');
        $sheet->setCellValue('F1', 'Inicio');
        $sheet->setCellValue('G1', 'Fin');
        $sheet->setCellValue('H1', 'Días');
        $sheet->setCellValue('I1', 'Promedio Diario');

        // Datos
        $row = 2;
        foreach ($consumptions as $consumption) {
            $sheet->setCellValue('A' . $row, $consumption['id']);
            $sheet->setCellValue('B' . $row, $consumption['sensor']['name'] ?? 'N/A');
            $sheet->setCellValue('C' . $row, $consumption['sensor']['group']['name'] ?? 'N/A');
            $sheet->setCellValue('D' . $row, $consumption['value']);
            $sheet->setCellValue('E' . $row, $consumption['unit']);
            $sheet->setCellValue('F' . $row, $consumption['period_start']);
            $sheet->setCellValue('G' . $row, $consumption['period_end']);
            $sheet->setCellValue('H' . $row, $consumption['days_between']);
            $sheet->setCellValue('I' . $row, $consumption['daily_average'] ?? 0);
            $row++;
        }

        // Estilos
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle('A1:I1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFDDDDDD');

        // Descargar el archivo
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'consumos_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }



    /**
     * Calcular consumo histórico en un rango de fechas custom
     */
    public function calculateRange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sensor_id' => 'required|exists:sensors,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $sensorId = $request->sensor_id;
        $sensor = Sensor::with('group')->findOrFail($sensorId);

        // Verificar permisos
        $canAccess = $user->hasRole('admin') ||
            ($sensor->group && $sensor->group->user_id === $user->id) ||
            ($sensor->group && $sensor->group->sharedAccess()->where('shared_with', $user->id)->exists());

        if (!$canAccess) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para analizar este sensor',
            ], 403);
        }

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        // Obtener la primera medición dentro del rango (o la más cercana después)
        $startMeasurement = Measurement::where('sensor_id', $sensorId)
            ->where('measured_at', '>=', $startDate)
            ->orderBy('measured_at', 'asc')
            ->first();

        // Obtener la última medición dentro del rango (o la más cercana antes)
        $endMeasurement = Measurement::where('sensor_id', $sensorId)
            ->where('measured_at', '<=', $endDate)
            ->orderBy('measured_at', 'desc')
            ->first();

        if (!$startMeasurement || !$endMeasurement || $startMeasurement->id === $endMeasurement->id || $startMeasurement->measured_at >= $endMeasurement->measured_at) {
            return response()->json([
                'success' => false,
                'message' => 'No hay suficientes datos medidos dentro de este rango para calcular un delta válido.',
            ], 404);
        }

        $startValue = (float) ($startMeasurement->data['valor'] ?? 0);
        $endValue = (float) ($endMeasurement->data['valor'] ?? 0);

        $delta = round($endValue - $startValue, 2);
        $daysBetween = Carbon::parse($endMeasurement->measured_at)->diffInDays(Carbon::parse($startMeasurement->measured_at));
        if ($daysBetween < 0)
            $daysBetween = abs($daysBetween);

        $dailyAverage = 0;
        if ($daysBetween > 0) {
            $dailyAverage = round($delta / $daysBetween, 2);
        }

        $unit = 'unidades';
        if ($sensor->group && $sensor->group->template) {
            $firstField = collect($sensor->group->template->schema['campos'] ?? [])->first();
            $unit = $firstField['unidad'] ?? 'unidades';
        }

        $intermediateMeasurements = Measurement::where('sensor_id', $sensorId)
            ->whereBetween('measured_at', [$startMeasurement->measured_at, $endMeasurement->measured_at])
            ->orderBy('measured_at', 'asc')
            ->get();

        $measurementsCount = $intermediateMeasurements->count();

        $chartData = [];
        $previousValue = null;
        $previousDate = null;
        $previousDailyRate = null;

        $thresholdPercent = $request->input('anomaly_threshold', 50); // Por defecto 50%
        $thresholdMultiplier = $thresholdPercent / 100;

        $stagnationDays = (int) $request->input('stagnation_days', 15); // Tolerancia de estancamiento

        foreach ($intermediateMeasurements as $m) {
            $val = (float) ($m->data['valor'] ?? 0);
            $currentDate = Carbon::parse($m->measured_at);
            $isAnomaly = false;

            if ($previousValue !== null && $previousDate !== null) {
                $daysDelta = $currentDate->diffInDays($previousDate);
                if ($daysDelta < 0) {
                    $daysDelta = abs($daysDelta);
                }

                // Prevenir división por cero
                if ($daysDelta < 1) {
                    $daysDelta = 1;
                }

                $currentDailyRate = abs($val - $previousValue) / $daysDelta;

                // 1. Detección de Estancamiento
                if ($val == $previousValue && $daysDelta >= $stagnationDays) {
                    $isAnomaly = true;
                }
                // 2. Detección de salto en la Tasa Diaria
                elseif ($previousDailyRate !== null && $previousDailyRate > 0) {
                    $rateChange = abs(($currentDailyRate - $previousDailyRate) / $previousDailyRate);
                    if ($rateChange > $thresholdMultiplier) {
                        $isAnomaly = true;
                    }
                }

                $previousDailyRate = $currentDailyRate;
            }

            $chartData[] = [
                'id' => $m->id,
                'date' => $currentDate->format('d/m/Y H:i'),
                'value' => $val,
                'anomaly' => $isAnomaly,
                'photo' => $m->data['foto'] ?? null,
            ];
            $previousValue = $val;
            $previousDate = $currentDate;
        }

        // ==========================================
        // 🌿 CÁLCULO DE PRORRATEO COMUNITARIO
        // ==========================================
        $communityContribution = 0;
        $totalCommunityDelta = 0;

        if (!$sensor->is_community && $sensor->group) {
            $communitySensors = Sensor::where('group_id', $sensor->group_id)->where('is_community', true)->get();
            $privateSensorsCount = Sensor::where('group_id', $sensor->group_id)->where('is_community', false)->count();

            if ($privateSensorsCount > 0 && $communitySensors->count() > 0) {
                foreach ($communitySensors as $cSensor) {
                    // Ignorar si está marcado como "Modo Estadístico" (prorratear_comunidad = false / '0')
                    $isProrated = ($cSensor->metadata['prorratear_comunidad'] ?? '1') == '1';
                    if (!$isProrated)
                        continue;

                    // Buscar delta de cada sensor comunitario en este mismo periodo de fechas
                    $cStart = Measurement::where('sensor_id', $cSensor->id)
                        ->where('measured_at', '>=', $startDate)
                        ->orderBy('measured_at', 'asc')->first();
                    $cEnd = Measurement::where('sensor_id', $cSensor->id)
                        ->where('measured_at', '<=', $endDate)
                        ->orderBy('measured_at', 'desc')->first();

                    if ($cStart && $cEnd && $cStart->measured_at < $cEnd->measured_at) {
                        $cStartVal = (float) ($cStart->data['valor'] ?? 0);
                        $cEndVal = (float) ($cEnd->data['valor'] ?? 0);
                        $totalCommunityDelta += round(max(0, $cEndVal - $cStartVal), 2);
                    }
                }

                $communityContribution = round($totalCommunityDelta / $privateSensorsCount, 2);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Rango calculado con éxito',
            'data' => [
                'sensor_id' => $sensor->id,
                'sensor_name' => $sensor->name,
                'unit' => $unit,
                'period_start' => $startMeasurement->measured_at,
                'period_end' => $endMeasurement->measured_at,
                'start_value' => $startValue,
                'end_value' => $endValue,
                'total_consumption' => $delta,
                'community_contribution' => $communityContribution,
                'final_billed_total' => round($delta + $communityContribution, 2),
                'days_between' => $daysBetween,
                'daily_average' => $dailyAverage,
                'measurements_count' => $measurementsCount,
                'chart_data' => $chartData
            ]
        ]);
    }

    /**
     * Extraer meta información de las mediciones del sensor (rangos máximos posibles)
     */
    public function getSensorMeta(Request $request, $sensorId)
    {
        $user = $request->user();
        $sensor = Sensor::with('group')->findOrFail($sensorId);

        $canAccess = $user->hasRole('admin') ||
            ($sensor->group && $sensor->group->user_id === $user->id) ||
            ($sensor->group && $sensor->group->sharedAccess()->where('shared_with', $user->id)->exists());

        if (!$canAccess) {
            return response()->json(['success' => false, 'message' => 'Sin permiso'], 403);
        }

        $first = Measurement::where('sensor_id', $sensorId)->orderBy('measured_at', 'asc')->first();
        $last = Measurement::where('sensor_id', $sensorId)->orderBy('measured_at', 'desc')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'first_date' => $first ? Carbon::parse($first->measured_at)->format('Y-m-d') : null,
                'last_date' => $last ? Carbon::parse($last->measured_at)->format('Y-m-d') : null,
            ]
        ]);
    }

    /**
     * Revisa todos los sensores a los que el usuario tiene acceso
     * para buscar anomalías de tiempo-ponderado en una fecha dada.
     */
    public function calculateGlobalAnomalies(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'anomaly_threshold' => 'nullable|numeric|min:1',
            'stagnation_days' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        $threshold = $request->anomaly_threshold ?? 50;
        $stagnationThresh = $request->stagnation_days ?? 15;

        // Fetch all accessible sensors
        $sensors = Sensor::with('group')
            ->whereHas('group', function ($query) use ($user) {
                if (!$user->hasRole('admin')) {
                    $query->where('user_id', $user->id)
                        ->orWhereHas('sharedAccess', function ($q) use ($user) {
                            $q->where('shared_with', $user->id);
                        });
                }
            })
            ->get();

        $anomaliesList = [];

        foreach ($sensors as $sensor) {
            $measurements = \App\Models\Measurement::where('sensor_id', $sensor->id)
                ->whereBetween('measured_at', [$startDate, $endDate])
                ->orderBy('measured_at', 'asc')
                ->get();

            if ($measurements->count() < 2) {
                continue; // Not enough data to calculate rates
            }

            $anomaliesCount = 0;
            $stagnationsCount = 0;
            $accelerationsCount = 0;
            $previousValue = null;
            $previousDate = null;
            $previousRate = null;

            foreach ($measurements as $m) {
                $val = 0;

                // Helper to extract value
                $fields = ['valor', 'consumo_m3', 'consumo', 'value', 'medicion'];
                foreach ($fields as $field) {
                    if (isset($m->data[$field])) {
                        $val = (float) $m->data[$field];
                        break;
                    }
                }
                if ($val == 0) {
                    foreach ($m->data as $k => $v) {
                        if (is_numeric($v)) {
                            $val = (float) $v;
                            break;
                        }
                    }
                }

                $currentDate = Carbon::parse($m->measured_at);
                $isAnomaly = false;

                if ($previousValue !== null && $previousDate !== null) {
                    $daysDiff = $previousDate->diffInDays($currentDate) ?: 1;

                    if ($daysDiff == 0) {
                        $daysDiff = 1;
                    }

                    $deltaRaw = $val - $previousValue;
                    $currentRate = $deltaRaw / $daysDiff;

                    // Stagnation logic
                    if ($deltaRaw == 0 && $daysDiff > $stagnationThresh) {
                        $isAnomaly = true;
                        $stagnationsCount++;
                    }

                    // Acceleration logic
                    if (!$isAnomaly && $previousRate !== null && $previousRate > 0) {
                        $ratePercentChange = abs(($currentRate - $previousRate) / $previousRate) * 100;
                        if ($ratePercentChange > $threshold) {
                            $isAnomaly = true;
                            $accelerationsCount++;
                        }
                    }

                    $previousRate = $currentRate;

                    if ($isAnomaly) {
                        $anomaliesCount++;
                    }
                }

                $previousValue = $val;
                $previousDate = $currentDate;
            }

            if ($anomaliesCount > 0) {
                $anomaliesList[] = [
                    'sensor_id' => $sensor->id,
                    'sensor_name' => $sensor->name,
                    'sensor_identifier' => $sensor->identifier ?? 'N/A',
                    'anomaly_count' => $anomaliesCount,
                    'stagnation_count' => $stagnationsCount,
                    'acceleration_count' => $accelerationsCount
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $anomaliesList
        ]);
    }
}