@extends('layouts.dashboard')

@section('title', 'Paiement — Tafely')

@section('page-content')

    <div class="max-w-lg mx-auto text-center py-16">
        @if ($paiement->statut === 'paye')
            <span class="material-symbols-outlined text-6xl text-primary-700" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            <h1 class="font-display text-2xl font-bold text-primary-900 mt-4">Paiement confirmé !</h1>
            <p class="font-body text-gray-500 mt-2">Merci, votre paiement de {{ $paiement->montantFormate() }} a bien été reçu.</p>
        @elseif ($paiement->statut === 'echoue')
            <span class="material-symbols-outlined text-6xl text-accent-500">error</span>
            <h1 class="font-display text-2xl font-bold text-primary-900 mt-4">Paiement non abouti</h1>
            <p class="font-body text-gray-500 mt-2">Le paiement n'a pas pu être confirmé. Vous pouvez réessayer depuis la page Abonnement.</p>
        @else
            <div x-data x-init="setTimeout(() => window.location.reload(), 4000)">
                <span class="material-symbols-outlined text-6xl text-accent-500 animate-spin" style="animation-duration: 2s;">progress_activity</span>
                <h1 class="font-display text-2xl font-bold text-primary-900 mt-4">Confirmation en cours...</h1>
                <p class="font-body text-gray-500 mt-2">Nous attendons la confirmation de votre opérateur. Cette page se mettra à jour automatiquement.</p>
            </div>
        @endif

        <a href="{{ route('abonnement') }}" class="inline-flex items-center gap-2 mt-8 bg-primary-800 hover:bg-primary-900 text-white font-body font-bold text-sm px-6 py-3 rounded-xl transition-colors">
            Retour à l'abonnement
        </a>
    </div>

@endsection