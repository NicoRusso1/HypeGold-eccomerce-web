<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Catalog\ProductIndexRequest;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    /**
     * Lista los productos activos, con paginación, orden, búsqueda y filtros.
     */
    #[OA\Get(
        path: '/products',
        summary: 'Listar productos',
        tags: ['Catálogo'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', description: 'Búsqueda por nombre', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'category', in: 'query', description: 'Slug de categoría', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'material', in: 'query', description: 'Material exacto', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'min_price', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'max_price', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                schema: new OA\Schema(type: 'string', enum: ['recientes', 'precio_asc', 'precio_desc', 'nombre']),
            ),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 12)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado paginado de productos',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Cadena cubana'),
                                    new OA\Property(property: 'slug', type: 'string', example: 'cadena-cubana'),
                                    new OA\Property(property: 'description', type: 'string', nullable: true),
                                    new OA\Property(property: 'material', type: 'string', example: 'Oro laminado'),
                                    new OA\Property(property: 'base_price', type: 'number', example: 45000),
                                    new OA\Property(property: 'total_stock', type: 'integer', example: 22),
                                    new OA\Property(property: 'image', type: 'string', nullable: true),
                                ],
                                type: 'object',
                            ),
                        ),
                        new OA\Property(property: 'meta', type: 'object'),
                        new OA\Property(property: 'links', type: 'object'),
                    ],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function index(ProductIndexRequest $request)
    {
        $filters = $request->validated();

        $query = Product::query()
            ->where('active', true)
            ->with(['category', 'variants', 'images']);

        if (! empty($filters['category'])) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $filters['category']));
        }

        if (! empty($filters['material'])) {
            $query->where('material', $filters['material']);
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        if (isset($filters['min_price'])) {
            $query->where('base_price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('base_price', '<=', $filters['max_price']);
        }

        match ($filters['sort'] ?? 'recientes') {
            'precio_asc' => $query->orderBy('base_price'),
            'precio_desc' => $query->orderByDesc('base_price'),
            'nombre' => $query->orderBy('name'),
            default => $query->latest(),
        };

        $products = $query->paginate($filters['per_page'] ?? 12)->withQueryString();

        return ProductResource::collection($products);
    }

    /**
     * Muestra el detalle de un producto activo: descripción, imágenes y variantes con stock.
     */
    #[OA\Get(
        path: '/products/{slug}',
        summary: 'Ver el detalle de un producto',
        tags: ['Catálogo'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detalle del producto',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Cadena cubana'),
                                new OA\Property(property: 'slug', type: 'string', example: 'cadena-cubana'),
                                new OA\Property(property: 'description', type: 'string', nullable: true),
                                new OA\Property(property: 'material', type: 'string', example: 'Oro laminado'),
                                new OA\Property(property: 'base_price', type: 'number', example: 45000),
                                new OA\Property(property: 'total_stock', type: 'integer', example: 22),
                                new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'variants', type: 'array', items: new OA\Items(type: 'object')),
                            ],
                            type: 'object',
                        ),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(response: 404, description: 'Producto no encontrado'),
        ],
    )]
    public function show(string $slug)
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('active', true)
            ->with(['category', 'variants', 'images'])
            ->firstOrFail();

        return new ProductDetailResource($product);
    }
}
