{{-- Audience : visites du site + connexions des marchands --}}
@php
    $v = $audience['visites'];
    $c = $audience['connexions'];
@endphp

<div class="mb-2 flex items-center gap-2">
    <span class="material-symbols-outlined text-primary-700 text-[22px]">monitoring</span>
    <h2 class="font-display text-lg font-bold text-primary-900">Audience du site</h2>
</div>

@if ($v['total'] === 0)
    <div class="mb-4 flex items-center gap-3 bg-gray-100 border border-gray-200 text-gray-600 rounded-xl px-4 py-3">
        <span class="material-symbols-outlined text-[20px]">hourglass_empty</span>
        <span class="font-body text-sm font-semibold">Aucune visite enregistrée depuis l'ouverture du site.</span>
    </div>
@else
    <div class="mb-4 flex items-center gap-3 bg-green-50 border border-green-100 text-green-700 rounded-xl px-4 py-3">
        <span class="material-symbols-outlined text-[20px]">check_circle</span>
        <span class="font-body text-sm font-semibold">
            Première visite le {{ $v['premiere']->format('d/m/Y à H:i') }} — dernière le {{ $v['derniere']->format('d/m/Y à H:i') }}.
        </span>
    </div>
@endif

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
        <span class="font-body text-sm text-gray-500">Visites aujourd'hui</span>
        <p class="font-display text-3xl font-bold text-primary-900 mt-2">{{ $v['aujourdhui'] }}</p>
        <p class="font-body text-xs text-gray-400 mt-1">{{ $v['uniques_aujourdhui'] }} visiteur{{ $v['uniques_aujourdhui'] > 1 ? 's' : '' }} unique{{ $v['uniques_aujourdhui'] > 1 ? 's' : '' }}</p>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
        <span class="font-body text-sm text-gray-500">Visiteurs uniques (7 j)</span>
        <p class="font-display text-3xl font-bold text-primary-900 mt-2">{{ $v['uniques_7j'] }}</p>
        <p class="font-body text-xs text-gray-400 mt-1">{{ $v['uniques_total'] }} depuis l'ouverture</p>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
        <span class="font-body text-sm text-gray-500">Visites (total)</span>
        <p class="font-display text-3xl font-bold text-primary-900 mt-2">{{ $v['total'] }}</p>
        <p class="font-body text-xs text-gray-400 mt-1">pages vues par des visiteurs</p>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
        <span class="font-body text-sm text-gray-500">Connexions marchands</span>
        <p class="font-display text-3xl font-bold text-primary-900 mt-2">{{ $c['aujourdhui'] }}</p>
        <p class="font-body text-xs text-gray-400 mt-1">aujourd'hui · {{ $c['total'] }} au total</p>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
        <h3 class="font-display text-base font-bold text-primary-900 mb-4">Visites et visiteurs (14 derniers jours)</h3>
        <div class="relative" style="height: 220px;"><canvas id="admin-graph-visites"></canvas></div>
    </div>
    <div class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
        <h3 class="font-display text-base font-bold text-primary-900 mb-1">Connexions des marchands (14 derniers jours)</h3>
        <p class="font-body text-xs text-gray-400 mb-4">{{ $c['sept_jours'] }} connexion{{ $c['sept_jours'] > 1 ? 's' : '' }} sur 7 jours, par {{ $c['marchands_7j'] }} marchand{{ $c['marchands_7j'] > 1 ? 's' : '' }} différent{{ $c['marchands_7j'] > 1 ? 's' : '' }}.</p>
        <div class="relative" style="height: 200px;"><canvas id="admin-graph-connexions"></canvas></div>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
        <h3 class="font-display text-base font-bold text-primary-900 mb-4">Pages les plus visitées</h3>
        @forelse ($v['pages'] as $page)
            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <span class="font-body text-sm text-gray-700 truncate pr-4">{{ $page->chemin }}</span>
                <span class="font-display font-bold text-sm text-primary-800">{{ $page->nombre }}</span>
            </div>
        @empty
            <p class="font-body text-sm text-gray-400">Pas encore de données.</p>
        @endforelse
    </div>
    <div class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
        <h3 class="font-display text-base font-bold text-primary-900 mb-4">D'où viennent les visiteurs ?</h3>
        @forelse ($v['sources'] as $source)
            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <span class="font-body text-sm text-gray-700">{{ $source->source }}</span>
                <span class="font-display font-bold text-sm text-primary-800">{{ $source->nombre }}</span>
            </div>
        @empty
            <p class="font-body text-sm text-gray-400">Pas encore de données.</p>
        @endforelse
    </div>
</div>

<script>
    // Attend que Chart.js (chargé en bas de la page) soit disponible.
    window.addEventListener('load', function () {
        if (typeof Chart === 'undefined') return;

        new Chart(document.getElementById('admin-graph-visites'), {
            type: 'line',
            data: {
                labels: {{ \Illuminate\Support\Js::from($v['graph']->pluck('label')) }},
                datasets: [
                    { label: 'Visites', data: {{ \Illuminate\Support\Js::from($v['graph']->pluck('visites')) }},
                      borderColor: '#1d4ed8', backgroundColor: 'rgba(29,78,216,.08)', fill: true, tension: .35, borderWidth: 2.5, pointRadius: 3 },
                    { label: 'Visiteurs uniques', data: {{ \Illuminate\Support\Js::from($v['graph']->pluck('visiteurs')) }},
                      borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,.08)', fill: true, tension: .35, borderWidth: 2.5, pointRadius: 3 },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { color: '#f3f4f6' } }, x: { grid: { display: false } } },
            },
        });

        new Chart(document.getElementById('admin-graph-connexions'), {
            type: 'bar',
            data: {
                labels: {{ \Illuminate\Support\Js::from($c['graph']->pluck('label')) }},
                datasets: [{ label: 'Connexions', data: {{ \Illuminate\Support\Js::from($c['graph']->pluck('total')) }},
                             backgroundColor: '#1d4ed8', borderRadius: 6, maxBarThickness: 24 }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { color: '#f3f4f6' } }, x: { grid: { display: false } } },
            },
        });
    });
</script>