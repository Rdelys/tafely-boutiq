@extends('layouts.admin')

@section('title', 'Communication — Admin Tafely')

@section('page-content')
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.support.index') }}" class="text-gray-400 hover:text-primary-700 transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Diffuser un message</h1>
            <p class="font-body text-gray-500 mt-1">Envoie une notification dans l'espace "Notifications" des marchands ciblés.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-6 flex items-center gap-3 bg-primary-50 border border-primary-100 text-primary-700 rounded-xl px-4 py-3">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span class="font-body text-sm font-semibold">{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.communication.store') }}" class="max-w-xl bg-white rounded-2xl border border-gray-100 shadow-sm p-5 md:p-7 flex flex-col gap-5">
        @csrf

        <div>
            <label class="block font-body text-sm font-semibold text-primary-900 mb-2">Destinataires</label>
            <div class="grid grid-cols-2 gap-2">
                @foreach ([
                    ['valeur' => 'tous', 'label' => 'Tous les marchands', 'compte' => $compteurs['tous']],
                    ['valeur' => 'actif', 'label' => 'Actifs (payant)', 'compte' => $compteurs['actif']],
                    ['valeur' => 'essai', 'label' => 'En essai', 'compte' => $compteurs['essai']],
                    ['valeur' => 'expire', 'label' => 'Essai expiré', 'compte' => $compteurs['expire']],
                ] as $option)
                    <label class="flex items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-colors has-[:checked]:border-primary-600 has-[:checked]:bg-primary-50 border-gray-200">
                        <input type="radio" name="cible" value="{{ $option['valeur'] }}" {{ old('cible', 'tous') === $option['valeur'] ? 'checked' : '' }} class="accent-primary-700">
                        <span class="font-body text-sm text-gray-700">{{ $option['label'] }} <span class="text-gray-400">({{ $option['compte'] }})</span></span>
                    </label>
                @endforeach
            </div>
            @error('cible')
                <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="titre" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Titre</label>
            <input id="titre" name="titre" type="text" required maxlength="255" value="{{ old('titre') }}"
                   placeholder="ex : Nouvelle fonctionnalité disponible !"
                   class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('titre') border-accent-400 @enderror">
            @error('titre')
                <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="message" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Message</label>
            <textarea id="message" name="message" rows="5" required maxlength="2000"
                      class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors resize-none @error('message') border-accent-400 @enderror">{{ old('message') }}</textarea>
            @error('message')
                <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="inline-flex items-center justify-center gap-2 bg-primary-800 hover:bg-primary-900 text-white font-body font-bold text-sm py-3 rounded-xl shadow-sm transition-colors">
            <span class="material-symbols-outlined text-[18px]">campaign</span>
            Envoyer
        </button>
    </form>
@endsection