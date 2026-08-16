@extends('layouts.modern')

@section('title', 'Crear Sensor - MeasureFlow')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header border-0 text-white p-3 d-flex justify-content-between align-items-center"
                        style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-cpu-fill fs-4"></i>
                            <div class="d-flex flex-column">
                                <h5 class="mb-0 fw-bold">Crear Nuevo Sensor</h5>
                                <span class="opacity-75" style="font-size: 0.8rem;">Registra un nuevo medidor físico o
                                    virtual</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body bg-light p-4">
                        <!-- ✅ Mostrar información útil -->
                        <div class="alert alert-info border-info-subtle shadow-sm d-flex align-items-start mb-4 rounded-3">
                            <i class="bi bi-info-circle-fill fs-3 me-3 text-info"></i>
                            <div>
                                <h6 class="alert-heading fw-bold mb-1">Estructura Requerida</h6>
                                <p class="mb-0 small text-dark">
                                    Para registrar un sensor, necesitas asignarlo a un <strong>Grupo de Lotes
                                        lógicos</strong>. Si no ves ningún grupo disponible, debes <a
                                        href="{{ route('sensor-groups.create') }}" class="alert-link">crear un Grupo
                                        primero</a>.
                                </p>
                            </div>
                        </div>

                        <form id="sensorForm" class="bg-white p-4 rounded-4 shadow-sm border border-light-subtle">
                            @csrf
                            <h6 class="text-uppercase text-muted fw-bold mb-4 border-bottom pb-2"
                                style="font-size: 0.75rem; letter-spacing: 1px;">Identidad del Medidor</h6>

                            <div class="row g-4 mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label small fw-semibold text-muted mb-1">Nombre
                                        Descriptivo <span class="text-danger">*</span></label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text bg-white text-muted border-end-0"><i
                                                class="bi bi-tag"></i></span>
                                        <input type="text" class="form-control border-start-0 ps-0" id="name" name="name"
                                            placeholder="Ej: Medidor de Presión Norte" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="identifier"
                                        class="form-label small fw-semibold text-muted mb-1">Identificador Físico (MAC /
                                        Serial) <span class="text-danger">*</span></label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text bg-white text-muted border-end-0"><i
                                                class="bi bi-upc-scan"></i></span>
                                        <input type="text" class="form-control border-start-0 ps-0" id="identifier"
                                            name="identifier" required
                                            value="{{ request('community') == '1' ? 'COMUNITARIO-' . strtoupper(Str::random(3)) : '' }}"
                                            placeholder="Ej: SENSOR-001">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="description" class="form-label small fw-semibold text-muted mb-1">Notas de
                                    Emplazamiento</label>
                                <div class="input-group shadow-sm">
                                    <span
                                        class="input-group-text bg-white text-muted border-end-0 align-items-start pt-2"><i
                                            class="bi bi-file-text"></i></span>
                                    <textarea class="form-control border-start-0 ps-0" id="description" name="description"
                                        rows="2"
                                        placeholder="Describe la ubicación exacta o el propósito de este sensor..."></textarea>
                                </div>
                            </div>

                            <div class="mb-4">
                                @if(request('community') == '1')
                                    <div class="alert alert-success border-success shadow-sm mb-0">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-tree-fill fs-3 me-3 text-success"></i>
                                            <div>
                                                <h5 class="alert-heading fw-bold mb-1">Has activado el Formulario de Medidor
                                                    Comunitario</h5>
                                                <p class="mb-1 small text-dark">
                                                    <strong>¿Cómo funciona el cálculo?</strong> El sistema separará
                                                    automáticamente la sumatoria de consumos de este componente.
                                                </p>

                                                <div class="card border-0 bg-white shadow-sm mt-3 p-3 rounded">
                                                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-diagram-3"></i> Modalidad
                                                        de Distribución Financiera</h6>
                                                    <div class="form-check form-switch mb-1">
                                                        <input type="hidden" name="metadata[prorratear_comunidad]" value="0">
                                                        <input class="form-check-input bg-success" type="checkbox" role="switch"
                                                            name="metadata[prorratear_comunidad]" id="prorratear_comunidad"
                                                            value="1" checked>
                                                        <label class="form-check-label fw-bold ms-1"
                                                            for="prorratear_comunidad">Prorratear automáticamente este cargo
                                                            entre todos los lotes privados</label>
                                                    </div>
                                                    <div class="form-text small text-dark mt-0 lh-sm"><i
                                                            class="bi bi-info-circle"></i> Si lo desactivas, este medidor
                                                        funcionará en <strong>Modo Estadístico</strong>. Los gastos serán
                                                        absorbidos por la administración central y NO se dividirán a los
                                                        vecinos.</div>
                                                </div>

                                                <input type="hidden" name="is_community" id="is_community" value="1">
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <input type="hidden" name="is_community" id="is_community" value="0">
                                @endif
                            </div>

                            <h6 class="text-uppercase text-muted fw-bold mb-3 mt-4 border-bottom pb-2"
                                style="font-size: 0.75rem; letter-spacing: 1px;">Asignación Lógica</h6>

                            <div class="mb-3">
                                <label for="group_id" class="form-label small fw-semibold text-muted mb-1">Grupo de
                                    Instalación Base <span class="text-danger">*</span></label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white text-muted border-end-0"><i
                                            class="bi bi-diagram-3"></i></span>
                                    <select class="form-select border-start-0 ps-0 fw-bold text-dark" id="group_id"
                                        name="group_id" required>
                                        <option value="" selected disabled>Selecciona un Lote/Grupo...</option>
                                        @forelse($groups as $group)
                                            <option value="{{ $group->id }}" {{ (isset($groupId) && $groupId == $group->id) ? 'selected' : '' }}>
                                                {{ $group->name }} ({{ $group->template->name ?? 'Sin plantilla' }})
                                            </option>
                                        @empty
                                            <option value="" disabled>⚠️ Consumo de Grupo bloqueado: No tienes grupos
                                                disponibles.</option>
                                        @endforelse
                                    </select>
                                </div>
                                @if($groups->isEmpty())
                                    <div class="mt-2 text-end">
                                        <small class="text-muted">
                                            <a href="{{ route('sensor-groups.create') }}"
                                                class="btn btn-sm btn-outline-primary rounded-pill px-3 mt-1 shadow-sm">
                                                <i class="bi bi-plus-circle"></i> Nuevo Grupo
                                            </a>
                                        </small>
                                    </div>
                                @endif
                            </div>

                            <!-- ✅ Contenedor dinámico de campos Metadata -->
                            <div id="dynamic-metadata-container"
                                class="mt-4 mb-3 p-3 bg-light rounded border border-light-subtle shadow-sm"
                                style="display: none;">
                                <h6 class="text-uppercase text-dark fw-bold mb-3 border-bottom border-secondary-subtle pb-2"
                                    style="font-size: 0.75rem; letter-spacing: 1px;"><i
                                        class="bi bi-card-checklist me-1"></i> Propiedades Extendidas de Plantilla</h6>
                                <div id="metadata-fields" class="row g-3"></div>
                            </div>

                            <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                                <a href="{{ route('sensors.index') }}"
                                    class="btn btn-light border-secondary-subtle shadow-sm px-4">
                                    <i class="bi bi-arrow-left"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold" id="submitBtn">
                                    <i class="bi bi-plus-circle me-1"></i> Desplegar Sensor
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
        $(document).ready(function () {
            // ✅ Escuchar cambios en el selector de Grupo para traer la plantilla
            $('#group_id').change(function () {
                const groupId = $(this).val();
                const container = $('#dynamic-metadata-container');
                const fieldsContainer = $('#metadata-fields');
                fieldsContainer.empty();
                container.hide();

                if (!groupId) return;

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
                            const schema = response.data.template.schema;
                            if (schema && schema.campos) {
                                // Extraer SOLO campos estáticos para el Sensor, excluyendo 'identificador' que ya es nativo
                                const sensorFields = schema.campos.filter(c => c.contexto === 'sensor' && c.nombre !== 'identificador');

                                if (sensorFields.length > 0) {
                                    let html = '';
                                    sensorFields.forEach(field => {
                                        const requiredAttr = field.requerido ? 'required' : '';
                                        const reqAsterisk = field.requerido ? '<span class="text-danger">*</span>' : '';

                                        let inputType = 'text';
                                        if (field.tipo === 'numero') inputType = 'number';
                                        if (field.tipo === 'fecha') inputType = 'date';

                                        const placeholder = field.unidad ? `(${field.unidad})` : 'Ingresar valor';

                                        let defaultVal = '';
                                        if ($('#is_community').val() == '1' && inputType === 'text') {
                                            defaultVal = 'Área Común'; // Sugerencia de Lote
                                        }

                                        html += `
                                                <div class="col-md-6 mb-2">
                                                    <label class="form-label small fw-semibold text-muted mb-1">${field.nombre} ${reqAsterisk}</label>
                                                    <div class="input-group shadow-sm">
                                                        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-braces"></i></span>
                                                        <input type="${inputType}" step="any" class="form-control border-start-0 ps-0" name="metadata[${field.nombre}]" value="${defaultVal}" placeholder="${$('#is_community').val() == '1' ? 'Ej: Área Común Central' : placeholder}" ${requiredAttr}>
                                                    </div>
                                                    ${field.valor_por_defecto ? '<div class="form-text mt-1" style="font-size:0.7rem;"><i class="bi bi-info-circle"></i> Sugerido: ' + field.valor_por_defecto + '</div>' : ''}
                                                </div>
                                            `;
                                    });
                                    fieldsContainer.html(html);
                                    container.hide().fadeIn('fast');
                                }
                            }
                        }
                    },
                    error: function (xhr) {
                        console.error("No se pudo cargar la plantilla del grupo.", xhr);
                    }
                });
            });

            // ✅ Si el grupo ya estaba preseleccionado por PHP (vía group_id en URL), disparar el evento
            if ($('#group_id').val()) {
                $('#group_id').trigger('change');
            }

            $('#sensorForm').submit(function (e) {
                e.preventDefault();

                const formData = $(this).serialize();
                const token = localStorage.getItem('token');

                if (!token) {
                    alert('No se encontró un token de autenticación. Por favor, inicia sesión nuevamente.');
                    window.location.href = '{{ route("login") }}';
                    return;
                }

                $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Guardando...');

                $.ajax({
                    url: '/api/sensors',
                    type: 'POST',
                    data: formData,
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.success) {
                            alert('Sensor creado correctamente');
                            window.location.href = '/sensors';
                        } else {
                            alert(response.message || 'Error al crear el sensor');
                        }
                    },
                    error: function (xhr) {
                        let errorMessage = 'Error al crear el sensor';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            let errorText = '';
                            for (const field in errors) {
                                errorText += `${field}: ${errors[field].join(', ')}\n`;
                            }
                            errorMessage = errorText || 'Error de validación';
                        } else {
                            errorMessage = xhr.statusText || errorMessage;
                        }
                        alert(errorMessage);
                    },
                    complete: function () {
                        $('#submitBtn').prop('disabled', false).html(`
                                                                        < i class= "bi bi-check-circle" ></i > Guardar Sensor
                                                            `);
                    }
                });
            });
        });
    </script>
@endpush