<?php
// app/Services/Subscription/Plans/PlanFactory.php

namespace App\Services\Subscription\Plans;

use App\Models\User;
use InvalidArgumentException;

class PlanFactory
{
    private static array $plans = [
        'free' => FreePlan::class,
        'basico' => BasicoPlan::class,
        'premium' => PremiumPlan::class,
    ];

    public static function make(string $planKey, ?User $user = null): PlanInterface
    {
        $class = self::$plans[$planKey] ?? null;

        if (!$class) {
            throw new InvalidArgumentException("Plan no soportado: {$planKey}");
        }

        return new $class($user);
    }

    public static function makeFromUser(User $user): PlanInterface
    {
        // ✅ 1. Verificar si tiene suscripción activa
        $activeSubscription = $user->getActiveSubscription();

        if ($activeSubscription) {
            // Si tiene suscripción activa, usar el plan correspondiente
            return self::make($activeSubscription->plan, $user);
        }

        // ✅ 2. Verificar si tiene un plan asignado en el campo subscription_plan
        if ($user->subscription_plan && in_array($user->subscription_plan, ['free', 'basico', 'premium'])) {
            // Si el plan es 'basico' o 'premium' pero no hay suscripción activa, es un downgrade
            if ($user->subscription_plan !== 'free') {
                // ✅ Guardar en sesión para que el frontend lo use
                session(['previous_plan' => $user->subscription_plan]);
            }
            return self::make($user->subscription_plan, $user);
        }

        // ✅ 3. Si es admin, darle Premium
        if ($user->hasRole('admin')) {
            return new PremiumPlan($user);
        }

        // ✅ 4. Por defecto, Free
        return new FreePlan($user);
    }

    /**
     * Obtener todos los planes disponibles para mostrar en UI
     */
    public static function getAvailablePlans(): array
    {
        $plans = [];
        foreach (self::$plans as $key => $class) {
            $plan = new $class();
            $plans[] = [
                'key' => $key,
                'name' => $plan->getPlanName(),
                'type' => $plan->getPlanType(),
                'price' => $plan->getPrice(),
                'description' => $plan->getDescription(),
                'limits' => [
                    'sensors' => $plan->getMaxSensors() === PHP_INT_MAX ? 'Ilimitado' : $plan->getMaxSensors(),
                    'groups' => $plan->getMaxGroups() === PHP_INT_MAX ? 'Ilimitado' : $plan->getMaxGroups(),
                    'collaborators' => $plan->getMaxCollaborators() === PHP_INT_MAX ? 'Ilimitado' : $plan->getMaxCollaborators(),
                ],
                'features' => [
                    'custom_templates' => $plan->canCreateCustomTemplates(),
                    'export_data' => $plan->canExportData(),
                    'view_analytics' => $plan->canViewAnalytics(),
                    'add_collaborators' => $plan->canAddCollaborators(),
                ]
            ];
        }
        return $plans;
    }
}