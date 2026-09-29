<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Cupones de ejemplo para desarrollo y demo.
     */
    public function run(): void
    {
        Coupon::firstOrCreate(
            ['code' => 'BIENVENIDO10'],
            ['type' => 'percentage', 'value' => 10, 'usage_limit' => null, 'active' => true],
        );

        Coupon::firstOrCreate(
            ['code' => 'HYPEGOLD5000'],
            ['type' => 'fixed', 'value' => 5000, 'usage_limit' => null, 'active' => true],
        );
    }
}
