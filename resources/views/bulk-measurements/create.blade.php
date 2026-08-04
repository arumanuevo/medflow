@extends('layouts.modern')

@section('title', 'Medición - ' . ($sensor->name ?? 'Sensor'))

@push('styles')
<style>
    .measurement-container {
        max-width: 900px;
        margin: 0 auto;
    }
    .card-progress {
        border-left: 4px solid #0d6efd;
        background: linear-gradient(90deg, #e7f1ff 0%, #f8f9fa 100%);
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }
    .progress-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }
    .progress-text {
        font-weight: 600;
        color: #1a202c;
    }
    .progress-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0d6efd;
    }
    .progress-bar-custom {
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }
    .progress-bar-custom .fill {
        height: 100%;
        background: linear-gradient(90deg, #0d6efd, #0a5fd9);
        border-radius: 4px;
        transition: width 0.5s ease;
    }
    .sensor-header {
        background: linear-gradient(135deg, #0d6efd, #0a5fd9);
        color: white;
        border-radius: 12px 12px 0 0;
        padding: 1.25rem 1.5rem;
    }
    .sensor-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .sensor-info-item {
        background: #f8f9fa;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    .sensor-info-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
        margin-bottom: 0.25rem;
    }
    .sensor-info-value {
        font-weight: 600;
        color: #1a202c;
    }
    .previous-measurement-box {
        background: #e7f1ff;
        border-left: 4px solid #0d6efd;
        padding: 1rem;
        border-radius: 6px;
        margin-bottom: 1.5rem;
    }
    .previous-measurement-box .label {
        font-size: 0.75rem;
        color: #0d6efd;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
        margin-bottom: 0.5rem;
    }
    .previous-measurement-box .value {
        font-weight: 700;
        color: #0d6efd;
        font-size: 1.25rem;
    }
    .previous-measurement-box .date {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
    .form-section {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        margin-bottom: 1.5rem;
    }
    .form-section h6 {
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #e9ecef;
    }
    .btn-action {
        padding: 0.75rem 2rem;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        min-height: 48px;
    }
    .btn-save {
        background: linear-gradient(135deg, #28a745, #20c997);
        border: none;
        color: white;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.35);
        color: white;
    }
    .btn-next {
        background: linear-gradient(135deg, #0d6efd, #0a5fd9);
        border: none;
        color: white;
    }
    .btn-next:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(13, 110, 253, 0.35);
        color: white;
    }
    .btn-previous {
        background: #6c757d;
        border: none;
        color: white;
    }
    .btn-previous:hover {
        background: #5a6268;
        color: white;
    }
    .btn-cancel {
        background: #dc3545;
        border: none;
        color: white;
    }
    .btn-cancel:hover {
        background: #c82333;
        color: white;
    }
    .action-buttons {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 1.5rem;
    }
    .action-buttons .btn {
        flex: 1;
        min-width: 150px;
    }
    @media (max-width: 768px) {
        .action-buttons .btn {
            flex: 1 1 100%;
        }
    }
    .camera-preview-container {
        position: relative;
        background: #f8f9fa;
        border-radius: 8px;
        overflow: hidden;
        min-height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .camera-preview-container video {
        width: 100%;
        max-height: 300px;
        object-fit: cover;
    }
    .photo-preview {
        max-height: 150px;
        border-radius: 8px;
        object-fit: cover;
        margin-top: 0.5rem;
    }
    .field-required {
        color: #dc3545;
    }
    .field-unit {
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: normal;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="measurement-container">
        <!-- Progreso -->
        <div class="card-progress">
            <div class="progress-info">
                <span class="progress-text">
                    <i class="bi bi-list-check me-2"></i>
                    Sensor {{ $currentPosition }} de {{ $totalSensors }}
                </span>
                <span class="progress-value">
                    {{ round(($currentPosition / $totalSensors) * 100) }}%
                </span>
            </div>
            <div class="progress-bar-custom">
                <div class="fill" style="width: {{ ($currentPosition / $totalSensors) * 100 }}%;"></div>
            </div>
        </div>

        <!-- Tarjeta principal -->
        <div class="card shadow-sm">
            <!-- Header -->
            <div class="sensor-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">
                            <i class="bi bi-rulers me-2"></i>
                            Tomar Medición
                        </h4>
                        <small class="opacity-75">
                            Espacio de: <strong>{{ $ownerName }}</strong>
                        </small>
                    </div>
                    <div>
                        @if($hasNext)
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-arrow-right"></i> Siguiente disponible
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Alertas -->
                <div id="alertContainer"></div>

                <!-- Información del sensor -->
                <div class="sensor-info-grid">
                    <div class="sensor-info-item">
                        <span class="sensor-info-label">Sensor</span>
                        <span class="sensor-info-value">{{ $sensor->name }}</span>
                    </div>
                    <div class="sensor-info-item">
                        <span class="sensor-info-label">Identificador</span>
                        <span class="sensor-info-value"><code>{{ $sensor->identifier }}</code></span>
                    </div>
                    <div class="sensor-info-item">
                        <span class="sensor-info-label">Grupo</span>
                        <span class="sensor-info-value">{{ $sensor->group->name ?? 'Sin grupo' }}</span>
                    </div>
                    <div class="sensor-info-item">
                        <span class="sensor-info-label">Período</span>
                        <span class="sensor-info-value">{{ $periodoMedicion }} días</span>
                    </div>
                </div>

                <!-- Medición anterior -->
                @if($previousMeasurement)
                    <div class="previous-measurement-box">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="label">Última medición</span>
                                <span class="value">{{ is_numeric($lastValue) ? number_format($lastValue, 2) : ($lastValue ?? 'N/A') }}</span>
                            </div>
                            <div class="text-end">
                                <span class="date">
                                    <i class="bi bi-calendar3"></i> 
                                    {{ $previousMeasurement->measured_at->format('d/m/Y H:i') }}
                                </span>
                                <small class="text-muted d-block mt-1">
                                    <i class="bi bi-info-circle"></i> 
                                    El nuevo valor debe ser <strong>mayor</strong>
                                </small>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        No hay mediciones anteriores para este sensor. Puedes ingresar cualquier valor.
                    </div>
                @endif

                <!-- Formulario -->
                <form id="measurementForm">
                    @csrf
                    <input type="hidden" name="sensor_id" value="{{ $sensor->id }}">
                    <input type="hidden" id="groupName" value="{{ str_replace(' ', '_', $sensor->group->name ?? 'SinGrupo') }}">

                    <!-- Campo principal (Valor) -->
                    <div class="form-section">
                        <h6>
                            <i class="bi bi-hash me-2 text-primary"></i>
                            Valor de Medición
                        </h6>
                        <div class="mb-3">
                            <label for="main_value" class="form-label fw-semibold">
                                {{ ucfirst($mainField ?? 'Valor') }}
                                <span class="field-required">*</span>
                                @if($sensor->group && $sensor->group->template)
                                    @php
                                        $unit = null;
                                        if (isset($sensor->group->template->schema['campos'])) {
                                            foreach ($sensor->group->template->schema['campos'] as $campo) {
                                                if ($campo['nombre'] === $mainField && isset($campo['unidad'])) {
                                                    $unit = $campo['unidad'];
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp
                                    @if($unit)
                                        <span class="field-unit">({{ $unit }})</span>
                                    @endif
                                @endif
                            </label>
                            <input type="number" step="0.01" class="form-control form-control-lg" 
                                   id="main_value" 
                                   name="data[{{ $mainField ?? 'valor' }}]" 
                                   placeholder="Ingresa el valor medido"
                                   autofocus required>
                            @if($lastValue !== null)
                                <small class="text-muted">
                                    <i class="bi bi-arrow-up"></i> 
                                    Debe ser mayor que <strong>{{ is_numeric($lastValue) ? number_format($lastValue, 2) : $lastValue }}</strong>
                                </small>
                            @endif
                        </div>
                    </div>

                    <!-- Fecha -->
                    <div class="form-section">
                        <h6>
                            <i class="bi bi-calendar me-2 text-primary"></i>
                            Fecha de Medición
                        </h6>
                        <div class="mb-3">
                            <label for="measured_at" class="form-label fw-semibold">
                                Fecha y Hora <span class="field-required">*</span>
                            </label>
                            <input type="datetime-local" class="form-control form-control-lg" 
                                   id="measured_at" name="measured_at" required>
                        </div>
                    </div>

                    <!-- Foto -->
                    <div class="form-section">
                        <h6>
                            <i class="bi bi-camera me-2 text-primary"></i>
                            Foto (Opcional)
                        </h6>
                        <div class="mb-3">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-8">
                                    <input type="text" class="form-control" id="photo" name="data[foto]" 
                                           placeholder="Nombre de la foto" value="Sin Foto">
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-outline-primary w-100" id="btnActivarCamara">
                                        <i class="bi bi-camera"></i> Tomar Foto
                                    </button>
                                </div>
                            </div>
                            <div id="photoPreview" class="mt-2 d-none">
                                <img id="photoPreviewImg" class="photo-preview" alt="Foto de la medición">
                                <button type="button" class="btn btn-sm btn-danger mt-1" id="btnRemovePhoto">
                                    <i class="bi bi-x"></i> Quitar foto
                                </button>
                            </div>
                            <div class="camera-preview-container d-none" id="cameraPreview">
                                <video id="webcam" autoplay playsinline></video>
                                <button type="button" class="btn btn-sm btn-success position-absolute bottom-0 start-50 translate-middle-x mb-2" id="btnTakePhoto">
                                    <i class="bi bi-camera"></i> Capturar
                                </button>
                                <canvas id="canvas" class="d-none"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Campos personalizados -->
                    @php
                        $customFields = [];
                        if (isset($sensor->group) && isset($sensor->group->template) && isset($sensor->group->template->schema['campos'])) {
                            $customFields = array_filter($sensor->group->template->schema['campos'], function($campo) use ($mainField) {
                                return $campo['nombre'] !== ($mainField ?? 'valor') && 
                                       $campo['nombre'] !== 'foto' && 
                                       $campo['nombre'] !== 'fecha_medicion';
                            });
                        }
                    @endphp

                    @if(count($customFields) > 0)
                        <div class="form-section">
                            <h6>
                                <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                                Campos Adicionales
                            </h6>
                            <div class="row g-3">
                                @foreach($customFields as $campo)
                                    <div class="col-md-6">
                                        <label for="field_{{ $campo['nombre'] }}" class="form-label">
                                            {{ ucfirst(str_replace('_', ' ', $campo['nombre'])) }}
                                            @if($campo['requerido'] ?? false) <span class="field-required">*</span> @endif
                                            @if(isset($campo['unidad']) && $campo['unidad'])
                                                <span class="field-unit">({{ $campo['unidad'] }})</span>
                                            @endif
                                        </label>
                                        @if($campo['tipo'] === 'numero')
                                            <input type="number" step="0.01" class="form-control"
                                                   id="field_{{ $campo['nombre'] }}" 
                                                   name="data[campos_personalizados][{{ $campo['nombre'] }}]"
                                                   placeholder="Ingresa {{ $campo['nombre'] }}"
                                                   {{ ($campo['requerido'] ?? false) ? 'required' : '' }}>
                                        @elseif($campo['tipo'] === 'texto' || $campo['tipo'] === 'string')
                                            <input type="text" class="form-control"
                                                   id="field_{{ $campo['nombre'] }}" 
                                                   name="data[campos_personalizados][{{ $campo['nombre'] }}]"
                                                   placeholder="Ingresa {{ $campo['nombre'] }}"
                                                   {{ ($campo['requerido'] ?? false) ? 'required' : '' }}>
                                        @elseif($campo['tipo'] === 'fecha')
                                            <input type="date" class="form-control"
                                                   id="field_{{ $campo['nombre'] }}" 
                                                   name="data[campos_personalizados][{{ $campo['nombre'] }}]"
                                                   {{ ($campo['requerido'] ?? false) ? 'required' : '' }}>
                                        @elseif($campo['tipo'] === 'booleano')
                                            <select class="form-select" id="field_{{ $campo['nombre'] }}"
                                                    name="data[campos_personalizados][{{ $campo['nombre'] }}]"
                                                    {{ ($campo['requerido'] ?? false) ? 'required' : '' }}>
                                                <option value="1">Sí</option>
                                                <option value="0">No</option>
                                            </select>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Botones de acción -->
                    <div class="action-buttons">
                        <a href="{{ route('bulk-measurements.select') }}" 
                           class="btn btn-cancel btn-action" 
                           onclick="return confirm('¿Estás seguro de que deseas cancelar el flujo de medición masiva?');">
                            <i class="bi bi-x-lg"></i> Cancelar
                        </a>
                        
                        @if($hasPrevious)
                            <a href="{{ route('bulk-measurements.previous', $sensor->id) }}" 
                               class="btn btn-previous btn-action">
                                <i class="bi bi-arrow-left"></i> Anterior
                            </a>
                        @endif
                        
                        <button type="submit" class="btn btn-save btn-action">
                            <i class="bi bi-check-circle"></i> Guardar Medición
                        </button>
                        
                        @if($hasNext)
                            <button type="button" class="btn btn-next btn-action" id="saveAndContinueBtn">
                                <i class="bi bi-arrow-right"></i> Guardar y Continuar
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de cámara -->
<div class="modal fade" id="cameraModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-camera me-2"></i> Tomar Foto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="camera-preview-container">
                    <video id="modalWebcam" autoplay playsinline></video>
                    <canvas id="modalCanvas" class="d-none"></canvas>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" id="takeModalPhoto">
                    <i class="bi bi-camera"></i> Capturar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // =============================================
    // CONFIGURACIÓN INICIAL
    // =============================================
    const sensorId = {{ $sensor->id }};
    const groupName = $('#groupName').val();
    const hasNext = {{ $hasNext ? 'true' : 'false' }};
    const hasPrevious = {{ $hasPrevious ? 'true' : 'false' }};
    const lastValue = parseFloat('{{ is_numeric($lastValue) ? $lastValue : 0 }}') || 0;
    const token = localStorage.getItem('token');
    
    let stream = null;
    let photoBlob = null;
    let photoUploaded = false;

    // Inicializar fecha actual
    const now = new Date();
    const localDateTime = now.toISOString().slice(0, 16);
    $('#measured_at').val(localDateTime);

    // =============================================
    // VALIDACIÓN DEL VALOR
    // =============================================
    $('#main_value').on('change input', function() {
        const currentValue = parseFloat($(this).val());
        if (lastValue > 0 && currentValue <= lastValue) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
            $(this).after(`<div class="invalid-feedback">
                El valor debe ser mayor que ${lastValue}
            </div>`);
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });

    // =============================================
    // CÁMARA
    // =============================================
    async function startCamera(videoElement) {
        try {
            const constraints = {
                video: { facingMode: { ideal: "environment" }, width: { ideal: 640 }, height: { ideal: 480 } },
                audio: false
            };
            const mediaStream = await navigator.mediaDevices.getUserMedia(constraints);
            stream = mediaStream;
            videoElement.srcObject = mediaStream;
            videoElement.play();
            return true;
        } catch (err) {
            showAlert('No se pudo acceder a la cámara: ' + err.message, 'danger');
            return false;
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    }

    function takePhoto(videoElement, canvasElement) {
        if (!stream) {
            showAlert('La cámara no está activa', 'danger');
            return;
        }
        canvasElement.width = videoElement.videoWidth;
        canvasElement.height = videoElement.videoHeight;
        const ctx = canvasElement.getContext('2d');
        ctx.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);
        
        const photoName = generatePhotoName();
        $('#photo').val(photoName);
        
        canvasElement.toBlob(function(blob) {
            photoBlob = blob;
        }, 'image/png');
        
        const imageData = canvasElement.toDataURL('image/png');
        $('#photoPreviewImg').attr('src', imageData);
        $('#photoPreview').removeClass('d-none');
        $('#cameraPreview').addClass('d-none');
        stopCamera();
        $('#cameraModal').modal('hide');
    }

    function generatePhotoName() {
        const measuredAt = $('#measured_at').val() || new Date().toISOString();
        const dateObj = new Date(measuredAt);
        const date = dateObj.toISOString().slice(0, 10).replace(/-/g, '');
        const time = dateObj.toTimeString().slice(0, 5).replace(/:/g, '');
        return `${groupName}_${sensorId}_${date}_${time}.png`;
    }

    // Eventos de cámara
    $('#btnActivarCamara').click(function() {
        $('#cameraModal').modal('show');
        startCamera(document.getElementById('modalWebcam'));
    });

    $('#takeModalPhoto').click(function() {
        takePhoto(
            document.getElementById('modalWebcam'),
            document.getElementById('modalCanvas')
        );
    });

    $('#cameraModal').on('hidden.bs.modal', stopCamera);

    $('#btnRemovePhoto').click(function() {
        $('#photo').val('Sin Foto');
        $('#photoPreview').addClass('d-none');
        $('#photoPreviewImg').attr('src', '');
        photoBlob = null;
        photoUploaded = false;
    });

    // =============================================
    // SUBIR FOTO
    // =============================================
    function uploadPhoto() {
        return new Promise((resolve, reject) => {
            if (!photoBlob) {
                resolve();
                return;
            }

            const formData = new FormData();
            formData.append('foto', photoBlob, $('#photo').val());
            formData.append('sensor_id', sensorId);

            $.ajax({
                url: '/api/measurements/upload-photo',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#photo').val(response.path);
                        photoUploaded = true;
                        resolve();
                    } else {
                        reject(new Error(response.message || 'Error al subir foto'));
                    }
                },
                error: function(xhr) {
                    reject(new Error(xhr.responseJSON?.message || xhr.statusText));
                }
            });
        });
    }

    // =============================================
    // ENVÍO DEL FORMULARIO
    // =============================================
    function submitMeasurement(continueToNext = false) {
        // Validar que el campo principal tenga valor
        const mainValue = $('#main_value').val();
        if (!mainValue) {
            showAlert('El campo valor es obligatorio', 'danger');
            return;
        }

        // Validar que el valor sea mayor que el anterior
        const currentValue = parseFloat(mainValue);
        if (lastValue > 0 && currentValue <= lastValue) {
            showAlert(`El valor debe ser mayor que ${lastValue}`, 'danger');
            return;
        }

        // Construir datos del formulario
        const formData = {
            sensor_id: sensorId,
            data: {},
            measured_at: $('#measured_at').val()
        };

        // Obtener todos los campos del formulario
        $('#measurementForm').find('input, select, textarea').each(function() {
            const name = $(this).attr('name');
            const value = $(this).val();
            
            if (!name || !name.startsWith('data[')) return;
            
            // Omitir el campo de foto (se maneja aparte)
            if (name.includes('foto')) return;
            
            const fieldName = name.match(/data\[(.*?)\]/)[1];
            
            if (fieldName.includes('campos_personalizados')) {
                const customName = fieldName.match(/campos_personalizados\[(.*?)\]/)[1];
                if (value) {
                    if (!formData.data.campos_personalizados) {
                        formData.data.campos_personalizados = {};
                    }
                    formData.data.campos_personalizados[customName] = value;
                }
            } else {
                if (value || value === 0 || value === '0') {
                    formData.data[fieldName] = value;
                }
            }
        });

        // Agregar foto
        if (photoBlob) {
            formData.data.foto = $('#photo').val();
        } else {
            formData.data.foto = 'Sin Foto';
        }

        if (!formData.data.campos_personalizados) {
            formData.data.campos_personalizados = {};
        }

        // Si hay foto, subirla primero
        if (photoBlob && !photoUploaded) {
            uploadPhoto().then(() => {
                saveMeasurementData(formData, continueToNext);
            }).catch(function(err) {
                showAlert('Error al subir la foto: ' + err.message, 'danger');
                $('.btn-action').prop('disabled', false);
            });
        } else {
            saveMeasurementData(formData, continueToNext);
        }
    }

    function saveMeasurementData(formData, continueToNext) {
        // Actualizar el campo foto con la ruta subida
        if (photoUploaded) {
            formData.data.foto = $('#photo').val();
        }

        const $btn = $('.btn-action');
        $btn.prop('disabled', true);

        $.ajax({
            url: '{{ route("bulk-measurements.store") }}',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: JSON.stringify(formData),
            beforeSend: function() {
                if (continueToNext) {
                    $('#saveAndContinueBtn').html(`
                        <span class="spinner-border spinner-border-sm" role="status"></span> Guardando...
                    `);
                } else {
                    $('.btn-save').html(`
                        <span class="spinner-border spinner-border-sm" role="status"></span> Guardando...
                    `);
                }
            },
            success: function(response) {
                if (response.success) {
                    if (continueToNext && hasNext) {
                        // Redirigir al siguiente sensor
                        window.location.href = '{{ route("bulk-measurements.create", ":id") }}'.replace(':id', response.data.next_sensor_url?.split('/').pop() || '{{ $nextSensorId }}');
                    } else {
                        showAlert('Medición guardada correctamente', 'success');
                        setTimeout(() => {
                            window.location.href = '{{ route("bulk-measurements.select") }}';
                        }, 1500);
                    }
                } else {
                    showAlert(response.message || 'Error al guardar la medición', 'danger');
                    $btn.prop('disabled', false);
                    $('.btn-save').html('<i class="bi bi-check-circle"></i> Guardar Medición');
                    $('#saveAndContinueBtn').html('<i class="bi bi-arrow-right"></i> Guardar y Continuar');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Error al guardar la medición';
                showAlert(msg, 'danger');
                $btn.prop('disabled', false);
                $('.btn-save').html('<i class="bi bi-check-circle"></i> Guardar Medición');
                $('#saveAndContinueBtn').html('<i class="bi bi-arrow-right"></i> Guardar y Continuar');
            }
        });
    }

    // Eventos del formulario
    $('#measurementForm').submit(function(e) {
        e.preventDefault();
        submitMeasurement(false);
    });

    $('#saveAndContinueBtn').click(function() {
        submitMeasurement(true);
    });

    // Tecla Enter para guardar
    $('#main_value, #measured_at').keypress(function(e) {
        if (e.which === 13) {
            e.preventDefault();
            submitMeasurement(hasNext);
        }
    });

    // =============================================
    // ALERTAS
    // =============================================
    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#alertContainer').append(alertHtml);
        
        setTimeout(() => {
            $('#alertContainer .alert').first().fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
    }
});
</script>
@endpush
