@extends('layouts.admin')

@section('title', 'Commandes et ventes — Admin Tafely')

@section('page-content')
    <div class="mb-6">
        <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Commandes et ventes</h1>
        <p class="font-body text-gray-500 mt-1">
            {{ $compteurs['toutes'] }} au total, toutes boutiques confondues
            — {{ $compteurs['en_ligne'] }} en ligne, {{ $compteurs['boutique'] }} en boutique.
        </p>
    </div>

    {{-- ============ ALERTE BOUTIQUES INACTIVES ============ --}}
    @if ($boutiquesInactives->isNotEmpty())
        <div class="mb-6 bg-accent-50 border border-accent-100 rounded-2xl p-5">
            <div class="flex items-start gap-3 mb-3">
                <span class="material-symbols-outlined text-accent-600 text-[22px]">campaign</span>
                <div>
                    <h2 class="font-display font-bold text-accent-800">{{ $boutiquesInactives->count() }} boutique{{ $boutiquesInactives->count() > 1 ? 's' : '' }} sans commande depuis 14 jours</h2>
                    <p class="font-body text-xs text-accent-700 mt-0.5">Candidates à une relance commerciale.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($boutiquesInactives as $b)
                    <a href="{{ route('admin.marchands.show', $b) }}"
                       class="inline-flex items-center gap-2 bg-white border border-accent-100 rounded-full px-3 py-1.5 text-xs font-body hover:bg-accent-50 transition-colors">
                        <span class="font-semibold text-gray-800">{{ $b->nom_boutique ?: $b->email }}</span>
                        <span class="material-symbols-outlined text-[14px] text-accent-500">chevron_right</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ============ GRAPHIQUE ============ --}}
    <div class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100 mb-6">
        <h2 class="font-display text-lg font-bold text-primary-900 mb-4">Évolution des commandes/jour — plateforme entière (30 derniers jours)</h2>
        <div class="relative" style="height: 240px;">
            <canvas id="admin-graph-commandes"></canvas>
        </div>
    </div>

    {{-- ============ FILTRES ============ --}}
    <form method="GET" action="{{ route('admin.commandes.index') }}" class="flex flex-col sm:flex-row gap-3 mb-4">
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[20px]">search</span>
            <input type="search" name="q" value="{{ $recherche }}" placeholder="N° commande, client, boutique, email..."
                   class="w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl bg-white text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-body text-sm">
        </div>
        <button type="submit" class="inline-flex items-center justify-center gap-2 bg-primary-800 hover:bg-primary-900 text-white font-body font-bold text-sm px-5 py-2.5 rounded-xl transition-colors">
            Rechercher
        </button>
    </form>

    <div class="flex flex-wrap gap-2 mb-6">
        @foreach ([
            ['cle' => 'toutes', 'label' => 'Toutes'],
            ['cle' => 'en_ligne', 'label' => 'En ligne'],
            ['cle' => 'boutique', 'label' => 'Boutique'],
        ] as $onglet)
            <a href="{{ route('admin.commandes.index', array_filter(['source' => $onglet['cle'] === 'toutes' ? null : $onglet['cle'], 'q' => $recherche ?: null])) }}"
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

    {{-- ============ TABLEAU ============ --}}
    @if ($commandes->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 md:p-20 flex flex-col items-center text-center">
            <span class="material-symbols-outlined text-primary-700 text-3xl mb-3">receipt_long</span>
            <p class="font-body text-sm text-gray-500">Aucune commande ne correspond à ces critères.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">N° / Client</th>
                            <th class="hidden md:table-cell px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Boutique</th>
                            <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Origine</th>
                            <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Total</th>
                            <th class="hidden sm:table-cell px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Statut</th>
                            <th class="hidden md:table-cell px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($commandes as $c)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-body font-semibold text-sm text-primary-900">{{ $c->numero }}</p>
                                    <p class="font-body text-xs text-gray-400">{{ $c->nomClientAffiche() }}</p>
                                </td>
                                <td class="hidden md:table-cell px-4 py-3">
                                    @if ($c->user)
                                        <a href="{{ route('admin.marchands.show', $c->user) }}" class="font-body text-sm text-primary-700 hover:text-accent-600 transition-colors">{{ $c->user->nom_boutique ?: $c->user->email }}</a>
                                    @else
                                        <span class="font-body text-sm text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($c->estVenteBoutique())
                                        <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                                            <span class="material-symbols-outlined text-[13px]">point_of_sale</span>
                                            Boutique
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 bg-primary-50 text-primary-700 text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                                            <span class="material-symbols-outlined text-[13px]">shopping_cart</span>
                                            En ligne
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-display font-bold text-sm text-primary-800 whitespace-nowrap">{{ $c->totalFormate() }}</td>
                                <td class="hidden sm:table-cell px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap',
                                        'bg-green-50 text-green-700' => $c->estVenteBoutique() || $c->statut === 'livree',
                                        'bg-primary-50 text-primary-700' => ! $c->estVenteBoutique() && $c->statut === 'en_cours_de_livraison',
                                        'bg-accent-50 text-accent-700' => ! $c->estVenteBoutique() && $c->statut === 'a_prendre_en_compte',
                                    ])>{{ $c->estVenteBoutique() ? 'Vendue' : $c->statutLabel() }}</span>
                                </td>
                                <td class="hidden md:table-cell px-4 py-3 font-body text-sm text-gray-500 whitespace-nowrap">{{ $c->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $commandes->links('pagination.tafely') }}
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            if (typeof Chart === 'undefined') return;

            new Chart(document.getElementById('admin-graph-commandes'), {
                type: 'line',
                data: {
                    labels: {{ \Illuminate\Support\Js::from($graphJours->pluck('label')) }},
                    datasets: [
                        {
                            label: 'En ligne',
                            data: {{ \Illuminate\Support\Js::from($graphJours->pluck('en_ligne')) }},
                            borderColor: '#1d4ed8',
                            backgroundColor: 'rgba(29, 78, 216, 0.08)',
                            fill: true, tension: 0.35, borderWidth: 2.5,
                            pointRadius: 2, pointBackgroundColor: '#1d4ed8',
                        },
                        {
                            label: 'Boutique',
                            data: {{ \Illuminate\Support\Js::from($graphJours->pluck('boutique')) }},
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.08)',
                            fill: true, tension: 0.35, borderWidth: 2.5,
                            pointRadius: 2, pointBackgroundColor: '#ef4444',
                        },
                    ],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { color: '#f3f4f6' } },
                        x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } },
                    },
                },
            });
        })();
    </script>
@endsection