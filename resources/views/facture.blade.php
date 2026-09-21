<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $commande->numero }} — Tafely</title>
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
                        primary: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a' },
                        accent: { 500:'#ef4444',600:'#dc2626' },
                    },
                } }
            };
        }
    </script>
    <style>
        body { font-family: 'Be Vietnam Pro', sans-serif; }
        .font-display { font-family: 'Hanken Grotesk', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .facture-card { box-shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-10 px-4">

    <div class="max-w-2xl mx-auto no-print mb-4 flex justify-between items-center">
        <a href="{{ route('commandes') }}" class="inline-flex items-center gap-2 text-sm font-body font-semibold text-gray-500 hover:text-primary-700 transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Retour aux commandes
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 bg-primary-800 hover:bg-primary-900 text-white font-body font-bold text-sm px-5 py-2.5 rounded-xl shadow-sm transition-colors">
            <span class="material-symbols-outlined text-[18px]">print</span>
            Imprimer / PDF
        </button>
    </div>

    <div class="facture-card max-w-2xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-8 md:p-10">

        {{-- en-tête --}}
        <div class="flex justify-between items-start pb-6 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="h-14 w-14 rounded-xl bg-primary-50 overflow-hidden flex items-center justify-center shrink-0">
                    @if ($marchand->logo)
                        <img src="{{ asset('storage/'.$marchand->logo) }}" alt="Logo" class="h-full w-full object-cover">
                    @else
                        <span class="material-symbols-outlined text-primary-700 text-2xl">storefront</span>
                    @endif
                </div>
                <div>
                    <p class="font-display font-bold text-lg text-primary-900">{{ $marchand->nom_boutique ?: 'Ma boutique' }}</p>
                    @if ($marchand->adresse)
                        <p class="font-body text-xs text-gray-500">{{ $marchand->adresse }}</p>
                    @endif
                    <p class="font-body text-xs text-gray-500">{{ $marchand->email_notification ?: $marchand->email }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="font-display font-bold text-2xl text-primary-900">FACTURE</p>
                <p class="font-body text-sm text-gray-500 mt-1">{{ $commande->numero }}</p>
                <p class="font-body text-xs text-gray-400">{{ $commande->created_at->format('d/m/Y à H:i') }}</p>
            </div>
        </div>

        {{-- client + statut --}}
        <div class="grid sm:grid-cols-2 gap-6 py-6 border-b border-gray-100">
            <div>
                <p class="font-body text-xs font-bold text-gray-400 uppercase tracking-wide mb-1.5">Client</p>
                <p class="font-body font-semibold text-sm text-gray-900">{{ $commande->nom_client }}</p>
                <p class="font-body text-sm text-gray-500">{{ $commande->telephone_client }}</p>
            </div>
            <div>
                <p class="font-body text-xs font-bold text-gray-400 uppercase tracking-wide mb-1.5">
                    {{ $commande->estALivrer() ? 'Livraison' : 'Récupération' }}
                </p>
                @if ($commande->estALivrer())
                    <p class="font-body text-sm text-gray-700">{{ $commande->adresse_livraison }}</p>
                @else
                    <p class="font-body text-sm text-gray-700">
                        {{ $commande->date_recuperation?->format('d/m/Y') ?: '—' }} à {{ $commande->heure_recuperation ?: '—' }}
                    </p>
                @endif
                <span class="inline-block mt-2 px-2.5 py-1 rounded-full text-xs font-semibold
                    {{ match($commande->statut) {
                        'livree' => 'bg-green-50 text-green-700',
                        'en_cours_de_livraison' => 'bg-primary-50 text-primary-700',
                        default => 'bg-accent-50 text-accent-700',
                    } }}">
                    {{ $commande->statutLabel() }}
                </span>
            </div>
        </div>

        {{-- lignes --}}
        <div class="py-6">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="pb-2 font-body text-xs font-bold text-gray-400 uppercase tracking-wide">Produit</th>
                        <th class="pb-2 font-body text-xs font-bold text-gray-400 uppercase tracking-wide text-center">Qté</th>
                        <th class="pb-2 font-body text-xs font-bold text-gray-400 uppercase tracking-wide text-right">Prix unit.</th>
                        <th class="pb-2 font-body text-xs font-bold text-gray-400 uppercase tracking-wide text-right">Sous-total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($commande->lignes as $ligne)
                        <tr>
                            <td class="py-2.5 font-body text-sm text-gray-900">{{ $ligne->nom_produit }}</td>
                            <td class="py-2.5 font-body text-sm text-gray-500 text-center">{{ $ligne->quantite }}</td>
                            <td class="py-2.5 font-body text-sm text-gray-500 text-right">{{ $ligne->prixUnitaireFormate() }}</td>
                            <td class="py-2.5 font-body text-sm font-semibold text-gray-900 text-right">{{ $ligne->sousTotalFormate() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- totaux --}}
        <div class="flex justify-end">
            <div class="w-full max-w-[220px] space-y-2">
                <div class="flex justify-between font-body text-sm text-gray-500">
                    <span>Sous-total</span>
                    <span>{{ $commande->sousTotalFormate() }}</span>
                </div>
                <div class="flex justify-between font-body text-sm text-gray-500">
                    <span>Livraison</span>
                    <span>{{ $commande->prix_livraison ? number_format($commande->prix_livraison, 0, ',', ' ').' Ar' : 'Gratuite' }}</span>
                </div>
                <div class="flex justify-between font-display font-bold text-lg text-primary-900 pt-2 border-t border-gray-100">
                    <span>Total</span>
                    <span>{{ $commande->totalFormate() }}</span>
                </div>
            </div>
        </div>

        <p class="text-center font-body text-xs text-gray-400 mt-10 pt-6 border-t border-gray-100">
            Facture générée par {{ $marchand->nom_boutique ?: 'la boutique' }} via Tafely.
        </p>
    </div>

</body>
</html>