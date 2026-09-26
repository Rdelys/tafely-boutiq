@extends('layouts.dashboard')

@section('title', 'Notifications — Tafely')

@section('page-content')
    <div class="mb-8">
        <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Notifications</h1>
        <p class="font-body text-gray-500 mt-1">Les messages de l'équipe Tafely concernant votre boutique.</p>
    </div>

    @if ($notifications->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 md:p-20 flex flex-col items-center text-center">
            <div class="h-16 w-16 rounded-full bg-primary-50 flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-primary-700 text-3xl">notifications</span>
            </div>
            <h2 class="font-display text-xl font-bold text-primary-900 mb-2">Aucune notification</h2>
            <p class="font-body text-sm text-gray-500 max-w-sm">Vous serez averti ici en cas d'action de l'équipe Tafely sur votre compte ou vos produits.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-100 overflow-hidden">
            @foreach ($notifications as $n)
                <div class="p-4 md:p-5 flex items-start gap-4 {{ ! $n->estLue() ? 'bg-primary-50/40' : '' }}">
                    <div @class([
                        'h-10 w-10 rounded-full flex items-center justify-center shrink-0',
                        'bg-accent-50' => in_array($n->type, ['produit_bloque', 'produit_supprime']),
                        'bg-primary-50' => ! in_array($n->type, ['produit_bloque', 'produit_supprime']),
                    ])>
                        <span @class([
                            'material-symbols-outlined text-[20px]',
                            'text-accent-600' => in_array($n->type, ['produit_bloque', 'produit_supprime']),
                            'text-primary-700' => ! in_array($n->type, ['produit_bloque', 'produit_supprime']),
                        ])>{{ $n->icone() }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-body font-bold text-sm text-primary-900">{{ $n->titre }}</p>
                            @if (! $n->estLue())
                                <span class="h-2 w-2 rounded-full bg-accent-500 shrink-0"></span>
                            @endif
                        </div>
                        <p class="font-body text-sm text-gray-600 mt-1">{{ $n->message }}</p>
                        <p class="font-body text-xs text-gray-400 mt-1.5">{{ $n->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{ $notifications->links('pagination.tafely') }}
    @endif
@endsection