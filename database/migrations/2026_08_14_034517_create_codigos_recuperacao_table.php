<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('codigos_recuperacao', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // A quem o código pertence
            $table->string('email');

            // Os 6 dígitos, guardados com hash (nunca em texto puro)
            $table->string('codigo');

            // Validade curta
            $table->timestamp('expira_em');

            // Uso único
            $table->boolean('usado')->default(false);

            // Limite de tentativas de digitação (anti força-bruta)
            $table->integer('tentativas')->default(0);

            $table->timestamps();

            // Índice para busca rápida pelo email
            $table->index('email', 'idx_codigos_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codigos_recuperacao');
    }
};
