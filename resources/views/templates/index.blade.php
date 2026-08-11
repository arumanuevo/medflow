{{-- resources/views/templates/index.blade.php --}}
@extends('layouts.modern')

@section('title', 'Mis Plantillas - MedFlow')

@push('styles')
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
.field-tag.main-field {
    border-color: #0d6efd;
    background: #e8f4fd;
    font-weight: 600;
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
.main-field-info {
    background: #e8f4fd;
    border-left: 4px solid #0d6efd;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}
.main-field-info small {
    color: #0d6efd;
}
/* Estilos para las tarjetas de plantillas */
.template-card {
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: default;
    border: 1px solid #e9ecef;
}
.template-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}
.template-card .card-title {
    font-size: 1.05rem;
}

.template-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.6rem;
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
    padding: 0.15rem 0.5rem;
    border-radius: 12px;
    margin: 0.1rem;
    font-size: 0.75rem;
    border: 1px solid #dee2e6;
    transition: all 0.2s;
}
.field-tag:hover {
    background: #e9ecef;
}
.field-tag.required {
    border-color: #dc3545;
    background: #fff5f5;
}
.field-tag.main-field {
    border-color: #0d6efd;
    background: #e8f4fd;
    font-weight: 600;
}
.field-tag .badge {
    font-size: 0.55rem;
    margin-left: 0.2rem;
}

.view-fields-btn {
    cursor: pointer;
    color: #0d6efd;
    text-decoration: none;
    font-size: 0.8rem;
}
.view-fields-btn:hover {
    text-decoration: underline;
}

.main-field-info {
    background: #e8f4fd;
    border-left: 4px solid #0d6efd;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}
.main-field-info small {
    color: #0d6efd;
}
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4><i class="bi bi-file-earmark-text"></i> Mis Plantillas</h4>
                    {{-- ✅ BOTÓN CREAR PLANTILLA - depende de permisos --}}
                    @if(isset($permissions['create_template']) && $permissions['create_template'])
                        <a href="{{ route('templates.create') }}" class="btn btn-light">
                            <i class="bi bi-plus-circle"></i> Crear Plantilla Personalizada
                        </a>
                    @else
                        <button class="btn btn-secondary" disabled title="Funcionalidad exclusiva para Premium">
                            <i class="bi bi-plus-circle"></i> Crear Plantilla Personalizada
                            <span class="badge bg-warning text-dark ms-1">Premium</span>
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    {{-- ✅ INFO DEL CAMPO PRINCIPAL --}}
                    <div class="main-field-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Campo principal:</strong> 
                        <span class="badge bg-primary">valor</span>
                        <small class="text-muted ms-2">
                            Todas las plantillas usan <strong>"valor"</strong> como campo principal para la medición del sensor.
                            Este campo es obligatorio y de tipo número.
                        </small>
                    </div>

                    <div id="templatesContent">
                        <!-- Contenido dinámico -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver campos de la plantilla -->
<div class="modal fade" id="viewTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewTemplateTitle">Campos de la Plantilla</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
@endsection

@push('scripts')
<script>
let allTemplates = { default: [], custom: [] };
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

        console.log('📊 Respuesta de plantillas:', response);

        if (!response.success) {
            showAlert(response.message || 'Error al cargar plantillas', 'danger');
            return;
        }

        if (!response.data || typeof response.data !== 'object') {
            showAlert('Error: Estructura de datos inválida', 'danger');
            return;
        }

        allTemplates = {
            default: response.data.default || [],
            custom: response.data.custom || []
        };

        renderTemplates(allTemplates);
    } catch (error) {
        console.error('❌ Error detallado:', error);
        showAlert('Error al cargar plantillas: ' + (error.message || 'Error desconocido'), 'danger');
    }
}

function renderTemplates(data) {
    const content = $('#templatesContent');
    let html = '';

    const defaultTemplates = data.default || [];
    const customTemplates = data.custom || [];

    if (defaultTemplates.length > 0) {
        html += `
            <h5 class="mt-3">Plantillas por Defecto</h5>
            <div class="row">
        `;

        defaultTemplates.forEach(template => {
            html += createTemplateCard(template, true);
        });

        html += `
            </div>
        `;
    }

    if (customTemplates.length > 0) {
        html += `
            <h5 class="mt-4">Mis Plantillas Personalizadas</h5>
            <div class="row">
        `;

        customTemplates.forEach(template => {
            html += createTemplateCard(template, false);
        });

        html += `
            </div>
        `;
    }

    if (defaultTemplates.length === 0 && customTemplates.length === 0) {
        html += `
            <div class="empty-state">
                <i class="bi bi-file-earmark-text"></i>
                <h4>No hay plantillas disponibles</h4>
                <p>Crea tu primera plantilla personalizada para adaptar MedFlow a tus necesidades.</p>
                @if(isset($permissions['create_template']) && $permissions['create_template'])
                    <a href="{{ route('templates.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Crear Plantilla Personalizada
                    </a>
                @else
                    <button class="btn btn-secondary" disabled>
                        <i class="bi bi-plus-circle"></i> Crear Plantilla Personalizada
                        <span class="badge bg-warning text-dark ms-1">Premium</span>
                    </button>
                @endif
            </div>
        `;
    }

    content.html(html);

    $('.delete-template-btn').click(function() {
        currentTemplateId = $(this).data('template-id');
        modal.show();
    });

    $('.edit-template-btn').click(function() {
        const templateId = $(this).data('template-id');
        window.location.href = `/templates/${templateId}/edit`;
    });

    $('.view-fields-btn').click(function() {
        const templateId = $(this).data('template-id');
        const template = findTemplate(templateId);
        if (template) {
            showTemplateFields(template);
        }
    });
}

function findTemplate(id) {
    const all = [...(allTemplates.default || []), ...(allTemplates.custom || [])];
    return all.find(t => t.id === parseInt(id));
}

function createTemplateCard(template, isDefault) {
    // ✅ Verificar que template existe
    if (!template || !template.schema) {
        return `
            <div class="col-md-6 mb-3">
                <div class="card template-card border-danger">
                    <div class="card-body text-danger">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Error: Plantilla inválida o datos incompletos
                    </div>
                </div>
            </div>
        `;
    }

    // ✅ Determinar tipo de badge
    const typeBadge = isDefault ?
        `<span class="template-badge template-type-default">Por defecto</span>` :
        `<span class="template-badge template-type-custom">Personalizada</span>`;

    // ✅ Obtener campos normalizados
    const campos = template.schema.campos || [];
    const mainUnit = template.main_unit || '';

    // ✅ Generar tags de campos (mostrar hasta 5)
    let fieldTags = '';
    const camposMostrar = campos.slice(0, 5);
    camposMostrar.forEach(campo => {
        const requiredClass = campo.requerido ? 'required' : '';
        const mainClass = campo.nombre === 'valor' ? 'main-field' : '';
        const isMain = campo.nombre === 'valor' ? '⭐' : '';
        fieldTags += `
            <span class="field-tag ${requiredClass} ${mainClass}">
                ${campo.nombre}
                ${isMain}
                <span class="badge bg-secondary">${campo.tipo}</span>
                ${campo.requerido ? '<span class="badge bg-danger ms-1">*</span>' : ''}
            </span>
        `;
    });
    
    // ✅ Si hay más de 5 campos, mostrar indicador
    if (campos.length > 5) {
        fieldTags += `
            <span class="field-tag">
                +${campos.length - 5} más
            </span>
        `;
    }

    // ✅ Botones de acción (solo para plantillas personalizadas)
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

    // ✅ Determinar si el campo "valor" existe
    const hasMainField = campos.some(c => c.nombre === 'valor');

    return `
        <div class="col-md-6 col-xl-4 mb-3">
            <div class="card template-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0 d-flex align-items-center gap-2 flex-wrap">
                                ${template.name}
                                ${typeBadge}
                            </h5>
                            <small class="text-muted">Tipo: ${template.type}</small>
                        </div>
                        <span class="badge bg-secondary ms-2">${campos.length} campos</span>
                    </div>
                    
                    <p class="card-text text-muted small">${template.description || 'Sin descripción'}</p>
                    
                    ${hasMainField ? `
                        <div class="mb-2 p-2 bg-light rounded">
                            <small>
                                <strong>Campo principal:</strong> 
                                <span class="badge bg-primary">valor</span>
                                ${mainUnit ? `<span class="text-muted ms-1">(Unidad: ${mainUnit})</span>` : ''}
                            </small>
                        </div>
                    ` : `
                        <div class="mb-2 p-2 bg-warning bg-opacity-10 rounded border border-warning">
                            <small class="text-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Advertencia:</strong> No se encontró el campo principal "valor"
                            </small>
                        </div>
                    `}
                    
                    <div class="mb-2">
                        <strong>Campos:</strong>
                        <div class="mt-1 d-flex flex-wrap gap-1">
                            ${fieldTags}
                            <a href="#" class="view-fields-btn ms-1" data-template-id="${template.id}">
                                <i class="bi bi-eye"></i> Ver todos
                            </a>
                        </div>
                    </div>
                    
                    <div class="row mt-3 pt-2 border-top">
                        <div class="col-md-8">
                            <small class="text-muted">
                                <i class="bi bi-calendar3 me-1"></i>
                                ${new Date(template.created_at).toLocaleDateString()}
                                ${template.creator ? ` | <i class="bi bi-person me-1"></i>${template.creator.name}` : ''}
                            </small>
                        </div>
                        <div class="col-md-4 text-end">
                            ${actions}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function showTemplateFields(template) {
    const modalTitle = $('#viewTemplateTitle');
    const modalBody = $('#templateFieldsList');
    
    modalTitle.text(`Campos de: ${template.name}`);
    
    const campos = template.schema.campos || [];
    
    let html = `
        <div class="mb-3">
            <p><strong>Tipo:</strong> ${template.type}</p>
            <p><strong>Descripción:</strong> ${template.description || 'Sin descripción'}</p>
            <p><strong>Total de campos:</strong> ${campos.length}</p>
        </div>
        <hr>
        
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Campo principal:</strong> 
            <span class="badge bg-primary">valor</span>
            <small class="text-muted ms-2">
                Este es el campo que almacena la medición del sensor.
            </small>
        </div>
        
        <h6>Campos:</h6>
        <ul class="list-group">
    `;

    campos.forEach(campo => {
        const requiredBadge = campo.requerido ? 
            '<span class="badge bg-danger ms-1">Requerido</span>' : 
            '<span class="badge bg-secondary ms-1">Opcional</span>';
        
        const defaultVal = campo.valor_por_defecto ? 
            `<span class="text-muted"> | Por defecto: ${campo.valor_por_defecto}</span>` : '';
        
        const isMainField = campo.nombre === 'valor';
        const mainBadge = isMainField ? '<span class="badge bg-primary ms-1">Principal</span>' : '';
        
        html += `
            <li class="list-group-item d-flex justify-content-between align-items-center ${isMainField ? 'list-group-item-primary' : ''}">
                <div>
                    <strong>${campo.nombre}</strong>
                    <span class="badge bg-info ms-2">${campo.tipo}</span>
                    ${requiredBadge}
                    ${campo.unidad ? `<span class="badge bg-secondary ms-1">${campo.unidad}</span>` : ''}
                    ${mainBadge}
                    ${defaultVal}
                </div>
                ${isMainField ? '<i class="bi bi-check-circle-fill text-primary"></i>' : ''}
            </li>
        `;
    });

    html += `
        </ul>
        <div class="mt-3">
            <small class="text-muted">
                <i class="bi bi-info-circle"></i> 
                El campo <strong>"valor"</strong> es el campo principal y almacena la medición del sensor.
            </small>
        </div>
    `;

    modalBody.html(html);
    viewModal.show();
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