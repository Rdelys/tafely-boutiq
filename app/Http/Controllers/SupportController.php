<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(): View
    {
        $tickets = Auth::user()->supportTickets()
            ->withCount('messages')
            ->latest('updated_at')
            ->get();

        return view('support.index', compact('tickets'));
    }

    public function create(): View
    {
        return view('support.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sujet' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:4000'],
        ], [
            'sujet.required' => 'Merci d\'indiquer un sujet.',
            'message.required' => 'Merci de décrire votre demande.',
        ]);

        $ticket = Auth::user()->supportTickets()->create([
            'sujet' => $validated['sujet'],
            'statut' => 'ouvert',
            'nouveau_pour_admin' => true,
            'nouveau_pour_marchand' => false,
        ]);

        $ticket->messages()->create([
            'auteur_type' => 'marchand',
            'message' => $validated['message'],
        ]);

        return redirect()->route('support.show', $ticket)->with('status', 'Votre message a été envoyé à l\'équipe Tafely.');
    }

    public function show(SupportTicket $ticket): View
    {
        abort_if($ticket->user_id !== Auth::id(), 403);

        $ticket->marquerLuMarchand();
        $ticket->load('messages');

        return view('support.show', compact('ticket'));
    }

    public function repondre(Request $request, SupportTicket $ticket): RedirectResponse
    {
        abort_if($ticket->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $ticket->messages()->create([
            'auteur_type' => 'marchand',
            'message' => $validated['message'],
        ]);

        $ticket->update([
            'statut' => 'ouvert',
            'nouveau_pour_admin' => true,
            'nouveau_pour_marchand' => false,
        ]);

        return back()->with('status', 'Message envoyé.');
    }
}