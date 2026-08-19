<?php

namespace App\Http\Controllers;

use App\Services\GeoPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request, GeoPricingService $pricingService)
    {
        // Si une session/remember valide existe déjà, on va direct
        // au dashboard, jamais sur la page d'accueil.
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $pricing = $pricingService->getPrice($request->ip());

        return view('home', compact('pricing'));
    }
}
