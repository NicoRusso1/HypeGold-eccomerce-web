<?php

namespace Tests\Feature\Order;

use App\Models\Address;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\MercadoPagoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use MercadoPago\Resources\Preference;
use Tests\TestCase;

class OrderPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function fakePreference(string $id = 'pref-123', string $initPoint = 'https://mp.example/pago/pref-123'): Preference
    {
        $preferencia = new Preference();
        $preferencia->id = $id;
        $preferencia->init_point = $initPoint;

        return $preferencia;
    }

    public function test_al_confirmar_la_compra_se_crea_la_preferencia_y_se_devuelve_el_link_de_pago(): void
    {
        $this->mock(MercadoPagoService::class, function ($mock) {
            $mock->shouldReceive('crearPreferencia')->once()->andReturn($this->fakePreference());
        });

        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create();
        $variant = ProductVariant::factory()->create(['stock' => 10]);

        Sanctum::actingAs($user);
        $this->postJson('/api/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response = $this->postJson('/api/orders', ['address_id' => $address->id]);

        $response->assertCreated()->assertJsonPath('data.payment_url', 'https://mp.example/pago/pref-123');
        $this->assertDatabaseHas('orders', ['mp_preference_id' => 'pref-123']);
    }

    public function test_si_falla_la_creacion_de_la_preferencia_el_pedido_igual_se_crea(): void
    {
        $this->mock(MercadoPagoService::class, function ($mock) {
            $mock->shouldReceive('crearPreferencia')->once()->andThrow(new \Exception('MP caído'));
        });

        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create();
        $variant = ProductVariant::factory()->create(['stock' => 10]);

        Sanctum::actingAs($user);
        $this->postJson('/api/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response = $this->postJson('/api/orders', ['address_id' => $address->id]);

        $response->assertCreated()->assertJsonPath('data.payment_url', null);
        $this->assertDatabaseCount('orders', 1);
    }
}
