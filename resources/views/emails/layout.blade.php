@php
    // Logo intégré dans l'email (pièce jointe CID) : s'affiche même si la boîte mail
    // bloque les images distantes ou si l'URL du site n'est pas joignable.
    $logoFichier = public_path('logo.png');
    $logoSrc = (isset($message) && is_file($logoFichier))
        ? $message->embed($logoFichier)
        : asset('logo.png');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>@yield('titre', 'Tafely')</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;-webkit-text-size-adjust:100%;">

    {{-- Texte d'aperçu affiché dans la liste de la boîte mail --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;font-size:1px;line-height:1px;">
        @yield('apercu')
        &#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f1f5f9;">
        <tr>
            <td align="center" style="padding:32px 12px;">

                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;">

                    {{-- ===== EN-TÊTE ===== --}}
                    <tr>
                        <td align="center" style="background-color:#ffffff;border-radius:16px 16px 0 0;padding:28px 24px 22px 24px;border-bottom:4px solid #1d4ed8;">
                            <a href="{{ url('/') }}" style="text-decoration:none;">
                                <img src="{{ $logoSrc }}" alt="Tafely" height="44" style="height:44px;width:auto;border:0;display:block;margin:0 auto;font-family:Arial,Helvetica,sans-serif;font-size:22px;font-weight:bold;color:#1d4ed8;">
                            </a>
                        </td>
                    </tr>

                    {{-- ===== CONTENU ===== --}}
                    <tr>
                        <td style="background-color:#ffffff;padding:36px 32px 32px 32px;font-family:Arial,Helvetica,sans-serif;color:#1f2937;font-size:15px;line-height:24px;">
                            @yield('contenu')
                        </td>
                    </tr>

                    {{-- ===== PIED DE PAGE ===== --}}
                    <tr>
                        <td align="center" style="background-color:#172554;border-radius:0 0 16px 16px;padding:24px 24px 26px 24px;font-family:Arial,Helvetica,sans-serif;">
                            <p style="margin:0 0 6px 0;font-size:13px;line-height:20px;color:#dbeafe;font-weight:bold;">Tafely — Votre boutique en ligne en 5 minutes</p>
                            <p style="margin:0 0 10px 0;font-size:12px;line-height:18px;color:#93c5fd;">
                                Une question ? <a href="mailto:contact@tafely-gr.com" style="color:#ffffff;text-decoration:underline;">contact@tafely-gr.com</a>
                            </p>
                            <p style="margin:0;font-size:11px;line-height:16px;color:#60a5fa;">© {{ date('Y') }} Tafely · Madagascar</p>
                        </td>
                    </tr>
                </table>

                <p style="margin:16px 0 0 0;font-family:Arial,Helvetica,sans-serif;font-size:11px;line-height:16px;color:#94a3b8;text-align:center;">
                    Vous recevez cet email car il est lié à votre compte Tafely.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>