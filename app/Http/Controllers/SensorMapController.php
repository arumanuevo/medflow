<?php

namespace App\Http\Controllers;

use App\Models\Measurement;
use App\Models\Sensor;
use App\Models\SensorGroup;
use App\Models\User;
use App\Models\WorkspaceCollaborator;
use App\Services\MeasurementScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SensorMapController extends Controller
{
    /**
     * Verifica acceso al módulo Premium de Mapa GIS y resuelve los grupos accesibles en el espacio activo.
     */
    private function resolveWorkspaceAccess(Request $request): array
    {
        $user = $request->user();
        $activeWorkspace = session('active_workspace', $user->id);
        $isOwner = (int) $activeWorkspace === (int) $user->id;

        $workspaceOwner = $isOwner ? $user : User::find($activeWorkspace);
        if (!$workspaceOwner) {
            return ['allowed' => false, 'reason' => 'Espacio de trabajo no encontrado.'];
        }

        // Validar que el propietario del espacio tenga plan Premium (o sea SuperAdmin)
        $subService = app(\App\Services\Subscription\SubscriptionService::class, ['user' => $workspaceOwner]);
        $isSuperAdmin = $user->email === 'scastellanoadmin@gmail.com';

        if (!$isSuperAdmin && $subService->getPlan()->getPlanKey() !== 'premium') {
            return [
                'allowed' => false,
                'reason' => 'El Mapa Interactivo de Sensores (GIS) es una aplicación exclusiva del Plan Premium.'
            ];
        }

        if (!$isOwner) {
            $collaboration = WorkspaceCollaborator::where('workspace_id', $activeWorkspace)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where('is_paused', false)
                ->whereIn('role', ['admin', 'inspector'])
                ->first();

            if (!$collaboration) {
                return ['allowed' => false, 'reason' => 'No tienes permisos para acceder al mapa de este espacio.'];
            }
        }

        $groups = SensorGroup::where(function ($q) use ($activeWorkspace, $user, $isOwner) {
            if ($isOwner) {
                $q->where('user_id', $activeWorkspace)
                    ->orWhereHas('sharedAccess', function ($q2) use ($user) {
                        $q2->where('shared_with', $user->id)
                            ->whereIn('role', ['admin', 'inspector']);
                    });
            } else {
                $q->where('user_id', $activeWorkspace);
            }
        })
            ->with('template')
            ->withCount('sensors')
            ->orderBy('name')
            ->get();

        return [
            'allowed' => true,
            'user' => $user,
            'activeWorkspace' => $activeWorkspace,
            'isOwner' => $isOwner,
            'groups' => $groups,
        ];
    }

    /**
     * Extrae latitud y longitud normalizadas desde el atributo coordinates del sensor.
     */
    private function extractLatLng($coordinates): array
    {
        if (is_string($coordinates)) {
            $decoded = json_decode($coordinates, true);
            if (is_array($decoded)) {
                $coordinates = $decoded;
            }
        }

        if (!is_array($coordinates)) {
            return ['lat' => null, 'lng' => null];
        }

        $lat = $coordinates['lat'] ?? $coordinates['latitude'] ?? $coordinates[0] ?? null;
        $lng = $coordinates['lng'] ?? $coordinates['lon'] ?? $coordinates['longitude'] ?? $coordinates[1] ?? null;

        if ($lat !== null && $lng !== null && is_numeric($lat) && is_numeric($lng)) {
            $latFloat = (float) $lat;
            $lngFloat = (float) $lng;
            if ($latFloat >= -90 && $latFloat <= 90 && $lngFloat >= -180 && $lngFloat <= 180) {
                return ['lat' => $latFloat, 'lng' => $lngFloat];
            }
        }

        return ['lat' => null, 'lng' => null];
    }

    /**
     * Extrae el valor numérico principal desde el array data de una medición.
     */
    private function extractMeasurementNumericValue($data): ?float
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        if (!is_array($data)) {
            return null;
        }

        $preferredKeys = ['valor', 'consumo_m3', 'energia_kwh', 'caudal_lmin', 'iluminacion_lux', 'consumo', 'value', 'medicion'];
        foreach ($preferredKeys as $key) {
            if (isset($data[$key]) && is_numeric($data[$key])) {
                return (float) $data[$key];
            }
        }

        foreach ($data as $k => $v) {
            if (!in_array($k, ['foto', 'fecha_medicion', 'tipo']) && is_numeric($v)) {
                return (float) $v;
            }
        }

        return null;
    }

    /**
     * Determina la unidad de medida principal de la plantilla del grupo.
     */
    private function resolveSensorUnit(Sensor $sensor): string
    {
        if ($sensor->group && $sensor->group->template) {
            $fields = $sensor->group->template->getFields();
            foreach ($fields as $campo) {
                if (($campo['tipo'] ?? '') === 'numero' && !empty($campo['unidad'])) {
                    return $campo['unidad'];
                }
            }
            $type = strtolower($sensor->group->template->type ?? '');
            if (str_contains($type, 'agua')) return 'm³';
            if (str_contains($type, 'electr') || str_contains($type, 'luz')) return 'kWh';
            if (str_contains($type, 'gas')) return 'm³';
        }
        return 'unid.';
    }

    /**
     * Vista principal del Mapa Interactivo de Sensores (Exclusivo Premium) con Inteligencia GIS Operativa y de Consumos.
     */
    public function index(Request $request, MeasurementScheduleService $scheduleService)
    {
        $access = $this->resolveWorkspaceAccess($request);
        if (!$access['allowed']) {
            return redirect()->route('dashboard')->with('error', $access['reason']);
        }

        $groups = $access['groups'];
        $groupIds = $groups->pluck('id')->toArray();

        $sensors = Sensor::whereIn('group_id', $groupIds)
            ->with(['group.template.parent', 'lastMeasurement'])
            ->orderBy('name')
            ->get();

        $sensorIds = $sensors->pluck('id')->toArray();

        // Precargar las últimas mediciones por sensor en bloque (evita N+1 para análisis de consumo y picos)
        $recentMeasurementsBySensor = [];
        if (!empty($sensorIds)) {
            $allMeasurements = Measurement::whereIn('sensor_id', $sensorIds)
                ->orderBy('measured_at', 'desc')
                ->get(['id', 'sensor_id', 'measured_at', 'data', 'created_at']);

            foreach ($allMeasurements as $m) {
                $sId = $m->sensor_id;
                if (!isset($recentMeasurementsBySensor[$sId])) {
                    $recentMeasurementsBySensor[$sId] = [];
                }
                if (count($recentMeasurementsBySensor[$sId]) < 6) {
                    $recentMeasurementsBySensor[$sId][] = $m;
                }
            }
        }

        $today = Carbon::now()->startOfDay();
        $sensorsData = [];
        $locatedCount = 0;

        // Contadores de Inteligencia GIS
        $opStats = [
            'completed' => 0,     // Medidos / Al día
            'in_window' => 0,     // En ventana / ¡Hoy toca medir!
            'in_tolerance' => 0,  // En tolerancia
            'overdue' => 0,       // Atrasados / Vencidos
            'scheduled' => 0,     // Programados a futuro
        ];

        $consumptionStats = [
            'peak' => 0,          // Picos de consumo (>= +50% ritmo diario)
            'stagnant' => 0,      // Estancados (0 consumo en >= 15 días)
            'normal' => 0,        // Consumo normal
            'no_data' => 0,       // Sin historial suficiente (< 2 lecturas)
        ];

        foreach ($sensors as $sensor) {
            $coords = $this->extractLatLng($sensor->coordinates);
            $hasCoords = $coords['lat'] !== null && $coords['lng'] !== null;
            if ($hasCoords) {
                $locatedCount++;
            }

            $templateType = $sensor->group && $sensor->group->template
                ? ($sensor->group->template->type ?? 'personalizado')
                : 'personalizado';
            $unit = $this->resolveSensorUnit($sensor);

            // 1. Evaluar estado operativo actual (Calendario y Tolerancias)
            $scheduleEval = $scheduleService->evaluateCurrentSensorStatus($sensor, $today);
            $opStatus = $scheduleEval['status'] ?? 'scheduled'; // completed | scheduled | in_window | in_tolerance | overdue
            if (isset($opStats[$opStatus])) {
                $opStats[$opStatus]++;
            }

            // 2. Evaluar historial de lecturas, consumo reciente, tasa diaria y anomalías (picos / estancamiento)
            $sensorMeasurementsDesc = $recentMeasurementsBySensor[$sensor->id] ?? [];
            $sensorMeasurementsAsc = array_reverse($sensorMeasurementsDesc);

            $lastValue = null;
            $lastDate = null;
            $daysSinceLast = null;

            if (!empty($sensorMeasurementsDesc)) {
                $latestM = $sensorMeasurementsDesc[0];
                $lastValue = $this->extractMeasurementNumericValue($latestM->data);
                $dt = $latestM->measured_at ?: $latestM->created_at;
                if ($dt) {
                    $lastDate = Carbon::parse($dt)->format('d/m/Y H:i');
                    $daysSinceLast = (int) Carbon::parse($dt)->startOfDay()->diffInDays($today);
                }
            }

            $lastDelta = null;
            $dailyRate = null;
            $prevDailyRate = null;
            $rateVariationPct = null;
            $consumptionStatus = 'no_data'; // peak | stagnant | normal | no_data
            $consumptionLabel = 'Sin historial suficiente';
            $historyPoints = [];

            $prevVal = null;
            $prevDate = null;
            $ratesHistory = [];

            foreach ($sensorMeasurementsAsc as $m) {
                $val = $this->extractMeasurementNumericValue($m->data);
                if ($val === null) {
                    continue;
                }
                $mDate = Carbon::parse($m->measured_at ?: $m->created_at);
                $pointDelta = null;
                $pointDailyRate = null;
                $pointDays = null;

                if ($prevVal !== null && $prevDate !== null) {
                    $pointDays = max(1, (int) abs($mDate->diffInDays($prevDate)));
                    $pointDelta = round(max(0, $val - $prevVal), 2);
                    $pointDailyRate = round($pointDelta / $pointDays, 2);
                    $ratesHistory[] = [
                        'delta' => $pointDelta,
                        'days' => $pointDays,
                        'rate' => $pointDailyRate,
                    ];
                }

                $historyPoints[] = [
                    'date' => $mDate->format('d/m/Y'),
                    'full_date' => $mDate->format('d/m/Y H:i'),
                    'value' => round($val, 2),
                    'delta' => $pointDelta,
                    'daily_rate' => $pointDailyRate,
                    'days' => $pointDays,
                ];

                $prevVal = $val;
                $prevDate = $mDate;
            }

            if (count($ratesHistory) >= 1) {
                $latestSegment = $ratesHistory[count($ratesHistory) - 1];
                $lastDelta = $latestSegment['delta'];
                $dailyRate = $latestSegment['rate'];
                $latestDays = $latestSegment['days'];

                if (count($ratesHistory) >= 2) {
                    $prevSegment = $ratesHistory[count($ratesHistory) - 2];
                    $prevDailyRate = $prevSegment['rate'];
                    if ($prevDailyRate > 0) {
                        $rateVariationPct = round((($dailyRate - $prevDailyRate) / $prevDailyRate) * 100, 1);
                    } elseif ($dailyRate > 0) {
                        $rateVariationPct = 100.0;
                    } else {
                        $rateVariationPct = 0.0;
                    }
                }

                // Clasificación de anomalía de consumo (igual criterio que Análisis Avanzado de Consumos)
                if ($lastDelta == 0 && $latestDays >= 15) {
                    $consumptionStatus = 'stagnant';
                    $consumptionLabel = "Estancado (0 {$unit} en {$latestDays}d)";
                } elseif ($rateVariationPct !== null && $rateVariationPct >= 50) {
                    $consumptionStatus = 'peak';
                    $consumptionLabel = "Pico de consumo (+{$rateVariationPct}%)";
                } else {
                    $consumptionStatus = 'normal';
                    $consumptionLabel = 'Consumo normal';
                }
            }

            if (isset($consumptionStats[$consumptionStatus])) {
                $consumptionStats[$consumptionStatus]++;
            }

            $sensorsData[] = [
                'id' => $sensor->id,
                'name' => $sensor->name,
                'identifier' => $sensor->identifier,
                'description' => $sensor->description ?? '',
                'is_community' => (bool) $sensor->is_community,
                'group_id' => $sensor->group_id,
                'group_name' => $sensor->group ? $sensor->group->name : 'Sin grupo',
                'template_type' => $templateType,
                'unit' => $unit,
                'lat' => $coords['lat'],
                'lng' => $coords['lng'],
                'has_coords' => $hasCoords,
                // Datos de última lectura
                'last_value' => $lastValue,
                'last_measured_at' => $lastDate,
                'days_since_last' => $daysSinceLast,
                // Estado Operativo (Calendario y Tolerancias)
                'op_status' => $opStatus, // completed | scheduled | in_window | in_tolerance | overdue
                'op_label' => $scheduleEval['status_label'] ?? 'Programado',
                'op_message' => $scheduleEval['status_message'] ?? '',
                'target_date' => $scheduleEval['target_date_formatted'] ?? null,
                'tolerance_end' => $scheduleEval['tolerance_end_formatted'] ?? null,
                // Inteligencia de Consumo y Anomalías
                'consumption_status' => $consumptionStatus, // peak | stagnant | normal | no_data
                'consumption_label' => $consumptionLabel,
                'last_delta' => $lastDelta,
                'daily_rate' => $dailyRate,
                'prev_daily_rate' => $prevDailyRate,
                'rate_variation_pct' => $rateVariationPct,
                'history' => $historyPoints,
                // Enlaces directos
                'show_url' => url('/sensors/' . $sensor->id),
                'edit_url' => url('/sensors/' . $sensor->id . '/edit'),
                'measure_url' => url('/mediciones/create/' . $sensor->id),
            ];
        }

        $totalCount = count($sensorsData);
        $unlocatedCount = max(0, $totalCount - $locatedCount);

        $stats = [
            'total' => $totalCount,
            'located' => $locatedCount,
            'unlocated' => $unlocatedCount,
            'coverage_percent' => $totalCount > 0 ? round(($locatedCount / $totalCount) * 100) : 0,
            'op' => $opStats,
            'pending_window' => ($opStats['in_window'] ?? 0) + ($opStats['in_tolerance'] ?? 0),
            'consumption' => $consumptionStats,
        ];

        return view('sensor-map.index', compact('groups', 'sensorsData', 'stats'));
    }

    /**
     * Guarda o actualiza las coordenadas (lat, lng) de un sensor vía AJAX.
     */
    public function updateCoordinates(Request $request, Sensor $sensor)
    {
        $access = $this->resolveWorkspaceAccess($request);
        if (!$access['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $access['reason']
            ], 403);
        }

        $allowedGroupIds = $access['groups']->pluck('id')->toArray();
        if (!in_array($sensor->group_id, $allowedGroupIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para modificar este sensor.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Coordenadas inválidas. Latitud debe estar entre -90 y 90, y Longitud entre -180 y 180.',
                'errors' => $validator->errors()
            ], 422);
        }

        $lat = round((float) $request->input('lat'), 7);
        $lng = round((float) $request->input('lng'), 7);

        $sensor->coordinates = [
            'lat' => $lat,
            'lng' => $lng,
        ];
        $sensor->save();

        return response()->json([
            'success' => true,
            'message' => "Coordenadas guardadas para \"{$sensor->name}\".",
            'data' => [
                'id' => $sensor->id,
                'lat' => $lat,
                'lng' => $lng,
                'has_coords' => true,
            ]
        ]);
    }

    /**
     * Elimina las coordenadas asignadas a un sensor vía AJAX.
     */
    public function clearCoordinates(Request $request, Sensor $sensor)
    {
        $access = $this->resolveWorkspaceAccess($request);
        if (!$access['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $access['reason']
            ], 403);
        }

        $allowedGroupIds = $access['groups']->pluck('id')->toArray();
        if (!in_array($sensor->group_id, $allowedGroupIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para modificar este sensor.'
            ], 403);
        }

        $sensor->coordinates = null;
        $sensor->save();

        return response()->json([
            'success' => true,
            'message' => "Se quitó la ubicación en el mapa de \"{$sensor->name}\".",
            'data' => [
                'id' => $sensor->id,
                'lat' => null,
                'lng' => null,
                'has_coords' => false,
            ]
        ]);
    }
}
