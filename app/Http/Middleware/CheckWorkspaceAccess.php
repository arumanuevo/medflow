<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckWorkspaceAccess
{
    public function handle(Request $request, Closure $next, $role = null)
    {
        $user = auth()->user();
        $workspaceId = $request->route('workspace_id') ?? $request->input('workspace_id');

        if (!$workspaceId) {
            // Si no se especifica, usar el ID del usuario autenticado
            $workspaceId = $user->id;
        }

        // Verificar acceso
        $hasAccess = $user->id == $workspaceId || 
                     $user->collaborations()
                         ->where('workspace_id', $workspaceId)
                         ->where('status', 'active')
                         ->exists();

        if (!$hasAccess) {
            abort(403, 'No tienes acceso a este espacio de trabajo.');
        }

        // Si se especifica un rol, verificar que el colaborador tenga ese rol
        if ($role) {
            $collaboration = $user->collaborations()
                ->where('workspace_id', $workspaceId)
                ->where('status', 'active')
                ->first();

            if ($user->id != $workspaceId) {
                if (!$collaboration || $collaboration->role !== $role) {
                    abort(403, "Se requiere rol '$role' para esta acción.");
                }
            }
        }

        return $next($request);
    }
}