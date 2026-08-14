<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CodigoRecuperacao extends Model
{
    use HasUuids;

    protected $table = 'codigos_recuperacao';

    protected $fillable = [
        'email',
        'codigo',
        'expira_em',
        'usado',
        'tentativas',
    ];

    protected $casts = [
        'expira_em' => 'datetime',
        'usado'     => 'boolean',
    ];
}
