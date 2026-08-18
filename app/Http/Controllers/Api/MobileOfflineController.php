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
     * El parámetro opcional ?limit=N restringe cuántos sensores se entregan (para pruebas).
     */
    public function getSensors(Request $request)
    {
        $user = $request->user();

        // Límite de sensores: se respeta el más restrictivo entre el definido
        // en el token (ability 'sensor-limit:N', fijado por el admin) y el query
        // param ?limit=N (override temporal). 0 = sin límite.
        $tokenLimit = 0;
        $accessToken = $user?->currentAccessToken();
        if ($accessToken && method_exists($accessToken, 'getAttribute')) {
            $abilities = $accessToken->getAttribute('abilities');
            if (is_array($abilities)) {
                foreach ($abilities as $ability) {
                    if (is_string($ability) && str_starts_with($ability, 'sensor-limit:')) {
                        $tokenLimit = (int) substr($ability, strlen('sensor-limit:'));
                        break;
                    }
                }
            }
        }
        $queryLimit = (int) $request->query('limit', 0);
        // Tomar el más restrictivo (>0). Si ambos son 0, sin límite.
        $limit = ($tokenLimit > 0 && $queryLimit > 0)
            ? min($tokenLimit, $queryLimit)
            : max($tokenLimit, $queryLimit);

        $query = Sensor::with(['group', 'lastMeasurement'])
            ->whereHas('group', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        if ($limit > 0) {
            $query->take($limit);
        }

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

            // Tipo y unidad de medición, derivados de la plantilla del grupo
            $measurementType = $sensor->group?->template?->type;
            $measurementUnit = '';
            if ($measurementType) {
                $measurementUnit = Template::$defaultUnits[$measurementType] ?? '';
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
        ]);

        $user = $request->user();
        $sensorLimit = (int) $request->input('sensor_limit', 0);

        // Generar Token Sanctum con ability de lectura móvil + límite de sensores
        // embebido como 'sensor-limit:N' para que el endpoint lo respete sin depender
        // del cliente.
        $abilities = ['mobile:read'];
        if ($sensorLimit > 0) {
            $abilities[] = 'sensor-limit:' . $sensorLimit;
        }
        $tokenResult = $user->createToken('mobile-inspector-token', $abilities);
        $plainToken = $tokenResult->plainTextToken;

        // Construir el Deep Link con scope embebido
        $deepLink = 'medflowapp://auth/sync?' . http_build_query([
            'token' => $plainToken,
            'workspace' => $user->id,
            'limit' => $sensorLimit,
        ]);

        // Despachar el correo corporativo
        Mail::to($request->input('email'))
            ->send(new MobileAccessInvite($deepLink, $user->name, $sensorLimit));

        Log::info("Token móvil generado para inspector: {$request->input('email')} | limit={$sensorLimit}");

        return response()->json([
            'success' => true,
            'message' => "Enlace de acceso enviado a {$request->input('email')} correctamente.",
        ]);
    }

    /**
     * Endpoint 3: "Sincronizador de Mediciones" (El Bulk Sync)
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
        ]);

        $incomingData = $request->input('measurements');
        $successfulSaves = 0;
        $failedSaves = 0;

        DB::beginTransaction();
        try {
            foreach ($incomingData as $item) {
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
                    'data' => [$mainField => $item['value']],
                    'created_by' => $user->id,
                ]);

                $successfulSaves++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Sincronización completada. ({$successfulSaves} procesadas, {$failedSaves} bloqueadas).",
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
