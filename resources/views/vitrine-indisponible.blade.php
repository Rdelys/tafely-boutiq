@extends('layouts.app')

@section('title', 'Boutique indisponible — Tafely')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-5">
        <div class="max-w-md text-center">
            <span class="material-symbols-outlined text-gray-300 text-6xl">storefront</span>
            <h1 class="font-display text-2xl font-bold text-gray-900 mt-4">Boutique temporairement indisponible</h1>
            <p class="font-body text-sm text-gray-500 mt-2">
                {{ $marchand->nom_boutique ?: 'Cette boutique' }} n'est pas accessible pour le moment. Merci de réessayer plus tard.
            </p>
        </div>
    </div>
@endsection