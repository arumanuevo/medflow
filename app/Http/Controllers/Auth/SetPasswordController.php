<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class SetPasswordController extends Controller
{
    public function showForm($token)
    {
        // Verificar que el token sea válido
        $user = User::where('email', base64_decode($token))->first();
        
        if (!$user) {
            return redirect('/')->with('error', 'Enlace inválido.');
        }

        return view('auth.set-password', ['token' => $token, 'email' => $user->email]);
    }

    public function setPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Iniciar sesión automáticamente
        auth()->login($user);

        return redirect('/dashboard')->with('success', '¡Contraseña creada exitosamente!');
    }
}