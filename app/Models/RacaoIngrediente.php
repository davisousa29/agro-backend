<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RacaoIngrediente extends Model
{
    use HasUuids;

    protected $table = 'racao_ingredientes';

    protected $fillable = [
        'programa_id', 'ingrediente_id',
        'tipo', 'ordem',
        'proporcao_pct', 'preco_kg_local',
        'consumo_mn_kg', 'consumo_ms_kg',
        'contrib_ndt_kg', 'contrib_pb_g', 'contrib_pdr_g',
        'contrib_elm_mcal', 'contrib_elg_mcal',
        'contrib_ca_g', 'contrib_p_g',
        'custo_animal_dia',
    ];

    public function programa()
    {
        return $this->belongsTo(ProgramaRacao::class, 'programa_id');
    }

    public function ingrediente()
    {
        return $this->belongsTo(Ingrediente::class);
    }
}
