<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_id',
        'status',
        'total',
        'coupon_id',
        'coupon_code',
        'discount',
        'shipping_label',
        'shipping_street',
        'shipping_city',
        'shipping_province',
        'shipping_postal_code',
        'shipping_phone',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total' => 'decimal:2',
            'discount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Cambia el estado del pedido si la transición es válida.
     * Devuelve false (sin guardar) si la transición no está permitida.
     */
    public function transicionarA(OrderStatus $nuevoEstado): bool
    {
        if (! $this->status->puedeTransicionarA($nuevoEstado)) {
            return false;
        }

        $this->status = $nuevoEstado;

        return $this->save();
    }
}
