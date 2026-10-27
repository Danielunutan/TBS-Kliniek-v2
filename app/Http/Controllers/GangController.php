<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Models\Gang;
use App\Models\GangAlliance;
use App\Models\GangAttack;
use App\Models\GangDonation;
use App\Models\GangItem;
use App\Models\GangRequest;
use App\Models\User;
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
        $realGangs = Gang::with(['leader', 'members'])->withCount('members')->get();
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
                'model' => $g,
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

        return view('game.gangs', compact('character', 'realGangs', 'myGang', 'gangRankings', 'topRankings', 'hourlyWage', 'dailyWage', 'wageDetails', 'todayDonations', 'totalDonatedToday', 'todayRecentLogs', 'memberDonations', 'inboundAlliances', 'recentAttacks', 'gangMembersData', 'onlineMembersCount', 'activeTab', 'myInventoryItems', 'warsWon', 'warsLost', 'totalRespect'));
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
        ])
            ->where('id', $gang)
            ->orWhere('tag', $gang)
            ->orWhere('name', $gang)
            ->firstOrFail();

        $totalRespect = (float) $gangModel->members->sum('respect');
        $hourlyWage = $this->wageService->calculateHourlyWage($gangModel);
        $dailyWage = $this->wageService->calculateDailyWage($gangModel);

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
     * Create a new gang (costs 25 Stars).
     */
    public function create(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen bende oprichten!');
        }

        if ($character->gang_id) {
            return back()->with('error', 'Je bent al lid van een bende. Verlaat eerst je huidige bende.');
        }

        $starCost = 25;
        if (($character->stars ?? 0) < $starCost) {
            return back()->with('error', "Een bende oprichten kost {$starCost} Sterren ⭐! Je hebt momenteel {$character->stars} sterren.");
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:25', 'unique:gangs,name'],
            'tag' => ['required', 'string', 'min:2', 'max:4'],
            'specialty' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:300'],
        ]);

        $character->stars -= $starCost;
        $character->save();

        $gang = Gang::create([
            'name' => strip_tags($validated['name']),
            'tag' => strtoupper(strip_tags($validated['tag'])),
            'leader_character_id' => $character->id,
            'specialty' => strip_tags($validated['specialty'] ?? 'Kantine Controle'),
            'description' => strip_tags($validated['description'] ?? 'Een nieuwe gevreesde bende in de kliniek.'),
            'treasury' => 1000,
            'emblem' => 'fa-crown',
        ]);

        $character->gang_id = $gang->id;
        $character->gang_role = 'leader';
        $character->last_gang_wage_at = now();
        $character->is_vip = true;
        $currentExpiry = ($character->vip_expires_at && $character->vip_expires_at->isFuture())
            ? $character->vip_expires_at
            : now();
        $character->vip_expires_at = $currentExpiry->copy()->addDays(3);
        $character->save();

        return back()->with('success', "Gefeliciteerd! Bende '{$gang->name}' [{$gang->tag}] is succesvol opgericht! Je ontvangt direct 3 dagen gratis VIP status ⭐!");
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

        $action = $request->input('action') ?? $request->input('decision'); // 'accept' or 'reject'

        if ($action === 'accept') {
            $maxMembers = (int) ($gang->max_members ?? 10);
            if ($gang->members()->count() >= $maxMembers) {
                return back()
                    ->with('active_gang_tab', 'beheer')
                    ->with('beheer_tab', 'verzoeken')
                    ->with('error', "De bende zit vol! De maximale capaciteit is {$maxMembers} leden.");
            }

            $candidate = $requestRecord->character;
            if (! $candidate) {
                $requestRecord->status = 'rejected';
                $requestRecord->save();

                return back()
                    ->with('active_gang_tab', 'beheer')
                    ->with('beheer_tab', 'verzoeken')
                    ->with('error', 'Kandidaat niet gevonden.');
            }

            if ($candidate->gang_id) {
                $requestRecord->status = 'rejected';
                $requestRecord->save();

                return back()
                    ->with('active_gang_tab', 'beheer')
                    ->with('beheer_tab', 'verzoeken')
                    ->with('error', "{$candidate->name} is inmiddels al lid van een andere bende.");
            }

            if (($gang->min_respect ?? 0) > 0 && ($candidate->respect ?? 0) < (int) $gang->min_respect) {
                return back()
                    ->with('active_gang_tab', 'beheer')
                    ->with('beheer_tab', 'verzoeken')
                    ->with('error', 'Deze kandidaat voldoet niet meer aan de toelatingseis van '.number_format($gang->min_respect, 0, ',', '.').' respect! (Kandidaat heeft: '.number_format($candidate->respect ?? 0, 0, ',', '.').' respect)');
            }

            DB::transaction(function () use ($candidate, $gang, $requestRecord, $character) {
                $candidate->gang_id = $gang->id;
                $candidate->gang_role = 'member';
                $candidate->last_gang_wage_at = now();
                $candidate->is_vip = true;
                $currentExpiry = ($candidate->vip_expires_at && $candidate->vip_expires_at->isFuture())
                    ? $candidate->vip_expires_at
                    : now();
                $candidate->vip_expires_at = $currentExpiry->copy()->addDays(3);
                $candidate->save();

                GameMessage::create([
                    'recipient_id' => $candidate->id,
                    'sender_id' => $character->id,
                    'sender_name' => $gang->name,
                    'subject' => "🎉 Je aanvraag voor {$gang->name} is geaccepteerd (+3 Dagen Gratis VIP)!",
                    'content' => "Welkom bij de bende! Je bent nu officieel lid van {$gang->name}. Als welkomstgeschenk is er direct 3 Dagen Gratis VIP geactiveerd op je account!",
                    'type' => 'gang',
                ]);

                GameNotification::create([
                    'character_id' => $candidate->id,
                    'type' => 'gang',
                    'icon' => 'fa-users',
                    'title' => 'Toegelaten tot bende!',
                    'message' => "Gefeliciteerd! Je aanvraag voor '{$gang->name}' is geaccepteerd. Je bent nu lid!",
                ]);

                $requestRecord->status = 'accepted';
                $requestRecord->save();

                // Cancel any other pending requests for this character
                GangRequest::where('character_id', $candidate->id)
                    ->where('id', '!=', $requestRecord->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'rejected']);
            });

            return back()
                ->with('active_gang_tab', 'beheer')
                ->with('beheer_tab', 'verzoeken')
                ->with('success', "Aanvraag geaccepteerd! {$candidate->name} is nu lid.");
        }

        $requestRecord->status = 'rejected';
        $requestRecord->save();

        if ($candidate = $requestRecord->character) {
            GameNotification::create([
                'character_id' => $candidate->id,
                'type' => 'gang',
                'icon' => 'fa-xmark',
                'title' => 'Aanvraag afgewezen',
                'message' => "Je verzoek om lid te worden van bende '{$gang->name}' is afgewezen.",
            ]);
        }

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

        $gang = $character->gang;
        $leaveFee = 0;

        // Leave fee multiplier deduction (only if not disbanding as leader)
        if ($gang && $character->gang_role !== 'leader') {
            $hourlyWage = 150 + (($gang->bling_shops ?? 0) * 50);
            $multiplier = min(50, max(0, (int) ($gang->leave_fee_multiplier ?? 0)));
            $leaveFee = $hourlyWage * $multiplier;

            if ($leaveFee > 0) {
                if ($character->cash < $leaveFee) {
                    return back()->with('error', 'Je kunt de bende niet verlaten: de vertrekboete bedraagt €'.number_format($leaveFee, 0, ',', '.')." ({$multiplier}x uurloon). Je hebt momenteel slechts €".number_format($character->cash, 0, ',', '.').' contant geld op zak!');
                }

                $character->cash -= $leaveFee;
                $gang->treasury += $leaveFee;
                $gang->save();
            }
        }

        if ($character->gang_role === 'leader' && $gang) {
            $nextMember = $gang->members()->where('id', '!=', $character->id)->first();
            if ($nextMember) {
                $gang->leader_character_id = $nextMember->id;
                $gang->save();
                $nextMember->gang_role = 'leader';
                $nextMember->save();
            } else {
                $gang->delete();
            }
        }

        $this->wageService->payoutPendingWages($character);
        $character->gang_id = null;
        $character->gang_role = 'none';
        $character->last_gang_wage_at = null;
        $character->save();

        if ($leaveFee > 0) {
            return redirect()->route('gangs.index')->with('success', 'Je hebt de bende verlaten. Er is €'.number_format($leaveFee, 0, ',', '.').' overgemaakt naar de bende kluis als vertrekboete.');
        }

        return redirect()->route('gangs.index')->with('success', 'Je hebt de bende succesvol verlaten.');
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

            if (! $lockedCharacter || $lockedCharacter->cash < $amount) {
                return back()
                    ->with('active_gang_tab', 'doneren')
                    ->with('error', 'Je hebt niet genoeg contant geld om dit bedrag te doneren.');
            }

            /** @var Gang $lockedGang */
            $lockedGang = Gang::where('id', $lockedCharacter->gang_id)->lockForUpdate()->first();
            if (! $lockedGang) {
                return back()->with('error', 'Je bent geen lid van een geldige bende.');
            }

            // Deduct cash and calculate balanced respect (capped at 5,000 per donation to prevent respect-overflow)
            $lockedCharacter->cash -= $amount;
            $respectGained = min(5000, (int) ceil($amount / 2000));
            $lockedCharacter->respect += $respectGained;
            $lockedCharacter->save();

            // Add cash to gang treasury
            $lockedGang->treasury += $amount;
            $lockedGang->save();

            GangDonation::create([
                'gang_id' => $lockedGang->id,
                'character_id' => $lockedCharacter->id,
                'amount' => $amount,
            ]);

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
                'leader_id' => ['nullable', 'exists:characters,id'],
                'boss_babe_id' => ['nullable', 'exists:characters,id'],
                'co_leader_id' => ['nullable', 'exists:characters,id'],
                'personal_shopper_id' => ['nullable', 'exists:characters,id'],
                'money_magnet_id' => ['nullable', 'exists:characters,id'],
            ]);

            $newLeaderId = (int) ($validated['leader_id'] ?? $validated['boss_babe_id'] ?? $gang->leader_character_id);
            if ($newLeaderId && $newLeaderId !== (int) $gang->leader_character_id) {
                $newLeader = Character::where('id', $newLeaderId)->where('gang_id', $gang->id)->first();
                if ($newLeader) {
                    $character->gang_role = 'co_leader';
                    $character->save();
                    $newLeader->gang_role = 'leader';
                    $newLeader->save();
                    $gang->leader_character_id = $newLeader->id;
                }
            }

            $oldShopperId = (int) $gang->personal_shopper;
            $oldMagnetId = (int) $gang->money_magnet;

            $newShopperId = ! empty($validated['personal_shopper_id']) ? (int) $validated['personal_shopper_id'] : null;
            $newMagnetId = ! empty($validated['money_magnet_id']) ? (int) $validated['money_magnet_id'] : null;
            $newCoLeaderId = ! empty($validated['co_leader_id']) ? (int) $validated['co_leader_id'] : null;

            $gang->personal_shopper = $newShopperId;
            $gang->money_magnet = $newMagnetId;
            $gang->save();

            // All members assigned to key roles must be co_leader (unless they are the gang leader)
            $targetCoLeaderIds = array_filter(
                [$newCoLeaderId, $newShopperId, $newMagnetId],
                fn ($id) => ! empty($id) && (int) $id !== (int) $gang->leader_character_id
            );

            foreach ($targetCoLeaderIds as $cid) {
                $c = Character::where('id', $cid)->where('gang_id', $gang->id)->first();
                if ($c && $c->gang_role !== 'leader') {
                    $c->gang_role = 'co_leader';
                    $c->save();
                }
            }

            // Demote previously appointed role holders if they are no longer in any key role
            $prevKeyOfficers = array_unique(array_filter([$oldShopperId, $oldMagnetId]));
            foreach ($prevKeyOfficers as $pid) {
                if (! in_array($pid, $targetCoLeaderIds, true) && (int) $pid !== (int) $gang->leader_character_id) {
                    $prevChar = Character::where('id', $pid)->where('gang_id', $gang->id)->first();
                    if ($prevChar && $prevChar->gang_role === 'co_leader') {
                        $prevChar->gang_role = 'member';
                        $prevChar->save();
                    }
                }
            }

            return back()
                ->with('active_gang_tab', 'instellingen')
                ->with('settings_tab', 'rollen')
                ->with('success', 'Bende rollen en management succesvol bijgewerkt! Aangewezen leden hebben direct co-op rechten.');
        }

        if ($actionType === 'leave_fee') {
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

        if ($member->gang_id !== $gang->id) {
            return back()->with('error', 'Deze patiënt zit niet in jouw bende.');
        }

        if ($member->id === $character->id || $member->gang_role === 'leader') {
            return back()->with('error', 'Je kunt de bendeleider niet verwijderen.');
        }

        $this->wageService->payoutPendingWages($member);
        $member->gang_id = null;
        $member->gang_role = 'none';
        $member->last_gang_wage_at = null;
        $member->save();

        GameMessage::create([
            'recipient_id' => $member->id,
            'sender_id' => $character->id,
            'sender_name' => $gang->name,
            'subject' => "⚠️ Je bent verwijderd uit bende {$gang->name}",
            'content' => "Patiënt {$member->name},\n\nJe bent zojuist door {$character->name} uit de factie '{$gang->name}' gezet.",
            'type' => 'gang',
        ]);

        return back()
            ->with('active_gang_tab', 'beheer')
            ->with('beheer_tab', 'leden')
            ->with('success', "{$member->name} is uit de bende verwijderd.");
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

        if ($member->gang_id !== $gang->id) {
            return back()->with('error', 'Deze patiënt zit niet in jouw bende.');
        }

        $role = $request->input('role');
        if (! in_array($role, ['co_leader', 'member', 'leader'], true)) {
            return back()->with('error', 'Ongeldige rol gekozen.');
        }

        if ($role === 'leader') {
            $gang->leader_character_id = $member->id;
            $gang->save();

            $character->gang_role = 'co_leader';
            $character->save();

            $member->gang_role = 'leader';
            $member->save();

            return back()
                ->with('active_gang_tab', 'beheer')
                ->with('beheer_tab', 'leden')
                ->with('success', "Leiderschap van {$gang->name} overgedragen aan {$member->name}!");
        }

        $member->gang_role = $role;
        $member->save();

        $roleTitle = $role === 'co_leader' ? 'Co-Leider' : 'Gewoon Lid';

        return back()
            ->with('active_gang_tab', 'beheer')
            ->with('beheer_tab', 'leden')
            ->with('success', "{$member->name} is nu ingesteld als {$roleTitle}!");
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

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:100'],
        ]);

        $amount = (int) $validated['amount'];

        return DB::transaction(function () use ($gang, $character, $amount): RedirectResponse {
            /** @var Gang $lockedGang */
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();

            if ($lockedGang->treasury < $amount) {
                return back()->with('error', 'Er zit niet genoeg geld in de bende kas.');
            }

            $lockedGang->treasury -= $amount;
            $lockedGang->save();

            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            $lockedCharacter->cash += $amount;
            $lockedCharacter->save();

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

        $gang = $character->gang;
        if (! $gang || $character->gang_role !== 'leader') {
            return back()->with('error', 'Alleen de oprichter/leider kan de bende definitief opheffen!');
        }

        // Unlink all members
        $gang->members()->update([
            'gang_id' => null,
            'gang_role' => 'none',
        ]);

        $gang->delete();

        return back()->with('success', "Bende '{$gang->name}' is definitief opgeheven.");
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
                    $unitPrice = 50000;
                    $totalCost = $unitPrice * $qty;
                    if ($lockedGang->treasury < $totalCost) {
                        return back()->with('error', 'Er zit niet genoeg geld in de bende kas (gangpot)! Er is momenteel €'.number_format($lockedGang->treasury, 0, ',', '.').' aanwezig, maar €'.number_format($totalCost, 0, ',', '.').' is vereist. Persoonlijk zakgeld kan hier niet direct gebruikt worden. Doneer eerst geld aan de bende kas via het tabblad \'Doneren\'.');
                    }

                    $lockedGang->treasury -= $totalCost;
                    $lockedGang->bling_shops = ($lockedGang->bling_shops ?? 0) + $qty;
                    $lockedGang->save();

                    return back()
                        ->with('active_gang_tab', 'winkel')
                        ->with('success', "Succesvol {$qty} Smokkelkiosk(en) aangeschaft vanuit de bende kas! Het uurloon is verhoogd!");

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
                    $lockedGang->attack_power = ($lockedGang->attack_power ?? 500) + ($qty * 2500);
                    $lockedGang->save();

                    return back()
                        ->with('active_gang_tab', 'winkel')
                        ->with('success', "Succesvol {$qty}x Power aangeschaft vanuit de bende kas (+".number_format($qty * 2500, 0, ',', '.').' aanvalspunten)!');

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

        $validated = $request->validate([
            'stars' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        $stars = (int) $validated['stars'];

        return DB::transaction(function () use ($gang, $character, $stars): RedirectResponse {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            /** @var Gang $lockedGang */
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();

            if (($lockedCharacter->stars ?? 0) < $stars) {
                return back()->with('error', "Je hebt slechts {$lockedCharacter->stars} Sterren ⭐.");
            }

            $lockedCharacter->stars -= $stars;
            $lockedCharacter->respect += $stars * 5;
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

        $gang = $character->gang;

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $requestedQty = (int) $validated['quantity'];

        return DB::transaction(function () use ($gang, $character, $requestedQty): RedirectResponse {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            /** @var Gang $lockedGang */
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();

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

            $lockedCharacter->masks -= $quantity;
            $lockedCharacter->respect += max(1, (int) round($quantity * 0.5));
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

        $gang = $character->gang;

        $validated = $request->validate([
            'character_item_id' => ['required', 'integer', 'exists:character_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $requestedQty = (int) $validated['quantity'];
        $charItemId = (int) $validated['character_item_id'];

        return DB::transaction(function () use ($gang, $character, $charItemId, $requestedQty): RedirectResponse {
            /** @var CharacterItem|null $charItem */
            $charItem = CharacterItem::where('id', $charItemId)
                ->where('character_id', $character->id)
                ->lockForUpdate()
                ->with('item')
                ->first();

            if (! $charItem || $charItem->quantity < 1) {
                return back()->with('error', 'Dit voorwerp bevindt zich niet in je opbergplaats.');
            }

            /** @var Gang $lockedGang */
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();

            $quantity = min($requestedQty, $charItem->quantity);

            // Check gang depot storage capacity
            $storageCap = $lockedGang->total_storage_capacity;
            $storageUsed = $lockedGang->getStorageUsed();
            if ($storageUsed + $quantity > $storageCap) {
                $spaceLeft = max(0, $storageCap - $storageUsed);

                return back()->with('error', "Het Bende Depot zit te vol ({$storageUsed}/{$storageCap} plekken bezet)! Er passen maximaal {$spaceLeft} extra items bij. Koop meer opslagpanden.");
            }

            $itemName = $charItem->item->name;
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

            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            $lockedCharacter->respect += max(1, $quantity);
            $lockedCharacter->save();

            return back()
                ->with('active_gang_tab', 'depot')
                ->with('success', "📦 Succesvol {$quantity}x {$itemName} gedoneerd aan het Bende Depot!");
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

        $gang = $character->gang;
        if (! $gang->isOfficer($character)) {
            return back()->with('error', 'Alleen bendeleiders of beheerders kunnen spullen uit het Bende Depot verkopen!');
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $requestedQty = (int) $validated['quantity'];

        return DB::transaction(function () use ($gang, $requestedQty): RedirectResponse {
            /** @var Gang $lockedGang */
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();

            $currentMasks = (int) ($lockedGang->masks ?? 0);
            if ($currentMasks < 1) {
                return back()->with('error', 'Er liggen geen maskers in het Bende Depot.');
            }

            $quantity = min($requestedQty, $currentMasks);
            $pricePerMask = 750;
            $totalEarned = $pricePerMask * $quantity;

            $lockedGang->masks -= $quantity;
            $lockedGang->treasury += $totalEarned;
            $lockedGang->save();

            $formattedCash = number_format($totalEarned, 0, ',', '.');

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

        $gang = $character->gang;
        if (! $gang->isOfficer($character)) {
            return back()->with('error', 'Alleen bendeleiders of beheerders kunnen bende-eigendommen verkopen!');
        }

        $requestedQty = max(1, (int) $request->input('quantity', 1));

        return DB::transaction(function () use ($gang, $gangItem, $requestedQty): RedirectResponse {
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
            $pricePerUnit = max(500, (int) round(($item->price_kc > 0 ? $item->price_kc : 1000) * 0.6));
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

        // Actual attack power & protection points calculation
        $attackerPower = max(500, (int) ($myGang->attack_power ?? 500));
        $targetDefense = max(500, (int) ($targetGang->protection_points ?? 1000));

        // Randomized battle rolls (+/- 15% variance)
        $attackRoll = (int) round($attackerPower * (mt_rand(85, 115) / 100));
        $defenseRoll = (int) round($targetDefense * (mt_rand(85, 115) / 100));

        if ($attackRoll > $defenseRoll) {
            // Victory: Loot cash, reduce defender's protection points, earn respect
            $stolenCash = $targetGang->treasury > 0
                ? (int) round($targetGang->treasury * (mt_rand(8, 16) / 100))
                : 0;
            $defenseDamage = max(250, (int) round($targetDefense * (mt_rand(10, 20) / 100)));
            $respectGain = round(mt_rand(25, 60) / 10, 2); // 2.5 - 6.0 respect

            $targetGang->treasury = max(0, $targetGang->treasury - $stolenCash);
            $targetGang->protection_points = max(250, $targetGang->protection_points - $defenseDamage);
            $targetGang->shield_until = now()->addMinutes(15);
            $targetGang->save();

            $myGang->treasury += $stolenCash;
            $myGang->last_attack_at = now();
            $myGang->save();

            $character->respect += $respectGain;
            $character->save();

            $summary = "⚔️ OVERWINNING! {$myGang->name} heeft {$targetGang->name} succesvol overvallen! (Power: {$attackerPower} vs Bescherming: {$targetDefense}). Buit: €".number_format($stolenCash, 0, ',', '.')." geplunderd, verdediging van tegenstander -{$defenseDamage} punten, +{$respectGain} respect!";

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

            if ($targetGang->leader) {
                GameMessage::create([
                    'recipient_id' => $targetGang->leader->id,
                    'sender_id' => $character->id,
                    'sender_name' => "{$character->name} [{$myGang->name}]",
                    'subject' => "🚨 [Bende Oorlog] Bende {$myGang->name} heeft jullie aangevallen!",
                    'content' => "Patiënt {$targetGang->leader->name},\n\nJullie bende '{$targetGang->name}' is zojuist aangevallen door '{$myGang->name}' onder leiding van {$character->name}.\nHun power ({$attackerPower}) brak door jullie verdediging ({$targetDefense}) heen!\nZe hebben €".number_format($stolenCash, 0, ',', '.')." uit de kas geplunderd en jullie verdediging met {$defenseDamage} punten verlaagd.\nJullie bende geniet nu 15 minuten schildbescherming.",
                    'type' => 'gang',
                ]);
            }

            return back()
                ->with('active_gang_tab', 'oorlog')
                ->with('success', $summary);
        }

        // Defeat: Target defense held, attacker loses some attack power
        $powerDamage = max(150, (int) round($attackerPower * (mt_rand(5, 12) / 100)));
        $myGang->attack_power = max(250, $myGang->attack_power - $powerDamage);
        $myGang->last_attack_at = now();
        $myGang->save();

        $summary = "🛡️ AANVAL AFGESLAGEN! Bende {$targetGang->name} verdedigde zich met succes tegen {$myGang->name}! (Power: {$attackerPower} vs Bescherming: {$targetDefense}). Jullie leden verliezen (-{$powerDamage} power).";

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

        if ($targetGang->leader) {
            $targetGang->leader->respect += 2.0;
            $targetGang->leader->save();

            GameMessage::create([
                'recipient_id' => $targetGang->leader->id,
                'sender_id' => $character->id,
                'sender_name' => "{$character->name} [{$myGang->name}]",
                'subject' => "🛡️ [Bende Oorlog] Aanval van {$myGang->name} afgeslagen!",
                'content' => "Patiënt {$targetGang->leader->name},\n\nBende '{$myGang->name}' probeerde jullie bende aan te vallen, maar jullie verdediging ({$targetDefense} punten) hield stand!\nDe vijand verloor {$powerDamage} aanvalskracht. Je hebt +2.0 respect verdiend voor de verdediging.",
                'type' => 'gang',
            ]);
        }

        return back()
            ->with('active_gang_tab', 'oorlog')
            ->with('error', $summary);
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

        $payout = $this->wageService->payoutPendingWages($character);
        if (! $payout || $payout['amount'] <= 0) {
            $details = $this->wageService->getWageDetails($character);

            return back()->with('info', "Er is nog geen vol uur verstreken sinds je vorige uitbetaling. Volgende automatische uitbetaling over {$details['next_payout_human']}.");
        }

        return back()->with('success', '💰 Succesvol €'.number_format($payout['amount'], 0, ',', '.')." bende-uurloon geïnd voor {$payout['hours']} uur en als contant geld op zak ontvangen!");
    }
}
