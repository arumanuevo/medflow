<?php
// app/Http/Controllers/Api/SensorController.php
namespace App\Http\Controllers\Api;

use App\Models\Sensor;
use App\Models\SensorGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; 
use App\Models\Measurement;
use App\Services\Subscription\SubscriptionService;
use Carbon\Carbon;

class SensorController extends Controller
{
    /**
     * Listar todos los sensores del usuario (propios o compartidos).
     * Filtrado por el espacio de trabajo activo.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $activeWorkspace = session('active_workspace', $user->id);

        $hasAccess = $this->canAccessWorkspace($user, $activeWorkspace);

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este espacio de trabajo.'
            ], 403);
        }

        if ($activeWorkspace == $user->id) {
            $sensors = Sensor::whereHas('group', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->with(['group', 'group.template', 'group.user']);
        } else {
            $collaboration = \App\Models\WorkspaceCollaborator::where('workspace_id', $activeWorkspace)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$collaboration) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a este espacio de trabajo.'
                ], 403);
            }

            $sensors = Sensor::whereHas('group', function($q) use ($activeWorkspace) {
                $q->where('user_id', $activeWorkspace);
            })->with(['group', 'group.template', 'group.user']);
        }

        $sensors = $sensors->with(['measurements' => function($query) {
            $query->orderBy('measured_at', 'desc')->limit(1);
        }])
        ->orderBy('name')
        ->get();

        $sensors->map(function($sensor) {
            $sensor->last_measurement = $sensor->measurements->first();
            unset($sensor->measurements);
            return $sensor;
        });

        return response()->json([
            'success' => true,
            'message' => 'Sensores obtenidos correctamente',
            'data' => $sensors,
        ]);
    }

    /**
     * Verificar si el usuario puede acceder a un workspace
     */
    private function canAccessWorkspace($user, $workspaceId)
    {
        if ($workspaceId == $user->id) {
            return true;
        }

        $collaboration = \App\Models\WorkspaceCollaborator::where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        return $collaboration !== null;
    }

    /**
     * Crear un nuevo sensor CON VERIFICACIÓN DE LÍMITES
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'identifier' => 'required|string|max:255|unique:sensors',
            'description' => 'nullable|string',
            'group_id' => 'required|exists:sensor_groups,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $group = SensorGroup::findOrFail($request->group_id);

        // Verificar permisos básicos
        $canCreate = $user->hasRole('admin') ||
                    $group->user_id === $user->id ||
                    $group->sharedAccess()->where('shared_with', $user->id)->exists();

        if (!$canCreate) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para crear sensores en este grupo',
            ], 403);
        }

        // ✅ VERIFICAR LÍMITE DE SUSCRIPCIÓN
        $subscriptionService = new SubscriptionService($user);
        
        if (!$subscriptionService->canCreateSensor()) {
            $status = $subscriptionService->getLimitStatus()['sensors'];
            $planName = $subscriptionService->getPlan()->getPlanName();
            
            return response()->json([
                'success' => false,
                'message' => "Has alcanzado el límite de {$status['max']} sensores para tu plan {$planName}. " .
                             "Elimina algunos sensores o actualiza tu plan para crear más.",
                'code' => 'sensor_limit_exceeded',
                'data' => [
                    'limit_status' => $status,
                    'plan' => $planName
                ]
            ], 403);
        }

        // ✅ Crear el sensor
        $sensor = Sensor::create([
            'name' => $request->name,
            'identifier' => $request->identifier,
            'description' => $request->description,
            'group_id' => $request->group_id,
        ]);

        Log::info('✅ Sensor creado', [
            'user_id' => $user->id,
            'sensor_id' => $sensor->id,
            'sensor_name' => $sensor->name
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sensor creado correctamente',
            'data' => $sensor->load(['group', 'group.template', 'group.user']),
        ], 201);
    }

    /**
     * Mostrar un sensor específico.
     */
    public function show(Sensor $sensor)
    {
        $user = request()->user();
    
        $canAccess = $user->hasRole('admin') ||
                    ($sensor->group && $sensor->group->user_id === $user->id) ||
                    ($sensor->group && $sensor->group->sharedAccess()->where('shared_with', $user->id)->exists());
    
        if (!$canAccess) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para acceder a este sensor',
            ], 403);
        }
    
        $sensor->load(['group', 'group.template', 'group.user']);
        $sensor->last_measurement = Measurement::where('sensor_id', $sensor->id)
            ->orderBy('measured_at', 'desc')
            ->first();
    
        return response()->json([
            'success' => true,
            'message' => 'Sensor obtenido correctamente',
            'data' => $sensor,
        ]);
    }

    /**
     * Verificar qué identificadores existen en un grupo
     */
    public function checkIdentifiers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifiers' => 'required|array',
            'group_id' => 'required|exists:sensor_groups,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $identifiers = $request->identifiers;
        $groupId = $request->group_id;

        $group = SensorGroup::findOrFail($groupId);
        $canAccess = $user->hasRole('admin') ||
                     $group->user_id === $user->id ||
                     $group->sharedAccess()
                         ->where('shared_with', $user->id)
                         ->whereIn('role', ['inspector', 'admin'])
                         ->exists();

        if (!$canAccess) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para acceder a este grupo'
            ], 403);
        }

        $existingIdentifiers = Sensor::where('group_id', $groupId)
            ->whereIn('identifier', $identifiers)
            ->pluck('identifier')
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'existing' => $existingIdentifiers,
                'total_checked' => count($identifiers)
            ]
        ]);
    }

    /**
     * Actualizar un sensor.
     */
    public function update(Request $request, Sensor $sensor)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'identifier' => 'sometimes|string|max:255|unique:sensors,identifier,' . $sensor->id,
            'description' => 'nullable|string',
            'group_id' => 'sometimes|exists:sensor_groups,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        $canUpdate = $user->hasRole('admin') ||
                    ($sensor->group && $sensor->group->user_id === $user->id) ||
                    ($sensor->group && $sensor->group->sharedAccess()->where('shared_with', $user->id)->exists());

        if (!$canUpdate) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para actualizar este sensor',
            ], 403);
        }

        if ($request->filled('group_id')) {
            $newGroup = SensorGroup::findOrFail($request->group_id);
            $canUpdateGroup = $user->hasRole('admin') ||
                             $newGroup->user_id === $user->id ||
                             $newGroup->sharedAccess()->where('shared_with', $user->id)->exists();

            if (!$canUpdateGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para mover este sensor al grupo seleccionado',
                ], 403);
            }
        }

        $sensor->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Sensor actualizado correctamente',
            'data' => $sensor->load(['group', 'group.template', 'group.user']),
        ]);
    }

    /**
     * Eliminar un sensor
     */
    public function destroy($id)
    {
        try {
            $user = auth()->user();
            $sensor = Sensor::findOrFail($id);

            Log::info("Intento de eliminación de sensor", [
                'user_id' => $user->id,
                'sensor_id' => $sensor->id,
                'sensor_name' => $sensor->name
            ]);

            $canDelete = $user->hasRole('admin') ||
                        ($sensor->group && $sensor->group->user_id === $user->id) ||
                        ($sensor->group && $sensor->group->sharedAccess()
                            ->where('shared_with', $user->id)
                            ->whereIn('role', ['admin', 'inspector'])
                            ->exists());

            if (!$canDelete) {
                Log::warning("Intento de eliminación no autorizado", [
                    'user_id' => $user->id,
                    'sensor_id' => $sensor->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar este sensor.'
                ], 403);
            }

            $sensor->delete();

            Log::info("Sensor eliminado correctamente", [
                'sensor_id' => $sensor->id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sensor eliminado correctamente.'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Sensor no encontrado", ['sensor_id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'El sensor no existe.'
            ], 404);

        } catch (\Exception $e) {
            Log::error("Error al eliminar sensor", [
                'sensor_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el sensor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todos los sensores del usuario con días restantes.
     */
    public function getSensorsWithRemainingDays(Request $request)
    {
        $user = $request->user();

        $sensors = Sensor::where(function($query) use ($user) {
            $query->whereHas('group', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->orWhereHas('group.sharedAccess', function($q) use ($user) {
                $q->where('shared_with', $user->id);
            });
        })
        ->with(['group', 'lastMeasurement' => function($query) {
            $query->orderBy('measured_at', 'desc')->limit(1);
        }])
        ->get();

        $sensorsWithData = $sensors->map(function($sensor) {
            $lastMeasurement = $sensor->lastMeasurement;
            $nextMeasurementDate = null;
            $daysRemaining = null;
            $status = 'Pendiente';

            if ($lastMeasurement) {
                $nextMeasurementDays = $sensor->next_measurement_days ?? 30;
                $nextMeasurementDate = Carbon::parse($lastMeasurement->measured_at)
                    ->addDays($nextMeasurementDays);

                $now = Carbon::now();
                $daysRemaining = $nextMeasurementDate->diffInDays($now, false);

                if ($daysRemaining <= 0) {
                    $status = 'Atrasado';
                } elseif ($daysRemaining <= 7) {
                    $status = 'Próximo a Vencer';
                } else {
                    $status = 'Al Día';
                }
            } else {
                $status = 'Pendiente (Sin mediciones)';
            }

            return [
                'id' => $sensor->id,
                'name' => $sensor->name,
                'identifier' => $sensor->identifier,
                'group' => $sensor->group ? [
                    'id' => $sensor->group->id,
                    'name' => $sensor->group->name
                ] : null,
                'last_measurement' => $lastMeasurement ? [
                    'id' => $lastMeasurement->id,
                    'value' => $lastMeasurement->data['valor'] ?? null,
                    'measured_at' => $lastMeasurement->measured_at->toDateTimeString(),
                    'formatted_date' => $lastMeasurement->measured_at->format('d/m/Y, H:i')
                ] : null,
                'next_measurement_date' => $nextMeasurementDate ? $nextMeasurementDate->toDateTimeString() : null,
                'formatted_next_measurement_date' => $nextMeasurementDate ? $nextMeasurementDate->format('d/m/Y, H:i') : null,
                'days_remaining' => $daysRemaining !== null ? round($daysRemaining) : null,
                'status' => $status,
                'next_measurement_days' => $sensor->next_measurement_days ?? 30
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Sensores obtenidos correctamente',
            'data' => $sensorsWithData
        ]);
    }

    /**
     * Configurar días para la próxima medición de un sensor.
     */
    public function setNextMeasurementDays(Request $request, Sensor $sensor)
    {
        $user = $request->user();

        $canUpdate = $user->hasRole('admin') ||
                     ($sensor->group && $sensor->group->user_id === $user->id) ||
                     ($sensor->group && $sensor->group->sharedAccess()->where('shared_with', $user->id)->exists());

        if (!$canUpdate) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para actualizar este sensor'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'next_measurement_days' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $sensor->update([
            'next_measurement_days' => $request->next_measurement_days
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Días para próxima medición actualizados correctamente',
            'data' => $sensor
        ]);
    }
}