@extends('layouts.modern')

@section('title', 'Seleccionar Sensores - Medición Masiva')

@push('styles')
<style>
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
    .selected-row {
        background-color: #e7f1ff !important;
    }
    .sensor-checkbox {
        cursor: pointer;
    }
    .filter-section {
        background: white;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        margin-bottom: 1rem;
    }
    .filter-section .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #495057;
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
                            <h4 class="mb-0"><i class="bi bi-rulers"></i> Medición Masiva</h4>
                            <small>Espacio de: <strong>{{ $ownerName ?? 'Propietario' }}</strong></small>
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

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    {{-- Información del espacio --}}
                    <div class="alert alert-secondary">
                        <i class="bi bi-briefcase"></i> 
                        Estás en el espacio de <strong>{{ $ownerName ?? 'Propietario' }}</strong>
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
                            
                            {{-- Checkbox para elegir orden --}}
                            <div class="form-check form-check-inline ms-2">
                                <input class="form-check-input" type="checkbox" id="useSelectionOrder" checked>
                                <label class="form-check-label small" for="useSelectionOrder">
                                    <i class="bi bi-list-ol me-1"></i> Orden de selección
                                </label>
                                <span class="text-muted small ms-1" data-bs-toggle="tooltip" title="Si está marcado, los sensores se medirán en el orden en que los seleccionaste. Si no, se ordenarán por ID ascendente.">
                                    <i class="bi bi-info-circle"></i>
                                </span>
                            </div>
                            
                            <button class="btn btn-sm btn-primary" id="startBulkBtn" disabled>
                                <i class="bi bi-rulers"></i> Comenzar Medición Masiva
                            </button>
                        </div>
                    </div>

                    {{-- Filtros --}}
                    <div class="filter-section">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Grupo</label>
                                <select id="groupFilter" class="form-select form-select-sm">
                                    <option value="">Todos los grupos</option>
                                    @php
                                        $groups = $sensors->groupBy('group.name')->keys()->sort();
                                    @endphp
                                    @foreach($groups as $groupName)
                                        <option value="{{ $groupName }}">{{ $groupName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select id="statusFilter" class="form-select form-select-sm">
                                    <option value="">Todos los estados</option>
                                    <option value="al_dia">Al día</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="vencido">Vencido</option>
                                    <option value="sin_medicion">Sin medición</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" id="searchFilter" class="form-control form-control-sm" placeholder="Nombre o identificador...">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button class="btn btn-sm btn-outline-secondary w-100" id="clearFiltersBtn">
                                    <i class="bi bi-x-lg"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Formulario para medición masiva --}}
                    <form id="bulkMeasurementForm" method="POST" action="{{ route('bulk-measurements.start') }}">
                        @csrf
                        <input type="hidden" name="sensor_ids" id="sensorIdsInput" value="">
                        
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
                                    <th style="width: 80px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sensors as $sensor)
                                    @php
                                        $lastMeasurement = $sensor->lastMeasurement;
                                        
                                        // Obtener el campo principal de la plantilla
                                        $mainField = 'valor';
                                        if ($sensor->group && $sensor->group->template && isset($sensor->group->template->schema['campos'])) {
                                            foreach ($sensor->group->template->schema['campos'] as $campo) {
                                                if ($campo['tipo'] === 'numero' && ($campo['requerido'] ?? false)) {
                                                    $mainField = $campo['nombre'];
                                                    break;
                                                }
                                            }
                                        }
                                        
                                        $lastValue = $lastMeasurement ? ($lastMeasurement->data[$mainField] ?? $lastMeasurement->data['consumo_m3'] ?? $lastMeasurement->data['valor'] ?? 'N/A') : 'N/A';
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
                                        
                                        $isMarked = in_array($sensor->id, $markedSensorIds);
                                    @endphp
                                    <tr data-sensor-id="{{ $sensor->id }}" 
                                        data-sensor-name="{{ strtolower($sensor->name) }}"
                                        data-group="{{ $sensor->group->name ?? '' }}"
                                        data-status="{{ strtolower(str_replace(' ', '_', $estado)) }}"
                                        data-identifier="{{ strtolower($sensor->identifier) }}"
                                        class="{{ $isMarked ? 'selected-row' : '' }}">
                                        <td>
                                            <input type="checkbox" class="sensor-checkbox" 
                                                   data-sensor-id="{{ $sensor->id }}" 
                                                   {{ $isMarked ? 'checked' : '' }}>
                                        </td>
                                        <td>{{ $sensor->id }}</td>
                                        <td class="text-left"><strong>{{ $sensor->name }}</strong></td>
                                        <td><code>{{ $sensor->identifier }}</code></td>
                                        <td>{{ $sensor->group->name ?? 'Sin grupo' }}</td>
                                        <td>{{ $lastDate }}</td>
                                        <td>{{ is_numeric($lastValue) ? number_format($lastValue, 2) : $lastValue }}</td>
                                        <td>
                                            <span class="badge bg-{{ $estadoClass }}">{{ $estado }}</span>
                                        </td>
                                        <td style="width: 80px;">
                                            <a href="{{ route('measurements.create', $sensor->id) }}" 
                                               class="btn btn-sm btn-success" 
                                               title="Tomar medición individual">
                                                <i class="bi bi-rulers"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
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
                        <a href="{{ route('measurements.inspector') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                        </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-rulers"></i> Confirmar Medición Masiva
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-3">
                    <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">¿Estás seguro?</h5>
                    <p class="text-muted">
                        Vas a tomar mediciones para <strong id="modalSelectedCount">0</strong> sensores.
                        <br>
                        El sistema te guiará a través de cada sensor en secuencia ordenada por ID.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" id="confirmStartBtn" class="btn btn-primary">
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
    const $sensorCheckboxes = $('.sensor-checkbox');
    const $selectAllBtn = $('#selectAllBtn');
    const $deselectAllBtn = $('#deselectAllBtn');
    const $startBulkBtn = $('#startBulkBtn');
    const $selectedCount = $('#selectedCount');
    const $modalSelectedCount = $('#modalSelectedCount');
    const $groupFilter = $('#groupFilter');
    const $statusFilter = $('#statusFilter');
    const $searchFilter = $('#searchFilter');
    const $clearFiltersBtn = $('#clearFiltersBtn');
    const $sensorRows = $('#sensorsTable tbody tr');
    const $useSelectionOrder = $('#useSelectionOrder');
    
    // Array para mantener el orden de selección manual
    let selectionOrder = [];

    // Inicializar contador
    updateSelectedCount();

    // Seleccionar/Deseleccionar checkbox individual
    $sensorCheckboxes.change(function() {
        const $row = $(this).closest('tr');
        const sensorId = $(this).data('sensor-id');
        
        if ($(this).is(':checked')) {
            $row.addClass('selected-row');
            // Agregar al final del array de orden de selección
            if (!selectionOrder.includes(sensorId)) {
                selectionOrder.push(sensorId);
            }
        } else {
            $row.removeClass('selected-row');
            // Remover del array de orden de selección
            selectionOrder = selectionOrder.filter(id => id !== sensorId);
        }
        updateSelectedCount();
    });

    // Seleccionar todos
    $('#selectAllCheckbox').change(function() {
        const isChecked = $(this).is(':checked');
        $sensorCheckboxes.prop('checked', isChecked);
        $sensorRows.each(function() {
            if (isChecked) {
                $(this).addClass('selected-row');
            } else {
                $(this).removeClass('selected-row');
            }
        });
        
        // Actualizar el orden de selección
        if (isChecked) {
            selectionOrder = $sensorCheckboxes.map(function() {
                return $(this).data('sensor-id');
            }).get();
        } else {
            selectionOrder = [];
        }
        
        updateSelectedCount();
    });

    // Botón Seleccionar Todos
    $selectAllBtn.click(function() {
        $('#selectAllCheckbox').prop('checked', true).trigger('change');
    });

    // Botón Deseleccionar Todos
    $deselectAllBtn.click(function() {
        $('#selectAllCheckbox').prop('checked', false).trigger('change');
    });

    // Comenzar medición masiva
    $startBulkBtn.click(function() {
        const selectedCount = getSelectedCount();
        if (selectedCount === 0) {
            showAlert('Debes seleccionar al menos un sensor.', 'warning');
            return;
        }
        
        // Obtener los IDs de los sensores seleccionados
        const selectedSensorIds = $sensorCheckboxes.filter(':checked').map(function() {
            return $(this).data('sensor-id');
        }).get();
        
        // Guardar los IDs en el campo oculto
        $('#sensorIdsInput').val(selectedSensorIds.join(','));
        
        // Guardar el orden de selección
        const useSelectionOrder = $useSelectionOrder.is(':checked');
        if (useSelectionOrder && selectionOrder.length > 0) {
            $('#bulkMeasurementForm').append(`
                <input type="hidden" name="selection_order" value="${selectionOrder.join(',')}">
                <input type="hidden" name="use_selection_order" value="1">
            `);
        } else {
            $('#bulkMeasurementForm').append(`
                <input type="hidden" name="use_selection_order" value="0">
            `);
        }
        
        $modalSelectedCount.text(selectedCount);
        $('#confirmModal').modal('show');
    });
    
    // Confirmar desde el modal
    $('#confirmStartBtn').click(function() {
        $('#bulkMeasurementForm').submit();
    });

    // Limpiar filtros
    $clearFiltersBtn.click(function() {
        $groupFilter.val('');
        $statusFilter.val('');
        $searchFilter.val('');
        applyFilters();
    });

    // Actualizar contador de seleccionados
    function updateSelectedCount() {
        const count = getSelectedCount();
        $selectedCount.text(count + ' seleccionados');
        $startBulkBtn.prop('disabled', count === 0);
    }

    // Obtener cantidad de seleccionados
    function getSelectedCount() {
        return $sensorCheckboxes.filter(':checked').length;
    }

    // Filtros
    function applyFilters() {
        const group = $groupFilter.val().toLowerCase();
        const status = $statusFilter.val().toLowerCase();
        const search = $searchFilter.val().toLowerCase();

        $sensorRows.each(function() {
            const $row = $(this);
            const rowGroup = $row.data('group').toLowerCase();
            const rowStatus = $row.data('status').toLowerCase();
            const rowName = $row.data('sensor-name').toLowerCase();
            const rowIdentifier = $row.data('identifier').toLowerCase();

            let show = true;

            if (group && rowGroup !== group) {
                show = false;
            }

            if (status && rowStatus !== status) {
                show = false;
            }

            if (search && !rowName.includes(search) && !rowIdentifier.includes(search)) {
                show = false;
            }

            $row.toggle(show);
        });
    }

    $groupFilter.change(applyFilters);
    $statusFilter.change(applyFilters);
    $searchFilter.on('input', applyFilters);

    // Mostrar alertas
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
});
</script>
@endpush
