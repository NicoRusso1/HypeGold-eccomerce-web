<?php

namespace Tests\Feature\Assistant;

use App\Models\AssistantMessage;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AssistantChatTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGeminiReply(string $reply, array $productIds): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode(['reply' => $reply, 'product_ids' => $productIds])],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);
    }

    public function test_responde_con_los_productos_que_recomienda_la_ia(): void
    {
        $producto = Product::factory()->create(['name' => 'Cadena cubana']);
        ProductVariant::factory()->for($producto)->create(['stock' => 5]);

        $this->fakeGeminiReply('¡Te va a encantar esta cadena!', [$producto->id]);

        $response = $this->postJson('/api/assistant/chat', [
            'session_id' => 'sesion-1',
            'message' => 'Busco una cadena',
        ]);

        $response->assertOk()
            ->assertJsonPath('reply', '¡Te va a encantar esta cadena!')
            ->assertJsonPath('products.0.id', $producto->id)
            ->assertJsonPath('products.0.name', 'Cadena cubana');

        $this->assertDatabaseHas('assistant_messages', [
            'session_id' => 'sesion-1',
            'role' => 'user',
            'content' => 'Busco una cadena',
        ]);
        $this->assertDatabaseHas('assistant_messages', [
            'session_id' => 'sesion-1',
            'role' => 'assistant',
        ]);
    }

    public function test_ignora_ids_de_producto_que_la_ia_invento(): void
    {
        $producto = Product::factory()->create();
        ProductVariant::factory()->for($producto)->create(['stock' => 5]);

        // La IA "alucina" un id que no está en la lista de candidatos (999999).
        $this->fakeGeminiReply('Mirá esta opción', [$producto->id, 999999]);

        $response = $this->postJson('/api/assistant/chat', [
            'session_id' => 'sesion-2',
            'message' => 'Algo lindo',
        ]);

        $response->assertOk()->assertJsonCount(1, 'products');

        $guardado = AssistantMessage::where('role', 'assistant')->first();
        $this->assertSame([$producto->id], $guardado->product_ids);
    }

    public function test_requiere_session_id_y_message(): void
    {
        $response = $this->postJson('/api/assistant/chat', []);

        $response->assertStatus(422)->assertJsonValidationErrors(['session_id', 'message']);
    }

    public function test_el_mensaje_no_puede_superar_500_caracteres(): void
    {
        $response = $this->postJson('/api/assistant/chat', [
            'session_id' => 'sesion-3',
            'message' => str_repeat('a', 501),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['message']);
    }

    public function test_devuelve_502_si_gemini_falla(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => 'falló'], 500),
        ]);

        $response = $this->postJson('/api/assistant/chat', [
            'session_id' => 'sesion-4',
            'message' => 'Hola',
        ]);

        $response->assertStatus(502);
    }
}
