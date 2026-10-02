<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ParametresController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user();

        return view('parametres', [
            'user' => $user,
            'googleMapsKey' => config('services.google_maps.key'),
            'categories' => User::CATEGORIES,
            'manquants' => $user->champsManquants(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $etaitIncomplet = ! $user->profilComplet();

        $validated = $request->validate([
            'nom_boutique' => ['required', 'string', 'max:255'],
            'categorie_boutique' => ['nullable', 'in:'.implode(',', array_keys(User::CATEGORIES))],
            'categorie_autre' => ['nullable', 'required_if:categorie_boutique,autres', 'string', 'max:100'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'nif' => ['nullable', 'string', 'max:50'],
            'stat' => ['nullable', 'string', 'max:50'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'email_notification' => ['required', 'email', 'max:255'],
            'email_notification_secondaire' => ['nullable', 'email', 'max:255'],
            // Localisation : les deux coordonnées vont ensemble (ou aucune).
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'localisation_libelle' => ['nullable', 'string', 'max:255'],
        ], [
            'nom_boutique.required' => 'Le nom de la boutique est obligatoire.',
            'categorie_boutique.in' => 'Catégorie invalide.',
            'categorie_autre.required_if' => 'Précisez la catégorie de votre boutique.',
            'logo.image' => 'Le logo doit être une image.',
            'logo.max' => 'Le logo ne doit pas dépasser 2 Mo.',
            'email_notification.required' => "L'email principal est obligatoire.",
            'email_notification.email' => "L'email principal doit être une adresse valide.",
            'email_notification_secondaire.email' => "L'email secondaire doit être une adresse valide.",
            'latitude.numeric' => 'La position de la boutique est invalide.',
            'longitude.numeric' => 'La position de la boutique est invalide.',
            'latitude.between' => 'La position de la boutique est invalide.',
            'longitude.between' => 'La position de la boutique est invalide.',
        ]);

        if ($request->hasFile('logo')) {
            if ($user->logo) {
                Storage::disk('public')->delete($user->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        } else {
            unset($validated['logo']);
        }

        // La précision n'a de sens que pour la catégorie "Autres".
        if (($validated['categorie_boutique'] ?? null) !== 'autres') {
            $validated['categorie_autre'] = null;
        }

        // Position retirée : on efface aussi le libellé.
        if (is_null($validated['latitude'] ?? null) || is_null($validated['longitude'] ?? null)) {
            $validated['latitude'] = null;
            $validated['longitude'] = null;
            $validated['localisation_libelle'] = null;
        } else {
            $validated['latitude'] = round((float) $validated['latitude'], 7);
            $validated['longitude'] = round((float) $validated['longitude'], 7);
        }

        $user->update($validated);

        $message = ($etaitIncomplet && $user->fresh()->profilComplet())
            ? 'Paramètres enregistrés — votre profil est complet, votre boutique est prête à être trouvée !'
            : 'Paramètres enregistrés avec succès.';

        return back()->with('status', $message);
    }
}