<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WorkspaceCollaborator extends Model
{
    protected $fillable = [
        'workspace_id',
        'user_id',
        'role',
        'invited_by',
        'status',
        'token',
        'expires_at',
        'is_paused',      // ✅ Nuevo campo
        'last_active_at', // ✅ Nuevo campo
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_paused' => 'boolean',
        'last_active_at' => 'datetime',
    ];

    public function workspace()
    {
        return $this->belongsTo(User::class, 'workspace_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public static function generateToken()
    {
        return Str::random(60);
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isActive()
    {
        return $this->status === 'active' && !$this->is_paused;
    }

    public function isPaused()
    {
        return $this->status === 'active' && $this->is_paused;
    }

    public function pause()
    {
        $this->is_paused = true;
        $this->save();
    }

    public function unpause()
    {
        $this->is_paused = false;
        $this->last_active_at = now();
        $this->save();
    }
}