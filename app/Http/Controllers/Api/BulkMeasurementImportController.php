<?php
// app/Http/Controllers/Api/BulkMeasurementImportController.php

namespace App\Http\Controllers\Api;

use App\Models\Sensor;
use App\Models\Measurement;
use App\Models\SensorGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Shuchkin\SimpleXLSX;
use Carbon\Carbon;

class BulkMeasurementImportController extends Controller
{
    /**
     * Obtener grupos del usuario para el selector
     */
    public function getGroups(Request $request)
    {
        try {
            $user = $request->user();

            $groups = SensorGroup::where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('sharedAccess', function($q2) use ($user) {
                      $q2->where('shared_with', $user->id)
                         ->whereIn('role', ['inspector', 'admin']);
                  });
            })
            ->withCount('sensors')
            ->orderBy('name')
            ->get()
            ->map(function($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'sensor_count' => $group->sensors_count,
                    'has_sensors' => $group->sensors_count > 0
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $groups
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener grupos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los grupos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener sensores de un grupo específico
     */
    public function getSensorsByGroup(Request $request, $groupId)
    {
        try {
            $user = $request->user();

            $group = SensorGroup::findOrFail($groupId);
            
            // Verificar permisos
            $canAccess = $user->hasRole('admin') ||
                        $group->user_id === $user->id ||
                        $group->sharedAccess()
                            ->where('shared_with', $user->id)
                            ->whereIn('role', ['inspector', 'admin'])
                            ->exists();

            if (!$canAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para acceder a este grupo'
                ], 403);
            }

            $sensors = Sensor::where('group_id', $groupId)
                ->orderBy('name')
                ->get(['id', 'name', 'identifier'])
                ->map(function($sensor) {
                    return [
                        'id' => $sensor->id,
                        'name' => $sensor->name,
                        'identifier' => $sensor->identifier
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $sensors
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener sensores del grupo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los sensores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analizar el archivo Excel/CSV y devolver los campos detectados
     */
    public function analyzeFile(Request $request)
    {
        @ini_set('max_execution_time', 300);
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        try {
            $user = $request->user();

            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se ha subido ningún archivo'
                ], 400);
            }

            $file = $request->file('file');
            $fileExtension = strtolower($file->getClientOriginalExtension());

            $validExtensions = ['xlsx', 'csv'];
            if (!in_array($fileExtension, $validExtensions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Extensión de archivo no válida. Solo se permiten archivos .xlsx o .csv'
                ], 400);
            }

            $tempPath = $file->getRealPath();
            $totalRows = 0;

            if ($fileExtension === 'csv') {
                $headers = $this->readCsvHeaders($tempPath);
                $sampleData = $this->getCsvSampleData($tempPath, 5);

                // Contar total de filas de forma rápida sin cargar todo en memoria
                $handle = @fopen($tempPath, 'r');
                if ($handle) {
                    while (fgets($handle) !== false) {
                        $totalRows++;
                    }
                    fclose($handle);
                    $totalRows = max(0, $totalRows - 1); // Descontar fila de encabezados
                }
            } else {
                if (!class_exists(SimpleXLSX::class)) {
                    throw new \Exception('La librería SimpleXLSX no está disponible.');
                }
                $xlsx = SimpleXLSX::parse($tempPath);
                if ($xlsx === false) {
                    throw new \Exception('No se pudo leer el archivo Excel.');
                }
                $allRows = $xlsx->rows();
                $headers = !empty($allRows) ? array_map('trim', $allRows[0]) : [];
                $totalRows = max(0, count($allRows) - 1);
                $sampleData = array_slice($allRows, 1, 5);
            }

            $headers = array_map('trim', $headers);

            return response()->json([
                'success' => true,
                'message' => 'Archivo analizado correctamente',
                'data' => [
                    'headers' => $headers,
                    'sample_data' => $sampleData,
                    'total_rows' => $totalRows
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al analizar el archivo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al analizar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

  /**
 * Importar mediciones masivamente (múltiples sensores)
 */
public function bulkImport(Request $request)
{
    @ini_set('max_execution_time', 300);
    @set_time_limit(300);
    @ini_set('memory_limit', '512M');

    try {
        $user = $request->user();

        Log::info('Iniciando importación masiva de mediciones', [
            'user_id' => $user->id,
            'has_file' => $request->hasFile('file'),
            'group_id' => $request->input('group_id'),
            'field_mapping' => $request->input('field_mapping'),
            'overwrite_duplicates' => $request->input('overwrite_duplicates'),
            'identification_method' => $request->input('identification_method', 'name')
        ]);

        if (!$request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'No se ha subido ningún archivo'
            ], 400);
        }

        // ✅ Validación más permisiva para archivos CSV
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();
        
        Log::info('📄 Información del archivo', [
            'original_name' => $originalName,
            'extension' => $extension,
            'mime_type' => $mimeType,
            'size' => $file->getSize()
        ]);

        // ✅ Validar extensión y MIME type manualmente
        $validExtensions = ['xlsx', 'csv'];
        $validMimes = [
            'text/csv',
            'text/plain',
            'application/vnd.ms-excel',
            'application/csv',
            'application/x-csv',
            'text/x-csv',
            'text/comma-separated-values',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        if (!in_array($extension, $validExtensions)) {
            return response()->json([
                'success' => false,
                'message' => "Extensión de archivo no válida: '{$extension}'. Solo se permiten archivos .xlsx o .csv"
            ], 422);
        }

        // ✅ Si es CSV, verificar que el MIME type sea válido (o permitir pasar)
        if ($extension === 'csv' && !in_array($mimeType, $validMimes)) {
            Log::warning('⚠️ MIME type inesperado para CSV: ' . $mimeType . ', pero permitiendo el archivo');
            // No bloqueamos, solo advertimos
        }

        // ✅ Validación personalizada
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:sensor_groups,id',
            'field_mapping' => 'required'
        ]);

        // ✅ Validar el archivo manualmente
        if ($file->getSize() > 10240 * 1024) { // 10MB
            return response()->json([
                'success' => false,
                'message' => 'El archivo es demasiado grande. Máximo 10MB.'
            ], 422);
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $fieldMapping = $request->input('field_mapping');
        if (is_string($fieldMapping)) {
            $fieldMapping = json_decode($fieldMapping, true);
        }

        Log::info('Field Mapping decodificado', ['fieldMapping' => $fieldMapping]);

        if (!is_array($fieldMapping) || 
            !isset($fieldMapping['sensor']) || 
            !isset($fieldMapping['valor']) || 
            !isset($fieldMapping['fecha'])) {
            return response()->json([
                'success' => false,
                'message' => 'El mapeo de campos es inválido. Debe incluir "sensor", "valor" y "fecha".'
            ], 422);
        }

        // ✅ Obtener el método de identificación (por defecto: 'name')
        $identificationMethod = $request->input('identification_method', 'name');

        // Verificar permisos del grupo
        $group = SensorGroup::findOrFail($request->group_id);
        $canAccessGroup = $user->hasRole('admin') ||
                        $group->user_id === $user->id ||
                        $group->sharedAccess()
                            ->where('shared_with', $user->id)
                            ->whereIn('role', ['inspector', 'admin'])
                            ->exists();

        if (!$canAccessGroup) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para importar mediciones en este grupo'
            ], 403);
        }

        // ✅ Obtener todos los sensores del grupo
        $sensorsInGroup = Sensor::where('group_id', $request->group_id)->get();

        if ($sensorsInGroup->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'El grupo seleccionado no tiene sensores. Crea sensores primero.'
            ], 400);
        }

        // ✅ Crear mapas para búsqueda rápida por diferentes campos
        $sensorMapByName = [];
        $sensorMapByIdentifier = [];
        $sensorMapById = [];
        $sensorIds = [];
        
        foreach ($sensorsInGroup as $s) {
            $sensorIds[] = $s->id;
            $sensorMapByName[strtolower(trim($s->name))] = $s;
            if ($s->identifier) {
                $sensorMapByIdentifier[strtolower(trim($s->identifier))] = $s;
            }
            $sensorMapById[$s->id] = $s;
        }

        // Mapeo por metadata si aplica
        $sensorMapByMetadata = [];
        if ($identificationMethod && strpos($identificationMethod, 'metadata_') === 0) {
            $metaKey = str_replace('metadata_', '', $identificationMethod);
            $cleanMetaKey = strtolower(trim($metaKey));
            foreach ($sensorsInGroup as $sensor) {
                if ($sensor->metadata && is_array($sensor->metadata)) {
                    foreach ($sensor->metadata as $k => $v) {
                        if (strtolower(trim($k)) === $cleanMetaKey && $v !== null && $v !== '') {
                            $sensorMapByMetadata[strtolower(trim($v))] = $sensor;
                        }
                    }
                }
            }
        }

        // ✅ Procesar el archivo - leer según extensión
        $tempPath = $file->getRealPath();

        if ($extension === 'csv') {
            $allData = $this->readCsvAllData($tempPath);
        } else {
            $allData = $this->readExcelAllData($tempPath);
        }

        if (empty($allData) || count($allData) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo no contiene datos o solo tiene encabezados'
            ], 400);
        }

        // Tipo de plantilla y si es acumulativa
        $templateType = $group->template->type ?? 'personalizado';
        $isCumulative = in_array(strtolower($templateType), ['agua', 'gas', 'electricidad', 'caudal']);

        // ✅ Pre-cargar en memoria todo el historial de mediciones del grupo en 1 sola query
        $sensorHistories = [];
        foreach ($sensorIds as $sId) {
            $sensorHistories[$sId] = [];
        }

        $allExisting = Measurement::whereIn('sensor_id', $sensorIds)
            ->select('id', 'sensor_id', 'measured_at', 'data')
            ->orderBy('measured_at', 'asc')
            ->get();

        foreach ($allExisting as $m) {
            $val = isset($m->data['valor']) ? (float) $m->data['valor'] : 0.0;
            $dateObj = $m->measured_at instanceof Carbon ? $m->measured_at : Carbon::parse($m->measured_at);
            $sensorHistories[$m->sensor_id][] = [
                'id' => $m->id,
                'date_str' => $dateObj->format('Y-m-d'),
                'timestamp' => $dateObj->timestamp,
                'valor' => $val
            ];
        }

        // Procesar los datos de forma optimizada
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        $measurementsToCreate = [];
        $measurementsToDelete = [];
        $overwrittenCount = 0;
        $overwriteDuplicates = filter_var($request->input('overwrite_duplicates', true), FILTER_VALIDATE_BOOLEAN);
        $allowNegativeConsumption = filter_var($request->input('allow_negative_consumption', true), FILTER_VALIDATE_BOOLEAN);
        $negativeConsumptionCount = 0;
        $now = Carbon::now();

        foreach ($allData as $index => $row) {
            if ($index === 0) continue;

            if (empty($row) || count(array_filter($row)) === 0) {
                continue;
            }

            try {
                // Obtener valores mapeados
                $sensorIdentifier = trim((string) $this->getMappedValue($row, $fieldMapping, 'sensor'));
                $valor = $this->getMappedValue($row, $fieldMapping, 'valor');
                $fechaStr = $this->getMappedValue($row, $fieldMapping, 'fecha');
                $foto = $this->normalizePhotoName($this->getMappedValue($row, $fieldMapping, 'foto'));
                $observaciones = $this->getMappedValue($row, $fieldMapping, 'observaciones') ?? '';

                $errorsForRow = [];

                // 1. Validar sensor
                if ($sensorIdentifier === '') {
                    $errorsForRow[] = 'El campo "Sensor" es obligatorio';
                } else {
                    $sensorKey = strtolower($sensorIdentifier);
                    $foundSensor = null;

                    switch ($identificationMethod) {
                        case 'id':
                            if (is_numeric($sensorKey)) {
                                $foundSensor = $sensorMapById[(int) $sensorKey] ?? null;
                            }
                            break;
                        case 'identifier':
                            $foundSensor = $sensorMapByIdentifier[$sensorKey] ?? null;
                            break;
                        case 'name':
                            $foundSensor = $sensorMapByName[$sensorKey] ?? null;
                            break;
                        default:
                            if (!empty($sensorMapByMetadata)) {
                                $foundSensor = $sensorMapByMetadata[$sensorKey] ?? null;
                            }
                            break;
                    }

                    // Fallbacks si no coincide por el método exacto
                    if (!$foundSensor) {
                        $foundSensor = $sensorMapByIdentifier[$sensorKey] ?? ($sensorMapByName[$sensorKey] ?? (is_numeric($sensorKey) ? ($sensorMapById[(int) $sensorKey] ?? null) : null));
                    }

                    if (!$foundSensor) {
                        $errorsForRow[] = "El sensor '{$sensorIdentifier}' no existe en el grupo.";
                    } else {
                        $sensorId = $foundSensor->id;
                    }
                }

                // 2. Validar valor numérico
                if ($valor === null || $valor === '') {
                    $errorsForRow[] = 'El campo "Valor" es obligatorio';
                } elseif (!is_numeric($valor)) {
                    $errorsForRow[] = "El valor '{$valor}' no es un número válido";
                }

                // 3. Validar y parsear fecha
                $fecha = null;
                if ($fechaStr === null || $fechaStr === '') {
                    $errorsForRow[] = 'El campo "Fecha" es obligatorio';
                } else {
                    try {
                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaStr)) {
                            $fecha = Carbon::createFromFormat('Y-m-d', $fechaStr);
                        } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fechaStr)) {
                            $fecha = Carbon::createFromFormat('d/m/Y', $fechaStr);
                        } else {
                            $fecha = Carbon::parse($fechaStr);
                        }
                        $fecha->setTime(0, 0, 0);
                    } catch (\Exception $e) {
                        $errorsForRow[] = "Fecha inválida: '{$fechaStr}'. Usa formato YYYY-MM-DD o DD/MM/YYYY";
                    }
                }

                // 4. Lógica de sensor: duplicados y coherencia temporal
                if (empty($errorsForRow) && isset($sensorId) && $fecha !== null) {
                    $fechaYmd = $fecha->format('Y-m-d');
                    $timestamp = $fecha->timestamp;
                    $valorFloat = (float) $valor;

                    // Buscar duplicado exacto en el historial en memoria de este sensor
                    $duplicateIndex = null;
                    $duplicateRecord = null;
                    if (isset($sensorHistories[$sensorId])) {
                        foreach ($sensorHistories[$sensorId] as $idx => $histItem) {
                            if ($histItem['date_str'] === $fechaYmd && abs($histItem['valor'] - $valorFloat) < 0.0001) {
                                $duplicateIndex = $idx;
                                $duplicateRecord = $histItem;
                                break;
                            }
                        }
                    }

                    if ($duplicateRecord !== null) {
                        if ($overwriteDuplicates) {
                            if (!empty($duplicateRecord['id'])) {
                                $measurementsToDelete[] = $duplicateRecord['id'];
                            }
                            unset($sensorHistories[$sensorId][$duplicateIndex]);
                            $sensorHistories[$sensorId] = array_values($sensorHistories[$sensorId]);
                            $overwrittenCount++;
                        } else {
                            $errorsForRow[] = "Ya existe una medición para la fecha {$fechaYmd} con el valor {$valorFloat}";
                        }
                    }

                    // Validación de secuencia para medidores acumulativos
                    if (empty($errorsForRow) && $isCumulative && !empty($sensorHistories[$sensorId])) {
                        $prevItem = null;
                        $nextItem = null;
                        foreach ($sensorHistories[$sensorId] as $histItem) {
                            if ($histItem['timestamp'] <= $timestamp) {
                                if ($prevItem === null || $histItem['timestamp'] > $prevItem['timestamp']) {
                                    $prevItem = $histItem;
                                }
                            }
                            if ($histItem['timestamp'] >= $timestamp) {
                                if ($nextItem === null || $histItem['timestamp'] < $nextItem['timestamp']) {
                                    $nextItem = $histItem;
                                }
                            }
                        }

                        if ($prevItem && $valorFloat < $prevItem['valor']) {
                            if ($allowNegativeConsumption) {
                                $negativeConsumptionCount++;
                            } else {
                                $errorsForRow[] = "El valor {$valorFloat} debe ser mayor o igual a la medición anterior ({$prevItem['valor']} del {$prevItem['date_str']})";
                            }
                        }
                        if ($nextItem && $valorFloat > $nextItem['valor']) {
                            if (!$allowNegativeConsumption) {
                                $errorsForRow[] = "El valor {$valorFloat} debe ser menor o igual a la medición siguiente ({$nextItem['valor']} del {$nextItem['date_str']})";
                            }
                        }
                    }

                    if (empty($errorsForRow)) {
                        $measurementsToCreate[] = [
                            'sensor_id' => $sensorId,
                            'measured_at' => $fecha->format('Y-m-d 00:00:00'),
                            'data' => json_encode([
                                'valor' => $valorFloat,
                                'foto' => $foto,
                                'observaciones' => $observaciones,
                                'tipo' => $group->template->type ?? 'personalizado'
                            ]),
                            'created_by' => $user->id,
                            'created_at' => $now,
                            'updated_at' => $now
                        ];

                        $sensorHistories[$sensorId][] = [
                            'id' => null,
                            'date_str' => $fechaYmd,
                            'timestamp' => $timestamp,
                            'valor' => $valorFloat
                        ];

                        $successCount++;
                    }
                }

                if (!empty($errorsForRow)) {
                    $errorCount++;
                    $errors[] = [
                        'row' => $index + 1,
                        'sensor' => $sensorIdentifier ?: 'N/A',
                        'valor' => $valor ?: 'N/A',
                        'fecha' => $fechaStr ?: 'N/A',
                        'error' => implode('; ', $errorsForRow),
                        'status' => 'error'
                    ];
                }

            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = [
                    'row' => $index + 1,
                    'sensor' => $sensorIdentifier ?? 'N/A',
                    'valor' => $valor ?? 'N/A',
                    'fecha' => $fechaStr ?? 'N/A',
                    'error' => $e->getMessage(),
                    'status' => 'error'
                ];
            }
        }

        // Eliminar duplicados sobrescritos en lotes de 500
        if (!empty($measurementsToDelete)) {
            $deleteChunks = array_chunk(array_unique($measurementsToDelete), 500);
            foreach ($deleteChunks as $chunk) {
                Measurement::whereIn('id', $chunk)->delete();
            }
        }

        // Insertar nuevas mediciones en lotes de 500 dentro de una transacción
        if (!empty($measurementsToCreate)) {
            DB::beginTransaction();
            try {
                usort($measurementsToCreate, function($a, $b) {
                    return strcmp($a['measured_at'], $b['measured_at']);
                });

                $insertChunks = array_chunk($measurementsToCreate, 500);
                foreach ($insertChunks as $chunk) {
                    Measurement::insert($chunk);
                }
                DB::commit();

                Log::info('Mediciones importadas correctamente', [
                    'user_id' => $user->id,
                    'group_id' => $request->group_id,
                    'count' => count($measurementsToCreate),
                    'overwritten' => $overwrittenCount
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error al importar mediciones en lote: ' . $e->getMessage());
                throw $e;
            }
        }

        // Generar ID único para el informe
        $reportId = uniqid('import_', true);
        
        $reportData = [
            'id' => $reportId,
            'rows' => array_slice($errors, 0, 500),
            'summary' => [
                'total' => $successCount + $errorCount,
                'success' => $successCount,
                'errors' => $errorCount,
                'overwritten' => $overwrittenCount
            ],
            'group_id' => $request->group_id,
            'created_at' => now()->toDateTimeString(),
            'user_id' => $user->id
        ];
        
        $this->storeReport($reportId, $reportData);

        return response()->json([
            'success' => true,
            'message' => 'Importación de mediciones completada',
            'data' => [
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'overwritten_count' => $overwrittenCount,
                'negative_consumption_count' => $negativeConsumptionCount,
                'errors' => array_slice($errors, 0, 100),
                'total_errors' => count($errors),
                'total_processed' => $successCount + $errorCount,
                'group_id' => $group->id,
                'group_name' => $group->name,
                'report_id' => $reportId
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Error en bulkImport', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error al importar mediciones: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Validar la secuencia de mediciones (fechas y valores)
     * Permite inserciones en cualquier punto de la línea temporal
     * 
     * @param int $sensorId ID del sensor
     * @param Carbon $fecha Fecha de la nueva medición
     * @param float $valor Valor de la nueva medición
     * @param \Illuminate\Support\Collection $existingMeasurements Mediciones existentes en BD
     * @param array $newMeasurements Mediciones que ya se han procesado en este lote (en memoria)
     */
    private function validateMeasurementSequence($sensorId, Carbon $fecha, float $valor, $existingMeasurements, array $newMeasurements): array
    {
        try {
            // ✅ Combinar mediciones existentes con las nuevas (en memoria)
            $allMeasurements = collect();
            
            // Agregar mediciones existentes
            foreach ($existingMeasurements as $m) {
                $allMeasurements->push((object) [
                    'measured_at' => Carbon::parse($m->measured_at),
                    'data' => ['valor' => (float) ($m->data['valor'] ?? 0)]
                ]);
            }

            // ✅ Agregar mediciones nuevas (YA PROCESADAS en este lote)
            foreach ($newMeasurements as $newMeasurement) {
                if ($newMeasurement['sensor_id'] !== $sensorId) continue;
                
                $data = json_decode($newMeasurement['data'], true);
                $allMeasurements->push((object) [
                    'measured_at' => Carbon::parse($newMeasurement['measured_at']),
                    'data' => ['valor' => (float) ($data['valor'] ?? 0)]
                ]);
            }

            // ✅ Si no hay mediciones previas, la primera medición siempre es válida
            if ($allMeasurements->isEmpty()) {
                return ['valid' => true];
            }

            // Ordenar por fecha
            $sorted = $allMeasurements->sortBy(function($item) {
                return $item->measured_at;
            })->values();

            // Buscar posición de la nueva medición
            $position = null;
            $previous = null;
            $next = null;

            foreach ($sorted as $index => $measurement) {
                $measurementDate = $measurement->measured_at;
                
                // Comparar por fecha y valor
                if ($measurementDate->format('Y-m-d H:i:s') === $fecha->format('Y-m-d H:i:s') &&
                    isset($measurement->data['valor']) &&
                    abs((float) $measurement->data['valor'] - $valor) < 0.0001) {
                    $position = $index;
                    if ($index > 0) {
                        $previous = $sorted[$index - 1];
                    }
                    if ($index < $sorted->count() - 1) {
                        $next = $sorted[$index + 1];
                    }
                    break;
                }
            }

            // ✅ Si no se encontró la posición, insertar al final
            if ($position === null) {
                // Buscar la posición correcta (orden por fecha)
                $insertIndex = 0;
                foreach ($sorted as $index => $measurement) {
                    if ($fecha->gt($measurement->measured_at)) {
                        $insertIndex = $index + 1;
                    } else {
                        break;
                    }
                }
                
                // Obtener anterior y siguiente en la posición correcta
                if ($insertIndex > 0) {
                    $previous = $sorted[$insertIndex - 1] ?? null;
                }
                if ($insertIndex < $sorted->count()) {
                    $next = $sorted[$insertIndex] ?? null;
                }
                
                // Si no hay anterior y no hay siguiente, es la primera medición
                if (!$previous && !$next) {
                    return ['valid' => true];
                }
            }

            // ✅ Validar contra la medición anterior (si existe)
            if ($previous) {
                $previousDate = $previous->measured_at;
                $previousValue = (float) ($previous->data['valor'] ?? 0);

                // La fecha debe ser posterior o igual a la anterior
                if ($fecha->lt($previousDate)) {
                    return [
                        'valid' => false,
                        'message' => "La fecha {$fecha->format('d/m/Y')} debe ser posterior o igual a la medición anterior ({$previousDate->format('d/m/Y')})"
                    ];
                }

                // El valor debe ser mayor o igual al anterior
                if ($valor < $previousValue) {
                    return [
                        'valid' => false,
                        'message' => "El valor {$valor} debe ser mayor o igual a la medición anterior ({$previousValue})"
                    ];
                }
            }

            // ✅ Validar contra la medición siguiente (si existe)
            if ($next) {
                $nextDate = $next->measured_at;
                $nextValue = (float) ($next->data['valor'] ?? 0);

                // La fecha debe ser anterior o igual a la siguiente
                if ($fecha->gt($nextDate)) {
                    return [
                        'valid' => false,
                        'message' => "La fecha {$fecha->format('d/m/Y')} debe ser anterior o igual a la medición siguiente ({$nextDate->format('d/m/Y')})"
                    ];
                }

                // El valor debe ser menor o igual al siguiente
                if ($valor > $nextValue) {
                    return [
                        'valid' => false,
                        'message' => "El valor {$valor} debe ser menor o igual a la medición siguiente ({$nextValue})"
                    ];
                }
            }

            return ['valid' => true];

        } catch (\Exception $e) {
            Log::error('Error en validateMeasurementSequence', [
                'sensor_id' => $sensorId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['valid' => false, 'message' => 'Error interno al validar la medición: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener los campos disponibles para identificar sensores en un grupo
     */
    public function getSensorFields(Request $request, $groupId)
    {
        try {
            $user = $request->user();
            
            // ✅ Verificar que el grupo existe
            $group = SensorGroup::find($groupId);
            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grupo no encontrado'
                ], 404);
            }
            
            // Verificar permisos
            $canAccess = $user->hasRole('admin') ||
                        $group->user_id === $user->id ||
                        $group->sharedAccess()
                            ->where('shared_with', $user->id)
                            ->whereIn('role', ['inspector', 'admin'])
                            ->exists();
            
            if (!$canAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para acceder a este grupo'
                ], 403);
            }
            
            // ✅ Obtener sensores del grupo (limitado a 5 para ejemplo)
            $sensors = Sensor::where('group_id', $groupId)->limit(5)->get();
            $sampleSensor = $sensors->first();
            
            $fields = [];
            
            // ✅ Campos base siempre disponibles
            $fields[] = [
                'value' => 'name',
                'label' => 'Nombre del Sensor',
                'description' => 'Nombre descriptivo del sensor'
            ];
            
            $fields[] = [
                'value' => 'identifier',
                'label' => 'Identificador (Código)',
                'description' => 'Código único del sensor (ej: SENSOR_001)'
            ];
            
            $fields[] = [
                'value' => 'id',
                'label' => 'ID del Sensor',
                'description' => 'ID numérico del sensor en el sistema'
            ];
            
            // ✅ Si hay un sensor de ejemplo, agregar campos personalizados de metadata
            if ($sampleSensor && $sampleSensor->metadata) {
                $metadata = $sampleSensor->metadata;
                if (is_array($metadata) && !empty($metadata)) {
                    foreach (array_keys($metadata) as $key) {
                        if (!in_array($key, ['name', 'identifier', 'id'])) {
                            $fields[] = [
                                'value' => 'metadata_' . $key,
                                'label' => 'Campo: ' . ucfirst(str_replace('_', ' ', $key)),
                                'description' => 'Campo personalizado: ' . $key
                            ];
                        }
                    }
                }
            }
            
            // ✅ Construir lista de sensores para mostrar como ejemplo
            $sensorList = $sensors->map(function($s) {
                return [
                    'name' => $s->name,
                    'identifier' => $s->identifier,
                    'id' => $s->id
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => [
                    'fields' => $fields,
                    'default' => 'identifier',
                    'sample_sensor' => $sampleSensor ? [
                        'name' => $sampleSensor->name,
                        'identifier' => $sampleSensor->identifier,
                        'id' => $sampleSensor->id,
                        'metadata' => $sampleSensor->metadata
                    ] : null,
                    'sensors' => $sensorList
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al obtener campos de sensores: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener campos de sensores: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Guardar informe en sesión
     */
    private function storeReport($reportId, $reportData)
    {
        // Guardar en caché y sesión por 24 horas
        Cache::put("import_report_{$reportId}", $reportData, now()->addHours(24));
        try {
            session()->put("import_report_{$reportId}", $reportData);
            session()->put("import_report_{$reportId}_expires", now()->addHours(24));
        } catch (\Throwable $e) {
            // Ignorar si la sesión no está disponible en peticiones API
        }
    }

    /**
     * Obtener informe de sesión o caché
     */
    private function getReport($reportId)
    {
        if (Cache::has("import_report_{$reportId}")) {
            return Cache::get("import_report_{$reportId}");
        }

        try {
            if (session()->has("import_report_{$reportId}")) {
                return session()->get("import_report_{$reportId}");
            }
        } catch (\Throwable $e) {}

        return null;
    }

    /**
     * Generar contenido CSV del informe
     */
    private function generateReportCSV($reportData)
    {
        $handle = fopen('php://temp', 'r+');
        
        // Cabeceras
        fputcsv($handle, [
            'ID de Fila',
            'Sensor',
            'Valor',
            'Fecha',
            'Observaciones',
            'Estado',
            'Mensaje'
        ]);

        // Procesar filas del informe
        foreach ($reportData['rows'] as $row) {
            fputcsv($handle, [
                $row['row_number'] ?? 'N/A',
                $row['sensor'] ?? 'N/A',
                $row['valor'] ?? 'N/A',
                $row['fecha'] ?? 'N/A',
                $row['observaciones'] ?? 'N/A',
                $row['status'] ?? 'error',
                $row['message'] ?? 'Sin mensaje'
            ]);
        }

        // Agregar resumen al final
        fputcsv($handle, ['']);
        fputcsv($handle, ['RESUMEN DE IMPORTACIÓN', '', '', '', '', '', '']);
        fputcsv($handle, ['Total de registros:', $reportData['summary']['total'], '', '', '', '', '']);
        fputcsv($handle, ['Registros exitosos:', $reportData['summary']['success'], '', '', '', '', '']);
        fputcsv($handle, ['Registros con errores:', $reportData['summary']['errors'], '', '', '', '', '']);
        fputcsv($handle, ['Registros sobrescritos:', $reportData['summary']['overwritten'] ?? 0, '', '', '', '', '']);
        fputcsv($handle, ['Fecha y hora:', date('Y-m-d H:i:s'), '', '', '', '', '']);
        fputcsv($handle, ['Usuario:', auth()->user()->name ?? 'N/A', '', '', '', '', '']);

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return $csvContent;
    }

    /**
     * Generar informe de importación para descarga
     */
    public function generateReport(Request $request)
    {
        try {
            $user = $request->user();
            
            // Validar que se haya pasado el ID del informe
            $reportId = $request->input('report_id');
            if (!$reportId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de informe no proporcionado'
                ], 400);
            }

            // Buscar el informe en la sesión
            $reportData = $this->getReport($reportId);
            
            if (!$reportData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Informe no encontrado o expirado'
                ], 404);
            }

            // Generar CSV del informe
            $filename = "informe_importacion_{$reportId}_" . date('Y-m-d_H-i-s') . '.csv';
            $csvContent = $this->generateReportCSV($reportData);

            return response()->streamDownload(function () use ($csvContent) {
                echo $csvContent;
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\""
            ]);

        } catch (\Exception $e) {
            Log::error('Error al generar informe: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar informe: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Leer encabezados de un archivo CSV con detección automática de delimitador
     */
    private function readCsvHeaders($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('El archivo no existe.');
        }

        $file = fopen($filePath, 'r');
        if (!$file) {
            throw new \Exception('No se pudo abrir el archivo CSV.');
        }

        // ✅ Detectar delimitador desde la primera línea
        $firstLine = fgets($file);
        rewind($file);
        
        $delimiter = ';';
        if (strpos($firstLine, ',') !== false && strpos($firstLine, ';') === false) {
            $delimiter = ',';
        } elseif (strpos($firstLine, ';') !== false) {
            $delimiter = ';';
        } elseif (strpos($firstLine, "\t") !== false) {
            $delimiter = "\t";
        }

        $headers = fgetcsv($file, 0, $delimiter);
        fclose($file);

        if ($headers === false) {
            throw new \Exception('No se pudieron leer los encabezados del CSV.');
        }

        // Limpiar headers
        return array_map(function($header) {
            return trim($header, '" \'');
        }, $headers);
    }

    /**
     * Obtener datos de ejemplo de un archivo CSV
     */
    private function getCsvSampleData($filePath, $rows = 5)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('El archivo no existe.');
        }

        $file = fopen($filePath, 'r');
        if (!$file) {
            throw new \Exception('No se pudo abrir el archivo CSV.');
        }

        // ✅ Detectar delimitador
        $firstLine = fgets($file);
        rewind($file);
        
        $delimiter = ';';
        if (strpos($firstLine, ',') !== false && strpos($firstLine, ';') === false) {
            $delimiter = ',';
        } elseif (strpos($firstLine, ';') !== false) {
            $delimiter = ';';
        } elseif (strpos($firstLine, "\t") !== false) {
            $delimiter = "\t";
        }

        $sampleData = [];
        fgetcsv($file, 0, $delimiter); // Saltar encabezados
        
        for ($i = 0; $i < $rows && !feof($file); $i++) {
            $row = fgetcsv($file, 0, $delimiter);
            if ($row !== false) {
                // Limpiar campos
                $cleanRow = array_map(function($field) {
                    if (is_string($field)) {
                        return trim($field, '" \'');
                    }
                    return $field;
                }, $row);
                $sampleData[] = $cleanRow;
            }
        }
        
        fclose($file);
        return $sampleData;
    }

    /**
     * Leer encabezados de un archivo Excel
     */
    private function readExcelHeaders($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('El archivo no existe.');
        }

        if (!class_exists(SimpleXLSX::class)) {
            throw new \Exception('La librería SimpleXLSX no está disponible.');
        }

        $xlsx = SimpleXLSX::parse($filePath);
        if ($xlsx === false) {
            throw new \Exception('No se pudo leer el archivo Excel. Verifica que el archivo sea válido.');
        }

        $rows = $xlsx->rows();
        if (empty($rows)) {
            throw new \Exception('El archivo Excel está vacío.');
        }

        return $rows[0];
    }

    /**
     * Obtener datos de ejemplo de un archivo Excel
     */
    private function getExcelSampleData($filePath, $rows = 5)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('El archivo no existe.');
        }

        if (!class_exists(SimpleXLSX::class)) {
            throw new \Exception('La librería SimpleXLSX no está disponible.');
        }

        $xlsx = SimpleXLSX::parse($filePath);
        if ($xlsx === false) {
            throw new \Exception('No se pudo leer el archivo Excel.');
        }

        $allRows = $xlsx->rows();
        if (count($allRows) < 2) {
            return [];
        }

        return array_slice($allRows, 1, $rows);
    }

    /**
     * Leer todos los datos de un archivo CSV con detección automática de delimitador
     */
    private function readCsvAllData($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('El archivo no existe.');
        }

        $data = [];
        $file = fopen($filePath, 'r');
        if (!$file) {
            throw new \Exception('No se pudo abrir el archivo CSV.');
        }

        // ✅ Leer la primera línea para detectar el delimitador
        $firstLine = fgets($file);
        rewind($file);
        
        // Detectar delimitador: ; o ,
        $delimiter = ';'; // Por defecto punto y coma
        if (strpos($firstLine, ',') !== false && strpos($firstLine, ';') === false) {
            $delimiter = ',';
        } elseif (strpos($firstLine, ';') !== false) {
            $delimiter = ';';
        } else {
            // Si no se detecta, probar con tabulación
            if (strpos($firstLine, "\t") !== false) {
                $delimiter = "\t";
            }
        }
        
        Log::info('📊 Delimitador detectado para CSV: "' . $delimiter . '"');

        // ✅ Leer todo el archivo con el delimitador detectado
        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
            // Limpiar cada campo: eliminar comillas y espacios extras
            $cleanRow = array_map(function($field) {
                if (is_string($field)) {
                    return trim($field, '" \'');
                }
                return $field;
            }, $row);
            
            $data[] = $cleanRow;
        }
        
        fclose($file);

        if (empty($data)) {
            throw new \Exception('El archivo CSV está vacío o no se pudo leer correctamente.');
        }

        return $data;
    }

    /**
     * Leer todos los datos de un archivo Excel
     */
    private function readExcelAllData($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('El archivo no existe.');
        }

        if (!class_exists(SimpleXLSX::class)) {
            throw new \Exception('La librería SimpleXLSX no está disponible.');
        }

        $xlsx = SimpleXLSX::parse($filePath);
        if ($xlsx === false) {
            throw new \Exception('No se pudo leer el archivo Excel.');
        }

        $rows = $xlsx->rows();
        if (empty($rows)) {
            throw new \Exception('El archivo Excel está vacío.');
        }

        return $rows;
    }

    /**
     * Obtener el valor mapeado de una fila
     * ✅ Permite valores numéricos incluyendo 0
     */
    private function getMappedValue($row, $fieldMapping, $fieldName)
    {
        if (!isset($fieldMapping[$fieldName]) || $fieldMapping[$fieldName] === '') {
            return null;
        }

        $columnIndex = (int) $fieldMapping[$fieldName];
        
        if (!isset($row[$columnIndex])) {
            return null;
        }

        $value = trim($row[$columnIndex]);
        
        // ✅ Si el valor es un string vacío o null, devolver null
        if ($value === '' || $value === null) {
            return null;
        }
        
        // ✅ Si el valor es "NULL" (string), devolver null
        if (strtoupper($value) === 'NULL') {
            return null;
        }
        
        // ✅ PERMITIR "0" como valor válido (NO usar empty)
        // NOTA: No usar !empty($value) porque empty(0) es true
        return $value;
    }

    /**
     * Normaliza el nombre o ruta de la foto para mediciones masivas.
     * Si no tiene extensión, se le agrega .jpg por defecto.
     * Se asegura de que apunte a /measurements/filename.
     * Si está vacío, es 'Sin Foto', 'N/A' o similar, retorna 'Sin Foto'.
     */
    private function normalizePhotoName(?string $rawFoto): string
    {
        if ($rawFoto === null) {
            return 'Sin Foto';
        }

        $cleanFoto = trim($rawFoto);

        if ($cleanFoto === '' || in_array(strtolower($cleanFoto), ['sin foto', 'sin_foto', 'n/a', 'na', 'null', 'none', '-', 'no'])) {
            return 'Sin Foto';
        }

        // Remover posibles prefijos previos para normalizar limpio
        $cleanFoto = ltrim($cleanFoto, '/');
        if (str_starts_with(strtolower($cleanFoto), 'measurements/')) {
            $cleanFoto = substr($cleanFoto, strlen('measurements/'));
        }

        // Si no tiene extensión típica de imagen, agregar .jpg por defecto
        if (!preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $cleanFoto)) {
            $cleanFoto .= '.jpg';
        }

        return '/measurements/' . $cleanFoto;
    }

    /**
     * Descargar plantilla para importación de mediciones
     */
    public function downloadTemplate(Request $request)
    {
        try {
            // ✅ Cambiar el formato de fecha a solo YYYY-MM-DD
            $headers = ['Sensor *', 'Valor *', 'Fecha (YYYY-MM-DD) *', 'Foto', 'Observaciones'];
            $sampleData = [
                ['Sensor 1', '100.5', '2026-01-15', '100_17-06-2022', 'Medición inicial (nombre sin extensión -> .jpg)'],
                ['Sensor 2', '150.2', '2026-01-15', 'foto2.jpg', 'Medición con archivo con extensión'],
                ['Sensor 1', '200.8', '2026-02-15', '100_15-02-2026.png', 'Medición con archivo png'],
                ['Sensor 3', '75.3', '2026-01-20', '', 'Sin foto (se sube luego desde la app)'],
                ['Sensor 1', '300.1', '2026-03-15', 'foto4.jpg', 'Medición final']
            ];

            $tempFile = tempnam(sys_get_temp_dir(), 'plantilla_mediciones_') . '.csv';
            $file = fopen($tempFile, 'w');

            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, $headers);

            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);

            return response()->download($tempFile, 'plantilla_importacion_mediciones.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="plantilla_importacion_mediciones.csv"'
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error al descargar plantilla de mediciones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al descargar plantilla: ' . $e->getMessage()
            ], 500);
        }
    }
}