<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'total' => (float) $this->total,
            'discount' => (float) $this->discount,
            'coupon_code' => $this->coupon_code,
            'created_at' => $this->created_at?->toIso8601String(),
            'shipping' => [
                'label' => $this->shipping_label,
                'street' => $this->shipping_street,
                'city' => $this->shipping_city,
                'province' => $this->shipping_province,
                'postal_code' => $this->shipping_postal_code,
                'phone' => $this->shipping_phone,
            ],
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'variant_talle' => $item->variant_talle,
                'sku' => $item->sku,
                'unit_price' => (float) $item->unit_price,
                'quantity' => $item->quantity,
                'subtotal' => (float) $item->subtotal,
            ])),
        ];
    }
}
