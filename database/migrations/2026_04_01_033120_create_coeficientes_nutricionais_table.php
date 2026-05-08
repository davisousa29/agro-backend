<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coeficientes_nutricionais', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('especie_id');
            $table->foreign('especie_id')->references('id')->on('especies')->onDelete('cascade');
            $table->uuid('raca_id');
            $table->foreign('raca_id')->references('id')->on('racas')->onDelete('cascade');
            $table->uuid('categoria_id');
            $table->foreign('categoria_id')->references('id')->on('categorias_animais')->onDelete('cascade');
            $table->uuid('sistema_id');
            $table->foreign('sistema_id')->references('id')->on('sistemas_producao')->onDelete('cascade');

            // Identificação do nutriente e fórmula
            $table->string('nutriente');        // ELm, ELg, CMS, PDR, PLg, Ca, P...
            $table->string('unidade');          // Mcal, g, kg, %
            $table->string('formula_tipo');     // linear, exponencial, lookup
            $table->string('referencia');       // BR-CORTE 2016, NRC 2016, etc.

            // Coeficientes da fórmula: resultado = a × variavel^b + c
            $table->decimal('coef_a', 15, 8)->default(0);
            $table->decimal('coef_b', 15, 8)->default(1);
            $table->decimal('coef_c', 15, 8)->default(0);

            // Variável base da fórmula
            $table->string('variavel_base');    // PCVZeq, PCVZ, GMD, CMS, PV

            // Notas
            $table->text('observacao')->nullable();

            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coeficientes_nutricionais');
    }
};
