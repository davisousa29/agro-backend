<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Metodo2fa extends Model
{
    use HasUuids;

    protected $table = 'metodos_2fa';

    protected $fillable = [
        'chave',
        'nome',
        'descricao',
        'ativo',
        'ordem',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];
}
