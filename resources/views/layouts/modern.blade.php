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
     * SISTEMA DE SUSCRIPCIÓN Y LÍMITES
     * =============================================
     * Este script se ejecuta en todas las páginas
     * cuando el usuario está autenticado.
     */
    (function() {
        'use strict';

        // Variables de estado
        let currentSubscriptionData = null;
        let refreshInterval = null;

        /**
         * Inicializar al cargar la página
         */
        $(document).ready(function() {
            // Cargar estado inicial
            loadSubscriptionStatus();

            // Actualizar cada 30 segundos
            refreshInterval = setInterval(loadSubscriptionStatus, 30000);

            // Si hay cambios de página con Turbolinks/HTMX, reiniciar
            $(document).on('turbolinks:load', function() {
                loadSubscriptionStatus();
            });

            // Escuchar eventos de cambio de workspace
            $(document).on('workspaceChanged', function() {
                loadSubscriptionStatus();
            });
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
                        currentSubscriptionData = response.data;
                        updateUI(response.data);
                    } else {
                        console.warn('⚠️ Respuesta sin éxito:', response);
                    }
                },
                error: function(xhr) {
                    // Solo mostrar error si no es 401 (no autenticado)
                    if (xhr.status !== 401) {
                        console.error('❌ Error al cargar suscripción:', xhr.status, xhr.statusText);
                    }
                }
            });
        }

        /**
         * Actualizar toda la UI con los datos de suscripción
         */
        function updateUI(data) {
            // 1. Actualizar badge en el header
            updateBadge(data);

            // 2. Mostrar alertas de límites
            showLimitAlerts(data);

            // 3. Actualizar botones y elementos UI
            updateUIElements(data);

            // 4. Disparar evento para que otros scripts reaccionen
            $(document).trigger('subscriptionUpdated', [data]);
        }

        /**
         * Actualizar badge de suscripción en el header
         */
        function updateBadge(data) {
            const plan = data.plan;
            
            // Buscar el badge existente o crearlo
            let badge = $('.subscription-badge');
            if (badge.length === 0) {
                // Si no existe, crearlo en el header-right
                const headerRight = $('.modern-header .header-right');
                if (headerRight.length) {
                    headerRight.prepend('<span class="subscription-badge"></span>');
                    badge = $('.subscription-badge');
                } else {
                    // Si no hay header-right, no hacer nada
                    return;
                }
            }

            // Determinar estado y clase
            let icon = 'bi-hourglass-split';
            let className = 'free';
            let label = 'Gratuito';
            let dotClass = 'expired';
            let dotTitle = 'Sin suscripción activa';

            if (data.has_active_subscription) {
                if (plan.key === 'premium') {
                    icon = 'bi-star-fill';
                    className = 'premium';
                    label = '⭐ Premium';
                    dotClass = 'active';
                    dotTitle = 'Suscripción activa';
                } else if (plan.key === 'basico') {
                    icon = 'bi-credit-card';
                    className = 'basico';
                    label = '📋 Básico';
                    dotClass = 'active';
                    dotTitle = 'Suscripción activa';
                } else {
                    icon = 'bi-gift';
                    className = 'free';
                    label = '🎁 Free';
                    dotClass = 'active';
                    dotTitle = 'Plan gratuito';
                }
            } else {
                // Sin suscripción activa
                const sub = data.subscription;
                if (sub && sub.status === 'pending') {
                    icon = 'bi-hourglass-split';
                    className = 'expired';
                    label = '⏳ Pendiente';
                    dotClass = 'pending';
                    dotTitle = 'Pago pendiente de confirmación';
                } else {
                    icon = 'bi-exclamation-triangle';
                    className = 'expired';
                    label = '⚠️ Sin suscripción';
                    dotClass = 'expired';
                    dotTitle = 'Sin suscripción activa';
                }
            }

            // Si es colaborador, agregar indicador
            if (plan.is_collaborator) {
                label += ' 👥';
            }

            // Construir HTML del badge
            badge.html(`
                <span class="badge-dot ${dotClass}" title="${dotTitle}"></span>
                <i class="bi ${icon}"></i>
                ${label}
            `);
            
            // Actualizar clase
            badge.attr('class', `subscription-badge ${className}`);
            badge.attr('title', dotTitle);
        }

        /**
         * Mostrar alertas de límites
         */
        function showLimitAlerts(data) {
            const container = $('#limitAlertContainer');
            if (container.length === 0) return;

            // Limpiar alertas anteriores (excepto las que vienen del backend)
            container.find('.subscription-alert').remove();

            const sensors = data.limits.sensors;
            const groups = data.limits.groups;
            const collaborators = data.limits.collaborators;
            const plan = data.plan;

            let alerts = [];

            // 1. Alerta de sensores (si está cerca del límite)
            if (!sensors.is_unlimited && sensors.remaining <= 1) {
                const usedPercent = Math.round((sensors.used / sensors.max) * 100);
                alerts.push({
                    type: usedPercent >= 90 ? 'danger' : 'warning',
                    icon: 'bi-exclamation-triangle-fill',
                    message: `
                        <strong>⚠️ Límite de sensores alcanzado</strong><br>
                        Has usado <strong>${sensors.used}</strong> de <strong>${sensors.max}</strong> sensores disponibles.
                        ${sensors.remaining === 0 
                            ? 'Para crear más sensores, necesitas <a href="/profile" class="alert-link">actualizar tu plan</a>.'
                            : `Te queda <strong>${sensors.remaining}</strong> sensor(es) disponible(s).`
                        }
                    `
                });
            }

            // 2. Alerta de grupos (si está cerca del límite)
            if (!groups.is_unlimited && groups.remaining <= 1) {
                alerts.push({
                    type: groups.remaining === 0 ? 'danger' : 'warning',
                    icon: 'bi-exclamation-triangle-fill',
                    message: `
                        <strong>⚠️ Límite de grupos alcanzado</strong><br>
                        Has usado <strong>${groups.used}</strong> de <strong>${groups.max}</strong> grupos disponibles.
                        ${groups.remaining === 0 
                            ? 'Para crear más grupos, necesitas <a href="/profile" class="alert-link">actualizar tu plan</a>.'
                            : `Te queda <strong>${groups.remaining}</strong> grupo(s) disponible(s).`
                        }
                    `
                });
            }

            // 3. Si es colaborador, mostrar mensaje informativo
            if (plan.is_collaborator) {
                alerts.push({
                    type: 'info',
                    icon: 'bi-people-fill',
                    message: `
                        <strong>👥 Colaborador activo</strong><br>
                        Estás colaborando en el espacio de <strong>${plan.name}</strong>.
                        Los límites aplican al plan del propietario del espacio.
                    `
                });
            }

            // 4. Si no tiene suscripción activa y no es colaborador
            if (!data.has_active_subscription && !plan.is_collaborator) {
                alerts.push({
                    type: 'warning',
                    icon: 'bi-info-circle-fill',
                    message: `
                        <strong>📋 Sin suscripción activa</strong><br>
                        Estás usando el plan <strong>${plan.name}</strong> con límites básicos.
                        <a href="/profile" class="alert-link">Activa tu suscripción</a> para acceder a más funcionalidades.
                    `
                });
            }

            // 5. Si la suscripción está expirada
            if (data.subscription && data.subscription.status === 'expired') {
                alerts.push({
                    type: 'danger',
                    icon: 'bi-clock-fill',
                    message: `
                        <strong>⏰ Tu suscripción ha expirado</strong><br>
                        Renueva tu suscripción desde <a href="/profile" class="alert-link">tu perfil</a> para seguir disfrutando de todos los beneficios.
                    `
                });
            }

            // Renderizar alertas
            alerts.forEach(function(alert, index) {
                const alertHtml = `
                    <div class="alert alert-${alert.type} subscription-alert alert-dismissible fade show" role="alert">
                        <i class="bi ${alert.icon}"></i>
                        ${alert.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                container.append(alertHtml);
            });
        }

        /**
         * Actualizar elementos UI según permisos
         */
        function updateUIElements(data) {
            const features = data.features;
            const plan = data.plan;

            // ✅ Botón "Crear Plantilla Personalizada"
            if (!features.custom_templates) {
                $('.btn-create-template, [data-feature="create-template"]').each(function() {
                    $(this).addClass('d-none');
                    // Opcional: mostrar tooltip
                    if ($(this).data('original-title')) {
                        $(this).attr('title', 'Disponible en planes Premium');
                    }
                });
            } else {
                $('.btn-create-template, [data-feature="create-template"]').removeClass('d-none');
            }

            // ✅ Botón "Exportar Datos"
            if (!features.export_data) {
                $('.btn-export-data, [data-feature="export-data"]').each(function() {
                    $(this).addClass('d-none');
                    if ($(this).data('original-title')) {
                        $(this).attr('title', 'Disponible en planes Premium');
                    }
                });
            } else {
                $('.btn-export-data, [data-feature="export-data"]').removeClass('d-none');
            }

            // ✅ Botón "Agregar Colaborador"
            if (!features.add_collaborators) {
                $('.btn-add-collaborator, [data-feature="add-collaborator"]').each(function() {
                    $(this).addClass('d-none');
                    if ($(this).data('original-title')) {
                        $(this).attr('title', 'Disponible en planes Premium');
                    }
                });
            } else {
                $('.btn-add-collaborator, [data-feature="add-collaborator"]').removeClass('d-none');
            }

            // ✅ Botón "Análisis Avanzados"
            if (!features.view_analytics) {
                $('.btn-view-analytics, [data-feature="view-analytics"]').each(function() {
                    $(this).addClass('d-none');
                    if ($(this).data('original-title')) {
                        $(this).attr('title', 'Disponible en planes Premium');
                    }
                });
            } else {
                $('.btn-view-analytics, [data-feature="view-analytics"]').removeClass('d-none');
            }

            // ✅ Actualizar contadores en el sidebar o UI
            updateLimitCounters(data);
        }

        /**
         * Actualizar contadores de límites en la UI
         */
        function updateLimitCounters(data) {
            const sensors = data.limits.sensors;
            const groups = data.limits.groups;
            const collaborators = data.limits.collaborators;

            // Actualizar contadores si existen en la UI
            $('.counter-sensors').text(
                sensors.is_unlimited ? '∞' : `${sensors.used}/${sensors.max}`
            );
            
            $('.counter-groups').text(
                groups.is_unlimited ? '∞' : `${groups.used}/${groups.max}`
            );
            
            $('.counter-collaborators').text(
                collaborators.is_unlimited ? '∞' : `${collaborators.used}/${collaborators.max}`
            );

            // Actualizar barras de progreso si existen
            $('.progress-sensors').each(function() {
                const bar = $(this).find('.progress-bar');
                if (sensors.is_unlimited) {
                    bar.css('width', '100%').text('Ilimitado').removeClass('bg-danger bg-warning').addClass('bg-success');
                } else {
                    const percent = Math.min((sensors.used / sensors.max) * 100, 100);
                    bar.css('width', percent + '%')
                       .text(`${sensors.used}/${sensors.max}`)
                       .removeClass('bg-success bg-warning bg-danger')
                       .addClass(percent >= 90 ? 'bg-danger' : percent >= 70 ? 'bg-warning' : 'bg-info');
                }
            });

            $('.progress-groups').each(function() {
                const bar = $(this).find('.progress-bar');
                if (groups.is_unlimited) {
                    bar.css('width', '100%').text('Ilimitado').removeClass('bg-danger bg-warning').addClass('bg-success');
                } else {
                    const percent = Math.min((groups.used / groups.max) * 100, 100);
                    bar.css('width', percent + '%')
                       .text(`${groups.used}/${groups.max}`)
                       .removeClass('bg-success bg-warning bg-danger')
                       .addClass(percent >= 90 ? 'bg-danger' : percent >= 70 ? 'bg-warning' : 'bg-info');
                }
            });
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

        // Limpiar intervalo al salir de la página
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