@extends('layouts.app')

@php
    // ---------- Données dynamiques (réglées dans les Paramètres admin) ----------
    $prixFormate = number_format($tarif['prix'], 0, ',', ' ');
    $prixAffiche = $tarif['prix_affiche'];          // "20 000 Ar", "4,00 €" ou "$4.35"
    $estEtranger = $tarif['pays'] !== 'MG';          // France (€) ou autre pays ($)
    $estFrance = $tarif['pays'] !== 'MG'; 
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

    // Anneau de progression (SVG) : circonférence d'un cercle de rayon 54.
    $circonference = round(2 * M_PI * 54, 1);
    $decalageAnneau = round($circonference * (1 - $progression / 100), 1);

    // Nom de domaine affiché dans l'aperçu du lien de boutique.
    $hote = parse_url(url('/'), PHP_URL_HOST) ?: 'tafely.mg';

    // Photos facultatives : si le fichier existe dans public/images, il est utilisé.
    $photo = fn (string $nom) => file_exists(public_path('images/'.$nom)) ? asset('images/'.$nom) : null;

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
            'r' => "Tafely est gratuit pendant 30 jours, sans carte bancaire (jusqu'à 10 produits). Ensuite, le plan Actif payant coûte {$prixAffiche} par mois (jusqu'à 30 produits), avec des réductions si vous souscrivez plusieurs mois. Aucune commission n'est prélevée sur vos ventes.",
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

    {{-- Active les animations d'apparition uniquement si JavaScript tourne. --}}
    <script>document.documentElement.classList.add('js');</script>

    <style>
        /* ---------- Apparition au scroll ---------- */
        .js .reveal { opacity: 0; transform: translateY(28px); transition: opacity .8s cubic-bezier(.22,1,.36,1), transform .8s cubic-bezier(.22,1,.36,1); transition-delay: var(--d, 0s); }
        .js .reveal[data-r="left"] { transform: translateX(-36px); }
        .js .reveal[data-r="right"] { transform: translateX(36px); }
        .js .reveal[data-r="scale"] { transform: scale(.92); }
        .js .reveal.is-visible { opacity: 1; transform: none; }

        /* ---------- Flottement ---------- */
        @keyframes float-y { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-14px); } }
        @keyframes float-y-rev { 0%,100% { transform: translateY(-8px); } 50% { transform: translateY(10px); } }
        .anim-float { animation: float-y 6s ease-in-out infinite; }
        .anim-float-2 { animation: float-y-rev 7s ease-in-out infinite; }
        .anim-float-3 { animation: float-y 8s ease-in-out infinite; animation-delay: -2.5s; }

        /* ---------- Bandeau défilant ---------- */
        @keyframes marquee { to { transform: translateX(-50%); } }
        .marquee-track { display: flex; width: max-content; animation: marquee 38s linear infinite; }
        .marquee:hover .marquee-track { animation-play-state: paused; }

        /* ---------- Formes de fond ---------- */
        @keyframes blob { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(30px,-40px) scale(1.1); } 66% { transform: translate(-25px,20px) scale(.95); } }
        .anim-blob { animation: blob 16s ease-in-out infinite; }
        .bg-dots { background-image: radial-gradient(rgba(255,255,255,.14) 1px, transparent 1px); background-size: 26px 26px; }

        /* ---------- Reflet sur les boutons ---------- */
        @keyframes shine { to { transform: translateX(420%) skewX(-20deg); } }
        .btn-shine { position: relative; overflow: hidden; }
        .btn-shine::after { content: ""; position: absolute; top: 0; bottom: 0; left: -60%; width: 40%; background: linear-gradient(90deg, transparent, rgba(255,255,255,.5), transparent); transform: skewX(-20deg); animation: shine 3.4s ease-in-out infinite; }

        /* ---------- Pulsation ---------- */
        @keyframes pulse-ring { 0% { box-shadow: 0 0 0 0 rgba(239,68,68,.55); } 70% { box-shadow: 0 0 0 14px rgba(239,68,68,0); } 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0); } }
        .anim-pulse-ring { animation: pulse-ring 2.2s infinite; }

        /* ---------- Cadeau + étincelles ---------- */
        @keyframes gift-lid { 0%,100% { transform: translateY(0) rotate(0); } 50% { transform: translateY(-10px) rotate(-7deg); } }
        .gift-lid { transform-box: fill-box; transform-origin: left bottom; animation: gift-lid 2.4s ease-in-out infinite; }
        @keyframes twinkle { 0%,100% { opacity: .15; transform: scale(.6); } 50% { opacity: 1; transform: scale(1.15); } }
        .twinkle { transform-box: fill-box; transform-origin: center; animation: twinkle 2s ease-in-out infinite; }

        /* ---------- Ligne pointillée animée ---------- */
        @keyframes dash-move { to { stroke-dashoffset: -24; } }
        .anim-dash { animation: dash-move 1.4s linear infinite; }

        /* ---------- Graphiques qui se dessinent ---------- */
        .js .bar { transform-origin: bottom; transform: scaleY(0); transition: transform 1.1s cubic-bezier(.22,1,.36,1); transition-delay: var(--d, 0s); }
        .js .is-visible .bar { transform: scaleY(1); }
        .js .draw { stroke-dasharray: 320; stroke-dashoffset: 320; transition: stroke-dashoffset 2s ease .3s; }
        .js .is-visible .draw { stroke-dashoffset: 0; }
        .js .ring { stroke-dashoffset: {{ $circonference }}; transition: stroke-dashoffset 1.8s cubic-bezier(.22,1,.36,1) .2s; }
        .js .is-visible .ring { stroke-dashoffset: var(--target); }

        /* ---------- Lignes de commande qui arrivent ---------- */
        .js .order-row { opacity: 0; transform: translateX(-18px); transition: opacity .6s ease, transform .6s cubic-bezier(.22,1,.36,1); transition-delay: var(--d, 0s); }
        .js .is-visible .order-row { opacity: 1; transform: none; }

        /* ---------- Respect de « réduire les animations » ---------- */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
            .js .reveal { opacity: 1; transform: none; }
            .js .bar, .js .order-row { opacity: 1; transform: none; }
            .js .draw { stroke-dashoffset: 0; }
            .js .ring { stroke-dashoffset: var(--target); }
        }
    </style>
@endpush

@section('content')

{{-- barre de progression de lecture --}}
<div id="scroll-progress" class="fixed top-0 left-0 h-1 w-0 bg-gradient-to-r from-accent-500 to-accent-400 z-[60]"></div>

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
                    class="btn-shine inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white text-sm font-bold px-5 py-2.5 rounded-full shadow-sm shadow-accent-600/20 hover:-translate-y-0.5 active:scale-[0.98] transition-all">
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
<header class="relative overflow-hidden min-h-screen flex items-center bg-primary-950">
    <div class="absolute inset-0">
        <img src="{{ asset('hero.jpg') }}" alt="" fetchpriority="high" class="w-full h-full object-cover opacity-50">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-950/95 via-primary-900/80 to-primary-800/50"></div>
        <div class="absolute inset-0 bg-dots"></div>
        <div class="anim-blob absolute -top-24 -left-24 h-96 w-96 rounded-full bg-accent-500/25 blur-3xl"></div>
        <div class="anim-blob absolute bottom-0 right-0 h-[28rem] w-[28rem] rounded-full bg-primary-500/30 blur-3xl" style="animation-delay: -7s"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-5 md:px-10 pt-28 pb-20 w-full">

        {{-- ---- texte ---- --}}
        <div class="flex flex-col items-start gap-6 text-left max-w-2xl">

            @if ($offreActive)
                <a href="#offre"
                   class="reveal inline-flex items-center gap-2 bg-accent-500 text-white text-xs font-bold tracking-wide px-4 py-2 rounded-full shadow-lg shadow-accent-900/30 hover:bg-accent-600 transition-colors">
                    <span class="material-symbols-outlined text-[16px]">redeem</span>
                    {{ $derniere ? 'Dernières places' : 'Offre de lancement' }} · plus que {{ $restantesLabel }} sur {{ $places }}
                </a>
            @else
                <span class="reveal inline-flex items-center gap-2 bg-white/10 border border-white/20 text-accent-400 text-xs font-bold tracking-wide px-4 py-1.5 rounded-full">
                    <span class="material-symbols-outlined text-[16px]">stars</span>
                    Nouveau à Madagascar et à l'international
                </span>
            @endif

            <h1 class="reveal font-display text-4xl sm:text-5xl xl:text-6xl font-extrabold text-white leading-[1.05] tracking-tight" style="--d:.08s">
                Votre boutique en ligne
                <span class="block bg-gradient-to-r from-accent-400 to-amber-300 bg-clip-text text-transparent">en 5 minutes.</span>
            </h1>

            {{-- mot qui défile --}}
            <p class="reveal font-display text-xl sm:text-2xl font-semibold text-white/90" style="--d:.16s"
               x-data="{ i: 0, show: true, mots: ['WhatsApp', 'Facebook', 'Instagram', 'SMS'],
                         init() { setInterval(() => { this.show = false; setTimeout(() => { this.i = (this.i + 1) % this.mots.length; this.show = true; }, 300); }, 2600); } }">
                Partagez-la sur
                <span class="inline-block text-accent-400 transition-all duration-300"
                      :class="show ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2'"
                      x-text="mots[i]">WhatsApp</span>
            </p>

            <p class="reveal font-body text-base sm:text-lg text-primary-50/85 max-w-lg" style="--d:.22s">
                Vos clients commandent, vous encaissez.
                <strong class="text-white">0 % de commission</strong>, aucune compétence technique.
            </p>

            @if ($offreActive)
                <div class="reveal w-full max-w-lg bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl px-4 py-3 flex items-center gap-3" style="--d:.28s">
                    <span class="anim-pulse-ring h-11 w-11 shrink-0 rounded-full bg-accent-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-[22px]" style="font-variation-settings: 'FILL' 1;">redeem</span>
                    </span>
                    <p class="font-body text-sm text-white leading-snug">
                        <strong>{{ $premiersMaj }} {{ $verbe }} {{ $moisOfferts }}</strong>
                        <span class="text-primary-100/80">— soit</span>
                        <strong class="text-accent-400">{{ $totalMois }} mois gratuits</strong> avec l'essai.
                    </p>
                </div>
            @endif

            <div class="reveal flex flex-col sm:flex-row items-center gap-3 sm:gap-4 w-full sm:w-auto" style="--d:.34s">
                <button type="button" @click="openAuth('signup')"
                        class="btn-shine w-full sm:w-auto px-8 py-4 bg-accent-500 hover:bg-accent-600 text-white font-bold rounded-xl shadow-lg shadow-accent-900/40 hover:shadow-xl hover:-translate-y-0.5 active:scale-[0.98] transition-all">
                    {{ $offreActive ? 'Je réserve ma place gratuitement' : 'Créer ma boutique gratuitement' }}
                </button>
                <a href="#fonctionnement"
                   class="w-full sm:w-auto px-8 py-4 bg-white/10 hover:bg-white/20 border border-white/30 text-white font-bold rounded-xl transition-colors text-center">
                    Voir comment ça marche
                </a>
            </div>

            <ul class="reveal flex flex-wrap gap-x-5 gap-y-2 font-body text-sm text-primary-100/80" style="--d:.4s">
                <li class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px] text-green-400">check_circle</span>Sans carte bancaire</li>
                <li class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px] text-green-400">check_circle</span>30 jours gratuits</li>
                <li class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px] text-green-400">check_circle</span>Prête en 5 minutes</li>
            </ul>
        </div>

        {{--
            EMPLACEMENT RÉSERVÉ — future application mobile.
            Quand l'app sera prête, ajoute ici une colonne à droite (ex. un mockup de téléphone
            avec une capture d'écran) et passe ce conteneur en "grid lg:grid-cols-2 gap-14 items-center".
        --}}
    </div>

    {{-- indicateur de scroll --}}
    <a href="#fonctionnement" class="absolute bottom-6 left-1/2 -translate-x-1/2 hidden md:flex flex-col items-center gap-1 text-white/60 hover:text-white transition-colors" aria-label="Découvrir la suite">
        <span class="font-body text-[11px] uppercase tracking-widest">Découvrir</span>
        <span class="material-symbols-outlined anim-float text-[26px]">keyboard_double_arrow_down</span>
    </a>
</header>
{{-- ============ BANDEAU DÉFILANT ============ --}}
@php
    $atouts = [
        ['icone' => 'payments', 'texte' => '0 % de commission'],
        ['icone' => 'timer', 'texte' => 'Boutique prête en 5 minutes'],
        ['icone' => 'redeem', 'texte' => '30 jours gratuits'],
        ['icone' => 'chat', 'texte' => 'Lien WhatsApp & Facebook'],
        ['icone' => 'smartphone', 'texte' => 'MVola & Orange Money'],
        ['icone' => 'point_of_sale', 'texte' => 'Vente en boutique physique'],
        ['icone' => 'receipt_long', 'texte' => 'Reçus PDF automatiques'],
    ];
@endphp
<section aria-label="Les points forts de Tafely" class="marquee bg-white border-b border-gray-100 overflow-hidden py-5">
    <div class="marquee-track">
        @foreach ([1, 2] as $copie)
            <ul class="flex shrink-0 items-center gap-4 pr-4" @if ($copie === 2) aria-hidden="true" @endif>
                @foreach ($atouts as $atout)
                    <li class="flex items-center gap-2.5 bg-gray-50 border border-gray-100 rounded-full pl-2.5 pr-5 py-2 whitespace-nowrap">
                        <span class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary-700 text-[18px]">{{ $atout['icone'] }}</span>
                        </span>
                        <span class="font-body text-sm font-semibold text-gray-700">{{ $atout['texte'] }}</span>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </div>
</section>

{{-- ============ 3 ÉTAPES ============ --}}
<section id="fonctionnement" aria-labelledby="titre-etapes" class="bg-white py-20 md:py-28 scroll-mt-16 md:scroll-mt-20">
    <div class="max-w-6xl mx-auto px-5 md:px-10">
        <div class="reveal text-center max-w-2xl mx-auto mb-14 md:mb-20">
            <span class="text-accent-600 font-bold text-sm uppercase tracking-wide font-body">Comment ça marche</span>
            <h2 id="titre-etapes" class="font-display text-3xl md:text-5xl font-extrabold text-gray-900 mt-3 leading-tight">Trois étapes. Zéro prise de tête.</h2>
            <p class="font-body text-gray-500 mt-4">Pas de site à construire, pas de mot de passe à retenir.</p>
        </div>

        <div class="relative">
            {{-- ligne pointillée animée --}}
            <svg class="hidden md:block absolute top-14 left-[16%] w-[68%] h-2 z-0" viewBox="0 0 100 2" preserveAspectRatio="none" aria-hidden="true">
                <line x1="0" y1="1" x2="100" y2="1" stroke="#93c5fd" stroke-width="2" stroke-dasharray="6 6" class="anim-dash" vector-effect="non-scaling-stroke"/>
            </svg>

            <ol class="relative z-10 grid md:grid-cols-3 gap-10 md:gap-6 list-none">

                <li class="reveal text-center" style="--d:.05s">
                    <div class="mx-auto h-28 w-28">
                        <svg viewBox="0 0 120 120" class="h-full w-full anim-float" aria-hidden="true">
                            <circle cx="60" cy="60" r="56" fill="#eff6ff"/>
                            <rect x="28" y="40" width="64" height="44" rx="8" fill="#fff" stroke="#1d4ed8" stroke-width="3"/>
                            <path d="M30 44l30 22 30-22" fill="none" stroke="#1d4ed8" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="90" cy="38" r="14" fill="#22c55e"/>
                            <path d="M83 38l5 5 9-9" fill="none" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-primary-700 text-white font-display font-bold text-sm mt-5">1</span>
                    <h3 class="font-display font-bold text-xl text-gray-900 mt-3">Créez votre compte</h3>
                    <p class="font-body text-sm text-gray-500 mt-2 max-w-[16rem] mx-auto">Un email, un code. C'est tout.</p>
                </li>

                <li class="reveal text-center" style="--d:.2s">
                    <div class="mx-auto h-28 w-28">
                        <svg viewBox="0 0 120 120" class="h-full w-full anim-float-2" aria-hidden="true">
                            <circle cx="60" cy="60" r="56" fill="#fef2f2"/>
                            <rect x="30" y="28" width="60" height="60" rx="10" fill="#fff" stroke="#ef4444" stroke-width="3"/>
                            <path d="M36 78l16-18 12 12 8-8 12 14z" fill="#fecaca"/>
                            <circle cx="70" cy="46" r="6" fill="#f59e0b"/>
                            <rect x="66" y="74" width="40" height="22" rx="11" fill="#1d4ed8"/>
                            <path d="M86 79v12M80 85h12" stroke="#fff" stroke-width="3.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-primary-700 text-white font-display font-bold text-sm mt-5">2</span>
                    <h3 class="font-display font-bold text-xl text-gray-900 mt-3">Ajoutez vos produits</h3>
                    <p class="font-body text-sm text-gray-500 mt-2 max-w-[16rem] mx-auto">Photo, prix, stock. Prêt en quelques clics.</p>
                </li>

                <li class="reveal text-center" style="--d:.35s">
                    <div class="mx-auto h-28 w-28">
                        <svg viewBox="0 0 120 120" class="h-full w-full anim-float-3" aria-hidden="true">
                            <circle cx="60" cy="60" r="56" fill="#f0fdf4"/>
                            <path d="M26 62L94 32 78 92 60 72z" fill="#1d4ed8"/>
                            <path d="M94 32L60 72" stroke="#fff" stroke-width="3" stroke-linecap="round"/>
                            <path d="M60 72l-3 18 13-13z" fill="#93c5fd"/>
                            <circle cx="30" cy="34" r="5" fill="#22c55e"/><circle cx="96" cy="84" r="4" fill="#ef4444"/><circle cx="24" cy="86" r="3" fill="#f59e0b"/>
                        </svg>
                    </div>
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-primary-700 text-white font-display font-bold text-sm mt-5">3</span>
                    <h3 class="font-display font-bold text-xl text-gray-900 mt-3">Partagez votre lien</h3>
                    <p class="font-body text-sm text-gray-500 mt-2 max-w-[16rem] mx-auto">WhatsApp, Facebook, SMS. Les commandes arrivent.</p>
                </li>
            </ol>
        </div>

        <div class="reveal text-center mt-14">
            <button type="button" @click="openAuth('signup')"
                    class="btn-shine inline-flex items-center gap-2 bg-primary-800 hover:bg-primary-900 text-white font-bold px-8 py-4 rounded-xl shadow-lg shadow-primary-800/25 hover:-translate-y-0.5 active:scale-[0.98] transition-all">
                Créer ma boutique maintenant
                <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
            </button>
        </div>
    </div>
</section>

{{-- ============ FONCTIONNALITÉS (grille visuelle) ============ --}}
<section id="avantages" aria-labelledby="titre-avantages" class="bg-gray-50 py-20 md:py-28 scroll-mt-16 md:scroll-mt-20">
    <div class="max-w-7xl mx-auto px-5 md:px-10">
        <div class="reveal text-center max-w-2xl mx-auto mb-14">
            <span class="text-accent-600 font-bold text-sm uppercase tracking-wide font-body">Fonctionnalités</span>
            <h2 id="titre-avantages" class="font-display text-3xl md:text-5xl font-extrabold text-gray-900 mt-3 leading-tight">Tout pour vendre plus, dès le premier jour</h2>
            <p class="font-body text-gray-500 mt-4">Une seule plateforme pour votre boutique en ligne et votre boutique physique.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-5 md:gap-6">

            {{-- A : lien partageable --}}
            <article class="reveal md:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary-700 to-primary-900 p-7 md:p-9 text-white min-h-[260px]">
                <div class="anim-blob absolute -right-10 -top-10 h-56 w-56 rounded-full bg-accent-500/30 blur-3xl"></div>
                <h3 class="font-display text-2xl md:text-3xl font-extrabold relative">Un lien. Votre boutique.</h3>
                <p class="font-body text-primary-100/85 mt-2 relative">Partagez-le, ils commandent.</p>

                <div class="relative mt-6 inline-flex max-w-full items-center gap-3 bg-white/15 border border-white/25 rounded-full pl-5 pr-2 py-2 font-mono text-xs sm:text-sm backdrop-blur">
                    <span class="truncate">{{ $hote }}/b/<span class="font-bold text-accent-400">votre-boutique</span></span>
                    <span class="h-9 w-9 shrink-0 rounded-full bg-white text-primary-800 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">content_copy</span>
                    </span>
                </div>

                <div class="relative mt-7 flex flex-wrap items-center gap-4" aria-hidden="true">
                    <span class="anim-float h-14 w-14 rounded-2xl bg-green-500 shadow-lg flex items-center justify-center"><span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' 1;">chat</span></span>
                    <span class="anim-float-2 h-14 w-14 rounded-2xl bg-blue-600 shadow-lg flex items-center justify-center"><span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' 1;">thumb_up</span></span>
                    <span class="anim-float-3 h-14 w-14 rounded-2xl bg-gradient-to-br from-fuchsia-500 via-rose-500 to-amber-400 shadow-lg flex items-center justify-center"><span class="material-symbols-outlined text-[28px]">photo_camera</span></span>
                    <span class="anim-float h-14 w-14 rounded-2xl bg-white/20 border border-white/30 shadow-lg flex items-center justify-center" style="animation-delay:-3s"><span class="material-symbols-outlined text-[28px]">sms</span></span>
                </div>
            </article>

            {{-- B : commandes en direct --}}
            <article class="reveal rounded-3xl bg-white border border-gray-100 shadow-sm p-6 md:p-7" style="--d:.1s">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-xl font-extrabold text-gray-900">Commandes en direct</h3>
                    <span class="relative flex h-3 w-3"><span class="absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75 animate-ping"></span><span class="relative inline-flex h-3 w-3 rounded-full bg-green-500"></span></span>
                </div>
                <div class="mt-5 space-y-3" aria-hidden="true">
                    @foreach ([
                        ['ini' => 'H', 'nom' => 'Hery R.', 'info' => '2 articles', 'montant' => '45 000 Ar', 'statut' => 'Nouvelle', 'classe' => 'bg-accent-50 text-accent-700', 'avatar' => 'bg-accent-500', 'd' => '.15s'],
                        ['ini' => 'M', 'nom' => 'Miora A.', 'info' => '1 article', 'montant' => '18 500 Ar', 'statut' => 'En livraison', 'classe' => 'bg-primary-50 text-primary-700', 'avatar' => 'bg-primary-600', 'd' => '.35s'],
                        ['ini' => 'T', 'nom' => 'Tojo L.', 'info' => '3 articles', 'montant' => '72 000 Ar', 'statut' => 'Livrée', 'classe' => 'bg-green-50 text-green-700', 'avatar' => 'bg-green-500', 'd' => '.55s'],
                    ] as $ligne)
                        <div class="order-row flex items-center gap-3 rounded-2xl bg-gray-50 p-3" style="--d: {{ $ligne['d'] }}">
                            <span class="h-10 w-10 shrink-0 rounded-full {{ $ligne['avatar'] }} text-white font-display font-bold flex items-center justify-center">{{ $ligne['ini'] }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="font-body text-sm font-semibold text-gray-900 truncate">{{ $ligne['nom'] }}</p>
                                <p class="font-body text-[11px] text-gray-400">{{ $ligne['info'] }} · {{ $ligne['montant'] }}</p>
                            </div>
                            <span class="shrink-0 text-[10px] font-bold px-2 py-1 rounded-full {{ $ligne['classe'] }}">{{ $ligne['statut'] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>

            {{-- C : vente en boutique --}}
            <article class="reveal rounded-3xl bg-white border border-gray-100 shadow-sm p-6 md:p-7 overflow-hidden" style="--d:.05s">
                <h3 class="font-display text-xl font-extrabold text-gray-900">Vente en boutique</h3>
                <p class="font-body text-sm text-gray-500 mt-1">Stock à jour, facture en un clic.</p>
                <div class="relative mt-5 mx-auto max-w-[210px] rotate-[-2deg] bg-white border border-dashed border-gray-300 rounded-xl p-4 shadow-md" aria-hidden="true">
                    <p class="font-display text-[11px] font-bold text-gray-900 text-center tracking-wide">REÇU · BTQ-0142</p>
                    <div class="mt-3 space-y-2">
                        <div class="flex justify-between"><span class="h-2 w-20 rounded bg-gray-200"></span><span class="h-2 w-10 rounded bg-gray-200"></span></div>
                        <div class="flex justify-between"><span class="h-2 w-24 rounded bg-gray-200"></span><span class="h-2 w-8 rounded bg-gray-200"></span></div>
                        <div class="flex justify-between"><span class="h-2 w-16 rounded bg-gray-200"></span><span class="h-2 w-12 rounded bg-gray-200"></span></div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-dashed border-gray-300 flex justify-between items-center">
                        <span class="font-body text-[11px] font-bold text-gray-500">TOTAL</span>
                        <span class="font-display text-sm font-extrabold text-primary-800">63 500 Ar</span>
                    </div>
                    <span class="absolute -right-3 -bottom-3 rotate-[12deg] rounded-lg border-2 border-green-500 text-green-600 bg-white font-display text-[10px] font-extrabold px-2 py-1">PAYÉ · MVOLA</span>
                </div>
            </article>

            {{-- D : promotions --}}
            <article class="reveal rounded-3xl bg-gradient-to-br from-accent-50 to-amber-50 border border-accent-100 p-6 md:p-7" style="--d:.15s">
                <h3 class="font-display text-xl font-extrabold text-gray-900">Promos qui donnent envie</h3>
                <p class="font-body text-sm text-gray-500 mt-1">En % ou en montant.</p>
                <div class="relative mt-5 mx-auto w-40" aria-hidden="true">
                    <div class="rounded-2xl bg-white shadow-lg overflow-hidden">
                        <div class="h-24 bg-amber-50 p-3">
                            <svg viewBox="0 0 100 80" class="h-full w-full"><path d="M36 30c0-14 28-14 28 0" fill="none" stroke="#92400e" stroke-width="4" stroke-linecap="round"/><rect x="24" y="30" width="52" height="40" rx="8" fill="#f59e0b"/><rect x="24" y="30" width="52" height="12" rx="6" fill="#d97706"/><circle cx="50" cy="48" r="3.5" fill="#fef3c7"/></svg>
                        </div>
                        <div class="p-3">
                            <p class="font-body text-xs font-semibold text-gray-900">Sac en raphia</p>
                            <p class="font-display font-extrabold text-primary-800">20 000 Ar <span class="text-[10px] text-gray-400 line-through font-normal">25 000</span></p>
                        </div>
                    </div>
                    <span class="anim-pulse-ring absolute -top-3 -right-3 h-14 w-14 rounded-full bg-accent-500 text-white font-display font-extrabold text-sm flex items-center justify-center rotate-[12deg]">-20 %</span>
                </div>
            </article>

            {{-- E : tableau de bord --}}
            <article class="reveal rounded-3xl bg-white border border-gray-100 shadow-sm p-6 md:p-7" style="--d:.25s">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-xl font-extrabold text-gray-900">Votre activité</h3>
                    <span class="text-[11px] font-bold text-green-700 bg-green-50 px-2.5 py-1 rounded-full">+38 %</span>
                </div>
                <p class="font-body text-sm text-gray-500 mt-1">Chiffre d'affaires en un coup d'œil.</p>
                <div class="relative mt-5 h-32 reveal" aria-hidden="true">
                    <div class="absolute inset-0 flex items-end gap-2">
                        @foreach ([30, 46, 38, 60, 52, 74, 68, 92] as $i => $hauteur)
                            <div class="bar flex-1 rounded-t-lg bg-gradient-to-t from-primary-700 to-primary-400" style="height: {{ $hauteur }}%; --d: {{ number_format($i * 0.08, 2) }}s"></div>
                        @endforeach
                    </div>
                    <svg viewBox="0 0 200 100" preserveAspectRatio="none" class="absolute inset-0 h-full w-full">
                        <path class="draw" d="M0 78 L28 62 L57 68 L86 46 L114 52 L143 30 L171 34 L200 10" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
                    </svg>
                </div>
            </article>

            {{-- F : personnalisation interactive --}}
            <article class="reveal md:col-span-3 rounded-3xl bg-white border border-gray-100 shadow-sm p-6 md:p-9 overflow-hidden"
                     x-data="{ couleur: '#2563eb', couleurs: ['#2563eb', '#dc2626', '#16a34a', '#7c3aed', '#ea580c', '#111827'] }">
                <div class="grid md:grid-cols-2 gap-8 items-center">
                    <div>
                        <h3 class="font-display text-2xl md:text-3xl font-extrabold text-gray-900">Une boutique à votre image</h3>
                        <p class="font-body text-gray-500 mt-2">Essayez : choisissez votre couleur.</p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <template x-for="c in couleurs" :key="c">
                                <button type="button" @click="couleur = c" :aria-label="'Couleur ' + c"
                                        class="h-12 w-12 rounded-full border-4 border-white shadow-md transition-all hover:scale-110 active:scale-95"
                                        :class="couleur === c ? 'ring-4 ring-offset-2 scale-110' : ''"
                                        :style="`background-color:${c}; --tw-ring-color:${c}`"></button>
                            </template>
                        </div>
                        <ul class="mt-6 flex flex-wrap gap-2 font-body text-xs font-semibold text-gray-600">
                            <li class="bg-gray-100 rounded-full px-3 py-1.5">Logo</li>
                            <li class="bg-gray-100 rounded-full px-3 py-1.5">3 thèmes</li>
                            <li class="bg-gray-100 rounded-full px-3 py-1.5">Couleur libre</li>
                            <li class="bg-gray-100 rounded-full px-3 py-1.5">Texte de présentation</li>
                        </ul>
                    </div>

                    {{-- aperçu vivant --}}
                    <div class="relative mx-auto w-full max-w-sm rounded-3xl bg-gray-50 border border-gray-100 p-5 transition-colors duration-500" :style="`background-color:${couleur}12`" aria-hidden="true">
                        <div class="text-center">
                            <div class="mx-auto h-14 w-14 rounded-full flex items-center justify-center text-white font-display font-bold text-xl shadow-lg transition-colors duration-500" :style="`background-color:${couleur}`">N</div>
                            <p class="font-display font-bold text-gray-900 mt-2">Naly Boutique</p>
                            <p class="font-body text-xs text-gray-500">Créations faites main à Antananarivo</p>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            @foreach ([['Sac raphia', '20 000 Ar', 'bg-amber-50'], ['Panier tressé', '15 000 Ar', 'bg-rose-50']] as $p)
                                <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
                                    <div class="h-16 {{ $p[2] }}"></div>
                                    <div class="p-2.5">
                                        <p class="font-body text-xs font-semibold text-gray-900">{{ $p[0] }}</p>
                                        <p class="font-display text-xs font-bold transition-colors duration-500" :style="`color:${couleur}`">{{ $p[1] }}</p>
                                        <span class="mt-2 block text-center text-white text-[10px] font-bold rounded-lg py-1.5 transition-colors duration-500" :style="`background-color:${couleur}`">Ajouter</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>

{{-- ============ POUR QUI ? ============ --}}
@php
    $profils = [
        ['nom' => 'Mode & accessoires', 'icone' => 'checkroom', 'photo' => $photo('mode.jpg'), 'degrade' => 'from-rose-400 to-rose-700', 'alt' => 'Boutique de vêtements et accessoires en ligne à Madagascar'],
        ['nom' => 'Artisanat & déco', 'icone' => 'brush', 'photo' => $photo('artisanat.jpg'), 'degrade' => 'from-amber-400 to-orange-700', 'alt' => 'Vente de produits artisanaux malgaches en ligne'],
        ['nom' => 'Alimentation & pâtisserie', 'icone' => 'bakery_dining', 'photo' => $photo('alimentation.jpg'), 'degrade' => 'from-emerald-400 to-emerald-700', 'alt' => 'Boutique en ligne de produits alimentaires et pâtisserie'],
        ['nom' => 'Beauté & bien-être', 'icone' => 'spa', 'photo' => $photo('beaute.jpg'), 'degrade' => 'from-violet-400 to-violet-700', 'alt' => 'Boutique en ligne de cosmétiques et bien-être'],
    ];
@endphp
<section aria-labelledby="titre-profils" class="bg-white py-20 md:py-28">
    <div class="max-w-7xl mx-auto px-5 md:px-10">
        <div class="reveal text-center max-w-2xl mx-auto mb-12">
            <span class="text-accent-600 font-bold text-sm uppercase tracking-wide font-body">Pour tous les commerçants</span>
            <h2 id="titre-profils" class="font-display text-3xl md:text-5xl font-extrabold text-gray-900 mt-3 leading-tight">Quel que soit ce que vous vendez</h2>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            @foreach ($profils as $i => $profil)
                <div class="reveal group relative aspect-[3/4] overflow-hidden rounded-3xl bg-gradient-to-br {{ $profil['degrade'] }} shadow-lg hover:shadow-2xl transition-shadow" style="--d: {{ number_format($i * 0.1, 1) }}s">
                    @if ($profil['photo'])
                        <img src="{{ $profil['photo'] }}" alt="{{ $profil['alt'] }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-110">
                    @else
                        <span class="material-symbols-outlined absolute -right-4 -top-4 text-white/20 text-[9rem] transition-transform duration-700 group-hover:scale-110 group-hover:rotate-6" aria-hidden="true">{{ $profil['icone'] }}</span>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
                    <div class="absolute bottom-0 inset-x-0 p-4 md:p-5">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-gray-900 mb-2 transition-transform duration-300 group-hover:-translate-y-1">
                            <span class="material-symbols-outlined text-[22px]">{{ $profil['icone'] }}</span>
                        </span>
                        <p class="font-display font-bold text-white text-sm md:text-lg leading-tight">{{ $profil['nom'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ CHIFFRES ============ --}}
<section aria-label="Tafely en chiffres" class="relative overflow-hidden bg-gradient-to-br from-primary-800 to-primary-950 py-16 md:py-20">
    <div class="absolute inset-0 bg-dots opacity-60"></div>
    <div class="relative max-w-6xl mx-auto px-5 md:px-10 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
        @php
            $chiffres = [
                ['valeur' => 0, 'suffixe' => '%', 'texte' => 'de commission sur vos ventes'],
                ['valeur' => 5, 'suffixe' => 'min', 'texte' => 'pour lancer votre boutique'],
                ['valeur' => 30, 'suffixe' => 'jours', 'texte' => "d'essai gratuit"],
                $offreActive
                    ? ['valeur' => $totalMois, 'suffixe' => 'mois', 'texte' => "gratuits avec l'offre de lancement"]
                    : ['valeur' => 10, 'suffixe' => 'produits', 'texte' => 'inclus dès l\'essai'],
            ];
        @endphp
        @foreach ($chiffres as $i => $chiffre)
            <div class="reveal" style="--d: {{ number_format($i * 0.1, 1) }}s">
                <p class="font-display font-extrabold text-white leading-none">
                    <span class="text-5xl md:text-6xl" data-count="{{ $chiffre['valeur'] }}">{{ $chiffre['valeur'] }}</span>
                    <span class="text-2xl md:text-3xl text-accent-400 ml-1">{{ $chiffre['suffixe'] }}</span>
                </p>
                <p class="font-body text-sm text-primary-100/80 mt-3">{{ $chiffre['texte'] }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ============ OFFRE DE LANCEMENT (dynamique) ============ --}}
@if ($offreActive)
<section id="offre" aria-labelledby="titre-offre" class="relative overflow-hidden bg-primary-950 py-20 md:py-28 scroll-mt-16 md:scroll-mt-20">
    <div class="anim-blob absolute -top-24 -right-24 h-80 w-80 rounded-full bg-accent-500/25 blur-3xl"></div>
    <div class="anim-blob absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-primary-600/35 blur-3xl" style="animation-delay:-6s"></div>
    <div class="absolute inset-0 bg-dots"></div>

    <div class="relative max-w-5xl mx-auto px-5 md:px-10">
        <div class="grid md:grid-cols-2 gap-10 md:gap-14 items-center">

            {{-- visuel : cadeau + anneau --}}
            <div class="reveal flex flex-col items-center gap-6" data-r="scale">
                <div class="relative">
                    <svg viewBox="0 0 140 140" class="h-44 w-44 sm:h-52 sm:w-52 anim-float" aria-hidden="true">
                        <ellipse cx="70" cy="130" rx="40" ry="6" fill="#000" opacity=".25"/>
                        <rect x="28" y="64" width="84" height="60" rx="8" fill="#ef4444"/>
                        <rect x="62" y="64" width="16" height="60" fill="#fecaca"/>
                        <g class="gift-lid">
                            <rect x="22" y="46" width="96" height="24" rx="8" fill="#f87171"/>
                            <rect x="62" y="46" width="16" height="24" fill="#fecaca"/>
                            <path d="M70 46c-14-22-36-10-25 0 7 6 25 0 25 0zM70 46c14-22 36-10 25 0-7 6-25 0-25 0z" fill="none" stroke="#fecaca" stroke-width="5" stroke-linejoin="round"/>
                        </g>
                        <path class="twinkle" d="M22 30l3 7 7 3-7 3-3 7-3-7-7-3 7-3z" fill="#fde68a"/>
                        <path class="twinkle" style="animation-delay:.6s" d="M120 24l2 5 5 2-5 2-2 5-2-5-5-2 5-2z" fill="#fde68a"/>
                        <path class="twinkle" style="animation-delay:1.2s" d="M114 84l2 4 4 2-4 2-2 4-2-4-4-2 4-2z" fill="#fff"/>
                    </svg>
                </div>

                {{-- anneau de progression --}}
                <div class="reveal relative h-40 w-40" aria-label="{{ $prises }} inscrits sur {{ $places }}" role="img" style="--target: {{ $decalageAnneau }}">
                    <svg viewBox="0 0 120 120" class="h-full w-full -rotate-90">
                        <circle cx="60" cy="60" r="54" fill="none" stroke="rgba(255,255,255,.15)" stroke-width="10"/>
                        <circle class="ring" cx="60" cy="60" r="54" fill="none" stroke="url(#degradeAnneau)" stroke-width="10" stroke-linecap="round"
                                stroke-dasharray="{{ $circonference }}" stroke-dashoffset="{{ $decalageAnneau }}"/>
                        <defs>
                            <linearGradient id="degradeAnneau" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0" stop-color="#ef4444"/><stop offset="1" stop-color="#fbbf24"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                        <span class="font-display text-4xl font-extrabold text-white leading-none">{{ $restantes }}</span>
                        <span class="font-body text-[11px] uppercase tracking-wide text-primary-100/80 mt-1">place{{ $restantes > 1 ? 's' : '' }} restante{{ $restantes > 1 ? 's' : '' }}</span>
                    </div>
                </div>
            </div>

            {{-- texte --}}
            <div class="text-center md:text-left">
                <span class="reveal inline-flex items-center gap-2 bg-accent-500 text-white text-xs font-bold uppercase tracking-wide px-4 py-1.5 rounded-full">
                    <span class="material-symbols-outlined text-[16px]">redeem</span>
                    {{ $derniere ? 'Dernières places' : 'Offre de lancement' }}
                </span>

                <h2 id="titre-offre" class="reveal font-display text-3xl md:text-4xl font-extrabold text-white mt-5 leading-tight" style="--d:.08s">
                    {{ $premiersMaj }} {{ $verbe }}
                    <span class="text-accent-400">{{ $moisOfferts }}</span>
                </h2>

                <p class="reveal font-body text-primary-100/90 mt-4" style="--d:.16s">
                    Avec les 30 jours d'essai : <strong class="text-white">{{ $totalMois }} mois</strong> pour vendre avec toutes les fonctions du plan à {{ $prixAffiche }}/mois.
                </p>

                <ol class="reveal mt-7 space-y-3 text-left list-none" style="--d:.24s">
                    @foreach ([
                        ['icone' => 'person_add', 'texte' => 'Inscrivez-vous avec votre email'],
                        ['icone' => 'storefront', 'texte' => 'Créez votre boutique et vos produits'],
                        ['icone' => 'redeem', 'texte' => "Recevez votre bonus dès la validation"],
                    ] as $n => $etape)
                        <li class="flex items-center gap-3 bg-white/10 border border-white/15 rounded-2xl px-4 py-3">
                            <span class="h-10 w-10 shrink-0 rounded-full bg-accent-500 text-white flex items-center justify-center">
                                <span class="material-symbols-outlined text-[20px]">{{ $etape['icone'] }}</span>
                            </span>
                            <span class="font-body text-sm text-white"><strong class="text-accent-400">{{ $n + 1 }}.</strong> {{ $etape['texte'] }}</span>
                        </li>
                    @endforeach
                </ol>

                <button type="button" @click="openAuth('signup')"
                        class="reveal btn-shine mt-8 inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-bold px-8 py-4 rounded-xl shadow-lg shadow-accent-900/40 hover:-translate-y-0.5 active:scale-[0.98] transition-all" style="--d:.32s">
                    Je profite de l'offre
                    <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                </button>
                <p class="font-body text-xs text-primary-100/60 mt-3">Sans carte bancaire · Limitée aux premières boutiques validées</p>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ============ TARIF ============ --}}
<section id="tarif" aria-labelledby="titre-tarif" class="bg-white py-20 md:py-28 scroll-mt-16 md:scroll-mt-20">
    <div class="max-w-4xl mx-auto px-5 md:px-10 text-center">
        <div class="reveal">
            <span class="text-accent-600 font-bold text-sm uppercase tracking-wide font-body">Tarif</span>
            <h2 id="titre-tarif" class="font-display text-3xl md:text-5xl font-extrabold text-gray-900 mt-3 mb-3 leading-tight">Simple. Sans mauvaise surprise.</h2>
            <p class="font-body text-gray-500 mb-8">Aucune commission. Testez avant de payer quoi que ce soit.</p>
            <span class="inline-flex items-center gap-2 bg-primary-50 border border-primary-100 text-primary-700 text-xs font-bold px-4 py-2 rounded-full mb-12">
                <span class="material-symbols-outlined text-[16px]">verified</span>
                30 jours d'essai gratuit, sans carte bancaire
            </span>
        </div>

        <div class="grid sm:grid-cols-2 gap-6 text-left">
            {{-- plan gratuit --}}
            <div class="reveal bg-white rounded-3xl border border-gray-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 p-8 flex flex-col" data-r="left">
                <h3 class="font-display text-lg font-bold text-gray-900">Essai gratuit</h3>
                <p class="font-body text-sm text-gray-500 mt-1 mb-4">Pour démarrer et tester.</p>
                <div class="mb-6">
                    <span class="font-display text-4xl font-extrabold text-gray-900">0 Ar</span>
                    <span class="font-body text-sm text-gray-400"> / 30 jours</span>
                </div>
                <ul class="space-y-3 mb-8 text-gray-700 text-sm font-body flex-1">
                    <li class="flex items-center gap-3"><span class="material-symbols-outlined text-primary-700 text-[20px]">check_circle</span> Jusqu'à 10 produits</li>
                    <li class="flex items-center gap-3"><span class="material-symbols-outlined text-primary-700 text-[20px]">check_circle</span> Boutique personnalisable</li>
                    <li class="flex items-center gap-3"><span class="material-symbols-outlined text-primary-700 text-[20px]">check_circle</span> Lien de partage unique</li>
                    <li class="flex items-center gap-3"><span class="material-symbols-outlined text-primary-700 text-[20px]">check_circle</span> Commandes par email</li>
                </ul>
                <button type="button" @click="openAuth('signup')"
                        class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3.5 rounded-full transition-colors">
                    Commencer gratuitement
                </button>
            </div>

            {{-- plan payant --}}
            <div class="reveal relative bg-gradient-to-br from-primary-700 to-primary-900 text-white rounded-3xl shadow-2xl shadow-primary-900/30 hover:-translate-y-1 transition-all duration-300 p-8 flex flex-col" data-r="right">
                <span class="absolute -top-4 left-1/2 -translate-x-1/2 bg-accent-500 text-white text-xs font-bold uppercase tracking-wide px-4 py-1.5 rounded-full whitespace-nowrap shadow-lg">
                    {{ $offreActive ? $mois.' mois offert'.($mois > 1 ? 's' : '').' aux '.$places.' premiers' : 'Le plus choisi' }}
                </span>
                <h3 class="font-display text-lg font-bold mt-2">Actif payant</h3>
                <p class="font-body text-sm text-primary-100/80 mt-1 mb-4">Pour les boutiques qui vendent sérieusement.</p>
                <div class="mb-6">
                    <span class="font-display text-4xl font-extrabold">{{ $prixAffiche }}</span>
                    <span class="font-body text-sm text-primary-100/70"> / mois</span>
                    @if ($estEtranger)
                        <p class="font-body text-xs text-primary-100/80 mt-1">Paiement en Ariary : {{ $prixFormate }} Ar / mois (via Papi)</p>
                    @endif
                    @if ($reductionMax > 0)
                        <p class="font-body text-xs text-amber-300 font-semibold mt-1">Jusqu'à -{{ $reductionMax }} % sur 12 mois</p>
                    @endif
                </div>
                <ul class="space-y-3 mb-8 text-sm font-body flex-1">
                    <li class="flex items-center gap-3"><span class="material-symbols-outlined text-amber-300 text-[20px]">check_circle</span> Jusqu'à 30 produits</li>
                    <li class="flex items-center gap-3"><span class="material-symbols-outlined text-amber-300 text-[20px]">check_circle</span> MVola, Orange Money et Visa</li>
                    <li class="flex items-center gap-3"><span class="material-symbols-outlined text-amber-300 text-[20px]">check_circle</span> Thèmes et couleurs</li>
                    <li class="flex items-center gap-3"><span class="material-symbols-outlined text-amber-300 text-[20px]">check_circle</span> Vente en boutique physique</li>
                    <li class="flex items-center gap-3"><span class="material-symbols-outlined text-amber-300 text-[20px]">check_circle</span> Support prioritaire</li>
                </ul>
                <button type="button" @click="openAuth('signup')"
                        class="btn-shine w-full bg-accent-500 hover:bg-accent-600 text-white font-bold py-3.5 rounded-full transition shadow-lg shadow-accent-900/40 hover:-translate-y-0.5 active:scale-[0.98]">
                    {{ $offreActive ? 'Je réserve mon bonus' : 'Commencer maintenant' }}
                </button>
            </div>
        </div>

        <p class="reveal font-body text-xs text-gray-400 mt-8">
            Besoin de plus de place ? Ajoutez des emplacements produits par 5, 10, 15... depuis votre tableau de bord.
        </p>
    </div>
</section>

{{-- ============ FAQ ============ --}}
<section id="faq" aria-labelledby="titre-faq" class="bg-gray-50 py-20 md:py-28 scroll-mt-16 md:scroll-mt-20">
    <div class="max-w-3xl mx-auto px-5 md:px-10">
        <div class="reveal text-center mb-10 md:mb-12">
            <span class="text-accent-600 font-bold text-sm uppercase tracking-wide font-body">Questions fréquentes</span>
            <h2 id="titre-faq" class="font-display text-3xl md:text-5xl font-extrabold text-gray-900 mt-3 leading-tight">Avant de vous lancer</h2>
        </div>

        <div class="flex flex-col gap-3">
            @foreach ($faq as $i => $item)
                <details class="reveal group bg-white rounded-2xl border border-gray-100 shadow-sm open:shadow-lg open:border-primary-100 transition-shadow overflow-hidden" style="--d: {{ number_format(min($i, 6) * 0.05, 2) }}s" @if ($loop->first) open @endif>
                    <summary class="flex items-center justify-between gap-4 px-5 py-4 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                        <h3 class="font-body font-semibold text-sm md:text-base text-primary-900">{{ $item['q'] }}</h3>
                        <span class="h-8 w-8 shrink-0 rounded-full bg-gray-100 group-open:bg-primary-700 group-open:text-white text-gray-500 flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined text-[20px] transition-transform duration-300 group-open:rotate-180">expand_more</span>
                        </span>
                    </summary>
                    <p class="px-5 pb-5 font-body text-sm text-gray-600 leading-relaxed">{{ $item['r'] }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ APPEL FINAL ============ --}}
<section aria-labelledby="titre-final" class="relative overflow-hidden bg-gradient-to-br from-primary-800 via-primary-900 to-primary-950 py-20 md:py-28">
    <div class="absolute inset-0 bg-dots"></div>
    <span class="material-symbols-outlined anim-float absolute left-[8%] top-[18%] text-white/10 text-[6rem]" aria-hidden="true">shopping_bag</span>
    <span class="material-symbols-outlined anim-float-2 absolute right-[10%] top-[12%] text-white/10 text-[7rem]" aria-hidden="true">storefront</span>
    <span class="material-symbols-outlined anim-float-3 absolute right-[18%] bottom-[10%] text-white/10 text-[5rem]" aria-hidden="true">redeem</span>

    <div class="relative max-w-3xl mx-auto px-5 md:px-10 text-center">
        <h2 id="titre-final" class="reveal font-display text-3xl md:text-5xl font-extrabold text-white leading-tight">
            Vos clients vous cherchent.
            <span class="block text-accent-400">Donnez-leur une boutique où commander.</span>
        </h2>
        <p class="reveal font-body text-primary-100/90 mt-5 text-lg" style="--d:.1s">
            @if ($offreActive)
                Plus que <strong class="text-accent-400">{{ $restantesLabel }}</strong> pour recevoir {{ $moisOfferts }}.
            @else
                30 jours d'essai gratuit, sans carte bancaire.
            @endif
        </p>
        <button type="button" @click="openAuth('signup')"
                class="reveal btn-shine mt-9 inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-bold text-lg px-10 py-4 rounded-xl shadow-lg shadow-accent-900/50 hover:-translate-y-0.5 active:scale-[0.98] transition-all" style="--d:.2s">
            {{ $offreActive ? 'Je profite de l\'offre' : 'Créer ma boutique gratuitement' }}
            <span class="material-symbols-outlined text-[22px]">arrow_forward</span>
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

{{-- ============ BOUTON FIXE MOBILE ============ --}}
<div x-data="{ visible: false }"
     x-init="window.addEventListener('scroll', () => visible = window.scrollY > 700)"
     x-show="visible" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-6"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-6"
     class="md:hidden fixed bottom-4 inset-x-4 z-40" style="display: none;">
    <button type="button" @click="openAuth('signup')"
            class="btn-shine w-full flex items-center justify-center gap-2 bg-accent-500 text-white font-bold py-4 rounded-2xl shadow-2xl shadow-accent-900/40 active:scale-[0.98] transition-transform">
        <span class="material-symbols-outlined text-[20px]">storefront</span>
        {{ $offreActive ? 'Créer ma boutique · '.$restantesLabel.' offerte'.($restantes > 1 ? 's' : '') : 'Créer ma boutique gratuitement' }}
    </button>
</div>

{{-- ============ ANIMATIONS (apparition, compteurs, barre de lecture) ============ --}}
<script>
    (function () {
        var reduit = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var barre = document.getElementById('scroll-progress');

        // Barre de progression de lecture
        function majBarre() {
            var haut = document.documentElement.scrollHeight - window.innerHeight;
            if (barre && haut > 0) barre.style.width = Math.min(100, (window.scrollY / haut) * 100) + '%';
        }
        window.addEventListener('scroll', majBarre, { passive: true });
        majBarre();

        // Compteur animé
        function animerCompteur(el) {
            var fin = parseInt(el.getAttribute('data-count'), 10) || 0;
            if (reduit || fin === 0) { el.textContent = fin; return; }
            var debut = null, duree = 1500;
            function pas(t) {
                if (!debut) debut = t;
                var p = Math.min((t - debut) / duree, 1);
                el.textContent = Math.round(fin * (1 - Math.pow(1 - p, 3)));
                if (p < 1) requestAnimationFrame(pas);
            }
            el.textContent = '0';
            requestAnimationFrame(pas);
        }

        var cibles = document.querySelectorAll('.reveal, [data-count]');

        if (!('IntersectionObserver' in window)) {
            cibles.forEach(function (el) {
                el.classList.add('is-visible');
                if (el.hasAttribute('data-count')) animerCompteur(el);
            });
            return;
        }

        var observateur = new IntersectionObserver(function (entrees) {
            entrees.forEach(function (entree) {
                if (!entree.isIntersecting) return;
                var el = entree.target;
                el.classList.add('is-visible');
                if (el.hasAttribute('data-count')) animerCompteur(el);
                observateur.unobserve(el);
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

        cibles.forEach(function (el) { observateur.observe(el); });
    })();
</script>

@endsection