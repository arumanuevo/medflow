@extends('layouts.modern') 

@section('title', 'Dashboard - MedFlow')

@section('content')
<link rel="stylesheet" href="{{ asset('css/dashboard-styles.css') }}">

{{-- ✅ ALERTA DE USUARIO PAUSADO --}}
@if(isset($isPaused) && $isPaused)
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-start">
            <div class="flex-shrink-0">
                <i class="bi bi-exclamation-triangle-fill fs-2 me-3"></i>
            </div>
            <div>
                <h5 class="alert-heading">⚠️ Acceso suspendido temporalmente</h5>
                <p class="mb-0">{{ $pauseMessage ?? 'El propietario ha pausado tu acceso a este espacio.' }}</p>
                <hr>
                <p class="mb-0 small">
                    <i class="bi bi-info-circle"></i>
                    No podrás tomar mediciones ni acceder a los sensores hasta que el propietario reanude tu acceso.
                    <br>
                    Puedes seguir viendo esta página o cambiar a <strong>Mi espacio</strong> para gestionar tus propios datos.
                </p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- ✅ Indicador del espacio activo --}}
<div class="card mb-4">
    <div class="card-header  d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-briefcase me-2"></i> 
            Espacio activo: 
            <span class="badge {{ $workspaceInfo['type'] === 'owner' ? 'bg-success' : 'bg-info' }}">
                {{ $workspaceInfo['name'] }}
            </span>
            @if($workspaceInfo['type'] === 'collaborator')
                <span class="badge bg-warning text-dark ms-2">
                    <i class="bi bi-people"></i> {{ ucfirst($workspaceInfo['role']) }}
                </span>
            @endif
            @if(isset($isPaused) && $isPaused)
                <span class="badge bg-danger ms-2">
                    <i class="bi bi-pause-circle"></i> Pausado
                </span>
            @endif
        </h5>
        @if($workspaceInfo['type'] === 'collaborator')
            <small class="text-muted">
                <i class="bi bi-person"></i> Propietario: {{ $workspaceInfo['owner_name'] }}
            </small>
        @endif
    </div>
    <div class="card-body">
        @if(isset($isPaused) && $isPaused)
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Acceso suspendido:</strong> 
                El propietario ha pausado tu acceso a este espacio. 
                No puedes tomar mediciones ni ver los sensores.
                <br>
                <small class="text-muted">Contacta al propietario para reanudar tu acceso.</small>
            </div>
        @elseif($workspaceInfo['type'] === 'collaborator')
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                Estás viendo el espacio de <strong>{{ $workspaceInfo['owner_name'] }}</strong>.
                Tienes rol de <strong>{{ ucfirst($workspaceInfo['role']) }}</strong>.
                <br>
                <small>Solo puedes ver y gestionar los datos de este espacio según tus permisos.</small>
            </div>
        @else
            <div class="alert alert-success">
                <i class="bi bi-house"></i>
                Estás en tu propio espacio de trabajo.
                Puedes gestionar todos tus sensores y mediciones.
            </div>
        @endif
    </div>
</div>

{{-- 📅 Agenda de Tomas Programadas y Estado de Tolerancias --}}
@if(!isset($isPaused) || !$isPaused)
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <h5 class="mb-0 fw-bold text-white">
            <i class="bi bi-calendar2-check text-warning me-2"></i>
            Agenda de Tomas Programadas y Tolerancias
            @if(($scheduleAlerts['counts']['actionable_total'] ?? 0) > 0)
                <span class="badge bg-danger ms-2">{{ $scheduleAlerts['counts']['actionable_total'] }} pendiente(s)</span>
            @endif
        </h5>
        <div class="d-flex gap-2">
            <a href="{{ route('schedule.index') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-calendar3 me-1"></i> Abrir Calendario de Tomas
            </a>
        </div>
    </div>
    <div class="card-body">
        @php
            $actionableList = $scheduleAlerts['actionable'] ?? [];
            $upcomingList = $scheduleAlerts['upcoming'] ?? [];
        @endphp

        @if(count($actionableList) > 0)
            <div class="alert alert-warning border-warning d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 py-2">
                <div class="d-flex align-items-center">
                    <i class="bi bi-bell-fill fs-4 me-3 text-warning"></i>
                    <div class="small">
                        <strong>Atención de Agenda:</strong> Tienes <strong>{{ count($actionableList) }}</strong> grupo(s) con sensores que requieren medición según su fecha programada y margen de tolerancia.
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-1">
                    @if(($scheduleAlerts['counts']['overdue'] ?? 0) > 0)
                        <span class="badge bg-danger">🔴 {{ $scheduleAlerts['counts']['overdue'] }} vencidos</span>
                    @endif
                    @if(($scheduleAlerts['counts']['in_tolerance'] ?? 0) > 0)
                        <span class="badge bg-warning text-dark">🟠 {{ $scheduleAlerts['counts']['in_tolerance'] }} en tolerancia</span>
                    @endif
                    @if(($scheduleAlerts['counts']['in_window'] ?? 0) > 0)
                        <span class="badge bg-primary">🟡 {{ $scheduleAlerts['counts']['in_window'] }} hoy / en ventana</span>
                    @endif
                </div>
            </div>
            <div class="row g-3 mb-3">
                @foreach(array_slice($actionableList, 0, 6) as $alert)
                    @php
                        $bd = $alert['breakdown'] ?? [];
                        $overdueIds = $alert['overdue_sensor_ids'] ?? [];
                        $allPendingIds = $alert['pending_sensor_ids'] ?? [];
                    @endphp
                    <div class="col-md-6 col-xl-4">
                        <div class="p-3 rounded-3 border h-100 d-flex flex-column justify-content-between"
                             style="border-left: 4px solid {{ $alert['color_hex'] }} !important; background: #fcfcfc;">
                            <div>
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <span class="fw-bold text-dark small">
                                        <i class="bi bi-folder-fill text-primary me-1"></i>
                                        {{ $alert['title'] }}
                                    </span>
                                    <span class="badge {{ $alert['badge_class'] }}" style="font-size: 0.7rem; background-color: {{ $alert['color_hex'] }};">
                                        {{ $alert['status_label'] }}
                                    </span>
                                </div>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @if(($bd['overdue'] ?? 0) > 0)
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle" style="font-size: 0.68rem;">
                                            🔴 {{ $bd['overdue'] }} vencido(s)
                                        </span>
                                    @endif
                                    @if(($bd['in_tolerance'] ?? 0) > 0)
                                        <span class="badge bg-warning bg-opacity-10 text-dark border border-warning-subtle" style="font-size: 0.68rem;">
                                            🟠 {{ $bd['in_tolerance'] }} en tolerancia
                                        </span>
                                    @endif
                                    @if(($bd['in_window'] ?? 0) > 0)
                                        <span class="badge bg-warning bg-opacity-25 text-dark border border-warning" style="font-size: 0.68rem;">
                                            🟡 {{ $bd['in_window'] }} hoy / en ventana
                                        </span>
                                    @endif
                                </div>
                                <p class="text-muted mb-2" style="font-size: 0.78rem;">
                                    {{ $alert['status_message'] }}
                                </p>
                            </div>
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2 border-top">
                                <small class="text-muted" style="font-size: 0.72rem;">
                                    Fecha: <strong>{{ $alert['target_date_formatted'] }}</strong> (Tol: +{{ $alert['tolerance_days'] }}d)
                                </small>
                                <div class="d-flex flex-wrap gap-1">
                                    <button type="button"
                                            class="btn btn-sm btn-dark py-0 px-2"
                                            style="font-size: 0.72rem;"
                                            title="Asignar estos sensores a un Inspector en la App Móvil (Flutter / Offline) mediante Token"
                                            onclick="openDashboardMobileInvite({{ $alert['entity_id'] }})">
                                        <i class="bi bi-phone-fill text-info"></i> Enviar a App
                                    </button>
                                    @if(count($overdueIds) > 0 && count($overdueIds) < count($allPendingIds))
                                        <form method="POST" action="{{ route('bulk-measurements.start') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="sensor_ids" value="{{ implode(',', $overdueIds) }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.72rem;">
                                                <i class="bi bi-exclamation-circle"></i> Vencidos Web ({{ count($overdueIds) }})
                                            </button>
                                        </form>
                                    @endif
                                    @if(!empty($allPendingIds))
                                        <form method="POST" action="{{ route('bulk-measurements.start') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="sensor_ids" value="{{ implode(',', $allPendingIds) }}">
                                            <button type="submit" class="btn btn-sm btn-primary py-0 px-2" style="font-size: 0.75rem;">
                                                <i class="bi bi-laptop"></i> Medir Web ({{ count($allPendingIds) }})
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 rounded-3 bg-light border">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-check-circle-fill text-success fs-3"></i>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">¡Agenda de mediciones al día!</h6>
                        <small class="text-muted">No tienes grupos ni sensores vencidos o en ventana crítica hoy.</small>
                    </div>
                </div>
                <a href="{{ route('schedule.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-calendar-plus me-1"></i> Programar fechas (Ej: Día 15)
                </a>
            </div>
        @endif

        @if(count($upcomingList) > 0)
            <div class="mt-3 pt-2 border-top">
                <small class="text-muted fw-bold d-block mb-2">
                    <i class="bi bi-clock-history me-1"></i> Próximas tomas programadas en el calendario:
                </small>
                <div class="d-flex flex-wrap gap-2">
                    @foreach(array_slice($upcomingList, 0, 5) as $up)
                        <a href="{{ route('schedule.index') }}" class="badge bg-light text-dark border text-decoration-none p-2">
                            <i class="bi {{ $up['type'] === 'group' ? 'bi-folder text-primary' : 'bi-speedometer2 text-info' }} me-1"></i>
                            <strong>{{ $up['title'] }}</strong>: {{ $up['target_date_formatted'] }}
                            <span class="text-muted">(Preaviso -{{ $up['pre_notice_days'] }}d / Tol +{{ $up['tolerance_days'] }}d)</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endif

{{-- Configuración Global --}}
<div class="card mb-4">
    <div class="card-header  d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-gear me-2"></i> Configuración Global</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('dashboard.update-settings') }}">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="default_measurement_period" class="form-label">
                            <i class="bi bi-calendar-range me-2"></i> Período de Medición por Defecto (días)
                        </label>
                        <input type="number" class="form-control" id="default_measurement_period"
                               name="default_measurement_period" value="{{ $defaultPeriod ?? 30 }}" min="1" required>
                        <small class="text-muted">
                            Número de días entre mediciones para nuevos grupos de sensores.
                        </small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="default_expiry_days" class="form-label">
                            <i class="bi bi-hourglass-split me-2"></i> Días de Vencimiento por Defecto
                        </label>
                        <input type="number" class="form-control" id="default_expiry_days"
                               name="default_expiry_days" value="{{ $defaultExpiry ?? 5 }}" min="1" required>
                        <small class="text-muted">
                            Días antes de la próxima medición para considerar un sensor como "Pendiente".
                        </small>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i> Guardar Configuración
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Prueba de Correo (solo para administradores) --}}
@if(auth()->user()->hasRole('admin'))
<div class="card mb-4">
    <div class="card-header  d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-envelope me-2"></i> Prueba de Correo</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">
            <i class="bi bi-info-circle"></i>
            Haz clic en el botón para enviar un correo de prueba a <strong>scastellano10@gmail.com</strong>.
            Revisa tu bandeja de entrada y la carpeta de SPAM.
        </p>
        <div class="d-flex gap-2">
            <a href="{{ route('test.email') }}" class="btn btn-primary" onclick="this.innerHTML='<span class=\'spinner-border spinner-border-sm\' role=\'status\'></span> Enviando...'; this.disabled=true;">
                <i class="bi bi-envelope-paper"></i> Enviar Correo de Prueba
            </a>
        </div>
    </div>
</div>
@endif

{{-- ✅ Invitaciones pendientes (opcional) --}}
@auth
    @php
        $pendingInvitations = \App\Models\WorkspaceCollaborator::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->get();
    @endphp

    @if($pendingInvitations->count() > 0)
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-envelope"></i> Invitaciones Pendientes</h5>
            </div>
            <div class="card-body">
                <p>Tienes <strong>{{ $pendingInvitations->count() }}</strong> invitación(es) para colaborar en espacios de trabajo.</p>
                <a href="{{ route('collaborations.index') }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-people"></i> Ver invitaciones
                </a>
            </div>
        </div>
    @endif
@endauth

{{-- Modal de Asignación a Inspector Móvil (App Flutter / Offline) desde Dashboard --}}
<div class="modal fade" id="dashboardMobileInviteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-dark text-white" style="border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-phone-fill text-info me-2"></i>
                    Asignar Toma a Inspector Móvil (App Flutter)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">
                    Genera un <strong>Token de Inspección Remota</strong> para la App Móvil de MedFlow (con soporte <strong>Offline</strong>).
                    El inspector solo verá y medirá los sensores que determines aquí.
                </p>

                <div class="p-3 rounded-3 bg-light border mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-dark small" id="dashMobileInviteTitle">
                            <i class="bi bi-folder-fill text-primary me-1"></i> Grupo seleccionado
                        </span>
                        <span class="badge bg-primary" id="dashMobileInviteBadge">0 sensores</span>
                    </div>

                    <label class="form-label small fw-bold text-dark mb-1">
                        1. ¿Qué sensores deseas incluir en el Token del Inspector?
                    </label>
                    <div class="d-flex flex-column gap-2">
                        <div class="form-check" id="dashScopePendingWrap">
                            <input class="form-check-input" type="radio" name="dash_mobile_scope" id="dashScopePending" value="pending" checked onchange="updateDashMobileInviteScope()">
                            <label class="form-check-label small" for="dashScopePending">
                                ⏳ <strong>Todos los pendientes por medir</strong> (<span id="dashScopePendingCount">0</span> sensores)
                            </label>
                        </div>
                        <div class="form-check" id="dashScopeOverdueWrap">
                            <input class="form-check-input" type="radio" name="dash_mobile_scope" id="dashScopeOverdue" value="overdue" onchange="updateDashMobileInviteScope()">
                            <label class="form-check-label small text-danger fw-semibold" for="dashScopeOverdue">
                                🔴 <strong>Únicamente los sensores Vencidos urgentes</strong> (<span id="dashScopeOverdueCount">0</span> sensores)
                            </label>
                        </div>
                        <div class="form-check" id="dashScopeAllWrap">
                            <input class="form-check-input" type="radio" name="dash_mobile_scope" id="dashScopeAll" value="all" onchange="updateDashMobileInviteScope()">
                            <label class="form-check-label small text-muted" for="dashScopeAll">
                                📦 <strong>Todos los sensores del grupo</strong> (<span id="dashScopeAllCount">0</span> sensores)
                            </label>
                        </div>
                    </div>

                    <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
                        <small class="text-muted" style="font-size: 0.72rem;">¿Quieres elegir sensores manualmente?</small>
                        <a href="#" id="dashLinkCustomizeBulk" class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold" style="font-size: 0.75rem;">
                            <i class="bi bi-check2-square"></i> Ajustar en Tabla Masiva
                        </a>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="dashInviteEmail" class="form-label fw-semibold small text-dark">
                        2. Email del Inspector <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-envelope text-primary"></i></span>
                        <input type="email" id="dashInviteEmail" class="form-control" placeholder="inspector@empresa.com">
                    </div>
                    <div class="form-text" style="font-size: 0.73rem;">
                        Al enviar, también podrás copiar el enlace/token o enviarlo por <strong>WhatsApp</strong>.
                    </div>
                </div>

                <input type="hidden" id="dashInviteGroupId" value="0">
                <input type="hidden" id="dashInviteSensorIds" value="">
                <input type="hidden" id="dashInviteLimit" value="0">

                <div id="dashInviteResultMsg" class="d-none mt-3"></div>
            </div>
            <div class="modal-footer bg-light" style="border-radius: 0 0 16px 16px;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary btn-sm fw-bold px-3" id="btnSendDashMobileInvite" onclick="sendDashMobileInvite()">
                    <i class="bi bi-send-check me-1"></i>
                    <span id="btnSendDashMobileInviteText">Generar Token y Enviar</span>
                    <span id="btnSendDashMobileInviteSpinner" class="spinner-border spinner-border-sm d-none ms-1"></span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const dashAlertsByGroupMap = @json(collect($scheduleAlerts['actionable'] ?? [])->keyBy('entity_id'));
    let dashMobileCtx = { group_id: 0, title: '', pending_ids: [], overdue_ids: [], all_ids: [] };

    function openDashboardMobileInvite(groupId) {
        const alertItem = dashAlertsByGroupMap[groupId];
        if (!alertItem) return;

        dashMobileCtx = {
            group_id: groupId,
            title: alertItem.title || ('Grupo #' + groupId),
            pending_ids: alertItem.pending_sensor_ids || [],
            overdue_ids: alertItem.overdue_sensor_ids || [],
            all_ids: alertItem.all_sensor_ids || alertItem.pending_sensor_ids || []
        };

        document.getElementById('dashMobileInviteTitle').innerHTML = `<i class="bi bi-folder-fill text-primary me-1"></i> ${dashMobileCtx.title}`;
        document.getElementById('dashScopePendingCount').innerText = dashMobileCtx.pending_ids.length;
        document.getElementById('dashScopeOverdueCount').innerText = dashMobileCtx.overdue_ids.length;
        document.getElementById('dashScopeAllCount').innerText = dashMobileCtx.all_ids.length;

        const hasOverdue = dashMobileCtx.overdue_ids.length > 0;
        const hasPending = dashMobileCtx.pending_ids.length > 0;
        document.getElementById('dashScopeOverdueWrap').classList.toggle('d-none', !hasOverdue);
        document.getElementById('dashScopePendingWrap').classList.toggle('d-none', !hasPending);

        if (hasPending) {
            document.getElementById('dashScopePending').checked = true;
        } else if (hasOverdue) {
            document.getElementById('dashScopeOverdue').checked = true;
        } else {
            document.getElementById('dashScopeAll').checked = true;
        }

        document.getElementById('dashInviteGroupId').value = groupId;
        document.getElementById('dashInviteResultMsg').classList.add('d-none');
        document.getElementById('dashInviteResultMsg').innerHTML = '';

        const btn = document.getElementById('btnSendDashMobileInvite');
        btn.disabled = false;
        document.getElementById('btnSendDashMobileInviteText').innerText = 'Generar Token y Enviar';
        document.getElementById('btnSendDashMobileInviteSpinner').classList.add('d-none');

        updateDashMobileInviteScope();
        new bootstrap.Modal(document.getElementById('dashboardMobileInviteModal')).show();
    }

    function updateDashMobileInviteScope() {
        const selected = document.querySelector('input[name="dash_mobile_scope"]:checked')?.value || 'pending';
        let ids = [];
        if (selected === 'overdue') {
            ids = dashMobileCtx.overdue_ids;
        } else if (selected === 'all') {
            ids = dashMobileCtx.all_ids;
        } else {
            ids = dashMobileCtx.pending_ids;
        }
        document.getElementById('dashInviteSensorIds').value = ids.join(',');
        document.getElementById('dashInviteLimit').value = ids.length;
        document.getElementById('dashMobileInviteBadge').innerText = `${ids.length} sensor(es)`;
        document.getElementById('dashLinkCustomizeBulk').href = `{{ route('bulk-measurements.select') }}?preselect_ids=${encodeURIComponent(ids.join(','))}`;
    }

    function sendDashMobileInvite() {
        const email = (document.getElementById('dashInviteEmail').value || '').trim();
        const sensorIds = document.getElementById('dashInviteSensorIds').value;
        const limit = parseInt(document.getElementById('dashInviteLimit').value) || 0;
        const groupId = parseInt(document.getElementById('dashInviteGroupId').value) || 0;
        const msgDiv = document.getElementById('dashInviteResultMsg');
        const btn = document.getElementById('btnSendDashMobileInvite');
        const btnText = document.getElementById('btnSendDashMobileInviteText');
        const spinner = document.getElementById('btnSendDashMobileInviteSpinner');

        if (!email) {
            msgDiv.innerHTML = '<div class="alert alert-warning py-2 small mb-0">Por favor ingresa el correo electrónico del inspector.</div>';
            msgDiv.classList.remove('d-none');
            return;
        }
        if (limit === 0 || !sensorIds) {
            msgDiv.innerHTML = '<div class="alert alert-warning py-2 small mb-0">No hay sensores seleccionados.</div>';
            msgDiv.classList.remove('d-none');
            return;
        }

        btn.disabled = true;
        spinner.classList.remove('d-none');
        btnText.innerText = 'Generando token...';
        msgDiv.classList.add('d-none');

        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        };
        const bearer = localStorage.getItem('token');
        if (bearer) headers['Authorization'] = 'Bearer ' + bearer;

        fetch('{{ route('schedule.invite-mobile') }}', {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({
                email: email,
                sensor_limit: limit,
                sensor_ids: sensorIds,
                group_id: groupId
            })
        })
        .then(async res => {
            const text = await res.text();
            if (!res.ok) {
                let snippet = text.substring(0, 250).replace(/</g, '&lt;');
                msgDiv.innerHTML = `<div class="alert alert-danger py-2 small mb-0">❌ Error (${res.status}):<br><code style="font-size:11px">${snippet}</code></div>`;
                msgDiv.classList.remove('d-none');
                btn.disabled = false;
                btnText.innerText = 'Generar Token y Enviar';
                spinner.classList.add('d-none');
                return null;
            }
            try { return JSON.parse(text); } catch { return null; }
        })
        .then(data => {
            if (!data) return;
            if (data.success) {
                const linkText = data.invite_link || '';
                const rawToken = data.token || '';
                const alertClass = data.mail_sent === false ? 'alert-warning' : 'alert-success';
                const alertIcon = data.mail_sent === false ? '⚠️' : '✅';
                msgDiv.innerHTML = `
                    <div class="alert ${alertClass} py-2 small mb-2">
                        ${alertIcon} ${data.message}
                        <div class="mt-1 text-muted" style="font-size: 0.72rem;">
                            Alcance asignado: <strong>${limit} sensor(es)</strong> (${dashMobileCtx.title}).
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold mb-1" style="font-size: 0.72rem;">Enlace de Autorización Directa (App Flutter):</label>
                        <div class="d-flex align-items-center gap-1">
                            <input type="text" class="form-control form-control-sm" value="${linkText}" readonly id="dashInviteLinkInput">
                            <button type="button" class="btn btn-sm btn-outline-secondary text-nowrap" onclick="navigator.clipboard.writeText(document.getElementById('dashInviteLinkInput').value); this.innerText='¡Copiado!'; setTimeout(()=>this.innerText='Copiar', 2000);">Copiar</button>
                            <a href="https://api.whatsapp.com/send?text=${encodeURIComponent('Hola! Aquí tienes el enlace y token para tomar las ' + limit + ' mediciones pendientes de ' + dashMobileCtx.title + ' en la App MedFlow Inspector: ' + linkText)}" target="_blank" class="btn btn-sm btn-success text-nowrap">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </a>
                        </div>
                    </div>
                    ${rawToken ? `
                    <div>
                        <label class="form-label small fw-bold mb-1" style="font-size: 0.72rem;">Token manual para pegar en la App Flutter:</label>
                        <div class="d-flex align-items-center gap-1">
                            <input type="text" class="form-control form-control-sm font-monospace" value="${rawToken}" readonly id="dashInviteRawTokenInput">
                            <button type="button" class="btn btn-sm btn-outline-dark text-nowrap" onclick="navigator.clipboard.writeText(document.getElementById('dashInviteRawTokenInput').value); this.innerText='¡Copiado!'; setTimeout(()=>this.innerText='Copiar Token', 2000);">Copiar Token</button>
                        </div>
                    </div>` : ''}
                `;
                btn.disabled = false;
                btnText.innerText = 'Generar Nuevo Token';
                spinner.classList.add('d-none');
            } else {
                msgDiv.innerHTML = `<div class="alert alert-danger py-2 small mb-0">❌ ${data.message || 'Error al generar token.'}</div>`;
                btn.disabled = false;
                btnText.innerText = 'Generar Token y Enviar';
                spinner.classList.add('d-none');
            }
            msgDiv.classList.remove('d-none');
        })
        .catch(err => {
            msgDiv.innerHTML = `<div class="alert alert-danger py-2 small mb-0">❌ Error de red: ${err.message}</div>`;
            msgDiv.classList.remove('d-none');
            btn.disabled = false;
            btnText.innerText = 'Generar Token y Enviar';
            spinner.classList.add('d-none');
        });
    }
</script>
@endpush
@endsection