@extends('layouts.app')

@section('content')

@php($user = auth()->user())

{{-- ============ TOP NAV ============ --}}
<nav class="bg-white/90 backdrop-blur-md shadow-sm fixed top-0 left-0 w-full z-50 flex justify-between items-center px-4 md:px-10 h-16">
    <a href="{{ route('dashboard') }}" class="flex items-center">
        <img src="{{ asset('logo.png') }}" alt="Tafely" class="h-9">
    </a>
    <div class="flex items-center gap-2 md:gap-3">
        <a href="#"
           class="hidden md:inline-flex items-center gap-2 bg-white text-primary-700 font-semibold text-sm px-4 py-2 rounded-full border border-primary-200 hover:bg-primary-50 transition-colors">
            Voir ma boutique
        </a>
        <a href="{{ route('notifications') }}" class="text-gray-500 hover:text-primary-700 hover:bg-gray-50 transition-colors p-2.5 rounded-full flex items-center justify-center">
            <span class="material-symbols-outlined">notifications</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-gray-500 hover:text-accent-600 hover:bg-gray-50 transition-colors p-2.5 rounded-full flex items-center justify-center" aria-label="Déconnexion">
                <span class="material-symbols-outlined">logout</span>
            </button>
        </form>
    </div>
</nav>

{{-- ============ SIDEBAR (desktop) ============ --}}
<aside class="bg-white border-r border-gray-100 text-primary-900 hidden md:flex flex-col h-screen w-64 fixed left-0 top-0 pt-24 p-4 z-40">
    <div class="mb-6 px-2">
        <h2 class="font-display text-lg font-bold text-primary-900 truncate">{{ $user->nom_boutique ?: 'Ma boutique' }}</h2>
        <p class="font-body text-sm text-gray-500 mt-0.5">Plan : {{ $user->statusLabel() }}</p>
    </div>

    @if ($user->nombre_produits >= 10)
        <span title="Limite de 10 produits atteinte pour votre plan actuel"
              class="bg-gray-100 text-gray-400 w-full py-2.5 rounded-xl font-body font-bold text-sm mb-6 flex items-center justify-center gap-2 cursor-not-allowed select-none">
            <span class="material-symbols-outlined text-[20px]">block</span>
            Limite atteinte (10/10)
        </span>
    @else
        <a href="{{ route('produits.create') }}" class="bg-accent-500 hover:bg-accent-600 text-white w-full py-2.5 rounded-xl font-body font-bold text-sm mb-6 transition-colors flex items-center justify-center gap-2 shadow-sm">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Ajouter un produit
        </a>
    @endif

    <nav class="flex-1 flex flex-col gap-1 font-body text-sm">
        @foreach ([
            ['route' => 'dashboard', 'icon' => 'dashboard', 'label' => 'Dashboard'],
            ['route' => 'produits', 'icon' => 'inventory_2', 'label' => 'Produits'],
            ['route' => 'commandes', 'icon' => 'shopping_cart', 'label' => 'Commandes'],
            ['route' => 'boutique', 'icon' => 'storefront', 'label' => 'Ma boutique'],
            ['route' => 'notifications', 'icon' => 'notifications', 'label' => 'Notifications'],
            ['route' => 'abonnement', 'icon' => 'workspace_premium', 'label' => 'Abonnement'],
        ] as $link)
            <a href="{{ route($link['route']) }}"
               @class([
                   'rounded-xl font-semibold px-4 py-2.5 flex items-center gap-3 transition-colors',
                   'bg-primary-50 text-primary-700' => request()->routeIs($link['route']),
                   'text-gray-600 hover:bg-gray-50 font-normal' => ! request()->routeIs($link['route']),
               ])>
                <span class="material-symbols-outlined text-[20px]" @if(request()->routeIs($link['route'])) style="font-variation-settings: 'FILL' 1;" @endif>{{ $link['icon'] }}</span>
                {{ $link['label'] }}
            </a>
        @endforeach

        <a href="{{ route('parametres') }}"
           @class([
               'rounded-xl font-semibold px-4 py-2.5 flex items-center gap-3 transition-colors mt-auto mb-4',
               'bg-primary-50 text-primary-700' => request()->routeIs('parametres'),
               'text-gray-600 hover:bg-gray-50 font-normal' => ! request()->routeIs('parametres'),
           ])>
            <span class="material-symbols-outlined text-[20px]" @if(request()->routeIs('parametres')) style="font-variation-settings: 'FILL' 1;" @endif>settings</span>
            Paramètres
        </a>
    </nav>
</aside>

{{-- ============ MAIN CONTENT (fourni par chaque page) ============ --}}
<main class="pt-24 pb-28 md:pb-16 md:pl-72 md:pr-10 px-4 min-h-screen max-w-[1200px] mx-auto w-full">
    @yield('page-content')
</main>

{{-- ============ BOTTOM NAV (mobile) ============ --}}
<nav class="bg-white shadow-[0_-4px_20px_rgba(30,58,138,0.08)] fixed bottom-0 left-0 w-full z-50 flex md:hidden justify-around items-center px-4 pb-4 pt-2 rounded-t-3xl">
    <a href="{{ route('dashboard') }}"
       @class(['flex flex-col items-center justify-center rounded-full px-4 py-1.5', 'bg-primary-50 text-primary-700' => request()->routeIs('dashboard'), 'text-gray-500' => ! request()->routeIs('dashboard')])>
        <span class="material-symbols-outlined" @if(request()->routeIs('dashboard')) style="font-variation-settings: 'FILL' 1;" @endif>home</span>
        <span class="font-body text-xs mt-0.5">Accueil</span>
    </a>
    <a href="{{ route('produits') }}"
       @class(['flex flex-col items-center justify-center rounded-full px-4 py-1.5', 'bg-primary-50 text-primary-700' => request()->routeIs('produits'), 'text-gray-500' => ! request()->routeIs('produits')])>
        <span class="material-symbols-outlined" @if(request()->routeIs('produits')) style="font-variation-settings: 'FILL' 1;" @endif>grid_view</span>
        <span class="font-body text-xs mt-0.5">Produits</span>
    </a>
    <a href="{{ route('commandes') }}"
       @class(['flex flex-col items-center justify-center rounded-full px-4 py-1.5', 'bg-primary-50 text-primary-700' => request()->routeIs('commandes'), 'text-gray-500' => ! request()->routeIs('commandes')])>
        <span class="material-symbols-outlined" @if(request()->routeIs('commandes')) style="font-variation-settings: 'FILL' 1;" @endif>receipt_long</span>
        <span class="font-body text-xs mt-0.5">Ventes</span>
    </a>
    <a href="{{ route('parametres') }}"
       @class(['flex flex-col items-center justify-center rounded-full px-4 py-1.5', 'bg-primary-50 text-primary-700' => request()->routeIs('parametres'), 'text-gray-500' => ! request()->routeIs('parametres')])>
        <span class="material-symbols-outlined" @if(request()->routeIs('parametres')) style="font-variation-settings: 'FILL' 1;" @endif>menu</span>
        <span class="font-body text-xs mt-0.5">Menu</span>
    </a>
</nav>

{{-- ============ FOOTER (desktop) ============ --}}
<footer class="hidden md:flex bg-white border-t border-gray-100 w-full py-5 px-10 md:pl-72 justify-between items-center relative z-40">
    <span class="font-body text-sm text-gray-400">© {{ date('Y') }} Tafely. Propulsons le commerce en ligne.</span>
    <div class="flex gap-6 font-body text-sm text-gray-500">
        <a href="#" class="hover:text-accent-600 transition-colors">Aide</a>
        <a href="#" class="hover:text-accent-600 transition-colors">Confidentialité</a>
        <a href="#" class="hover:text-accent-600 transition-colors">Contact</a>
    </div>
</footer>

@endsection