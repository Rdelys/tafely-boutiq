<?php

namespace App\Support;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;

class Settings
{
    private const CLE_CACHE = 'app_settings';

    /**
     * Durées suggérées comme raccourcis dans l'interface (l'utilisateur
     * peut toujours saisir n'importe quel autre nombre de mois).
     */
    public const DUREES_SUGGEREES = [1, 3, 6, 9, 12, 24];

    private const DEFAUTS = [
        'prix_abonnement' => 20000,
        'maintenance_inscriptions' => false,
        'maintenance_paiements' => false,
        'duree_max_mois' => 36,

        // Emplacements produits supplémentaires, achetés à l'unité ou par pas.
        'prix_par_produit' => 500,
        'pas_produits' => 5,
        'quantite_max_produits' => 100,

        // Paliers de réduction (%) appliqués au prix total selon le nombre
        // de mois souscrits.
        'reduction_trimestre' => 5,   // à partir de 3 mois
        'reduction_semestre' => 10,   // à partir de 6 mois
        'reduction_9_mois' => 15,     // à partir de 9 mois
        'reduction_annuel' => 20,     // à partir de 12 mois

        // Offre de lancement : les N premières boutiques inscrites, une fois
        // validées par l'admin, reçoivent des mois de plan payant offerts.
        'offre_lancement_active' => true,
        'offre_lancement_places' => 10,
        'offre_lancement_mois' => 1,
    ];

    public static function get(string $cle)
    {
        $valeurs = Cache::rememberForever(self::CLE_CACHE, fn () => AppSetting::pluck('value', 'key')->all());

        if (! array_key_exists($cle, $valeurs)) {
            return self::DEFAUTS[$cle] ?? null;
        }

        $brut = $valeurs[$cle];
        $defaut = self::DEFAUTS[$cle] ?? null;

        return match (true) {
            is_bool($defaut) => filter_var($brut, FILTER_VALIDATE_BOOLEAN),
            is_int($defaut) => (int) $brut,
            default => $brut,
        };
    }

    public static function set(string $cle, $valeur): void
    {
        AppSetting::updateOrCreate(
            ['key' => $cle],
            ['value' => is_bool($valeur) ? ($valeur ? '1' : '0') : (string) $valeur]
        );

        Cache::forget(self::CLE_CACHE);
    }

    public static function prixAbonnement(): int
    {
        return self::get('prix_abonnement');
    }

    public static function maintenanceInscriptions(): bool
    {
        return self::get('maintenance_inscriptions');
    }

    public static function maintenancePaiements(): bool
    {
        return self::get('maintenance_paiements');
    }

    public static function dureeMaxMois(): int
    {
        return self::get('duree_max_mois');
    }

    // ---- Offre de lancement ----

    public static function offreLancementActive(): bool
    {
        return self::get('offre_lancement_active');
    }

    public static function offreLancementPlaces(): int
    {
        return max(0, self::get('offre_lancement_places'));
    }

    public static function offreLancementMois(): int
    {
        return max(1, self::get('offre_lancement_mois'));
    }

    // ---- Emplacements produits supplémentaires ----

    public static function prixParProduit(): int
    {
        return self::get('prix_par_produit');
    }

    public static function pasProduits(): int
    {
        return max(1, self::get('pas_produits'));
    }

    public static function quantiteMaxProduits(): int
    {
        return max(self::pasProduits(), self::get('quantite_max_produits'));
    }

    /**
     * Prix total (Ar) pour un nombre d'emplacements produits.
     */
    public static function prixPourQuantiteProduits(int $quantite): int
    {
        return max(0, $quantite) * self::prixParProduit();
    }

    /**
     * Raccourcis proposés (1, 2, 3, 4, 6 et 10 fois le pas), limités au maximum autorisé.
     */
    public static function suggestionsQuantiteProduits(): array
    {
        $pas = self::pasProduits();
        $max = self::quantiteMaxProduits();

        return collect([1, 2, 3, 4, 6, 10])
            ->map(fn ($multiple) => $multiple * $pas)
            ->filter(fn ($quantite) => $quantite <= $max)
            ->unique()
            ->values()
            ->map(fn ($quantite) => [
                'quantite' => $quantite,
                'prix_total' => self::prixPourQuantiteProduits($quantite),
            ])
            ->all();
    }

    // ---- Abonnement : paliers de réduction ----

    public static function paliersReduction(): array
    {
        return [
            ['seuil' => 12, 'reduction' => self::get('reduction_annuel'), 'label' => 'Annuel'],
            ['seuil' => 9, 'reduction' => self::get('reduction_9_mois'), 'label' => '9 mois'],
            ['seuil' => 6, 'reduction' => self::get('reduction_semestre'), 'label' => 'Semestre'],
            ['seuil' => 3, 'reduction' => self::get('reduction_trimestre'), 'label' => 'Trimestre'],
            ['seuil' => 1, 'reduction' => 0, 'label' => 'Mensuel'],
        ];
    }

    public static function reductionPourDuree(int $mois): int
    {
        foreach (self::paliersReduction() as $palier) {
            if ($mois >= $palier['seuil']) {
                return $palier['reduction'];
            }
        }

        return 0;
    }

    public static function prixAbonnementPourDuree(int $mois): int
    {
        $mois = max(1, $mois);
        $prixMensuel = self::prixAbonnement();
        $reduction = self::reductionPourDuree($mois);

        return (int) round($prixMensuel * $mois * (100 - $reduction) / 100);
    }

    public static function detailDurees(array $mois = self::DUREES_SUGGEREES): array
    {
        return collect($mois)->map(function ($m) {
            $prixTotal = self::prixAbonnementPourDuree($m);

            return [
                'mois' => $m,
                'prix_total' => $prixTotal,
                'prix_mensuel_equivalent' => (int) round($prixTotal / $m),
                'reduction' => self::reductionPourDuree($m),
            ];
        })->all();
    }
}