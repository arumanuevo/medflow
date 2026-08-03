<?php
// app/Models/SensorGroupSharedAccess.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorGroupSharedAccess extends Model
{
    protected $fillable = [
        'sensor_group_id',
        'shared_with',
        'role',
    ];

    /**
     * Relación con el grupo de sensores.
     */
    public function sensorGroup(): BelongsTo
    {
        return $this->belongsTo(SensorGroup::class);
    }

    /**
     * Relación con el usuario invitado.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_with');
    }
}