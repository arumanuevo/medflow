@extends('layouts.modern')

@section('title', 'Calendario de Tomas - MedFlow')

@section('content')
<style>
    .bg-orange {
        background-color: #fd7e14 !important;
        color: #fff !important;
    }
    .text-orange {
        color: #fd7e14 !important;
    }
    .border-orange {
        border-color: #fd7e14 !important;
    }
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 6px;
    }
    .calendar-weekday {
        text-align: center;
        font-weight: 700;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #495057;
        padding: 5px 4px;
        background: #f8f9fa;
        border-radius: 6px;
        border: 1px solid #e9ecef;
    }
    .calendar-day-cell {
        min-height: 92px;
        background: #ffffff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 6px;
        display: flex;
        flex-direction: column;
        transition: all 0.2s ease;
        position: relative;
    }
    .calendar-day-cell:hover {
        border-color: #0d6efd;
        box-shadow: 0 3px 10px rgba(13, 110, 253, 0.08);
    }
    .calendar-day-cell.is-today {
        border: 2px solid #0d6efd;
        background: #f4f8ff;
    }
    .calendar-day-cell.empty-cell {
        background: #f8f9fa;
        opacity: 0.55;
        min-height: 60px;
    }
    .calendar-day-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
    }
    .calendar-day-number {
        font-weight: 700;
        font-size: 0.8rem;
        color: #343a40;
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .calendar-day-cell.is-today .calendar-day-number {
        background: #0d6efd;
        color: #fff;
    }
    .btn-add-day-schedule {
        opacity: 0;
        transition: opacity 0.15s ease;
        padding: 0 4px !important;
        font-size: 0.68rem !important;
        line-height: 1.2;
    }
    .calendar-day-cell:hover .btn-add-day-schedule {
        opacity: 1;
    }
    .calendar-event-pill {
        display: block;
        width: 100%;
        text-align: left;
        padding: 3px 6px;
        margin-bottom: 4px;
        border-radius: 6px;
        font-size: 0.69rem;
        line-height: 1.2;
        cursor: pointer;
        border: 1px solid rgba(0,0,0,0.08);
        border-left-width: 3px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .calendar-event-pill:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    }
    .calendar-event-pill.status-completed {
        border-left-color: #198754;
        background: #f0fdf4;
    }
    .calendar-event-pill.status-in_window {
        border-left-color: #ffc107;
        background: #fffbeb;
    }
    .calendar-event-pill.status-in_tolerance {
        border-left-color: #fd7e14;
        background: #fff7ed;
    }
    .calendar-event-pill.status-overdue {
        border-left-color: #dc3545;
        background: #fef2f2;
    }
    .calendar-event-pill.status-scheduled {
        border-left-color: #0d6efd;
        background: #eff6ff;
    }
    @media (max-width: 991px) {
        .calendar-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .calendar-weekday {
            display: none;
        }
        .calendar-day-cell.empty-cell {
            display: none;
        }
        .btn-add-day-schedule {
            opacity: 1;
        }
    }
    @media (max-width: 575px) {
        .calendar-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid py-2">
    {{-- Mensajes flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Cabecera Principal --}}
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 14px; overflow: hidden;">
        <div class="card-body p-3 px-4" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: #fff;">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h4 class="mb-1 fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-calendar2-check-fill"></i>
                        Calendario de Programación de Tomas
                    </h4>
                    <p class="mb-0 opacity-75 small">
                        Programa qué día del mes (ej: el día 15) o fecha específica se debe medir cada grupo o sensor particular y define sus días de tolerancia.
                    </p>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <button type="button" class="btn btn-light fw-bold shadow-sm" onclick="openScheduleModal()">
                        <i class="bi bi-calendar-plus text-primary me-1"></i> Programar Toma / Tolerancia
                    </button>
                    <a href="{{ route('bulk-measurements.select') }}" class="btn btn-outline-light fw-semibold">
                        <i class="bi bi-rulers me-1"></i> Ir a Tomar Mediciones
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Banner de Alertas Operativas Activas Consolidado por Grupo (1 tarjeta por Grupo) --}}
    @if(count($activeAlerts['actionable']) > 0)
        <div class="card border-0 shadow-sm mb-3 border-start border-4 border-warning" style="border-radius: 12px;">
            <div class="card-header py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-bell-fill text-warning me-2"></i>
                        Avisos Activos por Grupo ({{ count($activeAlerts['actionable']) }} {{ count($activeAlerts['actionable']) === 1 ? 'grupo' : 'grupos' }})
                    </h6>
                    <small class="text-white-50">Consolidado automático por grupo (sin repetir tarjetas aunque existan miles de sensores)</small>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-1">
                    @if(($activeAlerts['counts']['overdue'] ?? 0) > 0)
                        <span class="badge bg-danger">🔴 {{ $activeAlerts['counts']['overdue'] }} sensor(es) vencidos</span>
                    @endif
                    @if(($activeAlerts['counts']['in_tolerance'] ?? 0) > 0)
                        <span class="badge bg-orange">🟠 {{ $activeAlerts['counts']['in_tolerance'] }} en tolerancia</span>
                    @endif
                    @if(($activeAlerts['counts']['in_window'] ?? 0) > 0)
                        <span class="badge bg-warning text-dark">🟡 {{ $activeAlerts['counts']['in_window'] }} hoy / en ventana</span>
                    @endif
                </div>
            </div>
            <div class="card-body py-3 px-3">
                <div class="row g-3">
                    @foreach($activeAlerts['actionable'] as $idx => $alert)
                        @php
                            $bd = $alert['breakdown'] ?? [];
                            $overdueIds = $alert['overdue_sensor_ids'] ?? [];
                            $allPendingIds = $alert['pending_sensor_ids'] ?? [];
                        @endphp
                        <div class="col-md-6 col-xl-4 {{ $idx >= 6 ? 'd-none extra-group-alert' : '' }}">
                            <div class="p-3 rounded-3 border d-flex flex-column justify-content-between h-100"
                                 style="border-left: 4px solid {{ $alert['color_hex'] }} !important; background: #fafafa;">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <span class="fw-bold text-dark small">
                                            <i class="bi bi-folder-fill text-primary me-1"></i>
                                            {{ $alert['title'] }}
                                        </span>
                                        <span class="badge {{ $alert['badge_class'] }}" style="font-size: 0.68rem;">
                                            {{ $alert['status_label'] }}
                                        </span>
                                    </div>

                                    {{-- Desglose compacto por estado dentro del grupo --}}
                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                        @if(($bd['overdue'] ?? 0) > 0)
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle" style="font-size: 0.68rem;">
                                                🔴 {{ $bd['overdue'] }} vencido(s)
                                            </span>
                                        @endif
                                        @if(($bd['in_tolerance'] ?? 0) > 0)
                                            <span class="badge bg-warning bg-opacity-10 text-orange border border-warning-subtle" style="font-size: 0.68rem;">
                                                🟠 {{ $bd['in_tolerance'] }} en tolerancia
                                            </span>
                                        @endif
                                        @if(($bd['in_window'] ?? 0) > 0)
                                            <span class="badge bg-warning bg-opacity-25 text-dark border border-warning" style="font-size: 0.68rem;">
                                                🟡 {{ $bd['in_window'] }} hoy / en ventana
                                            </span>
                                        @endif
                                        @if(($bd['completed'] ?? 0) > 0)
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle" style="font-size: 0.68rem;">
                                                🟢 {{ $bd['completed'] }} al día
                                            </span>
                                        @endif
                                    </div>

                                    <p class="mb-2 text-muted" style="font-size: 0.76rem;">
                                        {{ $alert['status_message'] }}
                                    </p>
                                </div>
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2 border-top">
                                    <small class="text-muted" style="font-size: 0.7rem;">
                                        <i class="bi bi-calendar-event"></i> Meta: <strong>{{ $alert['target_date_formatted'] }}</strong>
                                        (Tol: +{{ $alert['tolerance_days'] }}d)
                                    </small>
                                    <div class="d-flex flex-wrap gap-1">
                                        <button type="button"
                                                class="btn btn-sm btn-dark py-0 px-2"
                                                style="font-size: 0.72rem;"
                                                title="Asignar estos sensores a un Inspector en la App Móvil (Flutter / Offline) mediante Token por Email o WhatsApp"
                                                onclick="openMobileInviteForAlertGroup({{ $alert['entity_id'] }})">
                                            <i class="bi bi-phone-fill text-info"></i> Enviar a App
                                        </button>
                                        @if(count($overdueIds) > 0 && count($overdueIds) < count($allPendingIds))
                                            <form method="POST" action="{{ route('bulk-measurements.start') }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="sensor_ids" value="{{ implode(',', $overdueIds) }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.72rem;" title="Cargar por Web únicamente los sensores vencidos de este grupo">
                                                    <i class="bi bi-exclamation-circle"></i> Vencidos Web ({{ count($overdueIds) }})
                                                </button>
                                            </form>
                                        @endif
                                        @if(!empty($allPendingIds))
                                            <form method="POST" action="{{ route('bulk-measurements.start') }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="sensor_ids" value="{{ implode(',', $allPendingIds) }}">
                                                <button type="submit" class="btn btn-sm btn-primary py-0 px-2" style="font-size: 0.72rem;" title="Cargar mediciones desde la vista Web con tu usuario administrador">
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

                @if(count($activeAlerts['actionable']) > 6)
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="document.querySelectorAll('.extra-group-alert').forEach(el => el.classList.toggle('d-none')); this.innerText = this.innerText.includes('Ver') ? 'Ocultar grupos adicionales' : 'Ver los {{ count($activeAlerts['actionable']) - 6 }} grupos adicionales';">
                            Ver los {{ count($activeAlerts['actionable']) - 6 }} grupos adicionales
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Tarjetas Resumen del Semáforo del Mes --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 bg-success bg-opacity-10 text-success fs-4">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-success lh-1">{{ $calendarData['summary']['completed'] }}</div>
                        <small class="text-muted" style="font-size: 0.75rem;">Cumplidos este mes</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 bg-warning bg-opacity-25 text-dark fs-4">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-dark lh-1">{{ $calendarData['summary']['in_window'] }}</div>
                        <small class="text-muted" style="font-size: 0.75rem;">En Ventana (Toca medir)</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 bg-orange bg-opacity-10 fs-4" style="background: #fff3e6 !important; color: #fd7e14 !important;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-orange lh-1">{{ $calendarData['summary']['in_tolerance'] }}</div>
                        <small class="text-muted" style="font-size: 0.75rem;">En Tolerancia (Margen)</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 bg-danger bg-opacity-10 text-danger fs-4">
                        <i class="bi bi-exclamation-octagon-fill"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-danger lh-1">{{ $calendarData['summary']['overdue'] }}</div>
                        <small class="text-muted" style="font-size: 0.75rem;">Vencidos</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 bg-primary bg-opacity-10 text-primary fs-4">
                        <i class="bi bi-calendar-date"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-primary lh-1">{{ $calendarData['summary']['scheduled'] }}</div>
                        <small class="text-muted" style="font-size: 0.75rem;">Programados</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Calendario Mensual Interactivo --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-header py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('schedule.index', ['year' => $prevMonthDate->year, 'month' => $prevMonthDate->month]) }}"
                   class="btn btn-sm btn-outline-light">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <h5 class="mb-0 fw-bold px-2 text-white">
                    {{ $monthName }} {{ $year }}
                </h5>
                <a href="{{ route('schedule.index', ['year' => $nextMonthDate->year, 'month' => $nextMonthDate->month]) }}"
                   class="btn btn-sm btn-outline-light">
                    <i class="bi bi-chevron-right"></i>
                </a>
                <a href="{{ route('schedule.index') }}" class="btn btn-sm btn-light fw-semibold ms-2">
                    Hoy ({{ now()->format('d/m') }})
                </a>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2 small">
                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Cumplido</span>
                <span class="badge bg-warning text-dark"><i class="bi bi-bell me-1"></i>En ventana</span>
                <span class="badge bg-orange"><i class="bi bi-hourglass-split me-1"></i>En tolerancia</span>
                <span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Vencido</span>
                <span class="badge bg-primary"><i class="bi bi-calendar me-1"></i>Programado</span>
            </div>
        </div>

        <div class="card-body p-2 p-md-3">
            @php
                // Lunes = 1 ... Domingo = 7
                $firstDayOfWeek = $currentMonthDate->dayOfWeekIso;
                $daysInMonth = $currentMonthDate->daysInMonth;
                $todayStr = now()->format('Y-m-d');
            @endphp

            <div class="calendar-grid mb-2">
                <div class="calendar-weekday">Lunes</div>
                <div class="calendar-weekday">Martes</div>
                <div class="calendar-weekday">Miércoles</div>
                <div class="calendar-weekday">Jueves</div>
                <div class="calendar-weekday">Viernes</div>
                <div class="calendar-weekday">Sábado</div>
                <div class="calendar-weekday">Domingo</div>
            </div>

            <div class="calendar-grid">
                {{-- Celdas vacías previas al día 1 --}}
                @for($i = 1; $i < $firstDayOfWeek; $i++)
                    <div class="calendar-day-cell empty-cell"></div>
                @endfor

                {{-- Días del mes --}}
                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $cellDateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                        $isTodayCell = ($cellDateStr === $todayStr);
                        $dayEvents = $calendarData['events_by_day'][$day] ?? [];
                    @endphp
                    <div class="calendar-day-cell {{ $isTodayCell ? 'is-today' : '' }}">
                        <div class="calendar-day-header">
                            <span class="calendar-day-number" title="{{ $isTodayCell ? 'Hoy' : '' }}">
                                {{ $day }}
                            </span>
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary btn-add-day-schedule"
                                    title="Programar toma para el día {{ $day }}"
                                    onclick="openScheduleModalForDay({{ $day }}, '{{ $cellDateStr }}')">
                                <i class="bi bi-plus-lg"></i> Programar
                            </button>
                        </div>

                        <div class="calendar-day-events flex-grow-1">
                            @foreach($dayEvents as $evIdx => $ev)
                                <div class="calendar-event-pill status-{{ $ev['status'] }} {{ $evIdx >= 3 ? 'd-none extra-day-ev-' . $day : '' }}"
                                     onclick="openEventDetailById('{{ $ev['id'] }}')">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-truncate text-dark" style="max-width: 78%;">
                                            <i class="bi {{ $ev['type'] === 'group' ? 'bi-folder-fill text-primary' : 'bi-speedometer2 text-info' }} me-1"></i>
                                            {{ $ev['title'] }}
                                        </span>
                                        <span class="badge {{ $ev['badge_class'] }}" style="font-size: 0.62rem; padding: 2px 4px;">
                                            {{ $ev['measured_sensors'] }}/{{ $ev['total_sensors'] }}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1 text-muted" style="font-size: 0.64rem;">
                                        <span>{{ $ev['status_label'] }}</span>
                                        <span title="Preaviso: -{{ $ev['pre_notice_days'] }}d | Tolerancia: +{{ $ev['tolerance_days'] }}d">
                                            Tol: -{{ $ev['pre_notice_days'] }}/+{{ $ev['tolerance_days'] }}d
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                            @if(count($dayEvents) > 3)
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none w-100 text-center fw-semibold"
                                        style="font-size: 0.68rem;"
                                        onclick="document.querySelectorAll('.extra-day-ev-{{ $day }}').forEach(el => el.classList.toggle('d-none')); this.innerText = this.innerText.includes('+') ? 'Mostrar menos' : '+{{ count($dayEvents) - 3 }} más en este día';">
                                    +{{ count($dayEvents) - 3 }} más en este día
                                </button>
                            @endif
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>

    {{-- Resumen de Configuración de Reglas por Grupo y Excepciones por Sensor --}}
    <div class="row g-4">
        {{-- Tabla de Grupos --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-folder2-open text-warning me-2"></i>
                        Reglas de Programación por Grupo de Sensores
                    </h6>
                    <small class="text-white-50">Aplica a todos los sensores del grupo</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Grupo</th>
                                    <th>Regla de Toma</th>
                                    <th>Preaviso / Tolerancia</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($groups as $g)
                                    @php
                                        $gCfg = $groupsConfigMap[$g->id];
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $g->name }}</div>
                                            <small class="text-muted">{{ $g->sensors_count }} sensor(es)</small>
                                        </td>
                                        <td>
                                            @if($gCfg['mode'] === 'fixed_day')
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle">
                                                    <i class="bi bi-calendar-check me-1"></i> Día {{ $gCfg['day_of_month'] }} de cada mes
                                                </span>
                                            @elseif($gCfg['mode'] === 'specific_date')
                                                <span class="badge bg-info bg-opacity-10 text-dark border border-info-subtle">
                                                    <i class="bi bi-calendar-event me-1"></i> Fecha: {{ $gCfg['specific_date'] }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                                                    <i class="bi bi-arrow-repeat me-1"></i> Cada {{ $gCfg['interval_days'] }} días
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="d-block text-muted">
                                                🔔 Aviso: <strong>{{ $gCfg['pre_notice_days'] }} días antes</strong>
                                            </small>
                                            <small class="d-block text-muted">
                                                ⏳ Tolerancia: <strong>+{{ $gCfg['tolerance_days'] }} días después</strong>
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-dark"
                                                        title="Generar token de inspección para App Móvil (Flutter / Offline)"
                                                        onclick="openMobileInviteForAlertGroup({{ $g->id }})">
                                                    <i class="bi bi-phone-fill text-info"></i> Token App
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick='editGroupSchedule({{ $g->id }}, @json($gCfg))'>
                                                    <i class="bi bi-sliders me-1"></i> Configurar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            No tienes grupos creados todavía.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla de Sensores Particulares con Programación Propia (Excepciones) --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                @php
                    $overriddenSensors = $sensors->filter(fn($s) => !empty($sensorsConfigMap[$s->id]['override']));
                @endphp
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-speedometer2 text-info me-2"></i>
                        Sensores con Programación Particular ({{ $overriddenSensors->count() }})
                    </h6>
                    <button type="button" class="btn btn-sm btn-info text-dark fw-semibold" onclick="openScheduleModal('sensor')">
                        <i class="bi bi-plus-lg"></i> Añadir excepción
                    </button>
                </div>
                @if($overriddenSensors->count() > 5)
                    <div class="px-3 py-2 bg-light border-bottom">
                        <input type="text" class="form-control form-control-sm" id="searchOverriddenSensors"
                               placeholder="Filtrar sensores con excepción por nombre o código..."
                               oninput="filterOverrideTable(this.value)">
                    </div>
                @endif
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 360px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Sensor</th>
                                    <th>Programación</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="overrideSensorsTableBody">
                                @forelse($overriddenSensors as $s)
                                    @php
                                        $sCfg = $sensorsConfigMap[$s->id];
                                    @endphp
                                    <tr class="override-sensor-row">
                                        <td>
                                            <div class="fw-bold text-dark small">{{ $s->name }}</div>
                                            <small class="text-muted" style="font-size: 0.72rem;">
                                                {{ $s->identifier }} · {{ $s->group->name ?? '' }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($sCfg['mode'] === 'fixed_day')
                                                <span class="badge bg-info text-dark">Día {{ $sCfg['day_of_month'] }} / mes</span>
                                            @elseif($sCfg['mode'] === 'specific_date')
                                                <span class="badge bg-info text-dark">{{ $sCfg['specific_date'] }}</span>
                                            @else
                                                <span class="badge bg-secondary">Cada {{ $sCfg['interval_days'] }}d</span>
                                            @endif
                                            <div class="text-muted" style="font-size: 0.7rem;">
                                                Aviso: -{{ $sCfg['pre_notice_days'] }}d | Tol: +{{ $sCfg['tolerance_days'] }}d
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary"
                                                        title="Editar programación"
                                                        onclick='editSensorSchedule({{ $s->id }}, @json($sCfg))'>
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" action="{{ route('schedule.sensor.remove-override', $s->id) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger"
                                                            title="Quitar excepción y heredar del grupo">
                                                        <i class="bi bi-arrow-counterclockwise"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4 small">
                                            Todos los sensores heredan actualmente la programación de su grupo.<br>
                                            Puedes programar un sensor individual haciendo clic en <strong>"Añadir excepción"</strong> o en cualquier día del calendario.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- =========================================================================
   MODAL 1: PROGRAMAR TOMA / CONFIGURAR TOLERANCIAS (GRUPO O SENSOR)
   ========================================================================= --}}
<div class="modal fade" id="scheduleConfigModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form method="POST" action="{{ route('schedule.save') }}" id="scheduleConfigForm">
                @csrf
                <div class="modal-header bg-primary text-white" style="border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-calendar-plus me-2"></i>
                        <span id="scheduleModalTitle">Programar Toma de Medición y Tolerancias</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- 1. ¿A quién aplica? --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">1. ¿Qué deseas programar en el calendario?</label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="target_type" id="targetTypeGroup" value="group" checked>
                                <label class="btn btn-outline-primary w-100 text-start p-3 rounded-3" for="targetTypeGroup">
                                    <div class="fw-bold"><i class="bi bi-folder-fill me-1"></i> Un Grupo de Sensores Completo</div>
                                    <small class="d-block opacity-75">Todos los medidores del grupo seguirán esta fecha.</small>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="target_type" id="targetTypeSensor" value="sensor">
                                <label class="btn btn-outline-primary w-100 text-start p-3 rounded-3" for="targetTypeSensor">
                                    <div class="fw-bold"><i class="bi bi-speedometer2 me-1"></i> Sensor(es) Particular(es)</div>
                                    <small class="d-block opacity-75">Define una fecha especial para uno o varios sensores.</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Selector de Grupo --}}
                    <div class="mb-4" id="groupTargetSelector">
                        <label for="modalGroupId" class="form-label fw-semibold">Seleccionar Grupo de Sensores <span class="text-danger">*</span></label>
                        <select class="form-select" id="modalGroupId" name="group_id">
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->sensors_count }} sensores)</option>
                            @endforeach
                        </select>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="reset_sensor_overrides" id="resetSensorOverrides" value="1">
                            <label class="form-check-label small text-muted" for="resetSensorOverrides">
                                Aplicar también a los sensores de este grupo que tenían fechas particulares personalizadas
                            </label>
                        </div>
                    </div>

                    {{-- Selector de Sensores Particulares con Buscador Rápido --}}
                    <div class="mb-4 d-none" id="sensorTargetSelector">
                        <label for="modalSensorIds" class="form-label fw-semibold">Seleccionar Sensor(es) Particular(es) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control" id="filterModalSensorsInput"
                                   placeholder="Buscar sensor entre los {{ $sensors->count() }} disponibles por nombre, código o grupo..."
                                   oninput="filterModalSelectSensors(this.value)">
                        </div>
                        <select class="form-select" id="modalSensorIds" name="sensor_ids[]" multiple size="7">
                            @foreach($sensors as $s)
                                <option value="{{ $s->id }}">
                                    {{ $s->name }} ({{ $s->identifier }}) — Grupo: {{ $s->group->name ?? 'Sin grupo' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Puedes buscar arriba y mantener presionada la tecla Ctrl (o Cmd) para elegir varios sensores a la vez.</div>
                    </div>

                    {{-- 2. Modalidad de Fecha --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">2. ¿Cuándo se debe tomar la medición?</label>
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="mode" id="modeFixedDay" value="fixed_day" checked>
                                <label class="btn btn-outline-dark w-100 p-2 rounded-3" for="modeFixedDay">
                                    <i class="bi bi-calendar-check d-block fs-5 mb-1 text-primary"></i>
                                    <span class="fw-bold small">Día fijo de cada mes</span>
                                    <small class="d-block text-muted" style="font-size: 0.7rem;">Ej: Todos los días 15</small>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="mode" id="modeSpecificDate" value="specific_date">
                                <label class="btn btn-outline-dark w-100 p-2 rounded-3" for="modeSpecificDate">
                                    <i class="bi bi-calendar-event d-block fs-5 mb-1 text-info"></i>
                                    <span class="fw-bold small">Fecha puntual</span>
                                    <small class="d-block text-muted" style="font-size: 0.7rem;">Un día específico del año</small>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="mode" id="modeInterval" value="interval">
                                <label class="btn btn-outline-dark w-100 p-2 rounded-3" for="modeInterval">
                                    <i class="bi bi-arrow-repeat d-block fs-5 mb-1 text-secondary"></i>
                                    <span class="fw-bold small">Cada N días</span>
                                    <small class="d-block text-muted" style="font-size: 0.7rem;">Desde la última lectura</small>
                                </label>
                            </div>
                        </div>

                        {{-- Input Día Fijo del Mes --}}
                        <div id="inputFixedDayBox" class="p-3 bg-light rounded-3 border">
                            <label for="modalDayOfMonth" class="form-label fw-semibold mb-1">
                                Día del mes programado para la toma (1 al 31):
                            </label>
                            <div class="input-group" style="max-width: 260px;">
                                <span class="input-group-text bg-white"><i class="bi bi-calendar-day text-primary"></i></span>
                                <input type="number" class="form-control fw-bold" id="modalDayOfMonth" name="day_of_month" value="15" min="1" max="31">
                                <span class="input-group-text bg-white">de cada mes</span>
                            </div>
                            <small class="text-muted d-block mt-1">
                                Si eliges 31 y el mes tiene 28 o 30 días, el sistema lo ajustará automáticamente al último día del mes.
                            </small>
                        </div>

                        {{-- Input Fecha Puntual --}}
                        <div id="inputSpecificDateBox" class="p-3 bg-light rounded-3 border d-none">
                            <label for="modalSpecificDate" class="form-label fw-semibold mb-1">
                                Fecha exacta programada:
                            </label>
                            <input type="date" class="form-control" id="modalSpecificDate" name="specific_date"
                                   value="{{ now()->format('Y-m-15') }}" style="max-width: 260px;">
                        </div>

                        {{-- Input Intervalo de Días --}}
                        <div id="inputIntervalBox" class="p-3 bg-light rounded-3 border d-none">
                            <label for="modalIntervalDays" class="form-label fw-semibold mb-1">
                                Repetir cada cuántos días desde la última medición:
                            </label>
                            <div class="input-group" style="max-width: 260px;">
                                <input type="number" class="form-control fw-bold" id="modalIntervalDays" name="interval_days" value="30" min="1" max="365">
                                <span class="input-group-text bg-white">días</span>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Ventana de Preaviso y Días de Tolerancia --}}
                    <div class="p-3 rounded-3 border border-warning bg-warning bg-opacity-10">
                        <h6 class="fw-bold text-dark mb-2">
                            <i class="bi bi-sliders me-1"></i> 3. Ventana de Preaviso y Tolerancia (en días)
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="modalPreNoticeDays" class="form-label small fw-bold text-dark mb-1">
                                    🔔 Días de Preaviso (Antes de la fecha)
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="modalPreNoticeDays" name="pre_notice_days" value="3" min="0" max="60" required>
                                    <span class="input-group-text">días antes</span>
                                </div>
                                <small class="text-muted" style="font-size: 0.72rem;">
                                    Ej: Si pones 3 y la toma es el día 15, el sistema te avisará desde el día 12 ("En ventana").
                                </small>
                            </div>
                            <div class="col-md-6">
                                <label for="modalToleranceDays" class="form-label small fw-bold text-dark mb-1">
                                    ⏳ Días de Tolerancia (Después de la fecha)
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="modalToleranceDays" name="tolerance_days" value="5" min="0" max="60" required>
                                    <span class="input-group-text">días de gracia</span>
                                </div>
                                <small class="text-muted" style="font-size: 0.72rem;">
                                    Ej: Si pones 5 y la toma es el día 15, tendrás margen hasta el día 20 antes de que pase a "Vencido".
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light" style="border-radius: 0 0 16px 16px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-check-circle me-1"></i> Guardar en Calendario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- =========================================================================
   MODAL 2: DETALLE DE COMPROMISO DEL CALENDARIO (CON BUSCADOR Y PAGINACIÓN)
   ========================================================================= --}}
<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header text-white" id="eventDetailHeader" style="background: #0d6efd; border-radius: 16px 16px 0 0;">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="eventDetailTitle">Detalle de Toma</h5>
                    <small class="opacity-75" id="eventDetailSubtitle"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert mb-3" id="eventDetailStatusAlert">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <strong id="eventDetailStatusLabel">Estado</strong>:
                            <span id="eventDetailStatusMessage"></span>
                        </div>
                        <span class="badge bg-dark" id="eventDetailProgressBadge"></span>
                    </div>
                </div>

                <div class="row g-3 mb-3 text-center">
                    <div class="col-md-4">
                        <div class="p-2 border rounded-3 bg-light">
                            <small class="text-muted d-block">Inicio Ventana (Preaviso)</small>
                            <strong class="text-dark" id="eventDetailWindowStart">-</strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-2 border border-primary rounded-3 bg-primary bg-opacity-10">
                            <small class="text-primary fw-bold d-block">Fecha Programada</small>
                            <strong class="text-primary fs-6" id="eventDetailTargetDate">-</strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-2 border rounded-3 bg-light">
                            <small class="text-muted d-block">Límite de Tolerancia</small>
                            <strong class="text-danger" id="eventDetailToleranceEnd">-</strong>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-list-check me-1"></i> Sensores en este Compromiso
                    </h6>
                    <div class="d-flex flex-wrap gap-2">
                        <select class="form-select form-select-sm" id="detailSensorStatusFilter" style="width: auto;" onchange="resetAndRenderDetailSensors()">
                            <option value="all">Todos los estados</option>
                            <option value="pending">⏳ Solo Pendientes</option>
                            <option value="overdue">🔴 Solo Vencidos</option>
                            <option value="in_tolerance">🟠 Solo En Tolerancia</option>
                            <option value="in_window">🟡 Solo En Ventana / Hoy</option>
                            <option value="completed">🟢 Solo Medidos</option>
                        </select>
                        <div class="input-group input-group-sm" style="width: 230px;">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control" id="detailSensorSearch"
                                   placeholder="Buscar nombre o código..."
                                   oninput="resetAndRenderDetailSensors()">
                        </div>
                    </div>
                </div>

                <div class="table-responsive border rounded-3" style="max-height: 290px; overflow-y: auto;">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Sensor</th>
                                <th>Identificador</th>
                                <th>Estado en Ciclo</th>
                                <th class="text-end">Acción Individual</th>
                            </tr>
                        </thead>
                        <tbody id="eventDetailSensorsTableBody"></tbody>
                    </table>
                </div>

                {{-- Paginación en cliente para soportar 1.500+ sensores sin trabar el navegador --}}
                <div class="d-flex justify-content-between align-items-center mt-2 small text-muted" id="detailPaginationBar">
                    <span id="detailPaginationInfo">Mostrando 0 de 0</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary" id="btnDetailPrevPage" onclick="changeDetailPage(-1)">
                            <i class="bi bi-chevron-left"></i> Anterior
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnDetailNextPage" onclick="changeDetailPage(1)">
                            Siguiente <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between flex-wrap gap-2" style="border-radius: 0 0 16px 16px;">
                <button type="button" class="btn btn-outline-secondary" id="btnEditFromDetail">
                    <i class="bi bi-sliders me-1"></i> Editar Fecha / Tolerancia
                </button>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-dark fw-semibold" id="btnMobileInviteFromDetail" onclick="openMobileInviteFromDetailModal()">
                        <i class="bi bi-phone-fill text-info me-1"></i> Enviar a App Inspector (Token)
                    </button>
                    <form method="POST" action="{{ route('bulk-measurements.start') }}" id="formStartBulkFromCalendar">
                        @csrf
                        <input type="hidden" name="sensor_ids" id="inputBulkSensorIds" value="">
                        <button type="submit" class="btn btn-primary fw-bold" id="btnStartBulkFromCalendar">
                            <i class="bi bi-laptop me-1"></i> Iniciar Toma Web
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- =========================================================================
   MODAL 3: ASIGNAR SENSORES A INSPECTOR MÓVIL (APP FLUTTER / OFFLINE TOKEN)
   ========================================================================= --}}
<div class="modal fade" id="scheduleMobileInviteModal" tabindex="-1" aria-hidden="true">
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
                    El inspector solo verá y medirá los sensores que determines aquí, sin necesidad de entrar al panel administrativo web.
                </p>

                <div class="p-3 rounded-3 bg-light border mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-dark small" id="mobileInviteTargetTitle">
                            <i class="bi bi-folder-fill text-primary me-1"></i> Grupo seleccionado
                        </span>
                        <span class="badge bg-primary" id="mobileInviteSelectedBadge">0 sensores</span>
                    </div>

                    <label class="form-label small fw-bold text-dark mb-1">
                        1. ¿Qué sensores deseas incluir en el Token del Inspector?
                    </label>
                    <div class="d-flex flex-column gap-2" id="mobileInviteScopeRadios">
                        <div class="form-check" id="scopePendingWrap">
                            <input class="form-check-input" type="radio" name="mobile_invite_scope" id="scopePending" value="pending" checked onchange="updateMobileInviteScopeSelection()">
                            <label class="form-check-label small" for="scopePending">
                                ⏳ <strong>Todos los pendientes por medir</strong> (<span id="scopePendingCount">0</span> sensores)
                            </label>
                        </div>
                        <div class="form-check" id="scopeOverdueWrap">
                            <input class="form-check-input" type="radio" name="mobile_invite_scope" id="scopeOverdue" value="overdue" onchange="updateMobileInviteScopeSelection()">
                            <label class="form-check-label small text-danger fw-semibold" for="scopeOverdue">
                                🔴 <strong>Únicamente los sensores Vencidos urgentes</strong> (<span id="scopeOverdueCount">0</span> sensores)
                            </label>
                        </div>
                        <div class="form-check" id="scopeAllWrap">
                            <input class="form-check-input" type="radio" name="mobile_invite_scope" id="scopeAll" value="all" onchange="updateMobileInviteScopeSelection()">
                            <label class="form-check-label small text-muted" for="scopeAll">
                                📦 <strong>Todos los sensores del lote/grupo</strong> (<span id="scopeAllCount">0</span> sensores)
                            </label>
                        </div>
                    </div>

                    <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
                        <small class="text-muted" style="font-size: 0.72rem;">¿Quieres elegir sensores manualmente uno por uno?</small>
                        <a href="#" id="linkCustomizeInBulkSelect" class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold" style="font-size: 0.75rem;">
                            <i class="bi bi-check2-square"></i> Ajustar en Tabla Masiva
                        </a>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="scheduleInviteEmail" class="form-label fw-semibold small text-dark">
                        2. Email del Inspector (Recibirá el Token y Enlace directo) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-envelope text-primary"></i></span>
                        <input type="email" id="scheduleInviteEmail" class="form-control" placeholder="inspector@empresa.com">
                    </div>
                    <div class="form-text" style="font-size: 0.73rem;">
                        Al enviar, también podrás <strong>copiar el enlace o el token</strong> directamente o enviarlo por <strong>WhatsApp</strong>.
                    </div>
                </div>

                <input type="hidden" id="scheduleInviteGroupId" value="0">
                <input type="hidden" id="scheduleInviteSensorIds" value="">
                <input type="hidden" id="scheduleInviteLimit" value="0">

                <div id="scheduleInviteResultMsg" class="d-none mt-3"></div>
            </div>
            <div class="modal-footer bg-light" style="border-radius: 0 0 16px 16px;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary btn-sm fw-bold px-3" id="btnSendScheduleMobileInvite" onclick="sendScheduleMobileInvite()">
                    <i class="bi bi-send-check me-1"></i>
                    <span id="btnSendScheduleMobileInviteText">Generar Token y Enviar</span>
                    <span id="btnSendScheduleMobileInviteSpinner" class="spinner-border spinner-border-sm d-none ms-1"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const groupsConfigMap = @json($groupsConfigMap);
    const sensorsConfigMap = @json($sensorsConfigMap);
    const calendarEventsMap = @json(collect($calendarData['all_events'])->keyBy('id'));
    const allAlertsByGroupMap = @json(collect($activeAlerts['all'] ?? [])->keyBy('entity_id'));
    let currentDetailEvent = null;
    let currentDetailPage = 1;
    const DETAIL_PAGE_SIZE = 25;
    let currentMobileInviteContext = {
        group_id: 0,
        title: '',
        pending_ids: [],
        overdue_ids: [],
        all_ids: []
    };

    function syncTargetTypeUI() {
        const type = document.querySelector('input[name="target_type"]:checked').value;
        if (type === 'group') {
            document.getElementById('groupTargetSelector').classList.remove('d-none');
            document.getElementById('sensorTargetSelector').classList.add('d-none');
        } else {
            document.getElementById('groupTargetSelector').classList.add('d-none');
            document.getElementById('sensorTargetSelector').classList.remove('d-none');
        }
    }

    function syncModeUI() {
        const mode = document.querySelector('input[name="mode"]:checked').value;
        document.getElementById('inputFixedDayBox').classList.toggle('d-none', mode !== 'fixed_day');
        document.getElementById('inputSpecificDateBox').classList.toggle('d-none', mode !== 'specific_date');
        document.getElementById('inputIntervalBox').classList.toggle('d-none', mode !== 'interval');
    }

    document.querySelectorAll('input[name="target_type"]').forEach(el => {
        el.addEventListener('change', syncTargetTypeUI);
    });
    document.querySelectorAll('input[name="mode"]').forEach(el => {
        el.addEventListener('change', syncModeUI);
    });

    function filterModalSelectSensors(query) {
        const q = (query || '').toLowerCase().trim();
        const select = document.getElementById('modalSensorIds');
        Array.from(select.options).forEach(opt => {
            const match = !q || opt.text.toLowerCase().includes(q);
            opt.hidden = !match;
        });
    }

    function filterOverrideTable(query) {
        const q = (query || '').toLowerCase().trim();
        document.querySelectorAll('.override-sensor-row').forEach(tr => {
            const match = !q || tr.innerText.toLowerCase().includes(q);
            tr.style.display = match ? '' : 'none';
        });
    }

    function openScheduleModal(defaultType = 'group') {
        document.getElementById('scheduleModalTitle').innerText = 'Programar Toma de Medición y Tolerancias';
        if (defaultType === 'sensor') {
            document.getElementById('targetTypeSensor').checked = true;
        } else {
            document.getElementById('targetTypeGroup').checked = true;
        }
        syncTargetTypeUI();
        syncModeUI();
        new bootstrap.Modal(document.getElementById('scheduleConfigModal')).show();
    }

    function openScheduleModalForDay(day, dateStr) {
        document.getElementById('scheduleModalTitle').innerText = `Programar Toma para el Día ${day}`;
        document.getElementById('modeFixedDay').checked = true;
        document.getElementById('modalDayOfMonth').value = day;
        document.getElementById('modalSpecificDate').value = dateStr;
        syncTargetTypeUI();
        syncModeUI();
        new bootstrap.Modal(document.getElementById('scheduleConfigModal')).show();
    }

    function editGroupSchedule(groupId, cfg) {
        document.getElementById('scheduleModalTitle').innerText = `Configurar Programación de Grupo`;
        document.getElementById('targetTypeGroup').checked = true;
        document.getElementById('modalGroupId').value = groupId;

        if (cfg.mode === 'specific_date') {
            document.getElementById('modeSpecificDate').checked = true;
        } else if (cfg.mode === 'interval') {
            document.getElementById('modeInterval').checked = true;
        } else {
            document.getElementById('modeFixedDay').checked = true;
        }

        document.getElementById('modalDayOfMonth').value = cfg.day_of_month || 15;
        if (cfg.specific_date) document.getElementById('modalSpecificDate').value = cfg.specific_date;
        document.getElementById('modalIntervalDays').value = cfg.interval_days || 30;
        document.getElementById('modalPreNoticeDays').value = cfg.pre_notice_days ?? 3;
        document.getElementById('modalToleranceDays').value = cfg.tolerance_days ?? 5;

        syncTargetTypeUI();
        syncModeUI();
        new bootstrap.Modal(document.getElementById('scheduleConfigModal')).show();
    }

    function editSensorSchedule(sensorId, cfg) {
        document.getElementById('scheduleModalTitle').innerText = `Programación Particular de Sensor`;
        document.getElementById('targetTypeSensor').checked = true;

        const select = document.getElementById('modalSensorIds');
        Array.from(select.options).forEach(opt => {
            opt.selected = (parseInt(opt.value) === parseInt(sensorId));
        });

        if (cfg.mode === 'specific_date') {
            document.getElementById('modeSpecificDate').checked = true;
        } else if (cfg.mode === 'interval') {
            document.getElementById('modeInterval').checked = true;
        } else {
            document.getElementById('modeFixedDay').checked = true;
        }

        document.getElementById('modalDayOfMonth').value = cfg.day_of_month || 15;
        if (cfg.specific_date) document.getElementById('modalSpecificDate').value = cfg.specific_date;
        document.getElementById('modalIntervalDays').value = cfg.interval_days || 30;
        document.getElementById('modalPreNoticeDays').value = cfg.pre_notice_days ?? 3;
        document.getElementById('modalToleranceDays').value = cfg.tolerance_days ?? 5;

        syncTargetTypeUI();
        syncModeUI();
        new bootstrap.Modal(document.getElementById('scheduleConfigModal')).show();
    }

    function openEventDetailById(evId) {
        const ev = calendarEventsMap[evId];
        if (ev) {
            openEventDetailModal(ev);
        }
    }

    function getFilteredDetailSensors() {
        if (!currentDetailEvent || !currentDetailEvent.sensors_detail) return [];
        const query = (document.getElementById('detailSensorSearch').value || '').toLowerCase().trim();
        const statusFilter = document.getElementById('detailSensorStatusFilter').value || 'all';

        return currentDetailEvent.sensors_detail.filter(s => {
            if (statusFilter === 'pending' && s.is_measured) return false;
            if (statusFilter === 'completed' && !s.is_measured) return false;
            if (['overdue', 'in_tolerance', 'in_window'].includes(statusFilter) && s.status !== statusFilter) return false;

            if (query) {
                const nameMatch = (s.sensor_name || '').toLowerCase().includes(query);
                const idMatch = (s.sensor_identifier || '').toLowerCase().includes(query);
                return nameMatch || idMatch;
            }
            return true;
        });
    }

    function resetAndRenderDetailSensors() {
        currentDetailPage = 1;
        renderDetailSensorsTable();
    }

    function changeDetailPage(delta) {
        currentDetailPage += delta;
        renderDetailSensorsTable();
    }

    function renderDetailSensorsTable() {
        const tbody = document.getElementById('eventDetailSensorsTableBody');
        tbody.innerHTML = '';

        const filtered = getFilteredDetailSensors();
        const total = filtered.length;
        const totalPages = Math.max(1, Math.ceil(total / DETAIL_PAGE_SIZE));
        if (currentDetailPage < 1) currentDetailPage = 1;
        if (currentDetailPage > totalPages) currentDetailPage = totalPages;

        const startIdx = (currentDetailPage - 1) * DETAIL_PAGE_SIZE;
        const pageItems = filtered.slice(startIdx, startIdx + DETAIL_PAGE_SIZE);

        if (pageItems.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-3 small">No se encontraron sensores con ese filtro.</td></tr>`;
        } else {
            pageItems.forEach(s => {
                const tr = document.createElement('tr');
                const statusBadge = s.is_measured
                    ? `<span class="badge bg-success"><i class="bi bi-check-lg"></i> Medido (${s.measured_at})</span>`
                    : `<span class="badge ${s.badge_class}">${s.status_label}</span>`;

                tr.innerHTML = `
                    <td class="fw-semibold">${s.sensor_name}</td>
                    <td><code class="text-dark">${s.sensor_identifier}</code></td>
                    <td>${statusBadge}</td>
                    <td class="text-end">
                        <a href="/mediciones/create/${s.sensor_id}" class="btn btn-sm ${s.is_measured ? 'btn-outline-secondary' : 'btn-primary'} py-0 px-2">
                            <i class="bi bi-rulers"></i> ${s.is_measured ? 'Nueva toma' : 'Medir'}
                        </a>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        const fromNum = total === 0 ? 0 : startIdx + 1;
        const toNum = Math.min(startIdx + DETAIL_PAGE_SIZE, total);
        document.getElementById('detailPaginationInfo').innerText = `Mostrando ${fromNum}–${toNum} de ${total} sensor(es) (Pág. ${currentDetailPage}/${totalPages})`;
        document.getElementById('btnDetailPrevPage').disabled = (currentDetailPage <= 1);
        document.getElementById('btnDetailNextPage').disabled = (currentDetailPage >= totalPages);
    }

    function openEventDetailModal(ev) {
        currentDetailEvent = ev;
        document.getElementById('eventDetailHeader').style.background = ev.color_hex || '#0d6efd';
        document.getElementById('eventDetailTitle').innerText = (ev.type === 'group' ? '📦 Grupo: ' : '📟 Sensor(es): ') + ev.title;
        document.getElementById('eventDetailSubtitle').innerText = ev.subtitle || '';

        document.getElementById('eventDetailStatusLabel').innerText = ev.status_label;
        document.getElementById('eventDetailStatusMessage').innerText = ev.status_message;
        document.getElementById('eventDetailProgressBadge').innerText = `${ev.measured_sensors} de ${ev.total_sensors} medidos`;

        document.getElementById('eventDetailWindowStart').innerText = `${ev.window_start_formatted} (-${ev.pre_notice_days}d)`;
        document.getElementById('eventDetailTargetDate').innerText = ev.target_date_formatted;
        document.getElementById('eventDetailToleranceEnd').innerText = `${ev.tolerance_end_formatted} (+${ev.tolerance_days}d)`;

        document.getElementById('detailSensorSearch').value = '';
        document.getElementById('detailSensorStatusFilter').value = 'all';
        resetAndRenderDetailSensors();

        const idsToMeasure = (ev.pending_sensor_ids && ev.pending_sensor_ids.length > 0)
            ? ev.pending_sensor_ids
            : ev.all_sensor_ids;

        document.getElementById('inputBulkSensorIds').value = (idsToMeasure || []).join(',');
        document.getElementById('btnStartBulkFromCalendar').innerHTML = (ev.pending_sensor_ids && ev.pending_sensor_ids.length > 0)
            ? `<i class="bi bi-laptop me-1"></i> Medir Web (${ev.pending_sensor_ids.length})`
            : `<i class="bi bi-arrow-repeat me-1"></i> Medir Web Nuevamente (${(ev.all_sensor_ids || []).length})`;

        document.getElementById('btnEditFromDetail').onclick = function () {
            bootstrap.Modal.getInstance(document.getElementById('eventDetailModal')).hide();
            if (ev.type === 'group') {
                editGroupSchedule(ev.entity_id, groupsConfigMap[ev.entity_id] || ev);
            } else {
                editSensorSchedule(ev.entity_id, sensorsConfigMap[ev.entity_id] || ev);
            }
        };

        new bootstrap.Modal(document.getElementById('eventDetailModal')).show();
    }

    function openMobileInviteForAlertGroup(groupId) {
        const alertItem = allAlertsByGroupMap[groupId];
        if (!alertItem) return;

        openMobileInviteModalWithConfig({
            group_id: groupId,
            title: alertItem.title || ('Grupo #' + groupId),
            pending_ids: alertItem.pending_sensor_ids || [],
            overdue_ids: alertItem.overdue_sensor_ids || [],
            all_ids: alertItem.all_sensor_ids || alertItem.pending_sensor_ids || []
        });
    }

    function openMobileInviteFromDetailModal() {
        if (!currentDetailEvent) return;
        const detailModalEl = document.getElementById('eventDetailModal');
        const detailInstance = bootstrap.Modal.getInstance(detailModalEl);
        if (detailInstance) detailInstance.hide();

        const details = currentDetailEvent.sensors_detail || [];
        const overdueIds = details.filter(s => !s.is_measured && s.status === 'overdue').map(s => s.sensor_id);
        const pendingIds = currentDetailEvent.pending_sensor_ids || [];
        const allIds = currentDetailEvent.all_sensor_ids || [];

        openMobileInviteModalWithConfig({
            group_id: currentDetailEvent.group_id || (currentDetailEvent.type === 'group' ? currentDetailEvent.entity_id : 0),
            title: (currentDetailEvent.type === 'group' ? 'Grupo: ' : 'Compromiso: ') + currentDetailEvent.title,
            pending_ids: pendingIds,
            overdue_ids: overdueIds,
            all_ids: allIds
        });
    }

    function openMobileInviteModalWithConfig(ctx) {
        currentMobileInviteContext = ctx;

        document.getElementById('mobileInviteTargetTitle').innerHTML = `<i class="bi bi-folder-fill text-primary me-1"></i> ${ctx.title}`;
        document.getElementById('scopePendingCount').innerText = (ctx.pending_ids || []).length;
        document.getElementById('scopeOverdueCount').innerText = (ctx.overdue_ids || []).length;
        document.getElementById('scopeAllCount').innerText = (ctx.all_ids || []).length;

        const hasOverdue = (ctx.overdue_ids || []).length > 0;
        const hasPending = (ctx.pending_ids || []).length > 0;

        document.getElementById('scopeOverdueWrap').classList.toggle('d-none', !hasOverdue);
        document.getElementById('scopePendingWrap').classList.toggle('d-none', !hasPending);

        if (hasPending) {
            document.getElementById('scopePending').checked = true;
        } else if (hasOverdue) {
            document.getElementById('scopeOverdue').checked = true;
        } else {
            document.getElementById('scopeAll').checked = true;
        }

        document.getElementById('scheduleInviteGroupId').value = ctx.group_id || 0;
        document.getElementById('scheduleInviteResultMsg').classList.add('d-none');
        document.getElementById('scheduleInviteResultMsg').innerHTML = '';

        const btn = document.getElementById('btnSendScheduleMobileInvite');
        btn.disabled = false;
        document.getElementById('btnSendScheduleMobileInviteText').innerText = 'Generar Token y Enviar';
        document.getElementById('btnSendScheduleMobileInviteSpinner').classList.add('d-none');

        updateMobileInviteScopeSelection();
        new bootstrap.Modal(document.getElementById('scheduleMobileInviteModal')).show();
    }

    function updateMobileInviteScopeSelection() {
        const selectedScope = document.querySelector('input[name="mobile_invite_scope"]:checked')?.value || 'pending';
        let chosenIds = [];
        if (selectedScope === 'overdue') {
            chosenIds = currentMobileInviteContext.overdue_ids || [];
        } else if (selectedScope === 'all') {
            chosenIds = currentMobileInviteContext.all_ids || [];
        } else {
            chosenIds = currentMobileInviteContext.pending_ids || [];
        }

        document.getElementById('scheduleInviteSensorIds').value = chosenIds.join(',');
        document.getElementById('scheduleInviteLimit').value = chosenIds.length;
        document.getElementById('mobileInviteSelectedBadge').innerText = `${chosenIds.length} sensor(es)`;

        const preselectUrl = `{{ route('bulk-measurements.select') }}?preselect_ids=${encodeURIComponent(chosenIds.join(','))}`;
        document.getElementById('linkCustomizeInBulkSelect').href = preselectUrl;
    }

    function sendScheduleMobileInvite() {
        const email = (document.getElementById('scheduleInviteEmail').value || '').trim();
        const sensorIds = document.getElementById('scheduleInviteSensorIds').value;
        const limit = parseInt(document.getElementById('scheduleInviteLimit').value) || 0;
        const groupId = parseInt(document.getElementById('scheduleInviteGroupId').value) || 0;
        const msgDiv = document.getElementById('scheduleInviteResultMsg');
        const btn = document.getElementById('btnSendScheduleMobileInvite');
        const btnText = document.getElementById('btnSendScheduleMobileInviteText');
        const spinner = document.getElementById('btnSendScheduleMobileInviteSpinner');

        if (!email) {
            msgDiv.innerHTML = '<div class="alert alert-warning py-2 small mb-0"><i class="bi bi-exclamation-triangle me-1"></i> Por favor ingresa el correo electrónico del inspector.</div>';
            msgDiv.classList.remove('d-none');
            return;
        }
        if (limit === 0 || !sensorIds) {
            msgDiv.innerHTML = '<div class="alert alert-warning py-2 small mb-0">No hay sensores seleccionados en este alcance.</div>';
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
        if (bearer) {
            headers['Authorization'] = 'Bearer ' + bearer;
        }

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
                            Alcance asignado: <strong>${limit} sensor(es)</strong> (${currentMobileInviteContext.title}).
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold mb-1" style="font-size: 0.72rem;">Enlace de Autorización Directa (App Flutter):</label>
                        <div class="d-flex align-items-center gap-1">
                            <input type="text" class="form-control form-control-sm" value="${linkText}" readonly id="scheduleInviteLinkInput">
                            <button type="button" class="btn btn-sm btn-outline-secondary text-nowrap" onclick="navigator.clipboard.writeText(document.getElementById('scheduleInviteLinkInput').value); this.innerText='¡Copiado!'; setTimeout(()=>this.innerText='Copiar', 2000);">Copiar</button>
                            <a href="https://api.whatsapp.com/send?text=${encodeURIComponent('Hola! Aquí tienes el enlace y token para tomar las ' + limit + ' mediciones pendientes de ' + currentMobileInviteContext.title + ' en la App MedFlow Inspector: ' + linkText)}" target="_blank" class="btn btn-sm btn-success text-nowrap">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </a>
                        </div>
                    </div>
                    ${rawToken ? `
                    <div>
                        <label class="form-label small fw-bold mb-1" style="font-size: 0.72rem;">Token manual para pegar en la App Flutter:</label>
                        <div class="d-flex align-items-center gap-1">
                            <input type="text" class="form-control form-control-sm font-monospace" value="${rawToken}" readonly id="scheduleInviteRawTokenInput">
                            <button type="button" class="btn btn-sm btn-outline-dark text-nowrap" onclick="navigator.clipboard.writeText(document.getElementById('scheduleInviteRawTokenInput').value); this.innerText='¡Copiado!'; setTimeout(()=>this.innerText='Copiar Token', 2000);">Copiar Token</button>
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

