@extends('layouts.modern')

@section('title', 'Editar Medición')

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
                        @csrf
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
                                            @php
                                                // Obtener el nombre del campo principal de la plantilla
                                                $mainField = null;
                                                $mainFieldUnit = '';
                                                if(isset($measurement->sensor->group->template->schema['campos'])) {
                                                    foreach($measurement->sensor->group->template->schema['campos'] as $campo) {
                                                        if($campo['nombre'] !== 'foto' && $campo['nombre'] !== 'fecha_medicion' && $campo['nombre'] !== 'tipo') {
                                                            $mainField = $campo['nombre'];
                                                            $mainFieldUnit = $campo['unidad'] ?? '';
                                                            break;
                                                        }
                                                    }
                                                }
                                                $originalValue = $mainField ? ($measurement->data[$mainField] ?? 'N/A') : 'N/A';
                                            @endphp
                                            <dt class="col-sm-4">Valor Original:</dt>
                                            <dd class="col-sm-8">{{ $originalValue }} {{ $mainFieldUnit }}</dd>
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

                        <!-- Campo principal de la plantilla -->
                        @php
                            $mainField = null;
                            $mainFieldType = 'numero';
                            $mainFieldUnit = '';
                            $mainFieldLabel = 'Valor';
                            
                            if(isset($measurement->sensor->group->template->schema['campos'])) {
                                foreach($measurement->sensor->group->template->schema['campos'] as $campo) {
                                    if($campo['nombre'] !== 'foto' && $campo['nombre'] !== 'fecha_medicion' && $campo['nombre'] !== 'tipo') {
                                        $mainField = $campo['nombre'];
                                        $mainFieldType = $campo['tipo'] ?? 'numero';
                                        $mainFieldUnit = $campo['unidad'] ?? '';
                                        $mainFieldLabel = ucfirst(str_replace('_', ' ', $campo['nombre']));
                                        break;
                                    }
                                }
                            }
                            
                            // Si no se encontró un campo principal, usar 'valor' como fallback
                            if(!$mainField) {
                                $mainField = 'valor';
                                $mainFieldLabel = 'Valor';
                            }
                        @endphp

                        @if($mainField)
                        <div class="mb-3">
                            <label for="{{ $mainField }}" class="form-label">
                                <i class="bi bi-speedometer2 me-1 text-primary"></i>
                                {{ $mainFieldLabel }} <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="{{ $mainField }}" 
                                       name="data[{{ $mainField }}]" 
                                       value="{{ old($mainField, $measurement->data[$mainField] ?? '') }}" required autofocus>
                                @if($mainFieldUnit)
                                    <span class="input-group-text">{{ $mainFieldUnit }}</span>
                                @endif
                            </div>
                            @if($originalValue && $originalValue !== 'N/A')
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Valor original: <strong>{{ number_format((float)$originalValue, 2) }} {{ $mainFieldUnit }}</strong>
                                </small>
                            @endif
                        </div>
                        @endif

                        <!-- Fecha de medición -->
                        <div class="mb-3">
                            <label for="fecha_medicion" class="form-label">
                                <i class="bi bi-calendar3 me-1 text-primary"></i>
                                Fecha de Medición <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local" class="form-control" id="fecha_medicion" 
                                   name="data[fecha_medicion]" 
                                   value="{{ old('fecha_medicion', $measurement->data['fecha_medicion'] ?? $measurement->measured_at->format('Y-m-d\TH:i')) }}" required>
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Fecha y hora en que se tomó la medición.
                            </small>
                        </div>

                        <!-- Campo de solo lectura - Período de medición -->
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

                        <!-- Foto -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label">
                                    <i class="bi bi-camera me-1 text-primary"></i>
                                    Foto
                                </label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="photoToggle" 
                                           {{ (isset($measurement->data['foto']) && $measurement->data['foto'] !== 'Sin Foto') ? 'checked' : 'checked' }}>
                                    <label class="form-check-label small" for="photoToggle">
                                        <span class="text-success">Habilitado</span>
                                        <span class="text-muted">Deshabilitado</span>
                                    </label>
                                </div>
                            </div>
                            <div id="photoSection">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-7">
                                        <div class="photo-preview-box" id="photoPreviewBox">
                                            <div class="placeholder" id="photoPlaceholder">
                                                <i class="bi bi-camera"></i>
                                                <span>Tomar foto</span>
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
                                            
                                            {{-- Nombre de la foto --}}
                                            <div class="photo-name-display mt-2">
                                                <small class="text-muted">
                                                    <i class="bi bi-tag me-1"></i>
                                                    <span id="photoNameDisplay">
                                                        @if(isset($measurement->data['foto']) && $measurement->data['foto'] !== 'Sin Foto')
                                                            {{ $measurement->data['foto'] }}
                                                        @else
                                                            Sin foto
                                                        @endif
                                                    </span>
                                                </small>
                                            </div>
                                            
                                            <input type="hidden" id="photo" name="data[foto]" 
                                                   value="{{ $measurement->data['foto'] ?? 'Sin Foto' }}">
                                            
                                            <small class="text-muted text-center mt-1">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Formato: <code>(grupo)_(sensor)_(fecha).png</code>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Campos adicionales (personalizados) -->
                        @php
                            $customFields = [];
                            if(isset($measurement->sensor->group->template->schema['campos'])) {
                                $customFields = array_filter($measurement->sensor->group->template->schema['campos'], function($campo) {
                                    // Excluir campos que ya tenemos en el formulario
                                    return !in_array($campo['nombre'], [$mainField, 'foto', 'fecha_medicion', 'tipo']);
                                });
                            }
                        @endphp

                        @if(count($customFields) > 0)
                            <hr>
                            <h6 class="mb-3">
                                <i class="bi bi-file-earmark-text me-1 text-secondary"></i>
                                Campos adicionales
                            </h6>
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
                                                   placeholder="Ingresa {{ $campo['nombre'] }}"
                                                   value="{{ old('campos_personalizados.'.$campo['nombre'], $measurement->data['campos_personalizados'][$campo['nombre']] ?? '') }}">
                                        @elseif($campo['tipo'] === 'texto')
                                            <input type="text" class="form-control"
                                                   id="field_{{ $campo['nombre'] }}" 
                                                   name="data[campos_personalizados][{{ $campo['nombre'] }}]"
                                                   placeholder="Ingresa {{ $campo['nombre'] }}"
                                                   value="{{ old('campos_personalizados.'.$campo['nombre'], $measurement->data['campos_personalizados'][$campo['nombre']] ?? '') }}">
                                        @elseif($campo['tipo'] === 'fecha')
                                            <input type="date" class="form-control"
                                                   id="field_{{ $campo['nombre'] }}" 
                                                   name="data[campos_personalizados][{{ $campo['nombre'] }}]"
                                                   value="{{ old('campos_personalizados.'.$campo['nombre'], $measurement->data['campos_personalizados'][$campo['nombre']] ?? '') }}">
                                        @elseif($campo['tipo'] === 'booleano')
                                            <select class="form-select" id="field_{{ $campo['nombre'] }}"
                                                    name="data[campos_personalizados][{{ $campo['nombre'] }}]">
                                                <option value="1" {{ (old('campos_personalizados.'.$campo['nombre'], $measurement->data['campos_personalizados'][$campo['nombre']] ?? '') == '1') ? 'selected' : '' }}>Sí</option>
                                                <option value="0" {{ (old('campos_personalizados.'.$campo['nombre'], $measurement->data['campos_personalizados'][$campo['nombre']] ?? '') == '0') ? 'selected' : '' }}>No</option>
                                            </select>
                                        @endif
                                        @if(isset($campo['unidad']) && $campo['unidad'])
                                            <small class="text-muted">Unidad: {{ $campo['unidad'] }}</small>
                                        @endif
                                    </div>
                                @endforeach
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
    const groupName = $('#groupName').val();
    
    // Estado de la cámara
    let hasRealPhoto = {{ (isset($measurement->data['foto']) && $measurement->data['foto'] !== 'Sin Foto') ? 'true' : 'false' }};
    let stream = null;
    let photoBlob = null;
    let photoUploaded = false;
    let photoEnabled = true;

    // Inicializar fecha actual
    const now = new Date();
    
    // ============================================
    // 2. VALIDACIÓN DE SECUENCIA (FECHAS Y VALORES)
    // =============================================
    function validateMeasurement(value, date) {
        // Por ahora, en edición solo validamos que el valor no esté vacío
        if (!value || value === '') {
            return { valid: false, message: 'El valor es obligatorio.' };
        }
        return { valid: true, message: 'Válido' };
    }

    // ============================================
    // 3. FUNCIONES DE LA CÁMARA
    // =============================================
    
    // Función para generar nombre de foto
    function generatePhotoName() {
        const fechaInput = $('#fecha_medicion').val();
        let date, time;

        if (fechaInput) {
            const dateObj = new Date(fechaInput);
            date = dateObj.toISOString().slice(0, 10).replace(/-/g, '');
            time = dateObj.toTimeString().slice(0, 5).replace(/:/g, '');
        } else {
            const now = new Date();
            date = now.toISOString().slice(0, 10).replace(/-/g, '');
            time = now.toTimeString().slice(0, 5).replace(/:/g, '');
        }

        return `${groupName}_${sensorId}_${date}_${time}.png`;
    }

    // Función para iniciar la cámara
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
    function takePhoto() {
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

        // Mostrar preview
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
    // 7. EVENTOS DE LA CÁMARA
    // =============================================
    $('#btnActivarCamara').click(function() {
        $('#cameraModal').modal('show');
        setTimeout(() => startCamera(), 300);
    });

    $('#takeModalPhoto').click(function() {
        takePhoto();
    });

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
        photoUploaded = false;
    });

    $('#fecha_medicion').change(function() {
        if (hasRealPhoto) {
            const photoName = generatePhotoName();
            $('#photo').val(photoName);
            $('#photoNameDisplay').text(photoName);
        }
    });

    // =============================================
    // 8. SUBIR FOTO AL SERVIDOR
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
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#photo').val(response.path);
                        $('#photoNameDisplay').text(response.path);
                        photoUploaded = true;
                        resolve();
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
    // 9. FUNCIÓN showAlert (para toasts)
    // =============================================
    function showAlert(message, type = 'danger') {
        const alertId = 'alert-' + Date.now();
        const icons = {
            success: 'bi-check-circle-fill',
            danger: 'bi-exclamation-triangle-fill',
            warning: 'bi-exclamation-triangle-fill',
            info: 'bi-info-circle-fill'
        };
        const icon = icons[type] || icons.danger;
        
        const html = `
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true" id="${alertId}">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi ${icon} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        $('#toastContainer').remove();
        $('body').append('<div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 11"></div>');
        $('#toastContainer').append(html);
        
        const toastElement = document.getElementById(alertId);
        const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 5000 });
        toast.show();
    }

    // =============================================
    // 10. TOGGLE DE FOTO
    // =============================================
    const photoToggle = document.getElementById('photoToggle');
    const photoSection = document.getElementById('photoSection');
    
    if (photoToggle && photoSection) {
        // Inicialmente mostrar/ocultar según el estado del toggle
        photoEnabled = photoToggle.checked;
        
        // Evento para el toggle
        photoToggle.addEventListener('change', function() {
            const isChecked = this.checked;
            photoEnabled = isChecked;
            photoSection.style.display = isChecked ? 'block' : 'none';
            
            // Si se deshabilita la foto, establecer valor a "Sin Foto"
            if (!isChecked) {
                document.getElementById('photo').value = 'Sin Foto';
                document.getElementById('photoNameDisplay').textContent = 'Sin foto';
                document.getElementById('photoPreviewImg').classList.add('d-none');
                document.getElementById('photoPlaceholder').classList.remove('d-none');
                document.getElementById('btnRemovePhoto').style.display = 'none';
                hasRealPhoto = false;
                photoBlob = null;
                photoUploaded = false;
            }
        });
    }

    // =============================================
    // 11. ENVÍO DEL FORMULARIO
    // =============================================
    $('#editMeasurementForm').submit(async function(e) {
        e.preventDefault();

        // Obtener el campo principal
        const mainField = $('#{{ $mainField }}').val();
        const fecha = $('#fecha_medicion').val();

        // Validar campo principal
        if (!mainField) {
            showAlert('⚠️ El campo {{ $mainFieldLabel }} es obligatorio.', 'danger');
            return;
        }

        // Validar fecha
        if (!fecha) {
            showAlert('⚠️ Selecciona una fecha de medición.', 'danger');
            return;
        }

        // Validar foto
        if (photoEnabled && !hasRealPhoto) {
            showAlert('⚠️ La foto es obligatoria. Usa el botón "Tomar Foto".', 'danger');
            return;
        }

        // Subir foto si está pendiente
        if (photoBlob && !photoUploaded) {
            try {
                await uploadPhoto();
            } catch (error) {
                showAlert('❌ Error al subir la foto: ' + error.message, 'danger');
                return;
            }
        }

        // Preparar datos
        const formData = {
            measurement_id: {{ $measurement->id }},
            sensor_id: sensorId,
            data: {
                @if($mainField)
                {{ $mainField }}: parseFloat(mainField) || 0,
                @endif
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

        // Enviar el formulario
        $.ajax({
            url: '/api/measurements/' + {{ $measurement->id }},
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
                    showAlert('❌ Error: ' + (response.message || 'No se pudo guardar la medición'), 'danger');
                    $('#saveMeasurementBtn').prop('disabled', false).html(`
                        <i class="bi bi-check-circle"></i> Guardar Cambios
                    `);
                }
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON?.message || xhr.statusText;
                showAlert('❌ Error: ' + errorMessage, 'danger');
                $('#saveMeasurementBtn').prop('disabled', false).html(`
                    <i class="bi bi-check-circle"></i> Guardar Cambios
                `);
            }
        });
    });
});
</script>
@endpush
