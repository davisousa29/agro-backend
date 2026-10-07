<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('slug')->unique();      // monthly, semiannual, annual
            $table->string('name');                // Mensal, Semestral, Anual
            $table->decimal('price', 10, 2);       // valor cobrado
            $table->unsignedInteger('days');       // dias de acesso liberados
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
