<?php

$filePath = __DIR__ . '/app/Http/Controllers/Api/MeasurementController.php';
$content = file_get_contents($filePath);

// Buscar la sección que queremos modificar
$oldCode = <<<'OLDCODE'
        // A\u0000ADIR CAMPOS EXTRA DEL SENSOR
        $sensor = $measurement->sensor;
        if ($sensor) {
            // Obtener campos extra del sensor (metadata)
            $extraFields = $sensor->metadata ?? [];
            
            // Tambien podemos obtener campos especificos si existen como columnas
            $sensorExtraData = [
                'numero_lote' => $sensor->numero_lote ?? $extraFields['numero_lote'] ?? null,
                'codigo_medidor' => $sensor->codigo_medidor ?? $extraFields['codigo_medidor'] ?? null,
                'numero_serie' => $sensor->numero_serie ?? $extraFields['numero_serie'] ?? null,
                'marca' => $sensor->marca ?? $extraFields['marca'] ?? null,
                'modelo' => $sensor->modelo ?? $extraFields['modelo'] ?? null,
                // Agregar cualquier otro campo extra que pueda existir
            ];
            
            // Filtrar campos nulos
            $sensorExtraData = array_filter($sensorExtraData, function($value) {
                return $value !== null && $value !== '';
            });
            
            $measurement->sensor_extra_fields = $sensorExtraData;
        }
OLDCODE;

$newCode = <<<'NEWCODE'
        // A\u0000ADIR CAMPOS EXTRA DEL SENSOR
        $sensor = $measurement->sensor;
        if ($sensor) {
            // Obtener campos extra del sensor (metadata)
            $extraFields = $sensor->metadata ?? [];
            
            // Obtener campos de la plantilla si existe
            $templateFields = [];
            if ($sensor->group && $sensor->group->template) {
                $template = $sensor->group->template;
                if (isset($template->schema['campos'])) {
                    foreach ($template->schema['campos'] as $campo) {
                        $fieldName = $campo['nombre'] ?? '';
                        $fieldLabel = $campo['etiqueta'] ?? $campo['label'] ?? $fieldName;
                        if ($fieldName && $fieldName !== 'valor' && !in_array($fieldName, ['tipo', 'foto', 'observaciones'])) {
                            // Buscar el valor en metadata o en los datos de la medicion
                            $fieldValue = $extraFields[$fieldName] ?? null;
                            if ($fieldValue !== null && $fieldValue !== '') {
                                $templateFields[$fieldName] = [
                                    'value' => $fieldValue,
                                    'label' => $fieldLabel,
                                    'type' => $campo['tipo'] ?? 'texto'
                                ];
                            }
                        }
                    }
                }
            }
            
            // Tambien podemos obtener campos especificos si existen como columnas
            $sensorExtraData = [
                'numero_lote' => $sensor->numero_lote ?? $extraFields['numero_lote'] ?? null,
                'codigo_medidor' => $sensor->codigo_medidor ?? $extraFields['codigo_medidor'] ?? null,
                'numero_serie' => $sensor->numero_serie ?? $extraFields['numero_serie'] ?? null,
                'marca' => $sensor->marca ?? $extraFields['marca'] ?? null,
                'modelo' => $sensor->modelo ?? $extraFields['modelo'] ?? null,
            ];
            
            // Filtrar campos nulos
            $sensorExtraData = array_filter($sensorExtraData, function($value) {
                return $value !== null && $value !== '';
            });
            
            // Combinar todos los campos extra
            $allExtraFields = array_merge($sensorExtraData, $templateFields);
            
            $measurement->sensor_extra_fields = $allExtraFields;
            
            // Anadir informacion basica del sensor directamente en el objeto
            $measurement->sensor_identifier = $sensor->identifier;
            $measurement->sensor_name = $sensor->name;
            $measurement->sensor_description = $sensor->description;
            $measurement->group_name = $sensor->group ? $sensor->group->name : null;
            $measurement->group_id = $sensor->group ? $sensor->group->id : null;
            $measurement->template_type = $sensor->group && $sensor->group->template ? $sensor->group->template->type : null;
            $measurement->template_name = $sensor->group && $sensor->group->template ? $sensor->group->template->name : null;
        }
NEWCODE;

// Reemplazar el código
$newContent = str_replace($oldCode, $newCode, $content);

if ($newContent !== $content) {
    file_put_contents($filePath, $newContent);
    echo "Modificación aplicada con éxito!\n";
} else {
    echo "No se encontró el código a reemplazar. Buscando patrón alternativo...\n";
    
    // Intentar con otro patrón
    $oldCode2 = <<<'OLDCODE2'
        // A\u0019A\u0005ADIR CAMPOS EXTRA DEL SENSOR
        $sensor = $measurement->sensor;
        if ($sensor) {
            // Obtener campos extra del sensor (metadata)
            $extraFields = $sensor->metadata ?? [];
            
            // Tambi\u0019n podemos obtener campos espec\u0013ficos si existen como columnas
            $sensorExtraData = [
                'numero_lote' => $sensor->numero_lote ?? $extraFields['numero_lote'] ?? null,
                'codigo_medidor' => $sensor->codigo_medidor ?? $extraFields['codigo_medidor'] ?? null,
                'numero_serie' => $sensor->numero_serie ?? $extraFields['numero_serie'] ?? null,
                'marca' => $sensor->marca ?? $extraFields['marca'] ?? null,
                'modelo' => $sensor->modelo ?? $extraFields['modelo'] ?? null,
                // Agregar cualquier otro campo extra que pueda existir
            ];
            
            // Filtrar campos nulos
            $sensorExtraData = array_filter($sensorExtraData, function($value) {
                return $value !== null && $value !== '';
            });
            
            $measurement->sensor_extra_fields = $sensorExtraData;
        }
OLDCODE2;

    $newCode2 = <<<'NEWCODE2'
        // A\u0019A\u0005ADIR CAMPOS EXTRA DEL SENSOR
        $sensor = $measurement->sensor;
        if ($sensor) {
            // Obtener campos extra del sensor (metadata)
            $extraFields = $sensor->metadata ?? [];
            
            // Obtener campos de la plantilla si existe
            $templateFields = [];
            if ($sensor->group && $sensor->group->template) {
                $template = $sensor->group->template;
                if (isset($template->schema['campos'])) {
                    foreach ($template->schema['campos'] as $campo) {
                        $fieldName = $campo['nombre'] ?? '';
                        $fieldLabel = $campo['etiqueta'] ?? $campo['label'] ?? $fieldName;
                        if ($fieldName && $fieldName !== 'valor' && !in_array($fieldName, ['tipo', 'foto', 'observaciones'])) {
                            // Buscar el valor en metadata o en los datos de la medicion
                            $fieldValue = $extraFields[$fieldName] ?? null;
                            if ($fieldValue !== null && $fieldValue !== '') {
                                $templateFields[$fieldName] = [
                                    'value' => $fieldValue,
                                    'label' => $fieldLabel,
                                    'type' => $campo['tipo'] ?? 'texto'
                                ];
                            }
                        }
                    }
                }
            }
            
            // Tambi\u0019n podemos obtener campos espec\u0013ficos si existen como columnas
            $sensorExtraData = [
                'numero_lote' => $sensor->numero_lote ?? $extraFields['numero_lote'] ?? null,
                'codigo_medidor' => $sensor->codigo_medidor ?? $extraFields['codigo_medidor'] ?? null,
                'numero_serie' => $sensor->numero_serie ?? $extraFields['numero_serie'] ?? null,
                'marca' => $sensor->marca ?? $extraFields['marca'] ?? null,
                'modelo' => $sensor->modelo ?? $extraFields['modelo'] ?? null,
            ];
            
            // Filtrar campos nulos
            $sensorExtraData = array_filter($sensorExtraData, function($value) {
                return $value !== null && $value !== '';
            });
            
            // Combinar todos los campos extra
            $allExtraFields = array_merge($sensorExtraData, $templateFields);
            
            $measurement->sensor_extra_fields = $allExtraFields;
            
            // Anadir informacion basica del sensor directamente en el objeto
            $measurement->sensor_identifier = $sensor->identifier;
            $measurement->sensor_name = $sensor->name;
            $measurement->sensor_description = $sensor->description;
            $measurement->group_name = $sensor->group ? $sensor->group->name : null;
            $measurement->group_id = $sensor->group ? $sensor->group->id : null;
            $measurement->template_type = $sensor->group && $sensor->group->template ? $sensor->group->template->type : null;
            $measurement->template_name = $sensor->group && $sensor->group->template ? $sensor->group->template->name : null;
        }
NEWCODE2;

    $newContent = str_replace($oldCode2, $newCode2, $content);
    
    if ($newContent !== $content) {
        file_put_contents($filePath, $newContent);
        echo "Modificación aplicada con éxito usando patrón alternativo!\n";
    } else {
        echo "No se pudo encontrar el patrón a reemplazar.\n";
        echo "Buscando línea con 'A\\u0019A\\u0005ADIR CAMPOS EXTRA'...\n";
        
        // Buscar la línea específica
        if (strpos($content, 'A\u0019A\u0005ADIR CAMPOS EXTRA') !== false) {
            echo "Encontrado con codificación especial.\n";
        } elseif (strpos($content, 'AÑADIR CAMPOS EXTRA') !== false) {
            echo "Encontrado 'AÑADIR CAMPOS EXTRA'\n";
        } elseif (strpos($content, 'sensor_extra_fields') !== false) {
            echo "Encontrado 'sensor_extra_fields' en el archivo.\n";
            
            // Buscar la sección manualmente
            $lines = explode("\n", $content);
            foreach ($lines as $i => $line) {
                if (strpos($line, 'sensor_extra_fields') !== false) {
                    echo "Línea $i: $line\n";
                    // Mostrar contexto
                    for ($j = max(0, $i-5); $j <= min(count($lines)-1, $i+5); $j++) {
                        echo "  $j: " . $lines[$j] . "\n";
                    }
                    break;
                }
            }
        }
    }
}
