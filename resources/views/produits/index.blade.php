@extends('layouts.dashboard')

@section('title', 'Produits — Tafely')

@section('page-content')

    {{-- header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Produits</h1>
            <p class="font-body text-gray-500 mt-1">{{ $produits->count() }} / 10 produits utilisés sur votre plan actuel.</p>
        </div>
        @if (auth()->user()->nombre_produits >= 10)
            <span title="Limite de 10 produits atteinte pour votre plan actuel"
                  class="inline-flex items-center gap-2 bg-gray-100 text-gray-400 font-body font-bold text-sm px-5 py-2.5 rounded-full cursor-not-allowed select-none">
                <span class="material-symbols-outlined text-[20px]">block</span>
                Limite atteinte (10/10)
            </span>
        @else
            <a href="{{ route('produits.create') }}"
               class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm px-5 py-2.5 rounded-full shadow-sm transition-colors">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Ajouter un produit
            </a>
        @endif
    </div>

    {{-- erreur (ex: limite atteinte) --}}
    @if (session('erreur'))
        <div class="mb-6 flex items-center gap-3 bg-accent-50 border border-accent-100 text-accent-700 rounded-xl px-4 py-3">
            <span class="material-symbols-outlined text-[20px]">error</span>
            <span class="font-body text-sm font-semibold">{{ session('erreur') }}</span>
        </div>
    @endif

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

        <div x-data="{ selected: null }" @keydown.escape.window="selected = null">

            {{-- ============ TABLEAU ============ --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Produit</th>
                                <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Prix</th>
                                <th class="hidden md:table-cell px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Livraison</th>
                                <th class="hidden sm:table-cell px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Stock</th>
                                <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($produits as $produit)
                                <tr class="hover:bg-gray-50 transition-colors cursor-pointer"
                                    @click="selected = {{ \Illuminate\Support\Js::from([
                                        'nom' => $produit->nom,
                                        'description' => $produit->description,
                                        'prix' => $produit->prixFormate(),
                                        'image' => $produit->image ? asset('storage/'.$produit->image) : null,
                                        'livraison' => $produit->aLivraison(),
                                        'prixLivraison' => $produit->prix_livraison ? number_format($produit->prix_livraison, 0, ',', ' ').' Ar' : null,
                                        'stock' => $produit->stock,
                                        'editUrl' => route('produits.edit', $produit),
                                    ]) }}">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="h-11 w-11 rounded-lg bg-gray-100 overflow-hidden shrink-0 flex items-center justify-center">
                                                @if ($produit->image)
                                                    <img src="{{ asset('storage/'.$produit->image) }}" alt="{{ $produit->nom }}" class="h-full w-full object-cover">
                                                @else
                                                    <span class="material-symbols-outlined text-gray-300 text-xl">inventory_2</span>
                                                @endif
                                            </div>
                                            <span class="font-body font-semibold text-sm text-primary-900 truncate max-w-[160px] sm:max-w-xs">{{ $produit->nom }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-display font-bold text-sm text-primary-800 whitespace-nowrap">
                                        {{ $produit->prixFormate() }}
                                    </td>
                                    <td class="hidden md:table-cell px-4 py-3">
                                        @if ($produit->aLivraison())
                                            <span class="inline-flex items-center gap-1 bg-primary-50 text-primary-700 text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                                                <span class="material-symbols-outlined text-[14px]">local_shipping</span>
                                                {{ number_format($produit->prix_livraison, 0, ',', ' ') }} Ar
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-500 text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                                                Sans livraison
                                            </span>
                                        @endif
                                    </td>
                                    <td class="hidden sm:table-cell px-4 py-3">
                                        @if (! is_null($produit->stock))
                                            <span class="inline-flex items-center gap-1 {{ $produit->stock > 0 ? 'bg-green-50 text-green-700' : 'bg-accent-50 text-accent-700' }} text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                                                {{ $produit->stock > 0 ? $produit->stock : 'Rupture' }}
                                            </span>
                                        @else
                                            <span class="text-gray-300 text-xs font-body">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1" @click.stop>
                                            <a href="{{ route('produits.edit', $produit) }}" title="Modifier"
                                               class="h-9 w-9 flex items-center justify-center rounded-lg text-gray-400 hover:text-primary-700 hover:bg-primary-50 transition-colors">
                                                <span class="material-symbols-outlined text-[20px]">edit</span>
                                            </a>
                                            <form method="POST" action="{{ route('produits.destroy', $produit) }}"
                                                  onsubmit="return confirm('Supprimer définitivement ce produit ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Supprimer"
                                                        class="h-9 w-9 flex items-center justify-center rounded-lg text-gray-400 hover:text-accent-600 hover:bg-accent-50 transition-colors">
                                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ============ MODAL DÉTAIL PRODUIT ============ --}}
            <div x-show="selected" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6" style="display: none;">
                <div class="absolute inset-0 bg-primary-950/60 backdrop-blur-sm"
                     x-show="selected"
                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     @click="selected = null"></div>

                <div class="relative w-full max-w-md max-h-[90vh] overflow-y-auto bg-white rounded-2xl shadow-2xl"
                     x-show="selected"
                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     @click.outside="selected = null">

                    <button @click="selected = null" aria-label="Fermer"
                            class="absolute top-3 right-3 z-10 h-9 w-9 flex items-center justify-center rounded-full bg-white/90 text-gray-500 hover:text-accent-600 hover:bg-white shadow-sm transition-colors">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>

                    <template x-if="selected">
                        <div>
                            <div class="h-56 bg-gray-100 flex items-center justify-center overflow-hidden">
                                <template x-if="selected.image">
                                    <img :src="selected.image" class="h-full w-full object-cover">
                                </template>
                                <template x-if="! selected.image">
                                    <span class="material-symbols-outlined text-gray-300 text-6xl">inventory_2</span>
                                </template>
                            </div>

                            <div class="p-6">
                                <h2 class="font-display text-xl font-bold text-primary-900" x-text="selected.nom"></h2>
                                <p class="font-display text-2xl font-bold text-primary-800 mt-1" x-text="selected.prix"></p>

                                <p class="font-body text-sm text-gray-500 mt-3" x-show="selected.description" x-text="selected.description"></p>

                                <div class="flex flex-wrap items-center gap-2 mt-4">
                                    <template x-if="selected.livraison">
                                        <span class="inline-flex items-center gap-1 bg-primary-50 text-primary-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                            <span class="material-symbols-outlined text-[14px]">local_shipping</span>
                                            <span x-text="'Livraison ' + selected.prixLivraison"></span>
                                        </span>
                                    </template>
                                    <template x-if="! selected.livraison">
                                        <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-500 text-xs font-semibold px-2.5 py-1 rounded-full">
                                            Sans livraison
                                        </span>
                                    </template>

                                    <template x-if="selected.stock !== null">
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full"
                                              :class="selected.stock > 0 ? 'bg-green-50 text-green-700' : 'bg-accent-50 text-accent-700'"
                                              x-text="selected.stock > 0 ? selected.stock + ' en stock' : 'Rupture de stock'"></span>
                                    </template>
                                </div>

                                <div class="flex items-center gap-3 mt-6">
                                    <a :href="selected.editUrl"
                                       class="flex-1 inline-flex items-center justify-center gap-2 bg-primary-700 hover:bg-primary-800 text-white font-body font-bold text-sm py-3 rounded-xl transition-colors">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                        Modifier
                                    </a>
                                    <button @click="selected = null" type="button"
                                            class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-600 font-body font-bold text-sm py-3 rounded-xl transition-colors">
                                        Fermer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    @endif

@endsection