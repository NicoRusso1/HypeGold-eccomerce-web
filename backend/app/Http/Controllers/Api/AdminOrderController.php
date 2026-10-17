<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\OrderStatusRequest;
use App\Http\Resources\AdminOrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AdminOrderController extends Controller
{
    /**
     * Lista todos los pedidos de todos los clientes, con filtro opcional por estado.
     */
    #[OA\Get(
        path: '/admin/orders',
        summary: 'Listar pedidos (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [new OA\Response(response: 200, description: 'Listado paginado de pedidos')],
    )]
    public function index(Request $request)
    {
        $orders = Order::query()
            ->with('user')
            ->withCount('items')
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('search'), function ($query, $search) {
                $termino = mb_strtolower($search);

                $query->where(function ($q) use ($search, $termino) {
                    $q->when(is_numeric($search), fn ($q2) => $q2->orWhere('id', (int) $search))
                        ->orWhereHas('user', function ($q2) use ($termino) {
                            $q2->whereRaw('LOWER(name) LIKE ?', ['%'.$termino.'%'])
                                ->orWhereRaw('LOWER(email) LIKE ?', ['%'.$termino.'%']);
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return AdminOrderResource::collection($orders);
    }

    /**
     * Muestra el detalle de un pedido, con los datos del cliente y del envío.
     */
    #[OA\Get(
        path: '/admin/orders/{order}',
        summary: 'Ver el detalle de un pedido (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Detalle del pedido')],
    )]
    public function show(Order $order)
    {
        return new AdminOrderResource($order->load(['user', 'items']));
    }

    /**
     * Cambia el estado de un pedido (por ejemplo, marcarlo como enviado).
     */
    #[OA\Put(
        path: '/admin/orders/{order}/status',
        summary: 'Cambiar el estado de un pedido (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Estado actualizado'),
            new OA\Response(response: 422, description: 'Transición de estado inválida'),
        ],
    )]
    public function updateStatus(OrderStatusRequest $request, Order $order)
    {
        $nuevoEstado = OrderStatus::from($request->validated('status'));

        if (! $order->transicionarA($nuevoEstado)) {
            throw ValidationException::withMessages([
                'status' => ["No se puede pasar de \"{$order->status->value}\" a \"{$nuevoEstado->value}\"."],
            ]);
        }

        return new AdminOrderResource($order->load(['user', 'items']));
    }
}
