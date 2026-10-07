<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Gateway e referência da cobrança (ex: pay_...)
            $table->string('gateway')->default('asaas');
            $table->string('gateway_payment_id')->unique();

            // O que foi cobrado
            $table->string('plan');            // monthly, semiannual, annual
            $table->string('payment_method');  // pix, card
            $table->decimal('value', 10, 2);

            // pending, received, overdue, refunded, canceled
            $table->string('status')->default('pending');

            $table->date('due_date')->nullable();
            $table->string('invoice_url')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status'], 'idx_payments_user_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
