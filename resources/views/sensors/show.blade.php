@extends('layouts.modern')

@section('title', 'Detalles del Sensor - MeasureFlow')

@section('content')
    <!-- Incluir el archivo CSS externo -->
    <link rel="stylesheet" href="{{ asset('css/sensor-details-styles.css') }}">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="bi bi-sensors"></i> Detalles del Sensor</h4>
                    <div>
                        <a href="{{ route('sensors.index') }}" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Volver a Sensores
                        </a>
                        @auth
                            @if(
                                    auth()->user()->hasRole('admin') ||
                                    (isset($sensor->group) && $sensor->group->user_id === auth()->user()->id)
                                )
                                <a href="{{ route('sensors.edit', ['sensor' => $sensor->id]) }}" class="btn btn-warning">
                                    <i class="bi bi-pencil"></i> Editar Sensor
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
                <div class="card-body">
                    <!-- Información básica del sensor -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5><i class="bi bi-info-circle"></i> Información Básica</h5>
                            <hr>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Nombre:</strong></div>
                                <div class="col-md-8">{{ $sensor->name }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Identificador:</strong></div>
                                <div class="col-md-8">{{ $sensor->identifier }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Descripción:</strong></div>
                                <div class="col-md-8">{{ $sensor->description ?? 'Sin descripción' }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Fecha de creación:</strong></div>
                                <div class="col-md-8">{{ $sensor->created_at->format('d/m/Y H:i') }}</div>
                            </div>

                            @if ($sensor->metadata && !empty($sensor->metadata))
                                <h6 class="mt-4 text-primary"><i class="bi bi-tags"></i> Propiedades Dinámicas (Plantilla)</h6>
                                <hr class="mt-1 mb-2">
                                <div class="p-3 bg-light rounded border">
                                    @foreach ($sensor->metadata as $key => $value)
                                        @if($value !== null && $value !== '')
                                            <div class="row mb-2">
                                                <div class="col-md-4 text-capitalize text-muted"><i class="bi bi-tag-fill me-1"
                                                        style="font-size:0.7rem;"></i>{{ str_replace('_', ' ', $key) }}:</div>
                                                <div class="col-md-8 fw-bold">{{ $value }}</div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Información del grupo -->
                        <div class="col-md-6">
                            <h5><i class="bi bi-folder"></i> Grupo Asociado</h5>
                            <hr>
                            @if(isset($sensor->group))
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Nombre del grupo:</strong></div>
                                    <div class="col-md-8">{{ $sensor->group->name }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Descripción:</strong></div>
                                    <div class="col-md-8">{{ $sensor->group->description ?? 'Sin descripción' }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Dueño:</strong></div>
                                    <div class="col-md-8">{{ $sensor->group->user->name ?? 'Sin dueño' }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Fecha de creación:</strong></div>
                                    <div class="col-md-8">{{ $sensor->group->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4"><strong>Número de sensores:</strong></div>
                                    <div class="col-md-8">{{ $sensor->group->sensors->count() }}</div>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> Este sensor no tiene un grupo asociado.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Información de la plantilla -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5><i class="bi bi-file-earmark-text"></i> Plantilla del Grupo</h5>
                            <hr>
                            @if(isset($sensor->group) && isset($sensor->group->template))
                                <div class="row mb-2">
                                    <div class="col-md-2"><strong>Nombre:</strong></div>
                                    <div class="col-md-10">
                                        {{ $sensor->group->template->name }}
                                        @if($sensor->group->template->is_default)
                                            <span class="template-badge template-type-{{ $sensor->group->template->type }}">Por
                                                defecto</span>
                                        @else
                                            <span class="template-badge template-type-personalizado">Personalizada</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-2"><strong>Tipo:</strong></div>
                                    <div class="col-md-10">{{ $sensor->group->template->type }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-2"><strong>Descripción:</strong></div>
                                    <div class="col-md-10">{{ $sensor->group->template->description ?? 'Sin descripción' }}
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-2"><strong>Campos:</strong></div>
                                    <div class="col-md-10">
                                        @if(isset($sensor->group->template->schema['campos']) && count($sensor->group->template->schema['campos']) > 0)
                                            <ul class="mb-0">
                                                @foreach($sensor->group->template->schema['campos'] as $campo)
                                                    <li>
                                                        <strong>{{ $campo['nombre'] }}</strong> ({{ $campo['tipo'] }})
                                                        @if(isset($campo['unidad']) && $campo['unidad'])
                                                            - Unidad: {{ $campo['unidad'] }}
                                                        @endif
                                                        @if($campo['requerido'])
                                                            <span class="text-danger">(Requerido)</span>
                                                        @else
                                                            <span class="text-muted">(Opcional)</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            Sin campos definidos
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Este sensor no tiene una plantilla asociada.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Acciones disponibles -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5><i class="bi bi-lightning-charge"></i> Acciones</h5>
                            <hr>
                            <div class="d-flex gap-2">
                                <a href="{{ route('measurements.create', ['sensor' => $sensor->id]) }}"
                                    class="btn btn-primary">
                                    <i class="bi bi-rulers"></i> Tomar Medición
                                </a>
                                <!-- Botón de Enlace Público -->
                                <button type="button" class="btn btn-info text-white" data-bs-toggle="modal"
                                    data-bs-target="#publicAccessModal">
                                    <i class="bi bi-link-45deg"></i> Acceso Propietarios
                                </button>
                                @auth
                                    @if(
                                            auth()->user()->hasRole('admin') ||
                                            (isset($sensor->group) && $sensor->group->user_id === auth()->user()->id) ||
                                            (isset($sensor->group) && $sensor->group->sharedAccess()->where('shared_with', auth()->user()->id)->whereIn('role', ['inspector', 'admin'])->exists())
                                        )
                                        <a href="{{ route('sensors.edit', ['sensor' => $sensor->id]) }}" class="btn btn-warning">
                                            <i class="bi bi-pencil"></i> Editar Sensor
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasRole('admin') || (isset($sensor->group) && $sensor->group->user_id === auth()->user()->id))
                                        <button class="btn btn-danger deleteSensorBtn" data-sensor-id="{{ $sensor->id }}">
                                            <i class="bi bi-trash"></i> Eliminar Sensor
                                        </button>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para tomar medición -->
    <div class="modal fade" id="measurementModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tomar Medición para {{ $sensor->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="measurementForm">
                        <input type="hidden" id="sensorId" value="{{ $sensor->id }}">
                        <div class="mb-3">
                            <label for="measurementType" class="form-label">Tipo de Medición</label>
                            <select class="form-select" id="measurementType" required>
                                <option value="agua">Agua</option>
                                <option value="gas">Gas</option>
                                <option value="electricidad">Electricidad</option>
                                <option value="personalizado">Personalizado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="measurementValue" class="form-label">Valor</label>
                            <input type="number" step="0.01" class="form-control" id="measurementValue" required>
                        </div>
                        <div class="mb-3">
                            <label for="measurementDate" class="form-label">Fecha de Medición</label>
                            <input type="datetime-local" class="form-control" id="measurementDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="measurementPhoto" class="form-label">Foto (URL)</label>
                            <input type="text" class="form-control" id="measurementPhoto"
                                placeholder="URL de la foto (opcional)">
                        </div>
                        @if(isset($sensor->group) && isset($sensor->group->template))
                            <div class="mb-3">
                                <label class="form-label">Campos Personalizados</label>
                                <div id="customFieldsContainer">
                                    @foreach($sensor->group->template->schema['campos'] ?? [] as $campo)
                                        @if($campo['nombre'] !== 'valor' && $campo['nombre'] !== 'tipo')
                                            <div class="mb-2">
                                                <label for="{{ $campo['nombre'] }}" class="form-label">
                                                    {{ $campo['nombre'] }}
                                                    @if($campo['requerido']) <span class="text-danger">*</span> @endif
                                                </label>
                                                <input type="{{ $campo['tipo'] === 'numero' ? 'number' : 'text' }}" class="form-control"
                                                    id="{{ $campo['nombre'] }}" {{ $campo['requerido'] ? 'required' : '' }}
                                                    placeholder="{{ $campo['nombre'] }}">
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveMeasurement">Guardar Medición</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Acceso Público -->
    <div class="modal fade" id="publicAccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-share-fill"></i> Acceso Público al Consumidor</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <p class="mb-3 text-muted small text-start">
                        Este medidor tiene un <strong>Visor Público de Solo Lectura</strong>. Puedes enviarle el enlace al
                        propietario o inquilino para que audite sus propios consumos desde su teléfono sin necesidad de
                        registrarse en el sistema.
                    </p>

                    <div class="input-group mb-4">
                        <input type="text" class="form-control text-center text-primary fw-bold" id="publicLinkInput"
                            value="{{ route('public.visor', ['token' => $sensor->public_token ?? '']) }}" readonly>
                        <button class="btn btn-primary" type="button" onclick="copiarLinkPublico()">
                            <i class="bi bi-clipboard"></i> Copiar
                        </button>
                    </div>

                    <h6 class="text-muted mb-2">Código QR</h6>
                    <div class="p-3 bg-light rounded d-inline-block border">
                        <!-- Utilizando una API gratuita y muy ágil para renderizar rápida el QR en frontend -->
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(route('public.visor', ['token' => $sensor->public_token ?? ''])) }}"
                            alt="QR Code" class="img-fluid rounded">
                    </div>
                    <div class="mt-2 text-muted small">Escanea para abrir en el celular</div>
                </div>
                <div class="modal-footer pb-3 border-0 justify-content-center">
                    <button type="button" class="btn btn-secondary w-50" data-bs-dismiss="modal">Cerrar Panel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para confirmar eliminación -->
    <div class="modal fade" id="deleteSensorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que quieres eliminar el sensor <strong>{{ $sensor->name }}</strong>?</p>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> Esta acción no se puede deshacer y eliminará todas las
                        mediciones asociadas a este sensor.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteSensor">Eliminar Sensor</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Función global para copiar
            window.copiarLinkPublico = function () {
                var copyText = document.getElementById("publicLinkInput");
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(copyText.value).then(() => {
                    showAlert('¡Enlace copiado al portapapeles!', 'success');
                });
            };

            // Configurar eventos
            $('.takeMeasurementBtn').click(function () {
                const sensorId = $(this).data('sensor-id');
                $('#sensorId').val(sensorId);
                $('#measurementModal').modal('show');
            });

            $('#measurementType').change(configureMeasurementFields);
            $('#saveMeasurement').click(saveMeasurement);

            $('.deleteSensorBtn').click(function () {
                const sensorId = $(this).data('sensor-id');
                $('#deleteSensorModal').data('sensor-id', sensorId).modal('show');
            });

            $('#confirmDeleteSensor').click(function () {
                const sensorId = $('#deleteSensorModal').data('sensor-id');
                deleteSensor(sensorId);
            });

            // Configurar campos dinámicos según el tipo de medición
            function configureMeasurementFields() {
                const type = $(this).val();
                const customFieldsContainer = $('#customFieldsContainer');
                customFieldsContainer.empty();

                const fieldsByType = {
                    agua: [
                        { name: 'presion', label: 'Presión (bar)', type: 'number', step: '0.1' },
                        { name: 'temperatura', label: 'Temperatura (°C)', type: 'number', step: '0.1' }
                    ],
                    gas: [
                        { name: 'presion', label: 'Presión (bar)', type: 'number', step: '0.1' },
                        { name: 'temperatura', label: 'Temperatura (°C)', type: 'number', step: '0.1' }
                    ],
                    electricidad: [
                        { name: 'voltaje', label: 'Voltaje (V)', type: 'number', step: '0.1' },
                        { name: 'corriente', label: 'Corriente (A)', type: 'number', step: '0.1' }
                    ],
                    personalizado: []
                };

                if (fieldsByType[type]) {
                    fieldsByType[type].forEach(field => {
                        const fieldGroup = $(
                            '<div class="mb-3">' +
                            '   <label for="' + field.name + '" class="form-label">' + field.label + '</label>' +
                            '   <input type="' + field.type + '" class="form-control" id="' + field.name + '"' +
                            '          ' + (field.step ? 'step="' + field.step + '"' : '') + ' placeholder="' + field.label + '">' +
                            '</div>'
                        );
                        customFieldsContainer.append(fieldGroup);
                    });
                }
            }

            // Guardar medición
            function saveMeasurement() {
                const sensorId = $('#sensorId').val();
                const type = $('#measurementType').val();
                const value = $('#measurementValue').val();
                const date = $('#measurementDate').val();
                const photo = $('#measurementPhoto').val();

                if (!sensorId) {
                    showAlert('Debes seleccionar un sensor primero', 'danger');
                    return;
                }

                const customFields = {};
                $('#customFieldsContainer input').each(function () {
                    if ($(this).val()) {
                        customFields[$(this).attr('id')] = $(this).val();
                    }
                });

                const data = {
                    sensor_id: sensorId,
                    data: {
                        tipo: type,
                        valor: parseFloat(value),
                        foto: photo || 'Sin Foto',
                        campos_personalizados: customFields
                    },
                    measured_at: date
                };

                $.ajax({
                    url: '/api/measurements',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    data: JSON.stringify(data),
                    success: function (response) {
                        if (response.success) {
                            showAlert('Medición guardada correctamente', 'success');
                            $('#measurementModal').modal('hide');
                            // Recargar la página para actualizar los datos
                            location.reload();
                        } else {
                            showAlert(response.message || 'Error al guardar medición', 'danger');
                        }
                    },
                    error: function (xhr) {
                        showAlert('Error al guardar medición: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
                    }
                });
            }

            // Eliminar sensor
            function deleteSensor(sensorId) {
                $.ajax({
                    url: `/api/sensors/${sensorId}`,
                    type: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json'
                    },
                    success: function (response) {
                        if (response.success) {
                            showAlert('Sensor eliminado correctamente', 'success');
                            $('#deleteSensorModal').modal('hide');
                            window.location.href = '{{ route("sensors.index") }}';
                        } else {
                            showAlert(response.message || 'Error al eliminar sensor', 'danger');
                        }
                    },
                    error: function (xhr) {
                        showAlert('Error al eliminar sensor: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
                    }
                });
            }
        });
    </script>
@endpush