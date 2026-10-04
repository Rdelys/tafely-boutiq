<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoutiqueResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BoutiqueController extends Controller
{
    private const PAR_PAGE_DEFAUT = 15;
    private const PAR_PAGE_MAX = 50;

    /**
     * Liste paginée de toutes les boutiques publiques.
     *
     * Paramètres facultatifs :
     *  - q            : recherche (nom, adresse, description, noms de produits)
     *  - categorie    : clé de catégorie (ex : mode, beaute, alimentation...)
     *  - avec_produits: 1 pour ne garder que les boutiques ayant au moins un produit visible
     *  - tri          : recent (défaut) ou nom
     *  - per_page     : 1 à 50 (défaut 15)
     *  - page         : numéro de page
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $donnees = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'categorie' => ['nullable', 'in:'.implode(',', array_keys(User::CATEGORIES))],
            'avec_produits' => ['nullable', 'boolean'],
            'tri' => ['nullable', 'in:recent,nom'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.self::PAR_PAGE_MAX],
        ]);

        $requete = $this->boutiquesPubliques()
            ->withCount(['produits as produits_count' => fn (Builder $q) => $q->visibles()]);

        if (! empty($donnees['q'])) {
            $terme = $donnees['q'];

            $requete->where(function (Builder $q) use ($terme) {
                $q->where('nom_boutique', 'like', "%{$terme}%")
                  ->orWhere('adresse', 'like', "%{$terme}%")
                  ->orWhere('boutique_description', 'like', "%{$terme}%")
                  ->orWhereHas('produits', fn (Builder $p) => $p->visibles()->where('nom', 'like', "%{$terme}%"));
            });
        }

        if (! empty($donnees['categorie'])) {
            $requete->where('categorie_boutique', $donnees['categorie']);
        }

        if ($request->boolean('avec_produits')) {
            $requete->whereHas('produits', fn (Builder $q) => $q->visibles());
        }

        if (($donnees['tri'] ?? 'recent') === 'nom') {
            $requete->orderBy('nom_boutique')->orderBy('id');
        } else {
            $requete->latest('id');
        }

        $boutiques = $requete
            ->paginate($donnees['per_page'] ?? self::PAR_PAGE_DEFAUT)
            ->withQueryString();

        return BoutiqueResource::collection($boutiques);
    }

    /**
     * Une boutique complète avec tous ses produits visibles.
     * L'identifiant est celui du lien partagé : pseudo, slug ou numéro.
     */
    public function show(string $identifiant): JsonResponse|BoutiqueResource
    {
        $boutique = $this->trouver($identifiant);

        if (! $boutique) {
            return response()->json(['message' => 'Boutique introuvable.'], 404);
        }

        if ($boutique->estSuspendu()) {
            return response()->json(['message' => 'Cette boutique est temporairement indisponible.'], 403);
        }

        $boutique->loadCount(['produits as produits_count' => fn (Builder $q) => $q->visibles()]);
        $boutique->load(['produits' => fn ($q) => $q->visibles()->latest()->latest('id')]);

        return new BoutiqueResource($boutique);
    }

    /**
     * Transforme un lien partagé (ou un simple identifiant) en boutique complète.
     * Ex : ?lien=https://tafely-gr.com/b/naly-boutique
     */
    public function resoudre(Request $request): JsonResponse|BoutiqueResource
    {
        $donnees = $request->validate([
            'lien' => ['required', 'string', 'max:500'],
        ], [
            'lien.required' => 'Indiquez le lien de la boutique.',
        ]);

        $lien = trim($donnees['lien']);
        $chemin = parse_url($lien, PHP_URL_PATH) ?: $lien;

        // Lien du type .../b/{identifiant}, sinon on considère que c'est l'identifiant seul.
        if (preg_match('#/b/([^/?\#]+)#', $chemin, $correspondance)) {
            $identifiant = $correspondance[1];
        } else {
            $identifiant = trim($chemin, '/');
        }

        $identifiant = urldecode($identifiant);

        if ($identifiant === '') {
            return response()->json(['message' => 'Lien de boutique invalide.'], 422);
        }

        return $this->show($identifiant);
    }

    /**
     * Catégories avec le nombre de boutiques publiques de chacune.
     */
    public function categories(): JsonResponse
    {
        $totaux = $this->boutiquesPubliques()
            ->whereNotNull('categorie_boutique')
            ->selectRaw('categorie_boutique, COUNT(*) as total')
            ->groupBy('categorie_boutique')
            ->pluck('total', 'categorie_boutique');

        $categories = collect(User::CATEGORIES)->map(fn (array $categorie, string $cle) => [
            'cle' => $cle,
            'nom' => $categorie['nom'],
            'icone' => $categorie['icone'],
            'boutiques' => (int) ($totaux[$cle] ?? 0),
        ])->values();

        return response()->json(['data' => $categories]);
    }

    /**
     * Boutiques visibles du public : ni suspendues, ni sans nom.
     */
    private function boutiquesPubliques(): Builder
    {
        return User::query()
            ->where(fn (Builder $q) => $q->where('suspendu', false)->orWhereNull('suspendu'))
            ->whereNotNull('nom_boutique')
            ->where('nom_boutique', '!=', '');
    }

    /**
     * Même logique que la vitrine web : pseudo, puis slug, puis numéro.
     */
    private function trouver(string $identifiant): ?User
    {
        $boutique = User::where('pseudo', $identifiant)->first()
            ?? User::where('slug', $identifiant)->first();

        if (! $boutique && ctype_digit($identifiant)) {
            $boutique = User::find((int) $identifiant);
        }

        return $boutique;
    }
}