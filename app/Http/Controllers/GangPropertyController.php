<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\GangNpc;
use App\Models\GangProperty;
use App\Models\User;
use App\Support\GangPropertyCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class GangPropertyController extends Controller
{
    /**
     * Show the Gang Properties & Real Estate overview with 30-minute rotating showroom.
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
        $gang->load(['properties', 'npcs.assignedProperty', 'leader', 'storedVehicles']);

        $catalog = GangPropertyCatalog::all();
        $npcCatalog = GangPropertyCatalog::npcs();

        $rotatingProperties = GangPropertyCatalog::getRotatingProperties();
        $rotatingNpcs = GangPropertyCatalog::getRotatingNpcs();

        $ownedProperties = $gang->properties->keyBy('property_key');
        $hiredNpcs = $gang->npcs;

        $totalStorage = $gang->total_storage_capacity;
        $totalVehicles = $gang->total_vehicle_slots;
        $storedVehiclesCount = $gang->storedVehicles()->count();
        $totalNpcCap = $gang->total_npc_capacity;
        $availableNpcSlots = max(0, $totalNpcCap - $hiredNpcs->count());

        $isOfficer = $gang->isOfficer($character);

        return view('game.gangs.panden', compact(
            'character',
            'gang',
            'catalog',
            'npcCatalog',
            'rotatingProperties',
            'rotatingNpcs',
            'ownedProperties',
            'hiredNpcs',
            'totalStorage',
            'totalVehicles',
            'storedVehiclesCount',
            'totalNpcCap',
            'availableNpcSlots',
            'isOfficer'
        ));
    }

    /**
     * Show the Bende Garage overview and fleet management.
     */
    public function garage(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character->gang_id) {
            return redirect()->route('gangs.index')
                ->with('info', 'Sluit je eerst aan bij een bende of richt een bende op om de Bende Garage te openen!');
        }

        $gang = $character->gang;
        $gang->load(['properties', 'storedVehicles.vehicle', 'storedVehicles.character', 'storedVehicles.storedBy']);

        $totalVehicleSlots = $gang->total_vehicle_slots;
        $storedVehicles = $gang->storedVehicles;
        $storedCount = $storedVehicles->count();
        $freeSlots = max(0, $totalVehicleSlots - $storedCount);

        // Character's own personal vehicles that can be parked in the gang garage
        $personalVehicles = CharacterVehicle::where('character_id', $character->id)
            ->where('in_gang_garage', false)
            ->where('is_for_sale', false)
            ->with('vehicle')
            ->get();

        // Garage/storage properties that provide vehicle slots
        $garageProperties = $gang->properties()->where('vehicle_slots', '>', 0)->get();

        $isOfficer = $gang->isOfficer($character);

        return view('game.gangs.garage', compact(
            'character',
            'gang',
            'totalVehicleSlots',
            'storedVehicles',
            'storedCount',
            'freeSlots',
            'personalVehicles',
            'garageProperties',
            'isOfficer'
        ));
    }

    /**
     * Store a personal vehicle into the gang garage fleet.
     */
    public function storeVehicle(Request $request, CharacterVehicle $characterVehicle): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen voertuigen stallen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $gang = $character->gang;

        if (! $gang) {
            return back()->with('error', 'Je zit niet in een bende.');
        }

        if ($characterVehicle->character_id !== $character->id) {
            return back()->with('error', 'Dit voertuig is niet van jou.');
        }

        if ($characterVehicle->in_gang_garage) {
            return back()->with('info', 'Dit voertuig staat al in de bende garage.');
        }

        if ($characterVehicle->is_for_sale) {
            return back()->with('error', 'Dit voertuig staat te koop op de marktplaats en kan niet worden gestald.');
        }

        $errorMessage = DB::transaction(function () use ($characterVehicle, $gang, $character): ?string {
            /** @var Gang|null $lockedGang */
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();
            /** @var CharacterVehicle|null $lockedVehicle */
            $lockedVehicle = CharacterVehicle::where('id', $characterVehicle->id)->lockForUpdate()->first();

            if (! $lockedGang || ! $lockedVehicle || $lockedVehicle->character_id !== $character->id) {
                return 'Dit voertuig is niet van jou.';
            }

            if ($lockedVehicle->in_gang_garage) {
                return 'Dit voertuig staat al in de bende garage.';
            }

            if ($lockedVehicle->is_for_sale) {
                return 'Dit voertuig staat te koop op de marktplaats en kan niet worden gestald.';
            }

            $totalSlots = $lockedGang->total_vehicle_slots;
            $currentCount = $lockedGang->storedVehicles()->count();
            if ($currentCount >= $totalSlots) {
                return "De bende garage zit vol ({$currentCount}/{$totalSlots} plekken bezet)! Koop meer garage- of opslagpanden via Bende Panden om extra wagenplekken vrij te spelen.";
            }

            $lockedVehicle->update([
                'gang_id' => $lockedGang->id,
                'in_gang_garage' => true,
                'stored_at' => now(),
                'stored_by_character_id' => $character->id,
                'is_active' => false,
            ]);

            return null;
        });

        if ($errorMessage !== null) {
            return back()->with('error', $errorMessage);
        }

        return back()->with('success', "🚗 Je {$characterVehicle->vehicle->name} is veilig gestald in de Bende Garage van {$gang->name}!");
    }

    /**
     * Retrieve a vehicle from the gang garage back to personal garage.
     */
    public function retrieveVehicle(Request $request, CharacterVehicle $characterVehicle): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen voertuigen ophalen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $gang = $character->gang;

        if (! $gang) {
            return back()->with('error', 'Je zit niet in een bende.');
        }

        if (! $characterVehicle->in_gang_garage || $characterVehicle->gang_id !== $gang->id) {
            return back()->with('error', 'Dit voertuig staat niet in jullie bende garage.');
        }

        if ($characterVehicle->is_for_sale) {
            return back()->with('error', 'Dit voertuig staat te koop op de marktplaats en kan niet uit de bende garage worden gehaald.');
        }

        // Only owner or gang officer can retrieve
        $isOwner = $characterVehicle->character_id === $character->id;
        $isOfficer = $gang->isOfficer($character);

        if (! $isOwner && ! $isOfficer) {
            return back()->with('error', 'Alleen de eigenaar of een bendebeheerder kan dit voertuig uit de bende garage halen.');
        }

        $errorMessage = DB::transaction(function () use ($character, $characterVehicle, $gang): ?string {
            /** @var Character $lockedChar */
            $lockedChar = Character::where('id', $character->id)->lockForUpdate()->first();
            /** @var CharacterVehicle|null $lockedVehicle */
            $lockedVehicle = CharacterVehicle::where('id', $characterVehicle->id)->lockForUpdate()->first();

            if (! $lockedVehicle || ! $lockedVehicle->in_gang_garage || $lockedVehicle->gang_id !== $gang->id) {
                return 'Dit voertuig staat niet in jullie bende garage.';
            }

            if ($lockedVehicle->is_for_sale) {
                return 'Dit voertuig staat te koop op de marktplaats en kan niet uit de bende garage worden gehaald.';
            }

            /** @var Character $ownerChar */
            $ownerChar = $lockedVehicle->character_id === $lockedChar->id
                ? $lockedChar
                : Character::where('id', $lockedVehicle->character_id)->lockForUpdate()->first();

            if ($ownerChar && ! $ownerChar->canAddVehicle()) {
                return "De persoonlijke garage zit vol ({$ownerChar->getGarageUsed()}/{$ownerChar->getGarageCapacity()} plekken bezet)! Verkoop eerst een wagen of stal een andere wagen in de Bende Garage voordat je dit voertuig terughaalt.";
            }

            $lockedVehicle->update([
                'in_gang_garage' => false,
                'gang_id' => null,
                'stored_at' => null,
                'stored_by_character_id' => null,
            ]);

            return null;
        });

        if ($errorMessage !== null) {
            return back()->with('error', $errorMessage);
        }

        return back()->with('success', "🔑 De {$characterVehicle->vehicle->name} is succesvol uit de Bende Garage gehaald en terug in je persoonlijke wagenpark geplaatst!");
    }

    /**
     * Purchase a property for the gang using gang treasury.
     */
    public function buy(Request $request, string $propertyKey): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen bendepanden aanschaffen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

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

        // Check if purchased via rotating showroom offer with valid token & discount
        if ($request->filled('token') && $request->filled('dynamic_price')) {
            $slot = (int) floor(time() / 1800);
            $expectedToken = substr(hash_hmac('sha256', "{$slot}_{$propertyKey}_{$request->input('dynamic_price')}", config('app.key')), 0, 16);
            if ($request->input('token') === $expectedToken) {
                $price = (int) $request->input('dynamic_price');
            }
        }

        if ($gang->treasury < $price) {
            $shortage = number_format($price - $gang->treasury, 0, ',', '.');

            return back()->with('error', "Niet genoeg geld in de bendekas! Je komt nog €{$shortage} tekort.");
        }

        $errorMessage = DB::transaction(function () use ($gang, $propertyKey, $catalogProp, $price): ?string {
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();

            // Re-check under the gang lock so parallel requests cannot overspend the treasury or buy the same property twice.
            if ($lockedGang->properties()->where('property_key', $propertyKey)->exists()) {
                return 'Jouw bende bezit dit pand al!';
            }

            if ($lockedGang->treasury < $price) {
                $shortage = number_format($price - $lockedGang->treasury, 0, ',', '.');

                return "Niet genoeg geld in de bendekas! Je komt nog €{$shortage} tekort.";
            }

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

            return null;
        });

        if ($errorMessage !== null) {
            return back()->with('error', $errorMessage);
        }

        return back()->with('success', "🎉 Gefeliciteerd! Jouw bende is nu de trotse eigenaar van '{$catalogProp['name']}'!");
    }

    /**
     * Start a production cycle in a gang production facility.
     */
    public function startProduction(Request $request, GangProperty $property): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen productieprocessen starten!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $gang = $character->gang;

        if (! $gang || ! $gang->isOfficer($character) || (int) $property->gang_id !== (int) $gang->id) {
            return back()->with('error', 'Alleen bendebeheerders kunnen productieprocessen starten.');
        }

        if ($property->type !== 'production') {
            return back()->with('error', 'Dit pand heeft geen productiefaciliteit.');
        }

        if ($property->isProducing()) {
            return back()->with('error', 'De productie is al in volle gang!');
        }

        if ($property->isProductionReady()) {
            return back()->with('error', 'De vorige productiecyclus is voltooid! Claim eerst de opbrengst.');
        }

        $durationMinutes = $property->effective_duration_minutes;
        $completesAt = now()->addMinutes($durationMinutes);

        $property->update([
            'production_status' => 'producing',
            'production_started_at' => now(),
            'production_ends_at' => $completesAt,
        ]);

        return back()->with('success', "🏭 Productie gestart in '{$property->name}'! Over {$durationMinutes} minuten kan de opbrengst worden geclaimd.");
    }

    /**
     * Claim completed production yield into gang treasury and respect.
     */
    public function claimProduction(Request $request, GangProperty $property): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen productieopbrengsten claimen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $gang = $character->gang;

        if (! $gang || ! $gang->isOfficer($character) || (int) $property->gang_id !== (int) $gang->id) {
            return back()->with('error', 'Alleen bendebeheerders kunnen productieopbrengsten claimen.');
        }

        if (! $property->isProductionReady()) {
            return back()->with('error', 'De productiecyclus is nog niet afgerond.');
        }

        $cash = $property->effective_reward_cash;
        $respect = $property->effective_reward_respect;

        $claimed = DB::transaction(function () use ($gang, $character, $property, $cash, $respect): bool {
            // Lock the property first so parallel claims cannot pay the same cycle twice.
            $lockedProperty = GangProperty::where('id', $property->id)->lockForUpdate()->first();
            if (! $lockedProperty || ! $lockedProperty->isProductionReady()) {
                return false;
            }

            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();
            $lockedGang->treasury += $cash;
            if (Schema::hasColumn('gangs', 'respect')) {
                $lockedGang->respect = ($lockedGang->respect ?? 0) + $respect;
            }
            $lockedGang->save();

            if ($respect > 0) {
                $character->increment('respect', $respect);
            }

            $lockedProperty->update([
                'production_status' => 'idle',
                'production_started_at' => null,
                'production_ends_at' => null,
            ]);

            return true;
        });

        if (! $claimed) {
            return back()->with('error', 'De productiecyclus is nog niet afgerond of is al geclaimd.');
        }

        $formattedCash = number_format($cash, 0, ',', '.');

        return back()->with('success', "💰 Buit veiliggesteld! Er is €{$formattedCash} in de bendekas gestort en +{$respect} Respect toegekend aan {$gang->name}!");
    }

    /**
     * Hire an NPC recruit using gang treasury (requires NPC capacity from Luxury Penthouses/Villas).
     */
    public function hireNpc(Request $request, string $npcKey): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen NPC bendeleden aannemen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $gang = $character->gang;

        if (! $gang || ! $gang->isOfficer($character)) {
            return back()->with('error', 'Alleen bendebeheerders kunnen specialistische NPC bendeleden aannemen.');
        }

        $npcData = GangPropertyCatalog::getNpc($npcKey);
        if (! $npcData) {
            return back()->with('error', 'Ongeldig NPC bendelid geselecteerd.');
        }

        // Check capacity
        $totalCap = $gang->total_npc_capacity;
        $currentCount = $gang->npcs()->count();
        if ($currentCount >= $totalCap) {
            return back()->with('error', 'Geen vrije woonruimte meer! Koop eerst een Luxe Penthouse of Bende Villa om de NPC-capaciteit van de bende te verhogen.');
        }

        $cost = (int) $npcData['cost'];
        if ($gang->treasury < $cost) {
            $shortage = number_format($cost - $gang->treasury, 0, ',', '.');

            return back()->with('error', "Niet genoeg geld in de bendekas! Je komt nog €{$shortage} tekort voor dit NPC bendelid.");
        }

        $errorMessage = DB::transaction(function () use ($gang, $npcKey, $npcData, $cost): ?string {
            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();

            // Re-check capacity and treasury under the gang lock so parallel hires cannot exceed either limit.
            if ($lockedGang->npcs()->count() >= $lockedGang->total_npc_capacity) {
                return 'Geen vrije woonruimte meer! Koop eerst een Luxe Penthouse of Bende Villa om de NPC-capaciteit van de bende te verhogen.';
            }

            if ($lockedGang->treasury < $cost) {
                return 'Niet genoeg geld in de bendekas voor dit NPC bendelid.';
            }

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

            return null;
        });

        if ($errorMessage !== null) {
            return back()->with('error', $errorMessage);
        }

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
