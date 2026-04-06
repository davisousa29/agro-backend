<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('consultor_id');
            $table->foreign('consultor_id')->references('id')->on('users')->onDelete('cascade');
            $table->uuid('fazendeiro_id');
            $table->foreign('fazendeiro_id')->references('id')->on('users')->onDelete('cascade');
            $table->uuid('fazenda_id');
            $table->foreign('fazenda_id')->references('id')->on('fazendas')->onDelete('cascade');
            $table->enum('status', ['pendente', 'ativo', 'encerrado', 'cancelado'])->default('pendente');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('value', 10, 2)->nullable();
            $table->text('scope_description')->nullable();
            $table->string('file_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
