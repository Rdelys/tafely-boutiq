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

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $commande = $this->commande;
        $produit = $commande->produit;

        $message = (new MailMessage)
            ->subject('Nouvelle commande — '.$produit->nom)
            ->greeting('Vous avez reçu une nouvelle commande !')
            ->line('**Produit :** '.$produit->nom.' (x'.$commande->quantite.')')
            ->line('**Total :** '.$commande->totalFormate())
            ->line('**Client :** '.$commande->nom_client)
            ->line('**Téléphone :** '.$commande->telephone_client);

        if ($commande->mode === 'recuperer') {
            $date = $commande->date_recuperation?->format('d/m/Y');
            $message->line('**À récupérer** le '.($date ?: '—').' à '.($commande->heure_recuperation ?: '—'));
        } else {
            $message->line('**À livrer** à l\'adresse : '.$commande->adresse_livraison);
        }

        return $message
            ->line('Statut actuel : '.$commande->statutLabel())
            ->action('Voir mes commandes', route('commandes'))
            ->line('Merci de traiter cette commande depuis votre tableau de bord Tafely.');
    }
}