@extends('layouts.modern')

@section('title', 'Seleccionar Sensores - Medición Masiva')

@push('styles')
<style>
    .sensor-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        transition: all 0.3s ease;
        cursor: pointer;
        margin-bottom: 1rem;
    }
    .sensor-card:hover {
        border-color: #0d6efd;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
    }
    .sensor-card.selected {
        border-color: #0d6efd;
        background-color: #e7f1ff;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25);
    }
    .sensor-card .card-body {
        padding: 1.25rem;
    }
    .sensor-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    .sensor-name {
        font-weight: 600;
        font-size: 1.1rem;
        color: #1a202c;
    }
    .sensor-meta {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .sensor-status {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-al-dia {
        background-color: #d1e7dd;
        color: #155724;
    }
    .status-pendiente {
        background-color: #fff3cd;
        color: #856404;
    }
    .status-vencido {
        background-color: #f8d7da;
        color: #721c24;
    }
    .status-sin-medicion {
        background-color: #e2e3e5;
        color: #383d41;
    }
    .selection-controls {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }
    .btn-bulk-primary {
        background: linear-gradient(135deg, #0d6efd, #0a5fd9);
        border: none;
        padding: 0.75rem 2rem;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
        color: #fff;
        font-size: 1rem;
    }
    .btn-bulk-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(13, 110, 253, 0.35);
        color: #fff;
    }
    .selected-count {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0d6efd;
    }
    .sensor-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1rem;
    }
    @media (max-width: 768px) {
        .sensor-grid {
            grid-template-columns: 1fr;
        }
    }
    .checkbox-container {
        position: absolute;
        top: 1rem;
        right: 1rem;
        z-index: 1;
    }
    .checkbox-container input[type="checkbox"] {
        width: 24px;
        height: 24px;
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-rulers text-primary"></i> 
                        Medición Masiva
                    </h2>
                    <p class="text-muted mb-0">
                        Selecciona los sensores para tomar mediciones en secuencia
                    </p>
                </div>
                <div>
                    <a href="{{ route('measurements.inspector') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <!-- Info del espacio -->
            <div class="alert alert-info mb-4">
                <i class="bi bi-briefcase"></i> 
                Espacio de: <strong>{{ $ownerName }}</strong>
                @if(!$isOwner)
                    <span class="badge bg-warning text-dark ms-2">Colaborador</span>
                @endif
            </div>

            <!-- Controles de selección -->
            <div class="selection-controls">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <button type="button" id="selectAllBtn" class="btn btn-outline-primary">
                            <i class="bi bi-check-all"></i> Todos
                        </button>
                        <button type="button" id="deselectAllBtn" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Ninguno
                        </button>
                        <span class="text-muted">
                            <span id="selectedCount" class="selected-count">0</span> 
                            seleccionados de {{ $sensors->count() }}
                        </span>
                    </div>
                    <div>
                        <button type="button" id="startBulkBtn" class="btn btn-bulk-primary" disabled>
                            <i class="bi bi-rulers"></i> 
                            Comenzar Medición Masiva
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Grupo</label>
                            <select id="groupFilter" class="form-select">
                                <option value="">Todos los grupos</option>
                                @php
                                    $groups = $sensors->groupBy('group.name')->keys()->sort();
                                @endphp
                                @foreach($groups as $groupName)
                                    <option value="{{ $groupName }}">{{ $groupName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Estado</label>
                            <select id="statusFilter" class="form-select">
                                <option value="">Todos los estados</option>
                                <option value="al_dia">Al día</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="vencido">Vencido</option>
                                <option value="sin_medicion">Sin medición</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Buscar</label>
                            <input type="text" id="searchFilter" class="form-control" placeholder="Nombre o identificador...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de sensores -->
            <form id="bulkMeasurementForm" method="POST" action="{{ route('bulk-measurements.start') }}">
                @csrf
                <div class="sensor-grid" id="sensorGrid">
                    @foreach($sensors as $sensor)
                        @php
                            $lastMeasurement = $sensor->lastMeasurement;
                            $lastValue = $lastMeasurement ? ($sensor->group->template && isset($sensor->group->template->schema['campos']) ? 
                                ($lastMeasurement->data[$this->getMainField($sensor)] ?? 'N/A') : 
                                ($lastMeasurement->data['consumo_m3'] ?? $lastMeasurement->data['valor'] ?? 'N/A')) : 'N/A';
                            $lastDate = $lastMeasurement ? \Carbon\Carbon::parse($lastMeasurement->measured_at)->format('d/m/Y') : 'N/A';
                            
                            // Calcular estado
                            $estado = 'sin_medicion';
                            $estadoClass = 'status-sin-medicion';
                            if ($lastMeasurement) {
                                $periodoMedicion = $sensor->group->periodo_medicion ?? 30;
                                $diasVencimiento = $sensor->group->dias_vencimiento ?? 5;
                                $proximaMedicion = \Carbon\Carbon::parse($lastMeasurement->measured_at)->addDays($periodoMedicion);
                                $diasRestantes = now()->diffInDays($proximaMedicion, false);
                                
                                if ($diasRestantes < 0) {
                                    $estado = 'vencido';
                                    $estadoClass = 'status-vencido';
                                } elseif ($diasRestantes <= $diasVencimiento) {
                                    $estado = 'pendiente';
                                    $estadoClass = 'status-pendiente';
                                } else {
                                    $estado = 'al_dia';
                                    $estadoClass = 'status-al-dia';
                                }
                            }
                            
                            $isMarked = in_array($sensor->id, $markedSensorIds);
                        @endphp
                        
                        <div class="card sensor-card {{ $isMarked ? 'selected' : '' }}" 
                             data-sensor-id="{{ $sensor->id }}"
                             data-group="{{ $sensor->group->name ?? '' }}"
                             data-status="{{ $estado }}"
                             data-name="{{ strtolower($sensor->name) }}"
                             data-identifier="{{ strtolower($sensor->identifier) }}">
                            <div class="checkbox-container">
                                <input type="checkbox" 
                                       name="sensor_ids[]" 
                                       value="{{ $sensor->id }}"
                                       id="sensor_{{ $sensor->id }}"
                                       {{ $isMarked ? 'checked' : '' }}>
                            </div>
                            <div class="card-body">
                                <div class="sensor-info-row">
                                    <span class="sensor-name">{{ $sensor->name }}</span>
                                    <span class="sensor-status {{ $estadoClass }}">
                                        @if($estado == 'al_dia')
                                            <i class="bi bi-check-circle-fill"></i>
                                        @elseif($estado == 'pendiente')
                                            <i class="bi bi-exclamation-circle-fill"></i>
                                        @elseif($estado == 'vencido')
                                            <i class="bi bi-x-circle-fill"></i>
                                        @else
                                            <i class="bi bi-dash-circle-fill"></i>
                                        @endif
                                        {{ ucfirst(str_replace('_', ' ', $estado)) }}
                                    </span>
                                </div>
                                <div class="sensor-meta">
                                    <strong>ID:</strong> {{ $sensor->identifier }} | 
                                    <strong>Grupo:</strong> {{ $sensor->group->name ?? 'Sin grupo' }}
                                </div>
                                <div class="sensor-meta">
                                    <strong>Última medición:</strong> {{ $lastDate }} 
                                    @if($lastValue !== 'N/A')
                                        | <strong>Valor:</strong> {{ is_numeric($lastValue) ? number_format($lastValue, 2) : $lastValue }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Hidden inputs para mantener el estado -->
                <input type="hidden" name="active_workspace" value="{{ $activeWorkspace }}">
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-rulers"></i> 
                    Confirmar Medición Masiva
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
                        El sistema te guiará a través de cada sensor en secuencia.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="submit" form="bulkMeasurementForm" class="btn btn-primary">
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
    const $sensorCards = $('.sensor-card');
    const $selectAllBtn = $('#selectAllBtn');
    const $deselectAllBtn = $('#deselectAllBtn');
    const $startBulkBtn = $('#startBulkBtn');
    const $selectedCount = $('#selectedCount');
    const $modalSelectedCount = $('#modalSelectedCount');
    const $groupFilter = $('#groupFilter');
    const $statusFilter = $('#statusFilter');
    const $searchFilter = $('#searchFilter');

    // Inicializar contador
    updateSelectedCount();

    // Seleccionar/Deseleccionar tarjeta
    $sensorCards.on('click', function(e) {
        // Si el click fue en el checkbox, no hacer nada (el checkbox ya se manejó)
        if ($(e.target).is('input[type="checkbox"]')) {
            updateSelectedCount();
            return;
        }
        
        // Si el click fue en la tarjeta, toggle el checkbox
        const $checkbox = $(this).find('input[type="checkbox"]');
        $checkbox.prop('checked', !$checkbox.prop('checked'));
        $(this).toggleClass('selected');
        updateSelectedCount();
    });

    // Seleccionar todos
    $selectAllBtn.click(function() {
        $sensorCards.each(function() {
            $(this).addClass('selected');
            $(this).find('input[type="checkbox"]').prop('checked', true);
        });
        updateSelectedCount();
    });

    // Deseleccionar todos
    $deselectAllBtn.click(function() {
        $sensorCards.each(function() {
            $(this).removeClass('selected');
            $(this).find('input[type="checkbox"]').prop('checked', false);
        });
        updateSelectedCount();
    });

    // Comenzar medición masiva
    $startBulkBtn.click(function() {
        const selectedCount = getSelectedCount();
        if (selectedCount === 0) {
            showAlert('Debes seleccionar al menos un sensor.', 'warning');
            return;
        }
        
        $modalSelectedCount.text(selectedCount);
        $('#confirmModal').modal('show');
    });

    // Actualizar contador de seleccionados
    function updateSelectedCount() {
        const count = getSelectedCount();
        $selectedCount.text(count);
        $startBulkBtn.prop('disabled', count === 0);
    }

    // Obtener cantidad de seleccionados
    function getSelectedCount() {
        return $('input[name="sensor_ids[]"]:checked').length;
    }

    // Filtros
    function applyFilters() {
        const group = $groupFilter.val().toLowerCase();
        const status = $statusFilter.val().toLowerCase();
        const search = $searchFilter.val().toLowerCase();

        $sensorCards.each(function() {
            const $card = $(this);
            const cardGroup = $card.data('group').toLowerCase();
            const cardStatus = $card.data('status').toLowerCase();
            const cardName = $card.data('name').toLowerCase();
            const cardIdentifier = $card.data('identifier').toLowerCase();

            let show = true;

            if (group && cardGroup !== group) {
                show = false;
            }

            if (status && cardStatus !== status) {
                show = false;
            }

            if (search && !cardName.includes(search) && !cardIdentifier.includes(search)) {
                show = false;
            }

            $card.toggle(show);
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
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('.container').prepend(alertHtml);
        
        setTimeout(() => {
            $('.alert').first().fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Helper para obtener el campo principal (copiado del controlador)
    function getMainField(sensor) {
        if (sensor.group && sensor.group.template && sensor.group.template.schema && sensor.group.template.schema.campos) {
            for (const campo of sensor.group.template.schema.campos) {
                if (campo.tipo === 'numero' && campo.requerido) {
                    return campo.nombre;
                }
            }
        }
        return 'valor';
    }
});
</script>
@endpush
