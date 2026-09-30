<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    protected $fillable = [
        'user_id', 'type', 'reference', 'montant', 'duree_mois', 'quantite', 'statut',
        'papi_transaction_id', 'papi_payment_method', 'meta', 'paye_le',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'integer',
            'duree_mois' => 'integer',
            'quantite' => 'integer',
            'meta' => 'array',
            'paye_le' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function estPaye(): bool
    {
        return $this->statut === 'paye';
    }

    public function montantFormate(): string
    {
        return number_format($this->montant, 0, ',', ' ').' Ar';
    }

    public function dureeLabel(): ?string
    {
        if ($this->type !== 'abonnement' || ! $this->duree_mois) {
            return null;
        }

        return $this->duree_mois.' mois';
    }

    /**
     * Nombre d'emplacements produits achetés. Les anciens paiements
     * (packs fixes) n'ont pas de quantité enregistrée : c'était toujours 10.
     */
    public function quantiteProduits(): int
    {
        return $this->quantite ?: 10;
    }

    /**
     * Libellé affiché dans les listes : "Abonnement · 3 mois" ou "+15 produits".
     */
    public function typeLabel(): string
    {
        if ($this->type === 'abonnement') {
            return $this->duree_mois ? 'Abonnement · '.$this->duree_mois.' mois' : 'Abonnement';
        }

        return '+'.$this->quantiteProduits().' produits';
    }
}