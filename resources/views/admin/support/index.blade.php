@extends('layouts.admin')

@section('title', 'Support — Admin Tafely')

@section('page-content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Support</h1>
            <p class="font-body text-gray-500 mt-1">{{ $compteurs['tous'] }} ticket{{ $compteurs['tous'] > 1 ? 's' : '' }} au total.</p>
        </div>
        <a href="{{ route('admin.communication.create') }}"
           class="inline-flex items-center justify-center gap-2 bg-primary-800 hover:bg-primary-900 text-white font-body font-bold text-sm px-5 py-2.5 rounded-xl transition-colors shrink-0">
            <span class="material-symbols-outlined text-[18px]">campaign</span>
            Diffuser un message
        </a>
    </div>

    <div class="flex flex-wrap gap-2 mb-6">
        @foreach ([
            ['cle' => 'ouvert', 'label' => 'Ouverts'],
            ['cle' => 'ferme', 'label' => 'Clôturés'],
            ['cle' => 'tous', 'label' => 'Tous'],
        ] as $onglet)
            <a href="{{ route('admin.support.index', ['statut' => $onglet['cle']]) }}"
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

    @if ($tickets->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 md:p-20 flex flex-col items-center text-center">
            <span class="material-symbols-outlined text-primary-700 text-3xl mb-3">support_agent</span>
            <p class="font-body text-sm text-gray-500">Aucun ticket ne correspond à ce filtre.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-100 overflow-hidden">
            @foreach ($tickets as $t)
                <a href="{{ route('admin.support.show', $t) }}" class="p-4 md:p-5 flex items-center justify-between gap-4 hover:bg-gray-50 transition-colors {{ $t->nouveau_pour_admin ? 'bg-primary-50/40' : '' }}">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-body font-bold text-sm text-primary-900 truncate">{{ $t->sujet }}</p>
                            @if ($t->nouveau_pour_admin)
                                <span class="h-2 w-2 rounded-full bg-accent-500 shrink-0"></span>
                            @endif
                        </div>
                        <p class="font-body text-xs text-gray-400 mt-1">{{ $t->user?->nom_boutique ?? $t->user?->email ?? '—' }} · {{ $t->messages_count }} message(s) · {{ $t->updated_at->diffForHumans() }}</p>
                    </div>
                    <span @class([
                        'shrink-0 inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap',
                        'bg-green-50 text-green-700' => $t->estOuvert(),
                        'bg-gray-100 text-gray-500' => ! $t->estOuvert(),
                    ])>{{ $t->estOuvert() ? 'Ouvert' : 'Clôturé' }}</span>
                </a>
            @endforeach
        </div>

        {{ $tickets->links('pagination.tafely') }}
    @endif
@endsection