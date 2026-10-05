@extends('emails.layout')

@section('titre', $admin ? 'Code de vérification — Administration Tafely' : 'Votre code de vérification Tafely')
@section('apercu', 'Votre code de vérification : '.$code.' (valable '.$minutes.' minutes)')

@section('contenu')
    @if ($admin)
        <p style="margin:0 0 6px 0;font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#ef4444;">Administration</p>
    @endif

    <h1 style="margin:0 0 12px 0;font-size:24px;line-height:32px;font-weight:bold;color:#1e3a8a;">Votre code de vérification</h1>

    <p style="margin:0 0 24px 0;">
        Bonjour,<br>
        Saisissez le code ci-dessous pour {{ $admin ? "accéder au panneau d'administration Tafely" : 'accéder à votre boutique Tafely' }}.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center" style="background-color:#eff6ff;border:2px dashed #93c5fd;border-radius:14px;padding:22px 12px;">
                <span style="font-family:'Courier New',Courier,monospace;font-size:38px;line-height:44px;font-weight:bold;letter-spacing:12px;color:#1d4ed8;padding-left:12px;">{{ $code }}</span>
            </td>
        </tr>
    </table>

    <p style="margin:16px 0 0 0;text-align:center;font-size:13px;color:#64748b;">
        Ce code est valable <strong style="color:#1f2937;">{{ $minutes }} minutes</strong> et ne peut être utilisé qu'une seule fois.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:28px;">
        <tr>
            <td style="background-color:#fef2f2;border-left:4px solid #ef4444;border-radius:8px;padding:14px 16px;font-size:13px;line-height:20px;color:#7f1d1d;">
                <strong>Vous n'êtes pas à l'origine de cette demande ?</strong><br>
                @if ($admin)
                    Ignorez cet email et vérifiez la sécurité de votre boîte mail. Ne communiquez jamais ce code.
                @else
                    Vous pouvez ignorer cet email en toute sécurité. Ne communiquez jamais ce code à un tiers.
                @endif
            </td>
        </tr>
    </table>
@endsection