<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProjecaoVenda extends Model
{
    use HasUuids;

    protected $table = 'projecoes_venda';

    protected $fillable = [
        'criado_por',
        'contrato_id',
        'nome',
        'status',
        'modalidade',
        'preco_unitario',
        'total_animais',
        'total_vazias',
        'total_prenhas',
        'total_peso_kg',
        'total_arrobas',
        'media_peso_vazias',
        'valor_total',
        'observacoes',
    ];

    protected $casts = [
        'preco_unitario'    => 'decimal:2',
        'total_peso_kg'     => 'decimal:2',
        'total_arrobas'     => 'decimal:3',
        'media_peso_vazias' => 'decimal:2',
        'valor_total'       => 'decimal:2',
    ];

    public function animais()
    {
        return $this->hasMany(ProjecaoAnimal::class, 'projecao_id')->orderBy('ordem');
    }

    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'criado_por');
    }
}
