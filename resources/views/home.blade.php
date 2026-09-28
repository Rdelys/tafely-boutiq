@extends('layouts.app')

@section('title', 'Tafely — Créez votre boutique en ligne en un clic')

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
        <a href="#" class="flex items-center shrink-0" @click="menuOuvert = false">
            <img src="{{ asset('logo.png') }}" alt="Tafely" class="h-8 md:h-11">
        </a>

        {{-- liens desktop --}}
        <div class="hidden md:flex items-center gap-1">
            <a href="#" class="px-4 py-2 rounded-full text-sm font-semibold text-gray-600 hover:text-primary-700 hover:bg-primary-50 transition-colors">Accueil</a>
            <a href="#fonctionnement" class="px-4 py-2 rounded-full text-sm font-semibold text-gray-600 hover:text-primary-700 hover:bg-primary-50 transition-colors">Comment ça marche</a>
            <a href="#tarif" class="px-4 py-2 rounded-full text-sm font-semibold text-gray-600 hover:text-primary-700 hover:bg-primary-50 transition-colors">Tarif</a>
        </div>

        {{-- actions desktop --}}
        <div class="hidden md:flex items-center gap-3">
            <button @click="openAuth('login')" class="text-sm font-semibold text-gray-600 hover:text-primary-700 transition-colors">
                Se connecter
            </button>
            <button @click="openAuth('signup')"
                    class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white text-sm font-bold px-5 py-2.5 rounded-full shadow-sm shadow-accent-600/20 hover:-translate-y-0.5 active:scale-[0.98] transition-all">
                Créer ma boutique
            </button>
        </div>

        {{-- bouton burger (mobile / tablette) --}}
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

    {{-- menu déroulant (mobile / tablette) --}}
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
            <a href="#" @click="menuOuvert = false" class="px-4 py-3 rounded-xl text-sm font-semibold text-gray-700 hover:text-primary-700 hover:bg-primary-50 transition-colors">Accueil</a>
            <a href="#fonctionnement" @click="menuOuvert = false" class="px-4 py-3 rounded-xl text-sm font-semibold text-gray-700 hover:text-primary-700 hover:bg-primary-50 transition-colors">Comment ça marche</a>
            <a href="#tarif" @click="menuOuvert = false" class="px-4 py-3 rounded-xl text-sm font-semibold text-gray-700 hover:text-primary-700 hover:bg-primary-50 transition-colors">Tarif</a>

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

{{-- ============ HERO ============ --}}
<header class="relative overflow-hidden min-h-[90vh] flex items-center">
    {{-- image de fond --}}
    <div class="absolute inset-0">
        <img src="{{ asset('hero.jpg') }}" alt="" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-primary-950/75 via-primary-950/45 to-primary-950/20"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-primary-950/40 via-transparent to-transparent"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-5 md:px-10 py-16 md:py-24 w-full">
        <div class="max-w-2xl flex flex-col items-start gap-5 md:gap-6 text-left pt-16 md:pt-20">
            <span class="inline-flex items-center gap-2 bg-accent-500/15 border border-accent-400/30 text-accent-300 text-xs font-bold tracking-wide px-4 py-1.5 rounded-full">
                <span class="material-symbols-outlined text-[16px]">stars</span>
                Nouveau à Madagascar et à l'international
            </span>

            <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl xl:text-[3.4rem] font-bold text-white leading-[1.1] tracking-tight">
                Créez votre boutique en ligne <span class="text-accent-400">simplement</span>.
            </h1>

            <p class="font-body text-base sm:text-lg text-primary-50/90 max-w-xl">
                Configurez votre boutique, ajoutez vos produits et obtenez un lien
                unique à partager avec vos clients — sur Facebook, WhatsApp ou
                partout ailleurs. Sans compétence technique.
            </p>

            <div class="flex flex-col sm:flex-row items-center gap-3 sm:gap-4 w-full sm:w-auto">
                <button @click="openAuth('signup')"
                        class="w-full sm:w-auto px-8 py-4 bg-accent-500 hover:bg-accent-600 text-white font-bold rounded-xl shadow-lg shadow-accent-900/30 hover:shadow-xl transform hover:-translate-y-0.5 active:scale-[0.98] transition-all">
                    Créer ma boutique
                </button>
                <a href="#fonctionnement"
                   class="w-full sm:w-auto px-8 py-4 bg-white/10 hover:bg-white/20 border border-white/30 text-white font-bold rounded-xl transition-colors text-center">
                    Voir comment ça marche
                </a>
            </div>

            <p class="font-body text-sm text-primary-100/70">
                * Sans carte bancaire. 30 jours d'essai gratuit, boutique prête en 5 minutes.
            </p>
        </div>
    </div>
</header>

{{-- ============ 3 ÉTAPES ============ --}}
<section id="fonctionnement" class="bg-white py-16 sm:py-20 md:py-28 scroll-mt-16 md:scroll-mt-20">
    <div class="max-w-7xl mx-auto px-5 md:px-10">
        <div class="text-center max-w-2xl mx-auto mb-12 md:mb-16">
            <span class="text-accent-600 font-bold text-sm uppercase tracking-wide font-body">Comment ça marche</span>
            <h2 class="font-display text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mt-3">Lancez-vous en 3 étapes</h2>
            <p class="font-body text-gray-600 mt-4">Un processus pensé pour les vendeurs pressés. Aucune compétence technique requise.</p>
        </div>

        <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-5 md:gap-6 relative">
            <div class="hidden md:block absolute top-8 left-0 w-full h-0.5 bg-gray-100 -z-10"></div>

            @foreach([
                ['n' => '1', 'title' => 'Créez votre compte', 'text' => 'Inscrivez-vous avec votre email, sans mot de passe. Configurez le nom et l\'identité de votre boutique.'],
                ['n' => '2', 'title' => 'Ajoutez vos produits', 'text' => 'Photos, description, prix : votre catalogue prend vie en quelques minutes.'],
                ['n' => '3', 'title' => 'Partagez votre lien', 'text' => 'Un lien unique à diffuser sur Facebook, WhatsApp ou par SMS. Vous recevez les commandes directement.'],
            ] as $step)
            <div class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 text-center {{ $loop->first && $loop->count === 3 ? 'sm:col-span-2 md:col-span-1' : '' }}">
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
<section id="tarif" class="bg-gray-50 py-16 sm:py-20 md:py-28 scroll-mt-16 md:scroll-mt-20">
    <div class="max-w-3xl mx-auto px-5 md:px-10 text-center">
        <span class="text-accent-600 font-bold text-sm uppercase tracking-wide font-body">Tarif</span>
        <h2 class="font-display text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mt-3 mb-3">Un tarif simple et transparent</h2>
        <p class="font-body text-gray-600 mb-4">Aucune commission sur vos ventes. Testez gratuitement avant de payer quoi que ce soit.</p>

        <span class="inline-flex items-center gap-2 bg-primary-50 border border-primary-100 text-primary-700 text-xs font-bold px-4 py-2 rounded-full mb-10 md:mb-12">
            <span class="material-symbols-outlined text-[16px]">verified</span>
            30 jours d'essai gratuit, sans carte bancaire
        </span>

        <div class="grid sm:grid-cols-2 gap-5 text-left">
            {{-- plan gratuit --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 flex flex-col">
                <h3 class="font-display text-lg font-bold text-gray-900">Essai gratuit</h3>
                <p class="font-body text-sm text-gray-500 mt-1 mb-4">Idéal pour démarrer et tester votre boutique.</p>
                <div class="mb-6">
                    <span class="font-display text-3xl font-bold text-gray-900">0 Ar</span>
                    <span class="font-body text-sm text-gray-400"> pendant 30 jours</span>
                </div>
                <ul class="space-y-3 mb-8 text-gray-700 text-sm font-body flex-1">
                    <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Jusqu'à 10 produits</li>
                    <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Boutique en ligne personnalisable</li>
                    <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Lien de partage unique</li>
                </ul>
                <button @click="openAuth('signup')"
                        class="w-full bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold py-3.5 rounded-full transition-colors">
                    Commencer gratuitement
                </button>
            </div>

            {{-- plan payant --}}
            <div class="relative bg-white rounded-3xl shadow-xl border-2 border-primary-600 p-8 flex flex-col">
                <span class="absolute -top-4 left-1/2 -translate-x-1/2 bg-accent-500 text-white text-xs font-bold uppercase tracking-wide px-4 py-1.5 rounded-full whitespace-nowrap">
                    Offre boutique
                </span>
                <h3 class="font-display text-lg font-bold text-gray-900 mt-2">Actif payant</h3>
                <p class="font-body text-sm text-gray-500 mt-1 mb-4">Pour les boutiques qui vendent sérieusement.</p>
                <div class="mb-6">
                    <span class="font-display text-3xl font-bold text-primary-800">20 000 Ar</span>
                    <span class="font-body text-sm text-gray-400"> / mois, sans engagement</span>
                </div>
                <ul class="space-y-3 mb-8 text-gray-700 text-sm font-body flex-1">
                    <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Jusqu'à 30 produits</li>
                    <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Paiement MVola et Orange Money</li>
                    <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Plusieurs thèmes disponibles</li>
                    <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Vente en boutique physique incluse</li>
                </ul>
                <button @click="openAuth('signup')"
                        class="w-full bg-primary-800 hover:bg-primary-900 text-white font-bold py-3.5 rounded-full transition shadow-lg shadow-primary-800/20 hover:-translate-y-0.5 active:scale-[0.98]">
                    Commencer maintenant
                </button>
            </div>
        </div>

        <p class="font-body text-xs text-gray-400 mt-6">
            Besoin de plus de place ? Un pack de +10 produits est disponible à 5 000 Ar depuis votre tableau de bord.
        </p>
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