<?php

namespace App\Services;

use App\Models\Order;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Resources\Payment;
use MercadoPago\Resources\Preference;

class MercadoPagoService
{
    public function __construct()
    {
        MercadoPagoConfig::setAccessToken((string) config('services.mercadopago.access_token'));
    }

    /**
     * Crea la preferencia de pago (Checkout Pro) para un pedido y devuelve
     * la URL a la que hay que redirigir al comprador.
     */
    public function crearPreferencia(Order $order): Preference
    {
        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
        $webhookUrl = rtrim((string) config('app.url'), '/').'/api/webhooks/mercadopago';

        $items = $order->items->map(fn ($item) => [
            'title' => $item->product_name.($item->variant_talle ? " ({$item->variant_talle})" : ''),
            'quantity' => $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'currency_id' => 'ARS',
        ])->values()->all();

        $client = new PreferenceClient();

        $request = [
            'items' => $items,
            'payer' => [
                'name' => $order->user->name,
                'email' => $order->user->email,
            ],
            'back_urls' => [
                'success' => "{$frontendUrl}/pago/exito?order={$order->id}",
                'pending' => "{$frontendUrl}/pago/pendiente?order={$order->id}",
                'failure' => "{$frontendUrl}/pago/error?order={$order->id}",
            ],
            'notification_url' => $webhookUrl,
            'external_reference' => (string) $order->id,
            'statement_descriptor' => 'HYPEGOLD',
        ];

        // "auto_return" (volver sola a la tienda tras un pago aprobado) exige
        // que las back_urls sean https; en desarrollo local (http) se omite
        // y el comprador vuelve con el botón que Mercado Pago muestra igual.
        if (str_starts_with($frontendUrl, 'https://')) {
            $request['auto_return'] = 'approved';
        }

        return $client->create($request);
    }

    /**
     * Trae el detalle de un pago directamente desde la API de Mercado Pago
     * (nunca confiamos en los datos que trae el webhook, solo en el id).
     */
    public function obtenerPago(int $paymentId): Payment
    {
        return (new PaymentClient())->get($paymentId);
    }
}
