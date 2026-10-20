@extends('layouts.modern')

@section('title', 'Mapa Interactivo de Sensores (GIS) - MedFlow')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    .gis-header-card {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #ffffff;
        border-radius: 14px;
        border: none;
    }

    /* Tarjetas interactivas de filtro rápido GIS */
    .gis-quick-filters {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(145px, 1fr));
        gap: 0.6rem;
        margin-bottom: 0.9rem;
    }

    .gis-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #64748b;
        border-radius: 10px;
        padding: 0.6rem 0.75rem;
        cursor: pointer;
        transition: all 0.18s ease;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .gis-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
    }

    .gis-stat-card.active {
        background: #f8fafc;
        box-shadow: 0 0 0 2px #2563eb, 0 4px 12px rgba(37, 99, 235, 0.15);
    }

    .gis-stat-card .stat-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        color: #475569;
        line-height: 1.15;
    }

    .gis-stat-card .stat-sub {
        font-size: 0.66rem;
        color: #94a3b8;
    }

    .gis-stat-card .stat-value {
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1;
    }

    .gis-sidebar-card {
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        height: 690px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: #ffffff;
    }

    .gis-sensor-list {
        flex: 1;
        overflow-y: auto;
        padding: 0.75rem;
    }

    .gis-sensor-item {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.7rem;
        margin-bottom: 0.6rem;
        background: #ffffff;
        transition: all 0.18s ease;
        cursor: pointer;
        overflow: hidden;
    }

    .gis-sensor-item:hover {
        border-color: #93c5fd;
        box-shadow: 0 3px 10px rgba(37, 99, 235, 0.08);
        transform: translateY(-1px);
    }

    .gis-sensor-item.active-placing {
        border: 2px solid #f59e0b;
        background: #fffbeb;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
    }

    .gis-sensor-item.active-selected {
        border: 2px solid #2563eb;
        background: #eff6ff;
    }

    .gis-card-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        width: 100%;
    }

    .gis-card-actions .btn {
        flex: 1 1 auto;
        min-width: 0;
        font-size: 0.72rem;
        padding: 0.22rem 0.35rem;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
    }

    .gis-map-wrapper {
        position: relative;
        height: 690px;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #cbd5e1;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
    }

    #sensorsMapCanvas {
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    #sensorsMapCanvas.placing-cursor {
        cursor: crosshair !important;
    }

    /* Barra flotante superior en el mapa */
    .gis-floating-toolbar {
        position: absolute;
        top: 12px;
        left: 54px;
        right: 12px;
        z-index: 500;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        justify-content: space-between;
        pointer-events: none;
    }

    .gis-floating-toolbar > * {
        pointer-events: auto;
    }

    .gis-toolbar-box {
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(8px);
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 6px 10px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
    }

    /* Leyenda flotante inferior izquierda */
    .gis-map-legend {
        position: absolute;
        bottom: 16px;
        left: 14px;
        z-index: 500;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 8px 12px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.14);
        font-size: 0.74rem;
        max-width: 260px;
    }

    .gis-legend-dot {
        width: 11px;
        height: 11px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }

    /* Banner flotante de modo asignación por clic */
    .gis-placing-banner {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 550;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #ffffff;
        padding: 12px 20px;
        border-radius: 999px;
        box-shadow: 0 8px 24px rgba(217, 119, 6, 0.45);
        display: none;
        align-items: center;
        gap: 12px;
        max-width: 92%;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .gis-placing-banner.active {
        display: flex;
        animation: pulse-placing 1.8s infinite;
    }

    @keyframes pulse-placing {
        0%, 100% { box-shadow: 0 8px 24px rgba(217, 119, 6, 0.45); }
        50% { box-shadow: 0 8px 32px rgba(245, 158, 11, 0.75); }
    }

    /* Marcador personalizado en el mapa */
    .medflow-map-pin {
        width: 34px;
        height: 34px;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.35);
        border: 2px solid #ffffff;
        transition: transform 0.15s ease;
        position: relative;
    }

    .medflow-map-pin i {
        transform: rotate(45deg);
        color: #ffffff;
        font-size: 0.95rem;
    }

    /* Colores por modo Servicio */
    .pin-agua { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
    .pin-electricidad { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .pin-gas { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .pin-personalizado { background: linear-gradient(135deg, #6366f1, #4f46e5); }

    /* Colores por modo Estado Operativo (Tomas y Tolerancias) */
    .pin-op-completed { background: linear-gradient(135deg, #22c55e, #15803d); }
    .pin-op-scheduled { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .pin-op-in_window { background: linear-gradient(135deg, #eab308, #ca8a04); }
    .pin-op-in_tolerance { background: linear-gradient(135deg, #f97316, #c2410c); }
    .pin-op-overdue {
        background: linear-gradient(135deg, #ef4444, #b91c1c);
        animation: pin-alert-pulse 1.6s infinite;
    }

    /* Colores por modo Auditoría de Consumo */
    .pin-cons-peak {
        background: linear-gradient(135deg, #e11d48, #881337);
        animation: pin-alert-pulse 1.5s infinite;
    }
    .pin-cons-stagnant {
        background: linear-gradient(135deg, #06b6d4, #0e7490);
    }
    .pin-cons-normal {
        background: linear-gradient(135deg, #10b981, #047857);
    }
    .pin-cons-no_data {
        background: linear-gradient(135deg, #94a3b8, #475569);
    }

    @keyframes pin-alert-pulse {
        0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.75), 0 3px 10px rgba(0, 0, 0, 0.35); }
        70% { box-shadow: 0 0 0 12px rgba(239, 68, 68, 0), 0 3px 10px rgba(0, 0, 0, 0.35); }
        100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0), 0 3px 10px rgba(0, 0, 0, 0.35); }
    }

    .leaflet-popup-content-wrapper {
        border-radius: 12px;
        padding: 2px;
    }

    @media (max-width: 991.98px) {
        .gis-sidebar-card {
            height: 440px;
            margin-bottom: 1rem;
        }
        .gis-map-wrapper {
            height: 540px;
        }
        .gis-floating-toolbar {
            left: 12px;
            top: 56px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">

    {{-- ENCABEZADO DEL MÓDULO PREMIUM GIS + SELECTOR DE CAPA DE INTELIGENCIA --}}
    <div class="card gis-header-card shadow-sm mb-3">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-warning text-dark fw-bold px-2 py-1">
                            <i class="bi bi-star-fill me-1"></i> APP PREMIUM
                        </span>
                        <span class="text-white-50 small">Centro de Inteligencia Geográfica y Telemetría</span>
                    </div>
                    <h4 class="mb-0 fw-bold text-white">
                        <i class="bi bi-geo-alt-fill text-info me-1"></i> Mapa GIS de Sensores, Tomas y Consumos
                    </h4>
                </div>

                {{-- Selector de Modo de Coloración GIS --}}
                <div class="bg-dark bg-opacity-50 p-2 rounded-3 border border-secondary border-opacity-50">
                    <div class="text-white-50 mb-1" style="font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        <i class="bi bi-layers-fill text-info me-1"></i> Colorear Pines del Mapa según:
                    </div>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Modo de visualización GIS">
                        <input type="radio" class="btn-check" name="gisColorMode" id="modeOperational" value="operational" checked>
                        <label class="btn btn-outline-light fw-semibold" for="modeOperational">
                            <i class="bi bi-calendar2-check me-1 text-warning"></i> Estado de Tomas
                        </label>

                        <input type="radio" class="btn-check" name="gisColorMode" id="modeConsumption" value="consumption">
                        <label class="btn btn-outline-light fw-semibold" for="modeConsumption">
                            <i class="bi bi-graph-up-arrow me-1 text-danger"></i> Picos y Consumos
                        </label>

                        <input type="radio" class="btn-check" name="gisColorMode" id="modeService" value="service">
                        <label class="btn btn-outline-light fw-semibold" for="modeService">
                            <i class="bi bi-droplet-half me-1 text-info"></i> Tipo de Servicio
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TARJETAS INTERACTIVAS DE DIAGNÓSTICO GIS (CLIC PARA FILTRAR DE UN PANTALLAZO) --}}
    <div class="gis-quick-filters" id="gisQuickFiltersBar">
        <div class="gis-stat-card active" data-quick-filter="all" style="border-left-color: #3b82f6;">
            <div>
                <div class="stat-label">Todos los Sensores</div>
                <div class="stat-sub"><span id="kpiLocatedSub">{{ $stats['located'] }}</span> en mapa (<span id="kpiCoverageSub">{{ $stats['coverage_percent'] }}%</span>)</div>
            </div>
            <div class="stat-value text-primary" id="kpiTotalSensors">{{ $stats['total'] }}</div>
        </div>

        <div class="gis-stat-card" data-quick-filter="op_completed" style="border-left-color: #16a34a;">
            <div>
                <div class="stat-label">Medidos / Al Día</div>
                <div class="stat-sub">Ciclo actual cumplido</div>
            </div>
            <div class="stat-value text-success" id="kpiOpCompleted">{{ $stats['op']['completed'] ?? 0 }}</div>
        </div>

        <div class="gis-stat-card" data-quick-filter="op_pending" style="border-left-color: #f59e0b;">
            <div>
                <div class="stat-label">Faltan Medir Hoy</div>
                <div class="stat-sub">En ventana o tolerancia</div>
            </div>
            <div class="stat-value text-warning" id="kpiOpPending">{{ $stats['pending_window'] ?? 0 }}</div>
        </div>

        <div class="gis-stat-card" data-quick-filter="op_overdue" style="border-left-color: #dc2626;">
            <div>
                <div class="stat-label">Atrasados / Vencidos</div>
                <div class="stat-sub">Plazo y tolerancia vencidos</div>
            </div>
            <div class="stat-value text-danger" id="kpiOpOverdue">{{ $stats['op']['overdue'] ?? 0 }}</div>
        </div>

        <div class="gis-stat-card" data-quick-filter="cons_peak" style="border-left-color: #e11d48;">
            <div>
                <div class="stat-label">Picos de Consumo</div>
                <div class="stat-sub">Salto &ge; +50% ritmo diario</div>
            </div>
            <div class="stat-value" style="color: #e11d48;" id="kpiConsPeak">{{ $stats['consumption']['peak'] ?? 0 }}</div>
        </div>

        <div class="gis-stat-card" data-quick-filter="cons_stagnant" style="border-left-color: #0891b2;">
            <div>
                <div class="stat-label">Estancados (0 Cons.)</div>
                <div class="stat-sub">Posible medidor trabado</div>
            </div>
            <div class="stat-value text-info" id="kpiConsStagnant">{{ $stats['consumption']['stagnant'] ?? 0 }}</div>
        </div>

        <div class="gis-stat-card" data-quick-filter="unlocated" style="border-left-color: #64748b;">
            <div>
                <div class="stat-label">Sin Coordenada</div>
                <div class="stat-sub">Pendientes de ubicar</div>
            </div>
            <div class="stat-value text-secondary" id="kpiUnlocatedSensors">{{ $stats['unlocated'] }}</div>
        </div>
    </div>

    <div class="row g-3">
        {{-- PANEL LATERAL IZQUIERDO: FILTROS Y LISTADO DE SENSORES --}}
        <div class="col-lg-4 col-xl-3">
            <div class="gis-sidebar-card shadow-sm">
                <div class="p-3 border-bottom bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-funnel me-1 text-primary"></i> Explorador GIS
                        </h6>
                        <span class="badge bg-primary rounded-pill" id="filteredCountBadge">{{ $stats['total'] }}</span>
                    </div>

                    {{-- Filtro por Grupo --}}
                    <div class="mb-2">
                        <select id="filterGroup" class="form-select form-select-sm">
                            <option value="all">📁 Todos los grupos ({{ $groups->count() }})</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}">
                                    {{ $group->name }} ({{ $group->sensors_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Selector combinado de Diagnóstico / Estado --}}
                    <div class="mb-2">
                        <select id="filterDiagnostic" class="form-select form-select-sm">
                            <option value="all">🔍 Diagnóstico: Mostrar todos</option>
                            <optgroup label="Estado de Toma (Calendario)">
                                <option value="op_completed">🟢 Medidos / Al día</option>
                                <option value="op_pending">🟡 Faltan medir (En ventana / Tolerancia)</option>
                                <option value="op_in_window">📅 ¡Hoy toca medir! / En ventana</option>
                                <option value="op_in_tolerance">🟠 En margen de tolerancia</option>
                                <option value="op_overdue">🔴 Atrasados / Vencidos</option>
                                <option value="op_scheduled">🔵 Programados a futuro</option>
                            </optgroup>
                            <optgroup label="Análisis Avanzado de Consumo">
                                <option value="cons_peak">🔥 Con Pico de Consumo (&ge; +50%)</option>
                                <option value="cons_stagnant">🧊 Medidores Estancados (Consumo 0)</option>
                                <option value="cons_normal">✅ Consumo Normal / Estable</option>
                                <option value="cons_no_data">⚪ Sin historial suficiente</option>
                            </optgroup>
                            <optgroup label="Georreferenciación">
                                <option value="located">📍 Ubicados en el mapa</option>
                                <option value="unlocated">⚠️ Sin ubicar en el mapa</option>
                            </optgroup>
                        </select>
                    </div>

                    {{-- Buscador por texto --}}
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="filterSearch" class="form-control" placeholder="Buscar sensor o #código...">
                        <button class="btn btn-outline-secondary" type="button" id="btnClearSearch" title="Limpiar filtros">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>

                {{-- Lista scrolleable de sensores --}}
                <div class="gis-sensor-list" id="sensorListContainer">
                    {{-- Renderizado dinámicamente por JS --}}
                </div>
            </div>
        </div>

        {{-- PANEL DERECHO: MAPA INTERACTIVO LEAFLET --}}
        <div class="col-lg-8 col-xl-9">
            <div class="gis-map-wrapper">
                {{-- Barra de herramientas flotante sobre el mapa --}}
                <div class="gis-floating-toolbar">
                    {{-- Buscador de calles / localidades (Geocoder Nominatim) --}}
                    <div class="gis-toolbar-box d-flex align-items-center gap-1" style="min-width: 250px; max-width: 360px; flex: 1;">
                        <i class="bi bi-compass text-primary ms-1"></i>
                        <input type="text" id="addressSearchInput" class="form-control form-control-sm border-0 shadow-none"
                               placeholder="Ir a calle, barrio o ciudad (Ej: Tigre, Buenos Aires)...">
                        <button type="button" class="btn btn-primary btn-sm px-2" id="btnSearchAddress" title="Buscar en el mapa">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>

                    {{-- Controles rápidos del mapa --}}
                    <div class="gis-toolbar-box d-flex flex-wrap align-items-center gap-2">
                        <div class="form-check form-switch mb-0 me-1" title="Permite mover los marcadores arrastrándolos con el mouse">
                            <input class="form-check-input" type="checkbox" id="toggleDragMarkers" checked>
                            <label class="form-check-label small fw-semibold text-dark" for="toggleDragMarkers">
                                <i class="bi bi-arrows-move text-primary"></i> Arrastrar pines
                            </label>
                        </div>

                        <div class="vr"></div>

                        <button type="button" class="btn btn-outline-dark btn-sm" id="btnToggleMapLayer" title="Alternar entre Mapa Callejero y Vista Satelital">
                            <i class="bi bi-globe-americas me-1"></i> <span id="mapLayerLabel">Satélite</span>
                        </button>

                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnFitAllMarkers" title="Centrar mapa en los sensores filtrados">
                            <i class="bi bi-fullscreen me-1"></i> Encuadrar
                        </button>

                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnMyGpsLocation" title="Centrar en mi ubicación actual (GPS)">
                            <i class="bi bi-crosshair"></i>
                        </button>
                    </div>
                </div>

                {{-- Leyenda dinámica flotante (Esquina inferior izquierda) --}}
                <div class="gis-map-legend" id="gisMapLegend">
                    {{-- Renderizado dinámicamente según el modo GIS activo --}}
                </div>

                {{-- Banner flotante cuando se está asignando coordenada con clic --}}
                <div class="gis-placing-banner" id="placingModeBanner">
                    <i class="bi bi-geo-fill fs-5"></i>
                    <div>
                        <span>Ubicando sensor: <strong id="placingSensorName">-</strong></span>
                        <div class="small fw-normal opacity-90">Haz clic en el punto exacto del mapa donde se encuentra el medidor</div>
                    </div>
                    <button type="button" class="btn btn-light btn-sm rounded-pill fw-bold px-3 ms-2" id="btnCancelPlacing">
                        Cancelar (Esc)
                    </button>
                </div>

                {{-- Lienzo del Mapa --}}
                <div id="sensorsMapCanvas"></div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DE TELEMETRÍA Y CURVA DE CONSUMO DEL SENSOR (ANÁLISIS RÁPIDO EN MAPA) --}}
<div class="modal fade" id="sensorTelemetryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <div>
                    <h5 class="modal-title mb-0 fw-bold" id="telemetryModalTitle">
                        <i class="bi bi-graph-up-arrow text-info me-2"></i>Telemetría del Sensor
                    </h5>
                    <small class="text-white-50" id="telemetryModalSubtitle">-</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                {{-- Fila de KPIs del Sensor --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-3 col-6">
                        <div class="p-2 rounded border bg-light h-100">
                            <div class="text-muted" style="font-size: 0.7rem; text-transform: uppercase;">Estado de Toma</div>
                            <div class="fw-bold mt-1" id="telemetryOpBadge">-</div>
                            <div class="text-muted mt-1" style="font-size: 0.72rem;" id="telemetryOpTarget">-</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-2 rounded border bg-light h-100">
                            <div class="text-muted" style="font-size: 0.7rem; text-transform: uppercase;">Última Lectura</div>
                            <div class="fs-5 fw-bold text-dark mt-1" id="telemetryLastValue">-</div>
                            <div class="text-muted" style="font-size: 0.72rem;" id="telemetryLastDate">-</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-2 rounded border bg-light h-100">
                            <div class="text-muted" style="font-size: 0.7rem; text-transform: uppercase;">Último Consumo (&Delta;)</div>
                            <div class="fs-5 fw-bold text-primary mt-1" id="telemetryLastDelta">-</div>
                            <div class="text-muted" style="font-size: 0.72rem;" id="telemetryDailyRate">-</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-2 rounded border bg-light h-100">
                            <div class="text-muted" style="font-size: 0.7rem; text-transform: uppercase;">Diagnóstico Consumo</div>
                            <div class="fw-bold mt-1" id="telemetryConsBadge">-</div>
                            <div class="text-muted mt-1" style="font-size: 0.72rem;" id="telemetryConsVariation">-</div>
                        </div>
                    </div>
                </div>

                {{-- Alerta explicativa del estado operativo --}}
                <div class="alert alert-secondary py-2 px-3 small mb-3" id="telemetryOpMessage">-</div>

                {{-- Gráfico de Evolución de Lecturas y Consumo --}}
                <div class="card border shadow-none mb-3">
                    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark"><i class="bi bi-activity text-primary me-1"></i> Curva de Evolución (Últimas Lecturas)</span>
                        <span class="badge bg-light text-secondary border" id="telemetryUnitBadge">-</span>
                    </div>
                    <div class="card-body p-2" style="height: 230px;">
                        <canvas id="telemetryChartCanvas"></canvas>
                    </div>
                </div>

                {{-- Tabla de últimas lecturas --}}
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha de Toma</th>
                                <th class="text-end">Lectura</th>
                                <th class="text-end">Consumo (&Delta;)</th>
                                <th class="text-end">Ritmo Diario</th>
                            </tr>
                        </thead>
                        <tbody id="telemetryHistoryTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <a href="/consumptions" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-bar-chart-line me-1"></i> Ir a Análisis Avanzado de Consumos
                </a>
                <div class="d-flex gap-2">
                    <a href="#" id="telemetryMeasureBtn" class="btn btn-success btn-sm text-white">
                        <i class="bi bi-rulers me-1"></i> Tomar Medición
                    </a>
                    <a href="#" id="telemetryShowBtn" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-eye me-1"></i> Ficha del Sensor
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PARA EDITAR / PEGAR COORDENADAS MANUALMENTE --}}
<div class="modal fade" id="manualCoordsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    <i class="bi bi-pin-map-fill text-info me-2"></i>Coordenadas del Sensor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalSensorId">
                <div class="mb-3 p-2 bg-light rounded border">
                    <div class="fw-bold text-dark" id="modalSensorTitle">-</div>
                    <div class="small text-muted" id="modalSensorMeta">-</div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-primary">
                        <i class="bi bi-clipboard-check me-1"></i> Pegado rápido desde Google Maps (Opcional)
                    </label>
                    <input type="text" id="quickPasteCoords" class="form-control form-control-sm"
                           placeholder="Ej: -34.458912, -58.632145">
                    <div class="form-text">Si pegas el par "latitud, longitud" copiado de Google Maps, se separará automáticamente abajo.</div>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Latitud (-90 a 90)</label>
                        <input type="number" step="any" id="modalLatInput" class="form-control" placeholder="-34.603722" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Longitud (-180 a 180)</label>
                        <input type="number" step="any" id="modalLngInput" class="form-control" placeholder="-58.381592" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-danger btn-sm d-none" id="btnModalRemoveCoords">
                    <i class="bi bi-trash me-1"></i> Quitar del mapa
                </button>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSaveManualCoords">
                        <i class="bi bi-check2-circle me-1"></i> Guardar Coordenadas
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Contenedor de notificaciones Toast --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;" id="gisToastContainer"></div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Estado en memoria de todos los sensores con su inteligencia operativa y de consumos
    let sensors = @json($sensorsData);
    let markersById = {};
    let placingSensorId = null;
    let selectedSensorId = null;
    let isSatellite = false;
    let currentColorMode = 'operational'; // 'operational' | 'consumption' | 'service'
    let telemetryChartInstance = null;

    const defaultCenter = [-34.6037, -58.3816];
    const defaultZoom = 12;

    const streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    });

    const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 19,
        attribution: 'Tiles &copy; Esri'
    });

    const map = L.map('sensorsMapCanvas', {
        center: defaultCenter,
        zoom: defaultZoom,
        layers: [streetLayer],
        zoomControl: true
    });

    function notifyToast(message, type = 'success') {
        const bgClass = type === 'success' ? 'bg-success' : (type === 'warning' ? 'bg-warning text-dark' : 'bg-danger');
        const iconClass = type === 'success' ? 'bi-check-circle-fill' : (type === 'warning' ? 'bi-exclamation-triangle-fill' : 'bi-x-circle-fill');
        const container = document.getElementById('gisToastContainer');

        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-white ${bgClass} border-0 show mb-2`;
        toastEl.setAttribute('role', 'alert');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body fw-semibold">
                    <i class="bi ${iconClass} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        container.appendChild(toastEl);

        setTimeout(() => {
            toastEl.remove();
        }, 3500);
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Ícono dinámico según el Modo GIS seleccionado (Estado Operativo, Consumo/Picos, o Tipo de Servicio)
    function createSensorIcon(sensor) {
        let pinClass = 'pin-personalizado';
        let iconName = 'bi-speedometer2';

        const type = (sensor.template_type || '').toLowerCase();
        if (type.includes('agua')) {
            iconName = 'bi-droplet-fill';
        } else if (type.includes('electr') || type.includes('luz')) {
            iconName = 'bi-lightning-charge-fill';
        } else if (type.includes('gas')) {
            iconName = 'bi-fire';
        }

        if (currentColorMode === 'operational') {
            pinClass = `pin-op-${sensor.op_status || 'scheduled'}`;
            if (sensor.op_status === 'overdue') {
                iconName = 'bi-exclamation-triangle-fill';
            } else if (sensor.op_status === 'completed') {
                iconName = 'bi-check-lg';
            } else if (sensor.op_status === 'in_window' || sensor.op_status === 'in_tolerance') {
                iconName = 'bi-clock-history';
            }
        } else if (currentColorMode === 'consumption') {
            pinClass = `pin-cons-${sensor.consumption_status || 'no_data'}`;
            if (sensor.consumption_status === 'peak') {
                iconName = 'bi-graph-up-arrow';
            } else if (sensor.consumption_status === 'stagnant') {
                iconName = 'bi-snow';
            } else if (sensor.consumption_status === 'normal') {
                iconName = 'bi-check2-circle';
            }
        } else {
            if (type.includes('agua')) {
                pinClass = 'pin-agua';
            } else if (type.includes('electr') || type.includes('luz')) {
                pinClass = 'pin-electricidad';
            } else if (type.includes('gas')) {
                pinClass = 'pin-gas';
            }
        }

        return L.divIcon({
            className: 'custom-sensor-div-icon',
            html: `<div class="medflow-map-pin ${pinClass}" title="${escapeHtml(sensor.name)}"><i class="bi ${iconName}"></i></div>`,
            iconSize: [34, 34],
            iconAnchor: [17, 34],
            popupAnchor: [0, -32]
        });
    }

    // Helpers de badges de Estado Operativo y de Consumo
    function getOperationalBadgeHtml(sensor) {
        const mapBadge = {
            'completed': { cls: 'bg-success-subtle text-success border-success-subtle', icon: 'bi-check-circle-fill' },
            'in_window': { cls: 'bg-warning-subtle text-warning-emphasis border-warning-subtle', icon: 'bi-alarm-fill' },
            'in_tolerance': { cls: 'bg-warning text-dark border-warning', icon: 'bi-exclamation-circle-fill' },
            'overdue': { cls: 'bg-danger text-white border-danger', icon: 'bi-exclamation-octagon-fill' },
            'scheduled': { cls: 'bg-primary-subtle text-primary border-primary-subtle', icon: 'bi-calendar-event' }
        };
        const cfg = mapBadge[sensor.op_status] || mapBadge['scheduled'];
        return `<span class="badge ${cfg.cls} border" style="font-size: 0.67rem;"><i class="bi ${cfg.icon} me-1"></i>${escapeHtml(sensor.op_label)}</span>`;
    }

    function getConsumptionBadgeHtml(sensor) {
        if (sensor.consumption_status === 'peak') {
            return `<span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.67rem;"><i class="bi bi-fire me-1"></i>${escapeHtml(sensor.consumption_label)}</span>`;
        }
        if (sensor.consumption_status === 'stagnant') {
            return `<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size: 0.67rem;"><i class="bi bi-snow me-1"></i>${escapeHtml(sensor.consumption_label)}</span>`;
        }
        if (sensor.consumption_status === 'normal') {
            return `<span class="badge bg-emerald-subtle text-success border border-success-subtle" style="font-size: 0.67rem;"><i class="bi bi-activity me-1"></i>Ritmo: ${sensor.daily_rate ?? 0} ${escapeHtml(sensor.unit)}/d</span>`;
        }
        return `<span class="badge bg-light text-muted border" style="font-size: 0.66rem;">Sin curva de consumo</span>`;
    }

    // Contenido HTML enriquecido del Popup de cada marcador en el mapa
    function buildPopupHtml(sensor) {
        const coordsText = `${Number(sensor.lat).toFixed(6)}, ${Number(sensor.lng).toFixed(6)}`;
        const gmapsUrl = `https://www.google.com/maps?q=${sensor.lat},${sensor.lng}`;

        const lastReadingHtml = sensor.last_value !== null
            ? `<div class="d-flex justify-content-between small mt-1">
                   <span class="text-muted">Última lectura:</span>
                   <strong class="text-dark">${escapeHtml(sensor.last_value)} ${escapeHtml(sensor.unit)} <span class="text-muted fw-normal">(${escapeHtml(sensor.last_measured_at)})</span></strong>
               </div>`
            : `<div class="small text-muted fst-italic mt-1">Sin mediciones registradas</div>`;

        const deltaHtml = sensor.last_delta !== null
            ? `<div class="d-flex justify-content-between small mt-1">
                   <span class="text-muted">Último consumo (&Delta;):</span>
                   <strong class="text-primary">+${sensor.last_delta} ${escapeHtml(sensor.unit)} (${sensor.daily_rate} ${escapeHtml(sensor.unit)}/día)</strong>
               </div>`
            : '';

        return `
            <div style="min-width: 265px; padding: 4px;">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                    <h6 class="fw-bold mb-0 text-dark">${escapeHtml(sensor.name)}</h6>
                    <span class="badge bg-light text-dark border">#${escapeHtml(sensor.identifier)}</span>
                </div>
                <div class="small text-primary fw-semibold mb-2">
                    <i class="bi bi-folder2-open me-1"></i>${escapeHtml(sensor.group_name)}
                </div>

                <div class="d-flex flex-wrap gap-1 mb-2">
                    ${getOperationalBadgeHtml(sensor)}
                    ${getConsumptionBadgeHtml(sensor)}
                </div>

                <div class="bg-light rounded p-2 border mb-2">
                    ${lastReadingHtml}
                    ${deltaHtml}
                    ${sensor.target_date ? `<div class="d-flex justify-content-between small mt-1"><span class="text-muted">Meta ciclo:</span><span class="fw-semibold">${escapeHtml(sensor.target_date)}</span></div>` : ''}
                </div>

                <div class="d-flex align-items-center justify-content-between bg-light rounded p-1 px-2 mb-2 border">
                    <code class="small text-dark">${coordsText}</code>
                    <a href="${gmapsUrl}" target="_blank" rel="noopener" class="btn btn-link btn-sm p-0 text-decoration-none" title="Abrir en Google Maps">
                        <i class="bi bi-box-arrow-up-right"></i> Maps
                    </a>
                </div>

                <div class="d-flex flex-wrap gap-1">
                    <button type="button" class="btn btn-primary btn-sm flex-fill" onclick="window.gisOpenTelemetryModal(${sensor.id})">
                        <i class="bi bi-graph-up-arrow"></i> Telemetría
                    </button>
                    <a href="${sensor.measure_url}" class="btn btn-success btn-sm flex-fill text-white">
                        <i class="bi bi-rulers"></i> Medir
                    </a>
                    <button type="button" class="btn btn-outline-warning text-dark btn-sm" onclick="window.gisStartPlacing(${sensor.id})" title="Reubicar con clic">
                        <i class="bi bi-cursor-fill"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.gisOpenManualModal(${sensor.id})" title="Editar coordenadas">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <a href="${sensor.show_url}" class="btn btn-outline-dark btn-sm" title="Ver detalles del sensor">
                        <i class="bi bi-eye"></i>
                    </a>
                </div>
            </div>
        `;
    }

    // Renderizar leyenda flotante según el modo GIS activo
    function renderMapLegend() {
        const legendEl = document.getElementById('gisMapLegend');
        if (currentColorMode === 'operational') {
            legendEl.innerHTML = `
                <div class="fw-bold text-dark mb-1"><i class="bi bi-calendar2-check text-primary me-1"></i> Leyenda: Estado de Tomas</div>
                <div class="d-flex align-items-center gap-2 mb-1"><span class="gis-legend-dot" style="background:#16a34a;"></span><span>Cumplido / Al día</span></div>
                <div class="d-flex align-items-center gap-2 mb-1"><span class="gis-legend-dot" style="background:#eab308;"></span><span>En ventana / ¡Hoy toca medir!</span></div>
                <div class="d-flex align-items-center gap-2 mb-1"><span class="gis-legend-dot" style="background:#f97316;"></span><span>En margen de tolerancia</span></div>
                <div class="d-flex align-items-center gap-2 mb-1"><span class="gis-legend-dot" style="background:#dc2626;"></span><span>Atrasado / Vencido (Pulsa)</span></div>
                <div class="d-flex align-items-center gap-2"><span class="gis-legend-dot" style="background:#2563eb;"></span><span>Programado a futuro</span></div>
            `;
        } else if (currentColorMode === 'consumption') {
            legendEl.innerHTML = `
                <div class="fw-bold text-dark mb-1"><i class="bi bi-graph-up-arrow text-danger me-1"></i> Leyenda: Consumos y Picos</div>
                <div class="d-flex align-items-center gap-2 mb-1"><span class="gis-legend-dot" style="background:#e11d48;"></span><span>Pico de Consumo (&ge; +50%)</span></div>
                <div class="d-flex align-items-center gap-2 mb-1"><span class="gis-legend-dot" style="background:#0891b2;"></span><span>Medidor Estancado (0 cons.)</span></div>
                <div class="d-flex align-items-center gap-2 mb-1"><span class="gis-legend-dot" style="background:#059669;"></span><span>Consumo Normal / Estable</span></div>
                <div class="d-flex align-items-center gap-2"><span class="gis-legend-dot" style="background:#64748b;"></span><span>Sin historial suficiente</span></div>
            `;
        } else {
            legendEl.innerHTML = `
                <div class="fw-bold text-dark mb-1"><i class="bi bi-droplet-half text-info me-1"></i> Leyenda: Tipo de Servicio</div>
                <div class="d-flex align-items-center gap-2 mb-1"><span class="gis-legend-dot" style="background:#0284c7;"></span><span>Agua</span></div>
                <div class="d-flex align-items-center gap-2 mb-1"><span class="gis-legend-dot" style="background:#d97706;"></span><span>Electricidad</span></div>
                <div class="d-flex align-items-center gap-2 mb-1"><span class="gis-legend-dot" style="background:#dc2626;"></span><span>Gas</span></div>
                <div class="d-flex align-items-center gap-2"><span class="gis-legend-dot" style="background:#4f46e5;"></span><span>Personalizado</span></div>
            `;
        }
    }

    // Crear o actualizar el marcador de un sensor en el mapa
    function upsertMarker(sensor) {
        if (!sensor.has_coords || sensor.lat === null || sensor.lng === null) {
            if (markersById[sensor.id]) {
                map.removeLayer(markersById[sensor.id]);
                delete markersById[sensor.id];
            }
            return;
        }

        const draggable = document.getElementById('toggleDragMarkers').checked;

        if (markersById[sensor.id]) {
            const marker = markersById[sensor.id];
            marker.setLatLng([sensor.lat, sensor.lng]);
            marker.setIcon(createSensorIcon(sensor));
            marker.setPopupContent(buildPopupHtml(sensor));
            if (draggable) {
                marker.dragging.enable();
            } else {
                marker.dragging.disable();
            }
        } else {
            const marker = L.marker([sensor.lat, sensor.lng], {
                icon: createSensorIcon(sensor),
                draggable: draggable
            }).addTo(map);

            marker.bindPopup(buildPopupHtml(sensor));

            marker.on('click', function () {
                selectedSensorId = sensor.id;
                highlightSelectedInSidebar(sensor.id);
            });

            marker.on('dragend', function (e) {
                const newPos = e.target.getLatLng();
                saveSensorCoordinates(sensor.id, newPos.lat, newPos.lng, false);
            });

            markersById[sensor.id] = marker;
        }
    }

    // Sincronizar todos los marcadores según el filtro activo
    function syncMapMarkers(filteredSensors) {
        const visibleIds = new Set(filteredSensors.filter(s => s.has_coords).map(s => s.id));

        Object.keys(markersById).forEach(id => {
            if (!visibleIds.has(Number(id))) {
                map.removeLayer(markersById[id]);
                delete markersById[id];
            }
        });

        filteredSensors.forEach(sensor => {
            if (sensor.has_coords) {
                upsertMarker(sensor);
            }
        });
    }

    function fitAllVisibleMarkers() {
        const markerList = Object.values(markersById);
        if (markerList.length === 0) return;

        const group = L.featureGroup(markerList);
        map.fitBounds(group.getBounds(), { padding: [50, 50], maxZoom: 17 });
    }

    // Recalcular KPIs según el grupo seleccionado (para que el admin vea métricas globales o por grupo)
    function refreshStats() {
        const groupVal = document.getElementById('filterGroup').value;
        const baseList = groupVal === 'all'
            ? sensors
            : sensors.filter(s => String(s.group_id) === String(groupVal));

        const total = baseList.length;
        const located = baseList.filter(s => s.has_coords).length;
        const unlocated = Math.max(0, total - located);
        const coverage = total > 0 ? Math.round((located / total) * 100) : 0;

        const opCompleted = baseList.filter(s => s.op_status === 'completed').length;
        const opPending = baseList.filter(s => s.op_status === 'in_window' || s.op_status === 'in_tolerance').length;
        const opOverdue = baseList.filter(s => s.op_status === 'overdue').length;
        const consPeak = baseList.filter(s => s.consumption_status === 'peak').length;
        const consStagnant = baseList.filter(s => s.consumption_status === 'stagnant').length;

        document.getElementById('kpiTotalSensors').textContent = total;
        document.getElementById('kpiLocatedSub').textContent = located;
        document.getElementById('kpiCoverageSub').textContent = coverage + '%';
        document.getElementById('kpiOpCompleted').textContent = opCompleted;
        document.getElementById('kpiOpPending').textContent = opPending;
        document.getElementById('kpiOpOverdue').textContent = opOverdue;
        document.getElementById('kpiConsPeak').textContent = consPeak;
        document.getElementById('kpiConsStagnant').textContent = consStagnant;
        document.getElementById('kpiUnlocatedSensors').textContent = unlocated;
    }

    // Filtrar sensores según grupo, diagnóstico y texto
    function getFilteredSensors() {
        const groupVal = document.getElementById('filterGroup').value;
        const diagVal = document.getElementById('filterDiagnostic').value;
        const query = document.getElementById('filterSearch').value.trim().toLowerCase();

        return sensors.filter(sensor => {
            if (groupVal !== 'all' && String(sensor.group_id) !== String(groupVal)) {
                return false;
            }

            if (diagVal === 'located' && !sensor.has_coords) return false;
            if (diagVal === 'unlocated' && sensor.has_coords) return false;
            if (diagVal === 'op_completed' && sensor.op_status !== 'completed') return false;
            if (diagVal === 'op_pending' && !(sensor.op_status === 'in_window' || sensor.op_status === 'in_tolerance')) return false;
            if (diagVal === 'op_in_window' && sensor.op_status !== 'in_window') return false;
            if (diagVal === 'op_in_tolerance' && sensor.op_status !== 'in_tolerance') return false;
            if (diagVal === 'op_overdue' && sensor.op_status !== 'overdue') return false;
            if (diagVal === 'op_scheduled' && sensor.op_status !== 'scheduled') return false;
            if (diagVal === 'cons_peak' && sensor.consumption_status !== 'peak') return false;
            if (diagVal === 'cons_stagnant' && sensor.consumption_status !== 'stagnant') return false;
            if (diagVal === 'cons_normal' && sensor.consumption_status !== 'normal') return false;
            if (diagVal === 'cons_no_data' && sensor.consumption_status !== 'no_data') return false;

            if (query !== '') {
                const haystack = `${sensor.name} ${sensor.identifier} ${sensor.group_name} ${sensor.description}`.toLowerCase();
                if (!haystack.includes(query)) {
                    return false;
                }
            }
            return true;
        });
    }

    // Renderizar la lista lateral de sensores
    function renderSidebarList() {
        const filtered = getFilteredSensors();
        const container = document.getElementById('sensorListContainer');
        document.getElementById('filteredCountBadge').textContent = filtered.length;

        syncMapMarkers(filtered);

        if (filtered.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted py-5 px-3">
                    <i class="bi bi-geo-alt fs-1 d-block mb-2 opacity-50"></i>
                    <p class="mb-1 fw-semibold">No hay sensores con este criterio</p>
                    <small>Prueba seleccionando "Todos los Sensores" arriba.</small>
                </div>
            `;
            return;
        }

        let html = '';
        filtered.forEach(sensor => {
            const isPlacing = placingSensorId === sensor.id;
            const isSelected = selectedSensorId === sensor.id;
            const activeClass = isPlacing ? 'active-placing' : (isSelected ? 'active-selected' : '');

            const statusBadge = sensor.has_coords
                ? `<span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.65rem;"><i class="bi bi-geo-alt-fill me-1"></i>En mapa</span>`
                : `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:0.65rem;"><i class="bi bi-exclamation-circle me-1"></i>Sin ubicar</span>`;

            const primaryActionBtn = sensor.has_coords
                ? `<button type="button" class="btn btn-outline-primary btn-sm" onclick="event.stopPropagation(); window.gisFocusSensor(${sensor.id})" title="Centrar en el mapa">
                       <i class="bi bi-crosshair"></i> Ver
                   </button>
                   <button type="button" class="btn btn-outline-info text-dark btn-sm" onclick="event.stopPropagation(); window.gisOpenTelemetryModal(${sensor.id})" title="Ver curva y telemetría de consumo">
                       <i class="bi bi-graph-up-arrow"></i> Datos
                   </button>
                   <button type="button" class="btn btn-outline-warning text-dark btn-sm" onclick="event.stopPropagation(); window.gisStartPlacing(${sensor.id})" title="Hacer clic en el mapa para moverlo">
                       <i class="bi bi-cursor-fill"></i> Mover
                   </button>`
                : `<button type="button" class="btn btn-warning btn-sm fw-semibold text-dark" onclick="event.stopPropagation(); window.gisStartPlacing(${sensor.id})">
                       <i class="bi bi-geo-fill"></i> Ubicar
                   </button>
                   <button type="button" class="btn btn-outline-info text-dark btn-sm" onclick="event.stopPropagation(); window.gisOpenTelemetryModal(${sensor.id})" title="Ver curva y telemetría de consumo">
                       <i class="bi bi-graph-up-arrow"></i> Datos
                   </button>`;

            html += `
                <div class="gis-sensor-item ${activeClass}" id="sidebar-sensor-${sensor.id}" onclick="window.gisHandleCardClick(${sensor.id})">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="fw-bold text-dark text-truncate flex-grow-1" style="min-width: 0;" title="${escapeHtml(sensor.name)}">
                            ${escapeHtml(sensor.name)}
                        </div>
                        <div class="flex-shrink-0">${statusBadge}</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2 small text-muted mt-1">
                        <span class="text-truncate flex-grow-1" style="min-width: 0;" title="${escapeHtml(sensor.group_name)}">
                            <i class="bi bi-folder2 me-1"></i>${escapeHtml(sensor.group_name)}
                        </span>
                        <span class="badge bg-light text-secondary border flex-shrink-0">#${escapeHtml(sensor.identifier)}</span>
                    </div>
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        ${getOperationalBadgeHtml(sensor)}
                        ${getConsumptionBadgeHtml(sensor)}
                    </div>
                    <div class="gis-card-actions mt-2 pt-2 border-top">
                        ${primaryActionBtn}
                        <button type="button" class="btn btn-light btn-sm border" onclick="event.stopPropagation(); window.gisOpenManualModal(${sensor.id})" title="Editar latitud/longitud manualmente">
                            <i class="bi bi-pencil-square"></i> Manual
                        </button>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function highlightSelectedInSidebar(sensorId) {
        document.querySelectorAll('.gis-sensor-item').forEach(el => el.classList.remove('active-selected'));
        const item = document.getElementById(`sidebar-sensor-${sensorId}`);
        if (item) {
            item.classList.add('active-selected');
            item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    // Abrir Modal de Telemetría y Curva de Consumo
    const telemetryModalEl = document.getElementById('sensorTelemetryModal');
    const telemetryModal = new bootstrap.Modal(telemetryModalEl);

    window.gisOpenTelemetryModal = function (sensorId) {
        const sensor = sensors.find(s => s.id === Number(sensorId));
        if (!sensor) return;

        document.getElementById('telemetryModalTitle').innerHTML = `<i class="bi bi-graph-up-arrow text-info me-2"></i>${escapeHtml(sensor.name)} <span class="badge bg-secondary ms-1">#${escapeHtml(sensor.identifier)}</span>`;
        document.getElementById('telemetryModalSubtitle').textContent = `Grupo: ${sensor.group_name} · Unidad: ${sensor.unit}`;

        document.getElementById('telemetryOpBadge').innerHTML = getOperationalBadgeHtml(sensor);
        document.getElementById('telemetryOpTarget').textContent = sensor.target_date ? `Meta: ${sensor.target_date} (Tol. hasta ${sensor.tolerance_end || '-'})` : 'Sin fecha fija';

        document.getElementById('telemetryLastValue').textContent = sensor.last_value !== null ? `${sensor.last_value} ${sensor.unit}` : 'Sin lectura';
        document.getElementById('telemetryLastDate').textContent = sensor.last_measured_at ? `Tomada: ${sensor.last_measured_at}` : 'Nunca medido';

        document.getElementById('telemetryLastDelta').textContent = sensor.last_delta !== null ? `+${sensor.last_delta} ${sensor.unit}` : 'N/A';
        document.getElementById('telemetryDailyRate').textContent = sensor.daily_rate !== null ? `Promedio: ${sensor.daily_rate} ${sensor.unit}/día` : 'Requiere 2+ lecturas';

        document.getElementById('telemetryConsBadge').innerHTML = getConsumptionBadgeHtml(sensor);
        if (sensor.rate_variation_pct !== null) {
            const sign = sensor.rate_variation_pct > 0 ? '+' : '';
            document.getElementById('telemetryConsVariation').textContent = `Variación ritmo: ${sign}${sensor.rate_variation_pct}% vs ciclo previo`;
        } else {
            document.getElementById('telemetryConsVariation').textContent = 'Sin ciclo previo comparable';
        }

        document.getElementById('telemetryOpMessage').textContent = sensor.op_message || 'Sin observaciones operativas.';
        document.getElementById('telemetryUnitBadge').textContent = `Unidad: ${sensor.unit}`;
        document.getElementById('telemetryMeasureBtn').href = sensor.measure_url;
        document.getElementById('telemetryShowBtn').href = sensor.show_url;

        // Renderizar tabla de historial
        const tbody = document.getElementById('telemetryHistoryTableBody');
        const history = sensor.history || [];
        if (history.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-3">Este sensor aún no registra mediciones históricas.</td></tr>`;
        } else {
            tbody.innerHTML = [...history].reverse().map(pt => `
                <tr>
                    <td>${escapeHtml(pt.full_date)}</td>
                    <td class="text-end fw-bold">${pt.value} ${escapeHtml(sensor.unit)}</td>
                    <td class="text-end text-primary">${pt.delta !== null ? '+' + pt.delta + ' ' + escapeHtml(sensor.unit) : '—'}</td>
                    <td class="text-end text-muted">${pt.daily_rate !== null ? pt.daily_rate + ' ' + escapeHtml(sensor.unit) + '/día' : 'Lectura base'}</td>
                </tr>
            `).join('');
        }

        // Renderizar Gráfico Chart.js
        const ctx = document.getElementById('telemetryChartCanvas').getContext('2d');
        if (telemetryChartInstance) {
            telemetryChartInstance.destroy();
        }

        const labels = history.map(pt => pt.date);
        const values = history.map(pt => pt.value);
        const deltas = history.map(pt => pt.delta ?? 0);

        telemetryChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['Sin datos'],
                datasets: [
                    {
                        label: `Lectura Acumulada (${sensor.unit})`,
                        data: values.length ? values : [0],
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.12)',
                        borderWidth: 2.5,
                        tension: 0.3,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: `Consumo del Tramo Δ (${sensor.unit})`,
                        data: deltas.length ? deltas : [0],
                        type: 'bar',
                        backgroundColor: sensor.consumption_status === 'peak' ? 'rgba(225, 29, 72, 0.65)' : 'rgba(16, 185, 129, 0.6)',
                        borderRadius: 4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
                },
                scales: {
                    y: { type: 'linear', display: true, position: 'left', title: { display: true, text: 'Lectura' } },
                    y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Consumo (Δ)' } }
                }
            }
        });

        telemetryModal.show();
    };

    // Guardar coordenadas vía AJAX en el Backend
    function saveSensorCoordinates(sensorId, lat, lng, flyAfterSave = true) {
        const sensor = sensors.find(s => s.id === Number(sensorId));
        if (!sensor) return;

        fetch(`/mapa-sensores/${sensorId}/coordinates`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ lat: lat, lng: lng })
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                sensor.lat = resp.data.lat;
                sensor.lng = resp.data.lng;
                sensor.has_coords = true;

                stopPlacingMode();
                refreshStats();
                renderSidebarList();
                upsertMarker(sensor);

                if (flyAfterSave && markersById[sensor.id]) {
                    map.flyTo([sensor.lat, sensor.lng], Math.max(map.getZoom(), 16), { duration: 0.6 });
                    markersById[sensor.id].openPopup();
                }

                notifyToast(resp.message, 'success');
            } else {
                notifyToast(resp.message || 'No se pudieron guardar las coordenadas.', 'danger');
            }
        })
        .catch(() => {
            notifyToast('Error de red al guardar coordenadas.', 'danger');
        });
    }

    // Eliminar coordenadas de un sensor vía AJAX
    function removeSensorCoordinates(sensorId) {
        const sensor = sensors.find(s => s.id === Number(sensorId));
        if (!sensor) return;

        fetch(`/mapa-sensores/${sensorId}/coordinates`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                sensor.lat = null;
                sensor.lng = null;
                sensor.has_coords = false;

                if (markersById[sensor.id]) {
                    map.removeLayer(markersById[sensor.id]);
                    delete markersById[sensor.id];
                }

                refreshStats();
                renderSidebarList();
                notifyToast(resp.message, 'warning');
            } else {
                notifyToast(resp.message || 'Error al quitar coordenadas.', 'danger');
            }
        })
        .catch(() => {
            notifyToast('Error de red al eliminar ubicación.', 'danger');
        });
    }

    window.gisStartPlacing = function (sensorId) {
        const sensor = sensors.find(s => s.id === Number(sensorId));
        if (!sensor) return;

        placingSensorId = sensor.id;
        selectedSensorId = sensor.id;
        map.closePopup();

        document.getElementById('placingSensorName').textContent = `${sensor.name} (#${sensor.identifier})`;
        document.getElementById('placingModeBanner').classList.add('active');
        document.getElementById('sensorsMapCanvas').classList.add('placing-cursor');

        renderSidebarList();
    };

    function stopPlacingMode() {
        placingSensorId = null;
        document.getElementById('placingModeBanner').classList.remove('active');
        document.getElementById('sensorsMapCanvas').classList.remove('placing-cursor');
    }

    window.gisFocusSensor = function (sensorId) {
        const sensor = sensors.find(s => s.id === Number(sensorId));
        if (!sensor || !sensor.has_coords) return;

        stopPlacingMode();
        selectedSensorId = sensor.id;
        highlightSelectedInSidebar(sensor.id);

        if (!markersById[sensor.id]) {
            upsertMarker(sensor);
        }

        map.flyTo([sensor.lat, sensor.lng], 17, { duration: 0.7 });
        setTimeout(() => {
            if (markersById[sensor.id]) {
                markersById[sensor.id].openPopup();
            }
        }, 450);
    };

    window.gisHandleCardClick = function (sensorId) {
        const sensor = sensors.find(s => s.id === Number(sensorId));
        if (!sensor) return;

        if (sensor.has_coords) {
            window.gisFocusSensor(sensor.id);
        } else {
            window.gisStartPlacing(sensor.id);
        }
    };

    // Modal de edición manual de coordenadas
    const manualModalEl = document.getElementById('manualCoordsModal');
    const manualModal = new bootstrap.Modal(manualModalEl);

    window.gisOpenManualModal = function (sensorId) {
        const sensor = sensors.find(s => s.id === Number(sensorId));
        if (!sensor) return;

        document.getElementById('modalSensorId').value = sensor.id;
        document.getElementById('modalSensorTitle').textContent = `${sensor.name} (#${sensor.identifier})`;
        document.getElementById('modalSensorMeta').textContent = `Grupo: ${sensor.group_name}`;
        document.getElementById('quickPasteCoords').value = '';
        document.getElementById('modalLatInput').value = sensor.has_coords ? sensor.lat : '';
        document.getElementById('modalLngInput').value = sensor.has_coords ? sensor.lng : '';

        const removeBtn = document.getElementById('btnModalRemoveCoords');
        if (sensor.has_coords) {
            removeBtn.classList.remove('d-none');
        } else {
            removeBtn.classList.add('d-none');
        }

        manualModal.show();
    };

    document.getElementById('quickPasteCoords').addEventListener('input', function () {
        const raw = this.value.trim();
        const parts = raw.split(/[\s,;]+/).filter(Boolean);
        if (parts.length >= 2) {
            const lat = parseFloat(parts[0]);
            const lng = parseFloat(parts[1]);
            if (!isNaN(lat) && !isNaN(lng)) {
                document.getElementById('modalLatInput').value = lat;
                document.getElementById('modalLngInput').value = lng;
            }
        }
    });

    document.getElementById('btnSaveManualCoords').addEventListener('click', function () {
        const sensorId = Number(document.getElementById('modalSensorId').value);
        const lat = parseFloat(document.getElementById('modalLatInput').value);
        const lng = parseFloat(document.getElementById('modalLngInput').value);

        if (isNaN(lat) || isNaN(lng) || lat < -90 || lat > 90 || lng < -180 || lng > 180) {
            notifyToast('Por favor ingresa una Latitud (-90 a 90) y Longitud (-180 a 180) válidas.', 'danger');
            return;
        }

        manualModal.hide();
        saveSensorCoordinates(sensorId, lat, lng, true);
    });

    document.getElementById('btnModalRemoveCoords').addEventListener('click', function () {
        const sensorId = Number(document.getElementById('modalSensorId').value);
        if (confirm('¿Deseas quitar las coordenadas de este sensor del mapa?')) {
            manualModal.hide();
            removeSensorCoordinates(sensorId);
        }
    });

    map.on('click', function (e) {
        if (!placingSensorId) return;
        saveSensorCoordinates(placingSensorId, e.latlng.lat, e.latlng.lng, true);
    });

    document.getElementById('btnCancelPlacing').addEventListener('click', function () {
        stopPlacingMode();
        renderSidebarList();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && placingSensorId) {
            stopPlacingMode();
            renderSidebarList();
        }
    });

    // Cambio de modo de coloración GIS (Estado de Tomas / Consumos y Picos / Tipo de Servicio)
    document.querySelectorAll('input[name="gisColorMode"]').forEach(radio => {
        radio.addEventListener('change', function () {
            currentColorMode = this.value;
            renderMapLegend();
            renderSidebarList();
        });
    });

    // Clic en tarjetas superiores de filtro rápido GIS
    document.querySelectorAll('.gis-stat-card').forEach(card => {
        card.addEventListener('click', function () {
            const filterKey = this.getAttribute('data-quick-filter');
            document.querySelectorAll('.gis-stat-card').forEach(c => c.classList.remove('active'));
            this.classList.add('active');

            document.getElementById('filterDiagnostic').value = filterKey;

            // Si filtró por pico o estancamiento, cambiar automáticamente el color de pines a modo Consumo para resaltarlos
            if (filterKey.startsWith('cons_')) {
                document.getElementById('modeConsumption').checked = true;
                currentColorMode = 'consumption';
                renderMapLegend();
            } else if (filterKey.startsWith('op_')) {
                document.getElementById('modeOperational').checked = true;
                currentColorMode = 'operational';
                renderMapLegend();
            }

            renderSidebarList();
            fitAllVisibleMarkers();
        });
    });

    // Eventos de filtros del panel lateral
    document.getElementById('filterGroup').addEventListener('change', function () {
        refreshStats();
        renderSidebarList();
        fitAllVisibleMarkers();
    });

    document.getElementById('filterDiagnostic').addEventListener('change', function () {
        const val = this.value;
        document.querySelectorAll('.gis-stat-card').forEach(c => {
            c.classList.toggle('active', c.getAttribute('data-quick-filter') === val);
        });
        renderSidebarList();
        fitAllVisibleMarkers();
    });

    document.getElementById('filterSearch').addEventListener('input', renderSidebarList);

    document.getElementById('btnClearSearch').addEventListener('click', function () {
        document.getElementById('filterSearch').value = '';
        document.getElementById('filterDiagnostic').value = 'all';
        document.querySelectorAll('.gis-stat-card').forEach(c => {
            c.classList.toggle('active', c.getAttribute('data-quick-filter') === 'all');
        });
        renderSidebarList();
        fitAllVisibleMarkers();
    });

    // Switch para habilitar/deshabilitar arrastrar marcadores (Drag & Drop)
    document.getElementById('toggleDragMarkers').addEventListener('change', function () {
        const enabled = this.checked;
        Object.values(markersById).forEach(marker => {
            if (enabled) {
                marker.dragging.enable();
            } else {
                marker.dragging.disable();
            }
        });
        notifyToast(
            enabled ? 'Arrastre de pines activado: mueve cualquier pin para guardar su nueva posición.' : 'Arrastre de pines bloqueado.',
            'success'
        );
    });

    // Alternar capa Callejero / Satélite
    document.getElementById('btnToggleMapLayer').addEventListener('click', function () {
        isSatellite = !isSatellite;
        if (isSatellite) {
            map.removeLayer(streetLayer);
            map.addLayer(satelliteLayer);
            document.getElementById('mapLayerLabel').textContent = 'Mapa Callejero';
        } else {
            map.removeLayer(satelliteLayer);
            map.addLayer(streetLayer);
            document.getElementById('mapLayerLabel').textContent = 'Satélite';
        }
    });

    document.getElementById('btnFitAllMarkers').addEventListener('click', fitAllVisibleMarkers);

    document.getElementById('btnMyGpsLocation').addEventListener('click', function () {
        if (!navigator.geolocation) {
            notifyToast('Tu navegador no soporta geolocalización.', 'warning');
            return;
        }
        navigator.geolocation.getCurrentPosition(
            function (pos) {
                map.flyTo([pos.coords.latitude, pos.coords.longitude], 16, { duration: 0.8 });
            },
            function () {
                notifyToast('No se pudo obtener tu ubicación actual. Verifica los permisos del navegador.', 'warning');
            }
        );
    });

    // Buscador de direcciones / localidades con Nominatim (OpenStreetMap)
    function searchAddressOnMap() {
        const query = document.getElementById('addressSearchInput').value.trim();
        if (!query) return;

        const btn = document.getElementById('btnSearchAddress');
        btn.disabled = true;

        fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(results => {
                btn.disabled = false;
                if (results && results.length > 0) {
                    const place = results[0];
                    map.flyTo([parseFloat(place.lat), parseFloat(place.lon)], 15, { duration: 0.8 });
                } else {
                    notifyToast('No se encontró esa dirección o localidad. Intenta agregar provincia o país.', 'warning');
                }
            })
            .catch(() => {
                btn.disabled = false;
                notifyToast('Error al consultar el buscador geográfico.', 'danger');
            });
    }

    document.getElementById('btnSearchAddress').addEventListener('click', searchAddressOnMap);
    document.getElementById('addressSearchInput').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchAddressOnMap();
        }
    });

    // Inicialización: verificar si vienen parámetros ?group_id= o ?sensor_id= en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const paramGroupId = urlParams.get('group_id');
    const paramSensorId = urlParams.get('sensor_id');

    if (paramGroupId) {
        const groupSelect = document.getElementById('filterGroup');
        if ([...groupSelect.options].some(o => o.value === paramGroupId)) {
            groupSelect.value = paramGroupId;
        }
    }

    renderMapLegend();
    refreshStats();
    renderSidebarList();
    fitAllVisibleMarkers();

    if (paramSensorId) {
        const target = sensors.find(s => String(s.id) === String(paramSensorId));
        if (target) {
            if (target.has_coords) {
                window.gisFocusSensor(target.id);
            } else {
                window.gisStartPlacing(target.id);
            }
        }
    }
});
</script>
@endpush
