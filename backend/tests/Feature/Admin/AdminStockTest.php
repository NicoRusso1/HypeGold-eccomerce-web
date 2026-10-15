<?php

namespace Tests\Feature\Admin;

use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_cliente_no_puede_ver_el_stock(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/admin/stock')->assertForbidden();
    }

    public function test_un_visitante_no_autenticado_no_puede_ver_el_stock(): void
    {
        $this->getJson('/api/admin/stock')->assertUnauthorized();
    }

    public function test_un_administrador_puede_listar_el_stock_ordenado_de_menor_a_mayor(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        ProductVariant::factory()->create(['stock' => 20]);
        ProductVariant::factory()->create(['stock' => 3]);
        ProductVariant::factory()->create(['stock' => 10]);

        $response = $this->getJson('/api/admin/stock');

        $response->assertOk();
        $this->assertSame([3, 10, 20], $response->json('data.*.stock'));
    }

    public function test_marca_como_stock_bajo_las_variantes_por_debajo_del_umbral(): void
    {
        config(['shop.low_stock_threshold' => 5]);
        Sanctum::actingAs(User::factory()->admin()->create());
        ProductVariant::factory()->create(['stock' => 3]);
        ProductVariant::factory()->create(['stock' => 10]);

        $response = $this->getJson('/api/admin/stock');

        $response->assertOk()
            ->assertJsonPath('data.0.low_stock', true)
            ->assertJsonPath('data.1.low_stock', false)
            ->assertJsonPath('meta.low_stock_count', 1)
            ->assertJsonPath('meta.low_stock_threshold', 5);
    }

    public function test_un_administrador_puede_ajustar_el_stock_de_una_variante(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $variant = ProductVariant::factory()->create(['stock' => 10]);

        $response = $this->putJson("/api/admin/stock/{$variant->id}", ['stock' => 25]);

        $response->assertOk()->assertJsonPath('data.stock', 25);
        $this->assertSame(25, $variant->fresh()->stock);
    }

    public function test_no_se_puede_ajustar_el_stock_a_un_numero_negativo(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $variant = ProductVariant::factory()->create(['stock' => 10]);

        $this->putJson("/api/admin/stock/{$variant->id}", ['stock' => -1])
            ->assertStatus(422)
            ->assertJsonValidationErrors('stock');
        $this->assertSame(10, $variant->fresh()->stock);
    }
}
