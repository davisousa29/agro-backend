<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CategoriaAnimal extends Model
{
    use HasUuids;

    protected $table = 'categorias_animais';

    protected $fillable = [
        'especie_id',
        'nome',
        'sexo',
        'castrado',
        'fase',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'castrado' => 'boolean',
            'ativo'    => 'boolean',
        ];
    }

    public function especie()
    {
        return $this->belongsTo(Especie::class);
    }
}
