@extends('layouts.modern')

@section('title', 'Editar Plantilla - MeasureFlow')

@section('content')
<!-- Incluir el archivo CSS externo -->
<link rel="stylesheet" href="{{ asset('css/templates-styles_edit.css') }}">

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4><i class="bi bi-file-earmark btn-icon"></i> Editar Plantilla</h4>
                </div>
                <div class="card-body">
                    <!-- Mensaje de éxito o error -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form id="templateForm">
                        <input type="hidden" id="templateId" value="{{ $template->id ?? '' }}">

                        <!-- Nombre de la plantilla -->
                        <div class="mb-3">
                            <label for="templateName" class="form-label">Nombre de la Plantilla <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="templateName" value="{{ $template->name ?? '' }}" required>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="templateDescription" class="form-label">Descripción</label>
                            <textarea class="form-control" id="templateDescription" rows="2">{{ $template->description ?? '' }}</textarea>
                        </div>

                        <!-- Tipo de Medición -->
                        <div class="mb-3">
                            <label for="templateType" class="form-label">Tipo de Medición <span class="text-danger">*</span></label>
                            <select class="form-select" id="templateType" required>
                                <option value="" disabled>Selecciona un tipo...</option>
                                <option value="electricidad" {{ ($template->type ?? '') === 'electricidad' ? 'selected' : '' }}>Electricidad</option>
                                <option value="agua" {{ ($template->type ?? '') === 'agua' ? 'selected' : '' }}>Agua</option>
                                <option value="gas" {{ ($template->type ?? '') === 'gas' ? 'selected' : '' }}>Gas</option>
                                <option value="luz" {{ ($template->type ?? '') === 'luz' ? 'selected' : '' }}>Luz</option>
                                <option value="temperatura" {{ ($template->type ?? '') === 'temperatura' ? 'selected' : '' }}>Temperatura</option>
                                <option value="presion" {{ ($template->type ?? '') === 'presion' ? 'selected' : '' }}>Presión</option>
                                <option value="caudal" {{ ($template->type ?? '') === 'caudal' ? 'selected' : '' }}>Caudal</option>
                                <option value="personalizado" {{ ($template->type ?? '') === 'personalizado' ? 'selected' : '' }}>Personalizado</option>
                            </select>
                        </div>

                        <hr>

                        <!-- Sección de Campos -->
                        <h5>Campos de la Plantilla</h5>
                        <p class="text-muted">
                            Modifica los campos de tu plantilla.
                            <strong>Al menos uno debe ser de tipo "número" y llamado "valor".</strong>
                        </p>

                        <!-- Contenedor de campos -->
                        <div id="fieldsContainer" class="mb-3">
                            @if(isset($template->schema['campos']))
                                @foreach($template->schema['campos'] as $index => $campo)
                                    <div class="field-row mb-2 p-2 border rounded">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-3">
                                                <input type="text" class="form-control field-name"
                                                       name="schema[campos][{{ $index }}][nombre]"
                                                       value="{{ $campo['nombre'] ?? '' }}"
                                                       {{ ($campo['nombre'] ?? '') === 'valor' ? 'readonly' : '' }}>
                                            </div>
                                            <div class="col-md-2">
                                                <select class="form-select field-type"
                                                        name="schema[campos][{{ $index }}][tipo]"
                                                        {{ ($campo['nombre'] ?? '') === 'valor' ? 'disabled' : '' }} required>
                                                    <option value="numero" {{ ($campo['tipo'] ?? '') === 'numero' ? 'selected' : '' }}>Número</option>
                                                    <option value="texto" {{ ($campo['tipo'] ?? '') === 'texto' ? 'selected' : '' }}>Texto</option>
                                                    <option value="fecha" {{ ($campo['tipo'] ?? '') === 'fecha' ? 'selected' : '' }}>Fecha</option>
                                                    <option value="booleano" {{ ($campo['tipo'] ?? '') === 'booleano' ? 'selected' : '' }}>Booleano</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control field-unit"
                                                       name="schema[campos][{{ $index }}][unidad]"
                                                       value="{{ $campo['unidad'] ?? '' }}" placeholder="Unidad (ej: kWh, m³)">
                                            </div>
                                            <div class="col-md-2">
                                                <select class="form-select field-required"
                                                        name="schema[campos][{{ $index }}][requerido]">
                                                    <option value="1" {{ ($campo['requerido'] ?? false) ? 'selected' : '' }}>Requerido</option>
                                                    <option value="0" {{ !($campo['requerido'] ?? false) ? 'selected' : '' }}>Opcional</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control field-default"
                                                       name="schema[campos][{{ $index }}][valor_por_defecto]"
                                                       value="{{ $campo['valor_por_defecto'] ?? '' }}" placeholder="Valor por defecto">
                                            </div>
                                            <div class="col-md-1 text-center">
                                                @if(($campo['nombre'] ?? '') !== 'valor')
                                                    <i class="bi bi-trash remove-field-btn" title="Eliminar campo"
                                                       style="color: #dc3545; cursor: pointer; font-size: 1.2rem;"></i>
                                                @else
                                                    <i class="bi bi-lock-fill" title="Campo obligatorio"
                                                       style="color: #6c757d; cursor: default; font-size: 1.2rem;"></i>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <!-- Campo "valor" obligatorio por defecto -->
                                <div class="field-row mb-2 p-2 border rounded bg-light">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" name="schema[campos][][nombre]" value="valor" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-select" name="schema[campos][][tipo]" required disabled>
                                                <option value="numero" selected>Número</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" name="schema[campos][][unidad]" placeholder="Unidad (ej: kWh, m³)">
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-select" name="schema[campos][][requerido]">
                                                <option value="1" selected>Requerido</option>
                                                <option value="0">Opcional</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" name="schema[campos][][valor_por_defecto]" placeholder="Valor por defecto">
                                        </div>
                                        <div class="col-md-1 text-center">
                                            <i class="bi bi-lock-fill" title="Campo obligatorio" style="color: #6c757d; cursor: default; font-size: 1.2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            @endif
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
                                <i class="bi bi-check-circle btn-icon"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let fieldCounter = 0;

$(document).ready(function() {
    // Inicializar eventos
    $('#addFieldBtn').click(addField);
    $('#saveTemplate').click(saveTemplate);

    // Configurar el selector de tipo de medición para cargar campos predefinidos
    $('#templateType').change(function() {
        const type = $(this).val();
        if (type === 'personalizado') {
            return; // No cargar campos predefinidos para personalizado
        }

        // Preguntar al usuario si quiere reemplazar los campos actuales
        if ($('.field-row').length > 1) { // Más de 1 (el campo "valor" siempre está)
            if (!confirm('¿Deseas reemplazar los campos actuales con los predefinidos para este tipo de medición?')) {
                return;
            }
        }

        // Limpiar todos los campos excepto el campo "valor" obligatorio
        $('.field-row:not(:first)').remove();
        loadPredefinedFields(type);
    });

    // Configurar botones de eliminar para campos existentes
    $('.remove-field-btn').click(function() {
        $(this).closest('.field-row').remove();
    });
});

/**
 * Cargar campos predefinidos según el tipo de medición
 */
function loadPredefinedFields(type) {
    const container = $('#fieldsContainer');
    const predefinedFields = {
        electricidad: [
            { nombre: 'voltaje', tipo: 'numero', unidad: 'V', requerido: false },
            { nombre: 'corriente', tipo: 'numero', unidad: 'A', requerido: false },
            { nombre: 'factor_potencia', tipo: 'numero', unidad: '', requerido: false }
        ],
        agua: [
            { nombre: 'presion', tipo: 'numero', unidad: 'bar', requerido: false }
        ],
        gas: [
            { nombre: 'presion', tipo: 'numero', unidad: 'bar', requerido: false }
        ],
        luz: [
            { nombre: 'voltaje', tipo: 'numero', unidad: 'V', requerido: false },
            { nombre: 'corriente', tipo: 'numero', unidad: 'A', requerido: false }
        ],
        temperatura: [
            { nombre: 'humedad', tipo: 'numero', unidad: '%', requerido: false }
        ],
        presion: [
            { nombre: 'temperatura', tipo: 'numero', unidad: '°C', requerido: false }
        ],
        caudal: [
            { nombre: 'temperatura', tipo: 'numero', unidad: '°C', requerido: false }
        ]
    };

    if (predefinedFields[type]) {
        predefinedFields[type].forEach(field => {
            addField(field.nombre, field.tipo, field.unidad, field.requerido);
        });
    }
}

/**
 * Agregar un nuevo campo al formulario
 */
function addField(name = '', type = 'numero', unit = '', required = false) {
    fieldCounter++;
    const container = $('#fieldsContainer');

    const fieldRow = $(
        `<div class="field-row mb-2 p-2 border rounded">
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
        </div>`
    );

    container.append(fieldRow);

    // Configurar el botón de eliminar para el nuevo campo
    fieldRow.find('.remove-field-btn').click(function() {
        fieldRow.remove();
    });
}

/**
 * Guardar la plantilla
 */
function saveTemplate() {
    const id = $('#templateId').val();
    const name = $('#templateName').val();
    const description = $('#templateDescription').val();
    const type = $('#templateType').val();

    // Validar campos obligatorios
    if (!name || !type) {
        showAlert('Los campos obligatorios (Nombre y Tipo de Medición) deben completarse', 'danger');
        return;
    }

    // Obtener todos los campos
    const fieldRows = $('.field-row');
    const campos = [];

    fieldRows.each(function() {
        const nameInput = $(this).find('[name^="schema[campos]"]:eq(0)');
        const typeSelect = $(this).find('[name^="schema[campos]"]:eq(1)');
        const unitInput = $(this).find('[name^="schema[campos]"]:eq(2)');
        const requiredSelect = $(this).find('[name^="schema[campos]"]:eq(3)');
        const defaultInput = $(this).find('[name^="schema[campos]"]:eq(4)');

        const campo = {
            nombre: nameInput.val() || '',
            tipo: typeSelect.val() || '',
            unidad: unitInput.val() || null,
            requerido: requiredSelect.val() === '1' || requiredSelect.val() === true,
            valor_por_defecto: defaultInput.val() || null
        };

        // Validar que el campo tenga nombre y tipo
        if (!campo.nombre || !campo.tipo) {
            showAlert('Todos los campos deben tener un nombre y un tipo', 'danger');
            return;
        }

        campos.push(campo);
    });

    // Validar que haya al menos un campo "valor" de tipo número
    const hasValueField = campos.some(c => c.nombre === 'valor' && c.tipo === 'numero');
    if (!hasValueField) {
        showAlert('La plantilla debe incluir un campo llamado "valor" de tipo número', 'danger');
        return;
    }

    // Validar que no haya campos duplicados
    const fieldNames = campos.map(c => c.nombre);
    const uniqueFieldNames = [...new Set(fieldNames)];
    if (fieldNames.length !== uniqueFieldNames.length) {
        showAlert('No puedes tener campos con el mismo nombre', 'danger');
        return;
    }

    const templateData = {
        name: name,
        description: description,
        type: type,
        schema: {
            campos: campos
        }
    };

    // Mostrar confirmación antes de guardar
    if (confirm('¿Estás seguro de que quieres guardar los cambios en esta plantilla?')) {
        $.ajax({
            url: `/api/templates/${id}`,
            type: 'PUT',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(templateData),
            success: function(response) {
                if (response.success) {
                    showAlert('Plantilla actualizada correctamente', 'success');
                    setTimeout(function() {
                        window.location.href = '{{ route("templates.index") }}';
                    }, 1500);
                } else {
                    showAlert(response.message || 'Error al actualizar la plantilla', 'danger');
                }
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON?.message || xhr.statusText;
                showAlert('Error al actualizar la plantilla: ' + errorMessage, 'danger');
            }
        });
    }
}

// Función para mostrar alertas
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