<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminStockResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'talle' => $this->talle,
            'sku' => $this->sku,
            'stock' => $this->stock,
            'low_stock' => $this->stock <= config('shop.low_stock_threshold'),
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'category' => $this->product->category?->name,
            ],
        ];
    }
}
