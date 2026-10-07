<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Cópia dos dias do plano no momento da cobrança
            $table->unsignedInteger('days')->nullable();
        });

        // Preenche as cobranças antigas
        DB::table('payments')->whereNull('days')->update([
            'days' => DB::raw("CASE plan WHEN 'monthly' THEN 30 WHEN 'semiannual' THEN 180 WHEN 'annual' THEN 365 END"),
        ]);
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('days');
        });
    }
};
