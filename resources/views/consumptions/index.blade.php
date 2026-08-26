@extends('layouts.modern')

@section('title', 'Mis Consumos - MeasureFlow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/consumptions-styles.css') }}">
    <style>
        .compact-modal .modal-header {
            padding: 0.75rem 1rem;
        }

        .compact-modal .modal-title {
            font-size: 1.1rem;
        }

        .compact-modal .modal-body {
            padding: 1rem;
            font-size: 0.875rem;
        }

        .compact-modal h2 {
            font-size: 1.5rem !important;
        }

        .compact-modal h6 {
            font-size: 0.85rem !important;
        }

        .compact-modal .form-control,
        .compact-modal .btn {
            padding: 0.3rem 0.6rem;
            font-size: 0.875rem;
        }

        .compact-modal .card-body {
            padding: 0.75rem;
        }

        .compact-modal .alert {
            padding: 0.6rem;
            font-size: 0.85rem;
        }
    </style>
@endpush

@section('content')

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i class="bi bi-graph-up-arrow"></i> Mis Consumos</h4>
                        <div class="d-flex gap-2">
                            <button class="btn btn-warning" id="btnOpenGlobalRadar">
                                <i class="bi bi-radar btn-icon"></i> Radar Global
                            </button>
                            <button class="btn btn-primary" id="calculateConsumption">
                                <i class="bi bi-calculator btn-icon"></i> Recalcular Consumos
                            </button>
                            <button class="btn btn-info" id="exportConsumptions">
                                <i class="bi bi-file-earmark-spreadsheet btn-icon"></i> Exportar a Excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-3 align-items-end" id="filterControls">
                            <div class="col-md-3">
                                <label for="sensorFilter" class="form-label mb-1">Sensor</label>
                                <select class="form-select form-select-sm" id="sensorFilter">
                                    <option value="" selected>Todos los sensores</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="identifierFilter" class="form-label mb-1">Identificador</label>
                                <input type="text" class="form-control form-control-sm" id="identifierFilter"
                                    placeholder="Ej: SN-123">
                            </div>
                            <div class="col-md-2">
                                <label for="startDate" class="form-label mb-1">Fecha desde</label>
                                <input type="date" class="form-control form-control-sm" id="startDate">
                            </div>
                            <div class="col-md-2">
                                <label for="endDate" class="form-label mb-1">Fecha hasta</label>
                                <input type="date" class="form-control form-control-sm" id="endDate">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2 w-100">
                                    <button type="button" class="btn btn-outline-success" id="btnFilterCommunity" class="btn-sm" style="white-space: nowrap;">
                                        <i class="bi bi-tree-fill"></i> Áreas Comunes
                                    </button>
                                    <button type="button" class="btn btn-primary" id="applyFiltersBtn" class="btn-sm">
                                        <i class="bi bi-funnel"></i> Filtrar
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="clearFiltersBtn" class="btn-sm">
                                        <i class="bi bi-x-lg"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de Consumos -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Sensor</th>
                                        <th>Identificador</th>
                                        <th>Tipo</th>
                                        <th>Grupo</th>
                                        <th>Consumo Total</th>
                                        <th>Unidad</th>
                                        <th>Período</th>
                                        <th>Días transcurridos</th>
                                        <th>Promedio Diario</th>
                                        <th style="width: 100px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="consumptionsTable">
                                    <!-- Los consumos se cargarán aquí por JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Paginación (opcional, si se implementa) -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div id="paginationInfo"></div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination" id="pagination">
                                <!-- Paginación se generará dinámicamente -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>

<!-- Modal para detalles del consumo -->
    <div class="modal fade" id="consumptionDetailsModal" tabindex="-1" aria-labelledby="consumptionDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="consumptionDetailsModalLabel">Detalles del Consumo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="consumptionDetailsContent">
                    <!-- Contenido dinámico -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Análisis Avanzado de Rango -->
    <div class="modal fade" id="analyzeSensorModal" tabindex="-1" aria-labelledby="analyzeSensorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered compact-modal">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="analyzeSensorModalLabel"><i class="bi bi-bar-chart-fill"></i> Análisis Avanzado: <span id="analyzeSensorName"></span></h5>
                    <div class="d-flex align-items-center ms-auto">
                        <button type="button" class="btn btn-sm btn-outline-light me-1" id="btnPrevAnalyzeSensor" title="Sensor Anterior">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light me-3" id="btnNextAnalyzeSensor" title="Sensor Siguiente">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <!-- Loading Fechas Base -->
                    <div id="metaLoadingStatus" class="text-center text-muted small mb-2 d-none">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Sincronizando fechas disponibles...
                    </div>

                    <div id="metaAvailableDates" class="alert alert-info py-1 px-3 mb-3 d-none">
                        <strong>Fechas disponibles:</strong> entre <span id="metaFirstDate"></span> y <span id="metaLastDate"></span>
                    </div>

                    <!-- Configuración del rango -->
                    <div class="row align-items-end mb-3 bg-light p-2 rounded mx-0 border">
                        <div class="col-md-3">
                            <label for="analyzeStartDate" class="form-label mb-1">Inicio</label>
                            <input type="date" class="form-control form-control-sm" id="analyzeStartDate">
                        </div>
                        <div class="col-md-3">
                            <label for="analyzeEndDate" class="form-label mb-1">Corte</label>
                            <input type="date" class="form-control form-control-sm" id="analyzeEndDate">
                        </div>
                        <div class="col-md-2">
                            <label for="analyzeThreshold" class="form-label mb-1 text-danger small" title="Salto en la tasa diaria"><i class="bi bi-exclamation-triangle"></i> Anomalía</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control text-danger fw-bold" id="analyzeThreshold" value="50" min="1" step="5">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="analyzeStagnation" class="form-label mb-1 text-secondary small" title="Alerta si delta es 0 luego de X días"><i class="bi bi-hourglass-top"></i> Estancamiento</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control text-secondary fw-bold" id="analyzeStagnation" value="15" min="1" step="1">
                                <span class="input-group-text">Días</span>
                            </div>
                        </div>
                        <div class="col-md-1 d-grid px-1">
                            <button class="btn btn-primary btn-sm" id="btnCalculateRange" title="Calcular Rango">
                                <i class="bi bi-play-fill"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Explicación Sensibilidad Inteligente -->
                    <div class="alert alert-secondary py-2 px-3 mb-3 pointer-events-none">
                        <strong class="text-danger small"><i class="bi bi-shield-exclamation"></i> ¿Qué es la sensibilidad inteligente?</strong><br>
                        <small class="text-muted" style="font-size: 0.8rem;">
                            El sistema analiza matemáticamente la <b>tasa diaria de consumo</b> en lugar del salto
                            bruto. Si detecta que la aceleración de consumo entre mediciones supera el <strong class="text-danger"><span id="infoThreshold">50</span>%</strong>, o si detecta estancamiento por
                            más de <strong class="text-secondary"><span id="infoStagnation">15</span> días</strong>, dibujará
                            ese punto con una alerta ⚠️ roja. Esto previene distorsiones causadas por tiempos de inspección irregulares.
                        </small>
                    </div>

                    <input type="hidden" id="analyzeSensorId" value="">

                    <!-- Resultados del Dashboard Analítico -->
                    <div id="analyzeResults" class="d-none">
                        <div class="row g-2 mb-3">
                            <div class="col-md-12 mb-1">
                                <div class="card border-dark shadow-sm" style="background-color: #f8f9fa;">
                                    <div class="card-body text-center py-2">
                                        <h6 class="text-muted mb-0"><i class="bi bi-cash-coin"></i> Facturación Total Estimada</h6>
                                        <h3 class="text-dark my-1 fw-bold" id="resFinalBilledTotal">0</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 border-info shadow-sm">
                                    <div class="card-body text-center p-2">
                                        <h6 class="text-muted mb-1" style="font-size: 0.8rem;"><i class="bi bi-speedometer2"></i> Consumo Lote</h6>
                                        <h4 class="text-info my-1 fw-bold" id="resTotalDelta">0</h4>
                                        <p class="mb-0 text-muted" style="font-size: 0.65rem;"><span id="resMeasurementsCount" class="fw-bold"></span> lect.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 border-success shadow-sm" style="background-color: #f0fdf4;">
                                    <div class="card-body text-center p-2">
                                        <h6 class="text-success mb-1" style="font-size: 0.8rem;"><i class="bi bi-tree-fill"></i> Cargos Comunes</h6>
                                        <h4 class="text-success my-1 fw-bold" id="resCommunityContribution">0</h4>
                                        <p class="mb-0 text-muted" style="font-size: 0.65rem;">+ Prorrateado</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 border-secondary shadow-sm">
                                    <div class="card-body text-center p-2">
                                        <h6 class="text-muted mb-1" style="font-size: 0.8rem;"><i class="bi bi-calendar3"></i> Prom. Diario</h6>
                                        <h4 class="text-secondary my-1 fw-bold" id="resDailyAvg">0</h4>
                                        <p class="mb-0 text-muted" style="font-size: 0.65rem;"><span id="resDaysBetween"></span> días</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfico Chart.js -->
                        <div class="card shadow-sm border-0 mb-2">
                            <div class="card-body p-2" style="position: relative; height: 230px; width: 100%;">
                                <canvas id="evolutionChart"></canvas>
                            </div>
                        </div>

                        <div class="text-center mt-3 mb-2" id="shareBtnContainer">
                            <button type="button" class="btn btn-success px-4" id="btnShareAnalysis">
                                <i class="bi bi-envelope-check"></i> Enviar Resumen Avanzado al Usuario
                            </button>
                        </div>
                    </div>

                    <!-- Panel de Inspección de Anomalías (Solo visible si hay anomalías en el rango) -->
                    <div id="anomaliesInspectionContainer" class="d-none mt-4 mb-3 border rounded p-3 bg-light">
                        <h6 class="text-danger fw-bold border-bottom pb-2 mb-3">
                            <i class="bi bi-camera-fill"></i> Auditoría Visual de Anomalías
                        </h6>
                        <p class="small text-muted mb-3">
                            Se requiere verificación manual visual sobre los siguientes puntos atípicos generados. Compara si el registro se corresponde con la foto de prueba adjunta:
                        </p>
                        <div class="row g-3" id="anomaliesInspectionGrid">
                            <!-- Cards dinámicas inyectadas por JS -->
                        </div>
                    </div>

                    <div class="alert alert-secondary mb-0 pointer-events-none">
                        <strong><i class="bi bi-info-circle"></i> Resumen del período:</strong>
                        El cálculo abarca desde la lectura inicial <strong id="resStartLog"></strong> (<span id="resStartVal"></span>)
                        hasta el corte de <strong id="resEndLog"></strong> (<span id="resEndVal"></span>).
                    </div>
                </div>

                <!-- Loading / Estacionario state -->
                <div id="analyzeLoading" class="text-center d-none py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted mb-0">Calculando analíticas y dibujando trazados...</p>
                </div>
                
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para Radar Global de Anomalías -->
    <!-- Modal para Enviar Informe -->
    <div class="modal fade" id="shareReportModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-success text-white py-2">
                    <h6 class="modal-title m-0"><i class="bi bi-envelope"></i> Enviar Informe</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">Selecciona un campo del grupo o ingresa el correo manualmente:</p>
                    <div class="mb-3">
                        <select id="shareEmailSelect" class="form-select form-select-sm mb-2 d-none">
                            <option value="">-- Ingresar Manualmente --</option>
                        </select>
                        <input type="email" id="shareEmailInput" class="form-control form-control-sm" placeholder="ejemplo@correo.com">
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success btn-sm px-3" id="btnConfirmShare">Enviar <i class="bi bi-send-fill ms-1"></i></button>
                </div>
            </div>
        </div>
    </div>    <div class="modal fade" id="globalRadarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-radar"></i> Radar Global de Anomalías</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-secondary py-2 px-3 mb-3 pointer-events-none">
                        <strong class="text-danger small"><i class="bi bi-shield-exclamation"></i> ¿Qué hace este
                            Radar?</strong><br>
                        <small class="text-muted" style="font-size: 0.8rem;">
                            Escaneará <b>absolutamente todos tus sensores</b> en el fondo utilizando inteligencia de
                            <b>tasa diaria</b> y regresará solo un listado de los sensores que estén arrojando alertas
                            rojas bajo tu sensibilidad de umbral especificada, ahorrándote el trabajo manual de
                            analizarlos uno a uno.
                        </small>
                    </div>

                    <!-- Configuración del escaneo -->
                    <div class="row align-items-end mb-3 bg-light p-2 rounded mx-0 border">
                        <div class="col-md-3">
                            <label for="radarStartDate" class="form-label mb-1">Cota Inicial</label>
                            <input type="date" class="form-control form-control-sm" id="radarStartDate">
                        </div>
                        <div class="col-md-3">
                            <label for="radarEndDate" class="form-label mb-1">Cota Final</label>
                            <input type="date" class="form-control form-control-sm" id="radarEndDate">
                        </div>
                        <div class="col-md-2">
                            <label for="radarThreshold" class="form-label mb-1 text-danger"
                                title="Salto en la tasa diaria"><i class="bi bi-exclamation-triangle"></i>
                                Anomalía</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control text-danger fw-bold" id="radarThreshold" value="50"
                                    min="1" step="5">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="radarStagnation" class="form-label mb-1 text-secondary"
                                title="Alerta si delta es 0 luego de X días"><i class="bi bi-hourglass-top"></i>
                                Estancamiento</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control text-secondary fw-bold" id="radarStagnation"
                                    value="15" min="1" step="1">
                                <span class="input-group-text">Días</span>
                            </div>
                        </div>
                        <div class="col-md-2 d-grid px-1">
                            <button class="btn btn-warning btn-sm" id="btnScanGlobal" title="Iniciar Radar">
                                <i class="bi bi-search"></i> Escanear
                            </button>
                        </div>
                    </div>

                    <div id="radarLoading" class="text-center d-none py-4">
                        <div class="spinner-border text-warning" role="status"></div>
                        <p class="mt-2 text-muted mb-0">Rastreando trazos matemáticos, por favor espere...</p>
                    </div>

                    <div id="radarResults" class="d-none">
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-danger fw-bold mb-0">
                                <i class="bi bi-exclamation-octagon-fill"></i> Novedades Detectadas: <span
                                    id="radarCount">0</span>
                            </h6>
                            <div class="input-group input-group-sm w-50">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="radarSearch"
                                    placeholder="Buscar sensor o ID...">
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Sensor</th>
                                        <th>Incidenicas</th>
                                        <th>Acción Directa</th>
                                    </tr>
                                </thead>
                                <tbody id="radarTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let currentPage = 1;
        let evolutionChartInstance = null;
        let analyzedSensorsList = [];
        let currentAnalyzedIndex = -1;
        let filterOnlyCommunity = false;

        $(document).ready(function () {
            // Cargar de localstorage
            const storedThreshold = localStorage.getItem('medflow_analyze_threshold');
            if (storedThreshold) {
                $('#analyzeThreshold').val(storedThreshold);
            }

            const storedStagnation = localStorage.getItem('medflow_analyze_stagnation');
            if (storedStagnation) {
                $('#analyzeStagnation').val(storedStagnation);
            }

            // Bindear saves de localstorage on change
            $('#analyzeThreshold').change(function () {
                localStorage.setItem('medflow_analyze_threshold', $(this).val());
            });
            $('#analyzeStagnation').change(function () {
                localStorage.setItem('medflow_analyze_stagnation', $(this).val());
            });

            // Cargar sensores del usuario para el filtro
            loadSensors();

            // Cargar consumos al inicio
            loadConsumptions();

            // Eventos
            $('#calculateConsumption').click(calculateAllConsumptions);
            $('#exportConsumptions').click(exportConsumptions);
            $('#btnOpenGlobalRadar').click(openGlobalRadarModal);

            $('#btnFilterCommunity').click(function () {
                filterOnlyCommunity = !filterOnlyCommunity;
                if (filterOnlyCommunity) {
                    $(this).removeClass('btn-outline-success').addClass('btn-success');
                } else {
                    $(this).removeClass('btn-success').addClass('btn-outline-success');
                }
                currentPage = 1;
                loadConsumptions();
            });

            // Configurar botones de filtro locales
            $('#applyFiltersBtn').click(function () {
                currentPage = 1;
                loadConsumptions();
            });
            $('#resetFilters').click(resetFilters);
            $('#sensorFilter, #startDate, #endDate').change(function () {
                currentPage = 1;
                loadConsumptions();
            });
        });

        // Cargar sensores del usuario
        function loadSensors() {
            $.ajax({
                url: '/api/sensors',
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                },
                success: function (response) {
                    if (response.success) {
                        const sensorFilter = $('#sensorFilter');
                        sensorFilter.empty();
                        sensorFilter.append('<option value="" selected>Todos los sensores</option>');

                        response.data.forEach(function (sensor) {
                            sensorFilter.append('<option value="' + sensor.id + '">' + sensor.name + ' (' + sensor.identifier + ')</option>');
                        });
                    }
                },
                error: function (xhr) {
                    showAlert('Error al cargar sensores: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
                }
            });
        }

        // Cargar consumos
        function loadConsumptions() {
            const sensorId = $('#sensorFilter').val();
            const identifier = $('#identifierFilter').val();
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();

            const params = {
                page: currentPage,
                per_page: 15
            };
            if (sensorId) params.sensor_id = sensorId;
            if (identifier) params.identifier = identifier;
            if (startDate) params.start_date = startDate;
            if (endDate) params.end_date = endDate;
            if (filterOnlyCommunity) params.is_community = 1;

            $.ajax({
                url: '/api/consumptions',
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                },
                data: params,
                beforeSend: function () {
                    $('#consumptionsTable').html(
                        '<tr>' +
                        '   <td colspan="9" class="text-center">' +
                        '       <div class="spinner-border text-primary" role="status">' +
                        '           <span class="visually-hidden">Cargando...</span>' +
                        '       </div> Cargando consumos...' +
                        '   </td>' +
                        '</tr>'
                    );
                },
                success: function (response) {
                    if (response.success) {
                        renderConsumptions(response.data);
                        // Si la respuesta incluye paginación con meta, renderizarla
                        if (response.meta) {
                            renderPagination(response.meta);
                        }
                    } else {
                        showAlert(response.message || 'Error al cargar consumos', 'danger');
                    }
                },
                error: function (xhr) {
                    showAlert('Error al cargar consumos: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
                }
            });
        }

        // Renderizar consumos en la tabla
        function renderConsumptions(consumptions) {
            const tableBody = $('#consumptionsTable');
            tableBody.empty();

            if (!consumptions || consumptions.length === 0) {
                tableBody.append(
                    '<tr>' +
                    '   <td colspan="9" class="text-center">' +
                    '       <div class="empty-state">' +
                    '           <i class="bi bi-graph-up-arrow" style="font-size: 3rem;"></i>' +
                    '           <h4 class="mt-2">No hay consumos registrados</h4>' +
                    '           <p class="text-muted">Toma mediciones en tus sensores para generar consumos.</p>' +
                    '       </div>' +
                    '   </td>' +
                    '</tr>'
                );
                return;
            }

            // Filtrar sensores únicos para navegación modal
            analyzedSensorsList = [];
            const seen = new Set();
            consumptions.forEach(c => {
                if (c.sensor && !seen.has(c.sensor.id)) {
                    seen.add(c.sensor.id);
                    analyzedSensorsList.push({
                        id: c.sensor.id,
                        name: c.sensor.name || 'Sensor desconocido'
                    });
                }
            });

            consumptions.forEach(function (consumption) {
                // Asegurar que el sensor y grupo existan
                const sNameObj = consumption.sensor ? consumption.sensor.name : 'Sensor desconocido';
                const isCommunity = consumption.sensor && consumption.sensor.is_community ? '1' : '0';

                let sensorNameHtml = sNameObj;
                if (isCommunity === '1') {
                    sensorNameHtml += ' <span class="badge bg-success ms-1" style="font-size:0.6rem;"><i class="bi bi-tree-fill"></i> Común</span>';
                }

                const sensorIdentifier = (consumption.sensor && consumption.sensor.identifier) ? consumption.sensor.identifier : 'N/A';
                const groupName = (consumption.sensor && consumption.sensor.group && consumption.sensor.group.name) ? consumption.sensor.group.name : 'Sin grupo';
                const typeName = (consumption.sensor && consumption.sensor.group && consumption.sensor.group.template && consumption.sensor.group.template.type) ? consumption.sensor.group.template.type : 'N/A';

                const startDate = new Date(consumption.period_start).toLocaleString('es-ES');
                const endDate = new Date(consumption.period_end).toLocaleString('es-ES');
                const period = startDate + ' → ' + endDate;

                // Redondear días a 2 decimales
                const daysBetween = consumption.days_between ? parseFloat(consumption.days_between).toFixed(2) : 0;
                const dailyAverage = consumption.daily_average ? parseFloat(consumption.daily_average).toFixed(2) : 0;

                const row = '<tr>' +
                    '   <td>' + sensorNameHtml + '</td>' +
                    '   <td><code>' + sensorIdentifier + '</code></td>' +
                    '   <td><span class="badge bg-secondary">' + typeName.toUpperCase() + '</span></td>' +
                    '   <td>' + groupName + '</td>' +
                    '   <td>' + consumption.value + '</td>' +
                    '   <td>' + (consumption.unit || 'N/A') + '</td>' +
                    '   <td>' + period + '</td>' +
                    '   <td>' + daysBetween + '</td>' +
                    '   <td>' + dailyAverage + '</td>' +
                    '   <td>' +
                    '       <button class="btn btn-sm py-0 px-2 viewConsumptionBtn" data-consumption-id="' + consumption.id + '" title="Ver detalles">' +
                    '           <i class="bi bi-eye"></i>' +
                    '       </button>' +
                    '       <button class="btn btn-sm py-0 px-2 ms-1 analyzeSensorBtn" data-sensor-id="' + consumption.sensor.id + '" data-sensor-name="' + (consumption.sensor ? consumption.sensor.name : '') + '" title="Análisis de Rango">' +
                    '           <i class="bi bi-bar-chart-fill"></i>' +
                    '       </button>' +
                    '   </td>' +
                    '</tr>';

                tableBody.append(row);
            });

            // Asignar eventos a los botones de ver detalles
            $('.viewConsumptionBtn').click(function () {
                const consumptionId = $(this).data('consumption-id');
                viewConsumptionDetails(consumptionId);
            });

            // Asignar eventos a los botones de análisis
            $('.analyzeSensorBtn').click(function () {
                const sId = $(this).data('sensor-id');
                const sName = $(this).data('sensor-name');
                openAnalyticsModal(sId, sName);
            });
        }

        function openAnalyticsModal(sensorId, sensorName, autoCalc = false, inheritStartDate = null, inheritEndDate = null) {
            currentAnalyzedIndex = analyzedSensorsList.findIndex(s => s.id == sensorId);
            updateModalNavigationButtons();

            $('#analyzeSensorId').val(sensorId);
            $('#analyzeSensorName').text(sensorName);

            // set default values to current month if empty (preserve on navigation)
            if (inheritStartDate) {
                $('#analyzeStartDate').val(inheritStartDate);
            } else if (!$('#analyzeStartDate').val()) {
                const now = new Date();
                const startStr = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().substring(0, 10);
                $('#analyzeStartDate').val(startStr);
            }

            if (inheritEndDate) {
                $('#analyzeEndDate').val(inheritEndDate);
            } else if (!$('#analyzeEndDate').val()) {
                const now = new Date();
                const endStr = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().substring(0, 10);
                $('#analyzeEndDate').val(endStr);
            }

            $('#analyzeResults').addClass('d-none');
            $('#analyzeLoading').addClass('d-none');
            $('#metaAvailableDates').addClass('d-none');
            $('#metaLoadingStatus').removeClass('d-none');

            $('#analyzeSensorModal').modal('show');

            // Fetch meta boundaries
            $.ajax({
                url: '/api/consumptions/sensor-meta/' + sensorId,
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                },
                success: function (res) {
                    $('#metaLoadingStatus').addClass('d-none');
                    if (res.success && res.data.first_date && res.data.last_date) {
                        $('#metaFirstDate').text(new Date(res.data.first_date).toLocaleDateString());
                        $('#metaLastDate').text(new Date(res.data.last_date).toLocaleDateString());
                        $('#metaAvailableDates').removeClass('d-none');
                    }
                },
                error: function () {
                    $('#metaLoadingStatus').addClass('d-none');
                },
                complete: function () {
                    // Auto calculate if this was triggered via navigation
                    if (autoCalc) {
                        $('#btnCalculateRange').click();
                    }
                }
            });
        }

        function updateModalNavigationButtons() {
            $('#btnPrevAnalyzeSensor').prop('disabled', currentAnalyzedIndex <= 0);
            $('#btnNextAnalyzeSensor').prop('disabled', currentAnalyzedIndex >= analyzedSensorsList.length - 1 || currentAnalyzedIndex === -1);
        }

        $('#btnPrevAnalyzeSensor').click(function () {
            if (currentAnalyzedIndex > 0) {
                const prev = analyzedSensorsList[currentAnalyzedIndex - 1];
                openAnalyticsModal(prev.id, prev.name, true);
            }
        });

        $('#btnNextAnalyzeSensor').click(function () {
            if (currentAnalyzedIndex !== -1 && currentAnalyzedIndex < analyzedSensorsList.length - 1) {
                const nxt = analyzedSensorsList[currentAnalyzedIndex + 1];
                openAnalyticsModal(nxt.id, nxt.name, true);
            }
        });

        // Ver detalles de un consumo
        function viewConsumptionDetails(consumptionId) {
            $.ajax({
                url: '/api/consumptions/' + consumptionId,
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                },
                beforeSend: function () {
                    $('#consumptionDetailsContent').html(
                        '<div class="text-center">' +
                        '   <div class="spinner-border text-primary" role="status">' +
                        '       <span class="visually-hidden">Cargando...</span>' +
                        '   </div>' +
                        '   <p>Cargando detalles del consumo...</p>' +
                        '</div>'
                    );
                    $('#consumptionDetailsModal').modal('show');
                },
                success: function (response) {
                    if (response.success) {
                        renderConsumptionDetails(response.data);
                    } else {
                        $('#consumptionDetailsContent').html(
                            '<div class="alert alert-danger">' +
                            '   <i class="bi bi-exclamation-triangle"></i> ' + (response.message || 'Error al cargar detalles') +
                            '</div>'
                        );
                    }
                },
                error: function (xhr) {
                    const errorMessage = xhr.responseJSON?.message || xhr.statusText;
                    $('#consumptionDetailsContent').html(
                        '<div class="alert alert-danger">' +
                        '   <i class="bi bi-exclamation-triangle"></i> Error: ' + errorMessage +
                        '</div>'
                    );
                }
            });
        }

        // Renderizar detalles del consumo en el modal
        function renderConsumptionDetails(consumption) {
            const startDate = new Date(consumption.period_start).toLocaleString('es-ES');
            const endDate = new Date(consumption.period_end).toLocaleString('es-ES');
            const dailyAverage = consumption.daily_average || 0;

            let html = '<div class="row small">' +
                '   <div class="col-md-6 mb-3">' +
                '       <h6 class="border-bottom pb-2 text-primary fw-bold"><i class="bi bi-info-circle"></i> Información General</h6>' +
                '       <dl class="row mb-0">' +
                '           <dt class="col-sm-4 text-muted">ID:</dt>' +
                '           <dd class="col-sm-8 mb-1">' + consumption.id + '</dd>' +
                '           <dt class="col-sm-4 text-muted">Sensor:</dt>' +
                '           <dd class="col-sm-8 mb-1">' + (consumption.sensor?.name || 'N/A') + ' (<code>' + (consumption.sensor?.identifier || 'N/A') + '</code>)</dd>' +
                '           <dt class="col-sm-4 text-muted">Grupo:</dt>' +
                '           <dd class="col-sm-8 mb-1">' + (consumption.sensor?.group?.name || 'N/A') + '</dd>' +
                '           <dt class="col-sm-4 text-muted">Unidad:</dt>' +
                '           <dd class="col-sm-8 mb-1">' + consumption.unit + '</dd>' +
                '       </dl>' +
                '   </div>' +
                '   <div class="col-md-6 mb-3">' +
                '       <h6 class="border-bottom pb-2 text-primary fw-bold"><i class="bi bi-graph-up"></i> Datos del Consumo</h6>' +
                '       <dl class="row mb-0">' +
                '           <dt class="col-sm-4 text-muted">Valor:</dt>' +
                '           <dd class="col-sm-8 mb-1 fw-bold">' + consumption.value + ' ' + consumption.unit + '</dd>' +
                '           <dt class="col-sm-4 text-muted">Días:</dt>' +
                '           <dd class="col-sm-8 mb-1">' + consumption.days_between + '</dd>' +
                '           <dt class="col-sm-4 text-muted">Prom. Diario:</dt>' +
                '           <dd class="col-sm-8 mb-1">' + dailyAverage + ' ' + consumption.unit + '/día</dd>' +
                '       </dl>' +
                '   </div>' +
                '</div>' +
                '<div class="row small">' +
                '   <div class="col-md-12 mb-3">' +
                '       <h6 class="border-bottom pb-2 text-primary fw-bold"><i class="bi bi-calendar-range"></i> Período</h6>' +
                '       <dl class="row mb-0">' +
                '           <dt class="col-sm-2 text-muted">Inicio:</dt>' +
                '           <dd class="col-sm-4 mb-1">' + startDate + '</dd>' +
                '           <dt class="col-sm-2 text-muted">Fin:</dt>' +
                '           <dd class="col-sm-4 mb-1">' + endDate + '</dd>' +
                '       </dl>' +
                '   </div>' +
                '</div>';

            // Función para obtener el valor de una medición (soporta diferentes campos)
            function getMeasurementValue(measurement) {
                if (!measurement || !measurement.data) return 'N/A';

                // Intentar con campos comunes
                const fields = ['valor', 'consumo_m3', 'consumo', 'value', 'medicion'];
                for (const field of fields) {
                    if (measurement.data[field] !== undefined) {
                        return measurement.data[field];
                    }
                }

                // Si no se encuentra ningún campo conocido, devolver el primer valor numérico
                for (const [key, value] of Object.entries(measurement.data)) {
                    if (typeof value === 'number') {
                        return value;
                    }
                }

                return 'N/A';
            }

            // Agregar información de las mediciones si están disponibles
            if (consumption.start_measurement) {
                const startValue = getMeasurementValue(consumption.start_measurement) || 'N/A';
                const startMeasurementDate = new Date(consumption.start_measurement.measured_at).toLocaleString('es-ES');

                html += '<div class="row small mt-2">' +
                    '   <div class="col-md-6 mb-3">' +
                    '       <h6 class="border-bottom pb-2 text-primary fw-bold"><i class="bi bi-arrow-down-left"></i> Medición Inicial</h6>' +
                    '       <dl class="row mb-0">' +
                    '           <dt class="col-sm-4 text-muted">ID:</dt>' +
                    '           <dd class="col-sm-8 mb-1">' + consumption.start_measurement.id + '</dd>' +
                    '           <dt class="col-sm-4 text-muted">Valor:</dt>' +
                    '           <dd class="col-sm-8 mb-1">' + startValue + ' ' + consumption.unit + '</dd>' +
                    '           <dt class="col-sm-4 text-muted">Fecha:</dt>' +
                    '           <dd class="col-sm-8 mb-1">' + startMeasurementDate + '</dd>' +
                    '       </dl>' +
                    '   </div>';

                if (consumption.end_measurement) {
                    const endValue = getMeasurementValue(consumption.end_measurement) || 'N/A';
                    const endMeasurementDate = new Date(consumption.end_measurement.measured_at).toLocaleString('es-ES');

                    html += '   <div class="col-md-6 mb-3">' +
                        '       <h6 class="border-bottom pb-2 text-primary fw-bold"><i class="bi bi-arrow-up-right"></i> Medición Final</h6>' +
                        '       <dl class="row mb-0">' +
                        '           <dt class="col-sm-4 text-muted">ID:</dt>' +
                        '           <dd class="col-sm-8 mb-1">' + consumption.end_measurement.id + '</dd>' +
                        '           <dt class="col-sm-4 text-muted">Valor:</dt>' +
                        '           <dd class="col-sm-8 mb-1">' + endValue + ' ' + consumption.unit + '</dd>' +
                        '           <dt class="col-sm-4 text-muted">Fecha:</dt>' +
                        '           <dd class="col-sm-8 mb-1">' + endMeasurementDate + '</dd>' +
                        '       </dl>' +
                        '   </div>';
                }
                html += '</div>';
            }

            $('#consumptionDetailsContent').html(html);
        }

        // Calcular consumos para todos los sensores
        function calculateAllConsumptions() {
            if (!confirm('¿Estás seguro de que deseas recalcular todos los consumos? Esto puede tardar unos segundos.')) {
                return;
            }

            $.ajax({
                url: '/api/consumptions/calculate-all',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                },
                beforeSend: function () {
                    $('#calculateConsumption').prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Recalculando...'
                    );
                },
                success: function (response) {
                    if (response.success) {
                        showAlert(response.message || 'Consumos recalculados correctamente', 'success');
                        loadConsumptions();
                    } else {
                        showAlert(response.message || 'Error al recalcular consumos', 'danger');
                    }
                },
                error: function (xhr) {
                    showAlert('Error al recalcular consumos: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
                },
                complete: function () {
                    $('#calculateConsumption').prop('disabled', false).html(
                        '<i class="bi bi-calculator btn-icon"></i> Recalcular Consumos'
                    );
                }
            });
        }

        // Exportar consumos a Excel
        function exportConsumptions() {
            const sensorId = $('#sensorFilter').val();
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();

            const params = {};
            if (sensorId) params.sensor_id = sensorId;
            if (startDate) params.start_date = startDate;
            if (endDate) params.end_date = endDate;

            const url = '/api/consumptions/export?' + new URLSearchParams(params).toString();
            window.open(url, '_blank');
        }

        // Limpiar filtros
        function resetFilters() {
            $('#sensorFilter').val('');
            $('#identifierFilter').val('');
            $('#startDate').val('');
            $('#endDate').val('');
            currentPage = 1;
            loadConsumptions();
        }

        // Renderizar paginación
        function renderPagination(meta) {
            if (!meta || !meta.last_page) {
                $('#pagination').html('');
                $('#paginationInfo').html('');
                return;
            }

            const from = meta.from || 0;
            const to = meta.to || 0;
            const total = meta.total || 0;
            $('#paginationInfo').html(`Mostrando ${from} a ${to} de ${total} consumos`);

            let paginationHtml = '';

            // Función global para que sea accesible desde html inline si lo hay
            window.changePage = function (page) {
                // Obtenemos la referencia a través de scope o usamos la variable principal si existe
                if (typeof currentPage !== 'undefined') {
                    currentPage = page;
                } else {
                    // Intentar inyectar en el query param o recargar script local
                    window.currentPage = page;
                }
                loadConsumptions();
            };

            if (meta.current_page > 1) {
                paginationHtml += `
                                                                                                                        <li class="page-item">
                                                                                                                            <a class="page-link" href="#" onclick="event.preventDefault(); window.changePage(${meta.current_page - 1})" aria-label="Anterior">
                                                                                                                                <span aria-hidden="true">&laquo;</span>
                                                                                                                            </a>
                                                                                                                        </li>`;
            } else {
                paginationHtml += `
                                                                                                                        <li class="page-item disabled">
                                                                                                                            <span class="page-link" aria-hidden="true">&laquo;</span>
                                                                                                                        </li>`;
            }

            const maxPages = 5;
            let startPage = Math.max(1, meta.current_page - Math.floor(maxPages / 2));
            let endPage = Math.min(meta.last_page, startPage + maxPages - 1);

            if (endPage - startPage + 1 < maxPages) {
                startPage = Math.max(1, endPage - maxPages + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                if (i === meta.current_page) {
                    paginationHtml += `
                                                                                                                            <li class="page-item active">
                                                                                                                                <span class="page-link">${i}</span>
                                                                                                                            </li>`;
                } else {
                    paginationHtml += `
                                                                                                                            <li class="page-item">
                                                                                                                                <a class="page-link" href="#" onclick="event.preventDefault(); window.changePage(${i})">${i}</a>
                                                                                                                            </li>`;
                }
            }

            if (meta.current_page < meta.last_page) {
                paginationHtml += `
                                                                                                                        <li class="page-item">
                                                                                                                            <a class="page-link" href="#" onclick="event.preventDefault(); window.changePage(${meta.current_page + 1})" aria-label="Siguiente">
                                                                                                                                <span aria-hidden="true">&raquo;</span>
                                                                                                                            </a>
                                                                                                                        </li>`;
            } else {
                paginationHtml += `
                                                                                                                        <li class="page-item disabled">
                                                                                                                            <span class="page-link" aria-hidden="true">&raquo;</span>
                                                                                                                        </li>`;
            }

            $('#pagination').html(paginationHtml);
        }

        // Mostrar alerta
        function showAlert(message, type) {
            const alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                '</div>';
            $('.card-body').prepend(alertHtml);
        }

        $('#btnCalculateRange').click(function () {
            const sensorId = $('#analyzeSensorId').val();
            const start = $('#analyzeStartDate').val();
            const end = $('#analyzeEndDate').val();
            const threshold = $('#analyzeThreshold').val() || 50;
            const stagnation = $('#analyzeStagnation').val() || 15;

            if (!start || !end) {
                alert('Por favor selecciona ambas fechas.');
                return;
            }

            $('#analyzeResults').addClass('d-none');
            $('#analyzeLoading').removeClass('d-none');

            $.ajax({
                url: '/api/consumptions/calculate-range',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                },
                data: {
                    sensor_id: sensorId,
                    start_date: start,
                    end_date: end,
                    anomaly_threshold: threshold,
                    stagnation_days: stagnation
                },
                success: function (res) {
                    $('#analyzeLoading').addClass('d-none');
                    if (res.success && res.data) {
                        const d = res.data;

                        $('#resTotalDelta').text(d.total_consumption + ' ' + d.unit);
                        $('#resCommunityContribution').text('+' + (d.community_contribution || 0).toFixed(2) + ' ' + d.unit);
                        $('#resFinalBilledTotal').text((d.final_billed_total || d.total_consumption).toFixed(2) + ' ' + d.unit);
                        $('#resDailyAvg').text(d.daily_average + ' ' + d.unit);
                        $('#resDaysBetween').text(parseFloat(d.days_between).toFixed(0));
                        $('#resMeasurementsCount').text(d.measurements_count);
                        $('#resTotalPeriod').text('entre ' + new Date(d.period_start).toLocaleDateString() + ' y ' + new Date(d.period_end).toLocaleDateString());

                        $('#resStartLog').text(new Date(d.period_start).toLocaleString('es-ES'));
                        $('#resEndLog').text(new Date(d.period_end).toLocaleString('es-ES'));
                        $('#resStartVal').text(d.start_value + ' ' + d.unit);
                        $('#resEndVal').text(d.end_value + ' ' + d.unit);
                        $('#infoThreshold').text(threshold);
                        $('#infoStagnation').text(stagnation);

                        renderEvolutionChart(d.chart_data, d.unit);

                        // Poblar la Auditoría Visual si hay anomalías
                        const grid = $('#anomaliesInspectionGrid');
                        grid.empty();

                        const anomalies = d.chart_data.filter(c => c.anomaly);
                        if (anomalies.length > 0) {
                            anomalies.forEach((anom, idx) => {
                                let imgPath = anom.photo && anom.photo !== 'Sin Foto'
                                    ? (anom.photo.startsWith('http') ? anom.photo : '/' + anom.photo)
                                    : null;

                                let imgHtml = imgPath
                                    ? '<a href="' + imgPath + '" target="_blank"><img src="' + imgPath + '" class="img-fluid rounded border mt-2" style="max-height:120px; object-fit:cover; width:100%" alt="Foto medición"></a>'
                                    : '<div class="text-muted small mt-2 p-3 bg-white border rounded text-center"><i class="bi bi-camera-video-off"></i> Sin registro fotográfico</div>';

                                grid.append(
                                    '<div class="col-md-4">' +
                                    '   <div class="card h-100 border-danger shadow-sm">' +
                                    '       <div class="card-header bg-danger text-white py-1 small">' +
                                    '           <i class="bi bi-exclamation-triangle-fill"></i> Punto Atípico #' + (idx + 1) +
                                    '       </div>' +
                                    '       <div class="card-body p-2">' +
                                    '           <div class="fw-bold">' + anom.date + '</div>' +
                                    '           <div class="text-danger">Sensado: <strong>' + anom.value + '</strong> ' + d.unit + '</div>' +
                                    '           <div class="mt-2 text-center">' + imgHtml + '</div>' +
                                    '       </div>' +
                                    '   </div>' +
                                    '</div>'
                                );
                            });
                            $('#anomaliesInspectionContainer').removeClass('d-none');
                        } else {
                            $('#anomaliesInspectionContainer').addClass('d-none');
                        }

                        $('#analyzeResults').removeClass('d-none');
                    } else {
                        alert(res.message || 'Error en cálculo.');
                    }
                },
                error: function (xhr) {
                    $('#analyzeLoading').addClass('d-none');
                    alert(xhr.responseJSON?.message || 'Hubo un problema. No hay mediciones válidas para tu solicitud.');
                }
            });
        });

        function renderEvolutionChart(chartData, unit) {
            const ctx = document.getElementById('evolutionChart').getContext('2d');

            if (evolutionChartInstance) {
                evolutionChartInstance.destroy();
            }

            if (!chartData || chartData.length === 0) return;

            const labels = chartData.map(c => c.date);
            const dataValues = chartData.map(c => c.value);

            // Map colors: red if anomaly, otherwise standard blue 
            // Also increase radius for anomalies
            const pointColors = chartData.map(c => c.anomaly ? 'rgba(220, 53, 69, 1)' : 'rgba(13, 110, 253, 1)');
            const pointRadius = chartData.map(c => c.anomaly ? 6 : 3);

            evolutionChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Consumo registrado',
                        data: dataValues,
                        borderColor: 'rgba(13, 110, 253, 0.4)',
                        backgroundColor: 'rgba(13, 110, 253, 0.05)',
                        borderWidth: 2,
                        pointBackgroundColor: pointColors,
                        pointBorderColor: pointColors,
                        pointRadius: pointRadius,
                        pointHoverRadius: 8,
                        fill: true,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const raw = context.raw;
                                    const pointData = chartData[context.dataIndex];
                                    let label = raw + ' ' + unit;
                                    if (pointData.anomaly) {
                                        label += ' (⚠️ Salto atípico)';
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            title: {
                                display: true,
                                text: unit,
                                font: { size: 10 }
                            },
                            ticks: { font: { size: 10 } }
                        },
                        x: {
                            ticks: {
                                font: { size: 9 },
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });
        }

        function openGlobalRadarModal() {
            // Cargar de localstorage
            const storedThreshold = localStorage.getItem('medflow_analyze_threshold');
            if (storedThreshold) {
                $('#radarThreshold').val(storedThreshold);
            }

            const storedStagnation = localStorage.getItem('medflow_analyze_stagnation');
            if (storedStagnation) {
                $('#radarStagnation').val(storedStagnation);
            }

            if (!$('#radarStartDate').val()) {
                const now = new Date();
                const startStr = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().substring(0, 10);
                $('#radarStartDate').val(startStr);
            }
            if (!$('#radarEndDate').val()) {
                const now = new Date();
                const endStr = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().substring(0, 10);
                $('#radarEndDate').val(endStr);
            }

            $('#radarResults').addClass('d-none');
            $('#radarLoading').addClass('d-none');
            $('#globalRadarModal').modal('show');
        }

        $('#btnScanGlobal').click(function () {
            const start = $('#radarStartDate').val();
            const end = $('#radarEndDate').val();
            const threshold = $('#radarThreshold').val() || 50;
            const stagnation = $('#radarStagnation').val() || 15;

            // Save preferences
            localStorage.setItem('medflow_analyze_threshold', threshold);
            localStorage.setItem('medflow_analyze_stagnation', stagnation);

            if (!start || !end) {
                alert('Selecciona inicio y fin.');
                return;
            }

            $('#radarResults').addClass('d-none');
            $('#radarLoading').removeClass('d-none');

            $.ajax({
                url: '/api/consumptions/global-anomalies',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                },
                data: {
                    start_date: start,
                    end_date: end,
                    anomaly_threshold: threshold,
                    stagnation_days: stagnation
                },
                success: function (res) {
                    $('#radarLoading').addClass('d-none');
                    if (res.success && res.data) {
                        $('#radarCount').text(res.data.length);
                        const tbody = $('#radarTableBody');
                        tbody.empty();

                        if (res.data.length === 0) {
                            tbody.append('<tr><td colspan="3" class="text-center text-success"><i class="bi bi-check-circle-fill"></i> Todos los sistemas bajo límites nominales. Cero anomalías temporales encontradas.</td></tr>');
                        } else {
                            res.data.forEach(function (item) {
                                let tagsHtml = '';
                                if (item.acceleration_count > 0) {
                                    tagsHtml += '<span class="badge bg-danger shadow-sm me-1 mt-1" title="Saltos Acelerados"><i class="bi bi-graph-up-arrow"></i> ' + item.acceleration_count + ' picos</span>';
                                }
                                if (item.stagnation_count > 0) {
                                    tagsHtml += '<span class="badge bg-secondary shadow-sm me-1 mt-1" title="Días 0"><i class="bi bi-hourglass-bottom"></i> ' + item.stagnation_count + ' estancados</span>';
                                }

                                tbody.append(
                                    '<tr>' +
                                    '   <td><strong>' + item.sensor_name + '</strong> <br><code class="small">' + item.sensor_identifier + '</code></td>' +
                                    '   <td>' + tagsHtml + '</td>' +
                                    '   <td>' +
                                    '       <button class="btn btn-sm btn-primary mt-1" onclick="$(\'#globalRadarModal\').modal(\'hide\'); setTimeout(()=> { openAnalyticsModal(' + item.sensor_id + ', \'' + item.sensor_name + '\', true, \'' + start + '\', \'' + end + '\'); }, 400);"><i class="bi bi-bar-chart-fill"></i> Zoom</button>' +
                                    '   </td>' +
                                    '</tr>'
                                );
                            });
                        }
                        $('#radarResults').removeClass('d-none');
                        // Reset search field
                        $('#radarSearch').val('');
                    } else {
                        alert(res.message);
                    }
                },
                error: function (xhr) {
                    $('#radarLoading').addClass('d-none');
                    alert(xhr.responseJSON?.message || 'Error escaneando anomalías masivas.');
                }
            });
        });

        // Filtrado en vivo del radar global
        $('#radarSearch').on('keyup', function () {
            const value = $(this).val().toLowerCase();
            $('#radarTableBody tr').filter(function () {
                // If it's the "no errors" row, ignore it
                if ($(this).find('.bi-check-circle-fill').length > 0) return;

                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    
        $('#btnShareAnalysis').click(function() {
            const sensorId = $('#analyzeSensorId').val();
            if (!sensorId) return;

            const sensorObj = analyzedSensorsList.find(s => s.id == sensorId);
            const select = $('#shareEmailSelect');
            const input = $('#shareEmailInput');
            
            select.empty();
            select.append('<option value="">-- Ingresar Manualmente --</option>');
            let hasEmails = false;

            if (sensorObj && sensorObj.metadata) {
                for (const [key, value] of Object.entries(sensorObj.metadata)) {
                    if (value && typeof value === 'string' && value.includes('@')) {
                        select.append(`<option value="${value}">${key}: ${value}</option>`);
                        hasEmails = true;
                    }
                }
            }

            if (hasEmails) {
                select.removeClass('d-none');
                input.val(select.val());
            } else {
                select.addClass('d-none');
                input.val('');
            }
            
            select.off('change').on('change', function() {
                if($(this).val()) {
                    input.val($(this).val());
                } else {
                    input.val('');
                }
            });

            $('#shareReportModal').modal('show');
        });

        $('#btnConfirmShare').click(function() {
            const sensorId = $('#analyzeSensorId').val();
            const email = $('#shareEmailInput').val();
            
            if (!email) {
                alert('Debe ingresar un correo válido.');
                return;
            }

            const btn = $(this);
            const originalText = btn.html();
            btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Enviando...');

            $.ajax({
                url: '/api/sensors/' + sensorId + '/share',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                },
                data: { email: email },
                success: function (res) {
                    btn.prop('disabled', false).html(originalText);
                    if(res.success || res.message) {
                        alert('Informe enviado correctamente a ' + email);
                        $('#shareReportModal').modal('hide');
                    } else {
                        alert('Error: ' + (res.message || 'Error desconocido'));
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(originalText);
                    alert('Error de conexión al enviar informe.');
                }
            });
        });
    </script>
@endpush