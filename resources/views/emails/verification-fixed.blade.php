<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-mailverificatie hersteld | TBS Kliniek</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <!-- Hidden Preheader -->
    <div style="display: none; font-size: 1px; color: #f1f5f9; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
        Het probleem met de verificatielink is opgelost! Jouw nieuwe 6-cijferige verificatiecode is {{ $code }}.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f1f5f9; padding: 36px 12px;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 560px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);">
                    
                    <!-- Top Accent Bar -->
                    <tr>
                        <td style="height: 6px; background: linear-gradient(90deg, #10b981, #7c3aed, #f59e0b);"></td>
                    </tr>

                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 36px 32px 18px 32px; background-color: #f0fdf4; border-bottom: 1px solid #dcfce7;">
                            @if(isset($message) && file_exists(public_path('assets/logo.png')))
                                <img src="{{ $message->embed(public_path('assets/logo.png')) }}" alt="TBS Kliniek" width="76" height="76" style="display: block; margin: 0 auto 14px auto; border: 0; outline: none;">
                            @else
                                <img src="https://tbs-kliniek.nl/assets/logo.png?v=3" alt="TBS Kliniek" width="76" height="76" style="display: block; margin: 0 auto 14px auto; border: 0; outline: none;">
                            @endif
                            <div style="font-size: 24px; font-weight: 900; letter-spacing: -0.5px; color: #064e3b; text-transform: uppercase; margin: 0;">
                                TBS KLINIEK
                            </div>
                            <div style="display: inline-block; margin-top: 8px; font-size: 11px; font-weight: 800; letter-spacing: 2px; color: #059669; text-transform: uppercase; background-color: #d1fae5; padding: 5px 14px; border-radius: 20px; border: 1px solid #a7f3d0;">
                                ✓ FOUT VERHOLPEN • NIEUWE VERIFICATIECODE
                            </div>
                        </td>
                    </tr>

                    <!-- Content Body -->
                    <tr>
                        <td style="padding: 32px 36px 28px 36px;">
                            <p style="font-size: 16px; line-height: 1.6; color: #0f172a; margin: 0 0 16px 0;">
                                Beste <strong style="color: #6b21a8;">{{ $user?->name ?? 'Speler' }}</strong>,
                            </p>

                            <!-- Alert Box -->
                            <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 16px 20px; margin-bottom: 22px;">
                                <div style="font-size: 14px; font-weight: 800; color: #065f46; margin-bottom: 4px;">
                                    🛠️ De e-mailverificatie is succesvol hersteld
                                </div>
                                <div style="font-size: 13px; line-height: 1.6; color: #047857;">
                                    Mogelijk heb je eerder geprobeerd jouw e-mailadres te bevestigen via een link die door een storing bij de mailserver op een verkeerd adres uitkwam. Onze excuses voor het ongemak!
                                </div>
                            </div>

                            <p style="font-size: 14px; line-height: 1.7; color: #475569; margin: 0 0 24px 0;">
                                We hebben het verificatiesysteem compleet vernieuwd. Vanaf nu werkt de bevestiging eenvoudig en betrouwbaar met een <strong>6-cijferige verificatiecode</strong> zonder storingsgevoelige links.
                            </p>

                            <!-- The Code Box -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 0 0 26px 0; background-color: #faf5ff; border: 2px dashed #a855f7; border-radius: 16px;">
                                <tr>
                                    <td align="center" style="padding: 24px 16px;">
                                        <div style="font-size: 11px; font-weight: 800; letter-spacing: 2.5px; color: #7c3aed; text-transform: uppercase; margin-bottom: 10px;">
                                            JOUW VERIFICATIECODE
                                        </div>
                                        <div style="font-family: 'SF Mono', Consolas, 'Liberation Mono', Menlo, Courier, monospace; font-size: 44px; font-weight: 900; letter-spacing: 12px; color: #4c1d95; padding-left: 12px; margin: 0;">
                                            {{ $code }}
                                        </div>
                                        <div style="margin-top: 14px; font-size: 12px; font-weight: 700; color: #b91c1c; display: inline-block; background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 5px 14px;">
                                            ⏱️ Deze code is 30 minuten geldig{{ (isset($expiresAt) && $expiresAt instanceof \Carbon\CarbonInterface) ? ' (tot ' . $expiresAt->format('H:i') . ' uur)' : '' }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Direct Link to Site Button -->
                            <div style="text-align: center; margin: 0 0 28px 0;">
                                <a href="https://tbs-kliniek.nl/email/verify" target="_blank" style="display: inline-block; background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; font-weight: 800; font-size: 14px; text-decoration: none; padding: 14px 32px; border-radius: 12px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35); text-transform: uppercase; letter-spacing: 0.5px;">
                                    👉 Naar Verificatiepagina & Code Invoeren
                                </a>
                                <div style="margin-top: 10px; font-size: 11px; color: #64748b;">
                                    Directe link: <a href="https://tbs-kliniek.nl/email/verify" style="color: #059669; text-decoration: underline;">https://tbs-kliniek.nl/email/verify</a>
                                </div>
                            </div>

                            <!-- Instructions -->
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; margin-bottom: 22px;">
                                <div style="font-size: 13px; font-weight: 800; color: #1e293b; margin-bottom: 8px;">
                                    📌 Hoe rond je jouw verificatie af?
                                </div>
                                <ol style="margin: 0; padding-left: 20px; font-size: 13px; line-height: 1.8; color: #475569;">
                                    <li>Klik op de groene knop hierboven of ga in je browser naar <strong>TBS Kliniek</strong>.</li>
                                    <li>Voer de 6-cijferige code <strong>{{ $code }}</strong> in op het verificatiescherm.</li>
                                    <li>Klik op <strong>Verifieer Code & Start Spel</strong> om direct toegang te krijgen!</li>
                                </ol>
                            </div>

                            <p style="font-size: 12px; line-height: 1.6; color: #64748b; margin: 0 0 16px 0;">
                                💡 <em>Ben je later dan 30 minuten? Geen probleem. Op de verificatiepagina op de site kun je altijd met één klik direct een verse code aanvragen.</em>
                            </p>

                            <p style="font-size: 12px; line-height: 1.6; color: #94a3b8; margin: 0;">
                                🔒 <strong>Beveiliging:</strong> Deel deze code nooit met anderen. Medewerkers van de TBS Kliniek zullen je nooit om jouw wachtwoord of code vragen.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer Divider -->
                    <tr>
                        <td style="padding: 0 36px;">
                            <div style="height: 1px; background-color: #e2e8f0;"></div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 24px 36px 32px 36px; background-color: #faf5ff;">
                            <p style="font-size: 12px; font-weight: 700; color: #6b21a8; margin: 0 0 4px 0;">
                                TBS Kliniek • Het Ultieme Nederlandse Misdaad & TBS Spel
                            </p>
                            <p style="font-size: 11px; color: #94a3b8; margin: 0;">
                                © {{ date('Y') }} TBS Kliniek. Alle rechten voorbehouden.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
