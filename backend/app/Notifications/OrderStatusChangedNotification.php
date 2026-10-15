<?php

namespace App\Notifications;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification
{
    public function __construct(public readonly Order $order)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = sprintf(
            '%s/cuenta/pedidos/%d',
            rtrim(config('app.frontend_url'), '/'),
            $this->order->id,
        );

        [$asunto, $saludo, $texto] = match ($this->order->status) {
            OrderStatus::Pagado => [
                'Confirmamos tu pago',
                '¡Tu pago fue acreditado!',
                "Ya confirmamos el pago de tu pedido #{$this->order->id}. Lo vamos a preparar para el envío.",
            ],
            OrderStatus::Enviado => [
                'Tu pedido está en camino',
                '¡Tu pedido salió hacia tu domicilio!',
                "Tu pedido #{$this->order->id} ya está en camino a {$this->order->shipping_city}, {$this->order->shipping_province}.",
            ],
            OrderStatus::Entregado => [
                'Tu pedido fue entregado',
                '¡Tu pedido llegó a destino!',
                "Tu pedido #{$this->order->id} fue entregado. ¡Esperamos que disfrutes tu compra!",
            ],
            OrderStatus::Cancelado => [
                'Tu pedido fue cancelado',
                'Cancelamos tu pedido',
                "Tu pedido #{$this->order->id} fue cancelado. Si no reconocés este cambio o tenés dudas, contactanos.",
            ],
            default => [
                'Actualizamos tu pedido',
                'Tu pedido cambió de estado',
                "Tu pedido #{$this->order->id} ahora está: {$this->order->status->value}.",
            ],
        };

        return (new MailMessage)
            ->subject("{$asunto} · Pedido #{$this->order->id} · HypeGold")
            ->greeting($saludo)
            ->line($texto)
            ->action('Ver mi pedido', $url);
    }
}
