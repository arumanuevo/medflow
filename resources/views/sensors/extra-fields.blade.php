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
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4><i class="bi bi-tags"></i> Gestión de Campos Extras</h4>
            <a href="{{ route('sensors.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Volver a Sensores
            </a>
        </div>
        <div class="card-body">
            <!-- Alertas -->
            <div id="alertContainer"></div>

            <!-- Información -->
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>¿Qué son los campos extras?</strong>
                <p class="mb-0 mt-1">
                    Son campos adicionales que puedes agregar a tus sensores durante la importación masiva 
                    (ej: Lote, Apellido, Ubicación, Marca, etc.).
                    <br>
                    <strong>Eliminar un campo lo borrará de TODOS los sensores que lo tengan.</strong>
                    <br>
                    <small class="text-muted">Puedes renombrar un campo para actualizar su nombre en todos los sensores.</small>
                </p>
            </div>

            <!-- Contenido -->
            <div id="fieldsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando campos extras...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar eliminación -->
<div class="modal fade" id="deleteFieldModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar el campo <strong id="deleteFieldName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    Esto eliminará este campo de <strong id="deleteFieldCount"></strong> sensores.
                    Esta acción no se puede deshacer.
                </div>
                <div id="deleteFieldSensorsList" class="mt-2">
                    <small class="text-muted">Sensores afectados:</small>
                    <div id="deleteFieldSensors" class="mt-1"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteFieldBtn">Eliminar Campo</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para renombrar campo -->
<div class="modal fade" id="renameFieldModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-pencil"></i> Renombrar Campo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Renombrar el campo <strong id="renameOldName"></strong></p>
                <div class="mb-3">
                    <label for="newFieldName" class="form-label">Nuevo nombre</label>
                    <input type="text" class="form-control" id="newFieldName" placeholder="Ej: numero_lote">
                    <small class="text-muted">Usa letras minúsculas y guiones bajos (ej: numero_lote)</small>
                </div>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Se renombrará en <strong id="renameFieldCount"></strong> sensores.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmRenameFieldBtn">Renombrar</button>
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
});

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
                <i class="bi bi-tags"></i>
                <h4>No hay campos extras configurados</h4>
                <p>Los campos extras se crean automáticamente al importar sensores con columnas adicionales.</p>
                <a href="{{ route('sensor-groups.bulk-import') }}" class="btn btn-primary">
                    <i class="bi bi-file-earmark-excel"></i> Ir a Importar Sensores
                </a>
            </div>
        `);
        return;
    }

    let html = `<div class="row">`;

    fields.forEach(field => {
        const sensorNames = field.sensors.slice(0, 5).map(s => 
            `<span class="sensor-tag" title="ID: ${s.id}">${s.name}</span>`
        ).join(' ');
        const moreSensors = field.sensors.length > 5 ? 
            `<span class="sensor-tag text-muted">+${field.sensors.length - 5} más</span>` : '';

        html += `
            <div class="col-md-6 col-xl-4 mb-3">
                <div class="card field-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title mb-0">
                                <span class="badge bg-primary field-badge">${field.name}</span>
                            </h5>
                            <span class="badge bg-info">${field.count} sensores</span>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">Sensores:</small>
                            <div class="mt-1">
                                ${sensorNames}
                                ${moreSensors}
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-1">
                            <button class="btn btn-sm btn-warning btn-action rename-field-btn" 
                                    data-field="${field.name}" 
                                    data-count="${field.count}"
                                    title="Renombrar campo">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-action delete-field-btn" 
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
        `;
    });

    html += `</div>`;
    content.html(html);

    // ✅ Eventos
    $('.delete-field-btn').click(function() {
        const fieldName = $(this).data('field');
        const count = $(this).data('count');
        const sensors = $(this).data('sensors');
        
        currentFieldToDelete = fieldName;
        $('#deleteFieldName').text(fieldName);
        $('#deleteFieldCount').text(count);
        
        // Mostrar lista de sensores afectados
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
                loadFields(); // Recargar lista
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
                loadFields(); // Recargar lista
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
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    $('#alertContainer').append(alertHtml);
}
</script>
@endpush