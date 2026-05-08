<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Especie extends Model
{
    use HasUuids;

    protected $table = 'especies';

    protected $fillable = [
        'nome',
        'nome_cientifico',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    // ── Relacionamentos ───────────────────────────────────────────────────────

    public function racas()
    {
        return $this->hasMany(Raca::class);
    }

    public function categorias()
    {
        return $this->hasMany(CategoriaAnimal::class);
    }

    public function sistemas()
    {
        return $this->hasMany(SistemaProducao::class);
    }
}
