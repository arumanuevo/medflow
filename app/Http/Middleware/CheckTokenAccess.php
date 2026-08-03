<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\AccessToken;

class CheckTokenAccess
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->route('token') ?? $request->input('token');

        if (!$token) {
            abort(401, 'Token de acceso requerido.');
        }

        $accessToken = AccessToken::where('token', $token)->first();

        if (!$accessToken || !$accessToken->isValid()) {
            abort(401, 'Token inválido o expirado.');
        }

        // Guardar el token en la request para usarlo en el controlador
        $request->merge(['access_token' => $accessToken]);

        return $next($request);
    }
}