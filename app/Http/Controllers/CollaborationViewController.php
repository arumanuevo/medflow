<?php

namespace App\Http\Controllers;

use App\Models\WorkspaceCollaborator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollaborationViewController extends Controller
{
    /**
     * Mostrar la página de gestión de colaboraciones
     */
    public function index()
    {
        $user = Auth::user();
        
        // Obtener colaboradores de mi espacio
        $collaborators = WorkspaceCollaborator::where('workspace_id', $user->id)
            ->with(['user', 'inviter'])
            ->get();
        
        // Obtener invitaciones pendientes que recibí
        $pendingInvitations = WorkspaceCollaborator::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with(['workspace', 'inviter'])
            ->get();
        
        return view('collaborations.index', compact('collaborators', 'pendingInvitations'));
    }
}