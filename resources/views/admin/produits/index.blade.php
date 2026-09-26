@extends('layouts.admin')

@section('title', 'Produits — Admin Tafely')

@section('page-content')
    @if (session('status'))
        <div class="mb-6 flex items-center gap-3 bg-primary-50 border border-primary-100 text-primary-700 rounded-xl px-4 py-3">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span class="font-body text-sm font-semibold">{{ session('status') }}</span>
        </div>
    @endif

    <div class="mb-6">
        <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Catalogue produits</h1>
        <p class="font-body text-gray-500 mt-1">{{ $compteurs['tous'] }} produit{{ $compteurs['tous'] > 1 ? 's' : '' }} sur la plateforme, toutes boutiques confondues.</p>
    </div>

    {{-- ============ KPIs ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <span class="font-body text-sm text-gray-500">Produits au total</span>
                <div class="bg-primary-50 p-1.5 rounded-full">
                    <span class="material-symbols-outlined text-primary-700 text-[20px]">inventory_2</span>
                </div>
            </div>
            <span class="font-display text-4xl font-bold text-primary-900">{{ $compteurs['tous'] }}</span>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <span class="font-body text-sm text-gray-500">À vérifier</span>
                <div class="bg-accent-50 p-1.5 rounded-full">
                    <span class="material-symbols-outlined text-accent-600 text-[20px]">flag</span>
                </div>
            </div>
            <span class="font-display text-4xl font-bold text-primary-900">{{ $compteurs['signale'] }}</span>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <span class="font-body text-sm text-gray-500">En rupture de stock</span>
                <div class="bg-accent-50 p-1.5 rounded-full">
                    <span class="material-symbols-outlined text-accent-600 text-[20px]">production_quantity_limits</span>
                </div>
            </div>
            <span class="font-display text-4xl font-bold text-primary-900">{{ $compteurs['rupture'] }}</span>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <span class="font-body text-sm text-gray-500">Sans image</span>
                <div class="bg-gray-100 p-1.5 rounded-full">
                    <span class="material-symbols-outlined text-gray-500 text-[20px]">image_not_supported</span>
                </div>
            </div>
            <span class="font-display text-4xl font-bold text-primary-900">{{ $compteurs['sans_image'] }}</span>
        </div>
    </div>

    {{-- ============ GRAPHIQUE ============ --}}
    <div class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100 mb-6">
        <h2 class="font-display text-lg font-bold text-primary-900 mb-4">Produits ajoutés par semaine (12 dernières semaines)</h2>
        <div class="relative" style="height: 220px;">
            <canvas id="admin-graph-produits"></canvas>
        </div>
    </div>

    {{-- ============ FILTRES ============ --}}
    <form method="GET" action="{{ route('admin.produits.index') }}" class="flex flex-col sm:flex-row gap-3 mb-4">
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[20px]">search</span>
            <input type="search" name="q" value="{{ $recherche }}" placeholder="Nom du produit, boutique, email..."
                   class="w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl bg-white text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-body text-sm">
        </div>
        <button type="submit" class="inline-flex items-center justify-center gap-2 bg-primary-800 hover:bg-primary-900 text-white font-body font-bold text-sm px-5 py-2.5 rounded-xl transition-colors">
            Rechercher
        </button>
    </form>

    <div class="flex flex-wrap gap-2 mb-6">
        @foreach ([
            ['cle' => 'tous', 'label' => 'Tous'],
            ['cle' => 'signale', 'label' => 'À vérifier'],
            ['cle' => 'rupture', 'label' => 'Rupture de stock'],
            ['cle' => 'sans_image', 'label' => 'Sans image'],
        ] as $onglet)
            <a href="{{ route('admin.produits.index', array_filter(['filtre' => $onglet['cle'] === 'tous' ? null : $onglet['cle'], 'q' => $recherche ?: null])) }}"
               @class([
                   'inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-body font-semibold border transition-colors',
                   'bg-primary-700 text-white border-primary-700' => $filtre === $onglet['cle'],
                   'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' => $filtre !== $onglet['cle'],
               ])>
                {{ $onglet['label'] }}
                <span @class([
                    'text-xs font-bold px-2 py-0.5 rounded-full',
                    'bg-white/20 text-white' => $filtre === $onglet['cle'],
                    'bg-gray-100 text-gray-500' => $filtre !== $onglet['cle'],
                ])>{{ $compteurs[$onglet['cle']] }}</span>
            </a>
        @endforeach
    </div>

    {{-- ============ TABLEAU ============ --}}
    @if ($produits->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 md:p-20 flex flex-col items-center text-center">
            <span class="material-symbols-outlined text-primary-700 text-3xl mb-3">inventory_2</span>
            <p class="font-body text-sm text-gray-500">Aucun produit ne correspond à ces critères.</p>
        </div>
    @else
        <div x-data="{ selected: null }" @keydown.escape.window="selected = null">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Produit</th>
                                <th class="hidden md:table-cell px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Boutique</th>
                                <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Prix</th>
                                <th class="hidden sm:table-cell px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Stock</th>
                                <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Statut</th>
                                <th class="hidden md:table-cell px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Ajouté le</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($produits as $p)
                                <tr class="hover:bg-gray-50 transition-colors cursor-pointer"
                                    @click="selected = {{ \Illuminate\Support\Js::from([
                                        'id' => $p->id,
                                        'nom' => $p->nom,
                                        'description' => $p->description,
                                        'prix' => $p->prixFinalFormate(),
                                        'prixInitial' => $p->aRemise() ? $p->prixFormate() : null,
                                        'image' => $p->image ? asset('storage/'.$p->image) : null,
                                        'stock' => $p->stock,
                                        'livraison' => $p->aLivraison(),
                                        'prixLivraison' => $p->prix_livraison ? number_format($p->prix_livraison, 0, ',', ' ').' Ar' : null,
                                        'boutique' => $p->user?->nom_boutique ?? $p->user?->email ?? '—',
                                        'marchandUrl' => $p->user ? route('admin.marchands.show', $p->user) : null,
                                        'ajouteLe' => $p->created_at->format('d/m/Y à H:i'),
                                        'bloque' => $p->estBloque(),
                                        'bloqueRaison' => $p->bloque_raison,
                                        'bloqueLe' => $p->bloque_le?->format('d/m/Y'),
                                        'bloquerUrl' => route('admin.produits.bloquer', $p),
                                        'reactiverUrl' => route('admin.produits.reactiver', $p),
                                        'detruireUrl' => route('admin.produits.detruire', $p),
                                    ]) }}">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-lg bg-gray-100 overflow-hidden shrink-0 flex items-center justify-center">
                                                @if ($p->image)
                                                    <img src="{{ asset('storage/'.$p->image) }}" alt="" class="h-full w-full object-cover {{ $p->estBloque() ? 'grayscale opacity-50' : '' }}">
                                                @else
                                                    <span class="material-symbols-outlined text-gray-300 text-lg">image_not_supported</span>
                                                @endif
                                            </div>
                                            <span class="font-body font-semibold text-sm text-primary-900 truncate max-w-[160px]">{{ $p->nom }}</span>
                                        </div>
                                    </td>
                                    <td class="hidden md:table-cell px-4 py-3 font-body text-sm text-gray-600">{{ $p->user?->nom_boutique ?? $p->user?->email ?? '—' }}</td>
                                    <td class="px-4 py-3 font-display font-bold text-sm text-primary-800 whitespace-nowrap">{{ $p->prixFinalFormate() }}</td>
                                    <td class="hidden sm:table-cell px-4 py-3">
                                        @if (is_null($p->stock))
                                            <span class="text-gray-300 text-xs font-body">Non suivi</span>
                                        @else
                                            <span @class([
                                                'inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap',
                                                'bg-green-50 text-green-700' => $p->stock > 0,
                                                'bg-accent-50 text-accent-700' => $p->stock <= 0,
                                            ])>{{ $p->stock > 0 ? $p->stock : 'Rupture' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($p->estBloque())
                                            <span class="inline-flex items-center gap-1 bg-accent-50 text-accent-700 text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                                                <span class="material-symbols-outlined text-[13px]">block</span>
                                                Bloqué
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                                                Visible
                                            </span>
                                        @endif
                                    </td>
                                    <td class="hidden md:table-cell px-4 py-3 font-body text-sm text-gray-500 whitespace-nowrap">{{ $p->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $produits->links('pagination.tafely') }}

            {{-- ============ MODALE DÉTAIL PRODUIT ============ --}}
            <div x-show="selected" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6" style="display: none;">
                <div class="absolute inset-0 bg-primary-950/60 backdrop-blur-sm"
                     x-show="selected"
                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     @click="selected = null"></div>

                <div class="relative w-full max-w-lg max-h-[92vh] overflow-y-auto bg-white rounded-2xl shadow-2xl"
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
                            <div class="h-64 bg-gray-100 flex items-center justify-center overflow-hidden">
                                <template x-if="selected.image">
                                    <img :src="selected.image" class="h-full w-full object-cover" :class="selected.bloque && 'grayscale opacity-60'">
                                </template>
                                <template x-if="! selected.image">
                                    <span class="material-symbols-outlined text-gray-300 text-6xl">image_not_supported</span>
                                </template>
                            </div>

                            <div class="p-6">
                                <div class="flex items-start justify-between gap-3">
                                    <h2 class="font-display text-xl font-bold text-gray-900" x-text="selected.nom"></h2>
                                    <span x-show="selected.bloque" x-cloak class="shrink-0 inline-flex items-center gap-1 bg-accent-50 text-accent-700 text-xs font-bold px-2.5 py-1 rounded-full">
                                        <span class="material-symbols-outlined text-[13px]">block</span> Bloqué
                                    </span>
                                </div>

                                <p class="font-body text-xs text-gray-400 mt-1">
                                    <a :href="selected.marchandUrl" class="font-semibold text-primary-700 hover:text-accent-600 transition-colors" x-text="selected.boutique"></a>
                                    · Ajouté le <span x-text="selected.ajouteLe"></span>
                                </p>

                                <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1 mt-3">
                                    <p class="font-display text-2xl font-bold text-primary-800" x-text="selected.prix"></p>
                                    <template x-if="selected.prixInitial">
                                        <p class="font-body text-sm text-gray-400 line-through" x-text="selected.prixInitial"></p>
                                    </template>
                                </div>

                                <p class="font-body text-sm text-gray-600 mt-4 whitespace-pre-line" x-show="selected.description" x-text="selected.description"></p>
                                <p class="font-body text-sm text-gray-400 italic mt-4" x-show="! selected.description">Aucune description renseignée.</p>

                                <div class="flex flex-wrap items-center gap-2 mt-4">
                                    <template x-if="selected.livraison">
                                        <span class="inline-flex items-center gap-1 bg-primary-50 text-primary-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                            <span class="material-symbols-outlined text-[14px]">local_shipping</span>
                                            <span x-text="'Livraison ' + selected.prixLivraison"></span>
                                        </span>
                                    </template>
                                    <template x-if="selected.stock !== null">
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full"
                                              :class="selected.stock > 0 ? 'bg-green-50 text-green-700' : 'bg-accent-50 text-accent-700'"
                                              x-text="selected.stock > 0 ? selected.stock + ' en stock' : 'Rupture de stock'"></span>
                                    </template>
                                </div>

                                <template x-if="selected.bloque">
                                    <div class="mt-4 bg-accent-50 border border-accent-100 rounded-xl px-4 py-3">
                                        <p class="font-body text-xs text-accent-700"><span class="font-bold">Bloqué le</span> <span x-text="selected.bloqueLe"></span></p>
                                        <p class="font-body text-xs text-accent-700 mt-1" x-show="selected.bloqueRaison"><span class="font-bold">Raison :</span> <span x-text="selected.bloqueRaison"></span></p>
                                    </div>
                                </template>

                                {{-- ---- actions ---- --}}
                                <div class="mt-6 pt-6 border-t border-gray-100 space-y-3" x-data="{ vueAction: null }">

                                    <template x-if="! vueAction">
                                        <div class="flex flex-col sm:flex-row gap-3">
                                            <template x-if="! selected.bloque">
                                                <button type="button" @click="vueAction = 'bloquer'"
                                                        class="flex-1 inline-flex items-center justify-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm py-3 rounded-xl transition-colors">
                                                    <span class="material-symbols-outlined text-[18px]">block</span>
                                                    Bloquer ce produit
                                                </button>
                                            </template>
                                            <template x-if="selected.bloque">
                                                <form method="POST" :action="selected.reactiverUrl" class="flex-1">
                                                    @csrf
                                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-primary-800 hover:bg-primary-900 text-white font-body font-bold text-sm py-3 rounded-xl transition-colors">
                                                        <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                                        Réactiver
                                                    </button>
                                                </form>
                                            </template>

                                            <button type="button" @click="vueAction = 'supprimer'"
                                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-gray-50 hover:bg-gray-100 text-gray-700 font-body font-bold text-sm py-3 rounded-xl transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                                Supprimer
                                            </button>
                                        </div>
                                    </template>

                                    <template x-if="vueAction === 'bloquer'">
                                        <form method="POST" :action="selected.bloquerUrl" class="space-y-3">
                                            @csrf
                                            <label class="block font-body text-xs font-semibold text-gray-600">Raison du blocage (envoyée au marchand)</label>
                                            <textarea name="raison" rows="3" required maxlength="255" placeholder="ex : contenu lié à des stupéfiants / à caractère explicite"
                                                      class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-accent-500 resize-none"></textarea>
                                            <div class="flex gap-2">
                                                <button type="button" @click="vueAction = null" class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-600 font-body font-bold text-sm py-2.5 rounded-xl transition-colors">Annuler</button>
                                                <button type="submit" class="flex-1 bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm py-2.5 rounded-xl transition-colors">Confirmer le blocage</button>
                                            </div>
                                        </form>
                                    </template>

                                    <template x-if="vueAction === 'supprimer'">
                                        <form method="POST" :action="selected.detruireUrl" class="space-y-3"
                                              onsubmit="return confirm('Supprimer définitivement ce produit ? Cette action est irréversible.');">
                                            @csrf
                                            @method('DELETE')
                                            <label class="block font-body text-xs font-semibold text-gray-600">Raison de la suppression (envoyée au marchand)</label>
                                            <textarea name="raison" rows="3" required maxlength="255" placeholder="ex : contenu lié à des stupéfiants / à caractère explicite"
                                                      class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-accent-500 resize-none"></textarea>
                                            <div class="flex gap-2">
                                                <button type="button" @click="vueAction = null" class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-600 font-body font-bold text-sm py-2.5 rounded-xl transition-colors">Annuler</button>
                                                <button type="submit" class="flex-1 bg-gray-900 hover:bg-black text-white font-body font-bold text-sm py-2.5 rounded-xl transition-colors">Confirmer la suppression</button>
                                            </div>
                                        </form>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            if (typeof Chart === 'undefined') return;

            new Chart(document.getElementById('admin-graph-produits'), {
                type: 'bar',
                data: {
                    labels: {{ \Illuminate\Support\Js::from($graphAjouts->pluck('label')) }},
                    datasets: [{
                        label: 'Produits ajoutés',
                        data: {{ \Illuminate\Support\Js::from($graphAjouts->pluck('total')) }},
                        backgroundColor: '#1d4ed8',
                        borderRadius: 6,
                        maxBarThickness: 28,
                    }],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { color: '#f3f4f6' } },
                        x: { grid: { display: false } },
                    },
                },
            });
        })();
    </script>
@endsection