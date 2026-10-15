<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'author_name' => $this->faker->firstName().' '.mb_substr($this->faker->lastName(), 0, 1).'.',
            'rating' => $this->faker->numberBetween(4, 5),
            'comment' => $this->faker->sentence(12),
        ];
    }
}
