<?php

namespace App\Http\Controllers;

use App\Mail\AdminCustomMail;
use App\Models\AdminMailLog;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AdminMailController extends Controller
{
    /**
     * Active sender identities supported by Brevo and Plesk.
     *
     * @var array<string, array{name: string, title: string, desc: string, icon: string, badge_color: string}>
     */
    public const SENDER_IDENTITIES = [
        'vragen@tbs-kliniek.nl' => [
            'name' => 'TBS Kliniek Support & Vragen',
            'title' => 'Kliniek Klantenservice & Helpdesk',
            'desc' => 'Algemene spelersvragen, ondersteuning en hulp bij gameplay.',
            'icon' => 'fa-headset',
            'badge_color' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        ],
        'privacy@tbs-kliniek.nl' => [
            'name' => 'TBS Kliniek Privacy & FG',
            'title' => 'Functionaris Gegevensbescherming & AVG',
            'desc' => 'AVG / GDPR inzageverzoeken, privacyvragen en accountverwijderingen.',
            'icon' => 'fa-user-shield',
            'badge_color' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
        ],
        'daniel@tbs-kliniek.nl' => [
            'name' => 'Daniël | Eigenaar TBS Kliniek',
            'title' => 'Directie & Kliniek Eigenaar',
            'desc' => 'Officiële correspondentie, partnerships en directiebesluiten.',
            'icon' => 'fa-crown',
            'badge_color' => 'bg-amber-100 text-amber-800 border-amber-200',
        ],
    ];

    /**
     * Mailbox credentials and server configuration.
     *
     * @var array<string, string>
     */
    public const MAILBOX_CONFIG = [
        'webmail_url' => 'https://webmail.tbs-kliniek.nl',
        'primary_mailbox' => 'daniel@tbs-kliniek.nl',
        'mailbox_password' => 'TBSKliniek2026!Mail#Dan',
        'imap_host' => 'tbs-kliniek.nl',
        'imap_port' => '993 (SSL) / 143 (STARTTLS)',
        'smtp_host' => 'smtp-relay.brevo.com',
        'smtp_port' => '587 (TLS)',
        'smtp_user' => 'bd9cdf001@smtp-brevo.com',
    ];

    /**
     * Show the owner mailbox management and email dispatch dashboard.
     */
    public function index(Request $request): View
    {
        if (! $request->user()?->isOwner()) {
            abort(403, 'Alleen de eigenaar heeft toegang tot het E-mailbeheer.');
        }

        $logs = AdminMailLog::with('user')
            ->latest()
            ->paginate(15);

        // Fetch recent active players with verified emails for quick-select
        $players = User::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->latest('id')
            ->take(100)
            ->get(['id', 'name', 'email']);

        // Try reading inbox status via IMAP safely
        $inboxMessages = $this->fetchInboxMessages();

        return view('admin.mail.index', [
            'senders' => self::SENDER_IDENTITIES,
            'mailboxConfig' => self::MAILBOX_CONFIG,
            'logs' => $logs,
            'players' => $players,
            'inboxMessages' => $inboxMessages,
        ]);
    }

    /**
     * Send an email through Brevo SMTP relay with selected sender identity.
     */
    public function send(Request $request): RedirectResponse
    {
        if (! $request->user()?->isOwner()) {
            abort(403, 'Alleen de eigenaar heeft toegang tot het E-mailbeheer.');
        }

        $allowedSenders = array_keys(self::SENDER_IDENTITIES);

        $validated = $request->validate([
            'sender_email' => ['required', 'string', 'in:'.implode(',', $allowedSenders)],
            'recipient_email' => ['required', 'email', 'max:255'],
            'recipient_name' => ['nullable', 'string', 'max:150'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $senderInfo = self::SENDER_IDENTITIES[$validated['sender_email']];
        $senderName = $senderInfo['name'];
        $recipientName = $validated['recipient_name'] ?? '';

        try {
            // Send via Brevo configured in mail.php
            Mail::mailer('smtp')->to($validated['recipient_email'], $recipientName)->send(
                new AdminCustomMail(
                    senderEmail: $validated['sender_email'],
                    senderName: $senderName,
                    recipientName: $recipientName,
                    mailSubject: $validated['subject'],
                    mailBody: $validated['body'],
                    senderRoleTitle: $senderInfo['title']
                )
            );

            // Log successfully sent email
            AdminMailLog::create([
                'user_id' => $request->user()->id,
                'sender_email' => $validated['sender_email'],
                'sender_name' => $senderName,
                'recipient_email' => $validated['recipient_email'],
                'recipient_name' => $recipientName ?: null,
                'subject' => $validated['subject'],
                'body' => $validated['body'],
                'status' => 'sent',
                'error_message' => null,
                'ip_address' => $request->ip(),
            ]);

            return back()->with('success', "📧 E-mail succesvol verzonden vanaf {$validated['sender_email']} naar {$validated['recipient_email']} via Brevo!");
        } catch (Exception $e) {
            Log::error('AdminMailController send failed: '.$e->getMessage(), [
                'exception' => $e,
                'recipient' => $validated['recipient_email'],
                'sender' => $validated['sender_email'],
            ]);

            // Log failed attempt
            AdminMailLog::create([
                'user_id' => $request->user()->id,
                'sender_email' => $validated['sender_email'],
                'sender_name' => $senderName,
                'recipient_email' => $validated['recipient_email'],
                'recipient_name' => $recipientName ?: null,
                'subject' => $validated['subject'],
                'body' => $validated['body'],
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'ip_address' => $request->ip(),
            ]);

            return back()->with('error', 'Fout bij verzenden via Brevo: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Get JSON details of a specific mail log entry.
     */
    public function showLog(Request $request, AdminMailLog $log): JsonResponse
    {
        if (! $request->user()?->isOwner()) {
            abort(403);
        }

        return response()->json([
            'id' => $log->id,
            'sender_email' => $log->sender_email,
            'sender_name' => $log->sender_name,
            'recipient_email' => $log->recipient_email,
            'recipient_name' => $log->recipient_name,
            'subject' => $log->subject,
            'body' => $log->body,
            'status' => $log->status,
            'error_message' => $log->error_message,
            'created_at' => $log->created_at->format('d-m-Y H:i:s'),
            'sender_user' => $log->user?->name ?? 'Onbekend',
        ]);
    }

    /**
     * Fetch recent messages from IMAP safely without hanging or throwing unhandled errors.
     *
     * @return array{connected: bool, count: int, messages: array<int, array<string, mixed>>, error: ?string}
     */
    protected function fetchInboxMessages(): array
    {
        if (! extension_loaded('imap')) {
            return [
                'connected' => false,
                'count' => 0,
                'messages' => [],
                'error' => 'PHP IMAP-extensie is niet ingeschakeld in deze PHP-omgeving.',
            ];
        }

        $host = '{localhost:143/imap/novalidate-cert}INBOX';
        $user = self::MAILBOX_CONFIG['primary_mailbox'];
        $password = self::MAILBOX_CONFIG['mailbox_password'];

        try {
            // Set 3 second timeout to avoid any page freeze
            imap_timeout(IMAP_OPENTIMEOUT, 3);
            imap_timeout(IMAP_READTIMEOUT, 3);

            $mbox = @imap_open($host, $user, $password, OP_READONLY, 1);
            if (! $mbox) {
                $err = imap_last_error() ?: 'Kan geen verbinding maken met lokale mailserver.';

                return [
                    'connected' => false,
                    'count' => 0,
                    'messages' => [],
                    'error' => $err,
                ];
            }

            $numMessages = imap_num_msg($mbox);
            $messages = [];

            if ($numMessages > 0) {
                $start = max(1, $numMessages - 14); // fetch up to 15 latest
                for ($i = $numMessages; $i >= $start; $i--) {
                    $header = @imap_headerinfo($mbox, $i);
                    if ($header) {
                        $messages[] = [
                            'msgno' => $i,
                            'subject' => ! empty($header->subject) ? mb_decode_mimeheader($header->subject) : '(Geen onderwerp)',
                            'from' => ! empty($header->fromaddress) ? mb_decode_mimeheader($header->fromaddress) : 'Onbekend',
                            'to' => ! empty($header->toaddress) ? mb_decode_mimeheader($header->toaddress) : 'daniel@tbs-kliniek.nl',
                            'date' => ! empty($header->date) ? date('d-m-Y H:i', strtotime($header->date)) : '',
                            'unseen' => ($header->Unseen === 'U' || $header->Recent === 'N'),
                        ];
                    }
                }
            }

            @imap_close($mbox);

            return [
                'connected' => true,
                'count' => $numMessages,
                'messages' => $messages,
                'error' => null,
            ];
        } catch (Exception $e) {
            return [
                'connected' => false,
                'count' => 0,
                'messages' => [],
                'error' => $e->getMessage(),
            ];
        }
    }
}
