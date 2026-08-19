<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; background:#f4f6f8; padding:32px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:480px; margin:auto; background:#fff; border-radius:12px; overflow:hidden;">
        <tr>
            <td style="background:#1d4ed8; padding:24px; text-align:center;">
                <img src="{{ asset('logo.png') }}" alt="Logo" height="40">
            </td>
        </tr>
        <tr>
            <td style="padding:32px; text-align:center;">
                <h2 style="color:#111827;">Votre code de vérification</h2>
                <p style="color:#6b7280;">Utilisez ce code pour vous connecter. Il expire dans 10 minutes.</p>
                <div style="font-size:32px; letter-spacing:8px; font-weight:bold; color:#1d4ed8; margin:24px 0;">
                    {{ $code }}
                </div>
                <p style="color:#ef4444; font-size:13px;">Ne partagez ce code avec personne.</p>
            </td>
        </tr>
    </table>
</body>
</html>
