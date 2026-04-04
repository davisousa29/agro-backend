<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ConsultorProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'crea_number',
        'specialization',
        'bio',
        'service_rate',
        'location_state',
        'location_city',
    ];

    // ── Relacionamentos ───────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
