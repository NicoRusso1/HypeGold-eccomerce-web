<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Assistant\AssistantChatRequest;
use App\Http\Resources\AssistantProductResource;
use App\Services\ShoppingAssistantService;
use OpenApi\Attributes as OA;

class AssistantController extends Controller
{
    public function __construct(private readonly ShoppingAssistantService $assistant) {}

    /**
     * Le pasa el mensaje del cliente al asistente de compras con IA y
     * devuelve la respuesta junto con los productos recomendados.
     */
    #[OA\Post(
        path: '/assistant/chat',
        summary: 'Chatear con el asistente de compras',
        tags: ['Asistente'],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['session_id', 'message'],
                properties: [
                    new OA\Property(property: 'session_id', type: 'string'),
                    new OA\Property(property: 'message', type: 'string', maxLength: 500),
                    new OA\Property(property: 'history', type: 'array', items: new OA\Items(type: 'object')),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Respuesta del asistente con productos recomendados'),
            new OA\Response(response: 422, description: 'Error de validación'),
            new OA\Response(response: 502, description: 'El asistente no pudo responder'),
        ],
    )]
    public function chat(AssistantChatRequest $request)
    {
        $validated = $request->validated();

        try {
            $resultado = $this->assistant->responder(
                sessionId: $validated['session_id'],
                message: $validated['message'],
                history: $validated['history'] ?? [],
            );
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'No pudimos contactar al asistente. Probá de nuevo en un momento.',
            ], 502);
        }

        return response()->json([
            'reply' => $resultado['reply'],
            'products' => AssistantProductResource::collection($resultado['products']),
        ]);
    }
}
