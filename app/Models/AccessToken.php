<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AccessToken extends Model
{
    protected $fillable = [
        'user_id',
        'sensor_id',
        'group_id',
        'token',
        'expires_at',
        'max_uses',
        'used_count',
        'created_by'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sensor()
    {
        return $this->belongsTo(Sensor::class);
    }

    public function group()
    {
        return $this->belongsTo(SensorGroup::class, 'group_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateToken()
    {
        return Str::random(40);
    }

    public function isValid()
    {
        return $this->expires_at->isFuture() && 
               ($this->max_uses === null || $this->used_count < $this->max_uses);
    }

    public function useToken()
    {
        $this->increment('used_count');
    }
}