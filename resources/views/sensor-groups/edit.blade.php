@extends('layouts.modern')

@section('title', 'Editar Grupo de Sensores - MeasureFlow')

@section('content')
    <!-- Incluir el archivo CSS externo -->
    <link rel="stylesheet" href="{{ asset('css/sensor-groups-edit-styles.css') }}">

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="bi bi-folder btn-icon"></i> Editar Grupo de Sensores</h4>
                    </div>
                    <div class="card-body">
                        <form id="sensorGroupForm">
                            <input type="hidden" id="groupId" value="{{ $group->id }}">

                            <!-- Nombre del Grupo -->
                            <div class="mb-3">
                                <label for="groupName" class="form-label">Nombre del Grupo <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="groupName" value="{{ $group->name }}" required>
                            </div>

                            <!-- Descripción -->
                            <div class="mb-3">
                                <label for="groupDescription" class="form-label">Descripción</label>
                                <textarea class="form-control" id="groupDescription"
                                    rows="2">{{ $group->description ?? '' }}</textarea>
                            </div>

                            <!-- Plantilla -->
                            <div class="mb-3">
                                <label for="templateId" class="form-label">Plantilla <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="templateId" required>
                                    <option value="" selected disabled>Selecciona una plantilla...</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}" {{ $group->template_id == $template->id ? 'selected' : '' }}>
                                            {{ $template->name }} ({{ $template->type }})
                                        </option>
                                    @endforeach
                                </select>

                                <!-- Información de la plantilla -->
                                <div class="template-info mt-2" id="templateInfo">
                                    <strong>Información de la plantilla:</strong>
                                    <p id="templateDescriptionText">{{ $group->template->description ?? 'Sin descripción' }}
                                    </p>
                                    <div class="field-preview">
                                        <strong>Campos:</strong>
                                        <ul id="templateFieldsList">
                                            @if($group->template)
                                                @foreach($group->template->schema['campos'] as $campo)
                                                    <li>
                                                        <strong>{{ $campo['nombre'] }}</strong> ({{ $campo['tipo'] }})
                                                        @if(isset($campo['unidad']))
                                                            - Unidad: {{ $campo['unidad'] }}
                                                        @endif
                                                        @if(isset($campo['requerido']) && $campo['requerido'])
                                                            <span class="text-danger">(Requerido)</span>
                                                        @else
                                                            (Opcional)
                                                        @endif
                                                    </li>
                                                @endforeach
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Configuración Contable -->
                            <div class="card mt-4 border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="bi bi-wallet2"></i> Configuración Contable (Opcional)</h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        $billing = $group->billing_settings ?? [];
                                    @endphp
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="billingEnabled" {{ isset($billing['is_enabled']) && $billing['is_enabled'] ? 'checked' : '' }}>
                                        <label class="form-check-label" for="billingEnabled">Habilitar Liquidación
                                            Financiera</label>
                                    </div>
                                    <div id="billingFields"
                                        style="display: {{ isset($billing['is_enabled']) && $billing['is_enabled'] ? 'block' : 'none' }};">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Moneda</label>
                                                <select class="form-select" id="billingCurrency">
                                                    <option value="ARS" {{ (isset($billing['currency']) && $billing['currency'] == 'ARS') ? 'selected' : '' }}>ARS - Pesos Arg
                                                    </option>
                                                    <option value="USD" {{ (isset($billing['currency']) && $billing['currency'] == 'USD') ? 'selected' : '' }}>USD - Dólares
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label" data-bs-toggle="tooltip" title="Si aplicas un pago de expensas fijo sin importar el consumo, colócalo aquí.">Cargo Fijo <i class="bi bi-info-circle text-muted"></i></label>
                                                <input type="number" step="0.01" class="form-control" id="billingFixed"
                                                    value="{{ $billing['fixed_charge'] ?? '0' }}">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label" data-bs-toggle="tooltip" title="Ingresa el costo por cada unidad consumida. Se multiplicará por el consumo del periodo.">Costo x Unidad <i class="bi bi-info-circle text-muted"></i></label>
                                                <input type="number" step="0.01" class="form-control" id="billingPrice"
                                                    value="{{ $billing['price_per_unit'] ?? '0' }}">
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mt-3">
                                            <input class="form-check-input" type="checkbox" id="billingShowPublic" {{ (isset($billing['show_in_public_viewer']) && $billing['show_in_public_viewer']) || !isset($billing['show_in_public_viewer']) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="billingShowPublic">Mostrar valores monetarios en el Visor Público / Links Compartidos</label>
                                        </div>
                                        <small class="text-muted">Si habilitas esto, el sistema calculará un costo estimado en la vista pública de cada sensor de este grupo.</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Programación en Calendario y Tolerancias -->
                            @php
                                $sched = $billing['schedule'] ?? [];
                                $schedMode = $sched['mode'] ?? 'fixed_day';
                                $schedDay = $sched['day_of_month'] ?? 15;
                                $schedInterval = $sched['interval_days'] ?? ($group->periodo_medicion ?? 30);
                                $schedDate = $sched['specific_date'] ?? now()->format('Y-m-15');
                                $schedPreNotice = $sched['pre_notice_days'] ?? 3;
                                $schedTolerance = $sched['tolerance_days'] ?? ($group->dias_vencimiento ?? 5);
                            @endphp
                            <div class="card mt-4 border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="bi bi-calendar2-check"></i> Programación en Calendario y Tolerancias</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <label class="form-label fw-semibold">Modalidad de Toma</label>
                                            <select class="form-select" id="scheduleMode">
                                                <option value="fixed_day" {{ $schedMode === 'fixed_day' ? 'selected' : '' }}>Día fijo de cada mes (Ej: Día 15)</option>
                                                <option value="interval" {{ $schedMode === 'interval' ? 'selected' : '' }}>Cada N días (Desde última lectura)</option>
                                                <option value="specific_date" {{ $schedMode === 'specific_date' ? 'selected' : '' }}>Fecha puntual en calendario</option>
                                            </select>
                                        </div>
                                        <div class="col-md-7 {{ $schedMode === 'fixed_day' ? '' : 'd-none' }}" id="boxScheduleFixedDay">
                                            <label class="form-label fw-semibold">Día del Mes (1 al 31)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Día</span>
                                                <input type="number" class="form-control fw-bold" id="scheduleDayOfMonth" value="{{ $schedDay }}" min="1" max="31">
                                                <span class="input-group-text">de cada mes</span>
                                            </div>
                                        </div>
                                        <div class="col-md-7 {{ $schedMode === 'interval' ? '' : 'd-none' }}" id="boxScheduleInterval">
                                            <label class="form-label fw-semibold">Frecuencia en días</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="scheduleIntervalDays" value="{{ $schedInterval }}" min="1" max="365">
                                                <span class="input-group-text">días</span>
                                            </div>
                                        </div>
                                        <div class="col-md-7 {{ $schedMode === 'specific_date' ? '' : 'd-none' }}" id="boxScheduleSpecificDate">
                                            <label class="form-label fw-semibold">Fecha Programada</label>
                                            <input type="date" class="form-control" id="scheduleSpecificDate" value="{{ $schedDate }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">🔔 Días de Preaviso (Antes)</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="schedulePreNotice" value="{{ $schedPreNotice }}" min="0" max="60">
                                                <span class="input-group-text">días antes</span>
                                            </div>
                                            <small class="text-muted">Para avisarte que se acerca la toma.</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">⏳ Días de Tolerancia (Después)</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="scheduleTolerance" value="{{ $schedTolerance }}" min="0" max="60">
                                                <span class="input-group-text">días de margen</span>
                                            </div>
                                            <small class="text-muted">Margen antes de marcar el grupo como vencido.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de acción -->
                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ route('sensor-groups.index') }}" class="btn btn-secondary me-2">
                                    <i class="bi bi-arrow-left btn-icon"></i> Cancelar
                                </a>
                                <button type="button" class="btn btn-primary" id="saveSensorGroup">
                                    <i class="bi bi-check-circle btn-icon"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#scheduleMode').change(function () {
                const mode = $(this).val();
                $('#boxScheduleFixedDay').toggleClass('d-none', mode !== 'fixed_day');
                $('#boxScheduleInterval').toggleClass('d-none', mode !== 'interval');
                $('#boxScheduleSpecificDate').toggleClass('d-none', mode !== 'specific_date');
            });

            // Cargar información de la plantilla seleccionada
            $('#templateId').change(function () {
                const templateId = $(this).val();
                if (!templateId) {
                    $('#templateInfo').hide();
                    return;
                }

                // Buscar la plantilla en el array de templates
                const templates = @json($templates);
                const selectedTemplate = templates.find(t => t.id == templateId);

                if (selectedTemplate) {
                    $('#templateDescriptionText').text(selectedTemplate.description || 'Sin descripción');
                    const fieldsList = $('#templateFieldsList');
                    fieldsList.empty();

                    selectedTemplate.schema.campos.forEach(campo => {
                        let fieldInfo = `
                        <li>
                            <strong>${campo.nombre}</strong> (${campo.tipo})
                    `;
                        if (campo.unidad) {
                            fieldInfo += ` - Unidad: ${campo.unidad}`;
                        }
                        fieldInfo += campo.requerido ? ' <span class="text-danger">(Requerido)</span>' : ' (Opcional)';
                        fieldInfo += `</li>`;
                        fieldsList.append(fieldInfo);
                    });

                    $('#templateInfo').show();
                }
            });

            // Toggle configuración contable
            $('#billingEnabled').change(function() {
                if($(this).is(':checked')) {
                    $('#billingFields').slideDown();
                } else {
                    $('#billingFields').slideUp();
                }
            });

            // Guardar cambios
            $('#saveSensorGroup').click(async function() {
                const groupId = $('#groupId').val();
                const name = $('#groupName').val();
                const description = $('#groupDescription').val();
                const templateId = $('#templateId').val();

                const scheduleObj = {
                    enabled: true,
                    mode: $('#scheduleMode').val(),
                    day_of_month: parseInt($('#scheduleDayOfMonth').val()) || 15,
                    interval_days: parseInt($('#scheduleIntervalDays').val()) || 30,
                    specific_date: $('#scheduleSpecificDate').val() || null,
                    pre_notice_days: parseInt($('#schedulePreNotice').val()) || 3,
                    tolerance_days: parseInt($('#scheduleTolerance').val()) || 5
                };
                
                let billing_settings = null;
                if($('#billingEnabled').is(':checked')) {
                    billing_settings = {
                        is_enabled: true,
                        currency: $('#billingCurrency').val(),
                        fixed_charge: parseFloat($('#billingFixed').val()) || 0,
                        price_per_unit: parseFloat($('#billingPrice').val()) || 0,
                        show_in_public_viewer: $('#billingShowPublic').is(':checked'),
                        schedule: scheduleObj
                    };
                } else {
                    billing_settings = {
                        is_enabled: false,
                        schedule: scheduleObj
                    };
                }

                if (!name || !templateId) {
                    showAlert('Los campos obligatorios deben completarse', 'danger');
                    return;
                }

                try {
                    const response = await $.ajax({
                        url: `/api/sensor-groups/${groupId}`,
                        type: 'PUT',
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('token'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        data: JSON.stringify({
                            name: name,
                            description: description,
                            template_id: templateId,
                            periodo_medicion: scheduleObj.interval_days,
                            dias_vencimiento: scheduleObj.tolerance_days,
                            billing_settings: billing_settings
                        })
                    });

                    if (response.success) {
                        showAlert('Grupo actualizado correctamente', 'success');
                        window.location.href = '{{ route("sensor-groups.index") }}';
                    } else {
                        showAlert(response.message || 'Error al actualizar el grupo', 'danger');
                    }
                } catch (error) {
                    showAlert('Error al actualizar el grupo: ' + (error.responseJSON?.message || error.message), 'danger');
                }
            });

            // Función para mostrar alertas
            function showAlert(message, type) {
                const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
                $('.card-body').prepend(alertHtml);
            }
        });
    </script>
@endpush