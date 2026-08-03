@extends('layouts.modern')

@section('title', 'Mis Sensores')

@push('styles')
<style>
    /* ============================================
       ESTILOS PRINCIPALES
       ============================================ */
    .sensor-table-wrapper {
        overflow-x: auto;
    }
    .sensor-table-wrapper table {
        min-width: 600px;
        margin-bottom: 0;
    }
    .sensor-table-wrapper .table th {
        white-space: nowrap;
        font-size: 0.72rem;
        padding: 0.5rem 0.5rem;
        text-align: center !important;
        vertical-align: middle;
        cursor: pointer;
        user-select: none;
        position: relative;
        transition: background-color 0.15s;
        border-bottom: 2px solid #dee2e6;
    }
    .sensor-table-wrapper .table th:hover {
        background-color: rgba(13, 110, 253, 0.06);
    }
    .sensor-table-wrapper .table th:not(.no-sort):hover {
        background-color: rgba(13, 110, 253, 0.08);
    }
    .sensor-table-wrapper .table td {
        font-size: 0.82rem;
        padding: 0.4rem 0.5rem;
        vertical-align: middle;
        text-align: center;
    }
    .sensor-table-wrapper .table td:first-child {
        text-align: center;
    }
    .sensor-table-wrapper .table td:last-child {
        text-align: center;
    }
    .sensor-table-wrapper .table td.text-left {
        text-align: left;
    }

    /* ============================================
       ORDENAMIENTO
       ============================================ */
    .table th .sort-icon {
        display: inline-block;
        margin-left: 3px;
        font-size: 0.55rem;
        opacity: 0.35;
        transition: all 0.2s;
    }
    .table th .sort-icon.active {
        opacity: 1;
        color: #0d6efd;
    }
    .table th .sort-icon.asc {
        opacity: 1;
        color: #0d6efd;
    }
    .table th .sort-icon.desc {
        opacity: 1;
        color: #0d6efd;
        transform: rotate(180deg);
    }
    .sortable-header {
        cursor: pointer;
    }
    .sortable-header.no-sort {
        cursor: default;
    }
    .sortable-header.no-sort:hover {
        background-color: transparent !important;
    }

    /* ============================================
       BADGES Y ETIQUETAS
       ============================================ */
    .badge-extra-field {
        display: inline-block;
        font-size: 0.7rem;
        padding: 0.15rem 0.5rem;
        background: #f1f3f5;
        color: #495057;
        border-radius: 4px;
        border: 1px solid #e9ecef;
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .extra-field-header {
        background-color: #e8f4f8 !important;
        color: #0c5460 !important;
        font-weight: 600 !important;
        border-bottom: 2px solid #17a2b8 !important;
    }
    .extra-field-header i {
        font-size: 0.65rem;
        color: #17a2b8;
        margin-left: 2px;
    }
    .sensor-id-badge {
        font-weight: 600;
        color: #0d6efd;
    }
    .no-data {
        color: #adb5bd;
        font-style: italic;
    }

    /* ============================================
       ACCIONES
       ============================================ */
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

    /* ============================================
       HEADER MEJORADO
       ============================================ */
    .card-header-tools {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }
    .card-header-tools .btn-group .btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.6rem;
    }
    .card-header-tools .btn-group .btn i {
        margin-right: 3px;
    }
    .card-header-tools .dropdown-menu {
        min-width: 200px;
        font-size: 0.8rem;
    }
    .card-header-tools .dropdown-menu .dropdown-item {
        padding: 0.4rem 1rem;
    }
    .card-header-tools .dropdown-menu .dropdown-item i {
        margin-right: 8px;
        width: 18px;
        text-align: center;
    }
    .card-header-tools .dropdown-toggle::after {
        margin-left: 0.4rem;
    }

    /* ============================================
       SUSCRIPCIÓN - ESTILOS ADICIONALES
       ============================================ */
    .subscription-info-bar {
        background: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        padding: 0.5rem 1rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        font-size: 0.8rem;
    }
    .subscription-info-bar .limit-item {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem 0.6rem;
        border-radius: 4px;
        background: #fff;
        border: 1px solid #dee2e6;
    }
    .subscription-info-bar .limit-item .count {
        font-weight: 600;
        color: #0d6efd;
    }
    .subscription-info-bar .limit-item .count.danger {
        color: #dc3545;
    }
    .subscription-info-bar .limit-item .count.warning {
        color: #ffc107;
    }
    .subscription-info-bar .limit-item .badge-plan {
        font-size: 0.65rem;
        padding: 0.15rem 0.5rem;
        border-radius: 12px;
    }
    .subscription-info-bar .limit-item .badge-plan.premium {
        background: #ffd700;
        color: #000;
    }
    .subscription-info-bar .limit-item .badge-plan.basico {
        background: #cfe2ff;
        color: #084298;
    }
    .subscription-info-bar .limit-item .badge-plan.free {
        background: #f8d9da;
        color: #721c24;
    }

    .gate-blocked {
        opacity: 0.6;
        cursor: not-allowed;
        pointer-events: none;
        position: relative;
    }
    .gate-blocked::after {
        content: '🔒 Premium';
        position: absolute;
        top: -8px;
        right: -8px;
        font-size: 0.5rem;
        background: #ffc107;
        color: #000;
        padding: 0.1rem 0.4rem;
        border-radius: 10px;
        font-weight: 600;
        white-space: nowrap;
    }

    .btn-disabled-by-plan {
        opacity: 0.6;
        cursor: not-allowed;
        pointer-events: none;
    }

    .premium-badge {
        font-size: 0.5rem;
        background: #ffc107;
        color: #000;
        padding: 0.1rem 0.4rem;
        border-radius: 10px;
        font-weight: 600;
        margin-left: 0.3rem;
        vertical-align: middle;
    }

    /* ============================================
       BUSCADOR
       ============================================ */
    .search-wrapper {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex: 1;
        min-width: 200px;
        max-width: 400px;
    }
    .search-wrapper .form-control {
        font-size: 0.8rem;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        border: 1px solid #ced4da;
        transition: all 0.2s;
        height: 32px;
    }
    .search-wrapper .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.15);
    }
    .search-wrapper .btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.6rem;
        height: 32px;
        border-radius: 6px;
    }
    .search-wrapper .btn i {
        font-size: 0.85rem;
    }
    .search-clear {
        cursor: pointer;
        opacity: 0.5;
        transition: opacity 0.2s;
        font-size: 1.1rem;
        padding: 0 4px;
    }
    .search-clear:hover {
        opacity: 1;
    }

    /* ============================================
       PAGINACIÓN
       ============================================ */
    .pagination-wrapper {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding-top: 0.75rem;
        border-top: 1px solid #e9ecef;
    }
    .pagination-info {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .pagination-info strong {
        color: #212529;
    }
    .pagination-controls {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .pagination-controls .btn-group .btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.6rem;
        min-width: 32px;
    }
    .pagination-controls .btn-group .btn.active {
        background-color: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }
    .pagination-controls .per-page-select {
        font-size: 0.75rem;
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        border: 1px solid #ced4da;
        height: 30px;
        background-color: #fff;
    }
    .pagination-controls .per-page-select:focus {
        border-color: #0d6efd;
        outline: none;
    }

    /* ============================================
       EMPTY STATE
       ============================================ */
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: #6c757d;
    }
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    .empty-state h4 {
        font-size: 1.25rem;
        margin-bottom: 0.5rem;
    }
    .empty-state .btn {
        margin: 0.25rem;
    }

    /* ============================================
       RESPONSIVE
       ============================================ */
    @media (max-width: 992px) {
        .card-header-tools {
            flex-direction: column;
            align-items: stretch;
        }
        .search-wrapper {
            max-width: 100%;
        }
        .pagination-wrapper {
            flex-direction: column;
            align-items: center;
        }
        .subscription-info-bar {
            flex-direction: column;
            align-items: stretch;
            text-align: center;
        }
    }
    @media (max-width: 768px) {
        .table-actions {
            flex-wrap: wrap;
            justify-content: center;
        }
        .sensor-table-wrapper .table th,
        .sensor-table-wrapper .table td {
            padding: 0.3rem 0.3rem;
            font-size: 0.7rem;
        }
        .pagination-controls {
            flex-wrap: wrap;
            justify-content: center;
        }
        .subscription-info-bar .limit-item {
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="card">
        <!-- ============================================
        HEADER MEJORADO
        ============================================ -->
        <div class="card-header bg-primary text-white">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h4 class="mb-0 d-flex align-items-center gap-2">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Mis Sensores</span>
                    <span id="totalCountBadge" class="badge bg-light text-dark ms-2" style="font-size: 0.7rem;">0</span>
                </h4>

                <div class="card-header-tools">
                    <!-- BUSCADOR -->
                    <div class="search-wrapper">
                        <div class="position-relative w-100">
                            <input type="text" class="form-control" id="searchInput" placeholder="🔍 Buscar sensores...">
                            <span class="search-clear position-absolute end-0 top-50 translate-middle-y me-2 d-none" id="searchClear">&times;</span>
                        </div>
                    </div>

                    <!-- MENÚ DE ACCIONES -->
                    <div class="btn-group">
                        <button type="button" class="btn btn-light btn-sm dropdown-toggle" id="actionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-plus-circle"></i> Acciones
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdown">
                            <li>
                                <a href="{{ route('sensors.create') }}" class="dropdown-item" id="createSensorLink">
                                    <i class="bi bi-plus-circle text-success"></i> Crear Sensor
                                </a>
                            </li>
                            <li>
                                {{-- ✅ IMPORTACIÓN - depende de permisos --}}
                                @if($permissions['import_sensors'] ?? false)
                                    <a href="{{ route('sensor-groups.bulk-import') }}" class="dropdown-item" id="importSensorsLink">
                                        <i class="bi bi-file-earmark-excel text-info"></i> Importar Sensores
                                    </a>
                                @else
                                    <a href="#" class="dropdown-item text-muted" id="importSensorsLink" 
                                    onclick="showGateBlockedNotification(); return false;">
                                        <i class="bi bi-file-earmark-excel text-info"></i> Importar Sensores
                                        <span class="badge bg-warning text-dark ms-1">Premium</span>
                                    </a>
                                @endif
                            </li>
                            <!-- ... resto del menú ... -->
                        </ul>
                    </div>

                    <button class="btn btn-light btn-sm" id="refreshBtnSmall" title="Recargar">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================
        BARRA DE INFORMACIÓN DE SUSCRIPCIÓN
        ============================================ -->
        <div class="subscription-info-bar" id="subscriptionInfoBar">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="limit-item">
                    <i class="bi bi-credit-card"></i>
                    Plan: <span id="planName" class="badge-plan">Cargando...</span>
                </span>
                <span class="limit-item">
                    <i class="bi bi-speedometer2"></i>
                    Sensores: <span id="sensorLimit" class="count">...</span>
                </span>
                <span class="limit-item">
                    <i class="bi bi-folder"></i>
                    Grupos: <span id="groupLimit" class="count">...</span>
                </span>
                <span class="limit-item" id="collaboratorLimitItem">
                    <i class="bi bi-people"></i>
                    Colaboradores: <span id="collaboratorLimit" class="count">...</span>
                </span>
            </div>
            <div>
                <span id="planStatusBadge" class="badge bg-secondary">Cargando...</span>
                <button class="btn btn-sm btn-outline-primary ms-2" id="refreshSubscriptionBtn">
                    <i class="bi bi-arrow-repeat"></i>
                </button>
            </div>
        </div>

        <!-- ============================================
        CUERPO - TABLA
        ============================================ -->
        <div class="card-body p-0">
            <div class="sensor-table-wrapper p-2">
                <table class="table table-bordered table-striped table-hover mb-0" id="sensorsTable">
                    <thead id="sensorsTableHead"></thead>
                    <tbody id="sensorsTableBody">
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2 text-muted">Cargando sensores...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ============================================
            PAGINACIÓN
            ============================================ -->
            <div class="pagination-wrapper px-3 pb-3">
                <div class="pagination-info" id="paginationInfo">
                    Mostrando <strong id="pageStart">0</strong> - <strong id="pageEnd">0</strong> de <strong id="totalRecords">0</strong> sensores
                </div>
                <div class="pagination-controls">
                    <select class="per-page-select" id="perPageSelect">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-muted small">por página</span>
                    <div class="btn-group" id="paginationButtons">
                        <button class="btn btn-outline-secondary btn-sm" id="pagePrev" disabled>
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <span class="d-flex align-items-center px-2 small" id="pageInfo">Página 1</span>
                        <button class="btn btn-outline-secondary btn-sm" id="pageNext" disabled>
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contenedor para notificaciones -->
<div id="notificationContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const token = localStorage.getItem('token');
    let selectedSensors = new Set();
    let allExtraFields = [];
    let currentSort = { key: 'id', direction: 'asc' };
    let allSensorsData = [];
    let filteredData = [];
    let currentPage = 1;
    let perPage = 25;
    let searchTerm = '';

    if (typeof bootstrap !== 'undefined') {
        const dropdownElements = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        dropdownElements.forEach(el => {
            new bootstrap.Dropdown(el);
        });
    }

    if (!token) {
        $('#sensorsTableBody').html(`
            <tr>
                <td colspan="9" class="text-center text-danger py-4">
                    <i class="fas fa-exclamation-triangle"></i> No se encontró token. 
                    <a href="{{ route('login') }}">Iniciar sesión</a>
                </td>
            </tr>
        `);
        return;
    }

    // ============================================
    // FUNCIONES DE SUSCRIPCIÓN
    // ============================================

    function loadSubscriptionStatus() {
        $.ajax({
            url: '/api/subscription/plan/status',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    updateSubscriptionUI(response.data);
                }
            },
            error: function(xhr) {
                console.error('❌ Error al cargar suscripción:', xhr);
            }
        });
    }

    function updateSubscriptionUI(data) {
        const plan = data.plan;
        const limits = data.limits;

        // Plan
        const planName = $('#planName');
        let planClass = 'free';
        let planLabel = '🎁 Gratuito';
        
        if (plan.key === 'premium') {
            planClass = 'premium';
            planLabel = '⭐ Premium';
        } else if (plan.key === 'basico') {
            planClass = 'basico';
            planLabel = '📋 Básico';
        }
        
        if (plan.is_collaborator) {
            planLabel += ' 👥';
        }
        
        planName.text(planLabel);
        planName.attr('class', `badge-plan ${planClass}`);

        // Sensores
        const sensorLimit = $('#sensorLimit');
        if (limits.sensors.is_unlimited) {
            sensorLimit.text('∞');
            sensorLimit.removeClass('danger warning');
        } else {
            const used = limits.sensors.used;
            const max = limits.sensors.max;
            const remaining = limits.sensors.remaining;
            sensorLimit.text(`${used} / ${max}`);
            sensorLimit.removeClass('danger warning');
            if (remaining <= 0) {
                sensorLimit.addClass('danger');
            } else if (remaining <= 1) {
                sensorLimit.addClass('warning');
            }
        }

        // Grupos
        const groupLimit = $('#groupLimit');
        if (limits.groups.is_unlimited) {
            groupLimit.text('∞');
            groupLimit.removeClass('danger warning');
        } else {
            const used = limits.groups.used;
            const max = limits.groups.max;
            const remaining = limits.groups.remaining;
            groupLimit.text(`${used} / ${max}`);
            groupLimit.removeClass('danger warning');
            if (remaining <= 0) {
                groupLimit.addClass('danger');
            } else if (remaining <= 1) {
                groupLimit.addClass('warning');
            }
        }

        // Colaboradores
        const collaboratorLimit = $('#collaboratorLimit');
        if (limits.collaborators.is_unlimited) {
            collaboratorLimit.text('∞');
            collaboratorLimit.removeClass('danger warning');
        } else {
            const used = limits.collaborators.used;
            const max = limits.collaborators.max;
            collaboratorLimit.text(`${used} / ${max}`);
            collaboratorLimit.removeClass('danger warning');
        }

        // Estado
        const statusBadge = $('#planStatusBadge');
        if (data.has_active_subscription) {
            statusBadge.text('✅ Activa');
            statusBadge.removeClass('bg-secondary bg-danger bg-warning').addClass('bg-success');
        } else if (data.subscription && data.subscription.status === 'pending') {
            statusBadge.text('⏳ Pendiente');
            statusBadge.removeClass('bg-secondary bg-success bg-danger').addClass('bg-warning');
        } else {
            statusBadge.text('❌ Sin suscripción');
            statusBadge.removeClass('bg-secondary bg-success bg-warning').addClass('bg-danger');
        }

        // Botones según límites
        updateButtonsBasedOnLimits(data);

        if (!plan.can_add_collaborators && !plan.is_collaborator) {
            $('#collaboratorLimitItem').hide();
        } else {
            $('#collaboratorLimitItem').show();
        }
    }

    function updateButtonsBasedOnLimits(data) {
        const canCreateSensor = data.limits.sensors.can_create;
        const planName = data.plan.name;

        const createLink = $('#createSensorLink');
        if (!canCreateSensor) {
            createLink.addClass('btn-disabled-by-plan');
            createLink.attr('title', `Límite de sensores alcanzado para el plan ${planName}`);
            createLink.on('click', function(e) {
                e.preventDefault();
                showNotification(
                    `⚠️ Has alcanzado el límite de sensores para tu plan ${planName}. ` +
                    `<a href="/profile" class="alert-link">Actualiza tu plan</a> para crear más sensores.`,
                    'warning'
                );
            });
        } else {
            createLink.removeClass('btn-disabled-by-plan');
            createLink.off('click');
            createLink.attr('title', '');
        }
    }

    // ============================================
    // FUNCIÓN PARA NOTIFICACIÓN DE BLOQUEO
    // ============================================

    window.showGateBlockedNotification = function() {
        showNotification(
            '🔒 La importación masiva de sensores es una funcionalidad exclusiva para usuarios Premium. ' +
            '<a href="/profile" class="alert-link">Activa tu suscripción Premium</a> para acceder.',
            'warning'
        );
    };

    // ============================================
    // FUNCIONES DE CARGA DE DATOS
    // ============================================

    function loadData() {
        $.ajax({
            url: '/api/sensors/extra-fields',
            headers: { 'Authorization': 'Bearer ' + token },
            success: function(response) {
                if (response.success) {
                    allExtraFields = response.data.map(f => f.name);
                } else {
                    allExtraFields = [];
                }
                loadSensors();
            },
            error: function() {
                allExtraFields = [];
                loadSensors();
            }
        });
    }

    function loadSensors() {
        $.ajax({
            url: '/api/sensors',
            headers: { 'Authorization': 'Bearer ' + token },
            success: function(response) {
                if (response.success) {
                    const extraFieldsSet = new Set(allExtraFields);
                    response.data.forEach(sensor => {
                        if (sensor.metadata && typeof sensor.metadata === 'object') {
                            Object.keys(sensor.metadata).forEach(key => extraFieldsSet.add(key));
                        }
                    });
                    allExtraFields = [...extraFieldsSet];
                    allSensorsData = response.data;
                    applyFiltersAndSort();
                    loadSubscriptionStatus();
                } else {
                    renderEmptyState();
                }
            },
            error: function() {
                renderEmptyState();
            }
        });
    }

    // ============================================
    // FILTRADO Y BÚSQUEDA
    // ============================================

    function applyFiltersAndSort() {
        let data = [...allSensorsData];

        if (searchTerm.trim() !== '') {
            const term = searchTerm.toLowerCase().trim();
            data = data.filter(sensor => {
                const searchFields = [
                    sensor.name?.toLowerCase() || '',
                    sensor.identifier?.toLowerCase() || '',
                    sensor.group?.name?.toLowerCase() || '',
                    String(sensor.id)
                ];
                
                if (sensor.metadata && typeof sensor.metadata === 'object') {
                    Object.values(sensor.metadata).forEach(value => {
                        if (value !== null && value !== undefined) {
                            searchFields.push(String(value).toLowerCase());
                        }
                    });
                }
                
                return searchFields.some(field => field.includes(term));
            });
        }

        filteredData = data;
        currentPage = 1;
        renderWithPagination();
        updatePaginationInfo();
    }

    // ============================================
    // PAGINACIÓN
    // ============================================

    function renderWithPagination() {
        const total = filteredData.length;
        const totalPages = Math.ceil(total / perPage) || 1;
        
        if (currentPage > totalPages) currentPage = totalPages;
        
        const start = (currentPage - 1) * perPage;
        const end = Math.min(start + perPage, total);
        const pageData = filteredData.slice(start, end);

        renderSensors(pageData, total, start, end);
        updatePaginationControls(total, totalPages);
    }

    function updatePaginationInfo() {
        const total = filteredData.length;
        const start = total === 0 ? 0 : (currentPage - 1) * perPage + 1;
        const end = Math.min(start + perPage - 1, total);
        
        $('#pageStart').text(start);
        $('#pageEnd').text(end);
        $('#totalRecords').text(total);
        $('#totalCountBadge').text(total);
    }

    function updatePaginationControls(total, totalPages) {
        $('#pagePrev').prop('disabled', currentPage <= 1);
        $('#pageNext').prop('disabled', currentPage >= totalPages);
        $('#pageInfo').text(`Página ${currentPage} de ${totalPages}`);
        
        updateDeleteSelectedButton();
    }

    // ============================================
    // RENDERIZADO DE TABLA
    // ============================================

    function getBaseHeaders() {
        return [
            { key: 'checkbox', label: '', sortable: false, noSort: true },
            { key: 'id', label: 'ID', sortable: true },
            { key: 'name', label: 'Nombre', sortable: true },
            { key: 'identifier', label: 'Identificador', sortable: true },
            { key: 'group', label: 'Grupo', sortable: true },
            { key: 'actions', label: 'Acciones', sortable: false, noSort: true }
        ];
    }

    function getFullHeaders() {
        const baseHeaders = getBaseHeaders();
        const extraHeaders = allExtraFields.map(field => ({
            key: 'extra_' + field,
            label: field,
            sortable: true,
            isExtra: true,
            fieldName: field
        }));
        return [...baseHeaders.slice(0, -1), ...extraHeaders, baseHeaders[baseHeaders.length - 1]];
    }

    function renderTableHeaders(headers) {
        let html = '<tr>';
        headers.forEach(header => {
            const extraClass = header.isExtra ? 'extra-field-header' : '';
            const width = header.key === 'checkbox' ? '35px' : '';
            const minWidth = header.key === 'actions' ? '120px' : '';
            const extraIcon = header.isExtra ? ' <i class="bi bi-tag"></i>' : '';
            const noSortClass = header.noSort ? 'no-sort' : '';
            
            let sortIcon = '';
            if (header.sortable) {
                const isActive = currentSort.key === header.key;
                const directionClass = isActive ? (currentSort.direction === 'asc' ? 'asc' : 'desc') : '';
                const activeClass = isActive ? 'active' : '';
                sortIcon = `<span class="sort-icon ${activeClass} ${directionClass}">
                    <i class="bi bi-arrow-up"></i>
                </span>`;
            }
            
            const sortableClass = header.sortable ? 'sortable-header' : 'sortable-header no-sort';
            
            html += `
                <th style="width: ${width}; min-width: ${minWidth}" 
                    class="${extraClass} ${sortableClass} ${noSortClass}"
                    data-sort="${header.key}"
                    data-sortable="${header.sortable}">
                    <span class="th-content">
                        ${header.label}${extraIcon}
                        ${sortIcon}
                    </span>
                </th>
            `;
        });
        html += '</tr>';
        $('#sensorsTableHead').html(html);

        $('.sortable-header:not(.no-sort)').off('click').on('click', function() {
            const key = $(this).data('sort');
            if (key && key !== 'checkbox' && key !== 'actions') {
                applySort(key);
            }
        });
    }

    function applySort(key) {
        if (currentSort.key === key) {
            currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort.key = key;
            currentSort.direction = 'asc';
        }
        applyFiltersAndSort();
    }

    function renderSensors(sensors, total, start, end) {
        if (currentSort.key && currentSort.key !== 'checkbox' && currentSort.key !== 'actions') {
            const sortKey = currentSort.key;
            const direction = currentSort.direction === 'asc' ? 1 : -1;
            
            sensors.sort((a, b) => {
                let valA, valB;
                
                if (sortKey === 'group') {
                    valA = a.group?.name || '';
                    valB = b.group?.name || '';
                } else if (sortKey.startsWith('extra_')) {
                    const fieldName = sortKey.replace('extra_', '');
                    valA = a.metadata?.[fieldName] !== undefined && a.metadata?.[fieldName] !== null ? String(a.metadata[fieldName]) : '';
                    valB = b.metadata?.[fieldName] !== undefined && b.metadata?.[fieldName] !== null ? String(b.metadata[fieldName]) : '';
                } else {
                    valA = a[sortKey] !== undefined && a[sortKey] !== null ? String(a[sortKey]) : '';
                    valB = b[sortKey] !== undefined && b[sortKey] !== null ? String(b[sortKey]) : '';
                }
                
                if (typeof valA === 'string') valA = valA.toLowerCase();
                if (typeof valB === 'string') valB = valB.toLowerCase();
                
                if (valA < valB) return -1 * direction;
                if (valA > valB) return 1 * direction;
                return 0;
            });
        }

        const headers = getFullHeaders();
        renderTableHeaders(headers);

        const tbody = $('#sensorsTableBody');
        tbody.empty();

        if (!sensors || sensors.length === 0) {
            const emptyColspan = allExtraFields.length + getBaseHeaders().length;
            
            // ✅ Verificar si puede importar desde PHP (permissions)
            const canImport = @json($permissions['can_import_sensors'] ?? false);
            
            tbody.html(`
                <tr>
                    <td colspan="${emptyColspan}" class="text-center py-4">
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <h4>No se encontraron sensores</h4>
                            <p>${searchTerm ? 'Intenta con otro término de búsqueda.' : 'Crea tu primer sensor o importa uno desde Excel.'}</p>
                            ${!searchTerm ? `
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                <a href="{{ route('sensors.create') }}" class="btn btn-primary" id="emptyCreateSensor">
                                    <i class="bi bi-plus"></i> Crear Sensor
                                </a>
                                ${canImport ? `
                                    <a href="{{ route('sensor-groups.bulk-import') }}" class="btn btn-info" id="emptyImportSensors">
                                        <i class="bi bi-file-earmark-excel"></i> Importar Sensores
                                    </a>
                                ` : `
                                    <button class="btn btn-info gate-blocked" id="emptyImportSensors" 
                                            onclick="showGateBlockedNotification(); return false;">
                                        <i class="bi bi-file-earmark-excel"></i> Importar Sensores
                                    </button>
                                `}
                            </div>` : ''}
                        </div>
                    </td>
                </tr>
            `);
            
            // Aplicar restricciones al botón de crear
            const createBtn = $('#emptyCreateSensor');
            if (createBtn.length) {
                $.ajax({
                    url: '/api/subscription/plan/can-create-sensor',
                    type: 'GET',
                    headers: { 'Authorization': 'Bearer ' + token },
                    success: function(response) {
                        if (response.success && !response.data.can_create) {
                            createBtn.addClass('btn-disabled-by-plan');
                            createBtn.attr('title', 'Límite de sensores alcanzado');
                            createBtn.on('click', function(e) {
                                e.preventDefault();
                                showNotification('Has alcanzado el límite de sensores para tu plan. Actualiza tu plan para crear más.', 'warning');
                            });
                        }
                    }
                });
            }
            
            return;
        }

        sensors.forEach(sensor => {
            const metadata = sensor.metadata || {};

            let rowHtml = `<tr data-sensor-id="${sensor.id}">`;
            rowHtml += `<td class="text-center"><input type="checkbox" class="sensor-checkbox" data-sensor-id="${sensor.id}"></td>`;
            rowHtml += `<td><span class="sensor-id-badge">${sensor.id}</span></td>`;
            rowHtml += `<td class="text-left"><strong>${sensor.name}</strong></td>`;
            rowHtml += `<td><code>${sensor.identifier}</code></td>`;
            rowHtml += `<td>${sensor.group?.name || '<span class="no-data">Sin grupo</span>'}</td>`;
            
            allExtraFields.forEach(fieldName => {
                const value = metadata[fieldName] !== undefined && metadata[fieldName] !== null ? metadata[fieldName] : '—';
                rowHtml += `<td><span class="badge-extra-field" title="${value}">${value}</span></td>`;
            });
            
            rowHtml += `
                <td>
                    <div class="table-actions">
                        <a href="/mediciones/create/${sensor.id}" class="btn btn-sm btn-success" title="Tomar Medición">
                            <i class="bi bi-rulers"></i>
                        </a>
                        <a href="/sensors/${sensor.id}" class="btn btn-sm btn-info" title="Ver Sensor">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="/sensors/${sensor.id}/edit" class="btn btn-sm btn-warning" title="Editar Sensor">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button class="btn btn-sm btn-danger delete-sensor-btn"
                                data-sensor-id="${sensor.id}"
                                data-sensor-name="${sensor.name}"
                                title="Eliminar Sensor">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            
            rowHtml += '</tr>';
            tbody.append(rowHtml);
        });

        attachEvents();
        updatePaginationInfo();
    }

    function renderEmptyState() {
        const headers = getBaseHeaders();
        renderTableHeaders(headers);
        const emptyColspan = allExtraFields.length + getBaseHeaders().length;
        const canImport = @json($permissions['can_import_sensors'] ?? false);
        
        $('#sensorsTableBody').html(`
            <tr>
                <td colspan="${emptyColspan}" class="text-center py-4">
                    <div class="empty-state">
                        <i class="bi bi-info-circle"></i>
                        <h4>No tienes sensores</h4>
                        <p>Crea tu primer sensor o importa uno desde Excel.</p>
                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                            <a href="{{ route('sensors.create') }}" class="btn btn-primary" id="emptyCreateSensor">
                                <i class="bi bi-plus"></i> Crear Sensor
                            </a>
                            ${canImport ? `
                                <a href="{{ route('sensor-groups.bulk-import') }}" class="btn btn-info" id="emptyImportSensors">
                                    <i class="bi bi-file-earmark-excel"></i> Importar Sensores
                                </a>
                            ` : `
                                <button class="btn btn-info gate-blocked" id="emptyImportSensors" 
                                        onclick="showGateBlockedNotification(); return false;">
                                    <i class="bi bi-file-earmark-excel"></i> Importar Sensores
                                </button>
                            `}
                        </div>
                    </div>
                </td>
            </tr>
        `);
        $('#totalRecords').text('0');
        $('#totalCountBadge').text('0');
        $('#pageStart').text('0');
        $('#pageEnd').text('0');
        
        loadSubscriptionStatus();
    }

    // ============================================
    // EVENTOS
    // ============================================

    function attachEvents() {
        $('.sensor-checkbox').off('change').on('change', function() {
            const sensorId = $(this).data('sensor-id');
            if ($(this).is(':checked')) {
                selectedSensors.add(sensorId);
            } else {
                selectedSensors.delete(sensorId);
            }
            updateDeleteSelectedButton();
        });

        $('.delete-sensor-btn').off('click').on('click', function() {
            const sensorId = $(this).data('sensor-id');
            const sensorName = $(this).data('sensor-name');
            deleteSensor(sensorId, sensorName);
        });

        $('#deleteSelectedBtn').off('click').on('click', function() {
            if (selectedSensors.size === 0) return;
            if (!confirm(`¿Estás seguro de que deseas eliminar los ${selectedSensors.size} sensores seleccionados?`)) {
                return;
            }
            deleteSelectedSensors();
        });

        let searchTimeout;
        $('#searchInput').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchTerm = $(this).val();
                $('#searchClear').toggleClass('d-none', searchTerm === '');
                applyFiltersAndSort();
            }, 300);
        });

        $('#searchClear').on('click', function() {
            $('#searchInput').val('');
            searchTerm = '';
            $(this).addClass('d-none');
            applyFiltersAndSort();
        });

        $('#pagePrev').on('click', function() {
            if (currentPage > 1) {
                currentPage--;
                renderWithPagination();
            }
        });

        $('#pageNext').on('click', function() {
            const totalPages = Math.ceil(filteredData.length / perPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderWithPagination();
            }
        });

        $('#perPageSelect').on('change', function() {
            perPage = parseInt($(this).val());
            currentPage = 1;
            renderWithPagination();
        });

        $('#refreshBtn, #refreshBtnSmall').on('click', function() {
            loadData();
            loadSubscriptionStatus();
            showNotification('Datos actualizados', 'info');
        });

        $('#refreshSubscriptionBtn').on('click', function() {
            loadSubscriptionStatus();
            showNotification('Estado de suscripción actualizado', 'info');
        });
    }

    // ============================================
    // FUNCIONES DE ELIMINACIÓN
    // ============================================

    function updateDeleteSelectedButton() {
        const count = selectedSensors.size;
        $('#deleteSelectedBtn').prop('disabled', count === 0);
        $('#deleteSelectedBtn').html(`
            <i class="bi bi-trash"></i> Eliminar Seleccionados (${count})
        `);
    }

    function deleteSensor(sensorId, sensorName) {
        if (!confirm(`¿Estás seguro de que deseas eliminar el sensor "${sensorName}"?`)) return;

        const $btn = $(`button[data-sensor-id="${sensorId}"]`);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span>');

        $.ajax({
            url: `/api/sensors/${sensorId}`,
            type: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    $(`tr[data-sensor-id="${sensorId}"]`).fadeOut(300, function() {
                        $(this).remove();
                        selectedSensors.delete(sensorId);
                        loadData();
                    });
                    showNotification('Sensor eliminado correctamente', 'success');
                } else {
                    showNotification(response.message || 'Error al eliminar el sensor', 'danger');
                }
            },
            error: function(xhr) {
                let message = 'Error al eliminar el sensor';
                if (xhr.status === 403) message = 'No tienes permiso para eliminar este sensor';
                else if (xhr.status === 404) message = 'El sensor no existe';
                else if (xhr.responseJSON?.message) message = xhr.responseJSON.message;
                showNotification(message, 'danger');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
            }
        });
    }

    function deleteSelectedSensors() {
        if (selectedSensors.size === 0) return;

        const $btn = $('#deleteSelectedBtn');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Eliminando...');

        const sensorsToDelete = Array.from(selectedSensors);
        let successCount = 0;
        let errorCount = 0;
        const errors = [];

        const deleteNextSensor = (index) => {
            if (index >= sensorsToDelete.length) {
                selectedSensors.clear();
                updateDeleteSelectedButton();
                if (errorCount === 0) {
                    showNotification(`${successCount} sensores eliminados correctamente`, 'success');
                } else {
                    showNotification(`${successCount} sensores eliminados, ${errorCount} con errores`, 'warning');
                }
                $btn.prop('disabled', false).html(`<i class="bi bi-trash"></i> Eliminar Seleccionados (0)`);
                loadData();
                return;
            }

            const sensorId = sensorsToDelete[index];
            $.ajax({
                url: `/api/sensors/${sensorId}`,
                type: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        successCount++;
                    } else {
                        errorCount++;
                        errors.push({ sensorId, message: response.message || 'Error desconocido' });
                    }
                    deleteNextSensor(index + 1);
                },
                error: function(xhr) {
                    errorCount++;
                    let message = 'Error al eliminar el sensor';
                    if (xhr.status === 403) message = 'No tienes permiso para eliminar este sensor';
                    else if (xhr.status === 404) message = 'El sensor no existe';
                    else if (xhr.responseJSON?.message) message = xhr.responseJSON.message;
                    errors.push({ sensorId, message });
                    deleteNextSensor(index + 1);
                }
            });
        };

        deleteNextSensor(0);
    }

    // ============================================
    // NOTIFICACIONES
    // ============================================

    function showNotification(message, type = 'info') {
        const alertClass = `alert-${type}`;
        const icon = type === 'success' ? 'fa-check-circle' :
                     type === 'danger' ? 'fa-exclamation-circle' :
                     type === 'warning' ? 'fa-exclamation-triangle' :
                     'fa-info-circle';

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="font-size: 0.9rem;">
                <i class="fas ${icon}"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#notificationContainer').append(alertHtml);

        setTimeout(function() {
            $('#notificationContainer .alert:last-child').alert('close');
        }, 5000);
    }

    // ============================================
    // INICIALIZAR
    // ============================================

    loadData();
});
</script>
@endpush