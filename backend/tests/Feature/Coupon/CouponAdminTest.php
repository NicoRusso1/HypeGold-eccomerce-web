<?php

namespace Tests\Feature\Coupon;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CouponAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_cliente_no_puede_administrar_cupones(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/admin/coupons')->assertForbidden();
    }

    public function test_un_administrador_puede_crear_un_cupon(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $response = $this->postJson('/api/admin/coupons', [
            'code' => 'nuevo10',
            'type' => 'percentage',
            'value' => 15,
        ]);

        $response->assertCreated()->assertJsonPath('data.code', 'NUEVO10');
        $this->assertDatabaseHas('coupons', ['code' => 'NUEVO10']);
    }

    public function test_no_se_puede_crear_un_cupon_con_codigo_repetido(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        Coupon::factory()->create(['code' => 'REPETIDO']);

        $this->postJson('/api/admin/coupons', ['code' => 'REPETIDO', 'type' => 'fixed', 'value' => 100])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }

    public function test_un_administrador_puede_actualizar_un_cupon(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $coupon = Coupon::factory()->create(['active' => true]);

        $response = $this->putJson("/api/admin/coupons/{$coupon->id}", [
            'code' => $coupon->code,
            'type' => 'fixed',
            'value' => 2000,
            'active' => false,
        ]);

        $response->assertOk()->assertJsonPath('data.active', false);
    }

    public function test_un_administrador_puede_eliminar_un_cupon(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $coupon = Coupon::factory()->create();

        $this->deleteJson("/api/admin/coupons/{$coupon->id}")->assertNoContent();
        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }
}
