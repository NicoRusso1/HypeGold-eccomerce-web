<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'Anillos', 'Cadenas', 'Aros', 'Pulseras', 'Dijes', 'Relojes',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => "Joyas de la categoría {$name}.",
        ];
    }
}
