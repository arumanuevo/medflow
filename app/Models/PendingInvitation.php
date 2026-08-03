<?php
// app/Models/PendingInvitation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingInvitation extends Model
{
    protected $fillable = [
        'sensor_group_id',
        'email',
        'role',
        'token',
        'used'
    ];

    /**
     * Relación con el grupo de sensores.
     */
    public function sensorGroup(): BelongsTo
    {
        return $this->belongsTo(SensorGroup::class);
    }

    /**
     * Generar un token único para la invitación.
     */
    public static function generateToken()
    {
        return bin2hex(random_bytes(32));
    }
}
