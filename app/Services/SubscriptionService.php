<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subscription;

class SubscriptionService
{
    const TRIAL_DAYS = 7;
    const GRACE_TRIAL_DAYS = 7;
    const GRACE_PAID_DAYS  = 30;

    /**
     * Cria a assinatura de trial para um novo usuário.
     */
    public function createTrial(User $user): Subscription
    {
        return Subscription::create([
            'user_id'       => $user->id,
            'status'        => 'trial',
            'starts_at'     => now(),
            'expires_at'    => now()->addDays(self::TRIAL_DAYS),
            'is_subscriber' => false,
        ]);
    }

    /**
     * Concede assinatura vitalícia (cortesia para clientes de teste).
     */
    public function grantLifetime(User $user): Subscription
    {
        $subscription = $user->subscription ?? new Subscription(['user_id' => $user->id]);

        $subscription->fill([
            'user_id'    => $user->id,
            'status'     => 'lifetime',
            'starts_at'  => $subscription->starts_at ?? now(),
            'expires_at' => now()->addYears(100),
        ]);
        $subscription->save();

        return $subscription;
    }
}
