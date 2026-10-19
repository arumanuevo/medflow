<?php

namespace App\Services;

use App\Models\Sensor;
use App\Models\SensorGroup;
use App\Models\Measurement;
use App\Models\User;
use App\Models\WorkspaceCollaborator;
use Carbon\Carbon;

class MeasurementScheduleService
{
    /**
     * Obtiene la configuración de programación de un grupo.
     */
    public function getGroupScheduleConfig(SensorGroup $group): array
    {
        $billing = $group->billing_settings ?? [];
        $schedule = $billing['schedule'] ?? [];

        $enabled = isset($schedule['enabled']) ? (bool) $schedule['enabled'] : false;
        $mode = $schedule['mode'] ?? ($enabled ? 'fixed_day' : 'interval');
        $dayOfMonth = isset($schedule['day_of_month']) ? max(1, min(31, (int) $schedule['day_of_month'])) : 15;
        $specificDate = $schedule['specific_date'] ?? null;
        $intervalDays = isset($schedule['interval_days']) ? max(1, (int) $schedule['interval_days']) : ($group->periodo_medicion ?? 30);
        $preNoticeDays = isset($schedule['pre_notice_days']) ? max(0, (int) $schedule['pre_notice_days']) : 3;
        $toleranceDays = isset($schedule['tolerance_days']) ? max(0, (int) $schedule['tolerance_days']) : ($group->dias_vencimiento ?? 5);

        return [
            'enabled' => $enabled,
            'mode' => $mode, // 'fixed_day' | 'specific_date' | 'interval'
            'day_of_month' => $dayOfMonth,
            'specific_date' => $specificDate,
            'interval_days' => $intervalDays,
            'pre_notice_days' => $preNoticeDays,
            'tolerance_days' => $toleranceDays,
            'source' => 'group',
            'source_name' => $group->name,
        ];
    }

    /**
     * Obtiene la configuración efectiva de programación de un sensor (propia si tiene override, o heredada del grupo).
     */
    public function getSensorScheduleConfig(Sensor $sensor): array
    {
        $metadata = $sensor->metadata ?? [];
        $sensorSchedule = $metadata['schedule'] ?? [];

        if (!empty($sensorSchedule['override'])) {
            $mode = $sensorSchedule['mode'] ?? 'fixed_day';
            $dayOfMonth = isset($sensorSchedule['day_of_month']) ? max(1, min(31, (int) $sensorSchedule['day_of_month'])) : 15;
            $specificDate = $sensorSchedule['specific_date'] ?? null;
            $intervalDays = isset($sensorSchedule['interval_days']) ? max(1, (int) $sensorSchedule['interval_days']) : ($sensor->group->periodo_medicion ?? 30);
            $preNoticeDays = isset($sensorSchedule['pre_notice_days']) ? max(0, (int) $sensorSchedule['pre_notice_days']) : 3;
            $toleranceDays = isset($sensorSchedule['tolerance_days']) ? max(0, (int) $sensorSchedule['tolerance_days']) : ($sensor->group->dias_vencimiento ?? 5);

            return [
                'enabled' => true,
                'override' => true,
                'mode' => $mode,
                'day_of_month' => $dayOfMonth,
                'specific_date' => $specificDate,
                'interval_days' => $intervalDays,
                'pre_notice_days' => $preNoticeDays,
                'tolerance_days' => $toleranceDays,
                'source' => 'sensor',
                'source_name' => $sensor->name,
            ];
        }

        if ($sensor->group) {
            $groupConfig = $this->getGroupScheduleConfig($sensor->group);
            $groupConfig['override'] = false;
            return $groupConfig;
        }

        return [
            'enabled' => false,
            'override' => false,
            'mode' => 'interval',
            'day_of_month' => 15,
            'specific_date' => null,
            'interval_days' => 30,
            'pre_notice_days' => 3,
            'tolerance_days' => 5,
            'source' => 'default',
            'source_name' => 'Por defecto',
        ];
    }

    /**
     * Calcula la fecha objetivo y el estado de un sensor para un año y mes específicos (para el Calendario).
     */
    public function evaluateSensorForMonth(Sensor $sensor, int $year, int $month, ?Carbon $referenceToday = null): ?array
    {
        $today = ($referenceToday ? $referenceToday->copy() : Carbon::now())->startOfDay();
        $config = $this->getSensorScheduleConfig($sensor);

        $targetDate = null;

        if ($config['mode'] === 'fixed_day') {
            $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
            $day = min($config['day_of_month'], $daysInMonth);
            $targetDate = Carbon::createFromDate($year, $month, $day)->startOfDay();
        } elseif ($config['mode'] === 'specific_date' && !empty($config['specific_date'])) {
            try {
                $parsed = Carbon::parse($config['specific_date'])->startOfDay();
                if ($parsed->year === $year && $parsed->month === $month) {
                    $targetDate = $parsed;
                } else {
                    return null;
                }
            } catch (\Exception $e) {
                return null;
            }
        } else {
            // Modo 'interval' (por cada N días desde la última medición o proxima_medicion)
            $lastMeasurement = $sensor->lastMeasurement;
            if ($sensor->proxima_medicion) {
                $prox = Carbon::parse($sensor->proxima_medicion)->startOfDay();
                if ($prox->year === $year && $prox->month === $month) {
                    $targetDate = $prox;
                }
            }
            if (!$targetDate && $lastMeasurement) {
                $prox = Carbon::parse($lastMeasurement->measured_at)->addDays($config['interval_days'])->startOfDay();
                if ($prox->year === $year && $prox->month === $month) {
                    $targetDate = $prox;
                }
            }
            if (!$targetDate) {
                return null;
            }
        }

        return $this->buildEvaluationResult($sensor, $config, $targetDate, $today);
    }

    /**
     * Evalúa el estado operativo actual de un sensor respecto a su ciclo activo o próximo.
     */
    public function evaluateCurrentSensorStatus(Sensor $sensor, ?Carbon $referenceToday = null): array
    {
        $today = ($referenceToday ? $referenceToday->copy() : Carbon::now())->startOfDay();
        $config = $this->getSensorScheduleConfig($sensor);

        if ($config['mode'] === 'fixed_day') {
            // Evaluar primero el ciclo del mes actual
            $daysInCurrMonth = $today->copy()->daysInMonth;
            $currDay = min($config['day_of_month'], $daysInCurrMonth);
            $currTarget = Carbon::createFromDate($today->year, $today->month, $currDay)->startOfDay();

            // También chequear si viene arrastrando un ciclo del mes anterior cuya tolerancia cruza a principios de este mes
            $prevMonth = $today->copy()->subMonthNoOverflow();
            $daysInPrevMonth = $prevMonth->daysInMonth;
            $prevDay = min($config['day_of_month'], $daysInPrevMonth);
            $prevTarget = Carbon::createFromDate($prevMonth->year, $prevMonth->month, $prevDay)->startOfDay();
            $prevEval = $this->buildEvaluationResult($sensor, $config, $prevTarget, $today);

            $currEval = $this->buildEvaluationResult($sensor, $config, $currTarget, $today);

            // Si en el ciclo actual ya se midió y ya pasamos la fecha objetivo (o estamos en ella), proyectamos también el próximo mes
            if ($currEval['is_measured'] && $today->gt($currTarget)) {
                $nextMonth = $today->copy()->addMonthNoOverflow();
                $daysInNextMonth = $nextMonth->daysInMonth;
                $nextDay = min($config['day_of_month'], $daysInNextMonth);
                $nextTarget = Carbon::createFromDate($nextMonth->year, $nextMonth->month, $nextDay)->startOfDay();
                $nextEval = $this->buildEvaluationResult($sensor, $config, $nextTarget, $today);
                $nextEval['last_cycle_completed'] = true;
                return $nextEval;
            }

            // Si estamos a principios de mes y el ciclo del mes pasado está en tolerancia o vencido sin medir y aún no entramos en la ventana actual
            if (!$prevEval['is_measured'] && $today->lt($currEval['window_start'])) {
                return $prevEval;
            }

            return $currEval;
        }

        if ($config['mode'] === 'specific_date' && !empty($config['specific_date'])) {
            try {
                $targetDate = Carbon::parse($config['specific_date'])->startOfDay();
                return $this->buildEvaluationResult($sensor, $config, $targetDate, $today);
            } catch (\Exception $e) {
                // Fallback a intervalo
            }
        }

        // Modo intervalo
        $lastMeasurement = $sensor->lastMeasurement;
        if ($lastMeasurement) {
            $targetDate = Carbon::parse($lastMeasurement->measured_at)
                ->addDays($config['interval_days'])
                ->startOfDay();
        } elseif ($sensor->proxima_medicion) {
            $targetDate = Carbon::parse($sensor->proxima_medicion)->startOfDay();
        } else {
            $targetDate = $today->copy();
        }

        return $this->buildEvaluationResult($sensor, $config, $targetDate, $today);
    }

    /**
     * Construye el resultado detallado del semáforo de tolerancia para una fecha objetivo dada.
     */
    private function buildEvaluationResult(Sensor $sensor, array $config, Carbon $targetDate, Carbon $today): array
    {
        $preNoticeDays = $config['pre_notice_days'];
        $toleranceDays = $config['tolerance_days'];

        $windowStart = $targetDate->copy()->subDays($preNoticeDays)->startOfDay();
        $toleranceEnd = $targetDate->copy()->addDays($toleranceDays)->endOfDay();

        // Buscar si existe una medición dentro de la ventana ampliada del ciclo
        // Para día fijo o fecha puntual, aceptamos mediciones desde windowStart hasta toleranceEnd
        $cycleMeasurement = null;
        if ($config['mode'] === 'interval') {
            // En modo intervalo, si targetDate fue calculado desde lastMeasurement, aún no está medido el nuevo ciclo
            $lastM = $sensor->lastMeasurement;
            if ($lastM && Carbon::parse($lastM->measured_at)->gte($targetDate->copy()->startOfDay())) {
                $cycleMeasurement = $lastM;
            }
        } else {
            $cycleMeasurement = $sensor->measurements()
                ->whereBetween('measured_at', [$windowStart->copy()->startOfDay(), $toleranceEnd->copy()->endOfDay()])
                ->orderBy('measured_at', 'desc')
                ->first();
        }

        $isMeasured = $cycleMeasurement !== null;
        $daysDiffFromTarget = (int) $today->diffInDays($targetDate, false); // positivo = faltan días, 0 = hoy, negativo = pasaron días
        $daysRemainingTolerance = (int) $today->diffInDays($toleranceEnd->copy()->startOfDay(), false);

        if ($isMeasured) {
            $status = 'completed';
            $statusLabel = 'Cumplido (Al día)';
            $badgeClass = 'bg-success';
            $colorHex = '#198754';
            $statusMessage = 'Medición tomada el ' . Carbon::parse($cycleMeasurement->measured_at)->format('d/m/Y H:i');
        } elseif ($today->lt($windowStart)) {
            $status = 'scheduled';
            $statusLabel = 'Programado';
            $badgeClass = 'bg-primary';
            $colorHex = '#0d6efd';
            $statusMessage = "Faltan {$daysDiffFromTarget} día(s) para la fecha programada (" . $targetDate->format('d/m/Y') . ")";
        } elseif ($today->lte($targetDate)) {
            $status = 'in_window';
            $isToday = $today->isSameDay($targetDate);
            $statusLabel = $isToday ? '¡Hoy toca medir!' : 'En ventana de toma';
            $badgeClass = 'bg-warning text-dark';
            $colorHex = '#ffc107';
            $statusMessage = $isToday
                ? "¡Hoy (" . $targetDate->format('d/m') . ") es el día programado de medición! (Tolerancia: +{$toleranceDays} días)"
                : "Faltan {$daysDiffFromTarget} día(s) para el día programado (" . $targetDate->format('d/m') . "). Ya estás en ventana de preaviso.";
        } elseif ($today->lte($toleranceEnd->copy()->startOfDay())) {
            $status = 'in_tolerance';
            $daysPast = abs($daysDiffFromTarget);
            $statusLabel = 'En tolerancia';
            $badgeClass = 'bg-orange text-white';
            $colorHex = '#fd7e14';
            $statusMessage = "Pasaron {$daysPast} día(s) de la fecha ideal (" . $targetDate->format('d/m') . "). Quedan {$daysRemainingTolerance} día(s) de tolerancia (vence el " . $toleranceEnd->format('d/m') . ").";
        } else {
            $status = 'overdue';
            $daysOverdue = abs($daysRemainingTolerance);
            $statusLabel = 'Vencido';
            $badgeClass = 'bg-danger';
            $colorHex = '#dc3545';
            $statusMessage = "Plazo y tolerancia vencidos hace {$daysOverdue} día(s) (debía medirse el " . $targetDate->format('d/m') . " ±{$toleranceDays}d).";
        }

        return [
            'sensor_id' => $sensor->id,
            'sensor_name' => $sensor->name,
            'sensor_identifier' => $sensor->identifier,
            'group_id' => $sensor->group_id,
            'group_name' => $sensor->group->name ?? 'Sin grupo',
            'config' => $config,
            'target_date' => $targetDate->format('Y-m-d'),
            'target_date_formatted' => $targetDate->format('d/m/Y'),
            'target_day' => $targetDate->day,
            'window_start' => $windowStart->format('Y-m-d'),
            'window_start_formatted' => $windowStart->format('d/m/Y'),
            'tolerance_end' => $toleranceEnd->format('Y-m-d'),
            'tolerance_end_formatted' => $toleranceEnd->format('d/m/Y'),
            'pre_notice_days' => $preNoticeDays,
            'tolerance_days' => $toleranceDays,
            'is_measured' => $isMeasured,
            'measured_at' => $cycleMeasurement ? Carbon::parse($cycleMeasurement->measured_at)->format('d/m/Y H:i') : null,
            'status' => $status, // completed | scheduled | in_window | in_tolerance | overdue
            'status_label' => $statusLabel,
            'badge_class' => $badgeClass,
            'color_hex' => $colorHex,
            'status_message' => $statusMessage,
            'days_diff_from_target' => $daysDiffFromTarget,
            'days_remaining_tolerance' => $daysRemainingTolerance,
            'is_today' => $today->isSameDay($targetDate),
        ];
    }

    /**
     * Obtiene todos los sensores accesibles en el workspace activo.
     */
    public function getWorkspaceSensors(User $user, $workspaceId = null)
    {
        $activeWorkspace = $workspaceId ?? session('active_workspace', $user->id);
        $isOwner = $activeWorkspace == $user->id;

        return Sensor::whereHas('group', function ($q) use ($activeWorkspace, $user, $isOwner) {
            if ($isOwner) {
                $q->where('user_id', $activeWorkspace)
                    ->orWhereHas('sharedAccess', function ($q2) use ($user) {
                        $q2->where('shared_with', $user->id)
                            ->whereIn('role', ['inspector', 'admin']);
                    });
            } else {
                $q->where('user_id', $activeWorkspace);
            }
        })
            ->with(['group', 'group.template', 'lastMeasurement'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Genera los eventos del calendario mensual agrupando sensores por Grupo (cuando heredan)
     * e individualizando los Sensores con programación propia.
     */
    public function getCalendarMonthData(User $user, int $year, int $month, $workspaceId = null): array
    {
        $sensors = $this->getWorkspaceSensors($user, $workspaceId);
        $today = Carbon::now()->startOfDay();

        $eventsByDay = [];
        $allEvents = [];
        $summary = [
            'total_commitments' => 0,
            'completed' => 0,
            'in_window' => 0,
            'in_tolerance' => 0,
            'overdue' => 0,
            'scheduled' => 0,
        ];

        // Separar sensores que tienen override individual vs los que siguen a su grupo
        $groupBuckets = [];
        $individualEvals = [];

        foreach ($sensors as $sensor) {
            $eval = $this->evaluateSensorForMonth($sensor, $year, $month, $today);
            if (!$eval) {
                continue;
            }

            if (!empty($eval['config']['override'])) {
                $individualEvals[] = $eval;
            } else {
                $gKey = $sensor->group_id . '_' . $eval['target_date'];
                if (!isset($groupBuckets[$gKey])) {
                    $groupBuckets[$gKey] = [
                        'group' => $sensor->group,
                        'target_date' => $eval['target_date'],
                        'target_date_formatted' => $eval['target_date_formatted'],
                        'target_day' => $eval['target_day'],
                        'window_start_formatted' => $eval['window_start_formatted'],
                        'tolerance_end_formatted' => $eval['tolerance_end_formatted'],
                        'pre_notice_days' => $eval['pre_notice_days'],
                        'tolerance_days' => $eval['tolerance_days'],
                        'config' => $eval['config'],
                        'sensors' => [],
                    ];
                }
                $groupBuckets[$gKey]['sensors'][] = $eval;
            }
        }

        // Procesar compromisos por Grupo
        foreach ($groupBuckets as $gKey => $bucket) {
            $totalSensors = count($bucket['sensors']);
            $measuredSensors = collect($bucket['sensors'])->where('is_measured', true)->count();
            $pendingSensors = $totalSensors - $measuredSensors;
            $pendingSensorIds = collect($bucket['sensors'])->where('is_measured', false)->pluck('sensor_id')->values()->toArray();
            $allSensorIds = collect($bucket['sensors'])->pluck('sensor_id')->values()->toArray();

            // El estado del grupo lo define el primer sensor pendiente (o 'completed' si todos fueron medidos)
            $samplePending = collect($bucket['sensors'])->firstWhere('is_measured', false);
            $representative = $samplePending ?? $bucket['sensors'][0];

            $status = $pendingSensors === 0 ? 'completed' : $representative['status'];
            $statusLabel = $pendingSensors === 0 ? 'Grupo al día' : $representative['status_label'];
            $colorHex = $pendingSensors === 0 ? '#198754' : $representative['color_hex'];
            $badgeClass = $pendingSensors === 0 ? 'bg-success' : $representative['badge_class'];

            $event = [
                'id' => 'group_' . $bucket['group']->id . '_' . $bucket['target_date'],
                'type' => 'group',
                'entity_id' => $bucket['group']->id,
                'title' => $bucket['group']->name,
                'subtitle' => "{$measuredSensors}/{$totalSensors} medidos",
                'target_date' => $bucket['target_date'],
                'target_date_formatted' => $bucket['target_date_formatted'],
                'day' => $bucket['target_day'],
                'window_start_formatted' => $bucket['window_start_formatted'],
                'tolerance_end_formatted' => $bucket['tolerance_end_formatted'],
                'pre_notice_days' => $bucket['pre_notice_days'],
                'tolerance_days' => $bucket['tolerance_days'],
                'mode' => $bucket['config']['mode'],
                'day_of_month' => $bucket['config']['day_of_month'],
                'specific_date' => $bucket['config']['specific_date'],
                'interval_days' => $bucket['config']['interval_days'],
                'status' => $status,
                'status_label' => $statusLabel,
                'status_message' => $pendingSensors === 0
                    ? "Todos los sensores del grupo ({$totalSensors}) fueron medidos en este ciclo."
                    : "Faltan medir {$pendingSensors} de {$totalSensors} sensores. " . $representative['status_message'],
                'color_hex' => $colorHex,
                'badge_class' => $badgeClass,
                'total_sensors' => $totalSensors,
                'measured_sensors' => $measuredSensors,
                'pending_sensors' => $pendingSensors,
                'pending_sensor_ids' => $pendingSensorIds,
                'all_sensor_ids' => $allSensorIds,
                'sensors_detail' => $bucket['sensors'],
            ];

            $eventsByDay[$bucket['target_day']][] = $event;
            $allEvents[] = $event;
            $summary['total_commitments']++;
            if (isset($summary[$status])) {
                $summary[$status]++;
            }
        }

        // Procesar sensores individuales con override
        foreach ($individualEvals as $eval) {
            $status = $eval['status'];
            $event = [
                'id' => 'sensor_' . $eval['sensor_id'] . '_' . $eval['target_date'],
                'type' => 'sensor',
                'entity_id' => $eval['sensor_id'],
                'group_id' => $eval['group_id'],
                'group_name' => $eval['group_name'],
                'title' => $eval['sensor_name'],
                'subtitle' => $eval['sensor_identifier'] . ' (' . $eval['group_name'] . ')',
                'target_date' => $eval['target_date'],
                'target_date_formatted' => $eval['target_date_formatted'],
                'day' => $eval['target_day'],
                'window_start_formatted' => $eval['window_start_formatted'],
                'tolerance_end_formatted' => $eval['tolerance_end_formatted'],
                'pre_notice_days' => $eval['pre_notice_days'],
                'tolerance_days' => $eval['tolerance_days'],
                'mode' => $eval['config']['mode'],
                'day_of_month' => $eval['config']['day_of_month'],
                'specific_date' => $eval['config']['specific_date'],
                'interval_days' => $eval['config']['interval_days'],
                'status' => $status,
                'status_label' => $eval['status_label'],
                'status_message' => $eval['status_message'],
                'color_hex' => $eval['color_hex'],
                'badge_class' => $eval['badge_class'],
                'total_sensors' => 1,
                'measured_sensors' => $eval['is_measured'] ? 1 : 0,
                'pending_sensors' => $eval['is_measured'] ? 0 : 1,
                'pending_sensor_ids' => $eval['is_measured'] ? [] : [$eval['sensor_id']],
                'all_sensor_ids' => [$eval['sensor_id']],
                'sensors_detail' => [$eval],
            ];

            $eventsByDay[$eval['target_day']][] = $event;
            $allEvents[] = $event;
            $summary['total_commitments']++;
            if (isset($summary[$status])) {
                $summary[$status]++;
            }
        }

        // Ordenar todos los eventos por día ascendente
        usort($allEvents, fn($a, $b) => $a['day'] <=> $b['day']);

        return [
            'year' => $year,
            'month' => $month,
            'events_by_day' => $eventsByDay,
            'all_events' => $allEvents,
            'summary' => $summary,
        ];
    }

    /**
     * Obtiene las alertas operativas activas (en ventana, hoy, en tolerancia o vencidas)
     * para mostrar en el Header (Campanita) y en el Dashboard.
     */
    public function getActiveAlerts(User $user, $workspaceId = null): array
    {
        $sensors = $this->getWorkspaceSensors($user, $workspaceId);
        $today = Carbon::now()->startOfDay();

        $groupBuckets = [];
        $individualAlerts = [];

        foreach ($sensors as $sensor) {
            $eval = $this->evaluateCurrentSensorStatus($sensor, $today);

            if (!empty($eval['config']['override'])) {
                $individualAlerts[] = [
                    'type' => 'sensor',
                    'entity_id' => $sensor->id,
                    'title' => $sensor->name . ' (' . $sensor->identifier . ')',
                    'group_name' => $sensor->group->name ?? 'Sin grupo',
                    'target_date' => $eval['target_date'],
                    'target_date_formatted' => $eval['target_date_formatted'],
                    'window_start_formatted' => $eval['window_start_formatted'],
                    'tolerance_end_formatted' => $eval['tolerance_end_formatted'],
                    'tolerance_days' => $eval['tolerance_days'],
                    'pre_notice_days' => $eval['pre_notice_days'],
                    'status' => $eval['status'],
                    'status_label' => $eval['status_label'],
                    'status_message' => $eval['status_message'],
                    'color_hex' => $eval['color_hex'],
                    'badge_class' => $eval['badge_class'],
                    'is_today' => $eval['is_today'],
                    'pending_sensors' => $eval['is_measured'] ? 0 : 1,
                    'total_sensors' => 1,
                    'pending_sensor_ids' => $eval['is_measured'] ? [] : [$sensor->id],
                    'days_diff_from_target' => $eval['days_diff_from_target'],
                ];
            } else {
                $gKey = $sensor->group_id . '_' . $eval['target_date'];
                if (!isset($groupBuckets[$gKey])) {
                    $groupBuckets[$gKey] = [
                        'group' => $sensor->group,
                        'eval_sample' => $eval,
                        'sensors' => [],
                    ];
                }
                $groupBuckets[$gKey]['sensors'][] = $eval;
            }
        }

        $alerts = [];

        foreach ($groupBuckets as $bucket) {
            $total = count($bucket['sensors']);
            $pendingEvals = collect($bucket['sensors'])->where('is_measured', false);
            $pendingCount = $pendingEvals->count();
            $rep = $pendingEvals->first() ?? $bucket['eval_sample'];
            $status = $pendingCount === 0 ? 'completed' : $rep['status'];

            $alerts[] = [
                'type' => 'group',
                'entity_id' => $bucket['group']->id,
                'title' => 'Grupo: ' . $bucket['group']->name,
                'group_name' => $bucket['group']->name,
                'target_date' => $rep['target_date'],
                'target_date_formatted' => $rep['target_date_formatted'],
                'window_start_formatted' => $rep['window_start_formatted'],
                'tolerance_end_formatted' => $rep['tolerance_end_formatted'],
                'tolerance_days' => $rep['tolerance_days'],
                'pre_notice_days' => $rep['pre_notice_days'],
                'status' => $status,
                'status_label' => $pendingCount === 0 ? 'Al día' : $rep['status_label'],
                'status_message' => $pendingCount === 0
                    ? "Los {$total} sensores ya fueron medidos en este período."
                    : "{$pendingCount} de {$total} sensores pendientes. " . $rep['status_message'],
                'color_hex' => $pendingCount === 0 ? '#198754' : $rep['color_hex'],
                'badge_class' => $pendingCount === 0 ? 'bg-success' : $rep['badge_class'],
                'is_today' => $rep['is_today'] && $pendingCount > 0,
                'pending_sensors' => $pendingCount,
                'total_sensors' => $total,
                'pending_sensor_ids' => $pendingEvals->pluck('sensor_id')->values()->toArray(),
                'days_diff_from_target' => $rep['days_diff_from_target'],
            ];
        }

        foreach ($individualAlerts as $ind) {
            $alerts[] = $ind;
        }

        // Prioridad de ordenamiento: overdue -> in_tolerance -> in_window -> scheduled -> completed
        $priorityMap = [
            'overdue' => 1,
            'in_tolerance' => 2,
            'in_window' => 3,
            'scheduled' => 4,
            'completed' => 5,
        ];

        usort($alerts, function ($a, $b) use ($priorityMap) {
            $pA = $priorityMap[$a['status']] ?? 99;
            $pB = $priorityMap[$b['status']] ?? 99;
            if ($pA !== $pB) {
                return $pA <=> $pB;
            }
            return strcmp($a['target_date'], $b['target_date']);
        });

        $actionableAlerts = array_values(array_filter($alerts, fn($a) => in_array($a['status'], ['overdue', 'in_tolerance', 'in_window'])));
        $upcomingAlerts = array_values(array_filter($alerts, fn($a) => $a['status'] === 'scheduled'));

        return [
            'all' => $alerts,
            'actionable' => $actionableAlerts,
            'upcoming' => $upcomingAlerts,
            'counts' => [
                'actionable_total' => count($actionableAlerts),
                'overdue' => count(array_filter($actionableAlerts, fn($a) => $a['status'] === 'overdue')),
                'in_tolerance' => count(array_filter($actionableAlerts, fn($a) => $a['status'] === 'in_tolerance')),
                'in_window' => count(array_filter($actionableAlerts, fn($a) => $a['status'] === 'in_window')),
            ],
        ];
    }

    /**
     * Guarda la configuración de programación en un Grupo y sincroniza proxima_medicion de sus sensores sin override.
     */
    public function saveGroupSchedule(SensorGroup $group, array $scheduleData): SensorGroup
    {
        $billing = $group->billing_settings ?? [];

        $mode = $scheduleData['mode'] ?? 'fixed_day';
        $dayOfMonth = max(1, min(31, (int) ($scheduleData['day_of_month'] ?? 15)));
        $specificDate = $scheduleData['specific_date'] ?? null;
        $intervalDays = max(1, (int) ($scheduleData['interval_days'] ?? ($group->periodo_medicion ?? 30)));
        $preNoticeDays = max(0, (int) ($scheduleData['pre_notice_days'] ?? 3));
        $toleranceDays = max(0, (int) ($scheduleData['tolerance_days'] ?? 5));
        $enabled = isset($scheduleData['enabled']) ? (bool) $scheduleData['enabled'] : true;

        $billing['schedule'] = [
            'enabled' => $enabled,
            'mode' => $mode,
            'day_of_month' => $dayOfMonth,
            'specific_date' => $specificDate,
            'interval_days' => $intervalDays,
            'pre_notice_days' => $preNoticeDays,
            'tolerance_days' => $toleranceDays,
            'updated_at' => Carbon::now()->toDateTimeString(),
        ];

        $group->billing_settings = $billing;
        $group->periodo_medicion = $intervalDays;
        $group->dias_vencimiento = $toleranceDays;
        $group->save();

        // Sincronizar proxima_medicion en los sensores del grupo que heredan la programación
        $nextDate = $this->calculateNextTargetDate($billing['schedule']);
        if ($nextDate) {
            foreach ($group->sensors as $sensor) {
                $meta = $sensor->metadata ?? [];
                if (empty($meta['schedule']['override'])) {
                    $sensor->proxima_medicion = $nextDate;
                    $sensor->save();
                }
            }
        }

        return $group;
    }

    /**
     * Guarda la configuración de programación particular en un Sensor individual.
     */
    public function saveSensorSchedule(Sensor $sensor, array $scheduleData): Sensor
    {
        $metadata = $sensor->metadata ?? [];
        $override = isset($scheduleData['override']) ? (bool) $scheduleData['override'] : true;

        if (!$override) {
            unset($metadata['schedule']);
            $sensor->metadata = $metadata;
            if ($sensor->group) {
                $groupConfig = $this->getGroupScheduleConfig($sensor->group);
                $sensor->proxima_medicion = $this->calculateNextTargetDate($groupConfig);
            }
            $sensor->save();
            return $sensor;
        }

        $mode = $scheduleData['mode'] ?? 'fixed_day';
        $dayOfMonth = max(1, min(31, (int) ($scheduleData['day_of_month'] ?? 15)));
        $specificDate = $scheduleData['specific_date'] ?? null;
        $intervalDays = max(1, (int) ($scheduleData['interval_days'] ?? ($sensor->group->periodo_medicion ?? 30)));
        $preNoticeDays = max(0, (int) ($scheduleData['pre_notice_days'] ?? 3));
        $toleranceDays = max(0, (int) ($scheduleData['tolerance_days'] ?? 5));

        $metadata['schedule'] = [
            'override' => true,
            'mode' => $mode,
            'day_of_month' => $dayOfMonth,
            'specific_date' => $specificDate,
            'interval_days' => $intervalDays,
            'pre_notice_days' => $preNoticeDays,
            'tolerance_days' => $toleranceDays,
            'updated_at' => Carbon::now()->toDateTimeString(),
        ];

        $sensor->metadata = $metadata;
        $nextDate = $this->calculateNextTargetDate($metadata['schedule']);
        if ($nextDate) {
            $sensor->proxima_medicion = $nextDate;
        }
        $sensor->save();

        return $sensor;
    }

    /**
     * Calcula la próxima fecha objetivo Carbon a partir de una configuración.
     */
    public function calculateNextTargetDate(array $scheduleConfig): ?Carbon
    {
        $today = Carbon::now()->startOfDay();
        $mode = $scheduleConfig['mode'] ?? 'fixed_day';

        if ($mode === 'fixed_day') {
            $day = max(1, min(31, (int) ($scheduleConfig['day_of_month'] ?? 15)));
            $currMonthDays = $today->daysInMonth;
            $candidate = Carbon::createFromDate($today->year, $today->month, min($day, $currMonthDays))->startOfDay();
            $tolerance = (int) ($scheduleConfig['tolerance_days'] ?? 5);

            if ($candidate->copy()->addDays($tolerance)->lt($today)) {
                $nextMonth = $today->copy()->addMonthNoOverflow();
                $candidate = Carbon::createFromDate($nextMonth->year, $nextMonth->month, min($day, $nextMonth->daysInMonth))->startOfDay();
            }
            return $candidate;
        }

        if ($mode === 'specific_date' && !empty($scheduleConfig['specific_date'])) {
            try {
                return Carbon::parse($scheduleConfig['specific_date'])->startOfDay();
            } catch (\Exception $e) {
                return null;
            }
        }

        $interval = max(1, (int) ($scheduleConfig['interval_days'] ?? 30));
        return $today->copy()->addDays($interval);
    }
}
