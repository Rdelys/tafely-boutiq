@extends('layouts.app')

@section('title', 'Confidentialité — Tafely')

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
            <span class="material-symbols-outlined text-[16px]">lock</span>
            Confidentialité
        </span>
        <h1 class="font-display text-3xl md:text-4xl font-bold text-white">Politique de confidentialité</h1>
        <p class="font-body text-primary-100/80 mt-3">Dernière mise à jour : {{ now()->translatedFormat('F Y') }}</p>
    </div>
</header>

<section class="bg-white py-16 md:py-20">
    <div class="max-w-3xl mx-auto px-5 md:px-10">
        <div class="prose-tafely flex flex-col gap-8">

            <p class="font-body text-sm text-gray-600 leading-relaxed">
                Tafely (« nous ») respecte votre vie privée et celle de vos clients. Cette page explique quelles données nous collectons, pourquoi, et comment elles sont utilisées et protégées.
            </p>

            <div>
                <h2 class="font-display font-bold text-lg text-primary-900 mb-2">1. Données que nous collectons</h2>
                <ul class="space-y-2 font-body text-sm text-gray-600 leading-relaxed list-disc pl-5">
                    <li><strong class="text-gray-800">Compte marchand :</strong> email, nom, prénom, nom et description de la boutique, logo, adresse, téléphone, NIF/STAT si renseignés.</li>
                    <li><strong class="text-gray-800">Produits et ventes :</strong> les produits que vous ajoutez, ainsi que les commandes et ventes enregistrées sur votre boutique.</li>
                    <li><strong class="text-gray-800">Clients de votre boutique :</strong> nom, téléphone, adresse de livraison ou lieu de récupération, uniquement transmis par vos clients lors d'une commande.</li>
                    <li><strong class="text-gray-800">Paiement d'abonnement :</strong> montant, statut et référence de transaction — les informations bancaires ou de mobile money elles-mêmes sont traitées directement par notre prestataire de paiement (MVola / Orange Money / Papi), jamais stockées sur nos serveurs.</li>
                </ul>
            </div>

            <div>
                <h2 class="font-display font-bold text-lg text-primary-900 mb-2">2. Pourquoi nous les utilisons</h2>
                <ul class="space-y-2 font-body text-sm text-gray-600 leading-relaxed list-disc pl-5">
                    <li>Faire fonctionner votre boutique en ligne et votre tableau de bord.</li>
                    <li>Vous envoyer les notifications de nouvelles commandes et de confirmation de paiement.</li>
                    <li>Générer vos factures et reçus.</li>
                    <li>Assurer la sécurité de votre compte (connexion par code à usage unique).</li>
                    <li>Répondre à vos demandes auprès de notre support.</li>
                </ul>
            </div>

            <div>
                <h2 class="font-display font-bold text-lg text-primary-900 mb-2">3. Partage des données</h2>
                <p class="font-body text-sm text-gray-600 leading-relaxed">
                    Nous ne vendons ni ne louons vos données à des tiers. Elles sont partagées uniquement avec les prestataires nécessaires au fonctionnement du service : notre hébergeur, notre prestataire d'envoi d'emails, et notre prestataire de paiement mobile money (Papi) lors d'une souscription à l'abonnement.
                </p>
            </div>

            <div>
                <h2 class="font-display font-bold text-lg text-primary-900 mb-2">4. Conservation des données</h2>
                <p class="font-body text-sm text-gray-600 leading-relaxed">
                    Vos données sont conservées tant que votre compte est actif. Vous pouvez demander la suppression de votre compte et de vos données à tout moment en nous contactant.
                </p>
            </div>

            <div>
                <h2 class="font-display font-bold text-lg text-primary-900 mb-2">5. Vos droits</h2>
                <p class="font-body text-sm text-gray-600 leading-relaxed">
                    Vous pouvez à tout moment demander à consulter, corriger ou supprimer les données que nous détenons sur vous, en nous écrivant à
                    <a href="mailto:contact@tafely-gr.com" class="text-primary-700 font-semibold hover:text-accent-600 transition-colors">contact@tafely-gr.com</a>.
                </p>
            </div>

            <div>
                <h2 class="font-display font-bold text-lg text-primary-900 mb-2">6. Nous contacter</h2>
                <p class="font-body text-sm text-gray-600 leading-relaxed">
                    Pour toute question sur cette politique de confidentialité, contactez-nous par email à
                    <a href="mailto:contact@tafely-gr.com" class="text-primary-700 font-semibold hover:text-accent-600 transition-colors">contact@tafely-gr.com</a>
                    ou par WhatsApp au
                    <a href="https://wa.me/261343236379" target="_blank" rel="noopener" class="text-primary-700 font-semibold hover:text-accent-600 transition-colors">+261 34 32 363 79</a>.
                </p>
            </div>

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