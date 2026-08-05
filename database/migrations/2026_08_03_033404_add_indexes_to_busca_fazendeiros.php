<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Índice composto para o filtro base (role + active sempre usados juntos)
            $table->index(['role', 'active'], 'idx_users_role_active');
            // Índice para busca por username
            $table->index('username', 'idx_users_username');
        });

        Schema::table('fazendeiro_profiles', function (Blueprint $table) {
            $table->index('location_state', 'idx_fazendeiro_location_state');
            $table->index('location_city', 'idx_fazendeiro_location_city');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role_active');
            $table->dropIndex('idx_users_username');
        });

        Schema::table('fazendeiro_profiles', function (Blueprint $table) {
            $table->dropIndex('idx_fazendeiro_location_state');
            $table->dropIndex('idx_fazendeiro_location_city');
        });
    }
};
