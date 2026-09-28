@extends('layouts.app')

@section('title', 'Aide — Tafely')

@section('content')

{{-- ============ NAV ============ --}}
<nav
    x-data="{ scrolled: false, menuOuvert: false }"
    x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 20)"
    @keydown.escape.window="menuOuvert = false"
    :class="(scrolled || menuOuvert) ? 'shadow-md bg-white/95' : 'shadow-sm bg-white/80'"
    class="fixed top-0 inset-x-0 z-50 backdrop-blur-md transition-all duration-200"
>
    <div class="max-w-7xl mx-auto px-5 md:px-10 h-16 md:h-20 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center shrink-0" @click="menuOuvert = false">
            <img src="{{ asset('logo.png') }}" alt="Tafely" class="h-8 md:h-11">
        </a>

        <div class="hidden md:flex items-center gap-1">
            <a href="{{ route('home') }}" class="px-4 py-2 rounded-full text-sm font-semibold text-gray-600 hover:text-primary-700 hover:bg-primary-50 transition-colors">Accueil</a>
            <a href="{{ route('home') }}#fonctionnement" class="px-4 py-2 rounded-full text-sm font-semibold text-gray-600 hover:text-primary-700 hover:bg-primary-50 transition-colors">Comment ça marche</a>
            <a href="{{ route('home') }}#tarif" class="px-4 py-2 rounded-full text-sm font-semibold text-gray-600 hover:text-primary-700 hover:bg-primary-50 transition-colors">Tarif</a>
        </div>

        <div class="hidden md:flex items-center gap-3">
            <button @click="openAuth('login')" class="text-sm font-semibold text-gray-600 hover:text-primary-700 transition-colors">
                Se connecter
            </button>
            <button @click="openAuth('signup')"
                    class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white text-sm font-bold px-5 py-2.5 rounded-full shadow-sm shadow-accent-600/20 hover:-translate-y-0.5 active:scale-[0.98] transition-all">
                Créer ma boutique
            </button>
        </div>

        <button
            @click="menuOuvert = ! menuOuvert"
            aria-label="Menu"
            :aria-expanded="menuOuvert"
            class="md:hidden relative h-10 w-10 flex items-center justify-center rounded-full text-gray-600 hover:bg-primary-50 hover:text-primary-700 active:scale-[0.95] transition-all"
        >
            <span class="material-symbols-outlined text-[26px]" x-show="!menuOuvert">menu</span>
            <span class="material-symbols-outlined text-[26px]" x-show="menuOuvert" x-cloak>close</span>
        </button>
    </div>

    <div
        x-show="menuOuvert"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        @click.outside="menuOuvert = false"
        class="md:hidden absolute top-full inset-x-0 bg-white border-t border-gray-100 shadow-xl rounded-b-2xl overflow-hidden"
        style="display: none;"
    >
        <div class="flex flex-col p-3">
            <a href="{{ route('home') }}" @click="menuOuvert = false" class="px-4 py-3 rounded-xl text-sm font-semibold text-gray-700 hover:text-primary-700 hover:bg-primary-50 transition-colors">Accueil</a>
            <a href="{{ route('home') }}#fonctionnement" @click="menuOuvert = false" class="px-4 py-3 rounded-xl text-sm font-semibold text-gray-700 hover:text-primary-700 hover:bg-primary-50 transition-colors">Comment ça marche</a>
            <a href="{{ route('home') }}#tarif" @click="menuOuvert = false" class="px-4 py-3 rounded-xl text-sm font-semibold text-gray-700 hover:text-primary-700 hover:bg-primary-50 transition-colors">Tarif</a>

            <div class="h-px bg-gray-100 my-2"></div>

            <button @click="menuOuvert = false; openAuth('login')"
                    class="px-4 py-3 rounded-xl text-sm font-semibold text-gray-700 hover:text-primary-700 hover:bg-primary-50 transition-colors text-left">
                Se connecter
            </button>
            <button @click="menuOuvert = false; openAuth('signup')"
                    class="mt-1 inline-flex items-center justify-center gap-2 bg-accent-500 hover:bg-accent-600 text-white text-sm font-bold px-5 py-3 rounded-xl shadow-sm shadow-accent-600/20 active:scale-[0.98] transition-all">
                Créer ma boutique
            </button>
        </div>
    </div>
</nav>

{{-- ============ CONTENU ============ --}}
<header class="bg-primary-950 pt-32 pb-16 md:pt-40 md:pb-20">
    <div class="max-w-3xl mx-auto px-5 md:px-10 text-center">
        <span class="inline-flex items-center gap-2 bg-white/10 border border-white/20 text-primary-100 text-xs font-bold tracking-wide px-4 py-1.5 rounded-full mb-4">
            <span class="material-symbols-outlined text-[16px]">help</span>
            Centre d'aide
        </span>
        <h1 class="font-display text-3xl md:text-4xl font-bold text-white">Comment pouvons-nous vous aider ?</h1>
        <p class="font-body text-primary-100/80 mt-3">Les réponses aux questions les plus fréquentes sur Tafely.</p>
    </div>
</header>

<section class="bg-white py-16 md:py-20">
    <div class="max-w-3xl mx-auto px-5 md:px-10" x-data="{ ouvert: 0 }">
        @php
            $questions = [
                [
                    'q' => 'Comment créer ma boutique en ligne ?',
                    'r' => 'Cliquez sur « Créer ma boutique », entrez votre email et le code de vérification reçu. Votre compte est créé instantanément, sans mot de passe à retenir. Configurez ensuite le nom, le logo et l\'apparence de votre boutique depuis votre tableau de bord.',
                ],
                [
                    'q' => 'Combien de produits puis-je ajouter ?',
                    'r' => 'Jusqu\'à 10 produits pendant les 30 jours d\'essai gratuit, puis jusqu\'à 30 produits avec le plan Actif payant (20 000 Ar/mois). Un pack complémentaire de +10 produits est disponible à 5 000 Ar depuis la page Abonnement de votre tableau de bord.',
                ],
                [
                    'q' => 'Comment fonctionne l\'essai gratuit de 30 jours ?',
                    'r' => 'Il démarre automatiquement à la création de votre compte, sans carte bancaire. Vous pouvez ajouter des produits, personnaliser votre boutique et recevoir des commandes normalement pendant toute la durée de l\'essai.',
                ],
                [
                    'q' => 'Comment payer mon abonnement ?',
                    'r' => 'Depuis la page Abonnement de votre tableau de bord, cliquez sur « Souscrire ». Vous serez redirigé vers une page de paiement sécurisée où vous pouvez régler avec MVola, Orange Money ou une carte Visa.',
                ],
                [
                    'q' => 'Puis-je vendre à la fois en ligne et en boutique physique ?',
                    'r' => 'Oui. La fonctionnalité « Vente en boutique » vous permet d\'enregistrer des ventes faites directement sur place (client de passage ou non), avec mise à jour automatique du stock et facture disponible immédiatement.',
                ],
                [
                    'q' => 'Comment mes clients passent-ils commande ?',
                    'r' => 'Partagez le lien unique de votre boutique (visible dans votre tableau de bord) sur Facebook, WhatsApp, par SMS, etc. Vos clients choisissent leurs produits, indiquent s\'ils souhaitent récupérer ou se faire livrer, puis valident. Vous recevez la commande par email et dans votre tableau de bord.',
                ],
                [
                    'q' => 'Comment modifier le nom, le logo ou le NIF/STAT de ma boutique ?',
                    'r' => 'Rendez-vous dans Paramètres depuis votre tableau de bord. Vous pouvez y modifier le nom de la boutique, le logo, l\'adresse, le téléphone, le NIF, le STAT et les emails de notification.',
                ],
                [
                    'q' => 'Je n\'ai pas trouvé ma réponse, que faire ?',
                    'r' => 'Contactez-nous directement — nous répondons rapidement.',
                ],
            ];
        @endphp

        <div class="flex flex-col gap-3">
            @foreach ($questions as $i => $item)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <button type="button" @click="ouvert = (ouvert === {{ $i }} ? null : {{ $i }})"
                            class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left">
                        <span class="font-body font-semibold text-sm text-primary-900">{{ $item['q'] }}</span>
                        <span class="material-symbols-outlined text-gray-400 shrink-0 transition-transform" :class="ouvert === {{ $i }} ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="ouvert === {{ $i }}" x-cloak x-transition class="px-5 pb-4">
                        <p class="font-body text-sm text-gray-600 leading-relaxed">{{ $item['r'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-10 bg-primary-50 border border-primary-100 rounded-2xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
            <div>
                <h2 class="font-display font-bold text-primary-900">Encore besoin d'aide ?</h2>
                <p class="font-body text-sm text-primary-800/80 mt-1">Notre équipe est disponible sur WhatsApp et par email.</p>
            </div>
            <a href="{{ route('contact') }}"
               class="inline-flex items-center gap-2 bg-primary-800 hover:bg-primary-900 text-white font-body font-bold text-sm px-6 py-3 rounded-xl transition-colors shrink-0">
                Nous contacter
            </a>
        </div>
    </div>
</section>

{{-- ============ FOOTER ============ --}}
<footer class="bg-white border-t border-gray-100 py-10">
    <div class="max-w-7xl mx-auto px-5 md:px-10 flex flex-col md:flex-row items-center justify-between gap-6 text-center md:text-left">
        <div class="flex items-center gap-3">
            <div class="bg-white px-2 py-1 rounded-md">
                <img src="{{ asset('logo.png') }}" alt="Tafely" class="h-9">
            </div>
            <span class="font-body text-sm text-gray-400">© {{ date('Y') }} Tafely. Propulsons le commerce en ligne.</span>
        </div>
        <div class="flex gap-6 font-body text-sm text-gray-500">
            <a href="{{ route('aide') }}" class="hover:text-accent-600 transition-colors">Aide</a>
            <a href="{{ route('confidentialite') }}" class="hover:text-accent-600 transition-colors">Confidentialité</a>
            <a href="{{ route('contact') }}" class="hover:text-accent-600 transition-colors">Contact</a>
        </div>
    </div>
</footer>

@endsection