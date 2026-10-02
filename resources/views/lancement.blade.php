@php
    // Phrase d'offre de lancement : suit les réglages de l'admin (Paramètres > Validation boutiques).
    $offreActive = \App\Support\Settings::offreLancementActive() && \App\Support\Settings::offreLancementPlaces() > 0;
    $places = \App\Support\Settings::offreLancementPlaces();
    $moisOfferts = \App\Support\Settings::offreLancementMois();
    $premiers = $places > 1 ? 'Les '.$places.' premiers inscrits' : 'Le tout premier inscrit';
    $verbe = $places > 1 ? 'reçoivent' : 'reçoit';

    // Date courte pour le badge (la version longue vient du middleware).
    $ouverture = \Carbon\Carbon::parse(config('lancement.date'), config('lancement.fuseau'))->locale('fr');
    $dateCourte = $ouverture->translatedFormat('j F Y');
    $heureCourte = $ouverture->translatedFormat('H\hi');

    // Valeurs initiales du compte à rebours (évite l'affichage "00" avant le premier tic).
    $reste = max(0, (int) $secondes);
    $initiales = [
        'jours' => intdiv($reste, 86400),
        'heures' => intdiv($reste % 86400, 3600),
        'minutes' => intdiv($reste % 3600, 60),
        'secondes' => $reste % 60,
    ];
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#1d4ed8">
    <title>Tafely — Ouverture le {{ $dateLibelle }}</title>
    <meta name="description" content="Tafely, la plateforme pour créer votre boutique en ligne en 5 minutes, ouvre le {{ $dateLibelle }}. Préparez-vous !">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@600;700;800&family=Be+Vietnam+Pro:wght@400;500;600&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        if (typeof tailwind !== 'undefined') {
            tailwind.config = {
                theme: { extend: {
                    fontFamily: {
                        display: ['"Hanken Grotesk"', 'sans-serif'],
                        body: ['"Be Vietnam Pro"', 'sans-serif'],
                    },
                    colors: {
                        primary: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a',950:'#172554' },
                        accent: { 50:'#fef2f2',100:'#fee2e2',400:'#f87171',500:'#ef4444',600:'#dc2626',700:'#b91c1c' },
                    },
                } }
            };
        }
    </script>

    <style>
        html { -webkit-text-size-adjust: 100%; text-size-adjust: 100%; }
        body { font-family: 'Be Vietnam Pro', sans-serif; }
        .font-display { font-family: 'Hanken Grotesk', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }

        /* ---------- Très grands écrans (TV, 4K) : tout grossit proportionnellement ---------- */
        @media (min-width: 1920px) { html { font-size: 20px; } }
        @media (min-width: 2560px) { html { font-size: 26px; } }
        @media (min-width: 3400px) { html { font-size: 34px; } }

        /* ---------- Mise en page fluide ---------- */
        .plein-ecran { min-height: 100vh; min-height: 100dvh; }
        /* Zones sûres (encoche, barre d'accueil) : les marges latérales restent portées par px-5 / sm:px-8 */
        .zone-sure { padding-top: env(safe-area-inset-top, 0px); padding-bottom: env(safe-area-inset-bottom, 0px); }
        .espace { margin-top: clamp(1.25rem, 4.5vh, 2.5rem); }
        .equilibre { text-wrap: balance; }

        /* ---------- Compte à rebours ---------- */
        .compte { width: 100%; max-width: 34rem; margin-left: auto; margin-right: auto; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: clamp(.4rem, 2vw, 1.25rem); }
        @media (min-width: 1024px) { .compte { max-width: 40rem; } }
        .chiffre { font-size: clamp(1.7rem, 8.5vw, 3.75rem); }
        @media (min-width: 640px) { .chiffre { font-size: clamp(2.75rem, 6vw, 4rem); } }
        .etiquette { font-size: clamp(.55rem, 2.2vw, .75rem); letter-spacing: .08em; }
        .lib-court { display: none; }
        @media (max-width: 379px) { .lib-long { display: none; } .lib-court { display: inline; } }

        .carte {
            position: relative; overflow: hidden;
            padding: clamp(.8rem, 3.6vw, 1.6rem) 0;
            text-align: center;
            background: linear-gradient(160deg, #1e40af 0%, #172554 100%);
            box-shadow: 0 18px 40px -14px rgba(23,37,84,.55), inset 0 1px 0 rgba(255,255,255,.18);
            border-radius: clamp(.9rem, 3vw, 1.5rem);
        }
        .carte::after { content: ""; position: absolute; left: 0; right: 0; top: 50%; height: 1px; background: rgba(255,255,255,.12); }

        /* ---------- Fond ---------- */
        .bg-dots { background-image: radial-gradient(rgba(29,78,216,.13) 1.2px, transparent 1.2px); background-size: 28px 28px; }
        @keyframes blob { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(40px,-50px) scale(1.12); } 66% { transform: translate(-30px,30px) scale(.94); } }
        .blob { animation: blob 18s ease-in-out infinite; }
        @keyframes flotte { 0%,100% { transform: translateY(0) rotate(0); } 50% { transform: translateY(-18px) rotate(4deg); } }
        .flotte { animation: flotte 9s ease-in-out infinite; }

        /* ---------- Entrées ---------- */
        @keyframes monte { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: none; } }
        @keyframes logo-entree { from { opacity: 0; transform: scale(.85); } to { opacity: 1; transform: scale(1); } }
        .monte { opacity: 0; animation: monte .9s cubic-bezier(.22,1,.36,1) forwards; animation-delay: var(--d, 0s); }
        .logo-entree { opacity: 0; animation: logo-entree 1s cubic-bezier(.22,1,.36,1) .1s forwards; }

        @keyframes tic { 0% { transform: translateY(-14px); opacity: 0; } 100% { transform: none; opacity: 1; } }
        .tic { animation: tic .45s cubic-bezier(.22,1,.36,1); }
        @keyframes halo { 0% { box-shadow: 0 0 0 0 rgba(239,68,68,.5); } 70% { box-shadow: 0 0 0 12px rgba(239,68,68,0); } 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0); } }
        .halo { animation: halo 2s infinite; }

        /* ---------- Textes marketing fluides ---------- */
        .titre-mkt { font-size: clamp(1.2rem, 4.6vw, 2.25rem); }
        .texte-mkt { font-size: clamp(.85rem, 2.7vw, 1.05rem); }

        /* ---------- Téléphones en paysage / écrans très bas ---------- */
        @media (max-height: 540px) and (orientation: landscape) {
            .logo { height: 3rem !important; }
            .decor-icone { display: none !important; }
            .espace { margin-top: .75rem; }
            .chiffre { font-size: clamp(1.5rem, 7vh, 2.5rem); }
            .carte { padding: .6rem 0; }
            .titre-mkt { font-size: 1.1rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; }
            .monte, .logo-entree { opacity: 1; }
        }
    </style>
</head>

<body class="relative overflow-x-hidden bg-gradient-to-b from-primary-50 via-white to-primary-100 text-gray-800 antialiased">

    {{-- ============ DÉCOR ============ --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-dots"></div>
        <div class="blob absolute -top-32 -left-32 h-[26rem] w-[26rem] rounded-full bg-primary-300/40 blur-3xl"></div>
        <div class="blob absolute -bottom-40 -right-32 h-[30rem] w-[30rem] rounded-full bg-accent-400/25 blur-3xl" style="animation-delay:-8s"></div>
        <span class="decor-icone material-symbols-outlined flotte absolute left-[7%] top-[22%] text-primary-300/60 text-[5rem] hidden sm:block">shopping_bag</span>
        <span class="decor-icone material-symbols-outlined flotte absolute right-[8%] top-[18%] text-accent-400/40 text-[6rem] hidden sm:block" style="animation-delay:-3s">storefront</span>
        <span class="decor-icone material-symbols-outlined flotte absolute left-[12%] bottom-[24%] text-accent-400/35 text-[4rem] hidden md:block" style="animation-delay:-5s">redeem</span>
        <span class="decor-icone material-symbols-outlined flotte absolute right-[14%] bottom-[28%] text-primary-300/60 text-[4.5rem] hidden md:block" style="animation-delay:-2s">share</span>
    </div>

    <main class="zone-sure plein-ecran relative z-10 flex flex-col items-center text-center px-5 sm:px-8">

        {{-- ============ BADGE DATE ============ --}}
        <div class="w-full pt-6 sm:pt-8 flex justify-center monte">
            <span class="inline-flex items-center justify-center gap-2.5 bg-white/80 backdrop-blur border border-primary-100 text-primary-800 font-semibold px-4 py-2 rounded-2xl sm:rounded-full shadow-sm text-xs sm:text-sm">
                <span class="material-symbols-outlined text-[18px] text-accent-500 shrink-0" style="font-variation-settings: 'FILL' 1;">rocket_launch</span>
                <span class="flex flex-col sm:flex-row sm:items-center sm:gap-1.5 text-center leading-tight">
                    <span>Lancement officiel</span>
                    <span class="hidden sm:inline text-primary-300">·</span>
                    <span class="font-bold">{{ $dateCourte }} · {{ $heureCourte }}</span>
                </span>
            </span>
        </div>

        {{-- ============ LOGO + COMPTE À REBOURS (centre) ============ --}}
        <section class="w-full flex-1 flex flex-col items-center justify-center text-center py-6 sm:py-10">

            <img src="{{ asset('logo.png') }}" alt="Tafely" class="logo logo-entree mx-auto h-16 min-[400px]:h-20 sm:h-28 md:h-32 lg:h-36 w-auto max-w-[70vw] object-contain">

            <p class="monte espace w-full text-center font-display font-bold uppercase text-accent-600 tracking-[0.2em] sm:tracking-[0.25em] text-xs sm:text-base" style="--d:.35s">
                Ouverture dans
            </p>

            <div id="compte" class="compte monte mt-4 sm:mt-5" style="--d:.5s" role="timer" aria-live="off">
                @foreach ([
                    ['jours', 'Jours', 'Jours'],
                    ['heures', 'Heures', 'Heures'],
                    ['minutes', 'Minutes', 'Min.'],
                    ['secondes', 'Secondes', 'Sec.'],
                ] as [$id, $long, $court])
                    <div class="carte {{ $id === 'secondes' ? 'halo' : '' }}">
                        <span id="{{ $id }}" class="chiffre block text-center font-display font-extrabold text-white leading-none tabular-nums">{{ str_pad($initiales[$id], 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="etiquette mt-2 sm:mt-3 block text-center font-body font-semibold uppercase text-primary-200">
                            <span class="lib-long">{{ $long }}</span><span class="lib-court">{{ $court }}</span>
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- message affiché quand le compte à rebours atteint zéro --}}
            <div id="fin" class="hidden mt-5 mx-auto text-center rounded-3xl bg-gradient-to-br from-accent-500 to-accent-600 text-white px-6 sm:px-8 py-5 sm:py-6 shadow-xl max-w-full">
                <p class="font-display text-2xl sm:text-3xl font-extrabold">C'est parti ! 🎉</p>
                <p class="font-body text-sm mt-1 text-white/90">Ouverture de Tafely en cours…</p>
            </div>

            <p class="monte w-full mt-5 sm:mt-7 text-center font-body text-gray-500 text-sm leading-snug" style="--d:.65s">
                <span class="block">
                    <span class="material-symbols-outlined text-[18px] align-text-bottom text-primary-600">schedule</span>
                    {{ $dateLibelle }}
                </span>
                <span class="block text-xs mt-0.5">Heure de Madagascar (UTC+3)</span>
            </p>
        </section>

        {{-- ============ PHRASE MARKETING (bas) ============ --}}
        <footer class="monte pb-6 sm:pb-8 pt-2 sm:pt-4 max-w-2xl lg:max-w-3xl mx-auto w-full text-center" style="--d:.8s">

            <h1 class="titre-mkt equilibre text-center font-display font-extrabold text-primary-900 leading-tight">
                Votre boutique en ligne, <span class="text-accent-600">prête en 5 minutes.</span>
            </h1>
            <p class="texte-mkt equilibre text-center font-body text-gray-600 mt-3 leading-relaxed">
                Un seul lien à partager sur WhatsApp et Facebook, vos clients commandent, vous encaissez :
                <strong class="text-gray-800">0 % de commission</strong>. Tafely arrive pour propulser votre commerce.
            </p>

            @if ($offreActive)
                <div class="mt-4 mx-auto flex flex-col sm:flex-row items-center justify-center gap-1.5 sm:gap-2.5 text-center bg-accent-500 text-white font-body text-xs sm:text-sm font-semibold px-5 py-3 rounded-2xl shadow-lg shadow-accent-500/30 w-full max-w-md sm:max-w-xl">
                    <span class="material-symbols-outlined text-[22px] shrink-0" style="font-variation-settings: 'FILL' 1;">redeem</span>
                    <span class="equilibre">{{ $premiers }} {{ $verbe }} {{ $moisOfferts }} mois de plan payant offert{{ $moisOfferts > 1 ? 's' : '' }} !</span>
                </div>
            @endif

            <ul class="mt-5 flex flex-wrap justify-center items-center gap-2">
                @foreach ([['payments', '0 % commission'], ['redeem', '30 jours gratuits'], ['smartphone', 'MVola & Orange Money']] as [$icone, $texte])
                    <li class="inline-flex items-center justify-center gap-1.5 bg-white/80 backdrop-blur border border-primary-100 rounded-full pl-2.5 pr-3.5 py-1.5 font-body text-xs font-semibold text-primary-800">
                        <span class="material-symbols-outlined text-[16px] text-primary-600">{{ $icone }}</span>{{ $texte }}
                    </li>
                @endforeach
            </ul>

            <p class="mt-6 text-center font-body text-xs text-gray-400 leading-relaxed">
                Une question ? <a href="mailto:contact@tafely-gr.com" class="font-semibold text-primary-700 hover:text-accent-600 transition-colors break-all">contact@tafely-gr.com</a>
                <span class="whitespace-nowrap">· © {{ date('Y') }} Tafely</span>
            </p>
        </footer>
    </main>

    <script>
        (function () {
            // Départ calculé à partir de l'heure du serveur : une horloge de téléphone décalée ne fausse rien.
            var fin = performance.now() + {{ (int) $secondes }} * 1000;
            var termine = false;

            var cases = {
                jours: document.getElementById('jours'),
                heures: document.getElementById('heures'),
                minutes: document.getElementById('minutes'),
                secondes: document.getElementById('secondes'),
            };

            function pad(n) { return String(n).padStart(2, '0'); }

            function afficher(el, valeur) {
                if (el.textContent === valeur) return;
                el.textContent = valeur;
                el.classList.remove('tic');
                void el.offsetWidth; // relance l'animation
                el.classList.add('tic');
            }

            function maj() {
                var reste = Math.max(0, fin - performance.now());
                var s = Math.floor(reste / 1000);

                afficher(cases.jours, pad(Math.floor(s / 86400)));
                afficher(cases.heures, pad(Math.floor((s % 86400) / 3600)));
                afficher(cases.minutes, pad(Math.floor((s % 3600) / 60)));
                afficher(cases.secondes, pad(s % 60));

                document.title = '⏳ ' + Math.floor(s / 86400) + 'j ' + pad(Math.floor((s % 86400) / 3600)) + 'h ' + pad(Math.floor((s % 3600) / 60)) + 'min — Tafely';

                if (reste <= 0 && ! termine) {
                    termine = true;
                    document.title = '🎉 Tafely est ouvert !';
                    document.getElementById('compte').classList.add('hidden');
                    document.getElementById('fin').classList.remove('hidden');
                    // Le serveur laisse désormais passer : on recharge pour entrer sur le site.
                    setTimeout(function () { window.location.reload(); }, 1800);
                }
            }

            maj();
            setInterval(maj, 250);
        })();
    </script>
</body>
</html>