<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tafely')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@600;700;800&family=Be+Vietnam+Pro:wght@400;500;600&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        display: ['"Hanken Grotesk"', 'sans-serif'],
                        body: ['"Be Vietnam Pro"', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff', 100:'#dbeafe', 200:'#bfdbfe', 300:'#93c5fd',
                            400:'#60a5fa', 500:'#3b82f6', 600:'#2563eb', 700:'#1d4ed8',
                            800:'#1e40af', 900:'#1e3a8a', 950:'#172554',
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

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('authModal', () => ({
                authModalOpen: false,
                authMode: 'login',      // 'login' | 'signup' — n'affecte que les textes affichés
                authStep: 'email',      // 'email' | 'code'
                authEmail: '',
                authCode: ['', '', '', '', '', ''],
                authLoading: false,
                authError: '',
                authNewAccount: false,

                openAuth(mode) {
                    this.authMode = mode;
                    this.resetAuth();
                    this.authModalOpen = true;
                },

                resetAuth() {
                    this.authStep = 'email';
                    this.authCode = ['', '', '', '', '', ''];
                    this.authError = '';
                    this.authLoading = false;
                },

                csrf() {
                    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                },

                async sendOtp() {
                    if (! this.authEmail) {
                        this.authError = 'Merci de renseigner votre adresse email.';
                        return;
                    }
                    this.authError = '';
                    this.authLoading = true;
                    try {
                        const res = await fetch('{{ route('auth.otp.send') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrf(),
                            },
                            body: JSON.stringify({ email: this.authEmail }),
                        });
                        const data = await res.json();
                        if (! res.ok) {
                            this.authError = data.message || 'Une erreur est survenue.';
                            return;
                        }
                        this.authNewAccount = data.is_new_account;
                        this.authStep = 'code';
                        this.authCode = ['', '', '', '', '', ''];
                        this.$nextTick(() => document.getElementById('otp-0')?.focus());
                    } catch (e) {
                        this.authError = 'Connexion impossible. Réessayez.';
                    } finally {
                        this.authLoading = false;
                    }
                },

                async verifyOtp() {
                    const code = this.authCode.join('');
                    if (code.length !== 6) {
                        this.authError = 'Entrez les 6 chiffres du code.';
                        return;
                    }
                    this.authError = '';
                    this.authLoading = true;
                    try {
                        const res = await fetch('{{ route('auth.otp.verify') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrf(),
                            },
                            body: JSON.stringify({ email: this.authEmail, code }),
                        });
                        const data = await res.json();
                        if (! res.ok) {
                            this.authError = data.message || 'Code invalide ou expiré.';
                            return;
                        }
                        window.location.href = data.redirect;
                    } catch (e) {
                        this.authError = 'Connexion impossible. Réessayez.';
                    } finally {
                        this.authLoading = false;
                    }
                },

                otpInput(index, event) {
                    const val = event.target.value.replace(/[^0-9]/g, '').slice(-1);
                    this.authCode[index] = val;
                    if (val && index < 5) {
                        document.getElementById('otp-' + (index + 1))?.focus();
                    }
                },

                otpBackspace(index, event) {
                    if (event.key === 'Backspace' && ! this.authCode[index] && index > 0) {
                        document.getElementById('otp-' + (index - 1))?.focus();
                    }
                },

                otpPaste(event) {
                    const text = (event.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                    if (! text) return;
                    event.preventDefault();
                    this.authCode = text.split('').concat(['', '', '', '', '', '']).slice(0, 6);
                    this.$nextTick(() => document.getElementById('otp-' + Math.min(text.length, 5))?.focus());
                },
            }));
        });
    </script>

    <style>
        body { font-family: 'Be Vietnam Pro', sans-serif; }
        .font-display { font-family: 'Hanken Grotesk', sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .ambient-shadow { box-shadow: 0 4px 20px rgba(30, 58, 138, 0.10); }
        .glass-card { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.5); }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased" x-data="authModal()">

    @yield('content')

    {{-- ============ MODAL AUTH (inscription / connexion) ============ --}}
    <div
        x-show="authModalOpen"
        x-cloak
        class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6"
        style="display: none;"
    >
        {{-- fond --}}
        <div
            class="absolute inset-0 bg-primary-950/70 backdrop-blur-sm"
            x-show="authModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="authModalOpen = false"
        ></div>

        {{-- carte --}}
        <div
            class="relative w-full max-w-4xl max-h-[92vh] overflow-y-auto bg-white rounded-2xl sm:rounded-3xl shadow-2xl flex flex-col md:flex-row"
            x-show="authModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.outside="authModalOpen = false"
            @keydown.escape.window="authModalOpen = false"
        >
            {{-- bouton fermer --}}
            <button
                @click="authModalOpen = false"
                aria-label="Fermer"
                class="absolute top-4 right-4 z-20 h-9 w-9 flex items-center justify-center rounded-full bg-white/90 text-gray-500 hover:text-accent-600 hover:bg-white shadow-sm transition-colors"
            >
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>

            {{-- Panneau visuel (masqué en mobile) --}}
            <div class="hidden md:block md:w-1/2 relative overflow-hidden bg-primary-900 shrink-0">
                <img src="{{ asset('auth.jpg') }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-primary-950/90 via-primary-950/40 to-transparent"></div>
                <div class="absolute inset-0 flex flex-col justify-end p-8 lg:p-10">
                    <template x-if="authMode === 'signup'">
                        <div>
                            <h2 class="font-display text-2xl font-bold text-white mb-2">Gérez votre boutique avec élégance.</h2>
                            <p class="font-body text-sm text-primary-100/90">Rejoignez les commerçants qui simplifient leur quotidien avec Tafely.</p>
                        </div>
                    </template>
                    <template x-if="authMode === 'login'">
                        <div>
                            <h2 class="font-display text-2xl font-bold text-white mb-2">Ravi de vous revoir.</h2>
                            <p class="font-body text-sm text-primary-100/90">Connectez-vous à votre espace marchand pour suivre vos ventes et gérer votre boutique.</p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Panneau formulaire --}}
            <div class="w-full md:w-1/2 p-6 sm:p-8 lg:p-10 flex flex-col justify-center min-h-[420px]">

                <div class="inline-flex items-center gap-2 mb-6 justify-center md:justify-start">
                    <span class="material-symbols-outlined text-accent-500" style="font-variation-settings: 'FILL' 1;">storefront</span>
                    <span class="font-display font-bold text-primary-900">Tafely</span>
                </div>

                {{-- erreur --}}
                <p x-show="authError" x-cloak x-text="authError"
                   class="mb-4 text-sm font-body font-semibold text-accent-700 bg-accent-50 border border-accent-100 rounded-lg px-4 py-2.5"></p>

                {{-- ---- ÉTAPE 1 : EMAIL ---- --}}
                <div x-show="authStep === 'email'" x-cloak>
                    <div class="mb-7 text-center md:text-left">
                        <template x-if="authMode === 'signup'">
                            <div>
                                <h2 class="font-display text-2xl font-bold text-gray-900 mb-1">Bienvenue</h2>
                                <p class="font-body text-sm text-gray-500">Entrez votre email pour créer votre boutique. Aucun mot de passe à retenir.</p>
                            </div>
                        </template>
                        <template x-if="authMode === 'login'">
                            <div>
                                <h2 class="font-display text-2xl font-bold text-gray-900 mb-1">Connexion</h2>
                                <p class="font-body text-sm text-gray-500">Entrez votre email, nous vous envoyons un code de connexion.</p>
                            </div>
                        </template>
                    </div>

                    <form @submit.prevent="sendOtp()" class="space-y-5">
                        <div>
                            <label for="auth-email" class="block font-body text-sm font-semibold text-primary-900 mb-1.5">Adresse email</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[20px]">mail</span>
                                <input id="auth-email" name="email" type="email" required autocomplete="email" placeholder="vous@exemple.com"
                                       x-model="authEmail"
                                       class="w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl bg-white text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors font-body text-sm">
                            </div>
                        </div>
                        <button type="submit" :disabled="authLoading"
                                class="w-full flex justify-center items-center gap-2 py-3.5 px-4 rounded-xl font-body font-bold text-sm text-white bg-accent-500 hover:bg-accent-600 disabled:opacity-60 disabled:cursor-not-allowed shadow-sm transition-all active:scale-[0.98]">
                            <span x-text="authLoading ? 'Envoi du code...' : 'Recevoir mon code par email'"></span>
                        </button>
                    </form>

                    <p class="mt-6 text-center font-body text-sm text-gray-500">
                        <template x-if="authMode === 'signup'">
                            <span>Vous avez déjà un compte ?
                                <button type="button" @click="authMode = 'login'" class="font-semibold text-primary-700 hover:text-accent-600 transition-colors underline underline-offset-2">Connectez-vous</button>
                            </span>
                        </template>
                        <template x-if="authMode === 'login'">
                            <span>Nouveau marchand ?
                                <button type="button" @click="authMode = 'signup'" class="font-semibold text-primary-700 hover:text-accent-600 transition-colors underline underline-offset-2">Créer un compte</button>
                            </span>
                        </template>
                    </p>
                </div>

                {{-- ---- ÉTAPE 2 : CODE OTP ---- --}}
                <div x-show="authStep === 'code'" x-cloak>
                    <div class="mb-7 text-center md:text-left">
                        <h2 class="font-display text-2xl font-bold text-gray-900 mb-1">Vérification</h2>
                        <p class="font-body text-sm text-gray-500">
                            Code envoyé à <span class="font-semibold text-primary-900" x-text="authEmail"></span>
                        </p>
                    </div>

                    <form @submit.prevent="verifyOtp()" class="space-y-6">
                        <div class="flex justify-center md:justify-start gap-2 sm:gap-3">
                            @for ($i = 0; $i < 6; $i++)
                            <input
                                id="otp-{{ $i }}"
                                type="text"
                                inputmode="numeric"
                                maxlength="1"
                                autocomplete="one-time-code"
                                :value="authCode[{{ $i }}]"
                                @input="otpInput({{ $i }}, $event)"
                                @keydown="otpBackspace({{ $i }}, $event)"
                                @paste="otpPaste($event)"
                                class="w-11 h-13 sm:w-12 sm:h-14 text-center text-xl font-bold border border-gray-200 rounded-xl bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors"
                            >
                            @endfor
                        </div>

                        <button type="submit" :disabled="authLoading"
                                class="w-full flex justify-center items-center gap-2 py-3.5 px-4 rounded-xl font-body font-bold text-sm text-white bg-accent-500 hover:bg-accent-600 disabled:opacity-60 disabled:cursor-not-allowed shadow-sm transition-all active:scale-[0.98]">
                            <span x-text="authLoading ? 'Vérification...' : 'Vérifier et continuer'"></span>
                            <span class="material-symbols-outlined text-[18px]" x-show="!authLoading">arrow_forward</span>
                        </button>
                    </form>

                    <div class="mt-6 flex items-center justify-center md:justify-start gap-4 font-body text-sm">
                        <button type="button" @click="authStep = 'email'" class="text-gray-500 hover:text-primary-700 transition-colors">
                            Modifier l'email
                        </button>
                        <span class="text-gray-300">•</span>
                        <button type="button" @click="sendOtp()" :disabled="authLoading" class="font-semibold text-primary-700 hover:text-accent-600 transition-colors disabled:opacity-60">
                            Renvoyer le code
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>