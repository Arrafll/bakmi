<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'min_order',
        'max_uses',
        'used_count',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'valid_from'     => 'date',
        'valid_until'    => 'date',
        'is_active'      => 'boolean',
        'discount_value' => 'float',
        'min_order'      => 'float',
    ];

    public function isValid(float $orderTotal = 0): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $today = now()->toDateString();

        if ($this->valid_from && $this->valid_from->toDateString() > $today) {
            return false;
        }

        if ($this->valid_until && $this->valid_until->toDateString() < $today) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        if ($orderTotal < $this->min_order) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $orderTotal): float
    {
        if ($this->discount_type === 'percentage') {
            return round($orderTotal * ($this->discount_value / 100), 2);
        }

        return min($this->discount_value, $orderTotal);
    }
}
