<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MedFlow')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Modern Sidebar CSS -->
    <link rel="stylesheet" href="{{ asset('css/modern-sidebar.css') }}">
    <!-- Shared Styles CSS -->
    <link rel="stylesheet" href="{{ asset('css/shared-styles.css') }}">
    <!-- Table Styles CSS (fuerza consistencia en todas las tablas) -->
    <link rel="stylesheet" href="{{ asset('css/table-styles.css') }}">

    @stack('styles')

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        /* Estilos para páginas públicas (sin sidebar) */
        .public-page .modern-sidebar {
            display: none !important;
        }
        .public-page .modern-main {
            margin-left: 0 !important;
        }
        .public-page .modern-header {
            display: none !important;
        }
        .public-page .modern-content {
            padding: 0;
            background: transparent;
        }

        /* =============================================
           ESTILOS DE SUSCRIPCIÓN
           ============================================= */
        .subscription-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: #e9ecef;
            color: #495057;
            transition: all 0.3s ease;
            cursor: default;
        }
        .subscription-badge i {
            font-size: 0.8rem;
        }
        .subscription-badge.premium {
            background: linear-gradient(135deg, #ffd700, #f0c000);
            color: #000;
            box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
        }
        .subscription-badge.basico {
            background: #cfe2ff;
            color: #084298;
        }
        .subscription-badge.free {
            background: #f8d9da;
            color: #721c24;
        }
        .subscription-badge.expired {
            background: #f8d9da;
            color: #721c24;
            animation: pulse-badge 2s infinite;
        }
        @keyframes pulse-badge {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
        }

        .subscription-badge .badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 4px;
        }
        .subscription-badge .badge-dot.active {
            background: #28a745;
        }
        .subscription-badge .badge-dot.expired {
            background: #dc3545;
        }
        .subscription-badge .badge-dot.pending {
            background: #ffc107;
            animation: blink-dot 1s infinite;
        }
        @keyframes blink-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        /* Alertas de límites - Estilo más sutil y elegante */
        #limitAlertContainer {
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
        }
        #limitAlertContainer .alert {
            border-radius: 6px;
            padding: 0.65rem 0.9rem;
            margin-bottom: 0.4rem;
            font-size: 0.8rem;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            background-color: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        #limitAlertContainer .alert i {
            margin-right: 0;
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        #limitAlertContainer .alert strong {
            font-size: 0.85rem;
        }
        #limitAlertContainer .alert-warning {
            border-left: 3px solid #ffc107;
            color: #856404;
            background-color: rgba(255, 193, 7, 0.1);
        }
        #limitAlertContainer .alert-info {
            border-left: 3px solid #0dcaf0;
            color: #055160;
            background-color: rgba(13, 202, 240, 0.1);
        }
        #limitAlertContainer .alert-danger {
            border-left: 3px solid #dc3545;
            color: #491217;
            background-color: rgba(220, 53, 69, 0.1);
        }
        #limitAlertContainer .alert a.alert-link {
            font-weight: 600;
            text-decoration: none;
        }
        #limitAlertContainer .alert a.alert-link:hover {
            text-decoration: underline;
        }
        #limitAlertContainer .btn-close {
            padding: 0.5rem 0.75rem;
            margin-left: 0.5rem;
            opacity: 0.7;
        }
        #limitAlertContainer .btn-close:hover {
            opacity: 1;
        }

        /* Toggle de sidebar en móvil */
        .sidebar-toggle-btn {
            background: transparent;
            border: none;
            color: #6c757d;
            font-size: 1.5rem;
            padding: 0.25rem 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .sidebar-toggle-btn:hover {
            color: #0d6efd;
        }
        @media (min-width: 768px) {
            .sidebar-toggle-btn {
                display: none;
            }
        }

        /* Ajustes para el header */
        .modern-header .header-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .modern-header .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
    </style>
</head>
<body>
    <div class="modern-app {{ auth()->check() ? '' : 'public-page' }}">
        
        {{-- ✅ SIDEBAR --}}
        @auth
            @include('layouts.partials.modern-sidebar')
            {{-- ✅ OVERLAY MÓVIL --}}
            <div class="sidebar-overlay" id="sidebarOverlay"></div>
        @endauth

        {{-- ✅ MAIN CONTENT --}}
        <div class="modern-main" id="modernMain">
            
            {{-- ✅ HEADER --}}
            @auth
                @include('layouts.partials.modern-header')
            @endauth

            {{-- ✅ CONTENIDO --}}
            <div class="modern-content">
                {{-- ✅ CONTENEDOR DE ALERTAS DE SUSCRIPCIÓN --}}
                <!-- <div id="limitAlertContainer" class="container-fluid px-4 pt-3"></div> -->
                
                @yield('content')
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Scripts del sidebar moderno --}}
    @include('layouts.partials.modern-scripts')
    
    {{-- =============================================
     ✅ SISTEMA DE SUSCRIPCIÓN - SCRIPT PRINCIPAL
     ============================================= --}}
@auth
<script>
/**
 * =============================================
 * SISTEMA DE SUSCRIPCIÓN Y LÍMITES - VERSIÓN SIMPLIFICADA
 * =============================================
 */
(function() {
    'use strict';

    let refreshInterval = null;

    /**
     * Inicializar al cargar la página
     */
    $(document).ready(function() {
        loadSubscriptionStatus();
        refreshInterval = setInterval(loadSubscriptionStatus, 30000);
    });

    /**
     * Cargar estado de suscripción desde la API
     */
    function loadSubscriptionStatus() {
        const token = localStorage.getItem('token');
        if (!token) {
            console.warn('⚠️ No hay token de autenticación');
            return;
        }

        $.ajax({
            url: '/api/subscription/plan/status',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            timeout: 10000,
            success: function(response) {
                if (response.success) {
                    updateUI(response.data);
                } else {
                    console.warn('⚠️ Respuesta sin éxito:', response);
                }
            },
            error: function(xhr) {
                if (xhr.status !== 401) {
                    console.error('❌ Error al cargar suscripción:', xhr.status);
                }
            }
        });
    }

    /**
     * Actualizar toda la UI con los datos de suscripción
     */
    function updateUI(data) {
        // 1. Actualizar badge en el header
        updateHeaderBadge(data);

        // 2. Actualizar alertas en el dropdown
        updateDropdownAlerts(data);

        // 3. Disparar evento para que otros scripts reaccionen
        $(document).trigger('subscriptionUpdated', [data]);
    }

    /**
     * Actualizar badge de suscripción en el header (SIN ICONOS REDUNDANTES)
     */
    function updateHeaderBadge(data) {
        const plan = data.plan;
        const badge = document.querySelector('.subscription-badge');
        if (!badge) return;

        let icon = 'bi-hourglass-split';
        let className = 'free';
        let label = 'Gratuito';
        let dotClass = 'expired';

        if (data.has_active_subscription) {
            if (plan.key === 'premium') {
                icon = 'bi-star-fill';
                className = 'premium';
                label = 'Premium';
                dotClass = 'active';
            } else if (plan.key === 'basico') {
                icon = 'bi-credit-card';
                className = 'basico';
                label = 'Básico';
                dotClass = 'active';
            } else {
                icon = 'bi-gift';
                className = 'free';
                label = 'Free';
                dotClass = 'active';
            }
        } else {
            const sub = data.subscription;
            if (sub && sub.status === 'pending') {
                icon = 'bi-hourglass-split';
                className = 'expired';
                label = 'Pendiente';
                dotClass = 'pending';
            } else {
                icon = 'bi-exclamation-triangle';
                className = 'expired';
                label = 'Sin suscripción';
                dotClass = 'expired';
            }
        }

        if (plan.is_collaborator) {
            label += ' 👥';
        }

        badge.className = `subscription-badge ${className}`;
        badge.innerHTML = `
            <span class="badge-dot ${dotClass}"></span>
            <i class="bi ${icon}"></i>
            ${label}
        `;
    }

    /**
     * Actualizar alertas en el dropdown de la campanita
     */
    function updateDropdownAlerts(data) {
        const container = document.getElementById('alertsContainer');
        if (!container) return;

        // Limpiar SOLO las alertas del sistema (no las manuales)
        container.querySelectorAll('.system-alert').forEach(el => el.remove());

        const sensors = data.limits.sensors;
        const groups = data.limits.groups;
        const plan = data.plan;

        let alerts = [];
        let alertCount = 0;

        // Alerta de sensores
        if (!sensors.is_unlimited && sensors.remaining <= 1) {
            const message = sensors.remaining === 0
                ? `<strong>❌ Límite de sensores alcanzado</strong><br>Has usado <strong>${sensors.used}</strong> de <strong>${sensors.max}</strong>. <a href="/profile" class="alert-link">Actualiza tu plan</a>.`
                : `<strong>⚠️ Límite de sensores</strong><br>Te queda <strong>${sensors.remaining}</strong> sensor(es) de <strong>${sensors.max}</strong>.`;
            alerts.push({ message: message, type: sensors.remaining === 0 ? 'danger' : 'warning', id: 'alert_sensor' });
            alertCount++;
        }

        // Alerta de grupos
        if (!groups.is_unlimited && groups.remaining <= 1) {
            const message = groups.remaining === 0
                ? `<strong>❌ Límite de grupos alcanzado</strong><br>Has usado <strong>${groups.used}</strong> de <strong>${groups.max}</strong>. <a href="/profile" class="alert-link">Actualiza tu plan</a>.`
                : `<strong>⚠️ Límite de grupos</strong><br>Te queda <strong>${groups.remaining}</strong> grupo(s) de <strong>${groups.max}</strong>.`;
            alerts.push({ message: message, type: groups.remaining === 0 ? 'danger' : 'warning', id: 'alert_group' });
            alertCount++;
        }

        // Alerta de suscripción (solo si no hay suscripción activa y no es colaborador)
        if (!data.has_active_subscription && !plan.is_collaborator) {
            const message = `<strong>📋 Sin suscripción activa</strong><br>Estás usando el plan <strong>${plan.name}</strong> con límites básicos. <a href="/profile" class="alert-link">Activa tu suscripción</a>.`;
            alerts.push({ message: message, type: 'warning', id: 'alert_subscription' });
            alertCount++;
        }

        // Alerta de suscripción expirada
        if (data.subscription && data.subscription.status === 'expired') {
            const message = `<strong>⏰ Suscripción expirada</strong><br>Renueva tu suscripción desde <a href="/profile" class="alert-link">tu perfil</a>.`;
            alerts.push({ message: message, type: 'danger', id: 'alert_expired' });
            alertCount++;
        }

        // Agregar alertas al container
        const iconMap = {
            'info': 'bi-info-circle',
            'success': 'bi-check-circle',
            'warning': 'bi-exclamation-triangle',
            'danger': 'bi-exclamation-circle'
        };
        
        const colorMap = {
            'info': 'bg-info',
            'success': 'bg-success',
            'warning': 'bg-warning text-dark',
            'danger': 'bg-danger'
        };

        alerts.forEach(function(alert) {
            // Verificar si ya existe una alerta con este ID
            if (!container.querySelector(`#${alert.id}`)) {
                const alertHtml = `
                    <div class="alert-item system-alert" id="${alert.id}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="alert-text">
                                <span class="badge ${colorMap[alert.type] || 'bg-secondary'} me-2">
                                    <i class="bi ${iconMap[alert.type] || 'bi-info-circle'}"></i>
                                </span>
                                ${alert.message}
                            </div>
                            <button class="alert-close" onclick="dismissAlert('${alert.id}')">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', alertHtml);
            }
        });

        // ✅ Actualizar contador de la campanita
        updateBellBadge(alertCount);
    }

    /**
     * Actualizar contador de la campanita
     */
    function updateBellBadge(count) {
        const badge = document.getElementById('alertBadge');
        if (!badge) return;

        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('d-none');
            badge.style.display = 'inline-block';
        } else {
            badge.classList.add('d-none');
            badge.style.display = 'none';
        }
    }

    /**
     * Función pública para recargar manualmente
     */
    window.refreshSubscriptionStatus = function() {
        loadSubscriptionStatus();
    };

    /**
     * Función pública para obtener datos actuales
     */
    window.getSubscriptionData = function() {
        return currentSubscriptionData;
    };

    // Limpiar intervalo al salir
    $(window).on('beforeunload', function() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
    });

})();
</script>
@endauth

    @stack('scripts')
</body>
</html>