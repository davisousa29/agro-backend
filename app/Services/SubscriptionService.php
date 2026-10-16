<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    const TRIAL_DAYS = 7;
    const GRACE_TRIAL_DAYS = 7;
    const GRACE_PAID_DAYS  = 30;

    const PLAN_COURTESY = 'courtesy';

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
    /**
     * Concede assinatura vitalícia (cortesia para clientes de teste).
     * Limpa bloqueio e graça: se o cliente estava vencido, volta a ter acesso na hora.
     */
    public function grantLifetime(User $user): Subscription
    {
        $subscription = $user->subscription ?? new Subscription(['user_id' => $user->id]);

        $subscription->fill([
            'user_id'        => $user->id,
            'status'         => 'lifetime',
            'starts_at'      => $subscription->starts_at ?? now(),
            'expires_at'     => now()->addYears(100),
            'blocked_at'     => null,
            'grace_ends_at'  => null,
            'data_purged_at' => null,
        ]);
        $subscription->save();

        return $subscription;
    }

    /**
     * Revoga a assinatura vitalícia: o cliente volta ao ciclo normal com N dias até vencer.
     * A partir daí valem os avisos, o vencimento, a graça (7 dias, pois nunca pagou) e a exclusão.
     */
    public function revokeLifetime(User $user, int $days): Subscription
    {
        if ($days < 0) {
            throw new \InvalidArgumentException("Quantidade de dias inválida: {$days}");
        }

        $subscription = $user->subscription;

        if (!$subscription || $subscription->status !== 'lifetime') {
            throw new \RuntimeException('O usuário não tem assinatura vitalícia.');
        }

        $subscription->update([
            'status'         => 'active',
            'plan'           => self::PLAN_COURTESY,
            'payment_method' => null,
            'expires_at'     => now()->addDays($days),
            'blocked_at'     => null,
            'grace_ends_at'  => null,
            'data_purged_at' => null,
        ]);

        return $subscription;
    }

    /**
     * Ativa (ou renova) a assinatura após pagamento confirmado.
     * - Se ainda está ativa e no prazo: soma os dias ao vencimento atual (renovação antecipada).
     * - Se está em trial, vencida ou bloqueada: começa a contar de hoje (não retroativo).
     * - Vitalícia não é alterada.
     *
     * $days vem da própria cobrança (cópia do plano no momento da compra).
     */
    public function activate(User $user, string $plan, int $days, string $paymentMethod): Subscription
    {
        if ($days <= 0) {
            throw new \InvalidArgumentException("Quantidade de dias inválida: {$days}");
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
            'expires_at'     => $base->addDays($days),
            'is_subscriber'  => true,
            'blocked_at'     => null,
            'grace_ends_at'  => null,
            'data_purged_at' => null, // novo ciclo: a exclusão volta a valer se vencer de novo
        ]);
        $subscription->save();

        return $subscription;
    }

    /**
     * Até quando os dados ficam guardados depois do vencimento.
     * A graça conta a partir do vencimento (e não de quando o job rodou).
     */
    public function graceEndsAt(Subscription $subscription): Carbon
    {
        $days = $subscription->is_subscriber ? self::GRACE_PAID_DAYS : self::GRACE_TRIAL_DAYS;

        return $subscription->expires_at->copy()->addDays($days);
    }

    /**
     * Marca a assinatura como vencida e abre o período de graça.
     * Trava a linha e revalida: se um pagamento chegou nesse meio-tempo, não faz nada.
     * Retorna true se a assinatura foi de fato marcada como vencida.
     */
    public function expire(Subscription $subscription): bool
    {
        return DB::transaction(function () use ($subscription) {
            $locked = Subscription::whereKey($subscription->id)->lockForUpdate()->first();

            if (
                !$locked
                || !in_array($locked->status, ['trial', 'active'], true)
                || $locked->expires_at->isFuture()
            ) {
                return false;
            }

            $locked->update([
                'status'        => 'expired',
                'blocked_at'    => $locked->expires_at,
                'grace_ends_at' => $this->graceEndsAt($locked),
            ]);

            return true;
        });
    }
}
