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
        'quantite',
        'sous_total',
    ];

    protected function casts(): array
    {
        return [
            'prix_unitaire' => 'integer',
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

    public function prixUnitaireFormate(): string
    {
        return number_format($this->prix_unitaire, 0, ',', ' ').' Ar';
    }

    public function sousTotalFormate(): string
    {
        return number_format($this->sous_total, 0, ',', ' ').' Ar';
    }
}