@extends('layouts.dashboard')

@section('title', 'Ma boutique — Tafely')

@section('page-content')

    <div x-data="{ copied: false, lien: '{{ $user->lienBoutique() }}', copier() {
            navigator.clipboard.writeText(this.lien).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            });
        } }">

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
                    @foreach ($themes as $cle => $theme)
                        <label class="relative flex flex-col gap-1 p-4 rounded-xl border-2 border-gray-200 hover:border-gray-300 has-[:checked]:border-primary-600 has-[:checked]:bg-primary-50 cursor-pointer transition-colors">
                            <input type="radio" name="boutique_theme" value="{{ $cle }}" class="sr-only"
                                   {{ old('boutique_theme', $user->boutique_theme) === $cle ? 'checked' : '' }}>
                            <span class="font-body font-bold text-sm text-primary-900">{{ $theme['nom'] }}</span>
                            <span class="font-body text-xs text-gray-500">{{ $theme['description'] }}</span>
                        </label>
                    @endforeach
                </div>
                @error('boutique_theme')
                    <p class="mb-4 -mt-4 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                @enderror

                {{-- couleur --}}
                <p class="font-body text-sm font-semibold text-primary-900 mb-3">Couleur d'accent</p>
                <div class="flex flex-wrap gap-3">
                    @foreach ($couleurs as $cle => $hex)
                        <label class="cursor-pointer">
                            <input type="radio" name="boutique_couleur" value="{{ $cle }}" class="sr-only peer"
                                   {{ old('boutique_couleur', $user->boutique_couleur) === $cle ? 'checked' : '' }}>
                            <span class="h-10 w-10 rounded-full flex items-center justify-center border-2 border-transparent peer-checked:border-primary-700 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-primary-600 transition-all"
                                  style="background-color: {{ $hex }}"
                                  title="{{ ucfirst($cle) }}">
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('boutique_couleur')
                    <p class="mt-3 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                @enderror
            </section>

            <div class="flex justify-end mb-8">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-primary-800 hover:bg-primary-900 text-white font-body font-bold text-sm px-6 py-3 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-[0.98]">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Enregistrer l'apparence
                </button>
            </div>
        </form>

        {{-- aperçu --}}
        <section>
            <div class="flex items-center gap-2.5 mb-5">
                <span class="material-symbols-outlined text-primary-700 text-[22px]">visibility</span>
                <h2 class="font-display text-lg font-bold text-primary-900">Aperçu de votre vitrine</h2>
            </div>

            @php
                $couleurAccent = $couleurs[$user->boutique_couleur] ?? $couleurs['bleu'];
                $rayon = $user->boutique_theme === 'minimal' ? 'rounded-lg' : ($user->boutique_theme === 'moderne' ? 'rounded-2xl' : 'rounded-xl');
                $ombre = $user->boutique_theme === 'minimal' ? '' : ($user->boutique_theme === 'moderne' ? 'shadow-md' : 'shadow-sm');
                $bordure = $user->boutique_theme === 'minimal' ? '' : 'border border-gray-100';
            @endphp

            <div class="bg-gray-50 rounded-2xl p-5 md:p-8 border border-gray-100">
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
                            <div class="bg-white {{ $rayon }} {{ $ombre }} {{ $bordure }} overflow-hidden">
                                <div class="h-32 bg-gray-100 flex items-center justify-center overflow-hidden">
                                    @if ($produit->image)
                                        <img src="{{ asset('storage/'.$produit->image) }}" alt="{{ $produit->nom }}" class="h-full w-full object-cover">
                                    @else
                                        <span class="material-symbols-outlined text-gray-300 text-4xl">inventory_2</span>
                                    @endif
                                </div>
                                <div class="p-3.5">
                                    <p class="font-body font-semibold text-sm text-gray-900 truncate">{{ $produit->nom }}</p>
                                    <p class="font-display font-bold text-sm mt-0.5" style="color: {{ $couleurAccent }}">{{ $produit->prixFormate() }}</p>
                                    <button type="button" disabled
                                            class="w-full mt-3 text-white text-xs font-bold py-2 {{ $rayon === 'rounded-2xl' ? 'rounded-xl' : 'rounded-lg' }} cursor-default"
                                            style="background-color: {{ $couleurAccent }}">
                                        Commander
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <p class="font-body text-xs text-gray-400 mt-3">Aperçu indicatif — la vitrine publique interactive arrive bientôt.</p>
        </section>
    </div>

@endsection