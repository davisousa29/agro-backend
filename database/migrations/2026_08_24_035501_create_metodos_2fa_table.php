<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metodos_2fa', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Identificador técnico do método
            $table->string('chave')->unique();

            // Rótulo e descrição para exibição
            $table->string('nome');
            $table->string('descricao')->nullable();

            // Se o sistema oferece este método atualmente
            $table->boolean('ativo')->default(true);

            // Ordem de exibição
            $table->integer('ordem')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metodos_2fa');
    }
};
