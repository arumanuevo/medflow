@extends('layouts.modern')

@section('title', 'Tomar Medición - ' . ($sensor->name ?? 'Sensor'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/measurements-create-styles.css') }}">
@endpush

@section('content')
<div class="measurement-container">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-rulers me-2"></i>
                Tomar Medición
            </h5>
            <a href="{{ route('sensors.show', $sensor->id) }}" class="btn btn-light btn-sm">
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
                <span class="info-item">
                    <i class="bi bi-file-text"></i>
                    Plantilla: {{ $sensor->group->template->name ?? 'N/A' }}
                </span>
            </div>

            <!-- ============================================
            INFO DE LA MEDICIÓN ANTERIOR
            ============================================ -->
            @php
                $mainField = 'consumo_m3';
                $lastValue = null;
                $lastDate = null;
                $unit = 'm³';
                
                if (isset($previousMeasurement)) {
                    $lastValue = $previousMeasurement->data[$mainField] ?? null;
                    $lastDate = $previousMeasurement->measured_at;
                }
            @endphp

            @if($lastValue !== null)
                <div class="previous-measurement-box">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="label">Última medición</div>
                            <div class="value">
                                {{ number_format($lastValue, 2) }} <span class="unit">{{ $unit }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="label">Fecha</div>
                            <div class="value" style="font-size: 0.95rem;">
                                {{ $lastDate ? $lastDate->format('d/m/Y H:i') : 'N/A' }}
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
ADVERTENCIA DE PERÍODO (CON REDONDEO)
============================================ -->
@php
    $periodoMedicion = $sensor->group->periodo_medicion ?? 30;
    $diasVencimiento = $sensor->group->dias_vencimiento ?? 5;
    $diasDesdeUltima = null;
    $estadoPeriodo = 'ok';
    $mensajePeriodo = '';
    
    if (isset($previousMeasurement) && $previousMeasurement) {
        $ultimaFecha = Carbon\Carbon::parse($previousMeasurement->measured_at);
        $diasDesdeUltima = $ultimaFecha->diffInDays(now()); // ✅ Ya es entero
        $diasRestantes = $periodoMedicion - $diasDesdeUltima;
        
        if ($diasDesdeUltima < $periodoMedicion) {
            $estadoPeriodo = 'temprano';
            $mensajePeriodo = "⚠️ Han pasado <strong>" . number_format($diasDesdeUltima, 0) . "</strong> días desde la última medición. " .
                              "El período definido es de <strong>{$periodoMedicion}</strong> días. " .
                              "Faltan <strong>" . number_format($diasRestantes, 0) . "</strong> días para la próxima medición programada.";
        } elseif ($diasDesdeUltima > $periodoMedicion + $diasVencimiento) {
            $estadoPeriodo = 'vencido';
            $mensajePeriodo = "🔴 Han pasado <strong>" . number_format($diasDesdeUltima, 0) . "</strong> días desde la última medición. " .
                              "El período definido es de <strong>{$periodoMedicion}</strong> días. " .
                              "La medición está <strong>vencida por " . number_format($diasDesdeUltima - $periodoMedicion, 0) . " días</strong>.";
        } elseif ($diasDesdeUltima > $periodoMedicion) {
            $estadoPeriodo = 'pendiente';
            $diasExcedidos = $diasDesdeUltima - $periodoMedicion;
            $mensajePeriodo = "🟡 Han pasado <strong>" . number_format($diasDesdeUltima, 0) . "</strong> días desde la última medición. " .
                              "El período definido es de <strong>{$periodoMedicion}</strong> días. " .
                              "La medición está <strong>" . number_format($diasExcedidos, 0) . " días</strong> fuera del período.";
        } else {
            $estadoPeriodo = 'ok';
            $diasRestantes = $periodoMedicion - $diasDesdeUltima;
            $mensajePeriodo = "✅ Han pasado <strong>" . number_format($diasDesdeUltima, 0) . "</strong> días desde la última medición. " .
                              "El período definido es de <strong>{$periodoMedicion}</strong> días. " .
                              "Te quedan <strong>" . number_format($diasRestantes, 0) . "</strong> días para la próxima medición programada.";
        }
    }
@endphp
            @if(isset($previousMeasurement) && $previousMeasurement)
                <div class="alert alert-{{ $estadoPeriodo === 'ok' ? 'success' : ($estadoPeriodo === 'temprano' ? 'warning' : ($estadoPeriodo === 'vencido' ? 'danger' : 'warning')) }} mb-3" style="font-size: 0.85rem; padding: 0.5rem 0.75rem;">
                    <i class="bi bi-{{ $estadoPeriodo === 'ok' ? 'check-circle' : ($estadoPeriodo === 'temprano' ? 'info-circle' : ($estadoPeriodo === 'vencido' ? 'exclamation-triangle' : 'clock')) }} me-2"></i>
                    {!! $mensajePeriodo !!}
                </div>
            @endif
            <!-- ============================================
            FORMULARIO
            ============================================ -->
            <form id="measurementForm">
                @csrf
                <input type="hidden" name="sensor_id" value="{{ $sensor->id }}">
                <input type="hidden" id="groupName" value="{{ str_replace(' ', '_', $sensor->group->name ?? 'SinGrupo') }}">
                
                <!-- Campo: Valor de consumo (campo principal de la plantilla) -->
                <div class="mb-3">
                    <label for="consumo_m3" class="form-label">
                        <i class="bi bi-speedometer2 me-1 text-primary"></i>
                        Consumo Actual (m³) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="number" step="0.01" class="form-control" id="consumo_m3" 
                               name="data[consumo_m3]" 
                               placeholder="Ej: 125.50" required autofocus>
                        <span class="input-group-text">m³</span>
                    </div>
                    @if($lastValue !== null)
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Anterior: <strong>{{ number_format($lastValue, 2) }} m³</strong>
                            @php
                                $diff = $lastValue ? (float)old('consumo_m3', 0) - $lastValue : null;
                            @endphp
                            <span id="consumptionDiff" class="badge bg-light text-dark border ms-1">
                                Diferencia: 0.00 m³
                            </span>
                        </small>
                    @else
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Ingresa el valor de consumo registrado en el medidor.
                        </small>
                    @endif
                </div>

                <!-- Información de consumo calculado (solo si hay medición anterior) -->
                @if($lastValue !== null)
                    <div class="consumption-info" id="consumptionInfo">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <i class="bi bi-calculator text-primary"></i>
                            <span>
                                <strong>Consumo calculado:</strong>
                                <span id="calculatedConsumption">0.00</span> m³
                                <span class="text-muted small">(actual - anterior)</span>
                            </span>
                            <span class="badge bg-{{ $lastValue && (float)old('consumo_m3', 0) > $lastValue ? 'success' : 'secondary' }}" 
                                  id="consumptionStatus">
                                {{ $lastValue && (float)old('consumo_m3', 0) > $lastValue ? '✅ Válido' : '⏳ Pendiente' }}
                            </span>
                        </div>
                    </div>
                @endif

                <!-- Fecha de medición -->
                <div class="mb-3">
                    <label for="fecha_medicion" class="form-label">
                        <i class="bi bi-calendar3 me-1 text-primary"></i>
                        Fecha de Medición <span class="text-danger">*</span>
                    </label>
                    <input type="datetime-local" class="form-control" id="fecha_medicion" 
                           name="data[fecha_medicion]" required>
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Fecha y hora en que se tomó la medición.
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
                            <input class="form-check-input" type="checkbox" id="photoToggle" checked>
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
                                            <span id="photoNameDisplay">Sin foto</span>
                                        </small>
                                    </div>
                                    
                                    <input type="hidden" id="photo" name="data[foto]" value="Sin Foto">
                                    
                                    <small class="text-muted text-center mt-1">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Formato: <code>(grupo)_(sensor)_(fecha).png</code>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================
                CAMPOS PERSONALIZADOS (SI EXISTEN)
                ============================================ -->
                @php
                    $customFields = [];
                    if (isset($sensor->group) && isset($sensor->group->template) && isset($sensor->group->template->schema['campos'])) {
                        $customFields = array_filter($sensor->group->template->schema['campos'], function($campo) {
                            // Excluir campos que ya tenemos en el formulario
                            return !in_array($campo['nombre'], ['consumo_m3', 'foto', 'fecha_medicion']);
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
                    <a href="{{ route('sensors.show', $sensor->id) }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary flex-grow-1" id="saveMeasurementBtn">
                        <i class="bi bi-check-circle me-1"></i> Guardar Medición
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
            <div class="modal-header">
                <h6 class="modal-title">
                    <i class="bi bi-camera me-2"></i> Tomar Foto
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body position-relative" id="cameraModalBody">
                <div class="camera-container">
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
    // 1. CONFIGURACIÓN INICIAL (datos desde PHP)
    // =============================================
    const sensorId = {{ $sensor->id }};
    const groupName = '{{ str_replace(' ', '_', $sensor->group->name ?? 'SinGrupo') }}';
    const token = localStorage.getItem('token');
    const mainField = 'consumo_m3';
    const lastValue = {{ $lastValue !== null ? $lastValue : 'null' }};
    const periodoMedicion = {{ $periodoMedicion ?? 30 }};
    const diasVencimiento = {{ $diasVencimiento ?? 5 }};
    const ultimaFecha = @json(isset($previousMeasurement) ? $previousMeasurement->measured_at->toDateTimeString() : null);
    
    // ✅ TODAS las mediciones (pasadas desde el controlador)
    const allMeasurements = @json($allMeasurements ?? []);

    // Estado de la cámara
    let hasRealPhoto = false;
    let stream = null;
    let photoBlob = null;
    let photoUploaded = false;

    // Inicializar fecha actual
    const now = new Date();
    $('#fecha_medicion').val(now.toISOString().slice(0, 16));

    // =============================================
    // 2. VALIDACIÓN DE SECUENCIA (FECHAS Y VALORES)
    // =============================================
    function validateMeasurement(value, date) {
        // Si no hay mediciones, es válida
        if (allMeasurements.length === 0) {
            return { valid: true, message: 'Primera medición' };
        }

        const newDate = new Date(date);
        let previous = null;
        let next = null;
        let position = 'last';

        // Buscar posición en la secuencia
        for (let i = 0; i < allMeasurements.length; i++) {
            const mDate = new Date(allMeasurements[i].date);
            
            // Si la fecha es igual, duplicado
            if (newDate.getTime() === mDate.getTime()) {
                return { valid: false, message: 'Ya existe una medición con esta fecha.' };
            }
            
            // Si la nueva fecha es anterior a esta medición
            if (newDate < mDate) {
                position = 'intermediate';
                next = allMeasurements[i];
                if (i > 0) {
                    previous = allMeasurements[i - 1];
                }
                break;
            }
        }

        // Si no se encontró fecha posterior, es la última
        if (position === 'last') {
            previous = allMeasurements[allMeasurements.length - 1];
            next = null;
        }

        // Caso 1: Primera medición (fecha anterior a todas)
        if (position === 'intermediate' && previous === null) {
            if (next && value >= next.value) {
                return {
                    valid: false,
                    message: `Siendo la primera medición, el valor (${value}) debe ser MENOR al siguiente (${next.value}).`
                };
            }
            return { valid: true, message: 'Primera medición (fecha anterior a todas)' };
        }

        // Caso 2: Última medición
        if (position === 'last' && previous) {
            if (value <= previous.value) {
                return {
                    valid: false,
                    message: `Siendo la última medición, el valor (${value}) debe ser MAYOR al anterior (${previous.value}).`
                };
            }
            return { valid: true, message: `Última medición (${previous.value} → ${value})` };
        }

        // Caso 3: Medición intermedia
        if (position === 'intermediate' && previous && next) {
            if (value <= previous.value) {
                return {
                    valid: false,
                    message: `Siendo una medición intermedia, el valor (${value}) debe ser MAYOR al anterior (${previous.value}).`
                };
            }
            if (value >= next.value) {
                return {
                    valid: false,
                    message: `Siendo una medición intermedia, el valor (${value}) debe ser MENOR al siguiente (${next.value}).`
                };
            }
            return { valid: true, message: `${previous.value} → ${value} → ${next.value}` };
        }

        return { valid: true, message: 'Válida' };
    }

    // =============================================
    // 3. ACTUALIZAR UI EN TIEMPO REAL
    // =============================================
    function updateUI(value, date) {
        const diffSpan = $('#consumptionDiff');
        const calcSpan = $('#calculatedConsumption');
        const statusBadge = $('#consumptionStatus');

        // Si no hay valor o fecha, resetear
        if (!value || isNaN(value) || !date) {
            diffSpan.text('Diferencia: 0.00 m³');
            calcSpan.text('0.00');
            statusBadge.text('⏳ Pendiente')
                .removeClass('bg-success bg-danger bg-info')
                .addClass('bg-secondary');
            return;
        }

        // Validar secuencia
        const result = validateMeasurement(value, date);

        // Actualizar UI según validación
        if (result.valid) {
            statusBadge.text('✅ ' + result.message)
                .removeClass('bg-secondary bg-danger')
                .addClass('bg-success');
            diffSpan.removeClass('text-danger').addClass('text-success');
            
            // Calcular diferencia con anterior
            if (lastValue !== null) {
                const diff = value - lastValue;
                diffSpan.text(`Diferencia: ${Math.abs(diff).toFixed(2)} m³`);
                calcSpan.text(diff.toFixed(2));
            } else {
                diffSpan.text('Primera medición');
                calcSpan.text('N/A');
            }
        } else {
            statusBadge.text('❌ ' + result.message)
                .removeClass('bg-secondary bg-success')
                .addClass('bg-danger');
            diffSpan.removeClass('text-success').addClass('text-danger');
        }
    }

    // =============================================
    // 4. EVENTOS DEL FORMULARIO
    // =============================================
    $('#consumo_m3, #fecha_medicion').on('change input', function() {
        const value = parseFloat($('#consumo_m3').val());
        const date = $('#fecha_medicion').val();
        updateUI(value, date);
    });

    // =============================================
    // 5. ADVERTENCIA DE PERÍODO
    // =============================================
    function checkPeriodWarning() {
        if (!ultimaFecha) return;
        
        const ultima = new Date(ultimaFecha);
        const ahora = new Date();
        const diasDesde = Math.floor((ahora - ultima) / (1000 * 60 * 60 * 24));
        
        if (diasDesde < periodoMedicion) {
            const diasRestantes = periodoMedicion - diasDesde;
            showAlert(
                `ℹ️ Han pasado ${diasDesde} días desde la última medición. Faltan ${diasRestantes} días.`,
                'info'
            );
        } else if (diasDesde > periodoMedicion + diasVencimiento) {
            showAlert(
                `🔴 ¡ATENCIÓN! Han pasado ${diasDesde} días. La medición está vencida por ${diasDesde - periodoMedicion} días.`,
                'danger'
            );
        } else if (diasDesde > periodoMedicion) {
            const diasExcedidos = diasDesde - periodoMedicion;
            showAlert(
                `🟡 Han pasado ${diasDesde} días. La medición está ${diasExcedidos} días fuera del período.`,
                'warning'
            );
        }
    }
    checkPeriodWarning();

    // =============================================
    // 6. FUNCIONES DE LA CÁMARA
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
    // 9. ENVÍO DEL FORMULARIO
    // =============================================
    $('#measurementForm').submit(async function(e) {
        e.preventDefault();

        const consumo = $('#consumo_m3').val();
        const fecha = $('#fecha_medicion').val();

        // Validar consumo
        if (!consumo || isNaN(consumo)) {
            showAlert('⚠️ Ingresa un valor de consumo válido.', 'danger');
            return;
        }

        const consumoNum = parseFloat(consumo);

        // Validar fecha
        if (!fecha) {
            showAlert('⚠️ Selecciona una fecha de medición.', 'danger');
            return;
        }

        // Validar secuencia completa
        const validation = validateMeasurement(consumoNum, fecha);
        if (!validation.valid) {
            showAlert('❌ ' + validation.message, 'danger');
            return;
        }

        // =============================================
        // ✅ VALIDAR FOTO (CONSIDERANDO TOGGLE)
        // =============================================
        const photoToggle = document.getElementById('photoToggle');
        const isPhotoEnabled = photoToggle ? photoToggle.checked : true;

        if (isPhotoEnabled) {
            // ✅ Foto habilitada: debe haber una foto real
            if (!hasRealPhoto) {
                showAlert('⚠️ La foto es obligatoria. Usa el botón "Tomar Foto" o deshabilita la foto en el toggle.', 'danger');
                return;
            }
            
            // Subir foto si está pendiente
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
        } else {
            // ✅ Foto deshabilitada: usar "Sin Foto"
            $('#photo').val('Sin Foto');
            photoUploaded = true;
            hasRealPhoto = true; // Marcar como válido para que pase la validación
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
                setTimeout(() => {
                    window.location.href = '/sensors/' + sensorId;
                }, 1000);
            } else {
                showAlert('❌ ' + (response.message || 'Error al guardar'), 'danger');
                $btn.prop('disabled', false).html(`
                    <i class="bi bi-check-circle me-1"></i> Guardar Medición
                `);
            }
        } catch (xhr) {
            const errorMessage = xhr.responseJSON?.message || xhr.statusText;
            showAlert('❌ Error: ' + errorMessage, 'danger');
            $btn.prop('disabled', false).html(`
                <i class="bi bi-check-circle me-1"></i> Guardar Medición
            `);
        }
    });

    // =============================================
    // 10. FUNCIONES AUXILIARES
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

    // =============================================
    // TOGGLE DE FOTO
    // =============================================
    const photoToggle = document.getElementById('photoToggle');
    const photoSection = document.getElementById('photoSection');
    
    if (photoToggle && photoSection) {
        // Inicialmente mostrar/ocultar según el estado del toggle
        updatePhotoSection();
        
        // Evento para el toggle
        photoToggle.addEventListener('change', updatePhotoSection);
    }
    
    function updatePhotoSection() {
        if (!photoToggle || !photoSection) return;
        
        const isChecked = photoToggle.checked;
        photoSection.style.display = isChecked ? 'block' : 'none';
        
        // Si se deshabilita la foto, establecer valor a "Sin Foto"
        if (!isChecked) {
            document.getElementById('photo').value = 'Sin Foto';
            document.getElementById('photoNameDisplay').textContent = 'Sin foto';
            document.getElementById('photoPreviewImg').classList.add('d-none');
            document.getElementById('photoPlaceholder').classList.remove('d-none');
            document.getElementById('btnRemovePhoto').style.display = 'none';
        }
    }
});
</script>
@endpush