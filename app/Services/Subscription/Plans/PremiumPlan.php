<?php
// app/Services/Subscription/Plans/PremiumPlan.php

namespace App\Services\Subscription\Plans;

class PremiumPlan implements PlanInterface
{
    public function getMaxSensors(): int
    {
        return PHP_INT_MAX; // Ilimitado
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