<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programas_racao', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('contrato_id');
            $table->foreign('contrato_id')->references('id')->on('contratos')->onDelete('cascade');
            $table->uuid('criado_por');
            $table->foreign('criado_por')->references('id')->on('users')->onDelete('cascade');

            // Identificação
            $table->string('nome');
            $table->enum('status', ['rascunho', 'ativo', 'encerrado'])->default('rascunho');

            // Dados do animal
            $table->uuid('especie_id');
            $table->foreign('especie_id')->references('id')->on('especies')->onDelete('cascade');
            $table->uuid('raca_id');
            $table->foreign('raca_id')->references('id')->on('racas')->onDelete('cascade');
            $table->uuid('categoria_id');
            $table->foreign('categoria_id')->references('id')->on('categorias_animais')->onDelete('cascade');
            $table->uuid('sistema_id');
            $table->foreign('sistema_id')->references('id')->on('sistemas_producao')->onDelete('cascade');

            // Parâmetros zootécnicos
            $table->decimal('peso_inicial_kg', 8, 2);
            $table->decimal('peso_final_kg', 8, 2);
            $table->decimal('peso_medio_kg', 8, 2);
            $table->decimal('gmd_kg', 6, 3);               // Ganho médio diário desejado
            $table->integer('quantidade_animais')->default(1);

            // Exigências calculadas (resultado do cálculo)
            $table->decimal('exig_cms_kg', 8, 3)->nullable();    // Consumo MS kg/dia
            $table->decimal('exig_ndt_kg', 8, 3)->nullable();    // NDT kg/dia
            $table->decimal('exig_pb_g', 8, 2)->nullable();      // PB g/dia
            $table->decimal('exig_elm_mcal', 8, 3)->nullable();  // ELm Mcal/dia
            $table->decimal('exig_elg_mcal', 8, 3)->nullable();  // ELg Mcal/dia
            $table->decimal('exig_ca_g', 8, 2)->nullable();      // Ca g/dia
            $table->decimal('exig_p_g', 8, 2)->nullable();       // P g/dia

            // Resultado da formulação
            $table->decimal('custo_animal_dia', 10, 4)->nullable();  // R$/animal/dia
            $table->decimal('gmd_esperado_kg', 6, 3)->nullable();    // GMD esperado
            $table->string('referencia_nutricional')->nullable();     // BR-CORTE 2016

            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->text('observacoes')->nullable();

            // Individual ou lote
            $table->enum('tipo_aplicacao', ['individual', 'lote'])->default('lote');
            $table->unsignedBigInteger('lote_id')->nullable();
            $table->foreign('lote_id')->references('id')->on('lotes')->onDelete('set null');
            $table->string('identificacao_animal')->nullable(); // brinco/sisbov se individual

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programas_racao');
    }
};
