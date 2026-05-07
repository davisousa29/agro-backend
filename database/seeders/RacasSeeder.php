<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RacasSeeder extends Seeder
{
    public function run(): void
    {
        $bovino = DB::table('especies')->where('nome', 'Bovino')->first();
        $suino  = DB::table('especies')->where('nome', 'Suíno')->first();
        $ave    = DB::table('especies')->where('nome', 'Ave')->first();
        $ovino  = DB::table('especies')->where('nome', 'Ovino')->first();
        $caprino = DB::table('especies')->where('nome', 'Caprino')->first();
        $equino = DB::table('especies')->where('nome', 'Equino')->first();

        $racas = [
            // ── Bovinos ───────────────────────────────────────────────────────
            ['especie_id' => $bovino->id, 'nome' => 'Zebuíno',         'grupo' => 'zebu',    'aptidao' => 'corte'],
            ['especie_id' => $bovino->id, 'nome' => 'Cruzado Corte',   'grupo' => 'cruzado', 'aptidao' => 'corte'],
            ['especie_id' => $bovino->id, 'nome' => 'Cruzado Leite',   'grupo' => 'cruzado', 'aptidao' => 'leite'],
            ['especie_id' => $bovino->id, 'nome' => 'Nelore',          'grupo' => 'zebu',    'aptidao' => 'corte'],
            ['especie_id' => $bovino->id, 'nome' => 'Angus',           'grupo' => 'taurino', 'aptidao' => 'corte'],
            ['especie_id' => $bovino->id, 'nome' => 'Brahman',         'grupo' => 'zebu',    'aptidao' => 'corte'],
            ['especie_id' => $bovino->id, 'nome' => 'Gir',             'grupo' => 'zebu',    'aptidao' => 'leite'],
            ['especie_id' => $bovino->id, 'nome' => 'Girolando',       'grupo' => 'cruzado', 'aptidao' => 'leite'],
            ['especie_id' => $bovino->id, 'nome' => 'Holandês',        'grupo' => 'taurino', 'aptidao' => 'leite'],
            ['especie_id' => $bovino->id, 'nome' => 'Simmental',       'grupo' => 'taurino', 'aptidao' => 'dupla'],
            ['especie_id' => $bovino->id, 'nome' => 'Limousin',        'grupo' => 'taurino', 'aptidao' => 'corte'],

            // ── Suínos ────────────────────────────────────────────────────────
            ['especie_id' => $suino->id, 'nome' => 'Landrace',         'grupo' => 'comercial', 'aptidao' => 'corte'],
            ['especie_id' => $suino->id, 'nome' => 'Large White',      'grupo' => 'comercial', 'aptidao' => 'corte'],
            ['especie_id' => $suino->id, 'nome' => 'Duroc',            'grupo' => 'comercial', 'aptidao' => 'corte'],
            ['especie_id' => $suino->id, 'nome' => 'Pietrain',         'grupo' => 'comercial', 'aptidao' => 'corte'],

            // ── Aves ──────────────────────────────────────────────────────────
            ['especie_id' => $ave->id, 'nome' => 'Frango de Corte',    'grupo' => 'corte',    'aptidao' => 'corte'],
            ['especie_id' => $ave->id, 'nome' => 'Poedeira Comercial', 'grupo' => 'postura',  'aptidao' => 'postura'],
            ['especie_id' => $ave->id, 'nome' => 'Peru',               'grupo' => 'corte',    'aptidao' => 'corte'],

            // ── Ovinos ────────────────────────────────────────────────────────
            ['especie_id' => $ovino->id, 'nome' => 'Santa Inês',       'grupo' => 'deslanado', 'aptidao' => 'corte'],
            ['especie_id' => $ovino->id, 'nome' => 'Dorper',           'grupo' => 'deslanado', 'aptidao' => 'corte'],
            ['especie_id' => $ovino->id, 'nome' => 'Morada Nova',      'grupo' => 'deslanado', 'aptidao' => 'corte'],
            ['especie_id' => $ovino->id, 'nome' => 'Merino',           'grupo' => 'lanado',    'aptidao' => 'la'],

            // ── Caprinos ──────────────────────────────────────────────────────
            ['especie_id' => $caprino->id, 'nome' => 'Boer',           'grupo' => 'corte',  'aptidao' => 'corte'],
            ['especie_id' => $caprino->id, 'nome' => 'Saanen',         'grupo' => 'leite',  'aptidao' => 'leite'],
            ['especie_id' => $caprino->id, 'nome' => 'Anglo Nubiana',  'grupo' => 'dupla',  'aptidao' => 'dupla'],

            // ── Equinos ───────────────────────────────────────────────────────
            ['especie_id' => $equino->id, 'nome' => 'Quarto de Milha', 'grupo' => 'trabalho', 'aptidao' => 'trabalho'],
            ['especie_id' => $equino->id, 'nome' => 'Mangalarga',      'grupo' => 'trabalho', 'aptidao' => 'trabalho'],
            ['especie_id' => $equino->id, 'nome' => 'Crioulo',         'grupo' => 'trabalho', 'aptidao' => 'trabalho'],
        ];

        foreach ($racas as $raca) {
            DB::table('racas')->insert([
                'id'         => Str::uuid(),
                'especie_id' => $raca['especie_id'],
                'nome'       => $raca['nome'],
                'grupo'      => $raca['grupo'],
                'aptidao'    => $raca['aptidao'],
                'ativo'      => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
