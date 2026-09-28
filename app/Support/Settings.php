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
        'prix_pack_produits' => 5000,
        'maintenance_inscriptions' => false,
        'maintenance_paiements' => false,
        'duree_max_mois' => 36,

        // Paliers de réduction (%) appliqués au prix total selon le nombre
        // de mois souscrits. Chaque palier s'applique à partir du seuil
        // indiqué et jusqu'au seuil supérieur (exclu).
        'reduction_trimestre' => 5,   // à partir de 3 mois
        'reduction_semestre' => 10,   // à partir de 6 mois
        'reduction_9_mois' => 15,     // à partir de 9 mois
        'reduction_annuel' => 20,     // à partir de 12 mois
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

    public static function prixPackProduits(): int
    {
        return self::get('prix_pack_produits');
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

    /**
     * Les paliers de réduction, triés du plus élevé (seuil le plus haut)
     * au plus bas — prêts à parcourir pour trouver le palier applicable,
     * ou à passer tels quels au JS pour un calcul en direct côté client.
     */
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

    /**
     * Réduction (%) applicable pour un nombre de mois donné.
     */
    public static function reductionPourDuree(int $mois): int
    {
        foreach (self::paliersReduction() as $palier) {
            if ($mois >= $palier['seuil']) {
                return $palier['reduction'];
            }
        }

        return 0;
    }

    /**
     * Prix total (Ar) pour la durée choisie, réduction déjà appliquée.
     */
    public static function prixAbonnementPourDuree(int $mois): int
    {
        $mois = max(1, $mois);
        $prixMensuel = self::prixAbonnement();
        $reduction = self::reductionPourDuree($mois);

        return (int) round($prixMensuel * $mois * (100 - $reduction) / 100);
    }

    /**
     * Détail calculé pour un ensemble de durées données (par défaut les
     * suggestions), prêt à afficher — mois, prix total, équivalent
     * mensuel, réduction appliquée.
     */
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