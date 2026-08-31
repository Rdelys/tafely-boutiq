@extends('layouts.dashboard')

@section('title', 'Modifier '.$produit->nom.' — Tafely')

@section('page-content')

    <div class="mb-8 flex items-center gap-3">
        <a href="{{ route('produits') }}" class="text-gray-400 hover:text-primary-700 transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Modifier le produit</h1>
            <p class="font-body text-gray-500 mt-1">{{ $produit->nom }}</p>
        </div>
    </div>

    <div class="max-w-2xl flex flex-col gap-6">

        <form id="produit-form" method="POST" action="{{ route('produits.update', $produit) }}" enctype="multipart/form-data" class="flex flex-col gap-6">
            @csrf
            @method('PUT')

            @include('produits._form')
        </form>

        <div class="flex justify-between items-center pb-4">
            <form method="POST" action="{{ route('produits.destroy', $produit) }}"
                  onsubmit="return confirm('Supprimer définitivement ce produit ?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-2 text-accent-600 hover:text-accent-700 font-body font-semibold text-sm px-4 py-2.5 rounded-xl hover:bg-accent-50 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                    Supprimer
                </button>
            </form>

            <div class="flex gap-3">
                <a href="{{ route('produits') }}"
                   class="inline-flex items-center gap-2 bg-white text-gray-600 border border-gray-200 font-body font-bold text-sm px-6 py-3 rounded-xl hover:bg-gray-50 transition-colors">
                    Annuler
                </a>
                <button type="submit" form="produit-form"
                        class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm px-6 py-3 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-[0.98]">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Enregistrer
                </button>
            </div>
        </div>
    </div>

@endsection