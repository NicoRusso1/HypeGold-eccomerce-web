<?php

namespace Tests\Feature\Order;

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmar_la_compra_envia_el_email_de_pedido_creado(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create();
        $variant = ProductVariant::factory()->create(['stock' => 10]);

        Sanctum::actingAs($user);
        $this->postJson('/api/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $this->postJson('/api/orders', ['address_id' => $address->id]);

        Notification::assertSentTo(
            $user,
            OrderCreatedNotification::class,
            fn (OrderCreatedNotification $notification) => $notification->order->user_id === $user->id,
        );
    }

    public function test_transicionar_a_un_estado_valido_envia_el_email_de_cambio_de_estado(): void
    {
        Notification::fake();

        $order = Order::factory()->create(['status' => 'pendiente']);

        $order->transicionarA(OrderStatus::Pagado);

        Notification::assertSentTo(
            $order->user,
            OrderStatusChangedNotification::class,
            fn (OrderStatusChangedNotification $notification) => $notification->order->status === OrderStatus::Pagado,
        );
    }

    public function test_transicionar_a_un_estado_invalido_no_envia_ningun_email(): void
    {
        Notification::fake();

        $order = Order::factory()->create(['status' => 'pendiente']);

        $order->transicionarA(OrderStatus::Entregado);

        Notification::assertNothingSent();
    }
}
