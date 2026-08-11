<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'roles', // Campo para almacenar roles como JSON
        'subscription_type',
        'subscription_plan', 
    ];

    /**
     * Los atributos que deben ocultarse al serializar.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relación con los roles del usuario (usando Spatie Laravel Permission).
     * NOTA: HasRoles ya incluye esta relación, pero la dejamos explícita para claridad.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            \Spatie\Permission\Models\Role::class,
            'model_has_roles',
            'model_id',
            'role_id'
        );
    }

    /**
     * Verificar si el usuario tiene un rol específico.
     *
     * @param string|array $roles Rol o array de roles a verificar.
     * @return bool
     */
    public function hasRole($roles): bool
    {
        // Si es un array de roles, verificar si tiene alguno de ellos
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }
            return false;
        }

        // Si es un string, verificar si tiene ese rol
        return $this->roles->contains('name', $roles);
    }

    /**
     * Verificar si el usuario tiene todos los roles especificados.
     *
     * @param array $roles Array de roles a verificar.
     * @return bool
     */
    public function hasAllRoles(array $roles): bool
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Asignar rol según el tipo de suscripción.
     */
    public function assignRoleBySubscription()
    {
        $role = $this->subscription_type === 'corporativo' ? 'inspector' : 'consumidor';
        $this->syncRoles([$role]);
    }

    // Relación con espacios de trabajo (colaboraciones)
    public function collaborations()
    {
        return $this->hasMany(WorkspaceCollaborator::class, 'user_id');
    }

    // Relación con espacios que posee
    public function ownedWorkspaces()
    {
        return $this->hasMany(WorkspaceCollaborator::class, 'workspace_id');
    }

    // Verificar si tiene acceso a un workspace
    public function hasAccessToWorkspace($workspaceId)
    {
        return $this->id == $workspaceId || 
            $this->collaborations()->where('workspace_id', $workspaceId)
                ->where('status', 'active')
                ->exists();
    }

   /**
     * Obtener todos los workspaces a los que tiene acceso
     * (propio + colaboraciones activas)
     */
    public function getAccessibleWorkspaces()
    {
        $workspaces = [$this->id];
        $collaborations = $this->collaborations()
            ->where('status', 'active')
            ->pluck('workspace_id')
            ->toArray();
        return array_merge($workspaces, $collaborations);
    }

    /**
     * Verificar si el usuario tiene acceso activo a un workspace
     * (incluye verificación de pausa)
     */
    public function hasActiveCollaboration($workspaceId)
    {
        $collaboration = $this->collaborations()
            ->where('workspace_id', $workspaceId)
            ->where('status', 'active')
            ->where('is_paused', false) // ✅ Importante: no pausados
            ->first();
        
        return $collaboration !== null;
    }

    /**
     * Obtener colaboración activa en un workspace
     */
    public function getActiveCollaboration($workspaceId)
    {
        return $this->collaborations()
            ->where('workspace_id', $workspaceId)
            ->where('status', 'active')
            ->where('is_paused', false)
            ->first();
    }

    /**
 * Obtener la suscripción activa del usuario
 */
public function getActiveSubscription(): ?Subscription
{
    return $this->subscriptions()
        ->where('status', 'active')
        ->where(function($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        })
        ->latest()
        ->first();
}

/**
 * Verificar si el usuario tiene una suscripción activa
 */
public function hasActiveSubscription(): bool
{
    return $this->getActiveSubscription() !== null;
}

/**
 * Relación con suscripciones
 */
public function subscriptions()
{
    return $this->hasMany(Subscription::class);
}

/**
 * Verificar si el usuario es colaborador en algún workspace
 */
public function isCollaborator(): bool
{
    return WorkspaceCollaborator::where('user_id', $this->id)
        ->where('status', 'active')
        ->where('is_paused', false)
        ->exists();
}

/**
 * Obtener el propietario del workspace donde colabora
 */
public function getWorkspaceOwner(): ?User
{
    $collaboration = WorkspaceCollaborator::where('user_id', $this->id)
        ->where('status', 'active')
        ->where('is_paused', false)
        ->first();

    if ($collaboration) {
        return User::find($collaboration->workspace_id);
    }

    return null;
}
// app/Models/User.php

/**
 * Verificar si el usuario ha sido downgradeado (tenía plan pago y ahora está en Free)
 */
public function hasBeenDowngraded(): bool
{
    // Si no tiene suscripción activa pero su plan en BD es pago
    $activeSubscription = $this->getActiveSubscription();
    $userPlan = $this->subscription_plan;
    
    if (!$activeSubscription && ($userPlan === 'basico' || $userPlan === 'premium')) {
        return true;
    }
    
    // Si la suscripción expiró
    $lastSubscription = Subscription::where('user_id', $this->id)
        ->where('status', 'expired')
        ->whereIn('plan', ['basico', 'premium'])
        ->latest()
        ->first();
    
    if ($lastSubscription) {
        return true;
    }
    
    return false;
}

/**
 * Obtener el plan anterior (antes del downgrade)
 */
public function getPreviousPlan(): ?string
{
    $lastSubscription = Subscription::where('user_id', $this->id)
        ->where('status', 'expired')
        ->whereIn('plan', ['basico', 'premium'])
        ->latest()
        ->first();
    
    if ($lastSubscription) {
        return $lastSubscription->plan;
    }
    
    return null;
}
}