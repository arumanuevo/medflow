<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsumptionCalculation extends Model
{
    protected $fillable = [
        'sensor_id',
        'start_measurement_id',
        'end_measurement_id',
        'consumption_value',
        'unit',
        'period_start',
        'period_end',
        'cost',
        'currency'
    ];

    protected $casts = [
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'cost' => 'decimal:2'
    ];

    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class);
    }

    public function startMeasurement(): BelongsTo
    {
        return $this->belongsTo(Measurement::class, 'start_measurement_id');
    }

    public function endMeasurement(): BelongsTo
    {
        return $this->belongsTo(Measurement::class, 'end_measurement_id');
    }
}