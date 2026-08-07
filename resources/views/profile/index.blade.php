@extends('layouts.modern')

@section('title', 'Mi Perfil - MedFlow')

@push('styles')
<style>
    /* Estilos existentes */
    /* Estilo para el boton close blanco en modal de peligro */
    .btn-close-white {
        filter: brightness(0) invert(1);
    }

    /* ✅ Estilos para la sección de suscripción */
    #subscriptionStatus .btn-outline-primary:hover {
        background: #0d6efd;
        color: #fff;
    }
    #subscriptionStatus .btn-warning {
        color: #000;
        border-color: #ffc107;
    }
    #subscriptionStatus .btn-warning:hover {
        background: #e0a800;
        border-color: #d39e00;
    }
    
    /* ✅ Estilos para el contador regresivo */
    .countdown-timer {
        font-size: 1.2rem;
        font-weight: 600;
        color: #0d6efd;
        background: #e9ecef;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        display: inline-block;
    }
    .countdown-timer.expiring {
        color: #dc3545;
        animation: pulse 1s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    /* ✅ Badge de estado */
    .status-badge {
        font-size: 0.85rem;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
    }
    .status-badge.active {
        background: #d4edda;
        color: #155724;
    }
    .status-badge.expired {
        background: #f8d7da;
        color: #721c24;
    }
    .status-badge.pending {
        background: #fff3cd;
        color: #856404;
    }
    
    /* ✅ Botones de depuración */
    .debug-btn {
        border: 2px dashed #6c757d;
        background: #f8f9fa;
        transition: all 0.3s;
    }
    .debug-btn:hover {
        background: #e9ecef;
        border-color: #0d6efd;
    }
    .debug-section {
        background: #f8f9fa;
        border: 2px dashed #6c757d;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }
    .debug-section .badge {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Estilos para el modal de confirmación */
    #confirmDeleteAllDataModal .modal-header {
        border-bottom: none;
    }
    #confirmDeleteAllDataModal .modal-footer {
        border-top: none;
    }
    #confirmDeleteAllDataModal .form-control:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    /* ✅ Estilos para la sección de suscripción mejorada */
    #subscriptionStatus .card {
        border-radius: 10px;
        overflow: hidden;
    }
    #subscriptionStatus .card-header {
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
    }
    #subscriptionStatus .card-body {
        padding: 0.8rem 1rem;
    }
    #subscriptionStatus .card-body .text-muted {
        font-size: 0.75rem;
    }
    #subscriptionStatus .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.6rem;
    }
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-user-circle"></i> Mi Perfil</h4>
                    <span class="badge bg-light text-dark" id="userRole">
                        <i class="fas fa-id-badge"></i> Cargando...
                    </span>
                </div>
                <div class="card-body">
                    <!-- Alertas -->
                    <div id="alertContainer"></div>

                    <!-- Formulario -->
                    <form id="profileForm">
                        @csrf
                        @method('PUT')

                        <input type="hidden" id="hasGoogleId" value="{{ $user->google_id ? 'true' : 'false' }}">

                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre completo</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="subscription_type" class="form-label">Tipo de cuenta</label>
                            <select class="form-select" id="subscription_type" name="subscription_type">
                                <option value="domiciliario">Domiciliario</option>
                                <option value="corporativo">Corporativo</option>
                            </select>
                            <small class="text-muted">Cambiar el tipo de cuenta actualizará tus permisos automáticamente.</small>
                        </div>

                        {{-- Google Info --}}
                        <div id="googleInfo" class="mb-3 d-none">
                            <div class="alert alert-info">
                                <i class="fas fa-google"></i> 
                                Esta cuenta está vinculada con Google. No necesitas cambiar tu contraseña.
                            </div>
                        </div>

                        {{-- Password Fields --}}
                        <div id="passwordFields" class="{{ $user->google_id ? 'd-none' : '' }}">
                            <hr>
                            <h5>Cambiar contraseña</h5>
                            <div class="mb-3">
                                <label for="password" class="form-label">Nueva contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Dejar en blanco para no cambiar">
                                <small class="text-muted">Mínimo 8 caracteres.</small>
                            </div>
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirmar nueva contraseña</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Repite la nueva contraseña">
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                                <i class="fas fa-save"></i> Guardar cambios
                            </button>
                            <button type="button" class="btn btn-info" id="refreshStatsBtn">
                                <i class="fas fa-chart-bar"></i> Actualizar estadísticas
                            </button>
                        </div>
                    </form>

                    <hr>
                    <div class="mt-3" id="userStats">
                        <h6>Estadísticas de la cuenta</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 id="totalSensors">-</h5>
                                        <small>Sensores</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 id="totalMeasurements">-</h5>
                                        <small>Mediciones</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 id="totalGroups">-</h5>
                                        <small>Grupos</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <h6>Información de la cuenta</h6>
                        <ul class="list-unstyled">
                            <li><strong>ID:</strong> <span id="userId">-</span></li>
                            <li><strong>Plan:</strong> <span id="userPlan">-</span></li>
                            <li><strong>Fecha de registro:</strong> <span id="userCreatedAt">-</span></li>
                            <li><strong>Última actualización:</strong> <span id="userUpdatedAt">-</span></li>
                        </ul>
                    </div>

                    {{-- ✅ SECCIÓN DE SUSCRIPCIÓN --}}
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0"><i class="bi bi-credit-card"></i> Suscripción</h6>
                            <span class="badge bg-secondary" id="subscriptionEnv">
                                <i class="bi bi-tag"></i> {{ app()->environment() }}
                            </span>
                        </div>
                        
                        <div id="subscriptionStatus">
                            <div class="d-flex align-items-center gap-2">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                <span>Cargando estado de suscripción...</span>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN DE INFORMACIÓN DE PLANES --}}
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0"><i class="bi bi-info-circle"></i> Alcances de los Planes</h6>
                        </div>
                        <div class="row g-2">
                            <!-- Plan Free -->
                            <div class="col-md-4">
                                <div class="card h-100 border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="bi bi-gift"></i> Plan Free</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled mb-0">
                                            <li class="d-flex align-items-center mb-1">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <small>1 grupo máximo</small>
                                            </li>
                                            <li class="d-flex align-items-center mb-1">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <small>1 sensor máximo</small>
                                            </li>
                                            <li class="d-flex align-items-center mb-1">
                                                <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                                <small>Sin colaboradores</small>
                                            </li>
                                            <li class="d-flex align-items-center mb-1">
                                                <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                                <small>Sin importación masiva</small>
                                            </li>
                                            <li class="d-flex align-items-center mb-1">
                                                <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                                <small>Mediciones limitadas</small>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <!-- Plan Básico -->
                            <div class="col-md-4">
                                <div class="card h-100 border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="bi bi-gem"></i> Plan Básico</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled mb-0">
                                            <li class="d-flex align-items-center mb-1">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <small>2 grupos máximo</small>
                                            </li>
                                            <li class="d-flex align-items-center mb-1">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <small>2 sensores máximo</small>
                                            </li>
                                            <li class="d-flex align-items-center mb-1">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <small>1 colaborador</small>
                                            </li>
                                            <li class="d-flex align-items-center mb-1">
                                                <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                                <small>Sin importación masiva</small>
                                            </li>
                                            <li class="d-flex align-items-center mb-1">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <small>Mediciones ilimitadas</small>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <!-- Plan Premium -->
                            <div class="col-md-4">
                                <div class="card h-100 border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="bi bi-stars"></i> Plan Premium</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled mb-0">
                                            <li class="d-flex align-items-center mb-1">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <small>Grupos ilimitados</small>
                                            </li>
                                            <li class="d-flex align-items-center mb-1">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <small>Sensores ilimitados</small>
                                            </li>
                                            <li class="d-flex align-items-center mb-1">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <small>Colaboradores ilimitados</small>
                                            </li>
                                            <li class="d-flex align-items-center mb-1">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <small>Importación masiva</small>
                                            </li>
                                            <li class="d-flex align-items-center mb-1">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <small>Todas las funciones</small>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                  

                    {{-- SECCIÓN DE ELIMINACIÓN DE DATOS --}}
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 text-danger">
                                <i class="bi bi-exclamation-triangle-fill"></i> 
                                Eliminar todos mis datos
                            </h6>
                        </div>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>TODOS</strong> tus datos: sensores, mediciones, fotos asociadas, grupos, 
                            colaboraciones, suscripciones y configuraciones. 
                            <strong>No podrás recuperar esta información.</strong>
                        </div>
                        <button type="button" class="btn btn-danger w-100" id="deleteAllDataBtn">
                            <i class="bi bi-trash-fill me-2"></i> 
                            Eliminar todos mis datos
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar todos los datos -->
<div class="modal fade" id="confirmDeleteAllDataModal" tabindex="-1" aria-labelledby="confirmDeleteAllDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDeleteAllDataModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> 
                    ¿Estás seguro de que deseas eliminar TODOS tus datos?
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>¡Advertencia: Esta acción eliminará TODOS tus datos:</strong> sensores, mediciones, fotos asociadas, grupos, colaboraciones, suscripciones y configuraciones. No podrás recuperar esta información.
                </div>
                <p>Se eliminarán:</p>
                <ul>
                    <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Todos tus sensores</li>
                    <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Todas tus mediciones</li>
                    <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Todas las fotos asociadas a mediciones</li>
                    <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Todos tus grupos de sensores</li>
                    <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Todas tus colaboraciones y accesos compartidos</li>
                    <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Tus suscripciones</li>
                    <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Tus configuraciones personales</li>
                </ul>
                <p>Tu cuenta será anonimizada (nombre y email cambiados).</p>
                <div class="form-group">
                    <label for="confirmationText" class="form-label">Para confirmar, escribe <strong>"ELIMINAR TODO"</strong>:</label>
                    <input type="text" class="form-control" id="confirmationText" placeholder="ELIMINAR TODO">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteAllData">
                    <i class="bi bi-trash-fill me-1"></i> Sí, eliminar todo
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// =============================================
// ✅ FUNCIONES DE ACCIÓN DE SUSCRIPCIÓN (GLOBALES)
// =============================================

/**
 * Subir de plan (Free → Básico, Free → Premium, Básico → Premium)
 */
function upgradePlan(targetPlan) {
    const planNames = {
        'basico': 'Básico ($10 ARS)',
        'premium': 'Premium ($25 ARS)'
    };
    
    const planIcons = {
        'basico': '📋',
        'premium': '⭐'
    };
    
    if (!confirm(`¿Deseas cambiar al plan ${planIcons[targetPlan]} ${planNames[targetPlan]}?`)) {
        return;
    }
    
    @if(app()->environment('local'))
        debugActivateSubscription(targetPlan);
    @else
        window.location.href = `/suscripcion/${targetPlan}/pagar`;
    @endif
}

/**
 * Bajar de plan (Premium → Básico)
 */
function downgradePlan(targetPlan) {
    const planNames = {
        'basico': 'Básico ($10 ARS)',
        'free': 'Free (Gratuito)'
    };
    
    const planIcons = {
        'basico': '📋',
        'free': '🎁'
    };
    
    if (!confirm(`¿Deseas bajar al plan ${planIcons[targetPlan]} ${planNames[targetPlan]}?`)) {
        return;
    }
    
    @if(app()->environment('local'))
        debugActivateSubscription(targetPlan);
    @else
        showAlert('⚠️ La bajada de plan se aplicará al finalizar el período actual.', 'warning');
    @endif
}

/**
 * Cancelar suscripción
 */
function cancelSubscription() {
    if (!confirm('¿Estás seguro de que deseas cancelar tu suscripción? Perderás los beneficios al final del período actual.')) {
        return;
    }
    
    @if(app()->environment('local'))
        debugExpireSubscription();
    @else
        $.ajax({
            url: '/api/subscription/cancel',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    showAlert('✅ Suscripción cancelada correctamente.', 'success');
                    loadSubscriptionStatus();
                } else {
                    showAlert('❌ ' + (response.message || 'Error al cancelar'), 'danger');
                }
            },
            error: function(xhr) {
                showAlert('❌ Error: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
            }
        });
    @endif
}

// =============================================
// ✅ FUNCIONES DE DEPURACIÓN (SOLO LOCAL)
// =============================================

@if(app()->environment('local'))
function debugActivateSubscription(plan) {
    const planNames = {
        'free': 'Plan Free',
        'basico': 'Plan Básico',
        'premium': 'Plan Premium'
    };
    
    const planIcons = {
        'free': '🎁',
        'basico': '📋',
        'premium': '⭐'
    };
    
    const duration = plan === 'free' ? 9999 : 5;
    
    showAlert(
        `🔄 Activando ${planNames[plan]} por ${duration === 9999 ? 'tiempo indefinido' : duration + ' minutos'}...`,
        'info'
    );
    
    $.ajax({
        url: '/api/subscription/debug/activate',
        type: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        data: JSON.stringify({
            plan: plan,
            duration_minutes: duration
        }),
        success: function(response) {
            if (response.success) {
                showAlert(
                    `✅ ${planIcons[plan]} ${planNames[plan]} activado correctamente para pruebas.`,
                    'success'
                );
                loadSubscriptionStatus();
                loadStats();
                loadProfile();
            } else {
                showAlert('❌ ' + (response.message || 'Error al activar'), 'danger');
            }
        },
        error: function(xhr) {
            showAlert(
                '❌ Error: ' + (xhr.responseJSON?.message || xhr.statusText),
                'danger'
            );
        }
    });
}

function debugExpireSubscription() {
    showAlert('⏰ Forzando expiración de la suscripción...', 'warning');
    
    $.ajax({
        url: '/api/subscription/debug/expire',
        type: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                showAlert('✅ Suscripción expirada correctamente', 'success');
                loadSubscriptionStatus();
                loadStats();
                loadProfile();
            } else {
                showAlert('❌ ' + (response.message || 'Error al expirar'), 'danger');
            }
        },
        error: function(xhr) {
            showAlert(
                '❌ Error: ' + (xhr.responseJSON?.message || xhr.statusText),
                'danger'
            );
        }
    });
}

function debugClearSubscriptions() {
    if (!confirm('⚠️ ¿Estás seguro de que quieres eliminar TODO el historial de suscripciones?')) {
        return;
    }
    
    showAlert('🧹 Limpiando historial de suscripciones...', 'warning');
    
    $.ajax({
        url: '/api/subscription/debug/clear',
        type: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                showAlert('✅ Historial limpiado correctamente', 'success');
                loadSubscriptionStatus();
                loadStats();
                loadProfile();
            } else {
                showAlert('❌ ' + (response.message || 'Error al limpiar'), 'danger');
            }
        },
        error: function(xhr) {
            showAlert(
                '❌ Error: ' + (xhr.responseJSON?.message || xhr.statusText),
                'danger'
            );
        }
    });
}

window.debugRenewSubscription = function() {
    $.ajax({
        url: '/api/subscription/plan/status',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success && response.data.has_active_subscription) {
                const currentPlan = response.data.subscription.plan;
                debugActivateSubscription(currentPlan);
            } else {
                showAlert('❌ No hay suscripción activa para renovar', 'warning');
            }
        },
        error: function() {
            showAlert('❌ Error al obtener el plan actual', 'danger');
        }
    });
};
@endif

// =============================================
// ✅ FUNCIONES DE ALERTAS Y UTILIDADES
// =============================================

function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    $('#alertContainer').append(alertHtml);
    
    setTimeout(() => {
        $('#alertContainer .alert').first().fadeOut(500, function() {
            $(this).remove();
        });
    }, 8000);
}

// =============================================
// ✅ RENDERIZAR ESTADO DE SUSCRIPCIÓN
// =============================================

function renderSubscriptionStatus(data) {
    let html = '';
    
    // Limpiar intervalo anterior si existe
    if (countdownInterval) {
        clearInterval(countdownInterval);
        countdownInterval = null;
    }
    
    // =============================================
    // OBTENER ESTADO REAL
    // =============================================
    const hasActive = data.has_active_subscription;
    const sub = data.subscription;
    const planKey = data.plan.key;
    const planName = data.plan.name;
    const isPremium = planKey === 'premium';
    const isBasico = planKey === 'basico';
    const isFree = planKey === 'free';
    const isExpired = sub && sub.status === 'expired';
    const isPending = sub && sub.status === 'pending';
    
    let statusText = 'Sin suscripción';
    let statusClass = 'secondary';
    let statusIcon = 'bi-x-circle';
    let showRenew = false;
    let showCancel = false;
    let showUpgradeBasico = false;
    let showUpgradePremium = false;
    let showDowngrade = false;
    let countdownHtml = '';
    
    if (hasActive) {
        if (isPremium) {
            statusText = '⭐ Premium Activo';
            statusClass = 'success';
            statusIcon = 'bi-star-fill';
            showCancel = true;
            showDowngrade = true;
        } else if (isBasico) {
            statusText = '📋 Básico Activo';
            statusClass = 'primary';
            statusIcon = 'bi-credit-card';
            showCancel = true;
            showUpgradePremium = true;
        } else if (isFree) {
            statusText = '🎁 Free Activo';
            statusClass = 'info';
            statusIcon = 'bi-gift';
            showUpgradeBasico = true;
            showUpgradePremium = true;
        }
        
        if (sub && sub.expires_at) {
            const expiresAt = new Date(sub.expires_at);
            const now = new Date();
            const diffMs = expiresAt - now;
            
            if (diffMs > 0) {
                const diffMin = Math.floor(diffMs / 60000);
                const diffSec = Math.floor((diffMs % 60000) / 1000);
                const timeStr = `${diffMin}m ${diffSec}s`;
                const isExpiring = diffMin < 1;
                
                countdownHtml = `
                    <div class="mt-1">
                        <span class="countdown-timer ${isExpiring ? 'expiring' : ''}" id="countdownDisplay">
                            ⏱️ ${timeStr}
                        </span>
                        <small class="text-muted ms-2">tiempo restante</small>
                    </div>
                `;
                
                countdownInterval = setInterval(function() {
                    const now2 = new Date();
                    const diffMs2 = expiresAt - now2;
                    
                    if (diffMs2 <= 0) {
                        clearInterval(countdownInterval);
                        loadSubscriptionStatus();
                        return;
                    }
                    
                    const diffMin2 = Math.floor(diffMs2 / 60000);
                    const diffSec2 = Math.floor((diffMs2 % 60000) / 1000);
                    const timeStr2 = `${diffMin2}m ${diffSec2}s`;
                    
                    const display = $('#countdownDisplay');
                    if (display.length) {
                        display.text(`⏱️ ${timeStr2}`);
                        if (diffMin2 < 1) {
                            display.addClass('expiring');
                        }
                    }
                }, 1000);
            }
        }
        
        const expiresDate = sub && sub.expires_at ? new Date(sub.expires_at).toLocaleString('es-ES') : 'No definida';
        
        html = `
            <div class="card border-${statusClass}">
                <div class="card-header bg-${statusClass} text-white d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi ${statusIcon} me-2"></i>
                        <strong>${statusText}</strong>
                    </div>
                    <span class="badge bg-light text-dark">${planName}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div>
                                    <small class="text-muted d-block">Plan actual</small>
                                    <strong>${planName}</strong>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Válido hasta</small>
                                    <span>${expiresDate}</span>
                                </div>
                            </div>
                            ${countdownHtml}
                        </div>
                        <div class="col-md-5 mt-2 mt-md-0">
                            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                ${showUpgradeBasico ? `
                                    <button class="btn btn-primary btn-sm" onclick="upgradePlan('basico')">
                                        <i class="bi bi-credit-card me-1"></i> Plan Básico
                                    </button>
                                ` : ''}
                                ${showUpgradePremium ? `
                                    <button class="btn btn-warning btn-sm" onclick="upgradePlan('premium')">
                                        <i class="bi bi-star me-1"></i> Plan Premium
                                    </button>
                                ` : ''}
                                ${showDowngrade ? `
                                    <button class="btn btn-info btn-sm" onclick="downgradePlan('basico')">
                                        <i class="bi bi-arrow-down-circle me-1"></i> Bajar a Básico
                                    </button>
                                ` : ''}
                                ${showCancel ? `
                                    <button class="btn btn-danger btn-sm" onclick="cancelSubscription()">
                                        <i class="bi bi-x-circle me-1"></i> Cancelar
                                    </button>
                                ` : ''}
                                ${showRenew ? `
                                    <button class="btn btn-success btn-sm" onclick="debugRenewSubscription()">
                                        <i class="bi bi-arrow-repeat me-1"></i> Renovar
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
    } else if (isPending) {
        html = `
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-hourglass-split me-2"></i>
                    <strong>Pago pendiente de confirmación</strong>
                </div>
                <div class="card-body">
                    <p class="mb-0 text-muted">
                        Tu pago está siendo procesado. Esto puede tomar unos minutos.
                        <br>
                        <small>Si el problema persiste, contacta con soporte.</small>
                    </p>
                </div>
            </div>
        `;
    } else if (isExpired) {
        html = `
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Suscripción expirada</strong>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <p class="mb-0">
                                Tu suscripción <strong>${planName}</strong> ha expirado.
                                <br>
                                <small class="text-muted">Renueva para seguir disfrutando de los beneficios.</small>
                            </p>
                        </div>
                        <div class="col-md-5 mt-2 mt-md-0">
                            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                <button class="btn btn-primary btn-sm" onclick="upgradePlan('basico')">
                                    <i class="bi bi-credit-card me-1"></i> Plan Básico ($10 ARS)
                                </button>
                                <button class="btn btn-warning btn-sm" onclick="upgradePlan('premium')">
                                    <i class="bi bi-star me-1"></i> Plan Premium ($25 ARS)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else {
        html = `
            <div class="card border-secondary">
                <div class="card-header bg-secondary text-white">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Sin suscripción activa</strong>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        Estás usando el plan <strong>${planName}</strong> con funcionalidades limitadas.
                        <br>
                        <small class="text-muted">Activa una suscripción para acceder a todas las funcionalidades.</small>
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-primary btn-sm" onclick="upgradePlan('basico')">
                            <i class="bi bi-credit-card me-1"></i> Plan Básico ($10 ARS)
                        </button>
                        <button class="btn btn-warning btn-sm" onclick="upgradePlan('premium')">
                            <i class="bi bi-star me-1"></i> Plan Premium ($25 ARS)
                        </button>
                        @if(app()->environment('local'))
                        <button class="btn btn-secondary btn-sm" onclick="debugActivateSubscription('free')">
                            <i class="bi bi-gift me-1"></i> Emular Free
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        `;
    }
    
    $('#subscriptionStatus').html(html);
}

function renderSubscriptionError() {
    $('#subscriptionStatus').html(`
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            Error al cargar el estado de la suscripción.
        </div>
    `);
}

// =============================================
// ✅ FUNCIONES DE CARGA DE DATOS
// =============================================

function loadSubscriptionStatus() {
    $.ajax({
        url: '/api/subscription/plan/status',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                renderSubscriptionStatus(response.data);
            } else {
                renderSubscriptionError();
            }
        },
        error: function(xhr) {
            console.error('Error al cargar estado de suscripción:', xhr);
            renderSubscriptionError();
        }
    });
}

function loadStats() {
    $('#totalSensors').text('...');
    $('#totalMeasurements').text('...');
    $('#totalGroups').text('...');

    $.ajax({
        url: '/api/profile/stats',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                const stats = response.data;
                $('#totalSensors').text(stats.total_sensors);
                $('#totalMeasurements').text(stats.total_measurements);
                $('#totalGroups').text(stats.total_groups);
            }
        },
        error: function(xhr) {
            console.error('Error al cargar estadísticas:', xhr);
        }
    });
}

function loadProfile() {
    $.ajax({
        url: '/api/profile',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                const user = response.data.user;
                const subscription = response.data.subscription;
                
                $('#name').val(user.name || '');
                $('#email').val(user.email || '');
                $('#subscription_type').val(user.subscription_type || 'domiciliario');
                $('#userId').text(user.id || '-');
                
                let planDisplay = 'Free';
                if (subscription && subscription.plan) {
                    const planKey = subscription.plan.key || subscription.plan;
                    if (planKey === 'premium') planDisplay = 'Premium';
                    else if (planKey === 'basico') planDisplay = 'Básico';
                    else if (planKey === 'free') planDisplay = 'Free';
                } else {
                    planDisplay = user.subscription_plan === 'basico' ? 'Básico' : 
                                user.subscription_plan === 'premium' ? 'Premium' : 
                                user.subscription_plan === 'free' ? 'Free' : 'Free';
                }
                $('#userPlan').text(planDisplay);
                
                $('#userCreatedAt').text(user.created_at ? new Date(user.created_at).toLocaleString('es-ES') : '-');
                $('#userUpdatedAt').text(user.updated_at ? new Date(user.updated_at).toLocaleString('es-ES') : '-');

                let rolesText = 'Sin roles';
                if (user.roles) {
                    if (Array.isArray(user.roles)) {
                        rolesText = user.roles.join(', ');
                    } else if (typeof user.roles === 'string') {
                        rolesText = user.roles;
                    } else if (typeof user.roles === 'object') {
                        rolesText = Object.values(user.roles).join(', ');
                    }
                }
                $('#userRole').html(`<i class="fas fa-id-badge"></i> ${rolesText}`);

                if (user.google_id) {
                    $('#passwordFields').addClass('d-none');
                    $('#googleInfo').removeClass('d-none');
                    $('#hasGoogleId').val('true');
                } else {
                    $('#passwordFields').removeClass('d-none');
                    $('#googleInfo').addClass('d-none');
                    $('#hasGoogleId').val('false');
                }
            } else {
                showAlert(response.message || 'Error al cargar el perfil', 'danger');
            }
        },
        error: function(xhr) {
            showAlert('Error: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
        }
    });
}

function updateAccountInfo() {
    const token = localStorage.getItem('token');
    if (!token) return;

    $.ajax({
        url: '/api/profile',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success && response.data) {
                const user = response.data.user;
                const subscription = response.data.subscription;
                
                $('#userId').text(user.id || '-');
                $('#userCreatedAt').text(user.created_at ? new Date(user.created_at).toLocaleString('es-ES') : '-');
                $('#userUpdatedAt').text(user.updated_at ? new Date(user.updated_at).toLocaleString('es-ES') : '-');
                
                let planDisplay = 'Free';
                if (subscription && subscription.plan) {
                    const planKey = subscription.plan.key || subscription.plan;
                    if (planKey === 'premium') planDisplay = 'Premium';
                    else if (planKey === 'basico') planDisplay = 'Básico';
                    else if (planKey === 'free') planDisplay = 'Free';
                } else {
                    planDisplay = user.subscription_plan === 'basico' ? 'Básico' : 
                                user.subscription_plan === 'premium' ? 'Premium' : 
                                user.subscription_plan === 'free' ? 'Free' : 'Free';
                }
                $('#userPlan').text(planDisplay);
            }
        },
        error: function(xhr) {
            console.error('Error al cargar información de la cuenta:', xhr.status, xhr.statusText);
        }
    });
}

function saveProfile(e) {
    e.preventDefault();

    const formData = {
        name: $('#name').val(),
        email: $('#email').val(),
        subscription_type: $('#subscription_type').val(),
    };

    const passwordField = $('#password');
    const passwordConfField = $('#password_confirmation');
    
    if (passwordField.length > 0 && !passwordField.closest('#passwordFields').hasClass('d-none')) {
        const password = passwordField.val();
        const passwordConf = passwordConfField.val();

        if ((password && !passwordConf) || (!password && passwordConf)) {
            showAlert('Debes completar ambos campos de contraseña o dejarlos vacíos.', 'danger');
            return;
        }
        
        if (password && passwordConf) {
            if (password !== passwordConf) {
                showAlert('Las contraseñas no coinciden.', 'danger');
                return;
            }
            
            if (password.length < 8) {
                showAlert('La contraseña debe tener al menos 8 caracteres.', 'danger');
                return;
            }
            
            formData.password = password;
            formData.password_confirmation = passwordConf;
        }
    }

    $.ajax({
        url: '/api/profile',
        type: 'PUT',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        data: JSON.stringify(formData),
        beforeSend: function() {
            $('#saveProfileBtn').prop('disabled', true).html(`
                <span class="spinner-border spinner-border-sm" role="status"></span> Guardando...
            `);
        },
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                loadProfile();
                loadStats();
                loadSubscriptionStatus();
                if (passwordField.length > 0) {
                    passwordField.val('');
                    passwordConfField.val('');
                }
            } else {
                showAlert(response.message || 'Error al guardar', 'danger');
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors;
            let message = xhr.responseJSON?.message || 'Error al guardar';
            if (errors) {
                message = Object.values(errors).flat().join('<br>');
            }
            showAlert(message, 'danger');
        },
        complete: function() {
            $('#saveProfileBtn').prop('disabled', false).html(`
                <i class="fas fa-save"></i> Guardar cambios
            `);
        }
    });
}

function deleteAllUserData(token) {
    $.ajax({
        url: '/api/profile/delete-all-data',
        type: 'DELETE',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        data: JSON.stringify({
            confirm_token: token
        }),
        success: function(response) {
            if (response.success) {
                $('#confirmDeleteAllDataModal').modal('hide');
                showAlert(response.message, 'success');
                
                setTimeout(function() {
                    window.location.href = '/login';
                }, 3000);
            } else {
                showAlert(response.message || 'Error al eliminar datos', 'danger');
            }
        },
        error: function(xhr) {
            const errorMessage = xhr.responseJSON?.message || xhr.statusText;
            showAlert('Error: ' + errorMessage, 'danger');
        },
        complete: function() {
            $('#confirmDeleteAllData').prop('disabled', false).html(`
                <i class="bi bi-trash-fill me-1"></i> Sí, eliminar todo
            `);
        }
    });
}

// =============================================
// ✅ DOCUMENT READY - INICIALIZACIÓN
// =============================================
$(document).ready(function() {
    let countdownInterval = null;
    let subscriptionCheckInterval = null;
    
    // Exponer countdownInterval globalmente para renderSubscriptionStatus
    window.countdownInterval = countdownInterval;
    
    // Cargar datos del perfil
    loadProfile();
    loadStats();
    loadSubscriptionStatus();

    // Configuración de intervalos
    subscriptionCheckInterval = setInterval(function() {
        loadSubscriptionStatus();
    }, 10000);

    // Eventos del formulario
    $('#profileForm').submit(saveProfile);
    $('#refreshStatsBtn').click(function() {
        loadStats();
        loadSubscriptionStatus();
    });

    // Eventos de depuración (solo local)
    @if(app()->environment('local'))
    $('#debugActivateFree').click(function() {
        debugActivateSubscription('free');
    });
    
    $('#debugActivateBasico').click(function() {
        debugActivateSubscription('basico');
    });
    
    $('#debugActivatePremium').click(function() {
        debugActivateSubscription('premium');
    });
    
    $('#debugExpireNow').click(function() {
        debugExpireSubscription();
    });
    
    $('#debugClearSubscriptions').click(function() {
        debugClearSubscriptions();
    });
    
    $('#debugCheckStatus').click(function() {
        loadSubscriptionStatus();
        showAlert('✅ Estado actualizado', 'info');
    });
    @endif

    // Funcionalidad para eliminar todos los datos
    $('#deleteAllDataBtn').click(function() {
        $('#confirmDeleteAllDataModal').modal('show');
        $('#confirmationText').val('');
    });

    $('#confirmDeleteAllData').click(function() {
        const confirmationText = $('#confirmationText').val().trim();
        
        if (confirmationText !== 'ELIMINAR TODO') {
            showAlert('Debes escribir exactamente "ELIMINAR TODO" para confirmar.', 'danger');
            return;
        }

        $.ajax({
            url: '/api/profile/delete-all-data/confirmation-token',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            },
            beforeSend: function() {
                $('#confirmDeleteAllData').prop('disabled', true).html(`
                    <span class="spinner-border spinner-border-sm" role="status"></span> Procesando...
                `);
            },
            success: function(response) {
                if (response.success) {
                    deleteAllUserData(response.token);
                } else {
                    showAlert(response.message || 'Error al generar token', 'danger');
                    $('#confirmDeleteAllData').prop('disabled', false).html(`
                        <i class="bi bi-trash-fill me-1"></i> Sí, eliminar todo
                    `);
                }
            },
            error: function(xhr) {
                showAlert('Error: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
                $('#confirmDeleteAllData').prop('disabled', false).html(`
                    <i class="bi bi-trash-fill me-1"></i> Sí, eliminar todo
                `);
            }
        });
    });

    // Actualizar información de la cuenta
    updateAccountInfo();

    // Escuchar eventos
    $(document).on('workspaceChanged subscriptionUpdated', function() {
        updateAccountInfo();
    });

    // Actualizar cada 30 segundos
    setInterval(updateAccountInfo, 30000);
    
    // Función para actualizar solo el plan
    window.updatePlanInfo = function() {
        updateAccountInfo();
    };
});
</script>
@endpush