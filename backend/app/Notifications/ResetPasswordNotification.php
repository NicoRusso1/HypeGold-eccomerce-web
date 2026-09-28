<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(public readonly string $token)
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
            '%s/cuenta/restablecer-contrasena?token=%s&email=%s',
            rtrim(config('app.frontend_url'), '/'),
            $this->token,
            urlencode($notifiable->getEmailForPasswordReset()),
        );

        return (new MailMessage)
            ->subject('Recuperá tu contraseña de HypeGold')
            ->greeting('¡Hola!')
            ->line('Recibimos un pedido para restablecer tu contraseña de HypeGold.')
            ->action('Restablecer contraseña', $url)
            ->line('Este enlace vence en 60 minutos.')
            ->line('Si vos no pediste esto, podés ignorar este mensaje.');
    }
}
