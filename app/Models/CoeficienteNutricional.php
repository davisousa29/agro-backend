<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CoeficienteNutricional extends Model
{
    use HasUuids;

    protected $table = 'coeficientes_nutricionais';

    protected $fillable = [
        'especie_id',
        'raca_id',
        'categoria_id',
        'sistema_id',
        'nutriente',
        'unidade',
        'formula_tipo',
        'referencia',
        'coef_a',
        'coef_b',
        'coef_c',
        'variavel_base',
        'observacao',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'coef_a' => 'decimal:8',
            'coef_b' => 'decimal:8',
            'coef_c' => 'decimal:8',
            'ativo'  => 'boolean',
        ];
    }

    public function especie()
    {
        return $this->belongsTo(Especie::class);
    }

    public function raca()
    {
        return $this->belongsTo(Raca::class);
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaAnimal::class, 'categoria_id');
    }

    public function sistema()
    {
        return $this->belongsTo(SistemaProducao::class, 'sistema_id');
    }
}
