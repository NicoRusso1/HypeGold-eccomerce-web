<?php

namespace Tests\Feature\Catalog;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_solo_productos_activos_paginados(): void
    {
        Product::factory()->count(3)->create();
        Product::factory()->inactive()->create();

        $response = $this->getJson('/api/products');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
        $this->assertSame(3, $response->json('meta.total'));
    }

    public function test_el_listado_de_productos_no_requiere_autenticacion(): void
    {
        Product::factory()->create();

        $this->getJson('/api/products')->assertOk();
    }

    public function test_incluye_stock_total_e_imagen_principal(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->create(['product_id' => $product->id, 'stock' => 5]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'stock' => 3]);
        $product->images()->create(['url' => 'https://ejemplo.com/foto.jpg', 'order' => 0]);

        $response = $this->getJson('/api/products');

        $response->assertOk()
            ->assertJsonPath('data.0.total_stock', 8)
            ->assertJsonPath('data.0.image', 'https://ejemplo.com/foto.jpg');
    }

    public function test_busca_productos_por_nombre(): void
    {
        Product::factory()->create(['name' => 'Cadena cubana']);
        Product::factory()->create(['name' => 'Aros gota']);

        $response = $this->getJson('/api/products?search=cadena');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Cadena cubana', $response->json('data.0.name'));
    }

    public function test_filtra_productos_por_categoria(): void
    {
        $anillos = Category::factory()->create(['slug' => 'anillos']);
        $cadenas = Category::factory()->create(['slug' => 'cadenas']);
        Product::factory()->create(['category_id' => $anillos->id]);
        Product::factory()->create(['category_id' => $cadenas->id]);

        $response = $this->getJson('/api/products?category=anillos');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_filtra_productos_por_material_y_rango_de_precio(): void
    {
        Product::factory()->create(['material' => 'Oro 18k', 'base_price' => 90000]);
        Product::factory()->create(['material' => 'Plata 925', 'base_price' => 20000]);

        $response = $this->getJson('/api/products?material=Oro 18k&min_price=50000&max_price=100000');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Oro 18k', $response->json('data.0.material'));
    }

    public function test_ordena_productos_por_precio_ascendente(): void
    {
        Product::factory()->create(['name' => 'Caro', 'base_price' => 90000]);
        Product::factory()->create(['name' => 'Barato', 'base_price' => 10000]);

        $response = $this->getJson('/api/products?sort=precio_asc');

        $response->assertOk();
        $this->assertSame('Barato', $response->json('data.0.name'));
    }

    public function test_respeta_el_parametro_per_page(): void
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/products?per_page=2');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
        $this->assertSame(3, $response->json('meta.last_page'));
    }
}
