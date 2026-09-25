<?php

namespace App\Services\Payment;

use App\Models\User;
use App\Support\Document;
use Illuminate\Support\Facades\Http;

class AsaasGateway implements PaymentGateway
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.asaas.base_url');
        $this->apiKey  = config('services.asaas.api_key');
    }

    /**
     * Cliente HTTP pré-configurado com o header de autenticação do Asaas.
     */
    private function client()
    {
        return Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->baseUrl($this->baseUrl);
    }

    public function createCustomer(User $user): string
    {
        $response = $this->client()->post('/customers', [
            'name'    => $user->name,
            'email'   => $user->email,
            'cpfCnpj' => Document::onlyDigits($user->cpf),
            'mobilePhone' => Document::onlyDigits($user->whatsapp ?? $user->phone ?? ''),
        ]);

        if (!$response->successful()) {
            throw new \Exception('Erro ao criar cliente no Asaas: ' . $response->body());
        }

        return $response->json('id'); // ID do cliente no Asaas (ex: cus_000...)
    }

    public function createPayment(array $data): array
    {
        $response = $this->client()->post('/payments', [
            'customer'          => $data['customer_id'],
            'billingType'       => 'PIX',
            'value'             => $data['value'],
            'dueDate'           => $data['due_date'],
            'description'       => $data['description'] ?? 'Assinatura Colchete',
            'externalReference' => $data['external_reference'] ?? null,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Erro ao criar cobrança PIX no Asaas: ' . $response->body());
        }

        $payment = $response->json();

        return [
            'gateway_payment_id' => $payment['id'],
            'status'             => $payment['status'],
            'value'              => $payment['value'],
            'due_date'           => $payment['dueDate'],
            'invoice_url'        => $payment['invoiceUrl'] ?? null,
        ];
    }

    /**
     * Busca o QR Code PIX de uma cobrança já criada.
     * (O Asaas leva alguns segundos para gerar o QR após criar a cobrança,
     *  por isso é uma chamada separada.)
     */
    public function getPixQrCode(string $gatewayPaymentId): array
    {
        $response = $this->client()->get("/payments/{$gatewayPaymentId}/pixQrCode");

        if (!$response->successful()) {
            throw new \Exception('Erro ao buscar QR Code PIX no Asaas: ' . $response->body());
        }

        $qr = $response->json();

        return [
            'pix_qr_code'    => $qr['encodedImage'] ?? null,  // imagem base64
            'pix_copy_paste' => $qr['payload'] ?? null,        // código copia-e-cola
            'pix_expiration' => $qr['expirationDate'] ?? null,
        ];
    }

    public function createSubscription(array $data): array
    {
        // TODO: implementar assinatura recorrente (cartão)
        throw new \Exception('createSubscription ainda não implementado.');
    }

    public function cancelSubscription(string $gatewaySubscriptionId): bool
    {
        // TODO: implementar cancelamento
        throw new \Exception('cancelSubscription ainda não implementado.');
    }

    public function parseWebhook(array $payload): array
    {
        // TODO: implementar interpretação do webhook
        throw new \Exception('parseWebhook ainda não implementado.');
    }
}
