<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Créez votre boutique en ligne en un clic : configurez, ajoutez vos produits, partagez votre lien.">
    <title>@yield('title', 'Créez votre boutique en ligne en un clic')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                        display: ['Sora', 'ui-sans-serif', 'system-ui'],
                    },
                    colors: {
                        primary: {
                            50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',
                            400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',
                            800:'#1e40af',900:'#1e3a8a',950:'#151d4a',
                        },
                        accent: {
                            50:'#fef2f2',100:'#fee2e2',200:'#fecaca',300:'#fca5a5',
                            400:'#f87171',500:'#ef4444',600:'#dc2626',700:'#b91c1c',
                        },
                    }
                }
            }
        }
    </script>

    {{-- Plugin intersect AVANT le core Alpine (ordre important) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-display { font-family: 'Sora', sans-serif; }

        /* --- Révélation au scroll --- */
        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity .8s cubic-bezier(.16,1,.3,1), transform .8s cubic-bezier(.16,1,.3,1);
        }
        .reveal-in { opacity: 1; transform: none; }

        /* --- Texte en dégradé animé --- */
        .text-gradient {
            background: linear-gradient(90deg,#f87171,#fbbf24,#f87171);
            background-size: 200% auto;
            -webkit-background-clip: text; background-clip: text;
            color: transparent;
            animation: gradient-move 4s linear infinite;
        }
        @keyframes gradient-move {
            0% { background-position: 0% center; }
            100% { background-position: 200% center; }
        }

        /* --- Blobs flottants --- */
        @keyframes float-a { 0%,100%{ transform: translate(0,0) scale(1);} 50%{ transform: translate(30px,-40px) scale(1.08);} }
        @keyframes float-b { 0%,100%{ transform: translate(0,0) scale(1);} 50%{ transform: translate(-25px,30px) scale(0.95);} }
        .animate-float-a { animation: float-a 9s ease-in-out infinite; }
        .animate-float-b { animation: float-b 11s ease-in-out infinite; }

        /* --- Halo respirant --- */
        @keyframes pulse-glow { 0%,100%{ opacity:.5;} 50%{ opacity:1;} }
        .animate-pulse-glow { animation: pulse-glow 2.4s ease-in-out infinite; }

        /* --- Effet shine sur les boutons --- */
        .btn-shine { position: relative; overflow: hidden; }
        .btn-shine::after {
            content:''; position:absolute; top:0; left:-75%; width:50%; height:100%;
            background: linear-gradient(120deg, transparent, rgba(255,255,255,.35), transparent);
            transform: skewX(-20deg);
            transition: left .7s ease;
        }
        .btn-shine:hover::after { left: 125%; }

        /* --- Marquee --- */
        @keyframes marquee { 0%{ transform: translateX(0);} 100%{ transform: translateX(-50%);} }
        .animate-marquee { animation: marquee 22s linear infinite; }

        /* --- Scrollbar sur mesure --- */
        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #1d4ed8; border-radius: 999px; }
    </style>
</head>
<body class="bg-white text-gray-800 antialiased" x-data="{ authModalOpen: false, mobileMenuOpen: false }">

    <x-navbar />

    <main>
        @yield('content')
    </main>

    <footer class="bg-primary-950 text-gray-300 py-14">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="bg-white rounded-xl px-5 py-3 inline-flex items-center">
                <img src="{{ asset('logo.png') }}" alt="Logo" class="h-10 md:h-12 w-auto">
            </div>
            <p class="text-sm text-gray-500">&copy; {{ date('Y') }} — Tous droits réservés.</p>
        </div>
    </footer>

    <x-auth-modal />

</body>
</html>