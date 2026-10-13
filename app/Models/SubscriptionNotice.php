<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SubscriptionNotice extends Model
{
    use HasUuids;

    protected $fillable = [
        'subscription_id',
        'type',
        'reference_at',
    ];

    protected $casts = [
        'reference_at' => 'datetime',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
