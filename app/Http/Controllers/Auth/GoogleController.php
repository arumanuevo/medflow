<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Error al autenticar con Google.');
        }

        // Buscar usuario existente por google_id o email
        $user = User::where('google_id', $googleUser->id)->first();

        if (!$user) {
            // Si no existe, buscar por email y vincular
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
                // Asignar rol por defecto (asegúrate de que exista)
                $user->assignRole('consumidor');
            }
        }

        Auth::login($user);
        return redirect()->route('dashboard')->with('success', 'Bienvenido a MeasureFlow');
    }
}