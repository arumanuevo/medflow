<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\SensorGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

    /**
     * Obtener listado de grupos de sensores del workspace activo
     */
    public function getGroupsList(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuario no autenticado'], 401);
            }

            $activeWorkspace = session('active_workspace', $user->id);

            $groups = SensorGroup::where(function($q) use ($activeWorkspace) {
                $q->where('user_id', $activeWorkspace)
                  ->orWhereHas('sharedAccess', function($sq) use ($activeWorkspace) {
                      $sq->where('shared_with', $activeWorkspace);
                  });
            })
            ->withCount('sensors')
            ->orderBy('name')
            ->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'data' => $groups
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en getGroupsList: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener grupos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Agregar un nuevo campo en blanco a un grupo de sensores sin alterar datos existentes
     */
    public function addFieldToGroup(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuario no autenticado'], 401);
            }

            $activeWorkspace = session('active_workspace', $user->id);
            $groupId = $request->input('group_id');
            $rawFieldName = trim($request->input('field_name', ''));
            $defaultValue = $request->input('default_value', '');

            if (empty($rawFieldName)) {
                return response()->json(['success' => false, 'message' => 'El nombre del campo es obligatorio'], 422);
            }

            // Normalización limpia de la clave técnica (ej: "Teléfono" -> "telefono", "WhatsApp" -> "whatsapp")
            $fieldKey = Str::slug(Str::ascii($rawFieldName), '_');
            if (empty($fieldKey)) {
                $fieldKey = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $rawFieldName));
            }

            // Palabras reservadas del sistema
            $reserved = ['id', 'name', 'identifier', 'description', 'group_id', 'coordinates', 'metadata', 'schedule', 'prorratear_comunidad'];
            if (in_array($fieldKey, $reserved)) {
                return response()->json(['success' => false, 'message' => "El nombre '{$rawFieldName}' es una palabra reservada del sistema."], 422);
            }

            $sensorQuery = Sensor::whereHas('group', function($q) use ($activeWorkspace) {
                $q->where('user_id', $activeWorkspace)
                  ->orWhereHas('sharedAccess', function($sq) use ($activeWorkspace) {
                      $sq->where('shared_with', $activeWorkspace);
                  });
            });

            if (!empty($groupId) && $groupId !== 'all') {
                $sensorQuery->where('group_id', $groupId);
            }

            $sensors = $sensorQuery->get();

            if ($sensors->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron sensores en el grupo seleccionado.'
                ], 404);
            }

            $updatedCount = 0;
            $alreadyExistedCount = 0;

            foreach ($sensors as $sensor) {
                $meta = is_array($sensor->metadata) ? $sensor->metadata : [];

                // Si el sensor ya tiene el campo, preservamos su valor intacto (cero riesgo de sobrescribir datos)
                if (array_key_exists($fieldKey, $meta)) {
                    $alreadyExistedCount++;
                    continue;
                }

                // Asignamos el campo nuevo en blanco
                $meta[$fieldKey] = $defaultValue;
                $sensor->metadata = $meta;
                $sensor->save();
                $updatedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Campo '{$rawFieldName}' agregado con éxito a {$updatedCount} sensor(es)" . ($alreadyExistedCount > 0 ? " ({$alreadyExistedCount} ya lo tenían registrado)." : "."),
                'data' => [
                    'field_key' => $fieldKey,
                    'field_name' => $rawFieldName,
                    'updated_count' => $updatedCount,
                    'already_existed_count' => $alreadyExistedCount
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en addFieldToGroup: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al agregar el campo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar o registrar rápidamente el teléfono/contacto de un sensor específico
     */
    public function updateSensorPhone(Request $request, Sensor $sensor)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuario no autenticado'], 401);
            }

            $phone = trim($request->input('phone', ''));
            $fieldKey = $request->input('field_key', 'telefono');

            $meta = is_array($sensor->metadata) ? $sensor->metadata : [];
            $meta[$fieldKey] = $phone;
            $sensor->metadata = $meta;
            $sensor->save();

            return response()->json([
                'success' => true,
                'message' => 'Teléfono guardado correctamente en la ficha del sensor.',
                'phone' => $phone
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en updateSensorPhone: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al guardar teléfono: ' . $e->getMessage()], 500);
        }
    }
}