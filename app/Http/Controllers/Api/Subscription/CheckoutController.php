<?php

namespace App\Http\Controllers\Api\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payment\CheckoutService;
use App\Services\Payment\PaymentGateway;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    public function __construct(
        private CheckoutService $checkout,
        private PaymentGateway $gateway,
    ) {}

    // ── Lista os planos disponíveis ───────────────────────────────────────────
    public function plans()
    {
        $plans = collect(CheckoutService::PLAN_PRICES)
            ->map(fn ($price, $plan) => [
                'plan'  => $plan,
                'price' => $price,
                'days'  => SubscriptionService::PLAN_DAYS[$plan],
            ])
            ->values();

        return response()->json(['plans' => $plans]);
    }

    // ── Cria (ou reaproveita) a cobrança PIX do plano escolhido ──────────────
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan' => 'required|in:' . implode(',', array_keys(CheckoutService::PLAN_PRICES)),
        ], [
            'plan.required' => 'Escolha um plano.',
            'plan.in'       => 'Plano inválido.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        if ($user->subscription?->status === 'lifetime') {
            return response()->json(['message' => 'Sua assinatura é vitalícia.'], 422);
        }

        try {
            $payment = $this->checkout->createPixCharge($user, $request->plan);
        } catch (\Throwable $e) {
            Log::error('Erro ao gerar cobrança no checkout', [
                'user_id' => $user->id,
                'plan'    => $request->plan,
                'erro'    => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Não foi possível gerar a cobrança. Tente novamente em instantes.',
            ], 502);
        }

        return response()->json(['payment' => $this->present($payment, true)], 201);
    }

    // ── Consulta a cobrança (o app usa para saber se foi paga) ───────────────
    public function show(Request $request, string $id)
    {
        $payment = Payment::where('user_id', auth()->id())->findOrFail($id);

        return response()->json([
            'payment' => $this->present($payment, $request->boolean('pix')),
        ]);
    }

    // ── Formata a resposta da cobrança ────────────────────────────────────────
    private function present(Payment $payment, bool $withPix): array
    {
        $pix = null;

        if ($withPix && $payment->status === 'pending') {
            try {
                $qr  = $this->gateway->getPixQrCode($payment->gateway_payment_id);
                $pix = $qr['pix_copy_paste'] ? $qr : null; // QR ainda não gerado → null
            } catch (\Throwable $e) {
                $pix = null;
            }
        }

        return [
            'id'          => $payment->id,
            'plan'        => $payment->plan,
            'value'       => (float) $payment->value,
            'status'      => $payment->status,
            'due_date'    => $payment->due_date?->toDateString(),
            'paid_at'     => $payment->paid_at?->toIso8601String(),
            'invoice_url' => $payment->invoice_url,
            'pix'         => $pix,
        ];
    }
}
