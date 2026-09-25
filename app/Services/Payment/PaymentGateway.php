<?php

namespace App\Services\Payment;

use App\Models\User;

interface PaymentGateway
{
    /**
     * Cria (ou recupera) o cliente no gateway e retorna o ID dele lá.
     */
    public function createCustomer(User $user): string;

    /**
     * Cria uma cobrança única (ex: PIX de um plano semestral/anual).
     * Retorna os dados da cobrança (id, link/qrcode do pix, status).
     */
    public function createPayment(array $data): array;

    /**
     * Busca o QR Code PIX de uma cobrança já criada.
     */
    public function getPixQrCode(string $gatewayPaymentId): array;

    /**
     * Cria uma assinatura recorrente (ex: mensal no cartão).
     * Retorna os dados da assinatura no gateway.
     */
    public function createSubscription(array $data): array;

    /**
     * Cancela uma assinatura recorrente no gateway.
     */
    public function cancelSubscription(string $gatewaySubscriptionId): bool;

    /**
     * Valida e interpreta um webhook recebido do gateway.
     * Retorna um formato padronizado (evento + dados), independente do gateway.
     */
    public function parseWebhook(array $payload): array;
}
