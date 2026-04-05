<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fazendas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fazendeiro_id');
            $table->foreign('fazendeiro_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name');
            $table->decimal('area_hectares', 10, 2)->nullable();
            $table->string('address')->nullable();
            $table->string('inscricao_estadual')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fazendas');
    }
};
