@extends('layouts.modern')

@section('title', 'Crear Plantilla Personalizada - MedFlow')

@push('styles')
<style>
    /* Estilos para el formulario de plantillas */
    .field-row {
        transition: all 0.2s ease;
    }
    .field-row:hover {
        background-color: #f8f9fa;
    }
    .field-row .remove-field-btn {
        transition: all 0.2s ease;
    }
    .field-row .remove-field-btn:hover {
        transform: scale(1.2);
        color: #dc3545 !important;
    }
    .template-info {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        border-left: 4px solid #0d6efd;
    }
    .template-info ul {
        padding-left: 1.2rem;
        margin-bottom: 0;
    }
    .template-info ul li {
        padding: 0.2rem 0;
    }
    .field-preview {
        margin-top: 0.5rem;
    }
    .field-preview strong {
        display: block;
        margin-bottom: 0.3rem;
    }
    .btn-icon {
        margin-right: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4><i class="bi bi-file-earmark-plus btn-icon"></i> Crear Plantilla Personalizada</h4>
                </div>
                <div class="card-body">
                    <!-- Mensaje de éxito o error -->
                    <div id="alertContainer"></div>

                    <form id="templateForm">
                        <!-- Nombre de la plantilla -->
                        <div class="mb-3">
                            <label for="templateName" class="form-label">Nombre de la Plantilla <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="templateName" required placeholder="Ej: Medición de Temperatura">
                            <div class="form-text">El nombre debe ser descriptivo y único.</div>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="templateDescription" class="form-label">Descripción</label>
                            <textarea class="form-control" id="templateDescription" rows="2" placeholder="Describe el propósito de esta plantilla..."></textarea>
                            <div class="form-text">Opcional pero recomendado para identificar el uso de la plantilla.</div>
                        </div>

                        <!-- Tipo de Medición -->
                        <div class="mb-3">
                            <label for="templateType" class="form-label">Tipo de Medición <span class="text-danger">*</span></label>
                            <select class="form-select" id="templateType" name="type" required>
                                <option value="" selected disabled>Selecciona un tipo...</option>
                                <option value="electricidad">⚡ Electricidad</option>
                                <option value="agua">💧 Agua</option>
                                <option value="gas">🔥 Gas</option>
                                <option value="temperatura">🌡️ Temperatura</option>
                                <option value="presion">📊 Presión</option>
                                <option value="caudal">🌊 Caudal</option>
                                <option value="luz">💡 Luz</option>
                                <option value="personalizado">📋 Personalizado</option>
                            </select>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Al seleccionar un tipo, se cargarán automáticamente los campos predefinidos.
                            </small>
                        </div>

                        <hr>

                        <!-- Sección de Campos -->
                        <h5><i class="bi bi-grid-3x3-gap-fill me-2"></i> Campos de la Plantilla</h5>
                        <p class="text-muted">
                            Define los campos que tendrá tu medición.
                            <strong>El campo principal, la foto y la fecha son obligatorios.</strong>
                            <br>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> El inspector se asigna automáticamente cuando tomas una medición.
                            </small>
                        </p>

                        <!-- Contenedor de campos -->
                        <div id="fieldsContainer" class="mb-3">
                            <!-- ✅ Campo principal (dinámico según el tipo) -->
                            <div class="field-row mb-2 p-3 border rounded bg-light" id="mainFieldRow">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-3">
                                        <input type="text" class="form-control" id="mainFieldName" 
                                               name="schema[campos][0][nombre]" value="consumo_m3" readonly>
                                        <small class="text-muted">Campo principal obligatorio</small>
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select" id="mainFieldType" name="schema[campos][0][tipo]" disabled>
                                            <option value="numero" selected>Número</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="mainFieldUnit" 
                                               name="schema[campos][0][unidad]" placeholder="Unidad (ej: kWh, m³)" value="m³">
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select" id="mainFieldRequired" name="schema[campos][0][requerido]">
                                            <option value="1" selected>Requerido</option>
                                            <option value="0">Opcional</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="mainFieldDefault" 
                                               name="schema[campos][0][valor_por_defecto]" placeholder="Valor por defecto">
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <i class="bi bi-lock-fill" title="Campo obligatorio" style="color: #6c757d; cursor: default; font-size: 1.2rem;"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- ✅ Campo FOTO (siempre requerido) -->
                            <div class="field-row mb-2 p-3 border rounded bg-light" id="photoFieldRow">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-3">
                                        <input type="text" class="form-control" value="foto" readonly>
                                        <small class="text-muted">Foto de la medición (obligatoria)</small>
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select" disabled>
                                            <option value="string" selected>Texto</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" value="Sin Foto" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select" disabled>
                                            <option value="1" selected>Requerido</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" value="Sin Foto" readonly>
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <i class="bi bi-lock-fill" title="Campo obligatorio" style="color: #6c757d; cursor: default; font-size: 1.2rem;"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- ✅ Campo FECHA (siempre requerido) -->
                            <div class="field-row mb-2 p-3 border rounded bg-light" id="dateFieldRow">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-3">
                                        <input type="text" class="form-control" value="fecha_medicion" readonly>
                                        <small class="text-muted">Fecha de la medición (obligatoria)</small>
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select" disabled>
                                            <option value="fecha" selected>Fecha</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" value="dd/mm/yyyy" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select" disabled>
                                            <option value="1" selected>Requerido</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" value="" readonly placeholder="Sin valor por defecto">
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <i class="bi bi-lock-fill" title="Campo obligatorio" style="color: #6c757d; cursor: default; font-size: 1.2rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botón para agregar campos adicionales -->
                        <button type="button" class="btn btn-secondary add-field-btn mt-3" id="addFieldBtn">
                            <i class="bi bi-plus-circle btn-icon"></i> Agregar Campo
                        </button>

                        <!-- Botones de acción -->
                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('templates.index') }}" class="btn btn-secondary me-2">
                                <i class="bi bi-arrow-left btn-icon"></i> Cancelar
                            </a>
                            <button type="button" class="btn btn-primary" id="saveTemplate">
                                <i class="bi bi-check-circle btn-icon"></i> Guardar Plantilla
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contenedor para el loading (se crea automáticamente) -->
<div id="loadingContainer"></div>
@endsection

@push('scripts')
<script>
let fieldCounter = 3; // Empezamos en 3 (0: principal, 1: foto, 2: fecha)

// Mapeo de tipos a nombres de campo principal y unidad
const fieldMapping = {
    'agua': { nombre: 'consumo_m3', unidad: 'm³' },
    'gas': { nombre: 'consumo_m3', unidad: 'm³' },
    'electricidad': { nombre: 'energia_kwh', unidad: 'kWh' },
    'temperatura': { nombre: 'temperatura_c', unidad: '°C' },
    'presion': { nombre: 'presion_bar', unidad: 'bar' },
    'caudal': { nombre: 'caudal_lmin', unidad: 'L/min' },
    'luz': { nombre: 'iluminacion_lux', unidad: 'lux' },
    'personalizado': { nombre: 'medicion', unidad: '' }
};

const predefinedFields = {
    'agua': [
        { nombre: 'presion_bar', tipo: 'numero', unidad: 'bar', requerido: false },
        { nombre: 'temperatura_c', tipo: 'numero', unidad: '°C', requerido: false }
    ],
    'gas': [
        { nombre: 'presion_bar', tipo: 'numero', unidad: 'bar', requerido: false },
        { nombre: 'temperatura_c', tipo: 'numero', unidad: '°C', requerido: false }
    ],
    'electricidad': [
        { nombre: 'voltaje_v', tipo: 'numero', unidad: 'V', requerido: false },
        { nombre: 'corriente_a', tipo: 'numero', unidad: 'A', requerido: false },
        { nombre: 'factor_potencia', tipo: 'numero', unidad: '', requerido: false }
    ],
    'temperatura': [
        { nombre: 'humedad', tipo: 'numero', unidad: '%', requerido: false }
    ],
    'presion': [
        { nombre: 'temperatura_c', tipo: 'numero', unidad: '°C', requerido: false }
    ],
    'caudal': [
        { nombre: 'presion_bar', tipo: 'numero', unidad: 'bar', requerido: false }
    ],
    'luz': [
        { nombre: 'temperatura_color', tipo: 'numero', unidad: 'K', requerido: false }
    ],
    'personalizado': []
};

// ✅ Inicializar el indicador de carga
const loadingIndicator = {
    show: function(text = 'Guardando plantilla...') {
        // Crear overlay
        const overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(4px);
            z-index: 9998;
            display: flex;
            align-items: center;
            justify-content: center;
        `;
        
        // Crear contenido
        const content = document.createElement('div');
        content.style.cssText = `
            background: white;
            padding: 2.5rem 3rem;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            text-align: center;
            min-width: 220px;
        `;
        content.innerHTML = `
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">${text}</span>
            </div>
            <div class="mt-3 text-muted fw-semibold">${text}</div>
        `;
        
        overlay.appendChild(content);
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';
    },
    
    hide: function() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.remove();
            document.body.style.overflow = '';
        }
    },
    
    updateText: function(text) {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            const textElement = overlay.querySelector('.text-muted');
            if (textElement) {
                textElement.textContent = text;
            }
        }
    }
};

$(document).ready(function() {
    $('#addFieldBtn').click(addField);
    $('#saveTemplate').click(saveTemplate);

    // Configurar el selector de tipo de medición
    $('#templateType').change(function() {
        const type = $(this).val();
        if (!type) return;

        // Actualizar campo principal
        const mainField = fieldMapping[type];
        if (mainField) {
            $('#mainFieldName').val(mainField.nombre);
            $('#mainFieldUnit').val(mainField.unidad);
        }

        // Limpiar campos adicionales (excepto los 3 obligatorios)
        $('#fieldsContainer .field-row:not(#mainFieldRow):not(#photoFieldRow):not(#dateFieldRow)').remove();
        fieldCounter = 3;

        // Cargar campos predefinidos
        if (type !== 'personalizado' && predefinedFields[type]) {
            predefinedFields[type].forEach(field => {
                addField(field.nombre, field.tipo, field.unidad, field.requerido);
            });
            showAlert(`Se cargaron ${predefinedFields[type].length} campos predefinidos para "${type}"`, 'success');
        }
    });
});

function addField(name = '', type = 'numero', unit = '', required = false) {
    const container = $('#fieldsContainer');
    
    const fieldRow = $(`
        <div class="field-row mb-2 p-3 border rounded">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" class="form-control field-name" 
                           name="schema[campos][${fieldCounter}][nombre]"
                           value="${name}" placeholder="Nombre del campo" required>
                </div>
                <div class="col-md-2">
                    <select class="form-select field-type" 
                            name="schema[campos][${fieldCounter}][tipo]" required>
                        <option value="numero" ${type === 'numero' ? 'selected' : ''}>Número</option>
                        <option value="texto" ${type === 'texto' ? 'selected' : ''}>Texto</option>
                        <option value="fecha" ${type === 'fecha' ? 'selected' : ''}>Fecha</option>
                        <option value="booleano" ${type === 'booleano' ? 'selected' : ''}>Booleano</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control field-unit" 
                           name="schema[campos][${fieldCounter}][unidad]"
                           value="${unit}" placeholder="Unidad (ej: m³, kWh)">
                </div>
                <div class="col-md-2">
                    <select class="form-select field-required" 
                            name="schema[campos][${fieldCounter}][requerido]">
                        <option value="1" ${required ? 'selected' : ''}>Requerido</option>
                        <option value="0" ${!required ? 'selected' : ''}>Opcional</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control field-default" 
                           name="schema[campos][${fieldCounter}][valor_por_defecto]"
                           placeholder="Valor por defecto">
                </div>
                <div class="col-md-1 text-center">
                    <i class="bi bi-trash remove-field-btn" title="Eliminar campo"
                       style="color: #dc3545; cursor: pointer; font-size: 1.2rem;"></i>
                </div>
            </div>
        </div>
    `);

    container.append(fieldRow);
    fieldCounter++;

    // Configurar el botón de eliminar
    fieldRow.find('.remove-field-btn').click(function() {
        fieldRow.remove();
    });
}

function saveTemplate() {
    const name = $('#templateName').val().trim();
    const description = $('#templateDescription').val().trim();
    const type = $('#templateType').val();

    if (!name || !type) {
        showAlert('Los campos obligatorios (Nombre y Tipo de Medición) deben completarse', 'danger');
        return;
    }

    // Obtener todos los campos
    const campos = [];

    // ✅ Campo principal
    campos.push({
        nombre: $('#mainFieldName').val() || 'medicion',
        tipo: 'numero',
        unidad: $('#mainFieldUnit').val() || '',
        requerido: true,
        valor_por_defecto: $('#mainFieldDefault').val() || null
    });

    // ✅ Campo foto (SIEMPRE requerido)
    campos.push({
        nombre: 'foto',
        tipo: 'string',
        unidad: null,
        requerido: true,
        valor_por_defecto: 'Sin Foto',
        es_foto: true
    });

    // ✅ Campo fecha (SIEMPRE requerido)
    campos.push({
        nombre: 'fecha_medicion',
        tipo: 'fecha',
        unidad: null,
        requerido: true,
        valor_por_defecto: null
    });

    // ✅ Campos adicionales (NO incluir inspector aquí)
    $('#fieldsContainer .field-row:not(#mainFieldRow):not(#photoFieldRow):not(#dateFieldRow)').each(function() {
        const nameInput = $(this).find('.field-name');
        const typeSelect = $(this).find('.field-type');
        const unitInput = $(this).find('.field-unit');
        const requiredSelect = $(this).find('.field-required');
        const defaultInput = $(this).find('.field-default');

        const campo = {
            nombre: nameInput.val() || '',
            tipo: typeSelect.val() || 'numero',
            unidad: unitInput.val() || null,
            requerido: requiredSelect.val() === '1',
            valor_por_defecto: defaultInput.val() || null
        };

        if (!campo.nombre) {
            showAlert('Todos los campos deben tener un nombre', 'danger');
            return;
        }

        campos.push(campo);
    });

    // Validar que no haya campos duplicados
    const fieldNames = campos.map(c => c.nombre);
    const uniqueFieldNames = [...new Set(fieldNames)];
    if (fieldNames.length !== uniqueFieldNames.length) {
        showAlert('No puedes tener campos con el mismo nombre', 'danger');
        return;
    }

    // Validar que haya al menos 3 campos (principal + foto + fecha)
    if (campos.length < 3) {
        showAlert('La plantilla debe tener al menos 3 campos: principal, foto y fecha', 'danger');
        return;
    }

    const templateData = {
        name: name,
        description: description,
        type: type,
        schema: { campos: campos }
    };

    // ✅ Mostrar indicador de carga
    loadingIndicator.show('Creando plantilla...');

    $.ajax({
        url: '/api/templates',
        type: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        data: JSON.stringify(templateData),
        success: function(response) {
            loadingIndicator.hide();
            if (response.success) {
                showAlert('✅ Plantilla creada correctamente', 'success');
                // ✅ Redirigir a la lista de plantillas después de 1.5 segundos
                setTimeout(function() {
                    window.location.href = '/templates';
                }, 1500);
            } else {
                showAlert(response.message || 'Error al guardar la plantilla', 'danger');
            }
        },
        error: function(xhr) {
            loadingIndicator.hide();
            const errorMessage = xhr.responseJSON?.message || xhr.statusText;
            let errorDetail = '';
            if (xhr.responseJSON?.errors) {
                const errors = Object.values(xhr.responseJSON.errors).flat();
                errorDetail = '<br><ul class="mb-0">' + errors.map(e => `<li>${e}</li>`).join('') + '</ul>';
            }
            showAlert('Error al guardar la plantilla: ' + errorMessage + errorDetail, 'danger');
            console.error('Error:', xhr);
        }
    });
}

function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    $('#alertContainer').append(alertHtml);
    
    // Auto-eliminar después de 5 segundos (excepto success)
    if (type !== 'success') {
        setTimeout(() => {
            $('#alertContainer .alert').first().fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
    }
}
</script>
@endpush