@extends('layouts.modern')

@section('title', 'Editar Medición - ' . ($measurement->sensor->name ?? 'Sensor'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/measurements-create-styles.css') }}">
<style>
    /* Estilos adicionales para edición */
    .edit-measurement-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 1rem;
    }
    .edit-measurement-container .card {
        border-radius: 12px;
        overflow: hidden;
    }
    .edit-measurement-container .card-header {
        padding: 0.75rem 1.25rem;
    }
    .edit-measurement-container .card-body {
        padding: 1.25rem;
    }
    .edit-measurement-container .form-label {
        font-weight: 500;
        font-size: 0.85rem;
        margin-bottom: 0.25rem;
    }
    .edit-measurement-container .form-control,
    .edit-measurement-container .form-select {
        font-size: 0.9rem;
        padding: 0.4rem 0.75rem;
        border-radius: 8px;
    }
    .edit-measurement-container .form-control:focus,
    .edit-measurement-container .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }
    .edit-measurement-container .input-group-text {
        font-size: 0.85rem;
        padding: 0.4rem 0.75rem;
    }
    .edit-measurement-container .btn {
        font-size: 0.85rem;
        padding: 0.4rem 1rem;
        border-radius: 8px;
    }
    .edit-measurement-container .btn-sm {
        padding: 0.25rem 0.6rem;
        font-size: 0.75rem;
    }

    /* Info de medición anterior (para contexto) */
    .previous-measurement-box {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        border-left: 4px solid #0d6efd;
        margin-bottom: 1rem;
    }
    .previous-measurement-box .label {
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #6c757d;
        letter-spacing: 0.5px;
    }
    .previous-measurement-box .value {
        font-size: 1.1rem;
        font-weight: 600;
        color: #212529;
    }
    .previous-measurement-box .value .unit {
        font-weight: 400;
        font-size: 0.85rem;
        color: #6c757d;
    }

    /* Foto preview */
    .photo-preview-box {
        min-height: 120px;
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        overflow: hidden;
        position: relative;
    }
    .photo-preview-box.has-photo {
        border-color: #28a745;
        background: #f0fff4;
    }
    .photo-preview-box img {
        max-height: 150px;
        width: 100%;
        object-fit: contain;
    }
    .photo-preview-box .placeholder {
        color: #6c757d;
        font-size: 0.85rem;
        text-align: center;
        padding: 1rem;
    }
    .photo-preview-box .placeholder i {
        font-size: 2rem;
        display: block;
        margin-bottom: 0.5rem;
    }
    .photo-preview-box .btn-remove-photo {
        position: absolute;
        top: 5px;
        right: 5px;
        padding: 0.1rem 0.4rem;
        font-size: 0.7rem;
        border-radius: 50%;
        background: rgba(220, 53, 69, 0.9);
        color: #fff;
        border: none;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .photo-preview-box:hover .btn-remove-photo {
        opacity: 1;
    }
    .photo-preview-box .btn-remove-photo:hover {
        background: #dc3545;
    }

    .photo-name-display {
        background: #f8f9fa;
        padding: 0.3rem 0.6rem;
        border-radius: 4px;
        font-size: 0.7rem;
        font-family: monospace;
        word-break: break-all;
        border: 1px solid #e9ecef;
    }

    /* Toggle de foto */
    .photo-toggle {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
    }
    .photo-toggle .form-check-input {
        width: 40px;
        height: 22px;
        cursor: pointer;
    }
    .photo-toggle .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .sensor-info-compact {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem 1rem;
        font-size: 0.85rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e9ecef;
        margin-bottom: 1rem;
    }
    .sensor-info-compact .info-item {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        background: #f8f9fa;
        padding: 0.2rem 0.6rem;
        border-radius: 4px;
    }
    .sensor-info-compact .info-item i {
        color: #6c757d;
    }
    .sensor-info-compact .info-item strong {
        color: #212529;
    }

    /* Modal de cámara */
    .camera-modal .modal-body {
        padding: 0;
        background: #1a1a2e;
        position: relative;
    }
    .camera-modal .modal-body video {
        width: 100%;
        max-height: 70vh;
        object-fit: cover;
        background: #000;
    }
    .camera-modal .modal-body .camera-controls {
        position: absolute;
        bottom: 20px;
        left: 0;
        right: 0;
        display: flex;
        justify-content: center;
        gap: 1rem;
        padding: 1rem;
        background: linear-gradient(transparent, rgba(0,0,0,0.7));
    }
    .camera-modal .modal-body .camera-controls .btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .camera-modal .modal-body .camera-controls .btn-take-photo {
        background: #fff;
        color: #000;
        border: 4px solid #dc3545;
        width: 72px;
        height: 72px;
    }
    .camera-modal .modal-body .camera-controls .btn-take-photo:hover {
        background: #f0f0f0;
        transform: scale(1.05);
    }
    .camera-modal .modal-body .camera-error {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #fff;
        background: rgba(220, 53, 69, 0.9);
        padding: 1rem 2rem;
        border-radius: 8px;
        text-align: center;
    }
    .camera-modal .modal-body .camera-error i {
        font-size: 2rem;
        display: block;
        margin-bottom: 0.5rem;
    }

    .consumption-info {
        background: #e8f4fd;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
        border-left: 4px solid #0d6efd;
        margin-top: 0.5rem;
    }

    @media (max-width: 768px) {
        .edit-measurement-container {
            padding: 0.5rem;
        }
        .edit-measurement-container .card-body {
            padding: 0.75rem;
        }
        .sensor-info-compact .info-item {
            font-size: 0.75rem;
            padding: 0.15rem 0.5rem;
        }
        .photo-preview-box {
            min-height: 100px;
        }
        .photo-preview-box img {
            max-height: 120px;
        }
        .previous-measurement-box .value {
            font-size: 0.95rem;
        }
    }
</style>
@endpush

@section('content')
<div class="edit-measurement-container">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-pencil-square me-2"></i>
                Editar Medición
            </h5>
            <a href="{{ route('measurements.index') }}" class="btn btn-light btn-sm">
                <i class="bi bi-x-lg"></i> Cancelar
            </a>
        </div>
        <div class="card-body">
            <!-- ============================================
            ALERTAS
            ============================================ -->
            <div id="alertContainer"></div>

            <!-- ============================================
            INFO COMPACTA DEL SENSOR
            ============================================ -->
            <div class="sensor-info-compact">
                <span class="info-item">
                    <i class="bi bi-tag"></i>
                    <strong>{{ $measurement->sensor->name }}</strong>
                </span>
                <span class="info-item">
                    <i class="bi bi-hash"></i>
                    {{ $measurement->sensor->identifier }}
                </span>
                <span class="info-item">
                    <i class="bi bi-folder"></i>
                    {{ $measurement->sensor->group->name ?? 'Sin grupo' }}
                </span>
                <span class="info-item">
                    <i class="bi bi-clock"></i>
                    Período: {{ $measurement->periodo_medicion ?? 30 }} días
                </span>
                <span class="info-item">
                    <i class="bi bi-file-text"></i>
                    Plantilla: {{ $measurement->sensor->group->template->name ?? 'N/A' }}
                </span>
            </div>

            <!-- ============================================
            INFO DE LA MEDICIÓN ACTUAL
            ============================================ -->
            @php
                $mainField = 'consumo_m3';
                $currentValue = $measurement->data[$mainField] ?? null;
                $currentDate = $measurement->measured_at;
                $unit = 'm³';
                $fotoPath = $measurement->data['foto'] ?? 'Sin Foto';
            @endphp

            <div class="previous-measurement-box">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="label">Medición actual</div>
                        <div class="value">
                            {{ number_format($currentValue, 2) }} <span class="unit">{{ $unit }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="label">Fecha</div>
                        <div class="value" style="font-size: 0.95rem;">
                            {{ $currentDate ? $currentDate->format('d/m/Y H:i') : 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================
            FORMULARIO
            ============================================ -->
            <form id="measurementForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="measurement_id" value="{{ $measurement->id }}">
                <input type="hidden" name="sensor_id" value="{{ $measurement->sensor->id }}">
                <input type="hidden" id="groupName" value="{{ str_replace(' ', '_', $measurement->sensor->group->name ?? 'SinGrupo') }}">
                <input type="hidden" id="originalPhoto" value="{{ $fotoPath }}">

                <!-- Campo: Valor de consumo (campo principal) -->
                <div class="mb-3">
                    <label for="consumo_m3" class="form-label">
                        <i class="bi bi-speedometer2 me-1 text-primary"></i>
                        Consumo (m³) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="number" step="0.01" class="form-control" id="consumo_m3" 
                               name="data[consumo_m3]" 
                               value="{{ old('consumo_m3', $currentValue) }}"
                               placeholder="Ej: 125.50" required autofocus>
                        <span class="input-group-text">m³</span>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Valor actual registrado en el medidor.
                    </small>
                </div>

                <!-- Fecha de medición -->
                <div class="mb-3">
                    <label for="fecha_medicion" class="form-label">
                        <i class="bi bi-calendar3 me-1 text-primary"></i>
                        Fecha de Medición <span class="text-danger">*</span>
                    </label>
                    <input type="datetime-local" class="form-control" id="fecha_medicion" 
                           name="data[fecha_medicion]" 
                           value="{{ old('fecha_medicion', $currentDate ? $currentDate->format('Y-m-d\TH:i') : '') }}" required>
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Fecha y hora en que se tomó la medición.
                    </small>
                </div>

                <!-- Foto -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">
                            <i class="bi bi-camera me-1 text-primary"></i>
                            Foto
                            <span class="text-muted small">(opcional)</span>
                        </label>
                        <div class="photo-toggle">
                            <span class="text-muted small me-2">Sin foto</span>
                            <input type="checkbox" class="form-check-input" id="photoToggle" 
                                   {{ $fotoPath !== 'Sin Foto' ? 'checked' : '' }}>
                            <span class="text-muted small ms-2">Con foto</span>
                        </div>
                    </div>
                    
                    <div id="photoSection">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-7">
                                <div class="photo-preview-box" id="photoPreviewBox">
                                    @if($fotoPath !== 'Sin Foto')
                                        <img id="photoPreviewImg" src="/{{ $fotoPath }}" alt="Foto actual">
                                        <button type="button" class="btn-remove-photo" id="btnRemovePhoto">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    @else
                                        <div class="placeholder" id="photoPlaceholder">
                                            <i class="bi bi-camera"></i>
                                            <span>Sin foto</span>
                                        </div>
                                        <img id="photoPreviewImg" class="d-none" alt="Foto">
                                        <button type="button" class="btn-remove-photo" id="btnRemovePhoto" style="display:none;">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="d-flex flex-column gap-2">
                                    <button type="button" class="btn btn-primary w-100" id="btnActivarCamara">
                                        <i class="bi bi-camera me-1"></i> Tomar Foto
                                    </button>
                                    
                                    <div class="photo-name-display">
                                        <i class="bi bi-tag me-1 text-primary"></i>
                                        <span id="photoNameDisplay">{{ $fotoPath !== 'Sin Foto' ? $fotoPath : 'Sin foto' }}</span>
                                    </div>
                                    
                                    <input type="hidden" id="photo" name="data[foto]" value="{{ $fotoPath }}">
                                    
                                    <small class="text-muted text-center">
                                        <i class="bi bi-info-circle"></i>
                                        Formato: <code>(grupo)_(sensor)_(fecha).png</code>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================
                CAMPOS PERSONALIZADOS (SOLO CAMPOS EXTRA)
                ============================================ -->
                @php
                    $systemFields = ['consumo_m3', 'foto', 'fecha_medicion', 'tipo', 'valor'];
                    $customFields = [];
                    if (isset($measurement->sensor->group) && isset($measurement->sensor->group->template) && isset($measurement->sensor->group->template->schema['campos'])) {
                        $customFields = array_filter($measurement->sensor->group->template->schema['campos'], function($campo) use ($systemFields) {
                            if (in_array($campo['nombre'], $systemFields)) {
                                return false;
                            }
                            
                            // Comportamiento Estricto: Permitir SOLO si el contexto lo autoriza expresamente
                            // Asumimos que los campos sin contexto son metadata estática del Sensor
                            $contexto = isset($campo['contexto']) ? strtolower(trim($campo['contexto'])) : 'sensor';
                            return in_array($contexto, ['medicion', 'ambos']);
                        });
                    }
                @endphp

                @if(count($customFields) > 0)
                    <hr>
                    <h6 class="mb-3">
                        <i class="bi bi-file-earmark-text me-1 text-secondary"></i>
                        Campos personalizados
                    </h6>
                    <div class="row g-3">
                        @foreach($customFields as $campo)
                            <div class="col-md-6">
                                <label for="field_{{ $campo['nombre'] }}" class="form-label">
                                    {{ ucfirst(str_replace('_', ' ', $campo['nombre'])) }}
                                    @if($campo['requerido'] ?? false) <span class="text-danger">*</span> @endif
                                </label>
                                @php
                                    $fieldValue = $measurement->data['campos_personalizados'][$campo['nombre']] ?? '';
                                @endphp
                                @if($campo['tipo'] === 'numero')
                                    <input type="number" step="0.01" class="form-control"
                                           id="field_{{ $campo['nombre'] }}" 
                                           name="data[campos_personalizados][{{ $campo['nombre'] }}]"
                                           value="{{ old($campo['nombre'], $fieldValue) }}"
                                           placeholder="Ingresa {{ $campo['nombre'] }}">
                                @elseif($campo['tipo'] === 'texto')
                                    <input type="text" class="form-control"
                                           id="field_{{ $campo['nombre'] }}" 
                                           name="data[campos_personalizados][{{ $campo['nombre'] }}]"
                                           value="{{ old($campo['nombre'], $fieldValue) }}"
                                           placeholder="Ingresa {{ $campo['nombre'] }}">
                                @elseif($campo['tipo'] === 'fecha')
                                    <input type="date" class="form-control"
                                           id="field_{{ $campo['nombre'] }}" 
                                           name="data[campos_personalizados][{{ $campo['nombre'] }}]"
                                           value="{{ old($campo['nombre'], $fieldValue) }}">
                                @elseif($campo['tipo'] === 'booleano')
                                    <select class="form-select" id="field_{{ $campo['nombre'] }}"
                                            name="data[campos_personalizados][{{ $campo['nombre'] }}]">
                                        <option value="1" {{ $fieldValue == '1' ? 'selected' : '' }}>Sí</option>
                                        <option value="0" {{ $fieldValue == '0' ? 'selected' : '' }}>No</option>
                                    </select>
                                @endif
                                @if(isset($campo['unidad']) && $campo['unidad'])
                                    <small class="text-muted">Unidad: {{ $campo['unidad'] }}</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- ============================================
                BOTONES
                ============================================ -->
                <div class="d-flex gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('measurements.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary flex-grow-1" id="saveMeasurementBtn">
                        <i class="bi bi-check-circle me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================
MODAL DE CÁMARA
============================================ -->
<div class="modal fade camera-modal" id="cameraModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title">
                    <i class="bi bi-camera me-2"></i> Tomar Foto
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body position-relative" id="cameraModalBody">
                <video id="modalWebcam" autoplay playsinline muted></video>
                <canvas id="modalCanvas" class="d-none"></canvas>
                <div id="cameraError" class="camera-error d-none">
                    <i class="bi bi-camera-off"></i>
                    <span>No se pudo acceder a la cámara</span>
                </div>
                <div class="camera-controls">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    <button type="button" class="btn btn-take-photo" id="takeModalPhoto">
                        <i class="bi bi-circle-fill text-danger"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // =============================================
    // 1. CONFIGURACIÓN INICIAL
    // =============================================
    const sensorId = {{ $measurement->sensor->id }};
    const measurementId = {{ $measurement->id }};
    const groupName = $('#groupName').val();
    const token = localStorage.getItem('token');
    const mainField = 'consumo_m3';
    const originalPhoto = $('#originalPhoto').val();
    
    let hasRealPhoto = originalPhoto !== 'Sin Foto';
    let stream = null;
    let photoBlob = null;
    let photoUploaded = true; // Si tiene foto original, ya está subida
    let photoEnabled = true;
    let isNewPhoto = false;

    // =============================================
    // 2. TOGGLE DE FOTO
    // =============================================
    $('#photoToggle').on('change', function() {
        photoEnabled = $(this).is(':checked');
        if (photoEnabled) {
            $('#photoSection').slideDown();
            // Restaurar foto original si existe
            if (originalPhoto !== 'Sin Foto' && !isNewPhoto) {
                $('#photo').val(originalPhoto);
                $('#photoNameDisplay').text(originalPhoto);
                $('#photoPreviewImg').attr('src', '/' + originalPhoto).removeClass('d-none');
                $('#photoPlaceholder').addClass('d-none');
                $('#btnRemovePhoto').show();
                $('#photoPreviewBox').addClass('has-photo');
                hasRealPhoto = true;
            }
        } else {
            $('#photoSection').slideUp();
            hasRealPhoto = false;
            photoUploaded = true;
            $('#photo').val('Sin Foto');
            $('#photoNameDisplay').text('Sin foto (desactivada)');
            $('#photoPreviewImg').addClass('d-none').attr('src', '');
            $('#photoPlaceholder').removeClass('d-none');
            $('#btnRemovePhoto').hide();
            $('#photoPreviewBox').removeClass('has-photo');
            isNewPhoto = false;
        }
    });

    // =============================================
    // 3. FUNCIONES DE LA CÁMARA
    // =============================================
    function generatePhotoName() {
        const measuredAtInput = $('#fecha_medicion').val();
        let date, time;

        if (measuredAtInput) {
            const dateObj = new Date(measuredAtInput);
            date = dateObj.toISOString().slice(0, 10).replace(/-/g, '');
            time = dateObj.toTimeString().slice(0, 5).replace(/:/g, '');
        } else {
            const now = new Date();
            date = now.toISOString().slice(0, 10).replace(/-/g, '');
            time = now.toTimeString().slice(0, 5).replace(/:/g, '');
        }

        return `${groupName}_${sensorId}_${date}_${time}.png`;
    }

    async function startCamera() {
        try {
            const constraints = {
                video: {
                    facingMode: { ideal: "environment" },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            };

            const mediaStream = await navigator.mediaDevices.getUserMedia(constraints);
            stream = mediaStream;
            const video = document.getElementById('modalWebcam');
            video.srcObject = mediaStream;
            video.play();
            $('#cameraError').addClass('d-none');
            return true;
        } catch (err) {
            console.error("Error al acceder a la cámara:", err);
            $('#cameraError').removeClass('d-none');
            return false;
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    }

    function takePhoto() {
        if (!stream) {
            showAlert('La cámara no está activa', 'danger');
            return;
        }

        const video = document.getElementById('modalWebcam');
        const canvas = document.getElementById('modalCanvas');
        
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const photoName = generatePhotoName();
        $('#photo').val(photoName);
        $('#photoNameDisplay').text(photoName);
        hasRealPhoto = true;
        isNewPhoto = true;

        canvas.toBlob(function(blob) {
            photoBlob = blob;
        }, 'image/png');

        const imageData = canvas.toDataURL('image/png');
        const previewImg = $('#photoPreviewImg');
        const placeholder = $('#photoPlaceholder');
        const removeBtn = $('#btnRemovePhoto');
        const box = $('#photoPreviewBox');
        
        previewImg.attr('src', imageData);
        previewImg.removeClass('d-none');
        placeholder.addClass('d-none');
        removeBtn.show();
        box.addClass('has-photo');

        stopCamera();
        $('#cameraModal').modal('hide');
        photoUploaded = false;
    }

    // =============================================
    // 4. EVENTOS DE LA CÁMARA
    // =============================================
    $('#btnActivarCamara').click(function() {
        if (!photoEnabled) {
            showAlert('La foto está desactivada. Actívala en el toggle.', 'warning');
            return;
        }
        $('#cameraModal').modal('show');
        setTimeout(() => startCamera(), 300);
    });

    $('#takeModalPhoto').click(takePhoto);

    $('#cameraModal').on('hidden.bs.modal', function() {
        stopCamera();
    });

    $('#btnRemovePhoto').click(function() {
        $('#photoPreviewImg').addClass('d-none').attr('src', '');
        $('#photoPlaceholder').removeClass('d-none');
        $('#btnRemovePhoto').hide();
        $('#photoPreviewBox').removeClass('has-photo');
        $('#photo').val('Sin Foto');
        $('#photoNameDisplay').text('Sin foto');
        photoBlob = null;
        hasRealPhoto = false;
        photoUploaded = true;
        isNewPhoto = false;
    });

    $('#fecha_medicion').change(function() {
        if (hasRealPhoto && isNewPhoto) {
            const photoName = generatePhotoName();
            $('#photo').val(photoName);
            $('#photoNameDisplay').text(photoName);
        }
    });

    // =============================================
    // 5. SUBIR FOTO AL SERVIDOR
    // =============================================
    async function uploadPhoto() {
        if (!photoBlob) {
            throw new Error('No hay foto para subir');
        }

        const photoName = $('#photo').val();
        const formData = new FormData();
        formData.append('foto', photoBlob, photoName);
        formData.append('sensor_id', sensorId);

        return new Promise((resolve, reject) => {
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
                        resolve(response.path);
                    } else {
                        reject(new Error(response.message || 'Error al subir foto'));
                    }
                },
                error: function(xhr) {
                    const errorMessage = xhr.responseJSON?.message || xhr.statusText;
                    reject(new Error(errorMessage));
                }
            });
        });
    }

    // =============================================
    // 6. ENVÍO DEL FORMULARIO
    // =============================================
    $('#measurementForm').submit(async function(e) {
        e.preventDefault();

        const consumo = $('#consumo_m3').val();
        const fecha = $('#fecha_medicion').val();

        if (!consumo || isNaN(consumo)) {
            showAlert('⚠️ Ingresa un valor de consumo válido.', 'danger');
            return;
        }

        const consumoNum = parseFloat(consumo);

        if (!fecha) {
            showAlert('⚠️ Selecciona una fecha de medición.', 'danger');
            return;
        }

        // Verificar foto
        if (photoEnabled && !hasRealPhoto) {
            if (!confirm('⚠️ No has tomado una foto. ¿Deseas continuar sin foto?')) {
                return;
            }
            hasRealPhoto = true;
            photoUploaded = true;
            $('#photo').val('Sin Foto');
        }

        // Subir foto si existe y es nueva
        if (photoBlob && !photoUploaded) {
            try {
                const photoPath = await uploadPhoto();
                $('#photo').val(photoPath);
                photoUploaded = true;
            } catch (error) {
                showAlert('❌ Error al subir la foto: ' + error.message, 'danger');
                return;
            }
        }

        // Preparar datos
        const formData = {
            sensor_id: sensorId,
            data: {
                consumo_m3: consumoNum,
                foto: $('#photo').val(),
                fecha_medicion: fecha,
                campos_personalizados: {}
            },
            measured_at: fecha
        };

        // Agregar campos personalizados
        $('[name^="data[campos_personalizados]"]').each(function() {
            const name = $(this).attr('name').match(/data\[campos_personalizados\]\[(.*?)\]/)[1];
            const val = $(this).val();
            if (val !== '' && val !== null) {
                formData.data.campos_personalizados[name] = isNaN(val) ? val : parseFloat(val);
            }
        });

        // Enviar
        const $btn = $('#saveMeasurementBtn');
        $btn.prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
            Guardando...
        `);

        try {
            const response = await $.ajax({
                url: '/api/measurements/' + measurementId,
                type: 'PUT',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify(formData)
            });

            if (response.success) {
                showAlert('✅ Medición actualizada correctamente', 'success');
                setTimeout(() => {
                    window.location.href = '/measurements';
                }, 1000);
            } else {
                showAlert('❌ ' + (response.message || 'Error al guardar'), 'danger');
                $btn.prop('disabled', false).html(`
                    <i class="bi bi-check-circle me-1"></i> Guardar Cambios
                `);
            }
        } catch (xhr) {
            const errorMessage = xhr.responseJSON?.message || xhr.statusText;
            showAlert('❌ Error: ' + errorMessage, 'danger');
            $btn.prop('disabled', false).html(`
                <i class="bi bi-check-circle me-1"></i> Guardar Cambios
            `);
        }
    });

    // =============================================
    // 7. FUNCIONES AUXILIARES
    // =============================================
    function showAlert(message, type = 'danger') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="font-size: 0.85rem; padding: 0.5rem 0.75rem;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#alertContainer').append(alertHtml);

        setTimeout(() => {
            $('#alertContainer .alert').first().fadeOut(500, function() {
                $(this).remove();
            });
        }, 8000);
    }
});
</script>
@endpush