@extends('layouts.dashboard')

@section('title', 'Commandes — Tafely')

@section('page-content')

    {{-- header --}}
    <div class="mb-6">
        <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Commandes</h1>
        <p class="font-body text-gray-500 mt-1">
            {{ $compteurs['toutes'] }} commande{{ $compteurs['toutes'] > 1 ? 's' : '' }} au total
            — {{ $compteurs['en_ligne'] }} en ligne, {{ $compteurs['boutique'] }} en boutique.
        </p>
    </div>

    {{-- succès --}}
    @if (session('status'))
        <div class="mb-6 flex items-center gap-3 bg-primary-50 border border-primary-100 text-primary-700 rounded-xl px-4 py-3">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span class="font-body text-sm font-semibold">{{ session('status') }}</span>
        </div>
    @endif

    {{-- onglets d'origine --}}
    @if ($compteurs['toutes'] > 0)
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach ([
                ['cle' => 'toutes', 'label' => 'Toutes'],
                ['cle' => 'en_ligne', 'label' => 'En ligne'],
                ['cle' => 'boutique', 'label' => 'Boutique'],
            ] as $onglet)
                <a href="{{ route('commandes', $onglet['cle'] === 'toutes' ? [] : ['source' => $onglet['cle']]) }}"
                   @class([
                       'inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-body font-semibold border transition-colors',
                       'bg-primary-700 text-white border-primary-700' => $source === $onglet['cle'],
                       'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' => $source !== $onglet['cle'],
                   ])>
                    {{ $onglet['label'] }}
                    <span @class([
                        'text-xs font-bold px-2 py-0.5 rounded-full',
                        'bg-white/20 text-white' => $source === $onglet['cle'],
                        'bg-gray-100 text-gray-500' => $source !== $onglet['cle'],
                    ])>{{ $compteurs[$onglet['cle']] }}</span>
                </a>
            @endforeach
        </div>
    @endif

    @if ($commandes->isEmpty())
        {{-- état vide --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 md:p-20 flex flex-col items-center text-center">
            <div class="h-16 w-16 rounded-full bg-primary-50 flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-primary-700 text-3xl">{{ $source === 'boutique' ? 'point_of_sale' : 'shopping_cart' }}</span>
            </div>
            <h2 class="font-display text-xl font-bold text-primary-900 mb-2">
                @if ($source === 'boutique')
                    Aucune vente en boutique
                @elseif ($source === 'en_ligne')
                    Aucune commande en ligne
                @else
                    Aucune commande pour l'instant
                @endif
            </h2>
            @if ($source === 'boutique')
                <p class="font-body text-sm text-gray-500 max-w-sm mb-6">Enregistrez une vente faite dans votre boutique physique pour la retrouver ici.</p>
                <a href="{{ route('ventes.create') }}"
                   class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm px-6 py-3 rounded-xl shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-[18px]">point_of_sale</span>
                    Nouvelle vente
                </a>
            @else
                <p class="font-body text-sm text-gray-500 max-w-sm">Partagez le lien de votre boutique pour recevoir vos premières commandes.</p>
            @endif
        </div>
    @else
        <div x-data="{ selected: null }" @keydown.escape.window="selected = null">

            {{-- ============ TABLEAU ============ --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">N° / Client</th>
                                <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Articles</th>
                                <th class="hidden md:table-cell px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Mode</th>
                                <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Total</th>
                                <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Statut</th>
                                <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide text-right">Facture</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($commandes as $commande)
                                @php($premiereLigne = $commande->lignes->first())
                                <tr class="hover:bg-gray-50 transition-colors cursor-pointer"
                                    @click="selected = {{ \Illuminate\Support\Js::from([
                                        'numero' => $commande->numero,
                                        'source' => $commande->source,
                                        'client' => $commande->nomClientAffiche(),
                                        'telephone' => $commande->telephone_client,
                                        'paiement' => $commande->modePaiementLabel(),
                                        'total' => $commande->totalFormate(),
                                        'sousTotal' => $commande->sousTotalFormate(),
                                        'mode' => $commande->mode,
                                        'date' => $commande->date_recuperation?->format('d/m/Y'),
                                        'heure' => $commande->heure_recuperation,
                                        'adresse' => $commande->adresse_livraison,
                                        'creeLe' => $commande->created_at->format('d/m/Y à H:i'),
                                        'factureUrl' => route('commandes.facture', $commande),
                                        'lignes' => $commande->lignes->map(fn ($l) => [
                                            'nom' => $l->nom_produit,
                                            'quantite' => $l->quantite,
                                            'sousTotal' => $l->sousTotalFormate(),
                                        ])->all(),
                                    ]) }}">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <p class="font-body font-semibold text-sm text-primary-900">{{ $commande->nomClientAffiche() }}</p>
                                            @if ($commande->estVenteBoutique())
                                                <span class="bg-primary-50 text-primary-700 text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full">Boutique</span>
                                            @endif
                                        </div>
                                        <p class="font-body text-xs text-gray-400">{{ $commande->numero }}@if ($commande->telephone_client) · {{ $commande->telephone_client }}@endif</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-body text-sm text-gray-700 truncate max-w-[180px]">
                                            {{ $premiereLigne->nom_produit ?? '—' }}
                                            @if ($commande->lignes->count() > 1)
                                                <span class="text-gray-400">+{{ $commande->lignes->count() - 1 }} autre{{ $commande->lignes->count() - 1 > 1 ? 's' : '' }}</span>
                                            @endif
                                        </p>
                                        <p class="font-body text-xs text-gray-400">{{ $commande->nombreArticles() }} article(s)</p>
                                    </td>
                                    <td class="hidden md:table-cell px-4 py-3">
                                        @if ($commande->estVenteBoutique())
                                            <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                                                <span class="material-symbols-outlined text-[13px]">point_of_sale</span>
                                                Sur place
                                            </span>
                                        @elseif ($commande->estALivrer())
                                            <span class="inline-flex items-center gap-1 bg-primary-50 text-primary-700 text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                                                <span class="material-symbols-outlined text-[13px]">local_shipping</span>
                                                À livrer
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                                                <span class="material-symbols-outlined text-[13px]">storefront</span>
                                                À récupérer
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-display font-bold text-sm text-primary-800 whitespace-nowrap">
                                        {{ $commande->totalFormate() }}
                                    </td>
                                    <td class="px-4 py-3" @click.stop>
                                        @if ($commande->estVenteBoutique())
                                            <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 text-xs font-body font-bold px-3 py-1.5 rounded-full whitespace-nowrap">
                                                <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                                Vendue · {{ $commande->modePaiementLabel() }}
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('commandes.statut', $commande) }}">
                                                @csrf
                                                @method('PUT')
                                                <select name="statut" onchange="this.form.submit()"
                                                        class="text-xs font-body font-bold rounded-full px-3 py-1.5 border-0 cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-600
                                                            {{ match($commande->statut) {
                                                                'livree' => 'bg-green-50 text-green-700',
                                                                'en_cours_de_livraison' => 'bg-primary-50 text-primary-700',
                                                                default => 'bg-accent-50 text-accent-700',
                                                            } }}">
                                                    <option value="a_prendre_en_compte" @selected($commande->statut === 'a_prendre_en_compte')>À prendre en compte</option>
                                                    <option value="en_cours_de_livraison" @selected($commande->statut === 'en_cours_de_livraison')>En cours de livraison</option>
                                                    <option value="livree" @selected($commande->statut === 'livree')>Livrée</option>
                                                </select>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right" @click.stop>
                                        <a href="{{ route('commandes.facture', $commande) }}" target="_blank"
                                           class="inline-flex items-center gap-1 text-xs font-body font-semibold text-primary-700 hover:text-accent-600 transition-colors">
                                            <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                                            Voir
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- pagination --}}
            {{ $commandes->links('pagination.tafely') }}

            {{-- ============ MODAL DÉTAIL COMMANDE ============ --}}
            <div x-show="selected" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6" style="display: none;">
                <div class="absolute inset-0 bg-primary-950/60 backdrop-blur-sm"
                     x-show="selected"
                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     @click="selected = null"></div>

                <div class="relative w-full max-w-md max-h-[90vh] overflow-y-auto bg-white rounded-2xl shadow-2xl p-6"
                     x-show="selected"
                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     @click.outside="selected = null">

                    <button @click="selected = null" aria-label="Fermer"
                            class="absolute top-4 right-4 h-9 w-9 flex items-center justify-center rounded-full text-gray-400 hover:text-accent-600 hover:bg-gray-50 transition-colors">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>

                    <template x-if="selected">
                        <div>
                            <h2 class="font-display text-lg font-bold text-primary-900 mb-1"
                                x-text="selected.source === 'boutique' ? 'Détail de la vente en boutique' : 'Détail de la commande'"></h2>
                            <p class="font-body text-xs text-gray-400 mb-4" x-text="selected.numero"></p>

                            {{-- articles --}}
                            <div class="bg-gray-50 rounded-xl p-3 mb-4 space-y-2">
                                <template x-for="ligne in selected.lignes" :key="ligne.nom">
                                    <div class="flex justify-between font-body text-sm">
                                        <span class="text-gray-700" x-text="ligne.nom + ' x' + ligne.quantite"></span>
                                        <span class="font-semibold text-gray-900" x-text="ligne.sousTotal"></span>
                                    </div>
                                </template>
                            </div>

                            <dl class="space-y-3 font-body text-sm">
                                <div class="flex justify-between gap-4">
                                    <dt class="text-gray-500">Client</dt>
                                    <dd class="font-semibold text-primary-900 text-right" x-text="selected.client"></dd>
                                </div>
                                <template x-if="selected.telephone">
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-gray-500">Téléphone</dt>
                                        <dd class="font-semibold text-primary-900 text-right" x-text="selected.telephone"></dd>
                                    </div>
                                </template>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-gray-500">Total</dt>
                                    <dd class="font-bold text-primary-900 text-right" x-text="selected.total"></dd>
                                </div>
                                <template x-if="selected.source === 'boutique'">
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-gray-500">Paiement</dt>
                                        <dd class="font-semibold text-primary-900 text-right" x-text="selected.paiement"></dd>
                                    </div>
                                </template>
                                <div class="h-px bg-gray-100"></div>
                                <template x-if="selected.mode === 'recuperer'">
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-gray-500">À récupérer</dt>
                                        <dd class="font-semibold text-primary-900 text-right" x-text="(selected.date || '—') + ' à ' + (selected.heure || '—')"></dd>
                                    </div>
                                </template>
                                <template x-if="selected.mode === 'livrer'">
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-gray-500">Adresse de livraison</dt>
                                        <dd class="font-semibold text-primary-900 text-right" x-text="selected.adresse"></dd>
                                    </div>
                                </template>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-gray-500" x-text="selected.source === 'boutique' ? 'Vendue le' : 'Reçue le'"></dt>
                                    <dd class="text-gray-600 text-right" x-text="selected.creeLe"></dd>
                                </div>
                            </dl>

                            <div class="flex gap-3 mt-6">
                                <template x-if="selected.telephone">
                                    <a :href="'tel:' + selected.telephone"
                                       class="flex-1 flex items-center justify-center gap-2 bg-primary-700 hover:bg-primary-800 text-white font-body font-bold text-sm py-3 rounded-xl transition-colors">
                                        <span class="material-symbols-outlined text-[18px]">call</span>
                                        Appeler
                                    </a>
                                </template>
                                <a :href="selected.factureUrl" target="_blank"
                                   class="flex-1 flex items-center justify-center gap-2 bg-gray-50 hover:bg-gray-100 text-gray-700 font-body font-bold text-sm py-3 rounded-xl transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                                    Facture
                                </a>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    @endif

@endsection