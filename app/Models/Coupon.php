<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_order', 'max_uses', 'used_count', 'is_active', 'expires_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order' => 'decimal:2',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isValid(float $orderTotal = 0): bool
    {
        if (!$this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->max_uses && $this->used_count >= $this->max_uses) return false;
        if ($orderTotal < $this->min_order) return false;
        return true;
    }

    public function calculateDiscount(float $total): float
    {
        if ($this->type === 'percent') {
            return round($total * ($this->value / 100), 2);
        }
        return min((float) $this->value, $total);
    }
}
