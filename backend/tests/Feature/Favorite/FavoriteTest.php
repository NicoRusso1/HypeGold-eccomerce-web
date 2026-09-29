<?php

namespace Tests\Feature\Favorite;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_visitante_no_autenticado_no_puede_ver_sus_favoritos(): void
    {
        $this->getJson('/api/favorites')->assertUnauthorized();
    }

    public function test_un_cliente_ve_su_lista_de_favoritos_vacia(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/favorites');

        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
    }

    public function test_un_cliente_puede_agregar_un_producto_a_favoritos(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->create();

        $response = $this->postJson('/api/favorites', ['product_id' => $product->id]);

        $response->assertCreated();
        $this->assertDatabaseCount('favorites', 1);

        $listado = $this->getJson('/api/favorites');
        $listado->assertJsonPath('data.0.id', $product->id);
    }

    public function test_agregar_el_mismo_producto_dos_veces_no_duplica(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->create();

        $this->postJson('/api/favorites', ['product_id' => $product->id]);
        $this->postJson('/api/favorites', ['product_id' => $product->id]);

        $this->assertDatabaseCount('favorites', 1);
    }

    public function test_un_cliente_puede_quitar_un_producto_de_favoritos(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->create();
        $this->postJson('/api/favorites', ['product_id' => $product->id]);

        $response = $this->deleteJson("/api/favorites/{$product->id}");

        $response->assertNoContent();
        $this->assertDatabaseCount('favorites', 0);
    }

    public function test_un_cliente_ve_solo_sus_propios_favoritos(): void
    {
        $otro = User::factory()->create();
        $product = Product::factory()->create();
        $otro->favorites()->create(['product_id' => $product->id]);

        Sanctum::actingAs(User::factory()->create());
        $response = $this->getJson('/api/favorites');

        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
    }

    public function test_no_se_puede_favoritar_un_producto_inexistente(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/favorites', ['product_id' => 9999])
            ->assertStatus(422)
            ->assertJsonValidationErrors('product_id');
    }
}
