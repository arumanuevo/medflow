<?php
namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Models\Measurement;
use App\Models\Sensor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\UserSetting;
use App\Services\Subscription\SubscriptionService;
use App\Services\MeasurementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class MeasurementController extends Controller
{
    protected $measurementService;

    public function __construct(MeasurementService $measurementService)
    {
        $this->measurementService = $measurementService;
    }
    /**
     * Listar todas las mediciones con filtrado y paginación.
     * Solo muestra mediciones de sensores a los que el usuario tiene acceso.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Validar parámetros de paginación
        $perPage = (int) ($request->per_page ?? 10);
        $page = (int) ($request->page ?? 1);

        // Obtener el campo y dirección de ordenamiento
        $sortField = $request->sort_field ?? 'measured_at';
        $sortDirection = $request->sort_direction ?? 'desc';

        // Validar que el campo de ordenamiento sea válido
        $validSortFields = ['id', 'sensor_id', 'sensor', 'measured_at', 'created_at', 'valor', 'identifier', 'group_name', 'sensor_name'];
        if (!in_array($sortField, $validSortFields) && !str_starts_with($sortField, 'extra:')) {
            $sortField = 'measured_at';
        }

        // Validar dirección de ordenamiento
        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        // Construir la consulta base usando el Service
        $query = $this->measurementService->buildMeasurementQuery(
            $user,
            $request->all(),
            $sortField,
            $sortDirection
        );

        // Calcular estadísticas de errores globalmente (con caché de 30s)
        $errorStats = $this->measurementService->calculateErrorStats($user);

        // Si hay filtro por estado/error, filtrar por IDs de forma ultrarrápida
        if ($request->filled('error_type')) {
            $matchingIds = $this->measurementService->getMeasurementIdsByErrorType($user, $request->error_type, $request->group_id);
            $query->whereIn('measurements.id', $matchingIds);
        }

        // Paginación directa en MySQL
        $measurements = $query->paginate($perPage);

        // Decorar únicamente las mediciones de la página actual (10 o 25 elementos)
        $measurementsWithData = $measurements->getCollection()->map(function ($measurement) {
            return $this->decorateMeasurementWithData($measurement);
        });

        $measurements->setCollection($measurementsWithData);

        // Extraer campos personalizados de los sensores del grupo si hay filtro activo por grupo
        $groupExtraFields = [];
        if ($request->filled('group_id')) {
            $sensorsInGroup = Sensor::where('group_id', $request->group_id)->get(['id', 'metadata']);
            $keysSet = [];
            foreach ($sensorsInGroup as $s) {
                $meta = is_array($s->metadata) ? $s->metadata : json_decode($s->metadata, true);
                if (!empty($meta) && is_array($meta)) {
                    foreach (array_keys($meta) as $k) {
                        $keysSet[$k] = true;
                    }
                }
            }
            $groupExtraFields = array_keys($keysSet);
        }

        // Extraer campos dinámicos globales para el selector avanzado
        $allExtraKeys = [];
        $sampleSensors = Sensor::whereHas('group', function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhereHas('sharedAccess', function ($q2) use ($user) {
                    $q2->where('shared_with', $user->id);
                });
        })->get(['metadata']);
        foreach ($sampleSensors as $s) {
            $meta = is_array($s->metadata) ? $s->metadata : json_decode($s->metadata, true);
            if (!empty($meta) && is_array($meta)) {
                foreach (array_keys($meta) as $k) {
                    $allExtraKeys[$k] = true;
                }
            }
        }
        $availableExtraFields = array_keys($allExtraKeys);
        sort($availableExtraFields);

        // Formatear la respuesta
        return response()->json([
            'success' => true,
            'message' => 'Mediciones obtenidas correctamente',
            'data' => $measurements->items(),
            'error_stats' => $errorStats,
            'group_extra_fields' => $groupExtraFields,
            'available_extra_fields' => $availableExtraFields,
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
     * Helper paramétrica extraída para enriquecer cada medición.
     */
    private function decorateMeasurementWithData($measurement, $allMeasurementsBySensor = null)
    {
        $sensor = $measurement->sensor;
        $mainField = $this->measurementService->getMainFieldFromSensor($sensor);

        // Buscar únicamente la medición previa para este sensor específico
        $previousMeasurement = Measurement::where('sensor_id', $measurement->sensor_id)
            ->where('measured_at', '<', $measurement->measured_at)
            ->orderBy('measured_at', 'desc')
            ->first();

        $data = $measurement->data ?? [];
        $valor = $data[$mainField] ?? ($data['valor'] ?? 0);
        $isReset = !empty($data['is_reset']);

        if ($isReset) {
            $measurement->consumption = 0;
            $measurement->days_between = $previousMeasurement ? max(1, Carbon::parse($measurement->measured_at)->diffInDays(Carbon::parse($previousMeasurement->measured_at))) : 0;
            $measurement->daily_average = 0;
            $measurement->cost = 0;
            $measurement->currency = null;
            $measurement->is_reset = true;
            $measurement->reset_type = $data['reset_type'] ?? 'simple';
            $measurement->error_type = 'valid';
        } elseif ($previousMeasurement) {
            $lastData = $previousMeasurement->data ?? [];
            $lastValue = $lastData[$mainField] ?? ($lastData['valor'] ?? 0);
            $consumption = $valor - $lastValue;
            $measurement->consumption = $consumption;

            $lastDate = Carbon::parse($previousMeasurement->measured_at);
            $currentDate = Carbon::parse($measurement->measured_at);

            $daysBetween = max(1, $currentDate->diffInDays($lastDate));
            $measurement->days_between = $daysBetween;
            $measurement->daily_average = ($consumption >= 0 && $daysBetween > 0) ? round($consumption / $daysBetween, 2) : 0;

            // Cálculo monetario según configuración tarifaria (billing_settings del grupo)
            $cost = null;
            $currency = null;
            if ($sensor && $sensor->group && !empty($sensor->group->billing_settings) && ($sensor->group->billing_settings['is_enabled'] ?? false)) {
                $billing = $sensor->group->billing_settings;
                $fixedCharge = (float) ($billing['fixed_charge'] ?? 0);
                $pricePerUnit = (float) ($billing['price_per_unit'] ?? 0);
                $cost = round($fixedCharge + (max(0, $consumption) * $pricePerUnit), 2);
                $currency = $billing['currency'] ?? 'ARS';
            }
            $measurement->cost = $cost;
            $measurement->currency = $currency;

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
            $measurement->days_between = 0;
            $measurement->daily_average = 0;
            $measurement->cost = null;
            $measurement->currency = null;
            $measurement->error_type = 'first_measurement';
        }

        $measurement->previous_measurement = $previousMeasurement;

        // ✅ AÑADIR CAMPOS EXTRA DEL SENSOR (dinámico desde metadata)
        $sensor = $measurement->sensor;
        if ($sensor) {
            $meta = is_array($sensor->metadata) ? $sensor->metadata : json_decode($sensor->metadata, true);
            // Enviar TODOS los campos extra dinámicamente desde metadata
            $measurement->sensor_extra_fields = array_filter(
                $meta ?? [],
                fn($v) => $v !== null && $v !== ''
            );

            // Campos explícitos para columnas dedicadas en la tabla
            $measurement->sensor_identifier = $sensor->identifier;
            $measurement->group_name = $sensor->group->name ?? null;
            $measurement->template_type = $sensor->group->template->type ?? null;
            $measurement->template_name = $sensor->group->template->name ?? null;
        }

        return $measurement;
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

        // ✅ VERIFICAR LÍMITE DE SUSCRIPCIÓN
        $subscriptionService = new SubscriptionService($user);
        if (!$subscriptionService->canMeasureSensor($sensor)) {
            $planName = $subscriptionService->getPlan()->getPlanName();
            $maxSensors = $subscriptionService->getPlan()->getMaxSensors();
            Log::warning('Usuario superó límite de sensores en su plan e intentó medir', [
                'user_id' => $user->id,
                'sensor_id' => $sensor->id,
                'plan' => $planName
            ]);
            return response()->json([
                'success' => false,
                'message' => "No puedes tomar mediciones en este sensor con tu plan {$planName}. Tu plan permite medir solo en tus primeros {$maxSensors} sensores."
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
        $currentIndex = $sensorMeasurements->search(function ($m) use ($measurement) {
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

        Cache::forget("user_{$user->id}_error_stats");

        return response()->json([
            'success' => true,
            'message' => 'Medición actualizada correctamente',
            'data' => $measurement
        ]);
    }
    /**
     * Actualiza la foto de una medición existente manualmente.
     */
    public function updatePhoto(Request $request, Measurement $measurement)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240'
        ]);

        $user = auth()->user();
        $sensor = $measurement->sensor;

        if (!$this->canUploadPhoto($user, $sensor)) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para actualizar esta medición.'], 403);
        }

        try {
            $file = $request->file('foto');
            $extension = $file->getClientOriginalExtension();
            $timestamp = now()->format('Ymd_His');
            $filename = 'measurement_' . $measurement->id . '_' . $timestamp . '.' . $extension;

            $path = public_path('measurements');
            if (!file_exists($path)) {
                mkdir($path, 0775, true);
            }

            $currentData = $measurement->data ?? [];

            // Remove old photo if exists
            if (isset($currentData['foto']) && $currentData['foto']) {
                $oldPath = public_path(ltrim($currentData['foto'], '/'));
                if (file_exists($oldPath) && is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $file->move($path, $filename);

            $currentData['foto'] = '/measurements/' . $filename;
            $measurement->data = $currentData;
            $measurement->save();

            return response()->json([
                'success' => true,
                'message' => 'Foto adjuntada correctamente',
                'photo_path' => $currentData['foto']
            ]);

        } catch (\Exception $e) {
            \Log::error('Error actualizando foto de medición: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al subir imagen: ' . $e->getMessage()
            ], 500);
        }
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

        Cache::forget("user_{$user->id}_error_stats");

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
        $currentIndex = $sensorMeasurements->search(function ($m) use ($measurement) {
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
        return $this->measurementService->calculateErrorStats($user);
    }

    /**
     * Obtener mediciones con errores de un tipo específico.
     *
     * @param Request $request
     * @param string $errorType
     * @param bool $returnArray Si es true, devuelve un array en lugar de JsonResponse
     * @return array|JsonResponse
     */
    /**
     * Obtener mediciones con un tipo de error específico (filtrado eficiente).
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

        // Obtener los IDs que coinciden exactamente con el tipo de error
        $matchingIds = $this->measurementService->getMeasurementIdsByErrorType(
            $user,
            $errorType,
            $request->input('group_id')
        );

        if (empty($matchingIds)) {
            $emptyResult = [
                'success' => true,
                'message' => 'No se encontraron mediciones con este tipo de error',
                'data' => collect(),
                'error_type' => $errorType,
                'count' => 0
            ];
            return $returnArray ? $emptyResult : response()->json($emptyResult);
        }

        $measurements = Measurement::with(['sensor', 'sensor.group', 'sensor.group.template'])
            ->whereIn('id', $matchingIds)
            ->orderBy('measured_at', 'desc')
            ->get();

        $measurementsWithData = $measurements->map(function ($measurement) {
            return $this->decorateMeasurementWithData($measurement);
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
     * Obtener detalles de un error específico para el modal de auditoría.
     * Garantiza rigurosamente el orden cronológico y la relación medición actual vs anterior.
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
            $matchingIds = $this->measurementService->getMeasurementIdsByErrorType(
                $user,
                $errorType,
                $request->input('group_id')
            );

            if (empty($matchingIds)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No se encontraron registros con este tipo de error',
                    'data' => [],
                    'error_type' => $errorType,
                    'count' => 0
                ]);
            }

            // Cargar mediciones erróneas ordenadas por sensor y fecha
            $measurements = Measurement::with(['sensor', 'sensor.group', 'sensor.group.template'])
                ->whereIn('id', $matchingIds)
                ->orderBy('sensor_id')
                ->orderBy('measured_at', 'asc')
                ->get();

            $errorDetails = [];
            $recordNumber = 1;

            foreach ($measurements as $current) {
                $sensor = $current->sensor;
                $mainField = $this->measurementService->getMainFieldFromSensor($sensor);
                $currData = $current->data ?? [];
                $currVal = $currData[$mainField] ?? ($currData['valor'] ?? 0);

                // Obtener cronológicamente la medición anterior inmediata de este sensor
                $previous = Measurement::where('sensor_id', $current->sensor_id)
                    ->where('measured_at', '<', $current->measured_at)
                    ->orderBy('measured_at', 'desc')
                    ->first();

                $prevData = $previous ? ($previous->data ?? []) : [];
                $prevVal = $previous ? ($prevData[$mainField] ?? ($prevData['valor'] ?? 0)) : null;

                // Obtener cronológicamente la medición siguiente inmediata (para análisis secuencial)
                $next = Measurement::where('sensor_id', $current->sensor_id)
                    ->where('measured_at', '>', $current->measured_at)
                    ->orderBy('measured_at', 'asc')
                    ->first();
                $nextData = $next ? ($next->data ?? []) : [];
                $nextVal = $next ? ($nextData[$mainField] ?? ($nextData['valor'] ?? null)) : null;

                $currPhoto = !empty($currData['foto']) && $currData['foto'] !== 'Sin Foto' && $currData['foto'] !== 'Expirada'
                    ? (str_starts_with($currData['foto'], '/') ? $currData['foto'] : '/' . $currData['foto'])
                    : null;
                $prevPhoto = !empty($prevData['foto']) && $prevData['foto'] !== 'Sin Foto' && $prevData['foto'] !== 'Expirada'
                    ? (str_starts_with($prevData['foto'], '/') ? $prevData['foto'] : '/' . $prevData['foto'])
                    : null;
                $nextPhoto = !empty($nextData['foto']) && $nextData['foto'] !== 'Sin Foto' && $nextData['foto'] !== 'Expirada'
                    ? (str_starts_with($nextData['foto'], '/') ? $nextData['foto'] : '/' . $nextData['foto'])
                    : null;

                $consumption = null;
                $difference = null;
                if ($previous) {
                    $consumption = (float) $currVal - (float) $prevVal;
                    $diffDays = Carbon::parse($current->measured_at)->diffInDays(Carbon::parse($previous->measured_at));
                    $difference = [
                        'value' => $consumption,
                        'days' => $diffDays
                    ];
                }

                $errorMessage = $this->measurementService->getErrorMessage($errorType, $current, $previous, $mainField);

                $detail = [
                    'sensor_id' => $current->sensor_id,
                    'sensor_name' => $sensor->name ?? 'Sensor #' . $current->sensor_id,
                    'sensor_identifier' => $sensor->identifier ?? 'N/A',
                    'group_name' => $sensor->group->name ?? 'Sin grupo',
                    'current_measurement' => [
                        'id' => $current->id,
                        'value' => $currVal,
                        'date' => $current->measured_at,
                        'consumption' => $consumption,
                        'is_reset' => !empty($currData['is_reset']),
                        'reset_type' => $currData['reset_type'] ?? null,
                        'photo' => $currPhoto
                    ],
                    'previous_measurement' => $previous ? [
                        'id' => $previous->id,
                        'value' => $prevVal,
                        'date' => $previous->measured_at,
                        'photo' => $prevPhoto
                    ] : null,
                    'next_measurement' => $next ? [
                        'id' => $next->id,
                        'value' => $nextVal,
                        'date' => $next->measured_at,
                        'photo' => $nextPhoto
                    ] : null,
                    'error_type' => $errorType,
                    'error_message' => $errorMessage,
                    'difference' => $difference,
                    'record_number' => $recordNumber++
                ];

                $errorDetails[] = $detail;
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
     * Marcar o desmarcar una medición como Cambio de Medidor (Reseteo de contador).
     * Esto normaliza el consumo evitando anomalías negativas en reemplazos de hardware.
     */
    public function markReset(Request $request, Measurement $measurement)
    {
        $user = $request->user();

        // Verificar permisos
        $canUpdate = $user->hasRole('admin') ||
            ($measurement->sensor->group && $measurement->sensor->group->user_id === $user->id) ||
            ($measurement->sensor->group && $measurement->sensor->group->sharedAccess()
                ->where('shared_with', $user->id)
                ->whereIn('role', ['inspector', 'admin'])
                ->exists());

        if (!$canUpdate) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para modificar esta medición.'
            ], 403);
        }

        $data = $measurement->data ?? [];
        $currentReset = !empty($data['is_reset']);
        $newReset = $request->has('is_reset') ? (bool) $request->input('is_reset') : !$currentReset;

        if ($newReset) {
            $data['is_reset'] = true;
            $data['reset_type'] = $request->input('reset_type', 'simple');
            $data['reset_notes'] = $request->input('notes', 'Marcado como cambio de medidor / reseteo');
            $message = 'Medición marcada como Cambio de Medidor (Reseteo) exitosamente.';
        } else {
            unset($data['is_reset'], $data['reset_type'], $data['reset_notes']);
            $message = 'Se quitó la marca de Cambio de Medidor de la medición.';
        }

        $measurement->data = $data;
        $measurement->save();

        Cache::forget("user_{$user->id}_error_stats");

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_reset' => $newReset,
            'data' => $measurement
        ]);
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
        $nextSensor = Sensor::with([
            'group',
            'lastMeasurement' => function ($q) {
                $q->orderBy('measured_at', 'desc')->limit(1);
            }
        ])
            ->where('marcado_para_medicion', true)
            ->whereHas('group', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('sharedAccess', function ($q2) use ($user) {
                        $q2->where('shared_with', $user->id)
                            ->whereIn('role', ['inspector', 'admin']);
                    });
            })
            ->when($currentSensorId, function ($q) use ($currentSensorId) {
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
            ->map(function ($m) {
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
        $month = $measuredAt->format('M');
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
        if (
            $sensor->group && $sensor->group->sharedAccess()
                ->where('shared_with', $user->id)
                ->whereIn('role', ['inspector', 'admin'])
                ->exists()
        ) {
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
        $canAccess = $this->measurementService->canAccessSensor($user, $sensor);
        if (!$canAccess) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para acceder a este sensor'
            ], 403);

            // Verificar limites de suscripcion para toma de mediciones
            $subscriptionService = new SubscriptionService($user);
            $plan = $subscriptionService->getPlan();

            // Verificar si el sensor pertenece al usuario y si el plan permite tomar mediciones
            if ($sensor->group && $sensor->group->user_id === $user->id) {
                // Si el sensor es del usuario, verificar que el plan permita crear mediciones
                // Para planes Free y Basico, verificar que no se exceda el limite de sensores activos
                $currentSensorCount = $subscriptionService->getCurrentSensorCount();
                $maxSensors = $plan->getMaxSensors();

                if ($currentSensorCount > $maxSensors) {
                    return response()->json([
                        'success' => false,
                        'message' => "Tu plan {$plan->getPlanName()} permite solo {$maxSensors} sensor(es). " .
                            "Tienes {$currentSensorCount} sensores. " .
                            "Elimina algunos sensores o actualiza tu plan para tomar mediciones.",
                        'code' => 'sensor_limit_exceeded_for_measurements',
                        'data' => [
                            'current_sensors' => $currentSensorCount,
                            'max_sensors' => $maxSensors,
                            'plan' => $plan->getPlanKey()
                        ]
                    ], 403);
                }
            }
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

        // ✅ INYECTAR 'tipo' DESDE LA PLANTILLA PARA EVITAR EL 'N/A'
        $templateType = $sensor->group->template->type ?? 'personalizado';
        $data['tipo'] = $templateType;

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
     * Eliminación masiva de mediciones
     */
    public function bulkDelete(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'measurement_ids' => 'required|array',
            'measurement_ids.*' => 'exists:measurements,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $measurementIds = $request->input('measurement_ids');
        $measurements = Measurement::with('sensor.group')->whereIn('id', $measurementIds)->get();

        $deletedCount = 0;
        $unauthorizedCount = 0;

        foreach ($measurements as $measurement) {
            /** @var \App\Models\Measurement $measurement */
            $sensor = $measurement->sensor;

            // Verificar permisos (mismo método que canAccessSensor pero aquí es para eliminar)
            $canDelete = $user->hasRole('admin') ||
                ($sensor && $sensor->group && $sensor->group->user_id === $user->id);

            if (!$canDelete && $sensor) {
                $canDelete = $this->measurementService->canAccessSensor($user, $sensor);
            }

            if ($canDelete) {
                $measurement->delete();
                $deletedCount++;
            } else {
                $unauthorizedCount++;
            }
        }

        if ($deletedCount > 0) {
            Cache::forget("user_{$user->id}_error_stats");
        }

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deletedCount} mediciones correctamente.",
            'deleted_count' => $deletedCount,
            'unauthorized_count' => $unauthorizedCount
        ]);
    }

    /**
     * Exportar mediciones y consumos filtrados o seleccionados a Excel / CSV.
     */
    public function export(Request $request)
    {
        @set_time_limit(300);
        $user = $request->user();

        // 1. Construir la consulta base con filtros
        $filters = $request->all();
        $sortField = $request->input('sort_field', 'measured_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query = $this->measurementService->buildMeasurementQuery($user, $filters, $sortField, $sortDirection);

        // 1b. Si se envían IDs específicos (exportar selección manual)
        if ($request->filled('ids')) {
            $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
            $ids = array_filter(array_map('trim', $ids));
            if (!empty($ids)) {
                $query->whereIn('measurements.id', $ids);
            }
        }

        // 2. Si hay filtro por tipo de error, aplicar el conjunto de IDs
        $errorType = $request->input('error_type');
        if ($errorType) {
            $matchingIds = $this->measurementService->getMeasurementIdsByErrorType(
                $user,
                $errorType,
                $request->input('group_id')
            );
            $query->whereIn('measurements.id', $matchingIds);
        }

        // 3. Cargar mediciones con relaciones requeridas
        $measurements = $query->with([
            'sensor',
            'sensor.group',
            'sensor.group.template',
            'sensor.group.user'
        ])->get();

        $headers = [
            'ID',
            'Sensor',
            'Identificador',
            'Grupo',
            'Plantilla',
            'Fecha Medición',
            'Lectura (Valor)',
            'Unidad',
            'Lectura Anterior',
            'Consumo Calculado',
            'Días Transcurridos',
            'Promedio Diario',
            'Costo ($)',
            'Moneda',
            'Estado / Aviso',
            'Foto',
            'Campos Personalizados / Observaciones'
        ];

        // 4. Si PhpSpreadsheet está disponible en el servidor, generar .xlsx
        if (class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Mediciones y Consumos');

            $colIdx = 1;
            foreach ($headers as $h) {
                $sheet->setCellValueByColumnAndRow($colIdx++, 1, $h);
            }

            $row = 2;
            foreach ($measurements as $m) {
                $this->decorateMeasurementWithData($m);
                $sensor = $m->sensor;
                $data = $m->data ?? [];
                $mainField = $this->measurementService->getMainFieldFromSensor($sensor);
                $val = $data[$mainField] ?? ($data['valor'] ?? 'N/A');

                $unit = 'unidades';
                if ($sensor && $sensor->group && $sensor->group->template) {
                    foreach ($sensor->group->template->schema['campos'] ?? [] as $c) {
                        if (($c['tipo'] ?? '') === 'numero' && ($c['requerido'] ?? false)) {
                            $unit = $c['unidad'] ?? 'unidades';
                            break;
                        }
                    }
                }

                $extraDataStr = [];
                foreach ($data as $k => $v) {
                    if ($k !== $mainField && $k !== 'valor' && $k !== 'foto' && $k !== 'foto_evidencia') {
                        $extraDataStr[] = "$k: $v";
                    }
                }
                if ($sensor && is_array($sensor->metadata)) {
                    foreach ($sensor->metadata as $mk => $mv) {
                        if ($mv !== null && $mv !== '') {
                            $extraDataStr[] = "[$mk: $mv]";
                        }
                    }
                }

                $lastVal = '—';
                if ($m->previous_measurement) {
                    $pData = $m->previous_measurement->data ?? [];
                    $lastVal = $pData[$mainField] ?? ($pData['valor'] ?? '—');
                }

                $sheet->setCellValue('A' . $row, $m->id);
                $sheet->setCellValue('B' . $row, $sensor->name ?? 'N/A');
                $sheet->setCellValue('C' . $row, $sensor->identifier ?? '—');
                $sheet->setCellValue('D' . $row, $sensor->group->name ?? 'Sin grupo');
                $sheet->setCellValue('E' . $row, $sensor->group->template->name ?? ($sensor->group->template->type ?? 'N/A'));
                $sheet->setCellValue('F' . $row, $m->measured_at ? Carbon::parse($m->measured_at)->format('d/m/Y H:i') : 'N/A');
                $sheet->setCellValue('G' . $row, $val);
                $sheet->setCellValue('H' . $row, $unit);
                $sheet->setCellValue('I' . $row, $lastVal);
                $sheet->setCellValue('J' . $row, $m->consumption ?? 0);
                $sheet->setCellValue('K' . $row, $m->days_between ?? 0);
                $sheet->setCellValue('L' . $row, $m->daily_average ?? 0);
                $sheet->setCellValue('M' . $row, $m->cost !== null ? $m->cost : '—');
                $sheet->setCellValue('N' . $row, $m->currency ?? '—');
                $sheet->setCellValue('O' . $row, $m->error_type ?? 'valid');
                $sheet->setCellValue('P' . $row, $data['foto'] ?? 'Sin Foto');
                $sheet->setCellValue('Q' . $row, implode(' | ', $extraDataStr));

                $row++;
            }

            $sheet->getStyle('A1:Q1')->getFont()->setBold(true);
            $sheet->getStyle('A1:Q1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE9ECEF');

            foreach (range('A', 'Q') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'mediciones_consumos_' . date('Ymd_His') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;
        }

        // 5. Fallback nativo CSV con UTF-8 BOM para Excel (compatible con cualquier servidor compartido)
        $filename = 'mediciones_consumos_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $out = fopen('php://output', 'w');
        // Escribir UTF-8 BOM para que Microsoft Excel en Windows abra tildes automáticamente sin asistentes
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers, ';');

        foreach ($measurements as $m) {
            $this->decorateMeasurementWithData($m);
            $sensor = $m->sensor;
            $data = $m->data ?? [];
            $mainField = $this->measurementService->getMainFieldFromSensor($sensor);
            $val = $data[$mainField] ?? ($data['valor'] ?? 'N/A');

            $unit = 'unidades';
            if ($sensor && $sensor->group && $sensor->group->template) {
                foreach ($sensor->group->template->schema['campos'] ?? [] as $c) {
                    if (($c['tipo'] ?? '') === 'numero' && ($c['requerido'] ?? false)) {
                        $unit = $c['unidad'] ?? 'unidades';
                        break;
                    }
                }
            }

            $extraDataStr = [];
            foreach ($data as $k => $v) {
                if ($k !== $mainField && $k !== 'valor' && $k !== 'foto' && $k !== 'foto_evidencia') {
                    $extraDataStr[] = "$k: $v";
                }
            }
            if ($sensor && is_array($sensor->metadata)) {
                foreach ($sensor->metadata as $mk => $mv) {
                    if ($mv !== null && $mv !== '') {
                        $extraDataStr[] = "[$mk: $mv]";
                    }
                }
            }

            $lastVal = '—';
            if ($m->previous_measurement) {
                $pData = $m->previous_measurement->data ?? [];
                $lastVal = $pData[$mainField] ?? ($pData['valor'] ?? '—');
            }

            $csvRow = [
                $m->id,
                $sensor->name ?? 'N/A',
                $sensor->identifier ?? '—',
                $sensor->group->name ?? 'Sin grupo',
                $sensor->group->template->name ?? ($sensor->group->template->type ?? 'N/A'),
                $m->measured_at ? Carbon::parse($m->measured_at)->format('d/m/Y H:i') : 'N/A',
                $val,
                $unit,
                $lastVal,
                $m->consumption ?? 0,
                $m->days_between ?? 0,
                $m->daily_average ?? 0,
                $m->cost !== null ? $m->cost : '—',
                $m->currency ?? '—',
                $m->error_type ?? 'valid',
                $data['foto'] ?? 'Sin Foto',
                implode(' | ', $extraDataStr)
            ];

            fputcsv($out, $csvRow, ';');
        }

        fclose($out);
        exit;
    }

    /**
     * Subida masiva de fotos con verificación estricta de correlación contra la BD
     * y aplicación estricta de la regla de caducidad (máximo 365 días).
     */
    public function bulkUploadPhotos(Request $request)
    {
        @set_time_limit(300);
        $user = auth()->user();

        // 1. Obtener grupos del usuario y de workspaces colaborativos
        $userGroups = DB::table('sensor_groups')
            ->where('user_id', $user->id)
            ->pluck('id');

        $collabWorkspaceIds = DB::table('workspace_collaborators')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('workspace_id');

        if ($collabWorkspaceIds->isNotEmpty()) {
            $collabGroups = DB::table('sensor_groups')->whereIn('user_id', $collabWorkspaceIds)->pluck('id');
            $userGroups = $userGroups->merge($collabGroups)->unique();
        }

        // 2. Pre-cargar el índice de fotos de las mediciones de estos grupos
        $oneYearAgo = Carbon::now()->subDays(365)->startOfDay();

        $measurements = DB::table('measurements')
            ->join('sensors', 'measurements.sensor_id', '=', 'sensors.id')
            ->leftJoin('sensor_groups', 'sensors.group_id', '=', 'sensor_groups.id')
            ->whereIn('sensors.group_id', $userGroups)
            ->select(
                'measurements.id',
                'measurements.measured_at',
                'measurements.data',
                'sensors.name as sensor_name',
                'sensors.identifier as sensor_identifier',
                'sensor_groups.name as group_name'
            )
            ->get();

        $validPhotoTargets = [];   // [ normalized_key => [measurement_id, original_foto_target, ...] ]
        $expiredPhotoTargets = []; // [ normalized_key => [measurement_id, ...] ]

        foreach ($measurements as $m) {
            $data = is_array($m->data) ? $m->data : json_decode($m->data, true);
            $foto = $data['foto'] ?? null;
            if (!$foto || in_array(strtolower(trim($foto)), ['sin foto', 'sin_foto', 'n/a', 'na', 'null', 'none', '-', 'no', ''])) {
                continue;
            }

            $rawBase = basename(str_replace('\\', '/', trim($foto)));
            $cleanBaseLower = strtolower($rawBase);
            $nameWithoutExt = strtolower(pathinfo($rawBase, PATHINFO_FILENAME));

            $isExpired = Carbon::parse($m->measured_at)->lt($oneYearAgo);

            $targetInfo = [
                'id' => $m->id,
                'raw_base' => $rawBase,
                'measured_at' => $m->measured_at,
                'sensor_name' => $m->sensor_name ?? 'N/A',
                'sensor_identifier' => $m->sensor_identifier ?? 'N/A',
                'group_name' => $m->group_name ?? 'N/A',
            ];

            if ($isExpired) {
                $expiredPhotoTargets[$cleanBaseLower] = $targetInfo;
                $expiredPhotoTargets[$nameWithoutExt] = $targetInfo;
            } else {
                $validPhotoTargets[$cleanBaseLower] = $targetInfo;
                $validPhotoTargets[$nameWithoutExt] = $targetInfo;
            }
        }

        $destDir = public_path('measurements');
        if (!file_exists($destDir)) {
            @mkdir($destDir, 0775, true);
        }

        $totalEvaluated = 0;
        $linkedCount = 0;
        $rejectedOlderThanYear = 0;
        $skippedNoMatch = 0;
        $rejectedInvalidFormat = 0;
        $updatedMeasurementIds = [];
        $processingItems = [];

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        // ========================================================
        // CASO A: Archivo ZIP
        // ========================================================
        if ($request->hasFile('zip_file')) {
            $zipFile = $request->file('zip_file');
            $zip = new \ZipArchive();
            $openRes = $zip->open($zipFile->getRealPath());

            if ($openRes !== true) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo abrir el archivo ZIP. Verifique que sea un archivo comprimido válido.'
                ], 422);
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                if (str_ends_with($entryName, '/') || str_ends_with($entryName, '\\') || str_contains($entryName, '__MACOSX')) {
                    continue;
                }

                $cleanBase = basename(str_replace('\\', '/', $entryName));
                if (str_starts_with($cleanBase, '.') || empty($cleanBase)) {
                    continue;
                }

                $totalEvaluated++;
                $ext = strtolower(pathinfo($cleanBase, PATHINFO_EXTENSION));

                if (!in_array($ext, $allowedExtensions)) {
                    $rejectedInvalidFormat++;
                    $processingItems[] = [
                        'file' => $cleanBase,
                        'status' => 'invalid_format',
                        'status_label' => 'Formato no permitido',
                        'reason' => "Extensión (.{$ext}) no permitida. Solo se admiten JPG, JPEG, PNG y WEBP.",
                        'sensor' => '-',
                        'identifier' => '-',
                        'group' => '-',
                        'date' => '-',
                        'measurement_id' => '-'
                    ];
                    continue;
                }

                $cleanBaseLower = strtolower($cleanBase);
                $nameWithoutExt = strtolower(pathinfo($cleanBase, PATHINFO_FILENAME));

                if (isset($expiredPhotoTargets[$cleanBaseLower]) || isset($expiredPhotoTargets[$nameWithoutExt])) {
                    $rejectedOlderThanYear++;
                    $exp = $expiredPhotoTargets[$cleanBaseLower] ?? $expiredPhotoTargets[$nameWithoutExt] ?? null;
                    $expDate = ($exp && !empty($exp['measured_at'])) ? Carbon::parse($exp['measured_at'])->format('d/m/Y') : '> 365 días';
                    $processingItems[] = [
                        'file' => $cleanBase,
                        'status' => 'expired',
                        'status_label' => 'Rechazada (> 1 año)',
                        'reason' => "Toma del {$expDate} supera los 365 días de antigüedad máxima permitida.",
                        'sensor' => $exp['sensor_name'] ?? '-',
                        'identifier' => $exp['sensor_identifier'] ?? '-',
                        'group' => $exp['group_name'] ?? '-',
                        'date' => $expDate,
                        'measurement_id' => $exp['id'] ?? '-'
                    ];
                    continue;
                }

                $target = $validPhotoTargets[$cleanBaseLower] ?? $validPhotoTargets[$nameWithoutExt] ?? null;
                if (!$target) {
                    $skippedNoMatch++;
                    $processingItems[] = [
                        'file' => $cleanBase,
                        'status' => 'no_match',
                        'status_label' => 'Sin coincidencia (BD)',
                        'reason' => "No existe ninguna medición en tus grupos que requiera este archivo.",
                        'sensor' => '-',
                        'identifier' => '-',
                        'group' => '-',
                        'date' => '-',
                        'measurement_id' => '-'
                    ];
                    continue;
                }

                $destPath = $destDir . DIRECTORY_SEPARATOR . $cleanBase;
                $stream = $zip->getStream($entryName);
                if ($stream) {
                    $out = fopen($destPath, 'wb');
                    stream_copy_to_stream($stream, $out);
                    fclose($out);
                    fclose($stream);

                    $measurementId = $target['id'];
                    $photoRelative = '/measurements/' . $cleanBase;

                    DB::table('measurements')
                        ->where('id', $measurementId)
                        ->update([
                            'data' => DB::raw("JSON_SET(data, '$.foto', " . DB::getPdo()->quote($photoRelative) . ")")
                        ]);

                    $updatedMeasurementIds[] = $measurementId;
                    $linkedCount++;

                    $mDate = Carbon::parse($target['measured_at'])->format('d/m/Y');
                    $processingItems[] = [
                        'file' => $cleanBase,
                        'status' => 'linked',
                        'status_label' => 'Vinculada con éxito',
                        'reason' => "Guardada y vinculada a la medición #{$measurementId}.",
                        'sensor' => $target['sensor_name'] ?? '-',
                        'identifier' => $target['sensor_identifier'] ?? '-',
                        'group' => $target['group_name'] ?? '-',
                        'date' => $mDate,
                        'measurement_id' => $measurementId
                    ];
                }
            }

            $zip->close();
        }

        // ========================================================
        // CASO B: Lote de fotos individuales (Multi-file)
        // ========================================================
        if ($request->hasFile('photos')) {
            $photos = $request->file('photos');
            foreach ($photos as $photo) {
                if (!$photo->isValid()) {
                    $rejectedInvalidFormat++;
                    continue;
                }

                $cleanBase = basename($photo->getClientOriginalName());
                $ext = strtolower($photo->getClientOriginalExtension());
                $totalEvaluated++;

                if (!in_array($ext, $allowedExtensions)) {
                    $rejectedInvalidFormat++;
                    $processingItems[] = [
                        'file' => $cleanBase,
                        'status' => 'invalid_format',
                        'status_label' => 'Formato no permitido',
                        'reason' => "Extensión (.{$ext}) no permitida. Solo se admiten JPG, JPEG, PNG y WEBP.",
                        'sensor' => '-',
                        'identifier' => '-',
                        'group' => '-',
                        'date' => '-',
                        'measurement_id' => '-'
                    ];
                    continue;
                }

                $cleanBaseLower = strtolower($cleanBase);
                $nameWithoutExt = strtolower(pathinfo($cleanBase, PATHINFO_FILENAME));

                if (isset($expiredPhotoTargets[$cleanBaseLower]) || isset($expiredPhotoTargets[$nameWithoutExt])) {
                    $rejectedOlderThanYear++;
                    $exp = $expiredPhotoTargets[$cleanBaseLower] ?? $expiredPhotoTargets[$nameWithoutExt] ?? null;
                    $expDate = ($exp && !empty($exp['measured_at'])) ? Carbon::parse($exp['measured_at'])->format('d/m/Y') : '> 365 días';
                    $processingItems[] = [
                        'file' => $cleanBase,
                        'status' => 'expired',
                        'status_label' => 'Rechazada (> 1 año)',
                        'reason' => "Toma del {$expDate} supera los 365 días de antigüedad máxima permitida.",
                        'sensor' => $exp['sensor_name'] ?? '-',
                        'identifier' => $exp['sensor_identifier'] ?? '-',
                        'group' => $exp['group_name'] ?? '-',
                        'date' => $expDate,
                        'measurement_id' => $exp['id'] ?? '-'
                    ];
                    continue;
                }

                $target = $validPhotoTargets[$cleanBaseLower] ?? $validPhotoTargets[$nameWithoutExt] ?? null;
                if (!$target) {
                    $skippedNoMatch++;
                    $processingItems[] = [
                        'file' => $cleanBase,
                        'status' => 'no_match',
                        'status_label' => 'Sin coincidencia (BD)',
                        'reason' => "No existe ninguna medición en tus grupos que requiera este archivo.",
                        'sensor' => '-',
                        'identifier' => '-',
                        'group' => '-',
                        'date' => '-',
                        'measurement_id' => '-'
                    ];
                    continue;
                }

                $photo->move($destDir, $cleanBase);
                $measurementId = $target['id'];
                $photoRelative = '/measurements/' . $cleanBase;

                DB::table('measurements')
                    ->where('id', $measurementId)
                    ->update([
                        'data' => DB::raw("JSON_SET(data, '$.foto', " . DB::getPdo()->quote($photoRelative) . ")")
                    ]);

                $updatedMeasurementIds[] = $measurementId;
                $linkedCount++;

                $mDate = Carbon::parse($target['measured_at'])->format('d/m/Y');
                $processingItems[] = [
                    'file' => $cleanBase,
                    'status' => 'linked',
                    'status_label' => 'Vinculada con éxito',
                    'reason' => "Guardada y vinculada a la medición #{$measurementId}.",
                    'sensor' => $target['sensor_name'] ?? '-',
                    'identifier' => $target['sensor_identifier'] ?? '-',
                    'group' => $target['group_name'] ?? '-',
                    'date' => $mDate,
                    'measurement_id' => $measurementId
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Procesamiento masivo de fotos completado con éxito.',
            'summary' => [
                'total_evaluated' => $totalEvaluated,
                'linked_count' => $linkedCount,
                'rejected_older_than_year' => $rejectedOlderThanYear,
                'skipped_no_match' => $skippedNoMatch,
                'rejected_invalid_format' => $rejectedInvalidFormat,
                'items' => $processingItems,
            ]
        ]);
    }

}