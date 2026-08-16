<?php
// app/Http/Controllers/Api/BulkSensorImportController.php

namespace App\Http\Controllers\Api;

use App\Models\Sensor;
use App\Models\SensorGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Shuchkin\SimpleXLSX;
use Carbon\Carbon;
use App\Services\Subscription\SubscriptionService;

class BulkSensorImportController extends Controller
{
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
                // ✅ DETECTAR CODIFICACIÓN
                $encoding = $this->detectFileEncoding($tempPath);

                // ✅ LEER CON LA CODIFICACIÓN DETECTADA
                $delimiter = $this->detectCsvDelimiter($tempPath);
                $headers = $this->readCsvHeaders($tempPath, $delimiter, $encoding);
                $allData = $this->readCsvAllData($tempPath, $delimiter, $encoding);
            } else {
                $headers = $this->readExcelHeaders($tempPath);
                $allData = $this->readExcelAllData($tempPath);
            }

            // ✅ Limpiar encabezados (quitar comillas y espacios)
            $headers = array_map(function ($header) {
                $header = trim($header);
                $header = str_replace('"', '', $header);
                $header = str_replace(';', '', $header);
                return $header;
            }, $headers);

            // ✅ Obtener las primeras 5 filas para el preview
            $sampleData = array_slice($allData, 1, 5);

            // ✅ Contar el total de filas (excluyendo encabezados)
            $totalRows = max(0, count($allData) - 1);

            // ✅ Obtener cuota disponible
            $subscriptionService = new SubscriptionService($user);
            $limitStatus = $subscriptionService->getLimitStatus()['sensors'];

            if (!$limitStatus['is_unlimited'] && $totalRows > $limitStatus['remaining']) {
                $missingSensors = $totalRows - $limitStatus['remaining'];
                $neededPacks = ceil($missingSensors / 10);
                $cost = number_format($neededPacks * 10000, 0, ',', '.'); // 10000 ARS per pack

                return response()->json([
                    'success' => false,
                    'error_type' => 'quota_exceeded',
                    'message' => "Atención: El archivo contiene {$totalRows} sensores, pero solo tienes espacio libre para {$limitStatus['remaining']}.",
                    'upsell_data' => [
                        'missing_sensors' => $missingSensors,
                        'needed_packs' => $neededPacks,
                        'estimated_cost' => $neededPacks * 10000,
                        'formatted_cost' => $cost
                    ]
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Archivo analizado correctamente',
                'data' => [
                    'headers' => $headers,
                    'sample_data' => $sampleData,
                    'all_data' => $allData,
                    'total_rows' => $totalRows,
                    'preview_rows' => count($sampleData),
                    'delimiter' => $delimiter ?? 'auto',
                    'encoding' => $encoding ?? 'UTF-8',
                    'quota' => [
                        'max' => $limitStatus['max'],
                        'remaining' => $limitStatus['remaining'],
                        'is_unlimited' => $limitStatus['is_unlimited']
                    ]
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
     * ✅ DETECTAR CODIFICACIÓN DEL ARCHIVO
     */
    private function detectFileEncoding($filePath): string
    {
        if (!file_exists($filePath)) {
            return 'UTF-8';
        }

        // Leer los primeros 1000 bytes para detectar codificación
        $handle = fopen($filePath, 'r');
        $content = fread($handle, 1000);
        fclose($handle);

        // Intentar detectar la codificación
        $encodings = ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'CP1252'];

        foreach ($encodings as $encoding) {
            if (mb_check_encoding($content, $encoding)) {
                return $encoding;
            }
        }

        // Si no se detecta, intentar convertir forzadamente
        if (mb_check_encoding($content, 'UTF-8')) {
            return 'UTF-8';
        }

        // Por defecto, intentar convertir desde ISO-8859-1
        return 'ISO-8859-1';
    }

    /**
     * ✅ CONVERTIR STRING A UTF-8
     */
    private function convertToUtf8($string, $fromEncoding = 'ISO-8859-1')
    {
        if (empty($string)) {
            return $string;
        }

        // Si ya es UTF-8, devolver tal cual
        if (mb_check_encoding($string, 'UTF-8')) {
            return $string;
        }

        // Intentar convertir desde la codificación detectada
        $converted = @mb_convert_encoding($string, 'UTF-8', $fromEncoding);

        if ($converted === false) {
            // Fallback: eliminar caracteres no válidos
            $converted = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
            $converted = preg_replace('/[^\x20-\x7E\xA0-\xFF]/', '', $converted);
        }

        return $converted;
    }
    private function detectCsvDelimiter($filePath): string
    {
        $delimiters = [';', ',', "\t", '|'];
        $firstLine = '';

        if (($handle = fopen($filePath, 'r')) !== false) {
            $firstLine = fgets($handle);
            fclose($handle);
        }

        if (empty($firstLine)) {
            return ';'; // Por defecto
        }

        $counts = [];
        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($firstLine, $delimiter);
        }

        // Ordenar por cantidad de ocurrencias (descendente)
        arsort($counts);

        // Devolver el delimitador con más ocurrencias
        return key($counts);
    }




    /**
     * Leer encabezados de un archivo CSV
     */
    private function readCsvHeaders($filePath, $delimiter = ';', $encoding = 'UTF-8')
    {
        if (!file_exists($filePath)) {
            throw new \Exception('El archivo no existe.');
        }

        $file = fopen($filePath, 'r');
        if (!$file) {
            throw new \Exception('No se pudo abrir el archivo CSV.');
        }

        // Leer primera línea
        $headers = fgetcsv($file, 0, $delimiter);
        fclose($file);

        if ($headers === false) {
            throw new \Exception('No se pudieron leer los encabezados del CSV.');
        }

        // ✅ CONVERTIR CADA HEADER A UTF-8
        $headers = array_map(function ($header) use ($encoding) {
            $header = $this->convertToUtf8($header, $encoding);
            return trim(str_replace('"', '', $header));
        }, $headers);

        return $headers;
    }


    /**
     * Obtener datos de ejemplo de un archivo CSV
     */
    private function getCsvSampleData($filePath, $rows = 3)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('El archivo no existe.');
        }

        $file = fopen($filePath, 'r');
        if (!$file) {
            throw new \Exception('No se pudo abrir el archivo CSV.');
        }

        $sampleData = [];
        // Saltar la primera fila (encabezados)
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
    private function getExcelSampleData($filePath, $rows = 3)
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
    private function readCsvAllData($filePath, $delimiter = ';', $encoding = 'UTF-8')
    {
        if (!file_exists($filePath)) {
            throw new \Exception('El archivo no existe.');
        }

        $data = [];
        $file = fopen($filePath, 'r');
        if (!$file) {
            throw new \Exception('No se pudo abrir el archivo CSV.');
        }

        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
            // ✅ CONVERTIR CADA VALOR A UTF-8
            $cleanedRow = array_map(function ($value) use ($encoding) {
                $value = $this->convertToUtf8($value, $encoding);
                return trim(str_replace('"', '', $value));
            }, $row);
            $data[] = $cleanedRow;
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
     * Obtener el valor mapeado de una fila usando el fieldMapping
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

        $value = $row[$columnIndex];

        if ($value === null || $value === '' || $value === 'NULL') {
            return null;
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            return trim($value);
        }

        return (string) $value;
    }

    /**
     * Descargar plantilla para importación
     */
    public function downloadTemplate(Request $request)
    {
        try {
            $headers = ['Nombre *', 'Identificador *', 'Descripción'];
            $sampleData = [
                ['Sensor 1', 'SENSOR_001', 'Descripción del sensor 1'],
                ['Sensor 2', 'SENSOR_002', 'Descripción del sensor 2'],
                ['Sensor 3', 'SENSOR_003', 'Descripción del sensor 3']
            ];

            // Crear archivo CSV
            $tempFile = tempnam(sys_get_temp_dir(), 'plantilla_importacion_sensores_') . '.csv';
            $file = fopen($tempFile, 'w');

            // Agregar BOM para UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Escribir encabezados
            fputcsv($file, $headers);

            // Escribir datos de ejemplo
            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);

            return response()->download($tempFile, 'plantilla_importacion_sensores.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="plantilla_importacion_sensores.csv"'
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error al descargar plantilla: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al descargar plantilla: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener campos extras definidos por el usuario
     */
    /**
     * Obtener campos extras definidos por el usuario
     */
    /**
     * Obtener campos extras disponibles para la importación de sensores
     */
    public function getExtraFields(Request $request)
    {
        // ✅ Campos predefinidos que el usuario puede agregar como metadatos
        $availableExtraFields = [
            ['name' => 'lote', 'label' => 'Lote', 'type' => 'text', 'required' => false],
            ['name' => 'apellido', 'label' => 'Apellido', 'type' => 'text', 'required' => false],
            ['name' => 'ubicacion', 'label' => 'Ubicación', 'type' => 'text', 'required' => false],
            ['name' => 'fecha_instalacion', 'label' => 'Fecha de Instalación', 'type' => 'date', 'required' => false],
            ['name' => 'marca', 'label' => 'Marca', 'type' => 'text', 'required' => false],
            ['name' => 'modelo', 'label' => 'Modelo', 'type' => 'text', 'required' => false],
            ['name' => 'serial', 'label' => 'Número de Serie', 'type' => 'text', 'required' => false],
            ['name' => 'proveedor', 'label' => 'Proveedor', 'type' => 'text', 'required' => false],
            ['name' => 'fecha_fabricacion', 'label' => 'Fecha de Fabricación', 'type' => 'date', 'required' => false],
            ['name' => 'garantia', 'label' => 'Garantía (meses)', 'type' => 'number', 'required' => false],
            ['name' => 'calibracion', 'label' => 'Última Calibración', 'type' => 'date', 'required' => false],
        ];

        return response()->json([
            'success' => true,
            'data' => $availableExtraFields
        ]);
    }


    /**
     * Importar sensores masivamente con soporte para sobrescritura parcial
     */
    public function bulkImport(Request $request)
    {
        try {
            $user = $request->user();

            Log::info('📥 Iniciando importación masiva de sensores', [
                'user_id' => $user->id,
                'has_file' => $request->hasFile('file'),
                'group_id' => $request->input('group_id'),
                'field_mapping' => $request->input('field_mapping'),
                'extra_fields' => $request->input('extra_fields')
            ]);

            // ✅ VERIFICAR QUE EL ARCHIVO EXISTA
            if (!$request->hasFile('file')) {
                Log::error('❌ No se ha subido ningún archivo');
                return response()->json([
                    'success' => false,
                    'message' => 'No se ha subido ningún archivo. Por favor, selecciona un archivo .xlsx o .csv.'
                ], 400);
            }

            $file = $request->file('file');

            // ✅ VERIFICAR QUE EL ARCHIVO SEA VÁLIDO
            if (!$file->isValid()) {
                Log::error('❌ El archivo no es válido', [
                    'error' => $file->getError(),
                    'error_message' => $file->getErrorMessage()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo no es válido. Intenta nuevamente.'
                ], 400);
            }

            // ✅ OBTENER LA EXTENSIÓN REAL DEL ARCHIVO
            $originalName = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension());
            $mimeType = $file->getMimeType();

            Log::info('📄 Información del archivo', [
                'original_name' => $originalName,
                'extension' => $extension,
                'mime_type' => $mimeType,
                'size' => $file->getSize()
            ]);

            // ✅ VALIDAR EXTENSIÓN MANUALMENTE (más flexible)
            $validExtensions = ['xlsx', 'csv'];
            $validMimeTypes = [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                'application/vnd.ms-excel', // .xls
                'text/csv', // .csv
                'text/plain', // .csv
                'application/csv', // .csv
                'application/x-csv' // .csv
            ];

            if (!in_array($extension, $validExtensions)) {
                Log::error('❌ Extensión no válida', ['extension' => $extension]);
                return response()->json([
                    'success' => false,
                    'message' => "Extensión de archivo no válida: '{$extension}'. Solo se permiten archivos .xlsx o .csv."
                ], 400);
            }

            // ✅ VALIDAR TAMAÑO (10MB máximo)
            $maxSize = 10 * 1024 * 1024; // 10MB en bytes
            if ($file->getSize() > $maxSize) {
                Log::error('❌ Archivo demasiado grande', ['size' => $file->getSize()]);
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo es demasiado grande. El tamaño máximo permitido es 10MB.'
                ], 400);
            }

            // ✅ VALIDACIÓN DE CAMPOS
            $validator = Validator::make($request->all(), [
                'group_id' => 'required|exists:sensor_groups,id',
                'field_mapping' => 'required',
                'extra_fields' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                Log::error('❌ Error de validación de campos', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ Procesar field_mapping
            $fieldMapping = $request->input('field_mapping');
            if (is_string($fieldMapping)) {
                $fieldMapping = json_decode($fieldMapping, true);
            }

            // ✅ Procesar extra_fields
            $extraFieldsRaw = $request->input('extra_fields', []);
            if (is_string($extraFieldsRaw)) {
                $extraFieldsRaw = json_decode($extraFieldsRaw, true);
            }
            if (!is_array($extraFieldsRaw)) {
                $extraFieldsRaw = [];
            }

            $extraFieldMapping = [];
            foreach ($extraFieldsRaw as $field) {
                if (isset($field['name']) && isset($field['column'])) {
                    $extraFieldMapping[$field['name']] = (int) $field['column'];
                }
            }

            Log::info('📋 Mapeo procesado', [
                'field_mapping' => $fieldMapping,
                'extra_field_mapping' => $extraFieldMapping
            ]);

            if (!is_array($fieldMapping) || !isset($fieldMapping['name']) || !isset($fieldMapping['identifier'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'El mapeo de campos es inválido. Debe incluir "name" e "identifier".'
                ], 422);
            }

            // ✅ Verificar permisos del grupo
            $group = SensorGroup::findOrFail($request->group_id);
            $canAccessGroup = $user->hasRole('admin') ||
                $group->user_id == $user->id ||
                $group->sharedAccess()
                    ->where('shared_with', $user->id)
                    ->whereIn('role', ['inspector', 'admin', 'editor'])
                    ->exists();

            if (!$canAccessGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para importar sensores en este grupo'
                ], 403);
            }

            // ✅ Leer archivo con detección de codificación
            $tempPath = $file->getRealPath();

            // ✅ Si el archivo es CSV pero no tiene la extensión correcta, forzarlo
            $detectedExtension = $extension;
            if ($mimeType === 'text/csv' || $mimeType === 'text/plain' || $mimeType === 'application/csv') {
                $detectedExtension = 'csv';
            }

            if ($detectedExtension === 'csv' || $extension === 'csv') {
                $encoding = $this->detectFileEncoding($tempPath);
                $delimiter = $this->detectCsvDelimiter($tempPath);
                $allData = $this->readCsvAllData($tempPath, $delimiter, $encoding);
            } else {
                $allData = $this->readExcelAllData($tempPath);
            }

            if (empty($allData) || count($allData) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo no contiene datos o solo tiene encabezados'
                ], 400);
            }

            // ✅ VERIFICAR CUOTAS DE SENSORES
            $subscriptionService = new SubscriptionService($user);
            $limitStatus = $subscriptionService->getLimitStatus()['sensors'];

            // Calculamos cuántos sensores válidos vienen
            $incomingSensors = 0;
            for ($i = 1; $i < count($allData); $i++) {
                if (!empty($allData[$i]) && count(array_filter($allData[$i])) > 0) {
                    $incomingSensors++;
                }
            }

            if (!$limitStatus['is_unlimited'] && $incomingSensors > $limitStatus['remaining']) {
                $statusPlan = $subscriptionService->getPlan()->getPlanName();
                return response()->json([
                    'success' => false,
                    'message' => "Atención: El archivo contiene {$incomingSensors} sensores y tienes espacio para {$limitStatus['remaining']}. " .
                        "Para procesar este listado, adquiere Packs Extra desde Mi Perfil o depura tu base de datos actual.",
                ], 403);
            }

            // ... CONTINUAR CON EL RESTO DE LA IMPORTACIÓN ...
            $createdCount = 0;
            $updatedCount = 0;
            $errorCount = 0;
            $errors = [];
            $sensorsToCreate = [];

            // Saltar encabezados (fila 0)
            for ($i = 1; $i < count($allData); $i++) {
                $row = $allData[$i];

                if (empty($row) || count(array_filter($row)) === 0) {
                    continue;
                }

                try {
                    // Obtener campos base
                    $name = $this->getMappedValue($row, $fieldMapping, 'name');
                    $identifier = $this->getMappedValue($row, $fieldMapping, 'identifier');
                    $description = $this->getMappedValue($row, $fieldMapping, 'description');

                    if (empty($name) || empty($identifier)) {
                        $errorCount++;
                        $errors[] = [
                            'row' => $i + 1,
                            'error' => 'Faltan campos obligatorios (Nombre o Identificador)',
                            'data' => $row
                        ];
                        continue;
                    }

                    // Construir metadata
                    $newMetadata = [];
                    foreach ($extraFieldMapping as $fieldName => $columnIndex) {
                        $value = $this->getMappedValueFromColumn($row, $columnIndex);
                        if ($value !== null && $value !== '') {
                            $newMetadata[$fieldName] = $value;
                        }
                    }

                    // Buscar sensor existente por identificador
                    $existingSensor = Sensor::where('identifier', $identifier)
                        ->where('group_id', $request->group_id)
                        ->first();

                    if ($existingSensor) {
                        // Actualizar sensor existente
                        $updateData = [];

                        if (isset($fieldMapping['name']) && $fieldMapping['name'] !== '') {
                            $updateData['name'] = $name;
                        }

                        if (isset($fieldMapping['description']) && $fieldMapping['description'] !== '') {
                            $updateData['description'] = $description ?? null;
                        }

                        $currentMetadata = $existingSensor->metadata ?? [];
                        if (!is_array($currentMetadata)) {
                            $currentMetadata = json_decode($currentMetadata, true) ?? [];
                        }

                        if (!empty($newMetadata)) {
                            $updateData['metadata'] = array_merge($currentMetadata, $newMetadata);
                        }

                        if (!empty($updateData)) {
                            $updateData['updated_at'] = Carbon::now();
                            $existingSensor->update($updateData);
                            $updatedCount++;
                        } else {
                            $updatedCount++;
                        }
                    } else {
                        // Crear nuevo sensor
                        $sensorsToCreate[] = [
                            'name' => $name,
                            'identifier' => $identifier,
                            'description' => $description ?? null,
                            'group_id' => $request->group_id,
                            'metadata' => !empty($newMetadata) ? json_encode($newMetadata) : null,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ];
                        $createdCount++;
                    }

                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = [
                        'row' => $i + 1,
                        'error' => $e->getMessage(),
                        'data' => $row
                    ];
                }
            }

            // Insertar nuevos sensores en lote
            if (!empty($sensorsToCreate)) {
                DB::beginTransaction();
                try {
                    $chunks = array_chunk($sensorsToCreate, 100);
                    foreach ($chunks as $chunk) {
                        Sensor::insert($chunk);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            $totalProcessed = $createdCount + $updatedCount + $errorCount;

            return response()->json([
                'success' => true,
                'message' => 'Importación completada',
                'data' => [
                    'created_count' => $createdCount,
                    'updated_count' => $updatedCount,
                    'error_count' => $errorCount,
                    'errors' => $errors,
                    'total_processed' => $totalProcessed
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error en bulkImport', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al importar sensores: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Obtener valor de una columna específica (para campos extras)
     */
    private function getMappedValueFromColumn($row, $columnIndex)
    {
        if ($columnIndex === null || $columnIndex === '') {
            return null;
        }

        if (!isset($row[$columnIndex])) {
            return null;
        }

        $value = $row[$columnIndex];
        if ($value === null || $value === '' || $value === 'NULL') {
            return null;
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            return trim($value);
        }

        return (string) $value;
    }
    /**
     * Obtener los campos de la plantilla de un grupo
     */
    public function getTemplateFields(Request $request, $groupId)
    {
        try {
            $user = $request->user();

            $group = SensorGroup::with('template')->findOrFail($groupId);

            // Verificar permisos
            $canAccess = $user->hasRole('admin') ||
                $group->user_id == $user->id ||
                $group->sharedAccess()
                    ->where('shared_with', $user->id)
                    ->whereIn('role', ['inspector', 'admin', 'editor'])
                    ->exists();

            if (!$canAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para acceder a este grupo'
                ], 403);
            }

            // Obtener campos de la plantilla
            $template = $group->template;
            $fields = [];

            if ($template && isset($template->schema['campos'])) {
                foreach ($template->schema['campos'] as $campo) {
                    $fields[] = [
                        'name' => $campo['nombre'],
                        'label' => $this->getFieldLabel($campo['nombre']),
                        'type' => $campo['tipo'],
                        'required' => $campo['requerido'] ?? false,
                        'unit' => $campo['unidad'] ?? null,
                        'es_foto' => $campo['es_foto'] ?? false,
                        'contexto' => $campo['contexto'] ?? 'medicion',
                        'is_base' => false
                    ];
                }
            }

            // Agregar campos base del sensor
            $baseFields = [
                [
                    'name' => 'name',
                    'label' => 'Nombre del Sensor',
                    'type' => 'text',
                    'required' => true,
                    'is_base' => true
                ],
                [
                    'name' => 'identifier',
                    'label' => 'Identificador',
                    'type' => 'text',
                    'required' => true,
                    'is_base' => true
                ],
                [
                    'name' => 'description',
                    'label' => 'Descripción',
                    'type' => 'text',
                    'required' => false,
                    'is_base' => true
                ]
            ];

            // Combinar campos base con campos de la plantilla
            $allFields = array_merge($baseFields, $fields);

            return response()->json([
                'success' => true,
                'message' => 'Campos de la plantilla obtenidos correctamente',
                'data' => [
                    'fields' => $allFields,
                    'template_name' => $template->name ?? 'Sin plantilla',
                    'template_type' => $template->type ?? 'personalizado'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener campos de la plantilla: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener campos de la plantilla: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener etiqueta legible para un campo
     */
    private function getFieldLabel($fieldName)
    {
        $labels = [
            'name' => 'Nombre del Sensor',
            'identifier' => 'Identificador',
            'description' => 'Descripción',
            'consumo_m3' => 'Consumo (m³)',
            'energia_kwh' => 'Energía (kWh)',
            'temperatura_c' => 'Temperatura (°C)',
            'presion_bar' => 'Presión (bar)',
            'caudal_lmin' => 'Caudal (L/min)',
            'iluminacion_lux' => 'Iluminación (lux)',
            'medicion' => 'Medición',
            'foto' => 'Foto',
            'fecha_medicion' => 'Fecha de Medición',
            'voltaje_v' => 'Voltaje (V)',
            'corriente_a' => 'Corriente (A)',
            'factor_potencia' => 'Factor de Potencia',
            'humedad' => 'Humedad (%)',
            'temperatura_color' => 'Temperatura de Color (K)'
        ];

        return $labels[$fieldName] ?? ucfirst(str_replace('_', ' ', $fieldName));
    }


}