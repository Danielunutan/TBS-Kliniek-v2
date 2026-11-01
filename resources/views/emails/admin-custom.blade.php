<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $mailSubject }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f1f5f9; padding: 32px 12px;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);">
                    
                    <!-- Top Accent Bar -->
                    <tr>
                        <td style="height: 6px; background: linear-gradient(90deg, #7c3aed, #9333ea, #f59e0b);"></td>
                    </tr>

                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 32px 32px 20px 32px; background-color: #faf5ff; border-bottom: 1px solid #f3e8ff;">
                            <img src="https://tbs-kliniek.nl/assets/logo.png?v=3" alt="TBS Kliniek" width="70" height="70" style="display: block; margin: 0 auto 12px auto; border: 0; outline: none;">
                            <div style="font-size: 22px; font-weight: 900; letter-spacing: -0.5px; color: #1e1b4b; text-transform: uppercase; margin: 0;">
                                TBS KLINIEK
                            </div>
                            <div style="display: inline-block; margin-top: 8px; font-size: 11px; font-weight: 800; letter-spacing: 2px; color: #7c3aed; text-transform: uppercase; background-color: #ede9fe; padding: 5px 14px; border-radius: 20px; border: 1px solid #ddd6fe;">
                                {{ $senderRoleTitle ?? 'OFFICIEEL BERICHT' }}
                            </div>
                        </td>
                    </tr>

                    <!-- Content Body -->
                    <tr>
                        <td style="padding: 32px 36px 28px 36px;">
                            @if(!empty($recipientName))
                                <p style="font-size: 16px; line-height: 1.6; color: #0f172a; margin: 0 0 18px 0;">
                                    Beste <strong style="color: #6b21a8;">{{ $recipientName }}</strong>,
                                </p>
                            @endif

                            <!-- Custom Message Content -->
                            <div style="font-size: 15px; line-height: 1.8; color: #334155; margin: 0 0 28px 0;">
{!! nl2br(e($mailBody)) !!}
                            </div>

                            <!-- Sender Signature Box -->
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #7c3aed; border-radius: 12px; padding: 16px 20px; margin-top: 24px;">
                                <div style="font-size: 14px; font-weight: 800; color: #1e293b;">
                                    {{ $senderName }}
                                </div>
                                <div style="font-size: 12px; font-weight: 600; color: #7c3aed; margin-top: 2px;">
                                    {{ $senderEmail }}
                                </div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 6px;">
                                    Directe reacties op deze e-mail komen binnen in onze beveiligde kliniek mailbox.
                                </div>
                            </div>
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
                        <td align="center" style="padding: 22px 36px 28px 36px; background-color: #faf5ff;">
                            <p style="font-size: 12px; font-weight: 700; color: #6b21a8; margin: 0 0 4px 0;">
                                TBS Kliniek • Het Ultieme Nederlandse Misdaad & TBS Spel
                            </p>
                            <p style="font-size: 11px; color: #94a3b8; margin: 0 0 8px 0;">
                                <a href="https://tbs-kliniek.nl" style="color: #7c3aed; text-decoration: none; font-weight: 600;">tbs-kliniek.nl</a> &bull; 
                                <a href="https://webmail.tbs-kliniek.nl" style="color: #7c3aed; text-decoration: none; font-weight: 600;">Webmail</a>
                            </p>
                            <p style="font-size: 10px; color: #94a3b8; margin: 0;">
                                &copy; {{ date('Y') }} TBS Kliniek. Dit bericht is verzonden via ons beveiligde communicatiesysteem.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
