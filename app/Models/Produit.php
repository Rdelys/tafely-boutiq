<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produit extends Model
{
    public const REMISE_POURCENTAGE = 'pourcentage';
    public const REMISE_MONTANT = 'montant';

    protected $table = 'produits';

    protected $fillable = [
        'user_id',
        'nom',
        'description',
        'prix',
        'remise_type',
        'remise_valeur',
        'image',
        'stock',
        'livraison',
        'prix_livraison',
    ];

    protected function casts(): array
    {
        return [
            'prix' => 'integer',
            'remise_valeur' => 'integer',
            'prix_livraison' => 'integer',
            'stock' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aLivraison(): bool
    {
        return $this->livraison === 'payante';
    }

    // ---- Prix et remise ----

    // Prix après application de la remise (= prix de base s'il n'y a pas de remise valide).
    public function prixFinal(): int
    {
        $prix = (int) $this->prix;
        $valeur = (int) $this->remise_valeur;

        if ($valeur <= 0) {
            return $prix;
        }

        return match ($this->remise_type) {
            self::REMISE_POURCENTAGE => max(0, (int) round($prix * (100 - min($valeur, 100)) / 100)),
            self::REMISE_MONTANT => max(0, $prix - $valeur),
            default => $prix,
        };
    }

    public function aRemise(): bool
    {
        return $this->prixFinal() < (int) $this->prix;
    }

    // Économie réalisée par le client sur une unité.
    public function economieUnitaire(): int
    {
        return (int) $this->prix - $this->prixFinal();
    }

    // "-20 %" ou "-5 000 Ar" (null si aucune remise).
    public function remiseLabel(): ?string
    {
        if (! $this->aRemise()) {
            return null;
        }

        return $this->remise_type === self::REMISE_POURCENTAGE
            ? '-'.(int) $this->remise_valeur.' %'
            : '-'.number_format((int) $this->remise_valeur, 0, ',', ' ').' Ar';
    }

    // Prix de base (avant remise).
    public function prixFormate(): string
    {
        return number_format($this->prix, 0, ',', ' ').' Ar';
    }

    // Prix réellement payé par le client (après remise).
    public function prixFinalFormate(): string
    {
        return number_format($this->prixFinal(), 0, ',', ' ').' Ar';
    }
}