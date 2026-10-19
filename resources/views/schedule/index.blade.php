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
        gap: 8px;
    }
    .calendar-weekday {
        text-align: center;
        font-weight: 700;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #495057;
        padding: 8px 4px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    .calendar-day-cell {
        min-height: 125px;
        background: #ffffff;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 8px;
        display: flex;
        flex-direction: column;
        transition: all 0.2s ease;
        position: relative;
    }
    .calendar-day-cell:hover {
        border-color: #0d6efd;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.08);
    }
    .calendar-day-cell.is-today {
        border: 2px solid #0d6efd;
        background: #f4f8ff;
    }
    .calendar-day-cell.empty-cell {
        background: #f8f9fa;
        opacity: 0.55;
        min-height: 80px;
    }
    .calendar-day-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }
    .calendar-day-number {
        font-weight: 700;
        font-size: 0.9rem;
        color: #343a40;
        width: 28px;
        height: 28px;
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
        padding: 0 5px;
        font-size: 0.75rem;
        line-height: 1.3;
    }
    .calendar-day-cell:hover .btn-add-day-schedule {
        opacity: 1;
    }
    .calendar-event-pill {
        display: block;
        width: 100%;
        text-align: left;
        padding: 5px 7px;
        margin-bottom: 5px;
        border-radius: 7px;
        font-size: 0.74rem;
        line-height: 1.25;
        cursor: pointer;
        border: 1px solid rgba(0,0,0,0.08);
        border-left-width: 4px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
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
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; overflow: hidden;">
        <div class="card-body p-4" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: #fff;">
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

    {{-- Banner de Alertas Operativas Activas (Hoy / En Ventana / En Tolerancia / Vencidos) --}}
    @if(count($activeAlerts['actionable']) > 0)
        <div class="card border-0 shadow-sm mb-4 border-start border-4 border-warning" style="border-radius: 12px;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-bell-fill text-warning me-2"></i>
                    Avisos Activos de Medición ({{ count($activeAlerts['actionable']) }})
                </h6>
                <span class="badge bg-light text-dark border">Segun fechas programadas y márgenes de tolerancia</span>
            </div>
            <div class="card-body py-2 px-3">
                <div class="row g-2">
                    @foreach(array_slice($activeAlerts['actionable'], 0, 6) as $alert)
                        <div class="col-md-6 col-xl-4">
                            <div class="p-3 rounded-3 border d-flex flex-column justify-content-between h-100"
                                 style="border-left: 4px solid {{ $alert['color_hex'] }} !important; background: #fafafa;">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                        <span class="fw-bold text-dark small">
                                            <i class="bi {{ $alert['type'] === 'group' ? 'bi-folder-fill text-primary' : 'bi-speedometer2 text-info' }} me-1"></i>
                                            {{ $alert['title'] }}
                                        </span>
                                        <span class="badge {{ $alert['badge_class'] }}" style="font-size: 0.68rem;">
                                            {{ $alert['status_label'] }}
                                        </span>
                                    </div>
                                    <p class="mb-2 text-muted" style="font-size: 0.76rem;">
                                        {{ $alert['status_message'] }}
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <small class="text-muted" style="font-size: 0.7rem;">
                                        <i class="bi bi-calendar-event"></i> Meta: <strong>{{ $alert['target_date_formatted'] }}</strong>
                                        (Tol: +{{ $alert['tolerance_days'] }}d)
                                    </small>
                                    @if(!empty($alert['pending_sensor_ids']))
                                        <form method="POST" action="{{ route('bulk-measurements.start') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="sensor_ids" value="{{ implode(',', $alert['pending_sensor_ids']) }}">
                                            <button type="submit" class="btn btn-sm btn-primary py-0 px-2" style="font-size: 0.73rem;">
                                                <i class="bi bi-play-fill"></i> Medir ahora ({{ count($alert['pending_sensor_ids']) }})
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
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
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('schedule.index', ['year' => $prevMonthDate->year, 'month' => $prevMonthDate->month]) }}"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <h5 class="mb-0 fw-bold px-2 text-dark">
                    {{ $monthName }} {{ $year }}
                </h5>
                <a href="{{ route('schedule.index', ['year' => $nextMonthDate->year, 'month' => $nextMonthDate->month]) }}"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-chevron-right"></i>
                </a>
                <a href="{{ route('schedule.index') }}" class="btn btn-sm btn-light border ms-2">
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

        <div class="card-body p-3">
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
                            @foreach($dayEvents as $ev)
                                <div class="calendar-event-pill status-{{ $ev['status'] }}"
                                     onclick='openEventDetailModal(@json($ev))'>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-truncate text-dark" style="max-width: 78%;">
                                            <i class="bi {{ $ev['type'] === 'group' ? 'bi-folder-fill text-primary' : 'bi-speedometer2 text-info' }} me-1"></i>
                                            {{ $ev['title'] }}
                                        </span>
                                        <span class="badge {{ $ev['badge_class'] }}" style="font-size: 0.62rem; padding: 2px 4px;">
                                            {{ $ev['measured_sensors'] }}/{{ $ev['total_sensors'] }}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1 text-muted" style="font-size: 0.66rem;">
                                        <span>{{ $ev['status_label'] }}</span>
                                        <span title="Preaviso: -{{ $ev['pre_notice_days'] }}d | Tolerancia: +{{ $ev['tolerance_days'] }}d">
                                            Tol: -{{ $ev['pre_notice_days'] }}/+{{ $ev['tolerance_days'] }}d
                                        </span>
                                    </div>
                                </div>
                            @endforeach
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
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-folder2-open text-primary me-2"></i>
                        Reglas de Programación por Grupo de Sensores
                    </h6>
                    <small class="text-muted">Aplica a todos los sensores del grupo</small>
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
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick='editGroupSchedule({{ $g->id }}, @json($gCfg))'>
                                                <i class="bi bi-sliders me-1"></i> Configurar
                                            </button>
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
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-speedometer2 text-info me-2"></i>
                        Sensores con Programación Particular
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="openScheduleModal('sensor')">
                        <i class="bi bi-plus-lg"></i> Añadir excepción
                    </button>
                </div>
                <div class="card-body p-0">
                    @php
                        $overriddenSensors = $sensors->filter(fn($s) => !empty($sensorsConfigMap[$s->id]['override']));
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sensor</th>
                                    <th>Programación</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($overriddenSensors as $s)
                                    @php
                                        $sCfg = $sensorsConfigMap[$s->id];
                                    @endphp
                                    <tr>
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

                    {{-- Selector de Sensores Particulares --}}
                    <div class="mb-4 d-none" id="sensorTargetSelector">
                        <label for="modalSensorIds" class="form-label fw-semibold">Seleccionar Sensor(es) Particular(es) <span class="text-danger">*</span></label>
                        <select class="form-select" id="modalSensorIds" name="sensor_ids[]" multiple size="6">
                            @foreach($sensors as $s)
                                <option value="{{ $s->id }}">
                                    {{ $s->name }} ({{ $s->identifier }}) — Grupo: {{ $s->group->name ?? 'Sin grupo' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Puedes mantener presionada la tecla Ctrl (o Cmd) para elegir varios sensores a la vez.</div>
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
   MODAL 2: DETALLE DE COMPROMISO DEL CALENDARIO Y ACCIÓN RÁPIDA
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

                <h6 class="fw-bold mb-2">
                    <i class="bi bi-list-check me-1"></i> Estado de Sensores en este Compromiso
                </h6>
                <div class="table-responsive border rounded-3" style="max-height: 260px; overflow-y: auto;">
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
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between flex-wrap gap-2" style="border-radius: 0 0 16px 16px;">
                <button type="button" class="btn btn-outline-secondary" id="btnEditFromDetail">
                    <i class="bi bi-sliders me-1"></i> Editar Fecha / Tolerancia
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <form method="POST" action="{{ route('bulk-measurements.start') }}" id="formStartBulkFromCalendar">
                        @csrf
                        <input type="hidden" name="sensor_ids" id="inputBulkSensorIds" value="">
                        <button type="submit" class="btn btn-primary fw-bold" id="btnStartBulkFromCalendar">
                            <i class="bi bi-play-circle-fill me-1"></i> Iniciar Toma de Pendientes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const groupsConfigMap = @json($groupsConfigMap);
    const sensorsConfigMap = @json($sensorsConfigMap);
    let currentDetailEvent = null;

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

    function openEventDetailModal(ev) {
        currentDetailEvent = ev;
        document.getElementById('eventDetailHeader').style.background = ev.color_hex || '#0d6efd';
        document.getElementById('eventDetailTitle').innerText = (ev.type === 'group' ? '📦 Grupo: ' : '📟 Sensor: ') + ev.title;
        document.getElementById('eventDetailSubtitle').innerText = ev.subtitle || '';

        document.getElementById('eventDetailStatusLabel').innerText = ev.status_label;
        document.getElementById('eventDetailStatusMessage').innerText = ev.status_message;
        document.getElementById('eventDetailProgressBadge').innerText = `${ev.measured_sensors} de ${ev.total_sensors} medidos`;

        document.getElementById('eventDetailWindowStart').innerText = `${ev.window_start_formatted} (-${ev.pre_notice_days}d)`;
        document.getElementById('eventDetailTargetDate').innerText = ev.target_date_formatted;
        document.getElementById('eventDetailToleranceEnd').innerText = `${ev.tolerance_end_formatted} (+${ev.tolerance_days}d)`;

        const tbody = document.getElementById('eventDetailSensorsTableBody');
        tbody.innerHTML = '';

        (ev.sensors_detail || []).forEach(s => {
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

        const idsToMeasure = (ev.pending_sensor_ids && ev.pending_sensor_ids.length > 0)
            ? ev.pending_sensor_ids
            : ev.all_sensor_ids;

        document.getElementById('inputBulkSensorIds').value = (idsToMeasure || []).join(',');
        document.getElementById('btnStartBulkFromCalendar').innerHTML = (ev.pending_sensor_ids && ev.pending_sensor_ids.length > 0)
            ? `<i class="bi bi-play-circle-fill me-1"></i> Iniciar Toma de Pendientes (${ev.pending_sensor_ids.length})`
            : `<i class="bi bi-arrow-repeat me-1"></i> Tomar Medición Nuevamente (${(ev.all_sensor_ids || []).length})`;

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
</script>
@endpush
