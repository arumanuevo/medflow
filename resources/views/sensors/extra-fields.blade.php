@extends('layouts.modern')

@section('title', 'Gestión de Campos Extras - MedFlow')

@push('styles')
<style>
    .field-card {
        transition: all 0.3s ease;
        border-radius: 12px;
    }
    .field-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    .field-badge {
        font-size: 0.8rem;
        padding: 0.3rem 0.8rem;
    }
    .sensor-tag {
        display: inline-block;
        background: #f8f9fa;
        padding: 0.2rem 0.6rem;
        border-radius: 12px;
        margin: 0.15rem;
        font-size: 0.75rem;
        border: 1px solid #e9ecef;
    }
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: #6c757d;
    }
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    .empty-state h4 {
        font-size: 1.25rem;
        margin-bottom: 0.5rem;
    }
    .btn-action {
        margin: 0 0.15rem;
    }
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap gap-2 p-3">
            <h4 class="mb-0"><i class="bi bi-tags me-2"></i>Gestión de Campos Extras</h4>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success rounded-pill px-3 shadow-sm" id="openAddFieldModalBtn">
                    <i class="bi bi-plus-circle me-1"></i> Agregar Campo a un Grupo
                </button>
                <a href="{{ route('sensors.index') }}" class="btn btn-light rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Sensores
                </a>
            </div>
        </div>
        <div class="card-body p-4">
            <!-- Alertas -->
            <div id="alertContainer"></div>

            <!-- Información -->
            <div class="alert alert-info border-0 rounded-3 shadow-sm mb-4">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-info-circle-fill fs-5 text-info mt-1"></i>
                    <div>
                        <strong>¿Qué son los campos extras?</strong>
                        <p class="mb-1 mt-1 text-dark" style="font-size: 0.9rem;">
                            Son atributos personalizados adicionales asociados a cada sensor (ej: <strong>Teléfono</strong>, <strong>Lote</strong>, <strong>Propietario</strong>, <strong>Marca</strong>, <strong>Ubicación</strong>).
                        </p>
                        <small class="text-muted d-block">
                            • Puedes pulsar <strong>"Agregar Campo a un Grupo"</strong> para crear un nuevo atributo en blanco para todos los sensores de un grupo de forma 100% segura y no invasiva.<br>
                            • Las mediciones existentes y fórmulas no se alteran.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Contenido -->
            <div id="fieldsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Cargando campos extras...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar campo a un grupo de sensores (No invasivo) -->
<div class="modal fade" id="addFieldToGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-success text-white p-3 px-4">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-plus-circle-fill me-2"></i>Agregar Nuevo Campo a un Grupo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 bg-light rounded-3 border mb-3 small text-muted">
                    <i class="bi bi-shield-check text-success me-1"></i>
                    <strong>Operación 100% Segura:</strong> El campo se creará en blanco en los sensores seleccionados. Las mediciones ya tomadas, fechas y plantillas se mantendrán completamente intactas.
                </div>

                <div class="mb-3">
                    <label for="targetGroupSelect" class="form-label fw-semibold">Grupo de Sensores Destino</label>
                    <select class="form-select" id="targetGroupSelect" required>
                        <option value="" disabled selected>Cargando grupos...</option>
                    </select>
                    <small class="text-muted">Elige un grupo específico o aplica a todos los sensores de tu espacio.</small>
                </div>

                <div class="mb-3">
                    <label for="addFieldNameInput" class="form-label fw-semibold">Nombre del Nuevo Atributo / Campo</label>
                    <input type="text" class="form-control" id="addFieldNameInput" placeholder="Ej: Telefono, Celular, Whatsapp, Propietario..." required>
                    <div class="d-flex flex-wrap gap-1 mt-2 align-items-center">
                        <span class="text-muted small me-1">Sugerencias:</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 quick-name-btn rounded-pill" data-name="Telefono">+ Teléfono</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 quick-name-btn rounded-pill" data-name="Celular">+ Celular</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 quick-name-btn rounded-pill" data-name="WhatsApp">+ WhatsApp</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 quick-name-btn rounded-pill" data-name="Propietario">+ Propietario</button>
                    </div>
                </div>

                <div class="mb-2">
                    <label for="addFieldDefaultValInput" class="form-label fw-semibold">Valor Inicial (Opcional)</label>
                    <input type="text" class="form-control" id="addFieldDefaultValInput" placeholder="Dejar en blanco para inicializar vacío">
                    <small class="text-muted">Por defecto se crea vacío para que puedas cargarlo después a cada sensor cuando lo necesites.</small>
                </div>
            </div>
            <div class="modal-footer bg-light p-3 px-4">
                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success rounded-pill px-4 fw-semibold" id="confirmAddFieldBtn">
                    <i class="bi bi-check-lg me-1"></i> Crear y Asignar Campo
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar eliminación -->
<div class="modal fade" id="deleteFieldModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p>¿Estás seguro de que deseas eliminar el campo <strong id="deleteFieldName"></strong>?</p>
                <div class="alert alert-warning border-0 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Esto eliminará este campo de <strong id="deleteFieldCount"></strong> sensores.
                    Esta acción no se puede deshacer.
                </div>
                <div id="deleteFieldSensorsList" class="mt-2">
                    <small class="text-muted">Sensores afectados:</small>
                    <div id="deleteFieldSensors" class="mt-1"></div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmDeleteFieldBtn">Eliminar Campo</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para renombrar campo -->
<div class="modal fade" id="renameFieldModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-1"></i> Renombrar Campo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p>Renombrar el campo <strong id="renameOldName"></strong></p>
                <div class="mb-3">
                    <label for="newFieldName" class="form-label fw-semibold">Nuevo nombre</label>
                    <input type="text" class="form-control" id="newFieldName" placeholder="Ej: numero_lote">
                    <small class="text-muted">Usa letras y guiones bajos (ej: numero_lote)</small>
                </div>
                <div class="alert alert-info border-0 small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Se actualizará el nombre en <strong id="renameFieldCount"></strong> sensores.
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="confirmRenameFieldBtn">Renombrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// =============================================
// CONFIGURACIÓN
// =============================================
let currentFieldToDelete = null;
let currentFieldToRename = null;

// =============================================
// INICIALIZACIÓN
// =============================================
$(document).ready(function() {
    loadFields();

    // Abrir modal de agregar campo
    $('#openAddFieldModalBtn').click(function() {
        loadGroupsForModal();
    });

    // Chips de sugerencia rápida
    $('.quick-name-btn').click(function() {
        const name = $(this).data('name');
        $('#addFieldNameInput').val(name).focus();
    });

    // Guardar nuevo campo en el grupo
    $('#confirmAddFieldBtn').click(function() {
        saveFieldToGroup();
    });
});

// =============================================
// CARGAR GRUPOS PARA EL MODAL
// =============================================
function loadGroupsForModal() {
    const token = localStorage.getItem('token');
    const select = $('#targetGroupSelect');
    select.html('<option value="" disabled selected>Cargando grupos...</option>');

    $.ajax({
        url: '/api/sensors/extra-fields/groups-list',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success && response.data) {
                let options = '<option value="all">🌟 Todos los grupos de este espacio</option>';
                response.data.forEach(g => {
                    options += `<option value="${g.id}">${g.name} (${g.sensors_count || 0} sensores)</option>`;
                });
                select.html(options);
                $('#addFieldToGroupModal').modal('show');
            } else {
                showAlert('No se pudieron obtener los grupos', 'danger');
            }
        },
        error: function(xhr) {
            showAlert('Error al cargar grupos: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
        }
    });
}

// =============================================
// GUARDAR CAMPO EN GRUPO (NO TRAUMÁTICO)
// =============================================
function saveFieldToGroup() {
    const token = localStorage.getItem('token');
    const groupId = $('#targetGroupSelect').val();
    const fieldName = $('#addFieldNameInput').val().trim();
    const defaultValue = $('#addFieldDefaultValInput').val().trim();

    if (!fieldName) {
        alert('Por favor ingresa un nombre para el campo (ej: Telefono)');
        $('#addFieldNameInput').focus();
        return;
    }

    const btn = $('#confirmAddFieldBtn');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Guardando...');

    $.ajax({
        url: '/api/sensors/extra-fields/add-to-group',
        type: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        data: JSON.stringify({
            group_id: groupId,
            field_name: fieldName,
            default_value: defaultValue
        }),
        success: function(response) {
            if (response.success) {
                $('#addFieldToGroupModal').modal('hide');
                $('#addFieldNameInput').val('');
                $('#addFieldDefaultValInput').val('');
                showAlert(response.message || 'Campo agregado con éxito', 'success');
                loadFields(); // Recargar lista
            } else {
                showAlert(response.message || 'Error al agregar campo', 'danger');
            }
        },
        error: function(xhr) {
            showAlert('Error: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
        },
        complete: function() {
            btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Crear y Asignar Campo');
        }
    });
}

// =============================================
// CARGAR CAMPOS
// =============================================
function loadFields() {
    const token = localStorage.getItem('token');

    if (!token) {
        $('#fieldsContent').html(`
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                No se encontró token de autenticación. <a href="{{ route('login') }}">Inicia sesión</a>
            </div>
        `);
        return;
    }

    $.ajax({
        url: '/api/sensors/extra-fields',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                renderFields(response.data);
            } else {
                showAlert(response.message || 'Error al cargar campos', 'danger');
            }
        },
        error: function(xhr) {
            showAlert('Error al cargar campos: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
            $('#fieldsContent').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Error al cargar los campos extras. Intenta nuevamente.
                </div>
            `);
        }
    });
}

// =============================================
// RENDERIZAR CAMPOS
// =============================================
function renderFields(fields) {
    const content = $('#fieldsContent');
    content.empty();

    if (!fields || fields.length === 0) {
        content.html(`
            <div class="empty-state">
                <i class="bi bi-tags text-muted"></i>
                <h4>No hay campos extras configurados</h4>
                <p class="text-muted">Puedes agregar un nuevo atributo a cualquier grupo con el botón superior.</p>
                <button type="button" class="btn btn-success rounded-pill px-4" onclick="$('#openAddFieldModalBtn').click()">
                    <i class="bi bi-plus-circle me-1"></i> Agregar Mi Primer Campo Extra
                </button>
            </div>
        `);
        return;
    }

    let html = `<div class="row">`;

    fields.forEach(field => {
        const isPhoneField = ['telefono', 'celular', 'whatsapp', 'tel', 'phone', 'movil'].includes(field.name.toLowerCase());
        const sensorNames = field.sensors.slice(0, 5).map(s => 
            `<span class="sensor-tag" title="ID: ${s.id}">${s.name}</span>`
        ).join(' ');
        const moreSensors = field.sensors.length > 5 ? 
            `<span class="sensor-tag text-muted">+${field.sensors.length - 5} más</span>` : '';

        html += `
            <div class="col-md-6 col-xl-4 mb-3">
                <div class="card field-card h-100 border shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title mb-0">
                                <span class="badge ${isPhoneField ? 'bg-success' : 'bg-primary'} field-badge">
                                    ${isPhoneField ? '<i class="bi bi-whatsapp me-1"></i>' : ''}${field.name}
                                </span>
                            </h5>
                            <span class="badge bg-secondary-subtle text-secondary border">${field.count} sensores</span>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted fw-semibold">Sensores con este atributo:</small>
                            <div class="mt-1">
                                ${sensorNames}
                                ${moreSensors}
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted" style="font-size: 0.72rem;">
                                ${isPhoneField ? '<i class="bi bi-check-circle text-success me-1"></i>Habilitado para WhatsApp' : 'Atributo dinámico'}
                            </small>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-warning btn-action rename-field-btn rounded-circle" 
                                        data-field="${field.name}" 
                                        data-count="${field.count}"
                                        title="Renombrar campo">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-action delete-field-btn rounded-circle" 
                                        data-field="${field.name}" 
                                        data-count="${field.count}"
                                        data-sensors='${JSON.stringify(field.sensors)}'
                                        title="Eliminar campo">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    html += `</div>`;
    content.html(html);

    // Eventos
    $('.delete-field-btn').click(function() {
        const fieldName = $(this).data('field');
        const count = $(this).data('count');
        const sensors = $(this).data('sensors');
        
        currentFieldToDelete = fieldName;
        $('#deleteFieldName').text(fieldName);
        $('#deleteFieldCount').text(count);
        
        let sensorsHtml = '';
        if (sensors && sensors.length > 0) {
            sensors.slice(0, 10).forEach(s => {
                sensorsHtml += `<span class="sensor-tag">${s.name}</span>`;
            });
            if (sensors.length > 10) {
                sensorsHtml += `<span class="sensor-tag text-muted">+${sensors.length - 10} más</span>`;
            }
        }
        $('#deleteFieldSensors').html(sensorsHtml || '<span class="text-muted">Todos los sensores</span>');
        
        $('#deleteFieldModal').modal('show');
    });

    $('.rename-field-btn').click(function() {
        const fieldName = $(this).data('field');
        const count = $(this).data('count');
        
        currentFieldToRename = fieldName;
        $('#renameOldName').text(fieldName);
        $('#renameFieldCount').text(count);
        $('#newFieldName').val(fieldName);
        
        $('#renameFieldModal').modal('show');
    });
}

// =============================================
// ELIMINAR CAMPO
// =============================================
$('#confirmDeleteFieldBtn').click(function() {
    if (!currentFieldToDelete) return;

    const token = localStorage.getItem('token');
    const fieldName = currentFieldToDelete;

    $(this).prop('disabled', true).html(`
        <span class="spinner-border spinner-border-sm" role="status"></span> Eliminando...
    `);

    $.ajax({
        url: '/api/sensors/extra-fields',
        type: 'DELETE',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        data: JSON.stringify({ field_name: fieldName }),
        success: function(response) {
            if (response.success) {
                $('#deleteFieldModal').modal('hide');
                showAlert(response.message || `Campo "${fieldName}" eliminado correctamente`, 'success');
                loadFields();
            } else {
                showAlert(response.message || 'Error al eliminar el campo', 'danger');
            }
        },
        error: function(xhr) {
            showAlert('Error al eliminar el campo: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
        },
        complete: function() {
            $('#confirmDeleteFieldBtn').prop('disabled', false).html('Eliminar Campo');
        }
    });
});

// =============================================
// RENOMBRAR CAMPO
// =============================================
$('#confirmRenameFieldBtn').click(function() {
    if (!currentFieldToRename) return;

    const newName = $('#newFieldName').val().trim();
    if (!newName) {
        showAlert('El nuevo nombre es obligatorio', 'warning');
        return;
    }

    if (newName === currentFieldToRename) {
        showAlert('El nuevo nombre es igual al actual', 'warning');
        return;
    }

    const token = localStorage.getItem('token');

    $(this).prop('disabled', true).html(`
        <span class="spinner-border spinner-border-sm" role="status"></span> Renombrando...
    `);

    $.ajax({
        url: '/api/sensors/extra-fields/rename',
        type: 'PUT',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        data: JSON.stringify({
            old_name: currentFieldToRename,
            new_name: newName
        }),
        success: function(response) {
            if (response.success) {
                $('#renameFieldModal').modal('hide');
                showAlert(response.message || `Campo renombrado correctamente`, 'success');
                loadFields();
            } else {
                showAlert(response.message || 'Error al renombrar el campo', 'danger');
            }
        },
        error: function(xhr) {
            showAlert('Error al renombrar el campo: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
        },
        complete: function() {
            $('#confirmRenameFieldBtn').prop('disabled', false).html('Renombrar');
        }
    });
});

// =============================================
// ALERTAS
// =============================================
function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show shadow-sm border-0" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    $('#alertContainer').append(alertHtml);
}
</script>
@endpush
