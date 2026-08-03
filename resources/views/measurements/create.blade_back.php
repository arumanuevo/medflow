@extends('layouts.modern')

@section('title', 'Tomar Medición - ' . ($sensor->name ?? 'Sensor'))

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4><i class="bi bi-rulers"></i> Tomar Medición para {{ $sensor->name }}</h4>
                    <a href="{{ route('sensors.show', ['sensor' => $sensor->id]) }}" class="btn btn-light">
                        <i class="bi bi-arrow-left"></i> Volver al Sensor
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Información del sensor y medición anterior -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h5><i class="bi bi-info-circle"></i> Información del Sensor</h5>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-4">Nombre:</dt>
                                        <dd class="col-sm-8">{{ $sensor->name }}</dd>
                                        <dt class="col-sm-4">Identificador:</dt>
                                        <dd class="col-sm-8">{{ $sensor->identifier }}</dd>
                                        <dt class="col-sm-4">Grupo:</dt>
                                        <dd class="col-sm-8">{{ $sensor->group->name ?? 'Sin grupo' }}</dd>
                                        <dt class="col-sm-4">Tipo:</dt>
                                        <dd class="col-sm-8">{{ $sensor->group->template->type ?? 'N/A' }}</dd>
                                        <dt class="col-sm-4">Período de Medición:</dt>
                                        <dd class="col-sm-8">{{ $sensor->group->periodo_medicion ?? ($defaultPeriod ?? 30) }} días</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h5><i class="bi bi-clock-history"></i> Última Medición</h5>
                                </div>
                                <div class="card-body">
                                    @if(isset($previousMeasurement))
                                        <dl class="row">
                                            <dt class="col-sm-4">Fecha:</dt>
                                            <dd class="col-sm-8">{{ $previousMeasurement->measured_at->format('d/m/Y H:i') }}</dd>
                                            <dt class="col-sm-4">Valor:</dt>
                                            <dd class="col-sm-8">
                                                {{ $previousMeasurement->data['valor'] ?? 'N/A' }}
                                                {{ $sensor->group->template->schema['campos'][0]['unidad'] ?? '' }}
                                            </dd>
                                            <dt class="col-sm-4">Foto:</dt>
                                            <dd class="col-sm-8">
                                                @if(isset($previousMeasurement->data['foto']) && $previousMeasurement->data['foto'] !== 'Sin Foto')
                                                    <a href="/{{ $previousMeasurement->data['foto'] }}" target="_blank" class="btn btn-sm btn-info">
                                                        <i class="bi bi-image"></i> Ver Foto Anterior
                                                    </a>
                                                @else
                                                    <span class="text-muted">Sin Foto</span>
                                                @endif
                                            </dd>
                                        </dl>
                                    @else
                                        <p class="text-muted">No hay mediciones anteriores para este sensor.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario para tomar medición -->
                    <form id="measurementForm">
                        @csrf
                        <input type="hidden" name="sensor_id" value="{{ $sensor->id }}">
                        <input type="hidden" id="groupName" value="{{ str_replace(' ', '_', $sensor->group->name ?? 'SinGrupo') }}">
                        <input type="hidden" id="defaultPeriod" value="{{ $sensor->group->periodo_medicion ?? ($defaultPeriod ?? 30) }}">

                        <!-- Valor de la medición -->
                        <div class="mb-3">
                            <label for="value" class="form-label">Valor *</label>
                            <input type="number" step="0.01" class="form-control" id="value" name="data[valor]" required>
                            @if(isset($sensor->group) && isset($sensor->group->template) && isset($sensor->group->template->schema['campos']))
                                @foreach($sensor->group->template->schema['campos'] as $campo)
                                    @if($campo['nombre'] === 'valor' && isset($campo['unidad']))
                                        <small class="text-muted">Unidad: {{ $campo['unidad'] }}</small>
                                    @endif
                                @endforeach
                            @endif
                        </div>

                        <!-- Fecha de medición -->
                        <div class="mb-3">
                            <label for="measured_at" class="form-label">Fecha de Medición *</label>
                            <input type="datetime-local" class="form-control" id="measured_at" name="measured_at" required>
                        </div>

                        <!-- Período de Medición (solo lectura) -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-calendar-range me-2"></i> Período de Medición
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{ $periodoMedicion }} días" readonly>
                                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="tooltip" title="Este valor se toma de tu configuración global en el dashboard.">
                                    <i class="bi bi-info-circle"></i>
                                </button>
                            </div>
                            <small class="text-muted">
                                Se usará el período de medición configurado en tu dashboard:
                                <strong>{{ $periodoMedicion }} días</strong>.
                                <br>
                                <strong>Nota:</strong> Para cambiar este valor, dirígete a:
                                <a href="{{ route('dashboard') }}" target="_blank">
                                    <i class="bi bi-gear me-1"></i> Configuración Global
                                </a>.
                            </small>
                        </div>

                        <!-- ========================================= -->
                        <!-- SECCIÓN DE FOTO (MEJORADA) -->
                        <!-- ========================================= -->
                        <div class="mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="bi bi-camera text-primary"></i> Foto de la Medición</h5>
                                    <span class="badge bg-danger">Obligatorio</span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <!-- Contenedor de cámara -->
                                            <div class="camera-preview-container border rounded p-2 bg-light mb-3 d-none" id="cameraPreview">
                                                <video id="webcam" autoplay playsinline class="w-100 rounded" style="max-height: 300px;"></video>
                                                <canvas id="canvas" class="d-none w-100"></canvas>
                                            </div>
                                            <!-- Vista previa de la foto -->
                                            <div id="photoPreview" class="mb-3">
                                                <div class="text-center p-4 bg-light rounded border border-dashed">
                                                    <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                                    <p class="text-muted mt-2 mb-0">No hay foto seleccionada</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <!-- Nombre de la foto -->
                                            <div class="mb-3">
                                                <label for="photo" class="form-label fw-semibold">
                                                    <i class="bi bi-tag me-1"></i> Nombre de la Foto
                                                </label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="photo" name="data[foto]" value="Sin Foto" readonly>
                                                    <button type="button" class="btn btn-primary" id="btnActivarCamara">
                                                        <i class="bi bi-camera"></i> Activar Cámara
                                                    </button>
                                                </div>
                                                <small class="text-muted">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    Formato: (nombre_grupo)_(sensorId)_(fecha).png
                                                </small>
                                            </div>
                                            
                                            <!-- Advertencia de foto obligatoria -->
                                            <div id="photoWarning" class="alert alert-warning mt-2 mb-3 p-2 d-flex align-items-center">
                                                <i class="bi bi-exclamation-triangle me-2"></i>
                                                <div>
                                                    <strong>Foto obligatoria:</strong> 
                                                    <span class="small">Debes tomar una foto para guardar esta medición.</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Botones de acción -->
                                            <div class="d-flex gap-2 flex-wrap">
                                                <button type="button" class="btn btn-success d-none" id="btnTakePhoto">
                                                    <i class="bi bi-camera me-1"></i> Tomar Foto
                                                </button>
                                                <button type="button" class="btn btn-danger d-none" id="btnCancelPhoto">
                                                    <i class="bi bi-x-circle me-1"></i> Cancelar
                                                </button>
                                                <button type="button" class="btn btn-secondary d-none" id="btnRetakePhoto">
                                                    <i class="bi bi-arrow-clockwise me-1"></i> Repetir
                                                </button>
                                                <button type="button" class="btn btn-warning d-none" id="btnSubirFoto">
                                                    <i class="bi bi-cloud-arrow-up me-1"></i> Subir Foto
                                                </button>
                                                <button type="button" class="btn btn-info d-none" id="btnDownloadPhoto">
                                                    <i class="bi bi-download me-1"></i> Guardar
                                                </button>
                                            </div>
                                            
                                            <!-- Estado de subida -->
                                            <div id="uploadStatus" class="mt-2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ========================================= -->
                        <!-- CAMPOS PERSONALIZADOS -->
                        <!-- ========================================= -->
                        @if(isset($sensor->group) && isset($sensor->group->template) && isset($sensor->group->template->schema['campos']))
                            <div class="mb-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0"><i class="bi bi-file-earmark-text text-primary"></i> Campos Personalizados</h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach($sensor->group->template->schema['campos'] as $campo)
                                            @if($campo['nombre'] !== 'valor' && $campo['nombre'] !== 'foto' && $campo['nombre'] !== 'tipo')
                                                <div class="mb-3">
                                                    <label for="{{ $campo['nombre'] }}" class="form-label">
                                                        {{ ucfirst($campo['nombre']) }}
                                                        @if($campo['requerido']) <span class="text-danger">*</span> @endif
                                                    </label>
                                                    @if($campo['tipo'] === 'numero')
                                                        <input type="number" step="0.01" class="form-control"
                                                               id="{{ $campo['nombre'] }}" 
                                                               name="data[campos_personalizados][{{ $campo['nombre'] }}]"
                                                               placeholder="Ingresa {{ $campo['nombre'] }}">
                                                    @elseif($campo['tipo'] === 'texto')
                                                        <input type="text" class="form-control"
                                                               id="{{ $campo['nombre'] }}" 
                                                               name="data[campos_personalizados][{{ $campo['nombre'] }}]"
                                                               placeholder="Ingresa {{ $campo['nombre'] }}">
                                                    @elseif($campo['tipo'] === 'fecha')
                                                        <input type="date" class="form-control"
                                                               id="{{ $campo['nombre'] }}" 
                                                               name="data[campos_personalizados][{{ $campo['nombre'] }}]">
                                                    @elseif($campo['tipo'] === 'booleano')
                                                        <select class="form-select" id="{{ $campo['nombre'] }}"
                                                                name="data[campos_personalizados][{{ $campo['nombre'] }}]">
                                                            <option value="1">Sí</option>
                                                            <option value="0">No</option>
                                                        </select>
                                                    @endif
                                                    @if(isset($campo['unidad']) && $campo['unidad'])
                                                        <small class="text-muted">Unidad: {{ $campo['unidad'] }}</small>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Botones de acción -->
                        <div class="d-flex gap-2 mt-4">
                            <a href="{{ route('measurements.select-sensor') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="saveMeasurementBtn">
                                <i class="bi bi-check-circle me-2"></i> Guardar Medición
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================= -->
<!-- MODAL PARA LA CÁMARA (MÓVIL) -->
<!-- ========================================= -->
<div class="modal fade" id="cameraModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-camera me-2"></i> Cámara</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="camera-preview-container border rounded p-2 bg-light">
                        <video id="modalWebcam" autoplay playsinline class="w-100 rounded" style="max-height: 400px;"></video>
                        <canvas id="modalCanvas" class="d-none w-100"></canvas>
                    </div>
                    <div class="text-center text-danger d-none mt-2" id="modalErrorMsg"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i> Cerrar
                </button>
                <button type="button" class="btn btn-success" id="takeModalPhoto">
                    <i class="bi bi-camera me-2"></i> Tomar Foto
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================= -->
<!-- SCRIPTS -->
<!-- ========================================= -->
@push('scripts')
<script>
$(document).ready(function() {
    // =============================================
    // CONFIGURACIÓN INICIAL
    // =============================================
    const sensorId = {{ $sensor->id }};
    const groupName = $('#groupName').val();
    let hasRealPhoto = false;
    let stream = null;
    let photoBlob = null;
    let photoUploaded = false;

    // Inicializar fecha actual
    const now = new Date();
    const localDateTime = now.toISOString().slice(0, 16);
    $('#measured_at').val(localDateTime);

    // =============================================
    // FUNCIONES DE LA CÁMARA
    // =============================================
    
    // Generar nombre de foto
    function generatePhotoName() {
        const measuredAtInput = $('#measured_at').val();
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

    // Iniciar cámara
    async function startCamera(videoElement, canvasElement) {
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
            videoElement.srcObject = mediaStream;
            videoElement.play();
            return true;
        } catch (err) {
            console.error("Error al acceder a la cámara:", err);
            $('#modalErrorMsg').text("No se pudo acceder a la cámara. Verifica los permisos.").removeClass('d-none');
            return false;
        }
    }

    // Detener cámara
    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    }

    // Tomar foto
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
        hasRealPhoto = true;

        canvasElement.toBlob(function(blob) {
            photoBlob = blob;
        }, 'image/png');

        const imageData = canvasElement.toDataURL('image/png');
        $('#photoPreview').html(`
            <img src="${imageData}" class="img-thumbnail" style="max-height: 250px; width: 100%; object-fit: cover;">
        `);

        stopCamera();

        $('#btnTakePhoto, #takeModalPhoto').addClass('d-none');
        $('#btnCancelPhoto, #btnRetakePhoto, #btnSubirFoto, #btnDownloadPhoto').removeClass('d-none');
        photoUploaded = false;
    }

    // Subir foto al servidor
    function uploadPhoto() {
        return new Promise((resolve, reject) => {
            if (!photoBlob) {
                reject(new Error('No hay foto para subir'));
                return;
            }

            const photoName = $('#photo').val();
            const formData = new FormData();
            formData.append('foto', photoBlob, photoName);
            formData.append('sensor_id', sensorId);
            formData.append('measured_at', $('#measured_at').val());

            $('#uploadStatus').html(`
                <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                <span class="small">Subiendo foto...</span>
            `);

            $.ajax({
                url: '/api/measurements/upload-photo',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#uploadStatus').html(`
                            <div class="alert alert-success p-1 mb-0 small">
                                <i class="bi bi-check-circle-fill me-1"></i> Foto subida correctamente
                            </div>
                        `);
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
    // EVENTOS DE LA CÁMARA
    // =============================================

    // Activar cámara
    $('#btnActivarCamara').click(function() {
        if (/Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
            $('#cameraModal').modal('show');
            startCamera(document.getElementById('modalWebcam'), document.getElementById('modalCanvas'));
        } else {
            $('#cameraPreview').removeClass('d-none');
            startCamera(document.getElementById('webcam'), document.getElementById('canvas'));
            $('#btnTakePhoto').removeClass('d-none');
            $('#btnCancelPhoto, #btnRetakePhoto, #btnSubirFoto, #btnDownloadPhoto').addClass('d-none');
        }
    });

    // Tomar foto (principal)
    $('#btnTakePhoto').click(function() {
        takePhoto(document.getElementById('webcam'), document.getElementById('canvas'));
    });

    // Tomar foto (modal)
    $('#takeModalPhoto').click(function() {
        takePhoto(document.getElementById('modalWebcam'), document.getElementById('modalCanvas'));
        $('#cameraModal').modal('hide');
    });

    // Cancelar foto
    $('#btnCancelPhoto').click(function() {
        stopCamera();
        $('#cameraPreview').addClass('d-none');
        $('#btnTakePhoto, #btnCancelPhoto, #btnRetakePhoto, #btnSubirFoto, #btnDownloadPhoto').addClass('d-none');
        $('#photoPreview').html(`
            <div class="text-center p-4 bg-light rounded border border-dashed">
                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No hay foto seleccionada</p>
            </div>
        `);
        $('#photo').val('Sin Foto');
        photoBlob = null;
        hasRealPhoto = false;
        photoUploaded = false;
    });

    // Repetir foto
    $('#btnRetakePhoto').click(function() {
        if (/Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
            $('#cameraModal').modal('show');
            startCamera(document.getElementById('modalWebcam'), document.getElementById('modalCanvas'));
        } else {
            $('#cameraPreview').removeClass('d-none');
            startCamera(document.getElementById('webcam'), document.getElementById('canvas'));
            $('#btnTakePhoto').removeClass('d-none');
            $('#btnCancelPhoto, #btnRetakePhoto, #btnSubirFoto, #btnDownloadPhoto').addClass('d-none');
        }
        photoUploaded = false;
    });

    // Subir foto manualmente
    $('#btnSubirFoto').click(async function() {
        if (!photoBlob) {
            showAlert('No hay foto para subir', 'danger');
            return;
        }

        try {
            const photoPath = await uploadPhoto();
            $('#photo').val(photoPath);
            photoUploaded = true;
            showAlert('Foto subida correctamente', 'success');
        } catch (error) {
            showAlert('Error al subir foto: ' + error.message, 'danger');
        }
    });

    // Descargar foto
    $('#btnDownloadPhoto').click(function() {
        if (!photoBlob) {
            showAlert('No hay foto para descargar', 'danger');
            return;
        }

        const photoName = $('#photo').val();
        const url = URL.createObjectURL(photoBlob);
        const a = document.createElement('a');
        a.href = url;
        a.download = photoName;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        $('#uploadStatus').html(`
            <div class="alert alert-info p-1 mb-0 small">
                <i class="bi bi-check-circle-fill me-1"></i> Foto guardada como ${photoName}
            </div>
        `);
    });

    // Actualizar nombre de foto al cambiar fecha
    $('#measured_at').change(function() {
        if (hasRealPhoto) {
            const photoName = generatePhotoName();
            $('#photo').val(photoName);
        }
    });

    // Cerrar modal de cámara
    $('#cameraModal').on('hidden.bs.modal', function() {
        stopCamera();
    });

    // =============================================
    // MOSTRAR/OCULTAR ADVERTENCIA
    // =============================================
    $('#requirePhoto').on('change', function() {
        if ($(this).is(':checked')) {
            $('#photoWarning').removeClass('d-none');
        } else {
            $('#photoWarning').addClass('d-none');
        }
    });

    // =============================================
    // ENVÍO DEL FORMULARIO
    // =============================================
    $('#measurementForm').submit(async function(e) {
        e.preventDefault();

        // Validar valor
        if (!$('#value').val()) {
            showAlert('El campo Valor es obligatorio.', 'danger');
            return false;
        }

        // Validar foto
        if (!hasRealPhoto) {
            showAlert('⚠️ La foto es obligatoria. Activa la cámara y toma una foto.', 'danger');
            return false;
        }

        // Subir foto si está pendiente
        if (photoBlob && !photoUploaded) {
            try {
                const photoPath = await uploadPhoto();
                $('#photo').val(photoPath);
                photoUploaded = true;
            } catch (error) {
                showAlert('Error al subir la foto: ' + error.message, 'danger');
                return false;
            }
        }

        // Preparar datos
        const formData = {
            sensor_id: sensorId,
            data: {
                tipo: '{{ $sensor->group->template->type ?? "personalizado" }}',
                valor: parseFloat($('#value').val()),
                foto: $('#photo').val(),
                campos_personalizados: {}
            },
            measured_at: $('#measured_at').val()
        };

        // Agregar campos personalizados
        $('[name^="data[campos_personalizados]"]').each(function() {
            const name = $(this).attr('name').match(/data\[campos_personalizados\]\[(.*?)\]/)[1];
            const value = $(this).val();
            if (value !== '' && value !== null) {
                formData.data.campos_personalizados[name] = isNaN(value) ? value : parseFloat(value);
            }
        });

        // Enviar
        $('#saveMeasurementBtn').prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
            Guardando...
        `);

        $.ajax({
            url: '/api/measurements',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    showAlert('✅ Medición guardada correctamente', 'success');
                    setTimeout(() => {
                        window.location.href = '/sensors/' + sensorId;
                    }, 1000);
                } else {
                    showAlert(response.message || 'Error al guardar', 'danger');
                    $('#saveMeasurementBtn').prop('disabled', false).html(`
                        <i class="bi bi-check-circle me-2"></i> Guardar Medición
                    `);
                }
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON?.message || xhr.statusText;
                showAlert('❌ Error: ' + errorMessage, 'danger');
                $('#saveMeasurementBtn').prop('disabled', false).html(`
                    <i class="bi bi-check-circle me-2"></i> Guardar Medición
                `);
            }
        });
    });

    // =============================================
    // FUNCIONES AUXILIARES
    // =============================================
    function showAlert(message, type = 'danger') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('.card-body').prepend(alertHtml);

        setTimeout(() => {
            $('.alert').first().fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
    }
});
</script>
@endpush