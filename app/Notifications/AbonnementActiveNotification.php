<?php

namespace App\Notifications;

use App\Models\Paiement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AbonnementActiveNotification extends Notification
{
    use Queueable;

    public function __construct(protected Paiement $paiement)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)->subject('Paiement confirmé — Tafely')->greeting('Merci pour votre paiement !');

        if ($this->paiement->type === 'abonnement') {
            $message
                ->line('Votre abonnement Tafely est maintenant actif.')
                ->line('Il est valable jusqu\'au '.$notifiable->abonnement_expire_le->format('d/m/Y').'.');
        } else {
            $message->line('10 emplacements produits supplémentaires ont été ajoutés à votre boutique.');
        }

        return $message
            ->line('Référence : '.$this->paiement->reference)
            ->action('Aller à mon tableau de bord', route('dashboard'));
    }
}