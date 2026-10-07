<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasUuids;

    protected $fillable = [
        'slug',
        'name',
        'price',
        'days',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price'      => 'decimal:2',
        'days'       => 'integer',
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Apenas planos ativos, na ordem de exibição.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
