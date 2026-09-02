<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'email',
        'pseudo',
        'nom',
        'prenom',
        'nom_boutique',
        'adresse',
        'status',
        'email_notification',
        'email_notification_secondaire',
        'boutique_theme',
        'boutique_couleur',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    // "Test" ou "Actif payant" affiché dans le layout connecté
    public function statusLabel(): string
    {
        return match ($this->status) {
            'active' => 'Actif payant',
            'test' => 'Test',
            default => 'Gratuit',
        };
    }

    public function hasPseudo(): bool
    {
        return ! empty($this->pseudo);
    }

    public function produits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Produit::class);
    }

    // Lien public de la vitrine à partager. La page publique elle-même
    // (route "vitrine") n'est pas encore construite — ce lien est réservé
    // pour l'instant, prêt à être branché.
    public function lienBoutique(): string
    {
        return url('/b/'.($this->pseudo ?: $this->id));
    }
}