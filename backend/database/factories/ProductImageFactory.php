<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ProductImage>
 */
class ProductImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => ProductFactory::new(),
            'url' => 'https://picsum.photos/seed/'.$this->faker->uuid().'/600/600',
            'order' => 0,
        ];
    }
}
