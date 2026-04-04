<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class FazendeiroProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'cpf_cnpj',
        'location_state',
        'location_city',
    ];

    // ── Relacionamentos ───────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fazendas()
    {
        return $this->hasMany(Fazenda::class, 'fazendeiro_id', 'user_id');
    }
}
