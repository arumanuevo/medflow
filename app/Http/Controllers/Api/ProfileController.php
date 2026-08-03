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
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'subscription_type' => $user->subscription_type ?? 'domiciliario',
                'subscription_plan' => $user->subscription_plan ?? 'básico',
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
}