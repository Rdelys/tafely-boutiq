<div
    x-show="authModalOpen"
    x-cloak
    x-data="{
        step: 1,
        email: '',
        code: '',
        loading: false,
        error: '',
        info: '',
        reset() {
            this.step = 1; this.email = ''; this.code = '';
            this.error = ''; this.info = ''; this.loading = false;
        },
        async sendOtp() {
            this.loading = true; this.error = '';
            try {
                const res = await fetch('{{ route('auth.send-otp') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email: this.email }),
                });
                const data = await res.json();
                if (!res.ok) { this.error = data.message || 'Erreur, réessayez.'; return; }
                this.info = data.message;
                this.step = 2;
            } catch (e) {
                this.error = 'Erreur réseau, réessayez.';
            } finally {
                this.loading = false;
            }
        },
        async verifyOtp() {
            this.loading = true; this.error = '';
            try {
                const res = await fetch('{{ route('auth.verify-otp') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email: this.email, code: this.code }),
                });
                const data = await res.json();
                if (!res.ok) { this.error = data.message || 'Code invalide.'; return; }
                window.location.href = data.redirect;
            } catch (e) {
                this.error = 'Erreur réseau, réessayez.';
            } finally {
                this.loading = false;
            }
        }
    }"
    @keydown.escape.window="authModalOpen = false"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>
    {{-- overlay --}}
    <div class="absolute inset-0 bg-gray-900/60" @click="authModalOpen = false; reset()"></div>

    {{-- modal --}}
    <div x-show="authModalOpen" x-transition class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-8">
        <button class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"
                @click="authModalOpen = false; reset()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="text-center mb-6">
            <img src="{{ asset('logo.png') }}" alt="Logo" class="h-10 mx-auto mb-3">
            <h3 class="text-xl font-bold text-gray-900" x-text="step === 1 ? 'Connexion / Inscription' : 'Vérification du code'"></h3>
            <p class="text-sm text-gray-500 mt-1" x-show="step === 1">Saisissez votre email, aucun mot de passe requis.</p>
            <p class="text-sm text-gray-500 mt-1" x-show="step === 2">Un code à 6 chiffres a été envoyé à <span class="font-medium" x-text="email"></span></p>
        </div>

        <p class="text-sm text-accent-600 text-center mb-3" x-show="error" x-text="error"></p>

        {{-- Étape 1 : email --}}
        <form x-show="step === 1" @submit.prevent="sendOtp">
            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse email</label>
            <input type="email" x-model="email" required placeholder="vous@exemple.com"
                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 mb-4 focus:ring-2 focus:ring-primary-600 focus:outline-none">
            <button type="submit" :disabled="loading"
                    class="w-full bg-primary-700 hover:bg-primary-800 text-white font-semibold py-2.5 rounded-lg transition disabled:opacity-50">
                <span x-show="!loading">Recevoir le code</span>
                <span x-show="loading">Envoi en cours...</span>
            </button>
        </form>

        {{-- Étape 2 : code OTP --}}
        <form x-show="step === 2" @submit.prevent="verifyOtp">
            <label class="block text-sm font-medium text-gray-700 mb-1">Code de vérification</label>
            <input type="text" inputmode="numeric" maxlength="6" x-model="code" required placeholder="123456"
                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 mb-4 text-center tracking-[0.5em] text-lg focus:ring-2 focus:ring-primary-600 focus:outline-none">
            <button type="submit" :disabled="loading"
                    class="w-full bg-primary-700 hover:bg-primary-800 text-white font-semibold py-2.5 rounded-lg transition disabled:opacity-50">
                <span x-show="!loading">Vérifier et se connecter</span>
                <span x-show="loading">Vérification...</span>
            </button>
            <button type="button" class="w-full text-sm text-gray-500 mt-3 hover:text-accent-600" @click="step = 1; code = ''">
                Changer d'adresse email
            </button>
        </form>
    </div>
</div>
