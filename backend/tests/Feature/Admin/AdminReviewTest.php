<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_cliente_no_puede_administrar_resenas(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/admin/reviews')->assertForbidden();
    }

    public function test_un_visitante_no_autenticado_no_puede_administrar_resenas(): void
    {
        $this->getJson('/api/admin/reviews')->assertUnauthorized();
    }

    public function test_un_administrador_puede_listar_resenas(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        Review::factory()->count(2)->create();

        $response = $this->getJson('/api/admin/reviews');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_puede_filtrar_resenas_por_producto(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $producto = Product::factory()->create();
        Review::factory()->create(['product_id' => $producto->id]);
        Review::factory()->create();

        $response = $this->getJson("/api/admin/reviews?product_id={$producto->id}");

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_un_administrador_puede_crear_una_resena(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $producto = Product::factory()->create();

        $response = $this->postJson('/api/admin/reviews', [
            'product_id' => $producto->id,
            'author_name' => 'Marcela G.',
            'rating' => 5,
            'comment' => 'Hermosa la cadena, llegó rapidísimo.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.author_name', 'Marcela G.')
            ->assertJsonPath('data.rating', 5)
            ->assertJsonPath('data.product.id', $producto->id);
        $this->assertDatabaseHas('reviews', ['author_name' => 'Marcela G.', 'product_id' => $producto->id]);
    }

    public function test_la_calificacion_debe_estar_entre_1_y_5(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $producto = Product::factory()->create();

        $this->postJson('/api/admin/reviews', [
            'product_id' => $producto->id,
            'author_name' => 'Marcela G.',
            'rating' => 6,
        ])->assertStatus(422)->assertJsonValidationErrors('rating');
    }

    public function test_el_producto_debe_existir(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson('/api/admin/reviews', [
            'product_id' => 99999,
            'author_name' => 'Marcela G.',
            'rating' => 5,
        ])->assertStatus(422)->assertJsonValidationErrors('product_id');
    }

    public function test_un_administrador_puede_editar_una_resena(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $review = Review::factory()->create(['author_name' => 'Marcela G.', 'rating' => 4]);

        $response = $this->putJson("/api/admin/reviews/{$review->id}", [
            'product_id' => $review->product_id,
            'author_name' => 'Marcela Gómez',
            'rating' => 5,
            'comment' => 'Editado',
        ]);

        $response->assertOk()->assertJsonPath('data.author_name', 'Marcela Gómez')->assertJsonPath('data.rating', 5);
    }

    public function test_un_administrador_puede_eliminar_una_resena(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $review = Review::factory()->create();

        $this->deleteJson("/api/admin/reviews/{$review->id}")->assertNoContent();
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }
}
