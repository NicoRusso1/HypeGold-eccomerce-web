<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_usuario_puede_iniciar_sesion_con_credenciales_validas(): void
    {
        User::factory()->create([
            'email' => 'nico@hypegold.com',
            'password' => Hash::make('contraseña-segura'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'nico@hypegold.com',
            'password' => 'contraseña-segura',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token'])
            ->assertJsonPath('user.email', 'nico@hypegold.com');
    }

    public function test_no_se_puede_iniciar_sesion_con_contrasena_incorrecta(): void
    {
        User::factory()->create([
            'email' => 'nico@hypegold.com',
            'password' => Hash::make('contraseña-segura'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'nico@hypegold.com',
            'password' => 'incorrecta',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_no_se_puede_iniciar_sesion_con_un_email_inexistente(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'no-existe@hypegold.com',
            'password' => 'lo-que-sea',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_un_usuario_autenticado_puede_cerrar_sesion(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test');

        $this->withHeader('Authorization', "Bearer {$token->plainTextToken}")
            ->postJson('/api/logout')
            ->assertOk();

        // El token usado para cerrar sesión queda revocado en la base de datos.
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
    }
}
