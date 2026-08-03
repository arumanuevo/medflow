@extends('layouts.modern')

@section('title', 'Editar Medición')

<!-- Incluir el archivo CSS externo -->
<link rel="stylesheet" href="{{ asset('css/edit-measurement-styles.css') }}">

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4><i class="bi bi-pencil-square"></i> Editar Medición</h4>
                    <a href="{{ route('measurements.index') }}" class="btn btn-light">
                        <i class="bi bi-arrow-left"></i> Volver al Listado
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

                    <form id="editMeasurementForm">
                        <input type="hidden" name="measurement_id" value="{{ $measurement->id }}">
                        <input type="hidden" id="groupName" value="{{ str_replace(' ', '_', $measurement->sensor->group->name ?? 'SinGrupo') }}">
                        <input type="hidden" id="sensorId" value="{{ $measurement->sensor->id }}">

                        <!-- Información de la medición -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h5><i class="bi bi-info-circle"></i> Información de la Medición</h5>
                                    </div>
                                    <div class="card-body">
                                        <dl class="row">
                                            <dt class="col-sm-4">Sensor:</dt>
                                            <dd class="col-sm-8">{{ $measurement->sensor->name }} ({{ $measurement->sensor->identifier }})</dd>
                                            <dt class="col-sm-4">Grupo:</dt>
                                            <dd class="col-sm-8">{{ $measurement->sensor->group->name ?? 'Sin grupo' }}</dd>
                                            <dt class="col-sm-4">Fecha Original:</dt>
                                            <dd class="col-sm-8">{{ $measurement->measured_at->format('d/m/Y H:i') }}</dd>
                                            <dt class="col-sm-4">Valor Original:</dt>
                                            <dd class="col-sm-8">{{ $measurement->data['valor'] ?? 'N/A' }} {{ $measurement->sensor->group->template->schema['campos'][0]['unidad'] ?? '' }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h5><i class="bi bi-image"></i> Foto Actual</h5>
                                    </div>
                                    <div class="card-body">
                                        @if(isset($measurement->data['foto']) && $measurement->data['foto'] !== 'Sin Foto')
                                            <div class="text-center">
                                                <img src="/{{ $measurement->data['foto'] }}"
                                                     alt="Foto actual de la medición"
                                                     class="img-fluid rounded"
                                                     style="max-height: 200px;">
                                                <div class="mt-2">
                                                    <small class="text-muted">Foto actual: {{ $measurement->data['foto'] }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center text-muted">
                                                <i class="bi bi-image" style="font-size: 3rem;"></i>
                                                <p>No hay foto asociada a esta medición</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Valor de la medición -->
                        <div class="mb-3">
                            <label for="value" class="form-label">Valor *</label>
                            <input type="number" step="0.01" class="form-control" id="value" name="value"
                                   value="{{ $measurement->data['valor'] ?? '' }}" required>
                            @if(isset($measurement->sensor->group) && isset($measurement->sensor->group->template) && isset($measurement->sensor->group->template->schema['campos']))
                                @foreach($measurement->sensor->group->template->schema['campos'] as $campo)
                                    @if($campo['nombre'] === 'valor' && isset($campo['unidad']))
                                        <small class="text-muted">Unidad: {{ $campo['unidad'] }}</small>
                                    @endif
                                @endforeach
                            @endif
                        </div>

                        <!-- Fecha de medición -->
                        <div class="mb-3">
                            <label for="measured_at" class="form-label">Fecha de Medición *</label>
                            <input type="datetime-local" class="form-control" id="measured_at" name="measured_at"
                                   value="{{ old('measured_at', $measurement->measured_at->format('Y-m-d\TH:i')) }}" required>
                        </div>

                        <!-- Campo de solo lectura -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-calendar-range me-2"></i> Período de Medición
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{ $measurement->periodo_medicion ?? $defaultPeriod }} días" readonly>
                                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="tooltip" title="Este valor se tomó de tu configuración global en el dashboard al crear la medición.">
                                    <i class="bi bi-info-circle"></i>
                                </button>
                            </div>
                            <small class="text-muted">
                                Esta medición usa un período de:
                                <strong>{{ $measurement->periodo_medicion ?? $defaultPeriod }} días</strong>
                                (configurado en tu dashboard al momento de crearla).
                                <br>
                                <strong>Nota:</strong> Para cambiar el valor por defecto para futuras mediciones, dirígete a:
                                <a href="{{ route('dashboard') }}" target="_blank">
                                    <i class="bi bi-gear me-1"></i> Configuración Global
                                </a>.
                            </small>
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
                                            <div id="photoPreviewContainer" class="mb-3">
                                                @if(isset($measurement->data['foto']) && $measurement->data['foto'] !== 'Sin Foto')
                                                    <img src="/{{ $measurement->data['foto'] }}" id="photoPreview" class="img-thumbnail" style="max-height: 200px;">
                                                    <input type="hidden" id="currentPhoto" value="{{ $measurement->data['foto'] }}">
                                                @else
                                                    <div class="text-center p-4 bg-light rounded">
                                                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                                        <p class="text-muted mt-2">No hay foto seleccionada</p>
                                                    </div>
                                                    <input type="hidden" id="currentPhoto" value="Sin Foto">
                                                @endif
                                            </div>
                                            <div id="photoNameDisplay"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="photo" class="form-label">Nombre de la Foto</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="photo" name="photo"
                                                           value="{{ $measurement->data['foto'] ?? 'Sin Foto' }}" readonly>
                                                    <button type="button" class="btn btn-primary btn-camera" id="btnActivarCamara">
                                                        <i class="bi bi-camera"></i> Activar Cámara
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="btn-group-camera">
                                                <button type="button" class="btn btn-success btn-camera d-none" id="btnTakePhoto">
                                                    <i class="bi bi-camera"></i> Tomar Foto
                                                </button>
                                                <button type="button" class="btn btn-danger btn-camera d-none" id="btnCancelPhoto">
                                                    <i class="bi bi-x-circle"></i> Cancelar
                                                </button>
                                                <button type="button" class="btn btn-info btn-camera d-none" id="btnAcceptPhoto">
                                                    <i class="bi bi-check-circle"></i> Aceptar Foto
                                                </button>
                                                <button type="button" class="btn btn-secondary btn-camera d-none" id="btnRetakePhoto">
                                                    <i class="bi bi-arrow-clockwise"></i> Volver a Tomar
                                                </button>
                                                <button type="button" class="btn btn-warning btn-camera d-none" id="btnDownloadPhoto">
                                                    <i class="bi bi-download"></i> Descargar
                                                </button>
                                                <button type="button" class="btn btn-primary btn-camera d-none" id="btnUploadPhoto">
                                                    <i class="bi bi-cloud-arrow-up"></i> Subir al Servidor
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Campos personalizados -->
                        @if(isset($measurement->sensor->group) && isset($measurement->sensor->group->template) && isset($measurement->sensor->group->template->schema['campos']))
                            <div class="mb-4">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h5><i class="bi bi-file-earmark-text"></i> Campos Personalizados</h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach($measurement->sensor->group->template->schema['campos'] as $campo)
                                            @if($campo['nombre'] !== 'valor' && $campo['nombre'] !== 'tipo')
                                                <div class="mb-3">
                                                    <label for="{{ $campo['nombre'] }}" class="form-label">
                                                        {{ $campo['nombre'] }}
                                                        @if($campo['requerido']) <span class="text-danger">*</span> @endif
                                                    </label>
                                                    @if($campo['tipo'] === 'numero')
                                                        <input type="number" step="0.01" class="form-control"
                                                               id="{{ $campo['nombre'] }}" name="custom_fields[{{ $campo['nombre'] }}]"
                                                               value="{{ old($campo['nombre'], $measurement->data['campos_personalizados'][$campo['nombre']] ?? '') }}">
                                                    @elseif($campo['tipo'] === 'texto')
                                                        <input type="text" class="form-control"
                                                               id="{{ $campo['nombre'] }}" name="custom_fields[{{ $campo['nombre'] }}]"
                                                               value="{{ old($campo['nombre'], $measurement->data['campos_personalizados'][$campo['nombre']] ?? '') }}">
                                                    @elseif($campo['tipo'] === 'fecha')
                                                        <input type="date" class="form-control"
                                                               id="{{ $campo['nombre'] }}" name="custom_fields[{{ $campo['nombre'] }}]"
                                                               value="{{ old($campo['nombre'], $measurement->data['campos_personalizados'][$campo['nombre']] ?? '') }}">
                                                    @elseif($campo['tipo'] === 'booleano')
                                                        <select class="form-select" id="{{ $campo['nombre'] }}"
                                                                name="custom_fields[{{ $campo['nombre'] }}]">
                                                            <option value="1" {{ (old($campo['nombre'], $measurement->data['campos_personalizados'][$campo['nombre']] ?? '') == '1') ? 'selected' : '' }}>Sí</option>
                                                            <option value="0" {{ (old($campo['nombre'], $measurement->data['campos_personalizados'][$campo['nombre']] ?? '') == '0') ? 'selected' : '' }}>No</option>
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
                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('measurements.index') }}" class="btn btn-secondary me-2">
                                <i class="bi bi-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="saveMeasurementBtn">
                                <i class="bi bi-check-circle"></i> Guardar Cambios
                            </button>
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
    const sensorId = {{ $measurement->sensor->id }};
    const groupName = $('#groupName').val(); // Nombre del grupo sin espacios
    let stream = null;
    let currentCanvas = null;
    let photoBlob = null;
    let currentPhoto = $('#currentPhoto').val(); // Foto actual (nombre original)
    let originalPhotoName = currentPhoto !== 'Sin Foto' ? currentPhoto : null; // Guardar el nombre original
    let photoUploaded = false; // Bandera para saber si la foto ya fue subida

    // Función para generar nombre de foto usando la fecha de medición
    function generatePhotoName() {
        const measuredAtInput = $('#measured_at').val();
        let date, time;

        if (measuredAtInput) {
            // Usar la fecha de medición del formulario
            const dateObj = new Date(measuredAtInput);
            date = dateObj.toISOString().slice(0, 10).replace(/-/g, '');
            time = dateObj.toTimeString().slice(0, 5).replace(/:/g, '');
        } else {
            // Usar la fecha actual si no hay fecha de medición
            const now = new Date();
            date = now.toISOString().slice(0, 10).replace(/-/g, '');
            time = now.toTimeString().slice(0, 5).replace(/:/g, '');
        }

        return `${groupName}_${sensorId}_${date}_${time}.png`;
    }

    // Función para actualizar el nombre de la foto
    function updatePhotoName() {
        // Si hay un nombre original (foto existente), no cambiarlo
        if (originalPhotoName) {
            return;
        }

        // Si no hay nombre original, generar uno nuevo con la fecha de medición
        const photoName = generatePhotoName();
        $('#photo').val(photoName);
        $('#photoNameDisplay').html(`Nombre de la foto: <strong>${photoName}</strong>`);
    }

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
            currentCanvas = canvasElement;
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
            alert('La cámara no está activa');
            return;
        }

        // Configurar dimensiones del canvas
        canvasElement.width = videoElement.videoWidth;
        canvasElement.height = videoElement.videoHeight;
        const ctx = canvasElement.getContext('2d');
        ctx.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);

        // Generar nombre de la foto usando la fecha de medición actual
        const photoName = generatePhotoName();
        $('#photo').val(photoName);
        $('#photoNameDisplay').html(`Nombre de la foto: <strong>${photoName}</strong>`);

        // Guardar el blob localmente
        canvasElement.toBlob(function(blob) {
            photoBlob = blob;
        }, 'image/png');

        // Mostrar vista previa de la foto
        const imageData = canvasElement.toDataURL('image/png');
        $('#photoPreviewContainer').html(`
            <img src="${imageData}" id="photoPreview" class="img-thumbnail" style="max-height: 200px;">
        `);

        // Mostrar botones de acción
        $('#btnTakePhoto, #takeModalPhoto').addClass('d-none');
        $('#btnCancelPhoto, #btnAcceptPhoto, #btnRetakePhoto, #btnDownloadPhoto, #btnUploadPhoto').removeClass('d-none');
        photoUploaded = false;
        originalPhotoName = null; // Reiniciar el nombre original al tomar una nueva foto
    }

    // Activar cámara (desde el botón principal)
    $('#btnActivarCamara').click(function() {
        if (/Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
            $('#cameraModal').modal('show');
            startCamera(document.getElementById('modalWebcam'), document.getElementById('modalCanvas'));
        } else {
            $('#cameraPreview').removeClass('d-none');
            startCamera(document.getElementById('webcam'), document.getElementById('canvas'));
            $('#btnTakePhoto').removeClass('d-none');
            $('#btnCancelPhoto, #btnAcceptPhoto, #btnRetakePhoto, #btnDownloadPhoto, #btnUploadPhoto').addClass('d-none');
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

    // Aceptar foto
    $('#btnAcceptPhoto').click(function() {
        stopCamera();
        $('#cameraPreview').addClass('d-none');
        $('#btnTakePhoto, #btnCancelPhoto, #btnAcceptPhoto, #btnRetakePhoto').addClass('d-none');
    });

    // Cancelar foto
    $('#btnCancelPhoto').click(function() {
        stopCamera();
        $('#cameraPreview').addClass('d-none');
        $('#btnTakePhoto, #btnCancelPhoto, #btnAcceptPhoto, #btnRetakePhoto, #btnDownloadPhoto, #btnUploadPhoto').addClass('d-none');

        // Restaurar la foto anterior si existe
        if (currentPhoto !== 'Sin Foto') {
            $('#photoPreviewContainer').html(`
                <img src="/${currentPhoto}" id="photoPreview" class="img-thumbnail" style="max-height: 200px;">
            `);
            $('#photo').val(currentPhoto);
            $('#photoNameDisplay').html(`Nombre de la foto: <strong>${currentPhoto}</strong>`);
            originalPhotoName = currentPhoto; // Restaurar el nombre original
        } else {
            $('#photoPreviewContainer').html(`
                <div class="text-center p-4 bg-light rounded">
                    <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No hay foto seleccionada</p>
                </div>
            `);
            $('#photo').val('Sin Foto');
            $('#photoNameDisplay').html('');
            originalPhotoName = null;
        }
        photoBlob = null;
        photoUploaded = false;
    });

    // Volver a tomar foto
    $('#btnRetakePhoto').click(function() {
        if (/Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
            $('#cameraModal').modal('show');
            startCamera(document.getElementById('modalWebcam'), document.getElementById('modalCanvas'));
        } else {
            $('#cameraPreview').removeClass('d-none');
            startCamera(document.getElementById('webcam'), document.getElementById('canvas'));
            $('#btnTakePhoto').removeClass('d-none');
            $('#btnCancelPhoto, #btnAcceptPhoto, #btnRetakePhoto, #btnDownloadPhoto, #btnUploadPhoto').addClass('d-none');
        }
        photoUploaded = false;
    });

    // Descargar foto en el dispositivo
    $('#btnDownloadPhoto').click(function() {
        if (photoBlob) {
            const photoName = $('#photo').val();
            const url = URL.createObjectURL(photoBlob);
            const a = document.createElement('a');
            a.href = url;
            a.download = photoName;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            alert(`Foto guardada en tu dispositivo como ${photoName}`);
        } else if (currentPhoto !== 'Sin Foto') {
            const a = document.createElement('a');
            a.href = `/${currentPhoto}`;
            a.download = currentPhoto.split('/').pop();
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            alert(`Foto guardada en tu dispositivo como ${currentPhoto.split('/').pop()}`);
        } else {
            alert('No hay foto para descargar');
        }
    });

    // Subir foto al servidor
    $('#btnUploadPhoto').click(function() {
        if (!photoBlob) {
            alert('No hay foto para subir');
            return;
        }

        uploadPhoto().then(() => {
            alert('Foto subida correctamente al servidor');
        }).catch(() => {
            // El error ya se muestra en la función uploadPhoto
        });
    });

    // Evento para actualizar el nombre de la foto cuando cambia la fecha de medición
    $('#measured_at').change(function() {
        // Solo actualizar el nombre si no hay una foto existente
        if (!originalPhotoName) {
            updatePhotoName();
        }
    });

    // Cerrar modal de cámara
    $('#cameraModal').on('hidden.bs.modal', function() {
        stopCamera();
    });

    // Validar formulario antes de enviar
    $('#editMeasurementForm').submit(async function(e) {
        e.preventDefault();

        // Verificar si la foto es obligatoria
        const requirePhoto = $('#requirePhoto').is(':checked');
        const hasPhoto = $('#photo').val() !== 'Sin Foto' && $('#photo').val() !== '';

        if (requirePhoto && !hasPhoto) {
            alert('La foto es obligatoria. Activa la cámara y toma una foto.');
            return false;
        }

        // Validar que el campo "valor" no esté vacío
        if (!$('#value').val()) {
            alert('El campo Valor es obligatorio');
            return false;
        }

        // Si hay una nueva foto que no ha sido subida, subirla primero
        if (photoBlob && !photoUploaded) {
            try {
                await uploadPhoto();
            } catch (error) {
                return false;
            }
        }

        // Generar el nombre de la foto final usando la fecha de medición
        if (!originalPhotoName) {
            // Si no hay nombre original, generar uno nuevo con la fecha de medición final
            const photoName = generatePhotoName();
            $('#photo').val(photoName);
        }

        // Preparar los datos para la API
        const formData = {
            measurement_id: {{ $measurement->id }},
            sensor_id: sensorId,
            data: {
                tipo: '{{ $measurement->sensor->group->template->type ?? "personalizado" }}',
                valor: $('#value').val(),
                foto: $('#photo').val(), // Incluir el nombre de la foto en data
                campos_personalizados: {}
            },
            measured_at: $('#measured_at').val()
        };

        // Agregar campos personalizados
        $('[name^="custom_fields["]').each(function() {
            const name = $(this).attr('name').match(/custom_fields\[(.*?)\]/)[1];
            formData.data.campos_personalizados[name] = $(this).val();
        });

        // Enviar el formulario
        sendFormData(formData);
        return false;
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
                beforeSend: function() {
                    $('#saveMeasurementBtn, #btnUploadPhoto').prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        $('#photo').val(response.path);
                        currentPhoto = response.path;
                        originalPhotoName = response.path;
                        photoUploaded = true;
                        $('#saveMeasurementBtn, #btnUploadPhoto').prop('disabled', false);
                        resolve();
                    } else {
                        alert('Error al subir foto: ' + (response.message || 'Error desconocido'));
                        $('#saveMeasurementBtn, #btnUploadPhoto').prop('disabled', false);
                        reject(new Error('Error al subir foto'));
                    }
                },
                error: function(xhr) {
                    const errorMessage = xhr.responseJSON?.message || xhr.statusText;
                    alert('Error al subir foto: ' + errorMessage);
                    $('#saveMeasurementBtn, #btnUploadPhoto').prop('disabled', false);
                    reject(new Error(errorMessage));
                }
            });
        });
    }

    // Función para enviar los datos del formulario a la API
    function sendFormData(formData) {
        $.ajax({
            url: '/api/measurements/' + formData.measurement_id,
            type: 'PUT',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(formData),
            beforeSend: function() {
                $('#saveMeasurementBtn').prop('disabled', true).html(`
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Guardando...
                `);
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = '{{ route("measurements.index") }}';
                } else {
                    alert('Error: ' + (response.message || 'No se pudo guardar la medición'));
                    $('#saveMeasurementBtn').prop('disabled', false).html(`
                        <i class="bi bi-check-circle"></i> Guardar Cambios
                    `);
                }
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON?.message || xhr.statusText;
                alert('Error: ' + errorMessage);
                $('#saveMeasurementBtn').prop('disabled', false).html(`
                    <i class="bi bi-check-circle"></i> Guardar Cambios
                `);
            }
        });
    }

    // Inicializar el nombre de la foto al cargar la página
    updatePhotoName();
});
</script>
@endpush