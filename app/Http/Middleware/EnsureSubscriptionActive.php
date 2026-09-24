<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Sem usuário autenticado, deixa o fluxo de auth normal cuidar
        if (!$user) {
            return $next($request);
        }

        $subscription = $user->subscription;

        // Se não tem assinatura ou não está ativa, bloqueia com 402
        if (!$subscription || !$subscription->isActive()) {
            return response()->json([
                'message'       => 'Sua assinatura expirou. Renove para continuar.',
                'subscription'  => 'inactive',
            ], 402); // 402 Payment Required
        }

        return $next($request);
    }
}
