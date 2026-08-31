<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'email_notification' => ['required', 'email', 'max:255'],
            'email_notification_secondaire' => ['nullable', 'email', 'max:255'],
        ], [
            'nom_boutique.required' => 'Le nom de la boutique est obligatoire.',
            'email_notification.required' => "L'email principal est obligatoire.",
            'email_notification.email' => "L'email principal doit être une adresse valide.",
            'email_notification_secondaire.email' => "L'email secondaire doit être une adresse valide.",
        ]);

        $user->update($validated);

        return back()->with('status', 'Paramètres enregistrés avec succès.');
    }
}