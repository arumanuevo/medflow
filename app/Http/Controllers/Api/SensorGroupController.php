<?php
// app/Http/Controllers/Api/SensorGroupController.php

namespace App\Http\Controllers\Api;

use App\Models\SensorGroup;
use App\Models\Template;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\UserSetting;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Support\Facades\Log;

class SensorGroupController extends Controller
{
    /**
     * Listar todos los grupos de sensores del usuario.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $groups = SensorGroup::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereHas('sharedAccess', function ($q) use ($user) {
                    $q->where('shared_with', $user->id);
                });
        })->with(['user', 'template'])->withCount('sensors')->get();

        return response()->json([
            'success' => true,
            'message' => 'Grupos de sensores obtenidos correctamente',
            'data' => $groups,
        ]);
    }

    /**
     * Crear un nuevo grupo de sensores CON VERIFICACIÓN DE LÍMITES
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_id' => 'nullable|exists:templates,id',
            'periodo_medicion' => 'nullable|integer|min:1',
            'dias_vencimiento' => 'nullable|integer|min:1',
            'billing_settings' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // ✅ VERIFICAR LÍMITE DE SUSCRIPCIÓN PARA GRUPOS
        $subscriptionService = new SubscriptionService($user);

        if (!$subscriptionService->canCreateGroup()) {
            $status = $subscriptionService->getLimitStatus()['groups'];
            $planName = $subscriptionService->getPlan()->getPlanName();

            return response()->json([
                'success' => false,
                'message' => "Has alcanzado el límite de {$status['max']} grupos para tu plan {$planName}. " .
                    "Elimina algunos grupos o actualiza tu plan para crear más.",
                'code' => 'group_limit_exceeded',
                'data' => [
                    'limit_status' => $status,
                    'plan' => $planName
                ]
            ], 403);
        }

        $validatedData = $validator->validated();

        if (!isset($validatedData['periodo_medicion'])) {
            $validatedData['periodo_medicion'] = UserSetting::get($user->id, 'default_measurement_period', 30);
        }

        if (!isset($validatedData['dias_vencimiento'])) {
            $validatedData['dias_vencimiento'] = UserSetting::get($user->id, 'default_expiry_days', 5);
        }

        $validatedData['user_id'] = $user->id;

        $group = SensorGroup::create($validatedData);

        Log::info('✅ Grupo creado', [
            'user_id' => $user->id,
            'group_id' => $group->id,
            'group_name' => $group->name
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Grupo de sensores creado correctamente',
            'data' => $group
        ], 201);
    }

    /**
     * Mostrar un grupo de sensores específico.
     */
    public function show($id)
    {
        $user = request()->user();

        $group = SensorGroup::with(['template', 'sensors', 'user'])
            ->find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Grupo no encontrado',
            ], 404);
        }

        $group->sensors_count = $group->sensors()->count();

        return response()->json([
            'success' => true,
            'message' => 'Grupo obtenido correctamente',
            'data' => $group,
        ]);
    }

    /**
     * Actualizar un grupo de sensores.
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $group = SensorGroup::findOrFail($id);

        $canUpdate = $user->hasRole('admin') || $group->user_id === $user->id;

        if (!$canUpdate) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para actualizar este grupo'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'periodo_medicion' => 'nullable|integer|min:1',
            'dias_vencimiento' => 'nullable|integer|min:1',
            'billing_settings' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();

        if (!isset($validatedData['periodo_medicion'])) {
            $validatedData['periodo_medicion'] = $group->periodo_medicion;
        }

        if (!isset($validatedData['dias_vencimiento'])) {
            $validatedData['dias_vencimiento'] = $group->dias_vencimiento;
        }

        $group->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Grupo de sensores actualizado correctamente',
            'data' => $group
        ]);
    }

    /**
     * Eliminar un grupo de sensores.
     */
    public function destroy($id)
    {
        $user = request()->user();
        $group = SensorGroup::findOrFail($id);

        \Log::info('Intento de eliminar grupo', [
            'user_id' => $user->id,
            'group_id' => $group->id,
            'group_user_id' => $group->user_id,
            'is_admin' => $user->hasRole('admin'),
            'is_owner' => $group->user_id === $user->id
        ]);

        $canDelete = $user->hasRole('admin') || $group->user_id === $user->id;

        if (!$canDelete) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar este grupo.',
            ], 403);
        }

        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Grupo de sensores eliminado correctamente',
        ]);
    }

    /**
     * Listar plantillas disponibles para asignar a grupos.
     */
    public function getTemplates(Request $request)
    {
        $templates = Template::all();

        return response()->json([
            'success' => true,
            'message' => 'Plantillas obtenidas correctamente',
            'data' => $templates,
        ]);
    }
}