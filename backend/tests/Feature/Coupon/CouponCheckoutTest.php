<?php

namespace Tests\Feature\Coupon;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CouponCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmar_la_compra_con_cupon_aplica_el_descuento_y_marca_el_uso(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create();
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        $variant->product()->update(['base_price' => 10000]);
        $coupon = Coupon::factory()->create(['code' => 'DIEZ', 'type' => 'percentage', 'value' => 10]);

        Sanctum::actingAs($user);
        $this->postJson('/api/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response = $this->postJson('/api/orders', ['address_id' => $address->id, 'coupon_code' => 'diez']);

        $response->assertCreated()
            ->assertJsonPath('data.total', 9000)
            ->assertJsonPath('data.discount', 1000)
            ->assertJsonPath('data.coupon_code', 'DIEZ');
        $this->assertSame(1, $coupon->fresh()->times_used);
    }

    public function test_no_se_puede_confirmar_la_compra_con_un_cupon_invalido(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create();
        $variant = ProductVariant::factory()->create(['stock' => 10]);

        Sanctum::actingAs($user);
        $this->postJson('/api/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response = $this->postJson('/api/orders', ['address_id' => $address->id, 'coupon_code' => 'NOEXISTE']);

        $response->assertStatus(422)->assertJsonValidationErrors('coupon');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_la_compra_sin_cupon_no_tiene_descuento(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create();
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        $variant->product()->update(['base_price' => 10000]);

        Sanctum::actingAs($user);
        $this->postJson('/api/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response = $this->postJson('/api/orders', ['address_id' => $address->id]);

        $response->assertCreated()->assertJsonPath('data.discount', 0)->assertJsonPath('data.total', 10000);
    }
}
