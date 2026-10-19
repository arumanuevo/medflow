@extends('layouts.modern')

@section('title', 'Editar Sensor - MeasureFlow')

@section('content')
    <!-- Incluir el archivo CSS externo -->
    <link rel="stylesheet" href="{{ asset('css/sensors-edit-styles.css') }}">

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header ">
                    <h4><i class="bi bi-sensors"></i> Editar Sensor: {{ $sensor->name }}</h4>
                </div>
                <div class="card-body">
                    <form id="sensorEditForm">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre del Sensor <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $sensor->name }}"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="identifier" class="form-label">Identificador <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="identifier" name="identifier"
                                value="{{ $sensor->identifier }}" required>
                            <div class="form-text">Ej: SENSOR-001</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea class="form-control" id="description" name="description"
                                rows="2">{{ $sensor->description }}</textarea>
                        </div>

                        <div class="mb-4">
                            @if($sensor->is_community)
                                <div class="alert alert-success border-success shadow-sm mb-0">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-tree-fill fs-3 me-3 text-success"></i>
                                        <div>
                                            <h5 class="alert-heading fw-bold mb-1">Este es un Medidor de Consumo Común</h5>
                                            <p class="mb-1 small text-dark">
                                                <strong>¿Cómo funciona el cálculo?</strong> El sistema separará automáticamente
                                                la sumatoria de consumos de este componente.
                                            </p>

                                            <div class="card border-0 bg-white shadow-sm mt-3 p-3 rounded">
                                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-diagram-3"></i> Modalidad de
                                                    Distribución Financiera</h6>
                                                <div class="form-check form-switch mb-1">
                                                    <input type="hidden" name="metadata[prorratear_comunidad]" value="0">
                                                    <input class="form-check-input bg-success" type="checkbox" role="switch"
                                                        name="metadata[prorratear_comunidad]" id="prorratear_comunidad"
                                                        value="1" {{ ($sensor->metadata['prorratear_comunidad'] ?? '1') == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold ms-1"
                                                        for="prorratear_comunidad">Prorratear automáticamente este cargo entre
                                                        todos los lotes privados</label>
                                                </div>
                                                <div class="form-text small text-dark mt-0 lh-sm"><i
                                                        class="bi bi-info-circle"></i> Si lo desactivas, este medidor funcionará
                                                    en <strong>Modo Estadístico</strong>. Los gastos serán absorbidos por la
                                                    administración central y NO se dividirán a los vecinos.</div>
                                            </div>

                                            <input type="hidden" name="is_community" id="is_community" value="1">
                                        </div>
                                    </div>
                                </div>
                            @else
                                <input type="hidden" name="is_community" id="is_community" value="0">
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="group_id" class="form-label">Grupo de Sensores <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="group_id" name="group_id" required>
                                <option value="" selected disabled>Selecciona un grupo...</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ $sensor->group_id == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }} ({{ $group->template->name ?? 'Sin plantilla' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- ✅ Contenedor dinámico de campos Metadata y Plantilla -->
                        <div id="dynamic-metadata-container" class="mt-4 mb-3 p-3 bg-light rounded border shadow-sm" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3 flex-wrap gap-2">
                                <h5 class="mb-0"><i class="bi bi-card-list text-primary me-1"></i> Datos Específicos del Sensor</h5>
                                <span id="template-badge-info" class="badge bg-primary-subtle text-primary border border-primary-subtle"></span>
                            </div>
                            <div id="template-system-fields-info" class="mb-3"></div>
                            <div id="metadata-fields" class="row g-3"></div>
                        </div>

                        <!-- 📅 Programación Particular de Toma (Opcional) -->
                        @php
                            $sensorSched = $sensor->metadata['schedule'] ?? [];
                            $hasOverride = !empty($sensorSched['override']);
                            $sMode = $sensorSched['mode'] ?? 'fixed_day';
                            $sDay = $sensorSched['day_of_month'] ?? 15;
                            $sDate = $sensorSched['specific_date'] ?? now()->format('Y-m-15');
                            $sInterval = $sensorSched['interval_days'] ?? 30;
                            $sPreNotice = $sensorSched['pre_notice_days'] ?? 3;
                            $sTolerance = $sensorSched['tolerance_days'] ?? 5;
                        @endphp
                        <div class="card mt-4 mb-3 border-primary">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold"><i class="bi bi-calendar2-check me-1"></i> Programación en Calendario (Opcional)</h6>
                                <span class="badge bg-light text-primary">Tolerancias y Aviso</span>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-2">
                                    <input type="hidden" name="metadata[schedule][override]" value="0">
                                    <input class="form-check-input" type="checkbox" id="sensorScheduleOverride"
                                           name="metadata[schedule][override]" value="1" {{ $hasOverride ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="sensorScheduleOverride">
                                        Definir fecha y tolerancia particular para este sensor (no heredar del grupo)
                                    </label>
                                </div>
                                <small class="text-muted d-block mb-3">
                                    Si está desactivado, este sensor seguirá automáticamente la fecha programada de su grupo.
                                </small>

                                <div id="sensorScheduleFields" style="display: {{ $hasOverride ? 'block' : 'none' }};">
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <label class="form-label small fw-bold">Modalidad</label>
                                            <select class="form-select" name="metadata[schedule][mode]" id="sensorScheduleMode">
                                                <option value="fixed_day" {{ $sMode === 'fixed_day' ? 'selected' : '' }}>Día fijo de cada mes (Ej: Día 15)</option>
                                                <option value="specific_date" {{ $sMode === 'specific_date' ? 'selected' : '' }}>Fecha puntual</option>
                                                <option value="interval" {{ $sMode === 'interval' ? 'selected' : '' }}>Cada N días</option>
                                            </select>
                                        </div>
                                        <div class="col-md-7 {{ $sMode === 'fixed_day' ? '' : 'd-none' }}" id="sBoxFixedDay">
                                            <label class="form-label small fw-bold">Día del Mes (1-31)</label>
                                            <input type="number" class="form-control" name="metadata[schedule][day_of_month]" value="{{ $sDay }}" min="1" max="31">
                                        </div>
                                        <div class="col-md-7 {{ $sMode === 'specific_date' ? '' : 'd-none' }}" id="sBoxSpecificDate">
                                            <label class="form-label small fw-bold">Fecha Exacta</label>
                                            <input type="date" class="form-control" name="metadata[schedule][specific_date]" value="{{ $sDate }}">
                                        </div>
                                        <div class="col-md-7 {{ $sMode === 'interval' ? '' : 'd-none' }}" id="sBoxInterval">
                                            <label class="form-label small fw-bold">Frecuencia (días)</label>
                                            <input type="number" class="form-control" name="metadata[schedule][interval_days]" value="{{ $sInterval }}" min="1" max="365">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">🔔 Días de Preaviso (Antes)</label>
                                            <input type="number" class="form-control" name="metadata[schedule][pre_notice_days]" value="{{ $sPreNotice }}" min="0" max="60">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">⏳ Días de Tolerancia (Después)</label>
                                            <input type="number" class="form-control" name="metadata[schedule][tolerance_days]" value="{{ $sTolerance }}" min="0" max="60">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('sensors.show', ['sensor' => $sensor->id]) }}" class="btn btn-secondary me-2">
                                <i class="bi bi-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@php
    $groupTemplatesPayload = [];
    foreach ($groups as $g) {
        if ($g->template) {
            $groupTemplatesPayload[$g->id] = [
                'id' => $g->template->id,
                'name' => $g->template->name,
                'type' => $g->template->type,
                'is_default' => (bool) $g->template->is_default,
                'campos' => $g->template->getFields(),
            ];
        }
    }
@endphp

@push('scripts')
    <script>
        $(document).ready(function () {
            const existingMetadata = @json($sensor->metadata ?? []);
            const groupTemplatesMap = @json($groupTemplatesPayload);
            const structuralMeasurementFields = ['identificador', 'valor', 'consumo_m3', 'energia_kwh', 'caudal_lmin', 'iluminacion_lux', 'foto', 'fecha_medicion', 'tipo'];
            const internalMetadataKeys = ['schedule', 'prorratear_comunidad'];

            function formatFieldLabel(name) {
                if (!name) return '';
                const text = String(name).replace(/_/g, ' ');
                return text.charAt(0).toUpperCase() + text.slice(1);
            }

            function renderTemplateFields(templateData) {
                const container = $('#dynamic-metadata-container');
                const fieldsContainer = $('#metadata-fields');
                const systemInfoContainer = $('#template-system-fields-info');
                const badgeInfo = $('#template-badge-info');

                fieldsContainer.empty();
                systemInfoContainer.empty();

                const campos = (templateData && Array.isArray(templateData.campos))
                    ? templateData.campos
                    : (templateData && templateData.schema && Array.isArray(templateData.schema.campos) ? templateData.schema.campos : []);

                if (templateData && templateData.name) {
                    const typeBadge = templateData.is_default ? 'Por defecto' : 'Personalizada';
                    badgeInfo.html(`<i class="bi bi-file-earmark-text me-1"></i> Plantilla: ${templateData.name} (${typeBadge})`).show();
                } else {
                    badgeInfo.hide();
                }

                // 1. Separar campos base de toma (valor, foto, identificador) de los campos específicos/extendidos de la plantilla
                const baseFields = campos.filter(c => c && (structuralMeasurementFields.includes(c.nombre) || c.es_foto));
                const editableTemplateFields = campos.filter(c => c && !structuralMeasurementFields.includes(c.nombre) && !c.es_foto);

                if (baseFields.length > 0) {
                    const baseBadges = baseFields.map(c => {
                        const unitTxt = c.unidad ? ` (${c.unidad})` : '';
                        if (c.nombre === 'identificador') {
                            return `<span class="badge bg-warning-subtle text-dark border border-warning-subtle"><i class="bi bi-key-fill me-1"></i>${c.nombre}: vinculado al campo Identificador superior</span>`;
                        }
                        return `<span class="badge bg-white text-secondary border"><i class="bi bi-rulers me-1"></i>${c.nombre}${unitTxt} (se registra al tomar medición)</span>`;
                    }).join(' ');

                    systemInfoContainer.html(`
                        <div class="p-2 bg-white rounded border small text-muted d-flex flex-wrap align-items-center gap-2">
                            <span class="fw-semibold text-dark"><i class="bi bi-layers me-1 text-primary"></i>Campos base de plantilla:</span>
                            ${baseBadges}
                        </div>
                    `);
                }

                // 2. Construir lista unificada de campos editables en el sensor:
                //    a) Todos los campos específicos de la plantilla asignada (ej: voltaje, corriente, presion, lote, propietario, etc.)
                //    b) Cualquier metadato extra que el sensor ya tenga guardado en BD (ej: importado por CSV) y que no esté en la plantilla
                const renderedFieldNames = new Set();
                let html = '';

                editableTemplateFields.forEach(field => {
                    renderedFieldNames.add(field.nombre);

                    const isSensorContext = field.contexto === 'sensor';
                    const isRequired = isSensorContext && Boolean(field.requerido);
                    const requiredAttr = isRequired ? 'required' : '';
                    const reqAsterisk = isRequired ? '<span class="text-danger">*</span>' : '';

                    let inputType = 'text';
                    if (field.tipo === 'numero') inputType = 'number';
                    if (field.tipo === 'fecha') inputType = 'date';

                    const unitLabel = field.unidad ? `<span class="badge bg-secondary-subtle text-secondary ms-1">${field.unidad}</span>` : '';
                    const contextBadge = isSensorContext
                        ? `<span class="badge bg-info-subtle text-info border border-info-subtle ms-auto" style="font-size:0.68rem;">Atributo de Sensor</span>`
                        : `<span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-auto" style="font-size:0.68rem;">Parámetro de Plantilla</span>`;

                    const placeholder = field.unidad ? `Valor en ${field.unidad}` : 'Ingresar valor (opcional)';

                    let currentValue = (existingMetadata && existingMetadata[field.nombre] !== undefined && existingMetadata[field.nombre] !== null)
                        ? existingMetadata[field.nombre]
                        : (field.valor_por_defecto ?? '');

                    if (currentValue === '' && $('#is_community').val() == '1' && inputType === 'text') {
                        currentValue = 'Área Común';
                    }

                    if (field.tipo === 'booleano') {
                        html += `
                            <div class="col-md-6">
                                <label class="form-label d-flex align-items-center mb-1">
                                    <span class="fw-semibold">${formatFieldLabel(field.nombre)}</span> ${reqAsterisk} ${unitLabel} ${contextBadge}
                                </label>
                                <select class="form-select" name="metadata[${field.nombre}]" ${requiredAttr}>
                                    <option value="" ${currentValue === '' ? 'selected' : ''}>Sin especificar</option>
                                    <option value="1" ${String(currentValue) === '1' || currentValue === true ? 'selected' : ''}>Sí</option>
                                    <option value="0" ${String(currentValue) === '0' || currentValue === false ? 'selected' : ''}>No</option>
                                </select>
                            </div>
                        `;
                    } else {
                        html += `
                            <div class="col-md-6">
                                <label class="form-label d-flex align-items-center mb-1">
                                    <span class="fw-semibold">${formatFieldLabel(field.nombre)}</span> ${reqAsterisk} ${unitLabel} ${contextBadge}
                                </label>
                                <div class="input-group">
                                    <input type="${inputType}" step="any" class="form-control" name="metadata[${field.nombre}]" value="${String(currentValue).replace(/"/g, '&quot;')}" placeholder="${$('#is_community').val() == '1' && inputType === 'text' ? 'Ej: Área Común Central' : placeholder}" ${requiredAttr}>
                                    ${field.unidad ? `<span class="input-group-text bg-white text-muted">${field.unidad}</span>` : ''}
                                </div>
                                ${field.valor_por_defecto ? '<div class="form-text small">Referencia por defecto: ' + field.valor_por_defecto + '</div>' : ''}
                            </div>
                        `;
                    }
                });

                // 3. Agregar campos existentes en existingMetadata que no estén en la plantilla ni sean internos
                if (existingMetadata && typeof existingMetadata === 'object') {
                    Object.keys(existingMetadata).forEach(key => {
                        if (renderedFieldNames.has(key) || internalMetadataKeys.includes(key) || structuralMeasurementFields.includes(key)) {
                            return;
                        }
                        const val = existingMetadata[key];
                        if (val !== null && typeof val === 'object') {
                            return;
                        }
                        renderedFieldNames.add(key);
                        const safeVal = val !== undefined && val !== null ? String(val).replace(/"/g, '&quot;') : '';
                        html += `
                            <div class="col-md-6">
                                <label class="form-label d-flex align-items-center mb-1">
                                    <span class="fw-semibold">${formatFieldLabel(key)}</span>
                                    <span class="badge bg-secondary-subtle text-secondary border ms-auto" style="font-size:0.68rem;">Metadato Guardado</span>
                                </label>
                                <input type="text" class="form-control" name="metadata[${key}]" value="${safeVal}" placeholder="Ingresar valor">
                            </div>
                        `;
                    });
                }

                if (html !== '' || baseFields.length > 0) {
                    if (html === '') {
                        fieldsContainer.html(`
                            <div class="col-12">
                                <div class="text-muted small fst-italic">
                                    <i class="bi bi-info-circle me-1"></i> Esta plantilla solo utiliza los campos base de lectura (identificador, valor y foto).
                                </div>
                            </div>
                        `);
                    } else {
                        fieldsContainer.html(html);
                    }
                    container.fadeIn('fast');
                } else {
                    container.hide();
                }
            }

            function loadGroupFields(groupId) {
                if (!groupId) {
                    $('#dynamic-metadata-container').hide();
                    return;
                }

                // 1. Carga inmediata desde el mapa pre-cargado por PHP
                if (groupTemplatesMap[groupId]) {
                    renderTemplateFields(groupTemplatesMap[groupId]);
                    return;
                }

                // 2. Fallback vía API si el grupo no estaba en el mapa inicial
                const token = localStorage.getItem('token');
                if (!token) return;

                $.ajax({
                    url: `/api/sensor-groups/${groupId}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    },
                    success: function (response) {
                        if (response.success && response.data && response.data.template) {
                            renderTemplateFields(response.data.template);
                        }
                    }
                });
            }

            $('#sensorScheduleOverride').change(function () {
                if ($(this).is(':checked')) {
                    $('#sensorScheduleFields').slideDown();
                } else {
                    $('#sensorScheduleFields').slideUp();
                }
            });

            $('#sensorScheduleMode').change(function () {
                const mode = $(this).val();
                $('#sBoxFixedDay').toggleClass('d-none', mode !== 'fixed_day');
                $('#sBoxSpecificDate').toggleClass('d-none', mode !== 'specific_date');
                $('#sBoxInterval').toggleClass('d-none', mode !== 'interval');
            });

            $('#group_id').change(function () {
                loadGroupFields($(this).val());
            });

            // Carga inicial
            if ($('#group_id').val()) {
                loadGroupFields($('#group_id').val());
            }

            $('#sensorEditForm').submit(function (e) {
                e.preventDefault();

                const formData = $(this).serialize();
                const token = localStorage.getItem('token');
                const sensorId = {{ $sensor->id }};

                if (!token) {
                    alert('No se encontró un token de autenticación. Por favor, inicia sesión nuevamente.');
                    window.location.href = '{{ route("login") }}';
                    return;
                }

                $.ajax({
                    url: `/api/sensors/${sensorId}`,
                    type: 'PUT',
                    data: formData,
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.success) {
                            alert('Sensor actualizado correctamente');
                            window.location.href = '{{ route("sensors.show", ["sensor" => $sensor->id]) }}';
                        } else {
                            alert(response.message || 'Error al actualizar el sensor');
                        }
                    },
                    error: function (xhr) {
                        let errorMessage = 'Error al actualizar el sensor';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            let errorText = '';
                            for (const field in errors) {
                                errorText += `${field}: ${errors[field].join(', ')} \n`;
                            }
                            errorMessage = errorText || 'Error de validación';
                        } else {
                            errorMessage = xhr.statusText || errorMessage;
                        }
                        alert(errorMessage);
                    }
                });
            });
        });
    </script>
@endpush