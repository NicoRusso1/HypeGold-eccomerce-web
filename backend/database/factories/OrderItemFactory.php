<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $precio = $this->faker->randomFloat(2, 5000, 90000);
        $cantidad = $this->faker->numberBetween(1, 3);

        return [
            'order_id' => Order::factory(),
            'product_variant_id' => null,
            'product_name' => $this->faker->randomElement(['Cadena cubana', 'Anillo solitario', 'Pulsera forcet', 'Aros argolla']),
            'variant_talle' => null,
            'sku' => strtoupper($this->faker->bothify('SKU-####')),
            'unit_price' => $precio,
            'quantity' => $cantidad,
            'subtotal' => $precio * $cantidad,
        ];
    }
}
