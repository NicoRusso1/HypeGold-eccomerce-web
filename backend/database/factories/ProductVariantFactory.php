<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => ProductFactory::new(),
            'talle' => $this->faker->randomElement([null, 'Único', '16', '18', '20']),
            'sku' => strtoupper(Str::random(8)),
            'stock' => $this->faker->numberBetween(0, 30),
            'extra_price' => 0,
        ];
    }

    public function sinStock(): static
    {
        return $this->state(fn (array $attributes) => ['stock' => 0]);
    }
}
