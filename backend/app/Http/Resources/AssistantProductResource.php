<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssistantProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $variantConStock = $this->variants->firstWhere('stock', '>', 0);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'base_price' => (float) $this->base_price,
            'image' => optional($this->images->first())->url,
            'variant_id' => $variantConStock?->id,
        ];
    }
}
