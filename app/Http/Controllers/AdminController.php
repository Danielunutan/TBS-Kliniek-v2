<?php

namespace App\Http\Controllers;

use App\Models\BuyMeACoffeeOrder;
use App\Models\Character;
use App\Models\GameNotification;
use App\Models\GameSetting;
use App\Models\Gang;
use App\Models\Referral;
use App\Models\User;
use App\Models\VaultCode;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        $exceededStatsCount = $this->countExceededCharacters();

        $migrationStatus = $this->getMigrationStatusSummary();

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
            'exceededStatsCount',
            'migrationStatus',
            'recentPlayers'
        ));
    }

    /**
     * List and search players.
     */
    public function players(Request $request): View
    {
        $query = Character::with(['user.warnings', 'user.bannedBy', 'gang']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('email', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
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
            } elseif ($request->input('filter') === 'banned') {
                $query->whereHas('user', function ($uq) {
                    $uq->where('is_banned', true);
                });
            } elseif ($request->input('filter') === 'warned') {
                $query->whereHas('user.warnings');
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
            'vip_tier' => 'nullable|integer|between:0,4',
            'vip_action' => 'nullable|string|in:no_change,add_days,set_days,lifetime,revoke',
            'custom_vip_days' => 'nullable|integer|min:0|max:3650',
            'has_custom_name_color' => 'nullable|boolean',
            'has_sparkle_effect' => 'nullable|boolean',
            'custom_name_color' => 'nullable|string|max:10',
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
            $newRole = (string) $validated['role'];
            $targetUser = $character->user;

            // Only the owner can grant or remove staff power above moderator, so a moderator cannot promote anyone (or himself) to admin.
            if (in_array($newRole, ['owner', 'admin'], true) && ! $currentUser->isOwner()) {
                return back()->with('error', 'Alleen de eigenaar kan de Admin- of Eigenaar-rol toekennen.');
            }

            if (($targetUser->isOwner() || $targetUser->role === 'admin') && ! $currentUser->isOwner()) {
                return back()->with('error', 'Je hebt geen bevoegdheid om de rol van een Admin of Eigenaar te wijzigen.');
            }

            $oldRole = $targetUser->role;
            $targetUser->role = $newRole;
            $targetUser->is_staff = in_array($newRole, ['owner', 'admin', 'moderator'], true);
            $targetUser->save();

            if ($oldRole !== $newRole) {
                GameNotification::create([
                    'character_id' => $character->id,
                    'type' => 'role_change',
                    'icon' => match ($newRole) {
                        'owner' => 'fa-crown',
                        'admin' => 'fa-user-shield',
                        'moderator' => 'fa-gavel',
                        default => 'fa-user',
                    },
                    'title' => 'Rol Status Aangepast',
                    'message' => 'Je accountrol is aangepast naar '.ucfirst($newRole).' door de directie.',
                    'is_read' => false,
                ]);
            }
        }

        $character->cash = $validated['cash'];
        $character->bank = $validated['bank'];
        $character->stars = $validated['stars'];
        $character->respect = $validated['respect'];
        $character->vip_points = $validated['vip_points'];
        $character->health = $validated['health'];
        $character->energy = $validated['energy'];

        // VIP beheer & handmatige toekenning
        $vipAction = $request->input('vip_action', 'no_change');
        $vipTier = (int) $request->input('vip_tier', ($character->getSupporterTier() ?: 1));
        $perks = $character->perks ?? [];
        $wasVip = $character->isVip();

        if ($vipAction === 'revoke' || (! $request->boolean('is_vip') && in_array($vipAction, ['no_change', 'revoke'], true))) {
            $character->is_vip = false;
            $character->vip_expires_at = null;
            $perks['supporter_tier'] = 0;
            $perks['has_custom_name_color'] = false;
            $perks['has_sparkle_effect'] = false;
        } else {
            $character->is_vip = true;
            $currentExpiry = ($character->vip_expires_at && $character->vip_expires_at->isFuture())
                ? $character->vip_expires_at
                : now();

            $daysToAdd = (int) ($request->input('custom_vip_days') ?: ($request->input('vip_days') ?: 30));

            if ($vipAction === 'lifetime' || $vipTier === 4) {
                $character->vip_expires_at = now()->addYears(99);
                if ($vipTier < 4) {
                    $vipTier = 4;
                }
            } elseif ($vipAction === 'add_days') {
                $character->vip_expires_at = $currentExpiry->copy()->addDays($daysToAdd);
            } elseif ($vipAction === 'set_days') {
                $character->vip_expires_at = now()->addDays($daysToAdd);
            } elseif (! $character->vip_expires_at || $character->vip_expires_at->isPast()) {
                $character->vip_expires_at = now()->addDays($daysToAdd > 0 ? $daysToAdd : 30);
            }

            $perks['supporter_tier'] = max(1, $vipTier);
            $perks['hourly_cash_bonus'] = match ($perks['supporter_tier']) {
                4 => 50000,
                3 => 20000,
                2 => 7500,
                default => 2500,
            };

            // Custom perks toggles
            if ($request->has('has_custom_name_color')) {
                $perks['has_custom_name_color'] = $request->boolean('has_custom_name_color');
            } elseif ($vipTier >= 2) {
                $perks['has_custom_name_color'] = true;
            }

            if ($request->has('has_sparkle_effect')) {
                $perks['has_sparkle_effect'] = $request->boolean('has_sparkle_effect');
                $perks['sparkle_enabled'] = $request->boolean('has_sparkle_effect');
            } elseif ($vipTier >= 3) {
                $perks['has_sparkle_effect'] = true;
                $perks['sparkle_enabled'] = true;
            }

            if ($request->filled('custom_name_color')) {
                $perks['custom_name_color'] = (string) $request->input('custom_name_color');
            }

            if (! $wasVip || $vipAction !== 'no_change') {
                $tierTitle = match ($perks['supporter_tier']) {
                    4 => 'Lifetime V.I.P.',
                    3 => 'Kliniek Baas V.I.P. (Tier 3)',
                    2 => 'Gangster Elite V.I.P. (Tier 2)',
                    default => 'TBS V.I.P. Lid (Tier 1)',
                };
                GameNotification::create([
                    'character_id' => $character->id,
                    'type' => 'vip_granted',
                    'icon' => 'fa-crown',
                    'title' => 'VIP Status Toegekend!',
                    'message' => "Gefeliciteerd! De administratie heeft je {$tierTitle} status toegekend.",
                    'is_read' => false,
                ]);
            }
        }

        $character->perks = $perks;

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
     * Run database migrations and data normalization safely from the admin panel.
     */
    public function runMigrations(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        // Migrations change the schema of the live database, so moderators must not run them.
        if (! $user->isAdmin()) {
            return back()->with('error', 'Geen bevoegdheid.');
        }

        try {
            $exceededBefore = $this->countExceededCharacters();
            $pendingBefore = $this->getMigrationStatusSummary()['pending_count'];

            Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());
            $summary = $this->executeNormalizationMigration($exceededBefore);

            AuditLogService::log(
                action: 'admin_run_migrations',
                category: 'system',
                description: "Database migraties ({$pendingBefore} openstaand) en spelbalanscontrole uitgevoerd vanuit /admin",
                character: $user->character,
                user: $user,
                details: ['pending_before' => $pendingBefore, 'exceeded_before' => $exceededBefore]
            );

            return back()->with('success', "Database migraties en balancering succesvol uitgevoerd ({$summary}). ".($output !== '' ? "Output: {$output}" : ''));
        } catch (\Throwable $e) {
            return back()->with('error', 'Migratie fout: '.$e->getMessage());
        }
    }

    /**
     * Run the comprehensive stat, item, inventory, and timer normalization migration on demand from /admin.
     */
    public function runNormalization(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        // Migrations change the schema of the live database, so moderators must not run them.
        if (! $user->isAdmin()) {
            return back()->with('error', 'Geen bevoegdheid.');
        }

        try {
            $exceededBefore = $this->countExceededCharacters();
            $pendingBefore = $this->getMigrationStatusSummary()['pending_count'];

            Artisan::call('migrate', ['--force' => true]);
            $summary = $this->executeNormalizationMigration($exceededBefore);

            AuditLogService::log(
                action: 'admin_run_normalization',
                category: 'system',
                description: "Systeem- en spelbalans herstelmigratie uitgevoerd vanuit /admin ({$summary})",
                character: $user->character,
                user: $user,
                details: ['pending_before' => $pendingBefore, 'exceeded_before' => $exceededBefore]
            );

            return back()->with('success', "Systeem- en spelbalanscontrole voltooid! {$summary}");
        } catch (\Throwable $e) {
            return back()->with('error', 'Fout bij uitvoeren van herstelmigratie: '.$e->getMessage());
        }
    }

    /**
     * Count characters that currently have any stat above their level ceiling.
     */
    private function countExceededCharacters(): int
    {
        if (! Schema::hasTable('characters')) {
            return 0;
        }

        return Character::query()
            ->whereRaw('strength > (100 + COALESCE(level, 1) * 50)')
            ->orWhereRaw('defense > (100 + COALESCE(level, 1) * 50)')
            ->orWhereRaw('stamina > (100 + COALESCE(level, 1) * 50)')
            ->orWhereRaw('intelligence > (100 + COALESCE(level, 1) * 50)')
            ->orWhereRaw('charisma > (100 + COALESCE(level, 1) * 50)')
            ->orWhereRaw('discipline > (100 + COALESCE(level, 1) * 50)')
            ->orWhere('max_energy', '>', 100)
            ->orWhere('energy', '>', 100)
            ->count();
    }

    /**
     * Inspect database/migrations files against the migrations table.
     *
     * @return array{total: int, executed: int, pending_count: int, pending_files: array<int, string>}
     */
    private function getMigrationStatusSummary(): array
    {
        $files = glob(database_path('migrations/*.php')) ?: [];
        $fileNames = array_map(static fn (string $path): string => pathinfo($path, PATHINFO_FILENAME), $files);
        sort($fileNames);

        $ranMigrations = Schema::hasTable('migrations')
            ? DB::table('migrations')->pluck('migration')->all()
            : [];

        $pendingFiles = array_values(array_diff($fileNames, $ranMigrations));

        return [
            'total' => count($fileNames),
            'executed' => count($fileNames) - count($pendingFiles),
            'pending_count' => count($pendingFiles),
            'pending_files' => $pendingFiles,
        ];
    }

    /**
     * Execute the normalization migration and return a human-readable summary of inspected and repaired records.
     */
    private function executeNormalizationMigration(?int $exceededBefore = null): string
    {
        $exceededCount = $exceededBefore ?? $this->countExceededCharacters();

        $idempotentMigrations = [
            '2026_10_30_220000_align_item_prices_and_categories',
            '2026_10_30_223000_cap_exceeded_character_stats_to_level_max',
            '2026_10_30_224500_normalize_all_exceeded_stats_items_and_timers',
            '2026_10_30_231000_restore_max_energy_to_100',
        ];

        $nextBatch = Schema::hasTable('migrations')
            ? ((int) DB::table('migrations')->max('batch') + 1)
            : 1;

        foreach ($idempotentMigrations as $migrationName) {
            $migrationPath = database_path("migrations/{$migrationName}.php");
            if (is_file($migrationPath)) {
                $migration = require $migrationPath;
                $migration->up();

                if (Schema::hasTable('migrations') && ! DB::table('migrations')->where('migration', $migrationName)->exists()) {
                    DB::table('migrations')->insert([
                        'migration' => $migrationName,
                        'batch' => max(1, $nextBatch),
                    ]);
                }
            }
        }

        $totalCharacters = Schema::hasTable('characters') ? Character::count() : 0;

        return "{$totalCharacters} spelers gecontroleerd, {$exceededCount} spelers met overschreden stats hersteld & omgezet naar XP, winkel/depot prijzen en timers gesynchroniseerd";
    }

    /**
     * View Buy Me a Coffee orders, memberships, and unlinked supporters.
     */
    public function buymeacoffee(Request $request): View
    {
        $query = BuyMeACoffeeOrder::with('character.user');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('supporter_name', 'like', "%{$search}%")
                    ->orWhere('supporter_email', 'like', "%{$search}%")
                    ->orWhere('external_id', 'like', "%{$search}%")
                    ->orWhere('membership_tier', 'like', "%{$search}%")
                    ->orWhereHas('character', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $filter = $request->input('filter', 'all');
        if ($filter === 'unmatched') {
            $query->where('status', 'unmatched');
        } elseif ($filter === 'matched') {
            $query->whereIn('status', ['matched', 'manually_linked']);
        } elseif ($filter === 'memberships') {
            $query->where('is_membership', true);
        } elseif ($filter === 'cancelled') {
            $query->where('status', 'cancelled');
        }

        $orders = $query->latest()->paginate(25)->withQueryString();

        $totalOrders = BuyMeACoffeeOrder::count();
        $unmatchedCount = BuyMeACoffeeOrder::where('status', 'unmatched')->count();
        $activeMembershipsCount = BuyMeACoffeeOrder::where('is_membership', true)->where('status', '!=', 'cancelled')->count();
        $cancelledCount = BuyMeACoffeeOrder::where('status', 'cancelled')->count();

        // Get character list for quick linking
        $allCharacters = Character::select('id', 'name', 'level', 'is_vip')->orderBy('name')->get();

        return view('admin.buymeacoffee', compact(
            'orders',
            'totalOrders',
            'unmatchedCount',
            'activeMembershipsCount',
            'cancelledCount',
            'allCharacters',
            'filter'
        ));
    }

    /**
     * Manually link a Buy Me a Coffee order/membership to a player character.
     */
    public function linkBuyMeACoffee(Request $request, BuyMeACoffeeOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'character_id' => 'required|exists:characters,id',
            'membership_tier' => 'nullable|string',
            'is_membership' => 'nullable|boolean',
            'grant_rewards' => 'nullable|boolean',
        ]);

        $character = Character::findOrFail($validated['character_id']);
        $tierName = $validated['membership_tier'] ?: ($order->membership_tier ?: 'TBS V.I.P. Lid');
        $isMembership = $request->boolean('is_membership') || $order->is_membership || ! empty($validated['membership_tier']);

        $order->character_id = $character->id;
        $order->status = 'manually_linked';
        $order->membership_tier = $tierName;
        $order->is_membership = $isMembership;
        $order->membership_status = $isMembership ? 'active' : null;
        $order->linked_at = now();
        $order->save();

        if ($request->boolean('grant_rewards', true)) {
            $webhookController = app(BuyMeACoffeeWebhookController::class);
            $tierLower = strtolower($tierName);
            $isCoffee = str_contains($tierLower, 'koffie') || str_contains($tierLower, 'bakkie') || (! $isMembership);

            if (! $isCoffee && $isMembership) {
                $webhookController->grantMembershipTier($character, $tierName, $order->payload ?? []);
            } else {
                $coffees = 1;
                if (str_contains($tierName, '5x') || (float) $order->amount >= 15.0) {
                    $coffees = 5;
                } elseif (str_contains($tierName, '3x') || (float) $order->amount >= 9.0) {
                    $coffees = 3;
                }
                $payload = array_merge($order->payload ?? [], [
                    'support_coffees' => $coffees,
                    'quantity' => $coffees,
                    'amount' => max((float) $order->amount, $coffees * 3.0),
                ]);
                $webhookController->processEvent('donation', $payload, $character);
            }
        }

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'bmac_linked',
            'icon' => 'fa-mug-hot',
            'title' => 'Buy Me a Coffee Gekoppeld!',
            'message' => "Je Buy Me a Coffee bijdrage van {$order->formatted_amount} is handmatig door het beheer gekoppeld aan je account. Al je voordelen zijn direct geactiveerd!",
            'is_read' => false,
        ]);

        AuditLogService::log(
            action: 'buymeacoffee_manual_link',
            category: 'vip',
            description: "BuyMeACoffee order #{$order->id} ({$order->supporter_name}) handmatig gekoppeld aan {$character->name} met tier {$tierName}",
            character: $character,
            user: $character->user,
            amount: (int) $order->amount,
            details: ['order_id' => $order->id, 'tier' => $tierName]
        );

        return back()->with('success', "Buy Me a Coffee order succesvol gekoppeld aan {$character->name}!");
    }

    /**
     * Re-grant rewards for a linked Buy Me a Coffee order to ensure player receives items.
     */
    public function regrantBuyMeACoffee(BuyMeACoffeeOrder $order): RedirectResponse
    {
        if (! $order->character) {
            return back()->with('error', 'Koppel eerst een speler aan deze order voordat je beloningen kunt uitkeren!');
        }

        $character = $order->character;
        $webhookController = app(BuyMeACoffeeWebhookController::class);

        $tier = (string) ($order->membership_tier ?? '');
        $tierLower = strtolower($tier);
        $isCoffee = str_contains($tierLower, 'koffie') || str_contains($tierLower, 'bakkie') || (! $order->is_membership);

        if (! $isCoffee && $order->is_membership) {
            $webhookController->grantMembershipTier($character, $tier, $order->payload ?? []);
        } else {
            $coffees = 1;
            if (str_contains($tier, '5x') || (float) $order->amount >= 15.0) {
                $coffees = 5;
            } elseif (str_contains($tier, '3x') || (float) $order->amount >= 9.0) {
                $coffees = 3;
            }
            $payload = array_merge($order->payload ?? [], [
                'support_coffees' => $coffees,
                'quantity' => $coffees,
                'amount' => max((float) $order->amount, $coffees * 3.0),
            ]);
            $webhookController->processEvent('donation', $payload, $character);
        }

        AuditLogService::log(
            action: 'buymeacoffee_regrant',
            category: 'vip',
            description: "BuyMeACoffee order #{$order->id} beloningen ({$order->rewards_summary}) handmatig her-uitgekeerd aan {$character->name}",
            character: $character,
            user: $character->user,
            amount: (int) $order->amount,
            details: ['order_id' => $order->id]
        );

        return back()->with('success', "Beloningen ({$order->rewards_summary}) zijn zojuist succesvol uitgekeerd aan {$character->name}!");
    }

    /**
     * Cancel / expire membership and revoke VIP status.
     */
    public function cancelBuyMeACoffee(BuyMeACoffeeOrder $order): RedirectResponse
    {
        $character = $order->character;

        $order->status = 'cancelled';
        $order->membership_status = 'cancelled';
        $order->cancelled_at = now();
        $order->save();

        if ($character) {
            app(BuyMeACoffeeWebhookController::class)->revokeMembership($character, (string) $order->membership_tier, [
                'reason' => 'cancelled_by_admin',
                'order_id' => $order->id,
            ]);
        }

        return back()->with('success', 'Membership geannuleerd en VIP privileges zijn beëindigd!');
    }

    /**
     * Manually add and link a Buy Me a Coffee transaction.
     */
    public function createBuyMeACoffeeManual(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supporter_name' => 'required|string|max:100',
            'supporter_email' => 'nullable|email|max:150',
            'character_id' => 'required|exists:characters,id',
            'amount' => 'required|numeric|min:0',
            'membership_tier' => 'required|string',
            'is_membership' => 'required|boolean',
            'message' => 'nullable|string|max:500',
        ]);

        $character = Character::findOrFail($validated['character_id']);

        $tier = (string) $validated['membership_tier'];
        $tierLower = strtolower($tier);
        $isMembership = (bool) $validated['is_membership'];
        if (str_contains($tierLower, 'koffie') || str_contains($tierLower, 'bakkie')) {
            $isMembership = false;
        } elseif (str_contains($tierLower, 'vip') || str_contains($tierLower, 'v.i.p') || str_contains($tierLower, 'elite') || str_contains($tierLower, 'baas') || str_contains($tierLower, 'lifetime')) {
            $isMembership = true;
        }

        $coffees = 1;
        if (str_contains($tier, '5x') || (float) $validated['amount'] >= 15.0) {
            $coffees = 5;
        } elseif (str_contains($tier, '3x') || (float) $validated['amount'] >= 9.0) {
            $coffees = 3;
        }

        $order = BuyMeACoffeeOrder::create([
            'event' => $isMembership ? 'membership.manual' : 'donation.manual',
            'external_id' => 'manual_'.uniqid(),
            'supporter_name' => $validated['supporter_name'],
            'supporter_email' => $validated['supporter_email'],
            'amount' => $validated['amount'],
            'currency' => 'EUR',
            'membership_tier' => $validated['membership_tier'],
            'message' => $validated['message'],
            'character_id' => $character->id,
            'status' => 'manually_linked',
            'is_membership' => $isMembership,
            'membership_status' => $isMembership ? 'active' : null,
            'linked_at' => now(),
            'payload' => [
                'manual' => true,
                'created_by' => Auth::id(),
                'support_coffees' => $coffees,
                'quantity' => $coffees,
                'amount' => max((float) $validated['amount'], $coffees * 3.0),
            ],
        ]);

        $webhookController = app(BuyMeACoffeeWebhookController::class);
        if ($isMembership) {
            $webhookController->grantMembershipTier($character, $validated['membership_tier'], ['manual' => true]);
        } else {
            $webhookController->processEvent('donation', [
                'support_coffees' => $coffees,
                'quantity' => $coffees,
                'amount' => max((float) $validated['amount'], $coffees * 3.0),
            ], $character);
        }

        return back()->with('success', "Handmatige Buy Me a Coffee registratie succesvol gekoppeld aan {$character->name}!");
    }
}
