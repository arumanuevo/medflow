# Propuesta de Mejoras para la Vista de Mediciones

## Contexto
Actualmente, en la tabla de mediciones totales se muestran los campos básicos de las mediciones pero **no se muestran los campos relacionados al sensor**, como:
- Nombre de grupo
- Número de identificador del sensor
- Key/identificadores de sensores
- Campos extras personalizados de la plantilla

## Solución Propuesta

### 1. Cambios en el Backend (MeasurementController.php)

**Archivo**: `app/Http/Controllers/Api/MeasurementController.php`
**Método**: `index()` (alrededor de la línea 230)

**Cambio**: Mejorar la información del sensor que se incluye en cada medición.

```php
// ACTUAL (simplificado):
$measurement->sensor_extra_fields = $sensorExtraData;

// PROPUESTO:
// 1. Obtener campos de la plantilla
$templateFields = [];
if ($sensor->group && $sensor->group->template) {
    $template = $sensor->group->template;
    if (isset($template->schema['campos'])) {
        foreach ($template->schema['campos'] as $campo) {
            $fieldName = $campo['nombre'] ?? '';
            $fieldLabel = $campo['etiqueta'] ?? $campo['label'] ?? $fieldName;
            if ($fieldName && $fieldName !== 'valor') {
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

// 2. Combinar todos los campos
$allExtraFields = array_merge($sensorExtraData, $templateFields);
$measurement->sensor_extra_fields = $allExtraFields;

// 3. Añadir información básica del sensor directamente
$measurement->sensor_identifier = $sensor->identifier;
$measurement->sensor_name = $sensor->name;
$measurement->group_name = $sensor->group ? $sensor->group->name : null;
$measurement->group_id = $sensor->group ? $sensor->group->id : null;
$measurement->template_type = $sensor->group && $sensor->group->template ? $sensor->group->template->type : null;
```

### 2. Cambios en el Frontend (measurements/index.blade.php)

**Archivo**: `resources/views/measurements/index.blade.php`

**Cambios propuestos**:

#### a. Modificar la tabla para mostrar más columnas

```html
<!-- ACTUAL:
<tr>
    <td>
        <div class="sensor-info">${sensorDisplay}</div>
    </td>
    <td>${sensor.group?.name || 'Sin grupo'}</td>
    <td>${value} ${unit}</td>
    ...
</tr>
-->

<!-- PROPUESTO:
<tr>
    <td>
        <div class="sensor-info">
            <span class="sensor-name">${sensorName}</span>
            ${sensorIdentifier ? `<span class="sensor-identifier">#${sensorIdentifier}</span>` : ''}
            ${groupName ? `<span class="sensor-group badge bg-info">${groupName}</span>` : ''}
        </div>
    </td>
    <td>${value} ${unit}</td>
    <td>${templateType ? templateTypeLabels[templateType] : 'N/A'}</td>
    <td>${date}</td>
    ...
</tr>
-->
```

#### b. Añadir columna para campos extra del sensor

```javascript
// En la función renderMeasurements():
function renderMeasurements(measurements) {
    // ... código existente ...
    
    measurements.forEach(measurement => {
        // ... código existente ...
        
        // Obtener información del sensor
        const sensor = measurement.sensor || {};
        const sensorName = sensor.name || 'N/A';
        const sensorIdentifier = measurement.sensor_identifier || sensor.identifier || '';
        const groupName = measurement.group_name || (sensor.group?.name || 'Sin grupo');
        const templateType = measurement.template_type || '';
        
        // Obtener campos extra del sensor
        const extraFields = measurement.sensor_extra_fields || {};
        const extraFieldsHtml = [];
        
        // Mostrar campos extra en tooltip o en una celda adicional
        Object.entries(extraFields).forEach(([key, value]) => {
            if (typeof value === 'object' && value !== null) {
                // Campo de plantilla con estructura
                if (value.label) {
                    extraFieldsHtml.push(`${value.label}: ${value.value}`);
                } else {
                    extraFieldsHtml.push(`${key}: ${value.value || value}`);
                }
            } else if (value !== null && value !== '') {
                extraFieldsHtml.push(`${key}: ${value}`);
            }
        });
        
        const extraInfo = extraFieldsHtml.length > 0 ? 
            `<span class="extra-fields-tooltip" title="${extraFieldsHtml.join(' | ')}">
                <i class="bi bi-info-circle"></i> ${extraFieldsHtml.length} campos
            </span>` : '';
        
        // Modificar la fila de la tabla
        html += `
            <tr class="${status === 'valid' ? '' : 'table-warning'}">
                <td>
                    <div class="sensor-info">
                        <span class="sensor-name">${sensorName}</span>
                        ${sensorIdentifier ? `<span class="sensor-identifier">#${sensorIdentifier}</span>` : ''}
                        ${extraInfo}
                    </div>
                </td>
                <td>${groupName}</td>
                <td>${value} ${unit}</td>
                <td>${templateType ? getTemplateLabel(templateType) : 'N/A'}</td>
                <td>${date}</td>
                ...
            </tr>
        `;
    });
}

// Función para obtener etiqueta de tipo de plantilla
function getTemplateLabel(type) {
    const labels = {
        'agua': 'Agua',
        'gas': 'Gas',
        'electricidad': 'Electricidad',
        'temperatura': 'Temperatura',
        'presion': 'Presión',
        'caudal': 'Caudal',
        'luz': 'Luz',
        'personalizado': 'Personalizado'
    };
    return labels[type] || type;
}
```

### 3. Mejoras en la Importación Masiva

**Archivo**: `resources/views/measurements/bulk-import.blade.php`

**Cambios propuestos**:

#### a. Mejorar el selector de identificación de sensores

```javascript
// En la función loadSensorFields():
function loadSensorFields(groupId) {
    // ... código existente ...
    
    // Añadir campos de la plantilla
    if (response.data.template && response.data.template.schema) {
        const templateFields = response.data.template.schema.campos || [];
        templateFields.forEach(field => {
            if (field.nombre && field.nombre !== 'valor' && 
                !['tipo', 'foto', 'observaciones'].includes(field.nombre)) {
                sensorFields.push({
                    value: 'metadata_' + field.nombre,
                    label: field.etiqueta || field.label || field.nombre,
                    description: 'Campo de plantilla: ' + (field.descripcion || field.nombre)
                });
            }
        });
    }
    
    populateIdentificationMethod(sensorFields, 'identifier');
}
```

#### b. Mostrar información del sensor en el preview

```javascript
// En la función generatePreview():
function generatePreview() {
    // ... código existente ...
    
    rowsToProcess.forEach((row, index) => {
        // ... código existente ...
        
        // Añadir información del sensor encontrado
        let sensorInfo = '';
        if (foundSensor) {
            sensorInfo = `
                <small class="text-muted d-block">
                    ID: ${foundSensor.id} | Grupo: ${foundSensor.group_name || 'N/A'}
                    ${foundSensor.identifier ? `| Código: ${foundSensor.identifier}` : ''}
                </small>
            `;
        }
        
        html += `
            <tr class="${isValid ? '' : 'table-danger'}">
                <td>${measurement.index}</td>
                <td>
                    <strong>${measurement.sensor || 'N/A'}</strong>
                    ${sensorInfo}
                </td>
                ...
            </tr>
        `;
    });
}
```

### 4. Mejoras en el Controlador de Importación Masiva

**Archivo**: `app/Http/Controllers/Api/BulkMeasurementImportController.php`

**Cambios propuestos**:

#### a. Mejorar la función getSensorFields()

```php
public function getSensorFields(Request $request, $groupId)
{
    // ... código existente ...
    
    // Obtener la plantilla del grupo
    $template = $group->template;
    if ($template && isset($template->schema['campos'])) {
        foreach ($template->schema['campos'] as $campo) {
            $fieldName = $campo['nombre'] ?? '';
            $fieldLabel = $campo['etiqueta'] ?? $campo['label'] ?? $fieldName;
            
            if ($fieldName && $fieldName !== 'valor' && 
                !in_array($fieldName, ['tipo', 'foto', 'observaciones', 'name', 'identifier', 'id'])) {
                
                $fields[] = [
                    'value' => 'metadata_' . $fieldName,
                    'label' => 'Campo: ' . ucfirst(str_replace('_', ' ', $fieldName)),
                    'description' => $fieldLabel . ' (' . ($campo['tipo'] ?? 'texto') . ')'
                ];
            }
        }
    }
    
    // ... resto del código ...
}
```

## Beneficios de la Implementación

### Para la vista de mediciones:
1. **Información completa**: Los usuarios verán el nombre del grupo, identificador del sensor y campos personalizados
2. **Mejor UX**: Tooltips con información adicional sin saturar la tabla
3. **Consistencia**: La información está disponible en la API para cualquier consumo
4. **Flexibilidad**: Los campos personalizados de la plantilla se muestran automáticamente

### Para la importación masiva:
1. **Mejor identificación**: Los usuarios pueden identificar sensores por cualquier campo de la plantilla
2. **Preview mejorado**: Se muestra información completa del sensor en el preview
3. **Menos errores**: Validación más clara cuando un sensor no se encuentra

## Implementación Recomendada

1. **Crear un nuevo branch**: `vibe/enhance-measurements-sensor-fields`
2. **Aplicar cambios en el backend primero**: Modificar `MeasurementController.php`
3. **Actualizar el frontend**: Modificar `measurements/index.blade.php`
4. **Mejorar la importación**: Modificar `BulkMeasurementImportController.php` y `bulk-import.blade.php`
5. **Probar**: Verificar que todos los datos se muestren correctamente

## Ejemplo de Respuesta de la API (después de los cambios)

```json
{
    "success": true,
    "data": [
        {
            "id": 123,
            "sensor_id": 456,
            "measured_at": "2024-01-15T10:00:00Z",
            "data": {
                "valor": 150.5,
                "tipo": "agua",
                "foto": "measurements/foto1.jpg"
            },
            "consumption": 50.5,
            "error_type": "valid",
            "sensor": {
                "id": 456,
                "name": "Sensor de Agua Principal",
                "identifier": "AGUA_001",
                "description": "Medidor de agua en planta baja"
            },
            "sensor_identifier": "AGUA_001",
            "sensor_name": "Sensor de Agua Principal",
            "sensor_description": "Medidor de agua en planta baja",
            "group_name": "Sensores de Agua",
            "group_id": 789,
            "template_type": "agua",
            "template_name": "Plantilla de Agua",
            "sensor_extra_fields": {
                "numero_lote": "LOTE_2024_001",
                "codigo_medidor": "M001",
                "marca": "MarcaX",
                "modelo": "ModeloY",
                "ubicacion": {
                    "value": "Planta Baja",
                    "label": "Ubicación",
                    "type": "texto"
                }
            }
        }
    ]
}
```

## Notas Adicionales

1. **Rendimiento**: Los cambios en el backend añaden algunas consultas adicionales, pero como ya se está cargando el sensor con `with(['sensor', 'sensor.group', 'sensor.group.template'])`, el impacto debería ser mínimo.

2. **Compatibilidad**: Los cambios son aditivos, por lo que no deberían romper el funcionamiento actual.

3. **Seguridad**: No se exponen datos sensibles, solo información que el usuario ya tiene acceso.

4. **Pruebas**: Se recomienda probar con:
   - Sensores con y sin plantilla
   - Plantillas con campos personalizados
   - Sensores con metadata
   - Grupos compartidos

## Archivos a Modificar

1. `app/Http/Controllers/Api/MeasurementController.php` - Backend
2. `resources/views/measurements/index.blade.php` - Frontend tabla de mediciones
3. `app/Http/Controllers/Api/BulkMeasurementImportController.php` - Importación masiva backend
4. `resources/views/measurements/bulk-import.blade.php` - Importación masiva frontend
