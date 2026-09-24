<?php

namespace App\Http\Controllers\Api\Subscription;

use App\Http\Controllers\Controller;

class SubscriptionController extends Controller
{
    // ── Status da assinatura do usuário logado ────────────────────────────────
    public function status()
    {
        $user = auth()->user();
        $subscription = $user->subscription;

        // Sem assinatura (caso raro — usuário antigo antes do sistema)
        if (!$subscription) {
            return response()->json([
                'status'         => 'none',
                'is_active'      => false,
                'days_remaining' => 0,
            ]);
        }

        $isActive = $subscription->isActive();

        $daysRemaining = 0;
        if ($isActive && !$subscription->isLifetime()) {
            $daysRemaining = max(0, (int) ceil(now()->floatDiffInDays($subscription->expires_at, false)));
        }

        return response()->json([
            'status'         => $subscription->status,
            'is_active'      => $isActive,
            'is_lifetime'    => $subscription->isLifetime(),
            'plan'           => $subscription->plan,
            'expires_at'     => $subscription->expires_at,
            'days_remaining' => $daysRemaining,
            'is_subscriber'  => $subscription->is_subscriber,
        ]);
    }
}
