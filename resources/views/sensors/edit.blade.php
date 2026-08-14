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
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- ✅ Contenedor dinámico de campos Metadata -->
                        <div id="dynamic-metadata-container" class="mt-4 mb-3" style="display: none;">
                            <h5 class="border-bottom pb-2 mb-3"><i class="bi bi-card-list"></i> Datos Específicos del Sensor
                            </h5>
                            <div id="metadata-fields"></div>
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

@push('scripts')
    <script>
        $(document).ready(function () {
            const existingMetadata = @json($sensor->metadata ?? []);

            function loadGroupFields(groupId) {
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
                                // Evitamos volver a renderizar 'identificador' dinámicamente porque ya es un campo principal (identifier)
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

                                        // ✅ Usar valor preexistente si lo hay, si no el por defecto (o sugerencia de Área Común)
                                        let currentValue = existingMetadata[field.nombre] !== undefined ? existingMetadata[field.nombre] : '';

                                        if (currentValue === '' && $('#is_community').val() == '1' && inputType === 'text') {
                                            currentValue = 'Área Común';
                                        }

                                        html += `
                                                                <div class="mb-3">
                                                                    <label class="form-label">${field.nombre} ${reqAsterisk}</label>
                                                                    <input type="${inputType}" step="any" class="form-control" name="metadata[${field.nombre}]" value="${currentValue}" placeholder="${$('#is_community').val() == '1' ? 'Ej: Área Común Central' : placeholder}" ${requiredAttr}>
                                                                    ${field.valor_por_defecto ? '<div class="form-text">Referencia: ' + field.valor_por_defecto + '</div>' : ''}
                                                                </div>
                                                            `;
                                    });
                                    fieldsContainer.html(html);
                                    container.fadeIn();
                                }
                            }
                        }
                    }
                });
            }

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