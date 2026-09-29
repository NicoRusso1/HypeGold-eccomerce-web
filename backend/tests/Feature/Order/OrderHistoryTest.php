<?php

namespace Tests\Feature\Order;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function crearPedido(User $user, array $overrides = []): Order
    {
        $address = Address::factory()->for($user)->create();

        return Order::factory()->for($user)->create(array_merge([
            'address_id' => $address->id,
        ], $overrides));
    }

    public function test_un_visitante_no_autenticado_no_puede_ver_su_historial(): void
    {
        $this->getJson('/api/orders')->assertUnauthorized();
    }

    public function test_un_cliente_ve_su_historial_de_pedidos(): void
    {
        $user = User::factory()->create();
        $this->crearPedido($user);
        $this->crearPedido($user);

        Sanctum::actingAs($user);
        $response = $this->getJson('/api/orders');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_el_historial_ordena_los_pedidos_mas_recientes_primero(): void
    {
        $user = User::factory()->create();
        $viejo = $this->crearPedido($user, ['created_at' => now()->subDays(2)]);
        $nuevo = $this->crearPedido($user, ['created_at' => now()]);

        Sanctum::actingAs($user);
        $response = $this->getJson('/api/orders');

        $response->assertOk()->assertJsonPath('data.0.id', $nuevo->id);
    }

    public function test_un_cliente_no_ve_pedidos_de_otro_usuario(): void
    {
        $this->crearPedido(User::factory()->create());

        Sanctum::actingAs(User::factory()->create());
        $response = $this->getJson('/api/orders');

        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
    }

    public function test_un_cliente_puede_ver_el_detalle_de_su_pedido_con_items(): void
    {
        $user = User::factory()->create();
        $order = $this->crearPedido($user);
        $order->items()->create([
            'product_variant_id' => null,
            'product_name' => 'Cadena cubana',
            'variant_talle' => '50cm',
            'sku' => 'SKU-1',
            'unit_price' => 45000,
            'quantity' => 1,
            'subtotal' => 45000,
        ]);

        Sanctum::actingAs($user);
        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonCount(1, 'data.items');
    }

    public function test_un_cliente_no_puede_ver_el_detalle_del_pedido_de_otro_usuario(): void
    {
        $order = $this->crearPedido(User::factory()->create());

        Sanctum::actingAs(User::factory()->create());
        $this->getJson("/api/orders/{$order->id}")->assertNotFound();
    }
}
