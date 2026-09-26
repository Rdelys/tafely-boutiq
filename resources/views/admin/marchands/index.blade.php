@extends('layouts.admin')

@section('title', 'Marchands — Admin Tafely')

@section('page-content')
    <div class="mb-6">
        <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Marchands</h1>
        <p class="font-body text-gray-500 mt-1">{{ $compteurs['tous'] }} boutique{{ $compteurs['tous'] > 1 ? 's' : '' }} inscrite{{ $compteurs['tous'] > 1 ? 's' : '' }} au total.</p>
    </div>

    {{-- filtre + recherche --}}
    <form method="GET" action="{{ route('admin.marchands.index') }}" class="flex flex-col sm:flex-row gap-3 mb-4">
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[20px]">search</span>
            <input type="search" name="q" value="{{ $recherche }}" placeholder="Nom, boutique, email..."
                   class="w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl bg-white text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-body text-sm">
        </div>
        <button type="submit" class="inline-flex items-center justify-center gap-2 bg-primary-800 hover:bg-primary-900 text-white font-body font-bold text-sm px-5 py-2.5 rounded-xl transition-colors">
            Rechercher
        </button>
    </form>

    <div class="flex flex-wrap gap-2 mb-6">
        @foreach ([
            ['cle' => 'tous', 'label' => 'Tous'],
            ['cle' => 'actif', 'label' => 'Actifs (payant)'],
            ['cle' => 'essai', 'label' => 'En essai'],
            ['cle' => 'expire', 'label' => 'Essai expiré'],
        ] as $onglet)
            <a href="{{ route('admin.marchands.index', array_filter(['statut' => $onglet['cle'] === 'tous' ? null : $onglet['cle'], 'q' => $recherche ?: null])) }}"
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

    @if ($marchands->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 md:p-20 flex flex-col items-center text-center">
            <span class="material-symbols-outlined text-primary-700 text-3xl mb-3">storefront</span>
            <p class="font-body text-sm text-gray-500">Aucun marchand ne correspond à ces critères.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Boutique</th>
                            <th class="hidden md:table-cell px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Email</th>
                            <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Statut</th>
                            <th class="hidden sm:table-cell px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Produits</th>
                            <th class="hidden sm:table-cell px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Commandes</th>
                            <th class="hidden md:table-cell px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide">Inscrit le</th>
                            <th class="px-4 py-3 font-body text-xs font-bold text-gray-500 uppercase tracking-wide text-right">Fiche</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($marchands as $m)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-lg bg-gray-100 overflow-hidden shrink-0 flex items-center justify-center">
                                            @if ($m->logo)
                                                <img src="{{ asset('storage/'.$m->logo) }}" alt="" class="h-full w-full object-cover">
                                            @else
                                                <span class="material-symbols-outlined text-gray-300 text-lg">storefront</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-body font-semibold text-sm text-primary-900 truncate max-w-[160px]">{{ $m->nom_boutique ?: 'Sans nom' }}</p>
                                            <p class="font-body text-xs text-gray-400 md:hidden truncate max-w-[160px]">{{ $m->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell px-4 py-3 font-body text-sm text-gray-600">{{ $m->email }}</td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap',
                                        'bg-green-50 text-green-700' => $m->abonnementActif(),
                                        'bg-accent-50 text-accent-700' => ! $m->abonnementActif() && $m->essaiExpire(),
                                        'bg-gray-100 text-gray-600' => ! $m->abonnementActif() && ! $m->essaiExpire(),
                                    ])>{{ $m->statusLabel() }}</span>
                                </td>
                                <td class="hidden sm:table-cell px-4 py-3 font-body text-sm text-gray-600">{{ $m->produits_count }}</td>
                                <td class="hidden sm:table-cell px-4 py-3 font-body text-sm text-gray-600">{{ $m->commandes_count }}</td>
                                <td class="hidden md:table-cell px-4 py-3 font-body text-sm text-gray-500">{{ $m->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.marchands.show', $m) }}"
                                       class="inline-flex items-center gap-1 text-xs font-body font-semibold text-primary-700 hover:text-accent-600 transition-colors">
                                        Voir <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $marchands->links('pagination.tafely') }}
    @endif
@endsection