<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sensor;
use App\Models\Measurement;
use App\Models\Template;
use App\Mail\MobileAccessInvite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class MobileOfflineController extends Controller
{
    /**
     * Endpoint 1: "Vomitador de Sensores" (Descarga del Payload)
     * GET /api/mobile/v1/sensors?limit=N
     * Devuelve la lista de sensores a la App Móvil para guardarlos localmente en SQLite.
     */
    public function getSensors(Request $request)
    {
        $user = $request->user();

        // Límite de sensores y IDs específicos desde el token
        $tokenLimit = 0;
        $tokenSensorIds = [];
        $tokenGroupId = 0;
        $accessToken = $user?->currentAccessToken();
        if ($accessToken && method_exists($accessToken, 'getAttribute')) {
            $abilities = $accessToken->getAttribute('abilities');
            if (is_array($abilities)) {
                foreach ($abilities as $ability) {
                    if (is_string($ability)) {
                        if (str_starts_with($ability, 'sensor-limit:')) {
                            $tokenLimit = (int) substr($ability, strlen('sensor-limit:'));
                        }
                        if (str_starts_with($ability, 'sensor-ids:')) {
                            $idsStr = substr($ability, strlen('sensor-ids:'));
                            $tokenSensorIds = array_filter(explode(',', $idsStr));
                        }
                        if (str_starts_with($ability, 'group-id:')) {
                            $tokenGroupId = (int) substr($ability, strlen('group-id:'));
                        }
                    }
                }
            }
        }
        $queryLimit = (int) $request->query('limit', 0);
        $queryGroupId = (int) $request->query('group_id', 0);
        // Tomar el más restrictivo (>0). Si ambos son 0, sin límite.
        $limit = ($tokenLimit > 0 && $queryLimit > 0)
            ? min($tokenLimit, $queryLimit)
            : max($tokenLimit, $queryLimit);

        $groupId = $tokenGroupId > 0 ? $tokenGroupId : $queryGroupId;

        $query = Sensor::with(['group', 'lastMeasurement'])
            ->whereHas('group', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        // Aplicar Zona Geográfica / Ruta Asignada
        if ($groupId > 0) {
            $query->where('group_id', $groupId);
        }

        // Si el token tiene IDs específicos, filtrar SOLO por esos IDs
        if (!empty($tokenSensorIds)) {
            $query->whereIn('id', $tokenSensorIds);
            if ($limit > 0) {
                $query->take($limit);
            }
        } elseif ($limit > 0) {
            $query->take($limit);
        }

        // 2. MAGIA DE SINCRONIZACIÓN INTELIGENTE: (Evita medir algo dos veces)
        // Ignorar los sensores que hayan recibido una medición 'hoy'.
        $query->whereDoesntHave('measurements', function ($q) {
            $q->whereDate('measured_at', Carbon::today());
        });

        $sensors = $query->get()->map(function ($sensor) {
            $mainField = 'valor';
            if ($sensor->group && $sensor->group->template && isset($sensor->group->template->schema['campos'])) {
                foreach ($sensor->group->template->schema['campos'] as $campo) {
                    if ($campo['tipo'] === 'numero' && ($campo['requerido'] ?? false)) {
                        $mainField = $campo['nombre'];
                        break;
                    }
                }
            }

            $lastValue = null;
            if ($sensor->lastMeasurement && isset($sensor->lastMeasurement->data[$mainField])) {
                $lastValue = $sensor->lastMeasurement->data[$mainField];
            }

            // Tipo, unidad e icono de medición, derivados de la plantilla del grupo
            $measurementType = $sensor->group?->template?->type;
            $measurementUnit = '';
            $measurementIcon = '';
            if ($measurementType) {
                $measurementUnit = Template::$defaultUnits[$measurementType] ?? '';
                $measurementIcon = Template::$typeIcons[$measurementType] ?? 'fa-solid fa-circle';
            }

            return [
                'id' => $sensor->id,
                'identifier' => $sensor->identifier,
                'name' => $sensor->name,
                'group_name' => $sensor->group->name ?? 'Sin grupo',
                'last_value' => $lastValue,
                'main_field_name' => $mainField,
                'measurement_type' => $measurementType,
                'measurement_unit' => $measurementUnit,
                'measurement_icon' => $measurementIcon,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Payload descargado correctamente.',
            'count' => $sensors->count(),
            'data' => $sensors,
        ]);
    }

    /**
     * Endpoint 2: "Invitar Inspector Móvil"
     * POST /api/mobile/v1/invite
     * Genera un Token Sanctum con alcance definido y lo envía por correo al operario.
     */
    public function inviteOperator(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'sensor_limit' => 'nullable|integer|min:0',
            'sensor_ids' => 'nullable|string',
            'group_id' => 'nullable|integer|min:0',
        ]);

        $user = $request->user();
        $sensorLimit = (int) $request->input('sensor_limit', 0);
        $sensorIds = $request->input('sensor_ids', '');
        $groupId = (int) $request->input('group_id', 0);

        // Generar Token Sanctum con ability de lectura móvil + límite de sensores
        // y los IDs específicos de los sensores seleccionados + Grupo Asignado
        $abilities = ['mobile:read'];
        if ($sensorLimit > 0) {
            $abilities[] = 'sensor-limit:' . $sensorLimit;
        }
        if ($sensorIds !== '') {
            $abilities[] = 'sensor-ids:' . $sensorIds;
        }
        if ($groupId > 0) {
            $abilities[] = 'group-id:' . $groupId;
        }
        $tokenResult = $user->createToken('mobile-inspector-token', $abilities);
        $plainToken = $tokenResult->plainTextToken;

        // Construir el Deep Link con scope embebido
        $deepLink = 'medflowapp://auth/sync?' . http_build_query([
            'token' => $plainToken,
            'workspace' => $user->id,
            'limit' => $sensorLimit,
            'group_id' => $groupId,
        ]);

        $groupName = '';
        if ($groupId > 0) {
            $group = \App\Models\SensorGroup::find($groupId);
            $groupName = $group ? $group->name : '';
        }

        // Despachar el correo corporativo
        Mail::to($request->input('email'))
            ->send(new MobileAccessInvite($deepLink, $user->name, $sensorLimit, $groupName));

        Log::info("Token móvil generado para inspector: {$request->input('email')} | limit={$sensorLimit} | GroupID={$groupId}");

        return response()->json([
            'success' => true,
            'message' => "Enlace de acceso enviado a {$request->input('email')} correctamente.",
        ]);
    }

    /**
     * Endpoint 3: "Sincronizador Cíclico" (Bulk Sync con Idempotencia)
     * POST /api/mobile/v1/sync
     * Recibe una lista de lecturas tomadas offline por el móvil y las persiste.
     */
    public function syncMeasurements(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'measurements' => 'required|array',
            'measurements.*.sensor_id' => 'required|integer|exists:sensors,id',
            'measurements.*.value' => 'required|numeric',
            'measurements.*.mobile_uuid' => 'required|string', // UUID Crítico para tuneles offline
        ]);

        $incomingData = $request->input('measurements');
        $successfulSaves = 0;
        $failedSaves = 0;

        DB::beginTransaction();
        try {
            foreach ($incomingData as $item) {
                // 1. Comprobar Idempotencia Crítica. ¿El Operario perdió la red y se re-subió este registro JSON?
                $isDuplicate = Measurement::where('sensor_id', $item['sensor_id'])
                    ->where('data->mobile_uuid', $item['mobile_uuid'])
                    ->exists();

                if ($isDuplicate) {
                    // Silenciosamente ignoramos la validación en la DB, cuenta como success para vaciar app.
                    Log::info('Zero-Migration Idempotency Actuando: Medición Duplicada Ignorada UUID: ' . $item['mobile_uuid']);
                    $successfulSaves++;
                    continue;
                }

                $sensor = Sensor::with('group')->find($item['sensor_id']);

                if (!$sensor || !$sensor->group || $sensor->group->user_id !== $user->id) {
                    $failedSaves++;
                    continue;
                }

                $mainField = 'valor';
                if ($sensor->group && $sensor->group->template && isset($sensor->group->template->schema['campos'])) {
                    foreach ($sensor->group->template->schema['campos'] as $campo) {
                        if ($campo['tipo'] === 'numero' && ($campo['requerido'] ?? false)) {
                            $mainField = $campo['nombre'];
                            break;
                        }
                    }
                }

                $payloadData = [$mainField => $item['value'], 'mobile_uuid' => $item['mobile_uuid']];

                // Procesamiento Asignado de Evidencia Fotográfica (Conviniendo tabulación estándar de la API V1)
                if (!empty($item['photo_base64'])) {
                    $imageData = base64_decode($item['photo_base64']);
                    $filename = time() . '_' . \Illuminate\Support\Str::slug($sensor->name) . '_' . uniqid() . '.jpg';

                    $uploadPath = public_path('measurements/fotos');
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }

                    file_put_contents($uploadPath . '/' . $filename, $imageData);
                    $payloadData['foto'] = '/measurements/fotos/' . $filename;
                } else {
                    $payloadData['foto'] = 'Sin Foto';
                }

                Measurement::create([
                    'sensor_id' => $sensor->id,
                    'measured_at' => Carbon::now(),
                    'proxima_medicion' => Carbon::now()->addDays($sensor->group->periodo_medicion ?? 30),
                    'periodo_medicion' => $sensor->group->periodo_medicion ?? 30,
                    'data' => $payloadData,
                    'created_by' => $user->id,
                ]);

                $successfulSaves++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Sincronización procesada. ({$successfulSaves} confirmadas, {$failedSaves} bloqueadas).",
                'totals' => ['success' => $successfulSaves, 'failed' => $failedSaves],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fallo al Sincronizar App Offline: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error general durante la transacción de sincronización.',
            ], 500);
        }
    }
}
