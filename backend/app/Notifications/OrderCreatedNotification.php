<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification
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

        $mail = (new MailMessage)
            ->subject("Confirmamos tu pedido #{$this->order->id} · HypeGold")
            ->greeting('¡Gracias por tu compra!')
            ->line("Recibimos tu pedido #{$this->order->id} y ya lo estamos preparando.");

        foreach ($this->order->items as $item) {
            $detalle = $item->variant_talle ? "{$item->product_name} ({$item->variant_talle})" : $item->product_name;
            $mail->line("{$item->quantity} x {$detalle} — ".number_format($item->subtotal, 0, ',', '.').' ARS');
        }

        return $mail
            ->line('**Total: $'.number_format((float) $this->order->total, 0, ',', '.').' ARS**')
            ->line("Lo enviamos a: {$this->order->shipping_street}, {$this->order->shipping_city}, {$this->order->shipping_province}.")
            ->action('Ver mi pedido', $url)
            ->line('Te vamos a avisar por email cada vez que cambie el estado de tu pedido.');
    }
}
