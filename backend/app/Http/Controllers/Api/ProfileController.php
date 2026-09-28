<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Profile\UpdatePasswordRequest;
use App\Http\Requests\Api\Profile\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class ProfileController extends Controller
{
    /**
     * Devuelve los datos del usuario autenticado.
     */
    #[OA\Get(
        path: '/profile',
        summary: 'Ver mi perfil',
        security: [['bearerAuth' => []]],
        tags: ['Perfil'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Datos del perfil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Nicolás Russo'),
                        new OA\Property(property: 'email', type: 'string', example: 'nico@hypegold.com'),
                        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '+54 9 345 404 0162'),
                        new OA\Property(property: 'role', type: 'string', example: 'cliente'),
                    ],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function show(Request $request)
    {
        return $request->user();
    }

    /**
     * Actualiza nombre, email y teléfono del usuario autenticado.
     */
    #[OA\Put(
        path: '/profile',
        summary: 'Actualizar mi perfil',
        security: [['bearerAuth' => []]],
        tags: ['Perfil'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Nicolás Russo'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'nico@hypegold.com'),
                    new OA\Property(property: 'phone', type: 'string', nullable: true, example: '+54 9 345 404 0162'),
                ],
                type: 'object',
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Perfil actualizado'),
            new OA\Response(response: 422, description: 'Datos inválidos (email ya usado por otro usuario, etc.)'),
        ],
    )]
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $user->update($request->validated());

        return $user->fresh();
    }

    /**
     * Cambia la contraseña del usuario autenticado.
     */
    #[OA\Put(
        path: '/profile/password',
        summary: 'Cambiar mi contraseña',
        security: [['bearerAuth' => []]],
        tags: ['Perfil'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['current_password', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'current_password', type: 'string', format: 'password'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                ],
                type: 'object',
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Contraseña actualizada'),
            new OA\Response(response: 422, description: 'La contraseña actual no coincide, o la nueva no cumple los requisitos'),
        ],
    )]
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = $request->user();

        if (! Hash::check($request->validated('current_password'), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual no coincide.'],
            ]);
        }

        $user->update(['password' => Hash::make($request->validated('password'))]);

        return response()->json(['message' => 'Contraseña actualizada correctamente.']);
    }
}
