<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProgramaRacao extends Model
{
    use HasUuids;

    protected $table = 'programas_racao';

    protected $fillable = [
        'contrato_id', 'criado_por',
        'nome', 'status',
        'especie_id', 'raca_id', 'categoria_id', 'sistema_id',
        'peso_inicial_kg', 'peso_final_kg', 'peso_medio_kg',
        'gmd_kg', 'quantidade_animais',
        'exig_cms_kg', 'exig_ndt_kg', 'exig_pb_g',
        'exig_elm_mcal', 'exig_elg_mcal', 'exig_ca_g', 'exig_p_g',
        'custo_animal_dia', 'gmd_esperado_kg', 'referencia_nutricional',
        'data_inicio', 'data_fim', 'observacoes',
        'tipo_aplicacao', 'lote_id', 'identificacao_animal',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_fim'    => 'date',
        ];
    }

    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'criado_por');
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

    public function ingredientes()
    {
        return $this->hasMany(RacaoIngrediente::class, 'programa_id');
    }
}
