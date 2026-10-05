<?php

namespace App\Support;

use App\Models\Connexion;
use App\Models\Visite;
use Carbon\Carbon;

class Audience
{
    public static function donnees(): array
    {
        $debutJour = now()->startOfDay();
        $il7Jours = now()->subDays(6)->startOfDay();
        $il14Jours = now()->subDays(13)->startOfDay();

        // ---- Visites ----
        $visitesParJour = Visite::where('created_at', '>=', $il14Jours)
            ->selectRaw('DATE(created_at) as jour, COUNT(*) as visites, COUNT(DISTINCT visiteur) as visiteurs')
            ->groupBy('jour')
            ->get()
            ->keyBy('jour');

        $graphVisites = collect(range(13, 0))->map(function ($i) use ($visitesParJour) {
            $jour = now()->subDays($i);
            $ligne = $visitesParJour->get($jour->toDateString());

            return [
                'label' => $jour->format('d/m'),
                'visites' => (int) ($ligne->visites ?? 0),
                'visiteurs' => (int) ($ligne->visiteurs ?? 0),
            ];
        });

        $premiere = Visite::min('created_at');
        $derniere = Visite::max('created_at');

        $visites = [
            'total' => Visite::count(),
            'uniques_total' => Visite::distinct()->count('visiteur'),
            'aujourdhui' => Visite::where('created_at', '>=', $debutJour)->count(),
            'uniques_aujourdhui' => Visite::where('created_at', '>=', $debutJour)->distinct()->count('visiteur'),
            'uniques_7j' => Visite::where('created_at', '>=', $il7Jours)->distinct()->count('visiteur'),
            'premiere' => $premiere ? Carbon::parse($premiere) : null,
            'derniere' => $derniere ? Carbon::parse($derniere) : null,
            'pages' => Visite::selectRaw('chemin, COUNT(*) as nombre')->groupBy('chemin')->orderByDesc('nombre')->limit(5)->get(),
            'sources' => Visite::selectRaw('source, COUNT(*) as nombre')->groupBy('source')->orderByDesc('nombre')->get(),
            'graph' => $graphVisites,
        ];

        // ---- Connexions des marchands ----
        $connexionsParJour = Connexion::where('created_at', '>=', $il14Jours)
            ->selectRaw('DATE(created_at) as jour, COUNT(*) as total')
            ->groupBy('jour')
            ->pluck('total', 'jour');

        $graphConnexions = collect(range(13, 0))->map(function ($i) use ($connexionsParJour) {
            $jour = now()->subDays($i);

            return [
                'label' => $jour->format('d/m'),
                'total' => (int) ($connexionsParJour[$jour->toDateString()] ?? 0),
            ];
        });

        $connexions = [
            'total' => Connexion::count(),
            'aujourdhui' => Connexion::where('created_at', '>=', $debutJour)->count(),
            'sept_jours' => Connexion::where('created_at', '>=', $il7Jours)->count(),
            'marchands_7j' => Connexion::where('created_at', '>=', $il7Jours)->distinct()->count('user_id'),
            'graph' => $graphConnexions,
        ];

        return compact('visites', 'connexions');
    }
}