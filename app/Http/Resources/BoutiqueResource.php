<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class BoutiqueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'identifiant' => $this->identifiantBoutique(),
            'lien' => $this->lienBoutique(),

            'nom' => $this->nom_boutique,
            'description' => $this->boutique_description,
            'logo' => $this->logo ? asset('storage/'.$this->logo) : null,

            'categorie' => $this->categorie_boutique ? [
                'cle' => $this->categorie_boutique,
                'libelle' => $this->categorieLibelle(),
            ] : null,

            // Coordonnées publiques
            'adresse' => $this->adresse,
            'telephone' => $this->telephone,
            'nif' => $this->nif,
            'stat' => $this->stat,

            // Position exacte sur la carte
            'localisation' => $this->aLocalisation() ? [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'libelle' => $this->localisation_libelle,
                'lien_maps' => $this->lienGoogleMaps(),
                'lien_itineraire' => $this->lienItineraire(),
            ] : null,

            // Apparence de la vitrine
            'theme' => $this->boutique_theme ?: 'classique',
            'couleur' => $this->couleurBoutique(),

            'nombre_produits' => (int) ($this->produits_count ?? 0),
            'cree_le' => $this->created_at?->toIso8601String(),

            // Présent seulement sur le détail d'une boutique
            'produits' => $this->when(
                $this->relationLoaded('produits'),
                fn () => ProduitResource::collection($this->produits)
            ),
        ];
    }
}