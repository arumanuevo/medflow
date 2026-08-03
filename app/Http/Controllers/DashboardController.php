<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\WorkspaceCollaborator;

class DashboardController extends Controller
{
    /**
     * Mostrar el dashboard con configuraciones globales.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $activeWorkspace = $request->input('workspace') ?? session('active_workspace', $user->id);
        session(['active_workspace' => $activeWorkspace]);

        // ✅ Verificar si el usuario tiene acceso al workspace solicitado
        $hasAccess = $this->canAccessWorkspace($user, $activeWorkspace);
        
        if (!$hasAccess) {
            session(['active_workspace' => $user->id]);
            return redirect('/dashboard')->with('error', 'No tienes acceso a ese espacio.');
        }

        // ✅ Verificar si el usuario está pausado en este workspace
        $isPaused = false;
        $pauseMessage = null;
        
        if ($activeWorkspace != $user->id) {
            $collaboration = WorkspaceCollaborator::where('workspace_id', $activeWorkspace)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
            
            if ($collaboration && $collaboration->is_paused) {
                $isPaused = true;
                $pauseMessage = '⚠️ Tu acceso a este espacio está temporalmente suspendido por el propietario.';
            }
        }

        $workspaceInfo = $this->getWorkspaceInfo($user, $activeWorkspace);

        $defaultPeriod = UserSetting::get($user->id, 'default_measurement_period', 30);
        $defaultExpiry = UserSetting::get($user->id, 'default_expiry_days', 5);

        return view('dashboard', compact(
            'defaultPeriod',
            'defaultExpiry',
            'activeWorkspace',
            'workspaceInfo',
            'isPaused',      // ✅ Pasar estado de pausa
            'pauseMessage'   // ✅ Pasar mensaje de pausa
        ));
    }


    /**
     * Actualizar configuraciones globales del usuario.
     */
    public function updateSettings(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'default_measurement_period' => 'required|integer|min:1',
            'default_expiry_days' => 'required|integer|min:1'
        ], [
            'default_measurement_period.required' => 'El período de medición es obligatorio.',
            'default_measurement_period.integer' => 'El período de medición debe ser un número entero.',
            'default_measurement_period.min' => 'El período de medición debe ser al menos 1 día.',
            'default_expiry_days.required' => 'Los días de vencimiento son obligatorios.',
            'default_expiry_days.integer' => 'Los días de vencimiento deben ser un número entero.',
            'default_expiry_days.min' => 'Los días de vencimiento deben ser al menos 1 día.'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Guardar configuraciones
        UserSetting::set($user->id, 'default_measurement_period', $request->default_measurement_period);
        UserSetting::set($user->id, 'default_expiry_days', $request->default_expiry_days);

        return back()->with('success', 'Configuración actualizada correctamente');
    }

    /**
     * Verificar si el usuario puede acceder a un workspace
     */
    private function canAccessWorkspace($user, $workspaceId)
    {
        // Es su propio espacio
        if ($workspaceId == $user->id) {
            return true;
        }

        // ✅ Es un espacio donde colabora activamente (NO pausado)
        $collaboration = WorkspaceCollaborator::where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('is_paused', false) // ✅ Importante: no pausados
            ->first();

        return $collaboration !== null;
    }

    /**
     * Obtener información del workspace
     */
    private function getWorkspaceInfo($user, $workspaceId)
    {
        if ($workspaceId == $user->id) {
            return [
                'type' => 'owner',
                'name' => 'Mi espacio',
                'owner_name' => $user->name,
                'owner_email' => $user->email,
            ];
        }

        $collaboration = WorkspaceCollaborator::where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->with('workspace')
            ->first();

        if ($collaboration) {
            return [
                'type' => 'collaborator',
                'name' => $collaboration->workspace->name ?? 'Espacio colaborativo',
                'owner_name' => $collaboration->workspace->name ?? 'Usuario',
                'owner_email' => $collaboration->workspace->email ?? '',
                'role' => $collaboration->role,
                'is_paused' => $collaboration->is_paused, // ✅ Pasar estado de pausa
            ];
        }

        return null;
    }
}