@extends('layouts.modern')

@section('title', 'Listado de Mediciones')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/measurements-styles.css') }}">
    <style>
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
                                                       PAGINACION
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
                                                       ESTILOS PARA FILTROS
                                                       ============================================ */
        .filter-section {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #e9ecef;
        }

        .filter-section .form-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .filter-section .form-select,
        .filter-section .form-control {
            font-size: 0.85rem;
            padding: 0.4rem 0.75rem;
            border-radius: 6px;
            border: 1px solid #ced4da;
        }

        .filter-section input[type="date"] {
            padding: 0.45rem 0.75rem;
            border-radius: 6px;
            font-size: 0.875rem;
        }

        /* ============================================
                                                       ESTILOS PARA TARJETAS DE ERRORES
                                                       ============================================ */
        #errorStatsContainer .row>div {
            margin-bottom: 10px;
        }

        #errorStatsContainer .card {
            height: 100%;
        }

        .error-card {
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 8px;
            margin-bottom: 15px;
            height: 100%;
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .error-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
        }

        .error-card .card-body {
            padding: 1.25rem;
        }

        .error-card .card-title {
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .error-card .card-title i {
            font-size: 0.95rem;
        }

        .error-card .card-text.h4 {
            font-size: 1.75rem;
            font-weight: bold;
            margin: 0.5rem 0;
        }

        .error-card small {
            font-size: 0.8rem;
            opacity: 0.8;
            display: block;
            margin-top: 0.25rem;
        }

        /* ============================================
                                                       ESTILO PARA IDENTIFICADOR DEL SENSOR
                                                       ============================================ */
        .sensor-identifier {
            display: block;
            font-size: 0.65rem;
            color: #6c757d;
            font-weight: normal;
            margin-top: 1px;
        }

        .sensor-name {
            font-weight: 600;
        }

        .sensor-info {
            line-height: 1.3;
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
        }

        @media (max-width: 768px) {
            .table-actions {
                flex-wrap: wrap;
                justify-content: center;
            }

            .measurement-table-wrapper .table th,
            .measurement-table-wrapper .table td {
                padding: 0.3rem 0.3rem;
                font-size: 0.7rem;
            }

            .pagination-controls {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <h4 class="mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-graph-up"></i>
                                <span>Listado de Mediciones</span>
                                <span id="totalCountBadge" class="badge bg-light text-dark ms-2"
                                    style="font-size: 0.7rem;">0</span>
                            </h4>

                            <div class="card-header-tools">
                                <button class="btn btn-warning" id="toggleErrors">
                                    <i class="bi bi-exclamation-triangle me-1"></i> Mostrar/Ocultar Errores
                                </button>
                                <a href="{{ route('sensor-groups.index') }}" class="btn btn-light">
                                    <i class="bi bi-arrow-left me-1"></i> Volver a Grupos
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Estadísticas de errores (tarjetas clickeables) -->
                        <div class="row mb-4 d-none" id="errorStatsContainer">
                            <div class="col-md-3 col-6">
                                <div class="card text-white bg-danger error-card h-100"
                                    data-error-type="negative_consumption">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="bi bi-graph-down me-2"></i> Consumos Negativos</h5>
                                        <p class="card-text h4" id="negativeConsumptionCount">0</p>
                                        <small>Mediciones con valor menor a la anterior</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="card text-white bg-warning error-card h-100"
                                    data-error-type="inconsistent_date">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="bi bi-calendar-date me-2"></i> Fechas
                                            Inconsistentes</h5>
                                        <p class="card-text h4" id="inconsistentDateCount">0</p>
                                        <small>Mediciones con fecha anterior a la última</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="card text-white bg-info error-card h-100" data-error-type="first_measurement">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="bi bi-clock-history me-2"></i> Primera Medición
                                        </h5>
                                        <p class="card-text h4" id="noPreviousCount">0</p>
                                        <small>Primera medición de un sensor</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="card text-white bg-success h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="bi bi-check-circle me-2"></i> Mediciones Válidas
                                        </h5>
                                        <p class="card-text h4" id="validMeasurementsCount">0</p>
                                        <small>Mediciones sin errores</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filtros -->
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <label for="sensorFilter" class="form-label">Sensor</label>
                                <select class="form-select form-select-sm" id="sensorFilter">
                                    <option value="" selected>Todos</option>
                                    @foreach($sensors as $sensor)
                                        <option value="{{ $sensor->id }}"
                                            title="{{ $sensor->name }} ({{ $sensor->identifier }})">
                                            {{ Str::limit($sensor->name, 20) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="identifierFilter" class="form-label">Identificador <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="identifierFilter"
                                    placeholder="Ej: SN-1234">
                            </div>
                            <div class="col-md-2">
                                <label for="groupFilter" class="form-label">Grupo</label>
                                <select class="form-select form-select-sm" id="groupFilter">
                                    <option value="" selected>Todos</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" title="{{ $group->name }}">
                                            {{ Str::limit($group->name, 20) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="errorFilter" class="form-label">Estado</label>
                                <select class="form-select form-select-sm" id="errorFilter">
                                    <option value="" selected>Todos</option>
                                    <option value="negative_consumption">Aviso Consumo</option>
                                    <option value="inconsistent_date">Aviso Fecha</option>
                                    <option value="first_measurement">1ra Medición</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="dateFrom" class="form-label">Fecha desde</label>
                                <input type="date" class="form-control form-control-sm" id="dateFrom">
                            </div>
                            <div class="col-md-2">
                                <label for="dateTo" class="form-label">Fecha hasta</label>
                                <input type="date" class="form-control form-control-sm" id="dateTo">
                            </div>
                        </div>

                        <div class="row mb-4 align-items-end">
                            <div class="col-md-5 text-muted small">
                                <em>Completa los filtros deseados (Ej: el identificador) para afinar tu búsqueda.</em>
                            </div>
                            <div class="col-md-7 text-end d-flex gap-2 justify-content-end flex-wrap">
                                <button class="btn btn-outline-success" id="toggleCommunityFilterBtn"
                                    style="white-space:nowrap;">
                                    <i class="bi bi-tree-fill"></i> Áreas Comunes
                                </button>
                                <button class="btn btn-secondary" id="resetFilters">
                                    <i class="bi bi-arrow-clockwise"></i> Limpiar Filtros
                                </button>
                                <button class="btn btn-primary" id="applyFilters">
                                    <i class="bi bi-funnel"></i> Filtrar Resultados
                                </button>
                            </div>
                        </div>

                        <!-- Tabla de Mediciones -->
                        <div class="measurement-table-wrapper p-2">
                            <table class="table table-bordered table-striped table-hover" id="measurementsTable">
                                <thead>
                                    <tr>
                                        <!-- Checkbox para eliminar varios -->
                                        <th style="width: 40px; text-align: center;">
                                            <input class="form-check-input" type="checkbox" id="selectAllMeasurements">
                                        </th>
                                        <th>Sensor</th>
                                        <th>Identificador</th>
                                        <th>Grupo</th>
                                        <th>Plantilla</th>
                                        <th>Valor</th>
                                        <th>Fecha</th>
                                        <th>Consumo</th>
                                        <th>Estado</th>
                                        <th>Foto</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="measurementsTableBody">
                                    <tr>
                                        <td colspan="12" class="text-center">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div> Cargando mediciones...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div id="paginationInfo"></div>
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm" id="pagination">
                                    <!-- Paginación se generará dinámicamente -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver foto en grande -->
    <div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="photoModalLabel">Foto de la Medición</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalPhoto" src="" class="img-fluid" style="max-height: 70vh;" alt="Foto de la medición">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <a id="downloadPhoto" href="" class="btn btn-primary" download>
                        <i class="bi bi-download"></i> Descargar Foto
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para detalles de error -->
    <div class="modal fade" id="errorDetailsModal" tabindex="-1" aria-labelledby="errorDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="errorDetailsModalLabel">
                        <i class="bi bi-exclamation-triangle-fill"></i> Detalles del Error
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="errorDetailsContent">
                    <!-- Contenido dinámico -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de acciones flotante (se muestra al seleccionar filas) -->
    <div id="bulkActionsBar"
        class="position-fixed bottom-0 start-50 translate-middle-x mb-4 shadow rounded px-4 py-3 bg-white"
        style="display: none; z-index: 1050; border: 1px solid #dee2e6;">
        <div class="d-flex align-items-center gap-3">
            <span class="fw-bold" id="selectedCount">0 seleccionados</span>
            <button class="btn btn-danger btn-sm" id="btnBulkDelete">
                <i class="bi bi-trash"></i> Eliminar Selección
            </button>
            <button type="button" class="btn-close ms-2" id="btnHideBulkActions" aria-label="Cerrar"></button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            let currentPage = 1;
            const itemsPerPage = 10;
            let showErrors = false;
            let currentErrorType = null;
            let errorStats = null;
            let filterOnlyCommunity = false;

            // Función para cargar mediciones desde la API
            function loadMeasurements() {
                const sensorId = $('#sensorFilter').val();
                const identifier = $('#identifierFilter').val();
                const groupId = $('#groupFilter').val();
                const errorType = $('#errorFilter').val();
                const dateFrom = $('#dateFrom').val();
                const dateTo = $('#dateTo').val();
                const search = $('#searchInput').val();

                const params = {
                    page: currentPage,
                    per_page: itemsPerPage
                };

                if (sensorId) params.sensor_id = sensorId;
                if (identifier) params.identifier = identifier;
                if (groupId) params.group_id = groupId;
                if (errorType) params.error_type = errorType;
                if (dateFrom) params.date_from = dateFrom;
                if (dateTo) params.date_to = dateTo;
                if (search) params.search = search;
                if (filterOnlyCommunity) params.is_community = 1;

                $.ajax({
                    url: '/api/measurements',
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json'
                    },
                    data: params,
                    beforeSend: function () {
                        $('#measurementsTableBody').html(`
                                                                    <tr>
                                                                        <td colspan="12" class="text-center">
                                                                            <div class="spinner-border text-primary" role="status">
                                                                                <span class="visually-hidden">Cargando...</span>
                                                                            </div> Cargando mediciones...
                                                                        </td>
                                                                    </tr>
                                                                `);
                    },
                    success: function (response) {
                        if (response.success) {
                            if (response.error_stats) {
                                errorStats = response.error_stats;
                            }
                            renderMeasurements(response.data);
                            if (!$('#errorStatsContainer').hasClass('d-none') && errorStats) {
                                updateErrorStats(errorStats);
                            }
                            renderPagination(response.meta);

                            // Restablecer checkboxes al cargar nueva tabla
                            $('#selectAllMeasurements').prop('checked', false);
                            updateBulkActionsVisibility();
                        } else {
                            showError(response.message || 'Error al cargar mediciones');
                        }
                    },
                    error: function (xhr) {
                        console.error('Error cargando mediciones:', xhr);
                        $('#measurementsTableBody').html(`
                                                                    <tr>
                                                                        <td colspan="12" class="text-center text-danger">
                                                                            <i class="bi bi-exclamation-triangle"></i> Error al cargar las mediciones. Por favor, intente de nuevo.
                                                                        </td>
                                                                    </tr>
                                                                `);
                    }
                });
            }

            // Función para mostrar errores
            function showError(message) {
                $('#measurementsTableBody').html(`
                                                            <tr>
                                                                <td colspan="12" class="text-center text-danger">
                                                                    <i class="bi bi-exclamation-triangle"></i> ${message}
                                                                </td>
                                                            </tr>
                                                        `);
            }

            // Función para actualizar estadísticas de errores
            function updateErrorStats(stats) {
                $('#negativeConsumptionCount').text(stats.negative_consumption || 0);
                $('#inconsistentDateCount').text(stats.inconsistent_date || 0);
                $('#noPreviousCount').text(stats.first_measurement || 0);
                $('#validMeasurementsCount').text(stats.valid || 0);
            }

            // Función para obtener campos dinámicos del sensor
            function getSensorDynamicFields(sensor) {
                if (!sensor) return {};
                // Aquí puedes mapear los campos dinámicos que tenga el sensor
                // Por ejemplo: numero_lote, codigo_medidor, etc.
                const fields = {};
                if (sensor.numero_lote) fields.numero_lote = sensor.numero_lote;
                if (sensor.codigo_medidor) fields.codigo_medidor = sensor.codigo_medidor;
                if (sensor.numero_serie) fields.numero_serie = sensor.numero_serie;
                return fields;
            }

            // Función para renderizar mediciones
            // Función para renderizar mediciones
            function renderMeasurements(measurements) {
                if (measurements.length === 0) {
                    $('#measurementsTableBody').html(`
                                                                <tr>
                                                                    <td colspan="11" class="text-center">
                                                                        <i class="bi bi-inbox"></i> No se encontraron mediciones
                                                                    </td>
                                                                </tr>
                                                            `);
                    return;
                }

                let html = '';
                measurements.forEach(measurement => {
                    const data = measurement.data || {};

                    // ✅ Obtener el campo principal según el tipo de plantilla
                    const mainField = getMainField(measurement);
                    const value = data[mainField] ?? 'N/A';

                    // ✅ Obtener la unidad
                    const unit = getUnit(measurement);

                    const date = new Date(measurement.measured_at).toLocaleString('es-ES');
                    let photoPath = null;
                    if (data.foto && data.foto !== 'Sin Foto') {
                        photoPath = data.foto.startsWith('/') ? data.foto : `/${data.foto}`;
                    }

                    const status = getMeasurementStatus(measurement);
                    const statusBadge = getStatusBadge(status);
                    const consumption = measurement.consumption !== undefined ? measurement.consumption : 'N/A';
                    const consumptionClass = consumption < 0 ? 'text-danger fw-bold' : '';

                    // ✅ Datos del sensor desde campos explícitos del API
                    const sensor = measurement.sensor || {};
                    const sensorName = sensor.name || 'N/A';
                    const sensorIdentifier = measurement.sensor_identifier || sensor.identifier || '';
                    const groupName = measurement.group_name || sensor.group?.name || 'Sin grupo';
                    const templateName = measurement.template_name || '';
                    const templateType = measurement.template_type || '';

                    // ✅ Construir display del sensor con campos extra dinámicos
                    let sensorDisplay = `<span class="sensor-name">${sensorName}</span>`;

                    // ✅ Campos extra del sensor - 100% dinámico desde metadata
                    const extraFields = measurement.sensor_extra_fields || {};
                    let extraFieldsHtml = [];
                    Object.keys(extraFields).forEach(key => {
                        if (extraFields[key] && extraFields[key] !== null && extraFields[key] !== '') {
                            // Capitalizar el nombre del campo para mejor legibilidad
                            const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                            extraFieldsHtml.push(`${label}: ${extraFields[key]}`);
                        }
                    });

                    if (extraFieldsHtml.length > 0) {
                        sensorDisplay += `<span class="sensor-identifier">${extraFieldsHtml.join(' | ')}</span>`;
                    }

                    // ✅ Construir label de plantilla
                    let templateDisplay = templateName || templateType || 'N/A';
                    if (templateName && templateType && templateName !== templateType) {
                        templateDisplay = `<span class="sensor-name">${templateName}</span><span class="sensor-identifier">${templateType}</span>`;
                    }

                    html += `
                                                                <tr class="${status === 'valid' ? '' : 'table-warning'}">
                                                                    <td class="text-center">
                                                                        <input class="form-check-input measurement-checkbox" type="checkbox" value="${measurement.id}">
                                                                    </td>
                                                                    <td>
                                                                        <div class="sensor-info">${sensorDisplay}</div>
                                                                    </td>
                                                                    <td><code>${sensorIdentifier || '—'}</code></td>
                                                                    <td>${groupName}</td>
                                                                    <td><div class="sensor-info">${templateDisplay}</div></td>
                                                                    <td>${value} ${unit}</td>
                                                                    <td>${date}</td>
                                                                    <td class="${consumptionClass}">${consumption}</td>
                                                                    <td>${statusBadge}</td>
                                                                    <td>
                                                                        ${photoPath ?
                            `<button class="btn btn-sm btn-info viewPhotoBtn" data-photo-path="${photoPath}">
                                                                                <i class="bi bi-image me-1"></i> Ver
                                                                            </button>` : 'Sin Foto'}
                                                                    </td>
                                                                    <td>
                                                                        <div class="table-actions">
                                                                            <a href="{{ url('/mediciones/edit') }}/${measurement.id}" class="btn btn-sm btn-warning" title="Editar">
                                                                                <i class="bi bi-pencil"></i>
                                                                            </a>
                                                                            <button class="btn btn-sm btn-danger deleteMeasurementBtn" title="Eliminar" data-measurement-id="${measurement.id}">
                                                                                <i class="bi bi-trash"></i>
                                                                            </button>
                                                                            ${status !== 'valid' ?
                            `<button class="btn btn-sm btn-info viewErrorBtn" title="Ver detalles del error" data-measurement-id="${measurement.id}">
                                                                                    <i class="bi bi-exclamation-triangle"></i>
                                                                                </button>` : ''}
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            `;
                });

                $('#measurementsTableBody').html(html);

                // Asignar eventos
                $('.viewPhotoBtn').click(function () {
                    const photoPath = $(this).data('photo-path');
                    $('#modalPhoto').attr('src', photoPath);
                    $('#downloadPhoto').attr('href', photoPath);
                    $('#photoModal').modal('show');
                });

                $('.deleteMeasurementBtn').click(function () {
                    const measurementId = $(this).data('measurement-id');
                    if (confirm('¿Estás seguro de que deseas eliminar esta medición?')) {
                        deleteMeasurement(measurementId);
                    }
                });

                $('.viewErrorBtn').click(function () {
                    const measurementId = $(this).data('measurement-id');
                    viewErrorDetails(measurementId);
                });

                // Asignar eventos a las tarjetas de errores
                $('.error-card').click(function () {
                    const errorType = $(this).data('error-type');
                    showErrorDetailsModal(errorType);
                });
            }

            // ✅ Función para obtener el campo principal según la plantilla
            function getMainField(measurement) {
                const tipo = measurement.data?.tipo || '';

                const fieldMap = {
                    'agua': 'consumo_m3',
                    'gas': 'consumo_m3',
                    'electricidad': 'energia_kwh',
                    'temperatura': 'temperatura_c',
                    'presion': 'presion_bar',
                    'caudal': 'caudal_lmin',
                    'luz': 'iluminacion_lux',
                    'personalizado': 'medicion'
                };

                const mappedField = fieldMap[tipo] || 'valor';

                if (measurement.data && measurement.data[mappedField] !== undefined) {
                    return mappedField;
                }

                if (measurement.data) {
                    for (const key of Object.keys(measurement.data)) {
                        if (key !== 'foto' && key !== 'tipo' && key !== 'campos_personalizados' && key !== 'fecha_medicion') {
                            if (typeof measurement.data[key] === 'number' || !isNaN(measurement.data[key])) {
                                return key;
                            }
                        }
                    }
                }

                return 'valor';
            }

            // ✅ Función para obtener la unidad según el tipo de plantilla
            function getUnit(measurement) {
                const tipo = measurement.data?.tipo || '';

                const unitMap = {
                    'agua': 'm³',
                    'gas': 'm³',
                    'electricidad': 'kWh',
                    'temperatura': '°C',
                    'presion': 'bar',
                    'caudal': 'L/min',
                    'luz': 'lux',
                    'personalizado': ''
                };

                return unitMap[tipo] || '';
            }

            // Función para mostrar el modal de detalles de error por tipo
            function showErrorDetailsModal(errorType) {
                currentErrorType = errorType;

                $.ajax({
                    url: `/api/measurements/errors/${errorType}/details`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json'
                    },
                    beforeSend: function () {
                        $('#errorDetailsModalLabel').html(`
                                                                    <i class="bi bi-exclamation-triangle-fill"></i> Cargando detalles...
                                                                `);
                        $('#errorDetailsContent').html(`
                                                                    <div class="text-center">
                                                                        <div class="spinner-border text-primary" role="status">
                                                                            <span class="visually-hidden">Cargando...</span>
                                                                        </div>
                                                                        <p class="mt-2">Cargando detalles del error...</p>
                                                                    </div>
                                                                `);
                        $('#errorDetailsModal').modal('show');
                    },
                    success: function (response) {
                        if (response.success) {
                            const errorTypeName = getErrorTypeName(errorType);
                            $('#errorDetailsModalLabel').html(`
                                                                        <i class="bi bi-exclamation-triangle-fill"></i> ${errorTypeName} (${response.count} registros)
                                                                    `);
                            renderErrorDetails(response.data);
                        } else {
                            $('#errorDetailsModalLabel').html(`
                                                                        <i class="bi bi-exclamation-triangle-fill"></i> Error
                                                                    `);
                            $('#errorDetailsContent').html(`
                                                                        <div class="alert alert-danger">
                                                                            <i class="bi bi-exclamation-triangle"></i> ${response.message || 'Error al cargar detalles'}
                                                                        </div>
                                                                    `);
                        }
                    },
                    error: function (xhr) {
                        const errorMessage = xhr.responseJSON?.message || xhr.statusText;
                        $('#errorDetailsModalLabel').html(`
                                                                    <i class="bi bi-exclamation-triangle-fill"></i> Error
                                                                `);
                        $('#errorDetailsContent').html(`
                                                                    <div class="alert alert-danger">
                                                                        <i class="bi bi-exclamation-triangle"></i> Error: ${errorMessage}
                                                                    </div>
                                                                `);
                    }
                });
            }

            // Función para renderizar detalles de error en el modal
            function renderErrorDetails(errorDetails) {
                if (errorDetails.length === 0) {
                    $('#errorDetailsContent').html(`
                                                                <div class="alert alert-info">
                                                                    <i class="bi bi-info-circle"></i> No se encontraron registros con este tipo de error.
                                                                </div>
                                                            `);
                    return;
                }

                let html = '<div class="accordion" id="errorDetailsAccordion">';

                const groupedBySensor = {};
                errorDetails.forEach(detail => {
                    const sensorKey = `${detail.sensor_id}-${detail.sensor_name}`;
                    if (!groupedBySensor[sensorKey]) {
                        groupedBySensor[sensorKey] = [];
                    }
                    groupedBySensor[sensorKey].push(detail);
                });

                Object.entries(groupedBySensor).forEach(([sensorKey, details]) => {
                    const sensorDetail = details[0];
                    const sensorName = sensorDetail.sensor_name;
                    const sensorIdentifier = sensorDetail.sensor_identifier || '';
                    const groupName = sensorDetail.group_name;

                    html += `
                                                                <div class="accordion-item">
                                                                    <h2 class="accordion-header" id="heading-${sensorKey}">
                                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${sensorKey}" aria-expanded="false" aria-controls="collapse-${sensorKey}">
                                                                            <strong>${sensorName}</strong> ${sensorIdentifier ? `(#${sensorIdentifier})` : ''} - Grupo: ${groupName}
                                                                            <span class="badge bg-warning ms-2">${details.length} registros</span>
                                                                        </button>
                                                                    </h2>
                                                                    <div id="collapse-${sensorKey}" class="accordion-collapse collapse" aria-labelledby="heading-${sensorKey}" data-bs-parent="#errorDetailsAccordion">
                                                                        <div class="accordion-body">
                                                                            <div class="table-responsive">
                                                                                <table class="table table-bordered table-sm">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th>#</th>
                                                                                            <th>Medición Actual</th>
                                                                                            <th>Medición Anterior</th>
                                                                                            <th>Diferencia</th>
                                                                                            <th>Días</th>
                                                                                            <th>Error</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                            `;

                    details.forEach((detail, index) => {
                        const currentDate = new Date(detail.current_measurement.date).toLocaleString('es-ES');
                        const previousDate = detail.previous_measurement ?
                            new Date(detail.previous_measurement.date).toLocaleString('es-ES') : 'N/A';
                        const consumption = detail.current_measurement.consumption !== null ?
                            detail.current_measurement.consumption : 'N/A';
                        const consumptionClass = consumption < 0 ? 'text-danger fw-bold' : '';
                        const difference = detail.difference ? detail.difference.value : 'N/A';
                        const daysDifference = detail.difference ? detail.difference.days : 'N/A';

                        html += `
                                                                    <tr>
                                                                        <td>${detail.record_number}</td>
                                                                        <td>
                                                                            <strong>ID:</strong> ${detail.current_measurement.id}<br>
                                                                            <strong>Valor:</strong> ${detail.current_measurement.value}<br>
                                                                            <strong>Fecha:</strong> ${currentDate}
                                                                        </td>
                                                                        <td>
                                                                            ${detail.previous_measurement ?
                                `<strong>ID:</strong> ${detail.previous_measurement.id}<br>
                                                                                <strong>Valor:</strong> ${detail.previous_measurement.value}<br>
                                                                                <strong>Fecha:</strong> ${previousDate}` : 'N/A'}
                                                                        </td>
                                                                        <td class="${consumptionClass}">${difference}</td>
                                                                        <td>${daysDifference !== 'N/A' ? daysDifference + ' días' : 'N/A'}</td>
                                                                        <td>${detail.error_message}</td>
                                                                    </tr>
                                                                `;
                    });

                    html += `
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            `;
                });

                html += '</div>';
                $('#errorDetailsContent').html(html);
            }

            // Función para obtener el nombre del tipo de error
            function getErrorTypeName(errorType) {
                const names = {
                    'negative_consumption': 'Consumos Negativos',
                    'inconsistent_date': 'Fechas Inconsistentes',
                    'first_measurement': 'Primera Medición'
                };
                return names[errorType] || errorType;
            }

            // Función para determinar el estado de una medición
            function getMeasurementStatus(measurement) {
                if (measurement.error_type) {
                    return measurement.error_type;
                }

                if (!measurement.previous_measurement) {
                    return 'first_measurement';
                }

                if (measurement.consumption < 0) {
                    return 'negative_consumption';
                }

                if (new Date(measurement.measured_at) < new Date(measurement.previous_measurement.measured_at)) {
                    return 'inconsistent_date';
                }

                return 'valid';
            }

            // Función para obtener el badge de estado
            function getStatusBadge(status) {
                const statusConfig = {
                    'valid': { class: 'bg-success', text: 'Válida', icon: 'bi-check-circle' },
                    'negative_consumption': { class: 'bg-danger', text: 'Consumo Negativo', icon: 'bi-graph-down' },
                    'inconsistent_date': { class: 'bg-warning text-dark', text: 'Fecha Inconsistente', icon: 'bi-calendar-date' },
                    'first_measurement': { class: 'bg-info', text: 'Primera Medición', icon: 'bi-clock-history' }
                };

                const config = statusConfig[status] || { class: 'bg-secondary', text: 'Desconocido', icon: 'bi-question-circle' };
                return `<span class="badge ${config.class}"><i class="bi ${config.icon}"></i> ${config.text}</span>`;
            }

            // Función para ver detalles del error de una medición específica
            function viewErrorDetails(measurementId) {
                $.ajax({
                    url: `/api/measurements/${measurementId}/errors`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json'
                    },
                    beforeSend: function () {
                        $('#errorDetailsContent').html(`
                                                                    <div class="text-center">
                                                                        <div class="spinner-border text-primary" role="status">
                                                                            <span class="visually-hidden">Cargando...</span>
                                                                        </div>
                                                                        <p class="mt-2">Cargando detalles del error...</p>
                                                                    </div>
                                                                `);
                        $('#errorDetailsModal').modal('show');
                    },
                    success: function (response) {
                        if (response.success) {
                            const errors = response.data.errors || [];
                            const warnings = response.data.warnings || [];
                            let html = '';

                            if (errors.length === 0 && warnings.length === 0) {
                                html = '<p>No se encontraron errores en esta medición.</p>';
                            } else {
                                if (errors.length > 0) {
                                    html += '<h5 class="text-danger"><i class="bi bi-x-circle-fill"></i> Errores</h5><ul class="list-group list-group-flush mb-3">';
                                    errors.forEach(error => {
                                        html += `
                                                                                    <li class="list-group-item">
                                                                                        <strong>${error.type}:</strong> ${error.message}
                                                                                        ${error.suggestion ? `<br><small class="text-muted">${error.suggestion}</small>` : ''}
                                                                                    </li>
                                                                                `;
                                    });
                                    html += '</ul>';
                                }

                                if (warnings.length > 0) {
                                    html += '<h5 class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Advertencias</h5><ul class="list-group list-group-flush">';
                                    warnings.forEach(warning => {
                                        html += `
                                                                                    <li class="list-group-item">
                                                                                        <strong>${warning.type}:</strong> ${warning.message}
                                                                                        ${warning.suggestion ? `<br><small class="text-muted">${warning.suggestion}</small>` : ''}
                                                                                    </li>
                                                                                `;
                                    });
                                    html += '</ul>';
                                }
                            }

                            $('#errorDetailsContent').html(html);
                        } else {
                            $('#errorDetailsContent').html(`
                                                                        <div class="alert alert-danger">
                                                                            <i class="bi bi-exclamation-triangle"></i> ${response.message || 'Error al cargar detalles'}
                                                                        </div>
                                                                    `);
                        }
                    },
                    error: function (xhr) {
                        const errorMessage = xhr.responseJSON?.message || xhr.statusText;
                        $('#errorDetailsContent').html(`
                                                                    <div class="alert alert-danger">
                                                                        <i class="bi bi-exclamation-triangle"></i> Error: ${errorMessage}
                                                                    </div>
                                                                `);
                    }
                });
            }

            // Función para eliminar una medición
            function deleteMeasurement(measurementId) {
                $.ajax({
                    url: `/api/measurements/${measurementId}`,
                    type: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json'
                    },
                    success: function (response) {
                        if (response.success) {
                            loadMeasurements();
                        } else {
                            alert('Error: ' + (response.message || 'No se pudo eliminar la medición'));
                        }
                    },
                    error: function (xhr) {
                        const errorMessage = xhr.responseJSON?.message || xhr.statusText;
                        alert('Error: ' + errorMessage);
                    }
                });
            }

            // Función para renderizar paginación
            function renderPagination(meta) {
                if (!meta || !meta.last_page) {
                    $('#pagination').html('');
                    $('#paginationInfo').html('');
                    return;
                }

                const from = meta.from || 0;
                const to = meta.to || 0;
                const total = meta.total || 0;
                $('#paginationInfo').html(`Mostrando ${from} a ${to} de ${total} mediciones`);

                let paginationHtml = '';

                if (currentPage > 1) {
                    paginationHtml += `
                                                                <li class="page-item">
                                                                    <a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="Anterior">
                                                                        <span aria-hidden="true">&laquo;</span>
                                                                    </a>
                                                                </li>
                                                            `;
                } else {
                    paginationHtml += `
                                                                <li class="page-item disabled">
                                                                    <span class="page-link" aria-hidden="true">&laquo;</span>
                                                                </li>
                                                            `;
                }

                const maxPages = 5;
                let startPage = Math.max(1, currentPage - Math.floor(maxPages / 2));
                let endPage = Math.min(meta.last_page, startPage + maxPages - 1);

                if (endPage - startPage + 1 < maxPages) {
                    startPage = Math.max(1, endPage - maxPages + 1);
                }

                for (let i = startPage; i <= endPage; i++) {
                    if (i === currentPage) {
                        paginationHtml += `
                                                                    <li class="page-item active">
                                                                        <span class="page-link">${i}</span>
                                                                    </li>
                                                                `;
                    } else {
                        paginationHtml += `
                                                                    <li class="page-item">
                                                                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                                                                    </li>
                                                                `;
                    }
                }

                if (currentPage < meta.last_page) {
                    paginationHtml += `
                                                                <li class="page-item">
                                                                    <a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="Siguiente">
                                                                        <span aria-hidden="true">&raquo;</span>
                                                                    </a>
                                                                </li>
                                                            `;
                } else {
                    paginationHtml += `
                                                                <li class="page-item disabled">
                                                                    <span class="page-link" aria-hidden="true">&raquo;</span>
                                                                </li>
                                                            `;
                }

                $('#pagination').html(paginationHtml);
                $('#pagination a').click(function (e) {
                    e.preventDefault();
                    currentPage = parseInt($(this).data('page'));
                    loadMeasurements();
                });

            }

            // Eventos para filtros
            $('#sensorFilter, #groupFilter, #errorFilter').change(function () {
                currentPage = 1;
                loadMeasurements();
            });

            $('#toggleCommunityFilterBtn').click(function () {
                filterOnlyCommunity = !filterOnlyCommunity;
                if (filterOnlyCommunity) {
                    $(this).removeClass('btn-outline-success').addClass('btn-success');
                    $(this).html('<i class="bi bi-tree-fill"></i> Áreas Comunes (Activo)');
                } else {
                    $(this).removeClass('btn-success').addClass('btn-outline-success');
                    $(this).html('<i class="bi bi-tree-fill"></i> Áreas Comunes');
                }
                currentPage = 1;
                loadMeasurements();
            });

            $('#applyFilters').click(function () {
                currentPage = 1;
                loadMeasurements();
            });

            $('#searchInput').keypress(function (e) {
                if (e.which === 13) {
                    $('#applyFilters').click();
                }
            });

            $('#resetFilters').click(function () {
                $('#sensorFilter').val('');
                $('#identifierFilter').val('');
                $('#groupFilter').val('');
                $('#errorFilter').val('');
                $('#dateFrom').val('');
                $('#dateTo').val('');
                $('#searchInput').val('');

                filterOnlyCommunity = false;
                $('#toggleCommunityFilterBtn').removeClass('btn-success').addClass('btn-outline-success').html('<i class="bi bi-tree-fill"></i> Áreas Comunes');

                currentPage = 1;
                loadMeasurements();
            });

            // Evento para mostrar/ocultar errores
            $('#toggleErrors').click(function () {
                showErrors = !showErrors;
                if (showErrors) {
                    $(this).html('<i class="bi bi-exclamation-triangle"></i> Ocultar Errores');
                    $('#errorStatsContainer').removeClass('d-none');
                    if (errorStats) {
                        updateErrorStats(errorStats);
                    } else {
                        loadMeasurements();
                    }
                } else {
                    $(this).html('<i class="bi bi-exclamation-triangle"></i> Mostrar Errores');
                    $('#errorStatsContainer').addClass('d-none');
                }
            });

            // ==========================================
            // LÓGICA DE ELIMINACIÓN MASIVA
            // ==========================================

            // Función para actualizar visibilidad de la barra
            function updateBulkActionsVisibility() {
                const checkedCount = $('.measurement-checkbox:checked').length;
                const totalCount = $('.measurement-checkbox').length;

                if (totalCount > 0 && checkedCount === totalCount) {
                    $('#selectAllMeasurements').prop('checked', true);
                    $('#selectAllMeasurements').prop('indeterminate', false);
                } else if (checkedCount > 0) {
                    $('#selectAllMeasurements').prop('checked', false);
                    $('#selectAllMeasurements').prop('indeterminate', true);
                } else {
                    $('#selectAllMeasurements').prop('checked', false);
                    $('#selectAllMeasurements').prop('indeterminate', false);
                }

                if (checkedCount > 0) {
                    $('#selectedCount').text(`${checkedCount} seleccionado${checkedCount !== 1 ? 's' : ''}`);
                    $('#bulkActionsBar').fadeIn(200);
                } else {
                    $('#bulkActionsBar').fadeOut(200);
                }
            }

            // Seleccionar/Deseleccionar todos
            $('#selectAllMeasurements').on('change', function () {
                const isChecked = $(this).prop('checked');
                $('.measurement-checkbox').prop('checked', isChecked);
                updateBulkActionsVisibility();
            });

            // Cambio en checkbox individual (delegado a body porque se generan dinámicamente)
            $('#measurementsTableBody').on('change', '.measurement-checkbox', function () {
                updateBulkActionsVisibility();
            });

            // Botón ocultar acciones
            $('#btnHideBulkActions').click(function () {
                $('.measurement-checkbox').prop('checked', false);
                updateBulkActionsVisibility();
            });

            // Botón eliminar selección
            $('#btnBulkDelete').click(function () {
                const selectedIds = [];
                $('.measurement-checkbox:checked').each(function () {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) return;

                if (confirm(`¿Está seguro que desea eliminar ${selectedIds.length} mediciones seleccionadas?\nEsta acción no se puede deshacer.`)) {
                    // Deshabilitar botón mientras carga
                    const btn = $(this);
                    const originalHtml = btn.html();
                    btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Eliminando...').prop('disabled', true);

                    $.ajax({
                        url: '/api/measurements/bulk-delete',
                        type: 'POST',
                        data: JSON.stringify({ measurement_ids: selectedIds }),
                        contentType: 'application/json',
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('token'),
                            'Accept': 'application/json'
                        },
                        success: function (response) {
                            if (response.success) {
                                // Mostrar alerta estilizada pero sencilla (simulada con alert por ahora o si tienes un toast system usarlo)
                                alert(response.message);
                                loadMeasurements();
                                $('#bulkActionsBar').fadeOut();
                            } else {
                                alert('Error: ' + (response.message || 'No se pudieron eliminar las mediciones'));
                                btn.html(originalHtml).prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            const errorMessage = xhr.responseJSON?.message || xhr.statusText;
                            alert('Error: ' + errorMessage);
                            btn.html(originalHtml).prop('disabled', false);
                        }
                    });
                }
            });

            // Cargar mediciones al inicio
            loadMeasurements();
        });
    </script>
@endpush