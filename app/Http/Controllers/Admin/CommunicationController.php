<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarchandNotification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunicationController extends Controller
{
    public function create(): View
    {
        $compteurs = [
            'tous' => User::count(),
            'actif' => (clone User::query())->abonnementActif()->count(),
            'essai' => (clone User::query())->enEssai()->count(),
            'expire' => (clone User::query())->essaiExpire()->count(),
        ];

        return view('admin.communication.create', compact('compteurs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cible' => ['required', 'in:tous,actif,essai,expire'],
            'titre' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'titre.required' => 'Merci d\'indiquer un titre.',
            'message.required' => 'Merci de rédiger le message.',
        ]);

        $requete = User::query();

        match ($validated['cible']) {
            'actif' => $requete->abonnementActif(),
            'essai' => $requete->enEssai(),
            'expire' => $requete->essaiExpire(),
            default => null,
        };

        $total = 0;

        $requete->select('id')->chunkById(200, function ($lot) use ($validated, &$total) {
            $maintenant = now();

            $lignes = $lot->map(fn ($u) => [
                'user_id' => $u->id,
                'type' => 'annonce',
                'titre' => $validated['titre'],
                'message' => $validated['message'],
                'created_at' => $maintenant,
                'updated_at' => $maintenant,
            ])->all();

            MarchandNotification::insert($lignes);
            $total += count($lignes);
        });

        return redirect()->route('admin.communication.create')
            ->with('status', 'Message envoyé à '.$total.' marchand(s).');
    }
}