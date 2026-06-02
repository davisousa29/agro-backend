<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProjecaoAnimal extends Model
{
    use HasUuids;

    protected $table = 'projecao_animais';

    protected $fillable = [
        'projecao_id',
        'numero_animal',
        'prenhez',
        'peso_kg',
        'quantidade',
        'arrobas',
        'valor_unitario',
        'valor_total',
        'ordem',
    ];

    protected $casts = [
        'prenhez'       => 'boolean',
        'peso_kg'       => 'decimal:2',
        'arrobas'       => 'decimal:3',
        'valor_unitario'=> 'decimal:2',
        'valor_total'   => 'decimal:2',
    ];

    public function projecao()
    {
        return $this->belongsTo(ProjecaoVenda::class, 'projecao_id');
    }
}
