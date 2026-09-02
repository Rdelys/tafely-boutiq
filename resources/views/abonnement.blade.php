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
    @if ($user->status === 'active')
        <div class="mb-8 flex items-center gap-3 bg-primary-50 border border-primary-100 text-primary-800 rounded-xl px-5 py-4">
            <span class="material-symbols-outlined text-[22px]">workspace_premium</span>
            <p class="font-body text-sm font-semibold">Votre boutique est sur le plan <strong>Actif payant</strong>. Merci de votre confiance !</p>
        </div>
    @else
        <div class="mb-8 flex items-center gap-3 bg-gray-100 border border-gray-200 text-gray-700 rounded-xl px-5 py-4">
            <span class="material-symbols-outlined text-[22px]">info</span>
            <p class="font-body text-sm font-semibold">Votre boutique est actuellement sur le plan <strong>Gratuit</strong>.</p>
        </div>
    @endif

    {{-- grille des plans --}}
    <div class="grid sm:grid-cols-2 gap-5">
        @foreach ([
            [
                'status' => 'free',
                'nom' => 'Gratuit',
                'prix' => '0 Ar',
                'sousPrix' => null,
                'periode' => 'pendant 30 jours',
                'description' => 'Idéal pour démarrer et tester votre boutique.',
                'features' => [
                    'Jusqu\'à 10 produits',
                    'Lien de boutique partageable',
                    'Support communautaire',
                ],
            ],
            [
                'status' => 'active',
                'nom' => 'Actif payant',
                'prix' => '20 000 Ar',
                'sousPrix' => 'ou 6 €',
                'periode' => 'par mois',
                'description' => 'Pour les boutiques qui vendent sérieusement.',
                'features' => [
                    'Jusqu\'à 30 produits',
                    'Paiement MVOLA et Orange Money',
                    'Statistiques avancées',
                    'Support prioritaire',
                ],
            ],
        ] as $plan)
            @php($estActuel = $user->status === $plan['status'] || (! $user->status && $plan['status'] === 'free'))
            <div class="relative bg-white rounded-2xl p-6 border-2 flex flex-col {{ $estActuel ? 'border-primary-600 shadow-md' : 'border-gray-100 shadow-sm' }}">
                @if ($estActuel)
                    <span class="absolute -top-3 left-6 bg-primary-700 text-white text-xs font-bold uppercase tracking-wide px-3 py-1 rounded-full">
                        Plan actuel
                    </span>
                @endif

                <h2 class="font-display text-lg font-bold text-primary-900 mt-2">{{ $plan['nom'] }}</h2>
                <p class="font-body text-sm text-gray-500 mt-1 mb-4">{{ $plan['description'] }}</p>

                <div class="mb-1">
                    <span class="font-display text-3xl font-bold text-primary-900">{{ $plan['prix'] }}</span>
                    <span class="font-body text-sm text-gray-400"> {{ $plan['periode'] }}</span>
                </div>
                @if ($plan['sousPrix'])
                    <p class="font-body text-xs text-gray-400 mb-1">{{ $plan['sousPrix'] }}</p>
                    <p class="font-body text-[11px] text-gray-400 italic mb-4">Paiement en € : veuillez contacter l'administrateur : support@tafely-gr.com</p>
                @else
                    <p class="font-body text-xs text-gray-400 mb-4">&nbsp;</p>
                @endif

                <ul class="flex flex-col gap-2.5 mb-6 flex-1">
                    @foreach ($plan['features'] as $feature)
                        <li class="flex items-start gap-2.5 font-body text-sm text-gray-700">
                            <span class="material-symbols-outlined text-primary-600 text-[18px] mt-0.5">check_circle</span>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>

                @if ($estActuel)
                    <button type="button" disabled
                            class="w-full flex items-center justify-center gap-2 bg-primary-50 text-primary-700 font-body font-bold text-sm py-3 rounded-xl cursor-default">
                        <span class="material-symbols-outlined text-[18px]">check</span>
                        Plan actuel
                    </button>
                @else
                    <button type="button" disabled
                            class="w-full bg-gray-50 text-gray-400 font-body font-bold text-sm py-3 rounded-xl cursor-not-allowed">
                        Bientôt disponible
                    </button>
                @endif
            </div>
        @endforeach
    </div>

    {{-- pack complémentaire --}}
    <div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
        <div class="flex items-start gap-4">
            <div class="h-12 w-12 rounded-xl bg-accent-50 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-accent-600 text-[24px]">add_box</span>
            </div>
            <div>
                <h3 class="font-display font-bold text-primary-900">Besoin de plus de produits ?</h3>
                <p class="font-body text-sm text-gray-500 mt-1 max-w-md">Ajoutez un pack de 10 emplacements produits supplémentaires à votre plan actuel, sans changer d'offre.</p>
            </div>
        </div>

        <div class="flex items-center gap-4 shrink-0">
            <div class="text-right">
                <p class="font-display text-xl font-bold text-primary-900">5 000 Ar</p>
                <p class="font-body text-xs text-gray-400">ou 2 € · +10 produits</p>
                <p class="font-body text-[11px] text-gray-400 italic">Paiement en € : contactez l'administrateur : support@tafely-gr.com</p>
            </div>
            <button type="button" disabled
                    class="bg-gray-50 text-gray-400 font-body font-bold text-sm px-5 py-2.5 rounded-xl cursor-not-allowed whitespace-nowrap">
                Bientôt disponible
            </button>
        </div>
    </div>

    <p class="font-body text-xs text-gray-400 mt-6 text-center md:text-left">
        Le paiement en ligne (MVOLA, Orange Money) sera bientôt disponible directement depuis cette page.
    </p>

@endsection