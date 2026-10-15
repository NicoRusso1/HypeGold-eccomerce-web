<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_cliente_no_puede_ver_el_dashboard(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/admin/dashboard')->assertForbidden();
    }

    public function test_un_visitante_no_autenticado_no_puede_ver_el_dashboard(): void
    {
        $this->getJson('/api/admin/dashboard')->assertUnauthorized();
    }

    public function test_el_total_de_ventas_solo_cuenta_pedidos_pagados_enviados_o_entregados(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        Order::factory()->create(['status' => 'pagado', 'total' => 10000]);
        Order::factory()->create(['status' => 'enviado', 'total' => 20000]);
        Order::factory()->create(['status' => 'entregado', 'total' => 30000]);
        Order::factory()->create(['status' => 'pendiente', 'total' => 99999]);
        Order::factory()->create(['status' => 'cancelado', 'total' => 99999]);

        $response = $this->getJson('/api/admin/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.total_ventas', 60000)
            ->assertJsonPath('data.cantidad_ventas', 3);
    }

    public function test_el_conteo_de_pedidos_por_estado_incluye_todos_los_estados(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        Order::factory()->create(['status' => 'pagado']);
        Order::factory()->create(['status' => 'pagado']);
        Order::factory()->create(['status' => 'cancelado']);

        $response = $this->getJson('/api/admin/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.pedidos_por_estado.pagado', 2)
            ->assertJsonPath('data.pedidos_por_estado.cancelado', 1)
            ->assertJsonPath('data.pedidos_por_estado.pendiente', 0)
            ->assertJsonPath('data.pedidos_por_estado.enviado', 0)
            ->assertJsonPath('data.pedidos_por_estado.entregado', 0);
    }

    public function test_los_productos_mas_vendidos_se_ordenan_por_cantidad_y_excluyen_pedidos_no_vendidos(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $vendido = Order::factory()->create(['status' => 'pagado']);
        OrderItem::factory()->create(['order_id' => $vendido->id, 'product_name' => 'Cadena cubana', 'quantity' => 5]);
        OrderItem::factory()->create(['order_id' => $vendido->id, 'product_name' => 'Anillo solitario', 'quantity' => 2]);

        $pendiente = Order::factory()->create(['status' => 'pendiente']);
        OrderItem::factory()->create(['order_id' => $pendiente->id, 'product_name' => 'No debería contar', 'quantity' => 100]);

        $response = $this->getJson('/api/admin/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.productos_mas_vendidos.0.product_name', 'Cadena cubana')
            ->assertJsonPath('data.productos_mas_vendidos.0.cantidad', 5)
            ->assertJsonPath('data.productos_mas_vendidos.1.product_name', 'Anillo solitario');
    }

    public function test_filtra_por_rango_de_fechas(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        Order::factory()->create(['status' => 'pagado', 'total' => 10000, 'created_at' => now()->subDays(60)]);
        Order::factory()->create(['status' => 'pagado', 'total' => 5000, 'created_at' => now()]);

        $response = $this->getJson('/api/admin/dashboard?from='.now()->subDays(5)->toDateString().'&to='.now()->toDateString());

        $response->assertOk()->assertJsonPath('data.total_ventas', 5000);
    }

    public function test_rechaza_un_rango_de_fechas_invalido(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson('/api/admin/dashboard?from=2026-10-10&to=2026-10-01')
            ->assertStatus(422)
            ->assertJsonValidationErrors('to');
    }
}
