<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_cliente_no_puede_administrar_pedidos(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/admin/orders')->assertForbidden();
    }

    public function test_un_visitante_no_autenticado_no_puede_administrar_pedidos(): void
    {
        $this->getJson('/api/admin/orders')->assertUnauthorized();
    }

    public function test_un_administrador_ve_los_pedidos_de_todos_los_clientes(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        Order::factory()->create(['user_id' => User::factory()->create()->id]);
        Order::factory()->create(['user_id' => User::factory()->create()->id]);

        $response = $this->getJson('/api/admin/orders');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_un_administrador_puede_filtrar_pedidos_por_estado(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        Order::factory()->create(['status' => 'pendiente']);
        Order::factory()->create(['status' => 'pagado']);

        $response = $this->getJson('/api/admin/orders?status=pagado');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('pagado', $response->json('data.0.status'));
    }

    public function test_un_administrador_puede_buscar_pedidos_por_numero(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $buscado = Order::factory()->create();
        Order::factory()->create();

        $response = $this->getJson('/api/admin/orders?search='.$buscado->id);

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($buscado->id, $response->json('data.0.id'));
    }

    public function test_un_administrador_puede_buscar_pedidos_por_cliente(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $cliente = User::factory()->create(['name' => 'Juana Pérez', 'email' => 'juana@hypegold.com']);
        Order::factory()->create(['user_id' => $cliente->id]);
        Order::factory()->create();

        $response = $this->getJson('/api/admin/orders?search=juana');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Juana Pérez', $response->json('data.0.customer.name'));
    }

    public function test_un_administrador_puede_ver_el_detalle_de_un_pedido_con_datos_del_cliente(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $cliente = User::factory()->create(['name' => 'Juana Pérez']);
        $order = Order::factory()->create(['user_id' => $cliente->id]);

        $response = $this->getJson("/api/admin/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonPath('data.customer.name', 'Juana Pérez')
            ->assertJsonPath('data.shipping.street', $order->shipping_street);
    }

    public function test_un_administrador_puede_cambiar_el_estado_de_un_pedido(): void
    {
        Notification::fake();
        Sanctum::actingAs(User::factory()->admin()->create());
        $order = Order::factory()->create(['status' => 'pendiente']);

        $response = $this->putJson("/api/admin/orders/{$order->id}/status", ['status' => 'pagado']);

        $response->assertOk()->assertJsonPath('data.status', 'pagado');
        $this->assertSame('pagado', $order->fresh()->status->value);
    }

    public function test_no_se_puede_hacer_una_transicion_de_estado_invalida(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $order = Order::factory()->create(['status' => 'pendiente']);

        $this->putJson("/api/admin/orders/{$order->id}/status", ['status' => 'entregado'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');
        $this->assertSame('pendiente', $order->fresh()->status->value);
    }

    public function test_el_estado_debe_ser_uno_de_los_valores_validos_del_enum(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $order = Order::factory()->create(['status' => 'pendiente']);

        $this->putJson("/api/admin/orders/{$order->id}/status", ['status' => 'no-existe'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }
}
