<?php

namespace App\Http\Controllers;

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

        return view('parametres', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nom_boutique' => ['required', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'nif' => ['nullable', 'string', 'max:50'],
            'stat' => ['nullable', 'string', 'max:50'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'email_notification' => ['required', 'email', 'max:255'],
            'email_notification_secondaire' => ['nullable', 'email', 'max:255'],
        ], [
            'nom_boutique.required' => 'Le nom de la boutique est obligatoire.',
            'logo.image' => 'Le logo doit être une image.',
            'logo.max' => 'Le logo ne doit pas dépasser 2 Mo.',
            'email_notification.required' => "L'email principal est obligatoire.",
            'email_notification.email' => "L'email principal doit être une adresse valide.",
            'email_notification_secondaire.email' => "L'email secondaire doit être une adresse valide.",
        ]);

        if ($request->hasFile('logo')) {
            if ($user->logo) {
                Storage::disk('public')->delete($user->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        } else {
            unset($validated['logo']);
        }

        $user->update($validated);

        return back()->with('status', 'Paramètres enregistrés avec succès.');
    }
}