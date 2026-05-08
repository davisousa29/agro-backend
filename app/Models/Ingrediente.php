<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Ingrediente extends Model
{
    use HasUuids;

    protected $table = 'ingredientes';

    protected $fillable = [
        'nome',
        'tipo',
        'grupo',
        'fonte',
        'ms_pct', 'ndt_pct', 'pb_pct', 'pdr_pct', 'pndr_pct',
        'fdn_pct', 'fda_pct', 'ee_pct',
        'ca_pct', 'p_pct', 'mg_pct', 'k_pct', 'na_pct', 's_pct',
        'elm_mcal', 'elg_mcal', 'ed_mcal', 'em_mcal',
        'preco_kg',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo'    => 'boolean',
            'preco_kg' => 'decimal:4',
        ];
    }
}
