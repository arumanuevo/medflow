<?php
// app/Services/Subscription/SubscriptionGate.php

namespace App\Services\Subscription;

use App\Models\User;

class SubscriptionGate
{
    private User $user;
    private array $planPermissions;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->planPermissions = $this->loadPermissions();
    }

    /**
     * Definir qué puede hacer cada plan
     */
    private function loadPermissions(): array
    {
        return [
            'free' => [
                'create_sensor' => true,
                'create_group' => true,
                'create_community_sensor' => false,
                'import_sensors' => false,
                'create_template' => false,
                'add_collaborator' => false,
                'export_data' => false,
                'view_analytics' => false,
            ],
            'basico' => [
                'create_sensor' => true,
                'create_group' => true,
                'create_community_sensor' => false,
                'import_sensors' => false,
                'create_template' => false,
                'add_collaborator' => false,
                'export_data' => false,
                'view_analytics' => false,
            ],
            'premium' => [
                'create_sensor' => true,
                'create_group' => true,
                'create_community_sensor' => true,
                'import_sensors' => true,
                'create_template' => true,
                'add_collaborator' => true,
                'export_data' => true,
                'view_analytics' => true,
            ],
        ];
    }

    /**
     * Obtener el plan del usuario
     */
    private function getUserPlan(): string
    {
        $service = new SubscriptionService($this->user);
        return $service->getPlan()->getPlanKey();
    }

    /**
     * Verificar si el usuario puede hacer algo
     */
    public function allows(string $permission): bool
    {
        $plan = $this->getUserPlan();

        // Si el plan no existe en la configuración, denegar
        if (!isset($this->planPermissions[$plan])) {
            return false;
        }

        return $this->planPermissions[$plan][$permission] ?? false;
    }

    /**
     * Obtener todos los permisos del usuario
     */
    public function getAllPermissions(): array
    {
        $plan = $this->getUserPlan();
        return $this->planPermissions[$plan] ?? [];
    }
}