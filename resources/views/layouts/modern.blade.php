<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>@yield('title', 'MedFlow')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

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
           ESTILOS DE SUSCRIPCIÓN - Badge
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
            background: #d1e7dd;
            color: #0f5132;
        }

        .subscription-badge.expired {
            background: #f8d9da;
            color: #721c24;
            animation: pulse-badge 2s infinite;
        }

        @keyframes pulse-badge {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }

            100% {
                opacity: 1;
            }
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

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
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

        /* =============================================
        ESTILOS PARA BADGE DE DOWNGRADE
        ============================================= */
        .subscription-badge.downgrade-alert {
            background: linear-gradient(135deg, #fff3cd, #ffc107);
            color: #856404;
            border: 2px solid #ffc107;
            cursor: pointer;
            animation: pulse-downgrade 2s infinite;
            font-weight: 600;
        }

        @keyframes pulse-downgrade {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
            }

            50% {
                box-shadow: 0 0 0 8px rgba(255, 193, 7, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
            }
        }

        .subscription-badge.downgrade-alert:hover {
            transform: scale(1.02);
            background: linear-gradient(135deg, #ffe69b, #ffc107);
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
                @yield('content')
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Scripts del sidebar moderno --}}
    @include('layouts.partials.modern-scripts')

    <!-- ✅ Script para guardar el token en localStorage (desde cookie o sesión) -->
    @auth
        <script>
            // ✅ Intentar obtener token de la sesión (inyectado desde PHP)
            @if(session()->has('sanctum_token'))
                localStorage.setItem('token', '{{ session('sanctum_token') }}');
            @else
                    // ✅ Fallback: leer de la cookie
                    const cookieToken = document.cookie.split('; ').find(row => row.startsWith('sanctum_token='));
                if (cookieToken) {
                    const token = cookieToken.split('=')[1];
                    localStorage.setItem('token', token);
                }
            @endif

            // ✅ Log para depuración
            console.log('🔑 Token en localStorage:', localStorage.getItem('token') ? '✅ Presente' : '❌ No encontrado');
        </script>
    @endauth

    {{-- =============================================
    ✅ SISTEMA DE SUSCRIPCIÓN - SCRIPT PRINCIPAL
    ============================================= --}}
    @auth
        <script>
            /**
             * =============================================
             * SISTEMA DE SUSCRIPCIÓN Y LÍMITES
             * =============================================
             */
            (function () {
                'use strict';

                let currentSubscriptionData = null;
                let refreshInterval = null;

                $(document).ready(function () {
                    // Cargar estado inicial
                    loadSubscriptionStatus();

                    // Actualizar cada 30 segundos
                    refreshInterval = setInterval(loadSubscriptionStatus, 30000);

                    // Si hay cambios de página con Turbolinks/HTMX, reiniciar
                    $(document).on('turbolinks:load', function () {
                        loadSubscriptionStatus();
                    });

                    // Escuchar eventos de cambio de workspace
                    $(document).on('workspaceChanged', function () {
                        loadSubscriptionStatus();
                    });
                });

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
                        cache: false,
                        success: function (response) {
                            if (response.success) {
                                currentSubscriptionData = response.data;
                                updateUI(response.data);
                            } else {
                                console.warn('⚠️ Respuesta sin éxito:', response);
                            }
                        },
                        error: function (xhr) {
                            if (xhr.status !== 401) {
                                console.error('❌ Error al cargar suscripción:', xhr.status, xhr.statusText);
                            }
                        }
                    });
                }

                function updateUI(data) {
                    // 1. Actualizar badge en el header
                    updateBadge(data);

                    // 2. ✅ Mostrar alertas de límites (solo si existe)
                    if (typeof showLimitAlerts === 'function') {
                        showLimitAlerts(data);
                    }

                    // 3. Actualizar botones y elementos UI
                    updateUIElements(data);

                    // 4. ✅ INCLUIR PLAN ANTERIOR EN LOS DATOS
                    const previousPlan = localStorage.getItem('previous_plan') || null;
                    if (previousPlan) {
                        data.previous_plan = previousPlan;
                    }

                    // 5. ✅ DISPARAR EVENTO CON JAVASCRIPT PURO
                    document.dispatchEvent(new CustomEvent('subscriptionUpdated', {
                        detail: data
                    }));

                    // 6. Disparar evento para que otros scripts reaccionen
                    $(document).trigger('subscriptionUpdated', [data]);
                }

                function updateBadge(data) {
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
                 * Actualizar elementos UI según permisos
                 */
                function updateUIElements(data) {
                    const features = data.features;
                    const plan = data.plan;

                    // ✅ Botón "Crear Plantilla Personalizada"
                    if (!features.custom_templates) {
                        $('.btn-create-template, [data-feature="create-template"]').each(function () {
                            $(this).addClass('d-none');
                            if ($(this).data('original-title')) {
                                $(this).attr('title', 'Disponible en planes Premium');
                            }
                        });
                    } else {
                        $('.btn-create-template, [data-feature="create-template"]').removeClass('d-none');
                    }

                    // ✅ Botón "Exportar Datos"
                    if (!features.export_data) {
                        $('.btn-export-data, [data-feature="export-data"]').each(function () {
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
                        $('.btn-add-collaborator, [data-feature="add-collaborator"]').each(function () {
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
                        $('.btn-view-analytics, [data-feature="view-analytics"]').each(function () {
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

                    $('.counter-sensors').text(
                        sensors.is_unlimited ? '∞' : `${sensors.used}/${sensors.max}`
                    );

                    $('.counter-groups').text(
                        groups.is_unlimited ? '∞' : `${groups.used}/${groups.max}`
                    );

                    $('.counter-collaborators').text(
                        collaborators.is_unlimited ? '∞' : `${collaborators.used}/${collaborators.max}`
                    );

                    $('.progress-sensors').each(function () {
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

                    $('.progress-groups').each(function () {
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

                window.refreshSubscriptionStatus = function () {
                    loadSubscriptionStatus();
                };

                window.getSubscriptionData = function () {
                    return currentSubscriptionData;
                };

                $(window).on('beforeunload', function () {
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