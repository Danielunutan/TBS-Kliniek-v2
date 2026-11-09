<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jouw TBS Kliniek Wachtwoord Herstelcode</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <!-- Hidden Preheader -->
    <div style="display: none; font-size: 1px; color: #f1f5f9; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
        Jouw 6-cijferige wachtwoord herstelcode is {{ $code }} (30 minuten geldig). Stel een nieuw wachtwoord in voor TBS Kliniek.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f1f5f9; padding: 36px 12px;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 560px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);">
                    
                    <!-- Top Accent Bar -->
                    <tr>
                        <td style="height: 6px; background: linear-gradient(90deg, #7c3aed, #9333ea, #f59e0b);"></td>
                    </tr>

                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 36px 32px 18px 32px; background-color: #faf5ff; border-bottom: 1px solid #f3e8ff;">
                            @if(isset($message) && file_exists(public_path('assets/logo.png')))
                                <img src="{{ $message->embed(public_path('assets/logo.png')) }}" alt="TBS Kliniek" width="76" height="76" style="display: block; margin: 0 auto 14px auto; border: 0; outline: none;">
                            @else
                                <img src="https://tbs-kliniek.nl/assets/logo.png?v=3" alt="TBS Kliniek" width="76" height="76" style="display: block; margin: 0 auto 14px auto; border: 0; outline: none;">
                            @endif
                            <div style="font-size: 24px; font-weight: 900; letter-spacing: -0.5px; color: #1e1b4b; text-transform: uppercase; margin: 0;">
                                TBS KLINIEK
                            </div>
                            <div style="display: inline-block; margin-top: 8px; font-size: 11px; font-weight: 800; letter-spacing: 2px; color: #7c3aed; text-transform: uppercase; background-color: #ede9fe; padding: 5px 14px; border-radius: 20px; border: 1px solid #ddd6fe;">
                                🔐 BEVEILIGD WACHTWOORD HERSTEL
                            </div>
                        </td>
                    </tr>

                    <!-- Content Body -->
                    <tr>
                        <td style="padding: 32px 36px 28px 36px;">
                            <p style="font-size: 16px; line-height: 1.6; color: #0f172a; margin: 0 0 16px 0;">
                                Hallo <strong style="color: #6b21a8;">{{ $user?->name ?? 'Speler' }}</strong>,
                            </p>
                            <p style="font-size: 14px; line-height: 1.7; color: #475569; margin: 0 0 24px 0;">
                                Er is een verzoek ingediend om het wachtwoord van jouw TBS Kliniek account opnieuw in te stellen. Gebruik onderstaande <strong>6-cijferige herstelcode</strong> om direct een nieuw wachtwoord in te stellen:
                            </p>

                            <!-- The Code Box -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 0 0 26px 0; background-color: #faf5ff; border: 2px dashed #a855f7; border-radius: 16px;">
                                <tr>
                                    <td align="center" style="padding: 24px 16px;">
                                        <div style="font-size: 11px; font-weight: 800; letter-spacing: 2.5px; color: #7c3aed; text-transform: uppercase; margin-bottom: 10px;">
                                            JOUW 6-CIJFERIGE HERSTELCODE
                                        </div>
                                        <div style="font-family: 'SF Mono', Consolas, 'Liberation Mono', Menlo, Courier, monospace; font-size: 44px; font-weight: 900; letter-spacing: 12px; color: #4c1d95; padding-left: 12px; margin: 0;">
                                            {{ $code }}
                                        </div>
                                        <div style="font-size: 12px; font-weight: 600; color: #64748b; margin-top: 10px;">
                                            ⏱️ Deze code is 30 minuten geldig
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Instructions -->
                            <p style="font-size: 13px; line-height: 1.7; color: #475569; margin: 0 0 22px 0;">
                                Ga terug naar het geopende wachtwoordscherm in je browser en vul daar deze 6-cijferige code in samen met je nieuwe wachtwoord.
                            </p>

                            <!-- Direct Button -->
                            <div style="text-align: center; margin: 0 0 24px 0;">
                                <a href="{{ $resetUrl }}" target="_blank" style="display: inline-block; background: linear-gradient(135deg, #7c3aed, #6366f1); color: #ffffff; font-weight: 800; font-size: 14px; text-decoration: none; padding: 14px 32px; border-radius: 12px; box-shadow: 0 4px 14px rgba(124, 58, 237, 0.35); text-transform: uppercase; letter-spacing: 0.5px;">
                                    👉 Direct Wachtwoord Resetten
                                </a>
                            </div>

                            <div style="padding: 14px 16px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 12px; line-height: 1.6; color: #64748b;">
                                <strong>Tip:</strong> Werkt de knop niet door linkbeveiliging in je e-mailprogramma? Vul dan simpelweg handmatig de 6-cijferige code <strong>{{ $code }}</strong> in op <a href="https://tbs-kliniek.nl/wachtwoord-reset" style="color: #7c3aed; text-decoration: underline;">https://tbs-kliniek.nl/wachtwoord-reset</a>.
                            </div>

                            <p style="font-size: 12px; line-height: 1.6; color: #94a3b8; margin: 24px 0 0 0; padding-top: 18px; border-top: 1px solid #f1f5f9;">
                                Heb je deze herstelaanvraag niet zelf gedaan? Geen zorgen, je account blijft veilig en er verandert niets zolang niemand deze code heeft.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 20px 32px; background-color: #f8fafc; border-top: 1px solid #f1f5f9;">
                            <div style="font-size: 11px; color: #94a3b8; line-height: 1.6;">
                                &copy; {{ date('Y') }} TBS Kliniek. Alle rechten voorbehouden.
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
