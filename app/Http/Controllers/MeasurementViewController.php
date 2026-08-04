<?php
namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\Measurement;
use App\Models\SensorGroup;
use Illuminate\Http\Request;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Log;

class MeasurementViewController extends Controller
{
    /**
     * Mostrar el listado de mediciones del usuario.
     */
    public function index()
    {
        
        $user = auth()->user();

        $sensors = Sensor::whereHas('group', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhereHas('sharedAccess', function($q) use ($user) {
                      $q->where('shared_with', $user->id)
                        ->whereIn('role', ['inspector', 'admin']);
                  });
        })->with(['group'])->get();

        $groups = SensorGroup::where('user_id', $user->id)
            ->orWhereHas('sharedAccess', function($q) use ($user) {
                $q->where('shared_with', $user->id)
                  ->whereIn('role', ['inspector', 'admin']);
            })
            ->with(['user'])
            ->orderBy('name')
            ->get();

        return view('measurements.index', compact('sensors', 'groups'));
    }

    /**
     * Mostrar el formulario para seleccionar un sensor.
     * Si el usuario es colaborador, redirige a la vista específica de inspector.
     */
    public function selectSensor(Request $request)
    {
        $user = auth()->user();
        
        // ✅ Obtener el espacio activo (propio o colaborativo)
        $activeWorkspace = session('active_workspace', $user->id);
        $isOwner = $activeWorkspace == $user->id;

        \Log::info('🔍 selectSensor llamado', [
            'user_id' => $user->id,
            'active_workspace' => $activeWorkspace,
            'is_owner' => $isOwner,
        ]);

        // ✅ Si NO es propietario (es colaborador), redirigir a la vista de inspector
        if (!$isOwner) {
            // ✅ Verificar que el usuario tiene una colaboración activa en este workspace
            $collaboration = \App\Models\WorkspaceCollaborator::where('workspace_id', $activeWorkspace)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$collaboration) {
                \Log::warning('⚠️ Colaborador sin acceso al workspace', [
                    'user_id' => $user->id,
                    'workspace_id' => $activeWorkspace,
                ]);
                return redirect('/dashboard')->with('error', 'No tienes acceso a este espacio.');
            }

            // ✅ Redirigir a la vista específica de inspector
            return redirect()->route('measurements.inspector');
        }

        // ✅ Si es propietario, mostrar sus grupos y sensores
        $groups = SensorGroup::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereHas('sharedAccess', function($q) use ($user) {
                    $q->where('shared_with', $user->id)
                        ->whereIn('role', ['inspector', 'admin']);
                });
        })->with(['user', 'template', 'sensors'])
        ->orderBy('name')
        ->get();

        // ✅ Si no hay grupos con sensores, mostrar mensaje
        if ($groups->isEmpty()) {
            return redirect()->route('sensors.index')->with('info', 'No tienes sensores para medir. Crea un sensor primero.');
        }

        return view('measurements.select-sensor', compact('groups', 'activeWorkspace', 'isOwner'));
    }

    public function create(Sensor $sensor)
    {
        $user = auth()->user();
        $activeWorkspace = session('active_workspace', $user->id);

        // ✅ Verificar que el sensor pertenece al workspace activo
        $sensorOwnerId = $sensor->group->user_id ?? null;
        
        // Si el sensor no pertenece al workspace activo, denegar acceso
        if ($sensorOwnerId != $activeWorkspace && $activeWorkspace != $user->id) {
            abort(403, 'No tienes permiso para tomar mediciones en este sensor.');
        }

        // ✅ Verificar permisos específicos
        $canTakeMeasurement = $user->hasRole('admin') ||
                            ($sensor->group && $sensor->group->user_id === $user->id) ||
                            ($sensor->group && $sensor->group->sharedAccess()
                                ->where('shared_with', $user->id)
                                ->whereIn('role', ['inspector', 'admin'])
                                ->exists()) ||
                            // ✅ También permitir si es colaborador activo en el workspace del propietario
                            \App\Models\WorkspaceCollaborator::where('workspace_id', $sensorOwnerId)
                                ->where('user_id', $user->id)
                                ->where('status', 'active')
                                ->whereIn('role', ['inspector', 'admin'])
                                ->exists();

        if (!$canTakeMeasurement) {
            abort(403, 'No tienes permiso para tomar mediciones en este sensor.');
        }

        // ✅ Verificar que el usuario puede medir este sensor según su plan
        $subscriptionService = new \App\Services\Subscription\SubscriptionService($user);
        if (!$subscriptionService->canMeasureSensor($sensor)) {
            $planName = $subscriptionService->getPlan()->getPlanName();
            $maxSensors = $subscriptionService->getPlan()->getMaxSensors();
            $measurableSensors = $subscriptionService->getMeasurableSensorIds();
            
            abort(403, "No puedes tomar mediciones en este sensor con tu plan {$planName}. " .
                  "Tu plan permite medir solo en los primeros {$maxSensors} sensores. " .
                  "Actualiza tu plan para medir en todos tus sensores.");
        }

        // Verificar plantilla
        if (!$sensor->group || !$sensor->group->template) {
            return redirect()->route('measurements.select-sensor')
                ->with('error', 'Este sensor no tiene una plantilla asociada.');
        }

        // Obtener medición anterior
        $previousMeasurement = Measurement::where('sensor_id', $sensor->id)
            ->orderBy('measured_at', 'desc')
            ->first();

        // Obtener configuraciones globales del usuario (como entero)
        $defaultPeriod = (int) UserSetting::get($user->id, 'default_measurement_period', 30);
        $defaultExpiry = (int) UserSetting::get($user->id, 'default_expiry_days', 5);

        // Usar SIEMPRE el defaultPeriod del usuario (como int)
        $periodoMedicion = $defaultPeriod;
        $diasVencimiento = $sensor->group->dias_vencimiento ?? $defaultExpiry;

        // Verificar si hay más sensores marcados para medición masiva
        $hasMoreSensors = Sensor::where('marcado_para_medicion', true)
            ->where('id', '!=', $sensor->id)
            ->whereHas('group', function($q) use ($user) {
                $q->where('user_id', $user->id)
                ->orWhereHas('sharedAccess', function($q2) use ($user) {
                    $q2->where('shared_with', $user->id)
                        ->whereIn('role', ['inspector', 'admin']);
                });
            })
            ->exists();

        // Determinar si estamos en modo masivo (si hay más sensores marcados)
        $isBulkMode = $hasMoreSensors;

        return view('measurements.create', compact(
            'sensor',
            'previousMeasurement',
            'periodoMedicion',
            'diasVencimiento',
            'defaultPeriod',
            'defaultExpiry',
            'hasMoreSensors',
            'isBulkMode' // <-- Nueva variable para indicar modo masivo
        ));
    }

    /**
     * Mostrar el formulario para editar una medición.
     */
    /**
 * Mostrar el formulario para editar una medición.
 */
public function edit(Measurement $measurement)
{
    $user = auth()->user();

    // Verificar permisos
    $canEditMeasurement = $user->hasRole('admin') ||
                         ($measurement->sensor->group && $measurement->sensor->group->user_id === $user->id) ||
                         ($measurement->sensor->group && $measurement->sensor->group->sharedAccess()
                             ->where('shared_with', $user->id)
                             ->whereIn('role', ['inspector', 'admin'])
                             ->exists());

    if (!$canEditMeasurement) {
        abort(403, 'No tienes permiso para editar esta medición.');
    }

    // Obtener configuraciones globales del usuario
    $defaultPeriod = UserSetting::get($user->id, 'default_measurement_period', 30);
    $defaultExpiry = UserSetting::get($user->id, 'default_expiry_days', 5);

    return view('measurements.edit', compact(
        'measurement',
        'defaultPeriod',
        'defaultExpiry'
    ));
}
    /**
     * Redirigir al primer sensor marcado para medición masiva
     */
    public function bulkCreateRedirect()
    {
        $user = auth()->user();
        $activeWorkspace = session('active_workspace', $user->id);
        
        // ✅ Obtener el primer sensor marcado (ordenado por ID ascendente)
        $firstMarked = Sensor::where('marcado_para_medicion', true)
            ->whereHas('group', function($q) use ($activeWorkspace) {
                $q->where('user_id', $activeWorkspace);
            })
            ->orderBy('id', 'asc')
            ->first();
        
        if (!$firstMarked) {
            return redirect()->route('measurements.select-sensor')
                ->with('info', 'No hay sensores marcados para medición masiva.');
        }
        
        Log::info('🔄 Redirigiendo a bulkCreate para el primer sensor marcado', [
            'sensor_id' => $firstMarked->id,
            'sensor_name' => $firstMarked->name,
            'active_workspace' => $activeWorkspace
        ]);
        
        return redirect()->route('measurements.bulk-create', $firstMarked->id);
    }
    
/**
     * Mostrar el formulario de medición masiva para un sensor específico
     */
    public function bulkCreate(Sensor $sensor)
    {
        $user = auth()->user();
        $activeWorkspace = session('active_workspace', $user->id);
        
        // ✅ Verificar que el sensor pertenece al workspace activo
        $sensorOwnerId = $sensor->group->user_id ?? $user->id;
        $isOwner = $sensorOwnerId == $user->id;
        $isCollaborator = false;

        // ✅ Verificar si es colaborador activo
        if ($sensorOwnerId && $sensorOwnerId != $user->id) {
            $collaboration = WorkspaceCollaborator::where('workspace_id', $sensorOwnerId)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where('is_paused', false)
                ->whereIn('role', ['inspector', 'admin'])
                ->first();
            
            if ($collaboration) {
                $isCollaborator = true;
            }
        }

        if (!$isOwner && !$isCollaborator) {
            abort(403, 'No tienes permiso para tomar mediciones en este sensor.');
        }

        if (!$sensor->group || !$sensor->group->template) {
            return redirect()->route('measurements.select-sensor')
                ->with('error', 'Este sensor no tiene una plantilla asociada.');
        }

        // ✅ FORZAR MARCADO
        if (!$sensor->marcado_para_medicion) {
            $sensor->marcado_para_medicion = true;
            $sensor->save();
            $sensor->refresh();
        }

        // ✅ OBTENER TODOS LOS SENSORES MARCADOS
        $allMarkedSensors = Sensor::where('marcado_para_medicion', true)
            ->whereHas('group', function($q) use ($sensorOwnerId) {
                $q->where('user_id', $sensorOwnerId);
            })
            ->orderBy('id', 'asc')
            ->get();

        $totalMarked = $allMarkedSensors->count();

        // ✅ ENCONTRAR POSICIÓN ACTUAL
        $currentPosition = 1;
        foreach ($allMarkedSensors as $index => $markedSensor) {
            if ($markedSensor->id === $sensor->id) {
                $currentPosition = $index + 1;
                break;
            }
        }

        // ✅ VERIFICAR SIGUIENTE SENSOR
        $nextSensor = null;
        $hasMoreSensors = false;
        
        foreach ($allMarkedSensors as $index => $markedSensor) {
            if ($markedSensor->id === $sensor->id && isset($allMarkedSensors[$index + 1])) {
                $nextSensor = $allMarkedSensors[$index + 1];
                $hasMoreSensors = true;
                break;
            }
        }

        // ✅ Obtener mediciones
        $previousMeasurement = Measurement::where('sensor_id', $sensor->id)
            ->orderBy('measured_at', 'desc')
            ->first();

        $allMeasurements = Measurement::where('sensor_id', $sensor->id)
            ->orderBy('measured_at', 'asc')
            ->get()
            ->map(function($m) {
                return [
                    'id' => $m->id,
                    'date' => $m->measured_at->toISOString(),
                    'value' => (float) ($m->data['consumo_m3'] ?? 0)
                ];
            })
            ->toArray();

        $lastValue = $previousMeasurement ? ($previousMeasurement->data['consumo_m3'] ?? null) : null;

        $defaultPeriod = (int) UserSetting::get($user->id, 'default_measurement_period', 30);
        $periodoMedicion = $sensor->group->periodo_medicion ?? $defaultPeriod;
        $diasVencimiento = $sensor->group->dias_vencimiento ?? 5;

        // ✅ URL del siguiente sensor
        $nextSensorUrl = null;
        if ($hasMoreSensors && $nextSensor) {
            $nextSensorUrl = route('measurements.bulk-create', $nextSensor->id);
        }

        Log::info('📊 BULK CREATE', [
            'sensor_actual' => $sensor->id,
            'posicion' => $currentPosition,
            'total' => $totalMarked,
            'siguiente' => $nextSensor ? $nextSensor->id : null,
            'todos' => $allMarkedSensors->pluck('id')->toArray()
        ]);

        return view('measurements.bulk-create', compact(
            'sensor',
            'previousMeasurement',
            'allMeasurements',
            'lastValue',
            'periodoMedicion',
            'diasVencimiento',
            'totalMarked',
            'currentPosition',
            'hasMoreSensors',
            'nextSensor',
            'nextSensorUrl'
        ));
    }

/**
 * Mostrar formulario de importación masiva de mediciones
 */
public function showBulkImportForm()
{
    return view('measurements.bulk-import');
}

    /**
     * Mostrar el formulario para seleccionar un sensor (para colaboradores).
     * Vista simplificada que solo muestra los sensores del espacio activo.
     */
    public function inspectorSelectSensor(Request $request)
    {
        $user = auth()->user();
        
        // ✅ Obtener el espacio activo
        $activeWorkspace = session('active_workspace', $user->id);
        
        \Log::info('🔍 inspectorSelectSensor', [
            'user_id' => $user->id,
            'active_workspace' => $activeWorkspace,
        ]);

        // ✅ Verificar que NO sea el propietario (redirigir si es propietario)
        if ($activeWorkspace == $user->id) {
            return redirect()->route('measurements.select-sensor')
                ->with('info', 'Eres el propietario. Usa la vista de selección de sensores normal.');
        }

        // ✅ Verificar que el usuario tiene una colaboración activa en este workspace
        // ✅ IMPORTANTE: Verificar que NO esté pausado (is_paused = false)
        $collaboration = \App\Models\WorkspaceCollaborator::where('workspace_id', $activeWorkspace)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('is_paused', false) // ✅ No permitir acceso si está pausado
            ->first();

        if (!$collaboration) {
            // ✅ Verificar si existe pero está pausado para dar mensaje específico
            $pausedCollaboration = \App\Models\WorkspaceCollaborator::where('workspace_id', $activeWorkspace)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where('is_paused', true)
                ->first();

            if ($pausedCollaboration) {
                return redirect('/dashboard')->with('error', 'Tu acceso a este espacio está temporalmente pausado. Contacta al propietario para reanudarlo.');
            }

            return redirect('/dashboard')->with('error', 'No tienes acceso a este espacio.');
        }

        // ✅ Obtener el propietario del workspace
        $owner = \App\Models\User::find($activeWorkspace);
        $ownerName = $owner ? $owner->name : 'Propietario';

        // ✅ Obtener TODOS los sensores del propietario
        $sensors = \App\Models\Sensor::whereHas('group', function($query) use ($activeWorkspace) {
            $query->where('user_id', $activeWorkspace);
        })->with(['group', 'group.template'])->get();

        \Log::info('🔍 inspectorSelectSensor resultado', [
            'user_id' => $user->id,
            'active_workspace' => $activeWorkspace,
            'sensors_count' => $sensors->count(),
            'is_paused' => $collaboration->is_paused,
        ]);

        return view('measurements.inspector-select-sensor', compact('sensors', 'ownerName', 'activeWorkspace'));
    }

    /**
     * Crear una nueva medición (para inspector colaborador)
     */
    public function inspectorCreate(Sensor $sensor)
    {
        $user = auth()->user();
        $activeWorkspace = session('active_workspace', $user->id);

        // ✅ Verificar que el sensor pertenece al workspace activo
        if ($sensor->group->user_id != $activeWorkspace) {
            abort(403, 'No tienes permiso para acceder a este sensor.');
        }

        // ✅ Verificar permisos de colaboración
        $canTakeMeasurement = $user->hasRole('admin') ||
                            ($sensor->group && $sensor->group->user_id === $user->id) ||
                            \App\Models\WorkspaceCollaborator::where('workspace_id', $activeWorkspace)
                                ->where('user_id', $user->id)
                                ->where('status', 'active')
                                ->whereIn('role', ['inspector', 'admin'])
                                ->exists();

        if (!$canTakeMeasurement) {
            abort(403, 'No tienes permiso para tomar mediciones en este sensor.');
        }

        // ✅ Obtener propietario
        $owner = \App\Models\User::find($activeWorkspace);
        $ownerName = $owner ? $owner->name : 'Propietario';

        // ✅ Obtener medición anterior
        $previousMeasurement = Measurement::where('sensor_id', $sensor->id)
            ->orderBy('measured_at', 'desc')
            ->first();

        // ✅ Encontrar el campo principal de la plantilla
        $mainField = 'valor';
        $template = $sensor->group->template ?? null;
        if ($template && isset($template->schema['campos'])) {
            foreach ($template->schema['campos'] as $campo) {
                if ($campo['tipo'] === 'numero' && ($campo['requerido'] ?? false)) {
                    $mainField = $campo['nombre'];
                    break;
                }
            }
        }

        // ✅ Obtener el valor de la última medición
        $lastValue = null;
        if ($previousMeasurement) {
            $lastValue = $previousMeasurement->data[$mainField] ?? null;
        }

        // ✅ Configuraciones
        $defaultPeriod = (int) \App\Models\UserSetting::get($user->id, 'default_measurement_period', 30);

        // ✅ Verificar si hay más sensores marcados para medición masiva
        $hasMoreSensors = Sensor::where('marcado_para_medicion', true)
            ->where('id', '!=', $sensor->id)
            ->whereHas('group', function($q) use ($activeWorkspace) {
                $q->where('user_id', $activeWorkspace);
            })
            ->exists();

        $totalMarked = Sensor::where('marcado_para_medicion', true)
            ->whereHas('group', function($q) use ($activeWorkspace) {
                $q->where('user_id', $activeWorkspace);
            })
            ->count();

        $currentPosition = Sensor::where('marcado_para_medicion', true)
            ->whereHas('group', function($q) use ($activeWorkspace) {
                $q->where('user_id', $activeWorkspace);
            })
            ->where('id', '<=', $sensor->id)
            ->count();

        return view('measurements.inspector-create', compact(
            'sensor',
            'previousMeasurement',
            'defaultPeriod',
            'hasMoreSensors',
            'totalMarked',
            'currentPosition',
            'ownerName',
            'mainField',
            'lastValue'
        ));
    }
}