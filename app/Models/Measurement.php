<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Measurement extends Model
{
    protected $fillable = [
        'sensor_id',
        'measured_at',
        'proxima_medicion',
        'periodo_medicion', // <-- Agrega esta línea
        'data',
        'created_by'
    ];

    protected $casts = [
        'data' => 'array',
        'measured_at' => 'datetime',
    ];

    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class);
    }

    /**
     * Alias de compatibilidad para la relación con el medidor.
     */
    public function medidor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class, 'sensor_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}