<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\WorkspaceCollaborator;
use App\Models\Sensor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\CollaborationInvitation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Mail\CollaborationPausedMail;
use App\Mail\CollaborationResumedMail;
use App\Mail\CollaborationRevokedMail;
use App\Mail\CollaborationRoleChangedMail;


class CollaborationController extends Controller
{
    /**
     * Invitar a un usuario a mi espacio
     * Si el usuario no existe, se crea automáticamente
     */
    public function invite(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'role' => 'required|in:admin,inspector',
            'message' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // ✅ Buscar o crear usuario
        $invitedUser = User::where('email', $request->email)->first();

        if (!$invitedUser) {
            $name = explode('@', $request->email)[0];

            try {
                $invitedUser = User::create([
                    'name' => $name,
                    'email' => $request->email,
                    'password' => Hash::make(Str::random(16)),
                    'subscription_type' => 'domiciliario',
                    'subscription_plan' => 'básico',
                ]);

                $invitedUser->assignRole('consumidor');

                Log::info('🆕 Usuario creado automáticamente para invitación', [
                    'email' => $request->email,
                    'user_id' => $invitedUser->id
                ]);

            } catch (\Exception $e) {
                Log::error('❌ Error al crear usuario automático: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el usuario: ' . $e->getMessage()
                ], 500);
            }
        }

        if ($invitedUser->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes invitarte a ti mismo.'
            ], 400);
        }

        $existing = WorkspaceCollaborator::where('workspace_id', $user->id)
            ->where('user_id', $invitedUser->id)
            ->first();

        if ($existing) {
            $message = $existing->status === 'pending'
                ? 'Ya tienes una invitación pendiente para este usuario.'
                : 'Este usuario ya tiene acceso a tu espacio.';
            return response()->json([
                'success' => false,
                'message' => $message
            ], 400);
        }

        $token = WorkspaceCollaborator::generateToken();
        $collaborator = WorkspaceCollaborator::create([
            'workspace_id' => $user->id,
            'user_id' => $invitedUser->id,
            'role' => $request->role,
            'invited_by' => $user->id,
            'status' => 'pending',
            'token' => $token,
            'expires_at' => now()->addDays(7),
        ]);

        // ✅ Enviar email de invitación CON LOGS DETALLADOS
        try {
            Log::info('📧 Intentando enviar email a: ' . $invitedUser->email);

            Mail::to($invitedUser->email)->send(new CollaborationInvitation(
                $invitedUser,
                $user,
                $token,
                $request->message ?? null
            ));

            Log::info('✅ Email de invitación enviado a: ' . $invitedUser->email);

        } catch (\Exception $e) {
            Log::error('❌ Error al enviar email de invitación: ' . $e->getMessage());
            Log::error('📋 Stack trace: ' . $e->getTraceAsString());

            // ✅ La invitación se creó pero el email falló
            return response()->json([
                'success' => true,
                'warning' => 'Invitación creada, pero el email no se pudo enviar: ' . $e->getMessage(),
                'data' => [
                    'user_created' => !$invitedUser->wasRecentlyCreated,
                    'invitation_created' => true,
                    'collaborator' => $collaborator
                ]
            ], 201);
        }

        return response()->json([
            'success' => true,
            'message' => 'Invitación enviada correctamente. ' .
                ($invitedUser->wasRecentlyCreated ? 'Se creó una cuenta automáticamente para el usuario.' : ''),
            'data' => $collaborator
        ]);
    }
    /**
     * Aceptar invitación
     */
    public function acceptInvitation(Request $request, $token)
    {
        $collaborator = WorkspaceCollaborator::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if (!$collaborator) {
            return response()->json([
                'success' => false,
                'message' => 'Invitación inválida o expirada.'
            ], 404);
        }

        $user = $request->user();

        if ($collaborator->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Esta invitación no es para ti.'
            ], 403);
        }

        $collaborator->status = 'active';
        $collaborator->save();

        // ✅ Asignar el rol correspondiente con guard_name = web
        $user->assignRole($collaborator->role);

        return response()->json([
            'success' => true,
            'message' => '¡Invitación aceptada! Ahora tienes acceso al espacio.',
            'data' => [
                'workspace_id' => $collaborator->workspace_id,
                'workspace_name' => $collaborator->workspace->name,
                'role' => $collaborator->role
            ]
        ]);
    }

    /**
     * Rechazar invitación
     */
    public function rejectInvitation(Request $request, $token)
    {
        $collaborator = WorkspaceCollaborator::where('token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$collaborator) {
            return response()->json([
                'success' => false,
                'message' => 'Invitación no encontrada.'
            ], 404);
        }

        $user = $request->user();

        if ($collaborator->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Esta invitación no es para ti.'
            ], 403);
        }

        $collaborator->status = 'rejected';
        $collaborator->save();

        return response()->json([
            'success' => true,
            'message' => 'Invitación rechazada.'
        ]);
    }

    /**
     * Listar colaboradores de mi espacio
     */
    public function listCollaborators(Request $request)
    {
        $user = $request->user();

        $collaborators = WorkspaceCollaborator::where('workspace_id', $user->id)
            ->with(['user', 'inviter'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $collaborators
        ]);
    }

    /**
     * Listar invitaciones pendientes que recibí
     */
    public function listPendingInvitations(Request $request)
    {
        $user = $request->user();

        $invitations = WorkspaceCollaborator::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with(['workspace', 'inviter'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $invitations
        ]);
    }


    /**
     * Obtener historial de actividad de colaboradores
     */
    public function getActivityLog(Request $request)
    {
        $user = $request->user();

        $collaborators = WorkspaceCollaborator::where('workspace_id', $user->id)
            ->where('status', 'active')
            ->with('user')
            ->get()
            ->map(function ($collab) {
                return [
                    'id' => $collab->id,
                    'user' => $collab->user,
                    'role' => $collab->role,
                    'is_paused' => $collab->is_paused,
                    'last_active_at' => $collab->last_active_at,
                    'joined_at' => $collab->created_at,
                    'status' => $collab->is_paused() ? 'pausado' : 'activo',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $collaborators
        ]);
    }

    // =============================================
    // PAUSAR COLABORADOR
    // =============================================
    public function pauseCollaborator(Request $request, $id)
    {
        $user = $request->user();

        $collaborator = WorkspaceCollaborator::where('workspace_id', $user->id)
            ->where('id', $id)
            ->where('status', 'active')
            ->with('user')
            ->first();

        if (!$collaborator) {
            return response()->json([
                'success' => false,
                'message' => 'Colaborador no encontrado.'
            ], 404);
        }

        if ($collaborator->is_paused) {
            return response()->json([
                'success' => false,
                'message' => 'El colaborador ya está pausado.'
            ], 400);
        }

        $collaborator->pause();

        // ✅ Enviar email de notificación
        try {
            Mail::to($collaborator->user->email)->send(new CollaborationPausedMail($collaborator, $user));
            Log::info('📧 Email de pausa enviado a: ' . $collaborator->user->email);
        } catch (\Exception $e) {
            Log::error('❌ Error al enviar email de pausa: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Acceso del colaborador pausado correctamente. Se ha notificado al usuario.',
            'data' => $collaborator->load('user')
        ]);
    }

    // =============================================
    // REANUDAR COLABORADOR
    // =============================================
    public function unpauseCollaborator(Request $request, $id)
    {
        $user = $request->user();

        $collaborator = WorkspaceCollaborator::where('workspace_id', $user->id)
            ->where('id', $id)
            ->where('status', 'active')
            ->with('user')
            ->first();

        if (!$collaborator) {
            return response()->json([
                'success' => false,
                'message' => 'Colaborador no encontrado.'
            ], 404);
        }

        if (!$collaborator->is_paused) {
            return response()->json([
                'success' => false,
                'message' => 'El colaborador no está pausado.'
            ], 400);
        }

        $collaborator->unpause();

        // ✅ Enviar email de notificación
        try {
            Mail::to($collaborator->user->email)->send(new CollaborationResumedMail($collaborator, $user));
            Log::info('📧 Email de reanudación enviado a: ' . $collaborator->user->email);
        } catch (\Exception $e) {
            Log::error('❌ Error al enviar email de reanudación: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Acceso del colaborador reanudado correctamente. Se ha notificado al usuario.',
            'data' => $collaborator->load('user')
        ]);
    }

    // =============================================
    // ELIMINAR COLABORADOR (revocar acceso)
    // =============================================
    public function removeCollaborator(Request $request, $id)
    {
        $user = $request->user();

        $collaborator = WorkspaceCollaborator::where('workspace_id', $user->id)
            ->where('id', $id)
            ->with('user')
            ->first();

        if (!$collaborator) {
            return response()->json([
                'success' => false,
                'message' => 'Colaborador no encontrado.'
            ], 404);
        }

        // ✅ Guardar datos antes de eliminar para el email
        $collaboratorData = $collaborator;
        $userEmail = $collaborator->user ? $collaborator->user->email : null; // Cuidado con usuarios eliminados/soft-deletes
        $status = $collaborator->status;

        $collaborator->delete();

        // ✅ Enviar email de notificación solo si estaba activo y el usuario tiene mail válido
        if ($status === 'active' && $userEmail && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($userEmail)->send(new CollaborationRevokedMail($collaboratorData, $user));
                Log::info('📧 Email de revocación enviado a: ' . $userEmail);
            } catch (\Exception $e) {
                Log::error('❌ Error al enviar email de revocación: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Acceso del colaborador revocado correctamente. Se ha notificado al usuario.'
        ]);
    }

    // =============================================
    // CAMBIAR ROL
    // =============================================
    public function changeRole(Request $request, $id)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'role' => 'required|in:admin,inspector',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $collaborator = WorkspaceCollaborator::where('workspace_id', $user->id)
            ->where('id', $id)
            ->where('status', 'active')
            ->with('user')
            ->first();

        if (!$collaborator) {
            return response()->json([
                'success' => false,
                'message' => 'Colaborador no encontrado.'
            ], 404);
        }

        if ($collaborator->is_paused) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede cambiar el rol de un colaborador pausado. Reanúdalo primero.'
            ], 400);
        }

        $oldRole = $collaborator->role;
        $collaborator->role = $request->role;
        $collaborator->save();

        // Actualizar el rol del usuario
        $collaborator->user->assignRole($request->role);

        // ✅ Enviar email de notificación
        try {
            Mail::to($collaborator->user->email)->send(new CollaborationRoleChangedMail($collaborator, $user, $oldRole, $request->role));
            Log::info('📧 Email de cambio de rol enviado a: ' . $collaborator->user->email);
        } catch (\Exception $e) {
            Log::error('❌ Error al enviar email de cambio de rol: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => "Rol cambiado de '{$oldRole}' a '{$request->role}' correctamente. Se ha notificado al usuario.",
            'data' => $collaborator->load('user')
        ]);
    }

    // =============================================
    // ASIGNAR RUTAS DE SENSORES (FASE 38)
    // =============================================
    public function getSensors(Request $request, $id)
    {
        $user = $request->user();
        $collaborator = WorkspaceCollaborator::where('workspace_id', $user->id)
            ->where('id', $id)
            ->with(['user', 'sensors'])
            ->first();

        if (!$collaborator) {
            return response()->json(['success' => false, 'message' => 'Colaborador no encontrado.'], 404);
        }

        // Obtener todos los sensores primarios puros del Admin Workspace
        $allSensors = Sensor::whereHas('group', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('group:id,name')->get(); // Incluimos grupo para agrupar en cascada si fuera necesario en UI

        $formattedSensors = $allSensors->map(function ($sensor) {
            return [
                'id' => $sensor->id,
                'nombre' => $sensor->name,
                'identificador' => $sensor->identifier,
                'grupo' => $sensor->group ? $sensor->group->name : 'Sin grupo',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'collaborator_name' => collect(explode(' ', $collaborator->user->name))->first(),
                'has_restricted_access' => $collaborator->has_restricted_access,
                'assigned_sensor_ids' => $collaborator->sensors->pluck('id')->toArray(),
                'available_sensors' => $formattedSensors
            ]
        ]);
    }

    public function syncSensors(Request $request, $id)
    {
        $user = $request->user();
        $collaborator = WorkspaceCollaborator::where('workspace_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$collaborator) {
            return response()->json(['success' => false, 'message' => 'Colaborador no encontrado.'], 404);
        }

        $hasRestricted = filter_var($request->input('has_restricted_access', false), FILTER_VALIDATE_BOOLEAN);
        $assignedIds = $request->input('assigned_ids', []);

        if (!is_array($assignedIds)) {
            $assignedIds = [];
        }

        $collaborator->has_restricted_access = $hasRestricted;
        $collaborator->save();

        if ($hasRestricted) {
            $collaborator->sensors()->sync($assignedIds);
        } else {
            $collaborator->sensors()->detach();
        }

        return response()->json([
            'success' => true,
            'message' => 'Ruteo de sensores actualizado correctamente para el colaborador.'
        ]);
    }
}