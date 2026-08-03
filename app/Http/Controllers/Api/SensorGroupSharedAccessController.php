<?php
// app/Http/Controllers/Api/SensorGroupSharedAccessController.php
namespace App\Http\Controllers\Api;

use App\Models\SensorGroup;
use App\Models\User;
use App\Models\SensorGroupSharedAccess;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SensorGroupSharedAccessController extends Controller
{
    /**
     * Listar usuarios con acceso compartido a un grupo de sensores
     */
    public function index(Request $request, $groupId)
    {
        $user = $request->user();

        // Verificar que el usuario sea el dueño del grupo o admin
        $group = SensorGroup::findOrFail($groupId);

        if ($group->user_id !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver los usuarios compartidos de este grupo',
            ], 403);
        }

        // Obtener usuarios con acceso compartido
        $sharedAccess = SensorGroupSharedAccess::where('sensor_group_id', $groupId)
            ->with('user')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Usuarios con acceso compartido obtenidos correctamente',
            'data' => $sharedAccess,
        ]);
    }

    /**
     * Compartir acceso a un grupo de sensores con otro usuario
     */
    public function store(Request $request, $groupId)
    {
        $user = $request->user();

        // Verificar que el usuario sea el dueño del grupo o admin
        $group = SensorGroup::findOrFail($groupId);

        if ($group->user_id !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para compartir este grupo',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|in:inspector,viewer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que el usuario no sea el dueño del grupo
        if ($request->user_id == $group->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes compartir el grupo contigo mismo',
            ], 400);
        }

        // Verificar que el usuario no ya tenga acceso
        $existingAccess = SensorGroupSharedAccess::where('sensor_group_id', $groupId)
            ->where('shared_with', $request->user_id)
            ->first();

        if ($existingAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Este usuario ya tiene acceso a este grupo',
            ], 400);
        }

        // Crear el acceso compartido
        $sharedAccess = SensorGroupSharedAccess::create([
            'sensor_group_id' => $groupId,
            'shared_with' => $request->user_id,
            'role' => $request->role,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Acceso compartido correctamente',
            'data' => $sharedAccess->load('user'),
        ], 201);
    }

    /**
     * Eliminar acceso compartido de un usuario
     */
    public function destroy($groupId, $accessId)
    {
        $user = request()->user();

        // Verificar que el usuario sea el dueño del grupo o admin
        $group = SensorGroup::findOrFail($groupId);

        if ($group->user_id !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar este acceso compartido',
            ], 403);
        }

        // Verificar que el acceso compartido pertenezca al grupo
        $sharedAccess = SensorGroupSharedAccess::where('id', $accessId)
            ->where('sensor_group_id', $groupId)
            ->firstOrFail();

        $sharedAccess->delete();

        return response()->json([
            'success' => true,
            'message' => 'Acceso compartido eliminado correctamente',
        ]);
    }
}