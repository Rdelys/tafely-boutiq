<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarchandNotification;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(Request $request): View
    {
        $statut = $request->query('statut', 'ouvert');
        if (! in_array($statut, ['tous', 'ouvert', 'ferme'], true)) {
            $statut = 'ouvert';
        }

        $requete = SupportTicket::with('user:id,nom_boutique,email')->withCount('messages')->latest('updated_at');

        if ($statut !== 'tous') {
            $requete->where('statut', $statut);
        }

        $tickets = $requete->paginate(20)->withQueryString();

        $compteurs = [
            'tous' => SupportTicket::count(),
            'ouvert' => (clone SupportTicket::query())->where('statut', 'ouvert')->count(),
            'ferme' => (clone SupportTicket::query())->where('statut', 'ferme')->count(),
        ];

        return view('admin.support.index', compact('tickets', 'statut', 'compteurs'));
    }

    public function show(SupportTicket $ticket): View
    {
        $ticket->marquerLuAdmin();
        $ticket->load('messages', 'user');

        return view('admin.support.show', compact('ticket'));
    }

    public function repondre(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $ticket->messages()->create([
            'auteur_type' => 'admin',
            'admin_id' => Auth::guard('admin')->id(),
            'message' => $validated['message'],
        ]);

        $ticket->update([
            'statut' => 'ouvert',
            'nouveau_pour_marchand' => true,
            'nouveau_pour_admin' => false,
        ]);

        MarchandNotification::create([
            'user_id' => $ticket->user_id,
            'type' => 'support_reponse',
            'titre' => 'Réponse à votre demande : '.$ticket->sujet,
            'message' => 'L\'équipe Tafely vous a répondu. Consultez votre espace Support pour lire le message.',
        ]);

        return back()->with('status', 'Réponse envoyée.');
    }

    public function fermer(SupportTicket $ticket): RedirectResponse
    {
        $ticket->update(['statut' => 'ferme']);

        return back()->with('status', 'Ticket clôturé.');
    }

    public function rouvrir(SupportTicket $ticket): RedirectResponse
    {
        $ticket->update(['statut' => 'ouvert']);

        return back()->with('status', 'Ticket rouvert.');
    }
}