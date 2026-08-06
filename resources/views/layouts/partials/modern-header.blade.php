@php
    $userInitials = strtoupper(substr(auth()->user()->name ?? 'U', 0, 2));
    $userRole = auth()->user()->roles->pluck('name')->join(', ');
    
    // Obtener información de suscripción para el badge
    $subscriptionInfo = null;
    if (auth()->check()) {
        $subscriptionService = app(\App\Services\Subscription\SubscriptionService::class, ['user' => auth()->user()]);
        $subscriptionInfo = $subscriptionService->getFullStatus();
    }
@endphp

<header class="modern-header">
    <div class="modern-header-left">
        {{-- ✅ BOTÓN HAMBURGUESA - SOLO VISIBLE EN MÓVIL --}}
        <button class="modern-header-toggle" id="sidebarToggle" title="Abrir menú">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="modern-header-title">@yield('title', 'MedFlow')</h1>
    </div>
    
    <div class="modern-header-right">
        @if($subscriptionInfo)
        <span class="subscription-badge {{ $subscriptionInfo['plan']['key'] === 'premium' ? 'premium' : ($subscriptionInfo['plan']['key'] === 'basico' ? 'basico' : ($subscriptionInfo['has_active_subscription'] ? 'free' : 'expired')) }}" 
              title="{{ $subscriptionInfo['has_active_subscription'] ? 'Suscripción activa - Plan ' . $subscriptionInfo['plan']['name'] : 'Sin suscripción activa' }}">
            @if($subscriptionInfo['has_active_subscription'])
                <span class="badge-dot active"></span>
            @else
                <span class="badge-dot expired"></span>
            @endif
            <i class="bi bi-{{ $subscriptionInfo['plan']['key'] === 'premium' ? 'star-fill' : ($subscriptionInfo['plan']['key'] === 'basico' ? 'credit-card' : 'gift') }}"></i>
            {{ $subscriptionInfo['plan']['key'] === 'premium' ? '✨ Premium' : ($subscriptionInfo['plan']['key'] === 'basico' ? '📋 Básico' : ($subscriptionInfo['has_active_subscription'] ? '🎁 Free' : '⚠️ Sin suscripción')) }}
        </span>
        @endif
        
        <a href="{{ route('profile.index') }}" class="modern-header-user">
            <div class="modern-header-user-avatar">{{ $userInitials }}</div>
            <div>
                <div class="modern-header-user-name">{{ auth()->user()->name }}</div>
                <div class="modern-header-user-role">{{ $userRole }}</div>
            </div>
        </a>
        <button class="modern-header-logout" onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();" title="Cerrar sesión">
            <i class="bi bi-box-arrow-right"></i>
        </button>
        <form id="logout-form-header" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</header>
