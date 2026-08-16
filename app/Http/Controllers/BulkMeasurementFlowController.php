<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\Measurement;
use App\Models\SensorGroup;
use App\Models\UserSetting;
use App\Models\WorkspaceCollaborator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador dedicado para el flujo de mediciones masivas
 * Este controlador maneja todo el proceso de selección y toma de mediciones
 * en secuencia, sin interferir con el código existente.
 */
class BulkMeasurementFlowController extends Controller
{
    /**
     * Mostrar la vista de selección de sensores para medición masiva
     */
    public function selectSensors(Request $request)
    {
        $user = auth()->user();
        $activeWorkspace = session('active_workspace', $user->id);
        $isOwner = $activeWorkspace == $user->id;

        // Verificar permisos
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

        // Obtener todos los sensores del workspace activo
        $query = Sensor::whereHas('group', function ($q) use ($activeWorkspace, $user, $isOwner) {
            if ($isOwner) {
                $q->where('user_id', $activeWorkspace)
                    ->orWhereHas('sharedAccess', function ($q2) use ($user) {
                        $q2->where('shared_with', $user->id)
                            ->whereIn('role', ['inspector', 'admin']);
                    });
            } else {
                $q->where('user_id', $activeWorkspace);
            }
        })->with(['group', 'group.template', 'lastMeasurement']);

        $sensors = $query->orderBy('name')->get();

        $subscriptionService = new \App\Services\Subscription\SubscriptionService($user);
        $measurableSensorIds = $subscriptionService->getMeasurableSensorIds();

        foreach ($sensors as $sensor) {
            $sensor->is_measurable = in_array($sensor->id, $measurableSensorIds);
        }

        // Obtener los sensores actualmente marcados para medición
        $markedSensorIds = Sensor::where('marcado_para_medicion', true)
            ->whereHas('group', function ($q) use ($activeWorkspace, $user, $isOwner) {
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
            ->pluck('id')
            ->toArray();

        $ownerName = $isOwner ? $user->name : ($this->getWorkspaceOwnerName($activeWorkspace) ?? 'Propietario');

        // ✅ Obtener Permisos para la vista
        $gate = new \App\Services\Subscription\SubscriptionGate($user);
        $permissions = $gate->getAllPermissions();

        return view('bulk-measurements.select-sensors', compact(
            'sensors',
            'markedSensorIds',
            'ownerName',
            'activeWorkspace',
            'isOwner',
            'permissions'
        ));
    }

    /**
     * Iniciar el flujo de medición masiva con los sensores seleccionados
     */
    public function startBulkMeasurement(Request $request)
    {
        $user = auth()->user();
        $activeWorkspace = session('active_workspace', $user->id);
        $isOwner = $activeWorkspace == $user->id;

        // Validar que hay sensores seleccionados
        $sensorIds = $request->input('sensor_ids', []);

        // Convertir a array si viene como string (separado por comas)
        if (is_string($sensorIds)) {
            $sensorIds = explode(',', $sensorIds);
        }

        if (empty($sensorIds)) {
            return redirect()->back()->with('error', 'Debes seleccionar al menos un sensor.');
        }

        // Validar permisos para cada sensor
        $measurableSensorIds = [];
        foreach ($sensorIds as $sensorId) {
            $sensor = Sensor::find($sensorId);
            if (!$sensor)
                continue;

            if (!$this->canAccessSensor($user, $sensor, $activeWorkspace, $isOwner)) {
                return redirect()->back()->with('error', "No tienes permiso para el sensor {$sensor->name}.");
            }

            // ✅ Verificar que el usuario puede medir este sensor según su plan
            $subscriptionService = new \App\Services\Subscription\SubscriptionService($user);
            if ($subscriptionService->canMeasureSensor($sensor)) {
                $measurableSensorIds[] = $sensorId;
            }
        }

        // Si no hay sensores en los que pueda medir, mostrar error
        if (empty($measurableSensorIds)) {
            Log::error("BulkMeasurement abortado: no hay sensores medibles.", [
                'user_id' => $user->id,
                'requested_sensors' => $sensorIds
            ]);
            $planName = (new \App\Services\Subscription\SubscriptionService($user))->getPlan()->getPlanName();
            $maxSensors = (new \App\Services\Subscription\SubscriptionService($user))->getPlan()->getMaxSensors();
            return redirect()->back()->with(
                'error',
                "No puedes tomar mediciones en ninguno de los sensores seleccionados con tu plan {$planName}. " .
                "Tu plan permite medir solo en los primeros {$maxSensors} sensores."
            );
        }

        // Usar solo los sensores que el usuario puede medir
        $sensorIds = $measurableSensorIds;

        // Obtener el orden de selección si el usuario lo eligió
        $useSelectionOrder = $request->input('use_selection_order', 0);
        $selectionOrder = $request->input('selection_order', '');

        if ($useSelectionOrder && !empty($selectionOrder)) {
            // Usar el orden de selección manual del usuario
            $orderedSensorIds = explode(',', $selectionOrder);
            // Filtrar solo los IDs que están en la selección original (y que el usuario puede medir)
            $sensorIds = array_values(array_intersect($orderedSensorIds, $sensorIds));
        } else {
            // Ordenar por ID ascendente (orden por defecto)
            sort($sensorIds, SORT_NUMERIC);
        }

        // Marcar todos los sensores seleccionados
        Sensor::whereIn('id', $sensorIds)->update(['marcado_para_medicion' => true]);

        // Guardar la secuencia en sesión para mantener el orden
        session(['bulk_measurement_sequence' => $sensorIds]);
        session(['bulk_measurement_current_index' => 0]);

        // Redirigir al primer sensor en la secuencia
        $firstSensorId = $sensorIds[0];

        Log::info('🚀 Iniciando medición masiva', [
            'user_id' => $user->id,
            'sensor_ids' => $sensorIds,
            'first_sensor_id' => $firstSensorId,
            'active_workspace' => $activeWorkspace,
            'use_selection_order' => $useSelectionOrder
        ]);

        return redirect()->route('bulk-measurements.create', $firstSensorId);
    }

    /**
     * Mostrar el formulario de medición para un sensor en el flujo bulk
     */
    public function create(Sensor $sensor)
    {
        $user = auth()->user();
        $activeWorkspace = session('active_workspace', $user->id);
        $isOwner = $activeWorkspace == $user->id;

        // Verificar permisos
        if (!$this->canAccessSensor($user, $sensor, $activeWorkspace, $isOwner)) {
            abort(403, 'No tienes permiso para acceder a este sensor.');
        }

        // ✅ Verificar que el usuario puede medir este sensor según su plan
        $subscriptionService = new \App\Services\Subscription\SubscriptionService($user);
        if (!$subscriptionService->canMeasureSensor($sensor)) {
            $planName = $subscriptionService->getPlan()->getPlanName();
            $maxSensors = $subscriptionService->getPlan()->getMaxSensors();

            abort(403, "No puedes tomar mediciones en este sensor con tu plan {$planName}. " .
                "Tu plan permite medir solo en los primeros {$maxSensors} sensores. " .
                "Actualiza tu plan para medir en todos tus sensores.");
        }

        // Obtener la secuencia de sensores de la sesión
        $sequence = session('bulk_measurement_sequence', []);
        $currentIndex = session('bulk_measurement_current_index', 0);

        // Si no hay secuencia en sesión, crearla a partir de los sensores marcados
        if (empty($sequence)) {
            $sequence = Sensor::where('marcado_para_medicion', true)
                ->whereHas('group', function ($q) use ($activeWorkspace, $user, $isOwner) {
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
                ->orderBy('id', 'asc')
                ->pluck('id')
                ->toArray();

            // Encontrar la posición del sensor actual
            $currentIndex = array_search($sensor->id, $sequence);
            if ($currentIndex === false) {
                $currentIndex = 0;
                $sequence = [$sensor->id];
            }

            session(['bulk_measurement_sequence' => $sequence]);
            session(['bulk_measurement_current_index' => $currentIndex]);
        }

        // Verificar que el sensor actual esté en la secuencia
        if (!in_array($sensor->id, $sequence)) {
            // Si no está, redirigir al primer sensor de la secuencia
            if (!empty($sequence)) {
                return redirect()->route('bulk-measurements.create', $sequence[0]);
            }
            return redirect()->route('bulk-measurements.select')->with('error', 'No hay sensores en la secuencia.');
        }

        // Actualizar el índice actual si es necesario
        $currentIndex = array_search($sensor->id, $sequence);
        session(['bulk_measurement_current_index' => $currentIndex]);

        // Detectar si es una medición individual (acceso directo sin flujo masivo)
        // Si la secuencia tiene solo 1 sensor y no hay índice guardado previamente, es individual
        $isSingleMeasurement = (count($sequence) === 1 && !session('bulk_measurement_from_bulk', false));

        // Obtener información del sensor
        $previousMeasurement = Measurement::where('sensor_id', $sensor->id)
            ->orderBy('measured_at', 'desc')
            ->first();

        $defaultPeriod = (int) UserSetting::get($user->id, 'default_measurement_period', 30);
        $periodoMedicion = $sensor->group->periodo_medicion ?? $defaultPeriod;
        $diasVencimiento = $sensor->group->dias_vencimiento ?? 5;

        // Obtener el campo principal de la plantilla
        $mainField = $this->getMainFieldFromSensor($sensor);
        $lastValue = $previousMeasurement ? ($previousMeasurement->data[$mainField] ?? null) : null;

        // Información de progreso
        $totalSensors = count($sequence);
        $currentPosition = $currentIndex + 1;
        $hasNext = $currentIndex < $totalSensors - 1;
        $hasPrevious = $currentIndex > 0;
        $nextSensorId = $hasNext ? $sequence[$currentIndex + 1] : null;
        $previousSensorId = $hasPrevious ? $sequence[$currentIndex - 1] : null;

        // Para mediciones individuales, deshabilitar navegación
        if ($isSingleMeasurement) {
            $hasNext = false;
            $hasPrevious = false;
        }

        $ownerName = $isOwner ? $user->name : ($this->getWorkspaceOwnerName($activeWorkspace) ?? 'Propietario');

        Log::info('📝 Mostrando formulario bulk', [
            'sensor_id' => $sensor->id,
            'position' => $currentPosition,
            'total' => $totalSensors,
            'sequence' => $sequence,
            'is_single' => $isSingleMeasurement
        ]);

        return view('bulk-measurements.create', compact(
            'sensor',
            'previousMeasurement',
            'periodoMedicion',
            'diasVencimiento',
            'mainField',
            'lastValue',
            'totalSensors',
            'currentPosition',
            'hasNext',
            'hasPrevious',
            'nextSensorId',
            'previousSensorId',
            'ownerName',
            'activeWorkspace',
            'isSingleMeasurement',
            'isOwner'
        ));
    }

    /**
     * Guardar la medición y avanzar al siguiente sensor
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $sensorId = $request->input('sensor_id');

        // Validar que el sensor existe
        $sensor = Sensor::with('group')->findOrFail($sensorId);

        $activeWorkspace = session('active_workspace', $user->id);
        $isOwner = $activeWorkspace == $user->id;

        // Verificar permisos
        if (!$this->canAccessSensor($user, $sensor, $activeWorkspace, $isOwner)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para tomar mediciones en este sensor.'
            ], 403);
        }

        // Validar los datos
        $validator = Validator::make($request->all(), [
            'sensor_id' => 'required|exists:sensors,id',
            'data' => 'required|array',
            'measured_at' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Obtener el campo principal
        $mainField = $this->getMainFieldFromSensor($sensor);
        $currentValue = $request->data[$mainField] ?? null;

        if ($currentValue === null) {
            return response()->json([
                'success' => false,
                'message' => "El campo '{$mainField}' es obligatorio"
            ], 422);
        }

        // Validar que el valor sea mayor que el anterior
        $allMeasurements = Measurement::where('sensor_id', $sensor->id)
            ->orderBy('measured_at', 'asc')
            ->get();

        if ($allMeasurements->isNotEmpty()) {
            $lastMeasurement = $allMeasurements->last();
            $lastValue = $lastMeasurement->data[$mainField] ?? 0;

            if ($currentValue <= $lastValue) {
                return response()->json([
                    'success' => false,
                    'message' => "El valor debe ser mayor que el anterior ({$lastValue})",
                    'details' => [
                        'lastMeasurement' => [
                            'date' => Carbon::parse($lastMeasurement->measured_at)->format('d/m/Y H:i'),
                            'value' => $lastValue
                        ],
                        'currentMeasurement' => [
                            'date' => Carbon::parse($request->measured_at)->format('d/m/Y H:i'),
                            'value' => $currentValue
                        ]
                    ]
                ], 422);
            }
        }

        // Obtener período de medición
        $defaultPeriod = (int) UserSetting::get($user->id, 'default_measurement_period', 30);
        $periodoMedicion = $defaultPeriod;

        // Calcular próxima medición
        $proximaMedicion = Carbon::parse($request->measured_at)->addDays($periodoMedicion);

        // Crear la medición
        $measurementData = [
            'sensor_id' => $sensor->id,
            'measured_at' => $request->measured_at,
            'proxima_medicion' => $proximaMedicion,
            'periodo_medicion' => $periodoMedicion,
            'data' => $request->data,
            'created_by' => $user->id
        ];

        if (!isset($measurementData['data'][$mainField])) {
            $measurementData['data'][$mainField] = $currentValue;
        }

        $measurement = Measurement::create($measurementData);

        // Desmarcar el sensor después de guardar
        $sensor->marcado_para_medicion = false;
        $sensor->save();

        // Obtener la secuencia de la sesión
        $sequence = session('bulk_measurement_sequence', []);
        $currentIndex = session('bulk_measurement_current_index', 0);

        // Encontrar la posición del sensor actual en la secuencia
        $sensorPosition = array_search($sensor->id, $sequence);
        if ($sensorPosition === false) {
            $sensorPosition = $currentIndex;
        }

        // Calcular el siguiente índice
        $nextIndex = $sensorPosition + 1;
        $hasNext = $nextIndex < count($sequence);

        // Actualizar la sesión
        session(['bulk_measurement_current_index' => $nextIndex]);

        // Obtener el siguiente sensor
        $nextSensorId = $hasNext ? $sequence[$nextIndex] : null;
        $nextSensorUrl = $nextSensorId ? route('bulk-measurements.create', $nextSensorId) : null;

        Log::info('✅ Medición guardada (Bulk Flow)', [
            'measurement_id' => $measurement->id,
            'sensor_id' => $sensor->id,
            'user_id' => $user->id,
            'position' => $sensorPosition + 1,
            'total' => count($sequence),
            'has_next' => $hasNext,
            'next_sensor_id' => $nextSensorId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Medición guardada correctamente',
            'data' => [
                'measurement' => $measurement,
                'next_sensor_url' => $nextSensorUrl,
                'has_next_sensor' => $hasNext,
                'current_position' => $sensorPosition + 1,
                'total_sensors' => count($sequence)
            ]
        ], 201);
    }

    /**
     * Navegar al siguiente sensor en la secuencia
     */
    public function nextSensor(Sensor $sensor)
    {
        $user = auth()->user();
        $activeWorkspace = session('active_workspace', $user->id);
        $isOwner = $activeWorkspace == $user->id;

        // Verificar permisos
        if (!$this->canAccessSensor($user, $sensor, $activeWorkspace, $isOwner)) {
            abort(403, 'No tienes permiso para acceder a este sensor.');
        }

        // ✅ Verificar que el usuario puede medir este sensor según su plan
        $subscriptionService = new \App\Services\Subscription\SubscriptionService($user);
        if (!$subscriptionService->canMeasureSensor($sensor)) {
            $planName = $subscriptionService->getPlan()->getPlanName();
            $maxSensors = $subscriptionService->getPlan()->getMaxSensors();

            abort(403, "No puedes tomar mediciones en este sensor con tu plan {$planName}. " .
                "Tu plan permite medir solo en los primeros {$maxSensors} sensores. " .
                "Actualiza tu plan para medir en todos tus sensores.");
        }

        // Obtener la secuencia de la sesión
        $sequence = session('bulk_measurement_sequence', []);

        if (empty($sequence)) {
            return redirect()->route('bulk-measurements.select')->with('error', 'No hay secuencia de medición activa.');
        }

        // Encontrar la posición del sensor actual
        $currentIndex = array_search($sensor->id, $sequence);

        if ($currentIndex === false) {
            return redirect()->route('bulk-measurements.select')->with('error', 'El sensor no está en la secuencia.');
        }

        // Calcular el siguiente índice
        $nextIndex = $currentIndex + 1;

        if ($nextIndex >= count($sequence)) {
            // No hay más sensores, redirigir a la página de selección
            session()->forget(['bulk_measurement_sequence', 'bulk_measurement_current_index']);
            return redirect()->route('bulk-measurements.select')->with('success', 'Todas las mediciones completadas.');
        }

        $nextSensorId = $sequence[$nextIndex];

        // Actualizar la sesión
        session(['bulk_measurement_current_index' => $nextIndex]);

        return redirect()->route('bulk-measurements.create', $nextSensorId);
    }

    /**
     * Navegar al sensor anterior en la secuencia
     */
    public function previousSensor(Sensor $sensor)
    {
        $user = auth()->user();
        $activeWorkspace = session('active_workspace', $user->id);
        $isOwner = $activeWorkspace == $user->id;

        // Verificar permisos
        if (!$this->canAccessSensor($user, $sensor, $activeWorkspace, $isOwner)) {
            abort(403, 'No tienes permiso para acceder a este sensor.');
        }

        // ✅ Verificar que el usuario puede medir este sensor según su plan
        $subscriptionService = new \App\Services\Subscription\SubscriptionService($user);
        if (!$subscriptionService->canMeasureSensor($sensor)) {
            $planName = $subscriptionService->getPlan()->getPlanName();
            $maxSensors = $subscriptionService->getPlan()->getMaxSensors();

            abort(403, "No puedes tomar mediciones en este sensor con tu plan {$planName}. " .
                "Tu plan permite medir solo en los primeros {$maxSensors} sensores. " .
                "Actualiza tu plan para medir en todos tus sensores.");
        }

        // Obtener la secuencia de la sesión
        $sequence = session('bulk_measurement_sequence', []);

        if (empty($sequence)) {
            return redirect()->route('bulk-measurements.select')->with('error', 'No hay secuencia de medición activa.');
        }

        // Encontrar la posición del sensor actual
        $currentIndex = array_search($sensor->id, $sequence);

        if ($currentIndex === false || $currentIndex === 0) {
            // No hay sensor anterior, quedarse en el actual
            return redirect()->route('bulk-measurements.create', $sensor->id);
        }

        $previousIndex = $currentIndex - 1;
        $previousSensorId = $sequence[$previousIndex];

        // Actualizar la sesión
        session(['bulk_measurement_current_index' => $previousIndex]);

        return redirect()->route('bulk-measurements.create', $previousSensorId);
    }

    /**
     * Cancelar el flujo de medición masiva
     */
    public function cancelBulkMeasurement(Request $request)
    {
        // Limpiar la sesión
        session()->forget(['bulk_measurement_sequence', 'bulk_measurement_current_index']);

        // Opcionalmente, desmarcar todos los sensores
        $sensorIds = $request->input('sensor_ids', []);
        if (!empty($sensorIds)) {
            Sensor::whereIn('id', $sensorIds)->update(['marcado_para_medicion' => false]);
        }

        return redirect()->route('bulk-measurements.select')->with('info', 'Flujo de medición masiva cancelado.');
    }

    /**
     * Verificar si el usuario puede acceder a un sensor
     */
    private function canAccessSensor($user, $sensor, $activeWorkspace, $isOwner)
    {
        // Admin tiene permiso
        if ($user->hasRole('admin')) {
            return true;
        }

        // Propietario del sensor
        if ($sensor->group && $sensor->group->user_id === $user->id) {
            return true;
        }

        // Usuario con acceso compartido al grupo
        if (
            $sensor->group && $sensor->group->sharedAccess()
                ->where('shared_with', $user->id)
                ->whereIn('role', ['inspector', 'admin'])
                ->exists()
        ) {
            return true;
        }

        // Colaborador a través de workspace
        $workspaceId = $sensor->group->user_id ?? null;
        if ($workspaceId) {
            $collaboration = WorkspaceCollaborator::where('workspace_id', $workspaceId)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where('is_paused', false)
                ->whereIn('role', ['inspector', 'admin'])
                ->exists();

            if ($collaboration) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtener el nombre del propietario del workspace
     */
    private function getWorkspaceOwnerName($workspaceId)
    {
        $owner = \App\Models\User::find($workspaceId);
        return $owner ? $owner->name : null;
    }

    /**
     * Obtener el campo principal de un sensor según su plantilla
     */
    private function getMainFieldFromSensor($sensor)
    {
        $mainField = 'valor';

        if ($sensor && $sensor->group && $sensor->group->template) {
            $template = $sensor->group->template;
            if (isset($template->schema['campos'])) {
                foreach ($template->schema['campos'] as $campo) {
                    if ($campo['tipo'] === 'numero' && ($campo['requerido'] ?? false)) {
                        $mainField = $campo['nombre'];
                        break;
                    }
                }
            }
        }

        return $mainField;
    }
}
