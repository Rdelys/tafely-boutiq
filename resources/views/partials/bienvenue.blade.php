{{-- Message de bienvenue : affiché une seule fois au nouveau visiteur (mémorisé dans son navigateur). --}}
<div
    x-data="{
        ouvert: false,
        init() {
            try {
                if (localStorage.getItem('tafely_bienvenue_vue')) return;
            } catch (e) { return; }
            setTimeout(() => { this.ouvert = true; }, 1500);
        },
        fermer() {
            this.ouvert = false;
            try { localStorage.setItem('tafely_bienvenue_vue', '1'); } catch (e) {}
        },
    }"
    x-show="ouvert"
    x-cloak
    @keydown.escape.window="fermer()"
    role="dialog" aria-modal="true" aria-labelledby="titre-bienvenue"
    class="fixed inset-0 z-[90] flex items-center justify-center p-4"
    style="display: none;"
>
    {{-- fond --}}
    <div class="absolute inset-0 bg-primary-950/70 backdrop-blur-sm"
         x-show="ouvert"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="fermer()"></div>

    {{-- carte --}}
    <div class="relative w-full max-w-sm overflow-y-auto bg-white rounded-3xl shadow-2xl px-6 py-8 text-center"
         style="max-height: 92vh; max-height: 92dvh;"
         x-show="ouvert"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 scale-95">

        <button type="button" @click="fermer()" aria-label="Fermer"
                class="absolute top-3 right-3 h-9 w-9 flex items-center justify-center rounded-full text-gray-400 hover:text-accent-600 hover:bg-gray-50 transition-colors">
            <span class="material-symbols-outlined text-[20px]">close</span>
        </button>

        <div class="mx-auto h-16 w-16 rounded-2xl bg-primary-50 flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-primary-700 text-[34px]" style="font-variation-settings: 'FILL' 1;">storefront</span>
        </div>

        <h2 id="titre-bienvenue" class="font-display text-2xl font-extrabold text-primary-900">Bienvenue sur Tafely&nbsp;!</h2>
        <p class="font-body text-sm text-gray-500 mt-2">
            Créez votre boutique en ligne en 5 minutes et partagez-la sur WhatsApp et Facebook.
        </p>

        {{-- Conditions du bonus de lancement --}}
        @if ($offreActive)
            <div class="mt-5 text-left bg-accent-50 border border-accent-100 rounded-2xl px-4 py-4">
                <p class="flex items-center gap-2 font-body text-sm font-bold text-accent-800">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">redeem</span>
                    Pour recevoir votre bonus
                </p>
                <ul class="mt-3 space-y-2 font-body text-xs text-accent-900 leading-relaxed">
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-[16px] mt-0.5 text-accent-600">check_circle</span>
                        <span><strong>Complétez votre compte</strong> : nom, catégorie, adresse, téléphone et position de la boutique.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-[16px] mt-0.5 text-accent-600">check_circle</span>
                        <span>Créez une <strong>vraie boutique</strong>.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-[16px] mt-0.5 text-accent-600">check_circle</span>
                        <span>Ajoutez des <strong>produits</strong>, qui seront <strong>vérifiés et validés par notre équipe</strong>.</span>
                    </li>
                </ul>
                <p class="mt-3 font-body text-[11px] text-accent-800/80">
                    Le bonus n'est attribué qu'après validation par l'administrateur.
                </p>
            </div>
        @endif

        <button type="button" @click="fermer()"
                class="mt-6 w-full bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm py-3.5 rounded-xl shadow-sm active:scale-[0.98] transition-all">
            Découvrir Tafely
        </button>
    </div>
</div>