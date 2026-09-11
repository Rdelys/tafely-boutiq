<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commande extends Model
{
    protected $fillable = [
        'user_id',
        'produit_id',
        'nom_client',
        'telephone_client',
        'quantite',
        'mode',
        'date_recuperation',
        'heure_recuperation',
        'adresse_livraison',
        'prix_unitaire',
        'prix_livraison',
        'total',
        'statut',
        'stock_decremente',
    ];

    protected function casts(): array
    {
        return [
            'quantite' => 'integer',
            'prix_unitaire' => 'integer',
            'prix_livraison' => 'integer',
            'total' => 'integer',
            'date_recuperation' => 'date',
            'stock_decremente' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function statutLabel(): string
    {
        return match ($this->statut) {
            'en_cours_de_livraison' => 'En cours de livraison',
            'livree' => 'Livrée',
            default => 'À prendre en compte',
        };
    }

    public function estALivrer(): bool
    {
        return $this->mode === 'livrer';
    }

    public function totalFormate(): string
    {
        return number_format($this->total, 0, ',', ' ').' Ar';
    }
}