<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommandeController extends Controller
{
    private const PAR_PAGE = 20;

    /**
     * Une boutique sans commande depuis ce délai est considérée inactive
     * et proposée à la relance commerciale.
     */
    private const JOURS_INACTIVITE = 14;

    public function index(Request $request): View
    {
        $source = $request->query('source', 'toutes');
        if (! in_array($source, ['toutes', 'en_ligne', 'boutique'], true)) {
            $source = 'toutes';
        }

        $recherche = trim((string) $request->query('q'));

        $requete = Commande::with('user:id,nom_boutique,email')->latest()->latest('id');

        if ($source === 'en_ligne') {
            $requete->enLigne();
        } elseif ($source === 'boutique') {
            $requete->boutique();
        }

        if ($recherche !== '') {
            $requete->where(function ($q) use ($recherche) {
                $q->where('numero', 'like', "%{$recherche}%")
                  ->orWhere('nom_client', 'like', "%{$recherche}%")
                  ->orWhereHas('user', function ($q2) use ($recherche) {
                      $q2->where('nom_boutique', 'like', "%{$recherche}%")
                         ->orWhere('email', 'like', "%{$recherche}%");
                  });
            });
        }

        $commandes = $requete->paginate(self::PAR_PAGE)->withQueryString();

        $compteurs = [
            'toutes' => Commande::count(),
            'en_ligne' => (clone Commande::query())->enLigne()->count(),
            'boutique' => (clone Commande::query())->boutique()->count(),
        ];

        // ---- Évolution du nombre de commandes/jour, plateforme entière (30 derniers jours) ----
        $commandesBrut = Commande::where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->get(['created_at', 'source']);

        $graphJours = collect(range(29, 0))->map(function ($i) use ($commandesBrut) {
            $jour = now()->subDays($i);
            $duJour = $commandesBrut->filter(fn ($c) => $c->created_at->isSameDay($jour));

            return [
                'label' => $jour->format('d/m'),
                'en_ligne' => $duJour->where('source', Commande::SOURCE_EN_LIGNE)->count(),
                'boutique' => $duJour->where('source', Commande::SOURCE_BOUTIQUE)->count(),
            ];
        });

        // ---- Boutiques inactives : aucune commande/vente depuis N jours ----
        $seuil = now()->subDays(self::JOURS_INACTIVITE);

        $boutiquesInactives = User::query()
            ->whereDoesntHave('commandes', function ($q) use ($seuil) {
                $q->where('created_at', '>=', $seuil);
            })
            ->withCount('produits')
            ->having('produits_count', '>', 0) // inutile d'alerter sur une boutique vide
            ->orderBy('created_at')
            ->limit(30)
            ->get(['id', 'nom_boutique', 'email', 'created_at']);

        return view('admin.commandes.index', compact(
            'commandes', 'source', 'recherche', 'compteurs', 'graphJours', 'boutiquesInactives'
        ));
    }
}