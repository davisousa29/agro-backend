<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredientes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome');
            $table->string('tipo');                         // concentrado, volumoso, mineral, aditivo
            $table->string('grupo')->nullable();            // energia, proteina, mineral, volumoso_principal
            $table->string('fonte')->nullable();            // Embrapa, CQBAL, personalizado

            // Composição nutricional — todos em % da matéria seca, exceto MS
            $table->decimal('ms_pct', 6, 2)->default(0);   // Matéria Seca %
            $table->decimal('ndt_pct', 6, 2)->default(0);  // Nutrientes Digestíveis Totais %
            $table->decimal('pb_pct', 6, 2)->default(0);   // Proteína Bruta %
            $table->decimal('pdr_pct', 6, 2)->default(0);  // Proteína Degradável no Rúmen % da PB
            $table->decimal('pndr_pct', 6, 2)->default(0); // Proteína Não Degradável no Rúmen % da PB
            $table->decimal('fdn_pct', 6, 2)->default(0);  // Fibra em Detergente Neutro %
            $table->decimal('fda_pct', 6, 2)->default(0);  // Fibra em Detergente Ácido %
            $table->decimal('ee_pct', 6, 2)->default(0);   // Extrato Etéreo %
            $table->decimal('ca_pct', 6, 2)->default(0);   // Cálcio %
            $table->decimal('p_pct', 6, 2)->default(0);    // Fósforo %
            $table->decimal('mg_pct', 6, 2)->default(0);   // Magnésio %
            $table->decimal('k_pct', 6, 2)->default(0);    // Potássio %
            $table->decimal('na_pct', 6, 2)->default(0);   // Sódio %
            $table->decimal('s_pct', 6, 2)->default(0);    // Enxofre %

            // Energia
            $table->decimal('elm_mcal', 6, 3)->default(0); // Energia Líquida Mantença Mcal/kg MS
            $table->decimal('elg_mcal', 6, 3)->default(0); // Energia Líquida Ganho Mcal/kg MS
            $table->decimal('ed_mcal', 6, 3)->default(0);  // Energia Digestível Mcal/kg MS
            $table->decimal('em_mcal', 6, 3)->default(0);  // Energia Metabolizável Mcal/kg MS

            // Preço base (pode ser sobrescrito por fazenda)
            $table->decimal('preco_kg', 8, 4)->nullable();  // R$/kg

            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredientes');
    }
};
