<?php

namespace Tests\Feature\Order;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_visitante_no_autenticado_no_puede_hacer_checkout(): void
    {
        $this->postJson('/api/orders', ['address_id' => 1])->assertUnauthorized();
    }

    public function test_no_se_puede_hacer_checkout_con_el_carrito_vacio(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders', ['address_id' => $address->id]);

        $response->assertStatus(422)->assertJsonValidationErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_no_se_puede_hacer_checkout_con_la_direccion_de_otro_usuario(): void
    {
        $address = Address::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/orders', ['address_id' => $address->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors('address_id');
    }

    public function test_un_cliente_puede_confirmar_la_compra_y_se_crea_el_pedido_pendiente(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create();
        $product = Product::factory()->create(['base_price' => 45000]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'stock' => 10, 'extra_price' => 0]);

        Sanctum::actingAs($user);
        $this->postJson('/api/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 2]);

        $response = $this->postJson('/api/orders', ['address_id' => $address->id]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pendiente')
            ->assertJsonPath('data.total', 90000)
            ->assertJsonPath('data.shipping.street', $address->street)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.quantity', 2);
    }

    public function test_confirmar_la_compra_descuenta_el_stock(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create();
        $variant = ProductVariant::factory()->create(['stock' => 10]);

        Sanctum::actingAs($user);
        $this->postJson('/api/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 3]);
        $this->postJson('/api/orders', ['address_id' => $address->id]);

        $this->assertSame(7, $variant->fresh()->stock);
    }

    public function test_confirmar_la_compra_vacia_el_carrito(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create();
        $variant = ProductVariant::factory()->create(['stock' => 10]);

        Sanctum::actingAs($user);
        $this->postJson('/api/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $this->postJson('/api/orders', ['address_id' => $address->id]);

        $response = $this->getJson('/api/cart');
        $response->assertJsonPath('data.items', []);
    }

    public function test_no_se_puede_comprar_mas_que_el_stock_disponible(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create();
        $variant = ProductVariant::factory()->create(['stock' => 2]);

        Sanctum::actingAs($user);
        $this->postJson('/api/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 2]);
        // Otra compra se lleva el stock justo antes de confirmar esta.
        $variant->update(['stock' => 1]);

        $response = $this->postJson('/api/orders', ['address_id' => $address->id]);

        $response->assertStatus(422)->assertJsonValidationErrors('stock');
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(1, $variant->fresh()->stock);
    }

    public function test_el_pedido_falla_completo_si_falla_un_item_no_deja_datos_a_medias(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create();
        $variantOk = ProductVariant::factory()->create(['stock' => 10]);
        $variantSinStock = ProductVariant::factory()->create(['stock' => 1]);

        Sanctum::actingAs($user);
        $this->postJson('/api/cart/items', ['product_variant_id' => $variantOk->id, 'quantity' => 2]);
        $this->postJson('/api/cart/items', ['product_variant_id' => $variantSinStock->id, 'quantity' => 1]);
        $variantSinStock->update(['stock' => 0]);

        $this->postJson('/api/orders', ['address_id' => $address->id])->assertStatus(422);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertSame(10, $variantOk->fresh()->stock);
    }
}
