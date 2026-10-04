@extends('emails.layout')

@section('titre', 'Paiement confirmé — Tafely')
@section('apercu', 'Votre paiement '.$paiement->reference.' a bien été reçu.')

@section('contenu')
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:0 auto 18px auto;">
        <tr>
            <td align="center" style="width:56px;height:56px;background-color:#dcfce7;border-radius:50%;font-size:28px;line-height:56px;color:#16a34a;font-weight:bold;">&#10003;</td>
        </tr>
    </table>

    <h1 style="margin:0 0 8px 0;font-size:24px;line-height:32px;font-weight:bold;color:#1e3a8a;text-align:center;">Merci pour votre paiement !</h1>

    <p style="margin:0 0 24px 0;text-align:center;">
        @if ($paiement->type === 'abonnement')
            Votre abonnement Tafely est maintenant actif
            ({{ $paiement->duree_mois ?: 1 }} mois){{ $finAbonnement ? ", valable jusqu'au" : '' }}
            @if ($finAbonnement) <strong>{{ $finAbonnement }}</strong> @endif.
        @else
            {{ $quantite }} emplacement{{ $quantite > 1 ? 's' : '' }} produit supplémentaire{{ $quantite > 1 ? 's ont' : ' a' }}
            été ajouté{{ $quantite > 1 ? 's' : '' }} à votre boutique.
        @endif
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;border-collapse:separate;">
        <tr>
            <td style="padding:14px 20px;font-size:13px;color:#64748b;border-bottom:1px solid #e5e7eb;">Référence</td>
            <td align="right" style="padding:14px 20px;font-size:14px;font-weight:bold;color:#1f2937;border-bottom:1px solid #e5e7eb;">{{ $paiement->reference }}</td>
        </tr>
        <tr>
            <td style="padding:14px 20px;font-size:13px;color:#64748b;border-bottom:1px solid #e5e7eb;">Désignation</td>
            <td align="right" style="padding:14px 20px;font-size:14px;color:#1f2937;border-bottom:1px solid #e5e7eb;">{{ $paiement->typeLabel() }}</td>
        </tr>
        <tr>
            <td style="padding:14px 20px;font-size:13px;color:#64748b;">Montant payé</td>
            <td align="right" style="padding:14px 20px;font-size:18px;font-weight:bold;color:#1d4ed8;">{{ $paiement->montantFormate() }}</td>
        </tr>
    </table>

    @include('emails._bouton', ['url' => route('dashboard'), 'label' => 'Aller à mon tableau de bord'])
@endsection