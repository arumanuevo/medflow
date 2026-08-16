<?php
namespace App\Http\Controllers;

use App\Models\SensorGroup;
use App\Models\Template;
use Illuminate\Http\Request;


class SensorGroupViewController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $groups = SensorGroup::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereHas('sharedAccess', function ($q) use ($user) {
                    $q->where('shared_with', $user->id);
                });
        })->with(['user', 'template', 'sensors'])->get();

        $subscriptionService = new \App\Services\Subscription\SubscriptionService($user);
        $canCreateGroup = $subscriptionService->canCreateGroup();

        return view('sensor-groups.index', compact('groups', 'canCreateGroup'));
    }

    public function create()
    {
        $user = auth()->user();

        // REVISIÓN DE CUOTA PRE-VUELO
        $subscriptionService = new \App\Services\Subscription\SubscriptionService($user);
        if (!$subscriptionService->canCreateGroup()) {
            $planName = $subscriptionService->getPlan()->getPlanName();
            return redirect()->route('sensor-groups.index')->with('error', "Has alcanzado el límite de grupos para tu plan {$planName}. Sube de plan para crear más.");
        }

        $templates = Template::where(function ($q) use ($user) {
            $q->where('is_default', true)
                ->orWhereNull('created_by')
                ->orWhere('created_by', $user->id);
        })->orderBy('name')->get();

        return view('sensor-groups.create', compact('templates'));
    }

    public function edit(SensorGroup $group)
    {
        $user = auth()->user();
        $templates = Template::where(function ($q) use ($user) {
            $q->where('is_default', true)
                ->orWhereNull('created_by')
                ->orWhere('created_by', $user->id);
        })->orderBy('name')->get();

        return view('sensor-groups.edit', compact('group', 'templates'));
    }

    public function show(SensorGroup $group)
    {
        // Asegúrate de cargar las relaciones necesarias
        $group->load(['user', 'template', 'sensors']);
        return view('sensor-groups.show', compact('group'));
    }
    // app/Http/Controllers/SensorGroupViewController.php
    public function destroy(SensorGroup $group)
    {
        $user = auth()->user();

        // Verificar que el usuario tenga permiso para eliminar el grupo
        $canDelete = $user->hasRole('admin') || $group->user_id === $user->id;

        if (!$canDelete) {
            abort(403, 'No tienes permiso para eliminar este grupo');
        }

        $group->delete();

        // Redirigir a la lista de grupos con un mensaje de éxito
        return redirect()->route('sensor-groups.index')->with('success', 'Grupo eliminado correctamente');
    }

    /**
     * Mostrar el formulario para importar sensores masivamente
     */
    // En SensorGroupViewController.php
    public function showBulkImportForm(Request $request)
    {
        $user = auth()->user();

        // Obtener los grupos del usuario
        $groups = SensorGroup::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereHas('sharedAccess', function ($q) use ($user) {
                    $q->where('shared_with', $user->id)
                        ->whereIn('role', ['inspector', 'admin']);
                });
        })
            ->with(['user', 'template'])
            ->orderBy('name')
            ->get();

        // Obtener el grupo seleccionado (si se pasa por parámetro)
        $selectedGroupId = $request->get('group_id');

        return view('sensor-groups.bulk-import', compact('groups', 'selectedGroupId'));
    }

}