@extends('layouts.admin')

@section('title', 'Dashboard — Admin Tafely')

@section('page-content')
    <div class="mb-8">
        <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Dashboard</h1>
        <p class="font-body text-gray-500 mt-1">Vue d'ensemble de la plateforme Tafely, toutes boutiques confondues.</p>
    </div>

    {{-- ============ BOUTIQUES ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <span class="font-body text-sm text-gray-500">Boutiques (total)</span>
                <div class="bg-primary-50 p-1.5 rounded-full">
                    <span class="material-symbols-outlined text-primary-700 text-[20px]">storefront</span>
                </div>
            </div>
            <span class="font-display text-4xl font-bold text-primary-900">{{ $stats['boutiques_total'] }}</span>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <span class="font-body text-sm text-gray-500">Actives (payant)</span>
                <div class="bg-green-50 p-1.5 rounded-full">
                    <span class="material-symbols-outlined text-green-600 text-[20px]">workspace_premium</span>
                </div>
            </div>
            <span class="font-display text-4xl font-bold text-primary-900">{{ $stats['boutiques_actives'] }}</span>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <span class="font-body text-sm text-gray-500">En essai</span>
                <div class="bg-primary-50 p-1.5 rounded-full">
                    <span class="material-symbols-outlined text-primary-700 text-[20px]">hourglass_top</span>
                </div>
            </div>
            <span class="font-display text-4xl font-bold text-primary-900">{{ $stats['boutiques_essai'] }}</span>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <span class="font-body text-sm text-gray-500">Essai expiré</span>
                <div class="bg-accent-50 p-1.5 rounded-full">
                    <span class="material-symbols-outlined text-accent-600 text-[20px]">error</span>
                </div>
            </div>
            <span class="font-display text-4xl font-bold text-primary-900">{{ $stats['boutiques_essai_expire'] }}</span>
        </div>
    </div>

    {{-- ============ REVENU & COMMANDES ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-8">
        <div class="bg-gray-900 rounded-xl p-5 text-white">
            <p class="font-body text-xs font-semibold text-gray-300 uppercase tracking-wide mb-1">Revenu total (tout temps)</p>
            <p class="font-display text-2xl font-bold">{{ number_format($stats['revenu_total'], 0, ',', ' ') }} Ar</p>
        </div>

        <div class="bg-primary-50 rounded-xl p-5">
            <p class="font-body text-xs font-semibold text-primary-700 uppercase tracking-wide mb-1">Commandes aujourd'hui</p>
            <p class="font-display text-2xl font-bold text-primary-900">{{ $stats['commandes_jour'] }}</p>
        </div>

        <div class="bg-accent-50 rounded-xl p-5">
            <p class="font-body text-xs font-semibold text-accent-700 uppercase tracking-wide mb-1">Commandes ce mois-ci</p>
            <p class="font-display text-2xl font-bold text-primary-900">{{ $stats['commandes_mois'] }}</p>
        </div>

        <div class="bg-green-50 rounded-xl p-5">
            <p class="font-body text-xs font-semibold text-green-700 uppercase tracking-wide mb-1">Conversion essai → payant (ce mois)</p>
            <p class="font-display text-2xl font-bold text-primary-900">{{ $tauxConversion }} %</p>
        </div>
    </div>

    {{-- ============ MRR ============ --}}
    <div class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100 mb-6">
        <h2 class="font-display text-lg font-bold text-primary-900 mb-4">MRR — revenus d'abonnements encaissés (12 derniers mois)</h2>
        <div class="relative" style="height: 240px;">
            <canvas id="admin-graph-mrr"></canvas>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        {{-- ============ CHURN ============ --}}
        <div class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
            <h2 class="font-display text-lg font-bold text-primary-900 mb-1">Taux de churn mensuel</h2>
            <p class="font-body text-xs text-gray-400 mb-4">% des abonnements arrivés à expiration dans le mois qui n'ont pas été renouvelés.</p>
            <div class="relative" style="height: 220px;">
                <canvas id="admin-graph-churn"></canvas>
            </div>
        </div>

        {{-- ============ RÉPARTITION REVENUS ============ --}}
        <div class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
            <h2 class="font-display text-lg font-bold text-primary-900 mb-4">Répartition des revenus</h2>
            <div class="relative flex items-center justify-center" style="height: 220px;">
                <canvas id="admin-graph-repartition"></canvas>
            </div>
            <div class="flex justify-center gap-6 mt-4">
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-primary-700"></span>
                    <span class="font-body text-xs text-gray-600">Abonnements — {{ number_format($repartitionRevenus['abonnements'], 0, ',', ' ') }} Ar</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-accent-500"></span>
                    <span class="font-body text-xs text-gray-600">Packs produits — {{ number_format($repartitionRevenus['packs'], 0, ',', ' ') }} Ar</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ NOUVELLES BOUTIQUES ============ --}}
    <div class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100 mb-8">
        <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
            <h2 class="font-display text-lg font-bold text-primary-900">Nouvelles boutiques</h2>
            <div class="inline-flex rounded-full bg-gray-100 p-1">
                <button type="button" id="btn-nouvelles-jour" onclick="tafelyAdminAfficherNouvelles('jour')"
                        class="px-3.5 py-1.5 rounded-full text-xs font-body font-bold transition-all bg-white shadow-sm text-primary-700">
                    Par jour
                </button>
                <button type="button" id="btn-nouvelles-semaine" onclick="tafelyAdminAfficherNouvelles('semaine')"
                        class="px-3.5 py-1.5 rounded-full text-xs font-body font-bold transition-all text-gray-500">
                    Par semaine
                </button>
            </div>
        </div>
        <div class="relative" style="height: 240px;">
            <canvas id="admin-graph-nouvelles"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            if (typeof Chart === 'undefined') return;

            // ---- MRR ----
            new Chart(document.getElementById('admin-graph-mrr'), {
                type: 'line',
                data: {
                    labels: {{ \Illuminate\Support\Js::from($graphMrr->pluck('label')) }},
                    datasets: [{
                        label: 'MRR (Ar)',
                        data: {{ \Illuminate\Support\Js::from($graphMrr->pluck('total')) }},
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

            // ---- Churn ----
            new Chart(document.getElementById('admin-graph-churn'), {
                type: 'line',
                data: {
                    labels: {{ \Illuminate\Support\Js::from($graphChurn->pluck('label')) }},
                    datasets: [{
                        label: 'Churn (%)',
                        data: {{ \Illuminate\Support\Js::from($graphChurn->pluck('taux')) }},
                        borderColor: '#1d4ed8',
                        backgroundColor: 'rgba(29, 78, 216, 0.08)',
                        fill: true, tension: 0.35, borderWidth: 2.5,
                        pointRadius: 3, pointBackgroundColor: '#1d4ed8',
                        pointBorderColor: '#ffffff', pointBorderWidth: 1.5,
                    }],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: (ctx) => ctx.parsed.y + ' %' } },
                    },
                    scales: {
                        y: { beginAtZero: true, max: 100, ticks: { callback: (v) => v + '%' }, grid: { color: '#f3f4f6' } },
                        x: { grid: { display: false } },
                    },
                },
            });

            // ---- Répartition des revenus (donut) ----
            new Chart(document.getElementById('admin-graph-repartition'), {
                type: 'doughnut',
                data: {
                    labels: ['Abonnements', 'Packs produits'],
                    datasets: [{
                        data: [{{ $repartitionRevenus['abonnements'] }}, {{ $repartitionRevenus['packs'] }}],
                        backgroundColor: ['#1d4ed8', '#ef4444'],
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

            // ---- Nouvelles boutiques ----
            const labelsJour = {{ \Illuminate\Support\Js::from($nouvellesJour->pluck('label')) }};
            const dataJour = {{ \Illuminate\Support\Js::from($nouvellesJour->pluck('total')) }};
            const labelsSemaine = {{ \Illuminate\Support\Js::from($nouvellesSemaine->pluck('label')) }};
            const dataSemaine = {{ \Illuminate\Support\Js::from($nouvellesSemaine->pluck('total')) }};

            const chartNouvelles = new Chart(document.getElementById('admin-graph-nouvelles'), {
                type: 'bar',
                data: {
                    labels: labelsJour,
                    datasets: [{
                        label: 'Nouvelles boutiques',
                        data: dataJour,
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

            window.tafelyAdminAfficherNouvelles = function (periode) {
                const estJour = periode === 'jour';
                chartNouvelles.data.labels = estJour ? labelsJour : labelsSemaine;
                chartNouvelles.data.datasets[0].data = estJour ? dataJour : dataSemaine;
                chartNouvelles.update();
                const actif = 'px-3.5 py-1.5 rounded-full text-xs font-body font-bold transition-all bg-white shadow-sm text-primary-700';
                const inactif = 'px-3.5 py-1.5 rounded-full text-xs font-body font-bold transition-all text-gray-500';
                document.getElementById('btn-nouvelles-jour').className = estJour ? actif : inactif;
                document.getElementById('btn-nouvelles-semaine').className = ! estJour ? actif : inactif;
            };
        })();
    </script>
@endsection