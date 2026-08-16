<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sensor extends Model
{
    /**
     * Los atributos que son asignables en masa.
     */
    protected $fillable = [
        'name',
        'identifier',
        'description',
        'coordinates',
        'group_id',
        'ultima_medicion',
        'proxima_medicion',
        'marcado_para_medicion',
        'is_community',
        'public_token',
        'metadata'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    protected $casts = [
        'coordinates' => 'array',
        'ultima_medicion' => 'datetime',
        'proxima_medicion' => 'datetime',
        'marcado_para_medicion' => 'boolean',
        'is_community' => 'boolean',
        'metadata' => 'array'
    ];

    /**
     * Relación con el grupo de sensores al que pertenece.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(SensorGroup::class, 'group_id');
    }

    /**
     * Relación con las mediciones de este sensor.
     */
    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class);
    }

    /**
     * Relación con la última medición de este sensor.
     */
    public function lastMeasurement(): HasOne
    {
        return $this->hasOne(Measurement::class)
            ->orderBy('measured_at', 'desc')
            ->latestOfMany();
    }

    /**
     * Relación con los cálculos de consumo de este sensor.
     */
    public function consumptionCalculations(): HasMany
    {
        return $this->hasMany(ConsumptionCalculation::class);
    }

    /**
     * Relación con los accesos compartidos de este sensor.
     */
    public function sharedAccess(): HasMany
    {
        return $this->hasMany(SensorSharedAccess::class);
    }

    /**
     * Relación inversa con colaboradores (Fase 38).
     */
    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(WorkspaceCollaborator::class, 'collaborator_sensor', 'sensor_id', 'workspace_collaborator_id');
    }
}