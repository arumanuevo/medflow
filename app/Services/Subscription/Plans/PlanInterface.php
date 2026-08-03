<?php
// app/Services/Subscription/Plans/PlanInterface.php

namespace App\Services\Subscription\Plans;

interface PlanInterface
{
    public function getMaxSensors(): int;
    public function getMaxGroups(): int;
    public function getMaxCollaborators(): int;
    public function canCreateCustomTemplates(): bool;
    public function canExportData(): bool;
    public function canViewAnalytics(): bool;
    public function canAddCollaborators(): bool;
    public function getPlanName(): string;
    public function getPlanType(): string;
    public function getPlanKey(): string;
    public function getPrice(): float;
    public function getDescription(): string;
}