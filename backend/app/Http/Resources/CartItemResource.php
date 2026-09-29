<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $variant = $this->variant;
        $product = $variant->product;
        $unitPrice = (float) $product->base_price + (float) $variant->extra_price;

        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $unitPrice * $this->quantity,
            'variant' => [
                'id' => $variant->id,
                'talle' => $variant->talle,
                'stock' => $variant->stock,
            ],
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'image' => optional($product->images->first())->url,
            ],
        ];
    }
}
