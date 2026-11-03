<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Models\UserWarning;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminModerationController extends Controller
{
    /**
     * Show moderation overview with active bans, recent warnings, and incoming reports.
     */
    public function index(Request $request): View
    {
        abort_unless(Auth::user()?->canModerate(), 403, 'Toegang geweigerd. Deze beheeromgeving is uitsluitend toegankelijk voor TBS Kliniek personeel.');

        $search = $request->input('search');

        $activeBansQuery = User::where('is_banned', true)
            ->with(['character', 'bannedBy'])
            ->latest('banned_at');

        $warningsQuery = UserWarning::with(['user.character', 'issuer'])
            ->latest();

        $reportsQuery = Report::with(['reporter', 'handler'])
            ->latest();

        if ($search) {
            $activeBansQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('character', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });

            $warningsQuery->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhereHas('character', function ($cq) use ($search) {
                                $cq->where('name', 'like', "%{$search}%");
                            });
                    });
            });

            $reportsQuery->where(function ($q) use ($search) {
                $q->where('reported_name', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%")
                    ->orWhereHas('reporter', function ($rq) use ($search) {
                        $rq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $activeBans = $activeBansQuery->paginate(10, ['*'], 'bans_page')->withQueryString()->appends(['tab' => 'bans']);
        $recentWarnings = $warningsQuery->paginate(15, ['*'], 'warnings_page')->withQueryString()->appends(['tab' => 'warnings']);
        $reports = $reportsQuery->paginate(15, ['*'], 'reports_page')->withQueryString()->appends(['tab' => 'reports']);

        $totalBannedCount = User::where('is_banned', true)->count();
        $totalWarningsCount = UserWarning::count();
        $unackedWarningsCount = UserWarning::whereNull('acknowledged_at')->count();
        $pendingReportsCount = Report::where('status', 'pending')->count();
        $totalReportsCount = Report::count();

        return view('admin.moderation', compact(
            'activeBans',
            'recentWarnings',
            'reports',
            'totalBannedCount',
            'totalWarningsCount',
            'unackedWarningsCount',
            'pendingReportsCount',
            'totalReportsCount',
            'search'
        ));
    }

    /**
     * Ban a user from the website.
     */
    public function banUser(Request $request, User $user): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        abort_unless($currentUser?->canModerate(), 403, 'Toegang geweigerd.');

        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Je kunt jezelf niet verbannen.');
        }

        if ($user->isOwner()) {
            return back()->with('error', 'De eigenaar van het platform kan niet worden verbannen.');
        }

        if ($user->isStaff() && ! $currentUser->isOwner()) {
            return back()->with('error', 'Alleen de eigenaar kan staffleden en moderators verbannen.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:3|max:1000',
            'duration' => 'required|string|in:1_hour,6_hours,12_hours,1_day,3_days,7_days,14_days,30_days,permanent',
        ]);

        $until = match ($validated['duration']) {
            '1_hour' => Carbon::now()->addHour(),
            '6_hours' => Carbon::now()->addHours(6),
            '12_hours' => Carbon::now()->addHours(12),
            '1_day' => Carbon::now()->addDay(),
            '3_days' => Carbon::now()->addDays(3),
            '7_days' => Carbon::now()->addDays(7),
            '14_days' => Carbon::now()->addDays(14),
            '30_days' => Carbon::now()->addDays(30),
            'permanent' => null,
        };

        $ban = DB::transaction(fn () => $user->ban($validated['reason'], $until, $currentUser));

        $targetName = $user->character?->name ?? $user->name;
        $durationLabel = $until ? 'tot '.$until->locale('nl')->isoFormat('D MMMM [om] HH:mm') : 'permanent';

        $adminName = $currentUser?->name ?? 'Staff';

        try {
            AuditLogService::log(
                action: 'user_ban',
                category: 'moderation',
                description: "Speler verbannen door {$adminName}: {$validated['reason']} ({$durationLabel})",
                character: $user->character,
                user: $user,
                details: [
                    'source_key' => "ban_{$ban->id}",
                    'reason' => $validated['reason'],
                    'duration' => $validated['duration'],
                    'banned_until' => $until?->toIso8601String(),
                    'banned_by_id' => $currentUser?->id,
                    'banned_by_name' => $adminName,
                ],
                customRisk: 'hoog',
                customReason: "Account verbannen door {$adminName}: {$validated['reason']}"
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log user_ban audit record: '.$e->getMessage());
        }

        return back()->with('success', "Patiënt {$targetName} is succesvol verbannen ({$durationLabel}).");
    }

    /**
     * Unban a user and restore access.
     */
    public function unbanUser(Request $request, User $user): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        abort_unless($currentUser?->canModerate(), 403, 'Toegang geweigerd.');

        if (($user->isOwner() || $user->role === 'admin') && ! $currentUser->isOwner()) {
            return back()->with('error', 'Alleen de eigenaar kan de verbanning van een Admin of Eigenaar opheffen.');
        }

        if (! $user->is_banned) {
            return back()->with('info', 'Deze gebruiker is momenteel niet verbannen.');
        }

        DB::transaction(fn () => $user->unban($currentUser));

        $targetName = $user->character?->name ?? $user->name;

        $adminName = $currentUser?->name ?? 'Staff';

        try {
            AuditLogService::log(
                action: 'user_unban',
                category: 'moderation',
                description: "Verbanning van {$targetName} opgeheven door {$adminName}",
                character: $user->character,
                user: $user,
                details: [
                    'unbanned_by_id' => $currentUser?->id,
                    'unbanned_by_name' => $adminName,
                ],
                customRisk: 'verdacht',
                customReason: "Verbanning handmatig opgeheven door {$adminName}"
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log user_unban audit record: '.$e->getMessage());
        }

        return back()->with('success', "De verbanning van {$targetName} is opgeheven. De patiënt heeft weer toegang.");
    }

    /**
     * Issue an official warning to a user.
     */
    public function warnUser(Request $request, User $user): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        abort_unless($currentUser?->canModerate(), 403, 'Toegang geweigerd.');

        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Je kunt jezelf geen waarschuwing geven.');
        }

        if (($user->isOwner() || $user->role === 'admin') && ! $currentUser->isOwner()) {
            return back()->with('error', 'Je kunt geen officiële waarschuwing uitdelen aan een Admin of de Eigenaar.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:3|max:1000',
        ]);

        $warning = DB::transaction(fn () => $user->warn($validated['reason'], $currentUser));

        $targetName = $user->character?->name ?? $user->name;

        $adminName = $currentUser?->name ?? 'Staff';

        try {
            AuditLogService::log(
                action: 'user_warning',
                category: 'moderation',
                description: "Officiële waarschuwing uitgedeeld aan {$targetName} door {$adminName}: {$validated['reason']}",
                character: $user->character,
                user: $user,
                details: [
                    'source_key' => "warn_{$warning->id}",
                    'reason' => $validated['reason'],
                    'issuer_id' => $currentUser?->id,
                    'issuer_name' => $adminName,
                ],
                customRisk: 'verdacht',
                customReason: "Officiële waarschuwing: {$validated['reason']}"
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log user_warning audit record: '.$e->getMessage());
        }

        return back()->with('success', "Officiële waarschuwing succesvol uitgedeeld aan {$targetName}. De melding verschijnt direct bij de speler.");
    }

    /**
     * Delete / revoke a warning.
     */
    public function deleteWarning(UserWarning $warning): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        abort_unless($currentUser?->canModerate(), 403, 'Toegang geweigerd.');

        if (! $currentUser->isAdmin() && $warning->issuer_id !== $currentUser->id) {
            return back()->with('error', 'Je kunt alleen waarschuwingen intrekken die je zelf hebt uitgedeeld.');
        }

        $warningId = $warning->id;
        $warningUser = $warning->user;
        $warningReason = $warning->reason;

        $adminName = $currentUser?->name ?? 'Staff';

        try {
            AuditLogService::log(
                action: 'warning_deleted',
                category: 'moderation',
                description: "Waarschuwing #{$warningId} voor ".($warningUser?->name ?? 'onbekend')." ingetrokken door {$adminName}",
                character: $warningUser?->character,
                user: $warningUser,
                details: [
                    'warning_id' => $warningId,
                    'original_reason' => $warningReason,
                    'deleted_by_id' => $currentUser?->id,
                    'deleted_by_name' => $adminName,
                ],
                customRisk: 'verdacht',
                customReason: 'Waarschuwing ingetrokken door staff'
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log warning_deleted audit record: '.$e->getMessage());
        }

        return back()->with('success', 'Waarschuwing is verwijderd uit het dossier.');
    }

    /**
     * Mark a user report as resolved.
     */
    public function resolveReport(Request $request, Report $report): RedirectResponse
    {
        abort_unless(Auth::user()?->canModerate(), 403, 'Toegang geweigerd.');

        DB::transaction(function () use ($request, $report): void {
            $report->update([
                'status' => 'resolved',
                'handled_by' => Auth::id(),
                'handled_at' => now(),
                'mod_notes' => $request->input('notes'),
            ]);
        });

        try {
            AuditLogService::log(
                action: 'report_resolved',
                category: 'moderation',
                description: "Melding #{$report->id} ({$report->reason}) opgelost door ".(Auth::user()?->name ?? 'Staff'),
                character: Auth::user()?->character,
                user: Auth::user(),
                details: [
                    'report_id' => $report->id,
                    'reporter_character_id' => $report->reporter_id,
                    'reason' => $report->reason,
                    'reported_name' => $report->reported_name,
                    'notes' => $request->input('notes'),
                    'handled_by_id' => Auth::id(),
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log report_resolved audit record: '.$e->getMessage());
        }

        return back()->with('success', 'Melding gemarkeerd als opgelost / afgehandeld.');
    }

    /**
     * Dismiss a user report as invalid or rejected.
     */
    public function dismissReport(Request $request, Report $report): RedirectResponse
    {
        abort_unless(Auth::user()?->canModerate(), 403, 'Toegang geweigerd.');

        DB::transaction(function () use ($request, $report): void {
            $report->update([
                'status' => 'dismissed',
                'handled_by' => Auth::id(),
                'handled_at' => now(),
                'mod_notes' => $request->input('notes', 'Verworpen door moderatie.'),
            ]);
        });

        try {
            AuditLogService::log(
                action: 'report_dismissed',
                category: 'moderation',
                description: "Melding #{$report->id} ({$report->reason}) verworpen door ".(Auth::user()?->name ?? 'Staff'),
                character: Auth::user()?->character,
                user: Auth::user(),
                details: [
                    'report_id' => $report->id,
                    'reporter_character_id' => $report->reporter_id,
                    'reason' => $report->reason,
                    'reported_name' => $report->reported_name,
                    'notes' => $request->input('notes'),
                    'handled_by_id' => Auth::id(),
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log report_dismissed audit record: '.$e->getMessage());
        }

        return back()->with('info', 'Melding is verworpen en gearchiveerd.');
    }

    /**
     * Delete a report completely (restricted to administrators).
     */
    public function deleteReport(Report $report): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        abort_unless($currentUser?->isAdmin(), 403, 'Alleen beheerders kunnen meldingen definitief verwijderen.');

        $reportId = $report->id;
        $reportedName = $report->reported_name;
        $reason = $report->reason;

        DB::transaction(fn () => $report->delete());

        $adminName = $currentUser?->name ?? 'Staff';

        try {
            AuditLogService::log(
                action: 'report_deleted',
                category: 'moderation',
                description: "Melding #{$reportId} ({$reason}) definitief verwijderd door {$adminName}",
                character: $currentUser?->character,
                user: $currentUser,
                details: [
                    'report_id' => $reportId,
                    'reported_name' => $reportedName,
                    'reason' => $reason,
                    'deleted_by_id' => $currentUser?->id,
                    'deleted_by_name' => $adminName,
                ],
                customRisk: 'verdacht',
                customReason: 'Melding definitief verwijderd uit het dossier door beheerder'
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log report_deleted audit record: '.$e->getMessage());
        }

        return back()->with('success', 'Melding definitief verwijderd.');
    }
}
