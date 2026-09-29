<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Order\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class OrderController extends Controller
{
    /**
     * Confirma la compra: crea el pedido a partir del carrito del usuario,
     * descuenta el stock y vacía el carrito, todo dentro de una transacción.
     */
    #[OA\Post(
        path: '/orders',
        summary: 'Confirmar la compra (checkout)',
        tags: ['Pedidos'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            required: ['address_id'],
            properties: [new OA\Property(property: 'address_id', type: 'integer', example: 1)],
        )),
        responses: [
            new OA\Response(response: 201, description: 'Pedido creado'),
            new OA\Response(response: 422, description: 'Carrito vacío o stock insuficiente'),
        ],
    )]
    public function store(CheckoutRequest $request)
    {
        $user = $request->user();
        $address = $user->addresses()->findOrFail($request->validated('address_id'));
        $couponCode = $request->validated('coupon_code');

        $order = DB::transaction(function () use ($user, $address, $couponCode) {
            $cart = $user->cart()->with('items')->first();

            if (! $cart || $cart->items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => ['El carrito está vacío.']]);
            }

            // Se bloquean las variantes para evitar condiciones de carrera con
            // otra compra simultánea sobre el mismo stock.
            $variantIds = $cart->items->pluck('product_variant_id');
            $variants = ProductVariant::with('product')
                ->whereIn('id', $variantIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($cart->items as $item) {
                $variant = $variants->get($item->product_variant_id);

                if (! $variant || $item->quantity > $variant->stock) {
                    $nombre = $variant?->product?->name ?? 'un producto';

                    throw ValidationException::withMessages([
                        'stock' => ["No hay stock suficiente de {$nombre}."],
                    ]);
                }
            }

            $subtotal = $cart->items->sum(function ($item) use ($variants) {
                $variant = $variants->get($item->product_variant_id);
                $precio = (float) $variant->product->base_price + (float) $variant->extra_price;

                return $precio * $item->quantity;
            });

            [$coupon, $discount] = $this->aplicarCupon($couponCode, $subtotal);

            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $address->id,
                'status' => 'pendiente',
                'total' => round($subtotal - $discount, 2),
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'discount' => $discount,
                'shipping_label' => $address->label,
                'shipping_street' => $address->street,
                'shipping_city' => $address->city,
                'shipping_province' => $address->province,
                'shipping_postal_code' => $address->postal_code,
                'shipping_phone' => $address->phone,
            ]);

            $coupon?->increment('times_used');

            foreach ($cart->items as $item) {
                $variant = $variants->get($item->product_variant_id);
                $precio = (float) $variant->product->base_price + (float) $variant->extra_price;

                $order->items()->create([
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_talle' => $variant->talle,
                    'sku' => $variant->sku,
                    'unit_price' => $precio,
                    'quantity' => $item->quantity,
                    'subtotal' => $precio * $item->quantity,
                ]);

                $variant->decrement('stock', $item->quantity);
            }

            $cart->items()->delete();

            return $order;
        });

        return (new OrderResource($order->load('items')))->response()->setStatusCode(201);
    }

    /**
     * Valida el cupón (si se mandó uno) y calcula el descuento sobre el
     * subtotal, dentro de la misma transacción para que el conteo de usos
     * quede consistente ante compras simultáneas.
     *
     * @return array{0: ?Coupon, 1: float}
     */
    private function aplicarCupon(?string $couponCode, float $subtotal): array
    {
        if (! $couponCode) {
            return [null, 0.0];
        }

        $coupon = Coupon::whereRaw('UPPER(code) = ?', [strtoupper($couponCode)])
            ->lockForUpdate()
            ->first();

        if (! $coupon) {
            throw ValidationException::withMessages(['coupon' => ['No encontramos ese cupón.']]);
        }

        if (! $coupon->isValid()) {
            throw ValidationException::withMessages(['coupon' => [$coupon->invalidReason()]]);
        }

        return [$coupon, $coupon->calculateDiscount($subtotal)];
    }
}
