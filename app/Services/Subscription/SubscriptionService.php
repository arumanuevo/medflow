<?php
// app/Services/Subscription/SubscriptionService.php

namespace App\Services\Subscription;

use App\Models\User;
use App\Models\Sensor;
use App\Models\SensorGroup;
use App\Models\WorkspaceCollaborator;
use App\Models\Subscription;
use App\Services\Subscription\Plans\PlanFactory;
use App\Services\Subscription\Plans\PlanInterface;
use App\Services\Subscription\Exceptions\LimitExceededException;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    private User $user;
    private ?PlanInterface $ownerPlan = null;

    public function __construct(User $user, ?User $contextUser = null)
    {
        $this->user = $user;

        // ✅ Verificar expiración de suscripciones si aplica
        $this->checkAndUpdateExpiredSubscription();

        // ✅ Si el usuario es colaborador, usar el plan del propietario del workspace
        if ($contextUser) {
            $this->ownerPlan = PlanFactory::makeFromUser($contextUser);
        } else {
            $this->detectOwnerPlan();
        }
    }

    /**
     * Detectar automáticamente si el usuario está actuando en el contexto de un colaborador
     */
    private function detectOwnerPlan(): void
    {
        $activeWorkspace = null;

        // 1. Intentar desde el header de API
        if (request()->hasHeader('X-Workspace-Id')) {
            $activeWorkspace = request()->header('X-Workspace-Id');
        }
        // 2. Intentar desde input explícito en request
        elseif (request()->has('workspace_id')) {
            $activeWorkspace = request()->input('workspace_id');
        }
        // 3. Fallback al session web si existe
        elseif (function_exists('session') && session()->has('active_workspace')) {
            $activeWorkspace = session('active_workspace');
        }

        // Si estamos actuando en un workspace distinto al nuestro
        if ($activeWorkspace && $activeWorkspace != $this->user->id) {
            $collaboration = WorkspaceCollaborator::where('user_id', $this->user->id)
                ->where('workspace_id', $activeWorkspace)
                ->where('status', 'active')
                ->where('is_paused', false)
                ->first();

            if ($collaboration) {
                $owner = User::find($collaboration->workspace_id);
                if ($owner) {
                    $this->ownerPlan = PlanFactory::makeFromUser($owner);
                    Log::info('👥 Colaborador detectado en contexto activo, usando plan del propietario', [
                        'user_id' => $this->user->id,
                        'workspace_id' => $collaboration->workspace_id,
                        'owner_plan' => $this->ownerPlan->getPlanName()
                    ]);
                }
            }
        }
    }

    /**
     * Obtener el plan del usuario o de su propietario en contexto
     */
    public function getPlan(bool $personalOnly = false): PlanInterface
    {
        if (!$personalOnly && $this->ownerPlan) {
            return $this->ownerPlan;
        }

        return PlanFactory::makeFromUser($this->user);
    }

    /**
     * Verificar si el usuario es colaborador
     */
    public function isCollaborator(): bool
    {
        return $this->ownerPlan !== null;
    }

    /**
     * Verificar si el usuario tiene suscripción activa
     */
    public function hasActiveSubscription(): bool
    {
        return $this->user->getActiveSubscription() !== null;
    }

    /**
     * Verificar si puede crear más sensores
     */
    public function canCreateSensor(): bool
    {
        $plan = $this->getPlan();
        $currentCount = $this->getCurrentSensorCount();
        $max = $plan->getMaxSensors();

        return $currentCount < $max;
    }

    /**
     * Verificar si puede crear más grupos
     */
    public function canCreateGroup(): bool
    {
        $plan = $this->getPlan();
        $currentCount = $this->getCurrentGroupCount();
        $max = $plan->getMaxGroups();

        return $currentCount < $max;
    }

    /**
     * Verificar si puede agregar colaboradores
     */
    public function canAddCollaborator(): bool
    {
        $plan = $this->getPlan();
        $currentCount = $this->getCurrentCollaboratorCount();
        $max = $plan->getMaxCollaborators();

        return $currentCount < $max && $plan->canAddCollaborators();
    }

    /**
     * Obtener conteo actual de sensores (propios + compartidos)
     */
    public function getCurrentSensorCount(): int
    {
        return Sensor::whereHas('group', function ($q) {
            $q->where('user_id', $this->user->id);
        })->count();
    }

    /**
     * Obtener conteo actual de grupos
     */
    public function getCurrentGroupCount(): int
    {
        return SensorGroup::where('user_id', $this->user->id)->count();
    }

    /**
     * Obtener conteo actual de colaboradores activos
     */
    public function getCurrentCollaboratorCount(): int
    {
        return WorkspaceCollaborator::where('workspace_id', $this->user->id)
            ->where('status', 'active')
            ->count();
    }

    /**
     * Obtener estado completo de límites
     */
    public function getLimitStatus(bool $personalOnly = false): array
    {
        $plan = $this->getPlan($personalOnly);
        $isUnlimited = fn($value) => $value === PHP_INT_MAX;

        return [
            'sensors' => [
                'used' => $this->getCurrentSensorCount(),
                'max' => $isUnlimited($plan->getMaxSensors()) ? null : $plan->getMaxSensors(),
                'is_unlimited' => $isUnlimited($plan->getMaxSensors()),
                'remaining' => $isUnlimited($plan->getMaxSensors()) ? null : max(0, $plan->getMaxSensors() - $this->getCurrentSensorCount()),
                'can_create' => $this->canCreateSensor(),
            ],
            'groups' => [
                'used' => $this->getCurrentGroupCount(),
                'max' => $isUnlimited($plan->getMaxGroups()) ? null : $plan->getMaxGroups(),
                'is_unlimited' => $isUnlimited($plan->getMaxGroups()),
                'remaining' => $isUnlimited($plan->getMaxGroups()) ? null : max(0, $plan->getMaxGroups() - $this->getCurrentGroupCount()),
                'can_create' => $this->canCreateGroup(),
            ],
            'collaborators' => [
                'used' => $this->getCurrentCollaboratorCount(),
                'max' => $isUnlimited($plan->getMaxCollaborators()) ? null : $plan->getMaxCollaborators(),
                'is_unlimited' => $isUnlimited($plan->getMaxCollaborators()),
                'remaining' => $isUnlimited($plan->getMaxCollaborators()) ? null : max(0, $plan->getMaxCollaborators() - $this->getCurrentCollaboratorCount()),
                'can_add' => $this->canAddCollaborator(),
            ],
        ];
    }

    /**
     * Obtener estado completo de la suscripción
     */
    public function getFullStatus(bool $personalOnly = false): array
    {
        // ✅ PRIMERO: Verificar si la suscripción expiró y actualizar
        $this->checkAndUpdateExpiredSubscription();

        // ✅ Obtener estado actualizado SIEMPRE del usuario en BD
        $this->user->refresh();

        $plan = $this->getPlan($personalOnly);
        $limitStatus = $this->getLimitStatus($personalOnly);
        $activeSubscription = $this->user->getActiveSubscription();
        $expiredSub = Subscription::where('user_id', $this->user->id)
            ->where('status', 'expired')
            ->orderBy('updated_at', 'desc')
            ->first();

        return [
            'plan' => [
                'key' => $plan->getPlanKey(),
                'name' => $plan->getPlanName(),
                'type' => $plan->getPlanType(),
                'price' => $plan->getPrice(),
                'description' => $plan->getDescription(),
                'is_collaborator' => $this->isCollaborator(),
            ],
            'limits' => $limitStatus,
            'features' => [
                'custom_templates' => $plan->canCreateCustomTemplates(),
                'export_data' => $plan->canExportData(),
                'view_analytics' => $plan->canViewAnalytics(),
                'add_collaborators' => $plan->canAddCollaborators(),
            ],
            'subscription' => $activeSubscription ? [
                'id' => $activeSubscription->id,
                'plan' => $activeSubscription->plan,
                'status' => $activeSubscription->status,
                'expires_at' => $activeSubscription->expires_at,
                'paid_at' => $activeSubscription->paid_at,
            ] : null,
            'has_active_subscription' => $activeSubscription !== null || $plan->getPlanKey() !== 'free',
            'previous_plan' => $expiredSub ? $expiredSub->plan : null,
        ];
    }

    /**
     * Verificar y lanzar excepción si no puede crear sensor
     */
    public function ensureCanCreateSensor(): void
    {
        if (!$this->canCreateSensor()) {
            $status = $this->getLimitStatus()['sensors'];
            throw new LimitExceededException(
                "Has alcanzado el límite de {$status['max']} sensores para tu plan {$this->getPlan()->getPlanName()}.",
                'sensor_limit_exceeded',
                $status
            );
        }
    }

    /**
     * Verificar y lanzar excepción si no puede crear grupo
     */
    public function ensureCanCreateGroup(): void
    {
        if (!$this->canCreateGroup()) {
            $status = $this->getLimitStatus()['groups'];
            throw new LimitExceededException(
                "Has alcanzado el límite de {$status['max']} grupos para tu plan {$this->getPlan()->getPlanName()}.",
                'group_limit_exceeded',
                $status
            );
        }
    }

    /**
     * Verificar si el usuario puede tomar mediciones en un sensor específico
     * Basado en el límite de su plan actual
     */
    public function canMeasureSensor(Sensor $sensor): bool
    {
        $plan = $this->getPlan();
        $maxSensors = $plan->getMaxSensors();

        if ($maxSensors === PHP_INT_MAX) {
            return true;
        }

        $userSensors = Sensor::whereHas('group', function ($q) {
            $q->where('user_id', $this->user->id);
        })->orderBy('name', 'asc')->get();

        $sensorIndex = $userSensors->search(function ($s) use ($sensor) {
            return $s->id === $sensor->id;
        });

        return $sensorIndex !== false && $sensorIndex < $maxSensors;
    }

    /**
     * Obtener los IDs de los sensores en los que el usuario puede tomar mediciones
     */
    public function getMeasurableSensorIds(): array
    {
        $plan = $this->getPlan();
        $maxSensors = $plan->getMaxSensors();

        if ($maxSensors === PHP_INT_MAX) {
            return Sensor::whereHas('group', function ($q) {
                $q->where('user_id', $this->user->id);
            })->pluck('id')->toArray();
        }

        return Sensor::whereHas('group', function ($q) {
            $q->where('user_id', $this->user->id);
        })->orderBy('name', 'asc')->limit($maxSensors)->pluck('id')->toArray();
    }

    /**
     * Verificar y lanzar excepción si no puede agregar colaborador
     */
    public function ensureCanAddCollaborator(): void
    {
        if (!$this->canAddCollaborator()) {
            $status = $this->getLimitStatus()['collaborators'];
            throw new LimitExceededException(
                "Tu plan {$this->getPlan()->getPlanName()} no permite agregar colaboradores.",
                'collaborator_limit_exceeded',
                $status
            );
        }
    }

    /**
     * Verificar si la suscripción ha expirado y actualizar el estado si es necesario
     */
    public function checkAndUpdateExpiredSubscription(): bool
    {
        // Buscar TODAS las suscripciones activas cuya fecha de expiración ya pasó
        $expiredSubscriptions = Subscription::where('user_id', $this->user->id)
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        if ($expiredSubscriptions->isEmpty()) {
            return false;
        }

        $previousPlan = null;
        $lastExpired = null;

        // ✅ 2. Marcar TODAS las suscripciones viejas como expiradas
        foreach ($expiredSubscriptions as $sub) {
            if (!$previousPlan)
                $previousPlan = $sub->plan;
            $sub->status = 'expired';
            $sub->save();
            $lastExpired = $sub;
        }

        // ✅ 3. ACTUALIZAR USUARIO A 'free'
        $this->user->refresh();
        $this->user->subscription_type = 'domiciliario';
        $this->user->subscription_plan = 'free';
        $this->user->save();

        // ✅ 4. ACTUALIZAR ROLES
        $this->user->syncRoles(['consumidor']);

        // ✅ 5. PAUSAR ACCESOS A COLABORADORES SI EXISTEN
        // Dado que el plan Free no permite colaboradores nativamente, debemos revocar el acceso
        \App\Models\WorkspaceCollaborator::where('workspace_id', $this->user->id)
            ->where('status', 'active')
            ->update(['is_paused' => true]);

        // ✅ 6. RECARGAR EL USUARIO
        $this->user->refresh();

        Log::info('🔄 Suscripciones expiradas automáticamente - Usuario actualizado a Free', [
            'user_id' => $this->user->id,
            'subscription_id' => $lastExpired ? $lastExpired->id : null,
            'previous_plan' => $previousPlan,
            'expired_at' => $lastExpired ? $lastExpired->expires_at : null,
            'new_plan' => 'free'
        ]);

        return true;
    }
}