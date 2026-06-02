<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projecoes_venda', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('criado_por');
            $table->foreign('criado_por')->references('id')->on('users')->onDelete('cascade');

            $table->uuid('contrato_id')->nullable();
            $table->foreign('contrato_id')->references('id')->on('contratos')->onDelete('set null');

            // Identificação
            $table->string('nome');
            $table->enum('status', ['rascunho', 'finalizado'])->default('rascunho');

            // Modalidade de precificação
            $table->enum('modalidade', ['arroba', 'kg', 'cabeca']);
            $table->decimal('preco_unitario', 10, 2);

            // Resumo calculado
            $table->integer('total_animais')->default(0);
            $table->integer('total_vazias')->default(0);
            $table->integer('total_prenhas')->default(0);
            $table->decimal('total_peso_kg', 10, 2)->default(0);
            $table->decimal('total_arrobas', 10, 3)->nullable();
            $table->decimal('media_peso_vazias', 10, 2)->nullable();
            $table->decimal('valor_total', 10, 2)->default(0);

            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        Schema::create('projecao_animais', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('projecao_id');
            $table->foreign('projecao_id')->references('id')->on('projecoes_venda')->onDelete('cascade');

            $table->string('numero_animal')->nullable();
            $table->boolean('prenhez')->default(false);
            $table->decimal('peso_kg', 8, 2)->nullable();
            $table->integer('quantidade')->default(1);

            // Calculados
            $table->decimal('arrobas', 8, 3)->nullable();
            $table->decimal('valor_unitario', 10, 2)->default(0);
            $table->decimal('valor_total', 10, 2)->default(0);

            $table->integer('ordem')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projecao_animais');
        Schema::dropIfExists('projecoes_venda');
    }
};
