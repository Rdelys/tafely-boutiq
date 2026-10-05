@extends('emails.layout')

@section('titre', 'Nouvelle commande '.$commande->numero)
@section('apercu', $commande->nom_client.' a commandé pour '.$commande->totalFormate())

@section('contenu')
    <p style="margin:0 0 6px 0;font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#ef4444;">Nouvelle commande</p>
    <h1 style="margin:0 0 8px 0;font-size:24px;line-height:32px;font-weight:bold;color:#1e3a8a;">Vous avez reçu une commande !</h1>
    <p style="margin:0 0 24px 0;color:#64748b;">
        N° <strong style="color:#1f2937;">{{ $commande->numero }}</strong> · {{ $commande->created_at->format('d/m/Y à H:i') }}
    </p>

    {{-- ===== ARTICLES ===== --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;border-radius:12px;border-collapse:separate;overflow:hidden;">
        <tr>
            <td style="background-color:#f8fafc;padding:10px 16px;font-size:11px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#64748b;">Produit</td>
            <td align="center" style="background-color:#f8fafc;padding:10px 8px;font-size:11px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#64748b;">Qté</td>
            <td align="right" style="background-color:#f8fafc;padding:10px 16px;font-size:11px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#64748b;">Sous-total</td>
        </tr>
        @foreach ($commande->lignes as $ligne)
            <tr>
                <td style="padding:12px 16px;border-top:1px solid #f1f5f9;font-size:14px;color:#1f2937;">{{ $ligne->nom_produit }}</td>
                <td align="center" style="padding:12px 8px;border-top:1px solid #f1f5f9;font-size:14px;color:#64748b;">{{ $ligne->quantite }}</td>
                <td align="right" style="padding:12px 16px;border-top:1px solid #f1f5f9;font-size:14px;font-weight:bold;color:#1f2937;white-space:nowrap;">{{ $ligne->sousTotalFormate() }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="2" style="padding:10px 16px 4px 16px;border-top:1px solid #e5e7eb;font-size:13px;color:#64748b;">Sous-total</td>
            <td align="right" style="padding:10px 16px 4px 16px;border-top:1px solid #e5e7eb;font-size:13px;color:#64748b;white-space:nowrap;">{{ $commande->sousTotalFormate() }}</td>
        </tr>
        <tr>
            <td colspan="2" style="padding:4px 16px;font-size:13px;color:#64748b;">Livraison</td>
            <td align="right" style="padding:4px 16px;font-size:13px;color:#64748b;white-space:nowrap;">{{ $commande->prix_livraison ? number_format($commande->prix_livraison, 0, ',', ' ').' Ar' : 'Gratuite' }}</td>
        </tr>
        <tr>
            <td colspan="2" style="padding:12px 16px 14px 16px;font-size:16px;font-weight:bold;color:#1e3a8a;">Total</td>
            <td align="right" style="padding:12px 16px 14px 16px;font-size:18px;font-weight:bold;color:#1d4ed8;white-space:nowrap;">{{ $commande->totalFormate() }}</td>
        </tr>
    </table>

    {{-- ===== CLIENT ===== --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:20px;background-color:#eff6ff;border-radius:12px;">
        <tr>
            <td style="padding:18px 20px;font-size:14px;line-height:22px;color:#1f2937;">
                <p style="margin:0 0 8px 0;font-size:11px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#1d4ed8;">Client</p>
                <strong>{{ $commande->nom_client }}</strong><br>
                <a href="tel:{{ $commande->telephone_client }}" style="color:#1d4ed8;text-decoration:none;">{{ $commande->telephone_client }}</a>

                <p style="margin:16px 0 4px 0;font-size:11px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#1d4ed8;">
                    {{ $commande->estALivrer() ? 'À livrer' : 'À récupérer' }}
                </p>
                @if ($commande->estALivrer())
                    {{ $commande->adresse_livraison }}
                @else
                    Le {{ $commande->date_recuperation?->format('d/m/Y') ?: '—' }} à {{ $commande->heure_recuperation ?: '—' }}
                @endif
            </td>
        </tr>
    </table>

    <p style="margin:20px 0 0 0;text-align:center;">
        <span style="display:inline-block;background-color:#fef2f2;color:#b91c1c;font-size:12px;font-weight:bold;padding:6px 14px;border-radius:999px;">
            Statut : {{ $commande->statutLabel() }}
        </span>
    </p>

    @include('emails._bouton', ['url' => route('commandes'), 'label' => 'Voir mes commandes'])

    <p style="margin:20px 0 0 0;text-align:center;font-size:13px;color:#64748b;">Merci de traiter cette commande depuis votre tableau de bord Tafely.</p>
@endsection