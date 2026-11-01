<?php

namespace App\Services;

use App\Models\AdminMailLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PleskMailService
{
    private const IMAP_HOST = '{127.0.0.1:143/imap/notls}';

    public const PRIMARY_ACCOUNT = 'daniel@tbs-kliniek.nl';

    public const PRIMARY_PASSWORD = '';

    public const MAILBOXES = [
        'all' => [
            'id' => 'all',
            'name' => 'Alle Postbussen',
            'email' => 'beheer@tbs-kliniek.nl',
            'description' => 'Alle inkomende post van alle adressen',
            'icon' => 'fa-inbox',
            'badge' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/70',
        ],
        'vragen' => [
            'id' => 'vragen',
            'name' => 'Vragen & Helpdesk',
            'email' => 'vragen@tbs-kliniek.nl',
            'description' => 'Spelersvragen en gameplay ondersteuning',
            'icon' => 'fa-headset',
            'badge' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800/70',
        ],
        'privacy' => [
            'id' => 'privacy',
            'name' => 'Privacy & AVG',
            'email' => 'privacy@tbs-kliniek.nl',
            'description' => 'AVG / GDPR inzage en accountverwijdering',
            'icon' => 'fa-user-shield',
            'badge' => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200/80 dark:border-indigo-800/70',
        ],
        'daniel' => [
            'id' => 'daniel',
            'name' => 'Daniël (Directie)',
            'email' => 'daniel@tbs-kliniek.nl',
            'description' => 'Officiële correspondentie van de eigenaar',
            'icon' => 'fa-crown',
            'badge' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-200/80 dark:border-amber-800/70',
        ],
    ];

    /**
     * Get the primary IMAP account password from configuration or environment.
     */
    public function getPrimaryPassword(): string
    {
        return (string) (config('services.plesk.imap_password') ?? env('PLESK_IMAP_PASSWORD', ''));
    }

    /**
     * Open an IMAP connection to Dovecot on 127.0.0.1
     *
     * @return resource|false
     */
    public function openImap(string $folder = 'INBOX')
    {
        if (! extension_loaded('imap')) {
            return false;
        }

        $safeFolder = $this->sanitizeFolder($folder);
        $mailboxSpec = self::IMAP_HOST.$safeFolder;

        // Set reasonable timeouts so UI never hangs
        imap_timeout(IMAP_OPENTIMEOUT, 4);
        imap_timeout(IMAP_READTIMEOUT, 4);

        $password = $this->getPrimaryPassword();

        $conn = @imap_open($mailboxSpec, self::PRIMARY_ACCOUNT, $password, 0, 1, [
            'DISABLE_AUTHENTICATOR' => 'GSSAPI',
        ]);

        return $conn ?: false;
    }

    /**
     * Get folder overview counts (Inbox, Sent, Drafts, Spam, Trash)
     *
     * @return array<string, array{total: int, unread: int}>
     */
    public function getFolderStats(): array
    {
        $folders = ['INBOX', 'Sent', 'Drafts', 'Spam', 'Trash'];
        $stats = [];

        foreach ($folders as $f) {
            $stats[$f] = ['total' => 0, 'unread' => 0];
            $conn = $this->openImap($f);
            if ($conn) {
                $check = @imap_check($conn);
                if ($check) {
                    $stats[$f]['total'] = $check->Nmsgs ?? 0;
                    $unseen = @imap_search($conn, 'UNSEEN');
                    $stats[$f]['unread'] = $unseen ? count($unseen) : 0;
                }
                @imap_close($conn);
            }
        }

        return $stats;
    }

    /**
     * List messages from a folder with pagination, search, and mailbox filtering
     *
     * @return array{messages: array, total: int, page: int, perPage: int, totalPages: int}
     */
    public function listMessages(
        string $folder = 'INBOX',
        int $page = 1,
        int $perPage = 30,
        string $mailboxKey = 'all',
        ?string $search = null
    ): array {
        $conn = $this->openImap($folder);
        if (! $conn) {
            return [
                'messages' => [],
                'total' => 0,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => 1,
            ];
        }

        $searchCriteria = [];

        // Text search
        if (! empty(trim((string) $search))) {
            $term = addslashes(trim($search));
            $searchCriteria[] = "TEXT \"{$term}\"";
        }

        // Mailbox recipient filter
        if ($mailboxKey !== 'all' && isset(self::MAILBOXES[$mailboxKey])) {
            $boxEmail = self::MAILBOXES[$mailboxKey]['email'];
            $searchCriteria[] = "TO \"{$boxEmail}\"";
        }

        $uids = null;
        if (! empty($searchCriteria)) {
            $query = implode(' ', $searchCriteria);
            $uids = @imap_search($conn, $query, SE_UID) ?: [];
        }

        if ($uids !== null) {
            $total = count($uids);
            if ($total === 0) {
                @imap_close($conn);

                return [
                    'messages' => [],
                    'total' => 0,
                    'page' => 1,
                    'perPage' => $perPage,
                    'totalPages' => 1,
                ];
            }

            rsort($uids);
            $totalPages = max(1, (int) ceil($total / $perPage));
            $page = min(max(1, $page), $totalPages);
            $offset = ($page - 1) * $perPage;
            $pagedUids = array_slice($uids, $offset, $perPage);
            $sequence = implode(',', $pagedUids);
            $overview = @imap_fetch_overview($conn, $sequence, FT_UID);
        } else {
            $check = @imap_check($conn);
            $total = $check->Nmsgs ?? 0;
            if ($total === 0) {
                @imap_close($conn);

                return [
                    'messages' => [],
                    'total' => 0,
                    'page' => 1,
                    'perPage' => $perPage,
                    'totalPages' => 1,
                ];
            }

            $totalPages = max(1, (int) ceil($total / $perPage));
            $page = min(max(1, $page), $totalPages);

            $end = max(1, $total - (($page - 1) * $perPage));
            $start = max(1, $end - $perPage + 1);

            $overview = @imap_fetch_overview($conn, "{$start}:{$end}", 0);
        }

        if (! $overview || ! is_array($overview)) {
            @imap_close($conn);

            return [
                'messages' => [],
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => $totalPages,
            ];
        }

        // Sort overview descending by date (newest first)
        usort($overview, function ($a, $b) {
            $tA = isset($a->udate) ? (int) $a->udate : (isset($a->date) ? strtotime($a->date) : 0);
            $tB = isset($b->udate) ? (int) $b->udate : (isset($b->date) ? strtotime($b->date) : 0);

            return $tB <=> $tA;
        });

        $messages = [];
        foreach ($overview as $msg) {
            $from = $this->parseEmailHeader($msg->from ?? '');
            $to = $this->parseEmailHeader($msg->to ?? '');
            $subject = self::decodeMimeString($msg->subject ?? '');

            $dateStr = $msg->date ?? '';
            $timestamp = isset($msg->udate) ? (int) $msg->udate : (strtotime($dateStr) ?: time());

            $messages[] = [
                'id' => $msg->msgno ?? 0,
                'uid' => $msg->uid ?? ($msg->msgno ?? 0),
                'subject' => $subject ?: '(Geen onderwerp)',
                'from_name' => $from['name'],
                'from_email' => $from['email'],
                'to_name' => $to['name'],
                'to_email' => $to['email'],
                'date' => date('d-m-Y H:i', $timestamp),
                'date_short' => (date('Y-m-d', $timestamp) === date('Y-m-d')) ? date('H:i', $timestamp) : date('d M', $timestamp),
                'unread' => empty($msg->seen),
                'flagged' => ! empty($msg->flagged),
            ];
        }

        @imap_close($conn);

        return [
            'messages' => $messages,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
        ];
    }

    /**
     * Fetch a single message's details and body
     */
    public function getMessage(int $uid, string $folder = 'INBOX'): ?array
    {
        $conn = $this->openImap($folder);
        if (! $conn) {
            return null;
        }

        $msgNo = @imap_msgno($conn, $uid);
        if (! $msgNo) {
            $msgNo = $uid;
        }

        $header = @imap_headerinfo($conn, $msgNo);
        if (! $header) {
            @imap_close($conn);

            return null;
        }

        // Mark as seen
        @imap_setflag_full($conn, (string) $msgNo, '\\Seen');

        $from = $this->parseEmailHeader($header->fromaddress ?? '');
        $to = $this->parseEmailHeader($header->toaddress ?? '');
        $decodedSubject = self::decodeMimeString($header->subject ?? '(Geen onderwerp)');

        $body = $this->getHtmlBody($conn, $msgNo);
        $plain = $this->getPlainBody($conn, $msgNo);
        if (empty(trim(strip_tags($body)))) {
            $body = nl2br(e($plain));
        }

        @imap_close($conn);

        $dateTimestamp = isset($header->udate) ? (int) $header->udate : strtotime($header->date ?? 'now');

        return [
            'uid' => $uid,
            'msgno' => $msgNo,
            'subject' => $decodedSubject,
            'from_name' => $from['name'],
            'from_email' => $from['email'],
            'to_name' => $to['name'],
            'to_email' => $to['email'],
            'date' => date('d-m-Y H:i', $dateTimestamp),
            'body_html' => $body,
            'body_plain' => $plain,
        ];
    }

    /**
     * Send an email via Brevo SMTP relay, save copy to Sent IMAP folder, and log to DB.
     *
     * @param  array<int, UploadedFile>  $attachments
     */
    public function sendEmail(
        User $user,
        string $to,
        string $subject,
        string $body,
        ?string $cc = null,
        string $mailboxKey = 'vragen',
        array $attachments = []
    ): bool {
        $senderEmail = self::PRIMARY_ACCOUNT;
        $senderName = 'Daniël • TBS Kliniek Leiding';

        if (isset(self::MAILBOXES[$mailboxKey]) && $mailboxKey !== 'all') {
            $senderEmail = self::MAILBOXES[$mailboxKey]['email'];
            $senderName = self::MAILBOXES[$mailboxKey]['name'];
        }

        // Generate branded HTML
        $cleanHtml = view('emails.admin-custom', [
            'senderEmail' => $senderEmail,
            'senderName' => $senderName,
            'recipientName' => '',
            'mailSubject' => $subject,
            'mailBody' => $body,
            'senderRoleTitle' => 'OFFICIEEL BERICHT',
        ])->render();

        try {
            // Send via Brevo configured in mail.php
            Mail::mailer('smtp')->html($cleanHtml, function ($message) use ($to, $subject, $senderEmail, $senderName, $cc, $attachments) {
                $message->from($senderEmail, $senderName);
                $message->replyTo($senderEmail, $senderName);
                $message->to($to);
                if (! empty($cc)) {
                    $message->cc(array_map('trim', explode(',', $cc)));
                }
                $message->subject($subject);

                foreach ($attachments as $att) {
                    if ($att instanceof UploadedFile && $att->isValid()) {
                        $message->attach($att->getRealPath(), [
                            'as' => $att->getClientOriginalName(),
                            'mime' => $att->getMimeType(),
                        ]);
                    }
                }
            });

            // Append copy to Sent folder in Dovecot
            $this->appendSentCopy($to, $subject, $cleanHtml, $cc, $senderEmail, $senderName);

            // Log to database
            AdminMailLog::create([
                'user_id' => $user->id,
                'sender_email' => $senderEmail,
                'sender_name' => $senderName,
                'recipient_email' => $to,
                'recipient_name' => null,
                'subject' => $subject,
                'body' => $body,
                'status' => 'sent',
                'error_message' => null,
                'ip_address' => request()->ip(),
            ]);

            return true;
        } catch (Throwable $e) {
            Log::error('PleskMailService sendEmail failed: '.$e->getMessage(), [
                'exception' => $e,
                'to' => $to,
                'from' => $senderEmail,
            ]);

            AdminMailLog::create([
                'user_id' => $user->id,
                'sender_email' => $senderEmail,
                'sender_name' => $senderName,
                'recipient_email' => $to,
                'recipient_name' => null,
                'subject' => $subject,
                'body' => $body,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'ip_address' => request()->ip(),
            ]);

            return false;
        }
    }

    /**
     * Append email copy to Sent IMAP folder
     */
    private function appendSentCopy(
        string $to,
        string $subject,
        string $bodyHtml,
        ?string $cc = null,
        string $senderEmail = self::PRIMARY_ACCOUNT,
        string $senderName = 'TBS Kliniek'
    ): void {
        $conn = $this->openImap('Sent');
        if (! $conn) {
            return;
        }

        $cleanSenderName = str_replace(["\r", "\n"], '', $senderName);
        $cleanSenderEmail = str_replace(["\r", "\n"], '', $senderEmail);
        $cleanTo = str_replace(["\r", "\n"], '', $to);
        $cleanCc = $cc !== null ? str_replace(["\r", "\n"], '', $cc) : null;
        $cleanSubject = str_replace(["\r", "\n"], '', $subject);

        $date = date('r');
        $headers = "From: \"{$cleanSenderName}\" <{$cleanSenderEmail}>\r\n";
        $headers .= "To: <{$cleanTo}>\r\n";
        if (! empty($cleanCc)) {
            $headers .= "Cc: {$cleanCc}\r\n";
        }
        $headers .= "Date: {$date}\r\n";
        $headers .= "Subject: {$cleanSubject}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";

        $fullMsg = $headers.$bodyHtml;
        @imap_append($conn, self::IMAP_HOST.'INBOX.Sent', $fullMsg, '\\Seen');
        @imap_close($conn);
    }

    /**
     * Delete / trash a message
     */
    public function deleteMessage(int $uid, string $folder = 'INBOX'): bool
    {
        $conn = $this->openImap($folder);
        if (! $conn) {
            return false;
        }

        $msgNo = @imap_msgno($conn, $uid);
        if (! $msgNo) {
            $msgNo = $uid;
        }

        if ($folder !== 'Trash' && $folder !== 'INBOX.Trash') {
            @imap_mail_move($conn, (string) $msgNo, 'INBOX.Trash');
        } else {
            @imap_delete($conn, $msgNo);
        }

        @imap_expunge($conn);
        @imap_close($conn);

        return true;
    }

    /**
     * Move message to Spam folder
     */
    public function markSpam(int $uid, string $folder = 'INBOX'): bool
    {
        $conn = $this->openImap($folder);
        if (! $conn) {
            return false;
        }

        $msgNo = @imap_msgno($conn, $uid);
        if (! $msgNo) {
            $msgNo = $uid;
        }

        @imap_mail_move($conn, (string) $msgNo, 'INBOX.Spam');
        @imap_expunge($conn);
        @imap_close($conn);

        return true;
    }

    /**
     * Move message to a target folder
     */
    public function moveMessage(int $uid, string $fromFolder, string $toFolder): bool
    {
        $conn = $this->openImap($fromFolder);
        if (! $conn) {
            return false;
        }

        $msgNo = @imap_msgno($conn, $uid);
        if (! $msgNo) {
            $msgNo = $uid;
        }

        $targetSafe = $this->sanitizeFolder($toFolder);
        @imap_mail_move($conn, (string) $msgNo, $targetSafe);
        @imap_expunge($conn);
        @imap_close($conn);

        return true;
    }

    /**
     * Toggle Seen (read/unread) flag on a message
     */
    public function toggleSeen(int $uid, bool $seen, string $folder = 'INBOX'): bool
    {
        $conn = $this->openImap($folder);
        if (! $conn) {
            return false;
        }

        $msgNo = @imap_msgno($conn, $uid);
        if (! $msgNo) {
            $msgNo = $uid;
        }

        if ($seen) {
            @imap_setflag_full($conn, (string) $msgNo, '\\Seen');
        } else {
            @imap_clearflag_full($conn, (string) $msgNo, '\\Seen');
        }

        @imap_close($conn);

        return true;
    }

    private function sanitizeFolder(string $folder): string
    {
        return match (strtoupper(trim($folder))) {
            'SENT', 'INBOX.SENT' => 'INBOX.Sent',
            'DRAFTS', 'INBOX.DRAFTS' => 'INBOX.Drafts',
            'TRASH', 'INBOX.TRASH' => 'INBOX.Trash',
            'SPAM', 'INBOX.SPAM', 'JUNK', 'INBOX.JUNK' => 'INBOX.Spam',
            default => 'INBOX',
        };
    }

    /**
     * Decode any RFC 2047 MIME encoded header string (=?UTF-8?Q?...?= or =?UTF-8?B?...?=)
     */
    public static function decodeMimeString(?string $string): string
    {
        if (empty($string)) {
            return '';
        }

        $string = trim($string);

        if (str_contains($string, '=?')) {
            $decoded = @iconv_mime_decode($string, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
            if ($decoded !== false && ! empty($decoded) && ! str_contains($decoded, '=?')) {
                return $decoded;
            }

            $decodedMb = @mb_decode_mimeheader($string);
            if (! empty($decodedMb) && ! str_contains($decodedMb, '=?')) {
                return $decodedMb;
            }
        }

        return $string;
    }

    private function parseEmailHeader(string $raw): array
    {
        $decodedRaw = self::decodeMimeString(trim($raw));
        if (preg_match('/^(.*?)\s*<([^>]+)>$/u', $decodedRaw, $m)) {
            $name = trim(trim($m[1]), '"\'');
            $name = self::decodeMimeString($name);

            return ['name' => $name ?: $m[2], 'email' => strtolower($m[2])];
        }

        if (filter_var($decodedRaw, FILTER_VALIDATE_EMAIL)) {
            return ['name' => $decodedRaw, 'email' => strtolower($decodedRaw)];
        }

        return ['name' => $decodedRaw, 'email' => $decodedRaw];
    }

    private function getHtmlBody($conn, int $msgNo): string
    {
        $structure = @imap_fetchstructure($conn, $msgNo);
        if (! $structure) {
            return '';
        }

        return $this->extractBodyPart($conn, $msgNo, $structure, 'TEXT/HTML');
    }

    private function getPlainBody($conn, int $msgNo): string
    {
        $structure = @imap_fetchstructure($conn, $msgNo);
        if (! $structure) {
            return (string) @imap_body($conn, $msgNo);
        }

        return $this->extractBodyPart($conn, $msgNo, $structure, 'TEXT/PLAIN');
    }

    private function extractBodyPart($conn, int $msgNo, $structure, string $mimeType, string $partNo = ''): string
    {
        if (! empty($structure->parts)) {
            foreach ($structure->parts as $index => $subPart) {
                $prefix = $partNo ? $partNo.'.' : '';
                $currentPartNo = $prefix.($index + 1);
                $result = $this->extractBodyPart($conn, $msgNo, $subPart, $mimeType, $currentPartNo);
                if (! empty($result)) {
                    return $result;
                }
            }
        }

        $type = 'TEXT';
        if (isset($structure->type)) {
            $types = ['TEXT', 'MULTIPART', 'MESSAGE', 'APPLICATION', 'AUDIO', 'IMAGE', 'VIDEO', 'MODEL', 'OTHER'];
            $type = $types[$structure->type] ?? 'TEXT';
        }
        $sub = $structure->subtype ?? 'PLAIN';
        $currentMime = strtoupper("{$type}/{$sub}");

        if ($currentMime === strtoupper($mimeType)) {
            $data = $partNo ? @imap_fetchbody($conn, $msgNo, $partNo) : @imap_body($conn, $msgNo);
            $encoding = $structure->encoding ?? 0;
            if ($encoding === 3) {
                return (string) base64_decode($data);
            }
            if ($encoding === 4) {
                return (string) quoted_printable_decode($data);
            }

            return (string) $data;
        }

        return '';
    }
}
