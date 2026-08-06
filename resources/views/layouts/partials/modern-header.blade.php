@php
    $userInitials = strtoupper(substr(auth()->user()->name ?? 'U', 0, 2));
    $userRole = auth()->user()->roles->pluck('name')->join(', ');
    
    // Obtener información de límites para badges
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

{{-- Contenedor de toasts para alertas de suscripción --}}
<div id="subscriptionToastContainer" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;"></div>

<script>
// Funcionalidad para mostrar alertas de suscripción como toasts
document.addEventListener('DOMContentLoaded', function() {
    @if($subscriptionInfo && isset($subscriptionInfo['limits']))
        // Configuración de toasts mostrados
        const shownToasts = JSON.parse(localStorage.getItem('shownSubscriptionToasts') || '[]');
        const toastContainer = document.getElementById('subscriptionToastContainer');
        
        // Función para crear y mostrar un toast
        function showToast(title, message, type, icon, linkText, linkUrl) {
            const toastId = 'toast_' + type;
            
            // Verificar si ya se mostró este toast
            if (shownToasts.includes(toastId)) {
                return;
            }
            
            const toastElement = document.createElement('div');
            toastElement.className = 'toast align-items-center text-white border-0';
            toastElement.setAttribute('role', 'alert');
            toastElement.setAttribute('aria-live', 'assertive');
            toastElement.setAttribute('aria-atomic', 'true');
            toastElement.setAttribute('data-bs-autohide', 'false');
            toastElement.id = toastId;
            
            // Determinar clase de color según el tipo
            const bgClass = type === 'danger' ? 'bg-danger' : 
                           type === 'warning' ? 'bg-warning text-dark' : 
                           type === 'success' ? 'bg-success' : 'bg-info';
            
            // Crear el HTML del toast
            let toastHTML = `
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center">
                        <span class="me-2">${icon}</span>
                        <div>
                            <strong>${title}</strong>
                            <div class="mt-1">${message}</div>
        `;
            
            // Agregar enlace si existe
            if (linkText && linkUrl) {
                toastHTML += `<a href="${linkUrl}" class="alert-link mt-1 d-inline-block">${linkText}</a>`;
            }
            
            toastHTML += `
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close" onclick="markToastAsShown('${toastId}')"></button>
                </div>
            `;
            
            toastElement.className += ' ' + bgClass;
            toastElement.innerHTML = toastHTML;
            
            // Agregar toast al contenedor
            toastContainer.appendChild(toastElement);
            
            // Inicializar el toast con Bootstrap
            const toast = new bootstrap.Toast(toastElement, { autohide: false });
            toast.show();
            
            // Marcar como mostrado cuando se cierra
            toastElement.addEventListener('hidden.bs.toast', function() {
                markToastAsShown('${toastId}');
            });
        }
        
        // Función para marcar toast como mostrado
        function markToastAsShown(toastId) {
            let shown = JSON.parse(localStorage.getItem('shownSubscriptionToasts') || '[]');
            if (!shown.includes(toastId)) {
                shown.push(toastId);
                localStorage.setItem('shownSubscriptionToasts', JSON.stringify(shown));
            }
        }
        
        // Mostrar toasts según el estado de suscripción
        @if(!$subscriptionInfo['has_active_subscription'])
            showToast(
                '📋 Sin suscripción activa',
                'Estás usando el plan {{ $subscriptionInfo["plan"]["name"] }} con límites básicos.',
                'warning',
                '<i class="bi bi-exclamation-triangle"></i>',
                'Activa tu suscripción',
                '/profile'
            );
        @endif
        
        @if(!$subscriptionInfo['limits']['sensors']['is_unlimited'] && $subscriptionInfo['limits']['sensors']['remaining'] <= 1)
            showToast(
                '{{ $subscriptionInfo["limits"]["sensors"]["remaining"] === 0 ? "❌ Límite de sensores alcanzado" : "⚠️ Límite de sensores casi alcanzado" }}',
                'Has usado <strong>{{ $subscriptionInfo["limits"]["sensors"]["used"] }}</strong> de <strong>{{ $subscriptionInfo["limits"]["sensors"]["max"] }}</strong>. {{ $subscriptionInfo["limits"]["sensors"]["remaining"] === 0 ? "No puedes crear más sensores." : "Te queda solo 1 sensor." }}',
                '{{ $subscriptionInfo["limits"]["sensors"]["remaining"] === 0 ? "danger" : "warning" }}',
                '<i class="bi bi-{{ $subscriptionInfo["limits"]["sensors"]["remaining"] === 0 ? "exclamation-circle" : "exclamation-triangle" }}"></i>',
                'Actualiza tu plan',
                '/profile'
            );
        @endif
        
        @if(!$subscriptionInfo['limits']['groups']['is_unlimited'] && $subscriptionInfo['limits']['groups']['remaining'] <= 1)
            showToast(
                '{{ $subscriptionInfo["limits"]["groups"]["remaining"] === 0 ? "❌ Límite de grupos alcanzado" : "⚠️ Límite de grupos casi alcanzado" }}',
                'Has usado <strong>{{ $subscriptionInfo["limits"]["groups"]["used"] }}</strong> de <strong>{{ $subscriptionInfo["limits"]["groups"]["max"] }}</strong>. {{ $subscriptionInfo["limits"]["groups"]["remaining"] === 0 ? "No puedes crear más grupos." : "Te queda solo 1 grupo." }}',
                '{{ $subscriptionInfo["limits"]["groups"]["remaining"] === 0 ? "danger" : "warning" }}',
                '<i class="bi bi-{{ $subscriptionInfo["limits"]["groups"]["remaining"] === 0 ? "exclamation-circle" : "exclamation-triangle" }}"></i>',
                'Actualiza tu plan',
                '/profile'
            );
        @endif
        
        // Estilos para los toasts
        const style = document.createElement('style');
        style.textContent = `
            .toast-container {
                max-width: 350px;
            }
            .toast {
                min-width: 300px;
                font-size: 0.9rem;
            }
            .toast .alert-link {
                font-weight: 600;
                color: inherit;
                text-decoration: none;
            }
            .toast .alert-link:hover {
                text-decoration: underline;
            }
            .toast.warning .alert-link {
                color: #000 !important;
            }
            .btn-close-white {
                filter: brightness(1.5);
            }
        `;
        document.head.appendChild(style);
    @endif
});
</script>
