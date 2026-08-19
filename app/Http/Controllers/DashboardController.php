<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index', ['user' => Auth::user()]);
    }

    public function boutique()
    {
        return view('dashboard.boutique', ['user' => Auth::user()]);
    }

    public function commande()
    {
        return view('dashboard.commande', ['user' => Auth::user()]);
    }

    public function parametres()
    {
        return view('dashboard.parametres', ['user' => Auth::user()]);
    }
}
