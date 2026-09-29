<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('CUPON-####')),
            'type' => 'percentage',
            'value' => 10,
            'expires_at' => null,
            'usage_limit' => null,
            'times_used' => 0,
            'active' => true,
        ];
    }

    public function fixed(float $value = 5000): static
    {
        return $this->state(fn () => ['type' => 'fixed', 'value' => $value]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }

    public function agotado(): static
    {
        return $this->state(fn () => ['usage_limit' => 1, 'times_used' => 1]);
    }

    public function inactivo(): static
    {
        return $this->state(fn () => ['active' => false]);
    }
}
