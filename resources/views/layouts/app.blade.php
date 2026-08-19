<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tableau de bord')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff', 100:'#dbeafe', 200:'#bfdbfe', 300:'#93c5fd',
                            400:'#60a5fa', 500:'#3b82f6', 600:'#2563eb', 700:'#1d4ed8',
                            800:'#1e40af', 900:'#1e3a8a',
                        },
                        accent: {
                            50:'#fef2f2', 100:'#fee2e2', 400:'#f87171', 500:'#ef4444',
                            600:'#dc2626', 700:'#b91c1c',
                        },
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased" x-data="{ sidebarOpen: false }">

    @php($user = auth()->user())

    {{-- Barre du haut : plus de menu marketing, juste identité + statut + déconnexion --}}
    <header class="bg-white border-b border-gray-100 sticky top-0 z-30">
        <div class="h-16 px-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button class="md:hidden text-gray-600" @click="sidebarOpen = !sidebarOpen">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <img src="{{ asset('logo.png') }}" alt="Logo" class="h-8">
            </div>

            <div class="flex items-center gap-4">
                <span class="hidden sm:inline text-sm font-medium text-gray-700">
                    {{ $user->hasPseudo() ? $user->pseudo : $user->email }}
                </span>
                <span @class([
                    'text-xs font-semibold px-3 py-1 rounded-full',
                    'bg-accent-100 text-accent-700' => $user->status === 'test',
                    'bg-primary-100 text-primary-700' => $user->status === 'active',
                    'bg-gray-100 text-gray-600' => $user->status === 'free',
                ])>
                    {{ $user->statusLabel() }}
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-gray-500 hover:text-accent-600">
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="flex">
        {{-- Menu latéral --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
            class="fixed md:static top-16 left-0 bottom-0 w-64 bg-white border-r border-gray-100 transition-transform z-20"
        >
            <nav class="p-4 space-y-1">
                <a href="{{ route('dashboard') }}"
                   @class(['flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium',
                          'bg-primary-50 text-primary-700' => request()->routeIs('dashboard'),
                          'text-gray-600 hover:bg-gray-50' => ! request()->routeIs('dashboard')])>
                    Dashboard
                </a>
                <a href="{{ route('boutique') }}"
                   @class(['flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium',
                          'bg-primary-50 text-primary-700' => request()->routeIs('boutique'),
                          'text-gray-600 hover:bg-gray-50' => ! request()->routeIs('boutique')])>
                    Boutique
                </a>
                <a href="{{ route('commande') }}"
                   @class(['flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium',
                          'bg-primary-50 text-primary-700' => request()->routeIs('commande'),
                          'text-gray-600 hover:bg-gray-50' => ! request()->routeIs('commande')])>
                    Commande
                </a>
                <a href="{{ route('parametres') }}"
                   @class(['flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium',
                          'bg-primary-50 text-primary-700' => request()->routeIs('parametres'),
                          'text-gray-600 hover:bg-gray-50' => ! request()->routeIs('parametres')])>
                    Paramètres
                </a>
            </nav>
        </aside>

        <main class="flex-1 p-6 md:ml-0">
            @yield('content')
        </main>
    </div>

</body>
</html>
