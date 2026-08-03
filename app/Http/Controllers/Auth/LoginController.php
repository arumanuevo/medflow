<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // ✅ Crear token y guardarlo en sesión
        $token = $user->createToken('auth_token')->plainTextToken;
        $request->session()->put('sanctum_token', $token);
        
        // ✅ Guardar token en una cookie para que JavaScript pueda leerlo
        cookie()->queue('sanctum_token', $token, 60 * 24 * 7); // 7 días

        return redirect()->intended($this->redirectPath());
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $token = $request->user()->currentAccessToken();
            if ($token) {
                $token->delete();
            }
        }

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        cookie()->queue(cookie()->forget('sanctum_token'));

        return redirect('/');
    }
}