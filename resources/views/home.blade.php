@extends('layouts.app')

@section('title', 'Tafely — Créez votre boutique en ligne en un clic')

@section('content')

{{-- ============ NAV ============ --}}
<nav
    x-data="{ scrolled: false }"
    x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 20)"
    :class="scrolled ? 'shadow-md bg-white/95' : 'shadow-sm bg-white/80'"
    class="fixed top-0 inset-x-0 z-50 backdrop-blur-md transition-all duration-200"
>
    <div class="max-w-7xl mx-auto px-5 md:px-10 h-20 flex items-center justify-between">
        <a href="#" class="flex items-center">
            <img src="{{ asset('logo.png') }}" alt="Tafely" class="h-11">
        </a>

        <div class="hidden md:flex items-center gap-1">
            <a href="#" class="px-4 py-2 rounded-full text-sm font-semibold text-gray-600 hover:text-primary-700 hover:bg-primary-50 transition-colors">Accueil</a>
            <a href="#fonctionnement" class="px-4 py-2 rounded-full text-sm font-semibold text-gray-600 hover:text-primary-700 hover:bg-primary-50 transition-colors">Comment ça marche</a>
            <a href="#tarif" class="px-4 py-2 rounded-full text-sm font-semibold text-gray-600 hover:text-primary-700 hover:bg-primary-50 transition-colors">Tarif</a>
        </div>

        <div class="flex items-center gap-3">
            <button @click="authModalOpen = true" class="hidden sm:inline-flex text-sm font-semibold text-gray-600 hover:text-primary-700 transition-colors">
                Se connecter
            </button>
            <button @click="authModalOpen = true"
                    class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white text-sm font-bold px-5 py-2.5 rounded-full shadow-sm shadow-accent-600/20 hover:-translate-y-0.5 transition-all">
                Créer ma boutique
            </button>
        </div>
    </div>
</nav>

{{-- ============ HERO ============ --}}
<header class="relative overflow-hidden min-h-[90vh] flex items-center">
    {{-- image de fond --}}
    <div class="absolute inset-0">
        <img src="{{ asset('hero.jpg') }}" alt="" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-primary-950/75 via-primary-950/45 to-primary-950/20"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-primary-950/40 via-transparent to-transparent"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-5 md:px-10 py-24 w-full">
        <div class="max-w-2xl flex flex-col items-start gap-6 text-left pt-20">
            <span class="inline-flex items-center gap-2 bg-accent-500/15 border border-accent-400/30 text-accent-300 text-xs font-bold tracking-wide px-4 py-1.5 rounded-full">
                <span class="material-symbols-outlined text-[16px]">stars</span>
                Nouveau à Madagascar et à l'international
            </span>

            <h1 class="font-display text-4xl sm:text-5xl lg:text-[3.4rem] font-bold text-white leading-[1.1] tracking-tight">
                Créez votre boutique en ligne <span class="text-accent-400">simplement</span>.
            </h1>

            <p class="font-body text-lg text-primary-50/90 max-w-xl">
                Configurez votre boutique, ajoutez vos produits et obtenez un lien
                unique à partager avec vos clients — sur Facebook, WhatsApp ou
                partout ailleurs. Sans compétence technique.
            </p>

            <div class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
                <button @click="authModalOpen = true"
                        class="w-full sm:w-auto px-8 py-4 bg-accent-500 hover:bg-accent-600 text-white font-bold rounded-xl shadow-lg shadow-accent-900/30 hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                    Créer ma boutique
                </button>
                <a href="#fonctionnement"
                   class="w-full sm:w-auto px-8 py-4 bg-white/10 hover:bg-white/20 border border-white/30 text-white font-bold rounded-xl transition-colors text-center">
                    Voir comment ça marche
                </a>
            </div>

            <p class="font-body text-sm text-primary-100/70">
                * Sans carte bancaire. Boutique prête en 5 minutes.
            </p>
        </div>
    </div>
</header>

{{-- ============ 3 ÉTAPES ============ --}}
<section id="fonctionnement" class="bg-white py-20 md:py-28">
    <div class="max-w-7xl mx-auto px-5 md:px-10">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="text-accent-600 font-bold text-sm uppercase tracking-wide font-body">Comment ça marche</span>
            <h2 class="font-display text-3xl md:text-4xl font-bold text-gray-900 mt-3">Lancez-vous en 3 étapes</h2>
            <p class="font-body text-gray-600 mt-4">Un processus pensé pour les vendeurs pressés. Aucune compétence technique requise.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6 relative">
            <div class="hidden md:block absolute top-8 left-0 w-full h-0.5 bg-gray-100 -z-10"></div>

            @foreach([
                ['n' => '1', 'title' => 'Créez votre compte', 'text' => 'Inscrivez-vous avec votre email, sans mot de passe. Configurez le nom et l\'identité de votre boutique.'],
                ['n' => '2', 'title' => 'Ajoutez vos produits', 'text' => 'Photos, description, prix : votre catalogue prend vie en quelques minutes.'],
                ['n' => '3', 'title' => 'Partagez votre lien', 'text' => 'Un lien unique à diffuser sur Facebook, WhatsApp ou par SMS. Vous recevez les commandes directement.'],
            ] as $step)
            <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 text-center">
                <div class="w-14 h-14 mx-auto rounded-full bg-white border-2 border-primary-700 flex items-center justify-center font-display font-bold text-xl text-primary-700 mb-5">
                    {{ $step['n'] }}
                </div>
                <h3 class="font-display font-bold text-lg text-gray-900 mb-2">{{ $step['title'] }}</h3>
                <p class="font-body text-sm text-gray-600 leading-relaxed">{{ $step['text'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ TARIF ============ --}}
<section id="tarif" class="bg-gray-50 py-20 md:py-28">
    <div class="max-w-3xl mx-auto px-5 md:px-10 text-center">
        <span class="text-accent-600 font-bold text-sm uppercase tracking-wide font-body">Tarif</span>
        <h2 class="font-display text-3xl md:text-4xl font-bold text-gray-900 mt-3 mb-3">Un tarif simple et transparent</h2>
        <p class="font-body text-gray-600 mb-12">Le prix s'adapte automatiquement à votre localisation. Aucune commission sur vos ventes.</p>

        <div class="relative bg-white rounded-3xl shadow-xl border border-gray-100 p-10 md:p-12 max-w-md mx-auto">
            <span class="absolute -top-4 left-1/2 -translate-x-1/2 bg-accent-500 text-white text-xs font-bold uppercase tracking-wide px-4 py-1.5 rounded-full">
                Offre boutique
            </span>

            <div class="font-display text-5xl md:text-6xl font-bold text-primary-800 mb-2 mt-3">20 000 Ar</div>
            <p class="font-body text-gray-500 mb-8">par mois, sans engagement</p>

            <ul class="text-left space-y-3 mb-8 text-gray-700 text-sm font-body">
                <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Boutique en ligne personnalisable</li>
                <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Produits illimités</li>
                <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Lien de partage unique</li>
                <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Plusieurs thèmes disponibles</li>
                <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Paiement MVOLA et Orange Money</li>
            </ul>

            <button @click="authModalOpen = true"
                    class="w-full bg-primary-800 hover:bg-primary-900 text-white font-bold py-4 rounded-full transition shadow-lg shadow-primary-800/20 hover:-translate-y-0.5">
                Commencer maintenant
            </button>
        </div>
    </div>
</section>

{{-- ============ FOOTER ============ --}}
<footer class="bg-white border-t border-gray-100 py-10">
    <div class="max-w-7xl mx-auto px-5 md:px-10 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-3">
            <div class="bg-white px-2 py-1 rounded-md">
                <img src="{{ asset('logo.png') }}" alt="Tafely" class="h-9">
            </div>
            <span class="font-body text-sm text-gray-400">© {{ date('Y') }} Tafely. Propulsons le commerce en ligne.</span>
        </div>
        <div class="flex gap-6 font-body text-sm text-gray-500">
            <a href="#" class="hover:text-accent-600 transition-colors">Aide</a>
            <a href="#" class="hover:text-accent-600 transition-colors">Confidentialité</a>
            <a href="#" class="hover:text-accent-600 transition-colors">Contact</a>
        </div>
    </div>
</footer>

@endsection