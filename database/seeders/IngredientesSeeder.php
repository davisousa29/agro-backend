<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class IngredientesSeeder extends Seeder
{
    public function run(): void
    {
        $ingredientes = [
            // ── Energéticos ───────────────────────────────────────────────────
            ['nome' => 'Milho grão moído',         'tipo' => 'concentrado', 'grupo' => 'energia',  'ms_pct' => 87.0, 'ndt_pct' => 88.0, 'pb_pct' => 9.0,  'pdr_pct' => 60.0, 'pndr_pct' => 40.0, 'fdn_pct' => 12.0, 'ee_pct' => 4.0, 'ca_pct' => 0.03, 'p_pct' => 0.28, 'elm_mcal' => 2.05, 'elg_mcal' => 1.39],
            ['nome' => 'Sorgo grão',                'tipo' => 'concentrado', 'grupo' => 'energia',  'ms_pct' => 87.0, 'ndt_pct' => 84.0, 'pb_pct' => 9.5,  'pdr_pct' => 58.0, 'pndr_pct' => 42.0, 'fdn_pct' => 18.0, 'ee_pct' => 3.1, 'ca_pct' => 0.04, 'p_pct' => 0.30, 'elm_mcal' => 1.95, 'elg_mcal' => 1.30],
            ['nome' => 'Trigo farelo',              'tipo' => 'concentrado', 'grupo' => 'energia',  'ms_pct' => 88.0, 'ndt_pct' => 78.0, 'pb_pct' => 16.5, 'pdr_pct' => 72.0, 'pndr_pct' => 28.0, 'fdn_pct' => 43.0, 'ee_pct' => 3.9, 'ca_pct' => 0.12, 'p_pct' => 1.17, 'elm_mcal' => 1.74, 'elg_mcal' => 1.12],
            ['nome' => 'Polpa cítrica',             'tipo' => 'concentrado', 'grupo' => 'energia',  'ms_pct' => 88.0, 'ndt_pct' => 80.0, 'pb_pct' => 7.3,  'pdr_pct' => 68.0, 'pndr_pct' => 32.0, 'fdn_pct' => 24.0, 'ee_pct' => 2.1, 'ca_pct' => 1.80, 'p_pct' => 0.11, 'elm_mcal' => 1.82, 'elg_mcal' => 1.19],
            ['nome' => 'Melaço de cana',            'tipo' => 'concentrado', 'grupo' => 'energia',  'ms_pct' => 75.0, 'ndt_pct' => 74.0, 'pb_pct' => 5.8,  'pdr_pct' => 90.0, 'pndr_pct' => 10.0, 'fdn_pct' => 0.0,  'ee_pct' => 0.1, 'ca_pct' => 0.74, 'p_pct' => 0.08, 'elm_mcal' => 1.60, 'elg_mcal' => 1.01],
            ['nome' => 'Caroço de algodão',         'tipo' => 'concentrado', 'grupo' => 'energia',  'ms_pct' => 91.0, 'ndt_pct' => 90.0, 'pb_pct' => 23.0, 'pdr_pct' => 40.0, 'pndr_pct' => 60.0, 'fdn_pct' => 50.0, 'ee_pct' => 20.0,'ca_pct' => 0.15, 'p_pct' => 0.60, 'elm_mcal' => 2.16, 'elg_mcal' => 1.51],

            // ── Proteicos ─────────────────────────────────────────────────────
            ['nome' => 'Farelo de soja',            'tipo' => 'concentrado', 'grupo' => 'proteina', 'ms_pct' => 88.0, 'ndt_pct' => 84.0, 'pb_pct' => 49.0, 'pdr_pct' => 65.0, 'pndr_pct' => 35.0, 'fdn_pct' => 14.0, 'ee_pct' => 1.5, 'ca_pct' => 0.33, 'p_pct' => 0.71, 'elm_mcal' => 2.08, 'elg_mcal' => 1.43],
            ['nome' => 'Farelo de algodão 38%',     'tipo' => 'concentrado', 'grupo' => 'proteina', 'ms_pct' => 91.0, 'ndt_pct' => 69.0, 'pb_pct' => 38.0, 'pdr_pct' => 55.0, 'pndr_pct' => 45.0, 'fdn_pct' => 30.0, 'ee_pct' => 1.8, 'ca_pct' => 0.17, 'p_pct' => 1.10, 'elm_mcal' => 1.53, 'elg_mcal' => 0.96],
            ['nome' => 'Farelo de girassol',        'tipo' => 'concentrado', 'grupo' => 'proteina', 'ms_pct' => 90.0, 'ndt_pct' => 65.0, 'pb_pct' => 34.0, 'pdr_pct' => 50.0, 'pndr_pct' => 50.0, 'fdn_pct' => 42.0, 'ee_pct' => 2.1, 'ca_pct' => 0.41, 'p_pct' => 0.93, 'elm_mcal' => 1.42, 'elg_mcal' => 0.88],
            ['nome' => 'Ureia pecuária',            'tipo' => 'concentrado', 'grupo' => 'proteina', 'ms_pct' => 99.0, 'ndt_pct' => 0.0,  'pb_pct' => 281.0,'pdr_pct' => 100.0,'pndr_pct' => 0.0,  'fdn_pct' => 0.0,  'ee_pct' => 0.0, 'ca_pct' => 0.0,  'p_pct' => 0.0,  'elm_mcal' => 0.0,  'elg_mcal' => 0.0],
            ['nome' => 'Farinha de peixe',          'tipo' => 'concentrado', 'grupo' => 'proteina', 'ms_pct' => 92.0, 'ndt_pct' => 72.0, 'pb_pct' => 62.0, 'pdr_pct' => 30.0, 'pndr_pct' => 70.0, 'fdn_pct' => 0.0,  'ee_pct' => 9.0, 'ca_pct' => 5.11, 'p_pct' => 2.88, 'elm_mcal' => 1.82, 'elg_mcal' => 1.19],

            // ── Volumosos ─────────────────────────────────────────────────────
            ['nome' => 'Silagem de milho',          'tipo' => 'volumoso', 'grupo' => 'volumoso_principal', 'ms_pct' => 30.0, 'ndt_pct' => 68.0, 'pb_pct' => 8.0,  'pdr_pct' => 65.0, 'pndr_pct' => 35.0, 'fdn_pct' => 55.0, 'ee_pct' => 3.1, 'ca_pct' => 0.26, 'p_pct' => 0.22, 'elm_mcal' => 1.52, 'elg_mcal' => 0.95],
            ['nome' => 'Silagem de sorgo',          'tipo' => 'volumoso', 'grupo' => 'volumoso_principal', 'ms_pct' => 28.0, 'ndt_pct' => 62.0, 'pb_pct' => 7.0,  'pdr_pct' => 65.0, 'pndr_pct' => 35.0, 'fdn_pct' => 62.0, 'ee_pct' => 2.8, 'ca_pct' => 0.30, 'p_pct' => 0.20, 'elm_mcal' => 1.38, 'elg_mcal' => 0.84],
            ['nome' => 'Cana-de-açúcar',            'tipo' => 'volumoso', 'grupo' => 'volumoso_principal', 'ms_pct' => 28.0, 'ndt_pct' => 58.0, 'pb_pct' => 3.5,  'pdr_pct' => 70.0, 'pndr_pct' => 30.0, 'fdn_pct' => 55.0, 'ee_pct' => 0.8, 'ca_pct' => 0.22, 'p_pct' => 0.10, 'elm_mcal' => 1.27, 'elg_mcal' => 0.76],
            ['nome' => 'Capim elefante picado',     'tipo' => 'volumoso', 'grupo' => 'volumoso_principal', 'ms_pct' => 20.0, 'ndt_pct' => 55.0, 'pb_pct' => 8.5,  'pdr_pct' => 70.0, 'pndr_pct' => 30.0, 'fdn_pct' => 72.0, 'ee_pct' => 1.9, 'ca_pct' => 0.38, 'p_pct' => 0.24, 'elm_mcal' => 1.20, 'elg_mcal' => 0.70],
            ['nome' => 'Feno de Tifton',            'tipo' => 'volumoso', 'grupo' => 'volumoso_suplementar','ms_pct' => 88.0, 'ndt_pct' => 56.0, 'pb_pct' => 10.0, 'pdr_pct' => 62.0, 'pndr_pct' => 38.0, 'fdn_pct' => 72.0, 'ee_pct' => 2.2, 'ca_pct' => 0.43, 'p_pct' => 0.22, 'elm_mcal' => 1.22, 'elg_mcal' => 0.67],
            ['nome' => 'Feno de Coast-cross',       'tipo' => 'volumoso', 'grupo' => 'volumoso_suplementar','ms_pct' => 88.0, 'ndt_pct' => 54.0, 'pb_pct' => 9.0,  'pdr_pct' => 60.0, 'pndr_pct' => 40.0, 'fdn_pct' => 74.0, 'ee_pct' => 1.8, 'ca_pct' => 0.38, 'p_pct' => 0.19, 'elm_mcal' => 1.18, 'elg_mcal' => 0.64],
            ['nome' => 'Bagaço de cana',            'tipo' => 'volumoso', 'grupo' => 'volumoso_suplementar','ms_pct' => 91.0, 'ndt_pct' => 38.0, 'pb_pct' => 2.8,  'pdr_pct' => 60.0, 'pndr_pct' => 40.0, 'fdn_pct' => 88.0, 'ee_pct' => 0.9, 'ca_pct' => 0.19, 'p_pct' => 0.07, 'elm_mcal' => 0.76, 'elg_mcal' => 0.30],

            // ── Minerais e Aditivos ───────────────────────────────────────────
            ['nome' => 'Calcário calcítico',        'tipo' => 'mineral', 'grupo' => 'mineral', 'ms_pct' => 99.0, 'ndt_pct' => 0.0, 'pb_pct' => 0.0, 'pdr_pct' => 0.0, 'pndr_pct' => 0.0, 'fdn_pct' => 0.0, 'ee_pct' => 0.0, 'ca_pct' => 38.0, 'p_pct' => 0.0,  'elm_mcal' => 0.0, 'elg_mcal' => 0.0],
            ['nome' => 'Fosfato bicálcico',         'tipo' => 'mineral', 'grupo' => 'mineral', 'ms_pct' => 99.0, 'ndt_pct' => 0.0, 'pb_pct' => 0.0, 'pdr_pct' => 0.0, 'pndr_pct' => 0.0, 'fdn_pct' => 0.0, 'ee_pct' => 0.0, 'ca_pct' => 24.0, 'p_pct' => 18.5, 'elm_mcal' => 0.0, 'elg_mcal' => 0.0],
            ['nome' => 'Sal comum (NaCl)',          'tipo' => 'mineral', 'grupo' => 'mineral', 'ms_pct' => 99.0, 'ndt_pct' => 0.0, 'pb_pct' => 0.0, 'pdr_pct' => 0.0, 'pndr_pct' => 0.0, 'fdn_pct' => 0.0, 'ee_pct' => 0.0, 'ca_pct' => 0.0,  'p_pct' => 0.0,  'elm_mcal' => 0.0, 'elg_mcal' => 0.0],
            ['nome' => 'Núcleo mineral bovino',     'tipo' => 'mineral', 'grupo' => 'mineral', 'ms_pct' => 98.0, 'ndt_pct' => 0.0, 'pb_pct' => 0.0, 'pdr_pct' => 0.0, 'pndr_pct' => 0.0, 'fdn_pct' => 0.0, 'ee_pct' => 0.0, 'ca_pct' => 14.0, 'p_pct' => 7.0,  'elm_mcal' => 0.0, 'elg_mcal' => 0.0],
            ['nome' => 'Bicarbonato de sódio',      'tipo' => 'aditivo', 'grupo' => 'aditivo', 'ms_pct' => 99.0, 'ndt_pct' => 0.0, 'pb_pct' => 0.0, 'pdr_pct' => 0.0, 'pndr_pct' => 0.0, 'fdn_pct' => 0.0, 'ee_pct' => 0.0, 'ca_pct' => 0.0,  'p_pct' => 0.0,  'elm_mcal' => 0.0, 'elg_mcal' => 0.0],
        ];

        foreach ($ingredientes as $ing) {
            DB::table('ingredientes')->insert([
                'id'          => Str::uuid(),
                'nome'        => $ing['nome'],
                'tipo'        => $ing['tipo'],
                'grupo'       => $ing['grupo'],
                'fonte'       => 'Embrapa / CQBAL 3.0',
                'ms_pct'      => $ing['ms_pct'],
                'ndt_pct'     => $ing['ndt_pct'],
                'pb_pct'      => $ing['pb_pct'],
                'pdr_pct'     => $ing['pdr_pct'],
                'pndr_pct'    => $ing['pndr_pct'],
                'fdn_pct'     => $ing['fdn_pct'],
                'fda_pct'     => 0,
                'ee_pct'      => $ing['ee_pct'],
                'ca_pct'      => $ing['ca_pct'],
                'p_pct'       => $ing['p_pct'],
                'mg_pct'      => 0,
                'k_pct'       => 0,
                'na_pct'      => 0,
                's_pct'       => 0,
                'elm_mcal'    => $ing['elm_mcal'],
                'elg_mcal'    => $ing['elg_mcal'],
                'ed_mcal'     => 0,
                'em_mcal'     => 0,
                'preco_kg'    => null,
                'ativo'       => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }
}
