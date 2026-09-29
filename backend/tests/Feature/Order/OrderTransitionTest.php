<?php

namespace Tests\Feature\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_transicionar_a_un_estado_valido_actualiza_y_persiste(): void
    {
        $order = Order::factory()->create(['status' => 'pendiente']);

        $resultado = $order->transicionarA(OrderStatus::Pagado);

        $this->assertTrue($resultado);
        $this->assertSame(OrderStatus::Pagado, $order->fresh()->status);
    }

    public function test_transicionar_a_un_estado_invalido_no_cambia_nada(): void
    {
        $order = Order::factory()->create(['status' => 'pendiente']);

        $resultado = $order->transicionarA(OrderStatus::Entregado);

        $this->assertFalse($resultado);
        $this->assertSame(OrderStatus::Pendiente, $order->fresh()->status);
    }

    public function test_un_pedido_entregado_no_puede_cambiar_de_estado(): void
    {
        $order = Order::factory()->create(['status' => 'entregado']);

        $this->assertFalse($order->transicionarA(OrderStatus::Cancelado));
        $this->assertSame(OrderStatus::Entregado, $order->fresh()->status);
    }
}
