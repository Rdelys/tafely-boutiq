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
            // ---- partage ----
            copied: false,
            partager() {
                if (navigator.share) {
                    navigator.share({ title: '{{ addslashes($marchand->nom_boutique ?: 'Boutique') }}', url: '{{ $lienBoutique }}' }).catch(() => {});
                } else {
                    navigator.clipboard.writeText('{{ $lienBoutique }}').then(() => {
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    });
                }
            },

            // ---- panier ----
            panier: [],
            cartOuvert: false,
            vueCart: 'panier', // panier | checkout | succes
            flashIds: [],
            mode: 'recuperer',
            nom_client: '',
            telephone_client: '',
            date_recuperation: '',
            heure_recuperation: '',
            adresse_livraison: '',
            loading: false,
            error: '',
            numeroCommande: '',
            recuUrl: '',

            get nombreArticles() { return this.panier.reduce((s, i) => s + i.quantite, 0); },
            get sousTotal() { return this.panier.reduce((s, i) => s + (i.prix * i.quantite), 0); },
            get livraisonMax() { return this.panier.filter(i => i.livraison).reduce((m, i) => Math.max(m, i.prixLivraison || 0), 0); },
            get total() { return this.sousTotal + (this.mode === 'livrer' ? this.livraisonMax : 0); },
            get formatteMonnaie() { return (n) => new Intl.NumberFormat('fr-FR').format(n) + ' Ar'; },

            ajouter(produit) {
                const existant = this.panier.find(i => i.id === produit.id);
                if (existant) {
                    existant.quantite++;
                } else {
                    this.panier.push({ ...produit, quantite: 1 });
                }
                this.flashIds.push(produit.id);
                setTimeout(() => { this.flashIds = this.flashIds.filter(x => x !== produit.id); }, 1000);
            },
            majQuantite(id, delta) {
                const item = this.panier.find(i => i.id === id);
                if (! item) return;
                item.quantite += delta;
                if (item.quantite <= 0) this.panier = this.panier.filter(i => i.id !== id);
            },
            ouvrirPanier() {
                if (this.nombreArticles === 0) return;
                this.vueCart = 'panier';
                this.error = '';
                this.cartOuvert = true;
            },
            envoyerCommande() {
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
                fetch('{{ $commanderUrl }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        nom_client: this.nom_client,
                        telephone_client: this.telephone_client,
                        mode: this.mode,
                        date_recuperation: this.date_recuperation,
                        heure_recuperation: this.heure_recuperation,
                        adresse_livraison: this.adresse_livraison,
                        items: this.panier.map(i => ({ produit_id: i.id, quantite: i.quantite })),
                    }),
                }).then(async (res) => {
                    const data = await res.json();
                    this.loading = false;
                    if (! res.ok) {
                        this.error = data.message || 'Une erreur est survenue.';
                        return;
                    }
                    this.numeroCommande = data.numero;
                    this.recuUrl = data.recuUrl;
                    window.open(data.recuUrl, '_blank');
                    this.panier = [];
                    this.vueCart = 'succes';
                }).catch(() => {
                    this.loading = false;
                    this.error = 'Connexion impossible. Réessayez.';
                });
            },
        }"
        @keydown.escape.window="cartOuvert = false; selected = null">

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

                <div class="flex items-center gap-2 shrink-0">
                    <button @click="partager()" type="button"
                            class="inline-flex items-center gap-1.5 text-xs font-body font-bold px-3.5 py-2 rounded-full border border-gray-200 hover:bg-gray-50 transition-colors">
                        <span class="material-symbols-outlined text-[16px]" x-show="!copied">share</span>
                        <span class="material-symbols-outlined text-[16px] text-primary-700" x-show="copied" x-cloak>check</span>
                        <span class="hidden sm:inline" x-text="copied ? 'Copié !' : 'Partager'"></span>
                    </button>

                    <button @click="ouvrirPanier()" type="button" class="relative h-9 w-9 flex items-center justify-center rounded-full border border-gray-200 hover:bg-gray-50 transition-colors">
                        <span class="material-symbols-outlined text-[19px] text-gray-700">shopping_bag</span>
                        <span x-show="nombreArticles > 0" x-cloak x-text="nombreArticles"
                              class="absolute -top-1.5 -right-1.5 text-white text-[10px] font-bold h-4.5 min-w-[18px] px-1 rounded-full flex items-center justify-center"
                              style="background-color: {{ $couleurAccent }}"></span>
                    </button>
                </div>
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
                <div x-data="{ selected: null, selQuantite: 1 }">

                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6">
                        @foreach ($produits as $produit)
                            @php($rupture = ! is_null($produit->stock) && $produit->stock <= 0)
                            @php($donneesProduit = [
                                'id' => $produit->id,
                                'nom' => $produit->nom,
                                'description' => $produit->description,
                                'prix' => $produit->prix,
                                'prixFormate' => $produit->prixFormate(),
                                'image' => $produit->image ? asset('storage/'.$produit->image) : null,
                                'livraison' => $produit->aLivraison(),
                                'prixLivraison' => $produit->prix_livraison,
                                'prixLivraisonFormate' => $produit->prix_livraison ? number_format($produit->prix_livraison, 0, ',', ' ').' Ar' : null,
                                'rupture' => $rupture,
                            ])
                            <div class="group bg-white {{ $rayonCard }} {{ $ombre }} {{ $bordure }} overflow-hidden transition-all duration-200 hover:-translate-y-0.5">
                                <div class="relative h-48 bg-gray-100 flex items-center justify-center overflow-hidden cursor-pointer"
                                     @click="selected = {{ \Illuminate\Support\Js::from($donneesProduit) }}; selQuantite = 1">
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
                                    <h3 class="font-body font-semibold text-sm text-gray-900 truncate cursor-pointer"
                                        @click="selected = {{ \Illuminate\Support\Js::from($donneesProduit) }}; selQuantite = 1">{{ $produit->nom }}</h3>
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

                                    @if ($rupture)
                                        <div class="w-full mt-3.5 text-center text-gray-400 text-sm py-2.5 bg-gray-50 {{ $rayonBtn }}">Indisponible</div>
                                    @else
                                        <button type="button" @click="ajouter({{ \Illuminate\Support\Js::from($donneesProduit) }})"
                                                class="w-full mt-3.5 text-white text-sm font-bold py-2.5 {{ $rayonBtn }} transition-all hover:opacity-90 flex items-center justify-center gap-1.5"
                                                style="background-color: {{ $couleurAccent }}">
                                            <span class="material-symbols-outlined text-[18px]" x-show="! flashIds.includes({{ $produit->id }})">add_shopping_cart</span>
                                            <span class="material-symbols-outlined text-[18px]" x-show="flashIds.includes({{ $produit->id }})" x-cloak>check</span>
                                            <span x-text="flashIds.includes({{ $produit->id }}) ? 'Ajouté !' : 'Ajouter au panier'"></span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- ============ MODAL DÉTAIL PRODUIT ============ --}}
                    <div x-show="selected" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6" style="display: none;">
                        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
                             x-show="selected" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             @click="selected = null"></div>

                        <div class="relative w-full max-w-md max-h-[92vh] overflow-y-auto bg-white rounded-2xl shadow-2xl"
                             x-show="selected" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                             @click.outside="selected = null">

                            <button @click="selected = null" aria-label="Fermer"
                                    class="absolute top-3 right-3 z-10 h-9 w-9 flex items-center justify-center rounded-full bg-white/90 text-gray-500 hover:bg-white shadow-sm transition-colors">
                                <span class="material-symbols-outlined text-[20px]">close</span>
                            </button>

                            <template x-if="selected">
                                <div>
                                    <div class="h-52 bg-gray-100 flex items-center justify-center overflow-hidden">
                                        <template x-if="selected.image">
                                            <img :src="selected.image" class="h-full w-full object-cover" :class="selected.rupture && 'grayscale opacity-60'">
                                        </template>
                                        <template x-if="! selected.image">
                                            <span class="material-symbols-outlined text-gray-300 text-6xl">inventory_2</span>
                                        </template>
                                    </div>

                                    <div class="p-6">
                                        <h2 class="font-display text-xl font-bold text-gray-900" x-text="selected.nom"></h2>
                                        <p class="font-display text-2xl font-bold mt-1" :style="`color: {{ $couleurAccent }}`" x-text="selected.prixFormate"></p>
                                        <p class="font-body text-sm text-gray-500 mt-3" x-show="selected.description" x-text="selected.description"></p>

                                        <div class="flex flex-wrap items-center gap-2 mt-4">
                                            <template x-if="selected.livraison">
                                                <span class="inline-flex items-center gap-1 bg-gray-50 text-gray-600 text-xs font-semibold px-2.5 py-1 rounded-full">
                                                    <span class="material-symbols-outlined text-[14px]">local_shipping</span>
                                                    <span x-text="'Livraison ' + selected.prixLivraisonFormate"></span>
                                                </span>
                                            </template>
                                            <template x-if="! selected.livraison">
                                                <span class="inline-flex items-center gap-1 bg-gray-50 text-gray-500 text-xs font-semibold px-2.5 py-1 rounded-full">Sans livraison</span>
                                            </template>
                                        </div>

                                        <template x-if="selected.rupture">
                                            <div class="w-full mt-6 text-center text-gray-400 font-body text-sm py-3 bg-gray-50 {{ $rayonBtn }}">Actuellement indisponible</div>
                                        </template>

                                        <template x-if="! selected.rupture">
                                            <div class="mt-6">
                                                <div class="flex items-center justify-center gap-4 mb-4">
                                                    <button type="button" @click="selQuantite = Math.max(1, selQuantite - 1)"
                                                            class="h-9 w-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50">−</button>
                                                    <span class="font-display font-bold text-lg w-8 text-center" x-text="selQuantite"></span>
                                                    <button type="button" @click="selQuantite++"
                                                            class="h-9 w-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50">+</button>
                                                </div>
                                                <button type="button" @click="for (let i = 0; i < selQuantite; i++) { ajouter(selected); } selected = null"
                                                        class="w-full flex items-center justify-center gap-2 text-white font-body font-bold text-sm py-3 {{ $rayonBtn }} transition-opacity hover:opacity-90"
                                                        :style="`background-color: {{ $couleurAccent }}`">
                                                    <span class="material-symbols-outlined text-[18px]">add_shopping_cart</span>
                                                    Ajouter au panier
                                                </button>
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

        {{-- ============ BOUTON PANIER FLOTTANT (mobile) ============ --}}
        <button type="button" @click="ouvrirPanier()" x-show="nombreArticles > 0" x-cloak
                class="sm:hidden fixed bottom-5 right-5 z-40 h-14 w-14 rounded-full shadow-xl flex items-center justify-center text-white"
                style="background-color: {{ $couleurAccent }}">
            <span class="material-symbols-outlined">shopping_bag</span>
            <span x-text="nombreArticles" class="absolute -top-1 -right-1 bg-white text-gray-900 text-[11px] font-bold h-5 min-w-[20px] px-1 rounded-full flex items-center justify-center border-2" :style="`border-color: {{ $couleurAccent }}`"></span>
        </button>

        {{-- ============ TIROIR PANIER ============ --}}
        <div x-show="cartOuvert" x-cloak class="fixed inset-0 z-[110] flex items-end sm:items-center justify-center sm:p-6" style="display: none;">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
                 x-show="cartOuvert" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 @click="cartOuvert = false"></div>

            <div class="relative w-full sm:max-w-md max-h-[88vh] overflow-y-auto bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl"
                 x-show="cartOuvert" x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-8"
                 @click.outside="cartOuvert = false">

                <div class="sticky top-0 bg-white border-b border-gray-100 px-5 py-4 flex items-center justify-between z-10">
                    <div class="flex items-center gap-2">
                        <button type="button" x-show="vueCart === 'checkout'" @click="vueCart = 'panier'" class="text-gray-400 hover:text-gray-700">
                            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                        </button>
                        <h2 class="font-display text-lg font-bold text-gray-900" x-text="vueCart === 'checkout' ? 'Vos informations' : (vueCart === 'succes' ? 'Commande envoyée' : 'Votre panier')"></h2>
                    </div>
                    <button @click="cartOuvert = false" class="h-9 w-9 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-700 hover:bg-gray-50">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                {{-- ---- VUE PANIER ---- --}}
                <div x-show="vueCart === 'panier'" class="p-5">
                    <template x-if="panier.length === 0">
                        <p class="text-center font-body text-sm text-gray-400 py-10">Votre panier est vide.</p>
                    </template>

                    <div class="space-y-3 mb-5">
                        <template x-for="item in panier" :key="item.id">
                            <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-3">
                                <div class="h-12 w-12 rounded-lg bg-gray-100 overflow-hidden shrink-0 flex items-center justify-center">
                                    <template x-if="item.image"><img :src="item.image" class="h-full w-full object-cover"></template>
                                    <template x-if="! item.image"><span class="material-symbols-outlined text-gray-300 text-[18px]">inventory_2</span></template>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-body font-semibold text-sm text-gray-900 truncate" x-text="item.nom"></p>
                                    <p class="font-body text-xs text-gray-500" x-text="formatteMonnaie(item.prix)"></p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" @click="majQuantite(item.id, -1)" class="h-7 w-7 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-white">−</button>
                                    <span class="font-body font-bold text-sm w-4 text-center" x-text="item.quantite"></span>
                                    <button type="button" @click="majQuantite(item.id, 1)" class="h-7 w-7 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-white">+</button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <template x-if="panier.length > 0">
                        <div>
                            <div class="flex justify-between font-body text-sm text-gray-600 mb-1">
                                <span>Sous-total (<span x-text="nombreArticles"></span> article<span x-show="nombreArticles > 1">s</span>)</span>
                                <span class="font-bold text-gray-900" x-text="formatteMonnaie(sousTotal)"></span>
                            </div>
                            <button type="button" @click="vueCart = 'checkout'"
                                    class="w-full mt-4 flex items-center justify-center gap-2 text-white font-body font-bold text-sm py-3 {{ $rayonBtn }} transition-opacity hover:opacity-90"
                                    :style="`background-color: {{ $couleurAccent }}`">
                                Commander
                                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                            </button>
                        </div>
                    </template>
                </div>

                {{-- ---- VUE CHECKOUT ---- --}}
                <div x-show="vueCart === 'checkout'" class="p-5">
                    <p x-show="error" x-cloak x-text="error" class="mb-3 text-xs font-body font-semibold text-accent-700 bg-accent-50 border border-accent-100 rounded-lg px-3 py-2"></p>

                    <div class="space-y-3 mb-4">
                        <input type="text" x-model="nom_client" placeholder="Votre nom"
                               class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600">
                        <input type="tel" x-model="telephone_client" placeholder="Votre numéro de téléphone"
                               class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600">
                    </div>

                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <label class="flex items-center justify-center gap-1.5 py-2.5 rounded-lg border-2 cursor-pointer text-xs font-body font-bold transition-colors"
                               :class="mode === 'recuperer' ? 'border-primary-600 bg-primary-50 text-primary-700' : 'border-gray-200 text-gray-500'">
                            <input type="radio" x-model="mode" value="recuperer" class="sr-only">
                            <span class="material-symbols-outlined text-[16px]">storefront</span> À récupérer
                        </label>
                        <label class="flex items-center justify-center gap-1.5 py-2.5 rounded-lg border-2 cursor-pointer text-xs font-body font-bold transition-colors"
                               :class="mode === 'livrer' ? 'border-primary-600 bg-primary-50 text-primary-700' : 'border-gray-200 text-gray-500'">
                            <input type="radio" x-model="mode" value="livrer" class="sr-only">
                            <span class="material-symbols-outlined text-[16px]">local_shipping</span> À livrer
                        </label>
                    </div>

                    <div x-show="mode === 'recuperer'" x-cloak class="grid grid-cols-2 gap-3 mb-3">
                        <input type="date" x-model="date_recuperation" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600">
                        <input type="time" x-model="heure_recuperation" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600">
                    </div>
                    <div x-show="mode === 'livrer'" x-cloak class="mb-3">
                        <textarea x-model="adresse_livraison" rows="2" placeholder="Adresse de livraison complète"
                                  class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 resize-none"></textarea>
                        <p class="font-body text-xs text-gray-400 mt-1" x-show="livraisonMax > 0">Frais de livraison : <span x-text="formatteMonnaie(livraisonMax)"></span></p>
                    </div>

                    <div class="border-t border-gray-100 pt-4 mt-2 space-y-1.5 mb-4">
                        <div class="flex justify-between font-body text-xs text-gray-500">
                            <span>Sous-total</span><span x-text="formatteMonnaie(sousTotal)"></span>
                        </div>
                        <div class="flex justify-between font-body text-xs text-gray-500" x-show="mode === 'livrer' && livraisonMax > 0">
                            <span>Livraison</span><span x-text="formatteMonnaie(livraisonMax)"></span>
                        </div>
                        <div class="flex justify-between font-display font-bold text-gray-900 pt-1.5 border-t border-gray-100">
                            <span>Total</span><span x-text="formatteMonnaie(total)"></span>
                        </div>
                    </div>

                    <button type="button" @click="envoyerCommande()" :disabled="loading"
                            class="w-full flex items-center justify-center gap-2 text-white font-body font-bold text-sm py-3 {{ $rayonBtn }} transition-opacity hover:opacity-90 disabled:opacity-60"
                            :style="`background-color: {{ $couleurAccent }}`">
                        <span x-text="loading ? 'Envoi en cours...' : 'Confirmer la commande'"></span>
                    </button>
                    <p class="font-body text-xs text-gray-400 text-center mt-2">Le vendeur recevra votre commande et vous contactera.</p>
                </div>

                {{-- ---- VUE SUCCÈS ---- --}}
                <div x-show="vueCart === 'succes'" class="p-8 text-center">
                    <span class="material-symbols-outlined text-6xl" :style="`color: {{ $couleurAccent }}`">check_circle</span>
                    <p class="font-display font-bold text-lg text-gray-900 mt-3">Commande envoyée !</p>
                    <p class="font-body text-sm text-gray-500 mt-1">N° <span class="font-semibold" x-text="numeroCommande"></span></p>
                    <p class="font-body text-sm text-gray-500 mt-1">Le vendeur vous contactera au numéro indiqué.</p>
                    <a :href="recuUrl"
                       class="mt-5 inline-flex items-center gap-2 text-white font-body font-bold text-sm px-6 py-3 rounded-xl transition-opacity hover:opacity-90"
                       :style="`background-color: {{ $couleurAccent }}`">
                        <span class="material-symbols-outlined text-[18px]">download</span>
                        Télécharger mon reçu
                    </a>
                    <p class="font-body text-xs text-gray-400 mt-2">Il s'est peut-être déjà téléchargé automatiquement.</p>
                    <button type="button" @click="cartOuvert = false; vueCart = 'panier'"
                            class="mt-3 inline-flex items-center gap-2 bg-gray-50 hover:bg-gray-100 text-gray-700 font-body font-bold text-sm px-6 py-3 rounded-xl transition-colors">
                        Fermer
                    </button>
                </div>
            </div>
        </div>

        {{-- ============ FOOTER ============ --}}
        <footer class="border-t border-gray-100 py-8 text-center">
            <p class="font-body text-xs text-gray-400">
                Boutique propulsée par
                <a href="{{ route('home') }}" class="font-semibold text-primary-700 hover:text-accent-600 transition-colors">Tafely</a>
            </p>
        </footer>
    </div>

@endsection