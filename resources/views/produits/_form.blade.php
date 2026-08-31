@php
    $produit = $produit ?? null;
@endphp

<div x-data="{ livraison: '{{ old('livraison', $produit->livraison ?? 'aucune') }}' }" class="flex flex-col gap-6">

    {{-- Photo --}}
    <section class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
        <div class="flex items-center gap-2.5 mb-5 border-b border-gray-100 pb-4">
            <span class="material-symbols-outlined text-primary-700 text-[24px]">image</span>
            <h2 class="font-display text-lg font-bold text-primary-900">Photo du produit</h2>
        </div>

        <div class="flex items-center gap-5">
            <div class="h-20 w-20 rounded-xl bg-gray-100 overflow-hidden shrink-0 flex items-center justify-center">
                @if ($produit && $produit->image)
                    <img src="{{ asset('storage/'.$produit->image) }}" alt="{{ $produit->nom }}" class="h-full w-full object-cover">
                @else
                    <span class="material-symbols-outlined text-gray-300 text-3xl">inventory_2</span>
                @endif
            </div>
            <div class="flex-1">
                <input type="file" name="image" accept="image/*"
                       class="block w-full text-sm font-body text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 file:cursor-pointer cursor-pointer">
                @error('image')
                    <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                @enderror
                <p class="font-body text-xs text-gray-400 mt-1.5">JPG ou PNG, 4 Mo maximum. Optionnel.</p>
            </div>
        </div>
    </section>

    {{-- Infos générales --}}
    <section class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
        <div class="flex items-center gap-2.5 mb-5 border-b border-gray-100 pb-4">
            <span class="material-symbols-outlined text-primary-700 text-[24px]">inventory_2</span>
            <h2 class="font-display text-lg font-bold text-primary-900">Informations du produit</h2>
        </div>

        <div class="flex flex-col gap-5">
            <div>
                <label for="nom" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Nom du produit</label>
                <input id="nom" name="nom" type="text" required
                       value="{{ old('nom', $produit->nom ?? '') }}"
                       placeholder="ex : Sac en raphia tressé"
                       class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('nom') border-accent-400 @enderror">
                @error('nom')
                    <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Description</label>
                <textarea id="description" name="description" rows="3"
                          placeholder="Décrivez votre produit : matière, taille, particularités..."
                          class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors resize-none @error('description') border-accent-400 @enderror">{{ old('description', $produit->description ?? '') }}</textarea>
                @error('description')
                    <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="prix" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Prix (Ar)</label>
                    <input id="prix" name="prix" type="number" min="0" step="1" required
                           value="{{ old('prix', $produit->prix ?? '') }}"
                           placeholder="ex : 25000"
                           class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('prix') border-accent-400 @enderror">
                    @error('prix')
                        <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="stock" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Stock disponible</label>
                    <input id="stock" name="stock" type="number" min="0" step="1"
                           value="{{ old('stock', $produit->stock ?? '') }}"
                           placeholder="ex : 12"
                           class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('stock') border-accent-400 @enderror">
                    @error('stock')
                        <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                    @enderror
                    <p class="font-body text-xs text-gray-400 mt-1.5">Laissez vide si non suivi.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Livraison --}}
    <section class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
        <div class="flex items-center gap-2.5 mb-5 border-b border-gray-100 pb-4">
            <span class="material-symbols-outlined text-primary-700 text-[24px]">local_shipping</span>
            <h2 class="font-display text-lg font-bold text-primary-900">Livraison</h2>
        </div>

        <div class="grid sm:grid-cols-2 gap-3 mb-5">
            <label class="relative flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors"
                   :class="livraison === 'aucune' ? 'border-primary-600 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
                <input type="radio" name="livraison" value="aucune" x-model="livraison" class="mt-1 accent-primary-700">
                <span>
                    <span class="block font-body font-bold text-sm text-primary-900">Sans livraison</span>
                    <span class="block font-body text-xs text-gray-500 mt-0.5">Le client récupère la commande lui-même.</span>
                </span>
            </label>

            <label class="relative flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors"
                   :class="livraison === 'payante' ? 'border-primary-600 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
                <input type="radio" name="livraison" value="payante" x-model="livraison" class="mt-1 accent-primary-700">
                <span>
                    <span class="block font-body font-bold text-sm text-primary-900">Avec livraison</span>
                    <span class="block font-body text-xs text-gray-500 mt-0.5">Vous fixez un prix de livraison fixe.</span>
                </span>
            </label>
        </div>
        @error('livraison')
            <p class="mb-4 -mt-2 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
        @enderror

        <div x-show="livraison === 'payante'" x-cloak x-transition>
            <label for="prix_livraison" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Prix de la livraison (Ar)</label>
            <input id="prix_livraison" name="prix_livraison" type="number" min="0" step="1"
                   value="{{ old('prix_livraison', $produit->prix_livraison ?? '') }}"
                   placeholder="ex : 5000"
                   class="w-full sm:w-64 bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('prix_livraison') border-accent-400 @enderror">
            @error('prix_livraison')
                <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
            @enderror
            <p class="font-body text-xs text-gray-400 mt-1.5">Ce montant s'ajoutera au prix du produit à la commande.</p>
        </div>
    </section>
</div>