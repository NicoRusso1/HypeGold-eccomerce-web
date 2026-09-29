<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class FavoriteController extends Controller
{
    /**
     * Lista los productos favoritos del usuario autenticado.
     */
    #[OA\Get(
        path: '/favorites',
        summary: 'Listar mis favoritos',
        tags: ['Favoritos'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Listado de productos favoritos')],
    )]
    public function index(Request $request)
    {
        $products = Product::query()
            ->whereHas('favorites', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with(['category', 'images', 'variants'])
            ->get();

        return ProductResource::collection($products);
    }

    /**
     * Agrega un producto a favoritos (idempotente).
     */
    #[OA\Post(
        path: '/favorites',
        summary: 'Agregar un producto a favoritos',
        tags: ['Favoritos'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            required: ['product_id'],
            properties: [new OA\Property(property: 'product_id', type: 'integer', example: 1)],
        )),
        responses: [new OA\Response(response: 201, description: 'Favorito agregado')],
    )]
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $request->user()->favorites()->firstOrCreate(['product_id' => $data['product_id']]);

        return response()->json(['message' => 'Agregado a favoritos.'], 201);
    }

    /**
     * Quita un producto de favoritos.
     */
    #[OA\Delete(
        path: '/favorites/{product}',
        summary: 'Quitar un producto de favoritos',
        tags: ['Favoritos'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Favorito eliminado')],
    )]
    public function destroy(Request $request, int $product)
    {
        $request->user()->favorites()->where('product_id', $product)->delete();

        return response()->noContent();
    }
}
