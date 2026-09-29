<?php

namespace Tests\Feature\Catalog;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_muestra_el_detalle_de_un_producto_activo_por_slug(): void
    {
        $product = Product::factory()->create(['slug' => 'cadena-cubana', 'active' => true]);
        $product->images()->create(['url' => 'https://ejemplo.com/1.jpg', 'order' => 0]);
        $product->images()->create(['url' => 'https://ejemplo.com/2.jpg', 'order' => 1]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'talle' => '45cm', 'stock' => 10, 'extra_price' => 0]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'talle' => '50cm', 'stock' => 5, 'extra_price' => 2000]);

        $response = $this->getJson('/api/products/cadena-cubana');

        $response->assertOk()
            ->assertJsonPath('data.slug', 'cadena-cubana')
            ->assertJsonPath('data.total_stock', 15)
            ->assertJsonCount(2, 'data.images')
            ->assertJsonCount(2, 'data.variants');
    }

    public function test_incluye_el_precio_de_cada_variante_sumando_el_extra(): void
    {
        $product = Product::factory()->create(['slug' => 'pulsera-forcet', 'base_price' => 24000]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'talle' => 'Único', 'extra_price' => 3000]);

        $response = $this->getJson('/api/products/pulsera-forcet');

        $response->assertOk()->assertJsonPath('data.variants.0.price', 27000);
    }

    public function test_devuelve_404_para_un_slug_inexistente(): void
    {
        $this->getJson('/api/products/no-existe')->assertNotFound();
    }

    public function test_devuelve_404_para_un_producto_inactivo(): void
    {
        Product::factory()->inactive()->create(['slug' => 'producto-inactivo']);

        $this->getJson('/api/products/producto-inactivo')->assertNotFound();
    }

    public function test_el_detalle_de_producto_no_requiere_autenticacion(): void
    {
        $product = Product::factory()->create(['slug' => 'sin-auth']);

        $this->getJson('/api/products/sin-auth')->assertOk();
    }
}
