<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subscription;
use Carbon\Carbon;

class SubscriptionService
{
    const TRIAL_DAYS = 7;
    const GRACE_TRIAL_DAYS = 7;
    const GRACE_PAID_DAYS  = 30;

    // Dias de acesso liberados por plano
    const PLAN_DAYS = [
        'monthly'    => 30,
        'semiannual' => 180,
        'annual'     => 365,
    ];

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

    /**
     * Ativa (ou renova) a assinatura após pagamento confirmado.
     * - Se ainda está ativa e no prazo: soma os dias ao vencimento atual (renovação antecipada).
     * - Se está em trial, vencida ou bloqueada: começa a contar de hoje (não retroativo).
     * - Vitalícia não é alterada.
     */
    public function activate(User $user, string $plan, string $paymentMethod): Subscription
    {
        if (!isset(self::PLAN_DAYS[$plan])) {
            throw new \InvalidArgumentException("Plano inválido: {$plan}");
        }

        $subscription = $user->subscription ?? new Subscription(['user_id' => $user->id]);

        if ($subscription->status === 'lifetime') {
            return $subscription;
        }

        $expiresAt   = $subscription->expires_at ? Carbon::parse($subscription->expires_at) : null;
        $stillActive = $subscription->exists
            && $subscription->status === 'active'
            && $expiresAt?->isFuture();

        $base = $stillActive ? $expiresAt->copy() : now();

        $subscription->fill([
            'user_id'        => $user->id,
            'status'         => 'active',
            'plan'           => $plan,
            'payment_method' => $paymentMethod,
            'starts_at'      => $stillActive ? $subscription->starts_at : now(),
            'expires_at'     => $base->addDays(self::PLAN_DAYS[$plan]),
            'is_subscriber'  => true,
            'blocked_at'     => null,
            'grace_ends_at'  => null,
        ]);
        $subscription->save();

        return $subscription;
    }
}
