<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProduitController extends Controller
{
    private const PAR_PAGE = 20;

    /**
     * Termes évoquant des stupéfiants — signalés pour vérification manuelle.
     */
    private const MOTS_DROGUE = [
        'cannabis', 'marijuana', 'weed', 'beuh', 'chanvre indien',
        'cocaïne', 'cocaine', 'coke', 'crack', 'héroïne', 'heroine',
        'ecstasy', 'mdma', 'méthamphétamine', 'methamphetamine', 'meth',
        'opium', 'stupéfiant', 'stupefiant', 'khat',
    ];

    /**
     * Termes évoquant de la nudité / du contenu sexuel explicite —
     * signalés pour vérification manuelle.
     */
    private const MOTS_EXPLICITE = [
        'nudité', 'nudite', 'photo nue', 'photos nues', 'pornographique',
        'porno', 'hardcore', 'contenu adulte explicite', 'sexe explicite',
    ];

    /**
     * Vocabulaire des sextoys — toujours autorisé, jamais signalé même s'il
     * croise un mot de la liste ci-dessus.
     */
    private const TERMES_AUTORISES = [
        'sextoy', 'sex-toy', 'sex toy', 'jouet intime', 'jouet sexuel',
        'gode', 'godemichet', 'vibromasseur', 'masturbateur', 'plug anal',
        'lubrifiant', 'préservatif', 'preservatif', 'lingerie',
    ];

    public function index(Request $request): View
    {
        $filtre = $request->query('filtre', 'tous');
        if (! in_array($filtre, ['tous', 'rupture', 'sans_image', 'signale'], true)) {
            $filtre = 'tous';
        }

        $recherche = trim((string) $request->query('q'));

        $requete = Produit::with('user:id,nom_boutique,email')->latest();

        if ($filtre === 'rupture') {
            $requete->whereNotNull('stock')->where('stock', '<=', 0);
        } elseif ($filtre === 'sans_image') {
            $requete->whereNull('image');
        } elseif ($filtre === 'signale') {
            $this->appliquerFiltreSignale($requete);
        }

        if ($recherche !== '') {
            $requete->where(function ($q) use ($recherche) {
                $q->where('nom', 'like', "%{$recherche}%")
                  ->orWhereHas('user', function ($q2) use ($recherche) {
                      $q2->where('nom_boutique', 'like', "%{$recherche}%")
                         ->orWhere('email', 'like', "%{$recherche}%");
                  });
            });
        }

        $produits = $requete->paginate(self::PAR_PAGE)->withQueryString();

        $compteurs = [
            'tous' => Produit::count(),
            'rupture' => (clone Produit::query())->whereNotNull('stock')->where('stock', '<=', 0)->count(),
            'sans_image' => (clone Produit::query())->whereNull('image')->count(),
            'signale' => $this->appliquerFiltreSignale(Produit::query())->count(),
        ];

        // ---- Produits ajoutés par semaine (12 dernières semaines) ----
        $graphAjouts = collect(range(11, 0))->map(function ($i) {
            $debut = now()->subWeeks($i)->startOfWeek();
            $fin = now()->subWeeks($i)->endOfWeek();

            return [
                'label' => $debut->format('d/m'),
                'total' => Produit::whereBetween('created_at', [$debut, $fin])->count(),
            ];
        });

        return view('admin.produits.index', compact('produits', 'filtre', 'recherche', 'compteurs', 'graphAjouts'));
    }

    /**
     * Ajoute à la requête : (nom ou description contient un mot de la liste
     * "drogue" ou "explicite") ET (ne contient aucun terme autorisé, comme
     * le vocabulaire des sextoys).
     */
    private function appliquerFiltreSignale(Builder $requete): Builder
    {
        $motsSuspects = array_merge(self::MOTS_DROGUE, self::MOTS_EXPLICITE);

        $requete->where(function ($q) use ($motsSuspects) {
            foreach ($motsSuspects as $mot) {
                $q->orWhere('nom', 'like', "%{$mot}%")
                  ->orWhere('description', 'like', "%{$mot}%");
            }
        });

        foreach (self::TERMES_AUTORISES as $terme) {
            $requete->where('nom', 'not like', "%{$terme}%")
                    ->where(function ($q) use ($terme) {
                        $q->whereNull('description')->orWhere('description', 'not like', "%{$terme}%");
                    });
        }

        return $requete;
    }
}