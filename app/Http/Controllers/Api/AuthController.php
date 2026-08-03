<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\PendingInvitation; // ✅ Importar el modelo
use App\Models\SensorGroupSharedAccess; // ✅ Importar el modelo
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    /**
     * Registrar un nuevo usuario.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('inspector');

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
            'token' => $token,
        ], 201);
    }

    /**
     * Iniciar sesión y devolver un token.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
            'token' => $token,
        ]);
    }

    /**
     * Cerrar sesión (eliminar el token actual).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente',
        ]);
    }

    /**
     * Obtener los datos del usuario autenticado.
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'roles' => $request->user()->getRoleNames(),
        ]);
    }

    /**
     * Asignar roles (solo para admins)
     */
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|string|in:admin,inspector,consumidor',
        ]);

        if (!$request->user()->hasRole('admin')) {
            return response()->json([
                'message' => 'No tienes permiso para asignar roles',
            ], 403);
        }

        $user->syncRoles([$request->role]);

        return response()->json([
            'message' => 'Rol asignado correctamente',
            'user' => $user,
            'roles' => $user->getRoleNames(),
        ]);
    }

    /**
     * Asignar múltiples roles (solo para admins)
     */
    public function assignRoles(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'string|in:admin,inspector,consumidor',
        ]);

        if (!$request->user()->hasRole('admin')) {
            return response()->json([
                'message' => 'No tienes permiso para asignar roles',
            ], 403);
        }

        $user->syncRoles($request->roles);

        return response()->json([
            'message' => 'Roles asignados correctamente',
            'user' => $user,
            'roles' => $user->getRoleNames(),
        ]);
    }

/**
 * Registro público (consumidor) - API
 */
/**
 * Registro público (consumidor) - API
 */
public function registerConsumer(Request $request)
{
    \Log::info('📝 Intento de registro', $request->all());
    
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'subscription_type' => 'sometimes|in:domiciliario,corporativo',
        'g-recaptcha-response' => 'required|captcha',
    ]);

    if ($validator->fails()) {
        \Log::error('❌ Error de validación', $validator->errors()->toArray());
        return response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        $subscriptionType = $request->subscription_type ?? 'domiciliario';
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'subscription_type' => $subscriptionType,
            'subscription_plan' => $subscriptionType === 'corporativo' ? 'premium' : 'básico',
        ]);

        // Asignar rol según tipo de suscripción
        if ($subscriptionType === 'corporativo') {
            $user->assignRole('inspector');
        } else {
            $user->assignRole('consumidor');
        }

        // Verificar invitaciones pendientes
        $invitations = PendingInvitation::where('email', $request->email)
            ->where('used', 0)
            ->get();

        foreach ($invitations as $invitation) {
            SensorGroupSharedAccess::create([
                'sensor_group_id' => $invitation->sensor_group_id,
                'shared_with' => $user->id,
                'role' => $invitation->role,
            ]);
            $invitation->used = 1;
            $invitation->save();
        }

        // Crear token (opcional, por si quieres usarlo después)
        $token = $user->createToken('auth_token')->plainTextToken;

        \Log::info('✅ Usuario registrado exitosamente', [
            'user_id' => $user->id, 
            'email' => $user->email,
        ]);

        // ✅ Responder con los datos del usuario (sin sesión)
        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado correctamente',
            'data' => [
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'subscription_type' => $user->subscription_type,
                    'subscription_plan' => $user->subscription_plan,
                ],
                'token' => $token,
            ],
        ], 201);
        
    } catch (\Exception $e) {
        \Log::error('❌ Error al registrar usuario: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error interno al registrar: ' . $e->getMessage()
        ], 500);
    }
}
}