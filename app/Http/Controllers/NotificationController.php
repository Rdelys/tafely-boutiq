<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    private const PAR_PAGE = 15;

    public function index(): View
    {
        $user = Auth::user();

        $notifications = $user->notificationsMarchand()
            ->latest()
            ->paginate(self::PAR_PAGE);

        // On marque comme lues celles affichées sur cette page, une fois
        // qu'on a déjà déterminé leur état "non lue" pour l'affichage.
        $user->notificationsMarchand()
            ->whereNull('lu_le')
            ->whereIn('id', $notifications->pluck('id'))
            ->update(['lu_le' => now()]);

        return view('notifications', compact('notifications'));
    }
}