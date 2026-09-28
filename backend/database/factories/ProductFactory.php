<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $materials = ['Oro 18k', 'Oro laminado', 'Plata 925', 'Acero quirúrgico'];
        $name = ucfirst($this->faker->unique()->words(2, true));

        return [
            'category_id' => CategoryFactory::new(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(100, 999),
            'description' => $this->faker->sentence(12),
            'material' => $this->faker->randomElement($materials),
            'base_price' => $this->faker->numberBetween(10, 200) * 1000,
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['active' => false]);
    }
}
