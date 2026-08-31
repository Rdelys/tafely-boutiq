@extends('layouts.dashboard')

@section('title', 'Produits — Tafely')

@section('page-content')

    {{-- header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Produits</h1>
            <p class="font-body text-gray-500 mt-1">{{ $produits->count() }} produit{{ $produits->count() > 1 ? 's' : '' }} dans votre boutique.</p>
        </div>
        <a href="{{ route('produits.create') }}"
           class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm px-5 py-2.5 rounded-full shadow-sm transition-colors">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Ajouter un produit
        </a>
    </div>

    {{-- succès --}}
    @if (session('status'))
        <div class="mb-6 flex items-center gap-3 bg-primary-50 border border-primary-100 text-primary-700 rounded-xl px-4 py-3">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span class="font-body text-sm font-semibold">{{ session('status') }}</span>
        </div>
    @endif

    @if ($produits->isEmpty())
        {{-- état vide --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 md:p-20 flex flex-col items-center text-center">
            <div class="h-16 w-16 rounded-full bg-primary-50 flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-primary-700 text-3xl">inventory_2</span>
            </div>
            <h2 class="font-display text-xl font-bold text-primary-900 mb-2">Aucun produit pour l'instant</h2>
            <p class="font-body text-sm text-gray-500 max-w-sm mb-6">Ajoutez votre premier produit pour commencer à vendre sur votre boutique.</p>
            <a href="{{ route('produits.create') }}"
               class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm px-6 py-3 rounded-xl shadow-sm transition-colors">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Ajouter un produit
            </a>
        </div>
    @else
        {{-- grille produits --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($produits as $produit)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden group">
                    <div class="h-40 bg-gray-100 flex items-center justify-center overflow-hidden">
                        @if ($produit->image)
                            <img src="{{ asset('storage/'.$produit->image) }}" alt="{{ $produit->nom }}" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <span class="material-symbols-outlined text-gray-300 text-5xl">inventory_2</span>
                        @endif
                    </div>

                    <div class="p-4">
                        <h3 class="font-display font-bold text-primary-900 truncate">{{ $produit->nom }}</h3>
                        <p class="font-display font-bold text-lg text-primary-800 mt-1">{{ $produit->prixFormate() }}</p>

                        <div class="flex items-center gap-2 mt-3">
                            @if ($produit->aLivraison())
                                <span class="inline-flex items-center gap-1 bg-primary-50 text-primary-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                    <span class="material-symbols-outlined text-[14px]">local_shipping</span>
                                    Livraison {{ number_format($produit->prix_livraison, 0, ',', ' ') }} Ar
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-500 text-xs font-semibold px-2.5 py-1 rounded-full">
                                    Sans livraison
                                </span>
                            @endif

                            @if (! is_null($produit->stock))
                                <span class="inline-flex items-center gap-1 {{ $produit->stock > 0 ? 'bg-green-50 text-green-700' : 'bg-accent-50 text-accent-700' }} text-xs font-semibold px-2.5 py-1 rounded-full">
                                    {{ $produit->stock > 0 ? $produit->stock.' en stock' : 'Rupture' }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-100">
                            <a href="{{ route('produits.edit', $produit) }}"
                               class="flex-1 text-center font-body text-sm font-semibold text-primary-700 hover:bg-primary-50 rounded-lg py-2 transition-colors">
                                Modifier
                            </a>
                            <form method="POST" action="{{ route('produits.destroy', $produit) }}"
                                  onsubmit="return confirm('Supprimer définitivement ce produit ?');" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full font-body text-sm font-semibold text-accent-600 hover:bg-accent-50 rounded-lg py-2 transition-colors">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@endsection