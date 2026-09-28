<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'image' => optional($this->images->first())->url,
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
