@extends('layouts.dashboard')

@section('title', 'Vente en boutique — Tafely')

@section('page-content')
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 md:p-20 flex flex-col items-center text-center">
        <div class="h-16 w-16 rounded-full bg-accent-50 flex items-center justify-center mb-5">
            <span class="material-symbols-outlined text-accent-600 text-3xl">gpp_bad</span>
        </div>
        <h1 class="font-display text-xl font-bold text-primary-900 mb-2">Ventes en boutique bloquées</h1>
        <p class="font-body text-sm text-gray-500 max-w-sm mb-6">
            Votre boutique est suspendue. Vous ne pouvez pas enregistrer de vente pour le moment.
            @if (auth()->user()->suspendu_raison)
                <br><span class="font-semibold text-gray-700">Raison : {{ auth()->user()->suspendu_raison }}</span>
            @endif
        </p>
        <a href="{{ route('support.create') }}"
           class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm px-6 py-3 rounded-xl shadow-sm transition-colors">
            <span class="material-symbols-outlined text-[18px]">support_agent</span>
            Contacter le support
        </a>
    </div>
@endsection