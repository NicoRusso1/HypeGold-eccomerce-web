<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\DashboardFilterRequest;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class AdminDashboardController extends Controller
{
    /**
     * Estados que cuentan como venta efectiva (se excluyen pendiente y
     * cancelado, que todavía no representan un ingreso confirmado).
     */
    private const ESTADOS_VENDIDOS = ['pagado', 'enviado', 'entregado'];

    /**
     * Devuelve el resumen de ventas para el dashboard del panel de
     * administración: total vendido, pedidos por estado, productos más
     * vendidos y ventas por día, todo filtrable por rango de fechas.
     */
    #[OA\Get(
        path: '/admin/dashboard',
        summary: 'Resumen de ventas (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [new OA\Response(response: 200, description: 'Resumen de ventas')],
    )]
    public function index(DashboardFilterRequest $request)
    {
        $desde = $request->validated('from')
            ? Carbon::parse($request->validated('from'))->startOfDay()
            : now()->subDays(29)->startOfDay();

        $hasta = $request->validated('to')
            ? Carbon::parse($request->validated('to'))->endOfDay()
            : now()->endOfDay();

        $pedidosEnRango = Order::whereBetween('created_at', [$desde, $hasta]);
        $idsVendidos = (clone $pedidosEnRango)->whereIn('status', self::ESTADOS_VENDIDOS)->pluck('id');

        $totalVentas = (clone $pedidosEnRango)->whereIn('status', self::ESTADOS_VENDIDOS)->sum('total');

        $conteoPorEstado = (clone $pedidosEnRango)
            ->select('status', DB::raw('count(*) as cantidad'))
            ->groupBy('status')
            ->pluck('cantidad', 'status');

        $pedidosPorEstado = collect(OrderStatus::cases())
            ->mapWithKeys(fn (OrderStatus $estado) => [$estado->value => (int) ($conteoPorEstado[$estado->value] ?? 0)]);

        $productosMasVendidos = OrderItem::query()
            ->whereIn('order_id', $idsVendidos)
            ->select('product_name', DB::raw('sum(quantity) as cantidad'))
            ->groupBy('product_name')
            ->orderByDesc('cantidad')
            ->limit(5)
            ->get()
            ->map(fn ($fila) => ['product_name' => $fila->product_name, 'cantidad' => (int) $fila->cantidad]);

        $ventasPorDia = Order::query()
            ->whereIn('id', $idsVendidos)
            ->select(DB::raw('DATE(created_at) as fecha'), DB::raw('sum(total) as total'))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->map(fn ($fila) => ['fecha' => $fila->fecha, 'total' => (float) $fila->total]);

        return response()->json([
            'data' => [
                'total_ventas' => (float) $totalVentas,
                'cantidad_ventas' => $idsVendidos->count(),
                'pedidos_por_estado' => $pedidosPorEstado,
                'productos_mas_vendidos' => $productosMasVendidos,
                'ventas_por_dia' => $ventasPorDia,
            ],
            'meta' => [
                'from' => $desde->toDateString(),
                'to' => $hasta->toDateString(),
            ],
        ]);
    }
}
