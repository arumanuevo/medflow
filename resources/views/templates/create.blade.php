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

        /* Estilos sobrios y compactos para los inputs */
        .field-row .form-control {
            font-size: 0.85rem;
            padding: 0.35rem 0.6rem;
            border-radius: 4px;
            color: #495057;
            border-color: #ced4da;
        }

        .field-row .form-select {
            font-size: 0.85rem;
            padding: 0.35rem 2.25rem 0.35rem 0.6rem !important;
            /* espacio clave para la flecha nativa */
            border-radius: 4px;
            color: #495057;
            border-color: #ced4da;
        }

        .field-row .form-control:focus,
        .field-row .form-select:focus {
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
            border-color: #86b7fe;
        }

        .field-row small.text-muted {
            font-size: 0.75rem;
            margin-top: 0.2rem;
            display: inline-block;
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
                        <h4 class="mb-0 text-white"><i class="bi bi-file-earmark-plus btn-icon"></i> Crear Plantilla
                            Personalizada</h4>
                    </div>
                    <div class="card-body">
                        <!-- Mensaje de éxito o error -->
                        <div id="alertContainer"></div>

                        <form id="templateForm">
                            <!-- Nombre de la plantilla -->
                            <div class="mb-3">
                                <label for="templateName" class="form-label">Nombre de la Plantilla <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="templateName" required
                                    placeholder="Ej: Medición de Temperatura">
                                <div class="form-text">El nombre debe ser descriptivo y único.</div>
                            </div>

                            <!-- Descripción -->
                            <div class="mb-3">
                                <label for="templateDescription" class="form-label">Descripción</label>
                                <textarea class="form-control" id="templateDescription" rows="2"
                                    placeholder="Describe el propósito de esta plantilla..."></textarea>
                                <div class="form-text">Opcional pero recomendado para identificar el uso de la plantilla.
                                </div>
                            </div>

                            <!-- Tipo de Medición -->
                            <div class="mb-3">
                                <label for="templateType" class="form-label">Tipo de Medición <span
                                        class="text-danger">*</span></label>
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
                                    <i class="bi bi-info-circle"></i> Al seleccionar un tipo, se cargarán automáticamente
                                    los campos predefinidos.
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
                                    <i class="bi bi-info-circle"></i> El inspector se asigna automáticamente al registrar
                                    una medición.
                                    <br><br>
                                    <strong>💡 Glosario de Contextos:</strong><br>
                                    • <strong>Medición:</strong> Valores que varían en cada lectura (ej: m³,
                                    temperatura).<br>
                                    • <strong>Sensor:</strong> Atributos fijos del equipo (ej: número de serie, marca).
                                </small>
                            </p>

                            <!-- Contenedor de campos -->
                            <div id="fieldsContainer" class="mb-3">
                                <!-- ✅ Campo principal (dinámico según el tipo) -->
                                <div class="field-row mb-2 p-3 border rounded bg-light" id="mainFieldRow">
                                    <div class="row g-2 align-items-start">
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" id="mainFieldName"
                                                name="schema[campos][0][nombre]" value="valor" readonly>
                                            <small class="text-muted">Nombre</small>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-select" id="mainFieldType" name="schema[campos][0][tipo]"
                                                disabled>
                                                <option value="numero" selected>Número</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Define si es un dato que cambia en cada toma (Medición) o si es fijo en el equipo (Sensor).">
                                            <select class="form-select" id="mainFieldContext"
                                                name="schema[campos][0][contexto]" disabled>
                                                <option value="medicion" selected>Medición (Variable)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" id="mainFieldUnit"
                                                name="schema[campos][0][unidad]" placeholder="Unidad" value="m³">
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-select" id="mainFieldRequired"
                                                name="schema[campos][0][requerido]">
                                                <option value="1" selected>Requerido</option>
                                                <option value="0">Opcional</option>
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <input type="text" class="form-control" id="mainFieldDefault"
                                                name="schema[campos][0][valor_por_defecto]" placeholder="Defecto">
                                        </div>
                                        <div class="col-md-1 text-center">
                                            <i class="bi bi-lock-fill" title="Campo obligatorio"
                                                style="color: #6c757d; cursor: default; font-size: 1.2rem;"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- ✅ Campo IDENTIFICADOR (siempre requerido, sensor) -->
                                <div class="field-row mb-2 p-3 border rounded bg-light" id="identificadorFieldRow">
                                    <div class="row g-2 align-items-start">
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" value="identificador" readonly>
                                            <small class="text-muted">Obligatorio</small>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-select" disabled>
                                                <option value="texto" selected>Texto</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-select" disabled>
                                                <option value="sensor" selected>Atributo de Sensor (Fijo)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" value="N/A" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-select" disabled>
                                                <option value="1" selected>Requerido</option>
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <input type="text" class="form-control" value="N/A" readonly>
                                        </div>
                                        <div class="col-md-1 text-center">
                                            <i class="bi bi-lock-fill" title="Campo obligatorio"
                                                style="color: #6c757d; cursor: default; font-size: 1.2rem;"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- ✅ Campo FOTO (siempre requerido) -->
                                <div class="field-row mb-2 p-3 border rounded bg-light" id="photoFieldRow">
                                    <div class="row g-2 align-items-start">
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" value="foto" readonly>
                                            <small class="text-muted">Obligatorio</small>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-select" disabled>
                                                <option value="string" selected>Texto</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-select" disabled>
                                                <option value="medicion" selected>Medición (Variable)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" value="N/A" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-select" disabled>
                                                <option value="1" selected>Requerido</option>
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <input type="text" class="form-control" value="N/A" readonly>
                                        </div>
                                        <div class="col-md-1 text-center">
                                            <i class="bi bi-lock-fill" title="Campo obligatorio"
                                                style="color: #6c757d; cursor: default; font-size: 1.2rem;"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- ✅ Campo FECHA (siempre requerido) -->
                                <div class="field-row mb-2 p-3 border rounded bg-light" id="dateFieldRow">
                                    <div class="row g-2 align-items-start">
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" value="fecha_medicion" readonly>
                                            <small class="text-muted">Obligatorio</small>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-select" disabled>
                                                <option value="fecha" selected>Fecha</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-select" disabled>
                                                <option value="medicion" selected>Medición (Variable)</option>
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
                                        <div class="col-md-1">
                                            <input type="text" class="form-control" value="" readonly>
                                        </div>
                                        <div class="col-md-1 text-center">
                                            <i class="bi bi-lock-fill" title="Campo obligatorio"
                                                style="color: #6c757d; cursor: default; font-size: 1.2rem;"></i>
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
        let fieldCounter = 4; // Empezamos en 4 (0: principal, 1: identificador, 2: foto, 3: fecha)

        // Mapeo de tipos a nombres de campo principal y unidad
        const fieldMapping = {
            'agua': { nombre: 'valor', unidad: 'm³' },
            'gas': { nombre: 'valor', unidad: 'm³' },
            'electricidad': { nombre: 'valor', unidad: 'kWh' },
            'temperatura': { nombre: 'valor', unidad: '°C' },
            'presion': { nombre: 'valor', unidad: 'bar' },
            'caudal': { nombre: 'valor', unidad: 'L/min' },
            'luz': { nombre: 'valor', unidad: 'lux' },
            'personalizado': { nombre: 'valor', unidad: '' }
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
            show: function (text = 'Guardando plantilla...') {
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

            hide: function () {
                const overlay = document.getElementById('loadingOverlay');
                if (overlay) {
                    overlay.remove();
                    document.body.style.overflow = '';
                }
            },

            updateText: function (text) {
                const overlay = document.getElementById('loadingOverlay');
                if (overlay) {
                    const textElement = overlay.querySelector('.text-muted');
                    if (textElement) {
                        textElement.textContent = text;
                    }
                }
            }
        };

        $(document).ready(function () {
            $('#addFieldBtn').click(addField);
            $('#saveTemplate').click(saveTemplate);

            // Inicializar tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Configurar el selector de tipo de medición
            $('#templateType').change(function () {
                const type = $(this).val();
                if (!type) return;

                // Actualizar campo principal
                const mainField = fieldMapping[type];
                if (mainField) {
                    $('#mainFieldName').val(mainField.nombre);
                    $('#mainFieldUnit').val(mainField.unidad);
                }

                // Limpiar campos adicionales (excepto los 4 obligatorios)
                $('#fieldsContainer .field-row:not(#mainFieldRow):not(#identificadorFieldRow):not(#photoFieldRow):not(#dateFieldRow)').remove();
                fieldCounter = 4;

                // Cargar campos predefinidos
                if (type !== 'personalizado' && predefinedFields[type]) {
                    predefinedFields[type].forEach(field => {
                        addField(field.nombre, field.tipo, field.unidad, field.requerido);
                    });
                    showAlert(`Se cargaron ${predefinedFields[type].length} campos predefinidos para "${type}"`, 'success');
                }
            });
        });

        function addField(name = '', type = 'numero', unit = '', required = false, context = 'medicion') {
            const container = $('#fieldsContainer');

            const fieldRow = $(`
                                                        <div class="field-row mb-2 p-3 border rounded">
                                                            <div class="row g-2 align-items-start">
                                                                <div class="col-md-2">
                                                                    <input type="text" class="form-control field-name" 
                                                                           name="schema[campos][${fieldCounter}][nombre]"
                                                                           value="${name}" placeholder="Nombre" required>
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
                                                                <div class="col-md-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Define si es un dato que cambia en cada toma (Medición) o si es fijo en el equipo (Sensor).">
                                                                    <select class="form-select field-context" 
                                                                            name="schema[campos][${fieldCounter}][contexto]" required>
                                                                        <option value="medicion" ${context === 'medicion' ? 'selected' : ''}>📝 Medición (Variable)</option>
                                                                        <option value="sensor" ${context === 'sensor' ? 'selected' : ''}>⚙️ Atributo de Sensor (Fijo)</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <input type="text" class="form-control field-unit" 
                                                                           name="schema[campos][${fieldCounter}][unidad]"
                                                                           value="${unit}" placeholder="Unidad (ej: kWh)">
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <select class="form-select field-required" 
                                                                            name="schema[campos][${fieldCounter}][requerido]">
                                                                        <option value="1" ${required ? 'selected' : ''}>Requerido</option>
                                                                        <option value="0" ${!required ? 'selected' : ''}>Opcional</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-1">
                                                                    <input type="text" class="form-control field-default" 
                                                                           name="schema[campos][${fieldCounter}][valor_por_defecto]"
                                                                           placeholder="Defecto">
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
            fieldRow.find('.remove-field-btn').click(function () {
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

            // ✅ Campo identificador (SIEMPRE requerido)
            campos.push({
                nombre: 'identificador',
                tipo: 'texto',
                contexto: 'sensor',
                unidad: null,
                requerido: true,
                valor_por_defecto: null
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
            $('#fieldsContainer .field-row:not(#mainFieldRow):not(#identificadorFieldRow):not(#photoFieldRow):not(#dateFieldRow)').each(function () {
                const nameInput = $(this).find('.field-name');
                const typeSelect = $(this).find('.field-type');
                const contextSelect = $(this).find('.field-context');
                const unitInput = $(this).find('.field-unit');
                const requiredSelect = $(this).find('.field-required');
                const defaultInput = $(this).find('.field-default');

                const campo = {
                    nombre: nameInput.val() || '',
                    tipo: typeSelect.val() || 'numero',
                    contexto: contextSelect.val() || 'medicion',
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

            // Validar que haya al menos 4 campos (principal + identificador + foto + fecha)
            if (campos.length < 4) {
                showAlert('La plantilla debe tener al menos 4 campos base obligatorios', 'danger');
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
                success: function (response) {
                    loadingIndicator.hide();
                    if (response.success) {
                        showAlert('✅ Plantilla creada correctamente', 'success');
                        // ✅ Redirigir a la lista de plantillas después de 1.5 segundos
                        setTimeout(function () {
                            window.location.href = '/templates';
                        }, 1500);
                    } else {
                        showAlert(response.message || 'Error al guardar la plantilla', 'danger');
                    }
                },
                error: function (xhr) {
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
                    $('#alertContainer .alert').first().fadeOut(500, function () {
                        $(this).remove();
                    });
                }, 5000);
            }
        }
    </script>
@endpush