<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Metodo2fa;

class Metodos2faSeeder extends Seeder
{
    public function run(): void
    {
        $metodos = [
            [
                'chave'     => 'authenticator',
                'nome'      => 'Aplicativo autenticador',
                'descricao' => 'Use um app como Google Authenticator para gerar códigos.',
                'ativo'     => true,
                'ordem'     => 1,
            ],
            [
                'chave'     => 'email',
                'nome'      => 'Email',
                'descricao' => 'Receba um código de verificação no seu email.',
                'ativo'     => true,
                'ordem'     => 2,
            ],
        ];

        foreach ($metodos as $metodo) {
            Metodo2fa::updateOrCreate(
                ['chave' => $metodo['chave']],
                $metodo
            );
        }
    }
}
