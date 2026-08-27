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
        if (!$user)
            return [];
        $activeWorkspace = session('active_workspace', $user->id);
        $isOwner = $activeWorkspace == $user->id;

        $menu = [];

        // ✅ Si es propietario, mostrar menú completo
        if ($isOwner) {
            $menu = $this->getOwnerMenu();
        } else {
            // ✅ Si es colaborador, obtener su rol en el espacio activo
            $collaboration = WorkspaceCollaborator::where('workspace_id', $activeWorkspace)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$collaboration) {
                $menu = $this->getDefaultMenu();
            } else {
                $menu = $this->getCollaboratorMenu($collaboration->role);
            }
        }

        // ✅ INYECCIÓN DE ROL SUPERADMIN POR EMAIL (Root)
        if ($user->email === 'scastellanoadmin@gmail.com') {
            array_unshift($menu, [
                'icon' => 'bi bi-shield-lock-fill text-danger',
                'label' => 'Panel SuperAdmin',
                'url' => '/superadmin/users',
                'active' => request()->is('superadmin*'),
                'highlight' => true
            ]);
        }
        // AGREGAR CENTRO DE AYUDA AL FINAL
        $menu[] = [
            'icon' => 'bi bi-question-circle',
            'label' => 'Centro de Ayuda',
            'url' => '/ayuda',
            'active' => request()->is('ayuda*'),
        ];
        // AGREGAR CENTRO DE AYUDA AL FINAL
        $menu[] = [
            'icon' => 'bi bi-question-circle',
            'label' => 'Centro de Ayuda',
            'url' => '/ayuda',
            'active' => request()->is('ayuda*'),
        ];

        return $menu;
    }

    /**
     * Menú completo para propietario
     */
    private function getOwnerMenu()
    {
        $hasPremium = app(\App\Services\Subscription\SubscriptionService::class, ['user' => auth()->user()])->getPlan()->getPlanKey() === 'premium';

        // ✅ Usar URLs directamente en lugar de route()
        $menu = [
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
            ]
        ];

        if ($hasPremium) {
            $menu[] = [
                'icon' => 'bi bi-broadcast',
                'label' => 'Campañas Públicas',
                'url' => '/campaigns/bulk',
                'active' => request()->is('campaigns/bulk*'),
            ];
        }

        $menu = array_merge($menu, [
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
        ]);
        // AGREGAR CENTRO DE AYUDA AL FINAL
        $menu[] = [
            'icon' => 'bi bi-question-circle',
            'label' => 'Centro de Ayuda',
            'url' => '/ayuda',
            'active' => request()->is('ayuda*'),
        ];

        return $menu;
    }

    /**
     * Menú para colaborador según su rol
     */
    private function getCollaboratorMenu($role)
    {
        // ✅ Para ADMIN: Acceso casi total, menos Mi Perfil y Colaboraciones
        if ($role === 'admin') {
            $menu = $this->getOwnerMenu();
            // Filtrar "Mi Perfil" y "Colaboraciones" (ya que siempre son del usuario autenticado)
            $menu = array_filter($menu, function ($item) {
                return $item['url'] !== '/profile' && $item['url'] !== '/collaborations';
            });
            return array_values($menu);
        }

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
            ];
        }

        // ✅ Si el invitado tiene roles perdidos o remanentes, forzar por defecto interface silenciosa
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
            ]
        ];
    }

    /**
     * Obtener conteo de invitaciones pendientes
     */
    private function getPendingInvitationsCount()
    {
        $user = Auth::user();
        if (!$user)
            return 0;

        return $user->collaborations()
            ->where('status', 'pending')
            ->count();
    }
}