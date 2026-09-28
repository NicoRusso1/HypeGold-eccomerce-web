<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
    /**
     * Lista todas las categorías, con la cantidad de productos activos de cada una.
     */
    #[OA\Get(
        path: '/categories',
        summary: 'Listar categorías',
        tags: ['Catálogo'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de categorías',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Cadenas'),
                                    new OA\Property(property: 'slug', type: 'string', example: 'cadenas'),
                                    new OA\Property(property: 'description', type: 'string', nullable: true),
                                    new OA\Property(property: 'products_count', type: 'integer', example: 4),
                                ],
                                type: 'object',
                            ),
                        ),
                    ],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function index()
    {
        $categories = Category::withCount([
            'products' => fn ($query) => $query->where('active', true),
        ])->orderBy('name')->get();

        return CategoryResource::collection($categories);
    }
}
