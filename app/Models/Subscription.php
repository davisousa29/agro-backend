<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Subscription extends Model
{
    use HasUuids;

    protected $table = 'subscriptions';

    protected $fillable = [
        'user_id',
        'status',
        'plan',
        'payment_method',
        'starts_at',
        'expires_at',
        'blocked_at',
        'grace_ends_at',
        'is_subscriber',
        'gateway_customer_id',
        'gateway_subscription_id',
    ];

    protected $casts = [
        'starts_at'      => 'datetime',
        'expires_at'     => 'datetime',
        'blocked_at'     => 'datetime',
        'grace_ends_at'  => 'datetime',
        'is_subscriber'  => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ── Helpers de estado ─────────────────────────────────────────────────────

    public function isActive(): bool
    {
        // Vitalícia nunca expira
        if ($this->status === 'lifetime') {
            return true;
        }

        // Trial ou assinatura ativa, dentro da validade
        if (in_array($this->status, ['trial', 'active'])) {
            return now()->lessThanOrEqualTo($this->expires_at);
        }

        return false;
    }

    public function isLifetime(): bool
    {
        return $this->status === 'lifetime';
    }
}
