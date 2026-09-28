<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function authHeader(User $user): array
    {
        return ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];
    }

    public function test_un_usuario_autenticado_puede_ver_su_perfil(): void
    {
        $user = User::factory()->create(['name' => 'Nico', 'phone' => '+54 9 345 404 0162']);

        $this->withHeaders($this->authHeader($user))
            ->getJson('/api/profile')
            ->assertOk()
            ->assertJsonPath('name', 'Nico')
            ->assertJsonPath('phone', '+54 9 345 404 0162');
    }

    public function test_un_visitante_no_puede_ver_el_perfil(): void
    {
        $this->getJson('/api/profile')->assertUnauthorized();
    }

    public function test_un_usuario_puede_actualizar_su_nombre_email_y_telefono(): void
    {
        $user = User::factory()->create();

        $this->withHeaders($this->authHeader($user))
            ->putJson('/api/profile', [
                'name' => 'Nico Actualizado',
                'email' => 'nuevo@hypegold.com',
                'phone' => '+54 9 345 111 2222',
            ])
            ->assertOk()
            ->assertJsonPath('name', 'Nico Actualizado')
            ->assertJsonPath('email', 'nuevo@hypegold.com');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => 'nuevo@hypegold.com']);
    }

    public function test_no_puede_actualizar_su_email_al_de_otro_usuario(): void
    {
        User::factory()->create(['email' => 'ocupado@hypegold.com']);
        $user = User::factory()->create();

        $this->withHeaders($this->authHeader($user))
            ->putJson('/api/profile', [
                'name' => $user->name,
                'email' => 'ocupado@hypegold.com',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_un_usuario_puede_cambiar_su_contrasena(): void
    {
        $user = User::factory()->create(['password' => Hash::make('vieja-contraseña')]);

        $this->withHeaders($this->authHeader($user))
            ->putJson('/api/profile/password', [
                'current_password' => 'vieja-contraseña',
                'password' => 'nueva-contraseña',
                'password_confirmation' => 'nueva-contraseña',
            ])
            ->assertOk();

        $this->assertTrue(Hash::check('nueva-contraseña', $user->fresh()->password));
    }

    public function test_no_puede_cambiar_la_contrasena_si_la_actual_no_coincide(): void
    {
        $user = User::factory()->create(['password' => Hash::make('vieja-contraseña')]);

        $this->withHeaders($this->authHeader($user))
            ->putJson('/api/profile/password', [
                'current_password' => 'incorrecta',
                'password' => 'nueva-contraseña',
                'password_confirmation' => 'nueva-contraseña',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('current_password');
    }
}
