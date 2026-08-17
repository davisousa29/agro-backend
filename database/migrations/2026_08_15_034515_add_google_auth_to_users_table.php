<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Identificador único do Google (nulo para contas por email)
            $table->string('google_id')->nullable()->unique()->after('id');

            // Origem da conta: 'email' ou 'google'
            $table->string('auth_provider')->default('email')->after('password');

            // Foto de perfil vinda do Google (opcional)
            $table->string('avatar_url')->nullable()->after('auth_provider');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'auth_provider', 'avatar_url']);
        });
    }
};
