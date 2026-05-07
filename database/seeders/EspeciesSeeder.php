<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class EspeciesSeeder extends Seeder
{
    public function run(): void
    {
        $especies = [
            [
                'id'               => Str::uuid(),
                'nome'             => 'Bovino',
                'nome_cientifico'  => 'Bos taurus / Bos indicus',
                'ativo'            => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'id'               => Str::uuid(),
                'nome'             => 'Suíno',
                'nome_cientifico'  => 'Sus scrofa domesticus',
                'ativo'            => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'id'               => Str::uuid(),
                'nome'             => 'Ave',
                'nome_cientifico'  => 'Gallus gallus domesticus',
                'ativo'            => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'id'               => Str::uuid(),
                'nome'             => 'Ovino',
                'nome_cientifico'  => 'Ovis aries',
                'ativo'            => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'id'               => Str::uuid(),
                'nome'             => 'Caprino',
                'nome_cientifico'  => 'Capra aegagrus hircus',
                'ativo'            => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'id'               => Str::uuid(),
                'nome'             => 'Equino',
                'nome_cientifico'  => 'Equus caballus',
                'ativo'            => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ];

        DB::table('especies')->insert($especies);
    }
}
