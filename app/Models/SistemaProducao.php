<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SistemaProducao extends Model
{
    use HasUuids;

    protected $table = 'sistemas_producao';

    protected $fillable = [
        'especie_id',
        'nome',
        'descricao',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function especie()
    {
        return $this->belongsTo(Especie::class);
    }
}
