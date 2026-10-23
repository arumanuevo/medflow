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

        @keyframes pulse-subtle {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.6); }
            50% { transform: scale(1.03); box-shadow: 0 0 0 8px rgba(220, 53, 69, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }

        .pulse-audit-btn {
            animation: pulse-subtle 2.5s infinite ease-in-out;
            font-weight: 600 !important;
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
        .card-header-tools .btn {
            font-size: 0.78rem;
            padding: 0.22rem 0.55rem;
            white-space: nowrap;
            line-height: 1.35;
        }

        @media (max-width: 576px) {
            .card-header-tools {
                width: 100%;
                justify-content: flex-start;
            }
        }

        @media (max-width: 992px) {
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

            .measurement-table-wrapper {
                overflow-x: auto !important;
                max-width: 100% !important;
                width: 100% !important;
                -webkit-overflow-scrolling: touch;
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
                    <div class="card-header bg-primary text-white py-2 px-3">
                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap flex-xl-nowrap">
                            <h5 class="mb-0 fw-bold d-flex align-items-center gap-2" style="font-size: 1.05rem; white-space: nowrap;">
                                <i class="bi bi-graph-up-arrow"></i>
                                <span>Mediciones y Consumos</span>
                                <span id="totalCountBadge" class="badge bg-light text-dark ms-1"
                                    style="font-size: 0.72rem; padding: 0.25em 0.55em;">0</span>
                            </h5>

                            <div class="card-header-tools d-flex align-items-center flex-wrap gap-1 gap-md-2 justify-content-end">
                                <button class="btn btn-warning btn-sm" id="btnOpenGlobalRadar" title="Escanear anomalías en todos los sensores">
                                    <i class="bi bi-radar me-1"></i> Radar Global
                                </button>
                                <button class="btn btn-info text-white btn-sm" id="btnBulkUploadPhotos" title="Subir lote de fotos o archivo .ZIP">
                                    <i class="bi bi-cloud-arrow-up me-1"></i> Carga Masiva Fotos
                                </button>
                                <button class="btn btn-success btn-sm" id="btnExportExcel" title="Exportar registros filtrados a Excel">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Exportar a Excel
                                </button>
                                <button class="btn btn-outline-light btn-sm d-inline-flex align-items-center" id="toggleErrors" title="Mostrar u ocultar auditoría de errores">
                                    <i class="bi bi-shield-exclamation me-1" id="toggleErrorsIcon"></i>
                                    <span id="toggleErrorsText">Auditoría</span>
                                    <span id="auditBadge" class="badge rounded-pill ms-1 d-none" style="font-size: 0.65rem;"></span>
                                </button>
                                <a href="{{ route('sensor-groups.index') }}" class="btn btn-light btn-sm" title="Ir al listado y gestión de Grupos de Sensores">
                                    <i class="bi bi-folder2-open me-1"></i> Ver Grupos
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

                        <!-- Panel Unificado de Filtros y Búsqueda -->
                        <div class="card mb-3 border-primary-subtle shadow-sm" id="unifiedFilterContainer" style="background-color: #f8fbff; border-radius: 10px;">
                            <div class="card-body p-2 p-md-3">
                                <!-- Cabecera del Panel de Filtros -->
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 pb-2 border-bottom border-primary-subtle">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary px-2 py-1"><i class="bi bi-funnel-fill"></i> Panel de Filtros y Búsqueda</span>
                                        <span class="small fw-semibold text-primary">Criterios fijos, plantillas, auditoría y atributos dinámicos</span>
                                        <span class="badge rounded-pill bg-info text-dark d-none" id="activeAdvancedFiltersBadge">0 filtros</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-muted" id="btnToggleAdvancedHelp" title="Ver ayuda sobre cómo buscar y cruzar criterios">
                                            <i class="bi bi-question-circle"></i> Ayuda de consulta
                                        </button>
                                    </div>
                                </div>

                                <!-- Fila 1: Filtros de Entidad / Medidor / Plantilla / Auditoría -->
                                <div class="row g-2 mb-2">
                                    <div class="col-xl-2 col-md-4 col-sm-6">
                                        <label for="sensorFilter" class="form-label small fw-semibold text-muted mb-1">Medidor / Sensor</label>
                                        <select class="form-select form-select-sm" id="sensorFilter">
                                            <option value="" selected>Todos los sensores</option>
                                            @foreach($sensors as $sensor)
                                                <option value="{{ $sensor->id }}"
                                                    title="{{ $sensor->name }} ({{ $sensor->identifier }})">
                                                    {{ Str::limit($sensor->name, 22) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-2 col-md-4 col-sm-6">
                                        <label for="identifierFilter" class="form-label small fw-semibold text-muted mb-1">Identificador</label>
                                        <input type="text" class="form-control form-control-sm" id="identifierFilter"
                                            placeholder="Ej: SN-1234, 144, 178">
                                    </div>
                                    <div class="col-xl-2 col-md-4 col-sm-6">
                                        <label for="groupFilter" class="form-label small fw-semibold text-muted mb-1">Grupo</label>
                                        <select class="form-select form-select-sm" id="groupFilter">
                                            <option value="" selected>Todos los grupos</option>
                                            @foreach($groups as $group)
                                                <option value="{{ $group->id }}" title="{{ $group->name }}">
                                                    {{ Str::limit($group->name, 22) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-2 col-md-4 col-sm-6">
                                        <label for="templateFilter" class="form-label small fw-semibold text-muted mb-1">Tipo de Plantilla</label>
                                        <select class="form-select form-select-sm" id="templateFilter">
                                            <option value="" selected>Todas las plantillas</option>
                                            @if(isset($templates))
                                                @foreach($templates as $tmpl)
                                                    <option value="{{ $tmpl->id }}" title="{{ $tmpl->name }}">
                                                        {{ Str::limit($tmpl->name, 22) }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-xl-2 col-md-4 col-sm-6">
                                        <label for="errorFilter" class="form-label small fw-semibold text-muted mb-1">Estado de Auditoría</label>
                                        <select class="form-select form-select-sm" id="errorFilter">
                                            <option value="" selected>Todos los estados</option>
                                            <option value="first_measurement">1ra Medición</option>
                                            <option value="negative_consumption">Aviso Consumo (Negativo)</option>
                                            <option value="inconsistent_date">Aviso Fecha (Desfasada)</option>
                                            <option value="valid">Válidas (Sin anomalías)</option>
                                            <option value="any_error">Cualquier anomalía</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-2 col-md-4 col-sm-6">
                                        <label for="photoFilter" class="form-label small fw-semibold text-muted mb-1">Fotografía</label>
                                        <select class="form-select form-select-sm" id="photoFilter">
                                            <option value="" selected>Todas (con/sin foto)</option>
                                            <option value="with_photo">Con foto adjunta</option>
                                            <option value="without_photo">Sin foto adjunta</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Fila 2: Consumo / Fechas / Motor de Atributos Dinámicos -->
                                <div class="row g-2 mb-3">
                                    <div class="col-xl-2 col-md-4 col-sm-6">
                                        <label for="consumptionFilter" class="form-label small fw-semibold text-muted mb-1">Consumo / Costo</label>
                                        <select class="form-select form-select-sm" id="consumptionFilter">
                                            <option value="" selected>Todos los consumos</option>
                                            <option value="positive">Consumo positivo (&gt; 0)</option>
                                            <option value="zero">Consumo en cero (= 0)</option>
                                            <option value="negative">Consumo negativo (&lt; 0)</option>
                                            <option value="with_cost">Con tarifa de costo activa</option>
                                            <option value="without_cost">Sin tarifa de costo</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-2 col-md-4 col-sm-6">
                                        <label for="dateFrom" class="form-label small fw-semibold text-muted mb-1">Fecha desde</label>
                                        <input type="date" class="form-control form-control-sm" id="dateFrom">
                                    </div>
                                    <div class="col-xl-2 col-md-4 col-sm-6">
                                        <label for="dateTo" class="form-label small fw-semibold text-muted mb-1">Fecha hasta</label>
                                        <input type="date" class="form-control form-control-sm" id="dateTo">
                                    </div>
                                    <div class="col-xl-2 col-md-4 col-sm-6">
                                        <label for="customFieldSelect" class="form-label small fw-semibold text-muted mb-1">Campo Personalizado</label>
                                        <select class="form-select form-select-sm" id="customFieldSelect">
                                            <option value="">-- Cualquier campo dinámico --</option>
                                            @if(isset($availableExtraFields))
                                                @foreach($availableExtraFields as $ef)
                                                    <option value="{{ $ef }}">{{ ucfirst(str_replace('_', ' ', $ef)) }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-xl-2 col-md-4 col-sm-6">
                                        <label for="customOperatorSelect" class="form-label small fw-semibold text-muted mb-1">Condición / Cruce</label>
                                        <select class="form-select form-select-sm" id="customOperatorSelect">
                                            <option value="or" selected>Coincide con alguno (Lista: ej. 100, 144)</option>
                                            <option value="and">Contiene todas las palabras en el mismo campo (AND)</option>
                                            <option value="exact">Valor exacto</option>
                                            <option value="not">Excluir / No contiene</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-2 col-md-4 col-sm-6">
                                        <label for="customValuesInput" class="form-label small fw-semibold text-muted mb-1">Valores a buscar</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control form-control-sm" id="customValuesInput"
                                                placeholder="Ej: 144, 178">
                                            <button class="btn btn-outline-secondary" type="button" id="btnClearCustomValues" title="Limpiar este campo">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Traducción Semántica en Lenguaje Natural -->
                                <div class="p-2 px-3 rounded-2 mb-2 border border-primary-subtle d-flex align-items-center gap-2" style="background-color: #ffffff;" id="semanticQueryContainer">
                                    <span class="badge bg-primary text-white flex-shrink-0" style="font-size: 0.72rem; padding: 0.35rem 0.55rem;">
                                        <i class="bi bi-chat-left-quote-fill me-1"></i> Interpretación:
                                    </span>
                                    <div class="text-dark small flex-grow-1" id="semanticQueryText" style="font-size: 0.82rem; line-height: 1.4;">
                                        Mostrando todas las mediciones registradas en el sistema (sin filtros aplicados).
                                    </div>
                                </div>

                                <!-- Ayuda contextual desplegable -->
                                <div class="mb-2 small text-muted d-none" id="advancedHelpText" style="font-size: 0.78rem; background: #ffffff; padding: 0.6rem; border-radius: 6px; border: 1px dashed #cbd5e1;">
                                    <i class="bi bi-info-circle text-primary me-1"></i>
                                    <strong>¿Cómo combinar filtros?</strong> Todos los criterios seleccionados se cruzan simultáneamente. Por ejemplo, al elegir el Grupo <em>Barrio Norte</em>, Plantilla <em>Agua</em>, Campo Personalizado <em>Lote</em> con valores <code>144, 178</code> (OR) y Estado <em>1ra Medición</em>, el sistema recuperará únicamente las mediciones que cumplan <strong>todas</strong> esas condiciones a la vez.
                                </div>

                                <!-- Barra de Acciones del Panel de Filtros -->
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-1 border-top border-light-subtle">
                                    <div class="text-muted small">
                                        <i class="bi bi-info-circle me-1"></i><em>Presiona 'Filtrar' o pulsa Enter para actualizar los resultados.</em>
                                    </div>
                                    <div class="d-flex gap-2 align-items-center flex-wrap">
                                        <button class="btn btn-sm btn-outline-success" id="toggleCommunityFilterBtn"
                                            style="white-space:nowrap;">
                                            <i class="bi bi-tree-fill"></i> Áreas Comunes
                                        </button>
                                        <button class="btn btn-sm btn-secondary" id="resetFilters">
                                            <i class="bi bi-arrow-clockwise"></i> Limpiar Filtros
                                        </button>
                                        <button class="btn btn-sm btn-primary" id="applyFilters">
                                            <i class="bi bi-funnel"></i> Filtrar Resultados
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de Mediciones -->
                        <div class="measurement-table-wrapper table-responsive p-2">
                            <table class="table table-bordered table-striped table-hover mb-0" id="measurementsTable">
                                <thead>
                                    <tr>
                                        <!-- Checkbox para eliminar varios -->
                                        <th style="width: 40px; text-align: center;">
                                            <input class="form-check-input" type="checkbox" id="selectAllMeasurements">
                                        </th>
                                        <th class="sortable" data-field="sensor_id" style="cursor: pointer;">
                                            Sensor <i class="bi bi-arrow-down-up text-muted ms-1"
                                                style="font-size: 0.75rem;"></i>
                                        </th>
                                        <th class="sortable" data-field="identifier" style="cursor: pointer;">
                                            Identificador <i class="bi bi-arrow-down-up text-muted ms-1"
                                                style="font-size: 0.75rem;"></i>
                                        </th>
                                        <th class="sortable" data-field="group_name" style="cursor: pointer;">
                                            Grupo <i class="bi bi-arrow-down-up text-muted ms-1"
                                                style="font-size: 0.75rem;"></i>
                                        </th>
                                        <th>Plantilla</th>
                                        <th class="sortable" data-field="valor" style="cursor: pointer;">
                                            Valor <i class="bi bi-arrow-down-up text-muted ms-1"
                                                style="font-size: 0.75rem;"></i>
                                        </th>
                                        <th>Datos Dinámicos (Medición)</th>
                                        <th class="sortable" data-field="measured_at" style="cursor: pointer;">
                                            Fecha <i class="bi bi-arrow-down text-primary ms-1"
                                                style="font-size: 0.75rem;"></i>
                                        </th>
                                        <th>Consumo / Costo</th>
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

    <!-- Modal para ver foto en grande con Zoom, Paneo, Rotación y Mejora de Lectura -->
    <div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="photoModalLabel">Evidencia Fotográfica</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    {{-- Barra de herramientas de Zoom / Rotación / Contraste --}}
                    <div id="photoZoomToolbar" class="d-flex flex-wrap justify-content-center align-items-center gap-2 mb-2 p-2 bg-light border rounded-3">
                        <div class="btn-group btn-group-sm" role="group" aria-label="Controles de Zoom">
                            <button type="button" class="btn btn-outline-secondary" id="btnZoomOut" title="Alejar (-)">
                                <i class="bi bi-zoom-out"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary fw-bold px-2" id="btnZoomReset" title="Restablecer vista (100%)" style="min-width: 62px;">
                                100%
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnZoomIn" title="Acercar (+)">
                                <i class="bi bi-zoom-in"></i>
                            </button>
                        </div>

                        <div class="d-flex align-items-center gap-1 px-1" style="width: 140px;">
                            <input type="range" class="form-range" id="photoZoomSlider" min="1" max="5" step="0.1" value="1" title="Nivel de Zoom (100% a 500%)">
                        </div>

                        <div class="btn-group btn-group-sm" role="group" aria-label="Controles de Rotación">
                            <button type="button" class="btn btn-outline-secondary" id="btnRotateLeft" title="Girar 90° a la izquierda">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnRotateRight" title="Girar 90° a la derecha">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-dark" id="btnEnhancePhoto" title="Aumentar contraste y nitidez para leer mejor los dígitos del medidor">
                            <i class="bi bi-brightness-high"></i> Mejorar lectura
                        </button>
                    </div>

                    {{-- Visor interactivo con soporte de Rueda de Mouse, Arrastrar (Pan) y Doble Clic --}}
                    <div id="photoViewerViewport"
                         class="position-relative overflow-hidden rounded-3 border bg-dark d-flex align-items-center justify-content-center mb-2"
                         style="height: 54vh; cursor: grab; user-select: none; touch-action: none;">
                        <img id="modalPhoto"
                             src=""
                             alt="Foto de la medición"
                             draggable="false"
                             style="max-height: 100%; max-width: 100%; object-fit: contain; transform-origin: center center; transition: transform 0.08s ease-out; will-change: transform;">
                        <div class="position-absolute bottom-0 start-50 translate-middle-x mb-2 px-2 py-1 rounded bg-dark bg-opacity-75 text-white-50"
                             style="font-size: 0.7rem; pointer-events: none;">
                            💡 Usa la rueda del mouse o el control superior para hacer zoom · Arrastra para mover · Doble clic para acercar (2.5x)
                        </div>
                    </div>

                    <div id="photoUploadContainer" class="mt-3 p-3 bg-light rounded text-start">
                        <label class="form-label fw-bold">Adjuntar o Reemplazar Foto (Manual)</label>
                        <input type="hidden" id="uploadPhotoMeasurementsId" value="">
                        <div class="input-group">
                            <input type="file" class="form-control" id="newPhotoInput" accept="image/*">
                            <button class="btn btn-success" type="button" id="submitNewPhotoBtn"><i
                                    class="bi bi-cloud-upload"></i> Subir</button>
                        </div>
                        <div id="uploadResult" class="mt-2 text-center" style="display:none;"></div>
                    </div>
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
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold" id="errorDetailsModalLabel">
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

    <!-- Modal para Edición Rápida de Medición (Corrección de Tipeo / Fecha) -->
    <div class="modal fade" id="quickEditMeasurementModal" tabindex="-1" aria-labelledby="quickEditMeasurementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="quickEditMeasurementModalLabel">
                        <i class="bi bi-pencil-square me-2"></i> Corregir Medición
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <!-- Foto de evidencia asociada -->
                    <div id="quickEditPhotoContainer" class="p-2 mb-3 bg-light border rounded d-flex align-items-center justify-content-between d-none">
                        <div class="d-flex align-items-center gap-2">
                            <img id="quickEditPhotoThumb" src="" alt="Foto Medidor" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; cursor: pointer;" class="border btn-inspect-quick-photo" title="Clic para ampliar foto">
                            <div>
                                <strong class="d-block small text-dark"><i class="bi bi-camera me-1"></i> Foto de evidencia disponible</strong>
                                <small class="text-muted">Verifica los números reales del medidor.</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm btn-inspect-quick-photo" id="btnQuickEditOpenPhoto">
                            <i class="bi bi-zoom-in me-1"></i> Ver Foto
                        </button>
                    </div>
                    <div id="quickEditNoPhotoNotice" class="text-muted small mb-3 d-none">
                        <i class="bi bi-image text-secondary me-1"></i> Esta medición no posee foto adjunta.
                    </div>

                    <!-- Contexto secuencial (anterior y siguiente) -->
                    <div class="card bg-light border-0 p-2 mb-3 small">
                        <div class="fw-bold mb-1 text-secondary"><i class="bi bi-clock-history me-1"></i> Línea de tiempo secuencial:</div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">• Lectura Anterior:</span>
                            <strong id="quickEditPrevInfo" class="text-dark">—</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">• Lectura Siguiente:</span>
                            <strong id="quickEditNextInfo" class="text-dark">—</strong>
                        </div>
                        <div id="quickEditSafeRangeNotice" class="text-primary fw-semibold border-top pt-1 mt-1 small"></div>
                    </div>

                    <form id="quickEditMeasurementForm" onsubmit="return false;">
                        <input type="hidden" id="quickEditMeasurementId">
                        <div class="mb-3">
                            <label class="form-label fw-bold">ID Medición</label>
                            <input type="text" class="form-control" id="quickEditDisplayId" readonly disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Lectura (Valor) <span class="text-danger">*</span></label>
                            <input type="number" step="any" class="form-control" id="quickEditValue" required placeholder="Ej: 1450.5">
                            <div class="form-text">Ingresa la lectura correcta visualizada en la foto o planilla.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Fecha y Hora <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="quickEditDate" required>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch mb-1">
                                <input class="form-check-input" type="checkbox" id="quickEditForce" checked>
                                <label class="form-check-label fw-bold text-dark" for="quickEditForce">
                                    Forzar corrección histórica
                                </label>
                            </div>
                            <div class="alert alert-warning py-2 px-3 small mb-0 border-0 shadow-sm" style="background-color: #fff8e1;">
                                <div class="fw-semibold text-warning-emphasis mb-1">
                                    <i class="bi bi-info-circle-fill me-1"></i> ¿Qué significa este parámetro?
                                </div>
                                <ul class="mb-0 ps-3 text-secondary" style="font-size: 0.8rem; line-height: 1.35;">
                                    <li><strong>Desactivado (Estricto):</strong> El sistema valida rigurosamente que el nuevo valor no supere la lectura siguiente ni sea menor a la anterior. Si supera la siguiente, lo bloquea para que la medición posterior no quede con consumo negativo.</li>
                                    <li><strong>Activado (Forzar):</strong> Omite el bloqueo si la medición siguiente tiene un valor menor. Úsalo si la siguiente medición <em>también</em> tenía un error de tipeo y planeas corregirla luego, o si estás arreglando una serie histórica en lote.</li>
                                </ul>
                            </div>
                        </div>
                        <div id="quickEditAlert" class="alert alert-danger d-none mb-0"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSaveQuickEdit">
                        <i class="bi bi-check-lg me-1"></i> Guardar Corrección
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Confirmación de Cambio de Medidor (Reseteo / Muro Cortafuegos) -->
    <div class="modal fade" id="confirmResetModal" tabindex="-1" aria-labelledby="confirmResetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold" id="confirmResetModalLabel">
                        <i class="bi bi-arrow-repeat me-2"></i> Registrar Cambio de Medidor (Reseteo)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p>¿Deseas registrar esta medición como un <strong>Reemplazo de Medidor Físico</strong> o <strong>Reseteo de Contador</strong>?</p>
                    <div class="card bg-light p-3 mb-3 border-0">
                        <div><strong>Medición ID:</strong> <span id="resetModalDisplayId"></span></div>
                        <div><strong>Sensor:</strong> <span id="resetModalDisplaySensor"></span></div>
                        <div><strong>Lectura Inicial (Nuevo Medidor):</strong> <span id="resetModalDisplayValue" class="badge bg-primary"></span></div>
                        <div><strong>Fecha:</strong> <span id="resetModalDisplayDate"></span></div>
                    </div>
                    <div class="alert alert-info py-2 small mb-0">
                        <i class="bi bi-shield-check me-1"></i>
                        <strong>Muro Cortafuegos:</strong> El sistema establecerá esta lectura como nueva línea de base. El cálculo de consumo no será negativo y las mediciones posteriores continuarán normalmente a partir de este nuevo medidor.
                    </div>
                    <input type="hidden" id="resetModalMeasurementId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning fw-bold" id="btnConfirmReset">
                        <i class="bi bi-check-circle me-1"></i> Confirmar Cambio de Medidor
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Atributos y Campos Personalizados del Sensor -->
    <div class="modal fade" id="sensorMetadataModal" tabindex="-1" aria-labelledby="sensorMetadataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="sensorMetadataModalLabel">
                        <i class="bi bi-tags-fill me-2"></i> Atributos del Sensor
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center mb-3 p-3 bg-light rounded-3 border">
                        <div class="bg-primary text-white rounded-3 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-hdd-network-fill fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1" id="sensorMetaName">—</h6>
                            <small class="text-muted">Identificador: <code id="sensorMetaId" class="text-primary fw-bold">—</code></small>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 45%;">Campo Personalizado</th>
                                    <th>Valor Asignado</th>
                                </tr>
                            </thead>
                            <tbody id="sensorMetaTableBody">
                                <!-- Filas dinámicas -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Carga Masiva de Fotos (ZIP o Multi-archivo) con Validación Estricta -->
    <div class="modal fade" id="bulkUploadPhotosModal" tabindex="-1" aria-labelledby="bulkUploadPhotosModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="bulkUploadPhotosModalLabel">
                        <i class="bi bi-cloud-arrow-up me-2"></i> Asistente de Carga Masiva de Fotos
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Alerta informativa y reglas de seguridad --}}
                    <div class="alert alert-info border-0 rounded-3 mb-3" style="background-color: #e8f4fd;">
                        <h6 class="fw-bold text-primary mb-2">
                            <i class="bi bi-shield-check me-1"></i> Políticas estrictas de almacenamiento y vinculación
                        </h6>
                        <ul class="mb-0 small text-dark" style="line-height: 1.5;">
                            <li><strong>⏱️ Regla de Retención de 1 Año (365 días):</strong> Solo se aceptarán y guardarán fotos vinculadas a mediciones tomadas en los últimos 365 días. Toda foto con mayor antigüedad será rechazada para evitar sobrecargar el almacenamiento.</li>
                            <li><strong>🔗 Coincidencia estricta con la Base de Datos:</strong> El nombre de cada archivo debe coincidir exactamente con el nombre de foto registrado en tus mediciones (con o sin extensión). Fotos huérfanas sin coincidencia serán descartadas automáticamente sin ocupar espacio.</li>
                            <li><strong>📦 Formatos soportados:</strong> Puedes subir un archivo comprimido <strong>.ZIP</strong> (recomendado para lotes masivos de miles de fotos) o seleccionar <strong>múltiples imágenes</strong> (.jpg, .jpeg, .png, .webp).</li>
                        </ul>
                    </div>

                    {{-- Zona de carga Drag and Drop --}}
                    <div id="bulkDropZone" class="border border-2 border-dashed rounded-4 p-4 text-center bg-light" style="cursor: pointer; transition: all 0.2s ease;">
                        <input type="file" id="bulkPhotoInput" class="d-none" multiple accept=".zip,.jpg,.jpeg,.png,.webp">
                        <i class="bi bi-file-earmark-zip text-primary" style="font-size: 3rem;"></i>
                        <h6 class="fw-bold mt-2">Arrastra aquí tu archivo .ZIP o tus fotos seleccionadas</h6>
                        <p class="text-muted small mb-3">O haz clic para explorar los archivos de tu equipo</p>
                        <button type="button" class="btn btn-outline-primary btn-sm px-3" onclick="$('#bulkPhotoInput').click()">
                            <i class="bi bi-folder2-open me-1"></i> Seleccionar Archivos
                        </button>
                        <div id="selectedFilesSummary" class="mt-3 text-primary fw-semibold small d-none"></div>
                    </div>

                    {{-- Barra de Progreso --}}
                    <div id="bulkUploadProgressContainer" class="mt-3 d-none">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Subiendo y procesando fotos en el servidor...</span>
                            <span id="bulkUploadPercent">0%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="bulkUploadProgressBar" style="width: 0%;"></div>
                        </div>
                    </div>

                    {{-- Resumen de Resultados tras la Carga --}}
                    <div id="bulkUploadResults" class="mt-3 d-none">
                        <h6 class="fw-bold mb-2"><i class="bi bi-clipboard-data me-1"></i> Resumen del Procesamiento:</h6>
                        <div class="row g-2 text-center small">
                            <div class="col-6 col-md-3">
                                <div class="p-2 border rounded bg-white">
                                    <div class="text-muted">Total Analizadas</div>
                                    <div class="fs-5 fw-bold text-dark" id="statTotalEvaluated">0</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-2 border rounded bg-success-subtle text-success border-success">
                                    <div>Vinculadas</div>
                                    <div class="fs-5 fw-bold" id="statLinkedCount">0</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-2 border rounded bg-warning-subtle text-warning-emphasis border-warning">
                                    <div>&gt; 1 Año (Rechazadas)</div>
                                    <div class="fs-5 fw-bold" id="statRejectedOlder">0</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-2 border rounded bg-secondary-subtle text-secondary border-secondary">
                                    <div>Sin Coincidencia (BD)</div>
                                    <div class="fs-5 fw-bold" id="statSkippedNoMatch">0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary btn-sm px-3" id="btnSubmitBulkPhotos" disabled>
                        <i class="bi bi-cloud-arrow-up me-1"></i> Iniciar Carga y Validación
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Radar Global de Anomalías -->
    <div class="modal fade" id="globalRadarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-radar"></i> Radar Global de Anomalías</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-secondary py-2 px-3 mb-3 pointer-events-none">
                        <strong class="text-danger small"><i class="bi bi-shield-exclamation"></i> ¿Qué hace este Radar?</strong><br>
                        <small class="text-muted" style="font-size: 0.8rem;">
                            Escaneará <b>todos tus sensores</b> utilizando la tasa diaria de consumo y regresará un listado de los sensores que estén arrojando anomalías bajo la sensibilidad especificada.
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
                            <label for="radarThreshold" class="form-label mb-1 text-danger" title="Salto en la tasa diaria">
                                <i class="bi bi-exclamation-triangle"></i> Anomalía
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control text-danger fw-bold" id="radarThreshold" value="50" min="1" step="5">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="radarStagnation" class="form-label mb-1 text-secondary" title="Alerta si delta es 0 luego de X días">
                                <i class="bi bi-hourglass-top"></i> Estancamiento
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control text-secondary fw-bold" id="radarStagnation" value="15" min="1" step="1">
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
                                <i class="bi bi-exclamation-octagon-fill"></i> Novedades Detectadas: <span id="radarCount">0</span>
                            </h6>
                            <div class="input-group input-group-sm w-50">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="radarSearch" placeholder="Buscar sensor o ID...">
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Sensor</th>
                                        <th>Incidencias</th>
                                        <th>Acción Directa</th>
                                    </tr>
                                </thead>
                                <tbody id="radarTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Análisis Avanzado por Sensor con Gráfico -->
    <div class="modal fade" id="analyzeSensorModal" tabindex="-1" aria-labelledby="analyzeSensorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="analyzeSensorModalLabel">
                        <i class="bi bi-bar-chart-fill"></i> Análisis Avanzado: <span id="analyzeSensorName"></span>
                    </h5>
                    <div class="d-flex align-items-center ms-auto">
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body">
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
                            <label for="analyzeThreshold" class="form-label mb-1 text-danger small">
                                <i class="bi bi-exclamation-triangle"></i> Anomalía
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control text-danger fw-bold" id="analyzeThreshold" value="50" min="1" step="5">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="analyzeStagnation" class="form-label mb-1 text-secondary small">
                                <i class="bi bi-hourglass-top"></i> Estancamiento
                            </label>
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
                    </div>

                    <div id="analyzeLoading" class="text-center d-none py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted mb-0">Calculando analíticas y dibujando trazados...</p>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
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
            <button class="btn btn-success btn-sm" id="btnBulkExportExcel" title="Exportar únicamente los registros seleccionados">
                <i class="bi bi-file-earmark-excel me-1"></i> Exportar Seleccionados
            </button>
            <button class="btn btn-danger btn-sm" id="btnBulkDelete">
                <i class="bi bi-trash"></i> Eliminar Selección
            </button>
            <button type="button" class="btn-close ms-2" id="btnHideBulkActions" aria-label="Cerrar"></button>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function () {
            let currentPage = 1;
            const itemsPerPage = 10;
            let showErrors = false;
            let currentErrorType = null;
            let errorStats = null;
            let filterOnlyCommunity = false;
            let currentSortField = 'measured_at';
            let currentSortDirection = 'desc';

            // Helpers para interpretación semántica en lenguaje natural
            function escapeHtml(text) {
                if (!text) return '';
                return text.toString()
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function formatDateHuman(d) {
                if (!d) return '';
                const parts = d.split('-');
                if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
                return d;
            }

            function updateSemanticQueryTranslation() {
                const parts = [];

                // Grupo
                const groupVal = $('#groupFilter').val();
                if (groupVal) {
                    const groupText = $('#groupFilter option:selected').text().trim();
                    parts.push(`del grupo <strong>${escapeHtml(groupText)}</strong>`);
                }

                // Plantilla
                const tmplVal = $('#templateFilter').val();
                if (tmplVal) {
                    const tmplText = $('#templateFilter option:selected').text().trim();
                    parts.push(`con plantilla <strong>${escapeHtml(tmplText)}</strong>`);
                }

                // Sensor
                const sensorVal = $('#sensorFilter').val();
                if (sensorVal) {
                    const sensorText = $('#sensorFilter option:selected').text().trim();
                    parts.push(`del medidor <strong>${escapeHtml(sensorText)}</strong>`);
                }

                // Identificador
                const identifier = $('#identifierFilter').val() ? $('#identifierFilter').val().trim() : '';
                if (identifier) {
                    parts.push(`cuyo identificador contiene <strong>"${escapeHtml(identifier)}"</strong>`);
                }

                // Campo Dinámico
                const customVal = $('#customValuesInput').val() ? $('#customValuesInput').val().trim() : '';
                if (customVal) {
                    const customField = $('#customFieldSelect').val();
                    const customFieldText = customField ? $('#customFieldSelect option:selected').text().trim() : 'atributo dinámico';
                    const customOp = $('#customOperatorSelect').val();
                    let opDesc = '';
                    if (customOp === 'or') {
                        opDesc = `donde <strong>${escapeHtml(customFieldText)}</strong> sea cualquiera de: <em>"${escapeHtml(customVal)}"</em> (trae ambos lotes/valores)`;
                    } else if (customOp === 'and') {
                        const hasMultiple = customVal.includes(',') || customVal.toLowerCase().includes(' or ') || customVal.toLowerCase().includes(' and ') || customVal.toLowerCase().includes(' y ');
                        opDesc = `donde el <strong>mismo</strong> campo <strong>${escapeHtml(customFieldText)}</strong> contenga simultáneamente todas las palabras: <em>"${escapeHtml(customVal)}"</em>`;
                        if (hasMultiple && customField) {
                            opDesc += ` <span class="badge bg-warning text-dark"><i class="bi bi-info-circle"></i> Si buscas traer varios lotes a la vez, usa "Coincide con alguno (Lista)"</span>`;
                        }
                    } else if (customOp === 'exact') {
                        opDesc = `donde <strong>${escapeHtml(customFieldText)}</strong> sea exactamente: <em>"${escapeHtml(customVal)}"</em>`;
                    } else if (customOp === 'not') {
                        opDesc = `donde <strong>${escapeHtml(customFieldText)}</strong> excluya: <em>"${escapeHtml(customVal)}"</em>`;
                    }
                    parts.push(opDesc);
                }

                // Estado de auditoría
                const errorType = $('#errorFilter').val();
                if (errorType) {
                    const errorLabels = {
                        'first_measurement': '1ra Medición (inicial)',
                        'negative_consumption': 'Aviso de Consumo Negativo',
                        'inconsistent_date': 'Aviso de Fecha Desfasada',
                        'valid': 'Válidas (sin anomalías)',
                        'any_error': 'cualquier anomalía'
                    };
                    parts.push(`con estado de auditoría <strong>${errorLabels[errorType] || errorType}</strong>`);
                }

                // Foto
                const photo = $('#photoFilter').val();
                if (photo === 'with_photo') {
                    parts.push(`que <strong>tienen fotografía adjunta</strong>`);
                } else if (photo === 'without_photo') {
                    parts.push(`que <strong>no tienen fotografía</strong>`);
                }

                // Consumo / Costo
                const consumption = $('#consumptionFilter').val();
                if (consumption) {
                    const consumptionLabels = {
                        'positive': 'con consumo positivo (&gt; 0)',
                        'zero': 'con consumo nulo (= 0)',
                        'negative': 'con consumo negativo (&lt; 0)',
                        'with_cost': 'con tarifa de costo configurada',
                        'without_cost': 'sin tarifa de costo'
                    };
                    parts.push(`<strong>${consumptionLabels[consumption] || consumption}</strong>`);
                }

                // Fechas
                const dateFrom = $('#dateFrom').val();
                const dateTo = $('#dateTo').val();
                if (dateFrom && dateTo) {
                    parts.push(`registradas entre el <strong>${formatDateHuman(dateFrom)}</strong> y el <strong>${formatDateHuman(dateTo)}</strong>`);
                } else if (dateFrom) {
                    parts.push(`registradas desde el <strong>${formatDateHuman(dateFrom)}</strong>`);
                } else if (dateTo) {
                    parts.push(`registradas hasta el <strong>${formatDateHuman(dateTo)}</strong>`);
                }

                // Áreas Comunes
                if (filterOnlyCommunity) {
                    parts.push(`exclusivamente de <strong>Áreas Comunes</strong>`);
                }

                let message = '';
                if (parts.length === 0) {
                    message = 'Mostrando <strong>todas las mediciones</strong> registradas en el sistema (sin restricciones).';
                } else {
                    message = 'Buscando mediciones ' + parts.join(', ') + '.';
                }

                $('#semanticQueryText').html(message);
            }

            // Función para cargar mediciones desde la API
            function loadMeasurements() {
                const sensorId = $('#sensorFilter').val();
                const identifier = $('#identifierFilter').val();
                const groupId = $('#groupFilter').val();
                const templateId = $('#templateFilter').val();
                const errorType = $('#errorFilter').val();
                const hasPhoto = $('#photoFilter').val();
                const consumptionType = $('#consumptionFilter').val();
                const dateFrom = $('#dateFrom').val();
                const dateTo = $('#dateTo').val();
                const search = $('#searchInput').val();
                const customField = $('#customFieldSelect').val();
                const customOperator = $('#customOperatorSelect').val();
                const customValues = $('#customValuesInput').val();

                const params = {
                    page: currentPage,
                    per_page: itemsPerPage,
                    sort_field: currentSortField,
                    sort_direction: currentSortDirection
                };

                if (sensorId) params.sensor_id = sensorId;
                if (identifier) params.identifier = identifier;
                if (groupId) params.group_id = groupId;
                if (templateId) params.template_id = templateId;
                if (errorType) params.error_type = errorType;
                if (hasPhoto) params.has_photo = hasPhoto;
                if (consumptionType) params.consumption_type = consumptionType;
                if (dateFrom) params.date_from = dateFrom;
                if (dateTo) params.date_to = dateTo;
                if (search) params.search = search;
                if (filterOnlyCommunity) params.is_community = 1;

                if (customValues) {
                    params.custom_values = customValues;
                    params.custom_field = customField || '';
                    params.custom_operator = customOperator || 'or';
                    const fieldLabel = customField ? customField.replace(/_/g, ' ') : 'Cualquier campo';
                    $('#activeAdvancedFiltersBadge').text(`${fieldLabel} (${customOperator.toUpperCase()}): "${customValues}"`).removeClass('d-none');
                } else {
                    $('#activeAdvancedFiltersBadge').addClass('d-none');
                }

                updateSemanticQueryTranslation();

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
                                updateErrorStats(errorStats);
                            }

                            // Actualizar opciones de campos dinámicos si están disponibles
                            if (response.available_extra_fields && response.available_extra_fields.length > 0) {
                                const currentCustomField = $('#customFieldSelect').val();
                                let extraOpts = '<option value="">-- Cualquier campo dinámico --</option>';
                                response.available_extra_fields.forEach(f => {
                                    const lbl = f.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                                    extraOpts += `<option value="${escapeHtml(f)}" ${currentCustomField === f ? 'selected' : ''}>${escapeHtml(lbl)}</option>`;
                                });
                                $('#customFieldSelect').html(extraOpts);
                            }

                            const groupExtraFields = response.group_extra_fields || [];
                            renderTableHeaders(groupExtraFields);
                            renderMeasurements(response.data, groupExtraFields);
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
                        const totalColspan = 12 + activeGroupExtraFields.length;
                        $('#measurementsTableBody').html(`
                            <tr>
                                <td colspan="${totalColspan}" class="text-center text-danger py-4">
                                    <i class="bi bi-exclamation-triangle"></i> Error al cargar las mediciones. Por favor, intente de nuevo.
                                </td>
                            </tr>
                        `);
                    }
                });
            }

            // Función para escapar HTML de forma segura
            function escapeHtml(str) {
                if (str === null || str === undefined) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            // Función para mostrar errores
            function showError(message) {
                const totalColspan = 12 + activeGroupExtraFields.length;
                $('#measurementsTableBody').html(`
                    <tr>
                        <td colspan="${totalColspan}" class="text-center text-danger py-4">
                            <i class="bi bi-exclamation-triangle"></i> ${escapeHtml(message)}
                        </td>
                    </tr>
                `);
            }

            // Función para actualizar estadísticas de errores y alertas visuales
            function updateErrorStats(stats) {
                const negCount = parseInt(stats.negative_consumption) || 0;
                const dateCount = parseInt(stats.inconsistent_date) || 0;
                const firstCount = parseInt(stats.first_measurement) || 0;
                const validCount = parseInt(stats.valid) || 0;

                $('#negativeConsumptionCount').text(negCount);
                $('#inconsistentDateCount').text(dateCount);
                $('#noPreviousCount').text(firstCount);
                $('#validMeasurementsCount').text(validCount);

                // Actualizar textos en el desplegable de filtro de Estado
                $('#errorFilter option[value="negative_consumption"]').text(`Aviso Consumo (${negCount})`);
                $('#errorFilter option[value="inconsistent_date"]').text(`Aviso Fecha (${dateCount})`);
                $('#errorFilter option[value="first_measurement"]').text(`1ra Medición (${firstCount})`);

                // Actualizar botón de Auditoría en la barra superior con diseño llamativo pero elegante
                if (negCount > 0) {
                    $('#toggleErrors')
                        .removeClass('btn-outline-light btn-warning text-dark')
                        .addClass('btn-danger text-white pulse-audit-btn')
                        .attr('title', `Atención: Hay ${negCount} consumo(s) negativo(s) pendientes de revisión`);
                    $('#toggleErrorsIcon').removeClass('bi-exclamation-triangle bi-shield-check').addClass('bi-exclamation-octagon-fill');
                    $('#auditBadge')
                        .removeClass('d-none bg-dark text-white bg-success text-white')
                        .addClass('bg-white text-danger fw-bold shadow-sm')
                        .html(`<i class="bi bi-exclamation-circle-fill me-1"></i>${negCount} crítico(s)`);
                } else if (dateCount > 0) {
                    $('#toggleErrors')
                        .removeClass('btn-outline-light btn-danger text-white pulse-audit-btn')
                        .addClass('btn-warning text-dark')
                        .attr('title', `Aviso: Hay ${dateCount} fecha(s) desfasada(s)`);
                    $('#toggleErrorsIcon').removeClass('bi-exclamation-octagon-fill bi-shield-check').addClass('bi-exclamation-triangle-fill');
                    $('#auditBadge')
                        .removeClass('d-none bg-white text-danger bg-success text-white')
                        .addClass('bg-dark text-white fw-bold shadow-sm')
                        .text(`${dateCount} aviso(s)`);
                } else {
                    $('#toggleErrors')
                        .removeClass('btn-danger text-white btn-warning text-dark pulse-audit-btn')
                        .addClass('btn-outline-light text-white')
                        .attr('title', 'Todas las mediciones son consistentes y válidas');
                    $('#toggleErrorsIcon').removeClass('bi-exclamation-octagon-fill bi-exclamation-triangle-fill').addClass('bi-shield-check');
                    $('#auditBadge')
                        .removeClass('bg-white text-danger bg-dark text-white')
                        .addClass('bg-success text-white fw-semibold shadow-sm')
                        .html('<i class="bi bi-check-lg"></i> OK')
                        .removeClass('d-none');
                }
            }

            // Función para obtener campos dinámicos del sensor
            function getSensorDynamicFields(sensor) {
                if (!sensor) return {};
                const fields = {};
                if (sensor.numero_lote) fields.numero_lote = sensor.numero_lote;
                if (sensor.codigo_medidor) fields.codigo_medidor = sensor.codigo_medidor;
                if (sensor.numero_serie) fields.numero_serie = sensor.numero_serie;
                return fields;
            }

            let activeGroupExtraFields = [];

            // Renderizar dinámicamente los encabezados de la tabla
            function renderTableHeaders(groupExtraFields) {
                activeGroupExtraFields = groupExtraFields || [];

                const getSortIcon = (field) => {
                    if (currentSortField === field) {
                        return currentSortDirection === 'asc'
                            ? '<i class="bi bi-arrow-up text-primary ms-1" style="font-size: 0.75rem;"></i>'
                            : '<i class="bi bi-arrow-down text-primary ms-1" style="font-size: 0.75rem;"></i>';
                    }
                    return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size: 0.75rem;"></i>';
                };

                let headersHtml = `
                    <th style="width: 40px; text-align: center;">
                        <input class="form-check-input" type="checkbox" id="selectAllMeasurements">
                    </th>
                    <th class="sortable" data-field="sensor_id" style="cursor: pointer;">
                        Sensor ${getSortIcon('sensor_id')}
                    </th>
                    <th class="sortable" data-field="identifier" style="cursor: pointer;">
                        Identificador ${getSortIcon('identifier')}
                    </th>
                    <th class="sortable" data-field="group_name" style="cursor: pointer;">
                        Grupo ${getSortIcon('group_name')}
                    </th>
                `;

                // Columnas dinámicas contextuales si se filtró por un grupo específico (ahora ordenables)
                activeGroupExtraFields.forEach(field => {
                    const label = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    const sortKey = 'extra:' + field;
                    headersHtml += `
                        <th class="sortable bg-primary-subtle text-primary border-primary text-nowrap" data-field="${sortKey}" style="cursor: pointer;" title="Hacer clic para ordenar naturalmente por ${escapeHtml(label)}">
                            <i class="bi bi-tag-fill me-1" style="font-size: 0.72rem;"></i>${escapeHtml(label)} ${getSortIcon(sortKey)}
                        </th>
                    `;
                });

                headersHtml += `
                    <th>Plantilla</th>
                    <th class="sortable" data-field="valor" style="cursor: pointer;">
                        Valor ${getSortIcon('valor')}
                    </th>
                    <th>Datos Dinámicos (Medición)</th>
                    <th class="sortable" data-field="measured_at" style="cursor: pointer;">
                        Fecha ${getSortIcon('measured_at')}
                    </th>
                    <th>Consumo / Costo</th>
                    <th>Estado</th>
                    <th>Foto</th>
                    <th>Acciones</th>
                `;

                $('#measurementsTable thead tr').html(headersHtml);

                // Reasociar eventos de ordenamiento y selección general
                $('#selectAllMeasurements').off('change').on('change', function () {
                    const isChecked = $(this).prop('checked');
                    $('.measurement-checkbox').prop('checked', isChecked);
                    updateBulkActionsVisibility();
                });

                $('.sortable').off('click').on('click', function () {
                    const field = $(this).data('field');
                    if (currentSortField === field) {
                        currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        currentSortField = field;
                        currentSortDirection = 'asc';
                    }
                    loadMeasurements();
                });
            }

            // Función para renderizar mediciones
            function renderMeasurements(measurements, groupExtraFields) {
                const extraCols = groupExtraFields || [];
                const totalColspan = 12 + extraCols.length;

                if (!measurements || measurements.length === 0) {
                    $('#measurementsTableBody').html(`
                        <tr>
                            <td colspan="${totalColspan}" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i> No se encontraron mediciones
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

                    let consumptionCellHtml = '';
                    if (consumption === 'N/A' || measurement.error_type === 'first_measurement') {
                        consumptionCellHtml = '<span class="badge bg-secondary">Inicial (0)</span>';
                    } else {
                        const badgeColor = consumption < 0 ? 'danger' : 'success';
                        consumptionCellHtml = `<span class="badge bg-${badgeColor}">${consumption > 0 ? '+' : ''}${consumption} ${unit}</span>`;
                    }
                    if (measurement.days_between) {
                        consumptionCellHtml += `<div class="text-muted mt-1" style="font-size:0.68rem;"><i class="bi bi-clock-history"></i> ${measurement.days_between} días (prom: ${measurement.daily_average || 0}/d)</div>`;
                    }
                    if (measurement.cost !== null && measurement.cost !== undefined) {
                        consumptionCellHtml += `<div class="text-success fw-bold mt-1" style="font-size:0.72rem;"><i class="bi bi-cash-stack"></i> $${Number(measurement.cost).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ${escapeHtml(measurement.currency || 'ARS')}</div>`;
                    }

                    // ✅ Datos del sensor desde campos explícitos del API
                    const sensor = measurement.sensor || {};
                    const sensorName = sensor.name || 'N/A';
                    const sensorIdentifier = measurement.sensor_identifier || sensor.identifier || '';
                    const groupName = measurement.group_name || sensor.group?.name || 'Sin grupo';
                    const templateName = measurement.template_name || '';
                    const templateType = measurement.template_type || '';

                    // ✅ Campos extra del sensor (Metadata)
                    const extraFields = measurement.sensor_extra_fields || {};
                    const extraCount = Object.keys(extraFields).length;
                    const metaJsonSafe = escapeHtml(JSON.stringify(extraFields));

                    // ✅ Sensor display: limpio y compacto para no deformar celdas ni saturar con 25 badges
                    let sensorDisplay = `<span class="sensor-name">${escapeHtml(sensorName)}</span>`;
                    if (extraCount > 0 && extraCols.length === 0) {
                        // En vista unificada, mostrar enlace compacto y elegante para abrir la ficha de atributos
                        sensorDisplay += `
                            <a href="#" class="viewSensorMetaBtn text-decoration-none small text-muted d-inline-flex align-items-center mt-1" 
                               data-sensor-name="${escapeHtml(sensorName)}" 
                               data-sensor-id="${escapeHtml(sensorIdentifier)}" 
                               data-meta='${metaJsonSafe}'
                               title="Ver los ${extraCount} campos personalizados asignados a este sensor">
                                <i class="bi bi-tags text-primary me-1"></i> ${extraCount} atributos
                            </a>
                        `;
                    }

                    // ✅ Columnas dinámicas si se filtró por grupo específico
                    let dynamicCellsHtml = '';
                    extraCols.forEach(k => {
                        const val = (extraFields[k] !== undefined && extraFields[k] !== null && extraFields[k] !== '') ? extraFields[k] : '—';
                        dynamicCellsHtml += `<td class="text-nowrap fw-semibold text-dark">${escapeHtml(String(val))}</td>`;
                    });

                    // ✅ Construir label de plantilla
                    let templateDisplay = templateName || templateType || 'N/A';
                    if (templateName && templateType && templateName !== templateType) {
                        templateDisplay = `<span class="sensor-name">${escapeHtml(templateName)}</span><span class="sensor-identifier">${escapeHtml(templateType)}</span>`;
                    }

                    // ✅ Agregar datos dinámicos de la medición (excluyendo el campo principal y foto)
                    let measurementDataHtml = [];
                    Object.keys(data).forEach(k => {
                        if (k !== mainField && k !== 'foto' && k !== 'foto_evidencia') {
                            const lbl = k.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                            measurementDataHtml.push(`<div><strong>${escapeHtml(lbl)}:</strong> ${escapeHtml(String(data[k]))}</div>`);
                        }
                    });
                    const measurementDataDisplay = measurementDataHtml.length > 0 ? `<div style="font-size:0.75rem; color:#495057;">${measurementDataHtml.join('')}</div>` : '<span class="text-muted small">N/A</span>';

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
                                                                                                ${dynamicCellsHtml}
                                                                                                <td><div class="sensor-info">${templateDisplay}</div></td>
                                                                                                <td><strong>${value} ${unit}</strong></td>
                                                                                                <td>${measurementDataDisplay}</td>
                                                                                                <td>${date}</td>
                                                                                                <td class="${consumptionClass}">${consumptionCellHtml}</td>
                                                                                                <td>${statusBadge}</td>
                                                                                                <td>
                                                                                                    ${photoPath ?
                            `<button class="btn btn-sm btn-info viewPhotoBtn" data-photo-path="${photoPath}" data-measurement-id="${measurement.id}">
                                                                                                            <i class="bi bi-image me-1"></i> Ver
                                                                                                        </button>` :
                            `<button class="btn btn-sm btn-outline-secondary editPhotoBtn" data-measurement-id="${measurement.id}">
                                                <i class="bi bi-camera"></i> Subir
                                            </button>`}
                                                                                                </td>
                                                                                                <td>
                                                                                                    <div class="table-actions">
                                                                                                        <button class="btn btn-sm btn-outline-info analyzeSensorBtn" 
                                                                                                                title="Análisis Avanzado y Gráfico de Consumo" 
                                                                                                                data-sensor-id="${sensor.id || measurement.sensor_id}" 
                                                                                                                data-sensor-name="${escapeHtml(sensorName)}">
                                                                                                            <i class="bi bi-bar-chart-fill"></i>
                                                                                                        </button>
                                                                                                        ${extraCount > 0 ? `
                                                                                                            <button class="btn btn-sm btn-outline-primary viewSensorMetaBtn" 
                                                                                                                    title="Ver atributos del sensor (${extraCount})" 
                                                                                                                    data-sensor-name="${escapeHtml(sensorName)}" 
                                                                                                                    data-sensor-id="${escapeHtml(sensorIdentifier)}" 
                                                                                                                    data-meta='${metaJsonSafe}'>
                                                                                                                <i class="bi bi-tags"></i>
                                                                                                            </button>
                                                                                                        ` : ''}
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

                // Evento para abrir modal de atributos del sensor
                $('.viewSensorMetaBtn').off('click').on('click', function (e) {
                    e.preventDefault();
                    const sensorName = $(this).attr('data-sensor-name') || 'Sensor';
                    const sensorId = $(this).attr('data-sensor-id') || '—';
                    let meta = $(this).attr('data-meta');
                    if (typeof meta === 'string') {
                        try { meta = JSON.parse(meta); } catch (err) { meta = {}; }
                    }
                    meta = meta || {};

                    $('#sensorMetaName').text(sensorName);
                    $('#sensorMetaId').text(sensorId);

                    let rowsHtml = '';
                    const keys = Object.keys(meta);
                    if (keys.length === 0) {
                        rowsHtml = '<tr><td colspan="2" class="text-center text-muted py-3">Este sensor no tiene atributos personalizados asignados.</td></tr>';
                    } else {
                        keys.forEach(k => {
                            const label = k.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                            rowsHtml += `
                                <tr>
                                    <td class="fw-semibold text-secondary align-middle">${escapeHtml(label)}</td>
                                    <td class="align-middle"><code class="text-dark bg-white border px-2 py-1 rounded fs-6">${escapeHtml(String(meta[k]))}</code></td>
                                </tr>
                            `;
                        });
                    }

                    $('#sensorMetaTableBody').html(rowsHtml);
                    $('#sensorMetadataModal').modal('show');
                });

                // Asignar eventos
                $('.viewPhotoBtn').click(function () {
                    const photoPath = $(this).data('photo-path');
                    const mId = $(this).data('measurement-id');
                    $('#modalPhoto').attr('src', photoPath).show();
                    $('#photoZoomToolbar').removeClass('d-none').addClass('d-flex');
                    $('#photoViewerViewport').removeClass('d-none').addClass('d-flex');
                    if (typeof resetPhotoZoomState === 'function') {
                        resetPhotoZoomState();
                    }
                    $('#downloadPhoto').attr('href', photoPath).show();
                    $('#uploadPhotoMeasurementsId').val(mId);
                    $('#photoModalLabel').text('Ver/Reemplazar Foto');
                    $('#photoUploadContainer').show();
                    $('#uploadResult').hide();
                    $('#photoModal').modal('show');
                });

                $('.editPhotoBtn').click(function () {
                    const mId = $(this).data('measurement-id');
                    $('#modalPhoto').hide();
                    $('#photoZoomToolbar').removeClass('d-flex').addClass('d-none');
                    $('#photoViewerViewport').removeClass('d-flex').addClass('d-none');
                    $('#downloadPhoto').hide();
                    $('#uploadPhotoMeasurementsId').val(mId);
                    $('#photoModalLabel').text('Subir Foto Manual');
                    $('#photoUploadContainer').show();
                    $('#uploadResult').hide();
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
                                                                                                                        <th>Diagnóstico</th>
                                                                                                                        <th class="text-center">Acciones</th>
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

                        const nextDate = detail.next_measurement ?
                            new Date(detail.next_measurement.date).toLocaleString('es-ES') : '';

                        html += `
                                                                                                <tr>
                                                                                                    <td>${detail.record_number}</td>
                                                                                                    <td>
                                                                                                        <strong>ID:</strong> #${detail.current_measurement.id}<br>
                                                                                                        <strong>Valor:</strong> ${detail.current_measurement.value}<br>
                                                                                                        <strong>Fecha:</strong> ${currentDate}
                                                                                                        ${detail.current_measurement.is_reset ? '<br><span class="badge bg-info mt-1"><i class="bi bi-arrow-repeat"></i> Cambio Medidor</span>' : ''}
                                                                                                        ${detail.current_measurement.photo ? `<br><button type="button" class="btn btn-outline-info btn-sm py-0 px-1 mt-1 btn-view-photo-modal" data-photo-path="${detail.current_measurement.photo}" data-id="${detail.current_measurement.id}" title="Chequear foto del medidor"><i class="bi bi-camera me-1"></i> Ver Foto</button>` : ''}
                                                                                                    </td>
                                                                                                    <td>
                                                                                                        ${detail.previous_measurement ?
                                `<strong>ID:</strong> #${detail.previous_measurement.id}<br>
                                                                                                            <strong>Valor:</strong> ${detail.previous_measurement.value}<br>
                                                                                                            <strong>Fecha:</strong> ${previousDate}
                                                                                                            ${detail.previous_measurement.photo ? `<br><button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1 mt-1 btn-view-photo-modal" data-photo-path="${detail.previous_measurement.photo}" data-id="${detail.previous_measurement.id}" title="Ver foto previa"><i class="bi bi-camera me-1"></i> Foto Previa</button>` : ''}` : '<span class="text-muted fst-italic">Primera medición</span>'}
                                                                                                    </td>
                                                                                                    <td class="${consumptionClass}">${difference}</td>
                                                                                                    <td>${daysDifference !== 'N/A' ? daysDifference + ' días' : '—'}</td>
                                                                                                    <td><small>${detail.error_message}</small></td>
                                                                                                    <td class="text-nowrap text-center">
                                                                                                        <div class="d-inline-flex flex-column gap-1">
                                                                                                            ${detail.error_type === 'negative_consumption' ? `
                                                                                                                <button class="btn btn-warning btn-sm py-1 px-2 fw-semibold btn-mark-reset"
                                                                                                                    data-id="${detail.current_measurement.id}"
                                                                                                                    data-sensor="${escapeHtml(sensorName)}"
                                                                                                                    data-value="${detail.current_measurement.value}"
                                                                                                                    data-date="${currentDate}"
                                                                                                                    title="Registrar reemplazo físico de medidor / reseteo">
                                                                                                                    <i class="bi bi-arrow-repeat me-1"></i> Cambio Medidor
                                                                                                                </button>
                                                                                                            ` : ''}
                                                                                                            <button class="btn btn-outline-primary btn-sm py-1 px-2 btn-quick-edit"
                                                                                                                data-id="${detail.current_measurement.id}"
                                                                                                                data-value="${detail.current_measurement.value}"
                                                                                                                data-date="${detail.current_measurement.date}"
                                                                                                                data-photo="${detail.current_measurement.photo || ''}"
                                                                                                                data-prev-val="${detail.previous_measurement ? detail.previous_measurement.value : ''}"
                                                                                                                data-prev-date="${previousDate !== 'N/A' ? previousDate : ''}"
                                                                                                                data-next-val="${detail.next_measurement && detail.next_measurement.value !== null ? detail.next_measurement.value : ''}"
                                                                                                                data-next-date="${nextDate}"
                                                                                                                title="Corregir valor o fecha por error de tipeo">
                                                                                                                <i class="bi bi-pencil me-1"></i> Corregir
                                                                                                            </button>
                                                                                                            <button class="btn btn-outline-danger btn-sm py-1 px-2 btn-quick-delete"
                                                                                                                data-id="${detail.current_measurement.id}"
                                                                                                                title="Eliminar esta medición">
                                                                                                                <i class="bi bi-trash"></i>
                                                                                                            </button>
                                                                                                        </div>
                                                                                                    </td>
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

            // Lógica para subir foto manual
            $('#submitNewPhotoBtn').click(function () {
                const measurementId = $('#uploadPhotoMeasurementsId').val();
                const fileInput = $('#newPhotoInput')[0];

                if (!fileInput.files || fileInput.files.length === 0) {
                    $('#uploadResult').html('<div class="alert alert-warning py-1 mt-2">Por favor, selecciona una foto primero.</div>').show();
                    return;
                }

                const file = fileInput.files[0];
                const formData = new FormData();
                formData.append('foto', file);

                const btnText = $(this).html();
                $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Subiendo...').prop('disabled', true);

                $.ajax({
                    url: `/api/measurements/${measurementId}/update-photo`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json'
                    },
                    success: function (response) {
                        if (response.success) {
                            $('#uploadResult').html('<div class="alert alert-success py-1 mt-2">✅ Foto actualizada. Recargando...</div>').show();
                            setTimeout(() => {
                                $('#photoModal').modal('hide');
                                loadMeasurements(); // Refrescar la tabla
                            }, 1000);
                        } else {
                            $('#uploadResult').html(`<div class="alert alert-danger py-1 mt-2">❌ Error: ${response.message}</div>`).show();
                        }
                    },
                    error: function (xhr) {
                        $('#uploadResult').html(`<div class="alert alert-danger py-1 mt-2">❌ Ocurrió un error. Verifica el formato/tamaño.</div>`).show();
                    },
                    complete: function () {
                        $('#submitNewPhotoBtn').html(btnText).prop('disabled', false);
                    }
                });
            });

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

            // Eventos para filtros desplegables
            $('#sensorFilter, #groupFilter, #templateFilter, #errorFilter, #photoFilter, #consumptionFilter').change(function () {
                updateSemanticQueryTranslation();
                currentPage = 1;
                loadMeasurements();
            });

            // Eventos en tiempo real para interpretación semántica en lenguaje natural
            $('#identifierFilter, #customValuesInput, #dateFrom, #dateTo').on('input change', function () {
                updateSemanticQueryTranslation();
            });

            $('#identifierFilter').keypress(function (e) {
                if (e.which === 13) {
                    $('#applyFilters').click();
                }
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
                updateSemanticQueryTranslation();
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

            $('#btnToggleAdvancedHelp').click(function () {
                $('#advancedHelpText').toggleClass('d-none');
            });

            $('#btnClearCustomValues').click(function () {
                $('#customValuesInput').val('');
                updateSemanticQueryTranslation();
                currentPage = 1;
                loadMeasurements();
            });

            $('#customValuesInput').keypress(function (e) {
                if (e.which === 13) {
                    $('#applyFilters').click();
                }
            });

            $('#customFieldSelect, #customOperatorSelect').change(function () {
                updateSemanticQueryTranslation();
                if ($('#customValuesInput').val()) {
                    currentPage = 1;
                    loadMeasurements();
                }
            });

            $('#resetFilters').click(function () {
                $('#sensorFilter').val('');
                $('#identifierFilter').val('');
                $('#groupFilter').val('');
                $('#templateFilter').val('');
                $('#errorFilter').val('');
                $('#photoFilter').val('');
                $('#consumptionFilter').val('');
                $('#dateFrom').val('');
                $('#dateTo').val('');
                $('#searchInput').val('');
                $('#customFieldSelect').val('');
                $('#customOperatorSelect').val('or');
                $('#customValuesInput').val('');
                $('#activeAdvancedFiltersBadge').addClass('d-none');

                filterOnlyCommunity = false;
                $('#toggleCommunityFilterBtn').removeClass('btn-success').addClass('btn-outline-success').html('<i class="bi bi-tree-fill"></i> Áreas Comunes');

                updateSemanticQueryTranslation();

                currentPage = 1;
                loadMeasurements();
            });

            // Evento para mostrar/ocultar tarjetas de auditoría
            $('#toggleErrors').click(function () {
                showErrors = !showErrors;
                if (showErrors) {
                    $('#toggleErrorsText').text('Ocultar');
                    $('#errorStatsContainer').removeClass('d-none');
                    if (errorStats) {
                        updateErrorStats(errorStats);
                    }
                } else {
                    $('#toggleErrorsText').text('Auditoría');
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

            // Lógica de ordenamiento
            $('.sortable').click(function () {
                const field = $(this).data('field');

                if (currentSortField === field) {
                    currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSortField = field;
                    currentSortDirection = 'asc';
                }

                // Actualizar iconos
                $('.sortable').each(function () {
                    $(this).find('i').removeClass('bi-arrow-up bi-arrow-down').addClass('bi-arrow-down-up text-muted');
                });

                const $icon = $(this).find('i');
                $icon.removeClass('bi-arrow-down-up text-muted')
                    .addClass(currentSortDirection === 'asc' ? 'bi-arrow-up text-primary' : 'bi-arrow-down text-primary');

                loadMeasurements();
            });

            // =========================================================
            // 🔍 CONTROL INTERACTIVO DE ZOOM, PANEO, ROTACIÓN Y CONTRASTE EN FOTOS
            // =========================================================
            let photoZoom = 1;
            let photoPanX = 0;
            let photoPanY = 0;
            let photoRotate = 0;
            let photoEnhanced = false;
            let isDraggingPhoto = false;
            let dragStartX = 0;
            let dragStartY = 0;

            const viewportEl = document.getElementById('photoViewerViewport');
            const photoEl = document.getElementById('modalPhoto');
            const zoomSliderEl = document.getElementById('photoZoomSlider');
            const zoomResetBtn = document.getElementById('btnZoomReset');
            const enhanceBtn = document.getElementById('btnEnhancePhoto');

            function applyPhotoTransform() {
                if (!photoEl) return;
                if (photoZoom <= 1) {
                    photoPanX = 0;
                    photoPanY = 0;
                }
                photoEl.style.transform = `translate(${photoPanX}px, ${photoPanY}px) scale(${photoZoom}) rotate(${photoRotate}deg)`;
                photoEl.style.filter = photoEnhanced ? 'contrast(1.4) brightness(1.08) saturate(1.15)' : 'none';
                if (zoomResetBtn) {
                    zoomResetBtn.innerText = Math.round(photoZoom * 100) + '%';
                }
                if (zoomSliderEl) {
                    zoomSliderEl.value = photoZoom;
                }
                if (viewportEl) {
                    viewportEl.style.cursor = isDraggingPhoto ? 'grabbing' : (photoZoom > 1 ? 'grab' : 'zoom-in');
                }
            }

            window.resetPhotoZoomState = function () {
                photoZoom = 1;
                photoPanX = 0;
                photoPanY = 0;
                photoRotate = 0;
                photoEnhanced = false;
                if (enhanceBtn) {
                    enhanceBtn.classList.remove('btn-warning', 'fw-bold');
                    enhanceBtn.classList.add('btn-outline-dark');
                }
                applyPhotoTransform();
            };

            function setPhotoZoomLevel(newZoom) {
                photoZoom = Math.min(5, Math.max(1, parseFloat(newZoom.toFixed(2))));
                applyPhotoTransform();
            }

            $('#btnZoomIn').click(function () {
                setPhotoZoomLevel(photoZoom + 0.35);
            });

            $('#btnZoomOut').click(function () {
                setPhotoZoomLevel(photoZoom - 0.35);
            });

            $('#btnZoomReset').click(function () {
                window.resetPhotoZoomState();
            });

            $('#photoZoomSlider').on('input', function () {
                setPhotoZoomLevel(parseFloat(this.value) || 1);
            });

            $('#btnRotateLeft').click(function () {
                photoRotate = (photoRotate - 90) % 360;
                applyPhotoTransform();
            });

            $('#btnRotateRight').click(function () {
                photoRotate = (photoRotate + 90) % 360;
                applyPhotoTransform();
            });

            $('#btnEnhancePhoto').click(function () {
                photoEnhanced = !photoEnhanced;
                $(this).toggleClass('btn-outline-dark', !photoEnhanced)
                       .toggleClass('btn-warning fw-bold', photoEnhanced);
                applyPhotoTransform();
            });

            if (viewportEl) {
                // Zoom con rueda del mouse
                viewportEl.addEventListener('wheel', function (e) {
                    e.preventDefault();
                    const delta = e.deltaY < 0 ? 0.25 : -0.25;
                    setPhotoZoomLevel(photoZoom + delta);
                }, { passive: false });

                // Doble clic para alternar rápido entre 100% y 250%
                viewportEl.addEventListener('dblclick', function (e) {
                    e.preventDefault();
                    if (photoZoom > 1.1) {
                        photoZoom = 1;
                        photoPanX = 0;
                        photoPanY = 0;
                    } else {
                        photoZoom = 2.5;
                    }
                    applyPhotoTransform();
                });

                // Arrastrar con el mouse (Pan)
                viewportEl.addEventListener('mousedown', function (e) {
                    if (photoZoom <= 1) return;
                    isDraggingPhoto = true;
                    dragStartX = e.clientX - photoPanX;
                    dragStartY = e.clientY - photoPanY;
                    if (photoEl) photoEl.style.transition = 'none';
                    applyPhotoTransform();
                });

                window.addEventListener('mousemove', function (e) {
                    if (!isDraggingPhoto) return;
                    photoPanX = e.clientX - dragStartX;
                    photoPanY = e.clientY - dragStartY;
                    applyPhotoTransform();
                });

                window.addEventListener('mouseup', function () {
                    if (!isDraggingPhoto) return;
                    isDraggingPhoto = false;
                    if (photoEl) photoEl.style.transition = 'transform 0.08s ease-out';
                    applyPhotoTransform();
                });

                // Soporte táctil para arrastrar en tablets/celulares
                viewportEl.addEventListener('touchstart', function (e) {
                    if (photoZoom <= 1 || e.touches.length !== 1) return;
                    isDraggingPhoto = true;
                    dragStartX = e.touches[0].clientX - photoPanX;
                    dragStartY = e.touches[0].clientY - photoPanY;
                    if (photoEl) photoEl.style.transition = 'none';
                }, { passive: true });

                viewportEl.addEventListener('touchmove', function (e) {
                    if (!isDraggingPhoto || e.touches.length !== 1) return;
                    e.preventDefault();
                    photoPanX = e.touches[0].clientX - dragStartX;
                    photoPanY = e.touches[0].clientY - dragStartY;
                    applyPhotoTransform();
                }, { passive: false });

                viewportEl.addEventListener('touchend', function () {
                    if (!isDraggingPhoto) return;
                    isDraggingPhoto = false;
                    if (photoEl) photoEl.style.transition = 'transform 0.08s ease-out';
                });
            }

            // =========================================================
            // 📊 EXPORTAR A EXCEL / CSV (MEDICIONES Y CONSUMOS)
            // =========================================================
            $('#btnExportExcel').click(function () {
                const selectedIds = [];
                $('.measurement-checkbox:checked').each(function () {
                    selectedIds.push($(this).val());
                });

                const params = {
                    group_id: $('#groupFilter').val() || '',
                    template_id: $('#templateFilter').val() || '',
                    sensor_id: $('#sensorFilter').val() || '',
                    identifier: $('#identifierFilter').val() || '',
                    date_from: $('#dateFrom').val() || '',
                    date_to: $('#dateTo').val() || '',
                    error_type: currentErrorType || '',
                    has_photo: $('#photoFilter').val() || '',
                    consumption_type: $('#consumptionFilter').val() || '',
                    is_community: filterOnlyCommunity ? 'true' : '',
                    search: $('#searchInput').val() || '',
                    custom_field: $('#customFieldSelect').val() || '',
                    custom_operator: $('#customOperatorSelect').val() || 'or',
                    custom_values: $('#customValuesInput').val() || '',
                    sort_field: currentSortField,
                    sort_direction: currentSortDirection
                };

                if (selectedIds.length > 0) {
                    if (confirm(`Tienes ${selectedIds.length} mediciones seleccionadas con casillas.\n\n¿Deseas exportar únicamente las seleccionadas?\n• Haz clic en [Aceptar] para exportar solo las ${selectedIds.length} seleccionadas.\n• Haz clic en [Cancelar] para exportar todos los registros filtrados.`)) {
                        params.ids = selectedIds.join(',');
                    }
                }

                Object.keys(params).forEach(k => {
                    if (!params[k]) delete params[k];
                });

                window.location.href = '/mediciones/export?' + new URLSearchParams(params).toString();
            });

            $('#btnBulkExportExcel').click(function () {
                const selectedIds = [];
                $('.measurement-checkbox:checked').each(function () {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    alert('Por favor selecciona al menos una medición para exportar.');
                    return;
                }

                window.location.href = '/mediciones/export?ids=' + selectedIds.join(',');
            });

            // =========================================================
            // 🔄 AUDITORÍA: CAMBIO DE MEDIDOR, CORRECCIÓN Y ELIMINACIÓN
            // =========================================================
            $(document).on('click', '.btn-mark-reset', function () {
                const id = $(this).data('id');
                const sensor = $(this).data('sensor');
                const val = $(this).data('value');
                const date = $(this).data('date');

                $('#resetModalMeasurementId').val(id);
                $('#resetModalDisplayId').text('#' + id);
                $('#resetModalDisplaySensor').text(sensor);
                $('#resetModalDisplayValue').text(val);
                $('#resetModalDisplayDate').text(date);

                $('#confirmResetModal').modal('show');
            });

            $('#btnConfirmReset').click(function () {
                const id = $('#resetModalMeasurementId').val();
                if (!id) return;

                const btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Guardando...');

                $.ajax({
                    url: `/mediciones/${id}/mark-reset`,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    data: {
                        is_reset: 1,
                        reset_type: 'simple'
                    },
                    success: function (resp) {
                        btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Confirmar Cambio de Medidor');
                        $('#confirmResetModal').modal('hide');
                        showErrorDetailsModal(currentErrorType);
                        loadMeasurements();
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Confirmar Cambio de Medidor');
                        alert(xhr.responseJSON?.message || 'Error al registrar cambio de medidor.');
                    }
                });
            });

            // Visualizador directo de fotos desde el modal de auditoría
            $(document).on('click', '.btn-view-photo-modal', function (e) {
                e.stopPropagation();
                const photoPath = $(this).data('photo-path');
                const mId = $(this).data('id');
                if (!photoPath) return;

                $('#modalPhoto').attr('src', photoPath).show();
                $('#photoZoomToolbar').removeClass('d-none').addClass('d-flex');
                $('#photoViewerViewport').removeClass('d-none').addClass('d-flex');
                if (typeof resetPhotoZoomState === 'function') {
                    resetPhotoZoomState();
                }
                $('#downloadPhoto').attr('href', photoPath).show();
                $('#uploadPhotoMeasurementsId').val(mId);
                $('#photoModalLabel').text(`Foto de Medición #${mId}`);
                $('#photoUploadContainer').hide();
                $('#uploadResult').hide();
                $('#photoModal').modal('show');
            });

            // Inspeccionar foto desde el modal de edición rápida
            $(document).on('click', '.btn-inspect-quick-photo', function () {
                const photoPath = $('#btnQuickEditOpenPhoto').data('photo-path');
                const mId = $('#btnQuickEditOpenPhoto').data('id');
                if (!photoPath) return;

                $('#modalPhoto').attr('src', photoPath).show();
                $('#photoZoomToolbar').removeClass('d-none').addClass('d-flex');
                $('#photoViewerViewport').removeClass('d-none').addClass('d-flex');
                if (typeof resetPhotoZoomState === 'function') {
                    resetPhotoZoomState();
                }
                $('#downloadPhoto').attr('href', photoPath).show();
                $('#uploadPhotoMeasurementsId').val(mId);
                $('#photoModalLabel').text(`Evidencia Fotográfica - Medición #${mId}`);
                $('#photoUploadContainer').hide();
                $('#uploadResult').hide();
                $('#photoModal').modal('show');
            });

            $(document).on('click', '.btn-quick-edit', function () {
                const id = $(this).data('id');
                const val = $(this).data('value');
                const rawDate = $(this).data('date');
                const photo = $(this).data('photo');
                const prevVal = $(this).data('prev-val');
                const prevDate = $(this).data('prev-date');
                const nextVal = $(this).data('next-val');
                const nextDate = $(this).data('next-date');

                $('#quickEditMeasurementId').val(id);
                $('#quickEditDisplayId').val('#' + id);
                $('#quickEditValue').val(val);

                let dateVal = '';
                if (rawDate) {
                    const d = new Date(rawDate);
                    if (!isNaN(d.getTime())) {
                        const pad = num => String(num).padStart(2, '0');
                        dateVal = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
                    }
                }
                $('#quickEditDate').val(dateVal);

                // Configurar foto de evidencia
                if (photo) {
                    $('#quickEditPhotoThumb').attr('src', photo);
                    $('#btnQuickEditOpenPhoto').data('photo-path', photo).data('id', id);
                    $('#quickEditPhotoContainer').removeClass('d-none');
                    $('#quickEditNoPhotoNotice').addClass('d-none');
                } else {
                    $('#quickEditPhotoContainer').addClass('d-none');
                    $('#quickEditNoPhotoNotice').removeClass('d-none');
                }

                // Configurar contexto secuencial
                $('#quickEditPrevInfo').text(prevVal !== '' ? `${prevVal} (${prevDate})` : 'Ninguna (Primera medición)');
                $('#quickEditNextInfo').text(nextVal !== '' ? `${nextVal} (${nextDate})` : 'Ninguna (Última medición)');

                let safeRangeMsg = '';
                if (prevVal !== '' && nextVal !== '') {
                    safeRangeMsg = `<i class="bi bi-shield-check me-1"></i> Rango recomendado sin generar consumos negativos: entre <strong>${prevVal}</strong> y <strong>${nextVal}</strong>`;
                } else if (prevVal !== '') {
                    safeRangeMsg = `<i class="bi bi-shield-check me-1"></i> Mínimo recomendado sin consumo negativo: &ge; <strong>${prevVal}</strong>`;
                } else if (nextVal !== '') {
                    safeRangeMsg = `<i class="bi bi-shield-check me-1"></i> Máximo recomendado: &le; <strong>${nextVal}</strong>`;
                }
                $('#quickEditSafeRangeNotice').html(safeRangeMsg);

                $('#quickEditAlert').addClass('d-none').text('');
                $('#quickEditMeasurementModal').modal('show');
            });

            $('#btnSaveQuickEdit').click(function () {
                const id = $('#quickEditMeasurementId').val();
                const val = $('#quickEditValue').val();
                const date = $('#quickEditDate').val();
                const force = $('#quickEditForce').is(':checked');

                if (val === '' || !date) {
                    $('#quickEditAlert').removeClass('d-none').text('Por favor complete la lectura y la fecha.');
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Guardando...');

                $.ajax({
                    url: `/api/measurements/${id}`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json'
                    },
                    data: {
                        'data': { 'valor': parseFloat(val) },
                        'measured_at': date,
                        'force': force ? 1 : 0
                    },
                    success: function (resp) {
                        btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Guardar Corrección');
                        $('#quickEditMeasurementModal').modal('hide');
                        showErrorDetailsModal(currentErrorType);
                        loadMeasurements();
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Guardar Corrección');
                        const msg = xhr.responseJSON?.message || 'Error al actualizar la medición.';
                        $('#quickEditAlert').removeClass('d-none').text(msg);
                    }
                });
            });

            $(document).on('click', '.btn-quick-delete', function () {
                const id = $(this).data('id');
                if (!confirm(`¿Estás seguro de que deseas eliminar permanentemente la medición #${id}?`)) {
                    return;
                }

                $.ajax({
                    url: `/api/measurements/${id}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json'
                    },
                    success: function (resp) {
                        showErrorDetailsModal(currentErrorType);
                        loadMeasurements();
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON?.message || 'Error al eliminar la medición.');
                    }
                });
            });

            // =========================================================
            // 📦 ASISTENTE DE CARGA MASIVA DE FOTOS (ZIP / MULTI-ARCHIVO)
            // =========================================================
            let selectedBulkFiles = [];

            $('#btnBulkUploadPhotos').click(function () {
                selectedBulkFiles = [];
                $('#bulkPhotoInput').val('');
                $('#selectedFilesSummary').addClass('d-none').empty();
                $('#bulkUploadProgressContainer').addClass('d-none');
                $('#bulkUploadProgressBar').css('width', '0%');
                $('#bulkUploadPercent').text('0%');
                $('#bulkUploadResults').addClass('d-none');
                $('#btnSubmitBulkPhotos').prop('disabled', true);
                $('#bulkUploadPhotosModal').modal('show');
            });

            const dropZone = document.getElementById('bulkDropZone');
            if (dropZone) {
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        dropZone.classList.add('bg-primary-subtle', 'border-primary');
                    }, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        dropZone.classList.remove('bg-primary-subtle', 'border-primary');
                    }, false);
                });

                dropZone.addEventListener('drop', (e) => {
                    const dt = e.dataTransfer;
                    if (dt && dt.files && dt.files.length > 0) {
                        handleSelectedBulkFiles(dt.files);
                    }
                }, false);
            }

            $('#bulkPhotoInput').on('change', function () {
                if (this.files && this.files.length > 0) {
                    handleSelectedBulkFiles(this.files);
                }
            });

            function handleSelectedBulkFiles(files) {
                selectedBulkFiles = Array.from(files);
                if (selectedBulkFiles.length === 0) {
                    $('#btnSubmitBulkPhotos').prop('disabled', true);
                    return;
                }

                const isZip = selectedBulkFiles.length === 1 && selectedBulkFiles[0].name.toLowerCase().endsWith('.zip');
                let summaryText = '';
                if (isZip) {
                    const sizeMb = (selectedBulkFiles[0].size / (1024 * 1024)).toFixed(2);
                    summaryText = `<i class="bi bi-file-earmark-zip-fill me-1"></i> Archivo ZIP listo: <strong>${escapeHtml(selectedBulkFiles[0].name)}</strong> (${sizeMb} MB)`;
                } else {
                    summaryText = `<i class="bi bi-images me-1"></i> <strong>${selectedBulkFiles.length}</strong> fotos seleccionadas para carga.`;
                }

                $('#selectedFilesSummary').removeClass('d-none').html(summaryText);
                $('#btnSubmitBulkPhotos').prop('disabled', false);
                $('#bulkUploadResults').addClass('d-none');
            }

            $('#btnSubmitBulkPhotos').click(function () {
                if (selectedBulkFiles.length === 0) return;

                const formData = new FormData();
                const isZip = selectedBulkFiles.length === 1 && selectedBulkFiles[0].name.toLowerCase().endsWith('.zip');

                if (isZip) {
                    formData.append('zip_file', selectedBulkFiles[0]);
                } else {
                    selectedBulkFiles.forEach(file => {
                        formData.append('photos[]', file);
                    });
                }

                $('#btnSubmitBulkPhotos').prop('disabled', true);
                $('#bulkUploadProgressContainer').removeClass('d-none');
                $('#bulkUploadProgressBar').css('width', '10%');
                $('#bulkUploadPercent').text('10%');

                $.ajax({
                    url: '/api/measurements/bulk-upload-photos',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function () {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function (evt) {
                            if (evt.lengthComputable) {
                                const percentComplete = Math.round((evt.loaded / evt.total) * 90);
                                $('#bulkUploadProgressBar').css('width', percentComplete + '%');
                                $('#bulkUploadPercent').text(percentComplete + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function (res) {
                        $('#bulkUploadProgressBar').css('width', '100%');
                        $('#bulkUploadPercent').text('100%');

                        if (res.success && res.summary) {
                            const s = res.summary;
                            $('#statTotalEvaluated').text(s.total_evaluated);
                            $('#statLinkedCount').text(s.linked_count);
                            $('#statRejectedOlder').text(s.rejected_older_than_year);
                            $('#statSkippedNoMatch').text(s.skipped_no_match);
                            $('#bulkUploadResults').removeClass('d-none');

                            // Recargar la tabla para mostrar las nuevas fotos inmediatamente
                            loadMeasurements();
                        } else {
                            alert(res.message || 'Error en la subida.');
                        }
                    },
                    error: function (xhr) {
                        $('#bulkUploadProgressContainer').addClass('d-none');
                        $('#btnSubmitBulkPhotos').prop('disabled', false);
                        alert(xhr.responseJSON?.message || 'Error al procesar el lote de fotos en el servidor.');
                    }
                });
            });

            // =========================================================
            // 📡 RADAR GLOBAL DE ANOMALÍAS
            // =========================================================
            $('#btnOpenGlobalRadar').click(function () {
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
            });

            $('#btnScanGlobal').click(function () {
                const start = $('#radarStartDate').val();
                const end = $('#radarEndDate').val();
                const threshold = $('#radarThreshold').val() || 50;
                const stagnation = $('#radarStagnation').val() || 15;

                if (!start || !end) {
                    alert('Selecciona cotas de inicio y fin.');
                    return;
                }

                $('#radarResults').addClass('d-none');
                $('#radarLoading').removeClass('d-none');

                $.ajax({
                    url: '/api/consumptions/global-anomalies',
                    type: 'POST',
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
                                tbody.append('<tr><td colspan="3" class="text-center text-success py-3"><i class="bi bi-check-circle-fill"></i> Todos los sistemas bajo límites nominales. Cero anomalías temporales encontradas.</td></tr>');
                            } else {
                                res.data.forEach(function (item) {
                                    let tagsHtml = '';
                                    if (item.acceleration_count > 0) {
                                        tagsHtml += '<span class="badge bg-danger shadow-sm me-1 mt-1"><i class="bi bi-graph-up-arrow"></i> ' + item.acceleration_count + ' picos</span>';
                                    }
                                    if (item.stagnation_count > 0) {
                                        tagsHtml += '<span class="badge bg-secondary shadow-sm me-1 mt-1"><i class="bi bi-hourglass-bottom"></i> ' + item.stagnation_count + ' estancados</span>';
                                    }

                                    tbody.append(
                                        '<tr>' +
                                        '   <td><strong>' + escapeHtml(item.sensor_name) + '</strong><br><code class="small">' + escapeHtml(item.sensor_identifier) + '</code></td>' +
                                        '   <td>' + tagsHtml + '</td>' +
                                        '   <td>' +
                                        '       <button class="btn btn-sm btn-primary zoomSensorBtn" data-sensor-id="' + item.sensor_id + '" data-sensor-name="' + escapeHtml(item.sensor_name) + '"><i class="bi bi-bar-chart-fill"></i> Zoom</button>' +
                                        '   </td>' +
                                        '</tr>'
                                    );
                                });
                            }
                            $('#radarResults').removeClass('d-none');
                            $('#radarSearch').val('');
                        } else {
                            alert(res.message || 'Error en radar.');
                        }
                    },
                    error: function (xhr) {
                        $('#radarLoading').addClass('d-none');
                        alert(xhr.responseJSON?.message || 'Error escaneando anomalías masivas.');
                    }
                });
            });

            $(document).on('click', '.zoomSensorBtn', function () {
                const sId = $(this).data('sensor-id');
                const sName = $(this).data('sensor-name');
                const start = $('#radarStartDate').val();
                const end = $('#radarEndDate').val();
                $('#globalRadarModal').modal('hide');
                setTimeout(() => {
                    openAnalyticsModal(sId, sName, true, start, end);
                }, 400);
            });

            $('#radarSearch').on('keyup', function () {
                const value = $(this).val().toLowerCase();
                $('#radarTableBody tr').filter(function () {
                    if ($(this).find('.bi-check-circle-fill').length > 0) return;
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            // =========================================================
            // 📈 ANÁLISIS AVANZADO Y GRÁFICO POR SENSOR
            // =========================================================
            let evolutionChartInstance = null;

            $(document).on('click', '.analyzeSensorBtn', function (e) {
                e.preventDefault();
                const sensorId = $(this).data('sensor-id');
                const sensorName = $(this).data('sensor-name');
                openAnalyticsModal(sensorId, sensorName, true);
            });

            function openAnalyticsModal(sensorId, sensorName, autoCalc = false, inheritStartDate = null, inheritEndDate = null) {
                $('#analyzeSensorId').val(sensorId);
                $('#analyzeSensorName').text(sensorName);

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

                $.ajax({
                    url: '/api/consumptions/sensor-meta/' + sensorId,
                    type: 'GET',
                    success: function (res) {
                        $('#metaLoadingStatus').addClass('d-none');
                        if (res.success && res.data && res.data.first_date && res.data.last_date) {
                            $('#metaFirstDate').text(new Date(res.data.first_date).toLocaleDateString());
                            $('#metaLastDate').text(new Date(res.data.last_date).toLocaleDateString());
                            $('#metaAvailableDates').removeClass('d-none');
                        }
                    },
                    error: function () {
                        $('#metaLoadingStatus').addClass('d-none');
                    },
                    complete: function () {
                        if (autoCalc) {
                            $('#btnCalculateRange').click();
                        }
                    }
                });
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
                            if (d.financial_cost !== null && d.financial_cost !== undefined) {
                                $('#resFinalBilledTotal').text(d.currency + ' $' + Number(d.financial_cost).toLocaleString('es-ES', { minimumFractionDigits: 2 }));
                                $('#resFinalBilledTotal').removeClass('text-dark').addClass('text-success');
                            } else {
                                $('#resFinalBilledTotal').text((d.final_billed_total || d.total_consumption).toFixed(2) + ' ' + d.unit);
                                $('#resFinalBilledTotal').removeClass('text-success').addClass('text-dark');
                            }
                            $('#resDailyAvg').text(d.daily_average + ' ' + d.unit);
                            $('#resDaysBetween').text(parseFloat(d.days_between).toFixed(0));
                            $('#resMeasurementsCount').text(d.measurements_count);

                            renderEvolutionChart(d.chart_data, d.unit);
                            $('#analyzeResults').removeClass('d-none');
                        } else {
                            alert(res.message || 'Error en cálculo.');
                        }
                    },
                    error: function (xhr) {
                        $('#analyzeLoading').addClass('d-none');
                        alert(xhr.responseJSON?.message || 'No hay mediciones suficientes para analizar en este rango.');
                    }
                });
            });

            function renderEvolutionChart(chartData, unit) {
                const canvas = document.getElementById('evolutionChart');
                if (!canvas) return;
                const ctx = canvas.getContext('2d');

                if (evolutionChartInstance) {
                    evolutionChartInstance.destroy();
                }

                if (!chartData || chartData.length === 0) return;

                const labels = chartData.map(c => c.date);
                const dataValues = chartData.map(c => c.value);
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
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: false,
                                title: { display: true, text: unit || 'unidades', font: { size: 10 } },
                                ticks: { font: { size: 10 } }
                            },
                            x: {
                                ticks: { font: { size: 9 }, maxRotation: 45, minRotation: 45 }
                            }
                        }
                    }
                });
            }

            // Leer parámetros de la URL para filtros o apertura automática de auditoría
            const urlParams = new URLSearchParams(window.location.search);
            const urlErrorType = urlParams.get('error_type');
            const openAudit = urlParams.get('open_audit');

            if (urlErrorType) {
                $('#errorFilter').val(urlErrorType);
                currentErrorType = urlErrorType;
                loadMeasurements();
                // Abrir el modal de auditoría para este tipo específico
                setTimeout(() => {
                    showErrorDetailsModal(urlErrorType);
                }, 500);
            } else if (openAudit === '1') {
                loadMeasurements();
                setTimeout(() => {
                    $('#auditDetailsModal').modal('show');
                }, 500);
            } else {
                // Cargar mediciones al inicio de forma habitual
                loadMeasurements();
            }
        });
    </script>
@endpush