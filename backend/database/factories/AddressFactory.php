<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => UserFactory::new(),
            'label' => $this->faker->randomElement(['Casa', 'Trabajo', null]),
            'street' => $this->faker->streetAddress(),
            'city' => 'Concordia',
            'province' => 'Entre Ríos',
            'postal_code' => 'E3200',
            'phone' => '345 '.$this->faker->numerify('#######'),
            'is_default' => false,
        ];
    }
}
