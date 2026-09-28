<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'HypeGold API',
    description: 'API REST de HypeGold, tienda online de joyas. Documenta catálogo, autenticación, carrito, pedidos y administración.',
    contact: new OA\Contact(name: 'HypeGold Joyas', url: 'https://instagram.com/hypegold.arg'),
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'Servidor de la API',
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum token',
)]
#[OA\Tag(name: 'Autenticación', description: 'Registro, login, logout y recuperación de contraseña')]
#[OA\Tag(name: 'Catálogo', description: 'Categorías y productos')]
abstract class Controller
{
    //
}
