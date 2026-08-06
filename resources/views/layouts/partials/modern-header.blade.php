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
        <button class="modern-header-toggle" id="sidebarToggle" title="Abrir menú">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="modern-header-title">@yield('title', 'MedFlow')</h1>
    </div>
    
    <div class="modern-header-right">
        {{-- Badge de suscripción (se actualiza vía JavaScript) --}}
        <span class="subscription-badge" id="headerSubscriptionBadge">
            <span class="badge-dot"></span>
            <i class="bi bi-hourglass-split"></i>
            <span id="headerPlanLabel">Cargando...</span>
        </span>

        {{-- Campanita de alertas --}}
        <div class="modern-header-alerts dropdown" style="margin-right: 0.5rem;">
            <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="alertsDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Alertas">
                <i class="bi bi-bell"></i>
                <span class="badge bg-danger ms-1 d-none" id="alertBadge" style="font-size: 0.65rem; padding: 0.2rem 0.45rem;">0</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="alertsDropdown" style="min-width: 320px;">
                <li class="dropdown-header">
                    <i class="bi bi-info-circle me-2"></i>
                    Alertas del sistema
                </li>
                <li id="noAlertsMessage" class="dropdown-header text-success">
                    <i class="bi bi-check-circle me-2"></i>
                    Todo en orden
                </li>
                <div id="alertsContainer"></div>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.index') }}">
                        <i class="bi bi-gear me-2"></i>
                        Gestionar suscripción
                    </a>
                </li>
            </ul>
        </div>
        
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
    .modern-header-alerts .alert-item {
        padding: 0.5rem 1rem;
        border-bottom: 1px solid #f0f0f0;
    }
    .modern-header-alerts .alert-item:last-child {
        border-bottom: none;
    }
    .modern-header-alerts .alert-item .alert-text {
        font-size: 0.85rem;
    }
    .modern-header-alerts .alert-item .alert-close {
        padding: 0.1rem 0.3rem;
        font-size: 0.7rem;
        border: none;
        background: transparent;
        color: #6c757d;
        cursor: pointer;
    }
    .modern-header-alerts .alert-item .alert-close:hover {
        color: #dc3545;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // =============================================
    // SISTEMA DE ALERTAS SIMPLIFICADO
    // =============================================
    
    // Eliminar alertas obsoletas al cargar
    localStorage.removeItem('dismissedSubscriptionAlerts');
    
    // Función para agregar alerta al dropdown
    window.addAlertToDropdown = function(message, type = 'info', persistent = false) {
        const container = document.getElementById('alertsContainer');
        if (!container) return;
        
        // Ocultar mensaje "Todo en orden"
        const noAlerts = document.getElementById('noAlertsMessage');
        if (noAlerts) noAlerts.style.display = 'none';
        
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
        
        const alertId = 'alert_' + Date.now();
        const alertHtml = `
            <div class="alert-item" id="${alertId}" data-persistent="${persistent}">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="alert-text">
                        <span class="badge ${colorMap[type] || 'bg-secondary'} me-2">
                            <i class="bi ${iconMap[type] || 'bi-info-circle'}"></i>
                        </span>
                        ${message}
                    </div>
                    <button class="alert-close" onclick="dismissAlert('${alertId}')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', alertHtml);
        updateBadgeCount();
        
        // Auto-dismiss después de 30 segundos (solo si no es persistente)
        if (!persistent) {
            setTimeout(function() {
                const element = document.getElementById(alertId);
                if (element) {
                    element.style.transition = 'opacity 0.5s';
                    element.style.opacity = '0';
                    setTimeout(function() {
                        if (element.parentNode) {
                            element.remove();
                            updateBadgeCount();
                        }
                    }, 500);
                }
            }, 30000);
        }
    };
    
    // Función para descartar alerta manualmente
    window.dismissAlert = function(alertId) {
        const element = document.getElementById(alertId);
        if (element) {
            element.style.transition = 'opacity 0.3s';
            element.style.opacity = '0';
            setTimeout(function() {
                if (element.parentNode) {
                    element.remove();
                    updateBadgeCount();
                }
            }, 300);
        }
    };
    
    // Actualizar contador de la campanita
    // En modern-header.blade.php - CORREGIR updateBadgeCount
    function updateBadgeCount() {
        const container = document.getElementById('alertsContainer');
        const badge = document.getElementById('alertBadge');
        const noAlerts = document.getElementById('noAlertsMessage');
        
        if (!container || !badge) return;
        
        // ✅ Contar SOLO alertas visibles (no ocultas)
        const visibleAlerts = container.querySelectorAll('.alert-item:not([style*="display: none"]):not([style*="opacity: 0"])');
        const count = visibleAlerts.length;
        
        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('d-none');
            badge.style.display = 'inline-block';
            if (noAlerts) noAlerts.style.display = 'none';
        } else {
            badge.classList.add('d-none');
            badge.style.display = 'none';
            if (noAlerts) noAlerts.style.display = 'block';
        }
    }
    
    // Exponer función para que otros scripts la usen
    window.addAlert = window.addAlertToDropdown;
    
    // Inicializar
    updateBadgeCount();
});
</script>