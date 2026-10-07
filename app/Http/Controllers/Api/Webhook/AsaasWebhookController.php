<?php

namespace App\Http\Controllers\Api\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Payment\CheckoutService;
use App\Services\Payment\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AsaasWebhookController extends Controller
{
    // Eventos que liberam acesso
    private const PAID_EVENTS = ['PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED'];

    public function handle(Request $request, PaymentGateway $gateway, CheckoutService $checkout)
    {
        // 1. Valida o token de autenticação do webhook (segurança)
        $tokenRecebido = $request->header('asaas-access-token');
        $tokenEsperado = config('services.asaas.webhook_token');

        if (!$tokenEsperado || $tokenRecebido !== $tokenEsperado) {
            return response()->json(['message' => 'Token inválido.'], 401);
        }

        // 2. Interpreta o payload de forma padronizada
        $dados = $gateway->parseWebhook($request->all());
        Log::info('Webhook Asaas recebido', $dados);

        // 3. Pagamento confirmado → ativa a assinatura
        if (in_array($dados['event'], self::PAID_EVENTS, true) && $dados['gateway_payment_id']) {
            $resultado = $checkout->confirmPayment(
                $dados['gateway_payment_id'],
                $dados['external_reference']
            );

            Log::info('Webhook Asaas processado', [
                'gateway_payment_id' => $dados['gateway_payment_id'],
                'resultado'          => $resultado,
            ]);
        }

        // 4. Responde 200 rápido (o Asaas espera uma resposta rápida)
        return response()->json(['received' => true]);
    }
}
