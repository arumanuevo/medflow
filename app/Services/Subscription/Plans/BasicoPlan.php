<?php
// app/Services/Subscription/Plans/BasicoPlan.php

namespace App\Services\Subscription\Plans;

class BasicoPlan implements PlanInterface
{
    public function getMaxSensors(): int
    {
        return 2; // ✅ 2 sensores para pruebas (en producción sería 10)
    }

    public function getMaxGroups(): int
    {
        return 2; // ✅ 2 grupos para pruebas (en producción sería 5)
    }

    public function getMaxCollaborators(): int
    {
        return 0; // Sin colaboradores
    }

    public function canCreateCustomTemplates(): bool
    {
        return false;
    }

    public function canExportData(): bool
    {
        return false;
    }

    public function canViewAnalytics(): bool
    {
        return false;
    }

    public function canAddCollaborators(): bool
    {
        return false;
    }

    public function getPlanName(): string
    {
        return 'Básico';
    }

    public function getPlanType(): string
    {
        return 'domiciliario';
    }

    public function getPlanKey(): string
    {
        return 'basico';
    }

    public function getPrice(): float
    {
        return 10.00;
    }

    public function getDescription(): string
    {
        return 'Plan Básico con límites ampliados. Ideal para usuarios domésticos.';
    }
}