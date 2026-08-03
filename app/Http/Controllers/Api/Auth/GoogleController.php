<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    /**
     * Redirigir a Google para autenticación (para web)
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Manejar el callback de Google (para web)
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Error al autenticar con Google.');
        }
    
        // Buscar o crear usuario
        $user = $this->findOrCreateUser($googleUser);
    
        // ✅ Autenticar al usuario
        Auth::login($user);
    
        // ✅ Crear token de Sanctum y guardarlo en sesión
        $token = $user->createToken('auth_token')->plainTextToken;
        session(['sanctum_token' => $token]);
    
        // ✅ Redirigir al dashboard con el token en la sesión
        return redirect()->route('dashboard')->with([
            'success' => '¡Bienvenido ' . $user->name . '! Has iniciado sesión con Google.',
            'token' => $token
        ]);
    }
    /**
     * API: Obtener URL de redirección a Google (para clientes móviles/SPA)
     */
    public function getRedirectUrl(Request $request)
    {
        $url = Socialite::driver('google')
            ->stateless() // Importante para API
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'success' => true,
            'data' => [
                'redirect_url' => $url,
            ],
        ]);
    }

    /**
     * API: Manejar callback de Google (para clientes móviles/SPA)
     */
    public function handleApiCallback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Código de autorización requerido',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al autenticar con Google: ' . $e->getMessage(),
            ], 401);
        }

        $user = $this->findOrCreateUser($googleUser);

        // Crear token de Sanctum para API
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Autenticación con Google exitosa',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'subscription_type' => $user->subscription_type,
                    'roles' => $user->getRoleNames(),
                ],
                'token' => $token,
            ],
        ]);
    }

    /**
     * Buscar o crear usuario a partir de datos de Google
     */
    private function findOrCreateUser($googleUser)
    {
        // Buscar por google_id
        $user = User::where('google_id', $googleUser->id)->first();

        if (!$user) {
            // Buscar por email y vincular
            $user = User::where('email', $googleUser->email)->first();
            if ($user) {
                $user->google_id = $googleUser->id;
                $user->save();
            } else {
                // Crear nuevo usuario (por defecto domiciliario)
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'password' => Hash::make(Str::random(24)),
                    'google_id' => $googleUser->id,
                    'subscription_type' => 'domiciliario',
                    'subscription_plan' => 'básico',
                ]);

                // Asignar rol por defecto
                $user->assignRole('consumidor');
            }
        }

        return $user;
    }
}