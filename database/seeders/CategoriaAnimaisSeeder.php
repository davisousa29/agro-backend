<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CategoriaAnimaisSeeder extends Seeder
{
    public function run(): void
    {
        $bovino  = DB::table('especies')->where('nome', 'Bovino')->first();
        $suino   = DB::table('especies')->where('nome', 'Suíno')->first();
        $ave     = DB::table('especies')->where('nome', 'Ave')->first();
        $ovino   = DB::table('especies')->where('nome', 'Ovino')->first();
        $caprino = DB::table('especies')->where('nome', 'Caprino')->first();
        $equino  = DB::table('especies')->where('nome', 'Equino')->first();

        $categorias = [
            // ── Bovinos ───────────────────────────────────────────────────────
            ['especie_id' => $bovino->id, 'nome' => 'Macho não castrado', 'sexo' => 'macho', 'castrado' => false, 'fase' => 'crescimento'],
            ['especie_id' => $bovino->id, 'nome' => 'Macho castrado',     'sexo' => 'macho', 'castrado' => true,  'fase' => 'crescimento'],
            ['especie_id' => $bovino->id, 'nome' => 'Fêmea',              'sexo' => 'femea', 'castrado' => false, 'fase' => 'crescimento'],
            ['especie_id' => $bovino->id, 'nome' => 'Novilha prenhe',     'sexo' => 'femea', 'castrado' => false, 'fase' => 'reproducao'],
            ['especie_id' => $bovino->id, 'nome' => 'Vaca em lactação',   'sexo' => 'femea', 'castrado' => false, 'fase' => 'lactacao'],
            ['especie_id' => $bovino->id, 'nome' => 'Vaca seca',          'sexo' => 'femea', 'castrado' => false, 'fase' => 'manutencao'],
            ['especie_id' => $bovino->id, 'nome' => 'Bezerro',            'sexo' => 'macho', 'castrado' => false, 'fase' => 'cria'],
            ['especie_id' => $bovino->id, 'nome' => 'Bezerra',            'sexo' => 'femea', 'castrado' => false, 'fase' => 'cria'],

            // ── Suínos ────────────────────────────────────────────────────────
            ['especie_id' => $suino->id, 'nome' => 'Leitão',             'sexo' => 'macho', 'castrado' => false, 'fase' => 'cria'],
            ['especie_id' => $suino->id, 'nome' => 'Macho em crescimento','sexo' => 'macho', 'castrado' => true,  'fase' => 'crescimento'],
            ['especie_id' => $suino->id, 'nome' => 'Fêmea em crescimento','sexo' => 'femea', 'castrado' => false, 'fase' => 'crescimento'],
            ['especie_id' => $suino->id, 'nome' => 'Porca gestante',     'sexo' => 'femea', 'castrado' => false, 'fase' => 'reproducao'],
            ['especie_id' => $suino->id, 'nome' => 'Porca lactante',     'sexo' => 'femea', 'castrado' => false, 'fase' => 'lactacao'],
            ['especie_id' => $suino->id, 'nome' => 'Varrão',             'sexo' => 'macho', 'castrado' => false, 'fase' => 'reproducao'],

            // ── Aves ──────────────────────────────────────────────────────────
            ['especie_id' => $ave->id, 'nome' => 'Pinto inicial',        'sexo' => 'macho', 'castrado' => false, 'fase' => 'cria'],
            ['especie_id' => $ave->id, 'nome' => 'Frango crescimento',   'sexo' => 'macho', 'castrado' => false, 'fase' => 'crescimento'],
            ['especie_id' => $ave->id, 'nome' => 'Frango terminação',    'sexo' => 'macho', 'castrado' => false, 'fase' => 'terminacao'],
            ['especie_id' => $ave->id, 'nome' => 'Poedeira inicial',     'sexo' => 'femea', 'castrado' => false, 'fase' => 'cria'],
            ['especie_id' => $ave->id, 'nome' => 'Poedeira produção',    'sexo' => 'femea', 'castrado' => false, 'fase' => 'producao'],

            // ── Ovinos ────────────────────────────────────────────────────────
            ['especie_id' => $ovino->id, 'nome' => 'Cordeiro',           'sexo' => 'macho', 'castrado' => false, 'fase' => 'cria'],
            ['especie_id' => $ovino->id, 'nome' => 'Borrego castrado',   'sexo' => 'macho', 'castrado' => true,  'fase' => 'crescimento'],
            ['especie_id' => $ovino->id, 'nome' => 'Ovelha gestante',    'sexo' => 'femea', 'castrado' => false, 'fase' => 'reproducao'],
            ['especie_id' => $ovino->id, 'nome' => 'Ovelha lactante',    'sexo' => 'femea', 'castrado' => false, 'fase' => 'lactacao'],
            ['especie_id' => $ovino->id, 'nome' => 'Carneiro reprodutor','sexo' => 'macho', 'castrado' => false, 'fase' => 'reproducao'],

            // ── Caprinos ──────────────────────────────────────────────────────
            ['especie_id' => $caprino->id, 'nome' => 'Cabrito',          'sexo' => 'macho', 'castrado' => false, 'fase' => 'cria'],
            ['especie_id' => $caprino->id, 'nome' => 'Cabra gestante',   'sexo' => 'femea', 'castrado' => false, 'fase' => 'reproducao'],
            ['especie_id' => $caprino->id, 'nome' => 'Cabra lactante',   'sexo' => 'femea', 'castrado' => false, 'fase' => 'lactacao'],
            ['especie_id' => $caprino->id, 'nome' => 'Bode reprodutor',  'sexo' => 'macho', 'castrado' => false, 'fase' => 'reproducao'],

            // ── Equinos ───────────────────────────────────────────────────────
            ['especie_id' => $equino->id, 'nome' => 'Potro',             'sexo' => 'macho', 'castrado' => false, 'fase' => 'cria'],
            ['especie_id' => $equino->id, 'nome' => 'Cavalo em trabalho','sexo' => 'macho', 'castrado' => false, 'fase' => 'manutencao'],
            ['especie_id' => $equino->id, 'nome' => 'Égua gestante',     'sexo' => 'femea', 'castrado' => false, 'fase' => 'reproducao'],
            ['especie_id' => $equino->id, 'nome' => 'Égua lactante',     'sexo' => 'femea', 'castrado' => false, 'fase' => 'lactacao'],
        ];

        foreach ($categorias as $categoria) {
            DB::table('categorias_animais')->insert([
                'id'         => Str::uuid(),
                'especie_id' => $categoria['especie_id'],
                'nome'       => $categoria['nome'],
                'sexo'       => $categoria['sexo'],
                'castrado'   => $categoria['castrado'],
                'fase'       => $categoria['fase'],
                'ativo'      => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
