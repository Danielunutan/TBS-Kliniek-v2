<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\CharacterVehicle;
use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Models\Gang;
use App\Models\GangAlliance;
use App\Models\GangAttack;
use App\Models\GangDonation;
use App\Models\ControlPoint;
use App\Models\GangItem;
use App\Models\GangRequest;
use App\Models\GangWar;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\GangWageService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GangController extends Controller
{
    public function __construct(
        protected EnergyService $energyService,
        protected EconomyService $economyService,
        protected LevelService $levelService,
        protected GangWageService $wageService,
    ) {}

    /**
     * Show gang listing and player gang details (Matching Screenshot 2 & 4).
     */
    public function index(Request $request, ?string $forceTab = null): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $myGang = $character->gang;

        if ($myGang) {
            $myGang->load([
                'members',
                'requests.character',
                'donations.character' => fn ($q) => $q->latest()->take(10),
                'alliances.allyGang',
                'personalShopper',
                'moneyMagnet',
                'properties',
                'storedVehicles.vehicle',
                'items.item',
            ]);
        }

        $myInventoryItems = $myGang ? CharacterItem::where('character_id', $character->id)->where('quantity', '>', 0)->with('item')->get() : collect();

        // Real gang ranking list sorted by total member respect
        $realGangs = Gang::with(['leader', 'members', 'npcs'])->withCount('members')->get();
        $realGangs = $realGangs->sortByDesc(function ($g) {
            return $g->members->sum('respect');
        })->values();

        $pendingGangRequestIds = [];
        if (! $character->gang_id) {
            $pendingGangRequestIds = GangRequest::where('character_id', $character->id)
                ->where('status', 'pending')
                ->pluck('gang_id', 'gang_id')
                ->toArray();
        }

        $gangRankings = collect();
        foreach ($realGangs as $g) {
            $totalRespect = $g->members->sum('respect');
            $formattedRespect = $totalRespect >= 1000000 ? number_format($totalRespect / 1000000, 2).'M' : ($totalRespect >= 1000 ? number_format($totalRespect / 1000, 1).'K' : number_format($totalRespect, 0, ',', '.'));
            $gangRankings->push([
                'id' => $g->id,
                'name' => $g->name,
                'tag' => $g->tag,
                'leader_name' => $g->leader?->name ?? 'Onbekend',
                'leader_tag' => '@'.($g->leader?->name ?? 'onbekend'),
                'respect' => $formattedRespect,
                'respect_formatted' => $formattedRespect,
                'slay' => $formattedRespect,
                'raw_respect' => $totalRespect,
                'raw_slay' => $totalRespect,
                'cash' => '€'.number_format($g->treasury, 0, ',', '.'),
                'raw_cash' => (int) $g->treasury,
                'icon' => $g->emblem ?? 'fa-crown',
                'avatar_url' => $g->avatar_url,
                'banner_url' => $g->banner_url,
                'members_count' => $g->members_count,
                'max_members' => $g->max_members ?? 10,
                'is_real' => true,
                'is_open' => (bool) ($g->is_open ?? true),
                'min_respect' => (int) ($g->min_respect ?? 0),
                'has_pending_request' => isset($pendingGangRequestIds[$g->id]),
                'profile_url' => route('gangs.show', $g->tag ?? $g->id),
                'description' => $g->description,
            ]);
        }

        $topRankings = Character::getTopRankings(10);

        $wagePayout = null;
        if ($myGang) {
            $wagePayout = $this->wageService->payoutPendingWages($character);
            if ($wagePayout && $wagePayout['amount'] > 0) {
                session()->flash('success', '💰 Bende Uurloon Ontvangen! Er is zojuist €'.number_format($wagePayout['amount'], 0, ',', '.')." contant geld op zak uitbetaald voor {$wagePayout['hours']} gewerkte uur(en)!");
            }
        }

        $wageDetails = $this->wageService->getWageDetails($character);
        $wageBreakdown = $myGang ? $this->wageService->getWageBreakdown($myGang, $character) : [];
        $hourlyWage = $wageDetails['hourly_wage'];
        $dailyWage = $wageDetails['daily_wage'];

        $todayRecentLogs = $myGang ? GangDonation::where('gang_id', $myGang->id)->whereDate('created_at', today())->with('character')->latest()->take(20)->get() : collect();
        $totalDonatedToday = $myGang ? (int) GangDonation::where('gang_id', $myGang->id)->whereDate('created_at', today())->sum('amount') : 0;

        $todayDonations = $myGang ? GangDonation::where('gang_id', $myGang->id)
            ->whereDate('created_at', today())
            ->with('character')
            ->selectRaw('character_id, sum(amount) as amount, count(*) as donation_count, max(created_at) as latest_donation_at')
            ->groupBy('character_id')
            ->orderByDesc('amount')
            ->get() : collect();

        $memberDonations = $myGang ? GangDonation::where('gang_id', $myGang->id)
            ->with('character')
            ->selectRaw('character_id, sum(amount) as total_amount, count(*) as donation_count')
            ->groupBy('character_id')
            ->orderByDesc('total_amount')
            ->get() : collect();

        $inboundAlliances = $myGang ? GangAlliance::where('ally_gang_id', $myGang->id)->with('gang')->get() : collect();

        $recentAttacks = GangAttack::with(['attackerGang', 'targetGang', 'character'])->latest()->take(15)->get();

        $warsWon = $myGang ? (GangAttack::where('attacker_gang_id', $myGang->id)->where('won', true)->count()
            + GangAttack::where('target_gang_id', $myGang->id)->where('won', false)->count()) : 0;

        $warsLost = $myGang ? (GangAttack::where('attacker_gang_id', $myGang->id)->where('won', false)->count()
            + GangAttack::where('target_gang_id', $myGang->id)->where('won', true)->count()) : 0;

        $totalRespect = $myGang ? (float) $myGang->members->sum('respect') : 0;

        $gangMembersData = $myGang ? $myGang->members->sortByDesc('respect')->values()->map(function ($m) use ($myGang) {
            $isShopper = (int) $myGang->personal_shopper === (int) $m->id;
            $isMoneyMagnet = (int) $myGang->money_magnet === (int) $m->id;

            $roleLabel = 'Bendelid';
            if ($m->gang_role === 'leader' || (int) $myGang->leader_character_id === (int) $m->id) {
                $roleLabel = 'Boss / Leider';
            } elseif ($isShopper && $isMoneyMagnet) {
                $roleLabel = 'Kantine & Kas (Co-Leider)';
            } elseif ($isShopper) {
                $roleLabel = 'Kantine Smokkelaar (Co-Leider)';
            } elseif ($isMoneyMagnet) {
                $roleLabel = 'Kasbeheerder (Co-Leider)';
            } elseif ($m->gang_role === 'co_leader') {
                $roleLabel = 'Co-Leider';
            }

            return [
                'id' => $m->id,
                'name' => $m->name,
                'avatar_url' => $m->avatar_url,
                'level' => (int) $m->level,
                'respect' => (float) $m->respect,
                'respect_formatted' => number_format($m->respect, 0, ',', '.'),
                'gang_role' => $m->gang_role,
                'role_label' => $roleLabel,
                'is_shopper' => $isShopper,
                'is_money_magnet' => $isMoneyMagnet,
                'is_online' => $m->isOnline(),
                'online_label' => $m->online_status_label,
                'profile_url' => route('profile.show', $m->name),
                'message_url' => route('messages.index', ['recipient' => $m->name]),
                'last_active_at' => $m->last_active_at ? $m->last_active_at->diffForHumans() : 'Nooit',
                'last_active_timestamp' => $m->last_active_at ? $m->last_active_at->timestamp : 0,
            ];
        })->values() : collect();

        $onlineMembersCount = $myGang ? $myGang->members->filter(fn ($m) => $m->isOnline())->count() : 0;

        $defaultTab = $myGang ? 'mijn_gang' : 'ranglijst';
        $activeTab = $forceTab ?? $request->query('tab', session('active_gang_tab', $defaultTab));

        $activeWar = $myGang ? GangWar::findActiveForGang($myGang->id) : null;
        $clinicZones = ControlPoint::with(['holder', 'gang'])->get();

        return view('game.gangs', compact('character', 'realGangs', 'myGang', 'gangRankings', 'topRankings', 'hourlyWage', 'dailyWage', 'wageDetails', 'wageBreakdown', 'todayDonations', 'totalDonatedToday', 'todayRecentLogs', 'memberDonations', 'inboundAlliances', 'recentAttacks', 'gangMembersData', 'onlineMembersCount', 'activeTab', 'myInventoryItems', 'warsWon', 'warsLost', 'totalRespect', 'activeWar', 'clinicZones'));
    }

    /**
     * Dedicated Gang Members & Online status page view.
     */
    public function leden(Request $request): View
    {
        return $this->index($request, 'leden');
    }

    /**
     * Dedicated Gang Management page view.
     */
    public function beheer(Request $request): View
    {
        return $this->index($request, 'beheer');
    }

    /**
     * Dedicated Gang Settings page view.
     */
    public function instellingen(Request $request): View
    {
        return $this->index($request, 'instellingen');
    }

    /**
     * Public Gang Profile page view.
     */
    public function show(Request $request, string $gang): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $gangModel = Gang::with([
            'leader',
            'members',
            'personalShopper',
            'moneyMagnet',
            'alliances.allyGang',
            'properties',
            'npcs',
        ])
            ->where('id', $gang)
            ->orWhere('tag', $gang)
            ->orWhere('name', $gang)
            ->firstOrFail();

        $totalRespect = (float) $gangModel->members->sum('respect');
        $hourlyWage = $this->wageService->calculateHourlyWage($gangModel);
        $dailyWage = $this->wageService->calculateDailyWage($gangModel);
        $wageBreakdown = $this->wageService->getWageBreakdown($gangModel, (int) $character->gang_id === (int) $gangModel->id ? $character : null);

        $isMyGang = (int) $character->gang_id === (int) $gangModel->id;
        $isOfficer = $isMyGang && $gangModel->isOfficer($character);

        $hasPendingRequest = false;
        if (! $character->gang_id) {
            $hasPendingRequest = GangRequest::where('gang_id', $gangModel->id)
                ->where('character_id', $character->id)
                ->where('status', 'pending')
                ->exists();
        }

        $gangMembersData = $gangModel->members->sortByDesc('respect')->values()->map(function ($m) use ($gangModel) {
            $isShopper = (int) $gangModel->personal_shopper === (int) $m->id;
            $isMoneyMagnet = (int) $gangModel->money_magnet === (int) $m->id;

            $roleLabel = 'Bendelid';
            if ($m->gang_role === 'leader' || (int) $gangModel->leader_character_id === (int) $m->id) {
                $roleLabel = 'Boss / Leider';
            } elseif ($isShopper && $isMoneyMagnet) {
                $roleLabel = 'Kantine & Kas (Co-Leider)';
            } elseif ($isShopper) {
                $roleLabel = 'Kantine Smokkelaar (Co-Leider)';
            } elseif ($isMoneyMagnet) {
                $roleLabel = 'Kasbeheerder (Co-Leider)';
            } elseif ($m->gang_role === 'co_leader') {
                $roleLabel = 'Co-Leider';
            }

            return [
                'id' => $m->id,
                'name' => $m->name,
                'avatar_url' => $m->avatar_url,
                'level' => (int) $m->level,
                'respect' => (float) $m->respect,
                'respect_formatted' => number_format($m->respect, 0, ',', '.'),
                'gang_role' => $m->gang_role,
                'role_label' => $roleLabel,
                'is_shopper' => $isShopper,
                'is_money_magnet' => $isMoneyMagnet,
                'is_online' => $m->isOnline(),
                'online_label' => $m->online_status_label,
                'profile_url' => route('profile.show', $m->name),
                'message_url' => route('messages.index', ['recipient' => $m->name]),
                'last_active_at' => $m->last_active_at ? $m->last_active_at->diffForHumans() : 'Nooit',
                'last_active_timestamp' => $m->last_active_at ? $m->last_active_at->timestamp : 0,
            ];
        })->values();

        $onlineMembersCount = $gangModel->members->filter(fn ($m) => $m->isOnline())->count();

        $recentAttacks = GangAttack::with(['attackerGang', 'targetGang', 'character'])
            ->where(function ($q) use ($gangModel) {
                $q->where('attacker_gang_id', $gangModel->id)
                    ->orWhere('target_gang_id', $gangModel->id);
            })
            ->latest()
            ->take(10)
            ->get();

        $warsWon = GangAttack::where('attacker_gang_id', $gangModel->id)->where('won', true)->count()
            + GangAttack::where('target_gang_id', $gangModel->id)->where('won', false)->count();

        $warsLost = GangAttack::where('attacker_gang_id', $gangModel->id)->where('won', false)->count()
            + GangAttack::where('target_gang_id', $gangModel->id)->where('won', true)->count();

        $gang = $gangModel;

        return view('game.gang_show', compact(
            'character',
            'gang',
            'totalRespect',
            'hourlyWage',
            'dailyWage',
            'wageBreakdown',
            'isMyGang',
            'isOfficer',
            'hasPendingRequest',
            'gangMembersData',
            'onlineMembersCount',
            'recentAttacks',
            'warsWon',
            'warsLost'
        ));
    }

    /**
     * Create a new gang (costs 25 Stars or €50.000 Cash).
     */
    public function create(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen bende oprichten!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($character->gang_id) {
            return back()->with('error', 'Je bent al lid van een bende. Verlaat eerst je huidige bende.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:25', 'unique:gangs,name'],
            'tag' => ['required', 'string', 'min:2', 'max:4', 'unique:gangs,tag'],
            'specialty' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:300'],
            'payment_method' => ['nullable', 'string', 'in:stars,cash'],
        ]);

        $starCost = 25;
        $cashCost = 50000;
        $paymentMethod = $validated['payment_method'] ?? (($character->stars ?? 0) >= $starCost ? 'stars' : 'cash');

        try {
            $gang = DB::transaction(function () use ($character, $validated, $starCost, $cashCost, $paymentMethod): Gang {
                /** @var Character $lockedCharacter */
                $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

                if ($lockedCharacter->gang_id) {
                    throw new \RuntimeException('Je bent al lid van een bende. Verlaat eerst je huidige bende.');
                }

                if ($paymentMethod === 'cash') {
                    $totalFunds = ($lockedCharacter->cash ?? 0) + ($lockedCharacter->bank ?? 0);
                    if ($totalFunds < $cashCost) {
                        throw new \RuntimeException('Een bende oprichten met geld kost €'.number_format($cashCost, 0, ',', '.').' contant of van de bank. Je hebt momenteel €'.number_format($totalFunds, 0, ',', '.').'.');
                    }

                    if ($lockedCharacter->cash >= $cashCost) {
                        $lockedCharacter->cash -= $cashCost;
                    } else {
                        $remaining = $cashCost - $lockedCharacter->cash;
                        $lockedCharacter->cash = 0;
                        $lockedCharacter->bank -= $remaining;
                    }
                } else {
                    if (($lockedCharacter->stars ?? 0) < $starCost) {
                        $totalFunds = ($lockedCharacter->cash ?? 0) + ($lockedCharacter->bank ?? 0);
                        if ($totalFunds >= $cashCost) {
                            if ($lockedCharacter->cash >= $cashCost) {
                                $lockedCharacter->cash -= $cashCost;
                            } else {
                                $remaining = $cashCost - $lockedCharacter->cash;
                                $lockedCharacter->cash = 0;
                                $lockedCharacter->bank -= $remaining;
                            }
                        } else {
                            throw new \RuntimeException("Een bende oprichten kost {$starCost} Sterren ⭐ of €".number_format($cashCost, 0, ',', '.')." KliniekCash! Je hebt momenteel {$lockedCharacter->stars} sterren en €".number_format($totalFunds, 0, ',', '.').'.');
                        }
                    } else {
                        $lockedCharacter->stars -= $starCost;
                    }
                }

                $newGang = Gang::create([
                    'name' => strip_tags($validated['name']),
                    'tag' => strtoupper(strip_tags($validated['tag'])),
                    'leader_character_id' => $lockedCharacter->id,
                    'specialty' => strip_tags($validated['specialty'] ?? 'Kantine Controle'),
                    'description' => strip_tags($validated['description'] ?? 'Een nieuwe gevreesde bende in de kliniek.'),
                    'treasury' => 1000,
                    'emblem' => 'fa-crown',
                ]);

                $lockedCharacter->gang_id = $newGang->id;
                $lockedCharacter->gang_role = 'leader';
                $lockedCharacter->last_gang_wage_at = now()->startOfHour();

                // Cancel any pending join requests for this character
                GangRequest::where('character_id', $lockedCharacter->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'cancelled']);

                $perks = is_array($lockedCharacter->perks) ? $lockedCharacter->perks : [];
                if (empty($perks['gang_welcome_vip_claimed'])) {
                    $hasPermanentVip = ($lockedCharacter->is_vip && $lockedCharacter->vip_expires_at === null)
                        || $lockedCharacter->getVipTier() === 4;
                    $lockedCharacter->is_vip = true;
                    if (! $hasPermanentVip) {
                        $currentExpiry = ($lockedCharacter->vip_expires_at && $lockedCharacter->vip_expires_at->isFuture())
                            ? $lockedCharacter->vip_expires_at
                            : now();
                        $lockedCharacter->vip_expires_at = $currentExpiry->copy()->addDays(3);
                    }
                    $perks['gang_welcome_vip_claimed'] = true;
                    $lockedCharacter->perks = $perks;
                }

                $lockedCharacter->save();

                AuditLogService::log(
                    action: 'gang.create',
                    category: 'gang',
                    description: "Bende '{$newGang->name}' [{$newGang->tag}] opgericht door {$lockedCharacter->name}.",
                    character: $lockedCharacter,
                    user: $lockedCharacter->user,
                    details: [
                        'gang_id' => $newGang->id,
                        'name' => $newGang->name,
                        'tag' => $newGang->tag,
                    ]
                );

                return $newGang;
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        session()->flash('active_gang_tab', 'mijn_gang');

        return redirect()->route('gangs.index', ['tab' => 'mijn_gang'])
            ->with('success', "Gefeliciteerd! Bende '{$gang->name}' [{$gang->tag}] is succesvol opgericht! Je ontvangt direct 3 dagen gratis VIP status ⭐!");
    }

    /**
     * Join gang (redirected to requestJoin to require approval).
     */
    public function join(Gang $gang, Request $request): RedirectResponse
    {
        return $this->requestJoin($gang, $request);
    }

    /**
     * Request to join a gang.
     */
    public function requestJoin(Gang $gang, Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen lidmaatschapsverzoek indienen!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($character->gang_id) {
            return back()->with('error', 'Je bent al aangesloten bij een bende. Verlaat eerst je huidige bende.');
        }

        if (! (bool) ($gang->is_open ?? true)) {
            return back()->with('error', "Bende '{$gang->name}' accepteert momenteel geen nieuwe lidmaatschapsverzoeken (gesloten).");
        }

        if (($gang->min_respect ?? 0) > 0 && ($character->respect ?? 0) < (int) $gang->min_respect) {
            return back()->with('error', 'Je hebt minimaal '.number_format($gang->min_respect, 0, ',', '.')." respect nodig om je aan te melden bij {$gang->name}. (Jij hebt: ".number_format($character->respect ?? 0, 0, ',', '.').' respect)');
        }

        $exists = GangRequest::where('gang_id', $gang->id)
            ->where('character_id', $character->id)
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return back()->with('info', "Je hebt al een openstaande aanvraag bij bende '{$gang->name}'. Wacht tot de leiding deze beoordeelt.");
        }

        GangRequest::create([
            'gang_id' => $gang->id,
            'character_id' => $character->id,
            'message' => strip_tags((string) $request->input('message', 'Mag ik lid worden van jullie bende?')),
            'status' => 'pending',
        ]);

        if ($gang->leader) {
            // Notify leader and co-leaders via GameMessage and GameNotification
            $officers = Character::where('gang_id', $gang->id)
                ->whereIn('gang_role', ['leader', 'co_leader'])
                ->where('id', '!=', $character->id)
                ->get();

            if ($officers->isEmpty() && $gang->leader && $gang->leader->id !== $character->id) {
                $officers = collect([$gang->leader]);
            }

            foreach ($officers as $officer) {
                GameNotification::create([
                    'character_id' => $officer->id,
                    'type' => 'gang',
                    'icon' => 'fa-user-plus',
                    'title' => 'Nieuw lidmaatschapsverzoek!',
                    'message' => "{$character->name} heeft een verzoek ingediend om lid te worden van '{$gang->name}'!",
                ]);

                GameMessage::create([
                    'recipient_id' => $officer->id,
                    'sender_id' => $character->id,
                    'sender_name' => $character->name,
                    'subject' => "📩 Nieuwe lidmaatschapsaanvraag voor {$gang->name}",
                    'content' => "Patiënt {$character->name} wil zich graag aansluiten bij jouw bende {$gang->name}. Beheer verzoeken via Bende Beheer!",
                    'type' => 'gang',
                ]);
            }
        }

        return back()->with('success', "Je lidmaatschapsverzoek is ingediend bij bende '{$gang->name}'! De leiding zal je aanvraag beoordelen.");
    }

    /**
     * Handle join request (Accept / Reject).
     */
    public function handleRequest(GangRequest $requestRecord, Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $gang = $character->gang;
        if (! $gang || ! $gang->isOfficer($character) || (int) $requestRecord->gang_id !== (int) $gang->id) {
            return back()->with('error', 'Geen bevoegdheid.');
        }

        if ($requestRecord->status !== 'pending') {
            return back()
                ->with('active_gang_tab', 'beheer')
                ->with('beheer_tab', 'verzoeken')
                ->with('error', 'Deze aanvraag is al afgehandeld.');
        }

        $action = $request->input('action') ?? $request->input('decision'); // 'accept' or 'reject'

        if ($action === 'accept') {
            return DB::transaction(function () use ($requestRecord, $gang, $character): RedirectResponse {
                /** @var GangRequest|null $lockedRequest */
                $lockedRequest = GangRequest::where('id', $requestRecord->id)->lockForUpdate()->first();
                if (! $lockedRequest || $lockedRequest->status !== 'pending') {
                    return back()
                        ->with('active_gang_tab', 'beheer')
                        ->with('beheer_tab', 'verzoeken')
                        ->with('error', 'Deze aanvraag is al afgehandeld.');
                }

                /** @var Gang|null $lockedGang */
                $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();
                if (! $lockedGang) {
                    return back()->with('error', 'Bende niet gevonden.');
                }

                $maxMembers = (int) ($lockedGang->max_members ?? 10);
                if ($lockedGang->members()->count() >= $maxMembers) {
                    return back()
                        ->with('active_gang_tab', 'beheer')
                        ->with('beheer_tab', 'verzoeken')
                        ->with('error', "De bende zit vol! De maximale capaciteit is {$maxMembers} leden.");
                }

                /** @var Character|null $candidate */
                $candidate = Character::where('id', $lockedRequest->character_id)->lockForUpdate()->first();
                if (! $candidate) {
                    $lockedRequest->status = 'rejected';
                    $lockedRequest->save();

                    return back()
                        ->with('active_gang_tab', 'beheer')
                        ->with('beheer_tab', 'verzoeken')
                        ->with('error', 'Kandidaat niet gevonden.');
                }

                if ($candidate->gang_id) {
                    $lockedRequest->status = 'rejected';
                    $lockedRequest->save();

                    return back()
                        ->with('active_gang_tab', 'beheer')
                        ->with('beheer_tab', 'verzoeken')
                        ->with('error', "{$candidate->name} is inmiddels al lid van een andere bende.");
                }

                if (($lockedGang->min_respect ?? 0) > 0 && ($candidate->respect ?? 0) < (int) $lockedGang->min_respect) {
                    return back()
                        ->with('active_gang_tab', 'beheer')
                        ->with('beheer_tab', 'verzoeken')
                        ->with('error', 'Deze kandidaat voldoet niet meer aan de toelatingseis van '.number_format($lockedGang->min_respect, 0, ',', '.').' respect! (Kandidaat heeft: '.number_format($candidate->respect ?? 0, 0, ',', '.').' respect)');
                }

                $candidate->gang_id = $lockedGang->id;
                $candidate->gang_role = 'member';
                $candidate->last_gang_wage_at = now()->startOfHour();
                $candidatePerks = is_array($candidate->perks) ? $candidate->perks : [];
                $grantedWelcomeVip = empty($candidatePerks['gang_welcome_vip_claimed']);
                if ($grantedWelcomeVip) {
                    $hasPermanentVip = ($candidate->is_vip && $candidate->vip_expires_at === null)
                        || $candidate->getVipTier() === 4;
                    $candidate->is_vip = true;
                    if (! $hasPermanentVip) {
                        $currentExpiry = ($candidate->vip_expires_at && $candidate->vip_expires_at->isFuture())
                            ? $candidate->vip_expires_at
                            : now();
                        $candidate->vip_expires_at = $currentExpiry->copy()->addDays(3);
                    }
                    $candidatePerks['gang_welcome_vip_claimed'] = true;
                    $candidate->perks = $candidatePerks;
                }
                $candidate->save();

                GameMessage::create([
                    'recipient_id' => $candidate->id,
                    'sender_id' => $character->id,
                    'sender_name' => $lockedGang->name,
                    'subject' => $grantedWelcomeVip
                        ? "🎉 Je aanvraag voor {$lockedGang->name} is geaccepteerd (+3 Dagen Gratis VIP)!"
                        : "🎉 Je aanvraag voor {$lockedGang->name} is geaccepteerd!",
                    'content' => $grantedWelcomeVip
                        ? "Welkom bij de bende! Je bent nu officieel lid van {$lockedGang->name}. Als welkomstgeschenk is er direct 3 Dagen Gratis VIP geactiveerd op je account!"
                        : "Welkom bij de bende! Je bent nu officieel lid van {$lockedGang->name}.",
                    'type' => 'gang',
                ]);

                GameNotification::create([
                    'character_id' => $candidate->id,
                    'type' => 'gang',
                    'icon' => 'fa-users',
                    'title' => 'Toegelaten tot bende!',
                    'message' => "Gefeliciteerd! Je aanvraag voor '{$lockedGang->name}' is geaccepteerd. Je bent nu lid!",
                ]);

                $lockedRequest->status = 'accepted';
                $lockedRequest->save();

                // Cancel any other pending requests for this character
                GangRequest::where('character_id', $candidate->id)
                    ->where('id', '!=', $lockedRequest->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'rejected']);

                return back()
                    ->with('active_gang_tab', 'beheer')
                    ->with('beheer_tab', 'verzoeken')
                    ->with('success', "Aanvraag geaccepteerd! {$candidate->name} is nu lid.");
            });
        }

        DB::transaction(function () use ($requestRecord, $gang): void {
            /** @var GangRequest|null $lockedRequest */
            $lockedRequest = GangRequest::where('id', $requestRecord->id)->lockForUpdate()->first();
            if ($lockedRequest && $lockedRequest->status === 'pending') {
                $lockedRequest->status = 'rejected';
                $lockedRequest->save();

                if ($candidate = $lockedRequest->character) {
                    GameNotification::create([
                        'character_id' => $candidate->id,
                        'type' => 'gang',
                        'icon' => 'fa-xmark',
                        'title' => 'Aanvraag afgewezen',
                        'message' => "Je verzoek om lid te worden van bende '{$gang->name}' is afgewezen.",
                    ]);
                }
            }
        });

        return back()
            ->with('active_gang_tab', 'beheer')
            ->with('beheer_tab', 'verzoeken')
            ->with('info', 'Aanvraag afgewezen.');
    }

    /**
     * Leave the current gang.
     */
    public function leave(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character->gang_id) {
            return back()->with('error', 'Je bent momenteel geen lid van een bende.');
        }

        return DB::transaction(function () use ($character): RedirectResponse {
            /** @var Character $lockedChar */
            $lockedChar = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $lockedChar || ! $lockedChar->gang_id) {
                return back()->with('error', 'Je bent momenteel geen lid van een bende.');
            }

            /** @var Gang|null $gang */
            $gang = Gang::where('id', $lockedChar->gang_id)->lockForUpdate()->first();
            $leaveFee = 0;

            // Leave fee multiplier deduction (only if not disbanding as leader)
            if ($gang && $lockedChar->gang_role !== 'leader') {
                $hourlyWage = $this->wageService->calculateHourlyWage($gang);
                $multiplier = min(50, max(0, (int) ($gang->leave_fee_multiplier ?? 0)));
                $leaveFee = $hourlyWage * $multiplier;

                if ($leaveFee > 0) {
                    $totalFunds = (int) $lockedChar->cash + (int) ($lockedChar->bank ?? 0);
                    if ($totalFunds < $leaveFee) {
                        return back()->with('error', 'Je kunt de bende niet verlaten: de vertrekboete bedraagt €'.number_format($leaveFee, 0, ',', '.')." ({$multiplier}x uurloon). Je hebt momenteel slechts €".number_format($totalFunds, 0, ',', '.').' totaal vermogen (contant + bank)!');
                    }

                    if ($lockedChar->cash >= $leaveFee) {
                        $lockedChar->cash -= $leaveFee;
                    } else {
                        $remaining = $leaveFee - $lockedChar->cash;
                        $lockedChar->cash = 0;
                        $lockedChar->bank -= $remaining;
                    }
                    $gang->treasury += $leaveFee;
                    $gang->save();
                }
            }

            if (($lockedChar->gang_role === 'leader' || ($gang && (int) $gang->leader_character_id === (int) $lockedChar->id)) && $gang) {
                $nextMember = $gang->members()
                    ->where('id', '!=', $lockedChar->id)
                    ->orderByRaw("CASE WHEN gang_role = 'co_leader' THEN 0 ELSE 1 END")
                    ->orderByDesc('respect')
                    ->lockForUpdate()
                    ->first();
                if ($nextMember) {
                    $gang->leader_character_id = $nextMember->id;
                    $gang->save();
                    $nextMember->gang_role = 'leader';
                    $nextMember->save();
                } else {
                    CharacterVehicle::where('gang_id', $gang->id)
                        ->where('in_gang_garage', true)
                        ->update([
                            'in_gang_garage' => false,
                            'gang_id' => null,
                            'stored_at' => null,
                            'stored_by_character_id' => null,
                        ]);
                    AuditLogService::log(
                        action: 'gang.disband',
                        category: 'gang',
                        description: "Bende '{$gang->name}' (#{$gang->id}) opgeheven omdat het laatste lid de bende heeft verlaten.",
                        character: $lockedChar,
                        user: $lockedChar->user,
                        details: [
                            'gang_id' => $gang->id,
                            'gang_name' => $gang->name,
                        ]
                    );
                    $gang->delete();
                }
            }

            // Return all gang-stored vehicles owned by the departing character back to their personal garage
            CharacterVehicle::where('character_id', $lockedChar->id)
                ->where('in_gang_garage', true)
                ->update([
                    'in_gang_garage' => false,
                    'gang_id' => null,
                    'stored_at' => null,
                    'stored_by_character_id' => null,
                ]);

            $this->wageService->payoutPendingWages($lockedChar);
            $lockedChar->gang_id = null;
            $lockedChar->gang_role = 'none';
            $lockedChar->last_gang_wage_at = null;
            // Gang members receive no allowance; restart the clock so leaving does not pay a 24-hour backlog.
            $lockedChar->last_allowance_at = now();
            $lockedChar->save();

            if ($gang && $gang->exists) {
                $this->clearAppointedRoles($gang, $lockedChar);
            }

            if ($leaveFee > 0) {
                return redirect()->route('gangs.index')->with('success', 'Je hebt de bende verlaten. Er is €'.number_format($leaveFee, 0, ',', '.').' overgemaakt naar de bende kluis als vertrekboete.');
            }

            return redirect()->route('gangs.index')->with('success', 'Je hebt de bende succesvol verlaten.');
        });
    }

    /**
     * Donate cash to gang treasury.
     */
    public function donate(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character->gang_id) {
            return back()->with('error', 'Je bent geen lid van een bende.');
        }

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen geld doneren aan de bende!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:50', 'max:1000000000000'],
        ], [
            'amount.min' => 'Je moet minimaal €50 doneren.',
            'amount.max' => 'Het donatiebedrag is te hoog.',
            'amount.integer' => 'Voer een geldig heel getal in.',
            'amount.required' => 'Vul een donatiebedrag in.',
        ]);

        $amount = (int) $validated['amount'];

        return DB::transaction(function () use ($character, $amount) {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            if (! $lockedCharacter || $lockedCharacter->isImprisoned() || $lockedCharacter->isComatose() || $lockedCharacter->needsReviveSerum() || $lockedCharacter->cash < $amount) {
                return back()
                    ->with('active_gang_tab', 'doneren')
                    ->with('error', 'Je hebt niet genoeg contant geld om dit bedrag te doneren of je kunt momenteel niet doneren.');
            }

            /** @var Gang $lockedGang */
            $lockedGang = Gang::where('id', $lockedCharacter->gang_id)->lockForUpdate()->first();
            if (! $lockedGang) {
                return back()->with('error', 'Je bent geen lid van een geldige bende.');
            }

            // Deduct cash and calculate balanced respect (capped at 5,000 per donation to prevent respect-overflow)
            $lockedCharacter->cash -= $amount;
            $respectGained = min(5000, (int) ceil($amount / 2000));
            $lockedCharacter->addRespect($respectGained);
            $lockedCharacter->save();

            // Add cash to gang treasury
            $lockedGang->treasury += $amount;
            $lockedGang->save();

            $donation = GangDonation::create([
                'gang_id' => $lockedGang->id,
                'character_id' => $lockedCharacter->id,
                'amount' => $amount,
            ]);

            AuditLogService::log(
                action: 'gang_donation',
                category: 'gang',
                description: 'Donatie van €'.number_format($amount, 0, ',', '.')." aan bende {$lockedGang->name} (#{$lockedGang->id})",
                character: $lockedCharacter,
                user: $lockedCharacter->user,
                amount: $amount,
                details: [
                    'source_key' => "gang_don_{$donation->id}",
                    'gang_id' => $lockedGang->id,
                    'gang_name' => $lockedGang->name,
                    'respect_gained' => $respectGained,
                ]
            );

            return back()
                ->with('active_gang_tab', 'doneren')
                ->with('success', 'Je hebt €'.number_format($amount, 0, ',', '.').' contant gedoneerd aan de bende kas! (+'.$respectGained.' respect)');
        });
    }

    /**
     * Update gang settings (Leader / Co-leader).
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $gang = $character->gang;
        if (! $gang || ! $gang->isOfficer($character)) {
            return back()->with('error', 'Alleen bendebeheerders (leider, co-leider, kantine smokkelaar of kasbeheerder) kunnen instellingen wijzigen!');
        }

        $actionType = $request->input('action_type');

        if ($actionType === 'name' || $actionType === 'rename') {
            if ($character->gang_role !== 'leader') {
                return back()
                    ->with('active_gang_tab', 'instellingen')
                    ->with('settings_tab', 'algemeen')
                    ->with('error', 'Alleen de Bendeleider (oprichter) kan de naam en tag van de bende wijzigen!');
            }

            $validated = $request->validate([
                'name' => ['required', 'string', 'min:3', 'max:35', Rule::unique('gangs', 'name')->ignore($gang->id)],
                'tag' => ['required', 'string', 'min:2', 'max:5', 'alpha_num', Rule::unique('gangs', 'tag')->ignore($gang->id)],
            ], [
                'name.required' => 'Vul een geldige bendenaam in.',
                'name.min' => 'De bendenaam moet minimaal 3 tekens lang zijn.',
                'name.max' => 'De bendenaam mag maximaal 35 tekens lang zijn.',
                'name.unique' => 'Deze bendenaam is al in gebruik door een andere factie.',
                'tag.required' => 'Vul een geldige bende tag in.',
                'tag.min' => 'De bende tag moet minimaal 2 tekens lang zijn.',
                'tag.max' => 'De bende tag mag maximaal 5 tekens lang zijn.',
                'tag.alpha_num' => 'De bende tag mag alleen letters en cijfers bevatten.',
                'tag.unique' => 'Deze tag is al in gebruik door een andere bende.',
            ]);

            $oldName = $gang->name;
            $gang->update([
                'name' => trim($validated['name']),
                'tag' => strtoupper(trim($validated['tag'])),
            ]);

            return back()
                ->with('active_gang_tab', 'instellingen')
                ->with('settings_tab', 'algemeen')
                ->with('success', "Bendenaam succesvol bijgewerkt van '{$oldName}' naar '{$gang->name}' [{$gang->tag}]!");
        }

        if ($actionType === 'general_info' || $actionType === 'general') {
            $validated = $request->validate([
                'motd' => ['nullable', 'string', 'max:150'],
                'specialty' => ['nullable', 'string', 'max:50'],
                'emblem' => ['nullable', 'string', 'max:50'],
            ]);

            $gang->update([
                'motd' => strip_tags($validated['motd'] ?? ''),
                'specialty' => strip_tags($validated['specialty'] ?? $gang->specialty),
                'emblem' => strip_tags($validated['emblem'] ?? $gang->emblem),
            ]);

            return back()
                ->with('active_gang_tab', 'instellingen')
                ->with('settings_tab', 'algemeen')
                ->with('success', 'Bende gegevens & slogan succesvol bijgewerkt!');
        }

        if ($actionType === 'avatar') {
            $request->validate([
                'avatar' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:10240'],
            ], [
                'avatar.required' => 'Selecteer eerst een afbeelding om te uploaden.',
                'avatar.file' => 'Het geüploade bestand is ongeldig.',
                'avatar.image' => 'Het bestand moet een geldige afbeelding zijn (JPG, PNG, WEBP of GIF).',
                'avatar.mimes' => 'Alleen JPG, PNG, WEBP of GIF bestanden zijn toegestaan.',
                'avatar.max' => 'De gekozen avatar is te groot (maximaal 10MB).',
                'avatar.uploaded' => 'Het uploaden van de avatar is mislukt. Controleer of de afbeelding kleiner is dan 10MB en probeer het opnieuw.',
            ]);

            try {
                $path = $request->file('avatar')->store('gangs/avatars', 'public');
                $gang->update(['avatar' => 'storage/'.$path]);

                return back()
                    ->with('active_gang_tab', 'instellingen')
                    ->with('settings_tab', 'visueel')
                    ->with('success', 'Bende avatar succesvol bijgewerkt!');
            } catch (\Throwable $e) {
                return back()
                    ->with('active_gang_tab', 'instellingen')
                    ->with('settings_tab', 'visueel')
                    ->with('error', 'Opslaan van avatar mislukt. Probeer een andere afbeelding.');
            }
        }

        if ($actionType === 'banner') {
            $request->validate([
                'banner' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:12288'],
            ], [
                'banner.required' => 'Selecteer eerst een achtergrond banner om te uploaden.',
                'banner.file' => 'Het geüploade bestand is ongeldig.',
                'banner.image' => 'Het bestand moet een geldige afbeelding zijn (JPG, PNG, WEBP of GIF).',
                'banner.mimes' => 'Alleen JPG, PNG, WEBP of GIF bestanden zijn toegestaan.',
                'banner.max' => 'De gekozen banner is te groot (maximaal 12MB).',
                'banner.uploaded' => 'Het uploaden van de banner is mislukt. Controleer of het bestand kleiner is dan 12MB en probeer het opnieuw.',
            ]);

            try {
                $path = $request->file('banner')->store('gangs/banners', 'public');
                $gang->update(['banner' => 'storage/'.$path]);

                return back()
                    ->with('active_gang_tab', 'instellingen')
                    ->with('settings_tab', 'visueel')
                    ->with('success', 'Bende achtergrond succesvol bijgewerkt!');
            } catch (\Throwable $e) {
                return back()
                    ->with('active_gang_tab', 'instellingen')
                    ->with('settings_tab', 'visueel')
                    ->with('error', 'Opslaan van banner mislukt. Probeer een andere afbeelding.');
            }
        }

        if ($actionType === 'management') {
            if ($character->gang_role !== 'leader') {
                return back()
                    ->with('active_gang_tab', 'instellingen')
                    ->with('settings_tab', 'rollen')
                    ->with('error', 'Alleen de Bendeleider (oprichter) kan het management wijzigen!');
            }

            $validated = $request->validate([
                'leader_id' => ['nullable', Rule::exists('characters', 'id')->where('gang_id', $gang->id)],
                'boss_babe_id' => ['nullable', Rule::exists('characters', 'id')->where('gang_id', $gang->id)],
                'co_leader_id' => ['nullable', Rule::exists('characters', 'id')->where('gang_id', $gang->id)],
                'personal_shopper_id' => ['nullable', Rule::exists('characters', 'id')->where('gang_id', $gang->id)],
                'money_magnet_id' => ['nullable', Rule::exists('characters', 'id')->where('gang_id', $gang->id)],
            ]);

            DB::transaction(function () use ($gang, $character, $validated): void {
                /** @var Gang|null $lockedGang */
                $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();
                /** @var Character|null $lockedCharacter */
                $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

                if (! $lockedGang || ! $lockedCharacter) {
                    return;
                }

                $oldLeaderId = (int) $lockedGang->leader_character_id;
                $newLeaderId = (int) ($validated['leader_id'] ?? $validated['boss_babe_id'] ?? $lockedGang->leader_character_id);
                $handoverOutgoingLeaderId = null;

                if ($newLeaderId && $newLeaderId !== $oldLeaderId) {
                    $newLeader = Character::where('id', $newLeaderId)->where('gang_id', $lockedGang->id)->lockForUpdate()->first();
                    if ($newLeader) {
                        $lockedCharacter->gang_role = 'co_leader';
                        $lockedCharacter->save();
                        $newLeader->gang_role = 'leader';
                        $newLeader->save();
                        $lockedGang->leader_character_id = $newLeader->id;
                        $handoverOutgoingLeaderId = (int) $lockedCharacter->id;
                    }
                }

                $newShopperId = ! empty($validated['personal_shopper_id']) ? (int) $validated['personal_shopper_id'] : null;
                $newMagnetId = ! empty($validated['money_magnet_id']) ? (int) $validated['money_magnet_id'] : null;
                $newCoLeaderId = ! empty($validated['co_leader_id']) ? (int) $validated['co_leader_id'] : null;

                $lockedGang->personal_shopper = $newShopperId;
                $lockedGang->money_magnet = $newMagnetId;
                $lockedGang->save();

                // All members assigned to key roles must be co_leader (unless they are the gang leader)
                $targetCoLeaderIds = array_values(array_unique(array_filter(
                    [$newCoLeaderId, $newShopperId, $newMagnetId, $handoverOutgoingLeaderId],
                    fn ($id) => ! empty($id) && (int) $id !== (int) $lockedGang->leader_character_id
                )));

                foreach ($targetCoLeaderIds as $cid) {
                    $c = Character::where('id', $cid)->where('gang_id', $lockedGang->id)->lockForUpdate()->first();
                    if ($c && $c->gang_role !== 'leader') {
                        $c->gang_role = 'co_leader';
                        $c->save();
                    }
                }

                // Demote previously appointed co-leaders who are no longer in any key role
                Character::where('gang_id', $lockedGang->id)
                    ->where('gang_role', 'co_leader')
                    ->where('id', '!=', (int) $lockedGang->leader_character_id)
                    ->when(! empty($targetCoLeaderIds), fn ($q) => $q->whereNotIn('id', $targetCoLeaderIds))
                    ->update(['gang_role' => 'member']);
            });

            return back()
                ->with('active_gang_tab', 'instellingen')
                ->with('settings_tab', 'rollen')
                ->with('success', 'Bende rollen en management succesvol bijgewerkt! Aangewezen leden hebben direct co-op rechten.');
        }

        if ($actionType === 'leave_fee') {
            // The leave fee decides whether members can afford to leave, so only the leader may set it.
            if ($character->gang_role !== 'leader') {
                return back()
                    ->with('active_gang_tab', 'instellingen')
                    ->with('settings_tab', 'toelating')
                    ->with('error', 'Alleen de Bendeleider kan de verlatingskosten wijzigen!');
            }

            $validated = $request->validate([
                'leave_fee_multiplier' => ['required', 'integer', 'min:0', 'max:50'],
            ]);

            $gang->update([
                'leave_fee_multiplier' => (int) $validated['leave_fee_multiplier'],
            ]);

            return back()
                ->with('active_gang_tab', 'instellingen')
                ->with('settings_tab', 'toelating')
                ->with('success', 'Verlatingskosten succesvol gewijzigd (maximaal 50x uurloon)!');
        }

        if ($actionType === 'min_respect') {
            $validated = $request->validate([
                'min_respect' => ['required', 'integer', 'min:0'],
            ]);

            $gang->update([
                'min_respect' => (int) $validated['min_respect'],
            ]);

            return back()
                ->with('active_gang_tab', 'instellingen')
                ->with('settings_tab', 'toelating')
                ->with('success', 'Toelatingseis (Minimaal Respect) succesvol ingesteld op '.number_format((int) $validated['min_respect'], 0, ',', '.').' respect!');
        }

        if ($actionType === 'profile') {
            $validated = $request->validate([
                'description' => ['nullable', 'string', 'max:5000'],
            ]);

            $gang->update([
                'description' => strip_tags($validated['description'] ?? ''),
            ]);

            return back()
                ->with('active_gang_tab', 'instellingen')
                ->with('settings_tab', 'manifest')
                ->with('success', 'Bende profieltekst succesvol opgeslagen!');
        }

        if ($actionType === 'requests_toggle' || $actionType === 'is_open') {
            $isOpen = $request->boolean('is_open');
            $gang->update([
                'is_open' => $isOpen,
            ]);

            return back()
                ->with('active_gang_tab', 'instellingen')
                ->with('settings_tab', 'toelating')
                ->with('success', $isOpen
                    ? 'Lidmaatschapsverzoeken zijn ingeschakeld. Spelers kunnen zich nu aanmelden via de ranglijst!'
                    : 'Lidmaatschapsverzoeken zijn uitgeschakeld. Je bende is nu gesloten voor nieuwe aanmeldingen.'
                );
        }

        $validated = $request->validate([
            'description' => ['nullable', 'string', 'max:500'],
            'specialty' => ['nullable', 'string', 'max:50'],
            'emblem' => ['nullable', 'string', 'max:50'],
            'motd' => ['nullable', 'string', 'max:150'],
            'is_open' => ['nullable', 'boolean'],
        ]);

        $gang->update([
            'description' => strip_tags($validated['description'] ?? $gang->description),
            'specialty' => strip_tags($validated['specialty'] ?? $gang->specialty),
            'emblem' => strip_tags($validated['emblem'] ?? $gang->emblem),
            'motd' => strip_tags($validated['motd'] ?? $gang->motd),
            'is_open' => $request->has('is_open') ? (bool) $request->input('is_open') : $gang->is_open,
        ]);

        return back()
            ->with('active_gang_tab', 'instellingen')
            ->with('settings_tab', 'algemeen')
            ->with('success', 'Bende instellingen zijn succesvol bijgewerkt!');
    }

    /**
     * Kick a member from the gang.
     */
    public function kick(Character $member): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $gang = $character->gang;
        if (! $gang || ! $gang->isOfficer($character)) {
            return back()->with('error', 'Je hebt geen bevoegdheid om leden te verwijderen!');
        }

        return DB::transaction(function () use ($member, $character, $gang): RedirectResponse {
            /** @var Gang|null $lockedGang */
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();
            /** @var Character|null $lockedOfficer */
            $lockedOfficer = Character::where('id', $character->id)->lockForUpdate()->first();
            /** @var Character|null $lockedMember */
            $lockedMember = Character::where('id', $member->id)->lockForUpdate()->first();

            if (! $lockedGang || ! $lockedOfficer || ! $lockedMember || (int) $lockedMember->gang_id !== (int) $lockedGang->id) {
                return back()->with('error', 'Deze patiënt zit niet (meer) in jouw bende.');
            }

            if (! $lockedGang->isOfficer($lockedOfficer)) {
                return back()->with('error', 'Je hebt geen bevoegdheid om leden te verwijderen!');
            }

            if ((int) $lockedMember->id === (int) $lockedOfficer->id || $lockedMember->gang_role === 'leader' || (int) $lockedGang->leader_character_id === (int) $lockedMember->id) {
                return back()->with('error', 'Je kunt de bendeleider niet verwijderen.');
            }

            // A co-leader may not remove another co-leader; only the leader decides about the management team.
            if ($lockedMember->gang_role === 'co_leader' && $lockedOfficer->gang_role !== 'leader') {
                return back()->with('error', 'Alleen de bendeleider kan een co-leider uit de bende zetten.');
            }

            // Return all gang-stored vehicles owned by the kicked member back to their personal garage
            CharacterVehicle::where('character_id', $lockedMember->id)
                ->where('in_gang_garage', true)
                ->update([
                    'in_gang_garage' => false,
                    'gang_id' => null,
                    'stored_at' => null,
                    'stored_by_character_id' => null,
                ]);

            $this->wageService->payoutPendingWages($lockedMember);
            $lockedMember->gang_id = null;
            $lockedMember->gang_role = 'none';
            $lockedMember->last_gang_wage_at = null;
            // Gang members receive no allowance; restart the clock so a kick does not pay a 24-hour backlog.
            $lockedMember->last_allowance_at = now();
            $lockedMember->save();

            $this->clearAppointedRoles($lockedGang, $lockedMember);

            AuditLogService::log(
                action: 'gang.kick',
                category: 'gang',
                description: "{$lockedMember->name} is uit bende {$lockedGang->name} gezet door {$lockedOfficer->name}.",
                character: $lockedOfficer,
                user: $lockedOfficer->user,
                details: [
                    'gang_id' => $lockedGang->id,
                    'kicked_character_id' => $lockedMember->id,
                ]
            );

            GameMessage::create([
                'recipient_id' => $lockedMember->id,
                'sender_id' => $lockedOfficer->id,
                'sender_name' => $lockedGang->name,
                'subject' => "⚠️ Je bent verwijderd uit bende {$lockedGang->name}",
                'content' => "Patiënt {$lockedMember->name},\n\nJe bent zojuist door {$lockedOfficer->name} uit de factie '{$lockedGang->name}' gezet.",
                'type' => 'gang',
            ]);

            return back()
                ->with('active_gang_tab', 'beheer')
                ->with('beheer_tab', 'leden')
                ->with('success', "{$lockedMember->name} is uit de bende verwijderd.");
        });
    }

    /**
     * Promote / Demote a member (Leader only).
     */
    public function promote(Character $member, Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $gang = $character->gang;
        if (! $gang || $character->gang_role !== 'leader') {
            return back()->with('error', 'Alleen de bendeleider kan rangen en leiderschap toewijzen!');
        }

        $role = $request->input('role');
        if (! in_array($role, ['co_leader', 'member', 'leader'], true)) {
            return back()->with('error', 'Ongeldige rol gekozen.');
        }

        return DB::transaction(function () use ($gang, $character, $member, $role): RedirectResponse {
            /** @var Gang|null $lockedGang */
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();
            /** @var Character|null $lockedLeader */
            $lockedLeader = Character::where('id', $character->id)->lockForUpdate()->first();
            /** @var Character|null $lockedMember */
            $lockedMember = Character::where('id', $member->id)->lockForUpdate()->first();

            if (! $lockedGang || ! $lockedLeader || ! $lockedMember || (int) $lockedMember->gang_id !== (int) $lockedGang->id) {
                return back()->with('error', 'Deze patiënt zit niet (meer) in jouw bende.');
            }

            if ($lockedLeader->gang_role !== 'leader' && (int) $lockedGang->leader_character_id !== (int) $lockedLeader->id) {
                return back()->with('error', 'Alleen de bendeleider kan rangen en leiderschap toewijzen!');
            }

            if ((int) $lockedMember->id === (int) $lockedLeader->id) {
                return back()->with('error', 'Je kunt je eigen leidersrol niet verlagen. Draag eerst het leiderschap over aan een ander lid.');
            }

            if ($role === 'leader') {
                $lockedGang->leader_character_id = $lockedMember->id;
                $lockedGang->save();

                $lockedLeader->gang_role = 'co_leader';
                $lockedLeader->save();

                $lockedMember->gang_role = 'leader';
                $lockedMember->save();

                return back()
                    ->with('active_gang_tab', 'beheer')
                    ->with('beheer_tab', 'leden')
                    ->with('success', "Leiderschap van {$lockedGang->name} overgedragen aan {$lockedMember->name}!");
            }

            $lockedMember->gang_role = $role;
            $lockedMember->save();

            if ($role === 'member') {
                $this->clearAppointedRoles($lockedGang, $lockedMember);
            }

            $roleTitle = $role === 'co_leader' ? 'Co-Leider' : 'Gewoon Lid';

            return back()
                ->with('active_gang_tab', 'beheer')
                ->with('beheer_tab', 'leden')
                ->with('success', "{$lockedMember->name} is nu ingesteld als {$roleTitle}!");
        });
    }

    /**
     * Withdraw cash from gang treasury (Leader only).
     */
    public function withdraw(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $gang = $character->gang;
        if (! $gang || (! in_array($character->gang_role, ['leader', 'co_leader'], true) && (int) $gang->money_magnet !== (int) $character->id)) {
            return back()->with('error', 'Alleen de bendeleider, co-leider of de aangewezen Kasbeheerder kan geld uit de kas opnemen!');
        }

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen geld opnemen uit de bendekas!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:100', 'max:1000000000000'],
        ]);

        $amount = (int) $validated['amount'];

        return DB::transaction(function () use ($gang, $character, $amount): RedirectResponse {
            /** @var Gang $lockedGang */
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            if (! $lockedCharacter || $lockedCharacter->isImprisoned() || $lockedCharacter->isComatose() || $lockedCharacter->needsReviveSerum()) {
                return back()->with('error', 'Je bent momenteel niet in staat om geld op te nemen.');
            }

            if (! $lockedGang || (int) $lockedCharacter->gang_id !== (int) $lockedGang->id || (! in_array($lockedCharacter->gang_role, ['leader', 'co_leader'], true) && (int) $lockedGang->money_magnet !== (int) $lockedCharacter->id)) {
                return back()->with('error', 'Alleen de bendeleider, co-leider of de aangewezen Kasbeheerder kan geld uit de kas opnemen!');
            }

            if ($lockedGang->treasury < $amount) {
                return back()->with('error', 'Er zit niet genoeg geld in de bende kas.');
            }

            $lockedGang->treasury -= $amount;
            $lockedGang->save();

            $lockedCharacter->cash += $amount;
            $lockedCharacter->save();

            AuditLogService::log(
                action: 'gang_withdrawal',
                category: 'gang',
                description: 'Opname van €'.number_format($amount, 0, ',', '.')." uit bende kas van {$lockedGang->name} (#{$lockedGang->id})",
                character: $lockedCharacter,
                user: $lockedCharacter->user,
                amount: $amount,
                details: [
                    'gang_id' => $lockedGang->id,
                    'gang_name' => $lockedGang->name,
                    'treasury_after' => $lockedGang->treasury,
                    'role' => $lockedCharacter->gang_role,
                ]
            );

            return back()
                ->with('active_gang_tab', 'beheer')
                ->with('beheer_tab', 'instellingen')
                ->with('success', 'Je hebt €'.number_format($amount, 0, ',', '.').' opgenomen uit de bende kas!');
        });
    }

    /**
     * Send mass broadcast message to all gang members (Leader / Co-leader).
     */
    public function massMessage(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $gang = $character->gang;
        if (! $gang || ! $gang->isOfficer($character)) {
            return back()->with('error', 'Alleen bendebeheerders (leider, co-leider, kantine smokkelaar of kasbeheerder) kunnen een massabericht sturen!');
        }

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $members = $gang->members()->where('id', '!=', $character->id)->get();

        foreach ($members as $m) {
            GameMessage::create([
                'recipient_id' => $m->id,
                'sender_id' => $character->id,
                'sender_name' => "{$character->name} [{$gang->name}]",
                'subject' => "📢 [Bende Broadcast] {$validated['subject']}",
                'content' => $validated['message'],
                'type' => 'gang',
            ]);
        }

        return back()
            ->with('active_gang_tab', 'beheer')
            ->with('beheer_tab', 'massa_bericht')
            ->with('success', "Massabericht succesvol verzonden naar alle {$members->count()} bendeleden!");
    }

    /**
     * Add alliance with another gang.
     */
    public function addAlliance(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $gang = $character->gang;
        if (! $gang || $character->gang_role !== 'leader') {
            return back()->with('error', 'Alleen de bendeleider kan bondgenootschappen sluiten!');
        }

        $validated = $request->validate([
            'ally_gang_id' => ['required', 'exists:gangs,id'],
        ]);

        if ((int) $validated['ally_gang_id'] === $gang->id) {
            return back()->with('error', 'Je kunt geen bondgenootschap met jezelf sluiten!');
        }

        GangAlliance::firstOrCreate([
            'gang_id' => $gang->id,
            'ally_gang_id' => $validated['ally_gang_id'],
        ]);

        return back()
            ->with('active_gang_tab', 'beheer')
            ->with('beheer_tab', 'bondgenoten')
            ->with('success', 'Bondgenootschap succesvol gesloten!');
    }

    /**
     * Remove alliance.
     */
    public function removeAlliance(GangAlliance $alliance): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $gang = $character->gang;
        if (! $gang || $character->gang_role !== 'leader' || $alliance->gang_id !== $gang->id) {
            return back()->with('error', 'Geen bevoegdheid.');
        }

        $alliance->delete();

        return back()
            ->with('active_gang_tab', 'beheer')
            ->with('beheer_tab', 'bondgenoten')
            ->with('success', 'Bondgenootschap ontbonden.');
    }

    /**
     * Disband the entire gang (Leader only).
     */
    public function disband(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $gangName = DB::transaction(function () use ($character): ?string {
            /** @var Character|null $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $lockedCharacter || ! $lockedCharacter->gang_id || $lockedCharacter->gang_role !== 'leader') {
                return null;
            }

            /** @var Gang|null $lockedGang */
            $lockedGang = Gang::where('id', $lockedCharacter->gang_id)->lockForUpdate()->first();
            if (! $lockedGang) {
                return null;
            }

            // Return all vehicles stored in the disbanding gang garage back to their owners
            CharacterVehicle::where('gang_id', $lockedGang->id)
                ->where('in_gang_garage', true)
                ->update([
                    'in_gang_garage' => false,
                    'gang_id' => null,
                    'stored_at' => null,
                    'stored_by_character_id' => null,
                ]);

            // Unlink all members. Restart their allowance clock now: gang members receive no allowance,
            // and an old timestamp would pay up to 24 hours of backlog on the next page load.
            $lockedGang->members()->update([
                'gang_id' => null,
                'gang_role' => 'none',
                'last_gang_wage_at' => null,
                'last_allowance_at' => now(),
            ]);

            $name = $lockedGang->name;
            $gangId = $lockedGang->id;
            $treasuryLost = (int) $lockedGang->treasury;
            $lockedGang->delete();

            AuditLogService::log(
                action: 'gang_disband',
                category: 'gang',
                description: "Bende '{$name}' (#{$gangId}) definitief opgeheven door leider {$lockedCharacter->name}",
                character: $lockedCharacter,
                user: $lockedCharacter->user,
                amount: $treasuryLost > 0 ? $treasuryLost : null,
                details: [
                    'gang_id' => $gangId,
                    'gang_name' => $name,
                    'treasury_lost' => $treasuryLost,
                ],
                customRisk: $treasuryLost >= 2_500_000 ? 'hoog' : 'verdacht',
                customReason: 'Bende definitief ontbonden door bendeleider'
            );

            return $name;
        });

        if ($gangName === null) {
            return back()->with('error', 'Alleen de oprichter/leider kan de bende definitief opheffen!');
        }

        return back()->with('success', "Bende '{$gangName}' is definitief opgeheven.");
    }

    /**
     * Buy gang shop upgrade items (Bling bling shops, Bescherming, Power, Meer leden).
     * Matching Screenshot 4.
     */
    public function buyShopItem(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $gang = $character->gang;
        if (! $gang) {
            return back()->with('error', 'Je bent geen lid van een bende.');
        }

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen aankopen doen in de bendewinkel!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $validated = $request->validate([
            'item_type' => ['required', 'string', 'in:bling_shops,protection,power,max_members,gang_chat'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
        ], [
            'quantity.min' => 'Kies minimaal 1 om aan te schaffen.',
            'quantity.max' => 'Je kunt maximaal 1.000.000 per keer aanschaffen.',
        ]);

        $itemType = $validated['item_type'];
        $qty = (int) $validated['quantity'];

        return DB::transaction(function () use ($gang, $character, $itemType, $qty): RedirectResponse {
            /** @var Gang $lockedGang */
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            if (! $lockedCharacter || $lockedCharacter->isImprisoned() || $lockedCharacter->isComatose() || $lockedCharacter->needsReviveSerum()) {
                return back()
                    ->with('active_gang_tab', 'winkel')
                    ->with('error', 'Je bent momenteel niet in staat om aankopen te doen.');
            }

            // Upgrades paid from the treasury spend shared gang money, so only officers may buy them.
            $isTreasuryPurchase = in_array($itemType, ['bling_shops', 'protection', 'power'], true);
            if ($isTreasuryPurchase && ! $lockedGang->isOfficer($lockedCharacter)) {
                return back()
                    ->with('active_gang_tab', 'winkel')
                    ->with('error', 'Alleen bendebeheerders kunnen upgrades uit de bende kas betalen.');
            }

            switch ($itemType) {
                case 'gang_chat':
                    if ($lockedGang->has_gang_chat) {
                        return back()->with('error', 'Jullie bende beschikt al over een eigen besloten Bende Chat!');
                    }

                    $chatPrice = 500;
                    if (($lockedCharacter->stars ?? 0) >= $chatPrice) {
                        $lockedCharacter->stars -= $chatPrice;
                        $lockedCharacter->save();
                    } elseif (($lockedGang->gang_stars ?? 0) >= $chatPrice && $lockedGang->isOfficer($lockedCharacter)) {
                        $lockedGang->gang_stars -= $chatPrice;
                    } else {
                        return back()->with('error', 'Niet genoeg Sterren ⭐ om de Bende Chat te ontgrendelen (500 Sterren ⭐ vereist).');
                    }

                    $lockedGang->has_gang_chat = true;
                    $lockedGang->save();

                    return back()
                        ->with('active_gang_tab', 'winkel')
                        ->with('success', '🎉 Gefeliciteerd! De besloten Bende Chat is nu permanent ontgrendeld voor alle leden van de bende!');

                case 'bling_shops':
                    $currentKiosks = (int) ($lockedGang->bling_shops ?? 0);
                    $maxKiosks = \App\Services\GangWageService::MAX_CONTRIBUTING_KIOSKS;
                    if ($currentKiosks >= $maxKiosks) {
                        return back()
                            ->with('active_gang_tab', 'winkel')
                            ->with('error', "Jullie bende heeft de maximale limiet van {$maxKiosks} Smokkelkiosken al bereikt!");
                    }
                    if ($currentKiosks + $qty > $maxKiosks) {
                        $available = $maxKiosks - $currentKiosks;

                        return back()
                            ->with('active_gang_tab', 'winkel')
                            ->with('error', "Jullie bende kan nog maximaal {$available} Smokkelkiosk(en) aanschaffen (limiet is {$maxKiosks} stuks).");
                    }

                    $unitPrice = 50000;
                    $totalCost = $unitPrice * $qty;
                    if ($lockedGang->treasury < $totalCost) {
                        return back()->with('error', 'Er zit niet genoeg geld in de bende kas (gangpot)! Er is momenteel €'.number_format($lockedGang->treasury, 0, ',', '.').' aanwezig, maar €'.number_format($totalCost, 0, ',', '.').' is vereist. Persoonlijk zakgeld kan hier niet direct gebruikt worden. Doneer eerst geld aan de bende kas via het tabblad \'Doneren\'.');
                    }

                    $lockedGang->treasury -= $totalCost;
                    $lockedGang->bling_shops = $currentKiosks + $qty;
                    $lockedGang->save();

                    return back()
                        ->with('active_gang_tab', 'winkel')
                        ->with('success', "Succesvol {$qty} Smokkelkiosk(en) aangeschaft vanuit de bende kas! Het bende-basisloon is verhoogd met +€".number_format($qty * \App\Services\GangWageService::SHOP_BONUS_WAGE, 0, ',', '.').' per uur!');

                case 'protection':
                    $unitPrice = 15000;
                    $totalCost = $unitPrice * $qty;
                    if ($lockedGang->treasury < $totalCost) {
                        return back()->with('error', 'Er zit niet genoeg geld in de bende kas (gangpot)! Er is momenteel €'.number_format($lockedGang->treasury, 0, ',', '.').' aanwezig, maar €'.number_format($totalCost, 0, ',', '.').' is vereist. Persoonlijk zakgeld kan hier niet direct gebruikt worden. Doneer eerst geld aan de bende kas via het tabblad \'Doneren\'.');
                    }

                    $lockedGang->treasury -= $totalCost;
                    $lockedGang->protection_points = ($lockedGang->protection_points ?? 1000) + ($qty * 5000);
                    $lockedGang->save();

                    return back()
                        ->with('active_gang_tab', 'winkel')
                        ->with('success', "Succesvol {$qty}x Bescherming aangeschaft vanuit de bende kas (+".number_format($qty * 5000, 0, ',', '.').' verdedigingspunten)!');

                case 'power':
                    $unitPrice = 15000;
                    $totalCost = $unitPrice * $qty;
                    if ($lockedGang->treasury < $totalCost) {
                        return back()->with('error', 'Er zit niet genoeg geld in de bende kas (gangpot)! Er is momenteel €'.number_format($lockedGang->treasury, 0, ',', '.').' aanwezig, maar €'.number_format($totalCost, 0, ',', '.').' is vereist. Persoonlijk zakgeld kan hier niet direct gebruikt worden. Doneer eerst geld aan de bende kas via het tabblad \'Doneren\'.');
                    }

                    $lockedGang->treasury -= $totalCost;
                    $lockedGang->attack_power = ($lockedGang->attack_power ?? 500) + ($qty * 5000);
                    $lockedGang->save();

                    return back()
                        ->with('active_gang_tab', 'winkel')
                        ->with('success', "Succesvol {$qty}x Power aangeschaft vanuit de bende kas (+".number_format($qty * 5000, 0, ',', '.').' aanvalspunten)!');

                case 'max_members':
                    $starPrice = 25;
                    $totalStars = $starPrice * $qty;
                    $currentMax = $lockedGang->max_members ?? 10;
                    $newMax = $currentMax + $qty;

                    if ($newMax > 35) {
                        return back()->with('error', "Het maximale bende limiet is 35 leden (huidig: {$currentMax}).");
                    }

                    // Check character stars or gang stars
                    if (($lockedCharacter->stars ?? 0) >= $totalStars) {
                        $lockedCharacter->stars -= $totalStars;
                        $lockedCharacter->save();
                    } elseif (($lockedGang->gang_stars ?? 0) >= $totalStars && $lockedGang->isOfficer($lockedCharacter)) {
                        $lockedGang->gang_stars -= $totalStars;
                        $lockedGang->save();
                    } else {
                        return back()->with('error', "Niet genoeg Sterren ⭐ om {$qty} extra ledenplekken te kopen ({$totalStars} Sterren ⭐ vereist).");
                    }

                    $lockedGang->max_members = $newMax;
                    $lockedGang->save();

                    return back()
                        ->with('active_gang_tab', 'winkel')
                        ->with('success', "Capaciteit uitgebreid met {$qty} leden! Nieuw limiet: {$lockedGang->max_members} leden.");
            }

            return back()->with('active_gang_tab', 'winkel');
        });
    }

    /**
     * Donate stars to gang.
     * Matching Screenshot 5.
     */
    public function donateStars(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $gang = $character->gang;
        if (! $gang) {
            return back()->with('error', 'Je bent geen lid van een bende.');
        }

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen sterren doneren aan de bende!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $validated = $request->validate([
            'stars' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        $stars = (int) $validated['stars'];

        return DB::transaction(function () use ($gang, $character, $stars): RedirectResponse {
            /** @var Character|null $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            /** @var Gang|null $lockedGang */
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();

            if (! $lockedCharacter || $lockedCharacter->isImprisoned() || $lockedCharacter->isComatose() || $lockedCharacter->needsReviveSerum()) {
                return back()->with('error', 'Je bent momenteel niet in staat om sterren te doneren.');
            }

            if (! $lockedGang || (int) $lockedCharacter->gang_id !== (int) $lockedGang->id) {
                return back()->with('error', 'Je bent geen lid van deze bende.');
            }

            if (($lockedCharacter->stars ?? 0) < $stars) {
                return back()->with('error', "Je hebt slechts {$lockedCharacter->stars} Sterren ⭐.");
            }

            $lockedCharacter->stars -= $stars;
            $lockedCharacter->addRespect($stars * 5);
            $lockedCharacter->save();

            $lockedGang->gang_stars = ($lockedGang->gang_stars ?? 0) + $stars;
            $lockedGang->save();

            return back()
                ->with('active_gang_tab', 'doneren')
                ->with('success', "Je hebt {$stars} Sterren ⭐ gedoneerd aan {$lockedGang->name}! (+".($stars * 5).' Respect voor jou).');
        });
    }

    /**
     * Donate heist masks to the Gang Depot.
     */
    public function donateMasks(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character->gang_id) {
            return back()->with('error', 'Je bent geen lid van een bende.');
        }

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen maskers doneren aan de bende!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $gang = $character->gang;

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
        ]);

        $requestedQty = (int) $validated['quantity'];

        return DB::transaction(function () use ($gang, $character, $requestedQty): RedirectResponse {
            /** @var Character|null $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            /** @var Gang|null $lockedGang */
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();

            if (! $lockedCharacter || $lockedCharacter->isImprisoned() || $lockedCharacter->isComatose() || $lockedCharacter->needsReviveSerum()) {
                return back()->with('error', 'Je bent momenteel niet in staat om maskers te doneren.');
            }

            if (! $lockedGang || (int) $lockedCharacter->gang_id !== (int) $lockedGang->id) {
                return back()->with('error', 'Je bent geen lid van deze bende.');
            }

            $currentMasks = (int) ($lockedCharacter->masks ?? 0);
            if ($currentMasks < 1) {
                return back()->with('error', 'Je hebt geen maskers op zak om te doneren.');
            }

            $quantity = min($requestedQty, $currentMasks);

            // Check gang depot storage capacity (provided by gang properties)
            $storageCap = $lockedGang->total_storage_capacity;
            $storageUsed = $lockedGang->getStorageUsed();
            if ($storageUsed + $quantity > $storageCap) {
                $spaceLeft = max(0, $storageCap - $storageUsed);

                return back()->with('error', "Het Bende Depot zit te vol ({$storageUsed}/{$storageCap} plekken bezet)! Er passen maximaal {$spaceLeft} extra items/maskers bij. Schaf meer bende opslagpanden aan of laat beheerders overtollige voorraad verkopen.");
            }

            $respectGain = min(2500, max(1, (int) round($quantity * 0.5)));

            $lockedCharacter->masks -= $quantity;
            $lockedCharacter->addRespect($respectGain);
            $lockedCharacter->save();

            $lockedGang->masks = ($lockedGang->masks ?? 0) + $quantity;
            $lockedGang->save();

            return back()
                ->with('active_gang_tab', 'depot')
                ->with('success', "🎭 Je hebt succesvol {$quantity}x Overval Masker(s) gedoneerd aan het Bende Depot! De bende bezit nu {$lockedGang->masks} maskers voor Bende Heists.");
        });
    }

    /**
     * Donate an inventory item (cigarettes, contraband, tools) to the Gang Depot.
     */
    public function donateItem(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character->gang_id) {
            return back()->with('error', 'Je bent geen lid van een bende.');
        }

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen voorwerpen doneren aan de bende!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $gang = $character->gang;

        $validated = $request->validate([
            'character_item_id' => ['required', 'integer', 'exists:character_items,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
        ]);

        $requestedQty = (int) $validated['quantity'];
        $charItemId = (int) $validated['character_item_id'];

        return DB::transaction(function () use ($gang, $character, $charItemId, $requestedQty): RedirectResponse {
            /** @var Character|null $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            /** @var Gang|null $lockedGang */
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();

            if (! $lockedCharacter || $lockedCharacter->isImprisoned() || $lockedCharacter->isComatose() || $lockedCharacter->needsReviveSerum()) {
                return back()->with('error', 'Je bent momenteel niet in staat om voorwerpen te doneren.');
            }

            if (! $lockedGang || (int) $lockedCharacter->gang_id !== (int) $lockedGang->id) {
                return back()->with('error', 'Je bent geen lid van deze bende.');
            }

            /** @var CharacterItem|null $charItem */
            $charItem = CharacterItem::where('id', $charItemId)
                ->where('character_id', $lockedCharacter->id)
                ->lockForUpdate()
                ->with('item')
                ->first();

            if (! $charItem || $charItem->quantity < 1) {
                return back()->with('error', 'Dit voorwerp bevindt zich niet in je opbergplaats.');
            }

            $item = $charItem->item;
            if (! $item) {
                return back()->with('error', 'Item gegevens niet gevonden.');
            }

            if (str_starts_with((string) $item->slug, 'halloween_token_') || str_starts_with((string) $item->slug, 'quest_')) {
                return back()->with('error', 'Dit speciale evenementvoorwerp kan niet worden gedoneerd aan het Bende Depot.');
            }

            $quantity = min($requestedQty, $charItem->quantity);

            // Check gang depot storage capacity
            $storageCap = $lockedGang->total_storage_capacity;
            $storageUsed = $lockedGang->getStorageUsed();
            if ($storageUsed + $quantity > $storageCap) {
                $spaceLeft = max(0, $storageCap - $storageUsed);

                return back()->with('error', "Het Bende Depot zit te vol ({$storageUsed}/{$storageCap} plekken bezet)! Er passen maximaal {$spaceLeft} extra items bij. Koop meer opslagpanden.");
            }

            $itemName = $item->name;
            $itemId = $charItem->item_id;

            // Deduct from player
            $charItem->quantity -= $quantity;
            if ($charItem->quantity <= 0) {
                $charItem->delete();
            } else {
                $charItem->save();
            }

            // Add to gang items
            /** @var GangItem $gangItem */
            $gangItem = GangItem::where('gang_id', $lockedGang->id)->where('item_id', $itemId)->lockForUpdate()->first();
            if (! $gangItem) {
                $gangItem = GangItem::create([
                    'gang_id' => $lockedGang->id,
                    'item_id' => $itemId,
                    'quantity' => $quantity,
                ]);
            } else {
                $gangItem->quantity += $quantity;
                $gangItem->save();
            }

            $respectGain = min(2500, max(1, (int) round($quantity * 0.5)));
            $lockedCharacter->addRespect($respectGain);
            $lockedCharacter->save();

            return back()
                ->with('active_gang_tab', 'depot')
                ->with('success', "📦 Succesvol {$quantity}x {$itemName} gedoneerd aan het Bende Depot! (+{$respectGain} respect)");
        });
    }

    /**
     * Sell heist masks from the Gang Depot to fund the Gang Treasury (Officer only).
     */
    public function sellGangMasks(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character->gang_id) {
            return back()->with('error', 'Je bent geen lid van een bende.');
        }

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen maskers uit het Bende Depot verkopen!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $gang = $character->gang;
        if (! $gang->isOfficer($character)) {
            return back()->with('error', 'Alleen bendeleiders of beheerders kunnen spullen uit het Bende Depot verkopen!');
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
        ]);

        $requestedQty = (int) $validated['quantity'];

        return DB::transaction(function () use ($gang, $character, $requestedQty): RedirectResponse {
            /** @var Character|null $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $lockedCharacter || $lockedCharacter->isImprisoned() || $lockedCharacter->isComatose() || $lockedCharacter->needsReviveSerum()) {
                return back()->with('error', 'Je bent momenteel niet in staat om depot voorraad te verkopen.');
            }

            /** @var Gang $lockedGang */
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();

            $currentMasks = (int) ($lockedGang->masks ?? 0);
            if ($currentMasks < 1) {
                return back()->with('error', 'Er liggen geen maskers in het Bende Depot.');
            }

            $quantity = min($requestedQty, $currentMasks);
            $pricePerMask = 1500;
            $totalEarned = $pricePerMask * $quantity;

            $lockedGang->masks -= $quantity;
            $lockedGang->treasury += $totalEarned;
            $lockedGang->save();

            $formattedCash = number_format($totalEarned, 0, ',', '.');

            AuditLogService::log(
                action: 'gang.sell_depot_masks',
                category: 'gang',
                description: "{$quantity}x overval maskers verkocht uit depot voor €{$formattedCash}",
                character: $character,
                user: $character->user,
                amount: $totalEarned,
                details: [
                    'gang_id' => $lockedGang->id,
                    'quantity' => $quantity,
                ]
            );

            return back()
                ->with('active_gang_tab', 'depot')
                ->with('success', "💰 Succesvol {$quantity}x Bende Maskers verkocht aan de heler voor €{$formattedCash}! Dit bedrag is direct bijgeschreven in de Bende Kas 🏛️.");
        });
    }

    /**
     * Sell items from Gang Depot to fund the Gang Treasury (Officer only).
     */
    public function sellGangItem(Request $request, GangItem $gangItem): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character->gang_id) {
            return back()->with('error', 'Je bent geen lid van een bende.');
        }

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen voorwerpen uit het Bende Depot verkopen!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $gang = $character->gang;
        if (! $gang->isOfficer($character)) {
            return back()->with('error', 'Alleen bendeleiders of beheerders kunnen bende-eigendommen verkopen!');
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
        ]);

        $requestedQty = (int) $validated['quantity'];

        return DB::transaction(function () use ($gang, $character, $gangItem, $requestedQty): RedirectResponse {
            /** @var Character|null $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $lockedCharacter || $lockedCharacter->isImprisoned() || $lockedCharacter->isComatose() || $lockedCharacter->needsReviveSerum()) {
                return back()->with('error', 'Je bent momenteel niet in staat om depot items te verkopen.');
            }

            /** @var GangItem|null $lockedGangItem */
            $lockedGangItem = GangItem::where('id', $gangItem->id)
                ->where('gang_id', $gang->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedGangItem || $lockedGangItem->quantity < 1) {
                return back()->with('error', 'Dit voorwerp bevindt zich niet in het Bende Depot.');
            }

            $item = $lockedGangItem->item;
            if (! $item) {
                return back()->with('error', 'Item gegevens niet gevonden.');
            }

            $quantity = min($requestedQty, $lockedGangItem->quantity);
            $pricePerUnit = $item->getDepotSellPrice();
            $totalEarned = $pricePerUnit * $quantity;

            $lockedGangItem->quantity -= $quantity;
            if ($lockedGangItem->quantity <= 0) {
                $lockedGangItem->delete();
            } else {
                $lockedGangItem->save();
            }

            /** @var Gang $lockedGang */
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();
            $lockedGang->treasury += $totalEarned;
            $lockedGang->save();

            $formattedCash = number_format($totalEarned, 0, ',', '.');

            AuditLogService::log(
                action: 'gang.sell_depot_item',
                category: 'gang',
                description: "{$quantity}x {$item->name} verkocht uit depot voor €{$formattedCash}",
                character: $character,
                user: $character->user,
                amount: $totalEarned,
                details: [
                    'gang_id' => $lockedGang->id,
                    'item_id' => $item->id,
                    'quantity' => $quantity,
                ]
            );

            return back()
                ->with('active_gang_tab', 'depot')
                ->with('success', "💰 Succesvol {$quantity}x {$item->name} verkocht voor €{$formattedCash}! De opbrengst is direct gestort in de Bende Kas 🏛️.");
        });
    }

    /**
     * Launch a gang attack against a rival gang (Leader / Co-leader only).
     */
    public function attack(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $myGang = $character->gang;
        if (! $myGang) {
            return back()->with('error', 'Je bent geen lid van een bende.');
        }

        if (! $myGang->isOfficer($character)) {
            return back()->with('error', 'Alleen bendebeheerders (leider, co-leider, kantine smokkelaar of kasbeheerder) kunnen bende-aanvallen starten!');
        }

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen bende-aanval leiden!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($character->health < 20) {
            return back()->with('error', 'Je hebt minimaal 20% gezondheid nodig om een bende-aanval te kunnen commanderen!');
        }

        $remainingCooldown = $myGang->attackCooldownRemaining();
        if ($remainingCooldown > 0) {
            return back()->with('error', 'Jouw bende moet nog '.gmdate('i:s', $remainingCooldown).' wachten voor een nieuwe aanval.');
        }

        $validated = $request->validate([
            'target_gang_id' => ['required', 'exists:gangs,id'],
        ]);

        $targetGang = Gang::findOrFail($validated['target_gang_id']);

        if ($targetGang->id === $myGang->id) {
            return back()->with('error', 'Je kunt je eigen bende niet aanvallen!');
        }

        $isAlly = GangAlliance::where(function ($q) use ($myGang, $targetGang) {
            $q->where('gang_id', $myGang->id)->where('ally_gang_id', $targetGang->id);
        })->orWhere(function ($q) use ($myGang, $targetGang) {
            $q->where('gang_id', $targetGang->id)->where('ally_gang_id', $myGang->id);
        })->exists();

        if ($isAlly) {
            return back()->with('error', "{$targetGang->name} is een bondgenoot! Verbreek eerst het bondgenootschap om oorlog te voeren.");
        }

        if ($targetGang->isUnderShield()) {
            $shieldSec = $targetGang->shieldSecondsRemaining();

            return back()->with('error', "Bende {$targetGang->name} heeft nog een actief verdedigingsschild na een eerdere overval (".gmdate('i:s', $shieldSec).').');
        }

        return DB::transaction(function () use ($myGang, $targetGang, $character): RedirectResponse {
            /** @var Character|null $lockedCommander */
            $lockedCommander = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $lockedCommander || $lockedCommander->isImprisoned() || $lockedCommander->isComatose() || $lockedCommander->needsReviveSerum() || $lockedCommander->health < 20) {
                return back()->with('error', 'Je bent momenteel niet in staat om een aanval te leiden.');
            }

            // Lock both gangs in id order (prevents deadlocks) and re-check the limits, so parallel attacks cannot loot the same treasury twice.
            $lockedGangs = Gang::whereIn('id', [$myGang->id, $targetGang->id])->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $myGang = $lockedGangs->get($myGang->id);
            $targetGang = $lockedGangs->get($targetGang->id);

            if (! $myGang || ! $targetGang || (int) $lockedCommander->gang_id !== (int) $myGang->id || ! $myGang->isOfficer($lockedCommander)) {
                return back()->with('error', 'Je bent niet bevoegd om deze bende-aanval uit te voeren.');
            }

            $isAlly = GangAlliance::where(function ($q) use ($myGang, $targetGang) {
                $q->where('gang_id', $myGang->id)->where('ally_gang_id', $targetGang->id);
            })->orWhere(function ($q) use ($myGang, $targetGang) {
                $q->where('gang_id', $targetGang->id)->where('ally_gang_id', $myGang->id);
            })->exists();

            if ($isAlly) {
                return back()->with('error', "{$targetGang->name} is een bondgenoot! Verbreek eerst het bondgenootschap om oorlog te voeren.");
            }

            $remainingCooldown = $myGang->attackCooldownRemaining();
            if ($remainingCooldown > 0) {
                return back()->with('error', 'Jouw bende moet nog '.gmdate('i:s', $remainingCooldown).' wachten voor een nieuwe aanval.');
            }

            if ($targetGang->isUnderShield()) {
                return back()->with('error', "Bende {$targetGang->name} heeft nog een actief verdedigingsschild na een eerdere overval (".gmdate('i:s', $targetGang->shieldSecondsRemaining()).').');
            }

            $attackerPower = max(500, (int) ($myGang->attack_power ?? 500));
            $baseTargetDefense = max(500, (int) ($targetGang->protection_points ?? 1000));

            // Factor in hired Enforcer NPCs defense bonus (capped at max 100% to ensure fair balance)
            $rawNpcDefenseBonusPct = (int) $targetGang->npcs()->where('bonus_type', 'defense')->sum('bonus_value');
            $npcDefenseBonusPct = max(0, min(Gang::MAX_NPC_DEFENSE_BONUS_PERCENT, $rawNpcDefenseBonusPct));
            $targetNpcDefenseBonus = (int) round($baseTargetDefense * ($npcDefenseBonusPct / 100));
            $targetDefense = max(500, $baseTargetDefense + $targetNpcDefenseBonus);

            // Apply a 15% variance so evenly matched gangs do not have a deterministic outcome.
            $attackRollPercent = mt_rand(85, 115);
            $defenseRollPercent = mt_rand(85, 115);
            $attackRoll = (int) round($attackerPower * ($attackRollPercent / 100));
            $defenseRoll = (int) round($targetDefense * ($defenseRollPercent / 100));

            $attackModifier = ($attackRollPercent >= 100 ? '+' : '').($attackRollPercent - 100).'%';
            $defenseModifier = ($defenseRollPercent >= 100 ? '+' : '').($defenseRollPercent - 100).'%';

            $formattedAttackerPower = number_format($attackerPower, 0, ',', '.');
            $formattedTargetDefense = number_format($targetDefense, 0, ',', '.');
            $formattedBaseDefense = number_format($baseTargetDefense, 0, ',', '.');
            $formattedNpcDefense = number_format($targetNpcDefenseBonus, 0, ',', '.');
            $formattedAttackRoll = number_format($attackRoll, 0, ',', '.');
            $formattedDefenseRoll = number_format($defenseRoll, 0, ',', '.');

            $npcBreakdownText = $npcDefenseBonusPct > 0
                ? " (inclusief +{$npcDefenseBonusPct}% / +{$formattedNpcDefense} verdediging door Enforcer huurlingen)"
                : '';

            $myGangOfficers = $myGang->members()->whereIn('gang_role', ['leader', 'co_leader'])->get();
            $targetOfficers = $targetGang->members()->whereIn('gang_role', ['leader', 'co_leader'])->get();

            if ($attackRoll > $defenseRoll) {
                $protectedTreasury = max(0, (int) ($targetGang->protected_treasury ?? 0));
                $lootableTreasury = max(0, (int) $targetGang->treasury - $protectedTreasury);

                $lootPercent = $lootableTreasury > 0 ? mt_rand(8, 16) : 0;
                $stolenCash = $lootableTreasury > 0
                    ? (int) round($lootableTreasury * ($lootPercent / 100))
                    : 0;
                $damagePercent = mt_rand(10, 20);
                $baseDefenseDamage = max(250, (int) round($baseTargetDefense * ($damagePercent / 100)));
                $respectGain = round(mt_rand(25, 60) / 10, 2);

                $targetGang->treasury = max(0, $targetGang->treasury - $stolenCash);
                $targetGang->protected_treasury = min((int) ($targetGang->protected_treasury ?? 0), (int) $targetGang->treasury);
                $targetGang->protection_points = max(250, $targetGang->protection_points - $baseDefenseDamage);
                $targetGang->shield_until = now()->addMinutes(15);
                $targetGang->save();

                $newBaseDefense = (int) $targetGang->protection_points;
                $newTotalDefense = $newBaseDefense + (int) round($newBaseDefense * ($npcDefenseBonusPct / 100));
                $defenseDamage = max(250, $targetDefense - $newTotalDefense);

                $myGang->treasury += $stolenCash;
                $myGang->last_attack_at = now();
                $myGang->save();

                // Attacking commander gains respect and takes light combat strain (-5 HP)
                $lockedCommander->health = max(1, $lockedCommander->health - 5);
                $lockedCommander->addRespect($respectGain);
                $lockedCommander->save();

                // Defending leader takes 10 HP combat damage if present
                if ($targetGang->leader_character_id) {
                    $defendingLeader = Character::where('id', $targetGang->leader_character_id)->lockForUpdate()->first();
                    if ($defendingLeader) {
                        $defendingLeader->health = max(1, $defendingLeader->health - 10);
                        $defendingLeader->save();
                    }
                }

                $formattedStolenCash = number_format($stolenCash, 0, ',', '.');
                $formattedDefenseDamage = number_format($defenseDamage, 0, ',', '.');
                $formattedRemainingDefense = number_format($newTotalDefense, 0, ',', '.');

                $protectedNote = $protectedTreasury > 0
                    ? " (restitutietegoed van €".number_format($protectedTreasury, 0, ',', '.')." is kluis-beschermd tegen overvallen)"
                    : '';

                $summary = "⚔️ OVERWINNING! {$myGang->name} heeft {$targetGang->name} succesvol overvallen! Gevechtsrol (±15%): Aanval {$formattedAttackRoll} ({$attackRollPercent}% van {$formattedAttackerPower} power) vs Verdediging {$formattedDefenseRoll} ({$defenseRollPercent}% van {$formattedTargetDefense} bescherming{$npcBreakdownText}). Buit: €{$formattedStolenCash} ({$lootPercent}% plunderbare kas{$protectedNote}) geplunderd, verdediging van tegenstander -{$formattedDefenseDamage} punten (-{$damagePercent}%, nieuw: {$formattedRemainingDefense}), +{$respectGain} respect!";

                GangAttack::create([
                    'attacker_gang_id' => $myGang->id,
                    'target_gang_id' => $targetGang->id,
                    'character_id' => $character->id,
                    'attacker_power' => $attackerPower,
                    'target_defense' => $targetDefense,
                    'won' => true,
                    'stolen_cash' => $stolenCash,
                    'defense_damage' => $defenseDamage,
                    'power_damage' => 0,
                    'respect_gain' => $respectGain,
                    'summary' => $summary,
                ]);

                $activeWar = GangWar::areGangsAtWar((int) $myGang->id, (int) $targetGang->id);
                if ($activeWar) {
                    $activeWar->addScore((int) $myGang->id, 25);
                }

                AuditLogService::log(
                    action: 'gang.attack_victory',
                    category: 'gang',
                    description: "Oorlogsoverwinning: {$myGang->name} plundert €{$formattedStolenCash} bij {$targetGang->name}",
                    character: $lockedCommander,
                    user: $lockedCommander->user,
                    amount: $stolenCash > 0 ? $stolenCash : null,
                    details: [
                        'attacker_gang_id' => $myGang->id,
                        'target_gang_id' => $targetGang->id,
                        'won' => true,
                        'stolen_cash' => $stolenCash,
                        'attack_roll' => $attackRoll,
                        'defense_roll' => $defenseRoll,
                    ]
                );

                if ($targetGang->leader) {
                    GameMessage::create([
                        'recipient_id' => $targetGang->leader->id,
                        'sender_id' => $character->id,
                        'sender_name' => "{$character->name} [{$myGang->name}]",
                        'subject' => "🚨 [Bende Oorlog] Bende {$myGang->name} heeft jullie aangevallen!",
                        'content' => "Patiënt {$targetGang->leader->name},\n\n"
                            ."Jullie bende '{$targetGang->name}' is zojuist aangevallen door '{$myGang->name}' onder leiding van {$character->name}.\n\n"
                            ."📊 GEVECHTSBEREKENING (elke aanval heeft -15% tot +15% gevechtsrol):\n"
                            ."• Aanval {$myGang->name}: {$formattedAttackerPower} basispower × {$attackRollPercent}% ({$attackModifier}) = {$formattedAttackRoll} gevechtskracht\n"
                            ."• Verdediging {$targetGang->name}: {$formattedTargetDefense} totale verdediging ({$formattedBaseDefense} basis + {$formattedNpcDefense} NPC) × {$defenseRollPercent}% ({$defenseModifier}) = {$formattedDefenseRoll} verdedigingskracht\n"
                            ."👉 Hun gerolde aanval ({$formattedAttackRoll}) viel hoger uit dan jullie gerolde verdediging ({$formattedDefenseRoll}) en brak door jullie linie heen!\n\n"
                            ."💥 SCHADE & BUIT:\n"
                            ."• Geplunderde Bende Kas ({$lootPercent}% van kas, marge 8%-16%): €{$formattedStolenCash}\n"
                            ."• Verdedigingsschade ({$damagePercent}% van {$formattedTargetDefense}, marge 10%-20%): -{$formattedDefenseDamage} punten (resterend: {$formattedRemainingDefense} punten)\n"
                            .'• Schildbescherming: Jullie bende is nu 15 minuten beschermd tegen nieuwe aanvallen.',
                        'type' => 'gang',
                    ]);
                }

                foreach ($targetOfficers as $officer) {
                    GameNotification::create([
                        'character_id' => $officer->id,
                        'type' => 'gang',
                        'icon' => 'fa-skull-crossbones',
                        'title' => '🚨 Bende Overval!',
                        'message' => "Bende '{$myGang->name}' heeft jullie bende overvallen! Kas verlies: €{$formattedStolenCash}.",
                    ]);
                }

                foreach ($myGangOfficers as $officer) {
                    if ($officer->id !== $lockedCommander->id) {
                        GameNotification::create([
                            'character_id' => $officer->id,
                            'type' => 'gang',
                            'icon' => 'fa-trophy',
                            'title' => '⚔️ Oorlogsoverwinning!',
                            'message' => "{$lockedCommander->name} heeft een succesvolle overval geleid op '{$targetGang->name}'! Buit: €{$formattedStolenCash}.",
                        ]);
                    }
                }

                return back()
                    ->with('active_gang_tab', 'oorlog')
                    ->with('success', $summary);
            }

            $powerLossPercent = mt_rand(2, 5);
            $powerDamage = max(150, (int) round($attackerPower * ($powerLossPercent / 100)));
            $myGang->attack_power = max(250, $myGang->attack_power - $powerDamage);
            $myGang->last_attack_at = now();
            $myGang->save();

            // Attacking commander suffers 25 HP combat damage; coma if drops to 0
            $lockedCommander->health = max(0, $lockedCommander->health - 25);
            if ($lockedCommander->health <= 0) {
                $lockedCommander->health = 0;
                $lockedCommander->coma_until = now()->addMinutes(30);
            }
            $lockedCommander->save();

            $formattedPowerDamage = number_format($powerDamage, 0, ',', '.');
            $formattedRemainingPower = number_format((int) $myGang->attack_power, 0, ',', '.');

            $summary = "🛡️ AANVAL AFGESLAGEN! Bende {$targetGang->name} verdedigde zich met succes tegen {$myGang->name}! Gevechtsrol (±15%): Aanval {$formattedAttackRoll} ({$attackRollPercent}% van {$formattedAttackerPower} power) vs Verdediging {$formattedDefenseRoll} ({$defenseRollPercent}% van {$formattedTargetDefense} bescherming{$npcBreakdownText}). Jullie leden verliezen -{$formattedPowerDamage} power (-{$powerLossPercent}%, nieuw: {$formattedRemainingPower}).";

            GangAttack::create([
                'attacker_gang_id' => $myGang->id,
                'target_gang_id' => $targetGang->id,
                'character_id' => $character->id,
                'attacker_power' => $attackerPower,
                'target_defense' => $targetDefense,
                'won' => false,
                'stolen_cash' => 0,
                'defense_damage' => 0,
                'power_damage' => $powerDamage,
                'respect_gain' => 0.00,
                'summary' => $summary,
            ]);

            AuditLogService::log(
                action: 'gang.attack_defeat',
                category: 'gang',
                description: "Aanval afgeslagen: {$myGang->name} verliest van {$targetGang->name} (-{$formattedPowerDamage} power)",
                character: $lockedCommander,
                user: $lockedCommander->user,
                amount: null,
                details: [
                    'attacker_gang_id' => $myGang->id,
                    'target_gang_id' => $targetGang->id,
                    'won' => false,
                    'power_damage' => $powerDamage,
                    'attack_roll' => $attackRoll,
                    'defense_roll' => $defenseRoll,
                ]
            );

            if ($targetGang->leader_character_id) {
                /** @var Character|null $lockedLeader */
                $lockedLeader = Character::where('id', $targetGang->leader_character_id)->lockForUpdate()->first();
                if ($lockedLeader) {
                    $lockedLeader->addRespect(2.0);
                    $lockedLeader->save();

                    GameMessage::create([
                        'recipient_id' => $lockedLeader->id,
                        'sender_id' => $character->id,
                        'sender_name' => "{$character->name} [{$myGang->name}]",
                        'subject' => "🛡️ [Bende Oorlog] Aanval van {$myGang->name} afgeslagen!",
                        'content' => "Patiënt {$lockedLeader->name},\n\n"
                            ."Bende '{$myGang->name}' (onder leiding van {$character->name}) probeerde jullie bende '{$targetGang->name}' aan te vallen, maar jullie verdediging hield stand!\n\n"
                            ."📊 GEVECHTSBEREKENING (elke aanval heeft -15% tot +15% gevechtsrol):\n"
                            ."• Aanval {$myGang->name}: {$formattedAttackerPower} basispower × {$attackRollPercent}% ({$attackModifier}) = {$formattedAttackRoll} gevechtskracht\n"
                            ."• Verdediging {$targetGang->name}: {$formattedTargetDefense} totale verdediging ({$formattedBaseDefense} basis + {$formattedNpcDefense} NPC) × {$defenseRollPercent}% ({$defenseModifier}) = {$formattedDefenseRoll} verdedigingskracht\n"
                            ."👉 Jullie gerolde verdediging ({$formattedDefenseRoll}) hield stand tegen hun gerolde aanval ({$formattedAttackRoll})!\n\n"
                            ."🛡️ RESULTAAT:\n"
                            ."• Schade aan vijand ({$powerLossPercent}% van hun power, marge 2%-5%): -{$formattedPowerDamage} aanvalskracht\n"
                            ."• Jullie verdedigingsschade: 0 punten\n"
                            .'• Beloning: +2.0 respect verdiend voor de succesvolle verdediging.',
                        'type' => 'gang',
                    ]);
                }
            }

            foreach ($targetOfficers as $officer) {
                GameNotification::create([
                    'character_id' => $officer->id,
                    'type' => 'gang',
                    'icon' => 'fa-shield-halved',
                    'title' => '🛡️ Aanval afgeslagen!',
                    'message' => "Jullie bende heeft een aanval van '{$myGang->name}' met succes afgeslagen!",
                ]);
            }

            foreach ($myGangOfficers as $officer) {
                if ($officer->id !== $lockedCommander->id) {
                    GameNotification::create([
                        'character_id' => $officer->id,
                        'type' => 'gang',
                        'icon' => 'fa-shield-halved',
                        'title' => '⚠️ Aanval mislukt',
                        'message' => "De aanval op '{$targetGang->name}' is afgeslagen. Schade: -{$formattedPowerDamage} power.",
                    ]);
                }
            }

            return back()
                ->with('active_gang_tab', 'oorlog')
                ->with('error', $summary);
        });
    }

    /**
     * Remove a departing member from the appointed shopper and treasurer roles.
     *
     * Gang::isOfficer() also trusts these columns, so a stale value would give the member officer rights again after a rejoin.
     */
    private function clearAppointedRoles(Gang $gang, Character $departed): void
    {
        $changed = false;

        if ((int) $gang->personal_shopper === (int) $departed->id) {
            $gang->personal_shopper = null;
            $changed = true;
        }

        if ((int) $gang->money_magnet === (int) $departed->id) {
            $gang->money_magnet = null;
            $changed = true;
        }

        if ($changed) {
            $gang->save();
        }
    }

    /**
     * Dedicated Bende Uurloon overview and live interactive calculator page.
     */
    public function uurloon(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $gang = $character?->gang;

        $wageDetails = $character ? $this->wageService->getWageDetails($character) : null;
        $properties = $gang ? $gang->properties()->get() : collect();
        $storedVehicles = $gang ? $gang->storedVehicles()->with('vehicle')->get() : collect();
        $npcs = $gang ? $gang->npcs()->get() : collect();

        return view('game.gangs.uurloon', [
            'character' => $character,
            'gang' => $gang,
            'wageDetails' => $wageDetails,
            'breakdown' => $wageDetails['breakdown'] ?? [],
            'properties' => $properties,
            'storedVehicles' => $storedVehicles,
            'npcs' => $npcs,
            'propertyCatalog' => \App\Support\GangPropertyCatalog::all(),
        ]);
    }

    /**
     * Manually claim any accrued gang wages.
     */
    public function claimWage(): RedirectResponse
    {
        $character = Auth::user()->character;
        if (! $character->gang_id) {
            return back()->with('error', 'Je zit momenteel niet in een bende.');
        }

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen bende-uurloon opeisen!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $payout = $this->wageService->payoutPendingWages($character);
        if (! $payout || $payout['amount'] <= 0) {
            $details = $this->wageService->getWageDetails($character);

            return back()->with('info', "Er is nog geen vol uur verstreken sinds je vorige uitbetaling. Volgende automatische uitbetaling over {$details['next_payout_human']}.");
        }

        return back()->with('success', '💰 Succesvol €'.number_format($payout['amount'], 0, ',', '.')." bende-uurloon geïnd voor {$payout['hours']} uur en als contant geld op zak ontvangen!");
    }

    /**
     * Declare an official 24-hour Gang War against a rival gang.
     */
    public function declareWar(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character->gang_id || ! $character->gang) {
            return back()->with('error', 'Je bent geen lid van een bende!');
        }

        $myGang = $character->gang;
        if (! $myGang->isOfficer($character)) {
            return back()->with('error', 'Alleen de leider of een beheerder van de bende kan een officiële bendeoorlog verklaren!');
        }

        $targetGangId = (int) $request->input('target_gang_id');
        $targetGang = Gang::find($targetGangId);

        if (! $targetGang || $targetGang->id === $myGang->id) {
            return back()->with('error', 'Ongeldig doelwit voor de bendeoorlog geselecteerd.');
        }

        if (GangWar::findActiveForGang($myGang->id)) {
            return back()->with('error', 'Jullie bende bevindt zich momenteel al in een actieve 24-uurs bendeoorlog!');
        }

        if (GangWar::findActiveForGang($targetGang->id)) {
            return back()->with('error', "Bende '{$targetGang->name}' bevindt zich al in oorlog met een andere rivaliserende groepering!");
        }

        $entryStake = 250000;
        if ((int) $myGang->treasury < $entryStake) {
            return back()->with('error', 'Er is minimaal €' . number_format($entryStake, 0, ',', '.') . ' in de bende kluis nodig als oorlogsinzet!');
        }

        return DB::transaction(function () use ($myGang, $targetGang, $character, $entryStake) {
            $lockedMyGang = Gang::where('id', $myGang->id)->lockForUpdate()->first();
            $lockedTarget = Gang::where('id', $targetGang->id)->lockForUpdate()->first();

            $lockedMyGang->treasury = max(0, (int) $lockedMyGang->treasury - $entryStake);
            $lockedMyGang->save();

            $war = GangWar::create([
                'challenger_gang_id' => $lockedMyGang->id,
                'defender_gang_id' => $lockedTarget->id,
                'status' => 'active',
                'started_at' => now(),
                'ends_at' => now()->addHours(24),
                'challenger_score' => 0,
                'defender_score' => 0,
                'war_pot' => $entryStake * 2,
                'respect_pot' => 100,
            ]);

            $targetOfficers = $lockedTarget->members()->whereIn('gang_role', ['leader', 'co_leader', 'officer'])->get();
            foreach ($targetOfficers as $officer) {
                GameNotification::create([
                    'character_id' => $officer->id,
                    'type' => 'gang_war',
                    'icon' => 'fa-skull-crossbones',
                    'title' => '🚨 24-Uurs Bendeoorlog Uitgeroepen!',
                    'message' => "Bende '{$lockedMyGang->name}' heeft de oorlog aan jullie verklaard! Vecht 24 uur lang om de zones in de kliniek!",
                ]);
            }

            AuditLogService::log(
                action: 'gang.war_declared',
                category: 'gang',
                description: "Oorlog verklaard: {$lockedMyGang->name} daagt {$lockedTarget->name} uit voor 24-uurs strijd",
                character: $character,
                user: $character->user,
                details: [
                    'war_id' => $war->id,
                    'challenger_gang_id' => $lockedMyGang->id,
                    'defender_gang_id' => $lockedTarget->id,
                ]
            );

            return back()
                ->with('active_gang_tab', 'oorlog')
                ->with('success', "⚔️ OORLOG VERKLAARD! De 24-uurs bendeoorlog tegen '{$lockedTarget->name}' is begonnen! Beheers zones (Luchtplaats, Kantine, Smokkellijn) en schakel tegenstanders uit om oorlogspunten te verzamelen!");
        });
    }

    /**
     * Finalize and settle a 24-hour Gang War.
     */
    public function settleWar(Request $request, GangWar $war): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character->gang_id) {
            return back()->with('error', 'Je bent geen bendelid!');
        }

        if ($war->status !== 'active') {
            return back()->with('info', 'Deze bendeoorlog is al afgerond.');
        }

        if ($war->ends_at && $war->ends_at->isFuture()) {
            $minsLeft = (int) now()->diffInMinutes($war->ends_at);
            return back()->with('info', "De bendeoorlog is nog volop bezig! Resterende tijd: {$minsLeft} minuten.");
        }

        $winner = $war->settle();

        $msg = $winner
            ? "🏆 Bendeoorlog afgesloten! '{$winner->name}' is uitgeroepen tot overwinnaar en claimt de oorlogsbuit en kliniekheerschappij!"
            : "⚖️ Bendeoorlog afgesloten in een gelijkspel!";

        return back()
            ->with('active_gang_tab', 'oorlog')
            ->with('success', $msg);
    }
}
