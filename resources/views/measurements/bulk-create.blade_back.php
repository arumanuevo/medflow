@extends('layouts.modern')

@section('title', 'Medición Masiva - ' . ($sensor->name ?? 'Sensor'))

@section('content')
<!-- Modal para errores de validación -->
<div class="modal fade" id="validationErrorModal" tabindex="-1" aria-labelledby="validationErrorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="validationErrorModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i> Error de Validación de Medición
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="validationErrorMessage">
                <!-- Mensaje dinámico -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i> Entendido
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4><i class="bi bi-rulers"></i> Medición Masiva - {{ $sensor->name }}</h4>
                    <a href="{{ route('measurements.select-sensor') }}" class="btn btn-light">
                        <i class="bi bi-arrow-left"></i> Volver a Sensores
                    </a>
                </div>
                <div class="card-body">
                    <!-- ✅ Indicador de progreso para mediciones masivas -->
<!-- ✅ CÓDIGO CORRECTO -->
@if($hasMoreSensors)
<div class="alert alert-info mb-4">
    <div class="d-flex align-items-center">
        <div class="flex-shrink-0">
            <i class="bi bi-arrow-repeat text-primary" style="font-size: 1.5rem;"></i>
        </div>
        <div class="flex-grow-1 ms-3">
            <h6 class="mb-1">Modo de medición masiva activo</h6>
            <p class="mb-0 text-muted small">
                Estás tomando mediciones de varios sensores. Después de guardar, serás redirigido al siguiente sensor.
                <br>
               
            </p>
        </div>
        <div class="flex-shrink-0">
            <span class="badge bg-primary rounded-pill">
                <i class="bi bi-arrow-right me-1"></i> 
                {{ $hasMoreSensors ? 'Hay más sensores' : 'Último sensor' }}
            </span>
        </div>
    </div>
</div>
@else
<!-- ✅ Mensaje cuando NO hay más sensores -->
<div class="alert alert-warning mb-4">
    <div class="d-flex align-items-center">
        <div class="flex-shrink-0">
            <i class="bi bi-info-circle text-warning" style="font-size: 1.5rem;"></i>
        </div>
        <div class="flex-grow-1 ms-3">
            <h6 class="mb-1">Último sensor de la medición masiva</h6>
            <p class="mb-0 text-muted small">
                Este es el último sensor marcado. Después de guardar, finalizarás el proceso de medición masiva.
            </p>
        </div>
        <div class="flex-shrink-0">
            <span class="badge bg-success rounded-pill">
                <i class="bi bi-check-circle me-1"></i> Finalizar
            </span>
        </div>
    </div>
</div>
@endif
                    <!-- Alertas -->
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

                    <!-- Información del sensor -->
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
                                        </dl>
                                    @else
                                        <p class="text-muted">No hay mediciones anteriores para este sensor.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario para medición masiva -->
                    <form id="bulkMeasurementForm">
                        <input type="hidden" name="sensor_id" value="{{ $sensor->id }}">
                        <input type="hidden" id="groupName" value="{{ str_replace(' ', '_', $sensor->group->name ?? 'SinGrupo') }}">
                        <input type="hidden" id="sensorId" value="{{ $sensor->id }}">
                        <input type="hidden" id="previousMeasurementValue" value="{{ $previousMeasurement->data['valor'] ?? '' }}">
                        <input type="hidden" id="previousMeasurementDate" value="{{ $previousMeasurement->measured_at ?? '' }}">

                        <!-- Valor de la medición -->
                        <div class="mb-3">
                            <label for="value" class="form-label">Valor *</label>
                            <input type="number" step="0.01" class="form-control" id="value" name="data[valor]" required>
                            @if(isset($sensor->group->template->schema['campos']))
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

                        <!-- Sección de foto -->
                        <div class="mb-4">
                            <div class="card">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="bi bi-camera"></i> Foto de la Medición</h5>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="requirePhoto" checked>
                                        <label class="form-check-label" for="requirePhoto">Foto obligatoria</label>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <!-- Vista previa de la cámara -->
                                            <div class="camera-preview-container border rounded p-2 bg-light mb-3 d-none" id="cameraPreview">
                                                <video id="webcam" autoplay playsinline class="w-100"></video>
                                                <canvas id="canvas" class="d-none w-100"></canvas>
                                            </div>
                                            <!-- Vista previa de la foto tomada -->
                                            <div id="photoPreview" class="mb-3">
                                                <div class="text-center p-4 bg-light rounded">
                                                    <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                                    <p class="text-muted mt-2">No hay foto seleccionada</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="photo" class="form-label">Nombre de la Foto</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="photo" name="data[foto]" value="Sin Foto" readonly>
                                                    <button type="button" class="btn btn-primary" id="btnActivarCamara">
                                                        <i class="bi bi-camera"></i> Activar Cámara
                                                    </button>
                                                </div>
                                                <small class="text-muted">
                                                    Formato: (nombre_del_grupo)_(sensorId)_(fecha_de_medición).png
                                                </small>
                                                <div id="photoWarning" class="alert alert-warning mt-2 mb-0 p-2">
                                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                                    <strong>Foto obligatoria:</strong> Debes tomar una foto para continuar.
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <button type="button" class="btn btn-success d-none" id="btnTakePhoto">
                                                    <i class="bi bi-camera"></i> Tomar Foto
                                                </button>
                                                <button type="button" class="btn btn-danger d-none" id="btnCancelPhoto">
                                                    <i class="bi bi-x-circle"></i> Cancelar
                                                </button>
                                                <button type="button" class="btn btn-secondary d-none" id="btnRetakePhoto">
                                                    <i class="bi bi-arrow-clockwise"></i> Volver a Tomar
                                                </button>
                                                <button type="button" class="btn btn-warning d-none" id="btnSubirFoto">
                                                    <i class="bi bi-cloud-arrow-up"></i> Subir al Servidor
                                                </button>
                                            </div>
                                            <div id="uploadStatus" class="mt-2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Campos personalizados -->
                        @if(isset($sensor->group->template->schema['campos']))
                            <div class="mb-4">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h5><i class="bi bi-file-earmark-text"></i> Campos Personalizados</h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach($sensor->group->template->schema['campos'] as $campo)
                                            @if($campo['nombre'] !== 'valor' && $campo['nombre'] !== 'tipo')
                                                <div class="mb-3">
                                                    <label for="{{ $campo['nombre'] }}" class="form-label">
                                                        {{ $campo['nombre'] }}
                                                        @if($campo['requerido']) <span class="text-danger">*</span> @endif
                                                    </label>
                                                    @if($campo['tipo'] === 'numero')
                                                        <input type="number" step="0.01" class="form-control"
                                                               id="{{ $campo['nombre'] }}" name="data[campos_personalizados][{{ $campo['nombre'] }}]">
                                                    @elseif($campo['tipo'] === 'texto')
                                                        <input type="text" class="form-control"
                                                               id="{{ $campo['nombre'] }}" name="data[campos_personalizados][{{ $campo['nombre'] }}]">
                                                    @elseif($campo['tipo'] === 'fecha')
                                                        <input type="date" class="form-control"
                                                               id="{{ $campo['nombre'] }}" name="data[campos_personalizados][{{ $campo['nombre'] }}]">
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

                        <!-- Botones del formulario (solo "Continuar con el Siguiente Sensor") -->
                        <!-- Botones del formulario -->
<div class="d-flex gap-2 mt-4">
    <a href="{{ route('measurements.select-sensor') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-2"></i> Cancelar
    </a>
    @if($hasMoreSensors)
        <button type="button" class="btn btn-info" id="continueToNextSensor">
            <i class="bi bi-arrow-right me-2"></i> Continuar con el Siguiente Sensor
        </button>
    @else
        <button type="button" class="btn btn-success" id="saveAndFinish">
            <i class="bi bi-check-circle me-2"></i> Guardar y Finalizar
        </button>
    @endif
</div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para la cámara -->
<div class="modal fade" id="cameraModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cámara</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="camera-preview-container border rounded p-2 bg-light">
                        <video id="modalWebcam" autoplay playsinline class="w-100"></video>
                        <canvas id="modalCanvas" class="d-none w-100"></canvas>
                    </div>
                    <div class="text-center text-danger d-none mt-2" id="modalErrorMsg"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cerrar
                </button>
                <button type="button" class="btn btn-success" id="takeModalPhoto">
                    <i class="bi bi-camera"></i> Tomar Foto
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Configuración inicial
    const sensorId = {{ $sensor->id }};
    const groupName = $('#groupName').val();
    const previousMeasurementValue = parseFloat($('#previousMeasurementValue').val()) || null;
    const previousMeasurementDate = $('#previousMeasurementDate').val();
    let hasRealPhoto = false; // Variable para rastrear si hay una foto REAL
    let stream = null;
    let photoBlob = null;
    let photoUploaded = false;

    // Inicializar la fecha actual
    const now = new Date();
    const localDateTime = now.toISOString().slice(0, 16);
    $('#measured_at').val(localDateTime);

    // Función para mostrar alertas
    function showAlert(message, type = 'danger') {
        $('.card-body').prepend(`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
    }

    // Función para mostrar errores en modal
    function showValidationError(message) {
        $('#validationErrorMessage').html(`
            <div class="text-center">
                <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                <p class="mt-3">${message}</p>
            </div>
        `);
        $('#validationErrorModal').modal('show');
    }

    // Mostrar u ocultar el mensaje de advertencia según el checkbox
    $('#requirePhoto').on('change', function() {
        if ($(this).is(':checked')) {
            $('#photoWarning').removeClass('d-none');
        } else {
            $('#photoWarning').addClass('d-none');
        }
    });

    // Inicializar el mensaje de advertencia
    if ($('#requirePhoto').is(':checked')) {
        $('#photoWarning').removeClass('d-none');
    }

    // Función para generar nombre de foto
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

    // Función para actualizar el nombre de la foto
    function updatePhotoName() {
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
    // Inicializar el nombre de la foto
    updatePhotoName();

    // Función para iniciar la cámara
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

    // Función para detener la cámara
    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    }

    // Función para tomar foto
    function takePhoto(videoElement, canvasElement) {
    if (!stream) {
        showAlert('La cámara no está activa', 'danger');
        return;
    }

    canvasElement.width = videoElement.videoWidth;
    canvasElement.height = videoElement.videoHeight;
    const ctx = canvasElement.getContext('2d');
    ctx.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);

    // Generar nombre de la foto
    const photoName = updatePhotoName();
    $('#photo').val(photoName); // Actualizar el campo con el nombre de la foto
    hasRealPhoto = true; // Marcar que hay una foto real

    // Guardar el blob localmente
    canvasElement.toBlob(function(blob) {
        photoBlob = blob;
    }, 'image/png');

    // Mostrar vista previa de la foto
    const imageData = canvasElement.toDataURL('image/png');
    $('#photoPreview').html(`
        <img src="${imageData}" class="img-thumbnail" style="max-height: 200px;">
    `);

    // Detener la cámara
    stopCamera();

    // Mostrar botones de acción
    $('#btnTakePhoto, #takeModalPhoto').addClass('d-none');
    $('#btnCancelPhoto, #btnRetakePhoto, #btnSubirFoto, #btnDownloadPhoto').removeClass('d-none');
    photoUploaded = false;
}

    // Activar cámara
    $('#btnActivarCamara').click(function() {
        if (/Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
            $('#cameraModal').modal('show');
            startCamera(document.getElementById('modalWebcam'), document.getElementById('modalCanvas'));
        } else {
            $('#cameraPreview').removeClass('d-none');
            startCamera(document.getElementById('webcam'), document.getElementById('canvas'));
            $('#btnTakePhoto').removeClass('d-none');
            $('#btnCancelPhoto, #btnRetakePhoto, #btnSubirFoto').addClass('d-none');
        }
    });

    // Tomar foto (desde la página principal)
    $('#btnTakePhoto').click(function() {
        takePhoto(
            document.getElementById('webcam'),
            document.getElementById('canvas')
        );
    });

    // Tomar foto (desde el modal)
    $('#takeModalPhoto').click(function() {
        takePhoto(
            document.getElementById('modalWebcam'),
            document.getElementById('modalCanvas')
        );
        $('#cameraModal').modal('hide');
    });

    // Cancelar foto
    $('#btnCancelPhoto').click(function() {
        stopCamera();
        $('#cameraPreview').addClass('d-none');
        $('#btnTakePhoto').addClass('d-none');
        $('#btnCancelPhoto, #btnRetakePhoto, #btnSubirFoto, #btnDownloadPhoto').addClass('d-none');
        $('#photoPreview').html(`
            <div class="text-center p-4 bg-light rounded">
                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2">No hay foto seleccionada</p>
            </div>
        `);
        $('#photo').val('Sin Foto'); // Reiniciar el campo
        photoBlob = null; // Limpiar el blob
        hasRealPhoto = false; // Reiniciar el estado
            photoUploaded = false;
        });

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
    // No reiniciar photoBlob ni #photo aquí, porque la foto actual sigue siendo válida hasta que se tome una nueva
});

    // Subir foto al servidor
$('#btnSubirFoto').click(async function() {
    if (!photoBlob) {
        showAlert('No hay foto para subir', 'danger');
        return;
    }

    try {
        const photoPath = await uploadPhoto();
        $('#uploadStatus').html(`
            <div class="alert alert-success mb-0 p-2">
                <i class="bi bi-check-circle-fill"></i> Foto subida correctamente al servidor
            </div>
        `);
        photoUploaded = true;
        // Asegurar que el campo #photo tenga el valor correcto
        $('#photo').val(photoPath);
    } catch (error) {
        $('#uploadStatus').html(`
            <div class="alert alert-danger mb-0 p-2">
                <i class="bi bi-x-circle-fill"></i> Error: ${error.message}
            </div>
        `);
    }
});

    // Evento para actualizar el nombre de la foto cuando cambia la fecha
    $('#measured_at').change(function() {
        updatePhotoName();
    });

    // Cerrar modal de cámara
    $('#cameraModal').on('hidden.bs.modal', function() {
        stopCamera();
    });

    // Botón "Guardar y Finalizar" (solo se muestra si no hay más sensores marcados)
$('#saveAndFinish').on('click', async function() {
    // Validar que el campo "valor" no esté vacío
    if (!$('#value').val()) {
        showValidationError('El campo <strong>Valor</strong> es obligatorio.');
        return;
    }

    // Validar foto obligatoria
    const requirePhoto = $('#requirePhoto').is(':checked');
    const hasPhoto = photoBlob !== null || $('#photo').val() !== 'Sin Foto';

    if (requirePhoto && !hasPhoto) {
        showValidationError('La <strong>foto es obligatoria</strong>. Por favor, activa la cámara y toma una foto antes de continuar.');
        return;
    }

    // Si hay una foto en photoBlob que no ha sido subida, subirla primero
    if (photoBlob && !photoUploaded) {
        try {
            const photoPath = await uploadPhoto();
            $('#photo').val(photoPath);
            photoUploaded = true;
        } catch (error) {
            showValidationError('Error al subir la foto: ' + error.message);
            return;
        }
    }

    // Preparar los datos para la API
    const formData = {
        sensor_id: sensorId,
        data: {
            tipo: '{{ $sensor->group->template->type ?? "personalizado" }}',
            valor: $('#value').val(),
            foto: $('#photo').val(),
            campos_personalizados: {}
        },
        measured_at: $('#measured_at').val()
    };

    // Agregar campos personalizados
    $('[name^="data[campos_personalizados]"]').each(function() {
        const name = $(this).attr('name').match(/data\[campos_personalizados\]\[(.*?)\]/)[1];
        formData.data.campos_personalizados[name] = $(this).val();
    });

    // Enviar a la API
    $.ajax({
        url: '/api/bulk/measurements/store',
        type: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        data: JSON.stringify(formData),
        beforeSend: function() {
            $('#saveAndFinish').prop('disabled', true).html(`
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Guardando...
            `);
        },
        success: function(response) {
            if (response.success) {
                showAlert('Medición guardada correctamente. ¡Todas las mediciones masivas se han completado!', 'success');
                setTimeout(() => {
                    window.location.href = '/mediciones/select-sensor';
                }, 2000);
            } else {
                showValidationError(response.message || 'Error al guardar la medición.');
                $('#saveAndFinish').prop('disabled', false).html(`
                    <i class="bi bi-check-circle me-2"></i> Guardar y Finalizar
                `);
            }
        },
        error: function(xhr) {
            const errorResponse = xhr.responseJSON;
            if (errorResponse && errorResponse.message) {
                showValidationError(errorResponse.message, errorResponse.details);
            } else {
                showValidationError('Error al guardar la medición: ' + (xhr.responseJSON?.message || xhr.statusText));
            }
            $('#saveAndFinish').prop('disabled', false).html(`
                <i class="bi bi-check-circle me-2"></i> Guardar y Finalizar
            `);
        }
    });
});
    // Función para subir foto al servidor
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

        $('#uploadStatus').html(`
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Subiendo...</span>
            </div> Subiendo foto...
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
                        <div class="alert alert-success mb-0 p-2">
                            <i class="bi bi-check-circle-fill"></i> Foto subida correctamente
                        </div>
                    `);
                    // Actualizar el campo #photo con la ruta del servidor
                    $('#photo').val(response.path);
                    resolve(response.path);
                } else {
                    $('#uploadStatus').html(`
                        <div class="alert alert-danger mb-0 p-2">
                            <i class="bi bi-x-circle-fill"></i> Error: ${response.message || 'Error al subir foto'}
                        </div>
                    `);
                    reject(new Error(response.message || 'Error al subir foto'));
                }
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON?.message || xhr.statusText;
                $('#uploadStatus').html(`
                    <div class="alert alert-danger mb-0 p-2">
                        <i class="bi bi-x-circle-fill"></i> Error: ${errorMessage}
                    </div>
                `);
                reject(new Error(errorMessage));
            }
        });
    });
}

    // Función para mostrar errores en modal con detalles
function showValidationError(message, details = null) {
    let errorHtml = `
        <div class="text-center">
            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
            <h5 class="mt-3 text-danger">${message}</h5>
    `;

    if (details) {
        errorHtml += `
            <div class="alert alert-light mt-3 text-start">
                <strong>Detalles:</strong>
                <ul class="mb-0 mt-2">
        `;

        if (details.lastMeasurement) {
            errorHtml += `
                <li><strong>Última medición:</strong> ${details.lastMeasurement.date} con valor ${details.lastMeasurement.value}</li>
            `;
        }

        if (details.nextMeasurement) {
            errorHtml += `
                <li><strong>Próxima medición:</strong> ${details.nextMeasurement.date} con valor ${details.nextMeasurement.value}</li>
            `;
        }

        if (details.currentMeasurement) {
            errorHtml += `
                <li><strong>Medición actual:</strong> ${details.currentMeasurement.date} con valor ${details.currentMeasurement.value}</li>
            `;
        }

        errorHtml += `
                </ul>
                <p class="mt-2 text-muted"><small>Para mediciones intermedias, el valor debe ser mayor que el de la medición anterior y menor que el de la siguiente.</small></p>
            </div>
        `;
    }

    errorHtml += `</div>`;
    $('#validationErrorMessage').html(errorHtml);
    $('#validationErrorModal').modal('show');
}

    // En el botón "Continuar con el Siguiente Sensor"
$('#continueToNextSensor').on('click', async function() {
    // Validar que el campo "valor" no esté vacío
    if (!$('#value').val()) {
        showValidationError('El campo <strong>Valor</strong> es obligatorio.');
        return;
    }

    // Validar foto obligatoria
    const requirePhoto = $('#requirePhoto').is(':checked');
    const hasPhoto = photoBlob !== null || $('#photo').val() !== 'Sin Foto';

    if (requirePhoto && !hasPhoto) {
        showValidationError('La <strong>foto es obligatoria</strong>. Por favor, activa la cámara y toma una foto antes de continuar.');
        return;
    }

    // Si hay una foto en photoBlob que no ha sido subida, subirla primero
    if (photoBlob && !photoUploaded) {
        try {
            const photoPath = await uploadPhoto();
            $('#photo').val(photoPath);
            photoUploaded = true;
        } catch (error) {
            showValidationError('Error al subir la foto: ' + error.message);
            return;
        }
    }

    // Preparar los datos para la API
    const formData = {
        sensor_id: sensorId,
        data: {
            tipo: '{{ $sensor->group->template->type ?? "personalizado" }}',
            valor: $('#value').val(),
            foto: $('#photo').val(),
            campos_personalizados: {}
        },
        measured_at: $('#measured_at').val()
    };

    // Agregar campos personalizados
    $('[name^="data[campos_personalizados]"]').each(function() {
        const name = $(this).attr('name').match(/data\[campos_personalizados\]\[(.*?)\]/)[1];
        formData.data.campos_personalizados[name] = $(this).val();
    });

    // Enviar a la API
    $.ajax({
        url: '/api/bulk/measurements/store',
        type: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        data: JSON.stringify(formData),
        beforeSend: function() {
            $('#continueToNextSensor').prop('disabled', true).html(`
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Guardando y continuando...
            `);
        },
        success: function(response) {
            if (response.success) {
                // ✅ La URL del siguiente sensor viene directamente en la respuesta
                if (response.data.has_next_sensor && response.data.next_sensor_url) {
                    window.location.href = response.data.next_sensor_url;
                } else {
                    showAlert('¡Todas las mediciones masivas se han completado!', 'success');
                    setTimeout(() => {
                        window.location.href = '/mediciones/select-sensor';
                    }, 2000);
                }
            } else {
                showValidationError(response.message || 'Error al guardar la medición.');
                $('#continueToNextSensor').prop('disabled', false).html(`
                    <i class="bi bi-arrow-right me-2"></i> Continuar con el Siguiente Sensor
                `);
            }
        },
        error: function(xhr) {
            const errorResponse = xhr.responseJSON;
            if (errorResponse && errorResponse.message) {
                showValidationError(errorResponse.message, errorResponse.details);
            } else {
                showValidationError('Error al guardar la medición: ' + (xhr.responseJSON?.message || xhr.statusText));
            }
            $('#continueToNextSensor').prop('disabled', false).html(`
                <i class="bi bi-arrow-right me-2"></i> Continuar con el Siguiente Sensor
            `);
        }
    });
});
});
</script>
@endpush