<?php
// app/Services/Subscription/Plans/PremiumPlan.php

namespace App\Services\Subscription\Plans;

use App\Models\User;

class PremiumPlan implements PlanInterface
{
    public function __construct(protected ?User $user = null)
    {
    }

    public function getMaxSensors(): int
    {
        $base = 20;
        $extraPacks = $this->user ? ($this->user->additional_sensor_packs ?? 0) : 0;
        return $base + ($extraPacks * 10);
    }

    public function getMaxGroups(): int
    {
        return PHP_INT_MAX; // Ilimitado
    }

    public function getMaxCollaborators(): int
    {
        return PHP_INT_MAX; // Ilimitado
    }

    public function canCreateCustomTemplates(): bool
    {
        return true;
    }

    public function canExportData(): bool
    {
        return true;
    }

    public function canViewAnalytics(): bool
    {
        return true;
    }

    public function canAddCollaborators(): bool
    {
        return true;
    }

    public function getPlanName(): string
    {
        return 'Premium';
    }

    public function getPlanType(): string
    {
        return 'corporativo';
    }

    public function getPlanKey(): string
    {
        return 'premium';
    }

    public function getPrice(): float
    {
        return 25.00;
    }

    public function getDescription(): string
    {
        return 'Plan Premium con todas las funcionalidades ilimitadas. Ideal para empresas.';
    }
}