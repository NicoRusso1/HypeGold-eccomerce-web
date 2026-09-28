<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_solicitar_recuperacion_con_un_email_existente_envia_la_notificacion(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'nico@hypegold.com']);

        $this->postJson('/api/forgot-password', ['email' => 'nico@hypegold.com'])
            ->assertOk()
            ->assertJsonPath('message', 'Si el email existe, te enviamos instrucciones para recuperar tu contraseña.');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_solicitar_recuperacion_con_un_email_inexistente_no_filtra_informacion(): void
    {
        Notification::fake();

        $this->postJson('/api/forgot-password', ['email' => 'no-existe@hypegold.com'])
            ->assertOk()
            ->assertJsonPath('message', 'Si el email existe, te enviamos instrucciones para recuperar tu contraseña.');

        Notification::assertNothingSent();
    }

    public function test_se_puede_restablecer_la_contrasena_con_un_token_valido(): void
    {
        $user = User::factory()->create(['email' => 'nico@hypegold.com']);
        $token = Password::createToken($user);

        $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'nico@hypegold.com',
            'password' => 'contraseña-nueva',
            'password_confirmation' => 'contraseña-nueva',
        ])->assertOk();

        $this->assertTrue(Hash::check('contraseña-nueva', $user->fresh()->password));
    }

    public function test_restablecer_con_un_token_invalido_falla(): void
    {
        User::factory()->create(['email' => 'nico@hypegold.com']);

        $this->postJson('/api/reset-password', [
            'token' => 'token-invalido',
            'email' => 'nico@hypegold.com',
            'password' => 'contraseña-nueva',
            'password_confirmation' => 'contraseña-nueva',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_restablecer_revoca_los_tokens_de_acceso_existentes(): void
    {
        $user = User::factory()->create(['email' => 'nico@hypegold.com']);
        $user->createToken('vieja-sesion');
        $token = Password::createToken($user);

        $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'nico@hypegold.com',
            'password' => 'contraseña-nueva',
            'password_confirmation' => 'contraseña-nueva',
        ])->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
