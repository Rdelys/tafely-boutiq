<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration — Tafely')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
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
                        primary: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a',950:'#172554' },
                        accent: { 50:'#fef2f2',500:'#ef4444',600:'#dc2626',700:'#b91c1c' },
                    },
                } }
            };
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Be Vietnam Pro', sans-serif; }
        .font-display { font-family: 'Hanken Grotesk', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

    @php
        $supportEnAttente = \App\Models\SupportTicket::where('nouveau_pour_admin', true)->count();
        $adminLiens = [
            ['route' => 'admin.dashboard', 'icon' => 'dashboard', 'label' => 'TDB', 'actif' => true],
            ['route' => 'admin.marchands.index', 'icon' => 'storefront', 'label' => 'Marchands', 'actif' => true],
            ['route' => 'admin.paiements.index', 'icon' => 'payments', 'label' => 'Abonnements', 'actif' => true],
            ['route' => 'admin.produits.index', 'icon' => 'inventory_2', 'label' => 'Produits', 'actif' => true],
            ['route' => 'admin.commandes.index', 'icon' => 'receipt_long', 'label' => 'Commandes', 'actif' => true],
            ['route' => 'admin.support.index', 'icon' => 'support_agent', 'label' => 'Support', 'actif' => true],
            ['route' => 'admin.securite.index', 'icon' => 'security', 'label' => 'Sécurité', 'actif' => true],
            ['icon' => 'settings', 'label' => 'Paramètres', 'actif' => false],
        ];
    @endphp

    {{-- ============ TOP NAV ============ --}}
    <nav x-data="{ menuOuvert: false, compteOuvert: false }" @keydown.escape.window="menuOuvert = false; compteOuvert = false"
         class="bg-primary-950 text-white sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 md:px-6 h-16 flex items-center justify-between gap-4">

            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-accent-400" style="font-variation-settings: 'FILL' 1;">shield_person</span>
                    <span class="font-display font-bold text-white hidden sm:inline">Tafely Admin</span>
                </a>
            </div>

            {{-- liens desktop --}}
            <div class="hidden lg:flex items-center gap-1 flex-1 justify-center">
                @foreach ($adminLiens as $lien)
                    @if ($lien['actif'])
                        <a href="{{ route($lien['route']) }}"
                           @class([
                               'relative px-3.5 py-2 rounded-full text-sm font-body font-semibold flex items-center gap-2 transition-colors',
                               'bg-white/15 text-white' => request()->routeIs($lien['route']),
                               'text-primary-100 hover:bg-white/10' => ! request()->routeIs($lien['route']),
                           ])>
                            <span class="material-symbols-outlined text-[18px]">{{ $lien['icon'] }}</span>
                            {{ $lien['label'] }}
                            @if ($lien['route'] === 'admin.support.index' && $supportEnAttente > 0)
                                <span class="bg-accent-500 text-white text-[10px] font-bold h-4.5 min-w-[18px] px-1 rounded-full flex items-center justify-center">{{ $supportEnAttente > 99 ? '99+' : $supportEnAttente }}</span>
                            @endif
                        </a>
                    @else
                        <span title="Bientôt disponible"
                              class="px-3.5 py-2 rounded-full text-sm font-body font-semibold flex items-center gap-2 text-primary-300/50 cursor-not-allowed select-none">
                            <span class="material-symbols-outlined text-[18px]">{{ $lien['icon'] }}</span>
                            {{ $lien['label'] }}
                        </span>
                    @endif
                @endforeach
            </div>

            {{-- compte + burger --}}
            <div class="flex items-center gap-2 shrink-0">
                <div class="relative hidden sm:block">
                    <button @click="compteOuvert = ! compteOuvert" type="button"
                            class="flex items-center gap-2 pl-1.5 pr-3 py-1.5 rounded-full bg-white/10 hover:bg-white/15 transition-colors">
                        <span class="h-7 w-7 rounded-full bg-accent-500 flex items-center justify-center text-xs font-bold">A</span>
                        <span class="font-body text-xs font-semibold max-w-[140px] truncate">{{ auth('admin')->user()->email }}</span>
                        <span class="material-symbols-outlined text-[18px] text-primary-200">expand_more</span>
                    </button>
                    <div x-show="compteOuvert" x-cloak @click.outside="compteOuvert = false"
                         x-transition
                         class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden">
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-3 text-sm font-body font-semibold text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px] text-gray-400">logout</span>
                                Se déconnecter
                            </button>
                        </form>
                    </div>
                </div>

                <button @click="menuOuvert = ! menuOuvert" aria-label="Menu" :aria-expanded="menuOuvert"
                        class="lg:hidden h-10 w-10 flex items-center justify-center rounded-full hover:bg-white/10 transition-colors">
                    <span class="material-symbols-outlined text-[24px]" x-show="!menuOuvert">menu</span>
                    <span class="material-symbols-outlined text-[24px]" x-show="menuOuvert" x-cloak>close</span>
                </button>
            </div>
        </div>

        {{-- menu mobile --}}
        <div x-show="menuOuvert" x-cloak x-transition @click.outside="menuOuvert = false"
             class="lg:hidden bg-primary-900 border-t border-white/10">
            <div class="flex flex-col p-3 gap-1">
                @foreach ($adminLiens as $lien)
                    @if ($lien['actif'])
                        <a href="{{ route($lien['route']) }}" @click="menuOuvert = false"
                           @class([
                               'px-4 py-3 rounded-xl text-sm font-body font-semibold flex items-center gap-3 transition-colors',
                               'bg-white/15 text-white' => request()->routeIs($lien['route'].'*'),
                               'text-primary-100 hover:bg-white/10' => ! request()->routeIs($lien['route'].'*'),
                           ])>
                            <span class="material-symbols-outlined text-[20px]">{{ $lien['icon'] }}</span>
                            {{ $lien['label'] }}
                            @if ($lien['route'] === 'admin.support.index' && $supportEnAttente > 0)
                                <span class="ml-auto bg-accent-500 text-white text-[10px] font-bold h-4.5 min-w-[18px] px-1 rounded-full flex items-center justify-center">{{ $supportEnAttente > 99 ? '99+' : $supportEnAttente }}</span>
                            @endif
                        </a>
                    @else
                        <span class="px-4 py-3 rounded-xl text-sm font-body font-semibold flex items-center gap-3 text-primary-300/50">
                            <span class="material-symbols-outlined text-[20px]">{{ $lien['icon'] }}</span>
                            {{ $lien['label'] }}
                            <span class="ml-auto text-[10px] uppercase tracking-wide">bientôt</span>
                        </span>
                    @endif
                @endforeach

                <div class="h-px bg-white/10 my-2"></div>

                <div class="px-4 py-2 text-xs font-body text-primary-300 truncate">{{ auth('admin')->user()->email }}</div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-3 rounded-xl text-sm font-body font-semibold text-primary-100 hover:bg-white/10 flex items-center gap-3">
                        <span class="material-symbols-outlined text-[20px]">logout</span>
                        Se déconnecter
                    </button>
                </form>
            </div>
        </div>
    </nav>

    {{-- ============ CONTENU ============ --}}
    <main class="max-w-7xl mx-auto px-4 md:px-6 py-6 md:py-8 min-h-[calc(100vh-4rem)]">
        @yield('page-content')
    </main>

</body>
</html>