@extends('layouts.dashboard')

@section('title', 'Tableau de bord — Tafely')

@section('page-content')

    {{-- header + badge --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Vue d'ensemble</h1>
            <p class="font-body text-gray-500 mt-1">Bienvenue{{ $user->hasPseudo() ? ', '.$user->pseudo : '' }} sur votre espace vendeur{{ $user->nom_boutique ? ' '.$user->nom_boutique : '' }}.</p>
        </div>

        @if ($user->status === 'active')
            <div class="bg-primary-50 text-primary-700 border border-primary-100 px-4 py-2 rounded-full flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-[18px]">workspace_premium</span>
                <span class="font-body text-sm font-bold">Plan Actif payant</span>
            </div>
        @elseif ($user->status === 'test')
            <div class="bg-accent-50 text-accent-700 border border-accent-100 px-4 py-2 rounded-full flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-[18px]">info</span>
                <span class="font-body text-sm font-bold">Essai en cours</span>
            </div>
        @else
            <div class="bg-gray-100 text-gray-600 border border-gray-200 px-4 py-2 rounded-full flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-[18px]">info</span>
                <span class="font-body text-sm font-bold">Plan Gratuit</span>
            </div>
        @endif
    </div>

    {{-- grille stats + CTA --}}
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-8">
        <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div class="flex justify-between items-start mb-4">
                    <span class="font-body text-sm text-gray-500">Nombre de produits</span>
                    <div class="bg-primary-50 p-1.5 rounded-full">
                        <span class="material-symbols-outlined text-primary-700 text-[20px]">inventory_2</span>
                    </div>
                </div>
                <span class="font-display text-4xl font-bold text-primary-900">{{ $stats['produits'] }}</span>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div class="flex justify-between items-start mb-4">
                    <span class="font-body text-sm text-gray-500">En stock</span>
                    <div class="bg-accent-50 p-1.5 rounded-full">
                        <span class="material-symbols-outlined text-accent-600 text-[20px]">check_circle</span>
                    </div>
                </div>
                <span class="font-display text-4xl font-bold text-primary-900">{{ $stats['en_stock'] }}</span>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div class="flex justify-between items-start mb-4">
                    <span class="font-body text-sm text-gray-500">Commandes reçues</span>
                    <div class="bg-primary-50 p-1.5 rounded-full">
                        <span class="material-symbols-outlined text-primary-700 text-[20px]">shopping_cart</span>
                    </div>
                </div>
                <span class="font-display text-4xl font-bold text-primary-900">{{ $stats['commandes'] }}</span>
            </div>
        </div>

        <button type="button"
                x-data="{ copied: false, copier() {
                    navigator.clipboard.writeText('{{ $user->lienBoutique() }}').then(() => {
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    });
                } }"
                @click="copier()"
                class="md:col-span-4 bg-primary-900 text-white rounded-xl shadow-lg p-6 flex flex-col justify-center items-center text-center relative overflow-hidden group hover:shadow-xl transition-shadow">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-800 to-primary-950 opacity-90 z-0"></div>
            <div class="relative z-10 flex flex-col items-center gap-2">
                <span class="material-symbols-outlined text-4xl mb-1" x-show="!copied">share</span>
                <span class="material-symbols-outlined text-4xl mb-1" x-show="copied" x-cloak>check_circle</span>
                <h3 class="font-display text-lg font-bold" x-text="copied ? 'Lien copié !' : 'Partager ma boutique'"></h3>
                <p class="font-body text-sm text-primary-100/80" x-show="!copied">Attirez plus de clients en partageant votre lien.</p>
                <p class="font-body text-sm text-primary-100/80" x-show="copied" x-cloak>Collez-le où vous voulez pour le partager.</p>
            </div>
        </button>
    </div>

    {{-- activité récente --}}
    <div>
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-display text-lg font-bold text-primary-900">Activité récente</h2>
            <a href="{{ route('commandes') }}" class="font-body text-sm font-semibold text-accent-600 hover:text-accent-700 transition-colors">Voir toutes les commandes</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            @forelse ($recentOrders as $order)
                <div class="p-4 flex items-center justify-between border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-11 h-11 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-gray-400">person</span>
                        </div>
                        <div>
                            <p class="font-body font-bold text-primary-900 text-sm">Commande #{{ $order['id'] }}</p>
                            <p class="font-body text-xs text-gray-500">{{ $order['date'] }} • {{ $order['items'] }} article(s)</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-display font-bold text-primary-900">{{ $order['total'] }}</p>
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold mt-1
                            {{ $order['status'] === 'Terminée' ? 'bg-green-100 text-green-700' : 'bg-accent-50 text-accent-700' }}">
                            {{ $order['status'] }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center">
                    <span class="material-symbols-outlined text-4xl text-gray-300">shopping_cart</span>
                    <p class="font-body text-sm text-gray-500 mt-3">Aucune commande pour l'instant.</p>
                    <p class="font-body text-xs text-gray-400 mt-1">Partagez le lien de votre boutique pour recevoir vos premières commandes.</p>
                </div>
            @endforelse
        </div>
    </div>

@endsection