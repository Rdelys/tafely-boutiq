@extends('layouts.app')

@section('title', 'Contact — Tafely')

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
            <span class="material-symbols-outlined text-[16px]">forum</span>
            Contact
        </span>
        <h1 class="font-display text-3xl md:text-4xl font-bold text-white">Parlons de votre boutique</h1>
        <p class="font-body text-primary-100/80 mt-3">Une question, un problème, une suggestion ? Nous répondons rapidement.</p>
    </div>
</header>

<section class="bg-white py-16 md:py-20">
    <div class="max-w-3xl mx-auto px-5 md:px-10">
        <div class="grid sm:grid-cols-2 gap-5">

            {{-- WhatsApp --}}
            <a href="https://wa.me/261343236379" target="_blank" rel="noopener"
               class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 p-8 flex flex-col items-center text-center">
                <div class="h-14 w-14 rounded-full bg-green-50 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
                    <span class="material-symbols-outlined text-green-600 text-3xl">chat</span>
                </div>
                <h2 class="font-display font-bold text-lg text-gray-900 mb-1">WhatsApp</h2>
                <p class="font-body text-sm text-gray-500 mb-3">Réponse la plus rapide, du lundi au samedi.</p>
                <span class="font-body font-bold text-primary-700 group-hover:text-accent-600 transition-colors">+261 34 32 363 79</span>
            </a>

            {{-- Email --}}
            <a href="mailto:contact@tafely-gr.com"
               class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 p-8 flex flex-col items-center text-center">
                <div class="h-14 w-14 rounded-full bg-primary-50 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
                    <span class="material-symbols-outlined text-primary-700 text-3xl">mail</span>
                </div>
                <h2 class="font-display font-bold text-lg text-gray-900 mb-1">Email</h2>
                <p class="font-body text-sm text-gray-500 mb-3">Pour les demandes détaillées ou administratives.</p>
                <span class="font-body font-bold text-primary-700 group-hover:text-accent-600 transition-colors break-all">contact@tafely-gr.com</span>
            </a>

        </div>

        <div class="mt-10 bg-gray-50 border border-gray-100 rounded-2xl p-6 flex items-start gap-4">
            <span class="material-symbols-outlined text-gray-400 text-[22px] mt-0.5">info</span>
            <p class="font-body text-sm text-gray-600 leading-relaxed">
                Pour le paiement de votre abonnement en euros (€), ou pour toute question sur une facture, précisez-le directement dans votre message WhatsApp ou email — notre équipe vous répondra avec la procédure à suivre.
            </p>
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