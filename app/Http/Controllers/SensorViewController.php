<?php
// app/Http/Controllers/SensorViewController.php
namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\SensorGroup;
use Illuminate\Http\Request;
use App\Services\Subscription\SubscriptionGate;

class SensorViewController extends Controller
{
    public function create(Request $request)
    {
        $user = auth()->user();
        $groupId = $request->get('group_id');
        
        $groups = SensorGroup::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhereHas('sharedAccess', function($q) use ($user) {
                      $q->where('shared_with', $user->id);
                  });
        })->with(['user', 'template'])
          ->orderBy('name')
          ->get();

        if ($groups->isEmpty()) {
            return redirect()->route('sensor-groups.create')
                ->with('info', '📋 Para crear un sensor, primero necesitas crear un grupo de sensores.')
                ->with('alert_type', 'warning')
                ->with('alert_title', '¡Atención!');
        }

        return view('sensors.create', compact('groups', 'groupId'));
    }

    public function edit(Sensor $sensor)
    {
        $user = auth()->user();
        $canEdit = $user->hasRole('admin') || ($sensor->group && $sensor->group->user_id === $user->id);

        if (!$canEdit) {
            abort(403, 'No tienes permiso para editar este sensor.');
        }

        $groups = SensorGroup::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhereHas('sharedAccess', function($q) use ($user) {
                      $q->where('shared_with', $user->id);
                  });
        })->with(['user', 'template'])
          ->orderBy('name')
          ->get();

        return view('sensors.edit', compact('sensor', 'groups'));
    }

    public function index()
    {
        $user = auth()->user();
        
        $activeWorkspace = session('active_workspace', $user->id);
        $isOwner = $activeWorkspace == $user->id;
    
        if ($isOwner) {
            $groups = SensorGroup::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('sharedAccess', function($q) use ($user) {
                          $q->where('shared_with', $user->id);
                      });
            })->with(['user', 'template', 'sensors'])->get();
        } else {
            $collaboration = \App\Models\WorkspaceCollaborator::where('workspace_id', $activeWorkspace)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
    
            if (!$collaboration) {
                return redirect('/dashboard')->with('error', 'No tienes acceso a este espacio.');
            }
    
            $groups = SensorGroup::where('user_id', $activeWorkspace)
                ->with(['user', 'template', 'sensors'])
                ->orderBy('name')
                ->get();
        }
    
        // ✅ OBTENER PERMISOS (SIN HELPERS)
        $gate = new SubscriptionGate($user);
        $permissions = $gate->getAllPermissions();
    
        return view('sensors.index', compact('groups', 'permissions'));
    }

    /**
     * Mostrar el formulario para importar sensores masivamente
     */
    public function showBulkImportForm(Request $request)
    {
        $user = auth()->user();

        $groups = SensorGroup::where('user_id', $user->id)
            ->orWhereHas('sharedAccess', function($q) use ($user) {
                $q->where('shared_with', $user->id)
                  ->whereIn('role', ['inspector', 'admin']);
            })
            ->with(['user', 'template'])
            ->orderBy('name')
            ->get();

        $selectedGroupId = $request->get('group_id');

        return view('sensor-groups.bulk-import', compact('groups', 'selectedGroupId'));
    }

    // app/Http/Controllers/SensorViewController.php

    public function show(Sensor $sensor)
    {
        $user = auth()->user();

        // Verificar permisos: admin, inspector o consumidor
        $canViewSensor = $user->hasRole('admin') ||
                        ($sensor->group && $sensor->group->user_id === $user->id) ||
                        ($sensor->group && $sensor->group->sharedAccess()
                            ->where('shared_with', $user->id)
                            ->whereIn('role', ['inspector', 'admin', 'consumidor'])
                            ->exists());

        if (!$canViewSensor) {
            abort(403, 'No tienes permiso para ver este sensor.');
        }

        return view('sensors.show', compact('sensor'));
    }
}