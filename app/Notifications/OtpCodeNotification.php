<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class OtpCodeNotification extends Notification
{
    use Queueable;

    public function __construct(protected string $code)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre code de vérification Tafely')
            ->greeting('Bonjour,')
            ->line('Voici votre code de vérification pour accéder à votre boutique Tafely :')
            ->line(new HtmlString(
                '<div style="font-size:32px;font-weight:700;letter-spacing:10px;text-align:center;'
                .'margin:24px 0;color:#1d4ed8;">'.$this->code.'</div>'
            ))
            ->line('Ce code est valable 10 minutes.')
            ->line("Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email.");
    }
}