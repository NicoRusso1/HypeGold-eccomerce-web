<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Se puede correr mas de una vez sin duplicar datos (ej. en cada
     * arranque del contenedor en Render): si ya hay categorias, no hace nada.
     */
    public function run(): void
    {
        if (Category::query()->exists()) {
            return;
        }

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
