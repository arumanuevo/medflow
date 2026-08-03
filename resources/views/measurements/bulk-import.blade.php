{{-- resources/views/measurements/bulk-import.blade.php --}}
@extends('layouts.modern')

@section('title', 'Importar Mediciones Masivamente - MeasureFlow')

@section('content')
<!-- Incluir el archivo CSS externo -->
<link rel="stylesheet" href="{{ asset('css/bulk-measurements-import-styles.css') }}">

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h4><i class="bi bi-file-earmark-excel btn-icon"></i> Importar Mediciones Masivamente</h4>
                    <a href="{{ route('measurements.index') }}" class="btn btn-light">
                        <i class="bi bi-arrow-left btn-icon"></i> Volver a Mediciones
                    </a>
                </div>
                <div class="card-body">
                    <!-- Alertas -->
                    <div id="alertContainer"></div>

                    <!-- Paso 1: Seleccionar Grupo y Archivo -->
                    <div id="step1" class="step">
                        <h5><i class="bi bi-upload btn-icon"></i> Paso 1: Seleccionar Grupo y Subir Archivo</h5>
                        <p class="text-muted">Selecciona el grupo de sensores y sube un archivo <strong>.xlsx</strong> o <strong>.csv</strong> con las mediciones.</p>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle btn-icon"></i>
                            <strong>Instrucciones para el archivo:</strong>
                            <ul class="mb-0 mt-2">
                                <li>El archivo debe contener una columna con el <strong>nombre del sensor</strong> (exactamente como está registrado en el sistema).</li>
                                <li>Los valores deben ser <strong>números</strong> (enteros o decimales).</li>
                                <li>Las fechas deben estar en formato <strong>YYYY-MM-DD HH:MM:SS</strong> (ejemplo: 2026-01-15 10:30:00).</li>
                                <li>Puedes importar mediciones para <strong>múltiples sensores</strong> en el mismo archivo.</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle btn-icon"></i>
                            <strong>Validaciones del sistema (importantes):</strong>
                            <ul class="mb-0 mt-2">
                                <li>✅ Verifica que el <strong>sensor exista</strong> en el grupo seleccionado.</li>
                                <li>✅ Valida que no haya <strong>fechas duplicadas</strong> para un mismo sensor.</li>
                                <li>✅ Valida que no haya <strong>duplicados exactos</strong> (misma fecha y mismo valor).</li>
                                <li>✅ Las mediciones pueden insertarse en <strong>cualquier punto</strong> de la línea temporal.</li>
                                <li>✅ La <strong>fecha</strong> debe ser coherente con las mediciones vecinas (entre la anterior y la siguiente si existen).</li>
                                <li>✅ El <strong>valor</strong> debe ser coherente con las mediciones vecinas (entre el anterior y el siguiente si existen, o mayor si es la última).</li>
                                <li>✅ Los valores siempre deben ser <strong>crecientes o iguales</strong> (nunca decrecientes).</li>
                            </ul>
                        </div>

                        <div class="alert alert-success">
                            <i class="bi bi-lightbulb btn-icon"></i>
                            <strong>Ejemplo de importación correcta:</strong>
                            <div class="mt-2">
                                <p class="mb-1"><strong>Mediciones existentes del sensor "Sensor 1":</strong></p>
                                <ul class="mb-2">
                                    <li>01/01/2026 - Valor: 100</li>
                                    <li>01/03/2026 - Valor: 200</li>
                                </ul>
                                <p class="mb-1"><strong>Puedes importar:</strong></p>
                                <ul class="mb-0">
                                    <li>✅ 01/02/2026 - Valor: 150 (se inserta entre ambas)</li>
                                    <li>✅ 01/04/2026 - Valor: 250 (se agrega al final)</li>
                                    <li>✅ 01/12/2025 - Valor: 50 (se agrega al inicio)</li>
                                </ul>
                            </div>
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
                                <i class="bi bi-folder-open btn-icon"></i> Seleccionar Archivo
                            </button>
                            <button class="btn btn-info" id="downloadTemplateBtn">
                                <i class="bi bi-download btn-icon"></i> Descargar Plantilla (.csv)
                            </button>
                        </div>
                    </div>

                    <!-- Paso 2: Mapear Campos -->
                    <div id="step2" class="step d-none">
                        <hr>
                        <h5><i class="bi bi-arrow-left-right btn-icon"></i> Paso 2: Mapear Campos</h5>
                        <p class="text-muted">Asocia los campos de tu archivo con los campos del sistema.</p>

                        <div class="alert alert-info">
    <i class="bi bi-info-circle btn-icon"></i>
    <strong>Campos obligatorios:</strong>
    <ul class="mb-0 mt-2">
        <li><strong>Sensor</strong> - Nombre del sensor (debe existir en el grupo seleccionado)</li>
        <li><strong>Valor</strong> - Valor numérico de la medición</li>
        <li><strong>Fecha</strong> - Fecha de la medición (formato: <strong>YYYY-MM-DD</strong> o <strong>DD/MM/YYYY</strong>)</li>
    </ul>
    <p class="mt-2 mb-0 small text-muted">
        <i class="bi bi-info-circle"></i> La hora no es necesaria. El sistema usará las 00:00:00 por defecto.
    </p>
</div>

                        <div id="fieldMappingContainer" class="field-mapping">
                            <!-- Los campos de mapeo se generarán dinámicamente -->
                        </div>
                    </div>

                    <!-- Paso 3: Preview y Confirmación -->
                    <div id="step3" class="step d-none">
                        <hr>
                        <h5><i class="bi bi-eye btn-icon"></i> Paso 3: Preview y Confirmación</h5>
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
                            <h5><i class="bi bi-info-circle btn-icon"></i> Resumen de Importación</h5>
                            <ul>
                                <li><strong>Total de registros:</strong> <span id="totalRecords">0</span></li>
                                <li><strong>Registros válidos:</strong> <span id="validRecords">0</span></li>
                                <li><strong>Registros con errores:</strong> <span id="errorRecords">0</span></li>
                            </ul>
                        </div>

                        <div id="importErrors" class="error-container d-none">
                            <h5><i class="bi bi-exclamation-triangle btn-icon"></i> Errores Encontrados</h5>
                            <ul id="errorList"></ul>
                        </div>
                    </div>

                    <!-- Paso 4: Resultados de la Importación (persistente) -->
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
                            <i class="bi bi-arrow-left btn-icon"></i> Anterior
                        </button>
                        <button class="btn btn-primary" id="nextStepBtn" style="display: none;">
                            <i class="bi bi-arrow-right btn-icon"></i> Siguiente
                        </button>
                        <button class="btn btn-success" id="importBtn" style="display: none;">
                            <i class="bi bi-check-circle btn-icon"></i> Importar Mediciones
                        </button>
                        <button class="btn btn-danger" id="cancelBtn">
                            <i class="bi bi-x-circle btn-icon"></i> Cancelar
                        </button>
                    </div>

                    <!-- Barra de progreso -->
                    <div id="progressContainer" class="progress-container d-none">
                        <h5><i class="bi bi-hourglass-split btn-icon"></i> Importando...</h5>
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
                    <i class="bi bi-exclamation-triangle btn-icon"></i> Confirmar Cancelación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas cancelar la importación? Todos los datos no guardados se perderán.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle btn-icon"></i> No, Continuar
                </button>
                <button type="button" class="btn btn-danger" id="confirmCancel">
                    <i class="bi bi-check-circle btn-icon"></i> Sí, Cancelar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Configuración global
let excelHeaders = [];
let sampleData = [];
let fieldMapping = {};
let currentStep = 1;
let selectedGroupId = null;
let file = null;
let groups = [];
let sensorsInGroup = [];
let currentReportId = null;

$(document).ready(function() {
    // Cargar grupos
    loadGroups();

    // Configurar eventos
    $('#selectFileBtn').click(() => $('#fileInput').click());
    $('#fileInput').change(handleFileSelect);
    $('#dropZone').click(() => $('#fileInput').click());

    // Drag and drop
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

    // Botones de navegación
    $('#nextStepBtn').click(nextStep);
    $('#prevStepBtn').click(prevStep);
    $('#importBtn').click(importMeasurements);
    $('#cancelBtn').click(() => $('#cancelModal').modal('show'));
    $('#confirmCancel').click(() => window.location.href = '{{ route("measurements.index") }}');

    // Descargar plantilla
    $('#downloadTemplateBtn').click(function() {
        const token = localStorage.getItem('token');
        window.location.href = '/api/measurements/bulk-import/download-template?token=' + token;
    });

    // Botones de resultados
    $('#downloadReportBtn').click(downloadReport);
    $('#newImportBtn').click(startNewImport);
    $('#viewSensorsBtn').click(function() {
        window.location.href = '/sensors';
    });

    // Cambio de grupo
    $('#groupSelect').change(function() {
        selectedGroupId = $(this).val();
        if (selectedGroupId) {
            loadSensorsByGroup(selectedGroupId);
        } else {
            $('#groupInfo').addClass('d-none');
        }
    });
});

/**
 * Cargar grupos disponibles
 */
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

/**
 * Cargar sensores de un grupo específico
 */
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
                
                // Mostrar información del grupo
                $('#groupInfo').removeClass('d-none');
                $('#sensorCount').text(count);
                $('#groupInfoText').html(
                    `Sensores disponibles en este grupo: <strong>${count}</strong> ` +
                    `(${sensorsInGroup.map(s => s.name).join(', ')})`
                );
                
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

/**
 * Manejar selección de archivo
 */
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
                excelHeaders = response.data.headers;
                sampleData = response.data.sample_data;
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

function generateFieldMapping(headers) {
    const container = $('#fieldMappingContainer');
    container.empty();

    const measurementFields = [
        { name: 'sensor', label: 'Nombre del Sensor *', required: true },
        { name: 'valor', label: 'Valor *', required: true },
        { name: 'fecha', label: 'Fecha y Hora *', required: true },
        { name: 'foto', label: 'Foto', required: false },
        { name: 'observaciones', label: 'Observaciones', required: false }
    ];

    measurementFields.forEach(field => {
        const row = $(`
            <div class="row">
                <div class="col-md-5">
                    <label class="form-label">${field.label}</label>
                </div>
                <div class="col-md-5">
                    <select class="form-select field-mapping-select" data-field="${field.name}">
                        <option value="" selected>No mapear</option>
                    </select>
                </div>
                <div class="col-md-2 text-center">
                    ${field.required ? '<span class="text-danger">*</span>' : ''}
                </div>
            </div>
        `);

        const select = row.find('.field-mapping-select');

        headers.forEach((header, index) => {
            const displayHeader = header ? header.trim() : `Columna ${index + 1}`;
            select.append(`<option value="${index}">${displayHeader}</option>`);
        });

        container.append(row);
    });

    $('#nextStepBtn').show();
    $('#prevStepBtn').show();
}

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
    
    // Ocultar botones de navegación en el paso 4 (resultados)
    if (step === 4) {
        $('#prevStepBtn').hide();
        $('#nextStepBtn').hide();
        $('#importBtn').hide();
    }
}

function generatePreview() {
    const container = $('#previewTableBody');
    container.empty();

    let validRecords = 0;
    let errorRecords = 0;
    const errors = [];

    const rowsToProcess = sampleData.length > 0 ? sampleData : [];

    if (rowsToProcess.length === 0) {
        container.html('<tr><td colspan="7" class="text-center">No hay datos para mostrar</td></tr>');
        return;
    }

    // Crear lista de nombres de sensores disponibles para validación
    const sensorNames = sensorsInGroup.map(s => s.name);

    rowsToProcess.forEach((row, index) => {
        if (!row || row.length === 0) return;

        const measurement = {
            index: index + 1,
            sensor: getMappedValue(row, 'sensor'),
            valor: getMappedValue(row, 'valor'),
            fecha: getMappedValue(row, 'fecha'),
            foto: getMappedValue(row, 'foto') || 'N/A',
            observaciones: getMappedValue(row, 'observaciones') || '',
            status: 'valid'
        };

        let isValid = true;
        const recordErrors = [];

        // Validar sensor
        if (!measurement.sensor || measurement.sensor === '') {
            isValid = false;
            recordErrors.push('El campo "Sensor" es obligatorio');
        } else if (!sensorNames.includes(measurement.sensor)) {
            isValid = false;
            recordErrors.push(`El sensor "${measurement.sensor}" no existe en el grupo seleccionado. Sensores disponibles: ${sensorNames.join(', ')}`);
        }

        // Validar valor
        if (!measurement.valor || measurement.valor === '') {
            isValid = false;
            recordErrors.push('El campo "Valor" es obligatorio');
        } else if (isNaN(measurement.valor)) {
            isValid = false;
            recordErrors.push('El valor debe ser un número');
        }

        // En la función generatePreview, modificar la validación de fecha
// Validar fecha (acepta YYYY-MM-DD o DD/MM/YYYY)
if (!measurement.fecha || measurement.fecha === '') {
    isValid = false;
    recordErrors.push('El campo "Fecha" es obligatorio');
} else {
    try {
        // Intentar parsear YYYY-MM-DD
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
    if (fieldMapping[fieldName] === undefined) {
        return null;
    }

    const columnIndex = fieldMapping[fieldName];
    if (columnIndex === undefined || columnIndex >= row.length) {
        return null;
    }

    if (columnIndex < 0 || columnIndex >= row.length || !row || row.length === 0) {
        return null;
    }

    if (row[columnIndex] === undefined) {
        return null;
    }

    return row[columnIndex].trim();
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

// Reemplaza la función importMeasurements con esta versión mejorada

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

    // Obtener estado de sobrescritura
    const overwriteDuplicates = $('#overwriteDuplicates').is(':checked');

    // ✅ DEBUG: Mostrar el mapeo antes de enviar
    console.log('Field Mapping:', fieldMapping);
    console.log('Sample Data:', sampleData);
    console.log('Sensors in Group:', sensorsInGroup);
    
    // ✅ Verificar que el mapeo tenga los campos correctos
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
    formData.append('overwrite_duplicates', overwriteDuplicates);

    // ✅ DEBUG: Verificar el contenido del FormData
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }

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

                // Guardar report_id
                currentReportId = response.data.report_id;
                
                // Mostrar resultados
                showResults(response.data);
                
                // Mostrar alerta de éxito
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

/**
 * Mostrar resultados de la importación
 */
function showResults(data) {
    // Ocultar pasos y mostrar resultados
    $('.step').addClass('d-none');
    $('#step4').removeClass('d-none');
    
    // Actualizar resumen
    $('#resultTotal').text(data.total_processed || 0);
    $('#resultSuccess').text(data.success_count || 0);
    $('#resultErrors').text(data.error_count || 0);
    $('#resultOverwritten').text(data.overwritten_count || 0);
    
    // Si hay errores, mostrarlos en la tabla de resultados
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

/**
 * Descargar informe
 */
function downloadReport() {
    if (!currentReportId) {
        showAlert('No hay informe disponible para descargar', 'warning');
        return;
    }

    const token = localStorage.getItem('token');
    window.location.href = `/api/measurements/bulk-import/report?report_id=${currentReportId}&token=${token}`;
}

/**
 * Iniciar nueva importación
 */
function startNewImport() {
    // Resetear todo
    currentReportId = null;
    file = null;
    excelHeaders = [];
    sampleData = [];
    fieldMapping = {};
    $('#step4').addClass('d-none');
    $('#step1').removeClass('d-none');
    currentStep = 1;
    showStep(1);
    
    // Resetear campos
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