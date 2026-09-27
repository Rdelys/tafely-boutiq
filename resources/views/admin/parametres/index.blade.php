@extends('layouts.admin')

@section('title', 'Paramètres — Admin Tafely')

@section('page-content')
    <div class="mb-6">
        <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Paramètres plateforme</h1>
        <p class="font-body text-gray-500 mt-1">Réglages globaux, appliqués immédiatement sans redéploiement.</p>
    </div>

    @if (session('status'))
        <div class="mb-6 flex items-center gap-3 bg-primary-50 border border-primary-100 text-primary-700 rounded-xl px-4 py-3">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span class="font-body text-sm font-semibold">{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.parametres.update') }}" class="max-w-xl flex flex-col gap-6">
        @csrf
        @method('PUT')

        <section class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
            <div class="flex items-center gap-2.5 mb-5 border-b border-gray-100 pb-4">
                <span class="material-symbols-outlined text-primary-700 text-[24px]">payments</span>
                <h2 class="font-display text-lg font-bold text-primary-900">Tarifs</h2>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="prix_abonnement" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Prix de l'abonnement mensuel (Ar)</label>
                    <input id="prix_abonnement" name="prix_abonnement" type="number" min="0" step="1" required
                           value="{{ old('prix_abonnement', $parametres['prix_abonnement']) }}"
                           class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('prix_abonnement') border-accent-400 @enderror">
                    @error('prix_abonnement')
                        <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="prix_pack_produits" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Prix du pack +10 produits (Ar)</label>
                    <input id="prix_pack_produits" name="prix_pack_produits" type="number" min="0" step="1" required
                           value="{{ old('prix_pack_produits', $parametres['prix_pack_produits']) }}"
                           class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('prix_pack_produits') border-accent-400 @enderror">
                    @error('prix_pack_produits')
                        <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <p class="font-body text-xs text-gray-400 mt-4">Ces prix sont utilisés immédiatement pour toute nouvelle souscription — les abonnements déjà en cours ne sont pas affectés.</p>
        </section>

        <section class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
            <div class="flex items-center gap-2.5 mb-5 border-b border-gray-100 pb-4">
                <span class="material-symbols-outlined text-accent-600 text-[24px]">build</span>
                <h2 class="font-display text-lg font-bold text-primary-900">Mode maintenance</h2>
            </div>

            <div class="flex items-start justify-between gap-4 py-3">
                <div>
                    <p class="font-body font-semibold text-sm text-gray-900">Désactiver les nouvelles inscriptions</p>
                    <p class="font-body text-xs text-gray-500 mt-0.5">Les marchands déjà inscrits peuvent toujours se connecter.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" name="maintenance_inscriptions" value="1" class="sr-only peer"
                           {{ old('maintenance_inscriptions', $parametres['maintenance_inscriptions']) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-200 peer-checked:bg-accent-500 rounded-full transition-colors"></div>
                    <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                </label>
            </div>

            <div class="flex items-start justify-between gap-4 py-3 border-t border-gray-50">
                <div>
                    <p class="font-body font-semibold text-sm text-gray-900">Désactiver les paiements</p>
                    <p class="font-body text-xs text-gray-500 mt-0.5">Bloque les nouvelles souscriptions et achats de packs (maintenance Papi, par exemple).</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" name="maintenance_paiements" value="1" class="sr-only peer"
                           {{ old('maintenance_paiements', $parametres['maintenance_paiements']) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-200 peer-checked:bg-accent-500 rounded-full transition-colors"></div>
                    <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                </label>
            </div>
        </section>

        <button type="submit"
                class="inline-flex items-center justify-center gap-2 bg-primary-800 hover:bg-primary-900 text-white font-body font-bold text-sm py-3 rounded-xl shadow-sm transition-colors">
            <span class="material-symbols-outlined text-[18px]">save</span>
            Enregistrer
        </button>
    </form>
@endsection