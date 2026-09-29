<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Subtotal del carrito (sin descuentos), sumando precio unitario por cantidad.
     */
    public function calculateSubtotal(): float
    {
        $this->loadMissing('items.variant.product');

        return $this->items->sum(function (CartItem $item) {
            $precio = (float) $item->variant->product->base_price + (float) $item->variant->extra_price;

            return $precio * $item->quantity;
        });
    }
}
