<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Notificacao extends Model
{
    use HasUuids;

    protected $table = 'notificacoes';

    protected $fillable = [
        'user_id',
        'tipo',
        'titulo',
        'mensagem',
        'dados',
        'lida',
        'lida_em',
    ];

    protected $casts = [
        'dados'   => 'array',
        'lida'    => 'boolean',
        'lida_em' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
