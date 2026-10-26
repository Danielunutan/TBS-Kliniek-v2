<?php

namespace App\Http\Controllers;

use App\Models\AdminMailLog;
use App\Models\User;
use App\Services\PleskMailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMailController extends Controller
{
    public function __construct(
        protected PleskMailService $mailService
    ) {}

    /**
     * Show the full 3-column Outlook/Apple Mail web client for the Owner.
     */
    public function index(Request $request): View
    {
        if (! $request->user()?->isOwner()) {
            abort(403, 'Alleen de eigenaar heeft toegang tot het E-mailbeheer.');
        }

        $activeMailbox = $request->query('mailbox', 'all');
        if (! isset(PleskMailService::MAILBOXES[$activeMailbox])) {
            $activeMailbox = 'all';
        }

        $folder = $request->query('folder', 'INBOX');
        $page = max(1, (int) $request->query('page', 1));
        $search = $request->query('q');

        $stats = $this->mailService->getFolderStats();
        $paged = $this->mailService->listMessages($folder, $page, 30, $activeMailbox, $search);

        // Fetch recent active players with verified emails for quick-autocomplete in compose modal
        $players = User::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->latest('id')
            ->take(150)
            ->get(['id', 'name', 'email']);

        // Recent sent mail logs from DB for auditing
        $logs = AdminMailLog::with('user')->latest()->take(10)->get();

        return view('admin.mail.index', [
            'activeMailbox' => $activeMailbox,
            'mailboxes' => PleskMailService::MAILBOXES,
            'folder' => $folder,
            'stats' => $stats,
            'messages' => $paged['messages'],
            'totalMessages' => $paged['total'],
            'currentPage' => $paged['page'],
            'perPage' => $paged['perPage'],
            'totalPages' => $paged['totalPages'],
            'searchQuery' => $search,
            'players' => $players,
            'logs' => $logs,
        ]);
    }

    /**
     * Fetch message list as JSON for live AJAX folder switching, search, and polling.
     */
    public function feed(Request $request): JsonResponse
    {
        if (! $request->user()?->isOwner()) {
            abort(403);
        }

        $activeMailbox = $request->query('mailbox', 'all');
        $folder = $request->query('folder', 'INBOX');
        $page = max(1, (int) $request->query('page', 1));
        $search = $request->query('q');

        $paged = $this->mailService->listMessages($folder, $page, 30, $activeMailbox, $search);
        $stats = $this->mailService->getFolderStats();

        return response()->json([
            'mailbox' => $activeMailbox,
            'folder' => $folder,
            'messages' => $paged['messages'],
            'total' => $paged['total'],
            'page' => $paged['page'],
            'perPage' => $paged['perPage'],
            'totalPages' => $paged['totalPages'],
            'stats' => $stats,
        ]);
    }

    /**
     * Fetch single message body & headers via AJAX.
     */
    public function show(Request $request, int $uid): JsonResponse
    {
        if (! $request->user()?->isOwner()) {
            abort(403);
        }

        $folder = $request->query('folder', 'INBOX');
        $msg = $this->mailService->getMessage($uid, $folder);

        if (! $msg) {
            return response()->json(['error' => 'Bericht niet gevonden'], 404);
        }

        return response()->json(['message' => $msg]);
    }

    /**
     * Send email via Brevo relay, save copy to Dovecot Sent folder, and log to DB.
     */
    public function send(Request $request): JsonResponse
    {
        if (! $request->user()?->isOwner()) {
            abort(403);
        }

        $validated = $request->validate([
            'to' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:50000'],
            'cc' => ['nullable', 'string', 'max:255'],
            'mailbox' => ['nullable', 'string'],
            'attachments.*' => ['nullable', 'file', 'max:20480'],
        ]);

        $mailboxKey = $validated['mailbox'] ?? 'vragen';
        $attachments = $request->file('attachments') ?: [];

        $success = $this->mailService->sendEmail(
            $request->user(),
            $validated['to'],
            $validated['subject'],
            $validated['body'],
            $validated['cc'] ?? null,
            $mailboxKey,
            $attachments
        );

        if (! $success) {
            return response()->json(['error' => 'Fout bij het verzenden van de e-mail via Brevo.'], 500);
        }

        return response()->json([
            'ok' => true,
            'message' => 'E-mail succesvol verzonden via Brevo!',
        ]);
    }

    /**
     * Move message to Trash or delete permanently.
     */
    public function destroy(Request $request, int $uid): JsonResponse
    {
        if (! $request->user()?->isOwner()) {
            abort(403);
        }

        $folder = $request->query('folder', 'INBOX');
        $ok = $this->mailService->deleteMessage($uid, $folder);

        return response()->json(['ok' => $ok]);
    }

    /**
     * Mark message as spam.
     */
    public function spam(Request $request, int $uid): JsonResponse
    {
        if (! $request->user()?->isOwner()) {
            abort(403);
        }

        $folder = $request->query('folder', 'INBOX');
        $ok = $this->mailService->markSpam($uid, $folder);

        return response()->json(['ok' => $ok]);
    }

    /**
     * Move message to another folder.
     */
    public function move(Request $request, int $uid): JsonResponse
    {
        if (! $request->user()?->isOwner()) {
            abort(403);
        }

        $fromFolder = $request->query('folder', 'INBOX');
        $targetFolder = $request->input('target_folder', 'INBOX.Trash');

        $ok = $this->mailService->moveMessage($uid, $fromFolder, $targetFolder);

        return response()->json(['ok' => $ok]);
    }

    /**
     * Toggle read/unread flag on a message.
     */
    public function toggleRead(Request $request, int $uid): JsonResponse
    {
        if (! $request->user()?->isOwner()) {
            abort(403);
        }

        $folder = $request->query('folder', 'INBOX');
        $seen = $request->boolean('seen', true);

        $ok = $this->mailService->toggleSeen($uid, $seen, $folder);

        return response()->json(['ok' => $ok]);
    }
}
