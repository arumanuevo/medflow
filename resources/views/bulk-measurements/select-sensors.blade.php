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

        .table-actions {
            display: flex;
            gap: 3px;
            justify-content: center;
            flex-wrap: nowrap;
        }

        .table-actions .btn {
            padding: 0.15rem 0.3rem;
            font-size: 0.65rem;
            line-height: 1.2;
            border-radius: 4px;
            min-width: 26px;
        }

        .pagination-wrapper {
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }

        .pagination-info {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .pagination-info strong {
            color: #212529;
            font-weight: 600;
        }

        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pagination-controls .per-page-select {
            padding: 0.25rem 2rem 0.25rem 0.5rem;
            font-size: 0.85rem;
            border-radius: 4px;
            border: 1px solid #ced4da;
            background-color: white;
            cursor: pointer;
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
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-sensors"></i> {{ $sensors->count() }} sensores
                                </span>
                                <button type="button" class="btn btn-sm btn-light" id="inviteInspectorBtn"
                                    title="Invitar Inspector a App Móvil">
                                    📱 Invitar Inspector
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- ✅ MODAL: Advertencia - no hay sensores seleccionados --}}
                    <div class="modal fade" id="noSelectionModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header bg-warning text-dark border-0">
                                    <h5 class="modal-title">⚠️ Sin sensores seleccionados</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Debes seleccionar al menos un sensor antes de invitar a un inspector móvil.</p>
                                    <p class="text-muted small">Usá los checkboxes de la tabla para marcar los sensores que el inspector podrá medir.</p>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Entendido</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ✅ MODAL: Invitación a App Móvil --}}
                    <div class="modal fade" id="mobileInviteModal" tabindex="-1" aria-labelledby="mobileInviteModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header bg-dark text-white border-0">
                                    <h5 class="modal-title" id="mobileInviteModalLabel">📱 Invitar Inspector a App Móvil
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white"
                                        data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="text-muted small mb-3">
                                        Se generará un enlace de acceso temporal y se enviará al correo del operario.
                                        Al tocarlo desde su celular, se autorizará automáticamente en la App de MedFlow
                                        Inspector.
                                    </p>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Email del Inspector</label>
                                        <input type="email" id="inviteEmail" class="form-control"
                                            placeholder="inspector@empresa.com">
                                    </div>
                                    <input type="hidden" id="inviteLimit" value="0">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Sensores seleccionados</label>
                                        <div class="form-control-plaintext text-primary fw-semibold" id="inviteSelectedCount">0</div>
                                        <div class="form-text">Solo el inspector podrá medir estos sensores.</div>
                                    </div>
                                    <div id="inviteResultMsg" class="d-none"></div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-primary btn-sm" id="sendInviteBtn">
                                        <span id="sendInviteBtnText">Enviar Enlace de Acceso</span>
                                        <span id="sendInviteBtnSpinner"
                                            class="spinner-border spinner-border-sm d-none ms-1"></span>
                                    </button>
                                </div>
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

                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        {{-- Información del espacio --}}
                        <div class="alert alert-secondary">
                            <i class="bi bi-briefcase"></i>
                            Estás en el espacio de <strong>{{ $ownerName ?? 'Propietario' }}</strong>
                            <br>
                            <small class="text-muted">Selecciona los sensores que deseas medir y usa la acción
                                masiva.</small>
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
                                    <span class="text-muted small ms-1" data-bs-toggle="tooltip"
                                        title="Si está marcado, los sensores se medirán en el orden en que los seleccionaste. Si no, se ordenarán por ID ascendente.">
                                        <i class="bi bi-info-circle"></i>
                                    </span>
                                </div>

                                <button class="btn btn-sm btn-primary" id="startBulkBtn" disabled>
                                    <i class="bi bi-rulers"></i> Comenzar Medición Masiva
                                </button>
                                {{-- ✅ BOTÓN PARA IMPORTAR MEDICIONES (Restringido) --}}
                                @if($permissions['import_sensors'] ?? false)
                                    <a href="{{ route('measurements.bulk-import') }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-file-earmark-excel"></i> Importar Mediciones
                                    </a>
                                @else
                                    <a href="#" class="btn btn-sm btn-warning gate-blocked"
                                        onclick="showGateBlockedNotification(); return false;">
                                        <i class="bi bi-file-earmark-excel"></i> Importar Mediciones
                                    </a>
                                @endif
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
                                    <input type="text" id="searchFilter" class="form-control form-control-sm"
                                        placeholder="Nombre o identificador...">
                                </div>
                                <div class="col-md-3 d-flex align-items-end gap-2">
                                    <button type="button" class="btn btn-sm btn-primary w-50" id="filterBtn">
                                        <i class="bi bi-funnel"></i> Filtrar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary w-50"
                                        id="clearFiltersBtn">
                                        <i class="bi bi-x-lg"></i> Limpiar
                                    </button>
                                </div>
                                <div class="col-md-12 mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-success" id="filterCommunityBtn">
                                        <i class="bi bi-tree-fill"></i> Ver solo Áreas Comunes
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
                                                $isLocked = !($sensor->is_measurable ?? true); // Candado limitante
                                            @endphp
                                            <tr data-sensor-id="{{ $sensor->id }}"
                                                data-sensor-name="{{ strtolower($sensor->name) }}"
                                                data-group="{{ $sensor->group->name ?? '' }}"
                                                data-status="{{ strtolower(str_replace(' ', '_', $estado)) }}"
                                                data-identifier="{{ strtolower($sensor->identifier) }}"
                                                data-community="{{ $sensor->is_community ? '1' : '0' }}"
                                                class="{{ $isLocked ? 'table-secondary opacity-75' : '' }}">
                                                <td>
                                                    @if($isLocked)
                                                        <i class="bi bi-lock-fill text-danger fs-5 ms-2"
                                                            title="Bloqueado por límite de sensores de Plan"></i>
                                                    @else
                                                        <input type="checkbox" class="sensor-checkbox"
                                                            data-sensor-id="{{ $sensor->id }}">
                                                    @endif
                                                </td>
                                                <td class="text-left">
                                                    <strong>{{ $sensor->name }}</strong>
                                                    @if($sensor->is_community)
                                                        <span class="badge bg-success ms-1" style="font-size:0.6rem;"><i
                                                                class="bi bi-tree-fill"></i> Común</span>
                                                    @endif
                                                    @if($isLocked)
                                                        <span
                                                            class="badge bg-danger bg-opacity-10 text-danger border border-danger ms-1"
                                                            style="font-size: 0.65rem;" title="Límite del Plan Superado"><i
                                                                class="bi bi-lock-fill"></i> Bloqueado</span>
                                                    @endif
                                                </td>
                                                <td><code>{{ $sensor->identifier }}</code></td>
                                                <td>{{ $sensor->group->name ?? 'Sin grupo' }}</td>
                                                <td>{{ $lastDate }}</td>
                                                <td>{{ is_numeric($lastValue) ? number_format($lastValue, 2) : $lastValue }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $estadoClass }}">{{ $estado }}</span>
                                                </td>
                                                <td style="width: 80px;">
                                                    <div class="table-actions">
                                                        <a href="{{ route('measurements.create', $sensor->id) }}"
                                                            class="btn btn-success" title="Tomar medición individual">
                                                            <i class="bi bi-rulers"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                                                    <h5 class="mt-2">No hay sensores disponibles</h5>
                                                    <p class="text-muted">El propietario de este espacio aún no ha creado
                                                        sensores.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Paginación Client-Side --}}
                            <div class="pagination-wrapper px-3 py-2 border rounded shadow-sm bg-white mb-3 mt-2">
                                <div class="pagination-info text-muted small" id="paginationInfo">
                                    Mostrando <strong id="pageStart">0</strong> - <strong id="pageEnd">0</strong> de <strong
                                        id="totalRecords">0</strong> sensores
                                </div>
                                <div class="pagination-controls d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center gap-1">
                                        <select class="form-select form-select-sm w-auto" id="perPageSelect">
                                            <option value="10">10</option>
                                            <option value="25" selected>25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                        <span class="text-muted small">por pág</span>
                                    </div>
                                    <div class="btn-group" id="paginationButtons">
                                        <button class="btn btn-outline-secondary btn-sm" id="pagePrev" disabled>
                                            <i class="bi bi-chevron-left"></i>
                                        </button>
                                        <span class="d-flex align-items-center px-2 small bg-light border-top border-bottom"
                                            id="pageInfo">Página 1</span>
                                        <button class="btn btn-outline-secondary btn-sm" id="pageNext" disabled>
                                            <i class="bi bi-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>
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
        $(document).ready(function () {
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
            $sensorCheckboxes.change(function () {
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
            $('#selectAllCheckbox').change(function () {
                const isChecked = $(this).is(':checked');
                $sensorCheckboxes.prop('checked', isChecked);
                $sensorRows.each(function () {
                    if (isChecked) {
                        $(this).addClass('selected-row');
                    } else {
                        $(this).removeClass('selected-row');
                    }
                });

                // Actualizar el orden de selección
                if (isChecked) {
                    selectionOrder = $sensorCheckboxes.map(function () {
                        return $(this).data('sensor-id');
                    }).get();
                } else {
                    selectionOrder = [];
                }

                updateSelectedCount();
            });

            // Botón Seleccionar Todos
            $selectAllBtn.click(function () {
                $('#selectAllCheckbox').prop('checked', true).trigger('change');
            });

            // Botón Deseleccionar Todos
            $deselectAllBtn.click(function () {
                $('#selectAllCheckbox').prop('checked', false).trigger('change');
            });

            // Comenzar medición masiva
            $startBulkBtn.click(function () {
                const selectedCount = getSelectedCount();
                if (selectedCount === 0) {
                    showAlert('Debes seleccionar al menos un sensor.', 'warning');
                    return;
                }

                // Obtener los IDs de los sensores seleccionados
                const selectedSensorIds = $sensorCheckboxes.filter(':checked').map(function () {
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
            $('#confirmStartBtn').click(function () {
                $('#bulkMeasurementForm').submit();
            });

            // Limpiar filtros
            $clearFiltersBtn.click(function () {
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

            // Estado del filtro de comunidad
            let filterOnlyCommunity = false;

            $('#filterCommunityBtn').click(function (e) {
                e.preventDefault();
                filterOnlyCommunity = !filterOnlyCommunity;

                if (filterOnlyCommunity) {
                    $(this).removeClass('btn-outline-success').addClass('btn-success');
                } else {
                    $(this).removeClass('btn-success').addClass('btn-outline-success');
                }
                applyFilters();
            });

            // Paginación Globales
            let currentPage = 1;
            let itemsPerPage = 25;

            // Filtros
            function applyFilters() {
                const group = $groupFilter.val().toLowerCase();
                const status = $statusFilter.val().toLowerCase();
                const search = $searchFilter.val().toLowerCase();

                let visibleRows = [];

                $sensorRows.each(function () {
                    const $row = $(this);

                    // SAFE CASTING TO STRING TO AVOID .toLowerCase() TypeError on INT values
                    const rowGroup = String($row.data('group') || '').toLowerCase();
                    const rowStatus = String($row.data('status') || '').toLowerCase();
                    const rowName = String($row.data('sensor-name') || '').toLowerCase();
                    const rowIdentifier = String($row.data('identifier') || '').toLowerCase();
                    const isCommunity = String($row.data('community') || '0');

                    let show = true;

                    if (filterOnlyCommunity && isCommunity !== '1') show = false;
                    if (group && rowGroup !== group) show = false;
                    if (status && rowStatus !== status) show = false;
                    if (search && !rowName.includes(search) && !rowIdentifier.includes(search)) show = false;

                    if (show) {
                        visibleRows.push(this);
                    }
                    $row.toggle(false); // Ocultar todo inicialmente
                });

                // Cálculos de Paginación
                const total = visibleRows.length;
                const totalPages = Math.ceil(total / itemsPerPage) || 1;

                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;

                const start = (currentPage - 1) * itemsPerPage;
                const end = start + itemsPerPage;

                // Mostrar solo el segmento correspondiente
                $(visibleRows).slice(start, end).toggle(true);

                // Actualizar interfaz
                $('#pageStart').text(total === 0 ? 0 : start + 1);
                $('#pageEnd').text(Math.min(end, total));
                $('#totalRecords').text(total);
                $('#pageInfo').text(`Página ${currentPage} de ${totalPages}`);

                $('#pagePrev').prop('disabled', currentPage <= 1);
                $('#pageNext').prop('disabled', currentPage >= totalPages);
            }

            // Bind de Eventos Paginación
            $('#pagePrev').click(function (e) { e.preventDefault(); currentPage--; applyFilters(); });
            $('#pageNext').click(function (e) { e.preventDefault(); currentPage++; applyFilters(); });
            $('#perPageSelect').change(function () { itemsPerPage = parseInt($(this).val()); currentPage = 1; applyFilters(); });

            $('#filterBtn').click(applyFilters);
            $groupFilter.change(applyFilters);
            $statusFilter.change(applyFilters);
            $searchFilter.on('input', applyFilters);

            // Iniciar paginación en el renderizado inicial
            applyFilters();

            // ✅ BOTÓN: Abrir modal de invitación SOLO si hay sensores seleccionados
            document.getElementById('inviteInspectorBtn').addEventListener('click', function () {
                const selectedCount = getSelectedCount();
                if (selectedCount === 0) {
                    // Mostrar modal de advertencia
                    const $noSelectionModal = new bootstrap.Modal(document.getElementById('noSelectionModal'));
                    $noSelectionModal.show();
                    return;
                }
                // Abrir modal de invitación y setear el count
                const $inviteModal = new bootstrap.Modal(document.getElementById('mobileInviteModal'));
                document.getElementById('inviteLimit').value = selectedCount;
                document.getElementById('inviteSelectedCount').textContent = selectedCount + ' sensores';
                $inviteModal.show();
            });

            // ✅ MODAL: Enviar invitación de acceso a Inspector Móvil
            document.getElementById('sendInviteBtn').addEventListener('click', function () {
                const email = document.getElementById('inviteEmail').value.trim();
                const limit = parseInt(document.getElementById('inviteLimit').value) || 0;
                const selectedCount = getSelectedCount();
                if (selectedCount === 0) {
                    // No debería pasar porque el botón de abrir el modal ya valida,
                    // pero por seguridad
                    document.getElementById('inviteResultMsg').innerHTML = '<div class="alert alert-warning py-2">No hay sensores seleccionados.</div>';
                    document.getElementById('inviteResultMsg').classList.remove('d-none');
                    return;
                }
                // Usar la cantidad de seleccionados como límite
                document.getElementById('inviteLimit').value = selectedCount;
                const msgDiv = document.getElementById('inviteResultMsg');
                const btn = this;
                const spinner = document.getElementById('sendInviteBtnSpinner');
                const btnText = document.getElementById('sendInviteBtnText');

                if (!email) {
                    msgDiv.innerHTML = '<div class="alert alert-warning py-2">Por favor ingresá un email válido.</div>';
                    msgDiv.classList.remove('d-none');
                    return;
                }

                btn.disabled = true;
                spinner.classList.remove('d-none');
                btnText.textContent = 'Enviando...';
                msgDiv.classList.add('d-none');

                const authToken = localStorage.getItem('token');
                if (!authToken) {
                    msgDiv.innerHTML = '<div class="alert alert-warning py-2">No hay sesión activa. Por favor, volvé a iniciar sesión e intentá nuevamente.</div>';
                    msgDiv.classList.remove('d-none');
                    return;
                }

                fetch('/api/mobile/v1/invite', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({ email: email, sensor_limit: limit })
                })
                    .then(async res => {
                        const text = await res.text();
                        if (!res.ok) {
                            // Mostrar HTTP status y snippet de la respuesta para debug
                            let snippet = text.substring(0, 300).replace(/</g, '&lt;');
                            msgDiv.innerHTML = `<div class="alert alert-danger py-2">❌ Error HTTP <strong>${res.status}</strong>:<br><code style="font-size:11px">${snippet}</code></div>`;
                            msgDiv.classList.remove('d-none');
                            return null;
                        }
                        try { return JSON.parse(text); } catch { return null; }
                    })
                    .then(data => {
                        if (!data) return;
                        if (data.success) {
                            msgDiv.innerHTML = `<div class="alert alert-success py-2">✅ Enlace enviado a <strong>${email}</strong>.</div>`;
                        } else {
                            msgDiv.innerHTML = `<div class="alert alert-danger py-2">❌ ${data.message ?? 'Error al enviar.'}</div>`;
                        }
                        msgDiv.classList.remove('d-none');
                    })
                    .catch(err => {
                        msgDiv.innerHTML = `<div class="alert alert-danger py-2">❌ Error de red: ${err.message}</div>`;
                        msgDiv.classList.remove('d-none');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        spinner.classList.add('d-none');
                        btnText.textContent = 'Enviar Enlace de Acceso';
                    });
            });

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
                    $('.alert').first().fadeOut(500, function () {
                        $(this).remove();
                    });
                }, 5000);
            }
        });
    </script>
@endpush