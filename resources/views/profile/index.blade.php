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
    #subscriptionStatus .alert {
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
        border-radius: 8px;
    }
    #subscriptionStatus .alert i {
        flex-shrink: 0;
    }
    #subscriptionStatus .btn {
        border-radius: 6px;
        font-weight: 500;
    }
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

                    {{-- ✅ SECCIÓN DE DEPURACIÓN (SOLO LOCAL) --}}
                    @if(app()->environment('local'))
                    <div class="mt-4 pt-3 border-top debug-section">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 text-warning">
                                <i class="bi bi-bug"></i> 
                                Panel de Depuración
                            </h6>
                            <span class="badge bg-warning text-dark">SOLO DESARROLLO</span>
                        </div>
                        <p class="small text-muted mb-2">
                            <i class="bi bi-info-circle"></i> 
                            Los botones de depuración emulan suscripciones sin pasar por Mercado Pago. 
                            <strong>La suscripción expira en 5 minutos</strong> para fines de prueba.
                        </p>
                        
                        <div class="row g-2">
                            <div class="col-md-4">
                                <button type="button" class="btn btn-outline-secondary w-100 debug-btn" id="debugActivateFree">
                                    <i class="bi bi-play-circle"></i> 
                                    Emular Plan Free (Permanente)
                                    <small class="d-block text-muted" style="font-size: 0.65rem;">Gratis - 1 grupo, 1 sensor</small>
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-outline-success w-100 debug-btn" id="debugActivateBasico">
                                    <i class="bi bi-play-circle"></i> 
                                    Emular Plan Básico (5 min)
                                    <small class="d-block text-muted" style="font-size: 0.65rem;">$10 ARS - Sin pago real</small>
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-outline-warning w-100 debug-btn" id="debugActivatePremium">
                                    <i class="bi bi-play-circle"></i> 
                                    Emular Plan Premium (5 min)
                                    <small class="d-block text-muted" style="font-size: 0.65rem;">$25 ARS - Sin pago real</small>
                                </button>
                            </div>
                        </div>
                        
                        <div class="row g-2 mt-2">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-outline-danger w-100 debug-btn" id="debugExpireNow">
                                    <i class="bi bi-stop-circle"></i> 
                                    Expirar Suscripción Ahora
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-outline-secondary w-100 debug-btn" id="debugClearSubscriptions">
                                    <i class="bi bi-trash"></i> 
                                    Limpiar Historial
                                </button>
                            </div>
                        </div>
                        
                        <div class="mt-2">
                            <button type="button" class="btn btn-outline-info btn-sm w-100 debug-btn" id="debugCheckStatus">
                                <i class="bi bi-arrow-repeat"></i> 
                                Verificar Estado Manualmente
                            </button>
                        </div>
                    </div>
                    @endif

                    {{-- SECCI\u00d3N DE ELIMINACI\u00d3N DE DATOS --}}
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

<!-- Modal de confirmaci\u00f3n para eliminar todos los datos -->
<div class="modal fade" id="confirmDeleteAllDataModal" tabindex="-1" aria-labelledby="confirmDeleteAllDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDeleteAllDataModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> 
                    \u00bfEst\u00e1s seguro de que deseas eliminar TODOS tus datos?
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>¡Advertencia: Esta acción eliminará TODOS tus datos:</strong> sensores, mediciones, fotos asociadas, grupos, colaboraciones, suscripciones y configuraciones. No podrás recuperar esta información.
                </div>
                <p>Se eliminar\u00e1n:</p>
                <ul>
                    <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Todos tus sensores</li>
                    <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Todas tus mediciones</li>
                    <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Todas las fotos asociadas a mediciones</li>
                    <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Todos tus grupos de sensores</li>
                    <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Todas tus colaboraciones y accesos compartidos</li>
                    <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Tus suscripciones</li>
                    <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Tus configuraciones personales</li>
                </ul>
                <p>Tu cuenta ser\u00e1 anonimizada (nombre y email cambiados).</p>
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
                    <i class="bi bi-trash-fill me-1"></i> S\u00ed, eliminar todo
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let countdownInterval = null;
    let subscriptionCheckInterval = null;
    
    // Cargar datos del perfil
    loadProfile();
    loadStats();
    loadSubscriptionStatus();

    // =============================================
    // ✅ CONFIGURACIÓN DE INTERVALOS
    // =============================================
    
    // Verificar estado de suscripción cada 10 segundos
    subscriptionCheckInterval = setInterval(function() {
        loadSubscriptionStatus();
    }, 10000);

    // =============================================
    // ✅ EVENTOS DEL FORMULARIO
    // =============================================
    
    $('#profileForm').submit(saveProfile);
    $('#refreshStatsBtn').click(function() {
        loadStats();
        loadSubscriptionStatus();
    });

    // =============================================
    // ✅ EVENTOS DE DEPURACIÓN (SOLO LOCAL)
    // =============================================
    
    @if(app()->environment('local'))
    $('#debugActivateFree').click(function() {
        debugActivateSubscription('free');
    });
    
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

    // =============================================
    // ✅ FUNCIÓN PARA GUARDAR PERFIL
    // =============================================
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

    // =============================================
    // ✅ FUNCIÓN PARA CARGAR PERFIL
    // =============================================
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
                    const user = response.data;
                    $('#name').val(user.name);
                    $('#email').val(user.email);
                    $('#subscription_type').val(user.subscription_type || 'domiciliario');
                    $('#userId').text(user.id);
                    $('#userPlan').text(user.subscription_plan || 'básico');
                    $('#userCreatedAt').text(new Date(user.created_at).toLocaleString('es-ES'));
                    $('#userUpdatedAt').text(new Date(user.updated_at).toLocaleString('es-ES'));

                    const roles = user.roles.join(', ');
                    $('#userRole').html(`<i class="fas fa-id-badge"></i> ${roles}`);

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

    // =============================================
    // ✅ FUNCIÓN PARA CARGAR ESTADÍSTICAS
    // =============================================
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

    // =============================================
    // ✅ FUNCIÓN PARA CARGAR ESTADO DE SUSCRIPCIÓN
    // =============================================
    function loadSubscriptionStatus() {
        $.ajax({
            url: '/api/subscription/status',
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

    // =============================================
    // ✅ FUNCIÓN PARA RENDERIZAR ESTADO DE SUSCRIPCIÓN
    // =============================================
    function renderSubscriptionStatus(data) {
        let html = '';
        
        // Limpiar intervalo anterior si existe
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
        
        if (data.has_active_subscription) {
            const sub = data.subscription;
            const planName = sub.plan === 'premium' ? '⭐ Premium' : '📋 Básico';
            const planIcon = sub.plan === 'premium' ? 'star' : 'credit-card';
            const planColor = sub.plan === 'premium' ? 'warning' : 'primary';
            
            // Calcular tiempo restante
            const expiresAt = new Date(sub.expires_at);
            const now = new Date();
            const diffMs = expiresAt - now;
            
            let statusText = 'Activa';
            let statusClass = 'active';
            let countdownHtml = '';
            
            if (diffMs <= 0) {
                statusText = 'Expirada';
                statusClass = 'expired';
                countdownHtml = `<span class="badge bg-danger">⏰ Expirada</span>`;
            } else {
                // Mostrar contador regresivo
                const diffMin = Math.floor(diffMs / 60000);
                const diffSec = Math.floor((diffMs % 60000) / 1000);
                const timeStr = `${diffMin}m ${diffSec}s`;
                
                const isExpiring = diffMin < 1;
                countdownHtml = `
                    <span class="countdown-timer ${isExpiring ? 'expiring' : ''}" id="countdownDisplay">
                        ⏱️ ${timeStr}
                    </span>
                `;
                
                // Iniciar contador regresivo
                countdownInterval = setInterval(function() {
                    const now2 = new Date();
                    const diffMs2 = expiresAt - now2;
                    
                    if (diffMs2 <= 0) {
                        clearInterval(countdownInterval);
                        // Recargar estado para mostrar expirada
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
            
            const expiresDate = sub.expires_at ? new Date(sub.expires_at).toLocaleString('es-ES') : 'Indefinida';
            
            html = `
                <div class="alert alert-success">
                    <div class="d-flex justify-content-between align-items-start flex-wrap">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-${planIcon}-fill fs-4 text-${planColor}"></i>
                                <strong>${planName}</strong>
                                <span class="status-badge ${statusClass}">${statusText}</span>
                            </div>
                            <div class="mt-1">
                                <small class="text-muted">
                                    <i class="bi bi-calendar3"></i> 
                                    Válido hasta: ${expiresDate}
                                </small>
                            </div>
                            <div class="mt-1">
                                ${countdownHtml}
                            </div>
                            <div class="mt-1">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> 
                                    Al expirar, deberás renovar manualmente tu suscripción.
                                </small>
                            </div>
                        </div>
                        <div class="mt-2 mt-md-0">
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="debugRenewSubscription()">
                                <i class="bi bi-arrow-repeat"></i> Renovar ahora
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
        } else if (data.has_pending_payment) {
            html = `
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-hourglass-split fs-4"></i>
                        <div>
                            <strong>Pago pendiente de confirmación</strong>
                            <br>
                            <small class="text-muted">Tu pago está siendo procesado. Esto puede tomar unos minutos.</small>
                        </div>
                    </div>
                </div>
            `;
        } else {
            // ✅ Sin suscripción activa - Mostrar planes
            html = `
                <div class="alert alert-info">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-info-circle fs-4"></i>
                        <div>
                            <strong>No tienes una suscripción activa</strong>
                            <br>
                            <small class="text-muted">Activa tu suscripción para acceder a todas las funcionalidades.</small>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap mt-2">
                    <a href="/suscripcion/basico/pagar" class="btn btn-outline-primary btn-sm" id="subscribeBasicoBtn">
                        <i class="bi bi-credit-card"></i> Plan Básico ($10 ARS)
                    </a>
                    <a href="/suscripcion/premium/pagar" class="btn btn-warning btn-sm" id="subscribePremiumBtn">
                        <i class="bi bi-star"></i> Plan Premium ($25 ARS)
                    </a>
                    @if(app()->environment('local'))
                    <span class="text-muted small d-flex align-items-center ms-2">
                        <i class="bi bi-bug"></i> Usa los botones de depuración para pruebas
                    </span>
                    @endif
                </div>
            `;
        }
        
        $('#subscriptionStatus').html(html);
    }

    // =============================================
    // ✅ FUNCIÓN PARA RENDERIZAR ERROR
    // =============================================
    function renderSubscriptionError() {
        $('#subscriptionStatus').html(`
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                Error al cargar el estado de la suscripción.
            </div>
        `);
    }

    // =============================================
    // ✅ FUNCIONES DE DEPURACIÓN (SOLO LOCAL)
    // =============================================
    
    @if(app()->environment('local'))
    function debugActivateSubscription(plan) {
        const planNames = {
            'basico': 'Plan Básico',
            'premium': 'Plan Premium'
        };
        
        const planIcons = {
            'basico': '📋',
            'premium': '⭐'
        };
        
        showAlert(
            `🔄 Activando ${planNames[plan]} por 5 minutos...`,
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
                        `✅ ${planIcons[plan]} ${planNames[plan]} activado por 5 minutos para pruebas.`,
                        'success'
                    );
                    loadSubscriptionStatus();
                    loadStats();
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
    
    // Función global para renovar desde el botón
    window.debugRenewSubscription = function() {
        // Obtener el plan actual
        $.ajax({
            url: '/api/subscription/status',
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
    // ✅ FUNCIÓN PARA MOSTRAR ALERTAS
    // =============================================
    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#alertContainer').append(alertHtml);
        
        // Auto-eliminar después de 8 segundos
        setTimeout(() => {
            $('#alertContainer .alert').first().fadeOut(500, function() {
                $(this).remove();
            });
        }, 8000);
    }
    // =============================================
    // \u2705 FUNCIONALIDAD PARA ELIMINAR TODOS LOS DATOS
    // =============================================
    
    // Evento para abrir el modal de confirmaci\u00f3n
    $('#deleteAllDataBtn').click(function() {
        $('#confirmDeleteAllDataModal').modal('show');
        $('#confirmationText').val('');
    });

    // Evento para confirmar la eliminaci\u00f3n
    $('#confirmDeleteAllData').click(function() {
        const confirmationText = $('#confirmationText').val().trim();
        
        if (confirmationText !== 'ELIMINAR TODO') {
            showAlert('Debes escribir exactamente "ELIMINAR TODO" para confirmar.', 'danger');
            return;
        }

        // Obtener token de confirmaci\u00f3n
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
                    // Ahora ejecutar la eliminaci\u00f3n con el token
                    deleteAllUserData(response.token);
                } else {
                    showAlert(response.message || 'Error al generar token', 'danger');
                    $('#confirmDeleteAllData').prop('disabled', false).html(`
                        <i class="bi bi-trash-fill me-1"></i> S\u00ed, eliminar todo
                    `);
                }
            },
            error: function(xhr) {
                showAlert('Error: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
                $('#confirmDeleteAllData').prop('disabled', false).html(`
                    <i class="bi bi-trash-fill me-1"></i> S\u00ed, eliminar todo
                `);
            }
        });
    });

    // Funci\u00f3n para eliminar todos los datos del usuario
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
                    
                    // Redirigir al login despu\u00e9s de 3 segundos
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
                    <i class="bi bi-trash-fill me-1"></i> S\u00ed, eliminar todo
                `);
            }
        });
    }
});
</script>
@endpush
