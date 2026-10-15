<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    private function datosProducto(Category $category, array $overrides = []): array
    {
        return array_merge([
            'category_id' => $category->id,
            'name' => 'Cadena de prueba',
            'description' => 'Una cadena para testear.',
            'material' => 'Oro laminado',
            'base_price' => 25000,
            'variants' => [
                ['talle' => 'Único', 'sku' => 'TEST-SKU-1', 'stock' => 10, 'extra_price' => 0],
            ],
        ], $overrides);
    }

    public function test_un_cliente_no_puede_administrar_productos(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/admin/products')->assertForbidden();
    }

    public function test_un_visitante_no_autenticado_no_puede_administrar_productos(): void
    {
        $this->getJson('/api/admin/products')->assertUnauthorized();
    }

    public function test_un_administrador_puede_listar_productos_activos_e_inactivos(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        Product::factory()->create(['active' => true]);
        Product::factory()->inactive()->create();

        $response = $this->getJson('/api/admin/products');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_un_administrador_puede_crear_un_producto_con_variantes(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $category = Category::factory()->create();

        $response = $this->postJson('/api/admin/products', $this->datosProducto($category));

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Cadena de prueba')
            ->assertJsonPath('data.slug', 'cadena-de-prueba')
            ->assertJsonPath('data.active', true)
            ->assertJsonCount(1, 'data.variants');
        $this->assertDatabaseHas('product_variants', ['sku' => 'TEST-SKU-1']);
    }

    public function test_el_slug_se_desambigua_si_ya_existe(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $category = Category::factory()->create();
        Product::factory()->create(['slug' => 'cadena-de-prueba']);

        $response = $this->postJson('/api/admin/products', $this->datosProducto($category));

        $response->assertCreated()->assertJsonPath('data.slug', 'cadena-de-prueba-2');
    }

    public function test_no_se_puede_crear_un_producto_con_skus_duplicados_entre_si(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $category = Category::factory()->create();

        $response = $this->postJson('/api/admin/products', $this->datosProducto($category, [
            'variants' => [
                ['talle' => '45cm', 'sku' => 'REPETIDO', 'stock' => 5],
                ['talle' => '50cm', 'sku' => 'REPETIDO', 'stock' => 5],
            ],
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors('variants');
    }

    public function test_no_se_puede_usar_un_sku_que_ya_existe_en_otro_producto(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $category = Category::factory()->create();
        ProductVariant::factory()->create(['sku' => 'YA-EXISTE']);

        $response = $this->postJson('/api/admin/products', $this->datosProducto($category, [
            'variants' => [['talle' => 'Único', 'sku' => 'YA-EXISTE', 'stock' => 5]],
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors('variants');
    }

    public function test_un_administrador_puede_editar_un_producto_y_sus_variantes(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Original']);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'sku' => 'ORIG-1']);

        $response = $this->putJson("/api/admin/products/{$product->id}", $this->datosProducto($category, [
            'name' => 'Editado',
            'variants' => [
                ['id' => $variant->id, 'talle' => 'Único', 'sku' => 'ORIG-1', 'stock' => 20],
                ['talle' => 'Nueva', 'sku' => 'NUEVA-1', 'stock' => 3],
            ],
        ]));

        $response->assertOk()->assertJsonPath('data.name', 'Editado')->assertJsonCount(2, 'data.variants');
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 20]);
    }

    public function test_editar_un_producto_quitando_una_variante_la_elimina(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'sku' => 'A-BORRAR']);

        $this->putJson("/api/admin/products/{$product->id}", $this->datosProducto($category, [
            'variants' => [['talle' => 'Único', 'sku' => 'NUEVA', 'stock' => 5]],
        ]))->assertOk();

        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
    }

    public function test_un_administrador_puede_desactivar_un_producto_sin_borrarlo(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'active' => true]);
        ProductVariant::factory()->create(['product_id' => $product->id]);

        $response = $this->putJson("/api/admin/products/{$product->id}", $this->datosProducto($category, [
            'active' => false,
            'variants' => [['talle' => 'Único', 'sku' => 'SIGUE-1', 'stock' => 5]],
        ]));

        $response->assertOk()->assertJsonPath('data.active', false);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_un_administrador_puede_subir_una_imagen_de_producto(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->admin()->create());
        $product = Product::factory()->create();

        $response = $this->postJson("/api/admin/products/{$product->id}/images", [
            // create() en vez de image() porque el PHP de XAMPP no trae la extensión GD.
            'image' => UploadedFile::fake()->create('cadena.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertCreated();
        $this->assertDatabaseCount('product_images', 1);
        Storage::disk('public')->assertExists('products/'.basename($response->json('data.url')));
    }

    public function test_rechaza_un_archivo_que_no_es_una_imagen(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->admin()->create());
        $product = Product::factory()->create();

        $response = $this->postJson("/api/admin/products/{$product->id}/images", [
            'image' => UploadedFile::fake()->create('documento.pdf', 100),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('image');
    }

    public function test_un_administrador_puede_eliminar_una_imagen(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->admin()->create());
        $product = Product::factory()->create();
        $image = $product->images()->create(['url' => 'https://ejemplo.com/foto.jpg', 'order' => 0]);

        $response = $this->deleteJson("/api/admin/products/{$product->id}/images/{$image->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
    }

    public function test_un_administrador_puede_eliminar_un_producto(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        $response = $this->deleteJson("/api/admin/products/{$product->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
    }

    public function test_eliminar_un_producto_no_rompe_el_historial_de_pedidos(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $item = \App\Models\OrderItem::factory()->create([
            'product_variant_id' => $variant->id,
            'product_name' => 'Cadena de prueba',
        ]);

        $this->deleteJson("/api/admin/products/{$product->id}")->assertNoContent();

        $this->assertDatabaseHas('order_items', ['id' => $item->id, 'product_variant_id' => null, 'product_name' => 'Cadena de prueba']);
    }

    public function test_un_cliente_no_puede_eliminar_un_producto(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->create();

        $this->deleteJson("/api/admin/products/{$product->id}")->assertForbidden();
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
}
