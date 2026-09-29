<?php

namespace Tests\Feature\Catalog;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_requiere_el_parametro_q(): void
    {
        $this->getJson('/api/products/search')->assertStatus(422)->assertJsonValidationErrors('q');
    }

    public function test_devuelve_productos_que_coinciden_con_la_busqueda(): void
    {
        Product::factory()->create(['name' => 'Cadena cubana']);
        Product::factory()->create(['name' => 'Aros gota']);

        $response = $this->getJson('/api/products/search?q=cadena');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Cadena cubana', $response->json('data.0.name'));
    }

    public function test_no_incluye_productos_inactivos(): void
    {
        Product::factory()->inactive()->create(['name' => 'Cadena oculta']);

        $response = $this->getJson('/api/products/search?q=cadena');

        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
    }

    public function test_limita_la_cantidad_de_sugerencias_a_ocho(): void
    {
        $categoria = Category::factory()->create();
        Product::factory()->count(10)->create(['category_id' => $categoria->id, 'name' => 'Cadena de prueba']);

        $response = $this->getJson('/api/products/search?q=cadena');

        $response->assertOk();
        $this->assertCount(8, $response->json('data'));
    }

    public function test_la_busqueda_no_requiere_autenticacion(): void
    {
        Product::factory()->create(['name' => 'Cadena cubana']);

        $this->getJson('/api/products/search?q=cadena')->assertOk();
    }

    public function test_devuelve_vacio_si_no_hay_coincidencias(): void
    {
        Product::factory()->create(['name' => 'Cadena cubana']);

        $response = $this->getJson('/api/products/search?q=inexistente');

        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
    }
}
