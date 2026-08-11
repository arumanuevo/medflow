{{-- resources/views/measurements/bulk-import.blade.php --}}
@extends('layouts.modern')

@section('title', 'Importar Mediciones Masivamente - MedFlow')

@push('styles')
<style>
.bulk-import-container .step {
    animation: fadeIn 0.3s ease;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.drop-zone {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
}
.drop-zone:hover {
    border-color: #0d6efd;
    background: #e8f4fd;
}
.drop-zone.active {
    border-color: #0d6efd;
    background: #e8f4fd;
}
.drop-zone i {
    font-size: 3rem;
    color: #6c757d;
    margin-bottom: 1rem;
}
.field-mapping .row {
    padding: 0.5rem 0;
    border-bottom: 1px solid #f0f0f0;
}
.field-mapping .row:last-child {
    border-bottom: none;
}
.preview-table {
    max-height: 400px;
    overflow-y: auto;
}
.preview-table .table {
    font-size: 0.85rem;
}
.preview-table .table th {
    position: sticky;
    top: 0;
    background: #f8f9fa;
    z-index: 10;
}
.import-summary ul {
    list-style: none;
    padding-left: 0;
}
.import-summary ul li {
    padding: 0.25rem 0;
}
.import-summary ul li strong {
    display: inline-block;
    min-width: 140px;
}
.error-container ul {
    max-height: 200px;
    overflow-y: auto;
    padding-left: 1.5rem;
}
.error-container ul li {
    margin-bottom: 0.5rem;
}
.error-container ul li ul {
    padding-left: 1.5rem;
    margin-top: 0.25rem;
}
.progress-container {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-top: 1rem;
}
.progress-container .progress {
    height: 20px;
}
.sample-sensor-info {
    background: #e8f4fd;
    border-left: 4px solid #0d6efd;
    padding: 0.75rem 1rem;
    border-radius: 4px;
    font-size: 0.85rem;
}
.sample-sensor-info code {
    background: #fff;
    padding: 0.1rem 0.3rem;
    border-radius: 3px;
}
.identification-method-selector {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    border: 1px solid #e9ecef;
}
</style>
@endpush

@section('content')
<div class="container-fluid bulk-import-container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h4><i class="bi bi-file-earmark-excel"></i> Importar Mediciones Masivamente</h4>
                    <a href="{{ route('measurements.index') }}" class="btn btn-light">
                        <i class="bi bi-arrow-left"></i> Volver a Mediciones
                    </a>
                </div>
                <div class="card-body">
                    <!-- Alertas -->
                    <div id="alertContainer"></div>

                    <!-- Paso 1: Seleccionar Grupo y Archivo -->
                    <div id="step1" class="step">
                        <h5><i class="bi bi-upload"></i> Paso 1: Seleccionar Grupo y Subir Archivo</h5>
                        <p class="text-muted">Selecciona el grupo de sensores y sube un archivo <strong>.xlsx</strong> o <strong>.csv</strong> con las mediciones.</p>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Instrucciones para el archivo:</strong>
                            <ul class="mb-0 mt-2">
                                <li>El archivo debe contener una columna con el <strong>identificador del sensor</strong> (nombre, código o ID).</li>
                                <li>Los valores deben ser <strong>números</strong> (enteros o decimales).</li>
                                <li>Las fechas deben estar en formato <strong>YYYY-MM-DD</strong> o <strong>DD/MM/YYYY</strong>.</li>
                                <li>Puedes importar mediciones para <strong>múltiples sensores</strong> en el mismo archivo.</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Validaciones del sistema:</strong>
                            <ul class="mb-0 mt-2">
                                <li>✅ Verifica que el <strong>sensor exista</strong> en el grupo seleccionado.</li>
                                <li>✅ Valida que no haya <strong>fechas duplicadas</strong> para un mismo sensor.</li>
                                <li>✅ Las mediciones pueden insertarse en <strong>cualquier punto</strong> de la línea temporal.</li>
                                <li>✅ El <strong>valor</strong> debe ser coherente con las mediciones vecinas.</li>
                            </ul>
                        </div>

                        <!-- Selector de grupo -->
                        <div class="mb-3">
                            <label for="groupSelect" class="form-label">Grupo Destino <span class="text-danger">*</span></label>
                            <select class="form-select" id="groupSelect" required>
                                <option value="" selected disabled>Cargando grupos...</option>
                            </select>
                            <small class="text-muted">Se muestran los grupos que tienen al menos un sensor</small>
                        </div>

                        <!-- Información del grupo seleccionado -->
                        <div id="groupInfo" class="alert alert-secondary d-none">
                            <i class="bi bi-info-circle"></i>
                            <span id="groupInfoText">Sensores disponibles en este grupo: <strong id="sensorCount">0</strong></span>
                        </div>

                        <!-- Área de subida de archivos -->
                        <div class="drop-zone" id="dropZone">
                            <i class="bi bi-file-earmark-excel"></i>
                            <p><strong>Arrastra y suelta el archivo aquí</strong> o haz clic para seleccionarlo</p>
                            <p class="text-muted small">(Solo archivos .xlsx o .csv)</p>
                            <input type="file" id="fileInput" accept=".xlsx,.csv" style="display: none;">
                        </div>

                        <div class="d-flex gap-2 mt-3 flex-wrap">
                            <button class="btn btn-secondary" id="selectFileBtn">
                                <i class="bi bi-folder-open"></i> Seleccionar Archivo
                            </button>
                            <button class="btn btn-info" id="downloadTemplateBtn">
                                <i class="bi bi-download"></i> Descargar Plantilla (.csv)
                            </button>
                        </div>
                    </div>

                    <!-- Paso 2: Mapear Campos -->
                    <div id="step2" class="step d-none">
                        <hr>
                        <h5><i class="bi bi-arrow-left-right"></i> Paso 2: Mapear Campos</h5>
                        <p class="text-muted">Asocia los campos de tu archivo con los campos del sistema.</p>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Campos obligatorios:</strong>
                            <ul class="mb-0 mt-2">
                                <li><strong>Identificador del Sensor</strong> - Nombre, código o ID del sensor</li>
                                <li><strong>Valor</strong> - Valor numérico de la medición</li>
                                <li><strong>Fecha</strong> - Fecha de la medición (YYYY-MM-DD o DD/MM/YYYY)</li>
                            </ul>
                        </div>

                        <div id="fieldMappingContainer" class="field-mapping">
                            <!-- Los campos de mapeo se generarán dinámicamente -->
                        </div>
                    </div>

                    <!-- Paso 3: Preview y Confirmación -->
                    <div id="step3" class="step d-none">
                        <hr>
                        <h5><i class="bi bi-eye"></i> Paso 3: Preview y Confirmación</h5>
                        <p class="text-muted">Revisa los datos antes de importar. Todas las validaciones se aplicarán en el servidor.</p>

                        <!-- Opción de sobrescritura -->
                        <div class="alert alert-info">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="overwriteDuplicates">
                                <label class="form-check-label" for="overwriteDuplicates">
                                    <strong>Permitir sobrescritura de duplicados</strong>
                                    <span class="text-muted d-block small">Si se encuentra un registro con la misma fecha y valor, se eliminará el existente y se importará el nuevo.</span>
                                </label>
                            </div>
                        </div>

                        <div class="table-responsive preview-table">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Sensor</th>
                                        <th>Valor</th>
                                        <th>Fecha</th>
                                        <th>Foto</th>
                                        <th>Observaciones</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="previewTableBody">
                                    <!-- Datos generados dinámicamente -->
                                </tbody>
                            </table>
                        </div>

                        <div id="importSummary" class="import-summary d-none">
                            <h5><i class="bi bi-info-circle"></i> Resumen de Importación</h5>
                            <ul>
                                <li><strong>Total de registros:</strong> <span id="totalRecords">0</span></li>
                                <li><strong>Registros válidos:</strong> <span id="validRecords">0</span></li>
                                <li><strong>Registros con errores:</strong> <span id="errorRecords">0</span></li>
                            </ul>
                        </div>

                        <div id="importErrors" class="error-container d-none">
                            <h5><i class="bi bi-exclamation-triangle"></i> Errores Encontrados</h5>
                            <ul id="errorList"></ul>
                        </div>
                    </div>

                    <!-- Paso 4: Resultados de la Importación -->
                    <div id="step4" class="step d-none">
                        <hr>
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h5><i class="bi bi-check-circle"></i> Resultados de la Importación</h5>
                            </div>
                            <div class="card-body">
                                <!-- Resumen -->
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <div class="card text-center">
                                            <div class="card-body">
                                                <h6 class="text-muted">Total Registros</h6>
                                                <h3 id="resultTotal">0</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card text-center border-success">
                                            <div class="card-body">
                                                <h6 class="text-muted">Importados</h6>
                                                <h3 class="text-success" id="resultSuccess">0</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card text-center border-danger">
                                            <div class="card-body">
                                                <h6 class="text-muted">Errores</h6>
                                                <h3 class="text-danger" id="resultErrors">0</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card text-center border-warning">
                                            <div class="card-body">
                                                <h6 class="text-muted">Sobrescritos</h6>
                                                <h3 class="text-warning" id="resultOverwritten">0</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Detalle de resultados -->
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Sensor</th>
                                                <th>Valor</th>
                                                <th>Fecha</th>
                                                <th>Estado</th>
                                                <th>Mensaje</th>
                                            </tr>
                                        </thead>
                                        <tbody id="resultTableBody">
                                            <!-- Datos del informe -->
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Botones de acción -->
                                <div class="d-flex gap-2 mt-3 flex-wrap">
                                    <button class="btn btn-success" id="downloadReportBtn">
                                        <i class="bi bi-download"></i> Descargar Informe (CSV)
                                    </button>
                                    <button class="btn btn-primary" id="viewSensorsBtn">
                                        <i class="bi bi-eye"></i> Ver Sensores
                                    </button>
                                    <button class="btn btn-secondary" id="newImportBtn">
                                        <i class="bi bi-plus-circle"></i> Nueva Importación
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de navegación -->
                    <div class="d-flex justify-content-between mt-4">
                        <button class="btn btn-secondary" id="prevStepBtn" style="display: none;">
                            <i class="bi bi-arrow-left"></i> Anterior
                        </button>
                        <button class="btn btn-primary" id="nextStepBtn" style="display: none;">
                            <i class="bi bi-arrow-right"></i> Siguiente
                        </button>
                        <button class="btn btn-success" id="importBtn" style="display: none;">
                            <i class="bi bi-check-circle"></i> Importar Mediciones
                        </button>
                        <button class="btn btn-danger" id="cancelBtn">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    </div>

                    <!-- Barra de progreso -->
                    <div id="progressContainer" class="progress-container d-none">
                        <h5><i class="bi bi-hourglass-split"></i> Importando...</h5>
                        <div class="progress">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                        </div>
                        <p id="progressText" class="mt-2">Preparando importación...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle"></i> Confirmar Cancelación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas cancelar la importación? Todos los datos no guardados se perderán.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> No, Continuar
                </button>
                <button type="button" class="btn btn-danger" id="confirmCancel">
                    <i class="bi bi-check-circle"></i> Sí, Cancelar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// =============================================
// CONFIGURACIÓN GLOBAL
// =============================================
let excelHeaders = [];
let sampleData = [];
let allData = [];
let fieldMapping = {};
let currentStep = 1;
let selectedGroupId = null;
let file = null;
let groups = [];
let sensorsInGroup = [];
let currentReportId = null;
let sensorFields = [];

$(document).ready(function() {
    loadGroups();

    $('#selectFileBtn').click(() => $('#fileInput').click());
    $('#fileInput').change(handleFileSelect);
    $('#dropZone').click(() => $('#fileInput').click());

    $('#dropZone').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('active');
    });

    $('#dropZone').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('active');
    });

    $('#dropZone').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('active');
        if (e.originalEvent.dataTransfer.files.length) {
            $('#fileInput').prop('files', e.originalEvent.dataTransfer.files);
            handleFileSelect();
        }
    });

    $('#nextStepBtn').click(nextStep);
    $('#prevStepBtn').click(prevStep);
    $('#importBtn').click(importMeasurements);
    $('#cancelBtn').click(() => $('#cancelModal').modal('show'));
    $('#confirmCancel').click(() => window.location.href = '{{ route("measurements.index") }}');

    $('#downloadTemplateBtn').click(function() {
        const token = localStorage.getItem('token');
        window.location.href = '/api/measurements/bulk-import/download-template?token=' + token;
    });

    $('#downloadReportBtn').click(downloadReport);
    $('#newImportBtn').click(startNewImport);
    $('#viewSensorsBtn').click(function() {
        window.location.href = '/sensors';
    });

    $('#groupSelect').change(function() {
        selectedGroupId = $(this).val();
        if (selectedGroupId) {
            loadSensorsByGroup(selectedGroupId);
        } else {
            $('#groupInfo').addClass('d-none');
        }
    });
});

// =============================================
// FUNCIONES DE CARGA
// =============================================

function loadGroups() {
    const token = localStorage.getItem('token');
    
    if (!token) {
        showAlert('No se encontró token de autenticación. Por favor, inicia sesión nuevamente.', 'danger');
        return;
    }

    const select = $('#groupSelect');
    select.html('<option value="" disabled>Cargando grupos...</option>');
    select.prop('disabled', true);

    $.ajax({
        url: '/api/measurements/bulk-import/groups',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                groups = response.data;
                select.empty();
                select.append('<option value="" selected disabled>Selecciona un grupo...</option>');
                select.prop('disabled', false);
                
                if (groups.length === 0) {
                    select.append('<option value="" disabled>No tienes grupos con sensores</option>');
                    showAlert('No tienes grupos con sensores disponibles. Crea un grupo y sensores primero.', 'warning');
                } else {
                    groups.forEach(group => {
                        select.append(`<option value="${group.id}">${group.name} (${group.sensor_count} sensores)</option>`);
                    });
                }
            } else {
                showAlert(response.message || 'Error al cargar los grupos', 'danger');
            }
        },
        error: function(xhr) {
            select.empty();
            select.append('<option value="" disabled>Error al cargar grupos</option>');
            select.prop('disabled', false);
            
            let errorMessage = 'Error al cargar los grupos';
            if (xhr.responseJSON?.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showAlert(errorMessage, 'danger');
            console.error('Error:', xhr);
        }
    });
}

function loadSensorsByGroup(groupId) {
    const token = localStorage.getItem('token');
    
    $.ajax({
        url: `/api/measurements/bulk-import/groups/${groupId}/sensors`,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                sensorsInGroup = response.data;
                const count = sensorsInGroup.length;
                
                const displayNames = sensorsInGroup.slice(0, 3).map(s => s.name).join(', ');
                const moreText = count > 3 ? ` y ${count - 3} más` : '';
                
                $('#groupInfo').removeClass('d-none');
                $('#sensorCount').text(count);
                $('#groupInfoText').html(
                    `Sensores disponibles en este grupo: <strong>${count}</strong> ` +
                    `(${displayNames}${moreText})`
                );
                
                loadSensorFields(groupId);
                
                if (count === 0) {
                    showAlert('El grupo seleccionado no tiene sensores. Crea sensores primero.', 'warning');
                }
            }
        },
        error: function(xhr) {
            console.error('Error al cargar sensores del grupo:', xhr);
            $('#groupInfo').removeClass('d-none');
            $('#sensorCount').text('Error');
        }
    });
}

// =============================================
// FUNCIONES DE CAMPOS DE SENSORES
// =============================================

function loadSensorFields(groupId) {
    const token = localStorage.getItem('token');
    
    console.log('🔍 Cargando campos para el grupo:', groupId);
    
    const select = $('#identificationMethod');
    
    // ✅ Si el selector no existe, esperar a que generateFieldMapping lo cree
    if (select.length === 0) {
        console.log('⏳ Selector aún no creado, será creado por generateFieldMapping');
        // Guardar el groupId para usarlo después
        window._pendingGroupId = groupId;
        return;
    }
    
    select.html('<option value="" disabled>Cargando campos...</option>');
    select.prop('disabled', true);
    
    $.ajax({
        url: `/api/measurements/bulk-import/groups/${groupId}/sensor-fields`,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json'
        },
        timeout: 30000,
        success: function(response) {
            console.log('✅ Respuesta de sensor-fields:', response);
            
            if (response.success && response.data) {
                sensorFields = response.data.fields || [];
                
                console.log('📋 Campos recibidos:', sensorFields);
                
                if (sensorFields.length === 0) {
                    console.warn('⚠️ No se recibieron campos, usando fallback');
                    useFallbackFields();
                    return;
                }
                
                const defaultMethod = response.data.default || 'identifier';
                populateIdentificationMethod(sensorFields, defaultMethod);
                select.prop('disabled', false);
                
                if (response.data.sample_sensor) {
                    const sample = response.data.sample_sensor;
                    const sensorInfoHtml = `
                        <div class="sample-sensor-info mt-2">
                            <strong><i class="bi bi-info-circle"></i> Ejemplo de sensor en este grupo:</strong>
                            <ul class="mb-0 mt-1">
                                <li><strong>Nombre:</strong> <code>${sample.name || 'N/A'}</code></li>
                                <li><strong>Identificador:</strong> <code>${sample.identifier || 'N/A'}</code></li>
                                <li><strong>ID:</strong> <code>${sample.id || 'N/A'}</code></li>
                            </ul>
                            <small class="text-muted mt-1 d-block">
                                <i class="bi bi-info-circle"></i> 
                                Usa estos campos para identificar los sensores en tu archivo.
                            </small>
                        </div>
                    `;
                    $('#sampleSensorInfo').html(sensorInfoHtml);
                }
                
                console.log('✅ Selector de identificación poblado correctamente');
                
            } else {
                console.warn('⚠️ Respuesta sin éxito:', response);
                useFallbackFields();
            }
        },
        error: function(xhr) {
            console.error('❌ Error al cargar campos de sensores:', xhr);
            useFallbackFields();
        }
    });
}

function populateIdentificationMethod(fields, defaultMethod) {
    const select = $('#identificationMethod');
    
    if (select.length === 0) {
        console.error('❌ Selector #identificationMethod no encontrado');
        return;
    }
    
    select.empty();
    
    console.log('📋 Poblando selector con campos:', fields);
    console.log('📋 Default method:', defaultMethod);
    
    if (!fields || fields.length === 0) {
        select.append('<option value="" disabled>No hay campos disponibles</option>');
        return;
    }
    
    const existsDefault = fields.some(f => f.value === defaultMethod);
    console.log(`📋 ¿Existe el método "${defaultMethod}" en los campos?`, existsDefault);
    
    const effectiveDefault = existsDefault ? defaultMethod : fields[0].value;
    console.log(`📋 Usando default efectivo: "${effectiveDefault}"`);
    
    fields.forEach(field => {
        const selected = field.value === effectiveDefault ? 'selected' : '';
        const label = field.label || field.value;
        const desc = field.description ? ` (${field.description})` : '';
        select.append(`
            <option value="${field.value}" ${selected}>
                ${label}${desc}
            </option>
        `);
    });
    
    select.val(effectiveDefault);
    
    console.log('✅ Selector poblado, valor actual:', select.val());
    console.log('✅ Número de opciones:', select.find('option').length);
}

function useFallbackFields() {
    console.log('🔄 Usando campos base (fallback)');
    sensorFields = [
        { value: 'name', label: 'Nombre del Sensor', description: 'Nombre descriptivo' },
        { value: 'identifier', label: 'Identificador (Código)', description: 'Código único' },
        { value: 'id', label: 'ID del Sensor', description: 'ID numérico' }
    ];
    populateIdentificationMethod(sensorFields, 'identifier');
    $('#identificationMethod').prop('disabled', false);
    showAlert('No se pudieron cargar los campos personalizados. Usando campos base.', 'warning');
}

// =============================================
// FUNCIONES DE PROCESAMIENTO DE ARCHIVOS
// =============================================

function processRowData(row) {
    if (typeof row === 'string') {
        if (row.includes(';')) {
            return row.split(';').map(col => col.trim().replace(/^"|"$/g, ''));
        }
        if (row.includes(',')) {
            return row.split(',').map(col => col.trim().replace(/^"|"$/g, ''));
        }
        return [row.trim()];
    }
    
    if (Array.isArray(row)) {
        if (row.length === 1 && typeof row[0] === 'string' && row[0].includes(';')) {
            return row[0].split(';').map(col => col.trim().replace(/^"|"$/g, ''));
        }
        return row;
    }
    
    return [];
}

function handleFileSelect() {
    const fileInput = $('#fileInput')[0];
    if (fileInput.files.length === 0) return;

    file = fileInput.files[0];
    const fileName = file.name.toLowerCase();
    
    if (!fileName.endsWith('.xlsx') && !fileName.endsWith('.csv')) {
        showAlert('Por favor, selecciona un archivo .xlsx o .csv', 'danger');
        return;
    }

    selectedGroupId = $('#groupSelect').val();
    if (!selectedGroupId) {
        showAlert('Por favor, selecciona un grupo destino', 'danger');
        return;
    }

    if (sensorsInGroup.length === 0) {
        showAlert('El grupo seleccionado no tiene sensores. Crea sensores primero.', 'warning');
        return;
    }

    showLoading();

    const formData = new FormData();
    formData.append('file', file);

    $.ajax({
        url: '/api/measurements/bulk-import/analyze-file',
        type: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            hideLoading();
            if (response.success) {
                let rawHeaders = response.data.headers || [];
                
                if (typeof rawHeaders === 'string') {
                    rawHeaders = rawHeaders.split(';').map(h => h.trim().replace(/^"|"$/g, ''));
                } else if (Array.isArray(rawHeaders) && rawHeaders.length === 1) {
                    const singleHeader = rawHeaders[0];
                    if (typeof singleHeader === 'string') {
                        if (singleHeader.includes(';')) {
                            rawHeaders = singleHeader.split(';').map(h => h.trim().replace(/^"|"$/g, ''));
                        } else if (singleHeader.includes(',')) {
                            rawHeaders = singleHeader.split(',').map(h => h.trim().replace(/^"|"$/g, ''));
                        }
                    }
                }
                
                excelHeaders = rawHeaders.map(header => {
                    if (typeof header === 'string') {
                        return header.trim().replace(/^"|"$/g, '').replace(/^'|'$/g, '');
                    }
                    return String(header);
                }).filter(h => h !== '');
                
                let rawData = [];
                if (response.data.all_data) {
                    rawData = response.data.all_data;
                } else if (response.data.sample_data) {
                    rawData = response.data.sample_data;
                }
                
                allData = rawData.map(row => processRowData(row));
                sampleData = allData.slice(0, 5);
                
                console.log('📊 Headers procesados:', excelHeaders);
                console.log('📊 Total de filas:', allData.length);
                console.log('📊 Primera fila procesada:', allData.length > 0 ? allData[0] : 'No hay datos');
                console.log('📊 Longitud de la primera fila:', allData.length > 0 ? allData[0].length : 0);
                
                generateFieldMapping(excelHeaders);
                showStep(2);
            } else {
                showAlert(response.message || 'Error al analizar el archivo', 'danger');
            }
        },
        error: function(xhr) {
            hideLoading();
            const errorMessage = xhr.responseJSON?.message || xhr.statusText;
            showAlert('Error al analizar el archivo: ' + errorMessage, 'danger');
            console.error('Error:', xhr);
        }
    });
}

function showLoading() {
    $('#dropZone').html(`
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Analizando archivo...</p>
        </div>
    `);
}

function hideLoading() {
    $('#dropZone').html(`
        <i class="bi bi-file-earmark-excel"></i>
        <p><strong>Arrastra y suelta el archivo aquí</strong> o haz clic para seleccionarlo</p>
        <p class="text-muted small">(Solo archivos .xlsx o .csv)</p>
        <input type="file" id="fileInput" accept=".xlsx,.csv" style="display: none;">
    `);
}

// =============================================
// FUNCIONES DE MAPEO
// =============================================

function generateFieldMapping(headers) {
    const container = $('#fieldMappingContainer');
    container.empty();

    if (!Array.isArray(headers) || headers.length === 0) {
        container.html(`
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                No se pudieron identificar las columnas del archivo. 
                Verifica que el archivo tenga encabezados.
            </div>
        `);
        return;
    }

    // ✅ CREAR EL SELECTOR DE IDENTIFICACIÓN AQUÍ (no en loadSensorFields)
    const methodRow = $(`
        <div class="row mb-3 identification-method-selector">
            <div class="col-md-12">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Identificar sensor por:</label>
                    </div>
                    <div class="col-md-5">
                        <select class="form-select" id="identificationMethod">
                            <option value="">Cargando campos...</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Selecciona qué campo usar para identificar los sensores en el grupo
                        </small>
                    </div>
                </div>
                <div id="sampleSensorInfo" class="mt-2"></div>
            </div>
        </div>
    `);
    container.append(methodRow);

    // ✅ Si ya hay campos cargados (de loadSensorFields), poblarlos
    if (sensorFields && sensorFields.length > 0) {
        populateIdentificationMethod(sensorFields, 'identifier');
    } else {
        // Si no hay campos, cargarlos
        loadSensorFields(selectedGroupId);
    }

    const measurementFields = [
        { name: 'sensor', label: 'Identificador del Sensor *', required: true },
        { name: 'valor', label: 'Valor *', required: true },
        { name: 'fecha', label: 'Fecha *', required: true },
        { name: 'foto', label: 'Foto', required: false },
        { name: 'observaciones', label: 'Observaciones', required: false }
    ];

    measurementFields.forEach(field => {
        const row = $(`
            <div class="row mb-3 align-items-center">
                <div class="col-md-3">
                    <label class="form-label fw-bold">${field.label}</label>
                    ${field.required ? ' <span class="text-danger">*</span>' : ''}
                </div>
                <div class="col-md-7">
                    <select class="form-select field-mapping-select" data-field="${field.name}">
                        <option value="">-- Seleccionar columna --</option>
                    </select>
                </div>
                <div class="col-md-2">
                    ${field.required ? '<span class="badge bg-danger">Obligatorio</span>' : '<span class="badge bg-secondary">Opcional</span>'}
                </div>
            </div>
        `);

        const select = row.find('.field-mapping-select');

        headers.forEach((header, index) => {
            if (header && header.trim() !== '') {
                select.append(`<option value="${index}">${header.trim()}</option>`);
            }
        });

        container.append(row);
    });

    $('#nextStepBtn').show();
    $('#prevStepBtn').show();
}

// =============================================
// FUNCIONES DE NAVEGACIÓN
// =============================================

function nextStep() {
    if (currentStep === 1) {
        if (!file) {
            showAlert('Por favor, selecciona un archivo primero', 'danger');
            return;
        }
        if (!selectedGroupId) {
            showAlert('Por favor, selecciona un grupo destino', 'danger');
            return;
        }
        if (sensorsInGroup.length === 0) {
            showAlert('El grupo seleccionado no tiene sensores. Crea sensores primero.', 'warning');
            return;
        }
    } else if (currentStep === 2) {
        let allRequiredMapped = true;
        const requiredFields = ['sensor', 'valor', 'fecha'];

        requiredFields.forEach(field => {
            const select = $(`.field-mapping-select[data-field="${field}"]`);
            if (!select.val()) {
                allRequiredMapped = false;
                select.addClass('is-invalid');
            } else {
                select.removeClass('is-invalid');
            }
        });

        if (!allRequiredMapped) {
            showAlert('Por favor, mapea todos los campos obligatorios (marcados con *)', 'danger');
            return;
        }

        fieldMapping = {};
        $('.field-mapping-select').each(function() {
            const fieldName = $(this).data('field');
            const columnIndex = $(this).val();
            if (columnIndex !== '') {
                fieldMapping[fieldName] = parseInt(columnIndex);
            }
        });

        const identificationMethod = $('#identificationMethod').val();
        console.log('📋 Método de identificación:', identificationMethod);
        console.log('📋 Field Mapping:', fieldMapping);

        if (allData.length > 0) {
            const firstRow = allData[0];
            let hasError = false;
            Object.keys(fieldMapping).forEach(key => {
                const idx = fieldMapping[key];
                if (idx !== undefined && firstRow && idx < firstRow.length) {
                    console.log(`✅ Campo "${key}" -> columna ${idx} tiene valor: "${firstRow[idx]}"`);
                } else if (idx !== undefined) {
                    console.warn(`⚠️ Campo "${key}" -> columna ${idx} NO existe en los datos`);
                    hasError = true;
                }
            });
            
            if (hasError) {
                showAlert('⚠️ Algunas columnas mapeadas no existen en los datos. Verifica el mapeo.', 'warning');
                return;
            }
        }

        generatePreview();
    }

    showStep(currentStep + 1);
}

function prevStep() {
    if (currentStep > 1) {
        showStep(currentStep - 1);
    }
}

function showStep(step) {
    currentStep = step;
    $('.step').addClass('d-none');
    $(`#step${step}`).removeClass('d-none');
    $('#prevStepBtn').toggle(step > 1 && step < 4);
    $('#nextStepBtn').toggle(step > 0 && step < 3);
    $('#importBtn').toggle(step === 3);
    
    if (step === 4) {
        $('#prevStepBtn').hide();
        $('#nextStepBtn').hide();
        $('#importBtn').hide();
    }
}

// =============================================
// FUNCIONES DE PREVIEW
// =============================================

function generatePreview() {
    const container = $('#previewTableBody');
    container.empty();

    let validRecords = 0;
    let errorRecords = 0;
    const errors = [];

    const rowsToProcess = allData.length > 0 ? allData : [];

    if (rowsToProcess.length === 0) {
        container.html('<tr><td colspan="7" class="text-center">No hay datos para mostrar</td></tr>');
        return;
    }

    const sensorMapByName = {};
    const sensorMapByIdentifier = {};
    const sensorMapById = {};
    
    sensorsInGroup.forEach(s => {
        sensorMapByName[s.name.toLowerCase().trim()] = s;
        if (s.identifier) {
            sensorMapByIdentifier[s.identifier.toLowerCase().trim()] = s;
        }
        sensorMapById[s.id] = s;
    });

    const identificationMethod = $('#identificationMethod').val() || 'identifier';

    rowsToProcess.forEach((row, index) => {
        if (!row || row.length === 0) return;

        const sensorValue = getMappedValue(row, 'sensor');
        const valorValue = getMappedValue(row, 'valor');
        const fechaValue = getMappedValue(row, 'fecha');
        const fotoValue = getMappedValue(row, 'foto') || 'N/A';
        const observacionesValue = getMappedValue(row, 'observaciones') || '';

        const measurement = {
            index: index + 1,
            sensor: sensorValue,
            valor: valorValue,
            fecha: fechaValue,
            foto: fotoValue,
            observaciones: observacionesValue,
            status: 'valid'
        };

        let isValid = true;
        const recordErrors = [];

        if (!measurement.sensor || measurement.sensor === '') {
            isValid = false;
            recordErrors.push('El campo "Sensor" es obligatorio');
        } else {
            let foundSensor = null;
            const searchKey = measurement.sensor.toLowerCase().trim();

            switch (identificationMethod) {
                case 'id':
                    if (!isNaN(measurement.sensor)) {
                        foundSensor = sensorMapById[parseInt(measurement.sensor)] || null;
                    }
                    break;
                case 'identifier':
                    foundSensor = sensorMapByIdentifier[searchKey] || null;
                    break;
                case 'name':
                default:
                    foundSensor = sensorMapByName[searchKey] || null;
                    break;
            }

            if (!foundSensor && identificationMethod && identificationMethod.startsWith('metadata_')) {
                const metaKey = identificationMethod.replace('metadata_', '');
                for (const sensor of sensorsInGroup) {
                    if (sensor.metadata && sensor.metadata[metaKey]) {
                        const metaValue = String(sensor.metadata[metaKey]).toLowerCase().trim();
                        if (metaValue === searchKey) {
                            foundSensor = sensor;
                            break;
                        }
                    }
                }
            }

            if (!foundSensor) {
                foundSensor = sensorMapByName[searchKey] || null;
            }
            if (!foundSensor) {
                foundSensor = sensorMapByIdentifier[searchKey] || null;
            }
            if (!foundSensor && !isNaN(measurement.sensor)) {
                foundSensor = sensorMapById[parseInt(measurement.sensor)] || null;
            }

            if (!foundSensor) {
                isValid = false;
                const available = Object.keys(sensorMapByName).slice(0, 3).join(', ');
                const moreText = Object.keys(sensorMapByName).length > 3 ? ` y ${Object.keys(sensorMapByName).length - 3} más` : '';
                recordErrors.push(`El sensor "${measurement.sensor}" no existe. ` +
                    `Puedes identificarlo por: ${sensorFields.map(f => f.label).join(', ')}. ` +
                    `Sensores disponibles: ${available}${moreText}`);
            }
        }

        if (!measurement.valor || measurement.valor === '') {
            isValid = false;
            recordErrors.push('El campo "Valor" es obligatorio');
        } else if (isNaN(measurement.valor)) {
            isValid = false;
            recordErrors.push('El valor debe ser un número');
        }

        if (!measurement.fecha || measurement.fecha === '') {
            isValid = false;
            recordErrors.push('El campo "Fecha" es obligatorio');
        } else {
            try {
                let date = null;
                if (measurement.fecha.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    date = new Date(measurement.fecha + 'T00:00:00');
                } else if (measurement.fecha.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                    const parts = measurement.fecha.split('/');
                    date = new Date(parts[2], parts[1] - 1, parts[0]);
                } else {
                    date = new Date(measurement.fecha);
                }
                
                if (isNaN(date.getTime())) {
                    isValid = false;
                    recordErrors.push('La fecha no tiene un formato válido (YYYY-MM-DD o DD/MM/YYYY)');
                }
            } catch (e) {
                isValid = false;
                recordErrors.push('La fecha no tiene un formato válido (YYYY-MM-DD o DD/MM/YYYY)');
            }
        }

        if (isValid) {
            validRecords++;
        } else {
            errorRecords++;
            measurement.status = 'error';
            measurement.errors = recordErrors;
            errors.push({
                index: measurement.index,
                errors: recordErrors
            });
        }

        const statusBadge = isValid ?
            '<span class="badge bg-success">Válido</span>' :
            '<span class="badge bg-danger">Error</span>';

        container.append(`
            <tr class="${isValid ? '' : 'table-danger'}">
                <td>${measurement.index}</td>
                <td><strong>${measurement.sensor || 'N/A'}</strong></td>
                <td>${measurement.valor || 'N/A'}</td>
                <td>${measurement.fecha || 'N/A'}</td>
                <td>${measurement.foto}</td>
                <td>${measurement.observaciones}</td>
                <td>${statusBadge}</td>
            </tr>
        `);
    });

    $('#totalRecords').text(rowsToProcess.length);
    $('#validRecords').text(validRecords);
    $('#errorRecords').text(errorRecords);

    if (errorRecords > 0) {
        $('#importErrors').removeClass('d-none');
        renderErrors(errors);
    } else {
        $('#importErrors').addClass('d-none');
    }

    $('#importSummary').removeClass('d-none');
}

function getMappedValue(row, fieldName) {
    if (!fieldMapping || typeof fieldMapping !== 'object') {
        return null;
    }

    if (fieldMapping[fieldName] === undefined || fieldMapping[fieldName] === null) {
        return null;
    }

    const columnIndex = fieldMapping[fieldName];
    
    if (typeof columnIndex !== 'number' || isNaN(columnIndex)) {
        return null;
    }

    if (!row || !Array.isArray(row) || row.length === 0) {
        return null;
    }

    if (columnIndex >= row.length) {
        return null;
    }

    const value = row[columnIndex];
    
    if (value === undefined || value === null) {
        return null;
    }

    const strValue = String(value).trim();
    return strValue !== '' ? strValue : null;
}

function renderErrors(errors) {
    const container = $('#errorList');
    container.empty();

    errors.forEach(error => {
        container.append(`
            <li>
                <strong>Fila ${error.index}:</strong>
                <ul>
                    ${error.errors.map(e => `<li>${e}</li>`).join('')}
                </ul>
            </li>
        `);
    });
}

// =============================================
// FUNCIONES DE IMPORTACIÓN Y RESULTADOS
// =============================================

function importMeasurements() {
    if (!file || !fieldMapping || !selectedGroupId) {
        showAlert('No hay datos para importar', 'danger');
        return;
    }

    const errorRecords = parseInt($('#errorRecords').text());
    if (errorRecords > 0) {
        if (!confirm(`Hay ${errorRecords} registros con errores. ¿Deseas continuar con la importación de los registros válidos?`)) {
            return;
        }
    }

    const overwriteDuplicates = $('#overwriteDuplicates').is(':checked');
    const identificationMethod = $('#identificationMethod').val();

    console.log('Field Mapping:', fieldMapping);
    console.log('Identification Method:', identificationMethod);
    console.log('Method label:', $('#identificationMethod option:selected').text());
    
    if (!fieldMapping.sensor && fieldMapping.sensor !== 0) {
        showAlert('Error: El campo "Sensor" no está mapeado correctamente', 'danger');
        return;
    }
    
    if (!fieldMapping.valor && fieldMapping.valor !== 0) {
        showAlert('Error: El campo "Valor" no está mapeado correctamente', 'danger');
        return;
    }
    
    if (!fieldMapping.fecha && fieldMapping.fecha !== 0) {
        showAlert('Error: El campo "Fecha" no está mapeado correctamente', 'danger');
        return;
    }

    $('#progressContainer').removeClass('d-none');
    $('#importBtn').prop('disabled', true);

    const formData = new FormData();
    formData.append('file', file);
    formData.append('group_id', selectedGroupId);
    formData.append('field_mapping', JSON.stringify(fieldMapping));
    formData.append('identification_method', identificationMethod);
    formData.append('overwrite_duplicates', overwriteDuplicates);

    $.ajax({
        url: '/api/measurements/bulk-import/import',
        type: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        },
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#progressText').text('Enviando datos al servidor...');
            $('#progressBar').css('width', '30%');
        },
        success: function(response) {
            if (response.success) {
                $('#progressBar').css('width', '100%');
                $('#progressText').text('Importación completada');

                currentReportId = response.data.report_id;
                
                showResults(response.data);
                
                const message = `Importación completada: ${response.data.success_count} mediciones creadas, ${response.data.error_count} con errores`;
                showAlert(message, 'success');

            } else {
                $('#progressContainer').addClass('d-none');
                $('#importBtn').prop('disabled', false);
                showAlert(response.message || 'Error al importar mediciones', 'danger');
            }
        },
        error: function(xhr) {
            $('#progressContainer').addClass('d-none');
            $('#importBtn').prop('disabled', false);
            
            let errorMessage = 'Error al importar mediciones';
            if (xhr.responseJSON?.message) {
                errorMessage += ': ' + xhr.responseJSON.message;
            }
            if (xhr.responseJSON?.errors) {
                const errors = Object.values(xhr.responseJSON.errors).flat();
                errorMessage += '<br><ul>';
                errors.forEach(err => {
                    errorMessage += `<li>${err}</li>`;
                });
                errorMessage += '</ul>';
            }
            showAlert(errorMessage, 'danger');
            console.error('Error:', xhr);
            console.error('Response:', xhr.responseJSON);
        }
    });
}

function showResults(data) {
    $('.step').addClass('d-none');
    $('#step4').removeClass('d-none');
    
    $('#resultTotal').text(data.total_processed || 0);
    $('#resultSuccess').text(data.success_count || 0);
    $('#resultErrors').text(data.error_count || 0);
    $('#resultOverwritten').text(data.overwritten_count || 0);
    
    const tbody = $('#resultTableBody');
    tbody.empty();
    
    if (data.errors && data.errors.length > 0) {
        data.errors.forEach(err => {
            const statusBadge = err.status === 'success' ? 
                '<span class="badge bg-success">Éxito</span>' :
                err.status === 'overwritten' ?
                '<span class="badge bg-warning">Sobrescrito</span>' :
                '<span class="badge bg-danger">Error</span>';
            
            tbody.append(`
                <tr class="${err.status === 'error' ? 'table-danger' : err.status === 'overwritten' ? 'table-warning' : ''}">
                    <td>${err.row || 'N/A'}</td>
                    <td>${err.sensor || 'N/A'}</td>
                    <td>${err.valor || 'N/A'}</td>
                    <td>${err.fecha || 'N/A'}</td>
                    <td>${statusBadge}</td>
                    <td>${err.message || err.error || 'Sin mensaje'}</td>
                </tr>
            `);
        });
    } else {
        tbody.append(`
            <tr>
                <td colspan="6" class="text-center text-success">
                    <i class="bi bi-check-circle"></i> Todas las mediciones se importaron correctamente
                </td>
            </tr>
        `);
    }
}

function downloadReport() {
    if (!currentReportId) {
        showAlert('No hay informe disponible para descargar', 'warning');
        return;
    }

    const token = localStorage.getItem('token');
    window.location.href = `/api/measurements/bulk-import/report?report_id=${currentReportId}&token=${token}`;
}

function startNewImport() {
    currentReportId = null;
    file = null;
    excelHeaders = [];
    sampleData = [];
    allData = [];
    fieldMapping = {};
    $('#step4').addClass('d-none');
    $('#step1').removeClass('d-none');
    currentStep = 1;
    showStep(1);
    
    $('#fileInput').val('');
    $('#groupSelect').val('');
    $('#overwriteDuplicates').prop('checked', false);
    $('#alertContainer').empty();
    $('#dropZone').html(`
        <i class="bi bi-file-earmark-excel"></i>
        <p><strong>Arrastra y suelta el archivo aquí</strong> o haz clic para seleccionarlo</p>
        <p class="text-muted small">(Solo archivos .xlsx o .csv)</p>
        <input type="file" id="fileInput" accept=".xlsx,.csv" style="display: none;">
    `);
}

function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    $('#alertContainer').append(alertHtml);
}
</script>
@endpush