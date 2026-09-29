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

    @if (session('erreur'))
        <div class="mb-6 flex items-center gap-3 bg-accent-50 border border-accent-100 text-accent-700 rounded-xl px-4 py-3">
            <span class="material-symbols-outlined text-[20px]">error</span>
            <span class="font-body text-sm font-semibold">{{ session('erreur') }}</span>
        </div>
    @endif

    @php
        // Rouvre automatiquement l'onglet contenant le champ en erreur.
        $ongletParDefaut = 'tarifs';
        if ($errors->hasAny(['reduction_trimestre', 'reduction_semestre', 'reduction_9_mois', 'reduction_annuel'])) {
            $ongletParDefaut = 'reductions';
        } elseif ($errors->hasAny(['maintenance_inscriptions', 'maintenance_paiements'])) {
            $ongletParDefaut = 'maintenance';
        }
    @endphp

    <form method="POST" action="{{ route('admin.parametres.update') }}" class="max-w-2xl"
          x-data="{ onglet: '{{ $ongletParDefaut }}' }">
        @csrf
        @method('PUT')

        {{-- ============ ONGLETS ============ --}}
        <div class="flex flex-wrap gap-2 mb-6">
            <button type="button" @click="onglet = 'tarifs'"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-body font-semibold border transition-colors"
                    :class="onglet === 'tarifs' ? 'bg-primary-700 text-white border-primary-700' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'">
                <span class="material-symbols-outlined text-[18px]">payments</span>
                Tarifs
            </button>
            <button type="button" @click="onglet = 'reductions'"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-body font-semibold border transition-colors"
                    :class="onglet === 'reductions' ? 'bg-primary-700 text-white border-primary-700' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'">
                <span class="material-symbols-outlined text-[18px]">local_offer</span>
                Réductions par durée
            </button>
            <button type="button" @click="onglet = 'maintenance'"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-body font-semibold border transition-colors"
                    :class="onglet === 'maintenance' ? 'bg-primary-700 text-white border-primary-700' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'">
                <span class="material-symbols-outlined text-[18px]">build</span>
                Mode maintenance
            </button>
        </div>

        <div class="flex flex-col gap-6">

                        {{-- ============ ONGLET TARIFS ============ --}}
            <section x-show="onglet === 'tarifs'" x-cloak class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
                <div class="flex items-center gap-2.5 mb-5 border-b border-gray-100 pb-4">
                    <span class="material-symbols-outlined text-primary-700 text-[24px]">payments</span>
                    <h2 class="font-display text-lg font-bold text-primary-900">Tarifs</h2>
                </div>

                <p class="font-body text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Abonnement</p>
                <div class="grid sm:grid-cols-2 gap-5 mb-6">
                    <div>
                        <label for="prix_abonnement" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Prix mensuel de base (Ar)</label>
                        <input id="prix_abonnement" name="prix_abonnement" type="number" min="0" step="1" required
                               value="{{ old('prix_abonnement', $parametres['prix_abonnement']) }}"
                               class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('prix_abonnement') border-accent-400 @enderror">
                        @error('prix_abonnement')
                            <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="duree_max_mois" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Durée max. souscriptible (mois)</label>
                        <input id="duree_max_mois" name="duree_max_mois" type="number" min="1" max="120" step="1" required
                               value="{{ old('duree_max_mois', $parametres['duree_max_mois']) }}"
                               class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('duree_max_mois') border-accent-400 @enderror">
                        @error('duree_max_mois')
                            <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <p class="font-body text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Produits supplémentaires</p>
                <div class="grid sm:grid-cols-3 gap-5">
                    <div>
                        <label for="prix_par_produit" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Prix par produit (Ar)</label>
                        <input id="prix_par_produit" name="prix_par_produit" type="number" min="0" step="1" required
                               value="{{ old('prix_par_produit', $parametres['prix_par_produit']) }}"
                               class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('prix_par_produit') border-accent-400 @enderror">
                        @error('prix_par_produit')
                            <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="pas_produits" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Pas d'achat (produits)</label>
                        <input id="pas_produits" name="pas_produits" type="number" min="1" max="100" step="1" required
                               value="{{ old('pas_produits', $parametres['pas_produits']) }}"
                               class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('pas_produits') border-accent-400 @enderror">
                        @error('pas_produits')
                            <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="quantite_max_produits" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Max. par achat (produits)</label>
                        <input id="quantite_max_produits" name="quantite_max_produits" type="number" min="1" max="1000" step="1" required
                               value="{{ old('quantite_max_produits', $parametres['quantite_max_produits']) }}"
                               class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('quantite_max_produits') border-accent-400 @enderror">
                        @error('quantite_max_produits')
                            <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p class="font-body text-xs text-gray-400 mt-4">Le marchand achète des emplacements par multiples du pas (5 par défaut : 5, 10, 15...). Mettez 1 pour autoriser n'importe quel nombre. Ces prix s'appliquent immédiatement aux nouveaux achats, sans toucher aux abonnements en cours.</p>
            </section>

            {{-- ============ ONGLET RÉDUCTIONS ============ --}}
            <section x-show="onglet === 'reductions'" x-cloak class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
                <div class="flex items-center gap-2.5 mb-5 border-b border-gray-100 pb-4">
                    <span class="material-symbols-outlined text-primary-700 text-[24px]">local_offer</span>
                    <h2 class="font-display text-lg font-bold text-primary-900">Réductions par durée</h2>
                </div>
                <p class="font-body text-xs text-gray-500 mb-5">Réduction (%) appliquée au prix total à partir du nombre de mois indiqué. Le marchand peut saisir n'importe quelle durée — le palier applicable est le plus élevé qu'il atteint.</p>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label for="reduction_trimestre" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Trimestre — à partir de 3 mois (%)</label>
                        <input id="reduction_trimestre" name="reduction_trimestre" type="number" min="0" max="90" step="1" required
                               value="{{ old('reduction_trimestre', $parametres['reduction_trimestre']) }}"
                               class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('reduction_trimestre') border-accent-400 @enderror">
                        @error('reduction_trimestre')
                            <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="reduction_semestre" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Semestre — à partir de 6 mois (%)</label>
                        <input id="reduction_semestre" name="reduction_semestre" type="number" min="0" max="90" step="1" required
                               value="{{ old('reduction_semestre', $parametres['reduction_semestre']) }}"
                               class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('reduction_semestre') border-accent-400 @enderror">
                        @error('reduction_semestre')
                            <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="reduction_9_mois" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">9 mois — à partir de 9 mois (%)</label>
                        <input id="reduction_9_mois" name="reduction_9_mois" type="number" min="0" max="90" step="1" required
                               value="{{ old('reduction_9_mois', $parametres['reduction_9_mois']) }}"
                               class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('reduction_9_mois') border-accent-400 @enderror">
                        @error('reduction_9_mois')
                            <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="reduction_annuel" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Annuel — à partir de 12 mois (%)</label>
                        <input id="reduction_annuel" name="reduction_annuel" type="number" min="0" max="90" step="1" required
                               value="{{ old('reduction_annuel', $parametres['reduction_annuel']) }}"
                               class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('reduction_annuel') border-accent-400 @enderror">
                        @error('reduction_annuel')
                            <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- aperçu --}}
                <div class="mt-6 bg-gray-50 rounded-xl p-4">
                    <p class="font-body text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Aperçu avec les valeurs actuellement enregistrées</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach ($apercu as $d)
                            <div class="bg-white rounded-lg p-3 border border-gray-100">
                                <p class="font-body text-xs text-gray-500">{{ $d['mois'] }} mois</p>
                                <p class="font-display font-bold text-sm text-primary-900">{{ number_format($d['prix_total'], 0, ',', ' ') }} Ar</p>
                                @if ($d['reduction'] > 0)
                                    <p class="font-body text-[11px] text-green-600">-{{ $d['reduction'] }} %</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- ============ ONGLET MAINTENANCE ============ --}}
            <section x-show="onglet === 'maintenance'" x-cloak class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
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
        </div>
    </form>
@endsection