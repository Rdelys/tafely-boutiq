<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Produit
 */
class ProduitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $enRupture = ! is_null($this->stock) && $this->stock <= 0;

        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'description' => $this->description,

            // Prix de base et prix après remise (en Ariary)
            'prix' => $this->prix,
            'prix_formate' => $this->prixFormate(),
            'prix_final' => $this->prixFinal(),
            'prix_final_formate' => $this->prixFinalFormate(),

            'en_promo' => $this->aRemise(),
            'remise' => $this->aRemise() ? [
                'type' => $this->remise_type,
                'valeur' => $this->remise_valeur,
                'libelle' => $this->remiseLabel(),
            ] : null,

            'image' => $this->image ? asset('storage/'.$this->image) : null,

            // stock = null : stock non suivi
            'stock' => $this->stock,
            'en_rupture' => $enRupture,
            'disponible' => ! $enRupture,

            'livraison' => [
                'disponible' => $this->aLivraison(),
                'prix' => $this->aLivraison() ? $this->prix_livraison : null,
                'prix_formate' => $this->aLivraison()
                    ? number_format((int) $this->prix_livraison, 0, ',', ' ').' Ar'
                    : null,
            ],

            'cree_le' => $this->created_at?->toIso8601String(),
        ];
    }
}