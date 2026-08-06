<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Obtener el perfil del usuario autenticado
     */
    // En ProfileController.php - MODIFICAR show() para incluir plan actual
    public function show(Request $request)
    {
        $user = $request->user();

        // ✅ Verificar si hay una suscripción activa REAL
        $activeSubscription = \App\Models\Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->first();

        // ✅ Determinar el plan REAL
        if ($activeSubscription) {
            // Si hay suscripción activa, usar el plan de la suscripción
            $plan = $activeSubscription->plan;
            $subscriptionType = $plan === 'premium' ? 'corporativo' : 'domiciliario';
        } else {
            // ✅ SIN suscripción activa → PLAN FREE
            $plan = 'free';
            $subscriptionType = 'domiciliario';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'subscription_type' => $subscriptionType,
                'subscription_plan' => $plan, // ✅ Plan REAL (free si no hay suscripción)
                'google_id' => $user->google_id,
                'roles' => $user->getRoleNames(),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Actualizar el perfil del usuario autenticado
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'subscription_type' => 'sometimes|in:domiciliario,corporativo',
            'password' => 'nullable|string|min:8|confirmed', // ✅ Solo valida si se envía
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = [];

        if ($request->has('name')) {
            $data['name'] = $request->name;
        }

        if ($request->has('email')) {
            $data['email'] = $request->email;
        }

        if ($request->has('subscription_type')) {
            $data['subscription_type'] = $request->subscription_type;
            $data['subscription_plan'] = $request->subscription_type === 'domiciliario' ? 'básico' : 'premium';
        }

        // ✅ Solo actualizar password si se envía y no está vacío
        if ($request->filled('password')) {
            // ✅ Verificar que password_confirmation existe y coincide
            if (!$request->has('password_confirmation') || $request->password !== $request->password_confirmation) {
                return response()->json([
                    'success' => false,
                    'message' => 'La confirmación de la contraseña no coincide.',
                    'errors' => ['password' => ['La confirmación de la contraseña no coincide.']]
                ], 422);
            }
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'subscription_type' => $user->subscription_type,
                'subscription_plan' => $user->subscription_plan,
                'roles' => $user->getRoleNames(),
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Cambiar el tipo de suscripción del usuario (endpoint específico)
     */
    // En ProfileController.php - MODIFICAR el método updateSubscription
    public function updateSubscription(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'subscription_type' => 'required|in:domiciliario,corporativo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Actualizar el tipo de suscripción
        $user->subscription_type = $request->subscription_type;
        $user->subscription_plan = $request->subscription_type === 'domiciliario' ? 'básico' : 'premium';
        $user->save();

        // Actualizar rol según el tipo de suscripción
        if ($request->subscription_type === 'corporativo') {
            $user->syncRoles(['inspector']);
        } else {
            $user->syncRoles(['consumidor']);
        }

        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Tipo de suscripción actualizado correctamente',
            'data' => [
                'subscription_type' => $user->subscription_type,
                'subscription_plan' => $user->subscription_plan,
                'roles' => $user->getRoleNames(),
            ]
        ]);
    }

    /**
     * Obtener estadísticas del usuario (sensores, mediciones, etc.)
     */
    public function getStats(Request $request)
    {
        $user = $request->user();

        // Contar sensores del usuario
        $totalSensors = \App\Models\Sensor::whereHas('group', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhereHas('sharedAccess', function($q) use ($user) {
                      $q->where('shared_with', $user->id);
                  });
        })->count();

        // Contar mediciones del usuario
        $totalMeasurements = \App\Models\Measurement::whereHas('sensor.group', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhereHas('sharedAccess', function($q) use ($user) {
                      $q->where('shared_with', $user->id);
                  });
        })->count();

        // Contar grupos del usuario
        $totalGroups = \App\Models\SensorGroup::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhereHas('sharedAccess', function($q) use ($user) {
                      $q->where('shared_with', $user->id);
                  });
        })->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_sensors' => $totalSensors,
                'total_measurements' => $totalMeasurements,
                'total_groups' => $totalGroups,
                'subscription_type' => $user->subscription_type,
                'subscription_plan' => $user->subscription_plan,
            ]
        ]);
    }

    /**
     * Eliminar todos los datos del usuario (sensores, mediciones, fotos, grupos, etc.)
     * Este es un endpoint DANGEROUS que elimina todo rastro de datos del usuario
     */
    public function deleteAllData(Request $request)
    {
        $user = $request->user();

        // Validar que el usuario ha confirmado la accion con un token de seguridad
        $validator = Validator::make($request->all(), [
            'confirm_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Se requiere confirmacion para esta accion',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que el token de confirmacion es valido
        // Generamos un token basado en el ID del usuario y la fecha actual
        $expectedToken = hash('sha256', $user->id . $user->email . date('Y-m-d'));
        
        if ($request->confirm_token !== $expectedToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token de confirmacion invalido. Por favor, recarga la pagina e intentalo de nuevo.',
            ], 403);
        }

        // Usar transaccion para asegurar que todo se elimina o nada
        \DB::beginTransaction();

        try {
            // 1. Eliminar todas las fotos asociadas a mediciones del usuario
            $measurements = \App\Models\Measurement::whereHas('sensor.group', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->get();

            foreach ($measurements as $measurement) {
                $data = $measurement->data;
                if (isset($data['foto']) && $data['foto'] && $data['foto'] !== 'Sin Foto') {
                    $photoPath = public_path($data['foto']);
                    if (file_exists($photoPath)) {
                        unlink($photoPath);
                    }
                }
            }

            // 2. Eliminar todas las mediciones de los sensores del usuario
            \App\Models\Measurement::whereHas('sensor.group', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->delete();

            // 3. Eliminar todos los sensores del usuario
            $sensors = \App\Models\Sensor::whereHas('group', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->get();
            
            foreach ($sensors as $sensor) {
                // Eliminar fotos de los sensores si existen
                if ($sensor->metadata && isset($sensor->metadata['photo'])) {
                    $photoPath = public_path($sensor->metadata['photo']);
                    if (file_exists($photoPath)) {
                        unlink($photoPath);
                    }
                }
            }
            
            \App\Models\Sensor::whereHas('group', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->delete();

            // 4. Eliminar todos los grupos de sensores del usuario
            $groups = \App\Models\SensorGroup::where('user_id', $user->id)->get();
            
            foreach ($groups as $group) {
                // Eliminar accesos compartidos del grupo
                \App\Models\SensorGroupSharedAccess::where('sensor_group_id', $group->id)->delete();
                // Eliminar invitaciones pendientes del grupo
                \App\Models\PendingInvitation::where('sensor_group_id', $group->id)->delete();
            }
            
            \App\Models\SensorGroup::where('user_id', $user->id)->delete();

            // 5. Eliminar accesos compartidos donde el usuario es el dueno
            \App\Models\SensorSharedAccess::where('user_id', $user->id)->delete();

            // 6. Eliminar colaboraciones donde el usuario es el dueno
            \App\Models\WorkspaceCollaborator::where('workspace_id', $user->id)->delete();

            // 7. Eliminar invitaciones pendientes donde el usuario es el dueno
            \App\Models\PendingInvitation::where('user_id', $user->id)->delete();

            // 8. Eliminar suscripciones del usuario
            \App\Models\Subscription::where('user_id', $user->id)->delete();

            // 9. Eliminar configuraciones del usuario
            \App\Models\UserSetting::where('user_id', $user->id)->delete();

            // 10. Eliminar tokens de API del usuario
            $user->tokens()->delete();

            // 11. Eliminar roles del usuario
            $user->syncRoles([]);

            // 12. Actualizar el usuario para eliminar datos sensibles
            $user->update([
                'name' => 'Usuario Eliminado - ' . $user->id,
                'email' => 'eliminado_' . $user->id . '@medflow.com',
                'password' => null,
                'google_id' => null,
                'subscription_type' => null,
                'subscription_plan' => null,
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Todos los datos del usuario han sido eliminados correctamente. La cuenta ha sido anonimizada.',
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar los datos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generar token de confirmacion para la eliminacion de datos
     */
    public function generateDeleteConfirmationToken(Request $request)
    {
        $user = $request->user();
        
        // Generar token basado en el ID del usuario y la fecha actual
        $token = hash('sha256', $user->id . $user->email . date('Y-m-d'));

        return response()->json([
            'success' => true,
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
        ]);
    }
}