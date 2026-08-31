<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProduitController extends Controller
{
    public function index(): View
    {
        $produits = Auth::user()->produits()->latest()->get();

        return view('produits.index', compact('produits'));
    }

    public function create(): View
    {
        return view('produits.creer');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('produits', 'public');
        }

        Auth::user()->produits()->create($validated);

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

        return redirect()->route('produits')->with('status', 'Produit supprimé.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'prix' => ['required', 'integer', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'],
            'livraison' => ['required', 'in:aucune,payante'],
            'prix_livraison' => ['nullable', 'required_if:livraison,payante', 'integer', 'min:0'],
        ], [
            'nom.required' => 'Le nom du produit est obligatoire.',
            'prix.required' => 'Le prix est obligatoire.',
            'prix.integer' => 'Le prix doit être un nombre entier (en Ariary).',
            'image.image' => 'Le fichier doit être une image.',
            'image.max' => "L'image ne doit pas dépasser 4 Mo.",
            'prix_livraison.required_if' => 'Indiquez le prix de la livraison, ou choisissez "Sans livraison".',
        ]);

        // Si "sans livraison" est choisi, on ignore un éventuel prix saisi avant.
        if ($validated['livraison'] === 'aucune') {
            $validated['prix_livraison'] = null;
        }

        return $validated;
    }

    private function autoriser(Produit $produit): void
    {
        abort_if($produit->user_id !== Auth::id(), 403);
    }
}