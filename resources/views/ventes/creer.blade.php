@extends('layouts.dashboard')

@section('title', 'Nouvelle vente — Tafely')

@section('page-content')

    {{-- header --}}
    <div class="mb-8">
        <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Nouvelle vente en boutique</h1>
        <p class="font-body text-gray-500 mt-1">Enregistrez une vente faite directement dans votre boutique physique. Le stock est mis à jour automatiquement.</p>
    </div>

    @if ($produits->isEmpty())
        {{-- état vide --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 md:p-20 flex flex-col items-center text-center">
            <div class="h-16 w-16 rounded-full bg-primary-50 flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-primary-700 text-3xl">inventory_2</span>
            </div>
            <h2 class="font-display text-xl font-bold text-primary-900 mb-2">Aucun produit à vendre</h2>
            <p class="font-body text-sm text-gray-500 max-w-sm mb-6">Ajoutez d'abord vos produits pour pouvoir enregistrer des ventes en boutique.</p>
            <a href="{{ route('produits.create') }}"
               class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm px-6 py-3 rounded-xl shadow-sm transition-colors">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Ajouter un produit
            </a>
        </div>
    @else

        <div x-data="venteCaisse({{ \Illuminate\Support\Js::from($produits->map(fn ($p) => [
                'id' => $p->id,
                'nom' => $p->nom,
                'prix' => $p->prix,
                'prixFormate' => $p->prixFormate(),
                'image' => $p->image ? asset('storage/'.$p->image) : null,
                'stock' => $p->stock,
            ])->values()->all()) }})"
             class="grid lg:grid-cols-5 gap-6 items-start">

            {{-- ============ CHOIX DES PRODUITS ============ --}}
            <section class="lg:col-span-3">
                <div class="relative mb-4">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[20px]">search</span>
                    <input type="search" x-model="recherche" placeholder="Rechercher un produit..."
                           class="w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl bg-white text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors font-body text-sm">
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <template x-for="p in produitsFiltres" :key="p.id">
                        <button type="button" @click="ajouter(p)" :disabled="p.stock !== null && p.stock <= 0"
                                class="relative text-left bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden transition-all hover:shadow-md active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                            <div class="h-24 bg-gray-100 flex items-center justify-center overflow-hidden">
                                <template x-if="p.image">
                                    <img :src="p.image" :alt="p.nom" class="h-full w-full object-cover">
                                </template>
                                <template x-if="! p.image">
                                    <span class="material-symbols-outlined text-gray-300 text-4xl">inventory_2</span>
                                </template>
                            </div>
                            <div class="p-3">
                                <p class="font-body font-semibold text-sm text-gray-900 truncate" x-text="p.nom"></p>
                                <p class="font-display font-bold text-sm text-primary-800 mt-0.5" x-text="p.prixFormate"></p>
                                <p class="font-body text-xs mt-1"
                                   :class="p.stock !== null && p.stock <= 0 ? 'text-accent-600 font-semibold' : 'text-gray-400'"
                                   x-text="libelleStock(p)"></p>
                            </div>
                            <span x-show="quantiteDansPanier(p.id) > 0" x-cloak x-text="quantiteDansPanier(p.id)"
                                  class="absolute top-2 right-2 bg-accent-500 text-white text-[11px] font-bold h-5 min-w-[20px] px-1 rounded-full flex items-center justify-center shadow"></span>
                        </button>
                    </template>
                </div>

                <p x-show="produitsFiltres.length === 0" x-cloak class="text-center font-body text-sm text-gray-400 py-10">Aucun produit trouvé.</p>
            </section>

            {{-- ============ TICKET DE VENTE ============ --}}
            <aside x-ref="ticket" class="lg:col-span-2 lg:sticky lg:top-24 bg-white rounded-2xl border border-gray-100 shadow-sm p-5 md:p-6">

                {{-- ---- formulaire de vente ---- --}}
                <div x-show="! vente">
                    <div class="flex items-center gap-2.5 mb-4 border-b border-gray-100 pb-4">
                        <span class="material-symbols-outlined text-primary-700 text-[24px]">receipt_long</span>
                        <h2 class="font-display text-lg font-bold text-primary-900">Ticket de vente</h2>
                    </div>

                    <p x-show="erreur" x-cloak x-text="erreur"
                       class="mb-4 text-xs font-body font-semibold text-accent-700 bg-accent-50 border border-accent-100 rounded-lg px-3 py-2"></p>

                    {{-- lignes --}}
                    <p x-show="panier.length === 0" class="text-center font-body text-sm text-gray-400 py-8">Touchez un produit pour l'ajouter à la vente.</p>

                    <div class="space-y-2.5 mb-5">
                        <template x-for="item in panier" :key="item.id">
                            <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-body font-semibold text-sm text-gray-900 truncate" x-text="item.nom"></p>
                                    <p class="font-body text-xs text-gray-500" x-text="formatMonnaie(item.prix) + ' × ' + item.quantite + ' = ' + formatMonnaie(item.prix * item.quantite)"></p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" @click="majQuantite(item.id, -1)" class="h-7 w-7 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-white">−</button>
                                    <span class="font-body font-bold text-sm w-5 text-center" x-text="item.quantite"></span>
                                    <button type="button" @click="majQuantite(item.id, 1)" class="h-7 w-7 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-white">+</button>
                                    <button type="button" @click="retirer(item.id)" aria-label="Retirer" class="h-7 w-7 flex items-center justify-center rounded-full text-gray-400 hover:text-accent-600 hover:bg-white">
                                        <span class="material-symbols-outlined text-[18px]">close</span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- client (facultatif) --}}
                    <p class="font-body text-sm font-semibold text-primary-900 mb-2">Client <span class="font-normal text-gray-400">(facultatif)</span></p>
                    <div class="space-y-2.5 mb-5">
                        <input type="text" x-model="nomClient" maxlength="255" placeholder="Nom du client"
                               class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600">
                        <input type="tel" x-model="telephoneClient" maxlength="30" placeholder="Téléphone du client"
                               class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600">
                    </div>

                    {{-- paiement --}}
                    <p class="font-body text-sm font-semibold text-primary-900 mb-2">Moyen de paiement</p>
                    <div class="grid grid-cols-2 gap-2 mb-5">
                        @foreach ([
                            ['valeur' => 'especes', 'icone' => 'payments', 'label' => 'Espèces'],
                            ['valeur' => 'mvola', 'icone' => 'smartphone', 'label' => 'MVola'],
                            ['valeur' => 'orange_money', 'icone' => 'smartphone', 'label' => 'Orange Money'],
                            ['valeur' => 'autre', 'icone' => 'more_horiz', 'label' => 'Autre'],
                        ] as $moyen)
                            <label class="flex items-center justify-center gap-1.5 py-2.5 rounded-lg border-2 cursor-pointer text-xs font-body font-bold transition-colors"
                                   :class="modePaiement === '{{ $moyen['valeur'] }}' ? 'border-primary-600 bg-primary-50 text-primary-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                                <input type="radio" x-model="modePaiement" value="{{ $moyen['valeur'] }}" class="sr-only">
                                <span class="material-symbols-outlined text-[16px]">{{ $moyen['icone'] }}</span>
                                {{ $moyen['label'] }}
                            </label>
                        @endforeach
                    </div>

                    {{-- total --}}
                    <div class="flex justify-between items-center border-t border-gray-100 pt-4 mb-4">
                        <span class="font-body text-sm text-gray-500">Total (<span x-text="nombreArticles"></span> article<span x-show="nombreArticles > 1">s</span>)</span>
                        <span class="font-display text-2xl font-bold text-primary-900" x-text="formatMonnaie(total)"></span>
                    </div>

                    <button type="button" @click="enregistrer()" :disabled="loading || panier.length === 0"
                            class="w-full flex items-center justify-center gap-2 bg-accent-500 hover:bg-accent-600 disabled:opacity-60 disabled:cursor-not-allowed text-white font-body font-bold text-sm py-3.5 rounded-xl shadow-sm transition-all active:scale-[0.98]">
                        <span class="material-symbols-outlined text-[18px]" x-show="! loading">check</span>
                        <span x-text="loading ? 'Enregistrement...' : 'Enregistrer la vente'"></span>
                    </button>
                </div>

                {{-- ---- vente enregistrée ---- --}}
                <template x-if="vente">
                    <div class="text-center py-4">
                        <span class="material-symbols-outlined text-6xl text-primary-700" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                        <p class="font-display font-bold text-xl text-primary-900 mt-3">Vente enregistrée !</p>
                        <p class="font-body text-sm text-gray-500 mt-1">N° <span class="font-semibold text-gray-700" x-text="vente.numero"></span></p>
                        <p class="font-display text-2xl font-bold text-primary-900 mt-2" x-text="vente.total"></p>

                        <div class="mt-6 space-y-3">
                            <a :href="vente.factureUrl" target="_blank" rel="noopener"
                               class="w-full flex items-center justify-center gap-2 bg-primary-700 hover:bg-primary-800 text-white font-body font-bold text-sm py-3 rounded-xl transition-colors">
                                <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                                Générer la facture
                            </a>
                            <p class="font-body text-xs text-gray-400">Facultatif : seulement si le client la demande.</p>
                            <button type="button" @click="nouvelleVente()"
                                    class="w-full flex items-center justify-center gap-2 bg-gray-50 hover:bg-gray-100 text-gray-700 font-body font-bold text-sm py-3 rounded-xl transition-colors">
                                <span class="material-symbols-outlined text-[18px]">add</span>
                                Nouvelle vente
                            </button>
                        </div>
                    </div>
                </template>
            </aside>

            {{-- ============ BARRE FLOTTANTE (mobile) ============ --}}
            <button type="button" x-show="panier.length > 0 && ! vente" x-cloak
                    @click="$refs.ticket.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                    class="lg:hidden fixed bottom-24 left-4 right-4 z-40 flex items-center justify-between bg-primary-900 text-white rounded-xl shadow-lg px-4 py-3">
                <span class="font-body text-sm font-semibold"><span x-text="nombreArticles"></span> article(s) · Voir le ticket</span>
                <span class="font-display font-bold" x-text="formatMonnaie(total)"></span>
            </button>
        </div>

        <script>
            function venteCaisse(produits) {
                return {
                    produits: produits,
                    recherche: '',
                    panier: [],
                    nomClient: '',
                    telephoneClient: '',
                    modePaiement: 'especes',
                    loading: false,
                    erreur: '',
                    vente: null,

                    get produitsFiltres() {
                        const q = this.recherche.trim().toLowerCase();
                        return q ? this.produits.filter(p => p.nom.toLowerCase().includes(q)) : this.produits;
                    },
                    get nombreArticles() {
                        return this.panier.reduce((s, i) => s + i.quantite, 0);
                    },
                    get total() {
                        return this.panier.reduce((s, i) => s + i.prix * i.quantite, 0);
                    },

                    formatMonnaie(n) {
                        return new Intl.NumberFormat('fr-FR').format(n) + ' Ar';
                    },
                    libelleStock(p) {
                        if (p.stock === null) return 'Stock non suivi';
                        if (p.stock <= 0) return 'Rupture de stock';
                        return p.stock + ' en stock';
                    },
                    quantiteDansPanier(id) {
                        const item = this.panier.find(i => i.id === id);
                        return item ? item.quantite : 0;
                    },

                    ajouter(p) {
                        this.erreur = '';
                        const existant = this.panier.find(i => i.id === p.id);
                        const deja = existant ? existant.quantite : 0;

                        if (p.stock !== null && deja >= p.stock) {
                            this.erreur = p.stock <= 0
                                ? '« ' + p.nom + ' » est en rupture de stock.'
                                : 'Stock maximum atteint pour « ' + p.nom + ' » (' + p.stock + ').';
                            return;
                        }

                        if (existant) {
                            existant.quantite++;
                        } else {
                            this.panier.push({ id: p.id, nom: p.nom, prix: p.prix, stock: p.stock, quantite: 1 });
                        }
                    },
                    majQuantite(id, delta) {
                        this.erreur = '';
                        const item = this.panier.find(i => i.id === id);
                        if (! item) return;

                        if (delta > 0 && item.stock !== null && item.quantite >= item.stock) {
                            this.erreur = 'Stock maximum atteint pour « ' + item.nom + ' » (' + item.stock + ').';
                            return;
                        }

                        item.quantite += delta;
                        if (item.quantite <= 0) this.retirer(id);
                    },
                    retirer(id) {
                        this.panier = this.panier.filter(i => i.id !== id);
                    },

                    async enregistrer() {
                        this.erreur = '';
                        if (this.panier.length === 0) {
                            this.erreur = 'Ajoutez au moins un produit à la vente.';
                            return;
                        }

                        this.loading = true;
                        try {
                            const res = await fetch('{{ route('ventes.store') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                },
                                body: JSON.stringify({
                                    nom_client: this.nomClient,
                                    telephone_client: this.telephoneClient,
                                    mode_paiement: this.modePaiement,
                                    items: this.panier.map(i => ({ produit_id: i.id, quantite: i.quantite })),
                                }),
                            });
                            const data = await res.json();

                            if (! res.ok) {
                                this.erreur = data.message || 'Une erreur est survenue.';
                                return;
                            }

                            // Met à jour le stock affiché sans recharger la page.
                            this.panier.forEach(item => {
                                const p = this.produits.find(x => x.id === item.id);
                                if (p && p.stock !== null) p.stock -= item.quantite;
                            });

                            this.vente = { numero: data.numero, total: data.total, factureUrl: data.factureUrl };
                            this.panier = [];
                        } catch (e) {
                            this.erreur = 'Connexion impossible. Réessayez.';
                        } finally {
                            this.loading = false;
                        }
                    },

                    nouvelleVente() {
                        this.vente = null;
                        this.panier = [];
                        this.nomClient = '';
                        this.telephoneClient = '';
                        this.modePaiement = 'especes';
                        this.recherche = '';
                        this.erreur = '';
                    },
                };
            }
        </script>
    @endif

@endsection