<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    /**
     * Registra un nuevo usuario y le entrega un token de acceso.
     */
    #[OA\Post(
        path: '/register',
        summary: 'Registrar un nuevo usuario',
        description: 'Crea una cuenta de cliente y devuelve un token Sanctum para dejar la sesión iniciada.',
        tags: ['Autenticación'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Nicolás Russo'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'nico@hypegold.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'contraseña-segura'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'contraseña-segura'),
                ],
                type: 'object',
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuario registrado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'user',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Nicolás Russo'),
                                new OA\Property(property: 'email', type: 'string', example: 'nico@hypegold.com'),
                            ],
                            type: 'object',
                        ),
                        new OA\Property(property: 'token', type: 'string', example: '1|abcdef123456...'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 422,
                description: 'Datos inválidos (email duplicado, contraseñas que no coinciden, etc.)',
            ),
        ],
    )]
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        $token = $user->createToken('hypegold-web')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

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
