<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Models\Gang;
use App\Models\GangAlliance;
use App\Models\GangAttack;
use App\Models\GangDonation;
use App\Models\GangRequest;
use App\Models\User;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\GangWageService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $realGangs = Gang::with(['leader', 'members'])->withCount('members')->orderByDesc('treasury')->get();
        $myGang = $character->gang;

        if ($myGang) {
            $myGang->load([
                'members',
                'requests.character',
                'donations.character' => fn ($q) => $q->latest()->take(10),
                'alliances.allyGang',
                'personalShopper',
                'moneyMagnet',
            ]);
        }

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
            $totalSlay = $g->members->sum('respect');
            $gangRankings->push([
                'id' => $g->id,
                'name' => $g->name,
                'tag' => $g->tag,
                'leader_name' => $g->leader?->name ?? 'Onbekend',
                'leader_tag' => '@'.($g->leader?->name ?? 'onbekend'),
                'slay' => $totalSlay >= 1000000 ? number_format($totalSlay / 1000000, 2).'M' : ($totalSlay >= 1000 ? number_format($totalSlay / 1000, 1).'K' : number_format($totalSlay, 0, ',', '.')),
                'raw_slay' => $totalSlay,
                'cash' => '€'.number_format($g->treasury, 0, ',', '.'),
                'icon' => $g->emblem ?? 'fa-crown',
                'members_count' => $g->members_count,
                'is_real' => true,
                'is_open' => (bool) ($g->is_open ?? true),
                'min_respect' => (int) ($g->min_respect ?? 0),
                'has_pending_request' => isset($pendingGangRequestIds[$g->id]),
                'model' => $g,
            ]);
        }

        $topRankings = Character::orderByDesc('respect')->take(10)->get();

        $wagePayout = null;
        if ($myGang) {
            $wagePayout = $this->wageService->payoutPendingWages($character);
            if ($wagePayout && $wagePayout['amount'] > 0) {
                session()->flash('success', '💰 Bende Uurloon Ontvangen! Er is zojuist €'.number_format($wagePayout['amount'], 0, ',', '.')." wit salaris op je bankrekening gestort voor {$wagePayout['hours']} gewerkte uur(en)!");
            }
        }

        $wageDetails = $this->wageService->getWageDetails($character);
        $hourlyWage = $wageDetails['hourly_wage'];
        $dailyWage = $wageDetails['daily_wage'];
        $todayDonations = $myGang ? GangDonation::where('gang_id', $myGang->id)->whereDate('created_at', today())->with('character')->latest()->get() : collect();
        $totalDonatedToday = $todayDonations->sum('amount');

        $memberDonations = $myGang ? GangDonation::where('gang_id', $myGang->id)
            ->with('character')
            ->selectRaw('character_id, sum(amount) as total_amount, count(*) as donation_count')
            ->groupBy('character_id')
            ->orderByDesc('total_amount')
            ->get() : collect();

        $inboundAlliances = $myGang ? GangAlliance::where('ally_gang_id', $myGang->id)->with('gang')->get() : collect();

        $recentAttacks = GangAttack::with(['attackerGang', 'targetGang', 'character'])->latest()->take(15)->get();

        $defaultTab = $myGang ? 'mijn_gang' : 'ranglijst';
        $activeTab = $forceTab ?? $request->query('tab', session('active_gang_tab', $defaultTab));

        return view('game.gangs', compact('character', 'realGangs', 'myGang', 'gangRankings', 'topRankings', 'hourlyWage', 'dailyWage', 'wageDetails', 'todayDonations', 'totalDonatedToday', 'memberDonations', 'inboundAlliances', 'recentAttacks', 'activeTab'));
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
     * Create a new gang (costs 250 Stars).
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

        $starCost = 250;
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
        $character->save();

        return back()->with('success', "Gefeliciteerd! Bende '{$gang->name}' [{$gang->tag}] is succesvol opgericht voor 250 Sterren ⭐!");
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

        if (($gang->min_respect ?? 0) > 0 && ($character->respect ?? 0) < $gang->min_respect) {
            return back()->with('error', "Je hebt minimaal {$gang->min_respect} respect nodig om je aan te melden bij {$gang->name}.");
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
            GameMessage::create([
                'recipient_id' => $gang->leader->id,
                'sender_id' => $character->id,
                'sender_name' => $character->name,
                'subject' => "📩 Nieuwe lidmaatschapsaanvraag voor {$gang->name}",
                'content' => "Patiënt {$character->name} wil zich graag aansluiten bij jouw bende {$gang->name}. Beheer verzoeken via Bende Beheer!",
                'type' => 'gang',
            ]);

            if ($gang->leader->id !== $character->id) {
                GameNotification::create([
                    'character_id' => $gang->leader->id,
                    'type' => 'gang',
                    'icon' => 'fa-user-plus',
                    'title' => 'Nieuw lidmaatschapsverzoek!',
                    'message' => "{$character->name} heeft een verzoek ingediend om lid te worden van '{$gang->name}'!",
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
        if (! $gang || ! $gang->isOfficer($character) || $requestRecord->gang_id !== $gang->id) {
            return back()->with('error', 'Geen bevoegdheid.');
        }

        $action = $request->input('action'); // 'accept' or 'reject'

        if ($action === 'accept') {
            $candidate = $requestRecord->character;
            if ($candidate && ! $candidate->gang_id) {
                $candidate->gang_id = $gang->id;
                $candidate->gang_role = 'member';
                $candidate->last_gang_wage_at = now();
                $candidate->save();

                GameMessage::create([
                    'recipient_id' => $candidate->id,
                    'sender_id' => $character->id,
                    'sender_name' => $gang->name,
                    'subject' => "🎉 Je aanvraag voor {$gang->name} is geaccepteerd!",
                    'content' => "Welkom bij de bende! Je bent nu officieel lid van {$gang->name}.",
                    'type' => 'gang',
                ]);

                GameNotification::create([
                    'character_id' => $candidate->id,
                    'type' => 'gang',
                    'icon' => 'fa-users',
                    'title' => 'Toegelaten tot bende!',
                    'message' => "Gefeliciteerd! Je aanvraag voor '{$gang->name}' is geaccepteerd. Je bent nu lid!",
                ]);
            }

            $requestRecord->status = 'accepted';
            $requestRecord->save();

            // Cancel any other pending requests for this character
            if ($candidate) {
                GangRequest::where('character_id', $candidate->id)
                    ->where('id', '!=', $requestRecord->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'rejected']);
            }

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

        // Leave fee multiplier deduction (only if not disbanding as leader)
        if ($gang && $character->gang_role !== 'leader') {
            $hourlyWage = 150 + (($gang->bling_shops ?? 0) * 50);
            $leaveFee = $hourlyWage * ($gang->leave_fee_multiplier ?? 48);

            if ($leaveFee > 0 && $character->cash > 0) {
                $deducted = min($character->cash, $leaveFee);
                $character->cash -= $deducted;
                $gang->treasury += $deducted;
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

        return back()->with('success', 'Je hebt de bende verlaten.');
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
            'amount' => ['required', 'integer', 'min:50', 'max:5000000'],
        ]);

        $amount = (int) $validated['amount'];
        if ($character->cash < $amount) {
            return back()->with('error', 'Je hebt niet genoeg contant geld om dit bedrag te doneren.');
        }

        $character->cash -= $amount;
        $character->respect += (int) ceil($amount / 2000);
        $character->save();

        $gang = $character->gang;
        $gang->treasury += $amount;
        $gang->save();

        GangDonation::create([
            'gang_id' => $gang->id,
            'character_id' => $character->id,
            'amount' => $amount,
        ]);

        return back()
            ->with('active_gang_tab', 'doneren')
            ->with('success', 'Je hebt €'.number_format($amount, 0, ',', '.').' gedoneerd aan de bende kas!');
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

        if ($actionType === 'avatar') {
            $request->validate([
                'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:4096'],
            ]);
            $path = $request->file('avatar')->store('gangs/avatars', 'public');
            $gang->update(['avatar' => 'storage/'.$path]);

            return back()
                ->with('active_gang_tab', 'instellingen')
                ->with('success', 'Bende avatar succesvol bijgewerkt!');
        }

        if ($actionType === 'banner') {
            $request->validate([
                'banner' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:6144'],
            ]);
            $path = $request->file('banner')->store('gangs/banners', 'public');
            $gang->update(['banner' => 'storage/'.$path]);

            return back()
                ->with('active_gang_tab', 'instellingen')
                ->with('success', 'Bende achtergrond succesvol bijgewerkt!');
        }

        if ($actionType === 'management') {
            if ($character->gang_role !== 'leader') {
                return back()->with('error', 'Alleen de Bendeleider (oprichter) kan het management wijzigen!');
            }

            $validated = $request->validate([
                'leader_id' => ['nullable', 'exists:characters,id'],
                'boss_babe_id' => ['nullable', 'exists:characters,id'],
                'personal_shopper_id' => ['nullable', 'exists:characters,id'],
                'money_magnet_id' => ['nullable', 'exists:characters,id'],
            ]);

            $newLeaderId = (int) ($validated['leader_id'] ?? $validated['boss_babe_id'] ?? $gang->leader_character_id);
            if ($newLeaderId && $newLeaderId !== $gang->leader_character_id) {
                $newLeader = Character::where('id', $newLeaderId)->where('gang_id', $gang->id)->first();
                if ($newLeader) {
                    $character->gang_role = 'co_leader';
                    $character->save();
                    $newLeader->gang_role = 'leader';
                    $newLeader->save();
                    $gang->leader_character_id = $newLeader->id;
                }
            }

            $gang->personal_shopper = ! empty($validated['personal_shopper_id']) ? (int) $validated['personal_shopper_id'] : null;
            $gang->money_magnet = ! empty($validated['money_magnet_id']) ? (int) $validated['money_magnet_id'] : null;
            $gang->save();

            return back()
                ->with('active_gang_tab', 'instellingen')
                ->with('success', 'Bende rollen en management succesvol bijgewerkt!');
        }

        if ($actionType === 'leave_fee') {
            $validated = $request->validate([
                'leave_fee_multiplier' => ['required', 'integer', 'min:0', 'max:5000'],
            ]);

            $gang->update([
                'leave_fee_multiplier' => (int) $validated['leave_fee_multiplier'],
            ]);

            return back()
                ->with('active_gang_tab', 'instellingen')
                ->with('success', 'Verlatingskosten succesvol gewijzigd!');
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
                ->with('success', 'Minimaal respect voor toetreding succesvol ingesteld!');
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
                ->with('success', 'Bende profieltekst succesvol opgeslagen!');
        }

        if ($actionType === 'requests_toggle' || $actionType === 'is_open') {
            $isOpen = $request->boolean('is_open');
            $gang->update([
                'is_open' => $isOpen,
            ]);

            return back()
                ->with('active_gang_tab', 'instellingen')
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
            'amount' => ['required', 'integer', 'min:100', 'max:'.$gang->treasury],
        ], [
            'amount.max' => 'Er zit niet genoeg geld in de bende kas.',
        ]);

        $amount = (int) $validated['amount'];
        $gang->treasury -= $amount;
        $gang->save();

        $character->cash += $amount;
        $character->save();

        return back()
            ->with('active_gang_tab', 'beheer')
            ->with('beheer_tab', 'instellingen')
            ->with('success', 'Je hebt €'.number_format($amount, 0, ',', '.').' opgenomen uit de bende kas!');
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
            'item_type' => ['required', 'string', 'in:bling_shops,protection,power,max_members'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
        ], [
            'quantity.min' => 'Kies minimaal 1 om aan te schaffen.',
            'quantity.max' => 'Je kunt maximaal 1.000.000 per keer aanschaffen.',
        ]);

        $itemType = $validated['item_type'];
        $qty = (int) $validated['quantity'];

        switch ($itemType) {
            case 'bling_shops':
                $unitPrice = 50000;
                $totalCost = $unitPrice * $qty;
                if ($gang->treasury < $totalCost) {
                    return back()->with('error', 'Er zit niet genoeg geld in de bende kas (gangpot)! Er is momenteel €'.number_format($gang->treasury, 0, ',', '.').' aanwezig, maar €'.number_format($totalCost, 0, ',', '.').' is vereist. Persoonlijk zakgeld kan hier niet direct gebruikt worden. Doneer eerst geld aan de bende kas via het tabblad \'Doneren\'.');
                }

                $gang->treasury -= $totalCost;
                $gang->bling_shops = ($gang->bling_shops ?? 0) + $qty;
                $gang->save();

                return back()
                    ->with('active_gang_tab', 'winkel')
                    ->with('success', "Succesvol {$qty} Smokkelkiosk(en) aangeschaft vanuit de bende kas! Het uurloon is verhoogd!");

            case 'protection':
                $unitPrice = 15000;
                $totalCost = $unitPrice * $qty;
                if ($gang->treasury < $totalCost) {
                    return back()->with('error', 'Er zit niet genoeg geld in de bende kas (gangpot)! Er is momenteel €'.number_format($gang->treasury, 0, ',', '.').' aanwezig, maar €'.number_format($totalCost, 0, ',', '.').' is vereist. Persoonlijk zakgeld kan hier niet direct gebruikt worden. Doneer eerst geld aan de bende kas via het tabblad \'Doneren\'.');
                }

                $gang->treasury -= $totalCost;
                $gang->protection_points = ($gang->protection_points ?? 1000) + ($qty * 5000);
                $gang->save();

                return back()
                    ->with('active_gang_tab', 'winkel')
                    ->with('success', "Succesvol {$qty}x Bescherming aangeschaft vanuit de bende kas (+".number_format($qty * 5000, 0, ',', '.').' verdedigingspunten)!');

            case 'power':
                $unitPrice = 15000;
                $totalCost = $unitPrice * $qty;
                if ($gang->treasury < $totalCost) {
                    return back()->with('error', 'Er zit niet genoeg geld in de bende kas (gangpot)! Er is momenteel €'.number_format($gang->treasury, 0, ',', '.').' aanwezig, maar €'.number_format($totalCost, 0, ',', '.').' is vereist. Persoonlijk zakgeld kan hier niet direct gebruikt worden. Doneer eerst geld aan de bende kas via het tabblad \'Doneren\'.');
                }

                $gang->treasury -= $totalCost;
                $gang->attack_power = ($gang->attack_power ?? 500) + ($qty * 2500);
                $gang->save();

                return back()
                    ->with('active_gang_tab', 'winkel')
                    ->with('success', "Succesvol {$qty}x Power aangeschaft vanuit de bende kas (+".number_format($qty * 2500, 0, ',', '.').' aanvalspunten)!');

            case 'max_members':
                $starPrice = 25;
                $totalStars = $starPrice * $qty;
                $currentMax = $gang->max_members ?? 10;
                $newMax = $currentMax + $qty;

                if ($newMax > 35) {
                    return back()->with('error', "Het maximale bende limiet is 35 leden (huidig: {$currentMax}).");
                }

                // Check character stars or gang stars
                if (($character->stars ?? 0) >= $totalStars) {
                    $character->stars -= $totalStars;
                    $character->save();
                } elseif (($gang->gang_stars ?? 0) >= $totalStars && $gang->isOfficer($character)) {
                    $gang->gang_stars -= $totalStars;
                } else {
                    return back()->with('error', "Niet genoeg Sterren ⭐ om {$qty} extra ledenplekken te kopen ({$totalStars} Sterren ⭐ vereist).");
                }

                $gang->max_members = $newMax;
                $gang->save();

                return back()
                    ->with('active_gang_tab', 'winkel')
                    ->with('success', "Capaciteit uitgebreid met {$qty} leden! Nieuw limiet: {$gang->max_members} leden.");
        }

        return back()->with('active_gang_tab', 'winkel');
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
        if (($character->stars ?? 0) < $stars) {
            return back()->with('error', "Je hebt slechts {$character->stars} Sterren ⭐.");
        }

        $character->stars -= $stars;
        $character->respect += $stars * 5;
        $character->save();

        $gang->gang_stars = ($gang->gang_stars ?? 0) + $stars;
        $gang->save();

        return back()
            ->with('active_gang_tab', 'doneren')
            ->with('success', "Je hebt {$stars} Sterren ⭐ gedoneerd aan {$gang->name}! (+".($stars * 5).' Respect voor jou).');
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

        return back()->with('success', '💰 Succesvol €'.number_format($payout['amount'], 0, ',', '.')." bende-uurloon geïnd voor {$payout['hours']} uur en bijgeschreven op je bankrekening!");
    }
}
