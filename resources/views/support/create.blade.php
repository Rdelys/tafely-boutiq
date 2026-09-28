@extends('layouts.dashboard')

@section('title', 'Nouvelle demande — Support Tafely')

@section('page-content')
    <div class="mb-8 flex items-center gap-3">
        <a href="{{ route('support.index') }}" class="text-gray-400 hover:text-primary-700 transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Nouvelle demande</h1>
            <p class="font-body text-gray-500 mt-1">Décrivez votre question, l'équipe Tafely vous répondra ici.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('support.store') }}" class="max-w-xl bg-white rounded-2xl border border-gray-100 shadow-sm p-5 md:p-7 flex flex-col gap-5">
        @csrf

        <div>
            <label for="sujet" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Sujet</label>
            <input id="sujet" name="sujet" type="text" required maxlength="255" value="{{ old('sujet') }}"
                   placeholder="ex : Problème avec un paiement MVola"
                   class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('sujet') border-accent-400 @enderror">
            @error('sujet')
                <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="message" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Message</label>
            <textarea id="message" name="message" rows="6" required maxlength="4000"
                      placeholder="Décrivez votre demande avec le plus de détails possible..."
                      class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors resize-none @error('message') border-accent-400 @enderror">{{ old('message') }}</textarea>
            @error('message')
                <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="inline-flex items-center justify-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm py-3 rounded-xl shadow-sm transition-colors">
            <span class="material-symbols-outlined text-[18px]">send</span>
            Envoyer
        </button>
    </form>
@endsection