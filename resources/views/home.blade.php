@extends('layouts.guest')

@section('title', 'Créez votre boutique en ligne en un clic')

@section('content')

{{-- ============ BANNIÈRE HERO ============ --}}
<section
    x-data="{
        mx: 0, my: 0,
        onMove(e) {
            const r = e.currentTarget.getBoundingClientRect();
            this.mx = ((e.clientX - r.left) / r.width - 0.5) * 20;
            this.my = ((e.clientY - r.top) / r.height - 0.5) * 20;
        }
    }"
    @mousemove="onMove($event)"
    class="relative overflow-hidden bg-gradient-to-br from-primary-950 via-primary-800 to-primary-900"
    style="clip-path: polygon(0 0,100% 0,100% 96%,0 100%);"
>
    {{-- blobs animés --}}
    <div class="absolute -top-32 -right-24 w-[30rem] h-[30rem] bg-primary-500/30 rounded-full blur-3xl animate-float-a"></div>
    <div class="absolute bottom-0 -left-32 w-96 h-96 bg-accent-500/25 rounded-full blur-3xl animate-float-b"></div>
    <div class="absolute inset-0 opacity-[0.05]" style="background-image:radial-gradient(#fff 1px,transparent 1px);background-size:26px 26px;"></div>

    <div class="relative max-w-7xl mx-auto px-6 pt-14 md:pt-24 pb-24 md:pb-32">
        <div class="grid lg:grid-cols-2 gap-14 items-center">

            {{-- Texte --}}
            <div class="text-center lg:text-left reveal" x-intersect.once="$el.classList.add('reveal-in')">
                <span class="inline-flex items-center gap-2 bg-accent-500/15 border border-accent-400/30 text-accent-300 text-xs font-bold tracking-wide uppercase px-4 py-2 rounded-full mb-7">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent-400 animate-pulse-glow"></span>
                    Bienvenue dans un nouvel espace e-commerce
                </span>

                <h1 class="font-display text-4xl sm:text-5xl lg:text-[3.7rem] font-extrabold text-white leading-[1.1] tracking-tight">
                    Créez votre boutique en ligne
                    <span class="text-gradient">en un seul clic</span>
                </h1>

                <p class="mt-6 text-lg md:text-xl text-primary-100/90 max-w-xl mx-auto lg:mx-0">
                    Configurez votre boutique, ajoutez vos produits et obtenez un lien
                    unique à partager avec vos clients — sur Facebook, WhatsApp ou
                    partout ailleurs.
                </p>

                <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <button @click="authModalOpen = true"
                            class="btn-shine bg-accent-500 hover:bg-accent-600 text-white font-bold px-8 py-4 rounded-xl shadow-xl shadow-accent-900/30 hover:-translate-y-1 transition-all text-base">
                        Créer ma boutique gratuitement
                    </button>
                    <a href="#fonctionnalites"
                       class="bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold px-8 py-4 rounded-xl transition text-base text-center hover:-translate-y-1">
                        Voir comment ça marche
                    </a>
                </div>

                <div class="mt-10 flex items-center justify-center lg:justify-start gap-6 text-primary-100/80 text-sm">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-accent-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 111.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                        Sans carte bancaire
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-accent-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 111.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                        Prêt en 5 minutes
                    </div>
                </div>
            </div>

            {{-- Mockup visuel avec effet tilt 3D au survol --}}
            <div class="relative reveal" x-intersect.once="$el.classList.add('reveal-in')" style="transition-delay:.15s">
                <div
                    class="relative bg-white rounded-2xl shadow-2xl p-3 transition-transform duration-200 ease-out"
                    :style="`transform: perspective(1000px) rotateY(${mx*0.5}deg) rotateX(${-my*0.5}deg)`"
                >
                    <div class="rounded-xl overflow-hidden border border-gray-100">
                        <div class="bg-gray-50 px-4 py-3 flex items-center gap-2 border-b border-gray-100">
                            <span class="h-3 w-3 rounded-full bg-accent-400"></span>
                            <span class="h-3 w-3 rounded-full bg-yellow-400"></span>
                            <span class="h-3 w-3 rounded-full bg-green-400"></span>
                            <span class="ml-3 text-xs text-gray-400 font-medium">maboutique.exemple.com</span>
                        </div>
                        <div class="p-6 bg-white">
                            <div class="h-6 w-32 bg-primary-700 rounded-md mb-4"></div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-lg bg-gray-100 h-28"></div>
                                <div class="rounded-lg bg-gray-100 h-28"></div>
                                <div class="rounded-lg bg-primary-50 h-28 border-2 border-primary-200"></div>
                                <div class="rounded-lg bg-gray-100 h-28"></div>
                            </div>
                            <div class="mt-4 h-10 w-full bg-accent-500 rounded-lg"></div>
                        </div>
                    </div>
                </div>
                {{-- badge flottant animé --}}
                <div class="absolute -bottom-6 -left-6 bg-white rounded-xl shadow-xl px-5 py-3 flex items-center gap-3 animate-float-b">
                    <div class="h-10 w-10 rounded-full bg-accent-100 flex items-center justify-center text-accent-600 font-bold">✓</div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">Lien prêt</p>
                        <p class="text-xs text-gray-500">à partager partout</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bandeau de confiance --}}
        <div class="mt-16 md:mt-24 pt-10 border-t border-white/10 grid grid-cols-2 md:grid-cols-4 gap-8 text-center reveal"
             x-intersect.once="$el.classList.add('reveal-in')">
            @foreach([
                ['label' => '1 clic', 'text' => 'pour créer sa boutique'],
                ['label' => '100%', 'text' => 'personnalisable'],
                ['label' => '24/7', 'text' => 'boutique toujours en ligne'],
                ['label' => '∞', 'text' => 'partages sur vos réseaux'],
            ] as $stat)
            <div>
                <p class="text-3xl md:text-4xl font-extrabold text-white font-display">{{ $stat['label'] }}</p>
                <p class="text-primary-200 text-sm mt-1">{{ $stat['text'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ BANDE DE CONFIANCE DÉFILANTE ============ --}}
<div class="bg-gray-50 border-b border-gray-100 py-6 overflow-hidden">
    <div class="flex gap-16 whitespace-nowrap animate-marquee w-max text-gray-400 font-semibold text-sm tracking-widest uppercase">
        @for($i=0;$i<2;$i++)
            <span>Boutique en 1 clic</span>
            <span>•</span>
            <span>Thèmes personnalisés</span>
            <span>•</span>
            <span>Lien de partage unique</span>
            <span>•</span>
            <span>Sans code, sans effort</span>
            <span>•</span>
        @endfor
    </div>
</div>

{{-- ============ FONCTIONNALITÉS ============ --}}
<section id="fonctionnalites" class="max-w-7xl mx-auto px-6 py-20 md:py-28">
    <div class="text-center mb-16 max-w-2xl mx-auto reveal" x-intersect.once="$el.classList.add('reveal-in')">
        <span class="text-accent-600 font-bold text-sm uppercase tracking-wide">Fonctionnalités</span>
        <h2 class="font-display text-3xl md:text-4xl font-extrabold text-gray-900 mt-3">
            Tout ce qu'il vous faut pour vendre en ligne
        </h2>
        <p class="text-gray-600 mt-4">Une plateforme pensée pour les boutiques, simple, rapide et sans compétence technique.</p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach([
            ['num' => '1', 'title' => 'Configurez votre boutique', 'text' => 'Nom, adresse, thème : votre boutique prête en quelques minutes.', 'color' => 'primary'],
            ['num' => '2', 'title' => 'Ajoutez vos produits', 'text' => 'Photos, prix, descriptions : gérez votre catalogue facilement.', 'color' => 'primary'],
            ['num' => '3', 'title' => 'Choisissez un thème', 'text' => 'Plusieurs thèmes disponibles pour habiller votre lien boutique.', 'color' => 'accent'],
            ['num' => '4', 'title' => 'Partagez votre lien', 'text' => 'Un seul lien à partager sur Facebook, WhatsApp ou par SMS.', 'color' => 'accent'],
        ] as $i => $f)
        <div class="group p-7 rounded-2xl border border-gray-100 shadow-sm hover:shadow-2xl hover:-translate-y-2 hover:border-{{ $f['color'] }}-200 transition-all duration-300 bg-white reveal"
             x-intersect.once="$el.classList.add('reveal-in')" style="transition-delay: {{ $i * 0.1 }}s">
            <div class="h-12 w-12 rounded-xl bg-{{ $f['color'] }}-100 text-{{ $f['color'] }}-700 flex items-center justify-center font-extrabold text-xl mb-5 group-hover:scale-110 group-hover:rotate-6 transition-transform duration-300">
                {{ $f['num'] }}
            </div>
            <h3 class="font-bold text-gray-900 mb-2 text-lg">{{ $f['title'] }}</h3>
            <p class="text-sm text-gray-600 leading-relaxed">{{ $f['text'] }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- ============ AVIS ============ --}}
<section id="avis" class="bg-gray-50 py-20 md:py-28">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16 max-w-2xl mx-auto reveal" x-intersect.once="$el.classList.add('reveal-in')">
            <span class="text-accent-600 font-bold text-sm uppercase tracking-wide">Ils nous font confiance</span>
            <h2 class="font-display text-3xl md:text-4xl font-extrabold text-gray-900 mt-3">Des boutiques qui décollent</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach([
                ['nom' => 'Rina, mode', 'avis' => 'En un après-midi ma boutique était en ligne avec tous mes produits. Le lien partagé sur Facebook a fait le reste.'],
                ['nom' => 'Tojo, artisanat', 'avis' => 'Simple, rapide, et mes clients adorent pouvoir commander directement via le lien que je partage sur WhatsApp.'],
                ['nom' => 'Fanja, cosmétiques', 'avis' => 'Le thème de ma boutique correspond parfaitement à mon image de marque. Une vraie boutique pro sans effort.'],
            ] as $i => $t)
            <div class="bg-white rounded-2xl p-7 shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 reveal"
                 x-intersect.once="$el.classList.add('reveal-in')" style="transition-delay: {{ $i * 0.12 }}s">
                <div class="flex gap-1 text-accent-500 mb-4">
                    @for($i=0;$i<5;$i++)★@endfor
                </div>
                <p class="text-gray-700 leading-relaxed mb-5">« {{ $t['avis'] }} »</p>
                <p class="font-bold text-gray-900 text-sm">{{ $t['nom'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ TARIFS ============ --}}
<section id="tarifs" class="py-20 md:py-28">
    <div class="max-w-3xl mx-auto px-6 text-center reveal" x-intersect.once="$el.classList.add('reveal-in')">
        <span class="text-accent-600 font-bold text-sm uppercase tracking-wide">Tarifs</span>
        <h2 class="font-display text-3xl md:text-4xl font-extrabold text-gray-900 mt-3 mb-3">Un tarif simple et transparent</h2>
        <p class="text-gray-600 mb-12">Le prix s'adapte automatiquement à votre localisation.</p>

        <div class="relative bg-white rounded-3xl shadow-2xl border-2 border-primary-700 p-10 md:p-12 max-w-md mx-auto hover:shadow-primary-200 transition-shadow duration-500">
            <span class="absolute -top-4 left-1/2 -translate-x-1/2 bg-accent-500 text-white text-xs font-bold uppercase tracking-wide px-4 py-1.5 rounded-full animate-pulse-glow">
                Offre boutique
            </span>
            <div class="text-5xl md:text-6xl font-extrabold text-primary-700 mb-2 mt-3 font-display">{{ $pricing['formatted'] }}</div>
            <p class="text-gray-500 mb-8">par mois, sans engagement</p>
            <ul class="text-left space-y-3 mb-8 text-gray-700 text-sm">
                <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Boutique en ligne personnalisable</li>
                <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Produits illimités</li>
                <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Lien de partage unique</li>
                <li class="flex items-center gap-3"><span class="text-primary-700 font-bold">✓</span> Plusieurs thèmes disponibles</li>
            </ul>
            <button @click="authModalOpen = true"
                    class="btn-shine w-full bg-primary-700 hover:bg-primary-800 text-white font-bold py-4 rounded-xl transition shadow-lg shadow-primary-700/25 hover:-translate-y-0.5">
                Commencer maintenant
            </button>
        </div>
    </div>
</section>

{{-- ============ CTA FINAL ============ --}}
<section class="relative overflow-hidden bg-gradient-to-r from-primary-950 via-primary-800 to-primary-900 py-16 md:py-20">
    <div class="absolute -top-20 left-1/3 w-72 h-72 bg-accent-500/20 rounded-full blur-3xl animate-float-a"></div>
    <div class="relative max-w-4xl mx-auto px-6 text-center reveal" x-intersect.once="$el.classList.add('reveal-in')">
        <h2 class="font-display text-3xl md:text-4xl font-extrabold text-white mb-4">Prêt à lancer votre boutique ?</h2>
        <p class="text-primary-100 mb-8 text-lg">Rejoignez les vendeurs qui ont déjà simplifié leur e-commerce.</p>
        <button @click="authModalOpen = true"
                class="btn-shine bg-accent-500 hover:bg-accent-600 text-white font-bold px-10 py-4 rounded-xl shadow-xl hover:-translate-y-1 transition-all text-base">
            Créer ma boutique gratuitement
        </button>
    </div>
</section>

@endsection