<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // TODO: valores provisórios — ajustar após o estudo de preços
        $plans = [
            ['slug' => 'monthly',    'name' => 'Mensal',    'price' => 19.90,  'days' => 30,  'sort_order' => 1],
            ['slug' => 'semiannual', 'name' => 'Semestral', 'price' => 99.90,  'days' => 180, 'sort_order' => 2],
            ['slug' => 'annual',     'name' => 'Anual',     'price' => 179.90, 'days' => 365, 'sort_order' => 3],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
