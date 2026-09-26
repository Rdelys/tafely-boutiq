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