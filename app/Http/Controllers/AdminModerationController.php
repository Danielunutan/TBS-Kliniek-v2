<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Models\UserWarning;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminModerationController extends Controller
{
    /**
     * Show moderation overview with active bans, recent warnings, and incoming reports.
     */
    public function index(Request $request): View
    {
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

        $activeBans = $activeBansQuery->paginate(10, ['*'], 'bans_page')->withQueryString();
        $recentWarnings = $warningsQuery->paginate(15, ['*'], 'warnings_page')->withQueryString();
        $reports = $reportsQuery->paginate(15, ['*'], 'reports_page')->withQueryString();

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
        /** @var User $currentUser */
        $currentUser = Auth::user();

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

        $user->ban($validated['reason'], $until, $currentUser);

        $targetName = $user->character?->name ?? $user->name;
        $durationLabel = $until ? 'tot '.$until->locale('nl')->isoFormat('D MMMM [om] HH:mm') : 'permanent';

        return back()->with('success', "Patiënt {$targetName} is succesvol verbannen ({$durationLabel}).");
    }

    /**
     * Unban a user and restore access.
     */
    public function unbanUser(Request $request, User $user): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (($user->isOwner() || $user->role === 'admin') && ! $currentUser->isOwner()) {
            return back()->with('error', 'Alleen de eigenaar kan de verbanning van een Admin of Eigenaar opheffen.');
        }

        if (! $user->is_banned) {
            return back()->with('info', 'Deze gebruiker is momenteel niet verbannen.');
        }

        $user->unban($currentUser);

        $targetName = $user->character?->name ?? $user->name;

        return back()->with('success', "De verbanning van {$targetName} is opgeheven. De patiënt heeft weer toegang.");
    }

    /**
     * Issue an official warning to a user.
     */
    public function warnUser(Request $request, User $user): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Je kunt jezelf geen waarschuwing geven.');
        }

        if (($user->isOwner() || $user->role === 'admin') && ! $currentUser->isOwner()) {
            return back()->with('error', 'Je kunt geen officiële waarschuwing uitdelen aan een Admin of de Eigenaar.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:3|max:1000',
        ]);

        $user->warn($validated['reason'], $currentUser);

        $targetName = $user->character?->name ?? $user->name;

        return back()->with('success', "Officiële waarschuwing succesvol uitgedeeld aan {$targetName}. De melding verschijnt direct bij de speler.");
    }

    /**
     * Delete / revoke a warning.
     */
    public function deleteWarning(UserWarning $warning): RedirectResponse
    {
        $warning->delete();

        return back()->with('success', 'Waarschuwing is verwijderd uit het dossier.');
    }

    /**
     * Mark a user report as resolved.
     */
    public function resolveReport(Request $request, Report $report): RedirectResponse
    {
        $report->update([
            'status' => 'resolved',
            'handled_by' => Auth::id(),
            'handled_at' => now(),
            'mod_notes' => $request->input('notes'),
        ]);

        return back()->with('success', 'Melding gemarkeerd als opgelost / afgehandeld.');
    }

    /**
     * Dismiss a user report as invalid or rejected.
     */
    public function dismissReport(Request $request, Report $report): RedirectResponse
    {
        $report->update([
            'status' => 'dismissed',
            'handled_by' => Auth::id(),
            'handled_at' => now(),
            'mod_notes' => $request->input('notes', 'Verworpen door moderatie.'),
        ]);

        return back()->with('info', 'Melding is verworpen en gearchiveerd.');
    }

    /**
     * Delete a report completely.
     */
    public function deleteReport(Report $report): RedirectResponse
    {
        $report->delete();

        return back()->with('success', 'Melding definitief verwijderd.');
    }
}
