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
    $sensors = Sensor::where(function($query) use ($user) {
        $query->whereHas('group', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->orWhereHas('group.sharedAccess', function($q) use ($user) {
            $q->where('shared_with', $user->id);
        });
    })->with(['group', 'group.template'])->get();

    // Filtrar por sensor si se proporciona
    if ($request->has('sensor_id')) {
        $sensors = $sensors->where('id', $request->sensor_id);
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
                    $consumptionArray['daily_average'] = round((float)$consumption->value / $daysBetween, 2);
                } else {
                    $consumptionArray['daily_average'] = 0;
                }

                // Asegurar que el sensor y grupo estén cargados
                $consumptionArray['sensor'] = [
                    'id' => $sensor->id,
                    'name' => $sensor->name,
                    'identifier' => $sensor->identifier,
                    'group' => [
                        'id' => $sensor->group->id,
                        'name' => $sensor->group->name,
                    ]
                ];

                $allConsumptions->push($consumptionArray);
            }
        }
    }

    // Aplicar filtros de fecha si existen
    if ($request->has('start_date') || $request->has('end_date')) {
        $allConsumptions = $allConsumptions->filter(function($consumption) use ($request) {
            $startOk = !$request->has('start_date') ||
                       Carbon::parse($consumption['period_end'])->gte(Carbon::parse($request->start_date));
            $endOk = !$request->has('end_date') ||
                     Carbon::parse($consumption['period_start'])->lte(Carbon::parse($request->end_date));
            return $startOk && $endOk;
        });
    }

    // Ordenar por fecha de fin descendente
    $allConsumptions = $allConsumptions->sortByDesc('period_end')->values();

    return response()->json([
        'success' => true,
        'message' => 'Consumos obtenidos correctamente',
        'data' => $allConsumptions,
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
        if ($sensor->group && $sensor->group->template) {
            $firstField = collect($sensor->group->template->schema['campos'] ?? [])->first();
            $unit = $firstField['unidad'] ?? 'unidades';
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

            // Validar que el valor final sea mayor al inicial
            if ((float)$end->data['valor'] <= (float)$start->data['valor']) {
                continue;
            }

            $startValue = (float) $start->data['valor'];
            $endValue = (float) $end->data['valor'];
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
        if ((float)$endMeasurement->data['valor'] <= (float)$startMeasurement->data['valor']) {
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
                $consumptionArray['daily_average'] = round((float)$consumption->value / $daysBetween, 2);
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
    $sensors = Sensor::where(function($query) use ($user) {
        $query->whereHas('group', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->orWhereHas('group.sharedAccess', function($q) use ($user) {
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
    $sensors = Sensor::where(function($query) use ($user) {
        $query->whereHas('group', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->orWhereHas('group.sharedAccess', function($q) use ($user) {
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
            $consumptionArray['daily_average'] = round((float)$consumption->value / $daysBetween, 2);
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


}