<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommandeLigne extends Model
{
    protected $fillable = [
        'commande_id',
        'produit_id',
        'nom_produit',
        'prix_unitaire',
        'prix_initial',
        'quantite',
        'sous_total',
    ];

    protected function casts(): array
    {
        return [
            'prix_unitaire' => 'integer',
            'prix_initial' => 'integer',
            'quantite' => 'integer',
            'sous_total' => 'integer',
        ];
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    // Vrai si le produit a été vendu moins cher que son prix de base.
    public function aRemise(): bool
    {
        return ! is_null($this->prix_initial) && $this->prix_initial > $this->prix_unitaire;
    }

    // Économie totale du client sur cette ligne (toutes quantités).
    public function economie(): int
    {
        return $this->aRemise() ? ($this->prix_initial - $this->prix_unitaire) * $this->quantite : 0;
    }

    public function prixUnitaireFormate(): string
    {
        return number_format($this->prix_unitaire, 0, ',', ' ').' Ar';
    }

    public function prixInitialFormate(): string
    {
        return number_format((int) $this->prix_initial, 0, ',', ' ').' Ar';
    }

    public function sousTotalFormate(): string
    {
        return number_format($this->sous_total, 0, ',', ' ').' Ar';
    }
}