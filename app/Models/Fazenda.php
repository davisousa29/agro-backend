<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Fazenda extends Model
{
    use HasUuids;

    protected $fillable = [
        'fazendeiro_id',
        'name',
        'area_hectares',
        'address',
        'inscricao_estadual',
        'city',
        'state',
    ];

    // ── Relacionamentos ───────────────────────────────────────────────────────

    public function fazendeiro()
    {
        // segundo parâmetro = chave estrangeira na tabela fazendas
        // terceiro parâmetro = chave primária na tabela users
        return $this->belongsTo(User::class, 'fazendeiro_id', 'id');
    }
}
