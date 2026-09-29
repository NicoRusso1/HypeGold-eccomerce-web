<?php

namespace Tests\Feature\Coupon;

use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CouponValidationTest extends TestCase
{
    use RefreshDatabase;

    private function agregarAlCarrito(int $variantId, int $quantity = 1): void
    {
        $this->postJson('/api/cart/items', ['product_variant_id' => $variantId, 'quantity' => $quantity]);
    }

    public function test_un_visitante_no_autenticado_no_puede_validar_un_cupon(): void
    {
        $this->postJson('/api/coupons/validate', ['code' => 'X'])->assertUnauthorized();
    }

    public function test_valida_un_cupon_porcentual_y_calcula_el_descuento(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        $variant->product()->update(['base_price' => 10000]);
        $this->agregarAlCarrito($variant->id, 2);
        Coupon::factory()->create(['code' => 'DIEZ', 'type' => 'percentage', 'value' => 10]);

        $response = $this->postJson('/api/coupons/validate', ['code' => 'diez']);

        $response->assertOk()
            ->assertJsonPath('data.discount', 2000)
            ->assertJsonPath('data.total', 18000);
    }

    public function test_valida_un_cupon_de_monto_fijo(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        $variant->product()->update(['base_price' => 10000]);
        $this->agregarAlCarrito($variant->id, 1);
        Coupon::factory()->fixed(3000)->create(['code' => 'FIJO3000']);

        $response = $this->postJson('/api/coupons/validate', ['code' => 'FIJO3000']);

        $response->assertOk()->assertJsonPath('data.discount', 3000)->assertJsonPath('data.total', 7000);
    }

    public function test_el_descuento_no_deja_el_total_negativo(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        $variant->product()->update(['base_price' => 1000]);
        $this->agregarAlCarrito($variant->id, 1);
        Coupon::factory()->fixed(5000)->create(['code' => 'GRANDE']);

        $response = $this->postJson('/api/coupons/validate', ['code' => 'GRANDE']);

        $response->assertOk()->assertJsonPath('data.discount', 1000)->assertJsonPath('data.total', 0);
    }

    public function test_rechaza_un_cupon_inexistente(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/coupons/validate', ['code' => 'NOEXISTE'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }

    public function test_rechaza_un_cupon_vencido(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        $this->agregarAlCarrito($variant->id, 1);
        Coupon::factory()->expired()->create(['code' => 'VIEJO']);

        $this->postJson('/api/coupons/validate', ['code' => 'VIEJO'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }

    public function test_rechaza_un_cupon_que_alcanzo_su_limite_de_usos(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        $this->agregarAlCarrito($variant->id, 1);
        Coupon::factory()->agotado()->create(['code' => 'AGOTADO']);

        $this->postJson('/api/coupons/validate', ['code' => 'AGOTADO'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }

    public function test_rechaza_un_cupon_inactivo(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        $this->agregarAlCarrito($variant->id, 1);
        Coupon::factory()->inactivo()->create(['code' => 'INACTIVO']);

        $this->postJson('/api/coupons/validate', ['code' => 'INACTIVO'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }

    public function test_rechaza_si_el_carrito_esta_vacio(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Coupon::factory()->create(['code' => 'VACIO']);

        $this->postJson('/api/coupons/validate', ['code' => 'VACIO'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }
}
