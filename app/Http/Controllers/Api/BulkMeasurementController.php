<?php
namespace App\Http\Controllers\Api;

use App\Models\Sensor;
use App\Models\Measurement;
use App\Models\SensorGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; 
use Illuminate\Support\Facades\Validator; 

class BulkMeasurementController extends Controller
{
    /**
 * Obtener sensores con información de mediciones para gestión masiva.
 * Incluye: última medición, próxima medición, días de vencimiento, estado (pendiente/vencido)
 */
// En app/Http/Controllers/Api/BulkMeasurementController.php

/**
 * Obtener sensores para medición masiva (con estado calculado)
 */
public function getSensorsForBulkMeasurement(Request $request)
{
    $user = $request->user();

    // Filtrar sensores por usuario
    $query = Sensor::whereHas('group', function($query) use ($user) {
        $query->where('user_id', $user->id)
              ->orWhereHas('sharedAccess', function($q) use ($user) {
                  $q->where('shared_with', $user->id)
                    ->whereIn('role', ['inspector', 'admin', 'consumidor']);
              });
    })->with(['group', 'lastMeasurement']);

    // Aplicar filtros si existen
    if ($request->has('group_id') && $request->group_id) {
        $query->where('group_id', $request->group_id);
    }

    if ($request->has('status') && $request->status !== 'all') {
        // Filtrar por estado (se aplicará después de calcular el estado)
    }

    if ($request->has('search') && $request->search) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('identifier', 'like', "%{$search}%");
        });
    }

    // Obtener los sensores
    $sensors = $query->orderBy('name')->paginate($request->per_page ?? 20);

    // Calcular estado y días restantes para cada sensor
    $sensors->getCollection()->transform(function($sensor) {
        $lastMeasurement = $sensor->lastMeasurement;

        // Obtener configuración del grupo
        $periodoMedicion = $sensor->group->periodo_medicion ?? 30;
        $diasVencimiento = $sensor->group->dias_vencimiento ?? 5;

        if (!$lastMeasurement) {
            // Si no hay medición, se considera como "vencido"
            $sensor->estado = 'vencido';
            $sensor->dias_hasta_proxima = null;
            $sensor->proxima_medicion = null;
            $sensor->last_measurement_value = null;
            $sensor->last_measurement_date = null;
        } else {
            // Calcular próxima medición
            $proximaMedicion = new \DateTime($lastMeasurement->measured_at);
            $proximaMedicion->add(new \DateInterval("P{$periodoMedicion}D"));

            $hoy = new \DateTime();
            $diasHastaProxima = $hoy->diff($proximaMedicion)->days;

            // Determinar el estado
            if ($diasHastaProxima < 0) {
                $sensor->estado = 'vencido';
            } elseif ($diasHastaProxima <= $diasVencimiento) {
                $sensor->estado = 'pendiente';
            } else {
                $sensor->estado = 'al_dia';
            }

            $sensor->dias_hasta_proxima = $diasHastaProxima;
            $sensor->proxima_medicion = $proximaMedicion->format('Y-m-d H:i:s');
            $sensor->last_measurement_value = $lastMeasurement->data['valor'] ?? null;
            $sensor->last_measurement_date = $lastMeasurement->measured_at;
        }

        // Agregar campo marcado_para_medicion
        $sensor->marcado_para_medicion = (bool) $sensor->marcado_para_medicion;

        return $sensor;
    });

    // Aplicar filtro por estado (si se especificó)
    if ($request->has('status') && $request->status !== 'all') {
        $sensors = $sensors->filter(function($sensor) use ($request) {
            return $sensor->estado === $request->status;
        });
    }

    return response()->json([
        'success' => true,
        'data' => $sensors->values(),
        'meta' => [
            'current_page' => $sensors->currentPage(),
            'from' => $sensors->firstItem(),
            'last_page' => $sensors->lastPage(),
            'per_page' => $sensors->perPage(),
            'to' => $sensors->lastItem(),
            'total' => $sensors->total(),
        ],
    ]);
}

   /**
 * Marcar/desmarcar un sensor para medición
 */
public function toggleMarkForMeasurement(Request $request, Sensor $sensor)
{
    $user = $request->user();

    // ✅ Verificar permisos (incluyendo colaboradores)
    $canToggle = $this->canToggleMark($user, $sensor);

    if (!$canToggle) {
        Log::warning('⚠️ Intento de marcar sensor sin permisos', [
            'user_id' => $user->id,
            'sensor_id' => $sensor->id,
            'user_roles' => $user->getRoleNames()->toArray(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso para marcar este sensor'
        ], 403);
    }

    // ✅ Si se envía el parámetro 'mark', usarlo; si no, alternar
    if ($request->has('mark')) {
        $sensor->marcado_para_medicion = (bool) $request->mark;
    } else {
        $sensor->marcado_para_medicion = !$sensor->marcado_para_medicion;
    }
    
    $sensor->save();

    Log::info('✅ Sensor marcado/desmarcado', [
        'sensor_id' => $sensor->id,
        'sensor_name' => $sensor->name,
        'marcado_para_medicion' => $sensor->marcado_para_medicion,
        'user_id' => $user->id
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Sensor ' . ($sensor->marcado_para_medicion ? 'marcado' : 'desmarcado') . ' para medición',
        'data' => [
            'marcado_para_medicion' => $sensor->marcado_para_medicion
        ]
    ]);
}

    /**
     * Verificar si el usuario puede marcar/desmarcar un sensor
     */
    private function canToggleMark($user, $sensor)
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
        if ($sensor->group && $sensor->group->sharedAccess()
            ->where('shared_with', $user->id)
            ->whereIn('role', ['inspector', 'admin'])
            ->exists()) {
            return true;
        }

        // ✅ COLABORADOR A TRAVÉS DE WORKSPACE (verificando is_paused)
        $workspaceId = $sensor->group->user_id ?? null;
        if ($workspaceId) {
            $collaboration = \App\Models\WorkspaceCollaborator::where('workspace_id', $workspaceId)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where('is_paused', false) // ✅ Importante: no pausados
                ->whereIn('role', ['inspector', 'admin'])
                ->exists();
            
            if ($collaboration) {
                return true;
            }
        }

        return false;
    }

    /**
     * Marcar todos los sensores de un grupo para medición
     */
    public function markAllForMeasurement(Request $request, SensorGroup $group)
    {
        $user = $request->user();

        // Verificar permisos
        $canMark = $user->hasRole('admin') ||
                   $group->user_id === $user->id ||
                   $group->sharedAccess()
                       ->where('shared_with', $user->id)
                       ->whereIn('role', ['inspector', 'admin'])
                       ->exists();

        if (!$canMark) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para marcar sensores de este grupo'
            ], 403);
        }

        // Marcar todos los sensores del grupo
        $updated = DB::table('sensors')
            ->where('group_id', $group->id)
            ->update(['marcado_para_medicion' => true]);

        return response()->json([
            'success' => true,
            'message' => "Se marcaron $updated sensores del grupo para medición",
            'data' => [
                'group_id' => $group->id,
                'sensors_marked' => $updated
            ]
        ]);
    }

 

    /**
     * Actualizar la próxima fecha de medición para un sensor
     */
    public function updateNextMeasurementDate(Request $request, Sensor $sensor)
    {
        $user = $request->user();

        // Verificar permisos
        $canUpdate = $user->hasRole('admin') ||
                    ($sensor->group && $sensor->group->user_id === $user->id) ||
                    ($sensor->group && $sensor->group->sharedAccess()
                        ->where('shared_with', $user->id)
                        ->whereIn('role', ['inspector', 'admin'])
                        ->exists());

        if (!$canUpdate) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para actualizar este sensor'
            ], 403);
        }

        // Validar datos
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'proxima_medicion' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Actualizar la fecha
        $sensor->proxima_medicion = $request->proxima_medicion;
        $sensor->save();

        return response()->json([
            'success' => true,
            'message' => 'Fecha de próxima medición actualizada',
            'data' => [
                'proxima_medicion' => $sensor->proxima_medicion
            ]
        ]);
    }

    /**
     * Marcar/desmarcar todos los sensores según los filtros aplicados.
     */
    public function toggleAllMarked(Request $request)
    {
        $user = $request->user();

        // Validar parámetros
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:mark,unmark',
            'group_id' => 'nullable|exists:sensor_groups,id',
            'status' => 'nullable|in:all,al_dia,pendiente,vencido,marked',
            'search' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parámetros inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        // ✅ Obtener el workspace activo
        $activeWorkspace = session('active_workspace', $user->id);
        $isOwner = $activeWorkspace == $user->id;

        // ✅ Construir la consulta según el workspace
        if ($isOwner) {
            // Propietario: sus sensores + compartidos
            $query = Sensor::with(['group', 'lastMeasurement' => function($q) {
                $q->orderBy('measured_at', 'desc')->limit(1);
            }])
            ->whereHas('group', function($q) use ($user) {
                $q->where('user_id', $user->id)
                ->orWhereHas('sharedAccess', function($q2) use ($user) {
                    $q2->where('shared_with', $user->id)
                        ->whereIn('role', ['inspector', 'admin']);
                });
            });
        } else {
            // ✅ Colaborador: solo sensores del workspace activo (verificando is_paused)
            $collaboration = \App\Models\WorkspaceCollaborator::where('workspace_id', $activeWorkspace)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where('is_paused', false) // ✅ IMPORTANTE: no pausados
                ->whereIn('role', ['inspector', 'admin'])
                ->first();

            if (!$collaboration) {
                Log::warning('⚠️ Intento de toggleAllMarked sin permisos o usuario pausado', [
                    'user_id' => $user->id,
                    'active_workspace' => $activeWorkspace,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para marcar sensores en este espacio'
                ], 403);
            }

            $query = Sensor::with(['group', 'lastMeasurement' => function($q) {
                $q->orderBy('measured_at', 'desc')->limit(1);
            }])
            ->whereHas('group', function($q) use ($activeWorkspace) {
                $q->where('user_id', $activeWorkspace);
            });
        }

        // Aplicar filtros
        if ($request->has('group_id') && $request->group_id) {
            $query->where('group_id', $request->group_id);
        }

        if ($request->has('status') && $request->status !== 'all') {
            $today = now();
            switch ($request->status) {
                case 'pending':
                    $query->where(function($q) use ($today) {
                        $q->whereNull('proxima_medicion')
                        ->orWhere('proxima_medicion', '<=', $today->addDays(5));
                    });
                    break;
                case 'overdue':
                    $query->where('proxima_medicion', '<', $today);
                    break;
                case 'marked':
                    $query->where('marcado_para_medicion', true);
                    break;
            }
        }

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('identifier', 'like', "%{$request->search}%");
            });
        }

        // Obtener los IDs de los sensores filtrados
        $sensorIds = $query->pluck('id')->toArray();

        if (empty($sensorIds)) {
            return response()->json([
                'success' => true,
                'message' => 'No hay sensores para ' . ($request->action === 'mark' ? 'marcar' : 'desmarcar'),
                'data' => [
                    'affected' => 0
                ]
            ]);
        }

        // Actualizar el estado de marcado_para_medicion
        $affected = Sensor::whereIn('id', $sensorIds)
            ->update([
                'marcado_para_medicion' => ($request->action === 'mark')
            ]);

        Log::info('✅ Sensores marcados/desmarcados en lote', [
            'user_id' => $user->id,
            'action' => $request->action,
            'affected' => $affected,
            'workspace' => $activeWorkspace
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sensores ' . ($request->action === 'mark' ? 'marcados' : 'desmarcados') . ' correctamente',
            'data' => [
                'affected' => $affected,
                'sensor_ids' => $sensorIds
            ]
        ]);
    }

    /**
     * Obtener el siguiente sensor marcado para medición (para flujo masivo).
     */
    public function getNextSensorForBulkMeasurement(Request $request)
    {
        $user = $request->user();
        $currentSensorId = $request->current_sensor_id;
        $activeWorkspace = session('active_workspace', $user->id);
        $isOwner = $activeWorkspace == $user->id;

        // ✅ Buscar el siguiente sensor marcado según el workspace
        $query = Sensor::with(['group', 'lastMeasurement' => function($q) {
            $q->orderBy('measured_at', 'desc')->limit(1);
        }])
        ->where('marcado_para_medicion', true)
        ->where('id', '!=', $currentSensorId);

        if ($isOwner) {
            // ✅ Propietario: sus sensores + compartidos
            $query->whereHas('group', function($q) use ($user) {
                $q->where('user_id', $user->id)
                ->orWhereHas('sharedAccess', function($q2) use ($user) {
                    $q2->where('shared_with', $user->id)
                        ->whereIn('role', ['inspector', 'admin']);
                });
            });
        } else {
            // ✅ Colaborador: solo sensores del workspace activo (VERIFICANDO is_paused)
            $collaboration = \App\Models\WorkspaceCollaborator::where('workspace_id', $activeWorkspace)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where('is_paused', false) // ✅ IMPORTANTE: no pausados
                ->whereIn('role', ['inspector', 'admin'])
                ->first();

            if (!$collaboration) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para acceder a este espacio'
                ], 403);
            }

            $query->whereHas('group', function($q) use ($activeWorkspace) {
                $q->where('user_id', $activeWorkspace);
            });
        }

        $nextSensor = $query->orderBy('id')->first();

        if (!$nextSensor) {
            return response()->json([
                'success' => true,
                'message' => 'No hay más sensores marcados para medición',
                'data' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Próximo sensor para medición',
            'data' => [
                'sensor_id' => $nextSensor->id,
                'url' => route('measurements.inspector.create', $nextSensor->id)
            ]
        ]);
    }

/**
 * Guardar una nueva medición con validaciones estrictas para mediciones masivas.
 * Ahora también desmarca el sensor después de guardar.
 */
/*public function store(Request $request)
{
    $user = $request->user();

    // Validar que el sensor existe y el usuario tiene permiso
    $sensor = Sensor::findOrFail($request->sensor_id);

    $canAccess = $user->hasRole('admin') ||
                ($sensor->group && $sensor->group->user_id === $user->id) ||
                ($sensor->group && $sensor->group->sharedAccess()->where('shared_with', $user->id)->exists());

    if (!$canAccess) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso para acceder a este sensor'
        ], 403);
    }

    // Validar los datos del formulario
    $validator = Validator::make($request->all(), [
        'sensor_id' => 'required|exists:sensors,id',
        'data.valor' => 'required|numeric',
        'data.foto' => 'nullable|string',
        'measured_at' => 'required|date'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422);
    }

    $currentValue = $request->data['valor'];
    $currentDate = Carbon::parse($request->measured_at);

    // Obtener todas las mediciones del sensor ordenadas por fecha
    $allMeasurements = Measurement::where('sensor_id', $sensor->id)
        ->orderBy('measured_at', 'asc')
        ->get();

    // Validaciones de valor (mismo código que ya tienes)
    // ... (código de validación existente)

    // Obtener el período de medición del usuario
    $defaultPeriod = (int) \App\Models\UserSetting::get($user->id, 'default_measurement_period', 30);
    $periodoMedicion = $defaultPeriod;

    // Calcular próxima medición
    $proximaMedicion = Carbon::parse($request->measured_at)->addDays($periodoMedicion);

    // Crear la medición
    $measurement = Measurement::create([
        'sensor_id' => $sensor->id,
        'measured_at' => $request->measured_at,
        'proxima_medicion' => $proximaMedicion,
        'periodo_medicion' => $periodoMedicion,
        'data' => [
            'valor' => $request->data['valor'],
            'foto' => $request->data['foto'] ?? 'Sin Foto',
            'tipo' => $sensor->group->template->type ?? 'personalizado',
            'campos_personalizados' => $request->data['campos_personalizados'] ?? []
        ],
        'created_by' => $user->id
    ]);

    // ✅ Desmarcar el sensor DESPUÉS de guardar la medición
    $sensor->marcado_para_medicion = false;
    $sensor->save();

    // ✅ Obtener el siguiente sensor marcado
    $nextSensor = Sensor::where('marcado_para_medicion', true)
        ->where('id', '!=', $sensor->id) // Excluir el sensor actual
        ->whereHas('group', function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('sharedAccess', function($q2) use ($user) {
                  $q2->where('shared_with', $user->id)
                     ->whereIn('role', ['inspector', 'admin']);
              });
        })
        ->orderBy('id')
        ->first();

    $nextSensorUrl = null;
    if ($nextSensor) {
        // ✅ Usar la ruta correcta para mediciones masivas
        $nextSensorUrl = route('measurements.bulk-create', $nextSensor->id);
    }

    return response()->json([
        'success' => true,
        'message' => 'Medición guardada correctamente',
        'data' => [
            'measurement' => $measurement,
            'next_sensor_url' => $nextSensorUrl,
            'has_next_sensor' => $nextSensor !== null
        ]
    ], 201);
}*/
/**
 * Obtener estadísticas de mediciones para el dashboard
 */
public function getMeasurementStats(Request $request)
{
    $user = $request->user();

    // Obtener TODOS los sensores del usuario (incluyendo los compartidos)
    $sensors = Sensor::whereHas('group', function($query) use ($user) {
        $query->where('user_id', $user->id)
              ->orWhereHas('sharedAccess', function($q) use ($user) {
                  $q->where('shared_with', $user->id)
                    ->whereIn('role', ['inspector', 'admin', 'consumidor']);
              });
    })->with(['group', 'lastMeasurement'])->get();

    // Inicializar contadores
    $stats = [
        'total' => 0,
        'al_dia' => 0,
        'pendiente' => 0,
        'vencido' => 0,
    ];

    // Calcular estadísticas para cada sensor
    foreach ($sensors as $sensor) {
        $stats['total']++;

        // Obtener la última medición del sensor
        $lastMeasurement = $sensor->lastMeasurement;

        // Obtener configuración del grupo
        $periodoMedicion = $sensor->group->periodo_medicion ?? 30; // Valor por defecto: 30 días
        $diasVencimiento = $sensor->group->dias_vencimiento ?? 5; // Valor por defecto: 5 días

        if (!$lastMeasurement) {
            // Si no hay medición, se considera como "vencido" (necesita medición)
            $stats['vencido']++;
            continue;
        }

        // Calcular días hasta la próxima medición
        $proximaMedicion = new \DateTime($lastMeasurement->measured_at);
        $proximaMedicion->add(new \DateInterval("P{$periodoMedicion}D")); // Sumar período de medición

        $hoy = new \DateTime();
        $diasHastaProxima = $hoy->diff($proximaMedicion)->days;

        // Determinar el estado
        if ($diasHastaProxima < 0) {
            $stats['vencido']++;
        } elseif ($diasHastaProxima <= $diasVencimiento) {
            $stats['pendiente']++;
        } else {
            $stats['al_dia']++;
        }
    }

    return response()->json([
        'success' => true,
        'data' => $stats,
    ]);
}

/**
 * Guardar una nueva medición con validaciones estrictas para mediciones masivas.
 * Ahora también desmarca el sensor después de guardar.
 */
public function store(Request $request)
{
    $user = $request->user();

    // ✅ Validar que el sensor existe
    $sensor = Sensor::with('group')->findOrFail($request->sensor_id);

    // ✅ Verificar permisos (incluyendo colaboradores)
    $canAccess = $this->canAccessSensor($user, $sensor);

    if (!$canAccess) {
        Log::warning('⚠️ Intento de medición sin permisos', [
            'user_id' => $user->id,
            'sensor_id' => $sensor->id,
            'user_roles' => $user->getRoleNames()->toArray(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso para tomar mediciones en este sensor.'
        ], 403);
    }

    // Validar los datos del formulario
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

    // ✅ Obtener el campo principal de la plantilla
    $mainField = $this->getMainFieldFromSensor($sensor);
    $currentValue = $request->data[$mainField] ?? null;
    $currentDate = Carbon::parse($request->measured_at);

    if ($currentValue === null) {
        return response()->json([
            'success' => false,
            'message' => "El campo '{$mainField}' es obligatorio"
        ], 422);
    }

    // Obtener todas las mediciones del sensor ordenadas por fecha
    $allMeasurements = Measurement::where('sensor_id', $sensor->id)
        ->orderBy('measured_at', 'asc')
        ->get();

    // ✅ Validar que el valor sea mayor que la última medición (si existe)
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
                        'date' => $currentDate->format('d/m/Y H:i'),
                        'value' => $currentValue
                    ]
                ]
            ], 422);
        }
    }

    // Obtener el período de medición del usuario
    $defaultPeriod = (int) \App\Models\UserSetting::get($user->id, 'default_measurement_period', 30);
    $periodoMedicion = $defaultPeriod;

    // Calcular próxima medición
    $proximaMedicion = Carbon::parse($request->measured_at)->addDays($periodoMedicion);

    // ✅ Crear la medición con los datos completos
    $measurementData = [
        'sensor_id' => $sensor->id,
        'measured_at' => $request->measured_at,
        'proxima_medicion' => $proximaMedicion,
        'periodo_medicion' => $periodoMedicion,
        'data' => $request->data,
        'created_by' => $user->id
    ];

    // ✅ Asegurar que el campo principal tenga el valor
    if (!isset($measurementData['data'][$mainField])) {
        $measurementData['data'][$mainField] = $currentValue;
    }

    $measurement = Measurement::create($measurementData);

    // ✅ Desmarcar el sensor DESPUÉS de guardar la medición
    $sensor->marcado_para_medicion = false;
    $sensor->save();

    // ✅ Obtener el siguiente sensor marcado para el workspace activo
    $activeWorkspace = session('active_workspace', $user->id);
    $isOwner = $activeWorkspace == $user->id;

    $nextSensorQuery = Sensor::where('marcado_para_medicion', true)
        ->where('id', '!=', $sensor->id);

    if ($isOwner) {
        $nextSensorQuery->whereHas('group', function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('sharedAccess', function($q2) use ($user) {
                  $q2->where('shared_with', $user->id)
                     ->whereIn('role', ['inspector', 'admin']);
              });
        });
    } else {
        $nextSensorQuery->whereHas('group', function($q) use ($activeWorkspace) {
            $q->where('user_id', $activeWorkspace);
        });
    }

    $nextSensor = $nextSensorQuery->orderBy('id')->first();

    $nextSensorUrl = null;
    if ($nextSensor) {
        $nextSensorUrl = route('measurements.inspector.create', $nextSensor->id);
    }

    Log::info('✅ Medición guardada (Bulk)', [
        'measurement_id' => $measurement->id,
        'sensor_id' => $sensor->id,
        'user_id' => $user->id,
        'main_field' => $mainField,
        'value' => $currentValue
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Medición guardada correctamente',
        'data' => [
            'measurement' => $measurement,
            'next_sensor_url' => $nextSensorUrl,
            'has_next_sensor' => $nextSensor !== null
        ]
    ], 201);
}

/**
 * Verificar si el usuario puede acceder a un sensor
 */
private function canAccessSensor($user, $sensor)
{
    // ✅ Admin tiene permiso
    if ($user->hasRole('admin')) {
        return true;
    }

    // ✅ Propietario del sensor
    if ($sensor->group && $sensor->group->user_id === $user->id) {
        return true;
    }

    // ✅ Usuario con acceso compartido al grupo
    if ($sensor->group && $sensor->group->sharedAccess()
        ->where('shared_with', $user->id)
        ->whereIn('role', ['inspector', 'admin'])
        ->exists()) {
        return true;
    }

    // ✅ COLABORADOR A TRAVÉS DE WORKSPACE
    $workspaceId = $sensor->group->user_id ?? null;
    if ($workspaceId) {
        $collaboration = \App\Models\WorkspaceCollaborator::where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('role', ['inspector', 'admin'])
            ->exists();
        
        if ($collaboration) {
            return true;
        }
    }

    return false;
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