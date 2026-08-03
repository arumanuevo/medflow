<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class InjectSanctumToken
{
    public function handle(Request $request, Closure $next): Response
    {
        // Solo procesar rutas de API (no rutas web)
        if (str_starts_with($request->path(), 'api/')) {
            if (Auth::check()) {
                $token = Auth::user()->currentAccessToken();
                if (!$token) {
                    $token = Auth::user()->createToken('auth_token');
                }
                $request->session()->put('sanctum_token', $token->plainTextToken);
            }
        }
        return $next($request);
    }
}