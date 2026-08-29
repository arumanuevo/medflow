<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SensorGroup extends Model
{
    protected $fillable = [
        'name',
        'description',
        'user_id',
        'template_id',
        'periodo_medicion',
        'dias_vencimiento',
        'billing_settings'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    protected $casts = [
        'periodo_medicion' => 'integer',
        'dias_vencimiento' => 'integer',
        'billing_settings' => 'array'
    ];

    /**
     * Relación con el usuario dueño del grupo.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la plantilla asociada al grupo.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Relación con los sensores que pertenecen a este grupo.
     */
    public function sensors(): HasMany
    {
        return $this->hasMany(Sensor::class, 'group_id');
    }

    /**
     * Relación con los usuarios que tienen acceso compartido a este grupo.
     */
    public function sharedAccess(): HasMany
    {
        return $this->hasMany(SensorGroupSharedAccess::class, 'sensor_group_id');
    }

    public function pendingInvitations(): HasMany
    {
        return $this->hasMany(PendingInvitation::class, 'sensor_group_id');
    }
}