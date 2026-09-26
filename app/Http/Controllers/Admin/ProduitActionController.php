<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\MarchandNotification;
use App\Models\Produit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProduitActionController extends Controller
{
    public function bloquer(Request $request, Produit $produit): RedirectResponse
    {
        $validated = $request->validate([
            'raison' => ['required', 'string', 'max:255'],
        ], [
            'raison.required' => 'Merci d\'indiquer la raison du blocage.',
        ]);

        $produit->update([
            'bloque' => true,
            'bloque_raison' => $validated['raison'],
            'bloque_le' => now(),
        ]);

        $this->journaliser('produit_bloque', $produit, ['raison' => $validated['raison']]);

        if ($produit->user_id) {
            MarchandNotification::create([
                'user_id' => $produit->user_id,
                'type' => 'produit_bloque',
                'titre' => 'Produit bloqué : '.$produit->nom,
                'message' => 'Votre produit « '.$produit->nom.' » a été bloqué par l\'équipe Tafely et n\'est plus visible sur votre boutique. Raison : '.$validated['raison'],
            ]);
        }

        return back()->with('status', 'Produit bloqué.');
    }

    public function reactiver(Produit $produit): RedirectResponse
    {
        $produit->update([
            'bloque' => false,
            'bloque_raison' => null,
            'bloque_le' => null,
        ]);

        $this->journaliser('produit_reactive', $produit);

        if ($produit->user_id) {
            MarchandNotification::create([
                'user_id' => $produit->user_id,
                'type' => 'produit_reactive',
                'titre' => 'Produit réactivé : '.$produit->nom,
                'message' => 'Votre produit « '.$produit->nom.' » est de nouveau visible sur votre boutique.',
            ]);
        }

        return back()->with('status', 'Produit réactivé.');
    }

    public function detruire(Request $request, Produit $produit): RedirectResponse
    {
        $validated = $request->validate([
            'raison' => ['required', 'string', 'max:255'],
        ], [
            'raison.required' => 'Merci d\'indiquer la raison de la suppression.',
        ]);

        $nom = $produit->nom;
        $userId = $produit->user_id;

        $this->journaliser('produit_supprime', $produit, ['raison' => $validated['raison'], 'nom' => $nom]);

        if ($userId) {
            MarchandNotification::create([
                'user_id' => $userId,
                'type' => 'produit_supprime',
                'titre' => 'Produit supprimé : '.$nom,
                'message' => 'Votre produit « '.$nom.' » a été supprimé par l\'équipe Tafely. Raison : '.$validated['raison'],
            ]);
        }

        if ($produit->image) {
            Storage::disk('public')->delete($produit->image);
        }

        $produit->delete();

        return redirect()->route('admin.produits.index')->with('status', 'Produit supprimé.');
    }

    private function journaliser(string $action, Produit $produit, array $details = []): void
    {
        AdminAuditLog::create([
            'admin_id' => Auth::guard('admin')->id(),
            'user_id' => $produit->user_id,
            'produit_id' => $produit->id,
            'action' => $action,
            'details' => $details,
        ]);
    }
}