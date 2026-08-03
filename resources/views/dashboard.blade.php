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
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
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

{{-- Configuración Global --}}
<div class="card mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
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
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
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