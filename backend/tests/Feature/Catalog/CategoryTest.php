<?php

namespace Tests\Feature\Catalog;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_las_categorias_ordenadas_por_nombre(): void
    {
        Category::factory()->create(['name' => 'Pulseras']);
        Category::factory()->create(['name' => 'Anillos']);

        $response = $this->getJson('/api/categories');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertSame(['Anillos', 'Pulseras'], $names->all());
    }

    public function test_incluye_la_cantidad_de_productos_activos_por_categoria(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(2)->create(['category_id' => $category->id, 'active' => true]);
        Product::factory()->inactive()->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/categories');

        $response->assertOk()->assertJsonPath('data.0.products_count', 2);
    }

    public function test_el_listado_de_categorias_no_requiere_autenticacion(): void
    {
        $this->getJson('/api/categories')->assertOk();
    }
}
