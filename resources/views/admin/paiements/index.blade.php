@extends('layouts.admin')

@section('title', 'Abonnements et paiements — Admin Tafely')

@section('page-content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Abonnements et paiements</h1>
            <p class="font-body text-gray-500 mt-1">{{ $compteurs['tous'] }} transaction{{ $compteurs['tous'] > 1 ? 's' : '' }} au total.</p>
        </div>
        <a href="{{ route('admin.paiements.export', request()->query()) }}"
           class="inline-flex items-center justify-center gap-2 bg-primary-800 hover:bg-primary-900 text-white font-body font-bold text-sm px-5 py-2.5 rounded-xl transition-colors shrink-0">
            <span class="material-symbols-outlined text-[18px]">download</span>
            Exporter en CSV
        </a>
    </div>

    {{-- ============ ALERTE PAIEMENTS BLOQUÉS ============ --}}
    @if ($bloques->isNotEmpty())
        <div class="mb-6 bg-accent-50 border border-accent-100 rounded-2xl p-5">
            <div class="flex items-start gap-3 mb-3">
                <span class="material-symbols-outlined text-accent-600 text-[22px]">warning</span>
                <div>
                    <h2 class="font-display font-bold text-accent-800">{{ $bloques->count() }} paiement{{ $bloques->count() > 1 ? 's' : '' }} en attente depuis plus de 30 min</h2>
                    <p class="font-body text-xs text-accent-700 mt-0.5">Le callback Papi n'a probablement jamais été reçu — à vérifier manuellement auprès de Papi.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($bloques as $b)
                    <span class="inline-flex items-center gap-2 bg-white border border-accent-100 rounded-full px-3 py-1.5 text-xs font-body">
                        <span class="font-semibold text-gray-800">{{ $b->reference }}</span>
                        <span class="text-gray-400">·</span>
                        <span class="text-gray-500">{{ $b->user?->nom_boutique ?? '—' }}</span>
                        <span class="text-gray-400">·</span>
                        <span class="text-gray-500">{{ $b->created_at->diffForHumans() }}</span>
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ============ GRAPHIQUES ============ --}}
    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
            <h2 class="font-display text-lg font-bold text-primary-900 mb-4">Volume par méthode de paiement</h2>
            <div class="relative flex items-center justify-center" style="height: 220px;">
                <canvas id="admin-graph-methodes"></canvas>
            </div>
            <div class="flex flex-wrap justify-center gap-4 mt-4">
                @foreach ($volumeMethodes as $m)
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full" style="background-color: {{ ['mvola' => '#1d4ed8', 'orange_money' => '#ea580c', 'visa' => '#7c3aed'][$m->methode] ?? '#9ca3af' }}"></span>
                        <span class="font-body text-xs text-gray-600">{{ ucfirst(str_replace('_', ' ', $m->methode)) }} — {{ number_format($m->montant, 0, ',', ' ') }} Ar ({{ $m->nombre }})</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
            <h2 class="font-display text-lg font-bold text-primary-900 mb-4">Revenu par mois — abonnements vs packs</h2>
            <div class="relative" style="height: 220px;">
                <canvas id="admin-graph-revenu-mois"></canvas>
            </div>
        </div>
    </div>

    {{-- ============ FILTRES ============ --}}
    <form method="GET" action="{{ route('admin.paiements.index') }}" class="flex flex-col sm:flex-row gap-3 mb-4">
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[20px]">search</span>
            <input type="search" name="q" value="{{ $recherche }}" placeholder="Référence, transaction, boutique, email..."
                   class="w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl bg-white text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-body text-sm">
        </div>
        <select name="type" onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl bg-white px-3.5 py-2.5 font-body text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-600">
            <option value="tous" @selected($type === 'tous')>Tous les types</option>
            <option value="abonnement" @selected($type === 'abonnement')>Abonnement</option>
            <option value="pack_produits" @selected($type === 'pack_produits')>Pack produits</option>
        </select>
        <button type="submit" class="inline-flex items-center justify-center gap-2 bg-primary-800 hover:bg-primary-900 text-white font-body font-bold text-sm px-5 py-2.5 rounded-xl transition-colors">
            Filtrer
        </button>
    </form>

    <div class="flex flex-wrap gap-2 mb-6">
        @foreach ([
            ['cle' => 'tous', 'label' => 'Tous'],
            ['cle' => 'paye', 'label' => 'Payés'],
            ['cle' => 'echoue', 'label' => 'Échoués'],
            ['cle' => 'en_attente', 'label' => 'En attente'],
        ] as $onglet)
            <a href="{{ route('admin.paiements.index', array_filter(['statut' => $onglet['cle'] === 'tous' ? null : $onglet['cle'], 'type' => $type !== 'tous' ? $type : null, 'q' => $recherche ?: null])) }}"
               @class([
                   'inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-body font-semibold border transition-colors',
                   'bg-primary-700 text-white border-primary-700' => $statut === $onglet['cle'],
                   'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' => $statut !== $onglet['cle'],
               ])>
                {{ $onglet['label'] }}
                <span @class([
                    'text-xs font-bold px-2 py-0.5 rounded-full',
                    'bg-white/20 text-white' => $statut === $onglet['cle'],
                    'bg-gray-100 text-gray-500' => $statut !== $onglet['cle'],
                ])>{{ $compteurs[$onglet['cle']] }}</span>
            </a>
        @endforeach
    </div>

    {{-- ============ TABLEAU ============ --}}
    @if ($paiements->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 md:p-20 flex flex-col items-center text-center">
            <span class="material-symbols-outlined text-primary-700 text-3xl mb-3">payments</span>
            <p class="font-body text-sm text-gray-500">Aucune transaction ne correspond à ces critères.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Référence</th>
                            <th class="hidden md:table-cell px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Boutique</th>
                            <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Type</th>
                            <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Montant</th>
                            <th class="hidden sm:table-cell px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Méthode</th>
                            <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Statut</th>
                            <th class="hidden md:table-cell px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($paiements as $p)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-body font-semibold text-sm text-primary-900">{{ $p->reference }}</p>
                                    @if ($p->papi_transaction_id)
                                        <p class="font-body text-xs text-gray-400">{{ $p->papi_transaction_id }}</p>
                                    @endif
                                </td>
                                <td class="hidden md:table-cell px-4 py-3">
                                    @if ($p->user)
                                        <a href="{{ route('admin.marchands.show', $p->user) }}" class="font-body text-sm text-primary-700 hover:text-accent-600 transition-colors">{{ $p->user->nom_boutique ?: $p->user->email }}</a>
                                    @else
                                        <span class="font-body text-sm text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                                        {{ $p->type === 'abonnement' ? 'Abonnement' : 'Pack produits' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-display font-bold text-sm text-primary-800 whitespace-nowrap">{{ $p->montantFormate() }}</td>
                                <td class="hidden sm:table-cell px-4 py-3 font-body text-sm text-gray-600">{{ $p->papi_payment_method ? ucfirst(str_replace('_', ' ', $p->papi_payment_method)) : '—' }}</td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap',
                                        'bg-green-50 text-green-700' => $p->statut === 'paye',
                                        'bg-accent-50 text-accent-700' => $p->statut === 'echoue',
                                        'bg-gray-100 text-gray-600' => $p->statut === 'en_attente',
                                    ])>{{ ucfirst(str_replace('_', ' ', $p->statut)) }}</span>
                                </td>
                                <td class="hidden md:table-cell px-4 py-3 font-body text-sm text-gray-500 whitespace-nowrap">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $paiements->links('pagination.tafely') }}
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            if (typeof Chart === 'undefined') return;

            const couleursMethodes = { mvola: '#1d4ed8', orange_money: '#ea580c', visa: '#7c3aed' };
            const methodes = {{ \Illuminate\Support\Js::from($volumeMethodes->pluck('methode')) }};
            const montants = {{ \Illuminate\Support\Js::from($volumeMethodes->pluck('montant')) }};

            new Chart(document.getElementById('admin-graph-methodes'), {
                type: 'doughnut',
                data: {
                    labels: methodes.map(m => m.charAt(0).toUpperCase() + m.slice(1).replace('_', ' ')),
                    datasets: [{
                        data: montants,
                        backgroundColor: methodes.map(m => couleursMethodes[m] || '#9ca3af'),
                        borderWidth: 0,
                    }],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: (ctx) => new Intl.NumberFormat('fr-FR').format(ctx.parsed) + ' Ar' } },
                    },
                },
            });

            new Chart(document.getElementById('admin-graph-revenu-mois'), {
                type: 'bar',
                data: {
                    labels: {{ \Illuminate\Support\Js::from($graphRevenu->pluck('label')) }},
                    datasets: [
                        {
                            label: 'Abonnements',
                            data: {{ \Illuminate\Support\Js::from($graphRevenu->pluck('abonnements')) }},
                            backgroundColor: '#1d4ed8',
                            borderRadius: 4,
                        },
                        {
                            label: 'Packs produits',
                            data: {{ \Illuminate\Support\Js::from($graphRevenu->pluck('packs')) }},
                            backgroundColor: '#ef4444',
                            borderRadius: 4,
                        },
                    ],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
                        tooltip: { callbacks: { label: (ctx) => ctx.dataset.label + ' : ' + new Intl.NumberFormat('fr-FR').format(ctx.parsed.y) + ' Ar' } },
                    },
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: { stacked: true, beginAtZero: true, ticks: { callback: (v) => new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(v) }, grid: { color: '#f3f4f6' } },
                    },
                },
            });
        })();
    </script>
@endsection