<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_animais', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('especie_id');
            $table->foreign('especie_id')->references('id')->on('especies')->onDelete('cascade');
            $table->string('nome');                    // Macho não castrado, Macho castrado, Fêmea
            $table->string('sexo');                    // macho, femea
            $table->boolean('castrado')->default(false);
            $table->string('fase')->nullable();        // crescimento, terminacao, reproducao, cria
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_animais');
    }
};
