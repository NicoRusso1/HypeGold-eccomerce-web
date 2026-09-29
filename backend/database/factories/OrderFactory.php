<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => UserFactory::new(),
            'address_id' => AddressFactory::new(),
            'status' => 'pendiente',
            'total' => $this->faker->numberBetween(10, 200) * 1000,
            'discount' => 0,
            'shipping_label' => 'Casa',
            'shipping_street' => $this->faker->streetAddress(),
            'shipping_city' => 'Concordia',
            'shipping_province' => 'Entre Ríos',
            'shipping_postal_code' => 'E3200',
            'shipping_phone' => '345 4040162',
        ];
    }
}
