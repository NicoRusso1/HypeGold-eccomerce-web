<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    /** @use HasFactory<\Database\Factories\CouponFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'expires_at',
        'usage_limit',
        'times_used',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'expires_at' => 'datetime',
            'usage_limit' => 'integer',
            'times_used' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * Motivo por el cual el cupón no es válido en este momento, o null si es válido.
     */
    public function invalidReason(): ?string
    {
        if (! $this->active) {
            return 'Este cupón ya no está disponible.';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'Este cupón venció.';
        }

        if ($this->usage_limit !== null && $this->times_used >= $this->usage_limit) {
            return 'Este cupón alcanzó su límite de usos.';
        }

        return null;
    }

    public function isValid(): bool
    {
        return $this->invalidReason() === null;
    }

    /**
     * Calcula el descuento en pesos para un subtotal dado, sin dejarlo negativo.
     */
    public function calculateDiscount(float $subtotal): float
    {
        $descuento = $this->type === 'percentage'
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        return round(min($descuento, $subtotal), 2);
    }
}
