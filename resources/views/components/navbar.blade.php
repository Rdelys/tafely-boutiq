<header
    x-data="{ scrolled: false }"
    x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 10)"
    :class="scrolled ? 'shadow-lg bg-white/95 h-16 md:h-20' : 'bg-white/70 h-20 md:h-24'"
    class="sticky top-0 z-40 backdrop-blur-md border-b border-gray-100 transition-all duration-300"
    x-cloak
    x-show="true"
    x-transition:enter="transition ease-out duration-700"
    x-transition:enter-start="opacity-0 -translate-y-6"
    x-transition:enter-end="opacity-100 translate-y-0"
>
    <nav class="max-w-7xl mx-auto px-6 h-full flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0 group">
            <img src="{{ asset('logo.png') }}" alt="Logo"
                 :class="scrolled ? 'h-11 md:h-14' : 'h-14 md:h-20'"
                 class="w-auto transition-all duration-300 group-hover:scale-105">
        </a>

        {{-- Menu desktop --}}
        <div class="hidden md:flex items-center gap-10 text-[15px] font-semibold text-gray-600">
            <a href="#fonctionnalites" class="relative hover:text-primary-700 transition group">
                Fonctionnalités
                <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-accent-500 transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="#tarifs" class="relative hover:text-primary-700 transition group">
                Tarifs
                <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-accent-500 transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="#avis" class="relative hover:text-primary-700 transition group">
                Avis
                <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-accent-500 transition-all duration-300 group-hover:w-full"></span>
            </a>
            <button
                @click="authModalOpen = true"
                class="btn-shine bg-primary-700 hover:bg-primary-800 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-primary-700/25 hover:shadow-primary-700/40 hover:-translate-y-0.5 transition-all"
            >
                Connexion
            </button>
        </div>

        {{-- Hamburger mobile --}}
        <button class="md:hidden text-gray-700 p-2" @click="mobileMenuOpen = !mobileMenuOpen" aria-label="Menu">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </nav>

    {{-- Dropdown mobile --}}
    <div x-show="mobileMenuOpen" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="md:hidden absolute w-full border-t border-gray-100 bg-white px-6 py-5 space-y-4 shadow-xl">
        <a href="#fonctionnalites" class="block text-gray-700 font-semibold text-base" @click="mobileMenuOpen = false">Fonctionnalités</a>
        <a href="#tarifs" class="block text-gray-700 font-semibold text-base" @click="mobileMenuOpen = false">Tarifs</a>
        <a href="#avis" class="block text-gray-700 font-semibold text-base" @click="mobileMenuOpen = false">Avis</a>
        <button
            @click="mobileMenuOpen = false; authModalOpen = true"
            class="w-full bg-primary-700 hover:bg-primary-800 text-white px-6 py-3.5 rounded-xl font-bold transition"
        >
            Connexion
        </button>
    </div>
</header>