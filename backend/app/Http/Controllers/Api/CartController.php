<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Cart\AddCartItemRequest;
use App\Http\Requests\Api\Cart\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class CartController extends Controller
{
    /**
     * Devuelve el carrito del usuario autenticado (lo crea si no existe).
     */
    #[OA\Get(
        path: '/cart',
        summary: 'Ver el carrito',
        tags: ['Carrito'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Carrito del usuario')],
    )]
    public function index(Request $request)
    {
        $cart = $this->cartDe($request)->load(['items.variant.product.images']);

        return (new CartResource($cart))->response()->setStatusCode(200);
    }

    /**
     * Agrega un producto (variante) al carrito, sumando la cantidad si ya estaba.
     */
    #[OA\Post(
        path: '/cart/items',
        summary: 'Agregar un producto al carrito',
        tags: ['Carrito'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            required: ['product_variant_id'],
            properties: [
                new OA\Property(property: 'product_variant_id', type: 'integer', example: 1),
                new OA\Property(property: 'quantity', type: 'integer', example: 1),
            ],
        )),
        responses: [
            new OA\Response(response: 201, description: 'Carrito actualizado'),
            new OA\Response(response: 422, description: 'Stock insuficiente'),
        ],
    )]
    public function addItem(AddCartItemRequest $request)
    {
        $cart = $this->cartDe($request);
        $variant = ProductVariant::findOrFail($request->validated('product_variant_id'));
        $quantitySolicitada = $request->validated('quantity', 1);

        $item = $cart->items()->where('product_variant_id', $variant->id)->first();
        $quantityTotal = ($item?->quantity ?? 0) + $quantitySolicitada;

        $this->validarStock($variant, $quantityTotal);

        if ($item) {
            $item->update(['quantity' => $quantityTotal]);
        } else {
            $cart->items()->create([
                'product_variant_id' => $variant->id,
                'quantity' => $quantityTotal,
            ]);
        }

        return (new CartResource($cart->load(['items.variant.product.images'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Cambia la cantidad de un item del carrito.
     */
    #[OA\Put(
        path: '/cart/items/{item}',
        summary: 'Cambiar la cantidad de un item del carrito',
        tags: ['Carrito'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'item', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Carrito actualizado'),
            new OA\Response(response: 422, description: 'Stock insuficiente'),
        ],
    )]
    public function updateItem(UpdateCartItemRequest $request, int $item)
    {
        $cart = $this->cartDe($request);
        $cartItem = $cart->items()->with('variant')->findOrFail($item);
        $quantity = $request->validated('quantity');

        $this->validarStock($cartItem->variant, $quantity);

        $cartItem->update(['quantity' => $quantity]);

        return (new CartResource($cart->load(['items.variant.product.images'])))->response()->setStatusCode(200);
    }

    /**
     * Quita un item del carrito.
     */
    #[OA\Delete(
        path: '/cart/items/{item}',
        summary: 'Quitar un item del carrito',
        tags: ['Carrito'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'item', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Carrito actualizado')],
    )]
    public function removeItem(Request $request, int $item)
    {
        $cart = $this->cartDe($request);
        $cart->items()->findOrFail($item)->delete();

        return (new CartResource($cart->load(['items.variant.product.images'])))->response()->setStatusCode(200);
    }

    /**
     * Vacía el carrito.
     */
    #[OA\Delete(
        path: '/cart',
        summary: 'Vaciar el carrito',
        tags: ['Carrito'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Carrito vacío')],
    )]
    public function clear(Request $request)
    {
        $cart = $this->cartDe($request);
        $cart->items()->delete();

        return (new CartResource($cart->load(['items.variant.product.images'])))->response()->setStatusCode(200);
    }

    private function cartDe(Request $request)
    {
        return $request->user()->cart()->firstOrCreate([]);
    }

    private function validarStock(ProductVariant $variant, int $quantitySolicitada): void
    {
        if ($quantitySolicitada > $variant->stock) {
            throw ValidationException::withMessages([
                'quantity' => ["No hay stock suficiente. Disponible: {$variant->stock}."],
            ]);
        }
    }
}
