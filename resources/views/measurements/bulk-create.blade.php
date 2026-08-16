@extends('layouts.modern')

@section('title', 'Medición Masiva - ' . ($sensor->name ?? 'Sensor'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/bulk-measurement.css') }}">
@endpush

@section('content')
<div class="measurement-container">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-rulers me-2"></i>
                Medición Masiva
            </h5>
            <div>
                <a href="{{ route('measurements.select-sensor') }}" class="btn btn-light btn-sm me-1">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
                <a href="{{ route('sensors.index') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-x-lg"></i> Cancelar
                </a>
            </div>
        </div>
        <div class="card-body">
            <div id="alertContainer"></div>

            <!-- ============================================
            PROGRESO BULK
            ============================================ -->
            <div class="bulk-progress">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="progress-text">
                        <i class="bi bi-list-check me-1"></i>
                        Sensor {{ $currentPosition }} de {{ $totalMarked }}
                    </span>
                    @if($hasMoreSensors)
                        <span class="badge bg-info text-dark">
                            <i class="bi bi-arrow-right me-1"></i>
                            Siguiente sensor disponible
                        </span>
                    @else
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle me-1"></i>
                            Último sensor
                        </span>
                    @endif
                </div>
                <div class="progress-bar-custom">
                    <div class="fill" style="width: {{ ($currentPosition / $totalMarked) * 100 }}%;"></div>
                </div>
            </div>

            <!-- DEPURACIÓN TEMPORAL -->
            <div class="alert alert-info small">
                <strong>Debug:</strong> Sensor ID: {{ $sensor->id }} | Posición: {{ $currentPosition }} de {{ $totalMarked }} | ¿Siguiente? {{ $hasMoreSensors ? 'Sí' : 'No' }}
                @if($hasMoreSensors && isset($nextSensor))
                    | Siguiente ID: {{ $nextSensor->id }}
                @endif
            </div>

            <!-- ============================================
            INFO COMPACTA DEL SENSOR
            ============================================ -->
            <div class="sensor-info-compact">
                <span class="info-item">
                    <i class="bi bi-tag"></i>
                    <strong>{{ $sensor->name }}</strong>
                </span>
                <span class="info-item">
                    <i class="bi bi-hash"></i>
                    {{ $sensor->identifier }}
                </span>
                <span class="info-item">
                    <i class="bi bi-folder"></i>
                    {{ $sensor->group->name ?? 'Sin grupo' }}
                </span>
                <span class="info-item">
                    <i class="bi bi-clock"></i>
                    Período: {{ $periodoMedicion ?? 30 }} días
                </span>
            </div>

            <!-- ============================================
            INFO DE LA MEDICIÓN ANTERIOR
            ============================================ -->
            @if($lastValue !== null)
                <div class="previous-measurement-box">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="label">Última medición</div>
                            <div class="value">
                                {{ number_format($lastValue, 2) }} <span class="unit">m³</span>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="label">Fecha</div>
                            <div class="value" style="font-size: 0.95rem;">
                                {{ $previousMeasurement ? $previousMeasurement->measured_at->format('d/m/Y H:i') : 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="previous-measurement-box" style="border-left-color: #ffc107; background: #fff8e7;">
                    <div class="label">⚠️ Sin mediciones anteriores</div>
                    <div class="value" style="font-size: 0.9rem; font-weight: 400;">
                        Esta es la primera medición para este sensor.
                    </div>
                </div>
            @endif

            <!-- ============================================
            FORMULARIO
            ============================================ -->
            <form id="measurementForm">
                @csrf
                <input type="hidden" name="sensor_id" value="{{ $sensor->id }}">
                <input type="hidden" id="groupName" value="{{ str_replace(' ', '_', $sensor->group->name ?? 'SinGrupo') }}">

                <!-- Valor -->
                <div class="mb-3">
                    <label for="consumo_m3" class="form-label">
                        Consumo Actual (m³) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="number" step="0.01" class="form-control" id="consumo_m3" 
                               name="data[consumo_m3]" placeholder="Ej: 125.50" required autofocus>
                        <span class="input-group-text">m³</span>
                    </div>
                    @if($lastValue !== null)
                        <small class="text-muted">
                            Anterior: <strong>{{ number_format($lastValue, 2) }} m³</strong>
                            <span id="consumptionDiff" class="badge bg-secondary ms-1">Diferencia: 0.00 m³</span>
                        </small>
                    @endif
                </div>

                <!-- Consumo calculado -->
                @if($lastValue !== null)
                    <div class="consumption-info">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <i class="bi bi-calculator text-primary"></i>
                            <span>
                                <strong>Consumo calculado:</strong>
                                <span id="calculatedConsumption">0.00</span> m³
                            </span>
                            <span class="badge bg-secondary" id="consumptionStatus">⏳ Pendiente</span>
                        </div>
                    </div>
                @endif

                <!-- Fecha -->
                <div class="mb-3">
                    <label for="fecha_medicion" class="form-label">
                        Fecha de Medición <span class="text-danger">*</span>
                    </label>
                    <input type="datetime-local" class="form-control" id="fecha_medicion" 
                           name="data[fecha_medicion]" required>
                </div>

                <!-- Foto con Toggle -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">
                            <i class="bi bi-camera me-1 text-primary"></i> Foto
                        </label>
                        <div class="photo-toggle">
                            <span class="text-muted small me-2">Sin foto</span>
                            <input type="checkbox" class="form-check-input" id="photoToggle" checked>
                            <span class="text-muted small ms-2">Con foto</span>
                        </div>
                    </div>
                    
                    <div id="photoSection">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-7">
                                <div class="photo-preview-box" id="photoPreviewBox">
                                    <div class="placeholder" id="photoPlaceholder">
                                        <i class="bi bi-camera"></i>
                                        <span>Sin foto</span>
                                    </div>
                                    <img id="photoPreviewImg" class="d-none" alt="Foto">
                                    <button type="button" class="btn-remove-photo" id="btnRemovePhoto" style="display:none;">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="d-flex flex-column gap-2">
                                    <button type="button" class="btn btn-primary w-100" id="btnActivarCamara">
                                        <i class="bi bi-camera me-1"></i> Tomar Foto
                                    </button>
                                    <div class="photo-name-display">
                                        <i class="bi bi-tag me-1 text-primary"></i>
                                        <span id="photoNameDisplay">Sin foto</span>
                                    </div>
                                    <input type="hidden" id="photo" name="data[foto]" value="Sin Foto">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Campos personalizados -->
                @php
                    $customFields = [];
                    if (isset($sensor->group) && isset($sensor->group->template) && isset($sensor->group->template->schema['campos'])) {
                        $customFields = array_filter($sensor->group->template->schema['campos'], function($campo) {
                            return !in_array($campo['nombre'], ['consumo_m3', 'foto', 'fecha_medicion', 'identificador']);
                        });
                    }
                @endphp

                @if(count($customFields) > 0)
                    <hr>
                    <h6 class="mb-3">Campos adicionales</h6>
                    <div class="row g-3">
                        @foreach($customFields as $campo)
                            <div class="col-md-6">
                                <label for="field_{{ $campo['nombre'] }}" class="form-label">
                                    {{ ucfirst(str_replace('_', ' ', $campo['nombre'])) }}
                                    @if($campo['requerido'] ?? false) <span class="text-danger">*</span> @endif
                                </label>
                                @if($campo['tipo'] === 'numero')
                                    <input type="number" step="0.01" class="form-control"
                                           id="field_{{ $campo['nombre'] }}" 
                                           name="data[campos_personalizados][{{ $campo['nombre'] }}]"
                                           placeholder="Ingresa {{ $campo['nombre'] }}">
                                @elseif($campo['tipo'] === 'texto')
                                    <input type="text" class="form-control"
                                           id="field_{{ $campo['nombre'] }}" 
                                           name="data[campos_personalizados][{{ $campo['nombre'] }}]"
                                           placeholder="Ingresa {{ $campo['nombre'] }}">
                                @elseif($campo['tipo'] === 'fecha')
                                    <input type="date" class="form-control"
                                           id="field_{{ $campo['nombre'] }}" 
                                           name="data[campos_personalizados][{{ $campo['nombre'] }}]">
                                @elseif($campo['tipo'] === 'booleano')
                                    <select class="form-select" id="field_{{ $campo['nombre'] }}"
                                            name="data[campos_personalizados][{{ $campo['nombre'] }}]">
                                        <option value="1">Sí</option>
                                        <option value="0">No</option>
                                    </select>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
<!-- ============================================
DEBUG DETALLADO - TEMPORAL
============================================ -->
<div class="card mb-3" style="background: #f8f9fa; border: 2px solid #ffc107;">
    <div class="card-body">
        <h6 class="text-warning"><i class="bi bi-bug me-1"></i> DEBUG - BULK CREATE</h6>
        <div class="row small">
            <div class="col-md-6">
                <strong>Sensor actual:</strong> ID {{ $sensor->id }} - {{ $sensor->name }}
                <br>
                <strong>Marcado:</strong> {{ $sensor->marcado_para_medicion ? '✅ Sí' : '❌ No' }}
                <br>
                <strong>Posición:</strong> {{ $currentPosition }} de {{ $totalMarked }}
            </div>
            <div class="col-md-6">
                <strong>¿Tiene siguiente?</strong> {{ $hasMoreSensors ? '✅ Sí' : '❌ No' }}
                @if($hasMoreSensors && isset($nextSensor))
                    <br>
                    <strong>Siguiente:</strong> ID {{ $nextSensor->id }} - {{ $nextSensor->name }}
                    <br>
                    <strong>URL siguiente:</strong> <code>{{ $nextSensorUrl }}</code>
                @endif
                @if(isset($firstSensorId))
                    <br>
                    <strong>Primer sensor:</strong> ID {{ $firstSensorId }}
                @endif
            </div>
        </div>
        <hr class="my-1">
        <div class="small text-muted">
            <strong>Todos los IDs marcados:</strong> 
            @php
                $allIds = \App\Models\Sensor::where('marcado_para_medicion', true)
                    ->whereHas('group', function($q) use ($sensor) {
                        $q->where('user_id', $sensor->group->user_id ?? 0);
                    })
                    ->orderBy('id', 'asc')
                    ->pluck('id')
                    ->toArray();
            @endphp
            {{ implode(', ', $allIds) }}
        </div>
    </div>
</div>
                <!-- Botones -->
                <div class="d-flex gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('measurements.select-sensor') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                    <button type="submit" class="btn btn-primary flex-grow-1" id="saveMeasurementBtn">
                        <i class="bi bi-check-circle me-1"></i> Guardar Medición
                    </button>
                    @if($hasMoreSensors && isset($nextSensor))
                        <a href="{{ route('measurements.bulk-create', $nextSensor->id) }}" 
                           class="btn btn-info btn-next-sensor" id="nextSensorBtn">
                            <i class="bi bi-arrow-right me-1"></i> Siguiente
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de cámara -->
<div class="modal fade camera-modal" id="cameraModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-md-down modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white py-2">
                <h6 class="modal-title"><i class="bi bi-camera me-2"></i> Tomar Foto</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 position-relative">
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
    // CONFIGURACIÓN
    // =============================================
    const sensorId = {{ $sensor->id }};
    const groupName = '{{ str_replace(' ', '_', $sensor->group->name ?? 'SinGrupo') }}';
    const token = localStorage.getItem('token');
    const lastValue = {{ $lastValue !== null ? $lastValue : 'null' }};
    const hasMoreSensors = {{ $hasMoreSensors ? 'true' : 'false' }};
    const allMeasurements = @json($allMeasurements ?? []);
    const nextSensorUrl = @json(isset($nextSensor) ? route('measurements.bulk-create', $nextSensor->id) : null);

    let hasRealPhoto = false;
    let stream = null;
    let photoBlob = null;
    let photoUploaded = false;
    let photoEnabled = true;

    // Inicializar fecha
    const now = new Date();
    $('#fecha_medicion').val(now.toISOString().slice(0, 16));

    // =============================================
    // TOGGLE FOTO
    // =============================================
    $('#photoToggle').on('change', function() {
        photoEnabled = $(this).is(':checked');
        if (photoEnabled) {
            $('#photoSection').slideDown();
            if (!hasRealPhoto) {
                $('#photo').val('Sin Foto');
                $('#photoNameDisplay').text('Sin foto');
            }
        } else {
            $('#photoSection').slideUp();
            hasRealPhoto = true;
            photoUploaded = true;
            $('#photo').val('Sin Foto');
            $('#photoNameDisplay').text('Sin foto (desactivada)');
            $('#photoPreviewImg').addClass('d-none').attr('src', '');
            $('#photoPlaceholder').removeClass('d-none');
            $('#btnRemovePhoto').hide();
            $('#photoPreviewBox').removeClass('has-photo');
        }
    });

    // =============================================
    // VALIDACIÓN DE SECUENCIA
    // =============================================
    function validateMeasurement(value, date) {
        if (allMeasurements.length === 0) {
            return { valid: true, message: 'Primera medición' };
        }

        const newDate = new Date(date);
        let previous = null;
        let next = null;
        let position = 'last';

        for (let i = 0; i < allMeasurements.length; i++) {
            const mDate = new Date(allMeasurements[i].date);
            
            if (newDate.getTime() === mDate.getTime()) {
                return { valid: false, message: 'Ya existe una medición con esta fecha.' };
            }
            
            if (newDate < mDate) {
                position = 'intermediate';
                next = allMeasurements[i];
                if (i > 0) {
                    previous = allMeasurements[i - 1];
                }
                break;
            }
        }

        if (position === 'last') {
            previous = allMeasurements[allMeasurements.length - 1];
            next = null;
        }

        if (position === 'intermediate' && previous === null) {
            if (next && value >= next.value) {
                return {
                    valid: false,
                    message: `El valor (${value}) debe ser MENOR al siguiente (${next.value}).`
                };
            }
            return { valid: true, message: 'Primera medición' };
        }

        if (position === 'last' && previous) {
            if (value <= previous.value) {
                return {
                    valid: false,
                    message: `El valor (${value}) debe ser MAYOR al anterior (${previous.value}).`
                };
            }
            return { valid: true, message: `Última medición` };
        }

        if (position === 'intermediate' && previous && next) {
            if (value <= previous.value) {
                return {
                    valid: false,
                    message: `El valor (${value}) debe ser MAYOR al anterior (${previous.value}).`
                };
            }
            if (value >= next.value) {
                return {
                    valid: false,
                    message: `El valor (${value}) debe ser MENOR al siguiente (${next.value}).`
                };
            }
            return { valid: true, message: `Medición intermedia` };
        }

        return { valid: true, message: 'Válida' };
    }

    // =============================================
    // ACTUALIZAR UI
    // =============================================
    function updateUI(value, date) {
        const diffSpan = $('#consumptionDiff');
        const calcSpan = $('#calculatedConsumption');
        const statusBadge = $('#consumptionStatus');

        if (!value || isNaN(value) || !date) {
            diffSpan.text('Diferencia: 0.00 m³');
            calcSpan.text('0.00');
            statusBadge.text('⏳ Pendiente').removeClass('bg-success bg-danger').addClass('bg-secondary');
            return;
        }

        const result = validateMeasurement(value, date);

        if (result.valid) {
            statusBadge.text('✅ ' + result.message).removeClass('bg-secondary bg-danger').addClass('bg-success');
            
            if (lastValue !== null) {
                const diff = value - lastValue;
                diffSpan.text(`Diferencia: ${Math.abs(diff).toFixed(2)} m³`);
                calcSpan.text(diff.toFixed(2));
            }
        } else {
            statusBadge.text('❌ ' + result.message).removeClass('bg-secondary bg-success').addClass('bg-danger');
        }
    }

    // =============================================
    // EVENTOS
    // =============================================
    $('#consumo_m3, #fecha_medicion').on('change input', function() {
        const value = parseFloat($('#consumo_m3').val());
        const date = $('#fecha_medicion').val();
        updateUI(value, date);
    });

    // =============================================
    // CÁMARA
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
                video: { facingMode: { ideal: "environment" }, width: { ideal: 1280 }, height: { ideal: 720 } },
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

        canvas.toBlob(function(blob) {
            photoBlob = blob;
        }, 'image/png');

        const imageData = canvas.toDataURL('image/png');
        $('#photoPreviewImg').attr('src', imageData).removeClass('d-none');
        $('#photoPlaceholder').addClass('d-none');
        $('#btnRemovePhoto').show();
        $('#photoPreviewBox').addClass('has-photo');

        stopCamera();
        $('#cameraModal').modal('hide');
        photoUploaded = false;
    }

    // =============================================
    // EVENTOS CÁMARA
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

    $('#cameraModal').on('hidden.bs.modal', stopCamera);

    $('#btnRemovePhoto').click(function() {
        $('#photoPreviewImg').addClass('d-none').attr('src', '');
        $('#photoPlaceholder').removeClass('d-none');
        $('#btnRemovePhoto').hide();
        $('#photoPreviewBox').removeClass('has-photo');
        $('#photo').val('Sin Foto');
        $('#photoNameDisplay').text('Sin foto');
        photoBlob = null;
        hasRealPhoto = false;
        photoUploaded = false;
    });

    // =============================================
    // SUBIR FOTO
    // =============================================
    async function uploadPhoto() {
        if (!photoBlob) {
            throw new Error('No hay foto para subir');
        }

        const formData = new FormData();
        formData.append('foto', photoBlob, $('#photo').val());
        formData.append('sensor_id', sensorId);

        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/api/measurements/upload-photo',
                type: 'POST',
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
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
                    reject(new Error(xhr.responseJSON?.message || xhr.statusText));
                }
            });
        });
    }

    // =============================================
    // ENVÍO DEL FORMULARIO
    // =============================================
    $('#measurementForm').submit(async function(e) {
        e.preventDefault();

        const consumo = $('#consumo_m3').val();
        const fecha = $('#fecha_medicion').val();

        if (!consumo || isNaN(consumo)) {
            showAlert('⚠️ Ingresa un valor de consumo válido.', 'danger');
            return;
        }

        if (!fecha) {
            showAlert('⚠️ Selecciona una fecha de medición.', 'danger');
            return;
        }

        const consumoNum = parseFloat(consumo);
        const validation = validateMeasurement(consumoNum, fecha);
        
        if (!validation.valid) {
            showAlert('❌ ' + validation.message, 'danger');
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

        // Subir foto si existe
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

        $('[name^="data[campos_personalizados]"]').each(function() {
            const name = $(this).attr('name').match(/data\[campos_personalizados\]\[(.*?)\]/)[1];
            const val = $(this).val();
            if (val !== '' && val !== null) {
                formData.data.campos_personalizados[name] = isNaN(val) ? val : parseFloat(val);
            }
        });

        const $btn = $('#saveMeasurementBtn');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Guardando...');

        try {
            const response = await $.ajax({
                url: '/api/measurements',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify(formData)
            });

            if (response.success) {
                showAlert('✅ Medición guardada correctamente', 'success');
                
                if (hasMoreSensors && nextSensorUrl) {
                    $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Guardado ✓');
                    showAlert('📋 Redirigiendo al siguiente sensor...', 'info');
                    setTimeout(() => {
                        window.location.href = nextSensorUrl;
                    }, 1500);
                } else {
                    setTimeout(() => {
                        window.location.href = '/sensors/' + sensorId;
                    }, 1500);
                }
            } else {
                showAlert('❌ ' + (response.message || 'Error al guardar'), 'danger');
                $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Guardar Medición');
            }
        } catch (xhr) {
            showAlert('❌ Error: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
            $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Guardar Medición');
        }
    });

    // =============================================
    // ALERTAS
    // =============================================
    function showAlert(message, type = 'danger') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="font-size: 0.85rem; padding: 0.5rem 0.75rem;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('#alertContainer').append(alertHtml);
        setTimeout(() => {
            $('#alertContainer .alert').first().fadeOut(500, function() { $(this).remove(); });
        }, 8000);
    }
});
</script>
@endpush
