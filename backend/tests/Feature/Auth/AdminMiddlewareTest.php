<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ruta de prueba protegida por el middleware "admin", registrada
        // solo para este test (las rutas reales de administración se
        // agregan junto con sus historias, p. ej. HG-30, HG-31, HG-33).
        Route::middleware(['auth:sanctum', 'admin'])
            ->get('/api/_test/admin-only', fn () => response()->json(['ok' => true]));
    }

    public function test_un_cliente_recibe_403_en_una_ruta_de_administrador(): void
    {
        $cliente = User::factory()->create();
        $token = $cliente->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/_test/admin-only')
            ->assertForbidden();
    }

    public function test_un_administrador_puede_acceder_a_una_ruta_de_administrador(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/_test/admin-only')
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_un_visitante_sin_token_recibe_401_en_una_ruta_de_administrador(): void
    {
        $this->getJson('/api/_test/admin-only')->assertUnauthorized();
    }
}
