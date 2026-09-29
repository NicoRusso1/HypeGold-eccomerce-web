<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $items = CartItemResource::collection($this->items)->resolve();

        return [
            'id' => $this->id,
            'items' => $items,
            'items_count' => array_sum(array_column($items, 'quantity')),
            'total' => array_sum(array_column($items, 'subtotal')),
        ];
    }
}
