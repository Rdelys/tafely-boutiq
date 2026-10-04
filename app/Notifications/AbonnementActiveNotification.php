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
        return (new MailMessage)
            ->subject('Paiement confirmé — Tafely')
            ->view('emails.paiement-confirme', [
                'paiement' => $this->paiement,
                'finAbonnement' => $notifiable->abonnement_expire_le?->format('d/m/Y'),
                'quantite' => $this->paiement->quantiteProduits(),
            ]);
    }
}