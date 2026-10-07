<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    // TODO: valores provisórios — substituir após o estudo de preços
    const PLAN_PRICES = [
        'monthly'    => 19.90,
        'semiannual' => 99.90,
        'annual'     => 179.90,
    ];

    public function __construct(
        private PaymentGateway $gateway,
        private SubscriptionService $subscriptions,
    ) {}

    /**
     * Cria uma cobrança PIX para o plano escolhido e registra em `payments`.
     * Se já existir uma cobrança PIX pendente e não vencida do mesmo plano, reaproveita.
     */
    public function createPixCharge(User $user, string $plan): Payment
    {
        if (!isset(self::PLAN_PRICES[$plan])) {
            throw new \InvalidArgumentException("Plano inválido: {$plan}");
        }

        $pending = Payment::where('user_id', $user->id)
            ->where('plan', $plan)
            ->where('payment_method', 'pix')
            ->where('status', 'pending')
            ->whereDate('due_date', '>=', today())
            ->latest()
            ->first();

        if ($pending) {
            return $pending;
        }

        $subscription = $user->subscription;

        if (!$subscription) {
            throw new \RuntimeException('Usuário sem assinatura cadastrada.');
        }

        // Cria o cliente no Asaas só na primeira cobrança
        if (!$subscription->gateway_customer_id) {
            $subscription->gateway_customer_id = $this->gateway->createCustomer($user);
            $subscription->save();
        }

        $charge = $this->gateway->createPayment([
            'customer_id'        => $subscription->gateway_customer_id,
            'value'              => self::PLAN_PRICES[$plan],
            'due_date'           => now()->addDay()->toDateString(),
            'description'        => "Assinatura Colchete — plano {$plan}",
            'external_reference' => $user->id,
        ]);

        return Payment::create([
            'user_id'            => $user->id,
            'gateway'            => 'asaas',
            'gateway_payment_id' => $charge['gateway_payment_id'],
            'plan'               => $plan,
            'payment_method'     => 'pix',
            'value'              => $charge['value'],
            'status'             => 'pending',
            'due_date'           => $charge['due_date'],
            'invoice_url'        => $charge['invoice_url'],
        ]);
    }

    /**
     * Processa a confirmação de pagamento vinda do webhook.
     * Idempotente: se o Asaas reenviar o mesmo evento, não ativa de novo.
     */
    public function confirmPayment(string $gatewayPaymentId, ?string $externalReference): string
    {
        return DB::transaction(function () use ($gatewayPaymentId, $externalReference) {
            $payment = Payment::where('gateway_payment_id', $gatewayPaymentId)
                ->lockForUpdate()
                ->first();

            if (!$payment) {
                return 'not_found';
            }

            if ($payment->status === 'received') {
                return 'already_processed';
            }

            if ($externalReference && $externalReference !== $payment->user_id) {
                return 'reference_mismatch';
            }

            $payment->update([
                'status'  => 'received',
                'paid_at' => now(),
            ]);

            $this->subscriptions->activate($payment->user, $payment->plan, $payment->payment_method);

            return 'activated';
        });
    }
}
