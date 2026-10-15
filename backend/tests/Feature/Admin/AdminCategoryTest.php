<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_cliente_no_puede_administrar_categorias(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/admin/categories')->assertForbidden();
    }

    public function test_un_visitante_no_autenticado_no_puede_administrar_categorias(): void
    {
        $this->getJson('/api/admin/categories')->assertUnauthorized();
    }

    public function test_un_administrador_puede_listar_categorias_con_cantidad_de_productos(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $category = Category::factory()->create();
        Product::factory()->count(2)->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/admin/categories');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame(2, $response->json('data.0.products_count'));
    }

    public function test_un_administrador_puede_crear_una_categoria(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $response = $this->postJson('/api/admin/categories', [
            'name' => 'Pulseras',
            'description' => 'Pulseras de oro y plata.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Pulseras')
            ->assertJsonPath('data.slug', 'pulseras');
        $this->assertDatabaseHas('categories', ['name' => 'Pulseras', 'slug' => 'pulseras']);
    }

    public function test_crear_una_categoria_con_nombre_repetido_genera_un_slug_distinto(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        Category::factory()->create(['name' => 'Anillos', 'slug' => 'anillos']);

        $response = $this->postJson('/api/admin/categories', ['name' => 'Anillos']);

        $response->assertCreated()->assertJsonPath('data.slug', 'anillos-2');
    }

    public function test_el_nombre_de_la_categoria_es_obligatorio(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson('/api/admin/categories', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_un_administrador_puede_editar_una_categoria(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $category = Category::factory()->create(['name' => 'Aros', 'slug' => 'aros']);

        $response = $this->putJson("/api/admin/categories/{$category->id}", [
            'name' => 'Aros de plata',
            'description' => 'Actualizada.',
        ]);

        $response->assertOk()->assertJsonPath('data.name', 'Aros de plata');
        $this->assertSame('aros-de-plata', $category->fresh()->slug);
    }

    public function test_un_administrador_puede_eliminar_una_categoria_sin_productos(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $category = Category::factory()->create();

        $this->deleteJson("/api/admin/categories/{$category->id}")->assertNoContent();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_no_se_puede_eliminar_una_categoria_con_productos(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $this->deleteJson("/api/admin/categories/{$category->id}")
            ->assertStatus(422)
            ->assertJsonValidationErrors('category');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
