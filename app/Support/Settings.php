<?php

namespace App\Support;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;

class Settings
{
    private const CLE_CACHE = 'app_settings';

    private const DEFAUTS = [
        'prix_abonnement' => 20000,
        'prix_pack_produits' => 5000,
        'maintenance_inscriptions' => false,
        'maintenance_paiements' => false,
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
}