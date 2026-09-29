<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
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
}
