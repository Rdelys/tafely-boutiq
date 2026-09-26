<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarchandActionController extends Controller
{
    public function suspendre(Request $request, User $utilisateur): RedirectResponse
    {
        $validated = $request->validate([
            'raison' => ['nullable', 'string', 'max:255'],
        ]);

        $utilisateur->update([
            'suspendu' => true,
            'suspendu_raison' => $validated['raison'] ?? null,
            'suspendu_le' => now(),
        ]);

        $this->journaliser('suspension', $utilisateur, ['raison' => $validated['raison'] ?? null]);

        return back()->with('status', 'Compte suspendu.');
    }

    public function reactiver(User $utilisateur): RedirectResponse
    {
        $utilisateur->update([
            'suspendu' => false,
            'suspendu_raison' => null,
            'suspendu_le' => null,
        ]);

        $this->journaliser('reactivation', $utilisateur);

        return back()->with('status', 'Compte réactivé.');
    }

    public function prolongerEssai(Request $request, User $utilisateur): RedirectResponse
    {
        $validated = $request->validate([
            'jours' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $depart = $utilisateur->finEssaiLe();
        $utilisateur->update(['essai_jusquau' => $depart->copy()->addDays($validated['jours'])]);

        $this->journaliser('prolongation_essai', $utilisateur, ['jours' => $validated['jours']]);

        return back()->with('status', 'Essai prolongé de '.$validated['jours'].' jour(s).');
    }

    public function prolongerAbonnement(Request $request, User $utilisateur): RedirectResponse
    {
        $validated = $request->validate([
            'jours' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $depart = $utilisateur->abonnementActif() ? $utilisateur->abonnement_expire_le : now();

        $utilisateur->update([
            'status' => 'active',
            'abonnement_expire_le' => $depart->copy()->addDays($validated['jours']),
        ]);

        $this->journaliser('prolongation_abonnement', $utilisateur, ['jours' => $validated['jours']]);

        return back()->with('status', 'Abonnement prolongé de '.$validated['jours'].' jour(s).');
    }

    public function definirLimiteProduits(Request $request, User $utilisateur): RedirectResponse
    {
        $validated = $request->validate([
            'limite' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ]);

        $utilisateur->update(['limite_produits_personnalisee' => $validated['limite'] ?? null]);

        $this->journaliser('limite_produits_personnalisee', $utilisateur, ['limite' => $validated['limite'] ?? null]);

        return back()->with('status', $validated['limite'] === null
            ? 'Limite de produits revenue au calcul par plan.'
            : 'Limite de produits fixée à '.$validated['limite'].'.');
    }

    private function journaliser(string $action, User $utilisateur, array $details = []): void
    {
        AdminAuditLog::create([
            'admin_id' => Auth::guard('admin')->id(),
            'user_id' => $utilisateur->id,
            'action' => $action,
            'details' => $details,
        ]);
    }
}