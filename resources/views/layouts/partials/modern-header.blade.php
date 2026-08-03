@php
    $userInitials = strtoupper(substr(auth()->user()->name ?? 'U', 0, 2));
    $userRole = auth()->user()->roles->pluck('name')->join(', ');
    
    // Obtener información de límites para badges
    $subscriptionInfo = null;
    if (auth()->check()) {
        $subscriptionInfo = \App\Services\Subscription\SubscriptionService::getSubscriptionInfo(auth()->user());
    }
@endphp

<header class="modern-header">
    <div class="modern-header-left">
        {{-- \u2705 BOT\u00d3N HAMBURGUESA - SOLO VISIBLE EN M\u00d3VIL --}}
        <button class="modern-header-toggle" id="sidebarToggle" title="Abrir men\u00fa">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="modern-header-title">@yield('title', 'MedFlow')</h1>
    </div>
    
    <div class="modern-header-right">
        {{-- Badges de alerta de suscripci\u00f3n --}}
        @if($subscriptionInfo && isset($subscriptionInfo['limits']))
            <div class="modern-header-alerts dropdown" style="margin-right: 1rem;">
                <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="alertsDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Alertas de suscripci\u00f3n">
                    <i class="bi bi-bell"></i>
                    @php
                        $alertCount = 0;
                        if (!$subscriptionInfo['limits']['sensors']['is_unlimited'] && $subscriptionInfo['limits']['sensors']['remaining'] <= 1) $alertCount++;
                        if (!$subscriptionInfo['limits']['groups']['is_unlimited'] && $subscriptionInfo['limits']['groups']['remaining'] <= 1) $alertCount++;
                        if (!$subscriptionInfo['plan']['is_active']) $alertCount++;
                    @endphp
                    @if($alertCount > 0)
                        <span class="badge bg-danger ms-1">{{ $alertCount }}</span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="alertsDropdown" style="min-width: 300px;">
                    <li class="dropdown-header">
                        <i class="bi bi-info-circle me-2"></i>
                        Estado de Suscripci\u00f3n
                    </li>
                    
                    @if(!$subscriptionInfo['plan']['is_active'])
                        <li>
                            <div class="dropdown-item-text">
                                <span class="badge bg-warning text-dark me-2">!</span>
                                <strong>Sin suscripci\u00f3n activa</strong><br>
                                <small>Est\u00e1s usando el plan <strong>{{ $subscriptionInfo['plan']['name'] }}</strong> con l\u00edmites b\u00e1sicos.</small>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                    @endif
                    
                    @if(!$subscriptionInfo['limits']['sensors']['is_unlimited'] && $subscriptionInfo['limits']['sensors']['remaining'] <= 1)
                        <li>
                            <div class="dropdown-item-text">
                                <span class="badge bg-{{ $subscriptionInfo['limits']['sensors']['remaining'] === 0 ? 'danger' : 'warning' }} me-2">
                                    {{ $subscriptionInfo['limits']['sensors']['remaining'] === 0 ? '\u274c' : '\u26a0\ufe0f' }}
                                </span>
                                <strong>L\u00edmite de sensores</strong><br>
                                <small>Has usado <strong>{{ $subscriptionInfo['limits']['sensors']['used'] }}</strong> de <strong>{{ $subscriptionInfo['limits']['sensors']['max'] }}</strong>.
                                @if($subscriptionInfo['limits']['sensors']['remaining'] === 0)
                                    <a href="/profile" class="alert-link">Actualizar plan</a>
                                @else
                                    Te queda <strong>{{ $subscriptionInfo['limits']['sensors']['remaining'] }}</strong>.
                                @endif
                                </small>
                            </div>
                        </li>
                    @endif
                    
                    @if(!$subscriptionInfo['limits']['groups']['is_unlimited'] && $subscriptionInfo['limits']['groups']['remaining'] <= 1)
                        <li>
                            <div class="dropdown-item-text">
                                <span class="badge bg-{{ $subscriptionInfo['limits']['groups']['remaining'] === 0 ? 'danger' : 'warning' }} me-2">
                                    {{ $subscriptionInfo['limits']['groups']['remaining'] === 0 ? '\u274c' : '\u26a0\ufe0f' }}
                                </span>
                                <strong>L\u00edmite de grupos</strong><br>
                                <small>Has usado <strong>{{ $subscriptionInfo['limits']['groups']['used'] }}</strong> de <strong>{{ $subscriptionInfo['limits']['groups']['max'] }}</strong>.
                                @if($subscriptionInfo['limits']['groups']['remaining'] === 0)
                                    <a href="/profile" class="alert-link">Actualizar plan</a>
                                @else
                                    Te queda <strong>{{ $subscriptionInfo['limits']['groups']['remaining'] }}</strong>.
                                @endif
                                </small>
                            </div>
                        </li>
                    @endif
                    
                    @if($alertCount === 0)
                        <li class="dropdown-header text-success">
                            <i class="bi bi-check-circle me-2"></i>
                            Todo en orden
                        </li>
                    @else
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="/profile">
                                <i class="bi bi-gear me-2"></i>
                                Gestionar suscripci\u00f3n
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif
        
        <a href="{{ route('profile.index') }}" class="modern-header-user">
            <div class="modern-header-user-avatar">{{ $userInitials }}</div>
            <div>
                <div class="modern-header-user-name">{{ auth()->user()->name }}</div>
                <div class="modern-header-user-role">{{ $userRole }}</div>
            </div>
        </a>
        <button class="modern-header-logout" onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();" title="Cerrar sesi\u00f3n">
            <i class="bi bi-box-arrow-right"></i>
        </button>
        <form id="logout-form-header" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</header>

<style>
    .modern-header-alerts .dropdown-toggle {
        padding: 0.4rem 0.6rem;
        font-size: 1.1rem;
        color: #6c757d;
        border: none;
    }
    .modern-header-alerts .dropdown-toggle:hover {
        color: #0d6efd;
        background: rgba(13, 110, 253, 0.1);
    }
    .modern-header-alerts .dropdown-menu {
        font-size: 0.85rem;
    }
    .modern-header-alerts .dropdown-header {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .modern-header-alerts .dropdown-item-text {
        padding: 0.5rem 1rem;
        white-space: normal;
    }
    .modern-header-alerts .alert-link {
        font-weight: 600;
        color: #0d6efd;
        text-decoration: none;
    }
    .modern-header-alerts .alert-link:hover {
        text-decoration: underline;
    }
</style>
