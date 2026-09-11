@extends('layouts.app')

@section('title', ($marchand->nom_boutique ?: 'Boutique').' — Tafely')

@section('content')

    @php
        $themeCle = $marchand->boutique_theme;
        $rayonCard = $themeCle === 'minimal' ? 'rounded-lg' : ($themeCle === 'moderne' ? 'rounded-2xl' : 'rounded-xl');
        $rayonBtn = $themeCle === 'moderne' ? 'rounded-xl' : 'rounded-lg';
        $ombre = $themeCle === 'minimal' ? '' : ($themeCle === 'moderne' ? 'shadow-md hover:shadow-xl' : 'shadow-sm hover:shadow-lg');
        $bordure = $themeCle === 'minimal' ? '' : 'border border-gray-100';
        $lienBoutique = $marchand->lienBoutique();
        $initiale = mb_strtoupper(mb_substr($marchand->nom_boutique ?: 'B', 0, 1));
    @endphp

    <div x-data="{
            partager() {
                if (navigator.share) {
                    navigator.share({
                        title: '{{ addslashes($marchand->nom_boutique ?: 'Boutique') }}',
                        url: '{{ $lienBoutique }}',
                    }).catch(() => {});
                } else {
                    navigator.clipboard.writeText('{{ $lienBoutique }}').then(() => {
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    });
                }
            },
            copied: false,
        }">

        {{-- ============ MINI NAV STICKY ============ --}}
        <nav class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-gray-100 h-16 flex items-center">
            <div class="max-w-6xl mx-auto px-5 md:px-10 w-full flex items-center justify-between">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="h-8 w-8 rounded-full overflow-hidden flex items-center justify-center text-white font-display font-bold text-xs shrink-0"
                         style="background-color: {{ $couleurAccent }}">
                        @if ($marchand->logo)
                            <img src="{{ asset('storage/'.$marchand->logo) }}" alt="" class="h-full w-full object-cover">
                        @else
                            {{ $initiale }}
                        @endif
                    </div>
                    <span class="font-display font-bold text-sm text-gray-900 truncate">{{ $marchand->nom_boutique ?: 'Boutique' }}</span>
                </div>
                <button @click="partager()" type="button"
                        class="inline-flex items-center gap-1.5 text-xs font-body font-bold px-3.5 py-2 rounded-full border border-gray-200 hover:bg-gray-50 transition-colors shrink-0">
                    <span class="material-symbols-outlined text-[16px]" x-show="!copied">share</span>
                    <span class="material-symbols-outlined text-[16px] text-primary-700" x-show="copied" x-cloak>check</span>
                    <span x-text="copied ? 'Copié !' : 'Partager'"></span>
                </button>
            </div>
        </nav>

        {{-- ============ EN-TÊTE BOUTIQUE ============ --}}
        <header class="relative overflow-hidden py-14 md:py-20"
                style="background: radial-gradient(circle at 50% 0%, {{ $couleurAccent }}1a, transparent 60%);">
            <div class="max-w-3xl mx-auto px-5 md:px-10 flex flex-col items-center text-center gap-4">
                <div class="h-20 w-20 rounded-full overflow-hidden flex items-center justify-center text-white font-display font-bold text-2xl shadow-lg ring-4 ring-white"
                     style="background-color: {{ $couleurAccent }}">
                    @if ($marchand->logo)
                        <img src="{{ asset('storage/'.$marchand->logo) }}" alt="" class="h-full w-full object-cover">
                    @else
                        {{ $initiale }}
                    @endif
                </div>

                <div>
                    <h1 class="font-display text-3xl md:text-4xl font-bold text-gray-900 tracking-tight">{{ $marchand->nom_boutique ?: 'Boutique' }}</h1>
                    @if ($marchand->boutique_description)
                        <p class="font-body text-gray-500 mt-3 max-w-xl mx-auto">{{ $marchand->boutique_description }}</p>
                    @endif
                </div>

                <div class="flex flex-wrap items-center justify-center gap-2 mt-1">
                    @if ($marchand->adresse)
                        <span class="inline-flex items-center gap-1.5 bg-white border border-gray-200 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm">
                            <span class="material-symbols-outlined text-[15px]">location_on</span>
                            {{ $marchand->adresse }}
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-1.5 bg-white border border-gray-200 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm">
                        <span class="material-symbols-outlined text-[15px]">inventory_2</span>
                        {{ $produits->count() }} produit{{ $produits->count() > 1 ? 's' : '' }}
                    </span>
                </div>
            </div>
        </header>

        {{-- ============ PRODUITS ============ --}}
        <section class="max-w-6xl mx-auto px-5 md:px-10 py-10 md:py-14">
            @if ($produits->isEmpty())
                <div class="text-center py-20">
                    <span class="material-symbols-outlined text-gray-300 text-5xl">inventory_2</span>
                    <p class="font-body text-gray-500 mt-4">Cette boutique n'a pas encore de produits.</p>
                </div>
            @else
                <div x-data="{ selected: null }" @keydown.escape.window="selected = null">

                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6">
                        @foreach ($produits as $produit)
                            @php($rupture = ! is_null($produit->stock) && $produit->stock <= 0)
                            <div class="group bg-white {{ $rayonCard }} {{ $ombre }} {{ $bordure }} overflow-hidden transition-all duration-200 cursor-pointer hover:-translate-y-0.5"
                                 @click="selected = {{ \Illuminate\Support\Js::from([
                                     'nom' => $produit->nom,
                                     'description' => $produit->description,
                                     'prix' => $produit->prixFormate(),
                                     'image' => $produit->image ? asset('storage/'.$produit->image) : null,
                                     'livraison' => $produit->aLivraison(),
                                     'prixLivraison' => $produit->prix_livraison ? number_format($produit->prix_livraison, 0, ',', ' ').' Ar' : null,
                                     'rupture' => $rupture,
                                     'commanderUrl' => route('commandes.store', $produit),
                                 ]) }}">
                                <div class="relative h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
                                    @if ($produit->image)
                                        <img src="{{ asset('storage/'.$produit->image) }}" alt="{{ $produit->nom }}"
                                             class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105 {{ $rupture ? 'grayscale opacity-60' : '' }}">
                                    @else
                                        <span class="material-symbols-outlined text-gray-300 text-5xl">inventory_2</span>
                                    @endif

                                    @if ($rupture)
                                        <div class="absolute inset-0 bg-gray-900/40 flex items-center justify-center">
                                            <span class="bg-white/95 text-gray-800 text-xs font-bold px-3 py-1.5 rounded-full">Rupture de stock</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-4">
                                    <h3 class="font-body font-semibold text-sm text-gray-900 truncate">{{ $produit->nom }}</h3>
                                    <p class="font-display font-bold text-lg mt-1" style="color: {{ $couleurAccent }}">{{ $produit->prixFormate() }}</p>

                                    <div class="flex items-center gap-2 mt-2">
                                        @if ($produit->aLivraison())
                                            <span class="inline-flex items-center gap-1 bg-gray-50 text-gray-600 text-xs font-semibold px-2 py-0.5 rounded-full">
                                                <span class="material-symbols-outlined text-[13px]">local_shipping</span>
                                                {{ number_format($produit->prix_livraison, 0, ',', ' ') }} Ar
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 bg-gray-50 text-gray-500 text-xs font-semibold px-2 py-0.5 rounded-full">
                                                Sans livraison
                                            </span>
                                        @endif
                                    </div>

                                    <button type="button"
                                            class="w-full mt-3.5 text-white text-sm font-bold py-2.5 {{ $rayonBtn }} transition-opacity hover:opacity-90"
                                            style="background-color: {{ $couleurAccent }}">
                                        Voir le produit
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- ============ MODAL DÉTAIL + COMMANDE ============ --}}
                    <div x-show="selected" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6" style="display: none;">
                        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
                             x-show="selected"
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             @click="selected = null"></div>

                        <div class="relative w-full max-w-md max-h-[92vh] overflow-y-auto bg-white rounded-2xl shadow-2xl"
                             x-show="selected"
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                             @click.outside="selected = null">

                            <button @click="selected = null" aria-label="Fermer"
                                    class="absolute top-3 right-3 z-10 h-9 w-9 flex items-center justify-center rounded-full bg-white/90 text-gray-500 hover:bg-white shadow-sm transition-colors">
                                <span class="material-symbols-outlined text-[20px]">close</span>
                            </button>

                            <template x-if="selected">
                                <div>
                                    <div class="h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
                                        <template x-if="selected.image">
                                            <img :src="selected.image" class="h-full w-full object-cover" :class="selected.rupture && 'grayscale opacity-60'">
                                        </template>
                                        <template x-if="! selected.image">
                                            <span class="material-symbols-outlined text-gray-300 text-6xl">inventory_2</span>
                                        </template>
                                    </div>

                                    <div class="p-6">
                                        <h2 class="font-display text-xl font-bold text-gray-900" x-text="selected.nom"></h2>
                                        <p class="font-display text-2xl font-bold mt-1" :style="`color: {{ $couleurAccent }}`" x-text="selected.prix"></p>

                                        <p class="font-body text-sm text-gray-500 mt-3" x-show="selected.description" x-text="selected.description"></p>

                                        <div class="flex flex-wrap items-center gap-2 mt-4">
                                            <template x-if="selected.livraison">
                                                <span class="inline-flex items-center gap-1 bg-gray-50 text-gray-600 text-xs font-semibold px-2.5 py-1 rounded-full">
                                                    <span class="material-symbols-outlined text-[14px]">local_shipping</span>
                                                    <span x-text="'Livraison ' + selected.prixLivraison"></span>
                                                </span>
                                            </template>
                                            <template x-if="! selected.livraison">
                                                <span class="inline-flex items-center gap-1 bg-gray-50 text-gray-500 text-xs font-semibold px-2.5 py-1 rounded-full">
                                                    Sans livraison
                                                </span>
                                            </template>
                                        </div>

                                        {{-- ---- indisponible ---- --}}
                                        <template x-if="selected.rupture">
                                            <div class="w-full mt-6 text-center text-gray-400 font-body text-sm py-3 bg-gray-50 {{ $rayonBtn }}">
                                                Actuellement indisponible
                                            </div>
                                        </template>

                                        {{-- ---- formulaire de commande ---- --}}
                                        <template x-if="! selected.rupture">
                                            <div x-data="{
                                                    mode: 'recuperer',
                                                    quantite: 1,
                                                    nom_client: '',
                                                    telephone_client: '',
                                                    date_recuperation: '',
                                                    heure_recuperation: '',
                                                    adresse_livraison: '',
                                                    loading: false,
                                                    error: '',
                                                    envoyee: false,
                                                    envoyer() {
                                                        this.error = '';
                                                        if (! this.nom_client || ! this.telephone_client) {
                                                            this.error = 'Merci de renseigner votre nom et votre téléphone.';
                                                            return;
                                                        }
                                                        if (this.mode === 'recuperer' && (! this.date_recuperation || ! this.heure_recuperation)) {
                                                            this.error = 'Merci d\'indiquer une date et une heure de récupération.';
                                                            return;
                                                        }
                                                        if (this.mode === 'livrer' && ! this.adresse_livraison) {
                                                            this.error = 'Merci d\'indiquer une adresse de livraison.';
                                                            return;
                                                        }
                                                        this.loading = true;
                                                        fetch(selected.commanderUrl, {
                                                            method: 'POST',
                                                            headers: {
                                                                'Content-Type': 'application/json',
                                                                'Accept': 'application/json',
                                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                                            },
                                                            body: JSON.stringify({
                                                                nom_client: this.nom_client,
                                                                telephone_client: this.telephone_client,
                                                                quantite: this.quantite,
                                                                mode: this.mode,
                                                                date_recuperation: this.date_recuperation,
                                                                heure_recuperation: this.heure_recuperation,
                                                                adresse_livraison: this.adresse_livraison,
                                                            }),
                                                        }).then(async (res) => {
                                                            const data = await res.json();
                                                            this.loading = false;
                                                            if (! res.ok) {
                                                                this.error = data.message || 'Une erreur est survenue.';
                                                                return;
                                                            }
                                                            this.envoyee = true;
                                                        }).catch(() => {
                                                            this.loading = false;
                                                            this.error = 'Connexion impossible. Réessayez.';
                                                        });
                                                    }
                                                }" class="mt-6">

                                                {{-- confirmation --}}
                                                <div x-show="envoyee" x-cloak class="text-center py-4">
                                                    <span class="material-symbols-outlined text-5xl" :style="`color: {{ $couleurAccent }}`">check_circle</span>
                                                    <p class="font-display font-bold text-gray-900 mt-2">Commande envoyée !</p>
                                                    <p class="font-body text-sm text-gray-500 mt-1">Le vendeur vous contactera au numéro indiqué.</p>
                                                </div>

                                                {{-- formulaire --}}
                                                <div x-show="! envoyee">
                                                    <p class="font-body text-sm font-semibold text-primary-900 mb-2">Commander ce produit</p>

                                                    <p x-show="error" x-cloak x-text="error" class="mb-3 text-xs font-body font-semibold text-accent-700 bg-accent-50 border border-accent-100 rounded-lg px-3 py-2"></p>

                                                    <div class="grid grid-cols-2 gap-3 mb-3">
                                                        <div class="col-span-2">
                                                            <input type="text" x-model="nom_client" placeholder="Votre nom"
                                                                   class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600">
                                                        </div>
                                                        <div class="col-span-2">
                                                            <input type="tel" x-model="telephone_client" placeholder="Votre numéro de téléphone"
                                                                   class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600">
                                                        </div>
                                                        <div>
                                                            <label class="block font-body text-xs text-gray-500 mb-1">Quantité</label>
                                                            <input type="number" x-model.number="quantite" min="1" max="50"
                                                                   class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600">
                                                        </div>
                                                    </div>

                                                    {{-- mode --}}
                                                    <div class="grid grid-cols-2 gap-2 mb-3">
                                                        <label class="flex items-center justify-center gap-1.5 py-2.5 rounded-lg border-2 cursor-pointer text-xs font-body font-bold transition-colors"
                                                               :class="mode === 'recuperer' ? 'border-primary-600 bg-primary-50 text-primary-700' : 'border-gray-200 text-gray-500'">
                                                            <input type="radio" x-model="mode" value="recuperer" class="sr-only">
                                                            <span class="material-symbols-outlined text-[16px]">storefront</span>
                                                            À récupérer
                                                        </label>
                                                        <label class="flex items-center justify-center gap-1.5 py-2.5 rounded-lg border-2 cursor-pointer text-xs font-body font-bold transition-colors"
                                                               :class="mode === 'livrer' ? 'border-primary-600 bg-primary-50 text-primary-700' : 'border-gray-200 text-gray-500'">
                                                            <input type="radio" x-model="mode" value="livrer" class="sr-only">
                                                            <span class="material-symbols-outlined text-[16px]">local_shipping</span>
                                                            À livrer
                                                        </label>
                                                    </div>

                                                    {{-- champs récupération --}}
                                                    <div x-show="mode === 'recuperer'" x-cloak class="grid grid-cols-2 gap-3 mb-3">
                                                        <input type="date" x-model="date_recuperation"
                                                               class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600">
                                                        <input type="time" x-model="heure_recuperation"
                                                               class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600">
                                                    </div>

                                                    {{-- champ livraison --}}
                                                    <div x-show="mode === 'livrer'" x-cloak class="mb-3">
                                                        <textarea x-model="adresse_livraison" rows="2" placeholder="Adresse de livraison complète"
                                                                  class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 resize-none"></textarea>
                                                        <p class="font-body text-xs text-gray-400 mt-1" x-show="selected.livraison">
                                                            Frais de livraison : <span x-text="selected.prixLivraison"></span>
                                                        </p>
                                                    </div>

                                                    <button type="button" @click="envoyer()" :disabled="loading"
                                                            class="w-full flex items-center justify-center gap-2 text-white font-body font-bold text-sm py-3 {{ $rayonBtn }} transition-opacity hover:opacity-90 disabled:opacity-60"
                                                            :style="`background-color: {{ $couleurAccent }}`">
                                                        <span x-text="loading ? 'Envoi en cours...' : 'Envoyer ma commande'"></span>
                                                    </button>
                                                    <p class="font-body text-xs text-gray-400 text-center mt-2">Le vendeur recevra votre commande par email et vous contactera.</p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            @endif
        </section>

        {{-- ============ FOOTER ============ --}}
        <footer class="border-t border-gray-100 py-8 text-center">
            <p class="font-body text-xs text-gray-400">
                Boutique propulsée par
                <a href="{{ route('home') }}" class="font-semibold text-primary-700 hover:text-accent-600 transition-colors">Tafely</a>
            </p>
        </footer>
    </div>

@endsection