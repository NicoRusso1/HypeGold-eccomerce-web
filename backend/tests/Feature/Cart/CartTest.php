<?php

namespace Tests\Feature\Cart;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_visitante_no_autenticado_no_puede_ver_el_carrito(): void
    {
        $this->getJson('/api/cart')->assertUnauthorized();
    }

    public function test_un_cliente_autenticado_ve_su_carrito_vacio(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/cart');

        $response->assertOk()
            ->assertJsonPath('data.items', [])
            ->assertJsonPath('data.items_count', 0)
            ->assertJsonPath('data.total', 0);
    }

    public function test_un_cliente_puede_agregar_un_producto_al_carrito(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->create(['base_price' => 45000]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'stock' => 10, 'extra_price' => 0]);

        $response = $this->postJson('/api/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 2]);

        $response->assertCreated()
            ->assertJsonPath('data.items_count', 2)
            ->assertJsonPath('data.total', 90000)
            ->assertJsonPath('data.items.0.quantity', 2)
            ->assertJsonPath('data.items.0.unit_price', 45000);
    }

    public function test_agregar_el_mismo_producto_suma_la_cantidad(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variant = ProductVariant::factory()->create(['stock' => 10]);

        $this->postJson('/api/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 2]);
        $response = $this->postJson('/api/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 3]);

        $response->assertCreated()->assertJsonPath('data.items_count', 5);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_no_se_puede_agregar_mas_cantidad_que_el_stock_disponible(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variant = ProductVariant::factory()->create(['stock' => 3]);

        $response = $this->postJson('/api/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 5]);

        $response->assertStatus(422)->assertJsonValidationErrors('quantity');
    }

    public function test_un_cliente_puede_cambiar_la_cantidad_de_un_item(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        $this->postJson('/api/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 2]);
        $itemId = $this->getJson('/api/cart')->json('data.items.0.id');

        $response = $this->putJson("/api/cart/items/{$itemId}", ['quantity' => 4]);

        $response->assertOk()->assertJsonPath('data.items_count', 4);
    }

    public function test_no_se_puede_cambiar_la_cantidad_a_mas_del_stock_disponible(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variant = ProductVariant::factory()->create(['stock' => 3]);
        $this->postJson('/api/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 2]);
        $itemId = $this->getJson('/api/cart')->json('data.items.0.id');

        $response = $this->putJson("/api/cart/items/{$itemId}", ['quantity' => 10]);

        $response->assertStatus(422)->assertJsonValidationErrors('quantity');
    }

    public function test_un_cliente_puede_eliminar_un_item_del_carrito(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        $this->postJson('/api/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 2]);
        $itemId = $this->getJson('/api/cart')->json('data.items.0.id');

        $response = $this->deleteJson("/api/cart/items/{$itemId}");

        $response->assertOk()->assertJsonPath('data.items', []);
    }

    public function test_un_cliente_puede_vaciar_el_carrito(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variantA = ProductVariant::factory()->create(['stock' => 10]);
        $variantB = ProductVariant::factory()->create(['stock' => 10]);
        $this->postJson('/api/cart/items', ['product_variant_id' => $variantA->id, 'quantity' => 1]);
        $this->postJson('/api/cart/items', ['product_variant_id' => $variantB->id, 'quantity' => 1]);

        $response = $this->deleteJson('/api/cart');

        $response->assertOk()->assertJsonPath('data.items', []);
    }

    public function test_un_cliente_no_puede_modificar_el_carrito_de_otro_usuario(): void
    {
        $dueño = User::factory()->create();
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        Sanctum::actingAs($dueño);
        $this->postJson('/api/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $itemId = $this->getJson('/api/cart')->json('data.items.0.id');

        Sanctum::actingAs(User::factory()->create());

        $this->putJson("/api/cart/items/{$itemId}", ['quantity' => 2])->assertNotFound();
        $this->deleteJson("/api/cart/items/{$itemId}")->assertNotFound();
    }

    public function test_el_carrito_persiste_entre_peticiones_del_mismo_usuario(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::factory()->create(['stock' => 10]);

        Sanctum::actingAs($user);
        $this->postJson('/api/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 2]);

        Sanctum::actingAs($user);
        $response = $this->getJson('/api/cart');

        $response->assertOk()->assertJsonPath('data.items_count', 2);
    }
}
