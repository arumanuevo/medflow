@extends('layouts.modern')

@section('title', 'Gestión de Mediciones - Ayuda Completa')

@section('content')
@php
    \Log::info('🔍 Vista select-sensor', [
        'groups_count' => isset($groups) ? $groups->count() : 0,
        'groups' => isset($groups) ? $groups->pluck('name')->toArray() : [],
    ]);
@endphp

@if(isset($groups) && $groups->isEmpty())
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> 
        <strong>Debug:</strong> No hay grupos con sensoresdddddd.
        @if(!$isOwner)
            <br>Espacio activo: {{ $activeWorkspace }}
            <br>¿Eres colaborador? {{ auth()->user()->collaborations()->where('workspace_id', $activeWorkspace)->where('status', 'active')->exists() ? 'Sí' : 'No' }}
        @endif
    </div>
@endif
<!-- Incluir el archivo CSS externo -->
<link rel="stylesheet" href="{{ asset('css/help-styles.css') }}">

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <h4><i class="bi bi-rulers"></i> Gestión de Mediciones</h4>
                        <div class="ms-3 d-flex gap-2">
                            <button class="btn btn-light btn-sm" id="showHelpModal" title="Ver ayuda completa">
                                <i class="bi bi-question-circle-fill"></i> Ayuda
                            </button>
                            <button class="btn btn-light btn-sm" id="startTour" title="Iniciar tour guiado">
                                <i class="bi bi-play-circle-fill"></i> Tour
                            </button>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-light btn-sm" id="refreshStats" title="Actualizar estadísticas">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                        <a href="{{ route('sensor-groups.index') }}" class="btn btn-light btn-sm" title="Volver a Grupos">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">

                    <!-- Contenedor para alertas globales -->
                    <div id="globalAlertContainer" class="mb-3"></div>
                    <!-- Estadísticas -->
                    <div class="row mb-4" id="statsCards">
                        <div class="col-md-3">
                            <div class="card text-white bg-primary mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title"><i class="bi bi-inbox"></i> Total</h5>
                                            <p class="card-text h4" id="totalSensors">0</p>
                                        </div>
                                        <button class="btn btn-light btn-sm" data-bs-toggle="tooltip" title="Número total de sensores a los que tienes acceso">
                                            <i class="bi bi-info-circle"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-success mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title"><i class="bi bi-check-circle"></i> Al Día</h5>
                                            <p class="card-text h4" id="alDiaSensors">0</p>
                                        </div>
                                        <button class="btn btn-light btn-sm" data-bs-toggle="tooltip" title="Sensores que no necesitan medición aún">
                                            <i class="bi bi-info-circle"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-warning mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title"><i class="bi bi-exclamation-triangle"></i> Pendientes</h5>
                                            <p class="card-text h4" id="pendingSensors">0</p>
                                        </div>
                                        <button class="btn btn-light btn-sm" data-bs-toggle="tooltip" title="Sensores que necesitan medición pronto (dentro del período de vencimiento)">
                                            <i class="bi bi-info-circle"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-danger mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title"><i class="bi bi-x-circle"></i> Vencidos</h5>
                                            <p class="card-text h4" id="overdueSensors">0</p>
                                        </div>
                                        <button class="btn btn-light btn-sm" data-bs-toggle="tooltip" title="Sensores con medición vencida (fuera del período de vencimiento)">
                                            <i class="bi bi-info-circle"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros -->
<div class="row mb-4" id="filterControls">
    <div class="col-md-3">
        <label for="groupFilter" class="form-label">Grupo</label>
        <div class="input-group">
            <select class="form-select form-select-sm" id="groupFilter">
                <option value="" selected>Todos los grupos</option>
            </select>
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="tooltip" title="Filtrar sensores por grupo">
                <i class="bi bi-info-circle"></i>
            </button>
        </div>
    </div>
    <div class="col-md-3">
        <label for="statusFilter" class="form-label">Estado</label>
        <div class="input-group">
            <select class="form-select form-select-sm" id="statusFilter">
                <option value="all" selected>Todos los estados</option>
                <option value="al_dia">Al Día</option>
                <option value="pending">Pendientes</option>
                <option value="overdue">Vencidos</option>
                <option value="marked">Marcados para Medición</option>
            </select>
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="tooltip" title="Filtrar por estado: Al Día, Pendientes, Vencidos o Marcados">
                <i class="bi bi-info-circle"></i>
            </button>
        </div>
    </div>
    <div class="col-md-3">
        <label for="searchInput" class="form-label">Buscar</label>
        <div class="input-group">
            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Nombre o identificador...">
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="tooltip" title="Buscar sensores por nombre o identificador">
                <i class="bi bi-info-circle"></i>
            </button>
        </div>
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <button class="btn btn-primary w-100" id="applyFilters">
            <i class="bi bi-funnel"></i> Aplicar Filtros
        </button>
    </div>
</div>

                    <!-- Acciones masivas -->
                    <div class="row mb-4" id="bulkActions">
                        <div class="col-md-12 d-flex gap-2">
                            <button class="btn btn-success" id="markAllForMeasurement" data-bs-toggle="tooltip" title="Marca todos los sensores del grupo seleccionado para medición">
                                <i class="bi bi-check-square"></i> Marcar Todos para Medición
                            </button>
                            <button class="btn btn-danger" id="unmarkAllForMeasurement" data-bs-toggle="tooltip" title="Desmarca todos los sensores">
                                <i class="bi bi-x-square"></i> Desmarcar Todos
                            </button>
                            <button class="btn btn-info" id="takeBulkMeasurement" data-bs-toggle="tooltip" title="Inicia el proceso de medición para los sensores seleccionados">
                                <i class="bi bi-rulers"></i> Tomar Mediciones Masivas
                            </button>
                             <!-- Botón para importar mediciones desde Excel -->
                            <a href="{{ route('measurements.bulk-import') }}" class="btn btn-warning" data-bs-toggle="tooltip" title="Importar mediciones desde archivo Excel o CSV (múltiples sensores)">
                                <i class="bi bi-file-earmark-excel"></i> Importar Mediciones
                            </a>
                        </div>
                    </div>

                    <!-- Tabla de Sensores -->
                    <div class="table-responsive" id="sensorsTableContainer">
                        <table class="table table-bordered table-striped table-hover" id="sensorsTable">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAllSensors" data-bs-toggle="tooltip" title="Seleccionar/desseleccionar todos los sensores">
                                    </th>
                                    <th data-sort="id">ID <i class="bi bi-arrow-up-down"></i></th>
                                    <th data-sort="sensor">Sensor <i class="bi bi-arrow-up-down"></i></th>
                                    <th data-sort="identifier">Identificador <i class="bi bi-arrow-up-down"></i></th>
                                    <th data-sort="group">Grupo <i class="bi bi-arrow-up-down"></i></th>
                                    <th data-sort="last_measurement">Última Medición <i class="bi bi-arrow-up-down"></i></th>
                                    <th data-sort="next_measurement">Próxima Medición <i class="bi bi-arrow-up-down"></i></th>
                                    <th data-sort="days_remaining">Días Restantes <i class="bi bi-arrow-up-down"></i></th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="sensorsTableBody">
                                <tr>
                                    <td colspan="10" class="text-center">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <p class="mt-2">Cargando sensores...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div id="paginationInfo"></div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination" id="pagination"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Ayuda Completa -->

<!-- Modal para Ayuda Completa -->
<div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down modal-xl">
        <div class="modal-content d-flex flex-column h-100">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="helpModalLabel">
                    <i class="bi bi-book"></i> Ayuda Completa: Gestión de Mediciones
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body flex-grow-1 p-0 overflow-hidden">
                <div class="row g-0 h-100">
                    <!-- Sidebar izquierdo -->
                    <div class="col-12 col-lg-3 col-xl-2 d-none d-lg-block border-end" id="helpSidebar" style="background-color: #f8f9fa; height: 100%; overflow-y: auto;">
                        <div class="p-3 border-bottom">
                            <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i> Contenido</h6>
                        </div>
                        <div class="nav flex-column nav-pills p-2" id="helpNav" role="tablist" aria-orientation="vertical">
                            <a class="nav-link active d-flex align-items-center py-2 px-3" id="help-intro-tab" data-bs-toggle="pill" href="#help-intro" role="tab" aria-controls="help-intro" aria-selected="true">
                                <i class="bi bi-info-circle me-2"></i>
                                <span>Introducción</span>
                            </a>
                            <a class="nav-link d-flex align-items-center py-2 px-3" id="help-estados-tab" data-bs-toggle="pill" href="#help-estados" role="tab" aria-controls="help-estados" aria-selected="false">
                                <i class="bi bi-bar-chart-line me-2"></i>
                                <span>Estados de Sensores</span>
                            </a>
                            <a class="nav-link d-flex align-items-center py-2 px-3" id="help-filtros-tab" data-bs-toggle="pill" href="#help-filtros" role="tab" aria-controls="help-filtros" aria-selected="false">
                                <i class="bi bi-funnel me-2"></i>
                                <span>Filtros y Búsqueda</span>
                            </a>
                            <a class="nav-link d-flex align-items-center py-2 px-3" id="help-masivas-tab" data-bs-toggle="pill" href="#help-masivas" role="tab" aria-controls="help-masivas" aria-selected="false">
                                <i class="bi bi-collection me-2"></i>
                                <span>Mediciones Masivas</span>
                            </a>
                            <a class="nav-link d-flex align-items-center py-2 px-3" id="help-flujo-tab" data-bs-toggle="pill" href="#help-flujo" role="tab" aria-controls="help-flujo" aria-selected="false">
                                <i class="bi bi-diagram-3 me-2"></i>
                                <span>Flujo de Trabajo</span>
                            </a>
                        </div>
                    </div>

                    <!-- Botón para mostrar/ocultar sidebar en móviles -->
                    <button class="d-lg-none btn btn-primary position-absolute top-0 start-0 mt-2 ms-2 z-index-3" id="toggleHelpSidebar">
                        <i class="bi bi-list"></i>
                    </button>

                    <!-- Contenido principal -->
                    <div class="col-12 col-lg-9 col-xl-10 overflow-auto p-3 p-lg-4" id="helpContentContainer">
                        <div class="tab-content" id="helpTabContent">
                            <!-- Pestaña Introducción -->
                            <div class="tab-pane fade show active" id="help-intro" role="tabpanel" aria-labelledby="help-intro-tab">
                                <h4 class="mb-3"><i class="bi bi-info-circle me-2"></i> Introducción</h4>
                                <p class="mb-3">
                                    Esta página está diseñada para ayudarte a gestionar las mediciones de tus sensores de manera eficiente.
                                </p>
                                <div class="card mb-3 border-0 shadow-sm">
                                    <div class="card-body">
                                        <ul class="mb-0">
                                            <li class="mb-2">
                                                <strong>Tomar mediciones individuales:</strong>
                                                Haz clic en el botón <span class="badge bg-success"><i class="bi bi-rulers me-1"></i> Tomar Medición</span>
                                            </li>
                                            <li class="mb-2">
                                                <strong>Gestionar mediciones masivas:</strong>
                                                Marca varios sensores y usa el botón <span class="badge bg-info"><i class="bi bi-rulers me-1"></i> Tomar Mediciones Masivas</span>
                                            </li>
                                            <li class="mb-2"><strong>Filtrar y buscar:</strong> Usa los filtros para encontrar sensores por grupo, estado o nombre.</li>
                                            <li><strong>Visualizar el estado:</strong> Ve el estado de cada sensor (Al Día, Pendiente, Vencido).</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="alert alert-info">
                                    <strong><i class="bi bi-lightbulb me-2"></i> Consejos:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Los sensores marcados en <span class="badge bg-warning text-dark">Pendiente</span> o <span class="badge bg-danger">Vencido</span> son prioridad.</li>
                                        <li>Usa los filtros para enfocarte en los sensores que necesitan atención.</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Pestaña Estados de Sensores -->
                            <div class="tab-pane fade" id="help-estados" role="tabpanel" aria-labelledby="help-estados-tab">
                                <h4 class="mb-3"><i class="bi bi-bar-chart-line me-2"></i> Estados de Sensores</h4>
                                <p class="mb-4">
                                    Cada sensor tiene un estado que depende de su última medición y de la configuración de su grupo.
                                </p>

                                <div class="row">
                                    <div class="col-xl-4 col-lg-6 mb-4">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <div class="card-body d-flex align-items-start">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="p-2 bg-success text-white rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="bi bi-check-circle" style="font-size: 1.2rem;"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-2">Al Día</h5>
                                                    <p class="mb-2 small">
                                                        El sensor no necesita medición aún. La próxima medición está programada para una fecha futura <strong>fuera del período de vencimiento</strong>.
                                                    </p>
                                                    <p class="text-muted small mb-0">
                                                        <strong>Ejemplo:</strong> Si el período de medición es 30 días y la última medición fue hace 20 días.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-lg-6 mb-4">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <div class="card-body d-flex align-items-start">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="p-2 bg-warning text-dark rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="bi bi-exclamation-triangle" style="font-size: 1.2rem;"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-2">Pendiente</h5>
                                                    <p class="mb-2 small">
                                                        La próxima medición está cerca (<strong>dentro del período de vencimiento</strong>). <strong>Prioridad media.</strong>
                                                    </p>
                                                    <p class="text-muted small mb-0">
                                                        <strong>Ejemplo:</strong> Si el período de vencimiento es 5 días y la próxima medición es en 3 días.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-lg-6 mb-4">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <div class="card-body d-flex align-items-start">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="p-2 bg-danger text-white rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="bi bi-x-circle" style="font-size: 1.2rem;"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-2">Vencido</h5>
                                                    <p class="mb-2 small">
                                                        La medición está vencida (<strong>fuera del período de vencimiento</strong>). <strong>Prioridad alta.</strong>
                                                    </p>
                                                    <p class="text-muted small mb-0">
                                                        <strong>Ejemplo:</strong> Si la próxima medición era hace 2 días.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="mb-3"><i class="bi bi-gear me-2"></i> Configuración de Estados</h5>
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <p class="mb-3">
                                            Los estados se calculan automáticamente en función de dos parámetros configurables por grupo:
                                        </p>
                                        <div class="row">
                                            <div class="col-12 col-md-6 mb-3 mb-md-0">
                                                <h6 class="mb-2"><i class="bi bi-calendar-range me-2"></i> Período de medición</h6>
                                                <p class="mb-2 small">Número de días entre mediciones para un grupo de sensores.</p>
                                                <small class="text-muted">Ejemplo: 30 días para sensores de agua, 7 días para sensores de temperatura.</small>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <h6 class="mb-2"><i class="bi bi-hourglass-split me-2"></i> Días de vencimiento</h6>
                                                <p class="mb-2 small">Tolerancia en días para considerar una medición como "Pendiente".</p>
                                                <small class="text-muted">Ejemplo: 5 días.</small>
                                            </div>
                                        </div>
                                        <div class="alert alert-light mt-3 mb-0">
                                            <strong>Fórmula:</strong>
                                            <div class="mt-2">
                                                <code class="d-block mb-1">Próxima medición = Última medición + Período de medición</code>
                                                <code class="d-block mb-1">Estado = Pendiente si (Días hasta próxima ≤ Días de vencimiento)</code>
                                                <code class="d-block">Estado = Vencido si (Días hasta próxima &lt; 0)</code>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pestaña Filtros y Búsqueda -->
                            <div class="tab-pane fade" id="help-filtros" role="tabpanel" aria-labelledby="help-filtros-tab">
                                <h4 class="mb-3"><i class="bi bi-funnel me-2"></i> Filtros y Búsqueda</h4>
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="mb-3"><i class="bi bi-funnel-fill me-2"></i> Filtros Disponibles</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Filtro</th>
                                                        <th>Descripción</th>
                                                        <th>Ejemplo</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><strong>Grupo</strong></td>
                                                        <td>Filtrar sensores por grupo específico.</td>
                                                        <td>Seleccionar "Garage"</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Estado</strong></td>
                                                        <td>Filtrar por estado: Al Día, Pendiente, Vencido.</td>
                                                        <td>Seleccionar "Pendiente"</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Búsqueda</strong></td>
                                                        <td>Buscar por nombre o identificador.</td>
                                                        <td>Escribir "sensor 1"</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-info">
                                    <strong><i class="bi bi-lightbulb me-2"></i> Consejo:</strong>
                                    <p class="mb-0 mt-2">Combina varios filtros para afinar tu búsqueda.</p>
                                </div>
                            </div>

                            <!-- Pestaña Mediciones Masivas -->
                            <div class="tab-pane fade" id="help-masivas" role="tabpanel" aria-labelledby="help-masivas-tab">
                                <h4 class="mb-3"><i class="bi bi-collection me-2"></i> Mediciones Masivas</h4>
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="mb-3"><i class="bi bi-check-square me-2"></i> ¿Qué son las mediciones masivas?</h5>
                                        <p class="mb-3">
                                            Permiten tomar mediciones de varios sensores en una sola sesión, sin seleccionar cada sensor individualmente.
                                        </p>
                                        <h5 class="mb-3"><i class="bi bi-123 me-2"></i> Pasos para Realizar Mediciones Masivas</h5>
                                        <ol class="mb-0">
                                            <li class="mb-2">
                                                <strong>Seleccionar sensores:</strong>
                                                <ul class="mb-2">
                                                    <li>Usa los filtros para encontrar los sensores.</li>
                                                    <li>Marca los sensores individualmente haciendo clic en el botón <span class="badge bg-warning text-dark"><i class="bi bi-square me-1"></i></span>.</li>
                                                    <li>O usa el botón <strong>"Marcar Todos para Medición"</strong>.</li>
                                                </ul>
                                            </li>
                                            <li class="mb-2">
                                                <strong>Iniciar medición masiva:</strong>
                                                <ul class="mb-2">
                                                    <li>Haz clic en el botón <strong>"Tomar Mediciones Masivas"</strong>.</li>
                                                    <li>El sistema te redirigirá automáticamente al primer sensor.</li>
                                                </ul>
                                            </li>
                                            <li>
                                                <strong>Tomar mediciones:</strong>
                                                <ul class="mb-0">
                                                    <li>Completa el formulario de medición para el sensor actual.</li>
                                                    <li>Guarda la medición.</li>
                                                    <li>El sistema te redirigirá al siguiente sensor marcado.</li>
                                                </ul>
                                            </li>
                                        </ol>
                                    </div>
                                </div>
                                <div class="alert alert-success">
                                    <strong><i class="bi bi-check-circle me-2"></i> Beneficios:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Ahorra tiempo al medir varios sensores seguidos.</li>
                                        <li>Evita olvidar sensores importantes.</li>
                                        <li>Prioriza sensores por estado (Pendiente/Vencido).</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Pestaña Flujo de Trabajo - CORREGIDA -->
                            <div class="tab-pane fade" id="help-flujo" role="tabpanel" aria-labelledby="help-flujo-tab">
                                <h4 class="mb-3"><i class="bi bi-diagram-3 me-2"></i> Flujo de Trabajo</h4>
                                
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="mb-4"><i class="bi bi-arrow-right-circle me-2"></i> Flujo de Trabajo Básico</h5>
                                        <p class="text-muted mb-4">Sigue estos pasos para gestionar tus mediciones de manera eficiente:</p>
                                        
                                        <!-- Pasos del flujo -->
                                        <div class="d-flex flex-wrap justify-content-center align-items-start gap-3">
                                            <!-- Paso 1 -->
                                            <div class="text-center" style="min-width: 80px;">
                                                <div class="p-3 bg-primary text-white rounded-circle mx-auto mb-2" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="bi bi-house-door" style="font-size: 1.5rem;"></i>
                                                </div>
                                                <small class="d-block fw-semibold">Inicio</small>
                                                <small class="text-muted" style="font-size: 0.7rem;">Dashboard</small>
                                            </div>

                                            <div class="d-flex align-items-center" style="font-size: 1.5rem; color: #0d6efd;">
                                                <i class="bi bi-arrow-right"></i>
                                            </div>

                                            <!-- Paso 2 -->
                                            <div class="text-center" style="min-width: 80px;">
                                                <div class="p-3 bg-info text-white rounded-circle mx-auto mb-2" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="bi bi-funnel" style="font-size: 1.5rem;"></i>
                                                </div>
                                                <small class="d-block fw-semibold">Filtrar</small>
                                                <small class="text-muted" style="font-size: 0.7rem;">Sensores</small>
                                            </div>

                                            <div class="d-flex align-items-center" style="font-size: 1.5rem; color: #0d6efd;">
                                                <i class="bi bi-arrow-right"></i>
                                            </div>

                                            <!-- Paso 3 -->
                                            <div class="text-center" style="min-width: 80px;">
                                                <div class="p-3 bg-warning text-dark rounded-circle mx-auto mb-2" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="bi bi-check-square" style="font-size: 1.5rem;"></i>
                                                </div>
                                                <small class="d-block fw-semibold">Marcar</small>
                                                <small class="text-muted" style="font-size: 0.7rem;">Sensores</small>
                                            </div>

                                            <div class="d-flex align-items-center" style="font-size: 1.5rem; color: #0d6efd;">
                                                <i class="bi bi-arrow-right"></i>
                                            </div>

                                            <!-- Paso 4 -->
                                            <div class="text-center" style="min-width: 80px;">
                                                <div class="p-3 bg-success text-white rounded-circle mx-auto mb-2" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="bi bi-rulers" style="font-size: 1.5rem;"></i>
                                                </div>
                                                <small class="d-block fw-semibold">Medir</small>
                                                <small class="text-muted" style="font-size: 0.7rem;">Tomar Mediciones</small>
                                            </div>
                                        </div>

                                        <!-- Leyenda -->
                                        <div class="mt-4 pt-3 border-top">
                                            <div class="row g-2">
                                                <div class="col-md-3 col-6">
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-primary me-2">1</span>
                                                        <small>Inicio</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-6">
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-info me-2">2</span>
                                                        <small>Filtrar Sensores</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-6">
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-warning text-dark me-2">3</span>
                                                        <small>Marcar Sensores</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-6">
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-success me-2">4</span>
                                                        <small>Tomar Mediciones</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info mt-3">
                                    <i class="bi bi-lightbulb me-2"></i>
                                    <strong>Consejo:</strong> 
                                    <span class="small">El flujo de trabajo está diseñado para ser rápido y eficiente. Marca varios sensores a la vez para ahorrar tiempo.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Acciones -->
<div class="modal fade" id="confirmActionModal" tabindex="-1" aria-labelledby="confirmActionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="confirmActionModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i> Confirmar Acción
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="confirmActionMessage">
                <!-- El mensaje se inserta dinámicamente con JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="confirmActionButton">
                    <i class="bi bi-check-circle me-2"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<!-- Shepherd.js para tours guiados -->
<script src="https://cdn.jsdelivr.net/npm/shepherd.js@11.0.1/dist/js/shepherd.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@11.0.1/dist/css/shepherd.css">

<!-- Elemento oculto para el tour guiado -->
<div id="tourStart" style="display: none;"></div>

<script>
$(document).ready(function() {
    // =============================================
    // VARIABLES GLOBALES
    // =============================================
    let currentPage = 1;
    const itemsPerPage = 20;
    let currentGroupId = null;
    let currentStatus = 'all';
    let currentSearch = '';
    let currentAction = null;
    let selectedSensors = new Set();
    let helpContentCache = {};

    // =============================================
    // FUNCIONES DE INICIALIZACIÓN
    // =============================================

    // Inicializar tooltips
    function initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                trigger: 'hover',
                placement: 'top'
            });
        });
    }

    // Inicializar el tour guiado
    function initTour(tourSteps) {
        if (window.shepherdTour) {
            window.shepherdTour.destroy();
        }

        window.shepherdTour = new Shepherd.Tour({
            defaultStepOptions: {
                cancelIcon: { enabled: true },
                classes: 'shadow-md bg-purple-dark',
                scrollTo: { behavior: 'smooth', block: 'center' }
            }
        });

        if (tourSteps && tourSteps.length > 0) {
            tourSteps.forEach(step => {
                const element = document.getElementById(step.target_element);
                if (element) {
                    window.shepherdTour.addStep({
                        id: step.key,
                        text: step.content,
                        attachTo: { element: `#${step.target_element}`, on: 'bottom' },
                        buttons: [{ text: 'Siguiente', action: window.shepherdTour.next }]
                    });
                } else {
                    console.warn(`Elemento #${step.target_element} no encontrado. Usando #tourStart.`);
                    window.shepherdTour.addStep({
                        id: step.key,
                        text: step.content,
                        attachTo: { element: '#tourStart', on: 'bottom' },
                        buttons: [{ text: 'Siguiente', action: window.shepherdTour.next }]
                    });
                }
            });
        } else {
            window.shepherdTour.addStep({
                id: 'default-step',
                text: 'Bienvenido a la página de Gestión de Mediciones.',
                attachTo: { element: '#tourStart', on: 'bottom' },
                buttons: [{ text: 'Cerrar', action: window.shepherdTour.cancel }]
            });
        }

        $('#startTour').off('click').on('click', function() {
            if (window.shepherdTour) window.shepherdTour.start();
        });
    }

    // =============================================
    // FUNCIONES DE CARGA DE DATOS
    // =============================================

    // Cargar contenido de ayuda desde el backend
    function loadHelpContent() {
        $.ajax({
            url: '/api/help/measurements.select-sensor',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success && response.data) {
                    helpContentCache = {};
                    response.data.forEach(item => helpContentCache[item.key] = item);
                    loadHelpModalContent();
                    initTour(response.data.filter(item => item.type === 'tour'));
                } else {
                    loadFallbackHelpContent();
                }
            },
            error: function(xhr) {
                console.error('Error al cargar contenido de ayuda:', xhr.statusText);
                loadFallbackHelpContent();
            }
        });
    }

    // Cargar contenido estático de respaldo
    function loadFallbackHelpContent() {
        helpContentCache = {
            'select_sensor_guide_intro': {
                content: `
                    <h4><i class="bi bi-info-circle me-2"></i> Introducción</h4>
                    <p>Esta página está diseñada para ayudarte a gestionar las mediciones de tus sensores de manera eficiente.</p>
                    <div class="card mb-3">
                        <div class="card-body">
                            <ul class="mb-0">
                                <li class="mb-2">
                                    <strong>Tomar mediciones individuales:</strong>
                                    Haz clic en el botón <span class="badge bg-success"><i class="bi bi-rulers me-1"></i> Tomar Medición</span>
                                </li>
                                <li class="mb-2">
                                    <strong>Gestionar mediciones masivas:</strong>
                                    Marca varios sensores y usa el botón <span class="badge bg-info"><i class="bi bi-rulers me-1"></i> Tomar Mediciones Masivas</span>
                                </li>
                                <li class="mb-2"><strong>Filtrar y buscar:</strong> Usa los filtros para encontrar sensores por grupo, estado o nombre.</li>
                                <li><strong>Visualizar el estado:</strong> Ve el estado de cada sensor (Al Día, Pendiente, Vencido).</li>
                            </ul>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <strong><i class="bi bi-lightbulb me-2"></i> Consejos:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Los sensores marcados en <span class="badge bg-warning text-dark">Pendiente</span> o <span class="badge bg-danger">Vencido</span> son prioridad.</li>
                            <li>Usa los filtros para enfocarte en los sensores que necesitan atención.</li>
                        </ul>
                    </div>
                `
            },
        };
        loadHelpModalContent();
    }

    // Cargar contenido del modal de ayuda
    function loadHelpModalContent() {
        const tabToKeyMap = {
            'help-intro': 'select_sensor_guide_intro',
            'help-estados': 'select_sensor_guide_estados',
            'help-filtros': 'select_sensor_guide_filtros',
            'help-masivas': 'select_sensor_guide_masivas',
            'help-flujo': 'select_sensor_guide_flujo'
        };

        for (const [paneId, key] of Object.entries(tabToKeyMap)) {
            const content = helpContentCache[key]?.content || `
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> Contenido no disponible.
                </div>
            `;
            $(`#${paneId}`).html(content);
        }

        $('#helpNav a[data-bs-toggle="pill"]').on('click', function() {
            const tabId = $(this).attr('aria-controls');
            $('#helpNav a').removeClass('active');
            $('.tab-pane').removeClass('active show');
            $(this).addClass('active');
            $(`#${tabId}`).addClass('active show');
        });
    }

    function loadGroups() {
        $.ajax({
            url: '/api/sensor-groups',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    const groups = response.data;
                    $('#groupFilter').empty().append('<option value="" selected>Todos los grupos</option>');
                    groups.forEach(group => $('#groupFilter').append(`<option value="${group.id}">${group.name}</option>`));
                }
            },
            error: function(xhr) {
                console.error('Error al cargar grupos:', xhr.statusText);
            }
        });
    }

    function loadStats() {
        $.ajax({
            url: '/api/bulk/measurements/stats',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    const stats = response.data;
                    $('#totalSensors').text(stats.total);
                    $('#alDiaSensors').text(stats.al_dia);
                    $('#pendingSensors').text(stats.pendiente);
                    $('#overdueSensors').text(stats.vencido);
                }
            },
            error: function(xhr) {
                console.error('Error al cargar estadísticas:', xhr.statusText);
            }
        });
    }

    function loadSensors() {
        const params = {
            page: currentPage,
            per_page: itemsPerPage,
            group_id: currentGroupId,
            status: currentStatus,
            search: currentSearch
        };

        console.log('🔍 Cargando sensores con parámetros:', params);

        $.ajax({
            url: '/api/bulk/measurements/sensors',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            },
            data: params,
            beforeSend: function() {
                $('#sensorsTableBody').html(`
                    <tr>
                        <td colspan="10" class="text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">Cargando sensores...</p>
                        </td>
                    </tr>
                `);
            },
            success: function(response) {
                console.log('✅ Sensores cargados:', response);
                if (response.success) {
                    renderSensors(response.data);
                    loadStats();
                    
                    // ✅ Sincronizar checkboxes con estado de la base de datos
                    $('.sensorCheckbox').each(function() {
                        const sensorId = $(this).closest('tr').data('sensor-id');
                        const sensor = response.data.find(s => s.id === sensorId);
                        if (sensor) {
                            $(this).prop('checked', sensor.marcado_para_medicion === true);
                        }
                    });
                }
            },
            error: function(xhr) {
                console.error('❌ Error al cargar sensores:', xhr);
                showError('Error: ' + (xhr.responseJSON?.message || xhr.statusText));
            }
        });
    }

    // =============================================
    // FUNCIONES DE RENDERIZADO
    // =============================================

    function showError(message) {
        $('#sensorsTableBody').html(`
            <tr>
                <td colspan="10" class="text-center text-danger">
                    <i class="bi bi-exclamation-triangle"></i> ${message}
                </td>
            </tr>
        `);
    }

    function renderSensors(sensors) {
        if (sensors.length === 0) {
            $('#sensorsTableBody').html(`
                <tr>
                    <td colspan="10" class="text-center">
                        <i class="bi bi-inbox"></i> No se encontraron sensores
                    </td>
                </tr>
            `);
            $('#pagination').html('');
            $('#paginationInfo').html('');
            return;
        }

        let html = '';
        sensors.forEach(sensor => {
            const estadoClass = getEstadoClass(sensor.estado);
            const estadoText = getEstadoText(sensor.estado);

            const diasRestantes = sensor.dias_hasta_proxima !== null
                ? parseFloat(sensor.dias_hasta_proxima).toFixed(2)
                : 'N/A';

            const lastMeasurementDate = sensor.last_measurement_date
                ? new Date(sensor.last_measurement_date).toLocaleString('es-ES')
                : 'N/A';

            const proximaMedicionDate = sensor.proxima_medicion
                ? new Date(sensor.proxima_medicion).toLocaleString('es-ES')
                : 'N/A';

            const diasClass = diasRestantes === 'N/A' ? '' :
                             diasRestantes < 0 ? 'text-danger' :
                             diasRestantes <= (sensor.group.dias_vencimiento ?? 5) ? 'text-warning' :
                             'text-success';

            // ✅ Debug: Mostrar estado de marcado en consola
            console.log(`Sensor ${sensor.id} - ${sensor.name}: marcado_para_medicion = ${sensor.marcado_para_medicion}`);

            html += `
                <tr data-sensor-id="${sensor.id}">
                    <td><input type="checkbox" class="sensorCheckbox" ${sensor.marcado_para_medicion ? 'checked' : ''}></td>
                    <td>${sensor.id}</td>
                    <td>${sensor.name}</td>
                    <td>${sensor.identifier}</td>
                    <td>${sensor.group.name}</td>
                    <td>${sensor.last_measurement_value ?? 'N/A'} (${lastMeasurementDate})</td>
                    <td>${proximaMedicionDate}</td>
                    <td class="${diasClass}">${diasRestantes}</td>
                    <td><span class="badge bg-${estadoClass}">${estadoText}</span></td>
                    <td class="text-nowrap">
                        <a href="/mediciones/create/${sensor.id}" class="btn btn-sm btn-success" title="Tomar Medición">
                            <i class="bi bi-rulers"></i>
                        </a>
                        
                    </td>
                </tr>
            `;
        });

        $('#sensorsTableBody').html(html);

        // ✅ Eventos para checkboxes - VERSIÓN CORREGIDA
        $('.sensorCheckbox').off('change').on('change', function() {
            const sensorId = $(this).closest('tr').data('sensor-id');
            const isChecked = $(this).is(':checked');
            
            console.log(`🔄 Checkbox cambiado: Sensor ${sensorId}, marcado: ${isChecked}`);
            
            // ✅ Llamar al API para marcar/desmarcar el sensor
            toggleMarkSensor(sensorId, isChecked);
        });

        // Eventos para botones de marcar/desmarcar
        $('.toggleMarkBtn').off('click').on('click', function() {
            const sensorId = $(this).closest('tr').data('sensor-id');
            const currentState = $(this).find('i').hasClass('bi-check-square');
            const newState = !currentState;
            
            console.log(`🔄 Botón toggle: Sensor ${sensorId}, nuevo estado: ${newState}`);
            toggleMarkSensor(sensorId, newState);
        });

        // Evento para seleccionar todos
        $('#selectAllSensors').off('change').on('change', function() {
            const isChecked = $(this).is(':checked');
            console.log(`🔄 Seleccionar todos: ${isChecked}`);
            
            $('.sensorCheckbox').prop('checked', isChecked);
            $('.sensorCheckbox').each(function() {
                const sensorId = $(this).closest('tr').data('sensor-id');
                toggleMarkSensor(sensorId, isChecked);
            });
        });
    }

    function getEstadoClass(estado) {
        const classes = { 'al_dia': 'success', 'pendiente': 'warning', 'vencido': 'danger' };
        return classes[estado] || 'secondary';
    }

    function getEstadoText(estado) {
        const texts = { 'al_dia': 'Al Día', 'pendiente': 'Pendiente', 'vencido': 'Vencido' };
        return texts[estado] || 'Desconocido';
    }

    // =============================================
    // FUNCIONES DE ACCIÓN - CORREGIDAS
    // =============================================

    // ✅ Función para marcar/desmarcar sensor - VERSIÓN CORREGIDA
    function toggleMarkSensor(sensorId, mark) {
        console.log(`📤 toggleMarkSensor: Sensor ${sensorId}, mark: ${mark}`);
        
        $.ajax({
            url: `/api/bulk/measurements/sensors/${sensorId}/toggle-mark`,
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({ mark: mark }),
            success: function(response) {
                console.log(`✅ toggleMarkSensor éxito:`, response);
                if (response.success) {
                    // Actualizar el checkbox visualmente
                    $(`input[data-sensor-id="${sensorId}"]`).prop('checked', response.data.marcado_para_medicion);
                    
                    // Actualizar el icono del botón toggle
                    const icon = $(`button[data-sensor-id="${sensorId}"] i`);
                    if (response.data.marcado_para_medicion) {
                        icon.removeClass('bi-square').addClass('bi-check-square');
                    } else {
                        icon.removeClass('bi-check-square').addClass('bi-square');
                    }
                    
                    console.log(`✅ Sensor ${sensorId} ahora: marcado_para_medicion = ${response.data.marcado_para_medicion}`);
                } else {
                    showAlert('Error: ' + (response.message || 'No se pudo marcar el sensor'), 'danger');
                }
            },
            error: function(xhr) {
                console.error('❌ Error en toggleMarkSensor:', xhr);
                const errorMsg = xhr.responseJSON?.message || xhr.statusText;
                showAlert('Error al marcar sensor: ' + errorMsg, 'danger');
            }
        });
    }

    function showAlert(message, type = 'info') {
        $('#globalAlertContainer').prepend(`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);

        setTimeout(() => {
            $('#globalAlertContainer .alert').first().fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
    }

    // =============================================
    // EVENTOS
    // =============================================

    $('#applyFilters').on('click', function() {
        currentGroupId = $('#groupFilter').val();
        currentStatus = $('#statusFilter').val();
        currentSearch = $('#searchInput').val();
        currentPage = 1;
        loadSensors();
    });

    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) $('#applyFilters').click();
    });

    $('#refreshStats').on('click', function() {
        loadStats();
        loadSensors();
    });

    // ✅ Botón "Marcar Todos para Medición" - CORREGIDO
    $('#markAllForMeasurement').on('click', function() {
        const groupId = $('#groupFilter').val();
        const status = $('#statusFilter').val();
        const search = $('#searchInput').val();

        console.log(`📤 Marcar todos: groupId=${groupId}, status=${status}, search=${search}`);

        $.ajax({
            url: '/api/bulk/measurements/toggle-all-marked',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({
                action: 'mark',
                group_id: groupId,
                status: status,
                search: search
            }),
            success: function(response) {
                console.log('✅ Marcar todos éxito:', response);
                if (response.success) {
                    loadSensors();
                    showAlert(`Se marcaron ${response.data.affected} sensores para medición`, 'success');
                } else {
                    showAlert(response.message || 'Error al marcar sensores', 'danger');
                }
            },
            error: function(xhr) {
                console.error('❌ Error al marcar todos:', xhr);
                showAlert('Error: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
            }
        });
    });

    // ✅ Botón "Desmarcar Todos" - CORREGIDO
    $('#unmarkAllForMeasurement').off('click').on('click', function() {
        const groupId = $('#groupFilter').val();
        const status = $('#statusFilter').val();
        const search = $('#searchInput').val();

        console.log(`📤 Desmarcar todos: groupId=${groupId}, status=${status}, search=${search}`);

        currentAction = 'unmarkAll';
        $('#confirmActionMessage').html(`
            <p>¿Estás seguro de que deseas desmarcar todos los sensores filtrados?</p>
            <p class="text-muted">Se desmarcarán todos los sensores que coincidan con los filtros actuales.</p>
        `);
        $('#confirmActionModal').modal('show');
    });

    // ✅ Botón "Tomar Mediciones Masivas" - CORREGIDO
    $('#takeBulkMeasurement').on('click', function() {
        // Obtener los IDs de los sensores marcados en la base de datos
        const markedSensorIds = [];
        $('.sensorCheckbox:checked').each(function() {
            const sensorId = $(this).closest('tr').data('sensor-id');
            markedSensorIds.push(sensorId);
        });

        console.log(`📤 Tomar mediciones masivas: ${markedSensorIds.length} sensores marcados`, markedSensorIds);

        // Si no hay sensores marcados, mostrar error
        if (markedSensorIds.length === 0) {
            showAlert('No hay sensores seleccionados para medición. Marca al menos un sensor.', 'warning');
            return;
        }

        // ✅ Redirigir al primer sensor marcado en modo masivo
        const firstSensorId = markedSensorIds[0];
        console.log(`🚀 Redirigiendo a /mediciones/bulk-create/${firstSensorId}`);
        window.location.href = `/mediciones/bulk-create/${firstSensorId}`;
    });

    $('#showHelpModal').on('click', function() {
        $('#helpModal').modal('show');
    });

    $('#confirmActionButton').on('click', function() {
        $('#confirmActionModal').modal('hide');
        if (currentAction === 'unmarkAll') {
            const groupId = $('#groupFilter').val();
            const status = $('#statusFilter').val();
            const search = $('#searchInput').val();

            $.ajax({
                url: '/api/bulk/measurements/toggle-all-marked',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify({
                    action: 'unmark',
                    group_id: groupId,
                    status: status,
                    search: search
                }),
                success: function(response) {
                    console.log('✅ Desmarcar todos éxito:', response);
                    if (response.success) {
                        loadSensors();
                        showAlert(`Se desmarcaron ${response.data.affected} sensores`, 'success');
                        selectedSensors.clear();
                    } else {
                        showAlert(response.message || 'Error al desmarcar sensores', 'danger');
                    }
                },
                error: function(xhr) {
                    console.error('❌ Error al desmarcar todos:', xhr);
                    showAlert('Error: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
                }
            });
        }
        currentAction = null;
    });

    // Toggle para el sidebar en móviles
    $('#toggleHelpSidebar').click(function() {
        $('#helpSidebar').toggleClass('d-none d-lg-flex');
    });

    // Persistencia de pestañas
    $(document).on('shown.bs.tab', 'a[data-bs-toggle="pill"]', function(e) {
        localStorage.setItem('helpModalActiveTab', e.target.id);
    });

    $('#helpModal').on('shown.bs.modal', function() {
        const activeTab = localStorage.getItem('helpModalActiveTab');
        if (activeTab) $(`#${activeTab}`).tab('show');
    });

    // =============================================
    // INICIALIZACIÓN
    // =============================================
    initTooltips();
    loadGroups();
    loadSensors();
    loadHelpContent();
});
</script>

@endpush