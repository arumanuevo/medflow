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

    .gis-sidebar-card {
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        height: 680px;
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

    .gis-card-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        width: 100%;
    }

    .gis-card-actions .btn {
        flex: 1 1 auto;
        min-width: 0;
        font-size: 0.74rem;
        padding: 0.24rem 0.4rem;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
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

    .gis-map-wrapper {
        position: relative;
        height: 680px;
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
    }

    .medflow-map-pin i {
        transform: rotate(45deg);
        color: #ffffff;
        font-size: 0.95rem;
    }

    .pin-agua { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
    .pin-electricidad { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .pin-gas { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .pin-personalizado { background: linear-gradient(135deg, #6366f1, #4f46e5); }

    .gis-kpi-pill {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 10px;
        padding: 0.5rem 0.9rem;
        min-width: 120px;
    }

    .leaflet-popup-content-wrapper {
        border-radius: 12px;
        padding: 2px;
    }

    @media (max-width: 991.98px) {
        .gis-sidebar-card {
            height: 420px;
            margin-bottom: 1rem;
        }
        .gis-map-wrapper {
            height: 520px;
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

    {{-- ENCABEZADO DEL MÓDULO PREMIUM GIS --}}
    <div class="card gis-header-card shadow-sm mb-3">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-warning text-dark fw-bold px-2 py-1">
                            <i class="bi bi-star-fill me-1"></i> APP PREMIUM
                        </span>
                        <span class="text-white-50 small">Georreferenciación Interactiva</span>
                    </div>
                    <h4 class="mb-1 fw-bold text-white">
                        <i class="bi bi-geo-alt-fill text-info me-1"></i> Mapa Interactivo de Sensores (GIS)
                    </h4>
                    <p class="mb-0 text-white-50 small">
                        Visualiza tus medidores en el mapa, asigna coordenadas con un clic o arrastra los pines (Drag & Drop) para ajustar su posición exacta.
                    </p>
                </div>

                {{-- KPIs de Cobertura Geográfica --}}
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <div class="gis-kpi-pill text-center">
                        <div class="text-white-50" style="font-size: 0.7rem; text-transform: uppercase;">Total Sensores</div>
                        <div class="fs-5 fw-bold text-white" id="kpiTotalSensors">{{ $stats['total'] }}</div>
                    </div>
                    <div class="gis-kpi-pill text-center" style="border-color: rgba(34,197,94,0.45);">
                        <div class="text-success-emphasis" style="font-size: 0.7rem; text-transform: uppercase; color: #86efac !important;">Ubicados</div>
                        <div class="fs-5 fw-bold" style="color: #4ade80;" id="kpiLocatedSensors">{{ $stats['located'] }}</div>
                    </div>
                    <div class="gis-kpi-pill text-center" style="border-color: rgba(251,191,36,0.45);">
                        <div style="font-size: 0.7rem; text-transform: uppercase; color: #fde68a;">Sin Ubicar</div>
                        <div class="fs-5 fw-bold" style="color: #fbbf24;" id="kpiUnlocatedSensors">{{ $stats['unlocated'] }}</div>
                    </div>
                    <div class="gis-kpi-pill text-center">
                        <div class="text-white-50" style="font-size: 0.7rem; text-transform: uppercase;">Cobertura</div>
                        <div class="fs-5 fw-bold text-info" id="kpiCoveragePercent">{{ $stats['coverage_percent'] }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- PANEL LATERAL IZQUIERDO: FILTROS Y LISTADO DE SENSORES --}}
        <div class="col-lg-4 col-xl-3">
            <div class="gis-sidebar-card shadow-sm">
                <div class="p-3 border-bottom bg-light">
                    <h6 class="fw-bold mb-2 text-dark d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-funnel me-1 text-primary"></i> Filtrar Sensores</span>
                        <span class="badge bg-secondary rounded-pill" id="filteredCountBadge">{{ $stats['total'] }}</span>
                    </h6>

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

                    {{-- Filtro por Estado de Coordenada --}}
                    <div class="btn-group w-100 mb-2" role="group">
                        <input type="radio" class="btn-check" name="filterStatus" id="statusAll" value="all" checked>
                        <label class="btn btn-outline-secondary btn-sm" for="statusAll">Todos</label>

                        <input type="radio" class="btn-check" name="filterStatus" id="statusLocated" value="located">
                        <label class="btn btn-outline-success btn-sm" for="statusLocated">
                            <i class="bi bi-geo-alt-fill"></i> Ubicados
                        </label>

                        <input type="radio" class="btn-check" name="filterStatus" id="statusUnlocated" value="unlocated">
                        <label class="btn btn-outline-warning btn-sm text-dark" for="statusUnlocated">
                            <i class="bi bi-exclamation-circle"></i> Sin ubicar
                        </label>
                    </div>

                    {{-- Buscador por texto --}}
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="filterSearch" class="form-control" placeholder="Buscar por nombre o código...">
                        <button class="btn btn-outline-secondary" type="button" id="btnClearSearch" title="Limpiar búsqueda">
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
                    <div class="gis-toolbar-box d-flex align-items-center gap-1" style="min-width: 260px; max-width: 380px; flex: 1;">
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

                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnFitAllMarkers" title="Centrar mapa en todos los sensores ubicados">
                            <i class="bi bi-fullscreen me-1"></i> Encuadrar
                        </button>

                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnMyGpsLocation" title="Centrar en mi ubicación actual (GPS)">
                            <i class="bi bi-crosshair"></i>
                        </button>
                    </div>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Estado en memoria de todos los sensores
    let sensors = @json($sensorsData);
    let markersById = {};
    let placingSensorId = null;
    let selectedSensorId = null;
    let isSatellite = false;

    // Coordenadas iniciales por defecto (Buenos Aires, Argentina) si aún no hay sensores ubicados
    const defaultCenter = [-34.6037, -58.3816];
    const defaultZoom = 12;

    // Capas base de Leaflet (Callejero OSM + Satelital Esri)
    const streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    });

    const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 19,
        attribution: 'Tiles &copy; Esri'
    });

    // Inicializar Mapa Leaflet
    const map = L.map('sensorsMapCanvas', {
        center: defaultCenter,
        zoom: defaultZoom,
        layers: [streetLayer],
        zoomControl: true
    });

    // Función para mostrar notificación Toast limpia
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

    // Ícono personalizado según tipo de plantilla
    function createSensorIcon(sensor) {
        let pinClass = 'pin-personalizado';
        let iconName = 'bi-speedometer2';

        const type = (sensor.template_type || '').toLowerCase();
        if (type.includes('agua')) {
            pinClass = 'pin-agua';
            iconName = 'bi-droplet-fill';
        } else if (type.includes('electr') || type.includes('luz')) {
            pinClass = 'pin-electricidad';
            iconName = 'bi-lightning-charge-fill';
        } else if (type.includes('gas')) {
            pinClass = 'pin-gas';
            iconName = 'bi-fire';
        }

        return L.divIcon({
            className: 'custom-sensor-div-icon',
            html: `<div class="medflow-map-pin ${pinClass}" title="${escapeHtml(sensor.name)}"><i class="bi ${iconName}"></i></div>`,
            iconSize: [34, 34],
            iconAnchor: [17, 34],
            popupAnchor: [0, -32]
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Contenido HTML del Popup de cada marcador en el mapa
    function buildPopupHtml(sensor) {
        const coordsText = `${Number(sensor.lat).toFixed(6)}, ${Number(sensor.lng).toFixed(6)}`;
        const gmapsUrl = `https://www.google.com/maps?q=${sensor.lat},${sensor.lng}`;
        const lastReadingHtml = sensor.last_value !== null
            ? `<div class="small text-success fw-semibold mt-1"><i class="bi bi-activity me-1"></i>Última lectura: ${escapeHtml(sensor.last_value)} <span class="text-muted fw-normal">(${escapeHtml(sensor.last_measured_at)})</span></div>`
            : `<div class="small text-muted fst-italic mt-1">Sin mediciones registradas</div>`;

        return `
            <div style="min-width: 240px; padding: 4px;">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                    <h6 class="fw-bold mb-0 text-dark">${escapeHtml(sensor.name)}</h6>
                    <span class="badge bg-light text-dark border">#${escapeHtml(sensor.identifier)}</span>
                </div>
                <div class="small text-primary fw-semibold mb-1">
                    <i class="bi bi-folder2-open me-1"></i>${escapeHtml(sensor.group_name)}
                </div>
                ${lastReadingHtml}
                <hr class="my-2">
                <div class="d-flex align-items-center justify-content-between bg-light rounded p-1 px-2 mb-2 border">
                    <code class="small text-dark">${coordsText}</code>
                    <a href="${gmapsUrl}" target="_blank" rel="noopener" class="btn btn-link btn-sm p-0 text-decoration-none" title="Abrir en Google Maps">
                        <i class="bi bi-box-arrow-up-right"></i> Maps
                    </a>
                </div>
                <div class="d-flex flex-wrap gap-1">
                    <button type="button" class="btn btn-outline-primary btn-sm flex-fill" onclick="window.gisOpenManualModal(${sensor.id})">
                        <i class="bi bi-pencil-square"></i> Coords
                    </button>
                    <button type="button" class="btn btn-outline-warning text-dark btn-sm flex-fill" onclick="window.gisStartPlacing(${sensor.id})">
                        <i class="bi bi-cursor-fill"></i> Reubicar
                    </button>
                    <a href="${sensor.measure_url}" class="btn btn-success btn-sm flex-fill text-white">
                        <i class="bi bi-rulers"></i> Medir
                    </a>
                    <a href="${sensor.show_url}" class="btn btn-outline-secondary btn-sm" title="Ver detalles del sensor">
                        <i class="bi bi-eye"></i>
                    </a>
                </div>
            </div>
        `;
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

        // Quitar los que no están en el filtro actual
        Object.keys(markersById).forEach(id => {
            if (!visibleIds.has(Number(id))) {
                map.removeLayer(markersById[id]);
                delete markersById[id];
            }
        });

        // Agregar o actualizar los visibles
        filteredSensors.forEach(sensor => {
            if (sensor.has_coords) {
                upsertMarker(sensor);
            }
        });
    }

    // Ajustar el zoom del mapa para mostrar todos los marcadores visibles
    function fitAllVisibleMarkers() {
        const markerList = Object.values(markersById);
        if (markerList.length === 0) return;

        const group = L.featureGroup(markerList);
        map.fitBounds(group.getBounds(), { padding: [50, 50], maxZoom: 17 });
    }

    // Actualizar contadores KPI superiores
    function refreshStats() {
        const total = sensors.length;
        const located = sensors.filter(s => s.has_coords).length;
        const unlocated = Math.max(0, total - located);
        const coverage = total > 0 ? Math.round((located / total) * 100) : 0;

        document.getElementById('kpiTotalSensors').textContent = total;
        document.getElementById('kpiLocatedSensors').textContent = located;
        document.getElementById('kpiUnlocatedSensors').textContent = unlocated;
        document.getElementById('kpiCoveragePercent').textContent = coverage + '%';
    }

    // Obtener sensores según filtros del panel izquierdo
    function getFilteredSensors() {
        const groupVal = document.getElementById('filterGroup').value;
        const statusVal = document.querySelector('input[name="filterStatus"]:checked').value;
        const query = document.getElementById('filterSearch').value.trim().toLowerCase();

        return sensors.filter(sensor => {
            if (groupVal !== 'all' && String(sensor.group_id) !== String(groupVal)) {
                return false;
            }
            if (statusVal === 'located' && !sensor.has_coords) {
                return false;
            }
            if (statusVal === 'unlocated' && sensor.has_coords) {
                return false;
            }
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
                    <p class="mb-1 fw-semibold">No se encontraron sensores</p>
                    <small>Prueba cambiando el filtro de grupo o estado.</small>
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
                ? `<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-geo-alt-fill me-1"></i>Ubicado</span>`
                : `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="bi bi-exclamation-circle me-1"></i>Sin ubicar</span>`;

            const coordsLine = sensor.has_coords
                ? `<div class="small text-muted mt-1"><i class="bi bi-compass me-1"></i><code>${Number(sensor.lat).toFixed(5)}, ${Number(sensor.lng).toFixed(5)}</code></div>`
                : `<div class="small text-muted fst-italic mt-1">Sin coordenadas asignadas</div>`;

            const primaryActionBtn = sensor.has_coords
                ? `<button type="button" class="btn btn-outline-primary btn-sm" onclick="event.stopPropagation(); window.gisFocusSensor(${sensor.id})" title="Centrar en el mapa">
                       <i class="bi bi-crosshair"></i> Ver
                   </button>
                   <button type="button" class="btn btn-outline-warning text-dark btn-sm" onclick="event.stopPropagation(); window.gisStartPlacing(${sensor.id})" title="Hacer clic en el mapa para moverlo">
                       <i class="bi bi-cursor-fill"></i> Mover
                   </button>`
                : `<button type="button" class="btn btn-warning btn-sm fw-semibold text-dark" onclick="event.stopPropagation(); window.gisStartPlacing(${sensor.id})">
                       <i class="bi bi-geo-fill"></i> Ubicar en mapa
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
                    ${coordsLine}
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

    // Activar modo "Hacer clic en el mapa para ubicar el sensor"
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

    // Enfocar un sensor ya ubicado en el mapa
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

    // Clic sobre una tarjeta en el panel izquierdo
    window.gisHandleCardClick = function (sensorId) {
        const sensor = sensors.find(s => s.id === Number(sensorId));
        if (!sensor) return;

        if (sensor.has_coords) {
            window.gisFocusSensor(sensor.id);
        } else {
            window.gisStartPlacing(sensor.id);
        }
    };

    // Abrir modal de edición manual de coordenadas
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

    // Autoseparar coordenadas al pegar "lat, lng" de Google Maps
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

    // Evento de clic sobre el mapa cuando estamos en Modo Asignación
    map.on('click', function (e) {
        if (!placingSensorId) return;
        saveSensorCoordinates(placingSensorId, e.latlng.lat, e.latlng.lng, true);
    });

    // Cancelar modo ubicación con botón o tecla Escape
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

    // Eventos de filtros del panel lateral
    document.getElementById('filterGroup').addEventListener('change', function () {
        renderSidebarList();
        fitAllVisibleMarkers();
    });

    document.querySelectorAll('input[name="filterStatus"]').forEach(radio => {
        radio.addEventListener('change', renderSidebarList);
    });

    document.getElementById('filterSearch').addEventListener('input', renderSidebarList);

    document.getElementById('btnClearSearch').addEventListener('click', function () {
        document.getElementById('filterSearch').value = '';
        renderSidebarList();
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

    // Encuadrar todos los sensores visibles
    document.getElementById('btnFitAllMarkers').addEventListener('click', fitAllVisibleMarkers);

    // Ir a mi ubicación GPS actual
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
