<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarchandController extends Controller
{
    private const PAR_PAGE = 15;

    public function index(Request $request): View
    {
        $recherche = trim((string) $request->query('q'));
        $statut = $request->query('statut', 'tous');
        if (! in_array($statut, ['tous', 'actif', 'essai', 'expire'], true)) {
            $statut = 'tous';
        }

        $requete = User::query()->latest();

        if ($recherche !== '') {
            $requete->where(function ($q) use ($recherche) {
                $q->where('nom_boutique', 'like', "%{$recherche}%")
                  ->orWhere('email', 'like', "%{$recherche}%")
                  ->orWhere('pseudo', 'like', "%{$recherche}%")
                  ->orWhere('nom', 'like', "%{$recherche}%")
                  ->orWhere('prenom', 'like', "%{$recherche}%");
            });
        }

        match ($statut) {
            'actif' => $requete->abonnementActif(),
            'essai' => $requete->enEssai(),
            'expire' => $requete->essaiExpire(),
            default => null,
        };

        $marchands = $requete->withCount('produits', 'commandes')->paginate(self::PAR_PAGE)->withQueryString();

        $compteurs = [
            'tous' => User::count(),
            'actif' => (clone User::query())->abonnementActif()->count(),
            'essai' => (clone User::query())->enEssai()->count(),
            'expire' => (clone User::query())->essaiExpire()->count(),
        ];

        return view('admin.marchands.index', compact('marchands', 'recherche', 'statut', 'compteurs'));
    }

    public function show(User $utilisateur): View
    {
        $utilisateur->loadCount('produits', 'commandes');

        $dernieresCommandes = $utilisateur->commandes()->latest()->latest('id')->limit(5)->get();
        $derniersPaiements = $utilisateur->paiements()->latest()->limit(5)->get();

        return view('admin.marchands.show', [
            'marchand' => $utilisateur,
            'dernieresCommandes' => $dernieresCommandes,
            'derniersPaiements' => $derniersPaiements,
        ]);
    }
}