<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\WorkspaceCollaborator;

class SidebarService
{
    /**
     * Obtener los ítems del menú según el usuario y espacio activo
     */
    public function getMenuItems()
    {
        $user = Auth::user();
        $activeWorkspace = session('active_workspace', $user->id);
        $isOwner = $activeWorkspace == $user->id;

        // ✅ Si es propietario, mostrar menú completo
        if ($isOwner) {
            return $this->getOwnerMenu();
        }

        // ✅ Si es colaborador, obtener su rol en el espacio activo
        $collaboration = WorkspaceCollaborator::where('workspace_id', $activeWorkspace)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$collaboration) {
            return $this->getDefaultMenu();
        }

        return $this->getCollaboratorMenu($collaboration->role);
    }

    /**
     * Menú completo para propietario
     */
    private function getOwnerMenu()
    {
        // ✅ Usar URLs directamente en lugar de route()
        return [
            [
                'icon' => 'bi bi-house-door',  // antes fas fa-home
                'label' => 'Dashboard',
                'url' => '/dashboard',
                'active' => request()->is('dashboard') || request()->is('/'),
            ],
            [
                'icon' => 'bi bi-rulers',  // antes fas fa-ruler-combined
                'label' => 'Tomar Mediciones',
                'url' => '/bulk-measurements/select',
                'active' => request()->is('bulk-measurements*') || request()->is('mediciones/select-sensor*'),
            ],
            [
                'icon' => 'bi bi-people',
                'label' => 'Colaboraciones',
                'url' => '/collaborations',
                'active' => request()->is('collaborations*'),
                'badge' => $this->getPendingInvitationsCount(),
            ],
            [
                'icon' => 'bi bi-speedometer',
                'label' => 'Sensores',
                'url' => '/sensors',
                'active' => request()->is('sensors*') && !request()->is('sensors/create*'),
            ],
            [
                'icon' => 'bi bi-graph-up',
                'label' => 'Mediciones',
                'url' => '/mediciones',
                'active' => request()->is('mediciones'),
            ],
            [
                'icon' => 'bi bi-bar-chart-line',
                'label' => 'Consumos',
                'url' => '/consumptions',
                'active' => request()->is('consumptions*'),
            ],
            [
                'icon' => 'bi bi-folder',
                'label' => 'Grupos',
                'url' => '/sensor-groups',
                'active' => request()->is('sensor-groups*'),
            ],
            [
                'icon' => 'bi bi-file-earmark-text',
                'label' => 'Plantillas',
                'url' => '/templates',
                'active' => request()->is('templates*'),
            ],
            [
                'icon' => 'bi bi-person-circle',
                'label' => 'Mi Perfil',
                'url' => '/profile',
                'active' => request()->is('profile*'),
            ],
            [
                'icon' => 'bi bi-gear',
                'label' => 'Administración',
                'url' => '/admin',
                'active' => request()->is('admin*'),
                'admin_only' => true,
            ],
        ];
    }

    /**
     * Menú para colaborador según su rol
     */
    private function getCollaboratorMenu($role)
    {
        // ✅ Para INSPECTOR: solo tomar mediciones (usando la vista de inspector)
        if ($role === 'inspector') {
            return [
                [
                    'icon' => 'bi bi-house',
                    'label' => 'Dashboard',
                    'url' => '/dashboard',
                    'active' => request()->is('dashboard') || request()->is('/'),
                ],
                [
                    'icon' => 'bi bi-rulers',
                    'label' => 'Tomar Mediciones',
                    'url' => '/mediciones/inspector',  // ✅ NUEVA RUTA
                    'active' => request()->is('mediciones/inspector*'),
                    'highlight' => true,
                ],
                [
                    'icon' => 'bi bi-person-circle',
                    'label' => 'Mi Perfil',
                    'url' => '/profile',
                    'active' => request()->is('profile*'),
                ],
            ];
        }

        // ✅ Para ADMIN (colaborador): menú casi completo
        if ($role === 'admin') {
            return [
                [
                    'icon' => 'bi bi-house',
                    'label' => 'Dashboard',
                    'url' => '/dashboard',
                    'active' => request()->is('dashboard') || request()->is('/'),
                ],
                [
                    'icon' => 'bi bi-rulers',
                    'label' => 'Tomar Mediciones',
                    'url' => '/mediciones/select-sensor',
                    'active' => request()->is('mediciones/select-sensor*'),
                    'highlight' => true,
                ],
                [
                    'icon' => 'bi bi-speedometer',
                    'label' => 'Sensores',
                    'url' => '/sensors',
                    'active' => request()->is('sensors*') && !request()->is('sensors/create*'),
                ],
                [
                    'icon' => 'bi bi-graph-up',
                    'label' => 'Mediciones',
                    'url' => '/mediciones',
                    'active' => request()->is('mediciones'),
                ],
                [
                    'icon' => 'bi bi-bar-chart-line',
                    'label' => 'Consumos',
                    'url' => '/consumptions',
                    'active' => request()->is('consumptions*'),
                ],
                [
                    'icon' => 'bi bi-folder',
                    'label' => 'Grupos',
                    'url' => '/sensor-groups',
                    'active' => request()->is('sensor-groups*'),
                ],
                [
                    'icon' => 'bi bi-file-earmark-text',
                    'label' => 'Plantillas',
                    'url' => '/templates',
                    'active' => request()->is('templates*'),
                ],
                [
                    'icon' => 'bi bi-people',
                    'label' => 'Colaboraciones',
                    'url' => '/collaborations',
                    'active' => request()->is('collaborations*'),
                    'badge' => $this->getPendingInvitationsCount(),
                ],
                [
                    'icon' => 'bi bi-person-circle',
                    'label' => 'Mi Perfil',
                    'url' => '/profile',
                    'active' => request()->is('profile*'),
                ],
            ];
        }

        // ✅ Para CONSUMIDOR: solo ver
        if ($role === 'consumidor') {
            return [
                [
                    'icon' => 'bi bi-house',
                    'label' => 'Dashboard',
                    'url' => '/dashboard',
                    'active' => request()->is('dashboard') || request()->is('/'),
                ],
                [
                    'icon' => 'bi bi-speedometer',
                    'label' => 'Sensores',
                    'url' => '/sensors',
                    'active' => request()->is('sensors*') && !request()->is('sensors/create*'),
                ],
                [
                    'icon' => 'bi bi-graph-up',
                    'label' => 'Mediciones',
                    'url' => '/mediciones',
                    'active' => request()->is('mediciones'),
                ],
                [
                    'icon' => 'bi bi-bar-chart-line',
                    'label' => 'Consumos',
                    'url' => '/consumptions',
                    'active' => request()->is('consumptions*'),
                ],
                [
                    'icon' => 'bi bi-people',
                    'label' => 'Colaboraciones',
                    'url' => '/collaborations',
                    'active' => request()->is('collaborations*'),
                    'badge' => $this->getPendingInvitationsCount(),
                ],
                [
                    'icon' => 'bi bi-person-circle',
                    'label' => 'Mi Perfil',
                    'url' => '/profile',
                    'active' => request()->is('profile*'),
                ],
            ];
        }

        return $this->getDefaultMenu();
    }

    /**
     * Menú por defecto (sin permisos específicos)
     */
    private function getDefaultMenu()
    {
        return [
            [
                'icon' => 'bi bi-house',
                'label' => 'Dashboard',
                'url' => '/dashboard',
                'active' => request()->is('dashboard') || request()->is('/'),
            ],
            [
                'icon' => 'bi bi-person-circle',
                'label' => 'Mi Perfil',
                'url' => '/profile',
                'active' => request()->is('profile*'),
            ],
        ];
    }

    /**
     * Obtener conteo de invitaciones pendientes
     */
    private function getPendingInvitationsCount()
    {
        $user = Auth::user();
        if (!$user) return 0;
        
        return $user->collaborations()
            ->where('status', 'pending')
            ->count();
    }
}