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
            'average_rating' => $this->whenLoaded('reviews', fn () => $this->reviews->isEmpty() ? null : round($this->reviews->avg('rating'), 1)),
            'reviews_count' => $this->whenLoaded('reviews', fn () => $this->reviews->count()),
            'reviews' => $this->whenLoaded('reviews', fn () => $this->reviews->map(fn ($review) => [
                'id' => $review->id,
                'author_name' => $review->author_name,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at?->toIso8601String(),
            ])),
        ];
    }
}
