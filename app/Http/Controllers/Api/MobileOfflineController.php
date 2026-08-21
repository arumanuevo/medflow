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
     * Endpoint 1: "Descarga del Payload de Rutas" (Maneja el Re-Despacho Automático)
     * GET /api/mobile/v1/sensors
     * Devuelve la lista de sensores al celular filtrando mágicamente los que YA fueron medidos.
     */
    public function getSensors(Request $request)
    {
        $user = $request->user();

        // Extraer Grupo Asignado desde el Token (Binding Geográfico)
        $tokenGroupId = 0;
        $accessToken = $user?->currentAccessToken();
        if ($accessToken && method_exists($accessToken, 'getAttribute')) {
            $abilities = $accessToken->getAttribute('abilities');
            if (is_array($abilities)) {
                foreach ($abilities as $ability) {
                    if (is_string($ability) && str_starts_with($ability, 'group-id:')) {
                        $tokenGroupId = (int) substr($ability, strlen('group-id:'));
                    }
                }
            }
        }

        $queryRequestGroupId = (int) $request->query('group_id', 0);
        $groupId = $tokenGroupId > 0 ? $tokenGroupId : $queryRequestGroupId;

        $query = Sensor::with(['group', 'lastMeasurement'])
            ->whereHas('group', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        // 1. Aplicar Zona Geográfica / Ruta Asignada
        if ($groupId > 0) {
            $query->where('group_id', $groupId);
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

            $measurementType = $sensor->group?->template?->type;
            $measurementUnit = $measurementType ? (Template::$defaultUnits[$measurementType] ?? '') : '';
            $measurementIcon = $measurementType ? (Template::$typeIcons[$measurementType] ?? 'fa-solid fa-circle') : '';

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
            'message' => 'Payload de ruta descargado correctamente.',
            'count' => $sensors->count(),
            'data' => $sensors,
        ]);
    }

    /**
     * Endpoint 2: "Invitar Inspector Móvil"
     * POST /api/mobile/v1/invite
     * Asigna un Grupo/Ruta al Inspector Móvil y envía el Deep Link corporativo.
     */
    public function inviteOperator(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'group_id' => 'nullable|integer|min:0',
        ]);

        $user = $request->user();
        $groupId = (int) $request->input('group_id', 0);

        // Binding del Token a un Grupo (Ruta de Inspección)
        $abilities = ['mobile:read'];
        if ($groupId > 0) {
            $abilities[] = 'group-id:' . $groupId;
        }

        $tokenResult = $user->createToken('mobile-inspector-token', $abilities);
        $plainToken = $tokenResult->plainTextToken;

        // Construir el Deep Link con los parámetros
        $deepLink = 'medflowapp://auth/sync?' . http_build_query([
            'token' => $plainToken,
            'workspace' => $user->id,
            'group_id' => $groupId,
        ]);

        $groupName = '';
        if ($groupId > 0) {
            $group = \App\Models\SensorGroup::find($groupId);
            $groupName = $group ? $group->name : '';
        }

        // Despachar el correo corporativo (pasando Name en vez de Limit)
        Mail::to($request->input('email'))
            ->send(new MobileAccessInvite($deepLink, $user->name, $groupName));

        Log::info("Ruta de inspección enviada. Destino: {$request->input('email')} | GroupID={$groupId}");

        return response()->json([
            'success' => true,
            'message' => "Ruta enviada a {$request->input('email')} correctamente.",
        ]);
    }

    /**
     * Endpoint 3: "Sincronizador Cíclico" (Bulk Sync con Idempotencia)
     * POST /api/mobile/v1/sync
     * Recibe los datos y usa mobile_uuid para descartar duplicados sin afectar consumo.
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

                Measurement::create([
                    'sensor_id' => $sensor->id,
                    'measured_at' => Carbon::now(),
                    'proxima_medicion' => Carbon::now()->addDays($sensor->group->periodo_medicion ?? 30),
                    'periodo_medicion' => $sensor->group->periodo_medicion ?? 30,
                    // Grabar el UUID dentro de data JSON para evitar alterar la estructura SQL
                    'data' => [$mainField => $item['value'], 'mobile_uuid' => $item['mobile_uuid']],
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
            Log::error('Fallo Crítico al Sincronizar App Offline: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error en la persistencia local de la Nube.',
            ], 500);
        }
    }
}
