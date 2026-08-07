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
    private PlanInterface $plan;
    private ?PlanInterface $ownerPlan = null;

    public function __construct(User $user, ?User $contextUser = null)
    {
        $this->user = $user;
        $this->plan = PlanFactory::makeFromUser($user);

        // ✅ Si el usuario es colaborador, usar el plan del propietario del workspace
        if ($contextUser) {
            $this->ownerPlan = PlanFactory::makeFromUser($contextUser);
        } else {
            // Intentar detectar automáticamente si es colaborador
            $this->detectOwnerPlan();
        }
    }

    /**
     * Detectar automáticamente si el usuario es colaborador y usar el plan del propietario
     */
    private function detectOwnerPlan(): void
    {
        // Si el usuario tiene una colaboración activa como colaborador
        $collaboration = WorkspaceCollaborator::where('user_id', $this->user->id)
            ->where('status', 'active')
            ->where('is_paused', false)
            ->first();

        if ($collaboration) {
            $owner = User::find($collaboration->workspace_id);
            if ($owner) {
                $this->ownerPlan = PlanFactory::makeFromUser($owner);
                Log::info('👥 Colaborador detectado, usando plan del propietario', [
                    'user_id' => $this->user->id,
                    'workspace_id' => $collaboration->workspace_id,
                    'owner_plan' => $this->ownerPlan->getPlanName()
                ]);
            }
        }
    }

    /**
     * Obtener el plan activo (prioriza el plan del propietario si es colaborador)
     */
    public function getPlan(): PlanInterface
    {
        // Si es colaborador y tiene un plan de propietario, usar ese
        if ($this->ownerPlan) {
            return $this->ownerPlan;
        }
        return $this->plan;
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
        return Sensor::whereHas('group', function($q) {
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
    public function getLimitStatus(): array
    {
        $plan = $this->getPlan();
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
    public function getFullStatus(): array
    {
        $plan = $this->getPlan();
        $limitStatus = $this->getLimitStatus();

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
            'subscription' => $this->user->getActiveSubscription() ? [
                'id' => $this->user->getActiveSubscription()->id,
                'plan' => $this->user->getActiveSubscription()->plan,
                'status' => $this->user->getActiveSubscription()->status,
                'expires_at' => $this->user->getActiveSubscription()->expires_at,
                'paid_at' => $this->user->getActiveSubscription()->paid_at,
            ] : null,
            'has_active_subscription' => $this->hasActiveSubscription(),
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
        
        // Si el plan es ilimitado, puede medir cualquier sensor
        if ($maxSensors === PHP_INT_MAX) {
            return true;
        }
        
        // Obtener todos los sensores del usuario ordenados por fecha de creación (ascendente)
        $userSensors = Sensor::whereHas('group', function($q) {
            $q->where('user_id', $this->user->id);
        })->orderBy('created_at', 'asc')->get();
        
        // Encontrar la posición del sensor en la lista ordenada
        $sensorIndex = $userSensors->search(function($s) use ($sensor) {
            return $s->id === $sensor->id;
        });
        
        // Si el sensor está dentro de los primeros N (donde N = maxSensors), puede medir
        return $sensorIndex !== false && $sensorIndex < $maxSensors;
    }

    /**
     * Obtener los IDs de los sensores en los que el usuario puede tomar mediciones
     */
    public function getMeasurableSensorIds(): array
    {
        $plan = $this->getPlan();
        $maxSensors = $plan->getMaxSensors();
        
        // Si el plan es ilimitado, puede medir todos sus sensores
        if ($maxSensors === PHP_INT_MAX) {
            return Sensor::whereHas('group', function($q) {
                $q->where('user_id', $this->user->id);
            })->pluck('id')->toArray();
        }
        
        // Obtener los primeros N sensores ordenados por fecha de creación
        return Sensor::whereHas('group', function($q) {
            $q->where('user_id', $this->user->id);
        })->orderBy('created_at', 'asc')->limit($maxSensors)->pluck('id')->toArray();
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
    $activeSubscription = $this->user->getActiveSubscription();
    
    if (!$activeSubscription) {
        return false;
    }
    
    // Si la suscripción expiró, actualizar estado
    if ($activeSubscription->expires_at && $activeSubscription->expires_at->isPast()) {
        $activeSubscription->status = 'expired';
        $activeSubscription->save();
        
        // Actualizar el usuario a Free
        $this->user->subscription_type = 'domiciliario';
        $this->user->subscription_plan = 'free';
        $this->user->save();
        $this->user->syncRoles(['consumidor']);
        
        Log::info('🔄 Suscripción expirada automáticamente', [
            'user_id' => $this->user->id,
            'subscription_id' => $activeSubscription->id,
            'expired_at' => $activeSubscription->expires_at
        ]);
        
        return true;
    }
    
    return false;
}
}