<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan',
        'status',
        'payment_id',
        'preference_id',
        'amount',
        'currency',
        'paid_at',
        'expires_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function markAsPaid($paymentId = null)
    {
        $this->status = 'active';
        $this->paid_at = now();
        if ($paymentId) {
            $this->payment_id = $paymentId;
        }
        $this->expires_at = now()->addDays(30); // 30 días de suscripción
        $this->save();
    }

    public function markAsExpired()
    {
        $this->status = 'expired';
        $this->save();
    }
}