@extends('layouts.dashboard')

@section('title', 'Ajouter un produit — Tafely')

@section('page-content')

    <div class="mb-8 flex items-center gap-3">
        <a href="{{ route('produits') }}" class="text-gray-400 hover:text-primary-700 transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Ajouter un produit</h1>
            <p class="font-body text-gray-500 mt-1">Renseignez les informations de votre nouveau produit.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('produits.store') }}" enctype="multipart/form-data" class="max-w-2xl flex flex-col gap-6">
        @csrf

        @include('produits._form')

        <div class="flex justify-end gap-3 pb-4">
            <a href="{{ route('produits') }}"
               class="inline-flex items-center gap-2 bg-white text-gray-600 border border-gray-200 font-body font-bold text-sm px-6 py-3 rounded-xl hover:bg-gray-50 transition-colors">
                Annuler
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm px-6 py-3 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-[0.98]">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Ajouter le produit
            </button>
        </div>
    </form>

@endsection