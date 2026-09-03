<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'cpf',
        'phone',
        'username',
        'whatsapp',
        'role',
        'password',
        'google_id',
        'auth_provider',
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'configuracao2fa',
        'subscription',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    // ── JWT ───────────────────────────────────────────────────────────────────

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role,
            'name' => $this->name,
        ];
    }

    // ── Relacionamentos ───────────────────────────────────────────────────────

    public function consultorProfile()
    {
        return $this->hasOne(ConsultorProfile::class);
    }

    public function fazendeiroProfile()
    {
        return $this->hasOne(FazendeiroProfile::class);
    }

    public function fazendas()
    {
        return $this->hasMany(Fazenda::class, 'fazendeiro_id');
    }

    public function configuracao2fa()
    {
        return $this->hasOne(Configuracao2fa::class, 'user_id');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'user_id');
    }
}
