<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_notices', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('subscription_id');
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');

            // expiring_3d, expiring_1d, expired, purge_3d, purge_1d
            $table->string('type');

            // Data a que o aviso se refere: o vencimento ou o fim da graça.
            // Quando a assinatura renova, a referência muda e os avisos voltam a valer.
            $table->timestamp('reference_at');

            $table->timestamps();

            $table->unique(['subscription_id', 'type', 'reference_at'], 'uq_subscription_notices');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_notices');
    }
};
