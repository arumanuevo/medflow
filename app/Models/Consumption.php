<?php
// app/Models/Consumption.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consumption extends Model
{
    // Especificar explícitamente el nombre de la tabla
    protected $table = 'consumptions'; // <-- Asegúrate de que apunte a la tabla correcta

    protected $fillable = [
        'sensor_id',
        'start_measurement_id',
        'end_measurement_id',
        'value',
        'unit',
        'period_start',
        'period_end',
        'days_between',
        'created_by'
    ];

    protected $casts = [
        'period_start' => 'datetime',
        'period_end' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}