<?php
namespace App\Http\Controllers\Api;

use App\Models\Measurement;
use App\Models\Sensor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\UserSetting;

class MeasurementController extends Controller
{
    /**
 * Listar todas las mediciones con filtrado y paginación.
 * Solo muestra mediciones de sensores a los que el usuario tiene acceso.
 */
public function index(Request $request)
{
    $user = $request->user();

    // Validar parámetros de paginación
    $perPage = $request->per_page ?? 10;
    $page = $request->page ?? 1;

    // Obtener el campo y dirección de ordenamiento
    $sortField = $request->sort_field ?? 'measured_at';
    $sortDirection = $request->sort_direction ?? 'desc';

    // Validar que el campo de ordenamiento sea válido
    $validSortFields = ['id', 'sensor_id', 'measured_at', 'created_at'];
    if (!in_array($sortField, $validSortFields)) {
        $sortField = 'measured_at';
    }

    // Validar dirección de ordenamiento
    if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
        $sortDirection = 'desc';
    }

    // Construir la consulta base: SOLO mediciones de sensores a los que el usuario tiene acceso
    $query = Measurement::with(['sensor', 'sensor.group', 'sensor.group.template'])
        ->whereHas('sensor.group', function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('sharedAccess', function($q2) use ($user) {
                  $q2->where('shared_with', $user->id)
                     ->whereIn('role', ['inspector', 'admin']);
              });
        });

    // Aplicar filtros si existen
    if ($request->has('sensor_id') && $request->sensor_id) {
        $query->where('sensor_id', $request->sensor_id)
              ->whereHas('sensor.group', function($q) use ($user) {
                  $q->where('user_id', $user->id)
                    ->orWhereHas('sharedAccess', function($q2) use ($user) {
                        $q2->where('shared_with', $user->id)
                           ->whereIn('role', ['inspector', 'admin']);
                    });
              });
    }

    if ($request->has('group_id') && $request->group_id) {
        $query->whereHas('sensor.group', function($q) use ($request, $user) {
            $q->where('id', $request->group_id)
              ->where(function($q2) use ($user) {
                  $q2->where('user_id', $user->id)
                     ->orWhereHas('sharedAccess', function($q3) use ($user) {
                         $q3->where('shared_with', $user->id)
                            ->whereIn('role', ['inspector', 'admin']);
                     });
              });
        });
    }

    if ($request->has('error_type') && $request->error_type) {
        // Filtrar por tipo de error (se aplicará después de obtener los datos)
    }

    if ($request->has('date_from') && $request->date_from) {
        $query->where('measured_at', '>=', $request->date_from);
    }

    if ($request->has('date_to') && $request->date_to) {
        $query->where('measured_at', '<=', $request->date_to . ' 23:59:59');
    }

    if ($request->has('search') && $request->search) {
        $search = $request->search;
        $query->where(function($q) use ($search, $user) {
            $q->whereHas('sensor', function($q2) use ($search, $user) {
                $q2->where('name', 'like', "%{$search}%")
                   ->orWhere('identifier', 'like', "%{$search}%")
                   ->whereHas('group', function($q3) use ($user) {
                       $q3->where('user_id', $user->id)
                          ->orWhereHas('sharedAccess', function($q4) use ($user) {
                              $q4->where('shared_with', $user->id)
                                 ->whereIn('role', ['inspector', 'admin']);
                          });
                   });
            })
            ->orWhere('data->valor', 'like', "%{$search}%")
            ->orWhere('data->tipo', 'like', "%{$search}%");
        });
    }

    // Aplicar ordenamiento
    if ($sortField === 'sensor') {
        $query->orderBy('sensor_id', $sortDirection)->orderBy('measured_at', 'desc');
    } else {
        $query->orderBy($sortField, $sortDirection);
    }

    // Obtener TODAS las mediciones del usuario para calcular estadísticas (sin paginación)
    $allMeasurements = Measurement::with(['sensor', 'sensor.group', 'sensor.group.template'])
        ->whereHas('sensor.group', function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('sharedAccess', function($q2) use ($user) {
                  $q2->where('shared_with', $user->id)
                     ->whereIn('role', ['inspector', 'admin']);
              });
        })
        ->orderBy('sensor_id')
        ->orderBy('measured_at', 'asc')
        ->get();

    // Calcular estadísticas de errores manualmente
    $errorStats = [
        'negative_consumption' => 0,
        'inconsistent_date' => 0,
        'first_measurement' => 0,
        'valid' => 0
    ];

    // Agrupar todas las mediciones por sensor
    $allMeasurementsBySensor = $allMeasurements->groupBy('sensor_id');

    foreach ($allMeasurementsBySensor as $sensorId => $sensorMeasurements) {
        // Obtener el sensor y su plantilla para determinar el campo principal
        $sensor = $sensorMeasurements->first()->sensor;
        $mainField = $this->getMainFieldFromSensor($sensor);
        
        // Ordenar las mediciones del sensor por fecha (ascendente)
        $sortedMeasurements = $sensorMeasurements->sortBy('measured_at');

        foreach ($sortedMeasurements as $index => $measurement) {
            $previousMeasurement = ($index > 0) ? $sortedMeasurements->get($index - 1) : null;

            if (!$previousMeasurement) {
                $errorStats['first_measurement']++;
                continue;
            }

            // Validar fecha
            $lastDate = Carbon::parse($previousMeasurement->measured_at);
            $currentDate = Carbon::parse($measurement->measured_at);

            if ($currentDate->lt($lastDate)) {
                $errorStats['inconsistent_date']++;
                continue;
            }

            // ✅ Validar consumo usando el campo principal de la plantilla
            $lastValue = $previousMeasurement->data[$mainField] ?? 0;
            $currentValue = $measurement->data[$mainField] ?? 0;
            $consumption = $currentValue - $lastValue;

            if ($consumption < 0) {
                $errorStats['negative_consumption']++;
            } else {
                $errorStats['valid']++;
            }
        }
    }

    // Obtener las mediciones paginadas
    $measurements = $query->paginate($perPage);

    // Agrupar mediciones por sensor para calcular consumo y estado
    $measurementsBySensor = $measurements->getCollection()->groupBy('sensor_id');

    $measurementsWithData = $measurements->getCollection()->map(function($measurement) use ($allMeasurementsBySensor) {
        $sensorId = $measurement->sensor_id;
        $sensorMeasurements = $allMeasurementsBySensor->get($sensorId, collect());

        // Obtener el campo principal de la plantilla
        $mainField = $this->getMainFieldFromSensor($measurement->sensor);

        // Ordenar las mediciones del sensor por fecha (ascendente)
        $sortedMeasurements = $sensorMeasurements->sortBy('measured_at');

        // Encontrar el índice de la medición actual en el array ordenado
        $currentIndex = $sortedMeasurements->search(function($m) use ($measurement) {
            return $m->id === $measurement->id;
        });

        // Obtener la medición anterior (en el tiempo)
        $previousMeasurement = ($currentIndex > 0) ? $sortedMeasurements->get($currentIndex - 1) : null;

        $data = $measurement->data ?? [];
        $valor = $data[$mainField] ?? 0;

        if ($previousMeasurement) {
            $lastValue = $previousMeasurement->data[$mainField] ?? 0;
            $consumption = $valor - $lastValue;
            $measurement->consumption = $consumption;

            // Validar fecha
            $lastDate = Carbon::parse($previousMeasurement->measured_at);
            $currentDate = Carbon::parse($measurement->measured_at);

            if ($currentDate->lt($lastDate)) {
                $measurement->error_type = 'inconsistent_date';
            } elseif ($consumption < 0) {
                $measurement->error_type = 'negative_consumption';
            } else {
                $measurement->error_type = 'valid';
            }
        } else {
            // Primera medición
            $measurement->consumption = 0;
            $measurement->error_type = 'first_measurement';
        }

        $measurement->previous_measurement = $previousMeasurement;
        return $measurement;
    });

    // Filtrar por tipo de error si se especifica
    if ($request->has('error_type') && $request->error_type) {
        $filteredMeasurements = $measurementsWithData->filter(function($measurement) use ($request) {
            return $measurement->error_type === $request->error_type;
        });

        $measurements->setCollection($filteredMeasurements);
    } else {
        $measurements->setCollection($measurementsWithData);
    }

    // Formatear la respuesta
    return response()->json([
        'success' => true,
        'message' => 'Mediciones obtenidas correctamente',
        'data' => $measurements->items(),
        'error_stats' => $errorStats,
        'meta' => [
            'current_page' => $measurements->currentPage(),
            'from' => $measurements->firstItem(),
            'to' => $measurements->lastItem(),
            'total' => $measurements->total(),
            'last_page' => $measurements->lastPage(),
            'per_page' => $measurements->perPage()
        ]
    ]);
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

    /**
     * Crear una nueva medición.
     * Acepta dos formatos:
     * 1. Desde formulario web: value, photo, custom_fields, measured_at
     * 2. Desde API: data.valor, data.foto, data.campos_personalizados, measured_at
     */
   /**
     * Crear una nueva medición.
     * Validaciones:
     * - La fecha no puede ser anterior a la última medición (a menos que sea una corrección explícita).
     * - El valor no puede ser menor al de la última medición (para evitar consumos negativos).
     */
    /*public function store(Request $request)
    {
        Log::info('Petición para guardar medición:', [
            'all' => $request->all(),
            'user_id' => $request->user() ? $request->user()->id : 'No autenticado',
            'method' => $request->method(),
            'url' => $request->url()
        ]);

        $user = $request->user();

        if (!$user) {
            Log::error('Usuario no autenticado intentó guardar medición');
            return response()->json([
                'success' => false,
                'message' => 'No autenticado'
            ], 401);
        }

        if (!$request->has('sensor_id')) {
            Log::error('Falta sensor_id en la petición', $request->all());
            return response()->json([
                'success' => false,
                'message' => 'El campo sensor_id es obligatorio'
            ], 422);
        }

        $sensor = Sensor::find($request->sensor_id);

        if (!$sensor) {
            Log::error('Sensor no encontrado', ['sensor_id' => $request->sensor_id]);
            return response()->json([
                'success' => false,
                'message' => 'Sensor no encontrado'
            ], 404);
        }

        $canTakeMeasurement = $user->hasRole('admin') ||
                             ($sensor->group && $sensor->group->user_id === $user->id) ||
                             ($sensor->group && $sensor->group->sharedAccess()
                                 ->where('shared_with', $user->id)
                                 ->whereIn('role', ['inspector', 'admin'])
                                 ->exists());

        if (!$canTakeMeasurement) {
            Log::warning('Usuario sin permisos intentó guardar medición', [
                'user_id' => $user->id,
                'sensor_id' => $sensor->id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para tomar mediciones en este sensor'
            ], 403);
        }

        if (!$sensor->group || !$sensor->group->template) {
            Log::error('Sensor sin plantilla asociada', ['sensor_id' => $sensor->id]);
            return response()->json([
                'success' => false,
                'message' => 'Este sensor no tiene una plantilla asociada. No puedes tomar mediciones.'
            ], 400);
        }

        $type = $sensor->group->template->type;

        // Validar los datos
        $validator = Validator::make($request->all(), [
            'sensor_id' => 'required|exists:sensors,id',
            'value' => 'sometimes|required|numeric',
            'photo' => 'nullable|string',
            'custom_fields' => 'nullable|array',
            'data.valor' => 'sometimes|required|numeric',
            'data.foto' => 'nullable|string',
            'data.campos_personalizados' => 'nullable|array',
            'measured_at' => 'required|date',
            'force' => 'sometimes|boolean' // Campo opcional para forzar el guardado (ej: correcciones)
        ]);

        if ($validator->fails()) {
            Log::error('Error de validación al guardar medición', [
                'errors' => $validator->errors(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Normalizar los datos
            $valor = $request->value ?? $request->data['valor'] ?? null;
            $foto = $request->photo ?? $request->data['foto'] ?? 'Sin Foto';
            $camposPersonalizados = $request->custom_fields ?? $request->data['campos_personalizados'] ?? [];
            $measuredAt = $request->measured_at;
            $force = $request->force ?? false; // ¿Forzar el guardado aunque haya errores de validación?

            if (!$valor) {
                return response()->json([
                    'success' => false,
                    'message' => 'El campo valor es obligatorio'
                ], 422);
            }

            // Obtener la última medición del sensor
            $lastMeasurement = Measurement::where('sensor_id', $sensor->id)
                ->orderBy('measured_at', 'desc')
                ->first();

            // Validar fecha: no puede ser anterior a la última medición (a menos que se fuerce)
            if ($lastMeasurement && !$force) {
                $lastDate = Carbon::parse($lastMeasurement->measured_at);
                $newDate = Carbon::parse($measuredAt);

                if ($newDate->lt($lastDate)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La fecha de la nueva medición no puede ser anterior a la última medición registrada (' . $lastDate->format('d/m/Y H:i') . ').',
                        'last_measurement_date' => $lastDate->toDateTimeString(),
                        'suggestion' => 'Si necesitas corregir una medición anterior, usa el parámetro "force=true" o edita la medición existente.'
                    ], 422);
                }
            }

            // Validar valor: no puede ser menor al de la última medición (a menos que se fuerce)
            if ($lastMeasurement && !$force) {
                $lastValue = $lastMeasurement->data['valor'] ?? 0;
                if ($valor < $lastValue) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El valor de la nueva medición (' . $valor . ') no puede ser menor al de la última medición registrada (' . $lastValue . ').',
                        'last_measurement_value' => $lastValue,
                        'suggestion' => 'Si necesitas corregir una medición, usa el parámetro "force=true" o edita la medición existente.'
                    ], 422);
                }
            }

            // Crear la medición
            $measurement = Measurement::create([
                'sensor_id' => $sensor->id,
                'created_by' => $user->id,
                'data' => [
                    'tipo' => $type,
                    'valor' => (float) $valor,
                    'foto' => $foto,
                    'campos_personalizados' => $camposPersonalizados
                ],
                'measured_at' => $measuredAt
            ]);

            // Actualizar la última medición del sensor (opcional, para optimizar consultas futuras)
            $sensor->ultima_medicion = $measuredAt;
            $sensor->save();

            Log::info('Medición guardada correctamente', [
                'measurement_id' => $measurement->id,
                'sensor_id' => $sensor->id,
                'user_id' => $user->id,
                'data' => $measurement->data,
                'measured_at' => $measurement->measured_at
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Medición guardada correctamente',
                    'data' => $measurement
                ], 201);
            } else {
                return redirect()->route('sensors.show', $sensor->id)
                    ->with('success', 'Medición guardada correctamente.');
            }

        } catch (\Exception $e) {
            Log::error('Error al guardar medición en la base de datos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al guardar la medición: ' . $e->getMessage()
                ], 500);
            } else {
                return redirect()->back()
                    ->with('error', 'Error al guardar la medición: ' . $e->getMessage())
                    ->withInput();
            }
        }
    }*/



    /**
     * Mostrar una medición específica.
     */
    public function show(Measurement $measurement)
    {
        $user = request()->user();

        // Verificar que el usuario tenga permiso para ver esta medición
        $canViewMeasurement = $user->hasRole('admin') ||
                              ($measurement->sensor->group && $measurement->sensor->group->user_id === $user->id) ||
                              ($measurement->sensor->group && $measurement->sensor->group->sharedAccess()
                                  ->where('shared_with', $user->id)
                                  ->whereIn('role', ['inspector', 'admin'])
                                  ->exists());

        if (!$canViewMeasurement) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver esta medición'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Medición obtenida correctamente',
            'data' => $measurement
        ]);
    }

    /**
 * Actualizar una medición.
 * Validaciones:
 * - La nueva fecha no puede ser posterior a la fecha de la medición siguiente (si existe).
 * - El nuevo valor no puede ser mayor al valor de la medición siguiente (si existe).
 */
/**
 * Actualizar una medición.
 * Validaciones:
 * - La nueva fecha no puede ser posterior a la fecha de la medición siguiente (si existe).
 * - El nuevo valor no puede ser mayor al valor de la medición siguiente (si existe).
 */
public function update(Request $request, Measurement $measurement)
{
    $user = $request->user();

    // Verificar permisos
    $canUpdateMeasurement = $user->hasRole('admin') ||
                           ($measurement->sensor->group && $measurement->sensor->group->user_id === $user->id) ||
                           ($measurement->sensor->group && $measurement->sensor->group->sharedAccess()
                               ->where('shared_with', $user->id)
                               ->whereIn('role', ['inspector', 'admin'])
                               ->exists());

    if (!$canUpdateMeasurement) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso para actualizar esta medición'
        ], 403);
    }

    // Validar los datos
    $validator = Validator::make($request->all(), [
        'data.tipo' => 'sometimes|string|in:agua,gas,electricidad,personalizado',
        'data.valor' => 'sometimes|numeric',
        'data.foto' => 'nullable|string',
        'data.campos_personalizados' => 'nullable|array',
        'measured_at' => 'sometimes|date',
        'force' => 'sometimes|boolean'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422);
    }

    // Obtener todas las mediciones del mismo sensor, ordenadas por fecha
    $sensorMeasurements = Measurement::where('sensor_id', $measurement->sensor_id)
        ->orderBy('measured_at')
        ->get();

    // Encontrar el índice de la medición actual en el array ordenado
    $currentIndex = $sensorMeasurements->search(function($m) use ($measurement) {
        return $m->id === $measurement->id;
    });

    // Obtener la medición siguiente (en el tiempo)
    $nextMeasurement = ($currentIndex < $sensorMeasurements->count() - 1) ? $sensorMeasurements->get($currentIndex + 1) : null;

    $updateData = [];

    // Validar fecha: la nueva fecha no puede ser posterior a la fecha de la medición siguiente
    if ($request->has('measured_at')) {
        $newDate = Carbon::parse($request->measured_at);

        if ($nextMeasurement && !$request->force) {
            $nextDate = Carbon::parse($nextMeasurement->measured_at);
            if ($newDate->gt($nextDate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La fecha de la medición no puede ser posterior a la fecha de la medición siguiente (' . $nextDate->format('d/m/Y H:i') . ').',
                    'next_measurement_date' => $nextDate->toDateTimeString(),
                    'suggestion' => 'Si necesitas corregir la fecha, usa el parámetro "force=true" o edita la medición siguiente.'
                ], 422);
            }
        }

        $updateData['measured_at'] = $request->measured_at;

        // Si se actualiza la fecha, recalcular proxima_medicion usando el período existente
        if (isset($measurement->periodo_medicion)) {
            $updateData['proxima_medicion'] = Carbon::parse($request->measured_at)
                ->addDays($measurement->periodo_medicion);
        }
    }

    // Validar valor: el nuevo valor no puede ser mayor al valor de la medición siguiente
    if ($request->has('data.valor')) {
        $newValue = $request->data['valor'];

        if ($nextMeasurement && !$request->force) {
            $nextValue = $nextMeasurement->data['valor'] ?? 0;
            if ($newValue > $nextValue) {
                return response()->json([
                    'success' => false,
                    'message' => 'El valor de la medición (' . $newValue . ') no puede ser mayor al valor de la medición siguiente (' . $nextValue . ').',
                    'next_measurement_value' => $nextValue,
                    'suggestion' => 'Si necesitas corregir el valor, usa el parámetro "force=true" o edita la medición siguiente.'
                ], 422);
            }
        }

        // Actualizar el valor en los datos
        $updateData['data']['valor'] = $newValue;
    }

    // Actualizar la foto si se envía en la petición
    if ($request->has('data.foto')) {
        $updateData['data']['foto'] = $request->data['foto'];
    }

    // Validar otros campos de data
    if ($request->has('data')) {
        $newData = $request->data;
        foreach ($newData as $key => $value) {
            if ($key !== 'valor' && $key !== 'foto' && $key !== 'tipo') {
                $updateData['data'][$key] = $value;
            }
        }
    }

    // Fusionar los datos existentes con los nuevos
    if (isset($updateData['data'])) {
        $updateData['data'] = array_merge($measurement->data, $updateData['data']);
    }

    // Actualizar la medición
    $measurement->update($updateData);

    return response()->json([
        'success' => true,
        'message' => 'Medición actualizada correctamente',
        'data' => $measurement
    ]);
}
    /**
     * Eliminar una medición.
     */
    public function destroy(Measurement $measurement)
    {
        $user = request()->user();

        // Verificar que el usuario tenga permiso para eliminar esta medición
        $canDeleteMeasurement = $user->hasRole('admin') ||
                                ($measurement->sensor->group && $measurement->sensor->group->user_id === $user->id);

        if (!$canDeleteMeasurement) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar esta medición'
            ], 403);
        }

        $measurement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Medición eliminada correctamente'
        ]);
    }

    /**
 * Subir foto de medición (guarda en /public/measurements).
 */
/*public function uploadPhoto(Request $request)
{
    $user = $request->user();

    // Validar que el usuario esté autenticado
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'No autenticado'
        ], 401);
    }

    // Validar datos
    $validator = Validator::make($request->all(), [
        'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        'sensor_id' => 'required|exists:sensors,id'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422);
    }

    $sensor = Sensor::find($request->sensor_id);

    // Verificar permisos
    $canUpload = $user->hasRole('admin') ||
                 ($sensor->group && $sensor->group->user_id === $user->id) ||
                 ($sensor->group && $sensor->group->sharedAccess()
                     ->where('shared_with', $user->id)
                     ->whereIn('role', ['inspector', 'admin'])
                     ->exists());

    if (!$canUpload) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso para subir fotos para este sensor'
        ], 403);
    }

    try {
        // Guardar la foto en /public/measurements
        $file = $request->file('foto');
        $filename = $file->getClientOriginalName();

        // Asegurarse de que el directorio exista
        if (!file_exists(public_path('measurements'))) {
            mkdir(public_path('measurements'), 0775, true);
        }

        // Mover el archivo a /public/measurements
        $file->move(public_path('measurements'), $filename);

        // Devolver la ruta relativa desde /public
        return response()->json([
            'success' => true,
            'message' => 'Foto subida correctamente',
            'path' => 'measurements/' . $filename
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al guardar la foto: ' . $e->getMessage()
        ], 500);
    }
}*/

/**
 * Obtener detalles de errores de una medición específica.
 * Compara con la medición anterior en el tiempo (no por ID).
 */
public function getMeasurementErrors(Request $request, Measurement $measurement)
{
    $user = $request->user();

    // Verificar permisos
    $canViewMeasurement = $user->hasRole('admin') ||
                         ($measurement->sensor->group && $measurement->sensor->group->user_id === $user->id) ||
                         ($measurement->sensor->group && $measurement->sensor->group->sharedAccess()
                             ->where('shared_with', $user->id)
                             ->whereIn('role', ['inspector', 'admin'])
                             ->exists());

    if (!$canViewMeasurement) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso para ver esta medición'
        ], 403);
    }

    // Obtener TODAS las mediciones del mismo sensor, ordenadas por fecha
    $sensorMeasurements = Measurement::where('sensor_id', $measurement->sensor_id)
        ->orderBy('measured_at')
        ->get();

    // Encontrar el índice de la medición actual en el array ordenado
    $currentIndex = $sensorMeasurements->search(function($m) use ($measurement) {
        return $m->id === $measurement->id;
    });

    // Obtener la medición anterior (en el tiempo)
    $previousMeasurement = ($currentIndex > 0) ? $sensorMeasurements->get($currentIndex - 1) : null;

    $errors = [];
    $warnings = [];

    if (!$previousMeasurement) {
        $warnings[] = [
            'type' => 'Primera Medición',
            'message' => 'Esta es la primera medición registrada para este sensor.',
            'suggestion' => 'No hay datos previos para comparar.'
        ];
    } else {
        // Validar fecha: la fecha actual debe ser >= a la fecha anterior
        $lastDate = Carbon::parse($previousMeasurement->measured_at);
        $currentDate = Carbon::parse($measurement->measured_at);

        if ($currentDate->lt($lastDate)) {
            $errors[] = [
                'type' => 'Fecha Inconsistente',
                'message' => 'La fecha de esta medición (' . $currentDate->format('d/m/Y H:i') . ') es anterior a la última medición registrada (' . $lastDate->format('d/m/Y H:i') . ').',
                'suggestion' => 'Considera corregir la fecha o contactar con un administrador.'
            ];
        }

        // Validar consumo
        $lastValue = $previousMeasurement->data['valor'] ?? 0;
        $currentValue = $measurement->data['valor'] ?? 0;
        $consumption = $currentValue - $lastValue;

        if ($consumption < 0) {
            $errors[] = [
                'type' => 'Consumo Negativo',
                'message' => 'El consumo calculado (' . $consumption . ') es negativo (valor actual: ' . $currentValue . ', valor anterior: ' . $lastValue . ').',
                'suggestion' => 'Verifica que los valores sean correctos. Si es una corrección, usa el parámetro "force=true".'
            ];
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Detalles de errores obtenidos correctamente',
        'data' => [
            'measurement_id' => $measurement->id,
            'sensor_id' => $measurement->sensor_id,
            'previous_measurement_id' => $previousMeasurement ? $previousMeasurement->id : null,
            'previous_measurement_date' => $previousMeasurement ? $previousMeasurement->measured_at : null,
            'previous_measurement_value' => $previousMeasurement ? ($previousMeasurement->data['valor'] ?? 0) : null,
            'errors' => $errors,
            'warnings' => $warnings
        ]
    ]);
}

/**
 * Obtener estadísticas de errores de mediciones.
 * Usa la MISMA lógica que el método index para garantizar consistencia.
 */
public function getErrorStats(Request $request)
{
    $user = $request->user();

    // Obtener TODAS las mediciones del usuario (sin paginación)
    $query = Measurement::with(['sensor', 'sensor.group'])
        ->whereHas('sensor.group', function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('sharedAccess', function($q2) use ($user) {
                  $q2->where('shared_with', $user->id)
                     ->whereIn('role', ['inspector', 'admin']);
              });
        })
        ->orderBy('sensor_id')
        ->orderBy('measured_at');

    // Aplicar los mismos filtros que en el método index
    if ($request->has('sensor_id') && $request->sensor_id) {
        $query->where('sensor_id', $request->sensor_id);
    }

    if ($request->has('group_id') && $request->group_id) {
        $query->whereHas('sensor.group', function($q) use ($request) {
            $q->where('id', $request->group_id);
        });
    }

    if ($request->has('date_from') && $request->date_from) {
        $query->where('measured_at', '>=', $request->date_from);
    }

    if ($request->has('date_to') && $request->date_to) {
        $query->where('measured_at', '<=', $request->date_to . ' 23:59:59');
    }

    if ($request->has('search') && $request->search) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->whereHas('sensor', function($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%")
                   ->orWhere('identifier', 'like', "%{$search}%");
            })
            ->orWhere('data->valor', 'like', "%{$search}%")
            ->orWhere('data->tipo', 'like', "%{$search}%");
        });
    }

    $measurements = $query->get();

    // Agrupar mediciones por sensor
    $measurementsBySensor = $measurements->groupBy('sensor_id');

    $stats = [
        'negative_consumption' => 0,
        'inconsistent_date' => 0,
        'first_measurement' => 0,
        'valid' => 0
    ];

    foreach ($measurementsBySensor as $sensorId => $sensorMeasurements) {
        // Ordenar las mediciones del sensor por fecha (ascendente)
        $sortedMeasurements = $sensorMeasurements->sortBy('measured_at');

        foreach ($sortedMeasurements as $index => $measurement) {
            $previousMeasurement = ($index > 0) ? $sortedMeasurements->get($index - 1) : null;

            if (!$previousMeasurement) {
                $stats['first_measurement']++;
                continue;
            }

            // Validar fecha: la fecha actual debe ser >= a la fecha anterior
            $lastDate = Carbon::parse($previousMeasurement->measured_at);
            $currentDate = Carbon::parse($measurement->measured_at);

            if ($currentDate->lt($lastDate)) {
                $stats['inconsistent_date']++;
                continue; // No contar como válida
            }

            // Validar consumo
            $lastValue = $previousMeasurement->data['valor'] ?? 0;
            $currentValue = $measurement->data['valor'] ?? 0;
            $consumption = $currentValue - $lastValue;

            if ($consumption < 0) {
                $stats['negative_consumption']++;
            } else {
                $stats['valid']++;
            }
        }
    }

    return [
        'negative_consumption' => $stats['negative_consumption'],
        'inconsistent_date' => $stats['inconsistent_date'],
        'first_measurement' => $stats['first_measurement'],
        'valid' => $stats['valid']
    ];
}

/**
 * Obtener mediciones con errores de un tipo específico.
 *
 * @param Request $request
 * @param string $errorType
 * @param bool $returnArray Si es true, devuelve un array en lugar de JsonResponse
 * @return array|JsonResponse
 */
public function getMeasurementsByErrorType(Request $request, $errorType, $returnArray = false)
{
    $user = $request->user();

    // Validar tipo de error
    $validErrorTypes = ['negative_consumption', 'inconsistent_date', 'first_measurement'];
    if (!in_array($errorType, $validErrorTypes)) {
        if ($returnArray) {
            return [
                'success' => false,
                'message' => 'Tipo de error no válido'
            ];
        }
        return response()->json([
            'success' => false,
            'message' => 'Tipo de error no válido'
        ], 400);
    }

    // Obtener todas las mediciones del usuario
    $measurements = Measurement::with(['sensor', 'sensor.group'])
        ->whereHas('sensor.group', function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('sharedAccess', function($q2) use ($user) {
                  $q2->where('shared_with', $user->id)
                     ->whereIn('role', ['inspector', 'admin']);
              });
        })
        ->orderBy('sensor_id')
        ->orderBy('measured_at')
        ->get();

    // Agrupar mediciones por sensor
    $measurementsBySensor = $measurements->groupBy('sensor_id');
    $errorMeasurements = collect();

    foreach ($measurementsBySensor as $sensorId => $sensorMeasurements) {
        // Ordenar las mediciones del sensor por fecha (ascendente)
        $sortedMeasurements = $sensorMeasurements->sortBy('measured_at');

        foreach ($sortedMeasurements as $index => $measurement) {
            $previousMeasurement = ($index > 0) ? $sortedMeasurements->get($index - 1) : null;

            if (!$previousMeasurement) {
                if ($errorType === 'first_measurement') {
                    $errorMeasurements->push($measurement);
                }
                continue;
            }

            // Validar fecha
            $lastDate = Carbon::parse($previousMeasurement->measured_at);
            $currentDate = Carbon::parse($measurement->measured_at);

            if ($currentDate->lt($lastDate) && $errorType === 'inconsistent_date') {
                // Agregar ambas mediciones involucradas
                if (!$errorMeasurements->contains('id', $measurement->id)) {
                    $errorMeasurements->push($measurement);
                }
                if (!$errorMeasurements->contains('id', $previousMeasurement->id)) {
                    $errorMeasurements->push($previousMeasurement);
                }
            }

            // Validar consumo
            $lastValue = $previousMeasurement->data['valor'] ?? 0;
            $currentValue = $measurement->data['valor'] ?? 0;
            $consumption = $currentValue - $lastValue;

            if ($consumption < 0 && $errorType === 'negative_consumption') {
                // Agregar ambas mediciones involucradas
                if (!$errorMeasurements->contains('id', $measurement->id)) {
                    $errorMeasurements->push($measurement);
                }
                if (!$errorMeasurements->contains('id', $previousMeasurement->id)) {
                    $errorMeasurements->push($previousMeasurement);
                }
            }
        }
    }

    // Ordenar las mediciones con errores por fecha (descendente)
    $errorMeasurements = $errorMeasurements->sortByDesc('measured_at')->values();

    // Agregar datos de consumo y estado a cada medición
    $measurementsWithData = $errorMeasurements->map(function($measurement) use ($measurementsBySensor) {
        $sensorId = $measurement->sensor_id;
        $sensorMeasurements = $measurementsBySensor->get($sensorId, collect());

        // Ordenar las mediciones del sensor por fecha (ascendente)
        $sortedMeasurements = $sensorMeasurements->sortBy('measured_at');

        // Encontrar el índice de la medición actual en el array ordenado
        $currentIndex = $sortedMeasurements->search(function($m) use ($measurement) {
            return $m->id === $measurement->id;
        });

        // Obtener la medición anterior (en el tiempo)
        $previousMeasurement = ($currentIndex > 0) ? $sortedMeasurements->get($currentIndex - 1) : null;

        $data = $measurement->data ?? [];
        $valor = $data['valor'] ?? 0;

        if ($previousMeasurement) {
            $lastValue = $previousMeasurement->data['valor'] ?? 0;
            $consumption = $valor - $lastValue;
            $measurement->consumption = $consumption;

            // Validar fecha
            $lastDate = Carbon::parse($previousMeasurement->measured_at);
            $currentDate = Carbon::parse($measurement->measured_at);

            if ($currentDate->lt($lastDate)) {
                $measurement->error_type = 'inconsistent_date';
            } elseif ($consumption < 0) {
                $measurement->error_type = 'negative_consumption';
            } else {
                $measurement->error_type = 'valid';
            }
        } else {
            $measurement->consumption = null;
            $measurement->error_type = 'first_measurement';
        }

        $measurement->previous_measurement = $previousMeasurement;
        return $measurement;
    });

    if ($returnArray) {
        return [
            'success' => true,
            'message' => 'Mediciones con errores obtenidas correctamente',
            'data' => $measurementsWithData,
            'error_type' => $errorType,
            'count' => $measurementsWithData->count()
        ];
    }

    return response()->json([
        'success' => true,
        'message' => 'Mediciones con errores obtenidas correctamente',
        'data' => $measurementsWithData,
        'error_type' => $errorType,
        'count' => $measurementsWithData->count()
    ]);
}

/**
 * Obtener detalles de un error específico (para mostrar en el modal al hacer clic en una tarjeta).
 * Incluye más detalles como número de registro, diferencias de valores, etc.
 */
public function getErrorDetails(Request $request, $errorType)
{
    $user = $request->user();

    // Validar tipo de error
    $validErrorTypes = ['negative_consumption', 'inconsistent_date', 'first_measurement'];
    if (!in_array($errorType, $validErrorTypes)) {
        return response()->json([
            'success' => false,
            'message' => 'Tipo de error no válido'
        ], 400);
    }

    try {
        // Obtener las mediciones con este tipo de error (como array)
        $response = $this->getMeasurementsByErrorType($request, $errorType, true);

        if (!$response['success']) {
            return response()->json($response, 400);
        }

        $measurements = $response['data'];

        // Agrupar las mediciones por sensor para mostrar pares relacionados
        $groupedBySensor = $measurements->groupBy('sensor_id');

        $errorDetails = [];

        foreach ($groupedBySensor as $sensorId => $sensorMeasurements) {
            // Ordenar por fecha
            $sortedMeasurements = $sensorMeasurements->sortBy('measured_at');

            // Agrupar en pares (medición actual + medición anterior)
            for ($i = 0; $i < $sortedMeasurements->count(); $i++) {
                $current = $sortedMeasurements->get($i);
                $previous = ($i > 0) ? $sortedMeasurements->get($i - 1) : null;

                $detail = [
                    'sensor_id' => $sensorId,
                    'sensor_name' => $current->sensor->name ?? 'N/A',
                    'sensor_identifier' => $current->sensor->identifier ?? 'N/A',
                    'group_name' => $current->sensor->group->name ?? 'Sin grupo',
                    'current_measurement' => [
                        'id' => $current->id,
                        'value' => $current->data['valor'] ?? 0,
                        'date' => $current->measured_at,
                        'consumption' => $current->consumption
                    ],
                    'previous_measurement' => $previous ? [
                        'id' => $previous->id,
                        'value' => $previous->data['valor'] ?? 0,
                        'date' => $previous->measured_at
                    ] : null,
                    'error_type' => $errorType,
                    'error_message' => $this->getErrorMessage($errorType, $current, $previous),
                    'difference' => $previous ? [
                        'value' => ($current->data['valor'] ?? 0) - ($previous->data['valor'] ?? 0),
                        'days' => $previous ? Carbon::parse($current->measured_at)->diffInDays(Carbon::parse($previous->measured_at)) : null
                    ] : null
                ];

                // Agregar número de registro (posición en el listado)
                $detail['record_number'] = $i + 1;

                $errorDetails[] = $detail;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalles del error obtenidos correctamente',
            'data' => $errorDetails,
            'error_type' => $errorType,
            'count' => count($errorDetails)
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al procesar los detalles del error: ' . $e->getMessage(),
            'error' => $e->getTraceAsString()
        ], 500);
    }
}
/**
 * Obtener mensaje de error personalizado.
 */
private function getErrorMessage($errorType, $current, $previous)
{
    switch ($errorType) {
        case 'negative_consumption':
            $currentValue = $current->data['valor'] ?? 0;
            $previousValue = $previous->data['valor'] ?? 0;
            $consumption = $currentValue - $previousValue;
            return "Consumo negativo detectado: {$consumption} (Valor actual: {$currentValue}, Valor anterior: {$previousValue}).";

        case 'inconsistent_date':
            $currentDate = Carbon::parse($current->measured_at)->format('d/m/Y H:i');
            $previousDate = Carbon::parse($previous->measured_at)->format('d/m/Y H:i');
            return "Fecha inconsistente: La medición del {$currentDate} es anterior a la del {$previousDate}.";

        case 'first_measurement':
            return "Esta es la primera medición registrada para este sensor.";

        default:
            return "Error desconocido.";
    }
}

/**
 * Obtener el siguiente sensor marcado para medición (para flujo masivo).
 */
public function getNextSensorForBulkMeasurement(Request $request)
{
    $user = $request->user();

    // Obtener el sensor actual (opcional, para evitar ciclos)
    $currentSensorId = $request->current_sensor_id;

    // Obtener el primer sensor marcado para medición
    $nextSensor = Sensor::with(['group', 'lastMeasurement' => function($q) {
            $q->orderBy('measured_at', 'desc')->limit(1);
        }])
        ->where('marcado_para_medicion', true)
        ->whereHas('group', function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('sharedAccess', function($q2) use ($user) {
                  $q2->where('shared_with', $user->id)
                     ->whereIn('role', ['inspector', 'admin']);
              });
        })
        ->when($currentSensorId, function($q) use ($currentSensorId) {
            $q->where('id', '!=', $currentSensorId); // Excluir el sensor actual
        })
        ->orderBy('id') // Ordenar por ID para consistencia
        ->first();

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
            'url' => route('measurements.create', $nextSensor->id)
        ]
    ]);
}

public function create(Sensor $sensor)
{
    $user = auth()->user();
    $activeWorkspace = session('active_workspace', $user->id);
    
    // Verificar permisos
    $canTakeMeasurement = $user->hasRole('admin') ||
                        ($sensor->group && $sensor->group->user_id === $user->id) ||
                        ($sensor->group && $sensor->group->sharedAccess()
                            ->where('shared_with', $user->id)
                            ->whereIn('role', ['inspector', 'admin'])
                            ->exists());

    if (!$canTakeMeasurement) {
        abort(403, 'No tienes permiso para tomar mediciones en este sensor.');
    }

    if (!$sensor->group || !$sensor->group->template) {
        return redirect()->route('measurements.select-sensor')
            ->with('error', 'Este sensor no tiene una plantilla asociada.');
    }

    $previousMeasurement = Measurement::where('sensor_id', $sensor->id)
        ->orderBy('measured_at', 'desc')
        ->first();

    // ✅ Obtener todas las mediciones para validación en frontend
    $allMeasurements = Measurement::where('sensor_id', $sensor->id)
        ->orderBy('measured_at', 'asc')
        ->get()
        ->map(function($m) {
            return [
                'id' => $m->id,
                'date' => $m->measured_at->toISOString(),
                'value' => (float) ($m->data['consumo_m3'] ?? 0)
            ];
        });

    $defaultPeriod = (int) UserSetting::get($user->id, 'default_measurement_period', 30);
    $periodoMedicion = $sensor->group->periodo_medicion ?? $defaultPeriod;

    return view('measurements.create', compact(
        'sensor',
        'previousMeasurement',
        'allMeasurements',
        'periodoMedicion'
    ));
}
/**
 * Generar nombre de archivo para la foto de medición
 */
/*public function generatePhotoPath($user, $sensor, $measuredAt)
{
    $userId = $user->id;
    $sensorId = $sensor->id;
    $year = $measuredAt->format('Y');
    $month = $measuredAt->format('m');
    $sensorName = Str::slug($sensor->name);
    $timestamp = $measuredAt->format('Ymd_His');
    
    // Generar nombre único
    $filename = $sensorName . '_' . $timestamp . '.png';
    
    // Ruta completa
    return "uploads/measurements/{$userId}/{$sensorId}/{$year}/{$month}/{$filename}";
}*/

/**
 * Subir foto de medición (guarda en /public/measurements).
 */
public function uploadPhoto(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'No autenticado'
        ], 401);
    }

    // ✅ Validación simplificada
    $validator = Validator::make($request->all(), [
        'foto' => 'required|image|max:5120', // ✅ Solo image, sin mimes
        'sensor_id' => 'required|exists:sensors,id'
    ]);

    if ($validator->fails()) {
        \Log::error('❌ Error de validación en uploadPhoto', [
            'errors' => $validator->errors()->toArray(),
            'file_size' => $request->file('foto')?->getSize(),
            'file_extension' => $request->file('foto')?->getClientOriginalExtension(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422);
    }

    $sensor = Sensor::with('group')->find($request->sensor_id);

    // ✅ Verificar permisos (incluyendo colaboradores)
    $canUpload = $this->canUploadPhoto($user, $sensor);

    if (!$canUpload) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso para subir fotos para este sensor'
        ], 403);
    }

    try {
        $file = $request->file('foto');
        
        // ✅ Generar nombre único
        $extension = $file->getClientOriginalExtension();
        $sensorIdentifier = $sensor->identifier ?? 'sensor_' . $sensor->id;
        $timestamp = now()->format('Ymd_His');
        $filename = $sensorIdentifier . '_' . $timestamp . '.' . $extension;
        $filename = str_replace(' ', '_', $filename);
        
        // ✅ Guardar en /public/measurements
        $path = public_path('measurements');
        if (!file_exists($path)) {
            mkdir($path, 0775, true);
        }

        $file->move($path, $filename);

        \Log::info('✅ Foto subida correctamente', [
            'filename' => $filename,
            'sensor_id' => $sensor->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Foto subida correctamente',
            'path' => 'measurements/' . $filename
        ]);

    } catch (\Exception $e) {
        \Log::error('❌ Error al guardar la foto: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al guardar la foto: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Verificar si el usuario puede subir fotos para un sensor
 */
private function canUploadPhoto($user, $sensor)
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
 * Generar nombre de archivo para la foto de medición
 */
private function generatePhotoPath($user, $sensor, $measuredAt)
{
    $userId = $user->id;
    $sensorId = $sensor->id;
    $year = $measuredAt->format('Y');
    $month = $measuredAt->format('m');
    $sensorName = Str::slug($sensor->name);
    $timestamp = $measuredAt->format('Ymd_His');
    $random = Str::random(6);
    
    $filename = $sensorName . '_' . $timestamp . '_' . $random . '.png';
    
    return "uploads/measurements/{$userId}/{$sensorId}/{$year}/{$month}/{$filename}";
}

/**
 * Guardar una nueva medición con validaciones estrictas.
 */
public function store(Request $request)
{
    $user = $request->user();

    // ✅ Validar que el sensor existe
    $sensor = Sensor::with('group.template')->findOrFail($request->sensor_id);

    // ✅ Verificar permisos
    $canAccess = $this->canAccessSensor($user, $sensor);
    if (!$canAccess) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso para acceder a este sensor'
        ], 403);
    }

    // ✅ Validación básica
    $validator = Validator::make($request->all(), [
        'sensor_id' => 'required|exists:sensors,id',
        'measured_at' => 'required|date',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422);
    }

    // ✅ Validar que data existe y es un array
    $data = $request->input('data', []);
    if (empty($data) || !is_array($data)) {
        return response()->json([
            'success' => false,
            'message' => 'Los datos de la medición son obligatorios'
        ], 422);
    }

    // ✅ Encontrar el campo principal de la plantilla
    $mainField = 'consumo_m3';
    $template = $sensor->group->template ?? null;
    if ($template && isset($template->schema['campos'])) {
        foreach ($template->schema['campos'] as $campo) {
            if ($campo['tipo'] === 'numero' && ($campo['requerido'] ?? false)) {
                $mainField = $campo['nombre'];
                break;
            }
        }
    }

    // ✅ Validar que el campo principal tenga valor
    if (!isset($data[$mainField]) || $data[$mainField] === '' || $data[$mainField] === null) {
        return response()->json([
            'success' => false,
            'message' => "El campo '{$mainField}' es obligatorio",
            'field' => $mainField
        ], 422);
    }

    $currentValue = (float) $data[$mainField];
    $currentDate = Carbon::parse($request->measured_at);

    // =============================================
    // ✅ VALIDACIÓN DE SECUENCIA POR FECHA
    // =============================================
    
    // Obtener TODAS las mediciones del sensor ordenadas por fecha
    $allMeasurements = Measurement::where('sensor_id', $sensor->id)
        ->orderBy('measured_at', 'asc')
        ->get();

    // Si no hay mediciones, es la primera → siempre válida
    if ($allMeasurements->isEmpty()) {
        // Crear la medición
        $measurement = Measurement::create([
            'sensor_id' => $sensor->id,
            'measured_at' => $request->measured_at,
            'data' => $data,
            'created_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Medición guardada correctamente (primera medición)',
            'data' => $measurement
        ], 201);
    }

    // =============================================
    // ✅ BUSCAR POSICIÓN EN LA SECUENCIA
    // =============================================
    
    $previousMeasurement = null;
    $nextMeasurement = null;
    $position = 'last'; // Por defecto, es la última

    foreach ($allMeasurements as $index => $measurement) {
        $measurementDate = Carbon::parse($measurement->measured_at);
        
        // Si la nueva fecha es igual a esta medición
        if ($currentDate->eq($measurementDate)) {
            return response()->json([
                'success' => false,
                'message' => "Ya existe una medición con la misma fecha ({$currentDate->format('d/m/Y H:i')}).",
                'code' => 'duplicate_date'
            ], 422);
        }
        
        // Si la nueva fecha es anterior a esta medición
        if ($currentDate->lt($measurementDate)) {
            $position = 'intermediate';
            $nextMeasurement = $measurement;
            if ($index > 0) {
                $previousMeasurement = $allMeasurements[$index - 1];
            }
            break;
        }
    }

    // Si no se encontró una fecha posterior, es la última
    if ($position === 'last') {
        $previousMeasurement = $allMeasurements->last();
        $nextMeasurement = null;
    }

    // =============================================
    // ✅ VALIDAR SEGÚN LA POSICIÓN
    // =============================================

    // Caso 1: Es la PRIMERA medición (no hay anterior)
    if ($position === 'intermediate' && $previousMeasurement === null) {
        // Es la primera medición pero con fecha anterior a la primera existente
        // Esto solo es válido si el valor es MENOR al siguiente
        if ($nextMeasurement) {
            $nextValue = $nextMeasurement->data[$mainField] ?? 0;
            if ($currentValue >= $nextValue) {
                return response()->json([
                    'success' => false,
                    'message' => "Siendo la primera medición (fecha anterior a todas), el valor ({$currentValue}) debe ser MENOR al siguiente ({$nextValue}).",
                    'code' => 'first_measurement_value_too_high'
                ], 422);
            }
        }
    }

    // Caso 2: Es la ÚLTIMA medición (fecha posterior a todas)
    if ($position === 'last' && $previousMeasurement) {
        $previousValue = $previousMeasurement->data[$mainField] ?? 0;
        
        // El valor debe ser MAYOR al anterior
        if ($currentValue <= $previousValue) {
            return response()->json([
                'success' => false,
                'message' => "Siendo la última medición (fecha posterior a todas), el valor ({$currentValue}) debe ser MAYOR al anterior ({$previousValue}).",
                'code' => 'last_measurement_value_too_low'
            ], 422);
        }
    }

    // Caso 3: Es una medición INTERMEDIA (entre dos fechas)
    if ($position === 'intermediate' && $previousMeasurement && $nextMeasurement) {
        $previousValue = $previousMeasurement->data[$mainField] ?? 0;
        $nextValue = $nextMeasurement->data[$mainField] ?? 0;
        
        // El valor debe estar ENTRE el anterior y el siguiente
        if ($currentValue <= $previousValue) {
            return response()->json([
                'success' => false,
                'message' => "Siendo una medición intermedia, el valor ({$currentValue}) debe ser MAYOR al anterior ({$previousValue}).",
                'code' => 'intermediate_value_too_low'
            ], 422);
        }
        
        if ($currentValue >= $nextValue) {
            return response()->json([
                'success' => false,
                'message' => "Siendo una medición intermedia, el valor ({$currentValue}) debe ser MENOR al siguiente ({$nextValue}).",
                'code' => 'intermediate_value_too_high'
            ], 422);
        }
    }

    // =============================================
    // ✅ CREAR LA MEDICIÓN
    // =============================================
    
    $measurement = Measurement::create([
        'sensor_id' => $sensor->id,
        'measured_at' => $request->measured_at,
        'data' => $data,
        'created_by' => $user->id,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Medición guardada correctamente',
        'data' => $measurement,
        'position' => $position,
        'previous' => $previousMeasurement ? $previousMeasurement->id : null,
        'next' => $nextMeasurement ? $nextMeasurement->id : null,
    ], 201);
}

/**
 * Verificar si el usuario puede acceder a un sensor
 */
private function canAccessSensor($user, $sensor)
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



}