<?php

namespace App\Services;

use App\Models\AssistantMessage;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShoppingAssistantService
{
    private const MAX_PRODUCTOS = 10;

    /**
     * Busca productos candidatos, le pregunta a Gemini cuáles recomendar,
     * guarda el intercambio y devuelve la respuesta ya armada para la API.
     *
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array{reply: string, products: Collection<int, Product>}
     */
    public function responder(string $sessionId, string $message, array $history): array
    {
        $productos = $this->buscarCandidatos($message);

        $respuesta = $this->consultarGemini($message, $history, $productos);

        $idsValidos = collect($respuesta['product_ids'])
            ->filter(fn ($id) => $productos->contains('id', $id))
            ->values();

        AssistantMessage::create([
            'session_id' => $sessionId,
            'role' => 'user',
            'content' => $message,
        ]);

        AssistantMessage::create([
            'session_id' => $sessionId,
            'role' => 'assistant',
            'content' => $respuesta['reply'],
            'product_ids' => $idsValidos->all(),
        ]);

        return [
            'reply' => $respuesta['reply'],
            'products' => $productos->whereIn('id', $idsValidos)->values(),
        ];
    }

    /**
     * Trae hasta 10 productos activos con stock que puedan servir de
     * contexto para la IA: por palabras clave del mensaje, o los más
     * recientes si el mensaje no menciona nada puntual (ej. "hola").
     */
    private function buscarCandidatos(string $message): Collection
    {
        $base = Product::query()
            ->where('active', true)
            ->with(['category', 'images', 'variants'])
            ->whereHas('variants', fn ($q) => $q->where('stock', '>', 0));

        $palabras = collect(preg_split('/\s+/', mb_strtolower($message)))
            ->filter(fn ($palabra) => mb_strlen($palabra) >= 3);

        if ($palabras->isNotEmpty()) {
            $conPalabras = (clone $base)->where(function ($query) use ($palabras) {
                foreach ($palabras as $palabra) {
                    $query->orWhereRaw('LOWER(name) LIKE ?', ['%'.$palabra.'%'])
                        ->orWhereRaw('LOWER(material) LIKE ?', ['%'.$palabra.'%'])
                        ->orWhereHas('category', fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%'.$palabra.'%']));
                }
            })->limit(self::MAX_PRODUCTOS)->get();

            if ($conPalabras->isNotEmpty()) {
                return $conPalabras;
            }
        }

        return $base->latest()->limit(self::MAX_PRODUCTOS)->get();
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array{reply: string, product_ids: array<int, int>}
     */
    private function consultarGemini(string $message, array $history, Collection $productos): array
    {
        $listaProductos = $productos->map(fn (Product $producto) => [
            'id' => $producto->id,
            'nombre' => $producto->name,
            'categoria' => $producto->category?->name,
            'material' => $producto->material,
            'precio' => (float) $producto->base_price,
        ])->values()->all();

        $contents = collect($history)
            ->map(fn (array $turno) => [
                'role' => $turno['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $turno['content']]],
            ])
            ->push([
                'role' => 'user',
                'parts' => [[
                    'text' => "Productos disponibles ahora (en JSON):\n"
                        .json_encode($listaProductos, JSON_UNESCAPED_UNICODE)
                        ."\n\nMensaje del cliente: {$message}",
                ]],
            ])
            ->all();

        $modelo = config('services.gemini.model');

        $response = Http::withHeader('x-goog-api-key', (string) config('services.gemini.api_key'))
            ->timeout(25)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent", [
                'system_instruction' => ['parts' => [['text' => $this->systemPrompt()]]],
                'contents' => $contents,
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'responseSchema' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'reply' => ['type' => 'STRING'],
                            'product_ids' => ['type' => 'ARRAY', 'items' => ['type' => 'INTEGER']],
                        ],
                        'required' => ['reply', 'product_ids'],
                    ],
                ],
            ]);

        if ($response->failed()) {
            Log::error('Gemini respondió con error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('No se pudo contactar al asistente.');
        }

        $texto = $response->json('candidates.0.content.parts.0.text');
        $datos = json_decode((string) $texto, true);

        if (! is_array($datos) || ! isset($datos['reply'])) {
            Log::error('Gemini devolvió una respuesta inesperada', ['body' => $response->body()]);

            throw new \RuntimeException('El asistente devolvió una respuesta inválida.');
        }

        return [
            'reply' => (string) $datos['reply'],
            'product_ids' => array_map('intval', $datos['product_ids'] ?? []),
        ];
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
Sos el asistente de compras de HypeGold, una joyería online que vende cadenas y
pulseras de oro enchapado 18k. Hablás en español rioplatense, con un tono
amable, cercano y breve (2 o 3 oraciones como máximo).

Reglas:
- Recomendá SOLO productos de la lista que se te pasa en cada mensaje. Nunca
  inventes productos, precios ni características que no estén en esa lista.
- Recomendá como máximo 3 productos por respuesta.
- Si ningún producto de la lista encaja con lo que pide el cliente, decilo con
  sinceridad y hacé una pregunta para afinar la búsqueda (presupuesto,
  categoría, para quién es el regalo, etc.) en vez de forzar una recomendación.
- Siempre respondé en JSON válido con el schema indicado: "reply" (tu
  respuesta en texto) y "product_ids" (los ids de los productos recomendados,
  array vacío si no recomendás ninguno).
PROMPT;
    }
}
