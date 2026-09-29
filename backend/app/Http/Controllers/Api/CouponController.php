<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Coupon\CouponRequest;
use App\Http\Requests\Api\Coupon\ValidateCouponRequest;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class CouponController extends Controller
{
    /**
     * Valida un cupón contra el carrito del usuario autenticado y devuelve
     * el descuento y el total con descuento aplicado, sin confirmarlo.
     */
    #[OA\Post(
        path: '/coupons/validate',
        summary: 'Validar un cupón contra el carrito actual',
        tags: ['Cupones'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            required: ['code'],
            properties: [new OA\Property(property: 'code', type: 'string', example: 'BIENVENIDO10')],
        )),
        responses: [
            new OA\Response(response: 200, description: 'Cupón válido, con el descuento calculado'),
            new OA\Response(response: 422, description: 'Cupón inválido, vencido o sin uso disponible'),
        ],
    )]
    public function validateCoupon(ValidateCouponRequest $request)
    {
        $coupon = Coupon::whereRaw('UPPER(code) = ?', [strtoupper($request->validated('code'))])->first();

        if (! $coupon) {
            throw ValidationException::withMessages(['code' => ['No encontramos ese cupón.']]);
        }

        if (! $coupon->isValid()) {
            throw ValidationException::withMessages(['code' => [$coupon->invalidReason()]]);
        }

        $cart = $request->user()->cart()->firstOrCreate([]);
        $subtotal = $cart->calculateSubtotal();

        if ($subtotal <= 0) {
            throw ValidationException::withMessages(['code' => ['Tu carrito está vacío.']]);
        }

        $discount = $coupon->calculateDiscount($subtotal);

        return response()->json([
            'data' => [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => (float) $coupon->value,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => round($subtotal - $discount, 2),
            ],
        ]);
    }

    /**
     * Lista los cupones (solo administradores).
     */
    #[OA\Get(
        path: '/admin/coupons',
        summary: 'Listar cupones (admin)',
        tags: ['Cupones'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Listado de cupones')],
    )]
    public function index()
    {
        return CouponResource::collection(Coupon::latest()->get());
    }

    /**
     * Crea un cupón (solo administradores).
     */
    #[OA\Post(
        path: '/admin/coupons',
        summary: 'Crear un cupón (admin)',
        tags: ['Cupones'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 201, description: 'Cupón creado')],
    )]
    public function store(CouponRequest $request)
    {
        $coupon = Coupon::create($request->validated());

        return (new CouponResource($coupon))->response()->setStatusCode(201);
    }

    /**
     * Actualiza un cupón (solo administradores).
     */
    #[OA\Put(
        path: '/admin/coupons/{coupon}',
        summary: 'Actualizar un cupón (admin)',
        tags: ['Cupones'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'coupon', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Cupón actualizado')],
    )]
    public function update(CouponRequest $request, Coupon $coupon)
    {
        $coupon->update($request->validated());

        return new CouponResource($coupon);
    }

    /**
     * Elimina un cupón (solo administradores).
     */
    #[OA\Delete(
        path: '/admin/coupons/{coupon}',
        summary: 'Eliminar un cupón (admin)',
        tags: ['Cupones'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'coupon', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Cupón eliminado')],
    )]
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return response()->noContent();
    }
}
