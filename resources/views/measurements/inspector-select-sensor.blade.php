@extends('layouts.modern')

@section('title', 'Tomar Mediciones - Espacio Colaborativo')

@push('styles')
<style>
    .sensor-table-wrapper {
        overflow-x: auto;
    }
    .sensor-table-wrapper .table th {
        white-space: nowrap;
        font-size: 0.75rem;
        padding: 0.5rem 0.6rem;
        text-align: center !important;
        vertical-align: middle;
        cursor: pointer;
        user-select: none;
        position: relative;
    }
    .sensor-table-wrapper .table th:hover {
        background-color: rgba(13, 110, 253, 0.08);
    }
    .sensor-table-wrapper .table td {
        font-size: 0.85rem;
        padding: 0.5rem 0.6rem;
        vertical-align: middle;
        text-align: center;
    }
    .sensor-table-wrapper .table td.text-left {
        text-align: left;
    }
    .badge-extra-field {
        display: inline-block;
        font-size: 0.75rem;
        padding: 0.2rem 0.6rem;
        background: #f1f3f5;
        color: #495057;
        border-radius: 4px;
        border: 1px solid #e9ecef;
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .selected-row {
        background-color: #e7f1ff !important;
    }
    .sensor-checkbox {
        cursor: pointer;
    }
    .bulk-actions {
        background: #f8f9fa;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }
    .bulk-actions .badge-count {
        font-size: 0.8rem;
        padding: 0.3rem 0.8rem;
        background: #0d6efd;
        color: white;
        border-radius: 20px;
    }
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><i class="bi bi-rulers"></i> Tomar Mediciones</h4>
                            <small>Espacio de: <strong>{{ $ownerName ?? 'Propietario' }}</strong> | Rol: <span class="badge bg-info">Inspector</span></small>
                        </div>
                        <div>
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-sensors"></i> {{ $sensors->count() }} sensores
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('info'))
                        <div class="alert alert-info">{{ session('info') }}</div>
                    @endif

                    {{-- Información del espacio --}}
                    <div class="alert alert-secondary">
                        <i class="bi bi-briefcase"></i> Estás tomando mediciones en el espacio de <strong>{{ $ownerName ?? 'Propietario' }}</strong>
                        <br>
                        <small class="text-muted">Selecciona los sensores que deseas medir y usa la acción masiva.</small>
                    </div>

                    {{-- Acciones Masivas --}}
                    <div class="bulk-actions">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <button class="btn btn-sm btn-success" id="selectAllBtn">
                                <i class="bi bi-check-all"></i> Seleccionar Todos
                            </button>
                            <button class="btn btn-sm btn-secondary" id="deselectAllBtn">
                                <i class="bi bi-x-circle"></i> Deseleccionar Todos
                            </button>
                            <span class="badge-count" id="selectedCount">0 seleccionados</span>
                            <button class="btn btn-sm btn-primary" id="takeBulkMeasurementBtn" disabled>
                                <i class="bi bi-rulers"></i> Tomar Mediciones Masivas
                            </button>
                            <button class="btn btn-sm btn-warning" id="toggleAllMarkedBtn">
                                <i class="bi bi-check-square"></i> Marcar/Desmarcar Todos para Medición
                            </button>
                        </div>
                    </div>

                    {{-- Tabla de Sensores --}}
                    <div class="sensor-table-wrapper">
                        <table class="table table-bordered table-striped table-hover" id="sensorsTable">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">
                                        <input type="checkbox" id="selectAllCheckbox">
                                    </th>
                                    <th>ID</th>
                                    <th class="text-left">Nombre</th>
                                    <th>Identificador</th>
                                    <th>Grupo</th>
                                    <th>Última Medición</th>
                                    <th>Valor</th>
                                    <th>Estado</th>
                                    <th style="min-width: 120px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sensors as $sensor)
                                    @php
                                        $lastMeasurement = $sensor->lastMeasurement;
                                        $lastValue = $lastMeasurement ? $lastMeasurement->data['consumo_m3'] ?? 'N/A' : 'N/A';
                                        $lastDate = $lastMeasurement ? \Carbon\Carbon::parse($lastMeasurement->measured_at)->format('d/m/Y H:i') : 'N/A';
                                        
                                        // Calcular estado
                                        $estado = 'Sin medición';
                                        $estadoClass = 'secondary';
                                        if ($lastMeasurement) {
                                            $periodoMedicion = $sensor->group->periodo_medicion ?? 30;
                                            $diasVencimiento = $sensor->group->dias_vencimiento ?? 5;
                                            $proximaMedicion = \Carbon\Carbon::parse($lastMeasurement->measured_at)->addDays($periodoMedicion);
                                            $diasRestantes = now()->diffInDays($proximaMedicion, false);
                                            
                                            if ($diasRestantes < 0) {
                                                $estado = 'Vencido';
                                                $estadoClass = 'danger';
                                            } elseif ($diasRestantes <= $diasVencimiento) {
                                                $estado = 'Pendiente';
                                                $estadoClass = 'warning';
                                            } else {
                                                $estado = 'Al día';
                                                $estadoClass = 'success';
                                            }
                                        }
                                    @endphp
                                    <tr data-sensor-id="{{ $sensor->id }}" data-sensor-name="{{ $sensor->name }}">
                                        <td>
                                            <input type="checkbox" class="sensor-checkbox" data-sensor-id="{{ $sensor->id }}" {{ $sensor->marcado_para_medicion ? 'checked' : '' }}>
                                        </td>
                                        <td>{{ $sensor->id }}</td>
                                        <td class="text-left"><strong>{{ $sensor->name }}</strong></td>
                                        <td><code>{{ $sensor->identifier }}</code></td>
                                        <td>{{ $sensor->group->name ?? 'Sin grupo' }}</td>
                                        <td>{{ $lastDate }}</td>
                                        <td>{{ $lastValue }}</td>
                                        <td>
                                            <span class="badge bg-{{ $estadoClass }}">{{ $estado }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="{{ route('measurements.inspector.create', $sensor->id) }}" 
                                                class="btn btn-sm btn-success" title="Tomar Medición">
                                                    <i class="bi bi-rulers"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-primary toggle-mark-btn" 
                                                        data-sensor-id="{{ $sensor->id }}"
                                                        data-marked="{{ $sensor->marcado_para_medicion ? 'true' : 'false' }}"
                                                        title="Marcar para medición masiva">
                                                    <i class="bi bi-{{ $sensor->marcado_para_medicion ? 'check-square' : 'square' }}"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                                            <h5 class="mt-2">No hay sensores disponibles</h5>
                                            <p class="text-muted">El propietario de este espacio aún no ha creado sensores.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <a href="/dashboard" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver al Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para medición masiva -->
<div class="modal fade" id="bulkMeasurementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-rulers"></i> Tomar Mediciones Masivas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas tomar mediciones para <strong id="bulkCount">0</strong> sensores?</p>
                <p class="text-muted small">Serás redirigido al primer sensor para comenzar la medición masiva.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmBulkMeasurementBtn">
                    <i class="bi bi-rulers"></i> Comenzar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let selectedSensors = new Set();

    // =============================================
    // SELECCIÓN DE SENSORES
    // =============================================
    
    // Seleccionar/Deseleccionar individual
    $('.sensor-checkbox').change(function() {
        const sensorId = $(this).data('sensor-id');
        if ($(this).is(':checked')) {
            selectedSensors.add(sensorId);
            $(this).closest('tr').addClass('selected-row');
        } else {
            selectedSensors.delete(sensorId);
            $(this).closest('tr').removeClass('selected-row');
        }
        updateBulkActions();
    });

    // Seleccionar todos
    $('#selectAllCheckbox').change(function() {
        const isChecked = $(this).is(':checked');
        $('.sensor-checkbox').prop('checked', isChecked);
        $('.sensor-checkbox').each(function() {
            const sensorId = $(this).data('sensor-id');
            if (isChecked) {
                selectedSensors.add(sensorId);
                $(this).closest('tr').addClass('selected-row');
            } else {
                selectedSensors.delete(sensorId);
                $(this).closest('tr').removeClass('selected-row');
            }
        });
        updateBulkActions();
    });

    // Botón Seleccionar Todos
    $('#selectAllBtn').click(function() {
        $('#selectAllCheckbox').prop('checked', true).trigger('change');
    });

    // Botón Deseleccionar Todos
    $('#deselectAllBtn').click(function() {
        $('#selectAllCheckbox').prop('checked', false).trigger('change');
    });

    // =============================================
    // BOTONES DE ACCIÓN MASIVA
    // =============================================
    
    function updateBulkActions() {
        const count = selectedSensors.size;
        $('#selectedCount').text(count + ' seleccionados');
        $('#takeBulkMeasurementBtn').prop('disabled', count === 0);
        
        // Actualizar texto del botón
        if (count > 0) {
            $('#takeBulkMeasurementBtn').html(`<i class="bi bi-rulers"></i> Tomar Mediciones Masivas (${count})`);
        } else {
            $('#takeBulkMeasurementBtn').html(`<i class="bi bi-rulers"></i> Tomar Mediciones Masivas`);
        }
    }

    // Tomar Mediciones Masivas
    $('#takeBulkMeasurementBtn').click(function() {
        const count = selectedSensors.size;
        if (count === 0) return;
        
        $('#bulkCount').text(count);
        $('#bulkMeasurementModal').modal('show');
    });

    // Confirmar medición masiva
    $('#confirmBulkMeasurementBtn').click(function() {
        // \u2705 Ordenar los sensores seleccionados por ID ascendente para mantener secuencia consistente
        const sensorIds = Array.from(selectedSensors).sort((a, b) => a - b);
        
        if (sensorIds.length === 0) return;
        
        // ✅ Marcar todos los sensores seleccionados en la base de datos
        const token = localStorage.getItem('token');
        let completed = 0;
        let errors = 0;
        
        // Mostrar progreso
        $('#confirmBulkMeasurementBtn').prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm" role="status"></span> Preparando...
        `);
        
        // Marcar sensores uno por uno
        sensorIds.forEach(function(sensorId, index) {
            $.ajax({
                url: `/api/bulk/measurements/sensors/${sensorId}/toggle-mark`,
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify({ mark: true }),
                success: function(response) {
                    completed++;
                    if (completed + errors === sensorIds.length) {
                        // Todos procesados
                        $('#bulkMeasurementModal').modal('hide');
                        // Redirigir al primer sensor
                        window.location.href = '/mediciones/bulk-create/' + sensorIds[0];
                    }
                },
                error: function() {
                    errors++;
                    if (completed + errors === sensorIds.length) {
                        // Todos procesados con errores
                        $('#bulkMeasurementModal').modal('hide');
                        if (completed > 0) {
                            // Redirigir al primer sensor que se marcó correctamente
                            window.location.href = '/mediciones/bulk-create/' + sensorIds[0];
                        } else {
                            showAlert('Error al marcar los sensores. Intenta nuevamente.', 'danger');
                            $('#confirmBulkMeasurementBtn').prop('disabled', false).html(`
                                <i class="bi bi-rulers"></i> Comenzar
                            `);
                        }
                    }
                }
            });
        });
    });

    // =============================================
    // MARCAR/DESMARCAR INDIVIDUAL
    // =============================================
    $('.toggle-mark-btn').click(function() {
        const sensorId = $(this).data('sensor-id');
        const isMarked = $(this).data('marked') === 'true';
        const newMark = !isMarked;
        const icon = $(this).find('i');
        const token = localStorage.getItem('token');
        
        $.ajax({
            url: `/api/bulk/measurements/sensors/${sensorId}/toggle-mark`,
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({ mark: newMark }),
            success: function(response) {
                if (response.success) {
                    // Actualizar icono
                    icon.removeClass('bi-square bi-check-square');
                    icon.addClass(newMark ? 'bi-check-square' : 'bi-square');
                    $(this).data('marked', newMark ? 'true' : 'false');
                    
                    // Actualizar checkbox
                    $(`.sensor-checkbox[data-sensor-id="${sensorId}"]`).prop('checked', newMark);
                    if (newMark) {
                        selectedSensors.add(sensorId);
                        $(`.sensor-checkbox[data-sensor-id="${sensorId}"]`).closest('tr').addClass('selected-row');
                    } else {
                        selectedSensors.delete(sensorId);
                        $(`.sensor-checkbox[data-sensor-id="${sensorId}"]`).closest('tr').removeClass('selected-row');
                    }
                    updateBulkActions();
                }
            },
            error: function(xhr) {
                showAlert('Error al marcar el sensor', 'danger');
            }
        });
    });

    // =============================================
    // MARCAR/DESMARCAR TODOS
    // =============================================
    $('#toggleAllMarkedBtn').click(function() {
        const token = localStorage.getItem('token');
        const groupId = null; // Para el espacio activo, no filtramos por grupo
        
        // Obtener el estado actual de todos los checkboxes
        const anyMarked = $('.sensor-checkbox:checked').length > 0;
        const action = anyMarked ? 'unmark' : 'mark';
        
        $.ajax({
            url: '/api/bulk/measurements/toggle-all-marked',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({
                action: action,
                group_id: groupId
            }),
            success: function(response) {
                if (response.success) {
                    // Actualizar todos los checkboxes
                    $('.sensor-checkbox').prop('checked', action === 'mark');
                    $('.sensor-checkbox').each(function() {
                        const sensorId = $(this).data('sensor-id');
                        if (action === 'mark') {
                            selectedSensors.add(sensorId);
                            $(this).closest('tr').addClass('selected-row');
                        } else {
                            selectedSensors.delete(sensorId);
                            $(this).closest('tr').removeClass('selected-row');
                        }
                    });
                    updateBulkActions();
                    showAlert(`Todos los sensores ${action === 'mark' ? 'marcados' : 'desmarcados'} correctamente`, 'success');
                }
            },
            error: function(xhr) {
                showAlert('Error al marcar/desmarcar sensores', 'danger');
            }
        });
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
        $('.card-body').prepend(alertHtml);
        
        setTimeout(() => {
            $('.alert').first().fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Inicializar contador
    updateBulkActions();
});
</script>
@endpush