<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\GeoPricingService;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request, GeoPricingService $pricingService)
    {
        // Si une session/remember valide existe déjà, on va direct à l'espace
        // marchand : les paramètres si le profil est incomplet, sinon le dashboard.
        if (Auth::check()) {
            return redirect(Auth::user()->routeApresConnexion());
        }

        // Pays du visiteur (France ou Madagascar) et devise d'affichage.
        $pricing = $pricingService->contexte($request);

        // ---- Offre de lancement (réglée dans les Paramètres admin) ----
        $places = Settings::offreLancementPlaces();
        $mois = Settings::offreLancementMois();
        $restantes = max(0, $places - User::count());

        $offre = [
            'active' => Settings::offreLancementActive() && $places > 0 && $restantes > 0,
            'places' => $places,
            'restantes' => $restantes,
            'mois' => $mois,
            // 30 jours d'essai (≈ 1 mois) + les mois offerts.
            'duree_totale' => 1 + $mois,
            'premiers' => $places > 1 ? 'les '.$places.' premiers inscrits' : 'le tout premier inscrit',
            'verbe' => $places > 1 ? 'reçoivent' : 'reçoit',
        ];

        $tarif = [
            // Prix de base en Ariary (utilisé pour les données structurées Google : toujours en MGA).
            'prix' => Settings::prixAbonnement(),
            // Prix affiché au visiteur : "20 000 Ar" ou "4,00 €" selon le pays.
            'prix_affiche' => $pricingService->formater(Settings::prixAbonnement(), $pricing),
            'pays' => $pricing['pays'],
            'taux' => $pricing['taux'],
            'reduction_annuelle' => (int) Settings::get('reduction_annuel'),
        ];

        return view('home', compact('pricing', 'offre', 'tarif'));
    }
}