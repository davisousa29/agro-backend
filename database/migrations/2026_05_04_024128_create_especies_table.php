<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('especies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome');                    // Bovino, Suíno, Aves, Ovino
            $table->string('nome_cientifico')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('especies');
    }
};
