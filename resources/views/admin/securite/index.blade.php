@extends('layouts.admin')

@section('title', 'Sécurité — Admin Tafely')

@section('page-content')
    <div class="mb-6">
        <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Sécurité</h1>
        <p class="font-body text-gray-500 mt-1">Journal des actions administrateur et surveillance des tentatives de connexion.</p>
    </div>

    {{-- ============ TENTATIVES OTP SUSPECTES ============ --}}
    @if ($otpSuspects->isNotEmpty())
        <div class="mb-6 bg-accent-50 border border-accent-100 rounded-2xl p-5">
            <div class="flex items-start gap-3 mb-3">
                <span class="material-symbols-outlined text-accent-600 text-[22px]">gpp_maybe</span>
                <div>
                    <h2 class="font-display font-bold text-accent-800">{{ $otpSuspects->count() }} adresse{{ $otpSuspects->count() > 1 ? 's' : '' }} avec des demandes de code répétées</h2>
                    <p class="font-body text-xs text-accent-700 mt-0.5">5 demandes de code ou plus en moins d'une heure pour la même adresse.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr>
                            <th class="py-2 pr-4 font-body text-xs font-bold text-accent-700 uppercase tracking-wide">Email</th>
                            <th class="py-2 pr-4 font-body text-xs font-bold text-accent-700 uppercase tracking-wide">Contexte</th>
                            <th class="py-2 pr-4 font-body text-xs font-bold text-accent-700 uppercase tracking-wide">Tentatives (1h)</th>
                            <th class="py-2 font-body text-xs font-bold text-accent-700 uppercase tracking-wide">Dernière tentative</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-accent-100">
                        @foreach ($otpSuspects as $s)
                            <tr>
                                <td class="py-2 pr-4 font-body text-sm text-gray-800">{{ $s->email }}</td>
                                <td class="py-2 pr-4">
                                    <span class="inline-flex items-center bg-white text-accent-700 text-xs font-semibold px-2.5 py-1 rounded-full">{{ ucfirst($s->contexte) }}</span>
                                </td>
                                <td class="py-2 pr-4 font-display font-bold text-sm text-accent-800">{{ $s->nombre }}</td>
                                <td class="py-2 font-body text-sm text-gray-600">{{ \Carbon\Carbon::parse($s->dernier)->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ============ FILTRES JOURNAL D'AUDIT ============ --}}
    <form method="GET" action="{{ route('admin.securite.index') }}" class="flex flex-col sm:flex-row gap-3 mb-4">
        <select name="admin" onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl bg-white px-3.5 py-2.5 font-body text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-600">
            <option value="">Tous les admins</option>
            @foreach ($admins as $a)
                <option value="{{ $a->id }}" @selected($adminFiltre == $a->id)>{{ $a->email }}</option>
            @endforeach
        </select>
        <select name="action" onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl bg-white px-3.5 py-2.5 font-body text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-600">
            <option value="toutes" @selected($actionFiltre === 'toutes')>Toutes les actions</option>
            @foreach ($actions as $a)
                <option value="{{ $a }}" @selected($actionFiltre === $a)>{{ ucfirst(str_replace('_', ' ', $a)) }}</option>
            @endforeach
        </select>
    </form>

    {{-- ============ TABLEAU JOURNAL ============ --}}
    @if ($logs->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 md:p-20 flex flex-col items-center text-center">
            <span class="material-symbols-outlined text-primary-700 text-3xl mb-3">history</span>
            <p class="font-body text-sm text-gray-500">Aucune action enregistrée pour ces critères.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Admin</th>
                            <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Action</th>
                            <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Cible</th>
                            <th class="hidden md:table-cell px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Détails</th>
                            <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($logs as $log)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 font-body text-sm text-gray-700">{{ $log->admin?->email ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center bg-primary-50 text-primary-700 text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">{{ $log->libelle() }}</span>
                                </td>
                                <td class="px-4 py-3 font-body text-sm">
                                    @if ($log->user)
                                        <a href="{{ route('admin.marchands.show', $log->user) }}" class="text-primary-700 hover:text-accent-600 transition-colors font-semibold">{{ $log->user->nom_boutique ?: $log->user->email }}</a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                    @if ($log->produit)
                                        <span class="text-gray-400"> · </span>
                                        <span class="text-gray-600">{{ $log->produit->nom }}</span>
                                    @endif
                                </td>
                                <td class="hidden md:table-cell px-4 py-3 font-body text-xs text-gray-500 max-w-[240px] truncate">
                                    @if ($log->details)
                                        {{ collect($log->details)->map(fn ($v, $k) => "$k: $v")->implode(' · ') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-body text-sm text-gray-500 text-right whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $logs->links('pagination.tafely') }}
    @endif
@endsection