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
}