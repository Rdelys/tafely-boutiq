@extends('layouts.dashboard')

@section('title', 'Support — Tafely')

@section('page-content')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Support</h1>
            <p class="font-body text-gray-500 mt-1">Une question, un problème ? Contactez l'équipe Tafely.</p>
        </div>
        <a href="{{ route('support.create') }}"
           class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm px-5 py-2.5 rounded-full shadow-sm transition-colors">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Nouvelle demande
        </a>
    </div>

    @if (session('status'))
        <div class="mb-6 flex items-center gap-3 bg-primary-50 border border-primary-100 text-primary-700 rounded-xl px-4 py-3">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span class="font-body text-sm font-semibold">{{ session('status') }}</span>
        </div>
    @endif

    @if ($tickets->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 md:p-20 flex flex-col items-center text-center">
            <div class="h-16 w-16 rounded-full bg-primary-50 flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-primary-700 text-3xl">support_agent</span>
            </div>
            <h2 class="font-display text-xl font-bold text-primary-900 mb-2">Aucune demande pour l'instant</h2>
            <p class="font-body text-sm text-gray-500 max-w-sm mb-6">Envoyez un message à l'équipe Tafely si vous avez besoin d'aide.</p>
            <a href="{{ route('support.create') }}"
               class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm px-6 py-3 rounded-xl shadow-sm transition-colors">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Nouvelle demande
            </a>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-100 overflow-hidden">
            @foreach ($tickets as $t)
                <a href="{{ route('support.show', $t) }}" class="p-4 md:p-5 flex items-center justify-between gap-4 hover:bg-gray-50 transition-colors {{ $t->nouveau_pour_marchand ? 'bg-primary-50/40' : '' }}">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-body font-bold text-sm text-primary-900 truncate">{{ $t->sujet }}</p>
                            @if ($t->nouveau_pour_marchand)
                                <span class="h-2 w-2 rounded-full bg-accent-500 shrink-0"></span>
                            @endif
                        </div>
                        <p class="font-body text-xs text-gray-400 mt-1">{{ $t->messages_count }} message(s) · dernière activité {{ $t->updated_at->diffForHumans() }}</p>
                    </div>
                    <span @class([
                        'shrink-0 inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap',
                        'bg-green-50 text-green-700' => $t->estOuvert(),
                        'bg-gray-100 text-gray-500' => ! $t->estOuvert(),
                    ])>{{ $t->estOuvert() ? 'Ouvert' : 'Clôturé' }}</span>
                </a>
            @endforeach
        </div>
    @endif
@endsection