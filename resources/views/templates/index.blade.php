{{-- resources/views/templates/index.blade.php --}}
@extends('layouts.modern')

@section('title', 'Mis Plantillas - MeasureFlow')

@section('content')
<!-- Incluir el archivo CSS externo -->
<link rel="stylesheet" href="{{ asset('css/templates-styles.css') }}">

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-file-earmark-text"></i> Mis Plantillas</h4>
                <a href="{{ route('templates.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Crear Plantilla Personalizada
                </a>
            </div>
            <div class="card-body">
                <div id="templatesContent">
                    <!-- Contenido dinámico -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver campos de la plantilla -->
<div class="modal fade" id="viewTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewTemplateTitle">Campos de la Plantilla</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="templateFieldsList">
                    <!-- Campos se cargarán dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar eliminación -->
<div class="modal fade" id="deleteTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres eliminar esta plantilla? Esta acción no se puede deshacer.</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> No podrás eliminar la plantilla si está siendo usada por uno o más grupos de sensores.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteTemplate">Eliminar Plantilla</button>
            </div>
        </div>
    </div>
</div>

<!-- Estilos adicionales -->
<style>
.template-card {
    transition: transform 0.2s;
    cursor: default;
}
.template-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.template-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 20px;
    font-weight: 500;
}
.template-type-default {
    background-color: #e9ecef;
    color: #495057;
}
.template-type-custom {
    background-color: #cfe2ff;
    color: #084298;
}
.field-tag {
    display: inline-block;
    background: #f8f9fa;
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    margin: 0.2rem;
    font-size: 0.8rem;
    border: 1px solid #dee2e6;
}
.field-tag.required {
    border-color: #dc3545;
    background: #fff5f5;
}
.field-tag .badge {
    font-size: 0.6rem;
    margin-left: 0.3rem;
}
.view-fields-btn {
    cursor: pointer;
    color: #0d6efd;
    text-decoration: none;
}
.view-fields-btn:hover {
    text-decoration: underline;
}
</style>
@endsection

@push('scripts')
<script>
let allTemplates = [];
let currentTemplateId = null;
const modal = new bootstrap.Modal(document.getElementById('deleteTemplateModal'));
const viewModal = new bootstrap.Modal(document.getElementById('viewTemplateModal'));

$(document).ready(async function() {
    await loadTemplates();
    $('#confirmDeleteTemplate').click(deleteTemplate);
});

async function loadTemplates() {
    try {
        const response = await $.ajax({
            url: '/api/templates',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            }
        });

        if (!response.success) {
            showAlert(response.message || 'Error al cargar plantillas', 'danger');
            return;
        }

        allTemplates = response.data;
        renderTemplates(response.data);
    } catch (error) {
        showAlert('Error al cargar plantillas: ' + error.message, 'danger');
    }
}

function renderTemplates(data) {
    const content = $('#templatesContent');
    let html = '';

    // Plantillas por defecto
    if (data.default && data.default.length > 0) {
        html += `
            <h5 class="mt-3">Plantillas por Defecto</h5>
            <div class="row">
        `;

        data.default.forEach(template => {
            html += createTemplateCard(template, true);
        });

        html += `
            </div>
        `;
    }

    // Plantillas personalizadas
    if (data.custom && data.custom.length > 0) {
        html += `
            <h5 class="mt-4">Mis Plantillas Personalizadas</h5>
            <div class="row">
        `;

        data.custom.forEach(template => {
            html += createTemplateCard(template, false);
        });

        html += `
            </div>
        `;
    } else {
        html += `
            <div class="empty-state">
                <i class="bi bi-file-earmark-text"></i>
                <h4>No tienes plantillas personalizadas</h4>
                <p>Crea tu primera plantilla personalizada para adaptar MeasureFlow a tus necesidades.</p>
                <a href="{{ route('templates.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Crear Plantilla Personalizada
                </a>
            </div>
        `;
    }

    content.html(html);

    // Configurar eventos para los botones de eliminar
    $('.delete-template-btn').click(function() {
        currentTemplateId = $(this).data('template-id');
        modal.show();
    });

    // Configurar eventos para los botones de editar
    $('.edit-template-btn').click(function() {
        const templateId = $(this).data('template-id');
        window.location.href = `/templates/${templateId}/edit`;
    });

    // Configurar eventos para ver campos
    $('.view-fields-btn').click(function() {
        const templateId = $(this).data('template-id');
        const template = findTemplate(templateId);
        if (template) {
            showTemplateFields(template);
        }
    });
}

function findTemplate(id) {
    // Buscar en todas las plantillas
    const all = [...(allTemplates.default || []), ...(allTemplates.custom || [])];
    return all.find(t => t.id === parseInt(id));
}

function showTemplateFields(template) {
    const modalTitle = $('#viewTemplateTitle');
    const modalBody = $('#templateFieldsList');
    
    modalTitle.text(`Campos de: ${template.name}`);
    
    let html = `
        <div class="mb-3">
            <p><strong>Tipo:</strong> ${template.type}</p>
            <p><strong>Descripción:</strong> ${template.description || 'Sin descripción'}</p>
            <p><strong>Total de campos:</strong> ${template.schema.campos.length}</p>
        </div>
        <hr>
        <h6>Campos:</h6>
        <ul class="list-group">
    `;

    template.schema.campos.forEach(campo => {
        const requiredBadge = campo.requerido ? 
            '<span class="badge bg-danger ms-1">Requerido</span>' : 
            '<span class="badge bg-secondary ms-1">Opcional</span>';
        
        const defaultVal = campo.valor_por_defecto ? 
            `<span class="text-muted"> | Por defecto: ${campo.valor_por_defecto}</span>` : '';
        
        html += `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>${campo.nombre}</strong>
                    <span class="badge bg-info ms-2">${campo.tipo}</span>
                    ${requiredBadge}
                    ${campo.unidad ? `<span class="badge bg-secondary ms-1">${campo.unidad}</span>` : ''}
                    ${defaultVal}
                </div>
                ${campo.nombre === 'valor' ? '<span class="badge bg-success">Principal</span>' : ''}
            </li>
        `;
    });

    html += `
        </ul>
        <div class="mt-3">
            <small class="text-muted">
                <i class="bi bi-info-circle"></i> 
                El campo <strong>"valor"</strong> es obligatorio y debe ser de tipo número.
            </small>
        </div>
    `;

    modalBody.html(html);
    viewModal.show();
}

function createTemplateCard(template, isDefault) {
    const typeClass = `template-type-${template.type}`;
    const typeBadge = isDefault ?
        `<span class="template-badge template-type-default">Por defecto</span>` :
        `<span class="template-badge template-type-custom">Personalizada</span>`;

    // Generar tags de campos
    let fieldTags = '';
    const camposMostrar = template.schema.campos.slice(0, 5);
    camposMostrar.forEach(campo => {
        const requiredClass = campo.requerido ? 'required' : '';
        fieldTags += `<span class="field-tag ${requiredClass}">
            ${campo.nombre}
            <span class="badge bg-secondary">${campo.tipo}</span>
        </span>`;
    });
    if (template.schema.campos.length > 5) {
        fieldTags += `<span class="field-tag">+${template.schema.campos.length - 5} más</span>`;
    }

    const actions = isDefault ? '' : `
        <div class="d-flex gap-2 mt-2">
            <a href="/templates/${template.id}/edit" class="btn btn-sm btn-warning edit-template-btn"
               data-template-id="${template.id}"
               title="Editar plantilla">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <button class="btn btn-sm btn-danger delete-template-btn"
                    data-template-id="${template.id}"
                    title="Eliminar plantilla">
                <i class="bi bi-trash"></i> Eliminar
            </button>
        </div>
    `;

    return `
        <div class="col-md-6 mb-3">
            <div class="card template-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="card-title mb-0">
                                ${template.name} ${typeBadge}
                            </h5>
                            <small class="text-muted">Tipo: ${template.type}</small>
                        </div>
                        <span class="badge bg-secondary">${template.schema.campos.length} campos</span>
                    </div>
                    
                    <p class="card-text text-muted small">${template.description || 'Sin descripción'}</p>
                    
                    <div class="mb-2">
                        <strong>Campos:</strong>
                        <div class="mt-1">
                            ${fieldTags}
                            <a href="#" class="view-fields-btn ms-1" data-template-id="${template.id}">
                                <i class="bi bi-eye"></i> Ver todos
                            </a>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-9">
                            <small class="text-muted">
                                <strong>Creada:</strong> ${new Date(template.created_at).toLocaleDateString()}
                                ${template.creator ? ` | <strong>Por:</strong> ${template.creator.name}` : ''}
                            </small>
                        </div>
                        <div class="col-md-3 text-end">
                            ${actions}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

async function deleteTemplate() {
    if (!currentTemplateId) return;

    try {
        const response = await $.ajax({
            url: `/api/templates/${currentTemplateId}`,
            type: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            }
        });

        modal.hide();

        if (response.success) {
            showAlert('Plantilla eliminada correctamente', 'success');
            loadTemplates();
        } else {
            showAlert(response.message || 'Error al eliminar la plantilla', 'danger');
        }
    } catch (error) {
        modal.hide();
        showAlert('Error al eliminar la plantilla: ' + error.message, 'danger');
    }
}

function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    $('.card-body').prepend(alertHtml);
}
</script>
@endpush