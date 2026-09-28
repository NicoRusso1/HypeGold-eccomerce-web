<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_visitante_puede_registrarse(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Nicolás Russo',
            'email' => 'nico@hypegold.com',
            'password' => 'contraseña-segura',
            'password_confirmation' => 'contraseña-segura',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token'])
            ->assertJsonPath('user.email', 'nico@hypegold.com');

        $this->assertDatabaseHas('users', ['email' => 'nico@hypegold.com']);
    }

    public function test_el_registro_deja_al_usuario_con_la_sesion_iniciada(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Nicolás Russo',
            'email' => 'nico@hypegold.com',
            'password' => 'contraseña-segura',
            'password_confirmation' => 'contraseña-segura',
        ]);

        $token = $response->json('token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('email', 'nico@hypegold.com');
    }

    public function test_no_se_puede_registrar_dos_veces_el_mismo_email(): void
    {
        User::factory()->create(['email' => 'nico@hypegold.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Otro Nombre',
            'email' => 'nico@hypegold.com',
            'password' => 'contraseña-segura',
            'password_confirmation' => 'contraseña-segura',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_las_contrasenas_deben_coincidir(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Nicolás Russo',
            'email' => 'nico@hypegold.com',
            'password' => 'contraseña-segura',
            'password_confirmation' => 'otra-distinta',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_la_contrasena_debe_tener_al_menos_8_caracteres(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Nicolás Russo',
            'email' => 'nico@hypegold.com',
            'password' => 'corta',
            'password_confirmation' => 'corta',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('password');
    }
}
