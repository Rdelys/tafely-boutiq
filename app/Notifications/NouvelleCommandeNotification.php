<?php

namespace App\Notifications;

use App\Models\Commande;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NouvelleCommandeNotification extends Notification
{
    use Queueable;

    public function __construct(protected Commande $commande)
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
        $this->commande->loadMissing('lignes');

        return (new MailMessage)
            ->subject('Nouvelle commande '.$this->commande->numero)
            ->view('emails.nouvelle-commande', ['commande' => $this->commande]);
    }
}