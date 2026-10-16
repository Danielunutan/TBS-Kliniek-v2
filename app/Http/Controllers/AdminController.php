<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameSetting;
use App\Models\Gang;
use App\Models\Referral;
use App\Models\User;
use App\Models\VaultCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Staff overview dashboard.
     */
    public function dashboard(): View
    {
        $totalUsers = User::count();
        $totalCharacters = Character::count();
        $totalVips = Character::where('is_vip', true)->count();
        $totalGangs = Gang::count();
        $totalCash = Character::sum('cash') + Character::sum('bank');
        $inmatesInPrison = Character::where('is_in_prison', true)->count();

        $recentPlayers = Character::with(['user', 'gang'])
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalCharacters',
            'totalVips',
            'totalGangs',
            'totalCash',
            'inmatesInPrison',
            'recentPlayers'
        ));
    }

    /**
     * List and search players.
     */
    public function players(Request $request): View
    {
        $query = Character::with(['user', 'gang']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('filter')) {
            if ($request->input('filter') === 'vip') {
                $query->where('is_vip', true);
            } elseif ($request->input('filter') === 'prison') {
                $query->where('is_in_prison', true);
            } elseif ($request->input('filter') === 'staff') {
                $query->whereHas('user', function ($uq) {
                    $uq->whereIn('role', ['owner', 'admin', 'moderator'])
                        ->orWhere('is_staff', true);
                });
            }
        }

        $players = $query->orderByDesc('respect')->paginate(15)->withQueryString();

        return view('admin.players', compact('players'));
    }

    /**
     * Update player profile, VIP status, currency, role, or jail state.
     */
    public function updatePlayer(Request $request, Character $character): RedirectResponse
    {
        $validated = $request->validate([
            'username' => 'nullable|string|min:2|max:50',
            'character_name' => 'nullable|string|min:2|max:50|unique:characters,name,'.$character->id,
            'reset_avatar' => 'nullable|boolean',
            'cash' => 'required|numeric|min:0',
            'bank' => 'required|numeric|min:0',
            'stars' => 'required|numeric|min:0',
            'respect' => 'required|numeric|min:0',
            'vip_points' => 'required|numeric|min:0',
            'is_vip' => 'nullable|boolean',
            'vip_days' => 'nullable|integer|min:0',
            'release_prison' => 'nullable|boolean',
            'health' => 'required|integer|between:0,100',
            'energy' => 'required|integer|between:0,1000',
            'role' => 'nullable|string|in:user,moderator,admin,owner',
        ]);

        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Moderatie: Update login gebruikersnaam (User name)
        if ($request->filled('username') && $character->user) {
            $character->user->name = trim((string) $validated['username']);
            $character->user->save();
        }

        // Moderatie: Update weergavenaam (Character name)
        if ($request->filled('character_name')) {
            $character->name = trim((string) $validated['character_name']);
        }

        // Moderatie: Reset profielfoto naar standaard default avatar
        if ($request->boolean('reset_avatar')) {
            $character->avatar_custom = null;
            $character->avatar = 'assets/characters/avatar_male_1.jpg';
        }

        // Update user platform role if provided
        if ($request->filled('role') && $character->user) {
            $newRole = $validated['role'];
            $targetUser = $character->user;

            // Only owner can assign owner role or change an existing owner's role
            if ($newRole === 'owner' && ! $currentUser->isOwner()) {
                return back()->with('error', 'Alleen de eigenaar kan de Eigenaar-rol toekennen.');
            }

            if ($targetUser->isOwner() && ! $currentUser->isOwner()) {
                return back()->with('error', 'Je hebt geen bevoegdheid om de rol van een Eigenaar te wijzigen.');
            }

            $targetUser->role = $newRole;
            $targetUser->is_staff = in_array($newRole, ['owner', 'admin', 'moderator'], true);
            $targetUser->save();
        }

        $character->cash = $validated['cash'];
        $character->bank = $validated['bank'];
        $character->stars = $validated['stars'];
        $character->respect = $validated['respect'];
        $character->vip_points = $validated['vip_points'];
        $character->health = $validated['health'];
        $character->energy = $validated['energy'];

        // VIP status toggle
        $isVip = $request->boolean('is_vip');
        $character->is_vip = $isVip;

        if ($isVip && ! empty($validated['vip_days']) && $validated['vip_days'] > 0) {
            $character->vip_expires_at = now()->addDays((int) $validated['vip_days']);
        } elseif (! $isVip) {
            $character->vip_expires_at = null;
        }

        // Release from prison if requested
        if ($request->boolean('release_prison')) {
            $character->is_in_prison = false;
            $character->prison_until = null;
            $character->prison_reason = null;
        }

        $character->save();

        return back()->with('success', "Speler {$character->name} is succesvol bijgewerkt!");
    }

    /**
     * Manage gameplay settings & win rates.
     */
    public function gameplay(): View
    {
        $settings = [
            'win_rate_casino' => GameSetting::get('win_rate_casino', 45),
            'win_rate_theft' => GameSetting::get('win_rate_theft', 60),
            'win_rate_hustle' => GameSetting::get('win_rate_hustle', 65),
            'win_rate_pickpocket' => GameSetting::get('win_rate_pickpocket', 50),
            'win_rate_lockers' => GameSetting::get('win_rate_lockers', 40),
            'attack_energy_cost' => GameSetting::get('attack_energy_cost', 20),
            'daily_attacks_limit' => GameSetting::get('daily_attacks_limit', 100),
            'vip_hourly_points' => GameSetting::get('vip_hourly_points', 5),
        ];

        return view('admin.gameplay', compact('settings'));
    }

    /**
     * Save gameplay settings.
     */
    public function updateGameplay(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'win_rate_casino' => 'required|integer|between:1,100',
            'win_rate_theft' => 'required|integer|between:1,100',
            'win_rate_hustle' => 'required|integer|between:1,100',
            'win_rate_pickpocket' => 'required|integer|between:1,100',
            'win_rate_lockers' => 'required|integer|between:1,100',
            'attack_energy_cost' => 'required|integer|between:1,100',
            'daily_attacks_limit' => 'required|integer|between:10,500',
            'vip_hourly_points' => 'required|integer|between:1,50',
        ]);

        foreach ($validated as $key => $value) {
            GameSetting::set($key, $value, "Beheerd via Admin Paneel ({$key})");
        }

        return back()->with('success', 'Gameplay winkansen en instellingen succesvol opgeslagen!');
    }

    /**
     * Manage chest / case contents & loot chances.
     */
    public function chests(): View
    {
        $settings = [
            'case_xxl_price_cash' => GameSetting::get('case_xxl_price_cash', 50000),
            'case_xxl_price_stars' => GameSetting::get('case_xxl_price_stars', 100),
            'case_xxl_min_cash' => GameSetting::get('case_xxl_min_cash', 25000),
            'case_xxl_max_cash' => GameSetting::get('case_xxl_max_cash', 150000),
            'case_xxl_stars_reward' => GameSetting::get('case_xxl_stars_reward', 25),
            'bag_sell_reward' => GameSetting::get('bag_sell_reward', 2500),
            'bag_open_min_cash' => GameSetting::get('bag_open_min_cash', 1250),
            'bag_open_max_cash' => GameSetting::get('bag_open_max_cash', 3750),
        ];

        return view('admin.chests', compact('settings'));
    }

    /**
     * Save chest settings.
     */
    public function updateChests(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'case_xxl_price_cash' => 'required|numeric|min:0',
            'case_xxl_price_stars' => 'required|numeric|min:0',
            'case_xxl_min_cash' => 'required|numeric|min:0',
            'case_xxl_max_cash' => 'required|numeric|min:0',
            'case_xxl_stars_reward' => 'required|integer|min:0',
            'bag_sell_reward' => 'required|numeric|min:0',
            'bag_open_min_cash' => 'required|numeric|min:0',
            'bag_open_max_cash' => 'required|numeric|min:0',
        ]);

        foreach ($validated as $key => $value) {
            GameSetting::set($key, $value, "Beheerd via Admin Paneel ({$key})");
        }

        return back()->with('success', 'Kisten, koffers en loot-inhoud succesvol bijgewerkt!');
    }

    /**
     * Manage safe (kluis) contents and codes.
     */
    public function vault(): View
    {
        $settings = [
            'vault_code' => GameSetting::get('vault_code', '7419'),
            'vault_reward_cash' => GameSetting::get('vault_reward_cash', 500000),
            'vault_reward_tokens' => GameSetting::get('vault_reward_tokens', 5),
            'vault_reward_respect' => GameSetting::get('vault_reward_respect', 100),
            'vault_hint_cost' => GameSetting::get('vault_hint_cost', 5000),
            'vault_jammer_cost' => GameSetting::get('vault_jammer_cost', 15000),
        ];

        $recentCodes = VaultCode::with('character')
            ->latest()
            ->take(15)
            ->get();

        return view('admin.vault', compact('settings', 'recentCodes'));
    }

    /**
     * Save vault settings.
     */
    public function updateVault(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vault_code' => 'required|string|min:2|max:10',
            'vault_reward_cash' => 'required|numeric|min:0',
            'vault_reward_tokens' => 'required|integer|min:0',
            'vault_reward_respect' => 'required|integer|min:0',
            'vault_hint_cost' => 'required|numeric|min:0',
            'vault_jammer_cost' => 'required|numeric|min:0',
        ]);

        foreach ($validated as $key => $value) {
            GameSetting::set($key, $value, "Beheerd via Admin Paneel ({$key})");
        }

        return back()->with('success', 'Kluiscode en kluisinhoud succesvol opgeslagen!');
    }

    /**
     * Generate new random 4-digit vault code.
     */
    public function randomizeVaultCode(): RedirectResponse
    {
        $newCode = (string) rand(1000, 9999);
        GameSetting::set('vault_code', $newCode, 'Automatisch gegenereerde kluiscode');

        return back()->with('success', "Nieuwe geheime kluiscode gegenereerd: {$newCode}");
    }

    /**
     * View referral system log and rewards.
     */
    public function referrals(Request $request): View
    {
        $query = Referral::with(['referrer.user', 'referred.user']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('referrer', function ($rq) use ($search) {
                    $rq->where('name', 'like', "%{$search}%");
                })->orWhereHas('referred', function ($rq) use ($search) {
                    $rq->where('name', 'like', "%{$search}%");
                })->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $referrals = $query->latest()->paginate(30)->withQueryString();

        $totalReferrals = Referral::count();
        $rewardedCount = Referral::where('rewarded', true)->count();
        $totalVipMonthsGranted = (int) floor($rewardedCount / 3);
        $unrewardedCount = Referral::where('rewarded', false)->count();

        $topReferrers = Referral::select('referrer_id', DB::raw('count(*) as count'))
            ->groupBy('referrer_id')
            ->orderByDesc('count')
            ->take(5)
            ->with('referrer')
            ->get();

        return view('admin.referrals', compact(
            'referrals',
            'totalReferrals',
            'rewardedCount',
            'totalVipMonthsGranted',
            'unrewardedCount',
            'topReferrers'
        ));
    }

    /**
     * Run database migrations safely from admin panel (owner/admin only).
     */
    public function runMigrations(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if ($user->role !== 'owner' && $user->role !== 'admin') {
            return back()->with('error', 'Geen bevoegdheid.');
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();

            return back()->with('success', 'Database migraties succesvol uitgevoerd: '.trim($output));
        } catch (\Throwable $e) {
            return back()->with('error', 'Migratie fout: '.$e->getMessage());
        }
    }
}
