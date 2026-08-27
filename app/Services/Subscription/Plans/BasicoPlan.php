<?php
// app/Services/Subscription/Plans/BasicoPlan.php

namespace App\Services\Subscription\Plans;

use App\Models\User;

class BasicoPlan implements PlanInterface
{
    public function __construct(protected ?User $user = null)
    {
    }
    public function getMaxSensors(): int
    {
        return 10;
    }

    public function getMaxGroups(): int
    {
        return 2;
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
        $prices = @json_decode(file_get_contents(storage_path('app/pricing.json')), true) ?: ['basico' => 10000.00, 'premium' => 25000.00];
        return isset($prices['basico']) ? (float)$prices['basico'] : 10000.00;
    }

    public function getDescription(): string
    {
        return 'Plan Básico con límites ampliados. Ideal para usuarios domésticos.';
    }
}