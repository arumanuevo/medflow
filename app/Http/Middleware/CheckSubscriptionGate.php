<?php
// app/Http/Middleware/CheckSubscriptionGate.php

namespace App\Http\Middleware;

use Closure;
use App\Services\Subscription\SubscriptionGate;
use Illuminate\Http\Request;

class CheckSubscriptionGate
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión.'
            ], 401);
        }

        $gate = new SubscriptionGate($user);

        if (!$gate->allows($permission)) {
            // Si es API
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para realizar esta acción. Actualiza tu plan.',
                    'code' => 'permission_denied'
                ], 403);
            }
            
            // Si es web
            return redirect()->back()->with('error', 
                'No tienes permiso para realizar esta acción. Actualiza tu plan.'
            );
        }

        return $next($request);
    }
}