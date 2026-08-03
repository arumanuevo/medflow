<?php
// app/Http/Controllers/Api/PendingInvitationController.php
namespace App\Http\Controllers\Api;

use App\Models\SensorGroup;
use App\Models\PendingInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PendingInvitationController extends Controller
{
    /**
     * Listar invitaciones pendientes de un grupo
     */
    public function index(Request $request, $groupId)
    {
        $user = $request->user();

        // Verificar que el usuario sea el dueño del grupo o admin
        $group = SensorGroup::findOrFail($groupId);

        if ($group->user_id !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver las invitaciones de este grupo',
            ], 403);
        }

        // Obtener invitaciones pendientes del grupo
        $invitations = PendingInvitation::where('sensor_group_id', $groupId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Invitaciones pendientes obtenidas correctamente',
            'data' => $invitations,
        ]);
    }

    /**
     * Invitar a un usuario por correo electrónico (registrado o no)
     */
    public function store(Request $request, $groupId)
    {
        $user = $request->user();

        // Verificar que el usuario sea el dueño del grupo o admin
        $group = SensorGroup::findOrFail($groupId);

        if ($group->user_id !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para invitar usuarios a este grupo',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'role' => 'required|string|in:inspector,viewer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que el correo no sea del dueño del grupo
        if ($request->email === $user->email) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes invitarte a ti mismo',
            ], 400);
        }

        // Verificar si el usuario ya está registrado
        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser) {
            // Si el usuario ya está registrado, verificar si ya tiene acceso al grupo
            $existingAccess = SensorGroupSharedAccess::where('sensor_group_id', $groupId)
                ->where('shared_with', $existingUser->id)
                ->first();

            if ($existingAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este usuario ya tiene acceso a este grupo',
                ], 400);
            }

            // Si el usuario está registrado pero no tiene acceso, crear acceso compartido directamente
            $sharedAccess = SensorGroupSharedAccess::create([
                'sensor_group_id' => $groupId,
                'shared_with' => $existingUser->id,
                'role' => $request->role,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Acceso compartido con usuario registrado correctamente',
                'data' => $sharedAccess->load('user'),
            ], 201);
        }

        // Si el usuario no está registrado, crear invitación pendiente
        $token = PendingInvitation::generateToken();

        $invitation = PendingInvitation::create([
            'sensor_group_id' => $groupId,
            'email' => $request->email,
            'role' => $request->role,
            'token' => $token,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invitación enviada correctamente. El usuario podrá registrarse y acceder al grupo.',
            'data' => $invitation,
        ], 201);
    }

    /**
     * Eliminar una invitación pendiente
     */
    public function destroy($groupId, $invitationId)
    {
        $user = request()->user();

        // Verificar que el usuario sea el dueño del grupo o admin
        $group = SensorGroup::findOrFail($groupId);

        if ($group->user_id !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar esta invitación',
            ], 403);
        }

        // Verificar que la invitación pertenezca al grupo
        $invitation = PendingInvitation::where('id', $invitationId)
            ->where('sensor_group_id', $groupId)
            ->firstOrFail();

        $invitation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invitación eliminada correctamente',
        ]);
    }

    /**
     * Verificar si un correo tiene invitaciones pendientes
     */
    public function checkInvitations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Buscar invitaciones pendientes para este correo
        $invitations = PendingInvitation::where('email', $request->email)
            ->where('used', 0)
            ->with('sensorGroup')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Invitaciones pendientes obtenidas correctamente',
            'data' => $invitations,
        ]);
    }

    /**
     * Usar una invitación al registrarse
     */
    public function useInvitation(Request $request, $token)
    {
        $invitation = PendingInvitation::where('token', $token)
            ->where('used', 0)
            ->firstOrFail();

        $user = $request->user();

        // Verificar que el usuario que usa la invitación sea el mismo al que se invitó
        if ($user->email !== $invitation->email) {
            return response()->json([
                'success' => false,
                'message' => 'Esta invitación no es válida para tu cuenta',
            ], 403);
        }

        // Marcar la invitación como usada
        $invitation->used = 1;
        $invitation->save();

        // Crear acceso compartido para el usuario
        $sharedAccess = SensorGroupSharedAccess::create([
            'sensor_group_id' => $invitation->sensor_group_id,
            'shared_with' => $user->id,
            'role' => $invitation->role,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invitación usada correctamente. Ahora tienes acceso al grupo.',
            'data' => $sharedAccess->load('sensorGroup'),
        ]);
    }
}