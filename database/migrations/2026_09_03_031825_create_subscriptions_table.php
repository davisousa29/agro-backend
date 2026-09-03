<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Um usuário tem uma assinatura
            $table->uuid('user_id')->unique();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Estado: trial, active, expired, canceled, lifetime
            $table->string('status')->default('trial');

            // Plano: monthly, semiannual, annual (nulo durante o trial)
            $table->string('plan')->nullable();

            // Método: pix, card (nulo durante o trial)
            $table->string('payment_method')->nullable();

            // Datas de vigência
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');

            // Bloqueio e período de graça
            $table->timestamp('blocked_at')->nullable();
            $table->timestamp('grace_ends_at')->nullable();

            // Já foi pagante alguma vez (define graça de 7 ou 30 dias)
            $table->boolean('is_subscriber')->default(false);

            // Referências ao gateway (Asaas) — preenchidas na integração
            $table->string('gateway_customer_id')->nullable();
            $table->string('gateway_subscription_id')->nullable();

            $table->timestamps();

            // Índice para os jobs que varrem por status e datas
            $table->index('status', 'idx_subscriptions_status');
            $table->index('expires_at', 'idx_subscriptions_expires');
            $table->index('grace_ends_at', 'idx_subscriptions_grace');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
