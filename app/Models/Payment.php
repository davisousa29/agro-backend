<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'gateway',
        'gateway_payment_id',
        'plan',
        'days',
        'payment_method',
        'value',
        'status',
        'due_date',
        'invoice_url',
        'paid_at',
    ];

    protected $casts = [
        'value'    => 'decimal:2',
        'days'     => 'integer',
        'due_date' => 'date',
        'paid_at'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
