<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Gang;
use App\Models\GangNpc;
use App\Models\GangProperty;
use App\Models\User;
use App\Support\GangPropertyCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GangPropertyController extends Controller
{
    /**
     * Show the Gang Properties & Real Estate overview.
     */
    public function index(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;

        if (! $character->gang_id) {
            return redirect()->route('gangs.index')
                ->with('info', 'Sluit je eerst aan bij een bende of richt een bende op om bende panden te beheren!');
        }

        /** @var Gang $gang */
        $gang = $character->gang;
        $gang->load(['properties', 'npcs.assignedProperty', 'leader']);

        $catalog = GangPropertyCatalog::all();
        $npcCatalog = GangPropertyCatalog::npcs();

        $ownedProperties = $gang->properties->keyBy('property_key');
        $hiredNpcs = $gang->npcs;

        $totalStorage = $gang->total_storage_capacity;
        $totalVehicles = $gang->total_vehicle_slots;
        $totalNpcCap = $gang->total_npc_capacity;
        $availableNpcSlots = max(0, $totalNpcCap - $hiredNpcs->count());

        $isOfficer = $gang->isOfficer($character);

        return view('game.gangs.panden', compact(
            'character',
            'gang',
            'catalog',
            'npcCatalog',
            'ownedProperties',
            'hiredNpcs',
            'totalStorage',
            'totalVehicles',
            'totalNpcCap',
            'availableNpcSlots',
            'isOfficer'
        ));
    }

    /**
     * Purchase a property for the gang using gang treasury.
     */
    public function buy(Request $request, string $propertyKey): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $gang = $character->gang;

        if (! $gang || ! $gang->isOfficer($character)) {
            return back()->with('error', 'Alleen de bendeleider of beheerders kunnen panden aanschaffen voor de bende.');
        }

        $catalogProp = GangPropertyCatalog::get($propertyKey);
        if (! $catalogProp) {
            return back()->with('error', 'Ongeldig pand geselecteerd.');
        }

        if ($gang->properties()->where('property_key', $propertyKey)->exists()) {
            return back()->with('error', 'Jouw bende bezit dit pand al!');
        }

        $price = (int) $catalogProp['price'];
        if ($gang->treasury < $price) {
            $shortage = number_format($price - $gang->treasury, 0, ',', '.');

            return back()->with('error', "Niet genoeg geld in de bendekas! Je komt nog €{$shortage} tekort.");
        }

        DB::transaction(function () use ($gang, $propertyKey, $catalogProp, $price): void {
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();
            $lockedGang->treasury -= $price;
            $lockedGang->save();

            GangProperty::create([
                'gang_id' => $lockedGang->id,
                'property_key' => $propertyKey,
                'name' => $catalogProp['name'],
                'type' => $catalogProp['category'],
                'purchase_price' => $price,
                'storage_capacity' => $catalogProp['storage_capacity'] ?? 0,
                'vehicle_slots' => $catalogProp['vehicle_slots'] ?? 0,
                'npc_capacity' => $catalogProp['npc_capacity'] ?? 0,
                'production_duration_minutes' => $catalogProp['production_duration_minutes'] ?? 0,
                'production_reward_cash' => $catalogProp['production_reward_cash'] ?? 0,
                'production_reward_respect' => $catalogProp['production_reward_respect'] ?? 0,
                'production_status' => 'idle',
            ]);
        });

        return back()->with('success', "🎉 Gefeliciteerd! Jouw bende is nu de trotse eigenaar van '{$catalogProp['name']}'!");
    }

    /**
     * Start a production cycle on a production facility.
     */
    public function startProduction(Request $request, GangProperty $property): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $gang = $character->gang;

        if (! $gang || ! $gang->isOfficer($character) || (int) $property->gang_id !== (int) $gang->id) {
            return back()->with('error', 'Alleen bendebeheerders kunnen productieprocessen opstarten.');
        }

        if (! $property->isProduction()) {
            return back()->with('error', 'Dit pand heeft geen productiefaciliteiten.');
        }

        if ($property->isProducing()) {
            return back()->with('error', 'Er loopt momenteel al een actieve productiecyclus in dit pand!');
        }

        if ($property->isReadyToClaim()) {
            return back()->with('error', 'De vorige productiecyclus is voltooid! Claim eerst de opbrengst.');
        }

        $durationMinutes = $property->production_duration_minutes;

        // Check if an assigned NPC speeds up production
        $assignedNpc = GangNpc::where('gang_id', $gang->id)
            ->where('assigned_property_id', $property->id)
            ->where('bonus_type', 'production_speed')
            ->first();

        if ($assignedNpc) {
            $reduction = $assignedNpc->bonus_value / 100;
            $durationMinutes = (int) max(15, round($durationMinutes * (1 - $reduction)));
        }

        $property->update([
            'production_status' => 'producing',
            'production_started_at' => now(),
            'production_ends_at' => now()->addMinutes($durationMinutes),
        ]);

        return back()->with('success', "⚙️ Productiecyclus succesvol gestart in '{$property->name}'! Over {$durationMinutes} minuten is de lading gereed om te claimen.");
    }

    /**
     * Claim completed production cycle rewards into the gang treasury.
     */
    public function claimProduction(Request $request, GangProperty $property): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $gang = $character->gang;

        if (! $gang || (int) $property->gang_id !== (int) $gang->id) {
            return back()->with('error', 'Je kunt alleen opbrengsten van je eigen bende panden claimen.');
        }

        if (! $property->isReadyToClaim()) {
            return back()->with('error', 'De productie is nog bezig en kan nog niet worden geclaimd.');
        }

        $rewards = $property->getEffectiveRewards();
        $cashReward = $rewards['cash'];
        $respectReward = $rewards['respect'];

        DB::transaction(function () use ($gang, $property, $cashReward, $character, $respectReward): void {
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();
            $lockedGang->treasury += $cashReward;
            $lockedGang->save();

            // Reward respect to the claiming player
            $character->increment('respect', $respectReward);

            $property->update([
                'production_status' => 'idle',
                'production_started_at' => null,
                'production_ends_at' => null,
            ]);
        });

        $formattedCash = number_format($cashReward, 0, ',', '.');

        return back()->with('success', "💰 Buit veiliggesteld! +€{$formattedCash} contant toegevoegd aan de bendekas en +{$respectReward} Respect verdiend!");
    }

    /**
     * Hire an NPC recruit using gang treasury (requires NPC capacity from Luxury Penthouses/Villas).
     */
    public function hireNpc(Request $request, string $npcKey): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $gang = $character->gang;

        if (! $gang || ! $gang->isOfficer($character)) {
            return back()->with('error', 'Alleen bendebeheerders kunnen specialistische NPC bendeleden aannemen.');
        }

        $npcData = GangPropertyCatalog::npcs()[$npcKey] ?? null;
        if (! $npcData) {
            return back()->with('error', 'Ongeldig NPC bendelid geselecteerd.');
        }

        // Check capacity
        $totalCap = $gang->total_npc_capacity;
        $currentCount = $gang->npcs()->count();
        if ($currentCount >= $totalCap) {
            return back()->with('error', 'Geen vrije woonruimte meer! Koop eerst een Luxe Penthouse of Gang Villa om de NPC-capaciteit van de bende te verhogen.');
        }

        $cost = (int) $npcData['cost'];
        if ($gang->treasury < $cost) {
            $shortage = number_format($cost - $gang->treasury, 0, ',', '.');

            return back()->with('error', "Niet genoeg geld in de bendekas! Je komt nog €{$shortage} tekort voor dit NPC bendelid.");
        }

        DB::transaction(function () use ($gang, $npcKey, $npcData, $cost): void {
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();
            $lockedGang->treasury -= $cost;
            $lockedGang->save();

            GangNpc::create([
                'gang_id' => $lockedGang->id,
                'npc_key' => $npcKey,
                'name' => $npcData['name'],
                'role_title' => $npcData['role_title'],
                'bonus_type' => $npcData['bonus_type'],
                'bonus_value' => $npcData['bonus_value'],
                'hired_at' => now(),
            ]);
        });

        return back()->with('success', "🤝 {$npcData['name']} ({$npcData['role_title']}) is succesvol toegetreden tot de bende!");
    }

    /**
     * Assign or unassign an NPC to a production property.
     */
    public function assignNpc(Request $request, GangProperty $property): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $gang = $character->gang;

        if (! $gang || ! $gang->isOfficer($character) || (int) $property->gang_id !== (int) $gang->id) {
            return back()->with('error', 'Alleen bendebeheerders kunnen NPC bendeleden toewijzen.');
        }

        $validated = $request->validate([
            'npc_id' => ['nullable', 'integer'],
        ]);

        $npcId = $validated['npc_id'] ?? null;

        if ($npcId) {
            $npc = GangNpc::where('id', $npcId)->where('gang_id', $gang->id)->first();
            if (! $npc) {
                return back()->with('error', 'Ongeldig NPC bendelid.');
            }

            // Unassign from any previous property
            GangNpc::where('gang_id', $gang->id)->where('assigned_property_id', $property->id)->update(['assigned_property_id' => null]);
            $npc->update(['assigned_property_id' => $property->id]);
            $property->update(['assigned_npc_key' => $npc->npc_key]);

            return back()->with('success', "✅ {$npc->name} is succesvol gestationeerd in '{$property->name}'!");
        }

        // Unassign
        GangNpc::where('gang_id', $gang->id)->where('assigned_property_id', $property->id)->update(['assigned_property_id' => null]);
        $property->update(['assigned_npc_key' => null]);

        return back()->with('success', "De NPC is teruggetrokken uit '{$property->name}'.");
    }
}
