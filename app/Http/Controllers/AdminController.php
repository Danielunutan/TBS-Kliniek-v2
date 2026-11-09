<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckMaintenanceMode;
use App\Models\BuyMeACoffeeOrder;
use App\Models\Character;
use App\Models\GameNotification;
use App\Models\GameSetting;
use App\Models\Gang;
use App\Models\Referral;
use App\Models\User;
use App\Models\VaultCode;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Staff overview dashboard.
     */
    public function dashboard(): View
    {
        abort_unless(Auth::user()?->isStaff(), 403, 'Toegang geweigerd. Deze beheeromgeving is uitsluitend toegankelijk voor TBS Kliniek personeel.');

        $totalUsers = User::count();
        $totalCharacters = Character::count();
        $totalVips = Character::where('is_vip', true)->count();
        $totalGangs = Gang::count();
        $totalCash = Character::sum('cash') + Character::sum('bank');
        $inmatesInPrison = Character::where('is_in_prison', true)->count();

        $exceededStatsCount = $this->countExceededCharacters();

        $migrationStatus = $this->getMigrationStatusSummary();
        $isMaintenanceEnabled = CheckMaintenanceMode::isEnabled();
        $maintenanceMeta = CheckMaintenanceMode::getMetadata();

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
            'isMaintenanceEnabled',
            'maintenanceMeta',
            'recentPlayers'
        ));
    }

    /**
     * List and search players.
     */
    public function players(Request $request): View
    {
        abort_unless(Auth::user()?->isStaff(), 403, 'Toegang geweigerd. Deze beheeromgeving is uitsluitend toegankelijk voor TBS Kliniek personeel.');

        $query = Character::with(['user.warnings', 'user.bannedBy', 'gang']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('email', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('registration_ip', 'like', "%{$search}%")
                            ->orWhere('last_login_ip', 'like', "%{$search}%");
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
            'username' => 'nullable|string|min:2|max:50'.($character->user ? '|unique:users,name,'.$character->user->id : ''),
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
            'energy' => 'required|integer|between:0,100',
            'role' => 'nullable|string|in:user,moderator,admin,owner',
            'original_updated_at' => 'nullable|integer',
            'original_cash' => 'nullable|numeric',
            'original_bank' => 'nullable|numeric',
            'original_stars' => 'nullable|numeric',
            'original_respect' => 'nullable|numeric',
            'original_vip_points' => 'nullable|numeric',
            'original_health' => 'nullable|integer',
            'original_energy' => 'nullable|integer',
            'original_role' => 'nullable|string',
            'original_username' => 'nullable|string',
            'original_character_name' => 'nullable|string',
            'original_is_vip' => 'nullable|boolean',
            'force_overwrite' => 'nullable|boolean',
        ]);

        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        abort_unless($currentUser?->isStaff(), 403, 'Toegang geweigerd.');

        if ($character->user && ($character->user->isOwner() || $character->user->role === 'admin') && ! $currentUser->isOwner()) {
            return back()->with('error', 'Alleen de eigenaar kan het profiel of de status van een Admin of Eigenaar wijzigen.');
        }

        if ($character->user && $character->user->isStaff() && ! $currentUser->isAdmin()) {
            return back()->with('error', 'Alleen administrators kunnen het profiel of de status van medewerkers wijzigen.');
        }

        if (! $currentUser->isAdmin()) {
            if ($request->boolean('release_prison')) {
                return back()->with('error', 'Alleen administrators kunnen spelers vrijlaten uit de gevangenis.');
            }

            $roleInput = $request->input('role');
            $originalRole = $request->input('original_role', $character->user?->role);
            if ($request->filled('role') && $roleInput !== $character->user?->role && $roleInput !== $originalRole) {
                return back()->with('error', 'Alleen administrators kunnen gebruikersrollen toewijzen of wijzigen.');
            }

            $modAttemptedCash = $request->has('original_cash') ? ((int) $validated['cash'] !== (int) $request->input('original_cash')) : ((int) $validated['cash'] !== (int) $character->cash);
            $modAttemptedBank = $request->has('original_bank') ? ((int) $validated['bank'] !== (int) $request->input('original_bank')) : ((int) $validated['bank'] !== (int) $character->bank);
            $modAttemptedStars = $request->has('original_stars') ? ((int) $validated['stars'] !== (int) $request->input('original_stars')) : ((int) $validated['stars'] !== (int) $character->stars);
            $modAttemptedRespect = $request->has('original_respect') ? ((int) round((float) $validated['respect']) !== (int) round((float) $request->input('original_respect'))) : ((float) $validated['respect'] !== (float) $character->respect);
            $modAttemptedVipPoints = $request->has('original_vip_points') ? ((int) $validated['vip_points'] !== (int) $request->input('original_vip_points')) : ((int) $validated['vip_points'] !== (int) $character->vip_points);
            $modAttemptedVipAction = $request->has('vip_action') && $request->input('vip_action') !== 'no_change';
            $modAttemptedIsVip = $request->has('original_is_vip') ? ($request->boolean('is_vip') !== $request->boolean('original_is_vip')) : ($request->has('is_vip') && $request->boolean('is_vip') !== (bool) $character->is_vip);
            $modAttemptedVipTier = $request->filled('vip_tier') && (int) $request->input('vip_tier') !== $character->getSupporterTier();

            if (
                $modAttemptedCash ||
                $modAttemptedBank ||
                $modAttemptedStars ||
                $modAttemptedRespect ||
                $modAttemptedVipPoints ||
                $modAttemptedVipAction ||
                $modAttemptedIsVip ||
                $modAttemptedVipTier
            ) {
                return back()->with('error', 'Alleen administrators kunnen economische tegoeden en VIP status handmatig wijzigen.');
            }
        }

        // Pre-validate role authorization before starting transaction
        if ($character->user && $request->filled('role')) {
            $checkRole = (string) $validated['role'];
            $targetUser = $character->user;
            if (in_array($checkRole, ['owner', 'admin'], true) && ! $currentUser->isOwner()) {
                return back()->with('error', 'Alleen de eigenaar kan de Admin- of Eigenaar-rol toekennen.');
            }

            if (($targetUser->isOwner() || $targetUser->role === 'admin') && ! $currentUser->isOwner()) {
                return back()->with('error', 'Je hebt geen bevoegdheid om de rol van een Admin of Eigenaar te wijzigen.');
            }
        }

        // Refresh character to read latest database state
        $character->refresh();
        $concurrencyWarnings = [];
        $forceOverwrite = $request->boolean('force_overwrite');

        try {
            DB::transaction(function () use (
                $request,
                $validated,
                &$character,
                $currentUser,
                $forceOverwrite,
                &$concurrencyWarnings
            ): void {
                $character = Character::where('id', $character->id)->lockForUpdate()->firstOrFail();
                $targetUser = $character->user_id
                    ? User::where('id', $character->user_id)->lockForUpdate()->first()
                    : null;

                // Moderatie: Update login gebruikersnaam (User name)
                if ($targetUser) {

                    if ($request->filled('username')) {
                        $newUsername = trim((string) $validated['username']);
                        $origUsername = $request->input('original_username', $targetUser->name);
                        $dbUsername = $targetUser->name;

                        if ($dbUsername !== $origUsername && $newUsername === $origUsername) {
                            $concurrencyWarnings[] = 'Inlognaam is tussentijds gewijzigd door een andere beheerder en niet overschreven';
                        } elseif ($dbUsername !== $origUsername && $newUsername !== $origUsername && ! $forceOverwrite) {
                            $concurrencyWarnings[] = 'Inlognaam is tussentijds gewijzigd door een andere beheerder en veilig behouden';
                        } else {
                            $targetUser->name = $newUsername;
                            $targetUser->save();
                        }
                    }

                    // Update user platform role if provided
                    if ($request->filled('role')) {
                        $newRole = (string) $validated['role'];
                        $origRole = $request->input('original_role', $targetUser->role);
                        $dbRole = $targetUser->role;

                        if ($dbRole !== $origRole && $newRole === $origRole) {
                            $concurrencyWarnings[] = 'Gebruikersrol is tussentijds gewijzigd en niet overschreven';
                        } elseif ($dbRole !== $origRole && $newRole !== $origRole && ! $forceOverwrite) {
                            $concurrencyWarnings[] = 'Gebruikersrol is tussentijds gewijzigd en veilig behouden';
                        } else {
                            $oldRole = $targetUser->role;
                            $targetUser->role = $newRole;
                            $targetUser->is_staff = in_array($newRole, ['owner', 'admin', 'moderator'], true);
                            $targetUser->save();

                            if ($oldRole !== $newRole) {
                                try {
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
                                } catch (\Throwable $e) {
                                    Log::warning('Failed to create role change game notification: '.$e->getMessage());
                                }
                            }
                        }
                    }
                }

                // Moderatie: Update weergavenaam (Character name)
                if ($request->filled('character_name')) {
                    $newCharName = trim((string) $validated['character_name']);
                    $origCharName = $request->input('original_character_name', $character->name);
                    $dbCharName = $character->name;

                    if ($dbCharName !== $origCharName && $newCharName === $origCharName) {
                        $concurrencyWarnings[] = 'Personagenaam is tussentijds gewijzigd en niet overschreven';
                    } elseif ($dbCharName !== $origCharName && $newCharName !== $origCharName && ! $forceOverwrite) {
                        $concurrencyWarnings[] = 'Personagenaam is tussentijds gewijzigd en veilig behouden';
                    } else {
                        $character->name = $newCharName;
                    }
                }

                // Moderatie: Reset profielfoto naar standaard default avatar
                if ($request->boolean('reset_avatar')) {
                    $character->avatar_custom = null;
                    $character->avatar = 'assets/characters/avatar_male_1.jpg';
                }

                // Concurrency-safe economic and stat updates
                // 1. Cash
                if ($request->has('original_cash')) {
                    $origCash = (int) $request->input('original_cash');
                    $submittedCash = (int) $validated['cash'];
                    $dbCash = (int) $character->cash;
                    $adminChangedCash = ($submittedCash !== $origCash);
                    $dbChangedCash = ($dbCash !== $origCash);

                    if ($dbChangedCash && ! $adminChangedCash) {
                        $concurrencyWarnings[] = 'Contant geld is tussentijds gewijzigd (nu: €'.number_format($dbCash, 0, ',', '.').') en niet overschreven';
                    } elseif ($dbChangedCash && $adminChangedCash && ! $forceOverwrite) {
                        $concurrencyWarnings[] = 'Contant geld is tussentijds gewijzigd (nu: €'.number_format($dbCash, 0, ',', '.').') vóór je bewerking en veilig behouden';
                    } else {
                        $character->cash = $submittedCash;
                    }
                } else {
                    $character->cash = (int) $validated['cash'];
                }

                // 2. Bank
                if ($request->has('original_bank')) {
                    $origBank = (int) $request->input('original_bank');
                    $submittedBank = (int) $validated['bank'];
                    $dbBank = (int) $character->bank;
                    $adminChangedBank = ($submittedBank !== $origBank);
                    $dbChangedBank = ($dbBank !== $origBank);

                    if ($dbChangedBank && ! $adminChangedBank) {
                        $concurrencyWarnings[] = 'Bank saldo is tussentijds gewijzigd (nu: €'.number_format($dbBank, 0, ',', '.').') en niet overschreven';
                    } elseif ($dbChangedBank && $adminChangedBank && ! $forceOverwrite) {
                        $concurrencyWarnings[] = 'Bank saldo is tussentijds gewijzigd (nu: €'.number_format($dbBank, 0, ',', '.').') vóór je bewerking en veilig behouden';
                    } else {
                        $character->bank = $submittedBank;
                    }
                } else {
                    $character->bank = (int) $validated['bank'];
                }

                // 3. Stars
                if ($request->has('original_stars')) {
                    $origStars = (int) $request->input('original_stars');
                    $submittedStars = (int) $validated['stars'];
                    $dbStars = (int) $character->stars;
                    $adminChangedStars = ($submittedStars !== $origStars);
                    $dbChangedStars = ($dbStars !== $origStars);

                    if ($dbChangedStars && ! $adminChangedStars) {
                        $concurrencyWarnings[] = 'Sterren zijn tussentijds gewijzigd (nu: '.$dbStars.') en niet overschreven';
                    } elseif ($dbChangedStars && $adminChangedStars && ! $forceOverwrite) {
                        $concurrencyWarnings[] = 'Sterren zijn tussentijds gewijzigd (nu: '.$dbStars.') vóór je bewerking en veilig behouden';
                    } else {
                        $character->stars = $submittedStars;
                    }
                } else {
                    $character->stars = (int) $validated['stars'];
                }

                // 4. Respect
                if ($request->has('original_respect')) {
                    $origRespect = (int) round((float) $request->input('original_respect'));
                    $submittedRespect = max(0, (int) round((float) $validated['respect']));
                    $dbRespect = (int) round((float) $character->respect);
                    $adminChangedRespect = ($submittedRespect !== $origRespect);
                    $dbChangedRespect = ($dbRespect !== $origRespect);

                    if ($dbChangedRespect && ! $adminChangedRespect) {
                        $concurrencyWarnings[] = 'Respect is tussentijds gewijzigd (nu: '.$dbRespect.') en niet overschreven';
                    } elseif ($dbChangedRespect && $adminChangedRespect && ! $forceOverwrite) {
                        $concurrencyWarnings[] = 'Respect is tussentijds gewijzigd (nu: '.$dbRespect.') vóór je bewerking en veilig behouden';
                    } else {
                        $character->respect = $submittedRespect;
                    }
                } else {
                    $character->respect = max(0, (int) round((float) $validated['respect']));
                }

                // 5. VIP Points
                if ($request->has('original_vip_points')) {
                    $origVipPoints = (int) $request->input('original_vip_points');
                    $submittedVipPoints = (int) $validated['vip_points'];
                    $dbVipPoints = (int) ($character->vip_points ?? 0);
                    $adminChangedVipPoints = ($submittedVipPoints !== $origVipPoints);
                    $dbChangedVipPoints = ($dbVipPoints !== $origVipPoints);

                    if ($dbChangedVipPoints && ! $adminChangedVipPoints) {
                        $concurrencyWarnings[] = 'VIP punten zijn tussentijds gewijzigd (nu: '.$dbVipPoints.') en niet overschreven';
                    } elseif ($dbChangedVipPoints && $adminChangedVipPoints && ! $forceOverwrite) {
                        $concurrencyWarnings[] = 'VIP punten zijn tussentijds gewijzigd (nu: '.$dbVipPoints.') vóór je bewerking en veilig behouden';
                    } else {
                        $character->vip_points = $submittedVipPoints;
                    }
                } else {
                    $character->vip_points = (int) $validated['vip_points'];
                }

                // 6. Health
                if ($request->has('original_health')) {
                    $origHealth = (int) $request->input('original_health');
                    $submittedHealth = max(0, min(100, (int) $validated['health']));
                    $dbHealth = (int) $character->health;
                    $adminChangedHealth = ($submittedHealth !== $origHealth);
                    $dbChangedHealth = ($dbHealth !== $origHealth);

                    if ($dbChangedHealth && ! $adminChangedHealth) {
                        $concurrencyWarnings[] = 'HP is tussentijds gewijzigd (nu: '.$dbHealth.'%) en niet overschreven';
                    } elseif ($dbChangedHealth && $adminChangedHealth && ! $forceOverwrite) {
                        $concurrencyWarnings[] = 'HP is tussentijds gewijzigd (nu: '.$dbHealth.'%) vóór je bewerking en veilig behouden';
                    } else {
                        $character->health = $submittedHealth;
                    }
                } else {
                    $character->health = max(0, min(100, (int) $validated['health']));
                }

                // 7. Energy
                if ($request->has('original_energy')) {
                    $origEnergy = (int) $request->input('original_energy');
                    $submittedEnergy = max(0, min(100, (int) $validated['energy']));
                    $dbEnergy = (int) $character->energy;
                    $adminChangedEnergy = ($submittedEnergy !== $origEnergy);
                    $dbChangedEnergy = ($dbEnergy !== $origEnergy);

                    if ($dbChangedEnergy && ! $adminChangedEnergy) {
                        $concurrencyWarnings[] = 'Energie is tussentijds gewijzigd (nu: '.$dbEnergy.') en niet overschreven';
                    } elseif ($dbChangedEnergy && $adminChangedEnergy && ! $forceOverwrite) {
                        $concurrencyWarnings[] = 'Energie is tussentijds gewijzigd (nu: '.$dbEnergy.') vóór je bewerking en veilig behouden';
                    } else {
                        $character->energy = $submittedEnergy;
                    }
                } else {
                    $character->energy = max(0, min(100, (int) $validated['energy']));
                }

                // VIP beheer & handmatige toekenning (alleen voor administrators/eigenaar)
                if ($currentUser->isAdmin()) {
                    $vipAction = $request->input('vip_action', 'no_change');
                    $vipTier = (int) $request->input('vip_tier', ($character->getSupporterTier() ?: 1));
                    $perks = $character->perks ?? [];
                    $wasVip = $character->isVip();

                    $origIsVip = $request->has('original_is_vip') ? $request->boolean('original_is_vip') : $wasVip;
                    $submittedIsVip = $request->boolean('is_vip');
                    $adminChangedVip = ($submittedIsVip !== $origIsVip) || ($vipAction !== 'no_change');
                    $dbChangedVip = ($wasVip !== $origIsVip);

                    if ($dbChangedVip && ! $adminChangedVip) {
                        $concurrencyWarnings[] = 'VIP status is tussentijds gewijzigd (nu: '.($wasVip ? 'actief' : 'inactief').') en niet overschreven';
                    } elseif ($dbChangedVip && $adminChangedVip && ! $forceOverwrite) {
                        $concurrencyWarnings[] = 'VIP status is tussentijds gewijzigd (nu: '.($wasVip ? 'actief' : 'inactief').') vóór je bewerking en veilig behouden';
                    } else {
                        if ($vipAction === 'revoke' || (! $request->boolean('is_vip') && in_array($vipAction, ['no_change', 'revoke'], true))) {
                            $character->is_vip = false;
                            $character->vip_expires_at = null;
                            $perks['supporter_tier'] = 0;
                            $perks['has_custom_name_color'] = false;
                            $perks['has_sparkle_effect'] = false;
                            $perks['hourly_cash_bonus'] = 0;
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
                            } elseif ((! $wasVip && ! $character->vip_expires_at) || ($character->vip_expires_at && $character->vip_expires_at->isPast())) {
                                $character->vip_expires_at = now()->addDays($daysToAdd > 0 ? $daysToAdd : 30);
                            }

                            $perks['supporter_tier'] = max(1, $vipTier);
                            $perks['hourly_cash_bonus'] = match ($perks['supporter_tier']) {
                                4 => 50000,
                                3 => 20000,
                                2 => 7500,
                                default => 2500,
                            };
                            $perks['is_donator'] = true;

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
                                try {
                                    GameNotification::create([
                                        'character_id' => $character->id,
                                        'type' => 'vip_granted',
                                        'icon' => 'fa-crown',
                                        'title' => 'VIP Status Toegekend!',
                                        'message' => "Gefeliciteerd! De administratie heeft je {$tierTitle} status toegekend.",
                                        'is_read' => false,
                                    ]);
                                } catch (\Throwable $e) {
                                    Log::warning('Failed to create VIP grant game notification: '.$e->getMessage());
                                }
                            }
                        }

                        $character->perks = $perks;
                    }
                }

                // Release from prison if requested
                if ($request->boolean('release_prison')) {
                    $character->is_in_prison = false;
                    $character->prison_until = null;
                    $character->prison_reason = null;
                }

                $character->save();
            });
            $character->refresh();
        } catch (\Throwable $e) {
            Log::error("Failed to update player {$character->name} (#{$character->id}): ".$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Er is een fout opgetreden bij het bijwerken van de speler: '.$e->getMessage());
        }

        try {
            AuditLogService::log(
                action: 'admin_update_player',
                category: 'admin',
                description: "Speler {$character->name} (#{$character->id}) profiel en status bijgewerkt door ".($currentUser?->name ?? 'Staff'),
                character: $character,
                user: $character->user,
                details: [
                    'updated_by_id' => $currentUser?->id,
                    'updated_by_name' => $currentUser?->name,
                    'fields_updated' => array_keys($validated),
                    'new_role' => $character->user?->role,
                    'cash' => $character->cash,
                    'bank' => $character->bank,
                    'stars' => $character->stars,
                    'respect' => $character->respect,
                    'is_vip' => (bool) $character->is_vip,
                ],
                customRisk: 'verdacht',
                customReason: 'Spelerprofiel of status gewijzigd via Admin Paneel door '.($currentUser?->name ?? 'Staff')
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log admin_update_player audit record: '.$e->getMessage());
        }

        $redirect = back()->with('success', "Speler {$character->name} is succesvol bijgewerkt!");

        if (! empty($concurrencyWarnings)) {
            $warningText = 'Let op: De volgende gegevens zijn tijdens het bewerken tussentijds gewijzigd en niet overschreven: '.implode('; ', $concurrencyWarnings).'.';
            $redirect->with('warning', $warningText)->with('editing_player_id', $character->id);
        }

        return $redirect;
    }

    /**
     * Manage gameplay settings & win rates.
     */
    public function gameplay(): View
    {
        abort_unless(Auth::user()?->isAdmin(), 403, 'Alleen beheerders (Admin/Owner) hebben toegang tot deze instellingen.');

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
        if (! Auth::user()?->isAdmin()) {
            return back()->with('error', 'Alleen beheerders (Admin/Owner) mogen deze instellingen wijzigen.');
        }

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

        try {
            AuditLogService::log(
                action: 'admin_update_gameplay',
                category: 'system',
                description: 'Gameplay winkansen en instellingen bijgewerkt door '.(Auth::user()?->name ?? 'Staff'),
                character: Auth::user()?->character,
                user: Auth::user(),
                details: $validated
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log admin_update_gameplay audit record: '.$e->getMessage());
        }

        return back()->with('success', 'Gameplay winkansen en instellingen succesvol opgeslagen!');
    }

    /**
     * Manage chest / case contents & loot chances.
     */
    public function chests(): View
    {
        abort_unless(Auth::user()?->isAdmin(), 403, 'Alleen beheerders (Admin/Owner) hebben toegang tot deze instellingen.');

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
        if (! Auth::user()?->isAdmin()) {
            return back()->with('error', 'Alleen beheerders (Admin/Owner) mogen deze instellingen wijzigen.');
        }

        $validated = $request->validate([
            'case_xxl_price_cash' => 'required|numeric|min:0',
            'case_xxl_price_stars' => 'required|numeric|min:0',
            'case_xxl_min_cash' => 'required|numeric|min:0',
            'case_xxl_max_cash' => 'required|numeric|gte:case_xxl_min_cash',
            'case_xxl_stars_reward' => 'required|integer|min:0',
            'bag_sell_reward' => 'required|numeric|min:0',
            'bag_open_min_cash' => 'required|numeric|min:0',
            'bag_open_max_cash' => 'required|numeric|gte:bag_open_min_cash',
        ]);

        foreach ($validated as $key => $value) {
            GameSetting::set($key, $value, "Beheerd via Admin Paneel ({$key})");
        }

        try {
            AuditLogService::log(
                action: 'admin_update_chests',
                category: 'system',
                description: 'Kisten en loot instellingen bijgewerkt door '.(Auth::user()?->name ?? 'Staff'),
                character: Auth::user()?->character,
                user: Auth::user(),
                details: $validated
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log admin_update_chests audit record: '.$e->getMessage());
        }

        return back()->with('success', 'Kisten, koffers en loot-inhoud succesvol bijgewerkt!');
    }

    /**
     * Manage safe (kluis) contents and codes.
     */
    public function vault(): View
    {
        abort_unless(Auth::user()?->isAdmin(), 403, 'Alleen beheerders (Admin/Owner) hebben toegang tot deze instellingen.');

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
        if (! Auth::user()?->isAdmin()) {
            return back()->with('error', 'Alleen beheerders (Admin/Owner) mogen deze instellingen wijzigen.');
        }

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

        try {
            AuditLogService::log(
                action: 'admin_update_vault',
                category: 'system',
                description: 'Kluisinstellingen bijgewerkt door '.(Auth::user()?->name ?? 'Staff'),
                character: Auth::user()?->character,
                user: Auth::user(),
                details: [
                    'reward_cash' => $validated['vault_reward_cash'],
                    'reward_tokens' => $validated['vault_reward_tokens'],
                    'reward_respect' => $validated['vault_reward_respect'],
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log admin_update_vault audit record: '.$e->getMessage());
        }

        return back()->with('success', 'Kluiscode en kluisinhoud succesvol opgeslagen!');
    }

    /**
     * Generate new random 4-digit vault code.
     */
    public function randomizeVaultCode(): RedirectResponse
    {
        if (! Auth::user()?->isAdmin()) {
            return back()->with('error', 'Alleen beheerders (Admin/Owner) mogen deze instellingen wijzigen.');
        }

        $newCode = (string) rand(1000, 9999);
        GameSetting::set('vault_code', $newCode, 'Automatisch gegenereerde kluiscode');

        try {
            AuditLogService::log(
                action: 'admin_randomize_vault_code',
                category: 'system',
                description: 'Nieuwe geheime kluiscode gegenereerd door '.(Auth::user()?->name ?? 'Staff'),
                character: Auth::user()?->character,
                user: Auth::user(),
                details: ['code_set' => true]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log admin_randomize_vault_code audit record: '.$e->getMessage());
        }

        return back()->with('success', "Nieuwe geheime kluiscode gegenereerd: {$newCode}");
    }

    /**
     * View referral system log and rewards.
     */
    public function referrals(Request $request): View
    {
        abort_unless(Auth::user()?->isStaff(), 403, 'Toegang geweigerd.');

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

            try {
                AuditLogService::log(
                    action: 'admin_run_migrations',
                    category: 'system',
                    description: "Database migraties ({$pendingBefore} openstaand) en spelbalanscontrole uitgevoerd vanuit /admin",
                    character: $user->character,
                    user: $user,
                    details: ['pending_before' => $pendingBefore, 'exceeded_before' => $exceededBefore]
                );
            } catch (\Throwable $e) {
                Log::warning('Failed to log admin_run_migrations audit record: '.$e->getMessage());
            }

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

            try {
                AuditLogService::log(
                    action: 'admin_run_normalization',
                    category: 'system',
                    description: "Systeem- en spelbalans herstelmigratie uitgevoerd vanuit /admin ({$summary})",
                    character: $user->character,
                    user: $user,
                    details: ['pending_before' => $pendingBefore, 'exceeded_before' => $exceededBefore]
                );
            } catch (\Throwable $e) {
                Log::warning('Failed to log admin_run_normalization audit record: '.$e->getMessage());
            }

            return back()->with('success', "Systeem- en spelbalanscontrole voltooid! {$summary}");
        } catch (\Throwable $e) {
            return back()->with('error', 'Fout bij uitvoeren van herstelmigratie: '.$e->getMessage());
        }
    }

    /**
     * Toggle maintenance mode on or off from the admin dashboard.
     */
    public function toggleMaintenance(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user || ! $user->isAdmin()) {
            return back()->with('error', 'Alleen administrators kunnen de onderhoudsmodus in- of uitschakelen.');
        }

        $validated = $request->validate([
            'action' => 'required|string|in:on,off',
            'target_time' => 'nullable|string|regex:/^[0-2][0-9]:[0-5][0-9]$/',
        ]);

        $action = $validated['action'];
        $targetTime = $validated['target_time'] ?? null;

        $params = ['action' => $action];
        if ($targetTime !== null) {
            $params['--time'] = $targetTime;
        }
        Artisan::call('tbs:maintenance', $params);

        try {
            AuditLogService::log(
                action: 'admin_toggle_maintenance',
                category: 'system',
                description: 'Onderhoudsmodus '.($action === 'on' ? 'INGESCHAKELD' : 'UITGESCHAKELD').' door '.$user->name,
                character: $user->character,
                user: $user,
                details: ['action' => $action, 'target_time' => $targetTime]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log admin_toggle_maintenance audit record: '.$e->getMessage());
        }

        if ($action === 'on') {
            return back()->with('success', 'Onderhoudsmodus is nu ACTIEF! Spelers hebben geen toegang tot het spel. Beheerders behouden toegang.');
        }

        return back()->with('success', 'Onderhoudsmodus is UITGESCHAKELD! De kliniekdeuren zijn weer geopend voor alle spelers.');
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
            ->where(function ($q) {
                $q->whereRaw('strength > (100 + COALESCE(level, 1) * 50)')
                    ->orWhereRaw('defense > (100 + COALESCE(level, 1) * 50)')
                    ->orWhereRaw('stamina > (100 + COALESCE(level, 1) * 50)')
                    ->orWhereRaw('intelligence > (100 + COALESCE(level, 1) * 50)')
                    ->orWhereRaw('charisma > (100 + COALESCE(level, 1) * 50)')
                    ->orWhereRaw('discipline > (100 + COALESCE(level, 1) * 50)')
                    ->orWhere('max_energy', '>', 100)
                    ->orWhere('energy', '>', 100);
            })
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
        abort_unless(Auth::user()?->isAdmin(), 403, 'Alleen beheerders (Admin/Owner) mogen Buy Me a Coffee orders beheren.');

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
        if (! Auth::user()?->isAdmin()) {
            return back()->with('error', 'Alleen beheerders (Admin/Owner) mogen Buy Me a Coffee orders beheren.');
        }

        /** @var User|null $currentUser */
        $currentUser = Auth::user();

        $validated = $request->validate([
            'character_id' => 'required|exists:characters,id',
            'membership_tier' => 'nullable|string',
            'is_membership' => 'nullable|boolean',
            'grant_rewards' => 'nullable|boolean',
        ]);

        $character = Character::findOrFail($validated['character_id']);
        $tierName = $validated['membership_tier'] ?: ($order->membership_tier ?: 'TBS V.I.P. Lid');
        $isMembership = $request->boolean('is_membership') || $order->is_membership || ! empty($validated['membership_tier']);

        try {
            DB::transaction(function () use ($order, $character, $tierName, $isMembership, $request): void {
                $lockedOrder = BuyMeACoffeeOrder::where('id', $order->id)->lockForUpdate()->firstOrFail();
                $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->firstOrFail();

                $alreadyGranted = ! empty($lockedOrder->payload['rewards_granted']);

                $lockedOrder->character_id = $lockedCharacter->id;
                $lockedOrder->status = 'manually_linked';
                $lockedOrder->membership_tier = $tierName;
                $lockedOrder->is_membership = $isMembership;
                $lockedOrder->membership_status = $isMembership ? 'active' : null;
                $lockedOrder->linked_at = now();
                $orderPayload = $lockedOrder->payload ?? [];
                $orderPayload['rewards_granted'] = true;
                $orderPayload['rewards_granted_at'] = $orderPayload['rewards_granted_at'] ?? now()->toIso8601String();
                $lockedOrder->payload = $orderPayload;
                $lockedOrder->save();

                if ($request->boolean('grant_rewards', true) && ! $alreadyGranted) {
                    $webhookController = app(BuyMeACoffeeWebhookController::class);
                    $tierLower = strtolower($tierName);
                    $isCoffee = str_contains($tierLower, 'koffie') || str_contains($tierLower, 'bakkie') || (! $isMembership);

                    if (! $isCoffee && $isMembership) {
                        $webhookController->grantMembershipTier($lockedCharacter, $tierName, $lockedOrder->payload ?? []);
                    } else {
                        $coffees = 1;
                        if (str_contains($tierName, '5x') || (float) $lockedOrder->amount >= 15.0) {
                            $coffees = 5;
                        } elseif (str_contains($tierName, '3x') || (float) $lockedOrder->amount >= 9.0) {
                            $coffees = 3;
                        }
                        $payload = array_merge($lockedOrder->payload ?? [], [
                            'support_coffees' => $coffees,
                            'quantity' => $coffees,
                            'amount' => max((float) $lockedOrder->amount, $coffees * 3.0),
                        ]);
                        $webhookController->processEvent('donation', $payload, $lockedCharacter);
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error("Failed to link BuyMeACoffee order #{$order->id}: ".$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Er is een fout opgetreden bij het koppelen van de order: '.$e->getMessage());
        }

        try {
            GameNotification::create([
                'character_id' => $character->id,
                'type' => 'bmac_linked',
                'icon' => 'fa-mug-hot',
                'title' => 'Buy Me a Coffee Gekoppeld!',
                'message' => "Je Buy Me a Coffee bijdrage van {$order->formatted_amount} is handmatig door het beheer gekoppeld aan je account. Al je voordelen zijn direct geactiveerd!",
                'is_read' => false,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to create BMAC linked notification: '.$e->getMessage());
        }

        try {
            AuditLogService::log(
                action: 'buymeacoffee_manual_link',
                category: 'vip',
                description: "BuyMeACoffee order #{$order->id} ({$order->supporter_name}) handmatig gekoppeld aan {$character->name} met tier {$tierName}",
                character: $character,
                user: $character->user,
                amount: (int) $order->amount,
                details: ['order_id' => $order->id, 'tier' => $tierName]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log buymeacoffee_manual_link audit record: '.$e->getMessage());
        }

        return back()->with('success', "Buy Me a Coffee order succesvol gekoppeld aan {$character->name}!");
    }

    /**
     * Re-grant rewards for a linked Buy Me a Coffee order to ensure player receives items.
     */
    public function regrantBuyMeACoffee(BuyMeACoffeeOrder $order): RedirectResponse
    {
        if (! Auth::user()?->isAdmin()) {
            return back()->with('error', 'Alleen beheerders (Admin/Owner) mogen Buy Me a Coffee orders beheren.');
        }

        if (! $order->character) {
            return back()->with('error', 'Koppel eerst een speler aan deze order voordat je beloningen kunt uitkeren!');
        }

        $character = $order->character;
        $webhookController = app(BuyMeACoffeeWebhookController::class);

        $tier = (string) ($order->membership_tier ?? '');
        $tierLower = strtolower($tier);
        $isCoffee = str_contains($tierLower, 'koffie') || str_contains($tierLower, 'bakkie') || (! $order->is_membership);

        try {
            DB::transaction(function () use ($character, $order, $tier, $tierLower, $isCoffee, $webhookController): void {
                $lockedOrder = BuyMeACoffeeOrder::where('id', $order->id)->lockForUpdate()->firstOrFail();
                $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->firstOrFail();

                // Rate guard: Prevent rapid duplicate regrants within 5 seconds
                $orderPayload = $lockedOrder->payload ?? [];
                if (! empty($orderPayload['regranted_at'])) {
                    $lastRegrant = Carbon::parse($orderPayload['regranted_at']);
                    if (now()->diffInSeconds($lastRegrant) < 5) {
                        return;
                    }
                }

                if (! $isCoffee && $lockedOrder->is_membership) {
                    if (str_contains($tierLower, 'lifetime') && $lockedCharacter->getVipTier() === 4) {
                        $orderPayload['rewards_granted'] = true;
                        $orderPayload['rewards_granted_at'] = now()->toIso8601String();
                        $lockedOrder->payload = $orderPayload;
                        $lockedOrder->save();

                        return;
                    }

                    $webhookController->grantMembershipTier($lockedCharacter, $tier, $lockedOrder->payload ?? []);
                } else {
                    $coffees = 1;
                    if (str_contains($tier, '5x') || (float) $lockedOrder->amount >= 15.0) {
                        $coffees = 5;
                    } elseif (str_contains($tier, '3x') || (float) $lockedOrder->amount >= 9.0) {
                        $coffees = 3;
                    }
                    $payload = array_merge($lockedOrder->payload ?? [], [
                        'support_coffees' => $coffees,
                        'quantity' => $coffees,
                        'amount' => max((float) $lockedOrder->amount, $coffees * 3.0),
                    ]);
                    $webhookController->processEvent('donation', $payload, $lockedCharacter);
                }

                $orderPayload = $lockedOrder->payload ?? [];
                $orderPayload['rewards_granted'] = true;
                $orderPayload['rewards_granted_at'] = $orderPayload['rewards_granted_at'] ?? now()->toIso8601String();
                $orderPayload['regranted_at'] = now()->toIso8601String();
                $orderPayload['regrant_count'] = ((int) ($orderPayload['regrant_count'] ?? 0)) + 1;
                $lockedOrder->payload = $orderPayload;
                $lockedOrder->save();
            });
        } catch (\Throwable $e) {
            Log::error("Failed to regrant BuyMeACoffee order #{$order->id}: ".$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Er is een fout opgetreden bij het uitkeren van de beloningen: '.$e->getMessage());
        }

        if (str_contains($tierLower, 'lifetime') && $character->getVipTier() === 4) {
            return back()->with('info', "{$character->name} heeft al VIP LIFETIME actief. Er zijn geen dubbele beloningen toegekend.");
        }

        try {
            AuditLogService::log(
                action: 'buymeacoffee_regrant',
                category: 'vip',
                description: "BuyMeACoffee order #{$order->id} beloningen ({$order->rewards_summary}) handmatig her-uitgekeerd aan {$character->name}",
                character: $character,
                user: $character->user,
                amount: (int) $order->amount,
                details: ['order_id' => $order->id]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log buymeacoffee_regrant audit record: '.$e->getMessage());
        }

        return back()->with('success', "Beloningen ({$order->rewards_summary}) zijn zojuist succesvol uitgekeerd aan {$character->name}!");
    }

    /**
     * Cancel / expire membership and revoke VIP status.
     */
    public function cancelBuyMeACoffee(BuyMeACoffeeOrder $order): RedirectResponse
    {
        if (! Auth::user()?->isAdmin()) {
            return back()->with('error', 'Alleen beheerders (Admin/Owner) mogen Buy Me a Coffee orders beheren.');
        }

        $character = $order->character;

        try {
            DB::transaction(function () use ($order): void {
                $lockedOrder = BuyMeACoffeeOrder::where('id', $order->id)->lockForUpdate()->firstOrFail();
                $lockedCharacter = $lockedOrder->character_id
                    ? Character::where('id', $lockedOrder->character_id)->lockForUpdate()->first()
                    : null;

                $lockedOrder->status = 'cancelled';
                $lockedOrder->membership_status = 'cancelled';
                $lockedOrder->cancelled_at = now();
                $lockedOrder->save();

                if ($lockedCharacter) {
                    app(BuyMeACoffeeWebhookController::class)->revokeMembership($lockedCharacter, (string) $lockedOrder->membership_tier, [
                        'reason' => 'cancelled_by_admin',
                        'order_id' => $lockedOrder->id,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::error("Failed to cancel BuyMeACoffee order #{$order->id}: ".$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Fout bij het annuleren van het lidmaatschap: '.$e->getMessage());
        }

        try {
            AuditLogService::log(
                action: 'buymeacoffee_membership_cancelled',
                category: 'vip',
                description: "BuyMeACoffee order #{$order->id} geannuleerd en VIP privileges beëindigd door ".(Auth::user()?->name ?? 'Staff'),
                character: $character,
                user: $character?->user,
                details: ['order_id' => $order->id, 'cancelled_by_id' => Auth::id()]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log buymeacoffee_membership_cancelled audit record: '.$e->getMessage());
        }

        return back()->with('success', 'Membership geannuleerd en VIP privileges zijn beëindigd!');
    }

    /**
     * Manually add and link a Buy Me a Coffee transaction.
     */
    public function createBuyMeACoffeeManual(Request $request): RedirectResponse
    {
        if (! Auth::user()?->isAdmin()) {
            return back()->with('error', 'Alleen beheerders (Admin/Owner) mogen Buy Me a Coffee orders beheren.');
        }

        /** @var User|null $currentUser */
        $currentUser = Auth::user();

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

        // Prevent rapid duplicate clicks from creating duplicate orders
        $recentDuplicate = BuyMeACoffeeOrder::where('character_id', $character->id)
            ->where('membership_tier', $validated['membership_tier'])
            ->where('amount', $validated['amount'])
            ->where('created_at', '>=', now()->subSeconds(10))
            ->first();
        if ($recentDuplicate) {
            return back()->with('warning', 'Een identieke handmatige order is zojuist al verwerkt om dubbele toekenning te voorkomen.');
        }

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

        try {
            DB::transaction(function () use ($isMembership, $validated, $character, $coffees, $tier, $currentUser): void {
                $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->firstOrFail();

                $order = BuyMeACoffeeOrder::create([
                    'event' => $isMembership ? 'membership.manual' : 'donation.manual',
                    'external_id' => 'manual_'.uniqid(),
                    'supporter_name' => $validated['supporter_name'],
                    'supporter_email' => $validated['supporter_email'],
                    'amount' => $validated['amount'],
                    'currency' => 'EUR',
                    'membership_tier' => $validated['membership_tier'],
                    'message' => $validated['message'],
                    'character_id' => $lockedCharacter->id,
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
                        'rewards_granted' => true,
                        'rewards_granted_at' => now()->toIso8601String(),
                    ],
                ]);

                $webhookController = app(BuyMeACoffeeWebhookController::class);
                if ($isMembership) {
                    $webhookController->grantMembershipTier($lockedCharacter, $validated['membership_tier'], ['manual' => true, 'order_id' => $order->id]);
                } else {
                    $webhookController->processEvent('donation', [
                        'support_coffees' => $coffees,
                        'quantity' => $coffees,
                        'amount' => max((float) $validated['amount'], $coffees * 3.0),
                    ], $lockedCharacter);
                }

                try {
                    AuditLogService::log(
                        action: 'buymeacoffee_manual_create',
                        category: 'vip',
                        description: "Handmatige BuyMeACoffee order #{$order->id} aangemaakt voor {$lockedCharacter->name} ({$validated['membership_tier']}) door ".($currentUser?->name ?? Auth::user()?->name ?? 'Staff'),
                        character: $lockedCharacter,
                        user: $lockedCharacter->user,
                        amount: (int) $validated['amount'],
                        details: ['order_id' => $order->id, 'tier' => $tier, 'is_membership' => $isMembership]
                    );
                } catch (\Throwable $e) {
                    Log::warning('Failed to log buymeacoffee_manual_create audit record: '.$e->getMessage());
                }
            });
        } catch (\Throwable $e) {
            Log::error('Failed to create manual BuyMeACoffee order: '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Er is een fout opgetreden bij het aanmaken van de handmatige order: '.$e->getMessage());
        }

        return back()->with('success', "Handmatige Buy Me a Coffee registratie succesvol gekoppeld aan {$character->name}!");
    }
}
