<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'material' => $this->material,
            'base_price' => (float) $this->base_price,
            'total_stock' => $this->total_stock,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'variants' => $this->whenLoaded('variants', fn () => $this->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'talle' => $variant->talle,
                'sku' => $variant->sku,
                'stock' => $variant->stock,
                'price' => (float) $this->base_price + (float) $variant->extra_price,
            ])),
        ];
    }
}
