@php
    $userInitials = strtoupper(substr(auth()->user()->name ?? 'U', 0, 2));
    $userRole = auth()->user()->roles->pluck('name')->join(', ');
    $user = auth()->user();

    // Obtener información de suscripción para el badge
    $subscriptionInfo = null;
    $showDowngradeAlert = false;
    $previousPlan = null;
    $downgradeMessage = '';
    $previousPlanValue = null;

    // Consultar tareas (Jobs) en segundo plano para notificar visualmente
    $pendingJobs = \DB::table('jobs')->count();

    if (auth()->check()) {
        try {
            $subscriptionService = app(\App\Services\Subscription\SubscriptionService::class, ['user' => $user]);
            $subscriptionInfo = $subscriptionService->getFullStatus();

            // ✅ OBTENER PLAN ANTERIOR ESTRICTO DESDE EL BACKEND
            $previousPlanValue = $subscriptionInfo['previous_plan'] ?? null;

            // ✅ VERIFICAR DOWNGRADE
            $hasActive = $subscriptionInfo['has_active_subscription'];
            $sub = $subscriptionInfo['subscription'];

            // Si no tiene suscripción activa pero tiene una expirada en su base de datos
            if (!$hasActive && $previousPlanValue && in_array($previousPlanValue, ['basico', 'premium'])) {
                $showDowngradeAlert = true;
                $previousPlan = $previousPlanValue === 'premium' ? 'Premium' : 'Básico';
                $downgradeMessage = "⚠️ Tu suscripción {$previousPlan} ha expirado o fue cancelada. Has perdido acceso a funcionalidades premium.";
            }

            // Si la suscripción está expirada
            if ($sub && $sub['status'] === 'expired') {
                $showDowngradeAlert = true;
                $previousPlan = $sub['plan'] === 'premium' ? 'Premium' : 'Básico';
                $downgradeMessage = "⚠️ Tu suscripción {$previousPlan} ha expirado. Renueva para recuperar tus beneficios.";
            }

        } catch (\Exception $e) {
            $showDowngradeAlert = false;
        }
    }
@endphp

<header class="modern-header">
    <div class="modern-header-left">
        <button class="modern-header-toggle" id="sidebarToggle" title="Abrir menú">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="modern-header-title">@yield('title', 'MedFlow')</h1>
    </div>

    <div class="modern-header-right">
        {{-- ✅ BADGE DE SUSCRIPCIÓN --}}
        @if($subscriptionInfo)
            <span
                class="subscription-badge {{ $subscriptionInfo['plan']['key'] === 'premium' ? 'premium' : ($subscriptionInfo['plan']['key'] === 'basico' ? 'basico' : ($subscriptionInfo['has_active_subscription'] ? 'free' : 'expired')) }}"
                title="{{ $subscriptionInfo['has_active_subscription'] ? 'Suscripción activa - Plan ' . $subscriptionInfo['plan']['name'] : 'Sin suscripción activa' }}">
                @if($subscriptionInfo['has_active_subscription'])
                    <span class="badge-dot active"></span>
                @else
                    <span class="badge-dot expired"></span>
                @endif
                <i
                    class="bi bi-{{ $subscriptionInfo['plan']['key'] === 'premium' ? 'star-fill' : ($subscriptionInfo['plan']['key'] === 'basico' ? 'credit-card' : 'gift') }}"></i>
                {{ $subscriptionInfo['plan']['key'] === 'premium' ? '⭐ Premium' : ($subscriptionInfo['plan']['key'] === 'basico' ? '📋 Básico' : ($subscriptionInfo['has_active_subscription'] ? '🎁 Free' : '⚠️ Sin suscripción')) }}
            </span>
        @endif

        {{-- ✅ BADGE DE ALERTA POR DOWNGRADE --}}
        @if($showDowngradeAlert)
            <span class="subscription-badge downgrade-alert" title="{{ $downgradeMessage }}"
                onclick="showDowngradeInfo('{{ $previousPlan }}')">
                <span class="badge-dot expired"></span>
                <i class="bi bi-exclamation-triangle"></i>
                Downgrade
                <span class="badge bg-danger ms-1" style="font-size: 0.5rem; padding: 0.1rem 0.3rem;">!</span>
            </span>
        @endif

        {{-- ✅ TAG DE TRABAJOS EN SEGUNDO PLANO --}}
        @if($pendingJobs > 0)
            <span class="subscription-badge downgrade-alert ms-2"
                style="background: linear-gradient(135deg, #e3f2fd, #0d6efd); color: white; border-color: #0d6efd;"
                title="{{ $pendingJobs }} correos procesándose asincrónicamente.">
                <span class="badge-dot" style="background: #fff; animation: blink-dot 1s infinite;"></span>
                <i class="bi bi-send-arrow-up"></i>
                Enviando ({{ $pendingJobs }})
            </span>
        @endif

        <a href="{{ route('profile.index') }}" class="modern-header-user">
            <div class="modern-header-user-avatar">{{ $userInitials }}</div>
            <div>
                <div class="modern-header-user-name">{{ auth()->user()->name }}</div>
                <div class="modern-header-user-role">{{ $userRole }}</div>
            </div>
        </a>
        <button class="modern-header-logout"
            onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();"
            title="Cerrar sesión">
            <i class="bi bi-box-arrow-right"></i>
        </button>
        <form id="logout-form-header" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</header>

{{-- Elemento oculto para pasar el plan anterior al frontend --}}
<input type="hidden" id="previousPlanValue" value="{{ $previousPlanValue ?? '' }}">

<style>
    .subscription-badge.downgrade-alert {
        background: linear-gradient(135deg, #fff3cd, #ffc107);
        color: #856404;
        border: 2px solid #ffc107;
        cursor: pointer;
        animation: pulse-downgrade 2s infinite;
        font-weight: 600;
        font-size: 0.65rem;
        padding: 0.2rem 0.6rem;
        transition: all 0.3s ease;
    }

    @keyframes pulse-downgrade {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
        }

        50% {
            box-shadow: 0 0 0 6px rgba(255, 193, 7, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
        }
    }

    .subscription-badge.downgrade-alert:hover {
        transform: scale(1.03);
        background: linear-gradient(135deg, #ffe69b, #ffc107);
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
    }

    .subscription-badge.downgrade-alert .badge-dot.expired {
        background: #dc3545;
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
</style>

<script>
    // =============================================
    // FUNCIÓN PARA MOSTRAR INFORMACIÓN DEL DOWNGRADE
    // =============================================
    function showDowngradeInfo(previousPlan) {
        const planName = previousPlan || 'anterior';

        const alertHtml = `
        <div class="alert alert-warning alert-dismissible fade show" role="alert" style="position: fixed; top: 80px; right: 20px; z-index: 9999; max-width: 420px; box-shadow: 0 8px 30px rgba(0,0,0,0.15); border-left: 4px solid #ffc107;">
            <div class="d-flex align-items-start">
                <i class="bi bi-exclamation-triangle-fill fs-3 me-3 text-warning"></i>
                <div class="flex-grow-1">
                    <h6 class="mb-1 fw-bold">⚠️ Plan downgradeado</h6>
                    <p class="mb-1 small">
                        Tu suscripción <strong>${planName}</strong> ha expirado o fue cancelada.
                        <br>
                        <span class="text-muted">Has perdido acceso a funcionalidades premium.</span>
                    </p>
                    <div class="d-flex gap-2 mt-2">
                        <a href="/profile" class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-right me-1"></i> Renovar ahora
                        </a>
                        <button class="btn btn-sm btn-outline-secondary" onclick="this.closest('.alert').remove()">
                            <i class="bi bi-x me-1"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

        const oldAlerts = document.querySelectorAll('.alert-dismissible');
        oldAlerts.forEach(function (alert) {
            alert.remove();
        });

        document.body.insertAdjacentHTML('beforeend', alertHtml);

        setTimeout(function () {
            const alert = document.querySelector('.alert-dismissible');
            if (alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function () {
                    alert.remove();
                }, 500);
            }
        }, 15000);
    }

    // =============================================
    // ACTUALIZAR BADGE DE DOWNGRADE
    // =============================================
    function updateDowngradeBadge(data) {
        const hasActive = data.has_active_subscription;
        const sub = data.subscription;
        const planKey = data.plan.key;
        const existingDowngrade = document.querySelector('.subscription-badge.downgrade-alert');
        const headerRight = document.querySelector('.modern-header-right');

        // ✅ Obtener plan anterior estricto de los datos del backend (No localStorage que se ensucia entre cuentas)
        let previousPlan = data.previous_plan || null;

        // ✅ Verificar downgrade (Solo si realmente tiene historial expired)
        const isExpired = sub && sub.status === 'expired';
        const isDowngraded = (!hasActive || isExpired) && previousPlan;
        const wasPaidPlan = previousPlan === 'basico' || previousPlan === 'premium';

        if (isDowngraded && wasPaidPlan) {
            if (!existingDowngrade && headerRight) {
                const planName = previousPlan === 'premium' ? 'Premium' : 'Básico';
                const newBadge = document.createElement('span');
                newBadge.className = 'subscription-badge downgrade-alert';
                newBadge.title = `⚠️ Tu suscripción ${planName} ha expirado. Algunas funcionalidades están limitadas.`;
                newBadge.onclick = function () { showDowngradeInfo(planName); };
                newBadge.innerHTML = `
                <span class="badge-dot expired"></span>
                <i class="bi bi-exclamation-triangle"></i>
                 Downgrade
                <span class="badge bg-danger ms-1" style="font-size: 0.5rem; padding: 0.1rem 0.3rem;">!</span>
            `;
                const subscriptionBadge = headerRight.querySelector('.subscription-badge:not(.downgrade-alert)');
                if (subscriptionBadge) {
                    subscriptionBadge.after(newBadge);
                } else {
                    headerRight.insertBefore(newBadge, headerRight.querySelector('.modern-header-user'));
                }
            }
        } else {
            if (existingDowngrade) {
                existingDowngrade.remove();
            }
        }
    }

    // ✅ ESCUCHAR EVENTO DE SUSCRIPCIÓN
    document.addEventListener('DOMContentLoaded', function () {
        document.addEventListener('subscriptionUpdated', function (event) {
            const data = event.detail;
            if (data) {
                updateDowngradeBadge(data);
            }
        });
    });

    window.showDowngradeInfo = showDowngradeInfo;
    window.updateDowngradeBadge = updateDowngradeBadge;
</script>