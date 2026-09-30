@extends('layouts.dashboard')

@section('title', 'Paramètres — Tafely')

@section('page-content')

    {{-- header --}}
    <div class="mb-8">
        <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Paramètres de la boutique</h1>
        <p class="font-body text-gray-500 mt-1">Gérez les informations générales et les préférences de notification de votre boutique.</p>
    </div>

    {{-- succès --}}
    @if (session('status'))
        <div class="max-w-2xl mb-6 flex items-center gap-3 bg-primary-50 border border-primary-100 text-primary-700 rounded-xl px-4 py-3">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span class="font-body text-sm font-semibold">{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('parametres.update') }}" enctype="multipart/form-data" class="max-w-2xl flex flex-col gap-6">
        @csrf
        @method('PUT')

        {{-- Section 1 : infos générales --}}
        <section class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
            <div class="flex items-center gap-2.5 mb-5 border-b border-gray-100 pb-4">
                <span class="material-symbols-outlined text-primary-700 text-[24px]">store</span>
                <h2 class="font-display text-lg font-bold text-primary-900">Informations générales</h2>
            </div>

            <div>
                <label for="nom_boutique" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Nom de la boutique</label>
                <input
                    id="nom_boutique"
                    name="nom_boutique"
                    type="text"
                    value="{{ old('nom_boutique', $user->nom_boutique) }}"
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('nom_boutique') border-accent-400 @enderror"
                >
                @error('nom_boutique')
                    <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                @enderror
                <p class="font-body text-xs text-gray-400 mt-1.5">Ce nom apparaîtra sur votre vitrine publique et sur les reçus des clients.</p>
            </div>

            <div class="mt-5">
                <label class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Logo de la boutique</label>
                <div class="flex items-center gap-4">
                    <div class="h-16 w-16 rounded-xl bg-gray-50 border border-gray-200 overflow-hidden flex items-center justify-center shrink-0">
                        @if ($user->logo)
                            <img src="{{ asset('storage/'.$user->logo) }}" alt="Logo actuel" class="h-full w-full object-cover">
                        @else
                            <span class="material-symbols-outlined text-gray-300 text-2xl">storefront</span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <input type="file" name="logo" accept="image/*"
                               class="block w-full text-sm font-body text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 file:cursor-pointer cursor-pointer">
                        @error('logo')
                            <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                        @enderror
                        <p class="font-body text-xs text-gray-400 mt-1.5">Affiché sur votre tableau de bord et votre vitrine publique. JPG/PNG, 2 Mo max.</p>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <label for="adresse" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Adresse de la boutique</label>
                <input
                    id="adresse"
                    name="adresse"
                    type="text"
                    value="{{ old('adresse', $user->adresse) }}"
                    placeholder="ex : Lot II M 45 Antananarivo, Madagascar"
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('adresse') border-accent-400 @enderror"
                >
                @error('adresse')
                    <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                @enderror
                <p class="font-body text-xs text-gray-400 mt-1.5">Utilisée pour les livraisons et affichée sur votre vitrine si activée.</p>
            </div>

            <div class="mt-5">
                <label for="telephone" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Numéro de téléphone</label>
                <input
                    id="telephone"
                    name="telephone"
                    type="tel"
                    value="{{ old('telephone', $user->telephone) }}"
                    placeholder="ex : 034 00 333 20"
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('telephone') border-accent-400 @enderror"
                >
                @error('telephone')
                    <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                @enderror
                <p class="font-body text-xs text-gray-400 mt-1.5">Affiché sur le reçu que reçoivent vos clients après une commande.</p>
            </div>
            <div class="mt-5 grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="nif" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">
                        NIF <span class="font-normal text-gray-400">(facultatif)</span>
                    </label>
                    <input
                        id="nif"
                        name="nif"
                        type="text"
                        value="{{ old('nif', $user->nif) }}"
                        placeholder="ex : 1234567890"
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('nif') border-accent-400 @enderror"
                    >
                    @error('nif')
                        <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="stat" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">
                        STAT <span class="font-normal text-gray-400">(facultatif)</span>
                    </label>
                    <input
                        id="stat"
                        name="stat"
                        type="text"
                        value="{{ old('stat', $user->stat) }}"
                        placeholder="ex : 12345 11 2020 0 12345"
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('stat') border-accent-400 @enderror"
                    >
                    @error('stat')
                        <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <p class="font-body text-xs text-gray-400 mt-1.5">Affichés sur votre boutique publique si renseignés, pour rassurer vos clients.</p>
        </section>

        {{-- Section 2 : localisation exacte sur Google Maps --}}
        @php
            $latInit = old('latitude', $user->latitude);
            $lngInit = old('longitude', $user->longitude);
            $donneesCarte = [
                'lat' => ($latInit === null || $latInit === '') ? null : (float) $latInit,
                'lng' => ($lngInit === null || $lngInit === '') ? null : (float) $lngInit,
                'libelle' => old('localisation_libelle', $user->localisation_libelle) ?? '',
                'cle' => $googleMapsKey,
            ];
        @endphp

        <section class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100"
                 x-data="localisationBoutique({{ \Illuminate\Support\Js::from($donneesCarte) }})"
                 @keydown.escape.window="ouvert = false"
                 @gmaps-auth-failure.window="erreur = 'La clé Google Maps est refusée. Vérifiez sa configuration.'">

            <div class="flex items-center gap-2.5 mb-5 border-b border-gray-100 pb-4">
                <span class="material-symbols-outlined text-primary-700 text-[24px]">location_on</span>
                <h2 class="font-display text-lg font-bold text-primary-900">Localisation de la boutique</h2>
            </div>

            <p class="font-body text-sm text-gray-500 mb-5">
                Placez votre boutique sur la carte : vos clients la retrouveront facilement depuis leur téléphone,
                avec l'itinéraire jusqu'à votre porte.
            </p>

            {{-- valeurs envoyées avec le formulaire --}}
            <input type="hidden" name="latitude" :value="lat ?? ''">
            <input type="hidden" name="longitude" :value="lng ?? ''">
            <input type="hidden" name="localisation_libelle" :value="libelle">

            @error('latitude')
                <p class="mb-3 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
            @enderror
            @error('longitude')
                <p class="mb-3 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
            @enderror

            {{-- position enregistrée --}}
            <template x-if="lat !== null">
                <div class="rounded-2xl border border-gray-100 overflow-hidden">
                    <iframe :src="`https://www.google.com/maps?q=${lat},${lng}&z=16&output=embed`"
                            class="w-full h-44 border-0 pointer-events-none" loading="lazy" title="Aperçu de la position de la boutique"></iframe>
                    <div class="p-4 bg-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-body text-sm font-semibold text-primary-900 truncate" x-text="libelle || 'Position choisie sur la carte'"></p>
                            <p class="font-body text-xs text-gray-400 mt-0.5" x-text="lat.toFixed(6) + ', ' + lng.toFixed(6)"></p>
                        </div>
                        <a :href="`https://www.google.com/maps/search/?api=1&query=${lat},${lng}`" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 text-xs font-body font-bold text-primary-700 hover:text-accent-600 transition-colors shrink-0">
                            <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                            Voir sur Google Maps
                        </a>
                    </div>
                </div>
            </template>

            <template x-if="lat === null">
                <div class="rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 px-5 py-8 text-center">
                    <span class="material-symbols-outlined text-gray-300 text-4xl">add_location_alt</span>
                    <p class="font-body text-sm text-gray-500 mt-2">Aucune position enregistrée pour le moment.</p>
                </div>
            </template>

            <p x-show="modifie" x-cloak class="mt-3 flex items-center gap-2 font-body text-xs font-semibold text-accent-700 bg-accent-50 border border-accent-100 rounded-lg px-3 py-2">
                <span class="material-symbols-outlined text-[16px]">info</span>
                Position modifiée : cliquez sur « Sauvegarder les modifications » pour l'enregistrer.
            </p>

            <div class="mt-4 flex flex-wrap gap-3">
                <button type="button" @click="ouvrir()"
                        class="inline-flex items-center gap-2 bg-primary-800 hover:bg-primary-900 text-white font-body font-bold text-sm px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-[18px]" x-text="lat === null ? 'add_location_alt' : 'edit_location_alt'"></span>
                    <span x-text="lat === null ? 'Placer ma boutique sur la carte' : 'Modifier la position'"></span>
                </button>
                <button type="button" x-show="lat !== null" x-cloak @click="retirer()"
                        class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 font-body font-bold text-sm px-5 py-2.5 rounded-xl transition-colors">
                    <span class="material-symbols-outlined text-[18px]">location_off</span>
                    Retirer la position
                </button>
            </div>

            {{-- ============ MODAL CARTE ============ --}}
            <div x-show="ouvert" x-cloak class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center sm:p-6" style="display: none;">
                <div class="absolute inset-0 bg-primary-950/60 backdrop-blur-sm"
                     x-show="ouvert"
                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     @click="ouvert = false"></div>

                <div class="relative w-full sm:max-w-3xl max-h-[95vh] flex flex-col bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl overflow-hidden"
                     x-show="ouvert"
                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-6">

                    {{-- en-tête --}}
                    <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-100">
                        <div>
                            <h3 class="font-display text-lg font-bold text-primary-900">Où se trouve votre boutique ?</h3>
                            <p class="font-body text-xs text-gray-400 mt-0.5">Touchez la carte ou déplacez le repère pour être précis.</p>
                        </div>
                        <button type="button" @click="ouvert = false" aria-label="Fermer"
                                class="h-9 w-9 shrink-0 flex items-center justify-center rounded-full text-gray-400 hover:text-accent-600 hover:bg-gray-50 transition-colors">
                            <span class="material-symbols-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    {{-- recherche --}}
                    <div class="px-5 pt-4 flex flex-col sm:flex-row gap-2">
                        <div class="relative flex-1">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[20px]">search</span>
                            <input type="search" x-model="recherche" @keydown.enter.prevent="rechercher()"
                                   placeholder="Rechercher un quartier, une rue, un repère..."
                                   class="w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl bg-white text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-body text-sm">
                        </div>
                        <div class="flex gap-2">
                            <button type="button" @click="rechercher()" :disabled="recherchant"
                                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-primary-800 hover:bg-primary-900 disabled:opacity-60 text-white font-body font-bold text-sm px-5 py-2.5 rounded-xl transition-colors">
                                <span x-text="recherchant ? 'Recherche...' : 'Rechercher'"></span>
                            </button>
                            <button type="button" @click="maPosition()" title="Utiliser ma position actuelle"
                                    class="inline-flex items-center justify-center gap-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-primary-700 font-body font-bold text-sm px-4 py-2.5 rounded-xl transition-colors">
                                <span class="material-symbols-outlined text-[20px]">my_location</span>
                                <span class="hidden sm:inline">Ma position</span>
                            </button>
                        </div>
                    </div>

                    <p x-show="erreur" x-cloak x-text="erreur"
                       class="mx-5 mt-3 text-xs font-body font-semibold text-accent-700 bg-accent-50 border border-accent-100 rounded-lg px-3 py-2"></p>

                    {{-- carte --}}
                    <div class="px-5 pt-3">
                        <div class="relative rounded-2xl overflow-hidden border border-gray-200 bg-gray-100">
                            <div x-ref="carte" class="h-[50vh] sm:h-[420px] w-full"></div>

                            <div x-show="chargement" x-cloak class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-gray-100">
                                <span class="material-symbols-outlined text-primary-700 text-4xl animate-spin" style="animation-duration: 1.5s;">progress_activity</span>
                                <p class="font-body text-sm text-gray-500">Chargement de la carte...</p>
                            </div>

                            <div x-show="! cle" x-cloak class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-gray-100 px-6 text-center">
                                <span class="material-symbols-outlined text-gray-300 text-5xl">map</span>
                                <p class="font-body text-sm text-gray-500">La carte n'est pas encore configurée (clé Google Maps manquante). Contactez le support.</p>
                            </div>
                        </div>
                    </div>

                    {{-- pied --}}
                    <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="min-w-0">
                            <template x-if="tmpLat !== null">
                                <div>
                                    <p class="font-body text-sm font-semibold text-primary-900 truncate" x-text="tmpLibelle || 'Position sélectionnée'"></p>
                                    <p class="font-body text-xs text-gray-400" x-text="tmpLat.toFixed(6) + ', ' + tmpLng.toFixed(6)"></p>
                                </div>
                            </template>
                            <p x-show="tmpLat === null" class="font-body text-sm text-gray-400">Aucun point sélectionné.</p>
                        </div>
                        <div class="flex gap-2 shrink-0">
                            <button type="button" @click="ouvert = false"
                                    class="flex-1 sm:flex-none bg-gray-50 hover:bg-gray-100 text-gray-600 font-body font-bold text-sm px-5 py-2.5 rounded-xl transition-colors">
                                Annuler
                            </button>
                            <button type="button" @click="valider()" :disabled="tmpLat === null"
                                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-accent-500 hover:bg-accent-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-body font-bold text-sm px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                                <span class="material-symbols-outlined text-[18px]">check</span>
                                Confirmer cette position
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Section 3 : notifications de commande --}}
        <section class="bg-white rounded-2xl p-5 md:p-7 shadow-sm border border-gray-100">
            <div class="flex items-center gap-2.5 mb-5 border-b border-gray-100 pb-4">
                <span class="material-symbols-outlined text-primary-700 text-[24px]">mail</span>
                <h2 class="font-display text-lg font-bold text-primary-900">Notifications de commande</h2>
            </div>
            <p class="font-body text-sm text-gray-500 mb-5">Définissez les adresses email qui recevront une alerte pour chaque nouvelle commande sur votre boutique.</p>

            <div class="space-y-5">
                <div>
                    <label for="email_notification" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">
                        Email principal <span class="text-accent-500">*</span>
                    </label>
                    <input
                        id="email_notification"
                        name="email_notification"
                        type="email"
                        required
                        value="{{ old('email_notification', $user->email_notification ?? $user->email) }}"
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('email_notification') border-accent-400 @enderror"
                    >
                    @error('email_notification')
                        <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                    @enderror
                    <p class="font-body text-xs text-gray-400 mt-1.5">L'adresse principale pour toutes les communications de vente.</p>
                </div>

                <div>
                    <label for="email_notification_secondaire" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">
                        Email secondaire (optionnel)
                    </label>
                    <input
                        id="email_notification_secondaire"
                        name="email_notification_secondaire"
                        type="email"
                        placeholder="ex: associe@maboutique.mg"
                        value="{{ old('email_notification_secondaire', $user->email_notification_secondaire) }}"
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors @error('email_notification_secondaire') border-accent-400 @enderror"
                    >
                    @error('email_notification_secondaire')
                        <p class="mt-1.5 text-xs font-body font-semibold text-accent-600">{{ $message }}</p>
                    @enderror
                    <p class="font-body text-xs text-gray-400 mt-1.5">Ajoutez une deuxième adresse pour qu'un collaborateur soit également notifié.</p>
                </div>
            </div>
        </section>

        {{-- action --}}
        <div class="flex justify-end pb-4">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm px-6 py-3 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-[0.98]">
                <span class="material-symbols-outlined text-[18px]">save</span>
                Sauvegarder les modifications
            </button>
        </div>
    </form>

    <script>
        // Charge l'API Google Maps une seule fois, à la première ouverture du modal.
        function chargerGoogleMaps(cle) {
            if (window.google && window.google.maps) return Promise.resolve();
            if (window.__gmapsPromise) return window.__gmapsPromise;

            window.__gmapsPromise = new Promise(function (resolve, reject) {
                window.__gmapsInit = function () { resolve(); };
                window.gm_authFailure = function () {
                    document.dispatchEvent(new CustomEvent('gmaps-auth-failure'));
                    window.dispatchEvent(new CustomEvent('gmaps-auth-failure'));
                };

                var s = document.createElement('script');
                s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(cle)
                    + '&callback=__gmapsInit&language=fr&region=MG';
                s.async = true;
                s.onerror = function () {
                    window.__gmapsPromise = null;
                    reject(new Error('chargement'));
                };
                document.head.appendChild(s);
            });

            return window.__gmapsPromise;
        }

        function localisationBoutique(init) {
            // Les objets Google Maps restent hors de l'état Alpine (ils ne supportent pas les proxys).
            var carte = null, marqueur = null, geocodeur = null;
            var CENTRE_TANA = { lat: -18.8792, lng: 47.5079 };

            return {
                cle: init.cle,
                lat: init.lat,
                lng: init.lng,
                libelle: init.libelle || '',
                modifie: false,

                ouvert: false,
                chargement: false,
                recherchant: false,
                erreur: '',
                recherche: '',

                tmpLat: null,
                tmpLng: null,
                tmpLibelle: '',

                async ouvrir() {
                    this.erreur = '';
                    this.tmpLat = this.lat;
                    this.tmpLng = this.lng;
                    this.tmpLibelle = this.libelle;
                    this.ouvert = true;

                    if (! this.cle) return;

                    this.chargement = true;
                    try {
                        await chargerGoogleMaps(this.cle);
                    } catch (e) {
                        this.chargement = false;
                        this.erreur = 'Impossible de charger Google Maps. Vérifiez votre connexion et réessayez.';
                        return;
                    }
                    this.chargement = false;

                    await this.$nextTick();
                    this.initCarte();

                    // Première ouverture sans position : on tente l'adresse déjà saisie.
                    if (this.tmpLat === null && ! this.recherche) {
                        var champAdresse = document.getElementById('adresse');
                        if (champAdresse && champAdresse.value.trim()) {
                            this.recherche = champAdresse.value.trim();
                            this.rechercher();
                        }
                    }
                },

                initCarte() {
                    var aPoint = this.tmpLat !== null;
                    var centre = aPoint ? { lat: this.tmpLat, lng: this.tmpLng } : CENTRE_TANA;

                    if (! carte) {
                        geocodeur = new google.maps.Geocoder();
                        carte = new google.maps.Map(this.$refs.carte, {
                            center: centre,
                            zoom: aPoint ? 17 : 12,
                            mapTypeControl: true,
                            streetViewControl: false,
                            fullscreenControl: false,
                            gestureHandling: 'greedy',
                        });
                        marqueur = new google.maps.Marker({ draggable: true, animation: google.maps.Animation.DROP });

                        var self = this;
                        carte.addListener('click', function (e) { self.placer(e.latLng); });
                        marqueur.addListener('dragend', function (e) { self.placer(e.latLng); });
                    } else {
                        google.maps.event.trigger(carte, 'resize');
                        carte.setCenter(centre);
                        carte.setZoom(aPoint ? 17 : 12);
                    }

                    if (aPoint) {
                        marqueur.setPosition(centre);
                        marqueur.setMap(carte);
                    } else {
                        marqueur.setMap(null);
                    }
                },

                // Pose le repère, mémorise les coordonnées et lit l'adresse correspondante.
                placer(latLng) {
                    var self = this;
                    marqueur.setPosition(latLng);
                    marqueur.setMap(carte);

                    this.tmpLat = Number(latLng.lat().toFixed(7));
                    this.tmpLng = Number(latLng.lng().toFixed(7));
                    this.tmpLibelle = '';
                    this.erreur = '';

                    geocodeur.geocode({ location: latLng }, function (resultats, statut) {
                        if (statut === 'OK' && resultats && resultats[0]) {
                            self.tmpLibelle = resultats[0].formatted_address;
                        }
                    });
                },

                rechercher() {
                    var q = this.recherche.trim();
                    if (! q || ! geocodeur) return;

                    var self = this;
                    this.erreur = '';
                    this.recherchant = true;

                    geocodeur.geocode({ address: q, region: 'mg' }, function (resultats, statut) {
                        self.recherchant = false;

                        if (statut !== 'OK' || ! resultats || ! resultats[0]) {
                            self.erreur = 'Adresse introuvable. Essayez un quartier ou un repère connu, ou touchez directement la carte.';
                            return;
                        }

                        var position = resultats[0].geometry.location;
                        carte.setCenter(position);
                        carte.setZoom(17);
                        self.placer(position);
                    });
                },

                maPosition() {
                    var self = this;

                    if (! carte) return;
                    if (! navigator.geolocation) {
                        this.erreur = 'La localisation n\'est pas disponible sur cet appareil.';
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(function (pos) {
                        var position = new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude);
                        carte.setCenter(position);
                        carte.setZoom(18);
                        self.placer(position);
                    }, function () {
                        self.erreur = 'Impossible d\'obtenir votre position. Autorisez la localisation ou touchez la carte.';
                    }, { enableHighAccuracy: true, timeout: 10000 });
                },

                valider() {
                    if (this.tmpLat === null) return;
                    this.lat = this.tmpLat;
                    this.lng = this.tmpLng;
                    this.libelle = this.tmpLibelle;
                    this.modifie = true;
                    this.ouvert = false;
                },

                retirer() {
                    this.lat = null;
                    this.lng = null;
                    this.libelle = '';
                    this.modifie = true;
                },
            };
        }
    </script>

@endsection