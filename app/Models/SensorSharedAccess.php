<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorSharedAccess extends Model
{
    protected $fillable = [
        'sensor_id', 'shared_with', 'permissions'
    ];

    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_with');
    }
}