<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion admin — Tafely</title>

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
                        accent: { 50:'#fef2f2',500:'#ef4444',600:'#dc2626' },
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
<body class="bg-primary-950 min-h-screen flex items-center justify-center p-4">

    <div
        x-data="{
            step: 'email',
            email: '',
            code: ['', '', '', '', '', ''],
            loading: false,
            error: '',
            info: '',

            csrf() { return document.querySelector('meta[name=csrf-token]').getAttribute('content'); },

            async sendOtp() {
                if (! this.email) { this.error = 'Merci de renseigner votre adresse email.'; return; }
                this.error = ''; this.info = ''; this.loading = true;
                try {
                    const res = await fetch('{{ route('admin.otp.send') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                        body: JSON.stringify({ email: this.email }),
                    });
                    const data = await res.json();
                    if (! res.ok) { this.error = data.message || 'Une erreur est survenue.'; return; }
                    this.info = data.message;
                    this.step = 'code';
                    this.code = ['', '', '', '', '', ''];
                    this.$nextTick(() => document.getElementById('otp-0')?.focus());
                } catch (e) {
                    this.error = 'Connexion impossible. Réessayez.';
                } finally { this.loading = false; }
            },

            async verifyOtp() {
                const code = this.code.join('');
                if (code.length !== 6) { this.error = 'Entrez les 6 chiffres du code.'; return; }
                this.error = ''; this.loading = true;
                try {
                    const res = await fetch('{{ route('admin.otp.verify') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                        body: JSON.stringify({ email: this.email, code }),
                    });
                    const data = await res.json();
                    if (! res.ok) { this.error = data.message || 'Code invalide ou expiré.'; return; }
                    window.location.href = data.redirect;
                } catch (e) {
                    this.error = 'Connexion impossible. Réessayez.';
                } finally { this.loading = false; }
            },

            otpInput(index, event) {
                const val = event.target.value.replace(/[^0-9]/g, '').slice(-1);
                this.code[index] = val;
                if (val && index < 5) document.getElementById('otp-' + (index + 1))?.focus();
            },
            otpBackspace(index, event) {
                if (event.key === 'Backspace' && ! this.code[index] && index > 0) {
                    document.getElementById('otp-' + (index - 1))?.focus();
                }
            },
            otpPaste(event) {
                const text = (event.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                if (! text) return;
                event.preventDefault();
                this.code = text.split('').concat(['', '', '', '', '', '']).slice(0, 6);
                this.$nextTick(() => document.getElementById('otp-' + Math.min(text.length, 5))?.focus());
            },
        }"
        class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-8 sm:p-10"
    >
        <div class="flex flex-col items-center gap-2 mb-8">
            <span class="material-symbols-outlined text-accent-500 text-3xl" style="font-variation-settings: 'FILL' 1;">shield_person</span>
            <h1 class="font-display text-xl font-bold text-primary-900">Administration Tafely</h1>
            <p class="font-body text-sm text-gray-500 text-center">Accès réservé — connexion par code de vérification.</p>
        </div>

        <p x-show="error" x-cloak x-text="error"
           class="mb-4 text-sm font-body font-semibold text-accent-700 bg-accent-50 border border-accent-100 rounded-lg px-4 py-2.5"></p>
        <p x-show="info && step === 'code'" x-cloak x-text="info"
           class="mb-4 text-sm font-body font-semibold text-primary-700 bg-primary-50 border border-primary-100 rounded-lg px-4 py-2.5"></p>

        {{-- ÉTAPE EMAIL --}}
        <div x-show="step === 'email'" x-cloak>
            <form @submit.prevent="sendOtp()" class="space-y-5">
                <div>
                    <label for="email" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Adresse email</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[20px]">mail</span>
                        <input id="email" type="email" required autocomplete="email" placeholder="Mail"
                               x-model="email"
                               class="w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl bg-white text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors font-body text-sm">
                    </div>
                </div>
                <button type="submit" :disabled="loading"
                        class="w-full flex justify-center items-center gap-2 py-3.5 px-4 rounded-xl font-body font-bold text-sm text-white bg-primary-800 hover:bg-primary-900 disabled:opacity-60 disabled:cursor-not-allowed shadow-sm transition-all active:scale-[0.98]">
                    <span x-text="loading ? 'Envoi du code...' : 'Recevoir mon code par email'"></span>
                </button>
            </form>
        </div>

        {{-- ÉTAPE CODE --}}
        <div x-show="step === 'code'" x-cloak>
            <p class="font-body text-sm text-gray-500 mb-5">Code envoyé à <span class="font-semibold text-primary-900" x-text="email"></span></p>

            <form @submit.prevent="verifyOtp()" class="space-y-6">
                <div class="flex justify-center gap-2 sm:gap-3">
                    @for ($i = 0; $i < 6; $i++)
                    <input
                        id="otp-{{ $i }}" type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code"
                        :value="code[{{ $i }}]"
                        @input="otpInput({{ $i }}, $event)"
                        @keydown="otpBackspace({{ $i }}, $event)"
                        @paste="otpPaste($event)"
                        class="w-11 h-13 sm:w-12 sm:h-14 text-center text-xl font-bold border border-gray-200 rounded-xl bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors">
                    @endfor
                </div>

                <button type="submit" :disabled="loading"
                        class="w-full flex justify-center items-center gap-2 py-3.5 px-4 rounded-xl font-body font-bold text-sm text-white bg-primary-800 hover:bg-primary-900 disabled:opacity-60 disabled:cursor-not-allowed shadow-sm transition-all active:scale-[0.98]">
                    <span x-text="loading ? 'Vérification...' : 'Vérifier et me connecter'"></span>
                </button>
            </form>

            <div class="mt-6 flex items-center justify-center gap-4 font-body text-sm">
                <button type="button" @click="step = 'email'" class="text-gray-500 hover:text-primary-700 transition-colors">Modifier l'email</button>
                <span class="text-gray-300">•</span>
                <button type="button" @click="sendOtp()" :disabled="loading" class="font-semibold text-primary-700 hover:text-accent-600 transition-colors disabled:opacity-60">Renvoyer le code</button>
            </div>
        </div>
    </div>

</body>
</html>