@extends('layouts.dashboard')

@section('title', 'Abonnement — Tafely')

@section('page-content')

    @php($user = auth()->user())

    {{-- header --}}
    <div class="mb-8">
        <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Abonnement</h1>
        <p class="font-body text-gray-500 mt-1">Consultez votre plan actuel et les options disponibles pour votre boutique.</p>
    </div>

    {{-- bandeau statut actuel --}}
    @if ($user->abonnementActif())
        <div class="mb-8 flex items-start gap-3 bg-primary-50 border border-primary-100 text-primary-800 rounded-xl px-5 py-4">
            <span class="material-symbols-outlined text-[22px] mt-0.5">workspace_premium</span>
            <div>
                <p class="font-body text-sm font-semibold">
                    Votre boutique est sur le plan <strong>Actif payant</strong>.
                    @if ($user->boutiqueValidee())
                        <span class="inline-flex items-center gap-1 bg-accent-500 text-white text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full align-middle ml-1">Offre de lancement</span>
                    @endif
                </p>
                @if ($user->abonnement_expire_le)
                    <p class="font-body text-sm mt-1">
                        Fin de l'abonnement : <strong>{{ $user->abonnement_expire_le->format('d/m/Y') }}</strong>
                        — il vous reste <strong>{{ $user->dureeRestanteLabel() }}</strong>.
                    </p>
                @else
                    <p class="font-body text-sm mt-1">Votre abonnement n'a pas de date de fin.</p>
                @endif
            </div>
        </div>
    @elseif ($user->essaiExpire())
        <div class="mb-8 flex items-center gap-3 bg-accent-50 border border-accent-100 text-accent-700 rounded-xl px-5 py-4">
            <span class="material-symbols-outlined text-[22px]">error</span>
            <p class="font-body text-sm font-semibold">Votre période d'essai gratuite est terminée. Souscrivez pour continuer à ajouter des produits.</p>
        </div>
    @else
        @php($joursRestants = $user->joursRestantsEssai())
        <div class="mb-8 flex items-center gap-3 bg-gray-100 border border-gray-200 text-gray-700 rounded-xl px-5 py-4">
            <span class="material-symbols-outlined text-[22px]">info</span>
            <p class="font-body text-sm font-semibold">Vous êtes en essai gratuit — il vous reste {{ $joursRestants }} jour{{ $joursRestants > 1 ? 's' : '' }}.</p>
        </div>
    @endif

    @if (session('erreur'))
        <div class="mb-6 flex items-center gap-3 bg-accent-50 border border-accent-100 text-accent-700 rounded-xl px-4 py-3">
            <span class="material-symbols-outlined text-[20px]">error</span>
            <span class="font-body text-sm font-semibold">{{ session('erreur') }}</span>
        </div>
    @endif

    @error('duree')
        <div class="mb-6 flex items-center gap-3 bg-accent-50 border border-accent-100 text-accent-700 rounded-xl px-4 py-3">
            <span class="material-symbols-outlined text-[20px]">error</span>
            <span class="font-body text-sm font-semibold">{{ $message }}</span>
        </div>
    @enderror

    {{-- grille des plans --}}
    <div class="grid sm:grid-cols-2 gap-5">

        {{-- ---- plan gratuit ---- --}}
        @php($estActuel = ! $user->abonnementActif())
        <div class="relative bg-white rounded-2xl p-6 border-2 flex flex-col {{ $estActuel ? 'border-primary-600 shadow-md' : 'border-gray-100 shadow-sm' }}">
            @if ($estActuel)
                <span class="absolute -top-3 left-6 bg-primary-700 text-white text-xs font-bold uppercase tracking-wide px-3 py-1 rounded-full">
                    Plan actuel
                </span>
            @endif

            <h2 class="font-display text-lg font-bold text-primary-900 mt-2">Gratuit</h2>
            <p class="font-body text-sm text-gray-500 mt-1 mb-4">Idéal pour démarrer et tester votre boutique.</p>

            <div class="mb-1">
                <span class="font-display text-3xl font-bold text-primary-900">0 Ar</span>
                <span class="font-body text-sm text-gray-400"> pendant 30 jours</span>
            </div>
            <p class="font-body text-xs text-gray-400 mb-4">&nbsp;</p>

            <ul class="flex flex-col gap-2.5 mb-6 flex-1">
                @foreach (['Jusqu\'à 10 produits', 'Lien de boutique partageable', 'Support communautaire'] as $feature)
                    <li class="flex items-start gap-2.5 font-body text-sm text-gray-700">
                        <span class="material-symbols-outlined text-primary-600 text-[18px] mt-0.5">check_circle</span>
                        {{ $feature }}
                    </li>
                @endforeach
            </ul>

            @if ($estActuel)
                <button type="button" disabled class="w-full flex items-center justify-center gap-2 bg-primary-50 text-primary-700 font-body font-bold text-sm py-3 rounded-xl cursor-default">
                    <span class="material-symbols-outlined text-[18px]">check</span>
                    Plan actuel
                </button>
            @else
                <button type="button" disabled class="w-full bg-gray-50 text-gray-400 font-body font-bold text-sm py-3 rounded-xl cursor-not-allowed">
                    Non disponible
                </button>
            @endif
        </div>

        {{-- ---- plan payant : durée libre ---- --}}
        @php($estActuelPayant = $user->abonnementActif())
        <div class="relative bg-white rounded-2xl p-6 border-2 flex flex-col {{ $estActuelPayant ? 'border-primary-600 shadow-md' : 'border-gray-100 shadow-sm' }}"
             x-data="{
                mois: 1,
                dureeMax: {{ $dureeMax }},
                actif: {{ \Illuminate\Support\Js::from($estActuelPayant) }},
                base: '{{ $estActuelPayant && $user->abonnement_expire_le ? $user->abonnement_expire_le->format('Y-m-d') : now()->format('Y-m-d') }}',
                paliers: {{ \Illuminate\Support\Js::from($paliers) }},
                suggestions: {{ \Illuminate\Support\Js::from($durees) }},

                get reduction() {
                    for (const p of this.paliers) {
                        if (this.mois >= p.seuil) return p.reduction;
                    }
                    return 0;
                },
                get prixMensuel() { return {{ $durees[0]['prix_total'] }}; },
                get prixTotal() {
                    const m = Math.max(1, parseInt(this.mois) || 1);
                    return Math.round(this.prixMensuel * m * (100 - this.reduction) / 100);
                },
                get prixMensuelEquivalent() {
                    const m = Math.max(1, parseInt(this.mois) || 1);
                    return Math.round(this.prixTotal / m);
                },
                get nouvelleFin() {
                    const m = Math.max(1, parseInt(this.mois) || 1);
                    const d = new Date(this.base + 'T00:00:00');
                    d.setMonth(d.getMonth() + m);
                    return d.toLocaleDateString('fr-FR');
                },
                formatteMonnaie(n) { return new Intl.NumberFormat('fr-FR').format(n) + ' Ar'; },
                majMois(v) { this.mois = Math.min(this.dureeMax, Math.max(1, parseInt(v) || 1)); },
             }">
            @if ($estActuelPayant)
                <span class="absolute -top-3 left-6 bg-primary-700 text-white text-xs font-bold uppercase tracking-wide px-3 py-1 rounded-full">
                    Plan actuel
                </span>
            @endif

            <h2 class="font-display text-lg font-bold text-primary-900 mt-2">Actif payant</h2>
            <p class="font-body text-sm text-gray-500 mt-1 mb-4">Pour les boutiques qui vendent sérieusement. Plus vous souscrivez longtemps, plus le prix mensuel baisse.</p>

            {{-- raccourcis --}}
            <div class="flex flex-wrap gap-1.5 mb-3">
                <template x-for="s in suggestions" :key="s.mois">
                    <button type="button" @click="mois = s.mois"
                            class="relative px-3 py-1.5 rounded-full border-2 text-xs font-body font-bold transition-colors"
                            :class="mois === s.mois ? 'border-primary-600 bg-primary-50 text-primary-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                        <span x-text="s.mois + ' mois'"></span>
                        <span x-show="s.reduction > 0" x-cloak class="ml-1 text-accent-600" x-text="'-' + s.reduction + '%'"></span>
                    </button>
                </template>
            </div>

            {{-- saisie libre --}}
            <div class="flex items-center gap-3 mb-4">
                <label for="mois-libre" class="font-body text-xs font-semibold text-gray-500 shrink-0">Ou saisissez une durée précise :</label>
                <input id="mois-libre" type="number" min="1" :max="dureeMax" step="1"
                       x-model.number="mois" @input="majMois($event.target.value)"
                       class="w-20 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5 font-body text-sm text-center focus:outline-none focus:ring-2 focus:ring-primary-600">
                <span class="font-body text-xs text-gray-400">mois (max <span x-text="dureeMax"></span>)</span>
            </div>

            <div class="mb-1">
                <span class="font-display text-3xl font-bold text-primary-900" x-text="formatteMonnaie(prixTotal)"></span>
            </div>
            <p class="font-body text-xs text-gray-400 mb-3">
                <span x-show="mois > 1" x-text="formatteMonnaie(prixMensuelEquivalent) + ' / mois équivalent'"></span>
                <span x-show="mois <= 1">par mois</span>
                <span x-show="reduction > 0" x-cloak class="text-green-600 font-semibold" x-text="' · réduction de ' + reduction + ' %'"></span>
            </p>

            {{-- date de fin (actuelle → nouvelle) --}}
            <div class="mb-4 bg-primary-50 border border-primary-100 rounded-xl px-4 py-3 font-body text-xs text-primary-800">
                @if ($estActuelPayant && $user->abonnement_expire_le)
                    <p>Fin actuelle : <strong>{{ $user->abonnement_expire_le->format('d/m/Y') }}</strong> (il reste {{ $user->dureeRestanteLabel() }})</p>
                @endif
                <p>
                    <span x-text="actif ? 'Nouvelle date de fin :' : 'Date de fin :'"></span>
                    <strong x-text="nouvelleFin"></strong>
                    <span class="text-primary-700/70" x-text="'(+' + mois + ' mois)'"></span>
                </p>
            </div>

            <p class="font-body text-[11px] text-gray-400 italic mb-4">Paiement en € : veuillez contacter l'administrateur : support@tafely-gr.com</p>

            <ul class="flex flex-col gap-2.5 mb-6 flex-1">
                @foreach (['Jusqu\'à 30 produits', 'Paiement MVOLA et Orange Money', 'Statistiques avancées', 'Support prioritaire'] as $feature)
                    <li class="flex items-start gap-2.5 font-body text-sm text-gray-700">
                        <span class="material-symbols-outlined text-primary-600 text-[18px] mt-0.5">check_circle</span>
                        {{ $feature }}
                    </li>
                @endforeach
            </ul>

            @if ($estActuelPayant)
                <p class="font-body text-xs text-gray-400 text-center mb-2">Le paiement ci-dessous s'ajoute à votre abonnement actuel : la durée choisie est ajoutée à la date de fin.</p>
            @endif

            <form method="POST" action="{{ route('abonnement.souscrire') }}">
                @csrf
                <input type="hidden" name="duree" :value="mois">
                <button type="submit" class="w-full flex items-center justify-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm py-3 rounded-xl shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-[18px]">payments</span>
                    <span x-text="(actif ? 'Prolonger de ' : 'Souscrire pour ') + mois + ' mois'"></span> — MVola / Orange Money
                </button>
            </form>
        </div>
    </div>

    {{-- produits supplémentaires, par quantité --}}
    <div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm p-6"
         x-data="{
            quantite: {{ $pack['pas'] }},
            pas: {{ $pack['pas'] }},
            max: {{ $pack['max'] }},
            prixParProduit: {{ $pack['prix_par_produit'] }},
            suggestions: {{ \Illuminate\Support\Js::from($pack['suggestions']) }},

            get prixTotal() { return this.quantite * this.prixParProduit; },
            formatteMonnaie(n) { return new Intl.NumberFormat('fr-FR').format(n) + ' Ar'; },
            ajuster(delta) {
                this.quantite = Math.min(this.max, Math.max(this.pas, (parseInt(this.quantite) || this.pas) + delta * this.pas));
            },
            corriger() {
                let q = parseInt(this.quantite) || this.pas;
                q = Math.round(q / this.pas) * this.pas;
                this.quantite = Math.min(this.max, Math.max(this.pas, q));
            },
         }">
        <div class="flex items-start gap-4 mb-5">
            <div class="h-12 w-12 rounded-xl bg-accent-50 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-accent-600 text-[24px]">add_box</span>
            </div>
            <div>
                <h3 class="font-display font-bold text-primary-900">Besoin de plus de produits ?</h3>
                <p class="font-body text-sm text-gray-500 mt-1 max-w-lg">Ajoutez le nombre d'emplacements que vous voulez à votre plan actuel, sans changer d'offre. Vous utilisez actuellement {{ $user->produits()->count() }} sur {{ $user->limiteProduits() }}.</p>
            </div>
        </div>

        @error('quantite')
            <p class="mb-3 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
        @enderror

        {{-- raccourcis --}}
        <div class="flex flex-wrap gap-1.5 mb-4">
            <template x-for="s in suggestions" :key="s.quantite">
                <button type="button" @click="quantite = s.quantite"
                        class="px-3 py-1.5 rounded-full border-2 text-xs font-body font-bold transition-colors"
                        :class="quantite === s.quantite ? 'border-primary-600 bg-primary-50 text-primary-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                    <span x-text="'+' + s.quantite"></span>
                </button>
            </template>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="flex items-center gap-3">
                <button type="button" @click="ajuster(-1)" class="h-9 w-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50">−</button>
                <input type="number" :min="pas" :max="max" :step="pas" x-model.number="quantite" @change="corriger()"
                       class="w-20 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 font-body text-sm text-center focus:outline-none focus:ring-2 focus:ring-primary-600">
                <button type="button" @click="ajuster(1)" class="h-9 w-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50">+</button>
                <span class="font-body text-xs text-gray-400">produits (par <span x-text="pas"></span>, max <span x-text="max"></span>)</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-4 w-full sm:w-auto">
                <div class="text-center sm:text-right">
                    <p class="font-display text-xl font-bold text-primary-900" x-text="formatteMonnaie(prixTotal)"></p>
                    <p class="font-body text-xs text-gray-400"><span x-text="formatteMonnaie(prixParProduit)"></span> par produit</p>
                    <p class="font-body text-[11px] text-gray-400 italic">Paiement en € : contactez l'administrateur : support@tafely-gr.com</p>
                </div>
                <form method="POST" action="{{ route('abonnement.pack') }}" class="w-full sm:w-auto">
                    @csrf
                    <input type="hidden" name="quantite" :value="quantite">
                    <button type="submit" class="w-full sm:w-auto bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm px-5 py-2.5 rounded-xl transition-colors whitespace-nowrap">
                        Acheter — MVola / Orange Money
                    </button>
                </form>
            </div>
        </div>
    </div>

    <p class="font-body text-xs text-gray-400 mt-6 text-center md:text-left">
        Le paiement en ligne (MVOLA, Orange Money) sera bientôt disponible directement depuis cette page.
    </p>

@endsection