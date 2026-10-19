<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\SensorGroup;
use App\Models\WorkspaceCollaborator;
use App\Services\MeasurementScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MeasurementScheduleController extends Controller
{
    protected MeasurementScheduleService $scheduleService;

    public function __construct(MeasurementScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Muestra la vista del Calendario de Programación de Tomas y Tolerancias.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $activeWorkspace = session('active_workspace', $user->id);
        $isOwner = $activeWorkspace == $user->id;

        if (!$isOwner) {
            $collaboration = WorkspaceCollaborator::where('workspace_id', $activeWorkspace)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where('is_paused', false)
                ->whereIn('role', ['inspector', 'admin'])
                ->first();

            if (!$collaboration) {
                return redirect('/dashboard')->with('error', 'No tienes acceso a este espacio.');
            }
        }

        $today = Carbon::now();
        $year = (int) $request->input('year', $today->year);
        $month = (int) $request->input('month', $today->month);

        if ($month < 1 || $month > 12) {
            $month = $today->month;
        }
        if ($year < 2020 || $year > 2035) {
            $year = $today->year;
        }

        $currentMonthDate = Carbon::createFromDate($year, $month, 1);
        $prevMonthDate = $currentMonthDate->copy()->subMonthNoOverflow();
        $nextMonthDate = $currentMonthDate->copy()->addMonthNoOverflow();

        $calendarData = $this->scheduleService->getCalendarMonthData($user, $year, $month, $activeWorkspace);
        $activeAlerts = $this->scheduleService->getActiveAlerts($user, $activeWorkspace);

        // Obtener grupos y sensores para el modal de programación
        $groups = SensorGroup::where(function ($q) use ($activeWorkspace, $user, $isOwner) {
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
            ->withCount('sensors')
            ->orderBy('name')
            ->get();

        // Enriquecer cada grupo con su config actual para el modal
        $groupsConfigMap = [];
        foreach ($groups as $g) {
            $groupsConfigMap[$g->id] = $this->scheduleService->getGroupScheduleConfig($g);
        }

        $sensors = $this->scheduleService->getWorkspaceSensors($user, $activeWorkspace);
        $sensorsConfigMap = [];
        foreach ($sensors as $s) {
            $sensorsConfigMap[$s->id] = $this->scheduleService->getSensorScheduleConfig($s);
        }

        $monthNames = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $monthName = $monthNames[$month] ?? '';

        return view('schedule.index', compact(
            'year',
            'month',
            'monthName',
            'currentMonthDate',
            'prevMonthDate',
            'nextMonthDate',
            'calendarData',
            'activeAlerts',
            'groups',
            'groupsConfigMap',
            'sensors',
            'sensorsConfigMap',
            'isOwner',
            'activeWorkspace'
        ));
    }

    /**
     * Guarda una regla de programación y tolerancia para un Grupo o Sensor(es).
     */
    public function saveSchedule(Request $request)
    {
        $user = auth()->user();
        $activeWorkspace = session('active_workspace', $user->id);

        $validator = Validator::make($request->all(), [
            'target_type' => 'required|in:group,sensor',
            'group_id' => 'required_if:target_type,group|nullable|exists:sensor_groups,id',
            'sensor_ids' => 'required_if:target_type,sensor|nullable',
            'mode' => 'required|in:fixed_day,specific_date,interval',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'specific_date' => 'nullable|date',
            'interval_days' => 'nullable|integer|min:1|max:365',
            'pre_notice_days' => 'required|integer|min:0|max:60',
            'tolerance_days' => 'required|integer|min:0|max:60',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación en los datos de programación.',
                    'errors' => $validator->errors(),
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $scheduleData = [
            'enabled' => true,
            'override' => true,
            'mode' => $request->input('mode'),
            'day_of_month' => (int) $request->input('day_of_month', 15),
            'specific_date' => $request->input('specific_date'),
            'interval_days' => (int) $request->input('interval_days', 30),
            'pre_notice_days' => (int) $request->input('pre_notice_days', 3),
            'tolerance_days' => (int) $request->input('tolerance_days', 5),
        ];

        if ($request->input('target_type') === 'group') {
            $group = SensorGroup::findOrFail($request->input('group_id'));

            $canEdit = $user->hasRole('admin') ||
                $group->user_id == $user->id ||
                $group->user_id == $activeWorkspace;

            if (!$canEdit) {
                return $this->respondError($request, 'No tienes permiso para programar este grupo.', 403);
            }

            $this->scheduleService->saveGroupSchedule($group, $scheduleData);

            // Si el usuario marcó "Limpiar excepciones individuales de sensores de este grupo"
            if ($request->boolean('reset_sensor_overrides', false)) {
                foreach ($group->sensors as $sensor) {
                    $this->scheduleService->saveSensorSchedule($sensor, ['override' => false]);
                }
            }

            $msg = "Programación del grupo \"{$group->name}\" actualizada correctamente.";
        } else {
            $sensorIds = $request->input('sensor_ids');
            if (!is_array($sensorIds)) {
                $sensorIds = array_filter(explode(',', (string) $sensorIds));
            }

            $updatedCount = 0;
            foreach ($sensorIds as $sId) {
                $sensor = Sensor::with('group')->find($sId);
                if (!$sensor || !$sensor->group) {
                    continue;
                }

                $canEdit = $user->hasRole('admin') ||
                    $sensor->group->user_id == $user->id ||
                    $sensor->group->user_id == $activeWorkspace;

                if ($canEdit) {
                    $this->scheduleService->saveSensorSchedule($sensor, $scheduleData);
                    $updatedCount++;
                }
            }

            $msg = "Se programaron {$updatedCount} sensor(es) particular(es) en el calendario.";
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Elimina la programación personalizada de un sensor (vuelve a heredar del grupo).
     */
    public function removeSensorOverride(Request $request, Sensor $sensor)
    {
        $user = auth()->user();
        $activeWorkspace = session('active_workspace', $user->id);

        $canEdit = $user->hasRole('admin') ||
            ($sensor->group && ($sensor->group->user_id == $user->id || $sensor->group->user_id == $activeWorkspace));

        if (!$canEdit) {
            return $this->respondError($request, 'No tienes permiso para modificar este sensor.', 403);
        }

        $this->scheduleService->saveSensorSchedule($sensor, ['override' => false]);

        $msg = "El sensor \"{$sensor->name}\" ahora hereda la programación de su grupo.";
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->back()->with('success', $msg);
    }

    private function respondError(Request $request, string $message, int $code = 400)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], $code);
        }
        return redirect()->back()->with('error', $message);
    }
}
