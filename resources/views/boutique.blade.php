@extends('layouts.dashboard')

@section('title', 'Ma boutique — Tafely')

@section('page-content')

    <div x-data="{
            copied: false,
            lien: '{{ $user->lienBoutique() }}',
            theme: '{{ old('boutique_theme', $user->boutique_theme) }}',
            couleur: '{{ old('boutique_couleur', $user->boutique_couleur) }}',
            couleurPerso: '{{ old('boutique_couleur_perso', $user->boutique_couleur_perso ?: '#2563eb') }}',
            description: {{ \Illuminate\Support\Js::from(old('boutique_description', $user->boutique_description ?? '')) }},
            palette: {{ \Illuminate\Support\Js::from($couleurs) }},
            get couleurActive() {
                return this.couleur === 'perso' ? (this.couleurPerso || '#2563eb') : (this.palette[this.couleur] || '#2563eb');
            },
            get rayon() { return this.theme === 'minimal' ? 'rounded-lg' : (this.theme === 'moderne' ? 'rounded-2xl' : 'rounded-xl'); },
            get rayonBtn() { return this.theme === 'moderne' ? 'rounded-xl' : 'rounded-lg'; },
            get ombre() { return this.theme === 'minimal' ? '' : (this.theme === 'moderne' ? 'shadow-md' : 'shadow-sm'); },
            get bordure() { return this.theme === 'minimal' ? '' : 'border border-gray-100'; },
            copier() {
                navigator.clipboard.writeText(this.lien).then(() => {
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                });
            }
        }">

        {{-- header --}}
        <div class="mb-8">
            <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Ma boutique</h1>
            <p class="font-body text-gray-500 mt-1">Personnalisez l'apparence de votre vitrine et partagez son lien.</p>
        </div>

        {{-- succès --}}
        @if (session('status'))
            <div class="mb-6 flex items-center gap-3 bg-primary-50 border border-primary-100 text-primary-700 rounded-xl px-4 py-3">
                <span class="material-symbols-outlined text-[20px]">check_circle</span>
                <span class="font-body text-sm font-semibold">{{ session('status') }}</span>
            </div>
        @endif

        {{-- lien à partager --}}
        <section class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100 mb-6">
            <div class="flex items-center gap-2.5 mb-5 border-b border-gray-100 pb-4">
                <span class="material-symbols-outlined text-primary-700 text-[24px]">share</span>
                <h2 class="font-display text-lg font-bold text-primary-900">Lien de la boutique</h2>
            </div>

            <p class="font-body text-sm text-gray-500 mb-4">Partagez ce lien unique à vos clients sur Facebook, WhatsApp ou par SMS pour qu'ils puissent commander.</p>

            <div class="flex flex-col sm:flex-row gap-3">
                <input type="text" readonly :value="lien" onclick="this.select()"
                       class="flex-1 bg-gray-50 border border-gray-200 text-gray-700 rounded-lg px-3.5 py-2.5 font-body text-sm select-all">
                <button type="button" @click="copier()"
                        class="inline-flex items-center justify-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm px-5 py-2.5 rounded-lg shadow-sm transition-colors whitespace-nowrap">
                    <span class="material-symbols-outlined text-[18px]" x-show="!copied">content_copy</span>
                    <span class="material-symbols-outlined text-[18px]" x-show="copied" x-cloak>check</span>
                    <span x-text="copied ? 'Lien copié !' : 'Copier le lien'"></span>
                </button>
            </div>
        </section>

        {{-- apparence --}}
        <form method="POST" action="{{ route('boutique.update') }}">
            @csrf
            @method('PUT')

            <section class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100 mb-6">
                <div class="flex items-center gap-2.5 mb-5 border-b border-gray-100 pb-4">
                    <span class="material-symbols-outlined text-primary-700 text-[24px]">palette</span>
                    <h2 class="font-display text-lg font-bold text-primary-900">Apparence de la boutique</h2>
                </div>

                {{-- thème --}}
                <p class="font-body text-sm font-semibold text-primary-900 mb-3">Thème</p>
                <div class="grid sm:grid-cols-3 gap-3 mb-6">
                    @foreach ($themes as $cle => $themeInfo)
                        <label class="relative flex flex-col gap-1 p-4 rounded-xl border-2 cursor-pointer transition-colors"
                               :class="theme === '{{ $cle }}' ? 'border-primary-600 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="boutique_theme" value="{{ $cle }}" x-model="theme" class="sr-only">
                            <span class="font-body font-bold text-sm text-primary-900">{{ $themeInfo['nom'] }}</span>
                            <span class="font-body text-xs text-gray-500">{{ $themeInfo['description'] }}</span>
                        </label>
                    @endforeach
                </div>
                @error('boutique_theme')
                    <p class="mb-4 -mt-4 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                @enderror

                {{-- couleur --}}
                <p class="font-body text-sm font-semibold text-primary-900 mb-3">Couleur d'accent</p>
                <div class="flex flex-wrap items-center gap-3">
                    @foreach ($couleurs as $cle => $hex)
                        <label class="cursor-pointer">
                            <input type="radio" name="boutique_couleur" value="{{ $cle }}" x-model="couleur" class="sr-only">
                            <span class="h-10 w-10 rounded-full flex items-center justify-center border-2 transition-all"
                                  :class="couleur === '{{ $cle }}' ? 'border-primary-700 ring-2 ring-offset-2 ring-primary-600' : 'border-transparent'"
                                  style="background-color: {{ $hex }}"
                                  title="{{ ucfirst($cle) }}">
                            </span>
                        </label>
                    @endforeach

                    {{-- couleur personnalisée --}}
                    <label class="cursor-pointer">
                        <input type="radio" name="boutique_couleur" value="perso" x-model="couleur" class="sr-only">
                        <span class="h-10 w-10 rounded-full flex items-center justify-center border-2 transition-all"
                              :class="couleur === 'perso' ? 'border-primary-700 ring-2 ring-offset-2 ring-primary-600' : 'border-gray-200 bg-white'"
                              :style="couleur === 'perso' ? `background-color: ${couleurPerso}` : ''"
                              title="Couleur personnalisée">
                            <span x-show="couleur !== 'perso'" class="material-symbols-outlined text-gray-400 text-[20px]">colorize</span>
                        </span>
                    </label>
                </div>

                {{-- champ code couleur personnalisé --}}
                <div x-show="couleur === 'perso'" x-cloak x-transition class="flex items-center gap-3 mt-4">
                    <input type="color" x-model="couleurPerso"
                           class="h-11 w-11 rounded-lg border border-gray-200 cursor-pointer p-0.5">
                    <input type="text" x-model="couleurPerso" name="boutique_couleur_perso" maxlength="7"
                           placeholder="#2563EB"
                           class="w-40 bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body font-mono text-sm uppercase focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors">
                    <span class="font-body text-xs text-gray-400">Format #RRGGBB</span>
                </div>
                @error('boutique_couleur')
                    <p class="mt-3 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                @enderror
                @error('boutique_couleur_perso')
                    <p class="mt-3 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                @enderror
            </section>

            {{-- texte de présentation --}}
            <section class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100 mb-6">
                <div class="flex items-center gap-2.5 mb-5 border-b border-gray-100 pb-4">
                    <span class="material-symbols-outlined text-primary-700 text-[24px]">edit_note</span>
                    <h2 class="font-display text-lg font-bold text-primary-900">Texte de présentation</h2>
                </div>

                <label for="boutique_description" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">
                    Message affiché sous le nom de votre boutique
                </label>
                <textarea id="boutique_description" name="boutique_description" x-model="description" rows="3" maxlength="300"
                          placeholder="ex : Créations artisanales faites main à Antananarivo, livrées avec soin partout à Madagascar."
                          class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors resize-none @error('boutique_description') border-accent-400 @enderror"></textarea>
                <div class="flex justify-between items-center mt-1.5">
                    @error('boutique_description')
                        <p class="text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                    @else
                        <p class="font-body text-xs text-gray-400">Facultatif — donne le ton de votre boutique à vos visiteurs.</p>
                    @enderror
                    <p class="font-body text-xs text-gray-400 shrink-0 ml-3" x-text="description.length + ' / 300'"></p>
                </div>
            </section>

            <div class="flex justify-end mb-8">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-primary-800 hover:bg-primary-900 text-white font-body font-bold text-sm px-6 py-3 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-[0.98]">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Enregistrer l'apparence
                </button>
            </div>
        </form>

        {{-- aperçu en direct --}}
        <section>
            <div class="flex items-center gap-2.5 mb-5">
                <span class="material-symbols-outlined text-primary-700 text-[22px]">visibility</span>
                <h2 class="font-display text-lg font-bold text-primary-900">Aperçu en direct</h2>
                <span class="font-body text-xs text-gray-400">(se met à jour pendant que vous personnalisez)</span>
            </div>

            <div class="bg-gray-50 rounded-2xl border border-gray-100 overflow-hidden">
                {{-- mini en-tête vitrine --}}
                <div class="p-6 md:p-8 text-center" :style="`background-color: ${couleurActive}0d`">
                    <div class="h-14 w-14 mx-auto rounded-full flex items-center justify-center text-white font-display font-bold text-lg shadow-sm"
                         :style="`background-color: ${couleurActive}`">
                        {{ mb_strtoupper(mb_substr($user->nom_boutique ?: 'B', 0, 1)) }}
                    </div>
                    <p class="font-display font-bold text-primary-900 mt-3">{{ $user->nom_boutique ?: 'Ma boutique' }}</p>
                    <p class="font-body text-xs text-gray-500 mt-1 max-w-sm mx-auto" x-show="description" x-text="description"></p>
                </div>

                <div class="p-5 md:p-8 pt-0">
                    @if ($produits->isEmpty())
                        <div class="text-center py-14">
                            <span class="material-symbols-outlined text-gray-300 text-4xl">inventory_2</span>
                            <p class="font-body text-sm text-gray-500 mt-3">Ajoutez des produits pour voir l'aperçu de votre vitrine.</p>
                            <a href="{{ route('produits.create') }}" class="inline-flex items-center gap-2 mt-4 font-body text-sm font-semibold text-primary-700 hover:text-accent-600 transition-colors">
                                <span class="material-symbols-outlined text-[18px]">add</span>
                                Ajouter un produit
                            </a>
                        </div>
                    @else
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($produits as $produit)
                                <div class="bg-white overflow-hidden transition-all" :class="[rayon, ombre, bordure]">
                                    <div class="h-32 bg-gray-100 flex items-center justify-center overflow-hidden">
                                        @if ($produit->image)
                                            <img src="{{ asset('storage/'.$produit->image) }}" alt="{{ $produit->nom }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="material-symbols-outlined text-gray-300 text-4xl">inventory_2</span>
                                        @endif
                                    </div>
                                    <div class="p-3.5">
                                        <p class="font-body font-semibold text-sm text-gray-900 truncate">{{ $produit->nom }}</p>
                                        <p class="font-display font-bold text-sm mt-0.5" :style="`color: ${couleurActive}`">{{ $produit->prixFormate() }}</p>
                                        <button type="button" disabled
                                                class="w-full mt-3 text-white text-xs font-bold py-2 cursor-default transition-colors"
                                                :class="rayonBtn"
                                                :style="`background-color: ${couleurActive}`">
                                            Commander
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <p class="font-body text-xs text-gray-400 mt-3">
                Aperçu indicatif —
                <a href="{{ $user->lienBoutique() }}" target="_blank" rel="noopener" class="font-semibold text-primary-700 hover:text-accent-600 transition-colors">voir la vraie vitrine publique</a>.
            </p>
        </section>
    </div>

@endsection