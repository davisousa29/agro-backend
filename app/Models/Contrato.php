<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Contrato extends Model
{
    use HasUuids;

    protected $fillable = [
        'consultor_id',
        'fazendeiro_id',
        'fazenda_id',
        'status',
        'start_date',
        'end_date',
        'value',
        'scope_description',
        'file_url',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
            'value'      => 'decimal:2',
        ];
    }

    // ── Relacionamentos ───────────────────────────────────────────────────────

    public function consultor()
    {
        return $this->belongsTo(User::class, 'consultor_id', 'id');
    }

    public function fazendeiro()
    {
        return $this->belongsTo(User::class, 'fazendeiro_id', 'id');
    }

    public function fazenda()
    {
        return $this->belongsTo(Fazenda::class, 'fazenda_id', 'id');
    }
}
