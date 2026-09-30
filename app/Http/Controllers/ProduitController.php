<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProduitController extends Controller
{
    //private const LIMITE_PRODUITS = 10;

    public function index(): View
    {
        $produits = Auth::user()->produits()->latest()->get();

        return view('produits.index', compact('produits'));
    }

    public function create(): View|RedirectResponse
{
    $limite = Auth::user()->limiteProduits();

    if (Auth::user()->nombre_produits >= $limite) {
        return redirect()->route('produits')
            ->with('erreur', 'Limite de '.$limite.' produits atteinte pour votre plan actuel.');
    }

    return view('produits.creer');
}


    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $limite = $user->limiteProduits();

        if ($user->nombre_produits >= $limite) {
            return redirect()->route('produits')
                ->with('erreur', 'Limite de '.$limite.' produits atteinte pour votre plan actuel.');
        }

        $validated = $this->validated($request);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('produits', 'public');
        }

        $user->produits()->create($validated);
        $user->increment('nombre_produits');

        return redirect()->route('produits')->with('status', 'Produit ajouté avec succès.');
    }

    public function edit(Produit $produit): View
    {
        $this->autoriser($produit);

        return view('produits.modifier', compact('produit'));
    }

    public function update(Request $request, Produit $produit): RedirectResponse
    {
        $this->autoriser($produit);

        $validated = $this->validated($request);

        if ($request->hasFile('image')) {
            if ($produit->image) {
                Storage::disk('public')->delete($produit->image);
            }
            $validated['image'] = $request->file('image')->store('produits', 'public');
        }

        $produit->update($validated);

        return redirect()->route('produits')->with('status', 'Produit modifié avec succès.');
    }

    public function destroy(Produit $produit): RedirectResponse
    {
        $this->autoriser($produit);

        if ($produit->image) {
            Storage::disk('public')->delete($produit->image);
        }

        $produit->delete();

        $user = Auth::user();
        if ($user->nombre_produits > 0) {
            $user->decrement('nombre_produits');
        }

        return redirect()->route('produits')->with('status', 'Produit supprimé.');
    }

    private function validated(Request $request): array
    {
        $typeRemise = $request->input('remise_type');

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'prix' => ['required', 'integer', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'],
            'livraison' => ['required', 'in:aucune,payante'],
            'prix_livraison' => ['nullable', 'required_if:livraison,payante', 'integer', 'min:0'],
            'remise_type' => ['nullable', 'in:aucune,pourcentage,montant'],
            'remise_valeur' => [
                'nullable',
                'integer',
                'min:1',
                Rule::requiredIf(fn () => in_array($typeRemise, ['pourcentage', 'montant'], true)),
                Rule::when($typeRemise === 'pourcentage', ['max:99']),
                Rule::when($typeRemise === 'montant', ['lt:prix']),
            ],
        ], [
            'nom.required' => 'Le nom du produit est obligatoire.',
            'prix.required' => 'Le prix est obligatoire.',
            'prix.integer' => 'Le prix doit être un nombre entier (en Ariary).',
            'image.image' => 'Le fichier doit être une image.',
            'image.max' => "L'image ne doit pas dépasser 4 Mo.",
            'prix_livraison.required_if' => 'Indiquez le prix de la livraison, ou choisissez "Sans livraison".',
            'remise_valeur.required' => 'Indiquez la valeur de la remise, ou choisissez "Aucune remise".',
            'remise_valeur.integer' => 'La remise doit être un nombre entier.',
            'remise_valeur.min' => 'La remise doit être supérieure à 0.',
            'remise_valeur.max' => 'Le pourcentage de remise doit être compris entre 1 et 99.',
            'remise_valeur.lt' => 'La remise doit être inférieure au prix du produit.',
        ]);

        // Si "sans livraison" est choisi, on ignore un éventuel prix saisi avant.
        if ($validated['livraison'] === 'aucune') {
            $validated['prix_livraison'] = null;
        }

        // Si "aucune remise" est choisi (ou rien n'est envoyé), on efface la remise.
        if (($validated['remise_type'] ?? 'aucune') === 'aucune') {
            $validated['remise_type'] = null;
            $validated['remise_valeur'] = null;
        }

        return $validated;
    }

    private function autoriser(Produit $produit): void
    {
        abort_if($produit->user_id !== Auth::id(), 403);
    }
}