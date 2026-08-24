<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Configuracao2fa extends Model
{
    use HasUuids;

    protected $table = 'configuracoes_2fa';

    protected $hidden = [
        'secret',
        'codigos_recuperacao',
    ];

    protected $fillable = [
        'user_id',
        'ativo',
        'metodo',
        'secret',
        'codigos_recuperacao',
        'confirmado_em',
    ];

    protected $casts = [
        'ativo'               => 'boolean',
        'confirmado_em'       => 'datetime',
        // Criptografia automática dos campos sensíveis
        'secret'              => 'encrypted',
        'codigos_recuperacao' => 'encrypted:array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
