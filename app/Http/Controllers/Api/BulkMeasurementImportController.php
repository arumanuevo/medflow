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

            if ($fileExtension === 'csv') {
                $headers = $this->readCsvHeaders($tempPath);
                $sampleData = $this->getCsvSampleData($tempPath, 5);
            } else {
                $headers = $this->readExcelHeaders($tempPath);
                $sampleData = $this->getExcelSampleData($tempPath, 5);
            }

            $headers = array_map('trim', $headers);

            return response()->json([
                'success' => true,
                'message' => 'Archivo analizado correctamente',
                'data' => [
                    'headers' => $headers,
                    'sample_data' => $sampleData
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
        try {
            $user = $request->user();

            Log::info('Iniciando importación masiva de mediciones', [
                'user_id' => $user->id,
                'has_file' => $request->hasFile('file'),
                'group_id' => $request->input('group_id'),
                'field_mapping' => $request->input('field_mapping'),
                'overwrite_duplicates' => $request->input('overwrite_duplicates')
            ]);

            // Validaciones
            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se ha subido ningún archivo'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:xlsx,csv|max:10240',
                'group_id' => 'required|exists:sensor_groups,id',
                'field_mapping' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Obtener field_mapping
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

            // Obtener todos los sensores del grupo
            $sensorsInGroup = Sensor::where('group_id', $request->group_id)
                ->get()
                ->keyBy('name');

            if ($sensorsInGroup->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El grupo seleccionado no tiene sensores. Crea sensores primero.'
                ], 400);
            }

            // Procesar el archivo
            $file = $request->file('file');
            $tempPath = $file->getRealPath();
            $fileExtension = strtolower($file->getClientOriginalExtension());

            if ($fileExtension === 'csv') {
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

            Log::info('Primeras filas del archivo', [
                'headers' => $allData[0] ?? [],
                'first_row' => $allData[1] ?? [],
                'second_row' => $allData[2] ?? [],
                'field_mapping' => $fieldMapping
            ]);

            // Procesar los datos
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $measurementsToCreate = [];
            $sensorCache = [];
            $existingMeasurementsCache = [];
            $reportRows = [];
            $overwrittenCount = 0;
            $overwriteDuplicates = $request->input('overwrite_duplicates', false);

            // Procesar cada fila
            foreach ($allData as $index => $row) {
                if ($index === 0) continue; // Saltar encabezados

                if (empty($row) || count(array_filter($row)) === 0) {
                    continue;
                }

                try {
                    // Obtener valores mapeados
                    $sensorName = trim($this->getMappedValue($row, $fieldMapping, 'sensor'));
                    $valor = $this->getMappedValue($row, $fieldMapping, 'valor');
                    $fechaStr = $this->getMappedValue($row, $fieldMapping, 'fecha');
                    $foto = $this->getMappedValue($row, $fieldMapping, 'foto') ?? 'Sin Foto';
                    $observaciones = $this->getMappedValue($row, $fieldMapping, 'observaciones') ?? '';

                    Log::debug("Procesando fila {$index}", [
                        'sensorName' => $sensorName,
                        'valor' => $valor,
                        'fechaStr' => $fechaStr,
                        'row_data' => $row
                    ]);

                    $reportRow = [
                        'row_number' => $index + 1,
                        'sensor' => $sensorName,
                        'valor' => $valor,
                        'fecha' => $fechaStr,
                        'observaciones' => $observaciones,
                        'status' => 'pending',
                        'message' => ''
                    ];

                    $errorsForRow = [];

                    // Validar sensor
                    if (empty($sensorName)) {
                        $errorsForRow[] = 'El campo "Sensor" es obligatorio';
                    } else if (!$sensorsInGroup->has($sensorName)) {
                        $errorsForRow[] = "El sensor '{$sensorName}' no existe en el grupo. Disponibles: " . $sensorsInGroup->keys()->implode(', ');
                    }

                    // Validar valor
                    if ($valor === null || $valor === '') {
                        $errorsForRow[] = 'El campo "Valor" es obligatorio';
                    } else if (!is_numeric($valor)) {
                        $errorsForRow[] = "El valor '{$valor}' no es un número válido";
                    }

                    // ✅ Validar y parsear fecha (SOLO FECHA, sin hora)
                    if ($fechaStr === null || $fechaStr === '') {
                        $errorsForRow[] = 'El campo "Fecha" es obligatorio';
                    } else {
                        try {
                            // Intentar parsear con formato YYYY-MM-DD
                            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaStr)) {
                                $fecha = Carbon::createFromFormat('Y-m-d', $fechaStr);
                            }
                            // Intentar parsear con formato DD/MM/YYYY
                            elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fechaStr)) {
                                $fecha = Carbon::createFromFormat('d/m/Y', $fechaStr);
                            }
                            // Intentar parseo automático
                            else {
                                $fecha = Carbon::parse($fechaStr);
                            }
                            
                            // Forzar hora a 00:00:00
                            $fecha->setTime(0, 0, 0);
                            
                        } catch (\Exception $e) {
                            $errorsForRow[] = "Fecha inválida: '{$fechaStr}'. Usa formato YYYY-MM-DD o DD/MM/YYYY";
                        }
                    }

                    if (empty($errorsForRow)) {
                        $valorFloat = (float) $valor;
                        $sensorId = $sensorsInGroup[$sensorName]->id;

                        // Obtener mediciones existentes del sensor
                        if (!isset($existingMeasurementsCache[$sensorId])) {
                            $existingMeasurementsCache[$sensorId] = Measurement::where('sensor_id', $sensorId)
                                ->orderBy('measured_at', 'asc')
                                ->get();
                        }

                        // ✅ Si el sensor NO tiene mediciones, la primera siempre es válida
                        if ($existingMeasurementsCache[$sensorId]->isEmpty()) {
                            // No hay mediciones previas, aceptar sin validación de secuencia
                        } else {
                            // Verificar duplicado exacto (misma fecha y mismo valor)
                            $duplicate = $existingMeasurementsCache[$sensorId]->first(function($m) use ($fecha, $valorFloat) {
                                return $m->measured_at->format('Y-m-d') === $fecha->format('Y-m-d') && 
                                       ($m->data['valor'] ?? 0) == $valorFloat;
                            });

                            if ($duplicate) {
                                if ($overwriteDuplicates) {
                                    // Eliminar duplicado
                                    Measurement::where('sensor_id', $sensorId)
                                        ->whereDate('measured_at', $fecha->format('Y-m-d'))
                                        ->where('data->valor', $valorFloat)
                                        ->delete();
                                    $overwrittenCount++;
                                    $reportRow['status'] = 'overwritten';
                                    $reportRow['message'] = 'Registro sobrescrito (duplicado eliminado)';
                                } else {
                                    $errorsForRow[] = "Ya existe una medición con la misma fecha y valor";
                                }
                            }

                            // Solo validar secuencia si no hay duplicado
                            if (empty($errorsForRow) && !$duplicate) {
                                $validationResult = $this->validateMeasurementSequence(
                                    $sensorId,
                                    $fecha,
                                    $valorFloat,
                                    $existingMeasurementsCache[$sensorId],
                                    $measurementsToCreate
                                );

                                if (!$validationResult['valid']) {
                                    $errorsForRow[] = $validationResult['message'];
                                }
                            }
                        }

                        if (empty($errorsForRow)) {
                            // ✅ Crear medición con fecha SIN hora (00:00:00)
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
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ];

                            // Agregar a cache para futuras validaciones
                            $existingMeasurementsCache[$sensorId]->push((object) [
                                'measured_at' => $fecha,
                                'data' => ['valor' => $valorFloat]
                            ]);

                            $successCount++;
                            $reportRow['status'] = 'success';
                            $reportRow['message'] = 'Importado correctamente';
                        }
                    }

                    if (!empty($errorsForRow)) {
                        $errorCount++;
                        $reportRow['status'] = 'error';
                        $reportRow['message'] = implode('; ', $errorsForRow);
                        $errors[] = [
                            'row' => $index + 1,
                            'sensor' => $sensorName ?? 'N/A',
                            'valor' => $valor ?? 'N/A',
                            'fecha' => $fechaStr ?? 'N/A',
                            'error' => $reportRow['message'],
                            'status' => 'error'
                        ];
                    }

                    $reportRows[] = $reportRow;

                } catch (\Exception $e) {
                    $errorCount++;
                    $reportRows[] = [
                        'row_number' => $index + 1,
                        'sensor' => $sensorName ?? 'N/A',
                        'valor' => $valor ?? 'N/A',
                        'fecha' => $fechaStr ?? 'N/A',
                        'observaciones' => $observaciones ?? 'N/A',
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                    
                    $errors[] = [
                        'row' => $index + 1,
                        'sensor' => $sensorName ?? 'N/A',
                        'valor' => $valor ?? 'N/A',
                        'fecha' => $fechaStr ?? 'N/A',
                        'error' => $e->getMessage(),
                        'status' => 'error'
                    ];
                    
                    Log::error('Error procesando fila', [
                        'row' => $index + 1,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Crear mediciones en transacción
            if (!empty($measurementsToCreate)) {
                DB::beginTransaction();
                try {
                    // Ordenar por fecha
                    usort($measurementsToCreate, function($a, $b) {
                        return strcmp($a['measured_at'], $b['measured_at']);
                    });

                    $chunks = array_chunk($measurementsToCreate, 100);
                    foreach ($chunks as $chunk) {
                        Measurement::insert($chunk);
                    }
                    DB::commit();

                    Log::info('Mediciones importadas correctamente', [
                        'user_id' => $user->id,
                        'group_id' => $request->group_id,
                        'count' => count($measurementsToCreate)
                    ]);

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Error al importar mediciones en lote', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            }

            // Generar ID único para el informe
            $reportId = uniqid('import_', true);
            
            // Guardar informe en sesión
            $reportData = [
                'id' => $reportId,
                'rows' => $reportRows,
                'summary' => [
                    'total' => count($reportRows),
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
                    'errors' => $errors,
                    'total_processed' => count($reportRows),
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'report_id' => $reportId
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en bulkImportMeasurement', [
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
     */
    private function validateMeasurementSequence($sensorId, Carbon $fecha, float $valor, $existingMeasurements, array $newMeasurements): array
    {
        // Combinar mediciones existentes con las nuevas (en memoria)
        $allMeasurements = collect($existingMeasurements);

        foreach ($newMeasurements as $newMeasurement) {
            if ($newMeasurement['sensor_id'] !== $sensorId) continue;
            
            $allMeasurements->push((object) [
                'measured_at' => Carbon::parse($newMeasurement['measured_at']),
                'data' => ['valor' => json_decode($newMeasurement['data'], true)['valor']]
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
            
            // Comparar SOLO por fecha (sin hora) para encontrar coincidencias
            if ($measurementDate->format('Y-m-d') === $fecha->format('Y-m-d') && 
                isset($measurement->data['valor']) && 
                $measurement->data['valor'] == $valor) {
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

        // Si no se encontró la posición (no debería pasar)
        if ($position === null) {
            return ['valid' => false, 'message' => 'Error interno al validar la medición'];
        }

        // ✅ Validar contra la medición anterior (si existe)
        if ($previous) {
            $previousDate = $previous->measured_at;
            $previousValue = $previous->data['valor'] ?? 0;

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
            $nextValue = $next->data['valor'] ?? 0;

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
    }

    /**
     * Guardar informe en sesión
     */
    private function storeReport($reportId, $reportData)
    {
        // Guardar en sesión por 24 horas
        session()->put("import_report_{$reportId}", $reportData);
        session()->put("import_report_{$reportId}_expires", now()->addHours(24));
    }

    /**
     * Obtener informe de sesión
     */
    private function getReport($reportId)
    {
        if (session()->has("import_report_{$reportId}")) {
            return session()->get("import_report_{$reportId}");
        }
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
     * Leer encabezados de un archivo CSV
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

        $headers = fgetcsv($file);
        fclose($file);

        if ($headers === false) {
            throw new \Exception('No se pudieron leer los encabezados del CSV.');
        }

        return $headers;
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

        $sampleData = [];
        fgetcsv($file);
        
        for ($i = 0; $i < $rows && !feof($file); $i++) {
            $row = fgetcsv($file);
            if ($row !== false) {
                $sampleData[] = $row;
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
     * Leer todos los datos de un archivo CSV
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

        while (($row = fgetcsv($file)) !== false) {
            $data[] = $row;
        }
        
        fclose($file);

        if (empty($data)) {
            throw new \Exception('El archivo CSV está vacío.');
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
        return !empty($value) ? $value : null;
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
            ['Sensor 1', '100.5', '2026-01-15', 'foto1.jpg', 'Medición inicial'],
            ['Sensor 2', '150.2', '2026-01-15', 'foto2.jpg', 'Medición de Sensor 2'],
            ['Sensor 1', '200.8', '2026-02-15', 'foto3.jpg', 'Medición con incremento'],
            ['Sensor 3', '75.3', '2026-01-20', '', 'Sin foto'],
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