@extends('layouts.modern')

@section('title', 'Tomar Medición - ' . ($sensor->name ?? 'Sensor'))

@push('styles')
<style>
    .measurement-form {
        max-width: 800px;
        margin: 0 auto;
    }
    .form-card {
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        border: none;
    }
    .form-card .card-header {
        border-radius: 12px 12px 0 0;
        padding: 1rem 1.5rem;
    }
    .form-control-lg {
        font-size: 1.1rem;
        padding: 0.75rem 1rem;
    }
    .btn-primary-custom {
        background: linear-gradient(135deg, #0d6efd, #0a5fd9);
        border: none;
        padding: 0.75rem 2rem;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
        color: #fff;
    }
    .btn-primary-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(13, 110, 253, 0.35);
        color: #fff;
    }
    .btn-success-custom {
        background: linear-gradient(135deg, #28a745, #20c997);
        border: none;
        padding: 0.75rem 2rem;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
        color: #fff;
    }
    .btn-success-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.35);
        color: #fff;
    }
    .btn-secondary-custom {
        background: #6c757d;
        border: none;
        padding: 0.75rem 2rem;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        height: 100%;
        min-height: 52px;
    }
    .btn-secondary-custom:hover {
        background: #5a6268;
        color: #fff;
    }
    .sensor-info {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
    .sensor-info .label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .sensor-info .value {
        font-weight: 600;
        color: #1a202c;
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
    .camera-preview-container .placeholder {
        color: #adb5bd;
        font-size: 3rem;
    }
    .photo-preview {
        max-height: 150px;
        border-radius: 8px;
        object-fit: cover;
    }
    .quick-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }
    .quick-actions .btn {
        flex: 1;
        min-width: 80px;
    }
    .bulk-progress {
        display: none;
        margin-top: 1rem;
    }
    .bulk-progress .progress {
        height: 8px;
        border-radius: 4px;
    }
    .bulk-progress .progress-bar {
        transition: width 0.3s ease;
    }
    .field-required {
        color: #dc3545;
    }
    .field-unit {
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: normal;
    }
    .previous-measurement-box {
        background: #e7f1ff;
        border-left: 4px solid #0d6efd;
        padding: 0.75rem 1rem;
        border-radius: 6px;
        margin-bottom: 1rem;
    }
    .previous-measurement-box .label {
        font-size: 0.7rem;
        color: #0d6efd;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .previous-measurement-box .value {
        font-weight: 700;
        color: #0d6efd;
        font-size: 1.1rem;
    }
    .previous-measurement-box .date {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .btn-group-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 1.5rem;
    }
    .btn-group-actions .btn {
        flex: 1;
        min-width: 120px;
        height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    @media (max-width: 768px) {
        .btn-group-actions .btn {
            flex: 1 1 100%;
        }
        .quick-actions .btn {
            flex: 1 1 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card form-card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><i class="bi bi-rulers"></i> Tomar Medición</h5>
                            <small>Espacio de: <strong>{{ $ownerName ?? 'Propietario' }}</strong></small>
                        </div>
                        <div>
                            @if($hasMoreSensors ?? false)
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-arrow-right"></i> {{ $currentPosition ?? 1 }} / {{ $totalMarked ?? 0 }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Alertas --}}
                    <div id="alertContainer"></div>

                    {{-- Información del sensor --}}
                    <div class="sensor-info">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="label">Sensor</div>
                                <div class="value">{{ $sensor->name }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="label">Identificador</div>
                                <div class="value"><code>{{ $sensor->identifier }}</code></div>
                            </div>
                            <div class="col-md-3">
                                <div class="label">Grupo</div>
                                <div class="value">{{ $sensor->group->name ?? 'Sin grupo' }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- ⭐ MEDICIÓN ANTERIOR --}}
                    @if(isset($previousMeasurement))
                        <div class="previous-measurement-box">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="label">Última medición</div>
                                    <div class="value">{{ $previousMeasurement->data['consumo_m3'] ?? 'N/A' }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="date">
                                        <i class="bi bi-calendar3"></i> 
                                        {{ $previousMeasurement->measured_at->format('d/m/Y H:i') }}
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i> 
                                        El nuevo valor debe ser <strong>mayor</strong> que el anterior
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

                    {{-- Formulario de medición --}}
                    <form id="measurementForm" class="measurement-form">
                        @csrf
                        <input type="hidden" name="sensor_id" value="{{ $sensor->id }}">
                        <input type="hidden" id="groupName" value="{{ str_replace(' ', '_', $sensor->group->name ?? 'SinGrupo') }}">
                        <input type="hidden" id="sensorId" value="{{ $sensor->id }}">
                        <input type="hidden" id="previousValue" value="{{ $previousMeasurement->data['consumo_m3'] ?? 0 }}">

                        {{-- ✅ CAMPOS DINÁMICOS SEGÚN LA PLANTILLA --}}
                        @php
                            $template = $sensor->group->template ?? null;
                            $fields = [];
                            if ($template && isset($template->schema['campos'])) {
                                $fields = $template->schema['campos'];
                            }
                            
                            // ✅ Separar campos por tipo
                            $mainField = null;
                            $dateField = null;
                            $photoField = null;
                            $extraFields = [];
                            
                            foreach ($fields as $campo) {
                                $nombre = $campo['nombre'] ?? '';
                                $tipo = $campo['tipo'] ?? 'texto';
                                
                                if ($tipo === 'numero' && ($campo['requerido'] ?? false)) {
                                    $mainField = $campo;
                                } elseif ($tipo === 'fecha') {
                                    $dateField = $campo;
                                } elseif (isset($campo['es_foto']) && $campo['es_foto'] === true) {
                                    $photoField = $campo;
                                } else {
                                    $extraFields[] = $campo;
                                }
                            }
                            
                            if (!$mainField) {
                                foreach ($fields as $campo) {
                                    if ($campo['tipo'] === 'numero') {
                                        $mainField = $campo;
                                        break;
                                    }
                                }
                            }
                            
                            if (!$dateField) {
                                foreach ($fields as $campo) {
                                    if ($campo['tipo'] === 'fecha') {
                                        $dateField = $campo;
                                        break;
                                    }
                                }
                            }
                            
                            if (!$photoField) {
                                foreach ($fields as $campo) {
                                    if (isset($campo['es_foto']) && $campo['es_foto'] === true || $campo['nombre'] === 'foto') {
                                        $photoField = $campo;
                                        break;
                                    }
                                }
                            }
                        @endphp

                        {{-- ⭐ CAMPO PRINCIPAL (Valor) --}}
                        @if($mainField)
                            <div class="mb-3">
                                <label for="main_value" class="form-label fw-semibold">
                                    {{ ucfirst($mainField['nombre'] ?? 'Valor') }}
                                    <span class="field-required">*</span>
                                    @if(isset($mainField['unidad']) && $mainField['unidad'])
                                        <span class="field-unit">({{ $mainField['unidad'] }})</span>
                                    @endif
                                </label>
                                <input type="number" step="0.01" class="form-control form-control-lg" 
                                       id="main_value" 
                                       name="data[{{ $mainField['nombre'] ?? 'valor' }}]" 
                                       placeholder="Ingresa el valor medido"
                                       autofocus required>
                                @if(isset($previousMeasurement))
                                    <small class="text-muted">
                                        <i class="bi bi-arrow-up"></i> 
                                        Debe ser mayor que <strong>{{ $previousMeasurement->data['consumo_m3'] ?? 0 }}</strong>
                                    </small>
                                @endif
                            </div>
                        @else
                            <div class="mb-3">
                                <label for="main_value" class="form-label fw-semibold">
                                    Valor <span class="field-required">*</span>
                                </label>
                                <input type="number" step="0.01" class="form-control form-control-lg" 
                                       id="main_value" name="data[valor]" 
                                       placeholder="Ingresa el valor medido"
                                       autofocus required>
                                @if(isset($previousMeasurement))
                                    <small class="text-muted">
                                        <i class="bi bi-arrow-up"></i> 
                                        Debe ser mayor que <strong>{{ $previousMeasurement->data['consumo_m3'] ?? 0 }}</strong>
                                    </small>
                                @endif
                            </div>
                        @endif

                        {{-- ⭐ CAMPO DE FECHA --}}
                        @if($dateField)
                            <div class="mb-3">
                                <label for="measured_at" class="form-label fw-semibold">
                                    {{ ucfirst($dateField['nombre'] ?? 'Fecha de Medición') }}
                                    <span class="field-required">*</span>
                                </label>
                                <input type="datetime-local" class="form-control form-control-lg" 
                                       id="measured_at" name="data[{{ $dateField['nombre'] ?? 'fecha_medicion' }}]" 
                                       required>
                                <small class="text-muted">Selecciona la fecha y hora de la medición</small>
                            </div>
                        @else
                            <div class="mb-3">
                                <label for="measured_at" class="form-label fw-semibold">
                                    Fecha de Medición <span class="field-required">*</span>
                                </label>
                                <input type="datetime-local" class="form-control form-control-lg" 
                                       id="measured_at" name="measured_at" required>
                            </div>
                        @endif

                        {{-- ⭐ CAMPO DE FOTO --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-camera"></i> 
                                {{ $photoField ? ucfirst($photoField['nombre'] ?? 'Foto') : 'Foto' }}
                                @if($photoField && ($photoField['requerido'] ?? false))
                                    <span class="field-required">*</span>
                                @else
                                    <span class="badge bg-warning text-dark">Opcional</span>
                                @endif
                            </label>
                            <div class="row g-2">
                                <div class="col-md-8">
                                    <input type="text" class="form-control" id="photo" name="data[{{ $photoField ? $photoField['nombre'] : 'foto' }}]" 
                                           placeholder="Nombre de la foto (opcional)" value="Sin Foto">
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

                        {{-- ⭐ CAMPOS ADICIONALES --}}
                        @if(count($extraFields) > 0)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-file-earmark-text"></i> Campos adicionales
                                </label>
                                @foreach($extraFields as $campo)
                                    @php
                                        $nombre = $campo['nombre'] ?? '';
                                        $tipo = $campo['tipo'] ?? 'texto';
                                        $requerido = $campo['requerido'] ?? false;
                                        $unidad = $campo['unidad'] ?? '';
                                        $valorDefecto = $campo['valor_por_defecto'] ?? '';
                                    @endphp
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-4">
                                            <label class="form-label small">
                                                {{ ucfirst($nombre) }}
                                                @if($requerido) <span class="field-required">*</span> @endif
                                                @if($unidad) <span class="field-unit">({{ $unidad }})</span> @endif
                                            </label>
                                        </div>
                                        <div class="col-md-8">
                                            @if($tipo === 'numero')
                                                <input type="number" step="0.01" class="form-control form-control-sm" 
                                                       name="data[{{ $nombre }}]" 
                                                       placeholder="{{ $nombre }}"
                                                       value="{{ $valorDefecto }}"
                                                       {{ $requerido ? 'required' : '' }}>
                                            @elseif($tipo === 'texto' || $tipo === 'string')
                                                <input type="text" class="form-control form-control-sm" 
                                                       name="data[{{ $nombre }}]" 
                                                       placeholder="{{ $nombre }}"
                                                       value="{{ $valorDefecto }}"
                                                       {{ $requerido ? 'required' : '' }}>
                                            @elseif($tipo === 'fecha')
                                                <input type="date" class="form-control form-control-sm" 
                                                       name="data[{{ $nombre }}]" 
                                                       value="{{ $valorDefecto }}"
                                                       {{ $requerido ? 'required' : '' }}>
                                            @elseif($tipo === 'booleano')
                                                <select class="form-select form-select-sm" 
                                                        name="data[{{ $nombre }}]"
                                                        {{ $requerido ? 'required' : '' }}>
                                                    <option value="1" {{ $valorDefecto == '1' ? 'selected' : '' }}>Sí</option>
                                                    <option value="0" {{ $valorDefecto == '0' ? 'selected' : '' }}>No</option>
                                                </select>
                                            @else
                                                <input type="text" class="form-control form-control-sm" 
                                                       name="data[{{ $nombre }}]" 
                                                       placeholder="{{ $nombre }}"
                                                       value="{{ $valorDefecto }}"
                                                       {{ $requerido ? 'required' : '' }}>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- ⭐ BOTONES DE ACCIÓN --}}
                        <div class="btn-group-actions">
                            <a href="{{ route('measurements.inspector') }}" class="btn btn-secondary-custom">
                                <i class="bi bi-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success-custom" id="saveMeasurementBtn">
                                <i class="bi bi-check-circle"></i> Guardar Medición
                            </button>
                            @if($hasMoreSensors ?? false)
                                <button type="button" class="btn btn-primary-custom" id="saveAndContinueBtn">
                                    <i class="bi bi-arrow-right"></i> Guardar y Continuar
                                </button>
                            @endif
                        </div>

                        {{-- ⭐ PROGRESO DE MEDICIÓN MASIVA --}}
                        @if($hasMoreSensors ?? false)
                            <div class="bulk-progress" id="bulkProgress">
                                <small class="text-muted">
                                    <i class="bi bi-arrow-repeat"></i> 
                                    Mediciones masivas: <span id="bulkProgressText">{{ $currentPosition ?? 1 }} de {{ $totalMarked ?? 0 }}</span>
                                </small>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         id="bulkProgressBar" 
                                         style="width: {{ (($currentPosition ?? 1) / ($totalMarked ?? 1)) * 100 }}%">
                                    </div>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de cámara --}}
<div class="modal fade" id="cameraModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-camera"></i> Tomar Foto</h5>
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
    const hasMoreSensors = {{ isset($hasMoreSensors) && $hasMoreSensors ? 'true' : 'false' }};
    const previousValue = parseFloat($('#previousValue').val()) || 0;
    let stream = null;
    let photoBlob = null;
    let photoUploaded = false;

    // ✅ Fecha actual en el campo de fecha
    const now = new Date();
    const localDateTime = now.toISOString().slice(0, 16);
    $('#measured_at').val(localDateTime);

    // =============================================
    // ✅ VALIDACIÓN DEL VALOR (debe ser mayor que el anterior)
    // =============================================
    $('#main_value').on('change input', function() {
        const currentValue = parseFloat($(this).val());
        if (previousValue > 0 && currentValue <= previousValue) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
            $(this).after(`<div class="invalid-feedback">
                El valor debe ser mayor que ${previousValue}
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
            // ✅ Mostrar nombre de la foto en la interfaz
            showAlert(`📸 Foto capturada: ${photoName}`, 'success');
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
        const sensorIdentifier = '{{ $sensor->identifier }}'; // ✅ Usar identificador del sensor
        return `${sensorIdentifier}_${date}_${time}.png`;
    }


    // Activar cámara
    $('#btnActivarCamara').click(function() {
        $('#cameraModal').modal('show');
        startCamera(document.getElementById('modalWebcam'));
    });

    // Tomar foto desde modal
    $('#takeModalPhoto').click(function() {
        takePhoto(
            document.getElementById('modalWebcam'),
            document.getElementById('modalCanvas')
        );
    });

    // Quitar foto
    $('#btnRemovePhoto').click(function() {
        $('#photo').val('Sin Foto');
        $('#photoPreview').addClass('d-none');
        $('#photoPreviewImg').attr('src', '');
        photoBlob = null;
        photoUploaded = false;
    });

    // =============================================
    // ENVÍO DEL FORMULARIO
    // =============================================
    function submitMeasurement(continueToNext = false) {
        // ✅ Validar que el campo principal tenga valor
        const mainValue = $('#main_value').val();
        if (!mainValue) {
            showAlert('El campo valor es obligatorio', 'danger');
            return;
        }

        // ✅ Validar que el valor sea mayor que el anterior
        const currentValue = parseFloat(mainValue);
        if (previousValue > 0 && currentValue <= previousValue) {
            showAlert(`El valor debe ser mayor que ${previousValue}`, 'danger');
            return;
        }

        // ✅ Construir datos del formulario
        const formData = {
            sensor_id: sensorId,
            data: {},
            measured_at: $('#measured_at').val()
        };

        // ✅ Obtener todos los campos del formulario (EXCLUYENDO el campo foto)
        $('#measurementForm').find('input, select, textarea').each(function() {
            const name = $(this).attr('name');
            const value = $(this).val();
            
            if (!name || !name.startsWith('data[')) return;
            
            // ✅ Omitir el campo de foto (se maneja aparte)
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

        // ✅ Agregar foto si existe
        if (photoBlob) {
            // ✅ Si hay foto, usamos el nombre generado
            formData.data.foto = $('#photo').val();
        } else {
            formData.data.foto = 'Sin Foto';
        }

        if (!formData.data.campos_personalizados) {
            formData.data.campos_personalizados = {};
        }

        // ✅ Si hay foto, subirla primero
        if (photoBlob && !photoUploaded) {
            // ✅ Subir foto y luego guardar medición
            uploadPhoto(formData.data.foto).then(() => {
                saveMeasurementData(formData, continueToNext);
            }).catch(function(err) {
                showAlert('Error al subir la foto: ' + err.message, 'danger');
                $('#saveMeasurementBtn, #saveAndContinueBtn').prop('disabled', false);
                $('#saveMeasurementBtn').html('<i class="bi bi-check-circle"></i> Guardar Medición');
                $('#saveAndContinueBtn').html('<i class="bi bi-arrow-right"></i> Guardar y Continuar');
            });
        } else {
            // ✅ Sin foto, guardar directamente
            saveMeasurementData(formData, continueToNext);
        }
    }

    // =============================================
    // SUBIR FOTO
    // =============================================
    function uploadPhoto(photoName) {
        return new Promise((resolve, reject) => {
            if (!photoBlob) {
                resolve();
                return;
            }

            // ✅ Verificar que sensorId esté definido
            if (!sensorId) {
                reject(new Error('ID de sensor no definido'));
                return;
            }

            const formData = new FormData();
            formData.append('foto', photoBlob, photoName);
            formData.append('sensor_id', sensorId);

            console.log('📤 Subiendo foto:', {
                sensor_id: sensorId,
                photo_name: photoName,
                photo_size: photoBlob.size,
                photo_type: photoBlob.type
            });

            $.ajax({
                url: '/api/measurements/upload-photo',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    // ✅ NO poner Content-Type: multipart/form-data
                    // ✅ NO poner Accept: application/json (puede causar problemas)
                },
                data: formData,
                processData: false,
                contentType: false, // ✅ Importante: dejar que el navegador lo establezca
                success: function(response) {
                    console.log('✅ Foto subida:', response);
                    if (response.success) {
                        $('#photo').val(response.path);
                        photoUploaded = true;
                        resolve();
                    } else {
                        reject(new Error(response.message || 'Error al subir foto'));
                    }
                },
                error: function(xhr) {
                    console.error('❌ Error al subir foto:', xhr);
                    console.error('❌ Respuesta:', xhr.responseText);
                    const msg = xhr.responseJSON?.message || xhr.statusText || 'Error al subir foto';
                    reject(new Error(msg));
                }
            });
        });
    }

    // =============================================
    // GUARDAR MEDICIÓN
    // =============================================
    function saveMeasurementData(formData, continueToNext) {
        // ✅ Actualizar el campo foto con la ruta subida
        if (photoUploaded) {
            formData.data.foto = $('#photo').val();
        }

        $.ajax({
            url: '/api/measurements',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(formData),
            beforeSend: function() {
                $('#saveMeasurementBtn, #saveAndContinueBtn').prop('disabled', true);
                if (continueToNext) {
                    $('#saveAndContinueBtn').html(`
                        <span class="spinner-border spinner-border-sm" role="status"></span> Guardando...
                    `);
                } else {
                    $('#saveMeasurementBtn').html(`
                        <span class="spinner-border spinner-border-sm" role="status"></span> Guardando...
                    `);
                }
            },
            success: function(response) {
                if (response.success) {
                    if (continueToNext && hasMoreSensors) {
                        $.ajax({
                            url: '/api/bulk/measurements/next-sensor',
                            type: 'GET',
                            headers: {
                                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                                'Accept': 'application/json'
                            },
                            data: { current_sensor_id: sensorId },
                            success: function(nextResponse) {
                                if (nextResponse.success && nextResponse.data) {
                                    window.location.href = nextResponse.data.url;
                                } else {
                                    showAlert('Todas las mediciones completadas', 'success');
                                    setTimeout(() => {
                                        window.location.href = '{{ route("measurements.inspector") }}';
                                    }, 1500);
                                }
                            },
                            error: function() {
                                showAlert('Error al obtener siguiente sensor', 'danger');
                                window.location.href = '{{ route("measurements.inspector") }}';
                            }
                        });
                    } else {
                        showAlert('Medición guardada correctamente', 'success');
                        setTimeout(() => {
                            window.location.href = '{{ route("measurements.inspector") }}';
                        }, 1000);
                    }
                } else {
                    showAlert(response.message || 'Error al guardar la medición', 'danger');
                    $('#saveMeasurementBtn, #saveAndContinueBtn').prop('disabled', false);
                    $('#saveMeasurementBtn').html('<i class="bi bi-check-circle"></i> Guardar Medición');
                    $('#saveAndContinueBtn').html('<i class="bi bi-arrow-right"></i> Guardar y Continuar');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Error al guardar la medición';
                showAlert(msg, 'danger');
                $('#saveMeasurementBtn, #saveAndContinueBtn').prop('disabled', false);
                $('#saveMeasurementBtn').html('<i class="bi bi-check-circle"></i> Guardar Medición');
                $('#saveAndContinueBtn').html('<i class="bi bi-arrow-right"></i> Guardar y Continuar');
            }
        });
    }

    // =============================================
    // EVENTOS
    // =============================================
    $('#measurementForm').submit(function(e) {
        e.preventDefault();
        submitMeasurement(false);
    });

    $('#saveAndContinueBtn').click(function() {
        submitMeasurement(true);
    });

    // Enter para guardar
    $('#main_value, #measured_at').keypress(function(e) {
        if (e.which === 13) {
            e.preventDefault();
            submitMeasurement(hasMoreSensors);
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