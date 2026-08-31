@extends('layouts.dashboard')

@section('title', 'Paramètres — Tafely')

@section('page-content')

    {{-- header --}}
    <div class="mb-8">
        <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Paramètres de la boutique</h1>
        <p class="font-body text-gray-500 mt-1">Gérez les informations générales et les préférences de notification de votre boutique.</p>
    </div>

    {{-- succès --}}
    @if (session('status'))
        <div class="max-w-2xl mb-6 flex items-center gap-3 bg-primary-50 border border-primary-100 text-primary-700 rounded-xl px-4 py-3">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span class="font-body text-sm font-semibold">{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('parametres.update') }}" class="max-w-2xl flex flex-col gap-6">
        @csrf
        @method('PUT')

        {{-- Section 1 : infos générales --}}
        <section class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
            <div class="flex items-center gap-2.5 mb-5 border-b border-gray-100 pb-4">
                <span class="material-symbols-outlined text-primary-700 text-[24px]">store</span>
                <h2 class="font-display text-lg font-bold text-primary-900">Informations générales</h2>
            </div>

            <div>
                <label for="nom_boutique" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Nom de la boutique</label>
                <input
                    id="nom_boutique"
                    name="nom_boutique"
                    type="text"
                    value="{{ old('nom_boutique', $user->nom_boutique) }}"
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('nom_boutique') border-accent-400 @enderror"
                >
                @error('nom_boutique')
                    <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                @enderror
                <p class="font-body text-xs text-gray-400 mt-1.5">Ce nom apparaîtra sur votre vitrine publique et sur les reçus des clients.</p>
            </div>

            <div class="mt-5">
                <label for="adresse" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Adresse de la boutique</label>
                <input
                    id="adresse"
                    name="adresse"
                    type="text"
                    value="{{ old('adresse', $user->adresse) }}"
                    placeholder="ex : Lot II M 45 Antananarivo, Madagascar"
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('adresse') border-accent-400 @enderror"
                >
                @error('adresse')
                    <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                @enderror
                <p class="font-body text-xs text-gray-400 mt-1.5">Utilisée pour les livraisons et affichée sur votre vitrine si activée.</p>
            </div>
        </section>

        {{-- Section 2 : notifications de commande --}}
        <section class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
            <div class="flex items-center gap-2.5 mb-5 border-b border-gray-100 pb-4">
                <span class="material-symbols-outlined text-primary-700 text-[24px]">mail</span>
                <h2 class="font-display text-lg font-bold text-primary-900">Notifications de commande</h2>
            </div>
            <p class="font-body text-sm text-gray-500 mb-5">Définissez les adresses email qui recevront une alerte pour chaque nouvelle commande sur votre boutique.</p>

            <div class="space-y-5">
                <div>
                    <label for="email_notification" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">
                        Email principal <span class="text-accent-500">*</span>
                    </label>
                    <input
                        id="email_notification"
                        name="email_notification"
                        type="email"
                        required
                        value="{{ old('email_notification', $user->email_notification ?? $user->email) }}"
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('email_notification') border-accent-400 @enderror"
                    >
                    @error('email_notification')
                        <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                    @enderror
                    <p class="font-body text-xs text-gray-400 mt-1.5">L'adresse principale pour toutes les communications de vente.</p>
                </div>

                <div>
                    <label for="email_notification_secondaire" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">
                        Email secondaire (optionnel)
                    </label>
                    <input
                        id="email_notification_secondaire"
                        name="email_notification_secondaire"
                        type="email"
                        placeholder="ex: associe@maboutique.mg"
                        value="{{ old('email_notification_secondaire', $user->email_notification_secondaire) }}"
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('email_notification_secondaire') border-accent-400 @enderror"
                    >
                    @error('email_notification_secondaire')
                        <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                    @enderror
                    <p class="font-body text-xs text-gray-400 mt-1.5">Ajoutez une deuxième adresse pour qu'un collaborateur soit également notifié.</p>
                </div>
            </div>
        </section>

        {{-- action --}}
        <div class="flex justify-end pb-4">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm px-6 py-3 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-[0.98]">
                <span class="material-symbols-outlined text-[18px]">save</span>
                Sauvegarder les modifications
            </button>
        </div>
    </form>

@endsection