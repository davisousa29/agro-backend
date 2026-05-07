<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EspeciesSeeder::class,
            RacasSeeder::class,
            CategoriaAnimaisSeeder::class,
            SistemasProducaoSeeder::class,
            CoeficientesNutricionaisSeeder::class,
            IngredientesSeeder::class,
        ]);
    }
}
