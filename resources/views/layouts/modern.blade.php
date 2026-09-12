<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxNiAxNiI+PHBhdGggZmlsbD0iIzBkNmVmZCIgZD0iTTE0LjUgM2EuNS41IDAgMCAxIC41LjV2OWEuNS41IDAgMCAxLS41LjVoLTEzYS41LjUgMCAwIDEtLjUtLjV2LTlhLjUuNSAwIDAgMSAuNS0uNWgxM3ptLTEzLTFBMS41IDEuNSAwIDAgMCAwIDMuNXY5QTEuNSAxLjUgMCAwIDAgMS41IDE0aDEzYTEuNSAxLjUgMCAwIDAgMS41LTEuNXYtOUExLjUgMS41IDAgMCAwIDE0LjUgMmgtMTN6Ii8+PHBhdGggZmlsbD0iIzBkNmVmZCIgZD0iTTMgOC41YS41LjUgMCAwIDEgLjUtLjVoMS44OWwxLjI1NC0yLjUwOGEuNS41IDAgMCAxIC45MS0uMDE2bDEuMjk0IDIuOTgzLjg0NC0xLjI2NmEuNS41IDAgMCAxIC44NDMuMDE4bDEuMTA5IDEuNzg5SDEyLjVhLjUuNSAwIDAgMSAwIDFIMTFhLjUuNSAwIDAgMS0uNDI0LS4yMzZsLS42ODktMS4xMS0uODQ5IDEuMjcyYS41LjUgMCAwIDEtLjg0NC0uMDE3bC0xLjMtMy0xLjI0NiAyLjQ5MkEuNS41IDAgMCAxIDUuMiA5SDMuNWEuNS41IDAgMCAxLS41LS41eiIvPjwvc3ZnPg==">
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
<!-- Botón Flotante Flowy AI -->
<button class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center" 
        id="btnFlowyAI" 
        style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 1050; border-radius: 50% !important;">
    <i class="bi bi-robot fs-3 text-white"></i>
</button>

<!-- Caja de Chat Oculta -->
<div class="card shadow-lg d-none" id="chatFlowyContainer" 
     style="position: fixed; bottom: 100px; right: 30px; width: 350px; z-index: 1050; border-radius: 15px; border: 1px solid #e0e0e0; overflow: hidden;">
    
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-robot me-2"></i> Flowy (Beta IA)</h6>
        <button type="button" class="btn-close btn-close-white" id="closeFlowyChat" style="font-size: 0.8rem;"></button>
    </div>

    <div class="card-body bg-light" id="flowyChatBox" style="height: 350px; overflow-y: auto; font-size: 0.9rem;">
        <div class="mb-3 text-start">
            <span class="badge bg-white text-dark shadow-sm px-3 py-2 text-wrap" style="border-radius: 15px 15px 15px 0;">
                ¡Hola! Soy tu asistente inteligente MedFlow. ¿En qué flujo u operación tienes dudas hoy?
            </span>
        </div>
    </div>

    <div class="card-footer bg-white border-top-0 p-2">
        <div class="input-group">
            <input type="text" id="flowyUserInput" class="form-control rounded-pill border-1 bg-light ps-3 me-2" placeholder="Escribe tu consulta..." aria-label="Escribe tu consulta...">
            <button class="btn btn-primary rounded-circle d-flex align-items-center px-3" id="btnSendFlowy" style="height: 40px; width: 40px !important;">
                <i class="bi bi-send-fill" style="margin-left: -2px;"></i>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnOpen = document.getElementById('btnFlowyAI');
    const btnClose = document.getElementById('closeFlowyChat');
    const chatContainer = document.getElementById('chatFlowyContainer');
    const chatBox = document.getElementById('flowyChatBox');
    const userInput = document.getElementById('flowyUserInput');
    const btnSend = document.getElementById('btnSendFlowy');

    if(btnOpen) btnOpen.addEventListener('click', () => chatContainer.classList.toggle('d-none'));
    if(btnClose) btnClose.addEventListener('click', () => chatContainer.classList.add('d-none'));

    function askQuestion() {
        const text = userInput.value.trim();
        if (!text) return;

        chatBox.innerHTML += `
            <div class="mb-3 text-end">
                <span class="badge bg-primary text-white shadow-sm px-3 py-2 text-wrap" style="border-radius: 15px 15px 0 15px; text-align: left !important; font-weight: normal;">
                    ${text}
                </span>
            </div>
        `;
        userInput.value = '';
        chatBox.scrollTop = chatBox.scrollHeight;

        const spinnerId = 'spinner-' + Date.now();
        chatBox.innerHTML += `
            <div class="mb-3 text-start" id="${spinnerId}">
                <span class="badge bg-white text-primary shadow-sm px-3 py-2 text-wrap" style="border-radius: 15px 15px 15px 0; font-weight: normal;">
                    <i class="bi bi-chat-dots-fill heartbeat-anim"></i> Pensando...
                </span>
            </div>
        `;
        chatBox.scrollTop = chatBox.scrollHeight;

        $.ajax({
            url: "/api/soporte/ask",
            type: "POST",
            data: {
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                message: text
            },
            success: function(response) {
                document.getElementById(spinnerId).remove();
                if(response.success === false) {
                   chatBox.innerHTML += `
                        <div class="mb-3 text-start">
                            <span class="badge bg-warning text-dark shadow-sm px-3 py-2 text-wrap" style="border-radius: 15px 15px 15px 0; max-width: 90%; text-align: left !important; white-space: pre-wrap; font-weight: normal; line-height: 1.4;">${response.answer}</span>
                        </div>
                    `;
                } else {
                    let formattedHtml = response.answer.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                    chatBox.innerHTML += `
                        <div class="mb-3 text-start">
                            <span class="badge bg-white text-dark shadow-sm px-3 py-2 text-wrap" style="border-radius: 15px 15px 15px 0; max-width: 90%; text-align: left !important; white-space: pre-wrap; font-weight: normal; line-height: 1.4;">${formattedHtml}</span>
                        </div>
                    `;
                }
                chatBox.scrollTop = chatBox.scrollHeight;
            },
            error: function(xhr) {
                document.getElementById(spinnerId).remove();
                let serverError = xhr.responseJSON && xhr.responseJSON.answer ? xhr.responseJSON.answer : 'Error de respuesta del servidor.';
                chatBox.innerHTML += `
                    <div class="mb-3 text-start">
                        <span class="badge bg-danger text-white shadow-sm px-3 py-2 text-wrap" style="border-radius: 15px 15px 15px 0; max-width: 90%; white-space: pre-wrap; font-weight: normal; line-height: 1.4;">Error 500: ${serverError}</span>
                    </div>
                `;
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        });
    }

    if(btnSend) btnSend.addEventListener('click', askQuestion);
    if(userInput) userInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') askQuestion(); });

    const style = document.createElement('style');
    style.innerHTML = `@keyframes heartbeat { 0% { transform: scale(1); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } } .heartbeat-anim { display: inline-block; animation: heartbeat 1.5s infinite; }`;
    document.head.appendChild(style);
});
</script>
</body>

</html>