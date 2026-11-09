<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\DailyBattleScore;
use App\Models\Gang;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MurderStatsController extends Controller
{
    public function index(): View
    {
        $character = Auth::user()->character;
        $today = now()->format('Y-m-d');
        $sevenDaysAgo = now()->subDays(7)->format('Y-m-d');

        // 1. TODAY: Top Patients by violence (kills or slay score)
        $todayScores = DailyBattleScore::with('character.gang')
            ->where('battle_date', $today)
            ->where(function ($q) {
                $q->where('score_kills', '>', 0)->orWhere('score_slay', '>', 0);
            })
            ->whereHas('character', function ($q) {
                $q->whereNotIn('name', Character::EXCLUDED_SYSTEM_NAMES);
            })
            ->orderByDesc('score_kills')
            ->orderByDesc('score_slay')
            ->take(5)
            ->get();

        if ($todayScores->isEmpty()) {
            // Fallback to characters with most fights won
            $topPatientsToday = Character::with('gang')
                ->whereNotIn('name', Character::EXCLUDED_SYSTEM_NAMES)
                ->where('fights_won', '>', 0)
                ->orderByDesc('fights_won')
                ->take(5)
                ->get()
                ->map(fn ($char) => (object) [
                    'name' => $char->name,
                    'gang' => $char->gang?->name,
                    'kills' => (int) $char->fights_won,
                    'is_online' => $char->isOnline(),
                ]);
        } else {
            $topPatientsToday = $todayScores->map(fn ($score) => (object) [
                'name' => $score->character ? $score->character->name : 'Patiënt',
                'gang' => $score->character?->gang?->name,
                'kills' => max((int) $score->score_kills, (int) round((int) $score->score_slay / 2)),
                'is_online' => $score->character ? $score->character->isOnline() : false,
            ]);
        }

        // Top Online Patients Today
        $topOnlinePatientsToday = $topPatientsToday->filter(fn ($p) => $p->is_online)->values();

        // 2. TODAY: Top Gangs by violence
        $topGangsToday = Gang::with('members')
            ->get()
            ->map(function ($gang) {
                $kills = (int) $gang->members->sum('fights_won');
                $hasOnline = $gang->members->contains(fn ($m) => $m->isOnline());

                return (object) [
                    'name' => $gang->name,
                    'kills' => $kills,
                    'is_online' => $hasOnline,
                ];
            })
            ->filter(fn ($g) => $g->kills > 0)
            ->sortByDesc('kills')
            ->take(5)
            ->values();

        $topOnlineGangsToday = $topGangsToday->filter(fn ($g) => $g->is_online)->values();

        // 3. 7 DAYS: Top Patients & Gangs
        $weekScores = DailyBattleScore::with('character.gang')
            ->where('battle_date', '>=', $sevenDaysAgo)
            ->whereHas('character', function ($q) {
                $q->whereNotIn('name', Character::EXCLUDED_SYSTEM_NAMES);
            })
            ->selectRaw('character_id, SUM(score_kills) as total_kills, SUM(score_slay) as total_slay')
            ->groupBy('character_id')
            ->orderByDesc('total_kills')
            ->orderByDesc('total_slay')
            ->take(5)
            ->get();

        if ($weekScores->isEmpty()) {
            $topPatientsWeek = $topPatientsToday;
            $topGangsWeek = $topGangsToday;
        } else {
            $topPatientsWeek = $weekScores->map(function ($score) {
                $char = $score->character;

                return (object) [
                    'name' => $char ? $char->name : 'Patiënt',
                    'gang' => $char?->gang?->name,
                    'kills' => max((int) $score->total_kills, (int) round((int) $score->total_slay / 2)),
                    'is_online' => $char ? $char->isOnline() : false,
                ];
            });
            $topGangsWeek = DailyBattleScore::with('character.gang.members')
                ->where('battle_date', '>=', $sevenDaysAgo)
                ->where(function ($q) {
                    $q->where('score_kills', '>', 0)->orWhere('score_slay', '>', 0);
                })
                ->get()
                ->filter(fn ($score) => $score->character?->gang !== null)
                ->groupBy(fn ($score) => $score->character->gang_id)
                ->map(function ($scores) {
                    $gang = $scores->first()->character->gang;
                    $kills = (int) $scores->sum(fn ($s) => max((int) $s->score_kills, (int) round((int) $s->score_slay / 2)));
                    $hasOnline = $gang->members->contains(fn ($m) => $m->isOnline());

                    return (object) [
                        'name' => $gang->name,
                        'kills' => $kills,
                        'is_online' => $hasOnline,
                    ];
                })
                ->filter(fn ($g) => $g->kills > 0)
                ->sortByDesc('kills')
                ->take(5)
                ->values();

            if ($topGangsWeek->isEmpty()) {
                $topGangsWeek = $topGangsToday;
            }
        }

        $topOnlinePatientsWeek = $topPatientsWeek->filter(fn ($p) => $p->is_online)->values();
        $topOnlineGangsWeek = $topGangsWeek->filter(fn ($g) => $g->is_online)->values();

        return view('game.murder_stats.index', compact(
            'character',
            'topPatientsToday',
            'topOnlinePatientsToday',
            'topGangsToday',
            'topOnlineGangsToday',
            'topPatientsWeek',
            'topOnlinePatientsWeek',
            'topGangsWeek',
            'topOnlineGangsWeek'
        ));
    }
}
