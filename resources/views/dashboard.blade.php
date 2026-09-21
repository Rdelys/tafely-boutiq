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
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">
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

    {{-- ============ CHIFFRE D'AFFAIRES ============ --}}
    <div class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100 mb-8">
        <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
            <h2 class="font-display text-lg font-bold text-primary-900">Chiffre d'affaires</h2>
            <form method="GET" action="{{ route('dashboard') }}">
                <select name="filtre" onchange="this.form.submit()"
                        class="text-xs font-body font-bold rounded-full px-3.5 py-2 border border-gray-200 bg-gray-50 text-gray-600 cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-600">
                    <option value="toutes" @selected($filtre === 'toutes')>Toutes les commandes</option>
                    <option value="livrees" @selected($filtre === 'livrees')>Commandes livrées uniquement</option>
                </select>
            </form>
        </div>

        {{-- 3 totaux --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-primary-50 rounded-xl p-4">
                <p class="font-body text-xs font-semibold text-primary-700 uppercase tracking-wide mb-1">Aujourd'hui</p>
                <p class="font-display text-2xl font-bold text-primary-900">{{ number_format($revenus['jour'], 0, ',', ' ') }} Ar</p>
            </div>
            <div class="bg-accent-50 rounded-xl p-4">
                <p class="font-body text-xs font-semibold text-accent-700 uppercase tracking-wide mb-1">Ce mois-ci</p>
                <p class="font-display text-2xl font-bold text-primary-900">{{ number_format($revenus['mois'], 0, ',', ' ') }} Ar</p>
            </div>
            <div class="bg-gray-900 rounded-xl p-4">
                <p class="font-body text-xs font-semibold text-gray-300 uppercase tracking-wide mb-1">Total (tout temps)</p>
                <p class="font-display text-2xl font-bold text-white">{{ number_format($revenus['total'], 0, ',', ' ') }} Ar</p>
            </div>
        </div>

        {{-- graphique revenu --}}
        <div class="flex items-center justify-between mb-3">
            <p class="font-body text-sm font-semibold text-gray-600">Évolution du chiffre d'affaires</p>
            <div class="inline-flex rounded-full bg-gray-100 p-1">
                <button type="button" id="btn-revenu-jour" onclick="tafelyAfficherRevenu('jour')"
                        class="px-3.5 py-1.5 rounded-full text-xs font-body font-bold transition-all bg-white shadow-sm text-primary-700">
                    Par jour
                </button>
                <button type="button" id="btn-revenu-mois" onclick="tafelyAfficherRevenu('mois')"
                        class="px-3.5 py-1.5 rounded-full text-xs font-body font-bold transition-all text-gray-500">
                    Par mois
                </button>
            </div>
        </div>
        <div class="relative" style="height: 240px;">
            <canvas id="tafely-graph-revenu"></canvas>
        </div>
    </div>

    {{-- suivi des commandes par statut --}}
    <div class="mb-8">
        <h2 class="font-display text-lg font-bold text-primary-900 mb-4">Suivi des commandes</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('commandes') }}" class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="h-12 w-12 rounded-full bg-accent-50 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-accent-600 text-[22px]">pending_actions</span>
                </div>
                <div>
                    <p class="font-display text-2xl font-bold text-primary-900">{{ $statutCounts['a_prendre_en_compte'] }}</p>
                    <p class="font-body text-xs text-gray-500">À prendre en compte</p>
                </div>
            </a>
            <a href="{{ route('commandes') }}" class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="h-12 w-12 rounded-full bg-primary-50 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-primary-700 text-[22px]">local_shipping</span>
                </div>
                <div>
                    <p class="font-display text-2xl font-bold text-primary-900">{{ $statutCounts['en_cours_de_livraison'] }}</p>
                    <p class="font-body text-xs text-gray-500">En cours de livraison</p>
                </div>
            </a>
            <a href="{{ route('commandes') }}" class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-green-600 text-[22px]">task_alt</span>
                </div>
                <div>
                    <p class="font-display text-2xl font-bold text-primary-900">{{ $statutCounts['livree'] }}</p>
                    <p class="font-body text-xs text-gray-500">Livrées</p>
                </div>
            </a>
        </div>
    </div>

    {{-- graphique nombre de commandes --}}
    <div class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100 mb-8">
        <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
            <h2 class="font-display text-lg font-bold text-primary-900">Évolution du nombre de commandes</h2>
            <div class="inline-flex rounded-full bg-gray-100 p-1">
                <button type="button" id="btn-periode-jour" onclick="tafelyAfficherPeriode('jour')"
                        class="px-3.5 py-1.5 rounded-full text-xs font-body font-bold transition-all bg-white shadow-sm text-primary-700">
                    Par jour
                </button>
                <button type="button" id="btn-periode-mois" onclick="tafelyAfficherPeriode('mois')"
                        class="px-3.5 py-1.5 rounded-full text-xs font-body font-bold transition-all text-gray-500">
                    Par mois
                </button>
            </div>
        </div>
        <div class="relative" style="height: 240px;">
            <canvas id="tafely-graph-commandes"></canvas>
        </div>
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
                            <p class="font-body font-bold text-primary-900 text-sm">{{ $order['id'] }}</p>
                            <p class="font-body text-xs text-gray-500">{{ $order['date'] }} • {{ $order['items'] }} article(s)</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-display font-bold text-primary-900">{{ $order['total'] }}</p>
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold mt-1
                            {{ match($order['status']) {
                                'Livrée' => 'bg-green-100 text-green-700',
                                'En cours de livraison' => 'bg-primary-50 text-primary-700',
                                default => 'bg-accent-50 text-accent-700',
                            } }}">
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            if (typeof Chart === 'undefined') return;

            // ---- graphique nombre de commandes ----
            const labelsJour = {{ \Illuminate\Support\Js::from($graphJours->pluck('label')) }};
            const dataJour = {{ \Illuminate\Support\Js::from($graphJours->pluck('total')) }};
            const labelsMois = {{ \Illuminate\Support\Js::from($graphMois->pluck('label')) }};
            const dataMois = {{ \Illuminate\Support\Js::from($graphMois->pluck('total')) }};

            const ctxCommandes = document.getElementById('tafely-graph-commandes');
            const chartCommandes = new Chart(ctxCommandes, {
                type: 'line',
                data: {
                    labels: labelsJour,
                    datasets: [{
                        label: 'Commandes',
                        data: dataJour,
                        borderColor: '#1d4ed8',
                        backgroundColor: 'rgba(29, 78, 216, 0.08)',
                        fill: true, tension: 0.35, borderWidth: 2.5,
                        pointRadius: 3, pointBackgroundColor: '#1d4ed8',
                        pointBorderColor: '#ffffff', pointBorderWidth: 1.5,
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

            window.tafelyAfficherPeriode = function (periode) {
                const estJour = periode === 'jour';
                chartCommandes.data.labels = estJour ? labelsJour : labelsMois;
                chartCommandes.data.datasets[0].data = estJour ? dataJour : dataMois;
                chartCommandes.update();
                const actif = 'px-3.5 py-1.5 rounded-full text-xs font-body font-bold transition-all bg-white shadow-sm text-primary-700';
                const inactif = 'px-3.5 py-1.5 rounded-full text-xs font-body font-bold transition-all text-gray-500';
                document.getElementById('btn-periode-jour').className = estJour ? actif : inactif;
                document.getElementById('btn-periode-mois').className = ! estJour ? actif : inactif;
            };

            // ---- graphique chiffre d'affaires ----
            const labelsRevJour = {{ \Illuminate\Support\Js::from($graphRevenuJours->pluck('label')) }};
            const dataRevJour = {{ \Illuminate\Support\Js::from($graphRevenuJours->pluck('total')) }};
            const labelsRevMois = {{ \Illuminate\Support\Js::from($graphRevenuMois->pluck('label')) }};
            const dataRevMois = {{ \Illuminate\Support\Js::from($graphRevenuMois->pluck('total')) }};

            const ctxRevenu = document.getElementById('tafely-graph-revenu');
            const chartRevenu = new Chart(ctxRevenu, {
                type: 'line',
                data: {
                    labels: labelsRevJour,
                    datasets: [{
                        label: 'Chiffre d\'affaires (Ar)',
                        data: dataRevJour,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.08)',
                        fill: true, tension: 0.35, borderWidth: 2.5,
                        pointRadius: 3, pointBackgroundColor: '#ef4444',
                        pointBorderColor: '#ffffff', pointBorderWidth: 1.5,
                    }],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: (ctx) => new Intl.NumberFormat('fr-FR').format(ctx.parsed.y) + ' Ar' } },
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: (v) => new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(v) }, grid: { color: '#f3f4f6' } },
                        x: { grid: { display: false } },
                    },
                },
            });

            window.tafelyAfficherRevenu = function (periode) {
                const estJour = periode === 'jour';
                chartRevenu.data.labels = estJour ? labelsRevJour : labelsRevMois;
                chartRevenu.data.datasets[0].data = estJour ? dataRevJour : dataRevMois;
                chartRevenu.update();
                const actif = 'px-3.5 py-1.5 rounded-full text-xs font-body font-bold transition-all bg-white shadow-sm text-primary-700';
                const inactif = 'px-3.5 py-1.5 rounded-full text-xs font-body font-bold transition-all text-gray-500';
                document.getElementById('btn-revenu-jour').className = estJour ? actif : inactif;
                document.getElementById('btn-revenu-mois').className = ! estJour ? actif : inactif;
            };
        })();
    </script>

@endsection