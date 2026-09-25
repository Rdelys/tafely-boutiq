<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu {{ $commande->numero }}</title>
    <style>
        @page { size: A5; margin: 12mm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #191c1d;
            font-size: 11px;
            margin: 0;
        }
        .header {
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #1d4ed8;
            margin-bottom: 14px;
        }
        .logo {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 6px auto;
            display: block;
        }
        .logo-fallback {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #1d4ed8;
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            line-height: 44px;
            margin: 0 auto 6px auto;
        }
        .boutique-nom { font-size: 16px; font-weight: bold; margin: 0; }
        .boutique-sub { font-size: 10px; color: #666; margin: 2px 0 0 0; }
        .titre-recu {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 10px 0 2px 0;
        }
        .numero { text-align: center; font-size: 10px; color: #666; margin-bottom: 14px; }
        .bloc { margin-bottom: 12px; }
        .bloc-titre { font-size: 9px; font-weight: bold; text-transform: uppercase; color: #999; margin-bottom: 3px; letter-spacing: 0.5px; }
        .bloc-valeur { font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        thead td { font-size: 9px; font-weight: bold; text-transform: uppercase; color: #999; padding-bottom: 4px; border-bottom: 1px solid #ddd; }
        tbody td { padding: 5px 0; font-size: 10.5px; border-bottom: 1px dashed #eee; }
        .col-qte { text-align: center; width: 30px; }
        .col-prix { text-align: right; width: 70px; }
        .totaux { margin-top: 6px; }
        .totaux .ligne { display: block; overflow: hidden; padding: 2px 0; font-size: 10.5px; }
        .totaux .ligne span:first-child { float: left; color: #666; }
        .totaux .ligne span:last-child { float: right; font-weight: bold; }
        .totaux .total-final { border-top: 1px solid #191c1d; margin-top: 4px; padding-top: 6px; font-size: 13px; }
        .footer {
            text-align: center;
            font-size: 8.5px;
            color: #999;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>

    <div class="header">
        @if ($marchand->logo)
            <img class="logo" src="{{ storage_path('app/public/'.$marchand->logo) }}">
        @else
            <div class="logo-fallback">{{ mb_strtoupper(mb_substr($marchand->nom_boutique ?: 'B', 0, 1)) }}</div>
        @endif
                <p class="boutique-nom">{{ $marchand->nom_boutique ?: 'Boutique' }}</p>
        @if ($marchand->adresse)
            <p class="boutique-sub">{{ $marchand->adresse }}</p>
        @endif
        @if ($marchand->telephone)
            <p class="boutique-sub">{{ $marchand->telephone }}</p>
        @endif
        @if ($marchand->nif)
            <p class="boutique-sub">NIF : {{ $marchand->nif }}</p>
        @endif
        @if ($marchand->stat)
            <p class="boutique-sub">STAT : {{ $marchand->stat }}</p>
        @endif
    </div>

    <p class="titre-recu">REÇU DE COMMANDE</p>
    <p class="numero">{{ $commande->numero }} &nbsp;·&nbsp; {{ $commande->created_at->format('d/m/Y à H:i') }}</p>

    <div class="bloc">
        <div class="bloc-titre">Client</div>
        <div class="bloc-valeur">{{ $commande->nom_client }} — {{ $commande->telephone_client }}</div>
    </div>

    <div class="bloc">
        <div class="bloc-titre">{{ $commande->estALivrer() ? 'Livraison' : 'Récupération' }}</div>
        <div class="bloc-valeur">
            @if ($commande->estALivrer())
                {{ $commande->adresse_livraison }}
            @else
                {{ $commande->date_recuperation?->format('d/m/Y') ?: '—' }} à {{ $commande->heure_recuperation ?: '—' }}
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <td>Produit</td>
                <td class="col-qte">Qté</td>
                <td class="col-prix">Sous-total</td>
            </tr>
        </thead>
        <tbody>
            @foreach ($commande->lignes as $ligne)
                <tr>
                    <td>{{ $ligne->nom_produit }}</td>
                    <td class="col-qte">{{ $ligne->quantite }}</td>
                    <td class="col-prix">{{ $ligne->sousTotalFormate() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totaux">
        <span class="ligne"><span>Sous-total</span><span>{{ $commande->sousTotalFormate() }}</span></span>
        <span class="ligne"><span>Livraison</span><span>{{ $commande->prix_livraison ? number_format($commande->prix_livraison, 0, ',', ' ').' Ar' : 'Gratuite' }}</span></span>
        <span class="ligne total-final"><span>TOTAL</span><span>{{ $commande->totalFormate() }}</span></span>
    </div>

    <div class="footer">
        Merci pour votre commande !<br>
        Conservez ce reçu — reçu généré via Tafely.
    </div>

</body>
</html>