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
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <h5 class="mb-0 fw-bold text-dark">
            <i class="bi bi-calendar2-check text-primary me-2"></i>
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
                                    @if(count($overdueIds) > 0 && count($overdueIds) < count($allPendingIds))
                                        <form method="POST" action="{{ route('bulk-measurements.start') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="sensor_ids" value="{{ implode(',', $overdueIds) }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.72rem;">
                                                <i class="bi bi-exclamation-circle"></i> Vencidos ({{ count($overdueIds) }})
                                            </button>
                                        </form>
                                    @endif
                                    @if(!empty($allPendingIds))
                                        <form method="POST" action="{{ route('bulk-measurements.start') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="sensor_ids" value="{{ implode(',', $allPendingIds) }}">
                                            <button type="submit" class="btn btn-sm btn-primary py-0 px-2" style="font-size: 0.75rem;">
                                                <i class="bi bi-play-fill"></i> Medir ({{ count($allPendingIds) }})
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
@endsection