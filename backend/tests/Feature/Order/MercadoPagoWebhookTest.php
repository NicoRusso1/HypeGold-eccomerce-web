<?php

namespace Tests\Feature\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\MercadoPagoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MercadoPago\Resources\Payment;
use Tests\TestCase;

class MercadoPagoWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function fakePayment(int $id, string $externalReference, string $status, float $amount): Payment
    {
        $payment = new Payment();
        $payment->id = $id;
        $payment->external_reference = $externalReference;
        $payment->status = $status;
        $payment->transaction_amount = $amount;

        return $payment;
    }

    public function test_no_requiere_autenticacion(): void
    {
        $response = $this->postJson('/api/webhooks/mercadopago', ['type' => 'otra_cosa']);

        $response->assertOk();
    }

    public function test_ignora_notificaciones_que_no_son_de_pago(): void
    {
        $response = $this->postJson('/api/webhooks/mercadopago', ['type' => 'merchant_order', 'data' => ['id' => '1']]);

        $response->assertOk()->assertJsonPath('message', 'Ignorado');
    }

    public function test_un_pago_aprobado_marca_el_pedido_como_pagado(): void
    {
        $order = Order::factory()->create(['status' => 'pendiente', 'total' => 45000]);

        $this->mock(MercadoPagoService::class, function ($mock) use ($order) {
            $mock->shouldReceive('obtenerPago')->with(999)
                ->andReturn($this->fakePayment(999, (string) $order->id, 'approved', 45000));
        });

        $response = $this->postJson('/api/webhooks/mercadopago', ['type' => 'payment', 'data' => ['id' => '999']]);

        $response->assertOk();
        $this->assertSame(OrderStatus::Pagado, $order->fresh()->status);
        $this->assertSame('999', $order->fresh()->mp_payment_id);
    }

    public function test_una_notificacion_repetida_no_falla_ni_duplica_efectos(): void
    {
        $order = Order::factory()->create(['status' => 'pendiente', 'total' => 45000]);

        $this->mock(MercadoPagoService::class, function ($mock) use ($order) {
            $mock->shouldReceive('obtenerPago')->with(999)
                ->twice()
                ->andReturn($this->fakePayment(999, (string) $order->id, 'approved', 45000));
        });

        $this->postJson('/api/webhooks/mercadopago', ['type' => 'payment', 'data' => ['id' => '999']])->assertOk();
        $response = $this->postJson('/api/webhooks/mercadopago', ['type' => 'payment', 'data' => ['id' => '999']]);

        $response->assertOk();
        $this->assertSame(OrderStatus::Pagado, $order->fresh()->status);
    }

    public function test_no_marca_como_pagado_si_el_monto_no_coincide(): void
    {
        $order = Order::factory()->create(['status' => 'pendiente', 'total' => 45000]);

        $this->mock(MercadoPagoService::class, function ($mock) use ($order) {
            $mock->shouldReceive('obtenerPago')->with(999)
                ->andReturn($this->fakePayment(999, (string) $order->id, 'approved', 1000));
        });

        $this->postJson('/api/webhooks/mercadopago', ['type' => 'payment', 'data' => ['id' => '999']])->assertOk();

        $this->assertSame(OrderStatus::Pendiente, $order->fresh()->status);
    }

    public function test_un_pago_rechazado_cancela_el_pedido_pendiente(): void
    {
        $order = Order::factory()->create(['status' => 'pendiente', 'total' => 45000]);

        $this->mock(MercadoPagoService::class, function ($mock) use ($order) {
            $mock->shouldReceive('obtenerPago')->with(999)
                ->andReturn($this->fakePayment(999, (string) $order->id, 'rejected', 45000));
        });

        $this->postJson('/api/webhooks/mercadopago', ['type' => 'payment', 'data' => ['id' => '999']])->assertOk();

        $this->assertSame(OrderStatus::Cancelado, $order->fresh()->status);
    }

    public function test_ignora_la_notificacion_si_el_pedido_no_existe(): void
    {
        $this->mock(MercadoPagoService::class, function ($mock) {
            $mock->shouldReceive('obtenerPago')->with(999)
                ->andReturn($this->fakePayment(999, '999999', 'approved', 45000));
        });

        $response = $this->postJson('/api/webhooks/mercadopago', ['type' => 'payment', 'data' => ['id' => '999']]);

        $response->assertOk();
    }
}
