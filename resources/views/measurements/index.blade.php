@extends('layouts.modern')

@section('title', 'Listado de Mediciones')

@push('styles')
<style>
    /* ===== ESTILOS GLOBALES PARA TODA LA PÁGINA ===== */

    /* Estilo para el card principal */
    .card {
        border-radius: 8px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: none;
    }

    .card-header {
        border-radius: 8px 8px 0 0 !important;
        padding: 1rem 1.5rem;
        font-weight: 600;
    }

    /* Estilo para los botones */
    .btn {
        border-radius: 6px;
        font-weight: 500;
        padding: 0.4rem 0.8rem;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .btn-sm {
        padding: 0.35rem 0.7rem;
        font-size: 0.8125rem;
    }

    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }

    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn-warning {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #212529;
    }

    .btn-light {
        background-color: #f8f9fa;
        border-color: #f8f9fa;
        color: #212529;
    }

    /* Estilo para los form-select */
    .form-select, .form-control {
        border-radius: 6px;
        padding: 0.45rem 0.75rem;
        font-size: 0.875rem;
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-select:focus, .form-control:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .form-select-sm, .form-control-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.8125rem;
        border-radius: 5px;
    }

    /* Estilo para las tarjetas de errores */
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
        font-size: 1.1rem;
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

    /* Estilo para la tabla de mediciones */
    .table {
        --bs-table-bg: transparent;
        --bs-table-accent-bg: transparent;
        --bs-table-striped-color: #212529;
        --bs-table-striped-bg: rgba(0, 0, 0, 0.02);
        --bs-table-active-color: #212529;
        --bs-table-active-bg: rgba(0, 0, 0, 0.05);
        --bs-table-hover-color: #212529;
        --bs-table-hover-bg: rgba(0, 0, 0, 0.05);
        width: 100%;
        margin-bottom: 1rem;
        color: #212529;
        vertical-align: top;
        border-color: #dee2e6;
    }

    .table th {
        background-color: #343a40;
        color: #fff;
        border-bottom: 2px solid #2e3235;
        padding: 0.75rem;
        vertical-align: middle;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .table td {
        padding: 0.75rem;
        vertical-align: middle;
        font-size: 0.85rem;
    }

    .table-striped > tbody > tr:nth-of-type(odd) > * {
        --bs-table-bg-type: rgba(0, 0, 0, 0.02);
    }

    .table-hover > tbody > tr:hover > * {
        --bs-table-bg-type: rgba(0, 0, 0, 0.05);
    }

    .table-warning {
        background-color: rgba(255, 206, 86, 0.1) !important;
    }

    /* Estilo para los badges */
    .badge {
        padding: 0.45em 0.65em;
        border-radius: 0.25rem;
        font-size: 0.75em;
        font-weight: 600;
        line-height: 1;
    }

    /* Estilo para los inputs de fecha */
    input[type="date"] {
        padding: 0.45rem 0.75rem;
        border-radius: 6px;
        font-size: 0.875rem;
    }

    /* Estilo para el contenedor de tarjetas */
    #errorStatsContainer .row > div {
        margin-bottom: 10px;
    }

    #errorStatsContainer .card {
        height: 100%;
    }

    /* Estilo para el modal de detalles */
    #errorDetailsModal .modal-dialog {
        max-width: 900px;
    }

    #errorDetailsModal .modal-header {
        border-radius: 8px 8px 0 0;
        padding: 1rem 1.5rem;
    }

    #errorDetailsModal .modal-body {
        max-height: 60vh;
        overflow-y: auto;
        padding: 1.5rem;
    }

    #errorDetailsAccordion .accordion-item {
        margin-bottom: 10px;
        border-radius: 6px;
        border: 1px solid rgba(0, 0, 0, 0.125);
    }

    #errorDetailsAccordion .accordion-header {
        background-color: #f8f9fa;
        border-radius: 6px 6px 0 0;
    }

    #errorDetailsAccordion .accordion-button {
        padding: 0.75rem 1rem;
        font-weight: 600;
        font-size: 0.875rem;
    }

    #errorDetailsAccordion .accordion-button:not(.collapsed) {
        background-color: #e9ecef;
        color: #495057;
        border-radius: 6px 6px 0 0;
    }

    #errorDetailsAccordion .accordion-body {
        padding: 1rem;
        background-color: #fff;
        border-radius: 0 0 6px 6px;
    }

    #errorDetailsAccordion table {
        margin-bottom: 0;
        font-size: 0.85rem;
    }

    #errorDetailsAccordion table th,
    #errorDetailsAccordion table td {
        padding: 0.75rem;
        vertical-align: middle;
    }

    #errorDetailsAccordion table th {
        background-color: #f1f3f4;
        font-weight: 600;
    }

    /* Estilo para la paginación */
    .pagination {
        --bs-pagination-padding-x: 0.75rem;
        --bs-pagination-padding-y: 0.375rem;
        --bs-pagination-font-size: 0.875rem;
        --bs-pagination-color: #6c757d;
        --bs-pagination-bg: #fff;
        --bs-pagination-border-width: 1px;
        --bs-pagination-border-color: #dee2e6;
        --bs-pagination-border-radius: 0.25rem;
        --bs-pagination-hover-color: #6c757d;
        --bs-pagination-hover-bg: #e9ecef;
        --bs-pagination-hover-border-color: #dee2e6;
        --bs-pagination-active-color: #fff;
        --bs-pagination-active-bg: #0d6efd;
        --bs-pagination-active-border-color: #0d6efd;
        --bs-pagination-disabled-color: #6c757d;
        --bs-pagination-disabled-bg: #fff;
        --bs-pagination-disabled-border-color: #dee2e6;
    }

    .pagination-sm {
        --bs-pagination-padding-x: 0.5rem;
        --bs-pagination-padding-y: 0.25rem;
        --bs-pagination-font-size: 0.75rem;
    }

    /* Estilo para el input de búsqueda */
    .input-group {
        border-radius: 6px;
        overflow: hidden;
    }

    .input-group .form-control {
        border-radius: 6px 0 0 6px;
    }

    .input-group .btn {
        border-radius: 0 6px 6px 0;
    }

    /* Estilo para el spinner */
    .spinner-border {
        width: 1.5rem;
        height: 1.5rem;
        border-width: 0.2em;
    }
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4><i class="bi bi-graph-up"></i> Listado de Mediciones</h4>
                    <div class="d-flex gap-2">
                        <button class="btn btn-warning" id="toggleErrors">
                            <i class="bi bi-exclamation-triangle"></i> Mostrar/Ocultar Errores
                        </button>
                        <a href="{{ route('sensor-groups.index') }}" class="btn btn-light">
                            <i class="bi bi-arrow-left"></i> Volver a Grupos
                        </a>
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
                            <div class="card text-white bg-danger error-card h-100" data-error-type="negative_consumption">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-graph-down"></i> Consumos Negativos</h5>
                                    <p class="card-text h4" id="negativeConsumptionCount">0</p>
                                    <small>Mediciones con valor menor a la anterior</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card text-white bg-warning error-card h-100" data-error-type="inconsistent_date">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-calendar-date"></i> Fechas Inconsistentes</h5>
                                    <p class="card-text h4" id="inconsistentDateCount">0</p>
                                    <small>Mediciones con fecha anterior a la última</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card text-white bg-info error-card h-100" data-error-type="first_measurement">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-clock-history"></i> Primera Medición</h5>
                                    <p class="card-text h4" id="noPreviousCount">0</p>
                                    <small>Primera medición de un sensor</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card text-white bg-success h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-check-circle"></i> Mediciones Válidas</h5>
                                    <p class="card-text h4" id="validMeasurementsCount">0</p>
                                    <small>Mediciones sin errores</small>
                                </div>
                            </div>
                        </div>
                    </div>

              <!-- Filtros -->
<div class="row mb-4">
    <div class="col-md-3">
        <label for="sensorFilter" class="form-label">Sensor</label>
        <select class="form-select form-select-sm" id="sensorFilter" style="width: 100%; max-width: 250px;">
            <option value="" selected>Todos los sensores</option>
            @foreach($sensors as $sensor)
                <option value="{{ $sensor->id }}" title="{{ $sensor->name }} ({{ $sensor->identifier }})">
                    {{ Str::limit($sensor->name, 20) }} ({{ Str::limit($sensor->identifier, 10) }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label for="groupFilter" class="form-label">Grupo</label>
        <select class="form-select form-select-sm" id="groupFilter" style="width: 100%; max-width: 200px;">
            <option value="" selected>Todos los grupos</option>
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
            <option value="negative_consumption">Consumo Negativo</option>
            <option value="inconsistent_date">Fecha Inconsistente</option>
            <option value="first_measurement">Primera Medición</option>
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

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchInput" placeholder="Buscar en mediciones...">
                                <button class="btn btn-primary" id="applyFilters">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-secondary" id="resetFilters">
                                <i class="bi bi-arrow-clockwise"></i> Limpiar Filtros
                            </button>
                        </div>
                    </div>

                    <!-- Tabla de Mediciones -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="measurementsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Sensor</th>
                                    <th>Grupo</th>
                                    <th>Valor</th>
                                    <th>Tipo</th>
                                    <th>Fecha</th>
                                    <th>Consumo</th>
                                    <th>Estado</th>
                                    <th>Foto</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="measurementsTableBody">
                                <tr>
                                    <td colspan="10" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div> Cargando mediciones...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Leyenda de estados -->
                    <!--<div class="mt-3">
                        <div class="d-flex flex-wrap gap-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success me-2"></span>
                                <small>Medición válida</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-warning text-dark me-2"></span>
                                <small>Fecha inconsistente</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-danger me-2"></span>
                                <small>Consumo negativo</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-info me-2"></span>
                                <small>Primera medición</small>
                            </div>
                        </div>
                    </div>-->

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
<div class="modal fade" id="errorDetailsModal" tabindex="-1" aria-labelledby="errorDetailsModalLabel" aria-hidden="true">
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentPage = 1;
    const itemsPerPage = 10;
    let showErrors = false;
    let currentErrorType = null;
    let errorStats = null;

    // Función para cargar mediciones desde la API
    function loadMeasurements() {
        const sensorId = $('#sensorFilter').val();
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
        if (groupId) params.group_id = groupId;
        if (errorType) params.error_type = errorType;
        if (dateFrom) params.date_from = dateFrom;
        if (dateTo) params.date_to = dateTo;
        if (search) params.search = search;

        $.ajax({
            url: '/api/measurements',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            },
            data: params,
            beforeSend: function() {
                $('#measurementsTableBody').html(`
                    <tr>
                        <td colspan="10" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div> Cargando mediciones...
                        </td>
                    </tr>
                `);
            },
            success: function(response) {
                if (response.success) {
                    if (response.error_stats) {
                        errorStats = response.error_stats;
                    }
                    renderMeasurements(response.data);
                    if (!$('#errorStatsContainer').hasClass('d-none') && errorStats) {
                        updateErrorStats(errorStats);
                    }
                    renderPagination(response.meta);
                } else {
                    showError(response.message || 'Error al cargar mediciones');
                }
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON?.message || xhr.statusText;
                showError('Error: ' + errorMessage);
            }
        });
    }

    // Función para mostrar errores
    function showError(message) {
        $('#measurementsTableBody').html(`
            <tr>
                <td colspan="10" class="text-center text-danger">
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

    // Función para renderizar mediciones
    function renderMeasurements(measurements) {
        if (measurements.length === 0) {
            $('#measurementsTableBody').html(`
                <tr>
                    <td colspan="10" class="text-center">
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
            const photoPath = data.foto && data.foto !== 'Sin Foto' ? `/${data.foto}` : null;

            const status = getMeasurementStatus(measurement);
            const statusBadge = getStatusBadge(status);
            const consumption = measurement.consumption !== undefined ? measurement.consumption : 'N/A';
            const consumptionClass = consumption < 0 ? 'text-danger fw-bold' : '';

            html += `
                <tr class="${status === 'valid' ? '' : 'table-warning'}">
                    <td>${measurement.id}</td>
                    <td>${measurement.sensor?.name || 'N/A'} (${measurement.sensor?.identifier || 'N/A'})</td>
                    <td>${measurement.sensor?.group?.name || 'Sin grupo'}</td>
                    <td>${value} ${unit}</td>
                    <td>${data.tipo ?? 'N/A'}</td>
                    <td>${date}</td>
                    <td class="${consumptionClass}">${consumption}</td>
                    <td>${statusBadge}</td>
                    <td>
                        ${photoPath ?
                            `<button class="btn btn-sm btn-info viewPhotoBtn" data-photo-path="${photoPath}">
                                <i class="bi bi-image"></i> Ver
                            </button>` : 'Sin Foto'}
                    </td>
                    <td class="text-nowrap">
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
                    </td>
                </tr>
            `;
        });

        $('#measurementsTableBody').html(html);

        // Asignar eventos
        $('.viewPhotoBtn').click(function() {
            const photoPath = $(this).data('photo-path');
            $('#modalPhoto').attr('src', photoPath);
            $('#downloadPhoto').attr('href', photoPath);
            $('#photoModal').modal('show');
        });

        $('.deleteMeasurementBtn').click(function() {
            const measurementId = $(this).data('measurement-id');
            if (confirm('¿Estás seguro de que deseas eliminar esta medición?')) {
                deleteMeasurement(measurementId);
            }
        });

        $('.viewErrorBtn').click(function() {
            const measurementId = $(this).data('measurement-id');
            viewErrorDetails(measurementId);
        });

        // Asignar eventos a las tarjetas de errores
        $('.error-card').click(function() {
            const errorType = $(this).data('error-type');
            showErrorDetailsModal(errorType);
        });
    }

// ✅ Función para obtener el campo principal según la plantilla
function getMainField(measurement) {
    // Si la medición tiene un campo 'tipo', usarlo para determinar el campo principal
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
    
    // ✅ Intentar obtener el campo principal del mapa
    const mappedField = fieldMap[tipo] || 'valor';
    
    // ✅ Verificar si el campo existe en los datos
    if (measurement.data && measurement.data[mappedField] !== undefined) {
        return mappedField;
    }
    
    // ✅ Si no existe, buscar cualquier campo número que no sea 'foto' ni 'tipo'
    if (measurement.data) {
        for (const key of Object.keys(measurement.data)) {
            if (key !== 'foto' && key !== 'tipo' && key !== 'campos_personalizados' && key !== 'fecha_medicion') {
                if (typeof measurement.data[key] === 'number' || !isNaN(measurement.data[key])) {
                    return key;
                }
            }
        }
    }
    
    return 'valor'; // fallback
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
            beforeSend: function() {
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
            success: function(response) {
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
            error: function(xhr) {
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
            const sensorIdentifier = sensorDetail.sensor_identifier;
            const groupName = sensorDetail.group_name;

            html += `
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-${sensorKey}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${sensorKey}" aria-expanded="false" aria-controls="collapse-${sensorKey}">
                            <strong>${sensorName}</strong> (${sensorIdentifier}) - Grupo: ${groupName}
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
            beforeSend: function() {
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
            success: function(response) {
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
            error: function(xhr) {
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
            success: function(response) {
                if (response.success) {
                    loadMeasurements();
                } else {
                    alert('Error: ' + (response.message || 'No se pudo eliminar la medición'));
                }
            },
            error: function(xhr) {
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

        $('#pagination a').click(function(e) {
            e.preventDefault();
            currentPage = parseInt($(this).data('page'));
            loadMeasurements();
        });
    }

    // Eventos para filtros
    $('#applyFilters').click(function() {
        currentPage = 1;
        loadMeasurements();
    });

    $('#searchInput').keypress(function(e) {
        if (e.which === 13) {
            $('#applyFilters').click();
        }
    });

    $('#resetFilters').click(function() {
        $('#sensorFilter').val('');
        $('#groupFilter').val('');
        $('#errorFilter').val('');
        $('#dateFrom').val('');
        $('#dateTo').val('');
        $('#searchInput').val('');
        currentPage = 1;
        loadMeasurements();
    });

    // Evento para mostrar/ocultar errores
    $('#toggleErrors').click(function() {
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

    // Cargar mediciones al inicio
    loadMeasurements();
});
</script>
@endpush