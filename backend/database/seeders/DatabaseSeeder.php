<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            CatalogSeeder::class,
            CouponSeeder::class,
        ]);

        // Cliente de prueba para desarrollo local (password: "password").
        User::factory()->create([
            'name' => 'Cliente de Prueba',
            'email' => 'cliente@hypegold.com',
        ]);
    }
}
