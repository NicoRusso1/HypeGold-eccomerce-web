<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MercadoPagoService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

class MercadoPagoWebhookController extends Controller
{
    public function __construct(private readonly MercadoPagoService $mercadoPago) {}

    /**
     * Recibe la notificación de Mercado Pago cuando cambia el estado de un
     * pago. Nunca confía en los datos del payload: siempre vuelve a
     * consultar el pago directamente a la API de Mercado Pago usando el id
     * recibido, y solo actualiza el pedido si el monto coincide. El proceso
     * es idempotente: una notificación repetida no vuelve a aplicar
     * cambios (la transición de estado y la columna única mp_payment_id lo
     * garantizan).
     */
    #[OA\Post(
        path: '/webhooks/mercadopago',
        summary: 'Webhook de notificaciones de Mercado Pago',
        tags: ['Pedidos'],
        responses: [new OA\Response(response: 200, description: 'Notificación recibida')],
    )]
    public function handle(Request $request)
    {
        $tipo = $request->input('type') ?? $request->query('topic');
        $paymentId = $request->input('data.id') ?? $request->query('id');

        // Ignoramos otros tipos de notificación (merchant_order, etc.): a
        // Mercado Pago solo le importa que respondamos 200 para no reintentar.
        if ($tipo !== 'payment' || ! $paymentId) {
            return response()->json(['message' => 'Ignorado'], 200);
        }

        try {
            $payment = $this->mercadoPago->obtenerPago((int) $paymentId);
        } catch (\Throwable $e) {
            Log::warning('No se pudo consultar el pago de Mercado Pago', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'No se pudo verificar el pago'], 200);
        }

        $order = Order::find($payment->external_reference);

        if (! $order) {
            Log::warning('Webhook de Mercado Pago para un pedido inexistente', [
                'payment_id' => $paymentId,
                'external_reference' => $payment->external_reference,
            ]);

            return response()->json(['message' => 'Pedido no encontrado'], 200);
        }

        // Defensa ante manipulación: el monto pagado tiene que coincidir con
        // el total del pedido antes de tocar nada.
        if (abs((float) $payment->transaction_amount - (float) $order->total) > 0.01) {
            Log::warning('El monto del pago de Mercado Pago no coincide con el del pedido', [
                'order_id' => $order->id,
                'payment_amount' => $payment->transaction_amount,
                'order_total' => $order->total,
            ]);

            return response()->json(['message' => 'El monto no coincide'], 200);
        }

        match ($payment->status) {
            'approved' => $this->marcarComoPagado($order, $payment->id),
            'rejected', 'cancelled' => $order->transicionarA(OrderStatus::Cancelado),
            default => null,
        };

        return response()->json(['message' => 'Notificación procesada'], 200);
    }

    private function marcarComoPagado(Order $order, int $paymentId): void
    {
        try {
            $order->mp_payment_id = $paymentId;
            $order->transicionarA(OrderStatus::Pagado);
        } catch (QueryException $e) {
            // mp_payment_id es unique: si esta notificación ya se procesó
            // (o el mismo pago llegó dos veces por una carrera), no hacemos nada más.
            Log::info('Notificación de pago de Mercado Pago repetida, se ignora', [
                'order_id' => $order->id,
                'payment_id' => $paymentId,
            ]);
        }
    }
}
