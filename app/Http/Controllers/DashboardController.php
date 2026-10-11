<?php

namespace App\Http\Controllers;

use App\Models\CharacterMission;
use App\Models\GameNotification;
use App\Models\Mission;
use App\Models\NewsArticle;
use App\Models\TheftRecord;
use App\Models\TrainingLog;
use App\Models\User;
use App\Services\AllowanceService;
use App\Services\EnergyService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected EnergyService $energyService,
        protected LevelService $levelService,
        protected AllowanceService $allowanceService,
    ) {}

    /**
     * Show player dashboard.
     */
    public function index(): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character) {
            return redirect()->route('login');
        }

        // Refresh energy/health timestamps
        $this->energyService->refresh($character);

        $allowanceDetails = $this->allowanceService->getAllowanceDetails($character);

        $nextLevelXp = $this->levelService->getXpRequiredForNextLevel($character->level);
        $xpPercentage = min(100, max(0, (int) round(($character->xp / max(1, $nextLevelXp)) * 100)));

        $quickMissions = Mission::orderBy('required_level')->take(3)->get();
        $notifications = $character->notifications()->take(5)->get();
        $recentGuestbook = $character->guestbookEntries()->with('author')->take(3)->get();

        $latestNews = NewsArticle::where('is_published', true)
            ->orderByDesc('is_pinned')
            ->latest()
            ->take(4)
            ->get();

        // Dynamische Recente Activiteiten samenstellen
        $activities = collect();

        // 1. Voltooide missies
        $missions = CharacterMission::where('character_id', $character->id)
            ->where('completed_count', '>', 0)
            ->with('mission')
            ->latest('last_attempted_at')
            ->take(5)
            ->get();

        foreach ($missions as $m) {
            if ($m->mission) {
                $activities->push([
                    'title' => "Je hebt de missie '{$m->mission->title}' voltooid!",
                    'badge' => '+ €'.number_format($m->mission->min_cash_reward, 0, ',', '.')."  ·  + {$m->mission->xp_reward} XP",
                    'badge_color' => 'text-emerald-600',
                    'icon' => 'fa-check',
                    'icon_bg' => 'bg-emerald-100 text-emerald-600',
                    'time' => $m->last_attempted_at ?: $m->created_at,
                ]);
            }
        }

        // 2. Krachttraining & Vechtoefening logs
        $trainings = TrainingLog::where('character_id', $character->id)
            ->latest()
            ->take(5)
            ->get();

        foreach ($trainings as $t) {
            $activities->push([
                'title' => "Je hebt {$t->energy_cost} energie verbruikt bij training van ".ucfirst($t->stat).'.',
                'badge' => "+ {$t->stat_gain} ".ucfirst($t->stat).'  ·  - €'.number_format($t->cash_cost, 0, ',', '.'),
                'badge_color' => 'text-purple-600',
                'icon' => 'fa-dumbbell',
                'icon_bg' => 'bg-purple-100 text-purple-600',
                'time' => $t->created_at,
            ]);
        }

        // 3. Diefstallen en overvallen
        $thefts = TheftRecord::where('thief_id', $character->id)
            ->latest()
            ->take(5)
            ->get();

        foreach ($thefts as $th) {
            $activities->push([
                'title' => $th->log_message ?: ($th->success ? "Buit binnengehaald bij {$th->target_name}!" : 'Overval mislukt.'),
                'badge' => $th->success
                    ? '+ €'.number_format($th->cash_stolen, 0, ',', '.')."  ·  + {$th->xp_gained} XP"
                    : 'Mislukt (0 buit)',
                'badge_color' => $th->success ? 'text-amber-600' : 'text-rose-600',
                'icon' => $th->success ? 'fa-mask' : 'fa-handcuffs',
                'icon_bg' => $th->success ? 'bg-amber-100 text-amber-600' : 'bg-rose-100 text-rose-600',
                'time' => $th->created_at,
            ]);
        }

        // 4. Notificaties en promoties (Level ups)
        $notifs = GameNotification::where('character_id', $character->id)
            ->latest()
            ->take(5)
            ->get();

        foreach ($notifs as $n) {
            $activities->push([
                'title' => $n->title,
                'badge' => Str::limit($n->message, 80),
                'badge_color' => 'text-indigo-600',
                'icon' => $n->icon ?: 'fa-trophy',
                'icon_bg' => 'bg-indigo-100 text-indigo-600',
                'time' => $n->created_at,
            ]);
        }

        // Sorteer op recentste datum en neem de top 5
        $recentActivities = $activities->sortByDesc('time')->take(5)->values();

        return view('game.dashboard', compact(
            'character',
            'nextLevelXp',
            'xpPercentage',
            'quickMissions',
            'notifications',
            'recentGuestbook',
            'allowanceDetails',
            'latestNews',
            'recentActivities'
        ));
    }
}
