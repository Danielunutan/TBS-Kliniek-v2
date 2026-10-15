<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\User;
use App\Services\EnergyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MembersController extends Controller
{
    public function __construct(
        protected EnergyService $energyService,
    ) {}

    /**
     * Show general member directory and online players list with realtime search.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $initialFilter = $request->query('filter', 'all');
        $initialSearch = (string) $request->query('search', '');

        $allCharacters = Character::with('gang')
            ->orderByDesc('last_active_at')
            ->take(500)
            ->get();

        $totalCount = Character::count();
        $onlineCount = Character::where('last_active_at', '>=', now()->subMinutes(5))->count();
        $gangCount = Character::whereNotNull('gang_id')->count();
        $soloCount = Character::whereNull('gang_id')->count();
        $prisonCount = Character::where('prison_until', '>', now())->count();
        $vipCount = Character::where('is_vip', true)->count();

        $membersData = $allCharacters->map(function (Character $c) use ($character) {
            $isOnline = $c->isOnline();
            $isInPrison = $c->isImprisoned();

            return [
                'id' => $c->id,
                'name' => $c->name,
                'avatar_url' => $c->avatar_url,
                'level' => (int) $c->level,
                'respect' => (float) $c->respect,
                'respect_formatted' => number_format($c->respect, 0, ',', '.'),
                'title' => $c->title ?? 'Patiënt',
                'is_vip' => (bool) $c->is_vip,
                'is_self' => $c->id === $character->id,
                'is_online' => $isOnline,
                'online_label' => $c->online_status_label,
                'is_in_prison' => $isInPrison,
                'prison_label' => $isInPrison ? 'In Isoleercel' : null,
                'current_location' => $c->current_location ?? 'Paviljoen 1',
                'gang_id' => $c->gang_id,
                'gang_name' => $c->gang?->name,
                'gang_tag' => $c->gang?->tag,
                'gang_role' => $c->gang_role,
                'role_label' => $c->gang_role === 'leader' ? 'Bendeleider' : ($c->gang_role === 'co_leader' ? 'Co-Leider' : ($c->gang_id ? 'Bendelid' : 'Geen bende')),
                'last_active_human' => $c->last_active_at ? $c->last_active_at->diffForHumans() : 'Nooit actief',
                'last_active_timestamp' => $c->last_active_at ? $c->last_active_at->timestamp : 0,
                'profile_url' => route('profile.show', $c->name),
                'message_url' => route('messages.index', ['recipient' => $c->name]),
            ];
        })->values();

        return view('game.members.index', compact(
            'character',
            'membersData',
            'totalCount',
            'onlineCount',
            'gangCount',
            'soloCount',
            'prisonCount',
            'vipCount',
            'initialFilter',
            'initialSearch'
        ));
    }
}
