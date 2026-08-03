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
            $headers = $this->readCsvHeaders($tempPath);
            $allData = $this->readCsvAllData($tempPath);
        } else {
            $headers = $this->readExcelHeaders($tempPath);
            $allData = $this->readExcelAllData($tempPath);
        }

        $headers = array_map(function($header) {
            return trim($header);
        }, $headers);

        // ✅ Obtener las primeras 5 filas para el preview
        $sampleData = array_slice($allData, 1, 5);
        
        // ✅ Contar el total de filas (excluyendo encabezados)
        $totalRows = max(0, count($allData) - 1);

        return response()->json([
            'success' => true,
            'message' => 'Archivo analizado correctamente',
            'data' => [
                'headers' => $headers,
                'sample_data' => $sampleData,
                'all_data' => $allData,        // ✅ TODOS los datos
                'total_rows' => $totalRows,    // ✅ Total de filas
                'preview_rows' => count($sampleData)
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
        
        Log::info('Iniciando importación masiva de sensores', [
            'user_id' => $user->id,
            'has_file' => $request->hasFile('file'),
            'group_id' => $request->input('group_id'),
            'field_mapping' => $request->input('field_mapping'),
            'extra_fields' => $request->input('extra_fields')
        ]);

        if (!$request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'No se ha subido ningún archivo'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,csv|max:10240',
            'group_id' => 'required|exists:sensor_groups,id',
            'field_mapping' => 'required',
            'extra_fields' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            Log::error('❌ Error de validación', $validator->errors()->toArray());
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

        // ✅ Procesar extra_fields (puede ser string JSON o array)
        $extraFieldsRaw = $request->input('extra_fields', []);
        if (is_string($extraFieldsRaw)) {
            $extraFieldsRaw = json_decode($extraFieldsRaw, true);
        }
        if (!is_array($extraFieldsRaw)) {
            $extraFieldsRaw = [];
        }

        // ✅ Convertir array de objetos a array asociativo [nombre => columna]
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
                         $group->user_id === $user->id ||
                         $group->sharedAccess()
                             ->where('shared_with', $user->id)
                             ->whereIn('role', ['inspector', 'admin'])
                             ->exists();

        if (!$canAccessGroup) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para importar sensores en este grupo'
            ], 403);
        }

        // ✅ Leer archivo
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

        $createdCount = 0;
        $updatedCount = 0;
        $errorCount = 0;
        $errors = [];
        $sensorsToCreate = [];

        foreach ($allData as $index => $row) {
            if ($index === 0) continue;

            if (empty($row) || count(array_filter($row)) === 0) {
                continue;
            }

            try {
                // ✅ Obtener campos base
                $name = $this->getMappedValue($row, $fieldMapping, 'name');
                $identifier = $this->getMappedValue($row, $fieldMapping, 'identifier');
                $description = $this->getMappedValue($row, $fieldMapping, 'description');

                if (empty($name) || empty($identifier)) {
                    $errorCount++;
                    $errors[] = [
                        'row' => $index + 1,
                        'error' => 'Faltan campos obligatorios (Nombre o Identificador)',
                        'data' => $row
                    ];
                    continue;
                }

                // ✅ Construir metadata con campos extras del archivo
                $newMetadata = [];
                foreach ($extraFieldMapping as $fieldName => $columnIndex) {
                    $value = $this->getMappedValueFromColumn($row, $columnIndex);
                    if ($value !== null && $value !== '') {
                        $newMetadata[$fieldName] = $value;
                    }
                }

                // ✅ BUSCAR POR IDENTIFICADOR (clave única)
                $existingSensor = Sensor::where('identifier', $identifier)
                    ->where('group_id', $request->group_id)
                    ->first();

                if ($existingSensor) {
                    // ✅ ACTUALIZAR sensor existente (SOLO campos que vienen en el archivo)
                    $updateData = [];
                    
                    // ✅ Actualizar 'name' solo si viene en el mapeo
                    if (isset($fieldMapping['name']) && $fieldMapping['name'] !== '') {
                        $updateData['name'] = $name;
                    }
                    
                    // ✅ Actualizar 'description' solo si viene en el mapeo
                    if (isset($fieldMapping['description']) && $fieldMapping['description'] !== '') {
                        $updateData['description'] = $description ?? null;
                    }
                    
                    // ✅ Manejar metadata (campos extras)
                    $currentMetadata = $existingSensor->metadata ?? [];
                    if (!is_array($currentMetadata)) {
                        $currentMetadata = json_decode($currentMetadata, true) ?? [];
                    }
                    
                    // ✅ Si hay campos extras en el archivo, FUSIONAR (no sobrescribir todo)
                    if (!empty($newMetadata)) {
                        // ✅ Fusionar: mantener campos existentes + actualizar/agregar nuevos
                        $updateData['metadata'] = array_merge($currentMetadata, $newMetadata);
                    } else {
                        // ✅ Si NO hay campos extras en el archivo, mantener los existentes
                        // (No actualizar metadata)
                    }
                    
                    // ✅ Solo actualizar si hay cambios
                    if (!empty($updateData)) {
                        $updateData['updated_at'] = Carbon::now();
                        $existingSensor->update($updateData);
                        $updatedCount++;
                        
                        Log::info('🔄 Sensor actualizado', [
                            'identifier' => $identifier,
                            'name' => $name,
                            'group_id' => $request->group_id,
                            'fields_updated' => array_keys($updateData)
                        ]);
                    } else {
                        // ✅ No hubo cambios, pero contamos como éxito
                        $updatedCount++;
                    }
                } else {
                    // ✅ CREAR nuevo sensor
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
                    'row' => $index + 1,
                    'error' => $e->getMessage(),
                    'data' => $row
                ];
            }
        }

        // ✅ Insertar nuevos sensores en lote
        if (!empty($sensorsToCreate)) {
            DB::beginTransaction();
            try {
                $chunks = array_chunk($sensorsToCreate, 100);
                foreach ($chunks as $chunk) {
                    Sensor::insert($chunk);
                }
                DB::commit();

                Log::info('✅ Nuevos sensores creados', [
                    'user_id' => $user->id,
                    'group_id' => $request->group_id,
                    'count' => count($sensorsToCreate)
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('❌ Error al importar sensores en lote', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
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