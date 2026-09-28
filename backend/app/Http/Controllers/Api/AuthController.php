<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    /**
     * Devuelve el usuario autenticado.
     *
     * Endpoint de ejemplo para verificar que la documentación de Swagger
     * y la autenticación con Sanctum funcionan de punta a punta.
     */
    #[OA\Get(
        path: '/user',
        summary: 'Obtener el usuario autenticado',
        description: 'Devuelve los datos del usuario dueño del token Sanctum enviado en el header Authorization.',
        security: [['bearerAuth' => []]],
        tags: ['Autenticación'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuario autenticado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Nicolás Russo'),
                        new OA\Property(property: 'email', type: 'string', example: 'nico@hypegold.com'),
                        new OA\Property(property: 'email_verified_at', type: 'string', format: 'date-time', nullable: true),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function me(Request $request)
    {
        return $request->user();
    }
}
