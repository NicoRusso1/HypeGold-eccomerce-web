<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Crea (o actualiza) el administrador de prueba para desarrollo local.
     *
     * Credenciales: admin@hypegold.com / password
     * Cambiar en producción — esto es solo para desarrollo.
     */
    public function run(): void
    {
        User::factory()
            ->admin()
            ->create([
                'name' => 'Admin HypeGold',
                'email' => 'admin@hypegold.com',
                'role' => UserRole::Administrador,
            ]);
    }
}
