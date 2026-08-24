<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracoes_2fa', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Cada usuário tem no máximo uma configuração de 2FA
            $table->uuid('user_id')->unique();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Estado
            $table->boolean('ativo')->default(false);

            // Método preferido: 'authenticator' ou 'email'
            $table->string('metodo')->nullable();

            // Segredo TOTP (criptografado via cast no Model)
            $table->text('secret')->nullable();

            // Códigos de recuperação/backup (criptografados via cast no Model)
            $table->text('codigos_recuperacao')->nullable();

            // Quando o usuário confirmou a ativação (prova de que configurou certo)
            $table->timestamp('confirmado_em')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes_2fa');
    }
};
