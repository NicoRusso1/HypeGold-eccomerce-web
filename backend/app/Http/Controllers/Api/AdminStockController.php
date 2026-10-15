<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StockUpdateRequest;
use App\Http\Resources\AdminStockResource;
use App\Models\ProductVariant;
use OpenApi\Attributes as OA;

class AdminStockController extends Controller
{
    /**
     * Lista el stock de todas las variantes, ordenadas de menor a mayor
     * cantidad para que las que están por agotarse aparezcan primero.
     */
    #[OA\Get(
        path: '/admin/stock',
        summary: 'Listar el stock por variante (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Listado de stock por variante')],
    )]
    public function index()
    {
        $variants = ProductVariant::with('product.category')->orderBy('stock')->get();

        $threshold = config('shop.low_stock_threshold');

        return AdminStockResource::collection($variants)->additional([
            'meta' => [
                'low_stock_threshold' => $threshold,
                'low_stock_count' => $variants->where('stock', '<=', $threshold)->count(),
            ],
        ]);
    }

    /**
     * Ajusta manualmente el stock de una variante a la cantidad indicada.
     */
    #[OA\Put(
        path: '/admin/stock/{variant}',
        summary: 'Ajustar el stock de una variante (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'variant', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Stock actualizado')],
    )]
    public function update(StockUpdateRequest $request, ProductVariant $variant)
    {
        $variant->update(['stock' => $request->validated('stock')]);

        return new AdminStockResource($variant->load('product.category'));
    }
}
