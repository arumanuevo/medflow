<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SensorExtraFieldController extends Controller
{
    public function index()
    {
        return view('sensors.extra-fields');
    }

    public function getFields(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $sensors = Sensor::whereHas('group', function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('sharedAccess', function($q) use ($user) {
                          $q->where('shared_with', $user->id);
                      });
            })->get();

            if ($sensors->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $fields = [];
            foreach ($sensors as $sensor) {
                if (!empty($sensor->metadata) && is_array($sensor->metadata)) {
                    foreach ($sensor->metadata as $key => $value) {
                        if (!isset($fields[$key])) {
                            $fields[$key] = [
                                'name' => $key,
                                'count' => 0,
                                'sensors' => []
                            ];
                        }
                        $fields[$key]['count']++;
                        $fields[$key]['sensors'][] = [
                            'id' => $sensor->id,
                            'name' => $sensor->name,
                            'identifier' => $sensor->identifier
                        ];
                    }
                }
            }

            ksort($fields);

            return response()->json([
                'success' => true,
                'data' => array_values($fields)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en getFields: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar campos extras: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteField(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $fieldName = $request->input('field_name');

            if (empty($fieldName)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El nombre del campo es obligatorio'
                ], 422);
            }

            $sensors = Sensor::whereHas('group', function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('sharedAccess', function($q) use ($user) {
                          $q->where('shared_with', $user->id);
                      });
            })->get();

            $affectedCount = 0;

            foreach ($sensors as $sensor) {
                if (!empty($sensor->metadata) && is_array($sensor->metadata)) {
                    if (array_key_exists($fieldName, $sensor->metadata)) {
                        // ✅ Crear una copia del array
                        $newMetadata = $sensor->metadata;
                        unset($newMetadata[$fieldName]);
                        $sensor->metadata = empty($newMetadata) ? null : $newMetadata;
                        $sensor->save();
                        $affectedCount++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Campo '{$fieldName}' eliminado de {$affectedCount} sensores",
                'data' => [
                    'affected_count' => $affectedCount,
                    'field_name' => $fieldName
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en deleteField: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el campo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function renameField(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $oldName = $request->input('old_name');
            $newName = $request->input('new_name');

            if (empty($oldName) || empty($newName)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El nombre antiguo y nuevo son obligatorios'
                ], 422);
            }

            if ($oldName === $newName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los nombres son iguales'
                ], 422);
            }

            $sensors = Sensor::whereHas('group', function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('sharedAccess', function($q) use ($user) {
                          $q->where('shared_with', $user->id);
                      });
            })->get();

            $affectedCount = 0;

            foreach ($sensors as $sensor) {
                if (!empty($sensor->metadata) && is_array($sensor->metadata)) {
                    if (array_key_exists($oldName, $sensor->metadata)) {
                        // ✅ Crear una copia del array
                        $newMetadata = $sensor->metadata;
                        $newMetadata[$newName] = $newMetadata[$oldName];
                        unset($newMetadata[$oldName]);
                        $sensor->metadata = $newMetadata;
                        $sensor->save();
                        $affectedCount++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Campo '{$oldName}' renombrado a '{$newName}' en {$affectedCount} sensores",
                'data' => [
                    'affected_count' => $affectedCount,
                    'old_name' => $oldName,
                    'new_name' => $newName
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en renameField: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al renombrar el campo: ' . $e->getMessage()
            ], 500);
        }
    }
}