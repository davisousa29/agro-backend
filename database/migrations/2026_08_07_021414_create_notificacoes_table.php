<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacoes', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Quem recebe a notificação
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Tipo — define ícone/cor no app (contrato_aceito, contrato_recusado, etc.)
            $table->string('tipo');

            // Conteúdo pronto para exibir
            $table->string('titulo');
            $table->text('mensagem');

            // Para onde navegar ao tocar: { "rota": "contrato", "id": "uuid" }
            $table->json('dados')->nullable();

            // Estado de leitura
            $table->boolean('lida')->default(false);
            $table->timestamp('lida_em')->nullable();

            $table->timestamps();

            // Índices — notificações só acumulam, então índice desde o início
            $table->index(['user_id', 'lida'], 'idx_notif_user_lida');
            $table->index('created_at', 'idx_notif_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacoes');
    }
};
