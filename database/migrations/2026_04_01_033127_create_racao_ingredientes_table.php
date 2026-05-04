<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('racao_ingredientes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('programa_id');
            $table->foreign('programa_id')->references('id')->on('programas_racao')->onDelete('cascade');
            $table->uuid('ingrediente_id');
            $table->foreign('ingrediente_id')->references('id')->on('ingredientes')->onDelete('cascade');

            // Classificação na dieta
            $table->string('tipo');                              // volumoso_principal, volumoso_suplementar, concentrado
            $table->integer('ordem')->default(0);

            // Proporção na dieta
            $table->decimal('proporcao_pct', 6, 2)->default(0); // % na matéria natural
            $table->decimal('preco_kg_local', 8, 4)->nullable(); // Preço local sobrescreve o padrão

            // Consumo calculado por animal/dia
            $table->decimal('consumo_mn_kg', 8, 3)->default(0);  // Matéria natural kg/animal/dia
            $table->decimal('consumo_ms_kg', 8, 3)->default(0);  // Matéria seca kg/animal/dia

            // Contribuição nutricional calculada
            $table->decimal('contrib_ndt_kg', 8, 3)->default(0);
            $table->decimal('contrib_pb_g', 8, 2)->default(0);
            $table->decimal('contrib_pdr_g', 8, 2)->default(0);
            $table->decimal('contrib_elm_mcal', 8, 3)->default(0);
            $table->decimal('contrib_elg_mcal', 8, 3)->default(0);
            $table->decimal('contrib_ca_g', 8, 2)->default(0);
            $table->decimal('contrib_p_g', 8, 2)->default(0);

            // Custo
            $table->decimal('custo_animal_dia', 10, 4)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('racao_ingredientes');
    }
};
