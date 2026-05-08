<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Raca extends Model
{
    use HasUuids;

    protected $table = 'racas';

    protected $fillable = [
        'especie_id',
        'nome',
        'grupo',
        'aptidao',
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
