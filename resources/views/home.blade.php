@extends('layouts.app')

@php
    // ---------- Données dynamiques (réglées dans les Paramètres admin) ----------
    $prixFormate = number_format($tarif['prix'], 0, ',', ' ');
    $reductionMax = (int) $tarif['reduction_annuelle'];

    $offreActive = $offre['active'];
    $places = $offre['places'];
    $restantes = $offre['restantes'];
    $mois = $offre['mois'];
    $totalMois = $offre['duree_totale'];
    $prises = max(0, $places - $restantes);
    $progression = $places > 0 ? min(100, (int) round($prises / $places * 100)) : 0;
    $restantesLabel = $restantes.' place'.($restantes > 1 ? 's' : '');
    $premiersMaj = ucfirst($offre['premiers']);          // "Les 10 premiers inscrits"
    $verbe = $offre['verbe'];                            // "reçoivent" / "reçoit"
    $moisOfferts = $mois.' mois de plan payant offert'.($mois > 1 ? 's' : '');
    $derniere = $restantes <= 3;

    // ---------- SEO ----------
    $seoTitle = 'Créer une boutique en ligne à Madagascar en 5 min — Tafely';
    $seoDescription = $offreActive
        ? "Créez votre boutique en ligne en 5 min, vendez sur WhatsApp et Facebook, 0 % commission. {$premiersMaj} {$verbe} {$moisOfferts}."
        : "Créez votre boutique en ligne en 5 min, vendez sur WhatsApp et Facebook. 30 jours gratuits, 0 % commission, paiement MVola et Orange Money.";

    // ---------- FAQ (affichée ET envoyée à Google en données structurées) ----------
    $faq = [
        [
            'q' => 'Comment créer ma boutique en ligne avec Tafely ?',
            'r' => "Cliquez sur « Créer ma boutique », saisissez votre email puis le code reçu : votre compte est prêt, sans mot de passe. Ajoutez ensuite vos produits, personnalisez votre boutique et partagez votre lien sur WhatsApp, Facebook ou par SMS. Comptez environ 5 minutes.",
        ],
        [
            'q' => 'Combien coûte Tafely ?',
            'r' => "Tafely est gratuit pendant 30 jours, sans carte bancaire (jusqu'à 10 produits). Ensuite, le plan Actif payant coûte {$prixFormate} Ar par mois (jusqu'à 30 produits), avec des réductions si vous souscrivez plusieurs mois. Aucune commission n'est prélevée sur vos ventes.",
        ],
        [
            'q' => 'Puis-je vendre sur WhatsApp et Facebook avec Tafely ?',
            'r' => "Oui. Votre boutique dispose d'un lien unique que vous partagez où vous voulez : WhatsApp, Facebook, Instagram, SMS. Vos clients choisissent leurs produits, valident leur commande et vous la recevez par email et dans votre tableau de bord.",
        ],
        [
            'q' => 'Ai-je besoin de compétences techniques ?',
            'r' => "Non. Tafely est pensé pour les commerçants pressés : vous ajoutez vos produits avec photo, prix et stock, puis vous choisissez un thème et une couleur. Il n'y a rien à installer ni à programmer.",
        ],
        [
            'q' => 'Puis-je gérer aussi ma boutique physique ?',
            'r' => "Oui. La vente en boutique physique est incluse : vous enregistrez vos ventes sur place, le stock est mis à jour automatiquement et vous pouvez générer une facture si le client la demande.",
        ],
        [
            'q' => "Comment payer l'abonnement Tafely ?",
            'r' => "Par MVola, Orange Money ou carte Visa, depuis votre tableau de bord. Vous choisissez librement le nombre de mois : plus la durée est longue, plus le prix mensuel baisse (jusqu'à -{$reductionMax} % sur 12 mois).",
        ],
        [
            'q' => 'Y a-t-il une commission sur mes ventes ?',
            'r' => "Non, aucune commission. Vous gardez 100 % de vos ventes : vous ne payez que l'abonnement, et rien pendant les 30 jours d'essai.",
        ],
    ];

    if ($offreActive) {
        array_splice($faq, 2, 0, [[
            'q' => "En quoi consiste l'offre de lancement Tafely ?",
            'r' => "{$premiersMaj} {$verbe} {$moisOfferts}, en plus de l'essai gratuit de 30 jours, une fois leur boutique validée par notre équipe. Cela représente {$totalMois} mois avec toutes les fonctions du plan payant (jusqu'à 30 produits). Il reste actuellement {$restantesLabel} sur {$places}.",
        ]]);
    }

    // ---------- Données structurées (JSON-LD) ----------
    $offresSchema = [
        ['@type' => 'Offer', 'name' => 'Essai gratuit 30 jours', 'price' => '0', 'priceCurrency' => 'MGA'],
        ['@type' => 'Offer', 'name' => 'Actif payant (par mois)', 'price' => (string) $tarif['prix'], 'priceCurrency' => 'MGA'],
    ];

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => url('/').'#organisation',
                'name' => 'Tafely',
                'url' => url('/'),
                'logo' => asset('logo.png'),
                'email' => 'contact@tafely-gr.com',
                'telephone' => '+261343236379',
                'areaServed' => 'MG',
            ],
            [
                '@type' => 'WebSite',
                '@id' => url('/').'#site',
                'url' => url('/'),
                'name' => 'Tafely',
                'inLanguage' => 'fr',
                'publisher' => ['@id' => url('/').'#organisation'],
            ],
            [
                '@type' => 'SoftwareApplication',
                'name' => 'Tafely',
                'applicationCategory' => 'BusinessApplication',
                'operatingSystem' => 'Web',
                'url' => url('/'),
                'description' => $seoDescription,
                'offers' => $offresSchema,
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => collect($faq)->map(fn ($f) => [
                    '@type' => 'Question',
                    'name' => $f['q'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['r']],
                ])->all(),
            ],
        ],
    ];
@endphp

@section('title', $seoTitle)
@section('meta_description', $seoDescription)

@push('head')
    <link rel="canonical" href="{{ url('/') }}">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="Tafely">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('hero.jpg') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ asset('hero.jpg') }}">

    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')

{{-- ============ NAV ============ --}}
<nav
    aria-label="Navigation principale"
    x-data="{ scrolled: false, menuOuvert: false }"
    x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 20)"
    @keydown.escape.window="menuOuvert = false"
    :class="(scrolled || menuOuvert) ? 'shadow-md bg-white/95' : 'shadow-sm bg-white/80'"
    class="fixed top-0 inset-x-0 z-50 backdrop-blur-md transition-all duration-200"
>
    <div class="max-w-7xl mx-auto px-5 md:px-10 h-16 md:h-20 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center shrink-0" @click="menuOuvert = false" aria-label="Tafely — accueil">
            <img src="{{ asset('logo.png') }}" alt="Tafely" class="h-8 md:h-11">
        </a>

        {{-- liens desktop --}}
        <div class="hidden md:flex items-center gap-1">
            <a href="#fonctionnement" class="px-4 py-2 rounded-full text-sm font-semibold text-gray-600 hover:text-primary-700 hover:bg-primary-50 transition-colors">Comment ça marche</a>
            <a href="#avantages" class="px-4 py-2 rounded-full text-sm font-semibold text-gray-600 hover:text-primary-700 hover:bg-primary-50 transition-colors">Fonctionnalités</a>
            @if ($offreActive)
                <a href="#offre" class="px-4 py-2 rounded-full text-sm font-bold text-accent-600 hover:bg-accent-50 transition-colors">🎁 Offre de lancement</a>
            @endif
            <a href="#tarif" class="px-4 py-2 rounded-full text-sm font-semibold text-gray-600 hover:text-primary-700 hover:bg-primary-50 transition-colors">Tarif</a>
            <a href="#faq" class="px-4 py-2 rounded-full text-sm font-semibold text-gray-600 hover:text-primary-700 hover:bg-primary-50 transition-colors">FAQ</a>
        </div>

        {{-- actions desktop --}}
        <div class="hidden md:flex items-center gap-3">
            <button type="button" @click="openAuth('login')" class="text-sm font-semibold text-gray-600 hover:text-primary-700 transition-colors">
                Se connecter
            </button>
            <button type="button" @click="openAuth('signup')"
                    class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white text-sm font-bold px-5 py-2.5 rounded-full shadow-sm shadow-accent-600/20 hover:-translate-y-0.5 active:scale-[0.98] transition-all">
                Créer ma boutique
            </button>
        </div>

        {{-- bouton burger (mobile / tablette) --}}
        <button
            type="button"
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
            <a href="#fonctionnement" @click="menuOuvert = false" class="px-4 py-3 rounded-xl text-sm font-semibold text-gray-700 hover:text-primary-700 hover:bg-primary-50 transition-colors">Comment ça marche</a>
            <a href="#avantages" @click="menuOuvert = false" class="px-4 py-3 rounded-xl text-sm font-semibold text-gray-700 hover:text-primary-700 hover:bg-primary-50 transition-colors">Fonctionnalités</a>
            @if ($offreActive)
                <a href="#offre" @click="menuOuvert = false" class="px-4 py-3 rounded-xl text-sm font-bold text-accent-600 hover:bg-accent-50 transition-colors">🎁 Offre de lancement</a>
            @endif
            <a href="#tarif" @click="menuOuvert = false" class="px-4 py-3 rounded-xl text-sm font-semibold text-gray-700 hover:text-primary-700 hover:bg-primary-50 transition-colors">Tarif</a>
            <a href="#faq" @click="menuOuvert = false" class="px-4 py-3 rounded-xl text-sm font-semibold text-gray-700 hover:text-primary-700 hover:bg-primary-50 transition-colors">FAQ</a>

            <div class="h-px bg-gray-100 my-2"></div>

            <button type="button" @click="menuOuvert = false; openAuth('login')"
                    class="px-4 py-3 rounded-xl text-sm font-semibold text-gray-700 hover:text-primary-700 hover:bg-primary-50 transition-colors text-left">
                Se connecter
            </button>
            <button type="button" @click="menuOuvert = false; openAuth('signup')"
                    class="mt-1 inline-flex items-center justify-center gap-2 bg-accent-500 hover:bg-accent-600 text-white text-sm font-bold px-5 py-3 rounded-xl shadow-sm shadow-accent-600/20 active:scale-[0.98] transition-all">
                Créer ma boutique
            </button>
        </div>
    </div>
</nav>

<main>

{{-- ============ HERO ============ --}}
<header class="relative overflow-hidden min-h-[90vh] flex items-center">
    {{-- image de fond --}}
    <div class="absolute inset-0">
        <img src="{{ asset('hero.jpg') }}" alt="" fetchpriority="high" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-primary-950/80 via-primary-950/55 to-primary-950/25"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-primary-950/40 via-transparent to-transparent"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-5 md:px-10 py-16 md:py-24 w-full">
        <div class="max-w-2xl flex flex-col items-start gap-5 md:gap-6 text-left pt-16 md:pt-20">

            @if ($offreActive)
                <a href="#offre"
                   class="inline-flex items-center gap-2 bg-accent-500 text-white text-xs font-bold tracking-wide px-4 py-2 rounded-full shadow-lg shadow-accent-900/30 hover:bg-accent-600 transition-colors">
                    <span class="material-symbols-outlined text-[16px]">redeem</span>
                    {{ $derniere ? 'Dernières places' : 'Offre de lancement' }} — plus que {{ $restantesLabel }} sur {{ $places }}
                </a>
            @else
                <span class="inline-flex items-center gap-2 bg-accent-500/15 border border-accent-400/30 text-accent-400 text-xs font-bold tracking-wide px-4 py-1.5 rounded-full">
                    <span class="material-symbols-outlined text-[16px]">stars</span>
                    Nouveau à Madagascar et à l'international
                </span>
            @endif

            <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl xl:text-[3.4rem] font-bold text-white leading-[1.1] tracking-tight">
                Votre boutique en ligne en 5 minutes.
                <span class="text-accent-400">Vos premières commandes dès aujourd'hui.</span>
            </h1>

            <p class="font-body text-base sm:text-lg text-primary-50/90 max-w-xl">
                Ajoutez vos produits, obtenez votre lien unique et partagez-le sur WhatsApp, Facebook ou par SMS.
                Vos clients commandent, vous vendez plus — <strong class="text-white">0 % de commission</strong>,
                sans aucune compétence technique.
            </p>

            @if ($offreActive)
                <div class="w-full max-w-xl bg-white/10 backdrop-blur-md border border-white/25 rounded-2xl px-5 py-4 flex items-start gap-3">
                    <span class="material-symbols-outlined text-accent-400 text-[26px] mt-0.5" style="font-variation-settings: 'FILL' 1;">redeem</span>
                    <p class="font-body text-sm sm:text-base text-white leading-snug">
                        <strong>{{ $premiersMaj }} {{ $verbe }} {{ $moisOfferts }}</strong>
                        une fois leur boutique validée — en plus des 30 jours d'essai :
                        <strong class="text-accent-400">{{ $totalMois }} mois pour vendre sans rien payer.</strong>
                    </p>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row items-center gap-3 sm:gap-4 w-full sm:w-auto">
                <button type="button" @click="openAuth('signup')"
                        class="w-full sm:w-auto px-8 py-4 bg-accent-500 hover:bg-accent-600 text-white font-bold rounded-xl shadow-lg shadow-accent-900/30 hover:shadow-xl transform hover:-translate-y-0.5 active:scale-[0.98] transition-all">
                    {{ $offreActive ? 'Je réserve ma place gratuitement' : 'Créer ma boutique gratuitement' }}
                </button>
                <a href="#fonctionnement"
                   class="w-full sm:w-auto px-8 py-4 bg-white/10 hover:bg-white/20 border border-white/30 text-white font-bold rounded-xl transition-colors text-center">
                    Voir comment ça marche
                </a>
            </div>

            <p class="font-body text-sm text-primary-100/80">
                Sans carte bancaire · 30 jours d'essai gratuit · Boutique prête en 5 minutes
            </p>
        </div>
    </div>
</header>

{{-- ============ BANDE DE CONFIANCE ============ --}}
<section aria-label="Les points forts de Tafely" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-5 md:px-10 py-6 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
        @foreach ([
            ['icone' => 'payments', 'titre' => '0 % commission', 'texte' => 'Vous gardez 100 % de vos ventes'],
            ['icone' => 'timer', 'titre' => '5 minutes', 'texte' => 'Pour lancer votre boutique'],
            ['icone' => 'redeem', 'titre' => '30 jours gratuits', 'texte' => 'Sans carte bancaire'],
            ['icone' => 'smartphone', 'titre' => 'MVola & Orange Money', 'texte' => 'Pour payer votre abonnement'],
        ] as $point)
            <div class="flex flex-col items-center gap-1">
                <span class="material-symbols-outlined text-primary-700 text-[28px]">{{ $point['icone'] }}</span>
                <p class="font-display font-bold text-sm md:text-base text-gray-900">{{ $point['titre'] }}</p>
                <p class="font-body text-xs text-gray-500">{{ $point['texte'] }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ============ 3 ÉTAPES ============ --}}
<section id="fonctionnement" aria-labelledby="titre-etapes" class="bg-white py-16 sm:py-20 md:py-28 scroll-mt-16 md:scroll-mt-20">
    <div class="max-w-7xl mx-auto px-5 md:px-10">
        <div class="text-center max-w-2xl mx-auto mb-12 md:mb-16">
            <span class="text-accent-600 font-bold text-sm uppercase tracking-wide font-body">Comment ça marche</span>
            <h2 id="titre-etapes" class="font-display text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mt-3">Lancez votre boutique en 3 étapes</h2>
            <p class="font-body text-gray-600 mt-4">Pas de site à construire, pas de mot de passe à retenir. Vous êtes en ligne avant la fin de votre café.</p>
        </div>

        <ol class="grid sm:grid-cols-2 md:grid-cols-3 gap-5 md:gap-6 relative list-none">
            <div class="hidden md:block absolute top-8 left-0 w-full h-0.5 bg-gray-100 -z-10"></div>

            @foreach ([
                ['n' => '1', 'title' => 'Créez votre compte', 'text' => 'Entrez votre email, recevez un code, et c\'est fait. Donnez un nom et une identité à votre boutique.'],
                ['n' => '2', 'title' => 'Ajoutez vos produits', 'text' => 'Photo, description, prix, stock, promotions : votre catalogue prend vie en quelques minutes.'],
                ['n' => '3', 'title' => 'Partagez votre lien', 'text' => 'Diffusez-le sur WhatsApp, Facebook ou par SMS. Les commandes arrivent directement chez vous.'],
            ] as $step)
                <li class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 text-center {{ $loop->first ? 'sm:col-span-2 md:col-span-1' : '' }}">
                    <div class="w-14 h-14 mx-auto rounded-full bg-white border-2 border-primary-700 flex items-center justify-center font-display font-bold text-xl text-primary-700 mb-5">
                        {{ $step['n'] }}
                    </div>
                    <h3 class="font-display font-bold text-lg text-gray-900 mb-2">{{ $step['title'] }}</h3>
                    <p class="font-body text-sm text-gray-600 leading-relaxed">{{ $step['text'] }}</p>
                </li>
            @endforeach
        </ol>

        <div class="text-center mt-12">
            <button type="button" @click="openAuth('signup')"
                    class="inline-flex items-center gap-2 bg-primary-800 hover:bg-primary-900 text-white font-bold px-8 py-4 rounded-xl shadow-lg shadow-primary-800/20 hover:-translate-y-0.5 active:scale-[0.98] transition-all">
                Créer ma boutique maintenant
                <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
            </button>
        </div>
    </div>
</section>

{{-- ============ FONCTIONNALITÉS ============ --}}
<section id="avantages" aria-labelledby="titre-avantages" class="bg-gray-50 py-16 sm:py-20 md:py-28 scroll-mt-16 md:scroll-mt-20">
    <div class="max-w-7xl mx-auto px-5 md:px-10">
        <div class="text-center max-w-2xl mx-auto mb-12 md:mb-16">
            <span class="text-accent-600 font-bold text-sm uppercase tracking-wide font-body">Fonctionnalités</span>
            <h2 id="titre-avantages" class="font-display text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mt-3">Tout ce qu'il faut pour vendre plus, dès le premier jour</h2>
            <p class="font-body text-gray-600 mt-4">Une seule plateforme pour votre boutique en ligne et votre boutique physique.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6">
            @foreach ([
                ['icone' => 'share', 'titre' => 'Un lien, votre boutique', 'texte' => 'Partagez votre vitrine sur WhatsApp, Facebook, Instagram ou par SMS. Vos clients commandent en quelques clics, sans créer de compte.'],
                ['icone' => 'shopping_cart_checkout', 'titre' => 'Commandes et reçus automatiques', 'texte' => 'Chaque commande vous arrive par email et dans votre tableau de bord. Votre client reçoit son reçu PDF immédiatement.'],
                ['icone' => 'point_of_sale', 'titre' => 'Vente en boutique physique', 'texte' => 'Enregistrez vos ventes sur place, le stock se met à jour tout seul, et la facture est prête si le client la demande.'],
                ['icone' => 'sell', 'titre' => 'Promotions et livraison', 'texte' => 'Remises en pourcentage ou en montant, frais de livraison par produit : attirez plus de clients et fixez vos conditions.'],
                ['icone' => 'monitoring', 'titre' => 'Votre activité en un coup d\'œil', 'texte' => 'Chiffre d\'affaires du jour, du mois, commandes à traiter, stock : gardez le contrôle sans tableur.'],
                ['icone' => 'palette', 'titre' => 'Une boutique à votre image', 'texte' => 'Logo, couleurs, thème et texte de présentation : votre vitrine inspire confiance et se démarque.'],
            ] as $avantage)
                <article class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    <div class="h-12 w-12 rounded-xl bg-primary-50 flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-primary-700 text-[26px]">{{ $avantage['icone'] }}</span>
                    </div>
                    <h3 class="font-display font-bold text-lg text-gray-900 mb-2">{{ $avantage['titre'] }}</h3>
                    <p class="font-body text-sm text-gray-600 leading-relaxed">{{ $avantage['texte'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ OFFRE DE LANCEMENT (dynamique) ============ --}}
@if ($offreActive)
<section id="offre" aria-labelledby="titre-offre" class="relative overflow-hidden bg-primary-950 py-16 sm:py-20 md:py-24 scroll-mt-16 md:scroll-mt-20">
    <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full bg-accent-500/20 blur-3xl"></div>
    <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-primary-600/30 blur-3xl"></div>

    <div class="relative max-w-4xl mx-auto px-5 md:px-10 text-center">
        <span class="inline-flex items-center gap-2 bg-accent-500 text-white text-xs font-bold uppercase tracking-wide px-4 py-1.5 rounded-full">
            <span class="material-symbols-outlined text-[16px]">redeem</span>
            {{ $derniere ? 'Dernières places' : 'Offre de lancement' }}
        </span>

        <h2 id="titre-offre" class="font-display text-2xl sm:text-3xl md:text-4xl font-bold text-white mt-5 leading-tight">
            {{ $premiersMaj }} {{ $verbe }}
            <span class="text-accent-400">{{ $moisOfferts }}</span>
        </h2>

        <p class="font-body text-primary-100/90 mt-4 max-w-2xl mx-auto">
            Créez votre boutique et ajoutez vos produits : dès que notre équipe la valide, vous passez en plan payant gratuitement.
            Avec les 30 jours d'essai, cela fait <strong class="text-white">{{ $totalMois }} mois complets</strong>,
            jusqu'à 30 produits et toutes les fonctions du plan à {{ $prixFormate }} Ar/mois.
        </p>

        {{-- compteur de places --}}
        <div class="mt-8 max-w-xl mx-auto bg-white/10 border border-white/20 rounded-2xl p-5 backdrop-blur-sm">
            <div class="flex items-center justify-between font-body text-sm text-white mb-3">
                <span>{{ $prises }} inscrit{{ $prises > 1 ? 's' : '' }} sur {{ $places }}</span>
                <span class="font-bold text-accent-400">Plus que {{ $restantesLabel }}</span>
            </div>
            <div class="h-3 rounded-full bg-white/15 overflow-hidden" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $places }}" aria-valuenow="{{ $prises }}" aria-label="Places de l'offre de lancement déjà prises">
                <div class="h-full rounded-full bg-gradient-to-r from-accent-500 to-accent-400" style="width: {{ $progression }}%"></div>
            </div>
        </div>

        {{-- étapes de l'offre --}}
        <ol class="mt-10 grid sm:grid-cols-3 gap-4 text-left list-none">
            @foreach ([
                ['n' => '1', 'titre' => 'Inscrivez-vous', 'texte' => 'Créez votre compte en 30 secondes avec votre email.'],
                ['n' => '2', 'titre' => 'Créez votre boutique', 'texte' => 'Ajoutez votre nom, votre logo et vos produits.'],
                ['n' => '3', 'titre' => 'Recevez votre bonus', 'texte' => "Une fois validée, votre boutique passe en plan payant : {$moisOfferts}."],
            ] as $etape)
                <li class="bg-white/10 border border-white/15 rounded-2xl p-5">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-accent-500 text-white font-display font-bold text-sm mb-3">{{ $etape['n'] }}</span>
                    <h3 class="font-display font-bold text-white">{{ $etape['titre'] }}</h3>
                    <p class="font-body text-sm text-primary-100/80 mt-1">{{ $etape['texte'] }}</p>
                </li>
            @endforeach
        </ol>

        <button type="button" @click="openAuth('signup')"
                class="mt-10 inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-bold px-8 py-4 rounded-xl shadow-lg shadow-accent-900/40 hover:-translate-y-0.5 active:scale-[0.98] transition-all">
            Je profite de l'offre
            <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
        </button>
        <p class="font-body text-xs text-primary-100/60 mt-3">Sans carte bancaire · Offre limitée aux premières boutiques validées</p>
    </div>
</section>
@endif

{{-- ============ TARIF ============ --}}
<section id="tarif" aria-labelledby="titre-tarif" class="bg-white py-16 sm:py-20 md:py-28 scroll-mt-16 md:scroll-mt-20">
    <div class="max-w-3xl mx-auto px-5 md:px-10 text-center">
        <span class="text-accent-600 font-bold text-sm uppercase tracking-wide font-body">Tarif</span>
        <h2 id="titre-tarif" class="font-display text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mt-3 mb-3">Un tarif simple, sans mauvaise surprise</h2>
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
                    <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Commandes par email et tableau de bord</li>
                </ul>
                <button type="button" @click="openAuth('signup')"
                        class="w-full bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold py-3.5 rounded-full transition-colors">
                    Commencer gratuitement
                </button>
            </div>

            {{-- plan payant --}}
            <div class="relative bg-white rounded-3xl shadow-xl border-2 border-primary-600 p-8 flex flex-col">
                <span class="absolute -top-4 left-1/2 -translate-x-1/2 bg-accent-500 text-white text-xs font-bold uppercase tracking-wide px-4 py-1.5 rounded-full whitespace-nowrap">
                    {{ $offreActive ? $mois.' mois offert'.($mois > 1 ? 's' : '').' aux '.$places.' premiers' : 'Offre boutique' }}
                </span>
                <h3 class="font-display text-lg font-bold text-gray-900 mt-2">Actif payant</h3>
                <p class="font-body text-sm text-gray-500 mt-1 mb-4">Pour les boutiques qui vendent sérieusement.</p>
                <div class="mb-6">
                    <span class="font-display text-3xl font-bold text-primary-800">{{ $prixFormate }} Ar</span>
                    <span class="font-body text-sm text-gray-400"> / mois, sans engagement</span>
                    @if ($reductionMax > 0)
                        <p class="font-body text-xs text-green-600 font-semibold mt-1">Jusqu'à -{{ $reductionMax }} % en souscrivant 12 mois</p>
                    @endif
                </div>
                <ul class="space-y-3 mb-8 text-gray-700 text-sm font-body flex-1">
                    <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Jusqu'à 30 produits</li>
                    <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Paiement MVola, Orange Money et Visa</li>
                    <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Plusieurs thèmes et couleurs</li>
                    <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Vente en boutique physique incluse</li>
                    <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Support prioritaire</li>
                </ul>
                <button type="button" @click="openAuth('signup')"
                        class="w-full bg-primary-800 hover:bg-primary-900 text-white font-bold py-3.5 rounded-full transition shadow-lg shadow-primary-800/20 hover:-translate-y-0.5 active:scale-[0.98]">
                    {{ $offreActive ? 'Je réserve mon bonus' : 'Commencer maintenant' }}
                </button>
            </div>
        </div>

        <p class="font-body text-xs text-gray-400 mt-6">
            Besoin de plus de place ? Ajoutez des emplacements produits par 5, 10, 15... depuis votre tableau de bord.
        </p>
    </div>
</section>

{{-- ============ FAQ ============ --}}
<section id="faq" aria-labelledby="titre-faq" class="bg-gray-50 py-16 sm:py-20 md:py-28 scroll-mt-16 md:scroll-mt-20">
    <div class="max-w-3xl mx-auto px-5 md:px-10">
        <div class="text-center mb-10 md:mb-12">
            <span class="text-accent-600 font-bold text-sm uppercase tracking-wide font-body">Questions fréquentes</span>
            <h2 id="titre-faq" class="font-display text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mt-3">Tout ce que vous voulez savoir avant de vous lancer</h2>
        </div>

        <div class="flex flex-col gap-3">
            @foreach ($faq as $item)
                <details class="group bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" @if ($loop->first) open @endif>
                    <summary class="flex items-center justify-between gap-4 px-5 py-4 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                        <h3 class="font-body font-semibold text-sm md:text-base text-primary-900">{{ $item['q'] }}</h3>
                        <span class="material-symbols-outlined text-gray-400 shrink-0 transition-transform group-open:rotate-180">expand_more</span>
                    </summary>
                    <p class="px-5 pb-5 font-body text-sm text-gray-600 leading-relaxed">{{ $item['r'] }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ APPEL FINAL ============ --}}
<section aria-labelledby="titre-final" class="bg-gradient-to-br from-primary-800 to-primary-950 py-16 md:py-24">
    <div class="max-w-3xl mx-auto px-5 md:px-10 text-center">
        <h2 id="titre-final" class="font-display text-2xl sm:text-3xl md:text-4xl font-bold text-white leading-tight">
            Vos clients vous cherchent. Donnez-leur une boutique où commander.
        </h2>
        <p class="font-body text-primary-100/90 mt-4">
            @if ($offreActive)
                Plus que <strong class="text-accent-400">{{ $restantesLabel }}</strong> pour recevoir {{ $moisOfferts }}. Créez votre boutique en 5 minutes.
            @else
                Créez votre boutique en 5 minutes et profitez de 30 jours d'essai gratuit, sans carte bancaire.
            @endif
        </p>
        <button type="button" @click="openAuth('signup')"
                class="mt-8 inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-bold px-10 py-4 rounded-xl shadow-lg shadow-accent-900/40 hover:-translate-y-0.5 active:scale-[0.98] transition-all">
            {{ $offreActive ? 'Je profite de l\'offre' : 'Créer ma boutique gratuitement' }}
            <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
        </button>
    </div>
</section>

</main>

{{-- ============ FOOTER ============ --}}
<footer class="bg-white border-t border-gray-100 py-10">
    <div class="max-w-7xl mx-auto px-5 md:px-10 flex flex-col md:flex-row items-center justify-between gap-6 text-center md:text-left">
        <div class="flex items-center gap-3">
            <div class="bg-white px-2 py-1 rounded-md">
                <img src="{{ asset('logo.png') }}" alt="Tafely" class="h-9" loading="lazy">
            </div>
            <span class="font-body text-sm text-gray-400">© {{ date('Y') }} Tafely. Propulsons le commerce en ligne.</span>
        </div>
        <nav aria-label="Pied de page" class="flex gap-6 font-body text-sm text-gray-500">
            <a href="{{ route('aide') }}" class="hover:text-accent-600 transition-colors">Aide</a>
            <a href="{{ route('confidentialite') }}" class="hover:text-accent-600 transition-colors">Confidentialité</a>
            <a href="{{ route('contact') }}" class="hover:text-accent-600 transition-colors">Contact</a>
        </nav>
    </div>
</footer>

@endsection