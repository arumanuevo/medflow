@extends('layouts.modern')

@section('title', 'Crear Sensor - MeasureFlow')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header ">
                        <h4><i class="bi bi-sensors"></i> Crear Nuevo Sensor</h4>
                    </div>
                    <div class="card-body">
                        <!-- ✅ Mostrar información útil -->
                        <div class="alert alert-info">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-info-circle-fill fs-3 me-3"></i>
                                </div>
                                <div>
                                    <h5 class="alert-heading">¡Importante!</h5>
                                    <p class="mb-0">
                                        Para crear un sensor, necesitas seleccionar un <strong>grupo de sensores</strong>
                                        existente.
                                        Si no ves ningún grupo en el selector, debes <a
                                            href="{{ route('sensor-groups.create') }}">crear un grupo primero</a>.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <form id="sensorForm">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre del Sensor <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="form-text">Ej: "Sensor pH 01", "Medidor de Presión Norte"</div>
                            </div>

                            <div class="mb-3">
                                <label for="identifier" class="form-label">Identificador <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="identifier" name="identifier" required>
                                <div class="form-text">Un código único para identificar este sensor. Ej: "SENSOR-001"</div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Descripción</label>
                                <textarea class="form-control" id="description" name="description" rows="2"
                                    placeholder="Describe la ubicación o propósito de este sensor..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="group_id" class="form-label">Grupo de Sensores <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="group_id" name="group_id" required>
                                    <option value="" selected disabled>Selecciona un grupo...</option>
                                    @forelse($groups as $group)
                                        <option value="{{ $group->id }}" {{ (isset($groupId) && $groupId == $group->id) ? 'selected' : '' }}>
                                            {{ $group->name }} ({{ $group->template->name ?? 'Sin plantilla' }})
                                        </option>
                                    @empty
                                        <option value="" disabled>⚠️ No tienes grupos disponibles. Crea uno primero.</option>
                                    @endforelse
                                </select>
                                <div class="mt-2">
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle"></i>
                                            ¿No ves tu grupo?
                                            <a href="{{ route('sensor-groups.create') }}" class="text-decoration-none">
                                                <i class="bi bi-plus-circle"></i> Crear Grupo
                                            </a>
                                        </small>
                                    </div>
                                </div>

                                <!-- ✅ Contenedor dinámico de campos Metadata -->
                                <div id="dynamic-metadata-container" class="mt-4 mb-3" style="display: none;">
                                    <h5 class="border-bottom pb-2 mb-3"><i class="bi bi-card-list"></i> Datos Específicos
                                        del Sensor</h5>
                                    <div id="metadata-fields"></div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <a href="{{ route('sensors.index') }}" class="btn btn-secondary me-2">
                                        <i class="bi bi-arrow-left"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="bi bi-check-circle"></i> Guardar Sensor
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
                                // Extraer SOLO campos estáticos para el Sensor
                                const sensorFields = schema.campos.filter(c => c.contexto === 'sensor');

                                if (sensorFields.length > 0) {
                                    let html = '';
                                    sensorFields.forEach(field => {
                                        const requiredAttr = field.requerido ? 'required' : '';
                                        const reqAsterisk = field.requerido ? '<span class="text-danger">*</span>' : '';

                                        let inputType = 'text';
                                        if (field.tipo === 'numero') inputType = 'number';
                                        if (field.tipo === 'fecha') inputType = 'date';

                                        const placeholder = field.unidad ? `(${field.unidad})` : 'Ingresar valor';

                                        html += \`
                                        <div class="mb-3">
                                            <label class="form-label">\${field.nombre} \${reqAsterisk}</label>
                                            <input type="\${inputType}" step="any" class="form-control" name="metadata[\${field.nombre}]" placeholder="\${placeholder}" \${requiredAttr}>
                                            \${field.valor_por_defecto ? '<div class="form-text">Referencia/Valor por defecto: ' + field.valor_por_defecto + '</div>' : ''}
                                        </div>
                                    \`;
                                });
                                fieldsContainer.html(html);
                                container.fadeIn();
                            }
                        }
                    }
                },
                error: function(xhr) {
                    console.error("No se pudo cargar la plantilla del grupo.", xhr);
                }
            });
        });

        $('#sensorForm').submit(function(e) {
            e.preventDefault();

            const formData = $(this).serialize();
            const token = localStorage.getItem('token');

            if (!token) {
                alert('No se encontró un token de autenticación. Por favor, inicia sesión nuevamente.');
                window.location.href = '{{ route("login") }}';
                return;
            }

            $('#submitBtn').prop('disabled', true).html(`
                                            < span class="spinner-border spinner-border-sm" role = "status" ></span >
                                                Guardando...
                                    `);

            $.ajax({
                url: '/api/sensors',
                type: 'POST',
                data: formData,
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        alert('Sensor creado correctamente');
                        window.location.href = '{{ route("sensors.index") }}';
                    } else {
                        alert(response.message || 'Error al crear el sensor');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Error al crear el sensor';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        let errorText = '';
                        for (const field in errors) {
                            errorText += `${ field }: ${ errors[field].join(', ') }\n`;
                        }
                        errorMessage = errorText || 'Error de validación';
                    } else {
                        errorMessage = xhr.statusText || errorMessage;
                    }
                    alert(errorMessage);
                },
                complete: function() {
                    $('#submitBtn').prop('disabled', false).html(`
                                    < i class= "bi bi-check-circle" ></i > Guardar Sensor
                    `);
                }
            });
        });
    });
    </script>
@endpush