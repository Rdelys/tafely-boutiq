@extends('layouts.admin')

@section('title', ($marchand->nom_boutique ?: 'Marchand').' — Admin Tafely')

@section('page-content')
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.marchands.index') }}" class="text-gray-400 hover:text-primary-700 transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">{{ $marchand->nom_boutique ?: 'Sans nom' }}</h1>
            <p class="font-body text-gray-500 mt-1">Inscrit le {{ $marchand->created_at->format('d/m/Y à H:i') }}</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">

        {{-- colonne infos --}}
        <div class="lg:col-span-1 flex flex-col gap-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 md:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-display text-base font-bold text-primary-900">Statut</h2>
                    <span @class([
                        'inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap',
                        'bg-green-50 text-green-700' => $marchand->abonnementActif(),
                        'bg-accent-50 text-accent-700' => ! $marchand->abonnementActif() && $marchand->essaiExpire(),
                        'bg-gray-100 text-gray-600' => ! $marchand->abonnementActif() && ! $marchand->essaiExpire(),
                    ])>{{ $marchand->statusLabel() }}</span>
                </div>
                @if ($marchand->abonnementActif())
                    <p class="font-body text-sm text-gray-500">Valable jusqu'au {{ $marchand->abonnement_expire_le?->format('d/m/Y') ?? '—' }}</p>
                @else
                    <p class="font-body text-sm text-gray-500">{{ $marchand->joursRestantsEssai() }} jour(s) d'essai restant(s)</p>
                @endif
                <p class="font-body text-xs text-gray-400 mt-2">Limite produits : {{ $marchand->limiteProduits() }} ({{ $marchand->produits_count }} utilisés)</p>
            </div>

            {{-- succès --}}
            @if (session('status'))
                <div class="bg-primary-50 border border-primary-100 text-primary-700 rounded-xl px-4 py-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    <span class="font-body text-xs font-semibold">{{ session('status') }}</span>
                </div>
            @endif

            {{-- suspension / réactivation --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 md:p-6">
                <h2 class="font-display text-base font-bold text-primary-900 mb-4">Accès au compte</h2>

                @if ($marchand->estSuspendu())
                    <div class="mb-4 bg-accent-50 border border-accent-100 text-accent-700 rounded-xl px-4 py-3">
                        <p class="font-body text-xs font-semibold">Compte suspendu depuis le {{ $marchand->suspendu_le?->format('d/m/Y') }}</p>
                        @if ($marchand->suspendu_raison)
                            <p class="font-body text-xs mt-1">Raison : {{ $marchand->suspendu_raison }}</p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.marchands.reactiver', $marchand) }}">
                        @csrf
                        <button type="submit" class="w-full bg-primary-800 hover:bg-primary-900 text-white font-body font-bold text-sm py-2.5 rounded-xl transition-colors">
                            Réactiver le compte
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.marchands.suspendre', $marchand) }}"
                          onsubmit="return confirm('Suspendre ce compte ? Le marchand ne pourra plus se connecter.');">
                        @csrf
                        <input type="text" name="raison" maxlength="255" placeholder="Raison (facultatif)"
                               class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 font-body text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-primary-600">
                        <button type="submit" class="w-full bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm py-2.5 rounded-xl transition-colors">
                            Suspendre le compte
                        </button>
                    </form>
                @endif
            </div>

            {{-- prolongations --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 md:p-6">
                <h2 class="font-display text-base font-bold text-primary-900 mb-4">Geste commercial</h2>

                <form method="POST" action="{{ route('admin.marchands.prolonger-essai', $marchand) }}" class="flex items-center gap-2 mb-3">
                    @csrf
                    <input type="number" name="jours" min="1" max="365" value="7" required
                           class="w-20 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600">
                    <button type="submit" class="flex-1 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-body font-bold text-xs py-2.5 rounded-xl transition-colors">
                        Prolonger l'essai (jours)
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.marchands.prolonger-abonnement', $marchand) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="number" name="jours" min="1" max="365" value="30" required
                           class="w-20 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600">
                    <button type="submit" class="flex-1 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-body font-bold text-xs py-2.5 rounded-xl transition-colors">
                        Prolonger l'abonnement (jours)
                    </button>
                </form>
            </div>

            {{-- limite produits personnalisée --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 md:p-6">
                <h2 class="font-display text-base font-bold text-primary-900 mb-1">Limite de produits</h2>
                <p class="font-body text-xs text-gray-400 mb-4">Laisser vide pour revenir au calcul automatique par plan ({{ $marchand->abonnementActif() ? 30 : 10 }} + bonus).</p>
                <form method="POST" action="{{ route('admin.marchands.limite-produits', $marchand) }}" class="flex items-center gap-2">
                    @csrf
                    @method('PUT')
                    <input type="number" name="limite" min="0" max="1000" placeholder="ex : 50"
                           value="{{ $marchand->limite_produits_personnalisee }}"
                           class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600">
                    <button type="submit" class="bg-primary-800 hover:bg-primary-900 text-white font-body font-bold text-xs px-4 py-2.5 rounded-xl transition-colors whitespace-nowrap">
                        Appliquer
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 md:p-6">
                <h2 class="font-display text-base font-bold text-primary-900 mb-4">Coordonnées</h2>
                <dl class="space-y-3 font-body text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-400">Email</dt>
                        <dd class="text-gray-800 text-right break-all">{{ $marchand->email }}</dd>
                    </div>
                    @if ($marchand->telephone)
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-400">Téléphone</dt>
                            <dd class="text-gray-800 text-right">{{ $marchand->telephone }}</dd>
                        </div>
                    @endif
                    @if ($marchand->adresse)
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-400">Adresse</dt>
                            <dd class="text-gray-800 text-right">{{ $marchand->adresse }}</dd>
                        </div>
                    @endif
                    @if ($marchand->nif)
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-400">NIF</dt>
                            <dd class="text-gray-800 text-right">{{ $marchand->nif }}</dd>
                        </div>
                    @endif
                    @if ($marchand->stat)
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-400">STAT</dt>
                            <dd class="text-gray-800 text-right">{{ $marchand->stat }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-400">Lien boutique</dt>
                        <dd class="text-right"><a href="{{ $marchand->lienBoutique() }}" target="_blank" class="text-primary-700 font-semibold hover:text-accent-600 transition-colors">Voir la vitrine</a></dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- colonne activité --}}
        <div class="lg:col-span-2 flex flex-col gap-6">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <p class="font-body text-sm text-gray-500 mb-1">Produits</p>
                    <p class="font-display text-3xl font-bold text-primary-900">{{ $marchand->produits_count }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <p class="font-body text-sm text-gray-500 mb-1">Commandes</p>
                    <p class="font-display text-3xl font-bold text-primary-900">{{ $marchand->commandes_count }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 md:p-6">
                <h2 class="font-display text-base font-bold text-primary-900 mb-4">Dernières commandes</h2>
                @forelse ($dernieresCommandes as $commande)
                    <div class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0">
                        <div>
                            <p class="font-body font-semibold text-sm text-gray-900">{{ $commande->numero }}</p>
                            <p class="font-body text-xs text-gray-400">{{ $commande->nomClientAffiche() }} · {{ $commande->created_at->format('d/m/Y') }}</p>
                        </div>
                        <p class="font-display font-bold text-sm text-primary-800">{{ $commande->totalFormate() }}</p>
                    </div>
                @empty
                    <p class="font-body text-sm text-gray-400">Aucune commande pour l'instant.</p>
                @endforelse
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 md:p-6">
                <h2 class="font-display text-base font-bold text-primary-900 mb-4">Derniers paiements</h2>
                @forelse ($derniersPaiements as $paiement)
                    <div class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0">
                        <div>
                            <p class="font-body font-semibold text-sm text-gray-900">{{ $paiement->reference }}</p>
                            <p class="font-body text-xs text-gray-400">{{ $paiement->type === 'abonnement' ? 'Abonnement' : 'Pack produits' }} · {{ $paiement->created_at->format('d/m/Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-display font-bold text-sm text-primary-800">{{ $paiement->montantFormate() }}</p>
                            <span @class([
                                'inline-block text-[10px] font-bold px-2 py-0.5 rounded-full mt-0.5',
                                'bg-green-50 text-green-700' => $paiement->statut === 'paye',
                                'bg-accent-50 text-accent-700' => $paiement->statut === 'echoue',
                                'bg-gray-100 text-gray-500' => $paiement->statut === 'en_attente',
                            ])>{{ ucfirst(str_replace('_', ' ', $paiement->statut)) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="font-body text-sm text-gray-400">Aucun paiement pour l'instant.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection