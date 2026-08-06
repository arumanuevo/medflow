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
        {{-- \u2705 BOTÓN HAMBURGUESA - SOLO VISIBLE EN MÓVIL --}}
        <button class="modern-header-toggle" id="sidebarToggle" title="Abrir menú">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="modern-header-title">@yield('title', 'MedFlow')</h1>
    </div>
    
    <div class="modern-header-right">
        {{-- Badges de alerta de suscripción --}}
        @if($subscriptionInfo && isset($subscriptionInfo['limits']))
            <div class="modern-header-alerts dropdown" style="margin-right: 1rem;">
                <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="alertsDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Alertas de suscripción">
                    <i class="bi bi-bell"></i>
                    @php
                        $alertCount = 0;
                        if (!$subscriptionInfo['limits']['sensors']['is_unlimited'] && $subscriptionInfo['limits']['sensors']['remaining'] <= 1) $alertCount++;
                        if (!$subscriptionInfo['limits']['groups']['is_unlimited'] && $subscriptionInfo['limits']['groups']['remaining'] <= 1) $alertCount++;
                        if (!$subscriptionInfo['has_active_subscription']) $alertCount++;
                    @endphp
                    @if($alertCount > 0)
                        <span class="badge bg-danger ms-1" style="font-size: 0.65rem; padding: 0.2rem 0.45rem;">{{ $alertCount }}</span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="alertsDropdown" style="min-width: 300px;">
                    <li class="dropdown-header">
                        <i class="bi bi-info-circle me-2"></i>
                        Estado de Suscripción
                    </li>
                    
                    @if(!$subscriptionInfo['has_active_subscription'])
                        <li>
                            <div class="dropdown-item-text d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge bg-warning text-dark me-2">!</span>
                                    <strong>Sin suscripción activa</strong>
                                    <small class="d-block">Estás usando el plan <strong>{{ $subscriptionInfo['plan']['name'] }}</strong> con límites básicos.</small>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary dismiss-alert" data-alert-type="subscription" style="padding: 0.2rem 0.4rem; font-size: 0.7rem;">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                    @endif
                    
                    @if(!$subscriptionInfo['limits']['sensors']['is_unlimited'] && $subscriptionInfo['limits']['sensors']['remaining'] <= 1)
                        <li>
                            <div class="dropdown-item-text d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge bg-{{ $subscriptionInfo['limits']['sensors']['remaining'] === 0 ? 'danger' : 'warning' }} me-2">
                                        {{ $subscriptionInfo['limits']['sensors']['remaining'] === 0 ? '❌' : '⚠️' }}
                                    </span>
                                    <strong>Límite de sensores</strong>
                                    <small class="d-block">Has usado <strong>{{ $subscriptionInfo['limits']['sensors']['used'] }}</strong> de <strong>{{ $subscriptionInfo['limits']['sensors']['max'] }}</strong>.
                                    @if($subscriptionInfo['limits']['sensors']['remaining'] === 0)
                                        <a href="/profile" class="alert-link">Actualizar plan</a>
                                    @else
                                        Te queda <strong>{{ $subscriptionInfo['limits']['sensors']['remaining'] }}</strong>.
                                    @endif
                                    </small>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary dismiss-alert" data-alert-type="sensor" style="padding: 0.2rem 0.4rem; font-size: 0.7rem;">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </li>
                    @endif
                    
                    @if(!$subscriptionInfo['limits']['groups']['is_unlimited'] && $subscriptionInfo['limits']['groups']['remaining'] <= 1)
                        <li>
                            <div class="dropdown-item-text d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge bg-{{ $subscriptionInfo['limits']['groups']['remaining'] === 0 ? 'danger' : 'warning' }} me-2">
                                        {{ $subscriptionInfo['limits']['groups']['remaining'] === 0 ? '❌' : '⚠️' }}
                                    </span>
                                    <strong>Límite de grupos</strong>
                                    <small class="d-block">Has usado <strong>{{ $subscriptionInfo['limits']['groups']['used'] }}</strong> de <strong>{{ $subscriptionInfo['limits']['groups']['max'] }}</strong>.
                                    @if($subscriptionInfo['limits']['groups']['remaining'] === 0)
                                        <a href="/profile" class="alert-link">Actualizar plan</a>
                                    @else
                                        Te queda <strong>{{ $subscriptionInfo['limits']['groups']['remaining'] }}</strong>.
                                    @endif
                                    </small>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary dismiss-alert" data-alert-type="group" style="padding: 0.2rem 0.4rem; font-size: 0.7rem;">
                                    <i class="bi bi-x"></i>
                                </button>
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
                                Gestionar suscripción
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
</style>

<script>
// Funcionalidad para descartar alertas del dropdown (Vanilla JS)
document.addEventListener('DOMContentLoaded', function() {
    // Función para ocultar alerta con animación
    function dismissAlert(button) {
        const alertType = button.dataset.alertType;
        const listItem = button.closest('li');
        
        // Animación de fade out
        listItem.style.transition = 'opacity 0.5s ease';
        listItem.style.opacity = '0';
        
        setTimeout(function() {
            listItem.remove();
            updateAlertCounter();
            saveDismissedAlert(alertType);
            
            // Si no quedan alertas, mostrar mensaje "Todo en orden"
            const remainingAlerts = document.querySelectorAll('.modern-header-alerts .dropdown-item-text:not([style*="display: none"]):not([style*="opacity: 0"])');
            if (remainingAlerts.length === 0) {
                const dropdownMenu = document.querySelector('.modern-header-alerts .dropdown-menu');
                // Verificar si ya existe el mensaje
                if (!dropdownMenu.querySelector('.dropdown-header.text-success')) {
                    const header = document.createElement('li');
                    header.className = 'dropdown-header text-success';
                    header.innerHTML = '<i class="bi bi-check-circle me-2"></i> Todo en orden';
                    dropdownMenu.prepend(header);
                }
            }
        }, 500);
    }

    // ✅ EXTENDER TIEMPO DE AUTO-DISMISS A 30 SEGUNDOS (era 10)
    function scheduleAutoDismiss() {
        const alerts = document.querySelectorAll('.modern-header-alerts .dropdown-item-text:not([style*="display: none"])');
        alerts.forEach(function(alert) {
            const listItem = alert.closest('li');
            // Solo programar si no tiene ya un timeout programado
            if (!listItem.dataset.dismissTimeout) {
                const timeout = setTimeout(function() {
                    // Verificar si la alerta sigue visible
                    if (listItem.style.display !== 'none' && listItem.style.opacity !== '0') {
                        const button = listItem.querySelector('.dismiss-alert');
                        if (button) {
                            dismissAlert(button);
                        }
                    }
                }, 30000); // ✅ 30 segundos en lugar de 10
                
                listItem.dataset.dismissTimeout = timeout;
            }
        });
    }
    // Actualizar el contador de alertas en el badge
    function updateAlertCounter() {
        // ✅ Contar SOLO las alertas visibles (no ocultas por CSS o display:none)
        const allAlertItems = document.querySelectorAll('.modern-header-alerts .dropdown-item-text');
        let visibleCount = 0;
        
        allAlertItems.forEach(function(item) {
            const li = item.closest('li');
            // ✅ Verificar que el elemento sea visible
            if (li) {
                const isHidden = li.style.display === 'none' || 
                            li.style.opacity === '0' ||
                            li.classList.contains('d-none') ||
                            li.style.visibility === 'hidden';
                
                if (!isHidden && li.offsetParent !== null) {
                    visibleCount++;
                }
            }
        });
        
        const badge = document.querySelector('.modern-header-alerts .badge');
        
        if (badge) {
            if (visibleCount === 0) {
                badge.style.display = 'none';
                badge.textContent = '0';
            } else {
                badge.textContent = visibleCount;
                badge.style.display = 'inline-block';
            }
        }
    }


    // Guardar alerta descartada en localStorage
    function saveDismissedAlert(alertType) {
        let dismissedAlerts = JSON.parse(localStorage.getItem('dismissedSubscriptionAlerts') || '[]');
        
        if (!dismissedAlerts.includes(alertType)) {
            dismissedAlerts.push(alertType);
            localStorage.setItem('dismissedSubscriptionAlerts', JSON.stringify(dismissedAlerts));
        }
    }

    // Verificar alertas descartadas al cargar la página
    function checkDismissedAlerts() {
        const dismissedAlerts = JSON.parse(localStorage.getItem('dismissedSubscriptionAlerts') || '[]');
        
        dismissedAlerts.forEach(function(alertType) {
            const buttons = document.querySelectorAll('.modern-header-alerts .dismiss-alert[data-alert-type="' + alertType + '"]');
            buttons.forEach(function(button) {
                const listItem = button.closest('li');
                if (listItem) {
                    listItem.style.display = 'none';
                    listItem.style.opacity = '0';
                }
            });
        });
        
        // ✅ Actualizar contador después de ocultar las alertas
        setTimeout(function() {
            updateAlertCounter();
        }, 100);
    }

    // Agregar event listeners a los botones de cierre
    const dismissButtons = document.querySelectorAll('.modern-header-alerts .dismiss-alert');
    dismissButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            // Limpiar timeout programado
            const listItem = this.closest('li');
            if (listItem && listItem.dataset.dismissTimeout) {
                clearTimeout(parseInt(listItem.dataset.dismissTimeout));
                delete listItem.dataset.dismissTimeout;
            }
            dismissAlert(this);
        });
    });

    // Inicializar al cargar la página
    checkDismissedAlerts();
    scheduleAutoDismiss();
     // ✅ También programar auto-dismiss para nuevas alertas (con MutationObserver)
     const dropdownMenu = document.querySelector('.modern-header-alerts .dropdown-menu');
    if (dropdownMenu) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length > 0) {
                    scheduleAutoDismiss();
                }
            });
        });
        observer.observe(dropdownMenu, { childList: true, subtree: true });
    }
});
</script>
