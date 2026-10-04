<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminOtpCodeNotification extends Notification
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
            ->subject('Code de vérification — Administration Tafely')
            ->view('emails.otp', [
                'code' => $this->code,
                'admin' => true,
                'minutes' => 10,
            ]);
    }
}