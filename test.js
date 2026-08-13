
        // ======================================    =======
        // ✅ FUNCIONES DE ACCIÓN DE SUSCRIP    CIÓN (GLOBALES)
        // =======================    ====     =================

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
                    success: function (response) {
                        if (response.success) {
                            showAlert('✅ Suscripción cancelada correctamente.', 'success');
                            loadSubscriptionStatus();
                        } else {
                            showAlert('❌ ' + (response.message || 'Error al cancelar'), 'danger');
                        }
                    },
                    error: function (xhr) {
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

                // ✅ Cambiar duración: 30 días para planes de prueba (43200 minutos)
                const duration = plan === 'free' ? 9999 : 43200; // 30 días = 43200 minutos
                const durationText = plan === 'free' ? 'tiempo indefinido' : '30 días';

                showAlert(
                    `🔄 Activando ${planNames[plan]} por ${durationText}...`,
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
                    success: function (response) {
                        if (response.success) {
                            showAlert(
                                `✅ ${planIcons[plan]} ${planNames[plan]} activado correctamente${plan === 'free' ? ' (permanente)' : ' por 30 días'}.`,
                                'success'
                            );

                            // ✅ Si se activa Free, limpiar el plan anterior
                            if (plan === 'free') {
                                localStorage.removeItem('previous_plan');
                            } else {
                                localStorage.setItem('previous_plan', plan);
                            }

                            // ✅ REFRESCAR TODO
                            loadSubscriptionStatus();
                            loadStats();
                            loadProfile();
                            updateAccountInfo();

                            // ✅ Eliminar alerta de downgrade si existe
                            const downgradeAlert = document.getElementById('downgradeAlert');
                            if (downgradeAlert) {
                                downgradeAlert.remove();
                            }

                            if (typeof refreshSubscriptionStatus === 'function') {
                                refreshSubscriptionStatus();
                            }
                        } else {
                            showAlert('❌ ' + (response.message || 'Error al activar'), 'danger');
                        }
                    },
                    error: function (xhr) {
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
                    success: function (response) {
                        if (response.success) {
                            showAlert('✅ Suscripción expirada correctamente', 'success');

                            // ✅ RECARGAR TODO
                            loadSubscriptionStatus();
                            loadStats();
                            loadProfile();
                            updateAccountInfo();

                            // ✅ La alerta de downgrade se mostrará automáticamente en renderSubscriptionStatus

                            if (typeof refreshSubscriptionStatus === 'function') {
                                refreshSubscriptionStatus();
                            }
                        } else {
                            showAlert('❌ ' + (response.message || 'Error al expirar'), 'danger');
                        }
                    },
                    error: function (xhr) {
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
                    success: function (response) {
                        if (response.success) {
                            showAlert('✅ Historial limpiado correctamente', 'success');
                            loadSubscriptionStatus();
                            loadStats();
                            loadProfile();
                        } else {
                            showAlert('❌ ' + (response.message || 'Error al limpiar'), 'danger');
                        }
                    },
                    error: function (xhr) {
                        showAlert(
                            '❌ Error: ' + (xhr.responseJSON?.message || xhr.statusText),
                            'danger'
                        );
                    }
                });
            }

            window.debugRenewSubscription = function () {
                $.ajax({
                    url: '/api/subscription/plan/status',
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json'
                    },
                    success: function (response) {
                        if (response.success && response.data.has_active_subscription) {
                            const currentPlan = response.data.subscription.plan;
                            debugActivateSubscription(currentPlan);
                        } else {
                            showAlert('❌ No hay suscripción activa para renovar', 'warning');
                        }
                    },
                    error: function () {
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
                    $('#alertContainer .alert').first().fadeOut(500, function () {
                        $(this).remove();
                    });
                }, 8000);
            }

        // =============================================
        // ✅ RENDERIZAR ESTADO DE SUSCRIPCIÓN
        // =============================================

        function renderSubscriptionStatus(data) {
            console.log('📊 Renderizando estado de suscripción:', data);

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

            console.log('📊 Estado:', { hasActive, planKey, isExpired, isPending });

            // =============================================
            // CASO 1: SUSCRIPCIÓN ACTIVA
            // =============================================
            if (hasActive) {
                let statusText = '';
                let statusClass = '';
                let statusIcon = '';
                let showCancel = false;
                let showUpgradeBasico = false;
                let showUpgradePremium = false;
                let showDowngrade = false;
                let countdownHtml = '';
                let expiresAtDate = null;

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

                // ✅ CONTADOR REGRESIVO - CUANDO TERMINA LLAMA A debugExpireSubscription() (igual que el botón Cancelar)
                // ✅ CONTADOR REGRESIVO CON FORMATO DE DÍAS, HORAS Y MINUTOS
                if (sub && sub.expires_at) {
                    expiresAtDate = new Date(sub.expires_at);
                    const now = new Date();
                    const diffMs = expiresAtDate - now;

                    if (diffMs > 0) {
                        // Calcular días, horas, minutos
                        const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
                        const diffHours = Math.floor((diffMs % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));

                        let timeStr = '';
                        if (diffDays > 0) {
                            timeStr = `${diffDays}d ${diffHours}h ${diffMinutes}m`;
                        } else if (diffHours > 0) {
                            timeStr = `${diffHours}h ${diffMinutes}m`;
                        } else {
                            timeStr = `${diffMinutes}m`;
                        }

                        const isExpiring = diffDays === 0 && diffHours === 0 && diffMinutes < 5;

                        countdownHtml = `
                                <div class="mt-1">
                                    <span class="countdown-timer ${isExpiring ? 'expiring' : ''}" id="countdownDisplay">
                                        ⏱️ ${timeStr}
                                    </span>
                                    <small class="text-muted ms-2">tiempo restante</small>
                                </div>
                            `;

                        // ✅ INICIAR CONTADOR CON VERIFICACIÓN DE EXPIRACIÓN (actualiza cada minuto)
                        countdownInterval = setInterval(function () {
                            const now2 = new Date();
                            const diffMs2 = expiresAtDate - now2;

                            if (diffMs2 <= 0) {
                                clearInterval(countdownInterval);

                                showAlert('⏰ Tu suscripción ha expirado. Volviendo al plan Free.', 'warning');

                                setTimeout(function () {
                                    debugExpireSubscription();
                                }, 500);
                                return;
                            }

                            // Recalcular días, horas, minutos
                            const diffDays2 = Math.floor(diffMs2 / (1000 * 60 * 60 * 24));
                            const diffHours2 = Math.floor((diffMs2 % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            const diffMinutes2 = Math.floor((diffMs2 % (1000 * 60 * 60)) / (1000 * 60));

                            let timeStr2 = '';
                            if (diffDays2 > 0) {
                                timeStr2 = `${diffDays2}d ${diffHours2}h ${diffMinutes2}m`;
                            } else if (diffHours2 > 0) {
                                timeStr2 = `${diffHours2}h ${diffMinutes2}m`;
                            } else {
                                timeStr2 = `${diffMinutes2}m`;
                            }

                            const display = $('#countdownDisplay');
                            if (display.length) {
                                display.text(`⏱️ ${timeStr2}`);
                                if (diffDays2 === 0 && diffHours2 === 0 && diffMinutes2 < 5) {
                                    display.addClass('expiring');
                                } else {
                                    display.removeClass('expiring');
                                }
                            }
                        }, 60000); // ✅ Actualizar cada minuto (60000 ms) en lugar de cada segundo
                    } else {
                        // ✅ Si ya expiró, ejecutar inmediatamente
                        setTimeout(function () {
                            debugExpireSubscription();
                        }, 500);
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
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                // =============================================
                // CASO 2: PAGO PENDIENTE
                // =============================================
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

                // =============================================
                // CASO 3: SUSCRIPCIÓN EXPIRADA
                // =============================================
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
                                            @if(app()->environment('local'))
                                                <button class="btn btn-secondary btn-sm" onclick="debugActivateSubscription('free')">
                                                    <i class="bi bi-gift me-1"></i> Emular Free
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                // =============================================
                // CASO 4: SIN SUSCRIPCIÓN ACTIVA
                // =============================================
            } else {
                html = `
                        <div class="card border-info">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-gift me-2"></i>
                                    <strong>🎁 Plan Free</strong>
                                </div>
                                <span class="badge bg-light text-dark">${planName}</span>
                            </div>
                            <div class="card-body">
                                <p class="mb-3">
                                    ${planKey === 'basico' || planKey === 'premium'
                        ? `Tu suscripción <strong>${planName}</strong> no está activa actualmente.`
                        : `Estás usando el plan <strong>${planName}</strong> con funcionalidades limitadas.`
                    }
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

            // ✅ ACTUALIZAR BADGE DEL HEADER
            updateHeaderBadge(data);

            // ✅ ACTUALIZAR ALERTA DE DOWNGRADE
            toggleDowngradeAlert(data);
        }

        // =============================================
        // ✅ FUNCIÓN PARA ACTUALIZAR ALERTA DE DOWNGRADE
        // =============================================

        function toggleDowngradeAlert(data) {
            const hasActive = data.has_active_subscription;
            const sub = data.subscription;
            const planKey = data.plan.key;

            // Verificar si hay downgrade
            const isExpired = sub && sub.status === 'expired';
            const isDowngraded = !hasActive && (planKey === 'basico' || planKey === ' premium');

            // Verificar si hay un plan anterior en localStorage
            const previousPlan = localStorage.getItem('previous_plan') || null;
            const wasPaidPlan = previousPlan === 'basico' || previousPlan === 'premium';

            const showAlert = isExpired || isDowngraded || wasPaidPlan;
            const existingAlert = document.getElementById('downgradeAlert');

            if (showAlert) {
                let planName = 'anterior';
                if (previousPlan === 'premium' || planKey === 'premium') planName = 'Premium';
                else if (previousPlan === 'basico' || planKey === 'basico') planName = 'Básico';

                if (!existingAlert) {
                    const alertHtml = `
                                <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert" id="downgradeAlert">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-warning"></i>
                                        <div class="flex-grow-1">
                                            <strong>⚠️ Plan downgradeado</strong>
                                            <p class="mb-1 small">
                                                Tu suscripción <strong>${planName}</strong> ha expirado o fue cancelada.
                                                <br>
                                                <span class="text-muted">Has perdido acceso a funcionalidades premium.</span>
                                            </p>
                                            <div class="d-flex gap-2 mt-2">
                                                <a href="/suscripciones" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-arrow-right me-1"></i> Renovar ahora
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('downgradeAlert').remove()">
                                                    <i class="bi bi-x me-1"></i> Cerrar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;

                    const alertContainer = document.getElementById('alertContainer');
                    if (alertContainer) {
                        alertContainer.insertAdjacentHTML('afterend', alertHtml);
                    }
                }
            } else {
                if (existingAlert) {
                    existingAlert.remove();
                }
            }
        }

        // ✅ FUNCIÓN PARA ACTUALIZAR EL BADGE DEL HEADER
        function updateHeaderBadge(data) {
            const planKey = data.plan.key;
            const hasActive = data.has_active_subscription;
            const planName = data.plan.name;
            const badge = document.querySelector('.subscription-badge');
            if (!badge) return;

            let icon = 'bi-hourglass-split';
            let className = 'free';
            let label = 'Gratuito';
            let dotClass = 'expired';

            // ✅ Si está activo, mostrar según el plan
            if (hasActive) {
                if (planKey === 'premium') {
                    icon = 'bi-star-fill';
                    className = 'premium';
                    label = 'Premium';
                    dotClass = 'active';
                } else if (planKey === 'basico') {
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
                // ✅ Si NO está activo, mostrar según el plan del usuario
                if (planKey === 'free') {
                    icon = 'bi-gift';
                    className = 'free';
                    label = 'Free';
                    dotClass = 'expired'; // Mantener el punto rojo para indicar que no hay suscripción activa
                } else if (planKey === 'basico' || planKey === 'premium') {
                    // Si tiene un plan pago pero no está activo (expirado)
                    icon = 'bi-exclamation-triangle';
                    className = 'expired';
                    label = planKey === 'premium' ? 'Premium (Expirado)' : 'Básico (Expirado)';
                    dotClass = 'expired';
                } else {
                    icon = 'bi-exclamation-triangle';
                    className = 'expired';
                    label = 'Sin suscripción';
                    dotClass = 'expired';
                }
            }

            badge.className = `subscription-badge ${className}`;
            badge.innerHTML = `
                    <span class="badge-dot ${dotClass}"></span>
                    <i class="bi ${icon}"></i>
                    ${label}
                `;
        }

        function renderSubscriptionError() {
            $('#subscriptionStatus').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Error al cargar el estado de la suscripción.</strong>
                        <br>
                        <small class="text-muted">Intenta recargar la página. Si el problema persiste, contacta con soporte.</small>
                        <br>
                        <button class="btn btn-sm btn-outline-danger mt-2" onclick="loadSubscriptionStatus()">
                            <i class="bi bi-arrow-repeat me-1"></i> Reintentar
                        </button>
                    </div>
                `);
        }

        // =============================================
        // ✅ FUNCIONES DE CARGA DE DATOS
        // =============================================

        function loadSubscriptionStatus() {
            const token = localStorage.getItem('token');
            if (!token) return;

            // ✅ Obtener plan anterior del input oculto
            const previousPlanInput = document.getElementById('previousPlanValue');
            if (previousPlanInput && previousPlanInput.value) {
                localStorage.setItem('previous_plan', previousPlanInput.value);
            }

            $.ajax({
                url: '/api/subscription/plan/status',
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                },
                cache: false,
                success: function (response) {
                    if (response.success) {
                        // ✅ Si hay un plan anterior en localStorage, pasarlo a los datos
                        const previousPlan = localStorage.getItem('previous_plan');
                        if (previousPlan) {
                            response.data.previous_plan = previousPlan;
                        }

                        renderSubscriptionStatus(response.data);
                        updateAccountInfo();
                        updateHeaderBadge(response.data);

                        // ✅ Actualizar badge de downgrade
                        if (typeof updateDowngradeBadge === 'function') {
                            updateDowngradeBadge(response.data);
                        }
                    } else {
                        renderSubscriptionError();
                    }
                },
                error: function (xhr) {
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
                success: function (response) {
                    if (response.success) {
                        const stats = response.data;
                        $('#totalSensors').text(stats.total_sensors);
                        $('#totalMeasurements').text(stats.total_measurements);
                        $('#totalGroups').text(stats.total_groups);
                    }
                },
                error: function (xhr) {
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
                cache: false,
                success: function (response) {
                    if (response.success) {
                        const user = response.data.user;
                        const subscription = response.data.subscription;

                        $('#name').val(user.name || '');
                        $('#email').val(user.email || '');
                        $('#subscription_type').val(user.subscription_type || 'domiciliario');
                        $('#userId').text(user.id || '-');

                        // ✅ Mostrar el plan REAL (si es free, mostrar "Free")
                        let planDisplay = 'Free';
                        if (subscription && subscription.plan) {
                            const planKey = subscription.plan.key || subscription.plan;
                            if (planKey === 'premium') planDisplay = 'Premium';
                            else if (planKey === 'basico') planDisplay = 'Básico';
                            else if (planKey === 'free') planDisplay = 'Free';
                        } else {
                            // Si no hay suscripción, usar el plan del usuario
                            const userPlan = user.subscription_plan || 'free';
                            planDisplay = userPlan === 'basico' ? 'Básico' :
                                userPlan === 'premium' ? 'Premium' :
                                    userPlan === 'free' ? 'Free' : 'Free';
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
                error: function (xhr) {
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
                cache: false, // ✅ Evitar caché
                success: function (response) {
                    if (response.success && response.data) {
                        const user = response.data.user;
                        const subscription = response.data.subscription;

                        $('#userId').text(user.id || '-');
                        $('#userCreatedAt').text(user.created_at ? new Date(user.created_at).toLocaleString('es-ES') : '-');
                        $('#userUpdatedAt').text(user.updated_at ? new Date(user.updated_at).toLocaleString('es-ES') : '-');

                        // ✅ Mostrar el plan REAL desde la suscripción
                        let planDisplay = 'Free';
                        if (subscription && subscription.plan) {
                            const planKey = subscription.plan.key || subscription.plan;
                            if (planKey === 'premium') planDisplay = 'Premium';
                            else if (planKey === 'basico') planDisplay = 'Básico';
                            else if (planKey === 'free') planDisplay = 'Free';
                        } else {
                            // Si no hay suscripción, usar el plan del usuario
                            planDisplay = user.subscription_plan === 'basico' ? 'Básico' :
                                user.subscription_plan === 'premium' ? 'Premium' :
                                    user.subscription_plan === 'free' ? 'Free' : 'Free';
                        }
                        $('#userPlan').text(planDisplay);
                    }
                },
                error: function (xhr) {
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
                beforeSend: function () {
                    $('#saveProfileBtn').prop('disabled', true).html(`
                            <span class="spinner-border spinner-border-sm" role="status"></span> Guardando...
                        `);
                },
                success: function (response) {
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
                error: function (xhr) {
                    const errors = xhr.responseJSON?.errors;
                    let message = xhr.responseJSON?.message || 'Error al guardar';
                    if (errors) {
                        message = Object.values(errors).flat().join('<br>');
                    }
                    showAlert(message, 'danger');
                },
                complete: function () {
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
                success: function (response) {
                    if (response.success) {
                        $('#confirmDeleteAllDataModal').modal('hide');
                        showAlert(response.message, 'success');

                        setTimeout(function () {
                            window.location.href = '/login';
                        }, 3000);
                    } else {
                        showAlert(response.message || 'Error al eliminar datos', 'danger');
                    }
                },
                error: function (xhr) {
                    const errorMessage = xhr.responseJSON?.message || xhr.statusText;
                    showAlert('Error: ' + errorMessage, 'danger');
                },
                complete: function () {
                    $('#confirmDeleteAllData').prop('disabled', false).html(`
                            <i class="bi bi-trash-fill me-1"></i> Sí, eliminar todo
                        `);
                }
            });
        }

        // =============================================
        // ✅ DOCUMENT READY - INICIALIZACIÓN
        // =============================================
        $(document).ready(function () {
            let countdownInterval = null;
            let subscriptionCheckInterval = null;

            // Exponer countdownInterval globalmente para renderSubscriptionStatus
            window.countdownInterval = countdownInterval;

            // Cargar datos del perfil
            loadProfile();
            loadStats();
            loadSubscriptionStatus();

            // Configuración de intervalos
            subscriptionCheckInterval = setInterval(function () {
                loadSubscriptionStatus();
            }, 10000);

            // Eventos del formulario
            $('#profileForm').submit(saveProfile);
            $('#refreshStatsBtn').click(function () {
                loadStats();
                loadSubscriptionStatus();
            });

            // Eventos de depuración (solo local)
            @if(app()->environment('local'))
                $('#debugActivateFree').click(function () {
                    debugActivateSubscription('free');
                });

                $('#debugActivateBasico').click(function () {
                    debugActivateSubscription('basico');
                });

                $('#debugActivatePremium').click(function () {
                    debugActivateSubscription('premium');
                });

                $('#debugExpireNow').click(function () {
                    debugExpireSubscription();
                });

                $('#debugClearSubscriptions').click(function () {
                    debugClearSubscriptions();
                });

                $('#debugCheckStatus').click(function () {
                    loadSubscriptionStatus();
                    showAlert('✅ Estado actualizado', 'info');
                });
            @endif

            // Funcionalidad para eliminar todos los datos
            $('#deleteAllDataBtn').click(function () {
                $('#confirmDeleteAllDataModal').modal('show');
                $('#confirmationText').val('');
            });

            $('#confirmDeleteAllData').click(function () {
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
                    beforeSend: function () {
                        $('#confirmDeleteAllData').prop('disabled', true).html(`
                                <span class="spinner-border spinner-border-sm" role="status"></span> Procesando...
                            `);
                    },
                    success: function (response) {
                        if (response.success) {
                            deleteAllUserData(response.token);
                        } else {
                            showAlert(response.message || 'Error al generar token', 'danger');
                            $('#confirmDeleteAllData').prop('disabled', false).html(`
                                    <i class="bi bi-trash-fill me-1"></i> Sí, eliminar todo
                                `);
                        }
                    },
                    error: function (xhr) {
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
            $(document).on('workspaceChanged subscriptionUpdated', function () {
                updateAccountInfo();
            });

            // Actualizar cada 30 segundos
            setInterval(updateAccountInfo, 30000);

            // Función para actualizar solo el plan
            window.updatePlanInfo = function () {
                updateAccountInfo();
            };
        });
    
