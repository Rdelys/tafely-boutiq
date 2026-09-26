<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaiementController extends Controller
{
    private const PAR_PAGE = 20;

    /**
     * Un paiement "en_attente" depuis plus de ce délai est considéré bloqué
     * (callback Papi probablement jamais reçu) et doit être vérifié à la main.
     */
    private const MINUTES_BLOCAGE = 30;

    public function index(Request $request): View
    {
        $type = $request->query('type', 'tous');
        if (! in_array($type, ['tous', 'abonnement', 'pack_produits'], true)) {
            $type = 'tous';
        }

        $statut = $request->query('statut', 'tous');
        if (! in_array($statut, ['tous', 'paye', 'echoue', 'en_attente'], true)) {
            $statut = 'tous';
        }

        $recherche = trim((string) $request->query('q'));

        $requete = Paiement::with('user:id,nom_boutique,email')->latest();

        if ($type !== 'tous') {
            $requete->where('type', $type);
        }

        if ($statut !== 'tous') {
            $requete->where('statut', $statut);
        }

        if ($recherche !== '') {
            $requete->where(function ($q) use ($recherche) {
                $q->where('reference', 'like', "%{$recherche}%")
                  ->orWhere('papi_transaction_id', 'like', "%{$recherche}%")
                  ->orWhereHas('user', function ($q2) use ($recherche) {
                      $q2->where('nom_boutique', 'like', "%{$recherche}%")
                         ->orWhere('email', 'like', "%{$recherche}%");
                  });
            });
        }

        $paiements = $requete->paginate(self::PAR_PAGE)->withQueryString();

        $compteurs = [
            'tous' => Paiement::count(),
            'paye' => (clone Paiement::query())->where('statut', 'paye')->count(),
            'echoue' => (clone Paiement::query())->where('statut', 'echoue')->count(),
            'en_attente' => (clone Paiement::query())->where('statut', 'en_attente')->count(),
        ];

        // ---- Paiements bloqués : en_attente depuis trop longtemps ----
        $bloques = Paiement::with('user:id,nom_boutique,email')
            ->where('statut', 'en_attente')
            ->where('created_at', '<=', now()->subMinutes(self::MINUTES_BLOCAGE))
            ->latest()
            ->limit(20)
            ->get();

        // ---- Volume par méthode de paiement (paiements payés uniquement) ----
        $volumeMethodes = Paiement::where('statut', 'paye')
            ->selectRaw("COALESCE(NULLIF(papi_payment_method, ''), 'inconnu') as methode, COUNT(*) as nombre, SUM(montant) as montant")
            ->groupBy('methode')
            ->get();

        // ---- Revenu par mois, abonnements vs packs (12 derniers mois) ----
        $graphRevenu = collect(range(11, 0))->map(function ($i) {
            $mois = now()->subMonths($i);

            $abonnements = Paiement::where('statut', 'paye')->where('type', 'abonnement')
                ->whereYear('paye_le', $mois->year)->whereMonth('paye_le', $mois->month)
                ->sum('montant');

            $packs = Paiement::where('statut', 'paye')->where('type', 'pack_produits')
                ->whereYear('paye_le', $mois->year)->whereMonth('paye_le', $mois->month)
                ->sum('montant');

            return [
                'label' => ucfirst($mois->translatedFormat('M Y')),
                'abonnements' => (int) $abonnements,
                'packs' => (int) $packs,
            ];
        });

        return view('admin.paiements.index', compact(
            'paiements', 'type', 'statut', 'recherche', 'compteurs',
            'bloques', 'volumeMethodes', 'graphRevenu'
        ));
    }

    /**
     * Export CSV des transactions filtrées (mêmes filtres que la liste),
     * sans pagination — pour la comptabilité.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $type = $request->query('type', 'tous');
        $statut = $request->query('statut', 'tous');
        $recherche = trim((string) $request->query('q'));

        $requete = Paiement::with('user:id,nom_boutique,email')->latest();

        if (in_array($type, ['abonnement', 'pack_produits'], true)) {
            $requete->where('type', $type);
        }

        if (in_array($statut, ['paye', 'echoue', 'en_attente'], true)) {
            $requete->where('statut', $statut);
        }

        if ($recherche !== '') {
            $requete->where(function ($q) use ($recherche) {
                $q->where('reference', 'like', "%{$recherche}%")
                  ->orWhereHas('user', fn ($q2) => $q2->where('nom_boutique', 'like', "%{$recherche}%"));
            });
        }

        $nomFichier = 'paiements-tafely-'.now()->format('Y-m-d').'.csv';

        return ResponseFacade::streamDownload(function () use ($requete) {
            $flux = fopen('php://output', 'w');

            // BOM UTF-8 pour qu'Excel affiche correctement les accents.
            fwrite($flux, "\xEF\xBB\xBF");

            fputcsv($flux, [
                'Référence', 'Boutique', 'Email', 'Type', 'Montant (Ar)',
                'Statut', 'Méthode', 'Transaction Papi', 'Créé le', 'Payé le',
            ], ';');

            $requete->chunk(200, function ($lot) use ($flux) {
                foreach ($lot as $paiement) {
                    fputcsv($flux, [
                        $paiement->reference,
                        $paiement->user?->nom_boutique ?? '—',
                        $paiement->user?->email ?? '—',
                        $paiement->type === 'abonnement' ? 'Abonnement' : 'Pack produits',
                        $paiement->montant,
                        Str::ucfirst(str_replace('_', ' ', $paiement->statut)),
                        $paiement->papi_payment_method ?: '—',
                        $paiement->papi_transaction_id ?: '—',
                        $paiement->created_at->format('d/m/Y H:i'),
                        $paiement->paye_le?->format('d/m/Y H:i') ?? '—',
                    ], ';');
                }
            });

            fclose($flux);
        }, $nomFichier, ['Content-Type' => 'text/csv']);
    }
}