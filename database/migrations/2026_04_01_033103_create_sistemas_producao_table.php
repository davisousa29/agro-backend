<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sistemas_producao', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('especie_id');
            $table->foreign('especie_id')->references('id')->on('especies')->onDelete('cascade');
            $table->string('nome');                    // Pasto, Confinamento, Semi-confinamento
            $table->string('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sistemas_producao');
    }
};
