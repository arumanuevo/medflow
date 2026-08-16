<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sensor;
use App\Models\Measurement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MobileOfflineController extends Controller
{
    /**
     * Endpoint 1: "Vomitador de Sensores" (Descarga del Payload)
     * GET /api/mobile/v1/sensors
     * Devuelve toda la lista de sensores a la API Móvil para Guardarlos Localmente en SQLite
     */
    public function getSensors(Request $request)
    {
        $user = $request->user();

        // MVP: Para simplificar, devolvemos los sensores donde el usuario es dueño directo
        // En fases futuras se puede vincular con "Colaboraciones" o leer el "Workspace Activo".
        $sensors = Sensor::with(['group', 'lastMeasurement']) // Eager load relationships needed for offline UI
            ->whereHas('group', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->get()
            ->map(function ($sensor) {
                // Determinar el valor de la última medición (para reglas lógicas locales)
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

                return [
                    'id' => $sensor->id,
                    'identifier' => $sensor->identifier,
                    'name' => $sensor->name,
                    'group_name' => $sensor->group->name ?? 'Sin grupo',
                    'last_value' => $lastValue,
                    'main_field_name' => $mainField
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Payload descargado correctamente.',
            'count' => $sensors->count(),
            'data' => $sensors
        ]);
    }

    /**
     * Endpoint 2: "Sincronizador de Mediciones" (El Bulk Sync)
     * POST /api/mobile/v1/sync
     * Recibe una lista de lecturas tomadas offline por el móvil para guardarlas.
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

                // Mínima verificación de permisos MVP
                if (!$sensor || !$sensor->group || $sensor->group->user_id !== $user->id) {
                    $failedSaves++;
                    continue;
                }

                // Extraemos el main_field de lectura para guardarlo igual que la web
                $mainField = 'valor';
                if ($sensor->group && $sensor->group->template && isset($sensor->group->template->schema['campos'])) {
                    foreach ($sensor->group->template->schema['campos'] as $campo) {
                        if ($campo['tipo'] === 'numero' && ($campo['requerido'] ?? false)) {
                            $mainField = $campo['nombre'];
                            break;
                        }
                    }
                }

                // Generamos la Medición
                Measurement::create([
                    'sensor_id' => $sensor->id,
                    'measured_at' => Carbon::now(), // O el timestamp local que envíe el app
                    'proxima_medicion' => Carbon::now()->addDays($sensor->group->periodo_medicion ?? 30),
                    'periodo_medicion' => $sensor->group->periodo_medicion ?? 30,
                    'data' => [
                        $mainField => $item['value']
                    ],
                    'created_by' => $user->id
                ]);

                $successfulSaves++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Sincronización completada. ({$successfulSaves} procesadas, {$failedSaves} bloqueadas).",
                'totals' => [
                    'success' => $successfulSaves,
                    'failed' => $failedSaves
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Fallo al Sincronizar App Offline: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error general durante la transacción de sincronización.'
            ], 500);
        }
    }
}
