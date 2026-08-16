<?php
// app/Services/Subscription/Plans/FreePlan.php

namespace App\Services\Subscription\Plans;

use App\Models\User;

class FreePlan implements PlanInterface
{
    public function __construct(protected ?User $user = null)
    {
    }
    public function getMaxSensors(): int
    {
        return 2; // ✅ 2 sensores permitidos
    }

    public function getMaxGroups(): int
    {
        return 1; // ✅ 1 grupo permitido
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
        return 'Gratuito';
    }

    public function getPlanType(): string
    {
        return 'domiciliario';
    }

    public function getPlanKey(): string
    {
        return 'free';
    }

    public function getPrice(): float
    {
        return 0.00;
    }

    public function getDescription(): string
    {
        return 'Plan gratuito con funcionalidades básicas. Ideal para probar la plataforma.';
    }
}