<?php

namespace Tests\Feature\Catalog;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_una_categoria_tiene_muchos_productos(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $this->assertCount(3, $category->fresh()->products);
    }

    public function test_un_producto_pertenece_a_una_categoria(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($product->category->is($category));
    }

    public function test_un_producto_tiene_variantes_e_imagenes(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->count(2)->create(['product_id' => $product->id]);
        ProductImage::factory()->create(['product_id' => $product->id]);

        $product->refresh();

        $this->assertCount(2, $product->variants);
        $this->assertCount(1, $product->images);
    }

    public function test_el_stock_total_suma_el_stock_de_todas_las_variantes(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->create(['product_id' => $product->id, 'stock' => 5]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'stock' => 3]);

        $this->assertSame(8, $product->fresh()->load('variants')->total_stock);
    }

    public function test_el_seeder_de_catalogo_crea_categorias_y_productos_con_variantes_e_imagenes(): void
    {
        $this->seed(CatalogSeeder::class);

        $this->assertSame(4, Category::count());
        $this->assertGreaterThanOrEqual(8, Product::count());

        $product = Product::with(['category', 'variants', 'images'])->first();

        $this->assertNotNull($product->category);
        $this->assertNotEmpty($product->variants);
        $this->assertNotEmpty($product->images);
    }
}
