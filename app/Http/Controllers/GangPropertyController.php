<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterVehicle;
use App\Models\GameNotification;
use App\Models\Gang;
use App\Models\GangAlliance;
use App\Models\GangNpc;
use App\Models\GangProperty;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\CooldownService;
use App\Services\EnergyService;
use App\Services\EventDrugsProductionService;
use App\Services\LevelService;
use App\Services\PrisonService;
use App\Support\GangPropertyCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        GangNpc::purgeExpiredForGang($gang);
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

        $excludedGangIds = GangAlliance::where('gang_id', $gang->id)->pluck('ally_gang_id')
            ->merge(GangAlliance::where('ally_gang_id', $gang->id)->pluck('gang_id'))
            ->push($gang->id)
            ->unique()
            ->all();

        $rivalLabs = GangProperty::with(['gang', 'assignedNpc'])
            ->whereNotIn('gang_id', $excludedGangIds)
            ->where('type', 'production')
            ->whereIn('production_status', ['producing', 'ready'])
            ->orderByDesc('production_reward_cash')
            ->limit(12)
            ->get();

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
            'isOfficer',
            'rivalLabs'
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

        if ($character->isComatose() || $character->needsReviveSerum()) {
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
            /** @var Character|null $lockedChar */
            $lockedChar = Character::where('id', $character->id)->lockForUpdate()->first();

            if (! $lockedChar || $lockedChar->isImprisoned() || $lockedChar->isComatose() || $lockedChar->needsReviveSerum()) {
                return 'Je bent momenteel niet in staat om voertuigen te stallen.';
            }

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

        if ($character->isComatose() || $character->needsReviveSerum()) {
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

            if (! $lockedChar || $lockedChar->isImprisoned() || $lockedChar->isComatose() || $lockedChar->needsReviveSerum()) {
                return 'Je bent momenteel niet in staat om voertuigen op te halen.';
            }

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

        if ($character->isComatose() || $character->needsReviveSerum()) {
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
            $dynPrice = (int) $request->input('dynamic_price');
            $slot = (int) floor(time() / 1800);
            $appSecret = (string) (config('app.key') ?: 'tbs-gang-prop-secret');
            $tokenCurrent = substr(hash_hmac('sha256', "{$slot}_{$propertyKey}_{$dynPrice}", $appSecret), 0, 16);
            $prevSlot = $slot - 1;
            $tokenPrev = substr(hash_hmac('sha256', "{$prevSlot}_{$propertyKey}_{$dynPrice}", $appSecret), 0, 16);

            if ($request->input('token') === $tokenCurrent || ($request->input('token') === $tokenPrev && (time() % 1800) < 60)) {
                $price = $dynPrice;
            } else {
                return back()->with('error', 'Deze tijdelijke showroom aanbieding is verlopen. Vernieuw de pagina voor de actuele aanbiedingen.');
            }
        }

        if ($gang->treasury < $price) {
            $shortage = number_format($price - $gang->treasury, 0, ',', '.');

            return back()->with('error', "Niet genoeg geld in de bendekas! Je komt nog €{$shortage} tekort.");
        }

        $errorMessage = DB::transaction(function () use ($gang, $character, $propertyKey, $catalogProp, $price): ?string {
            /** @var Character|null $lockedChar */
            $lockedChar = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $lockedChar || $lockedChar->isImprisoned() || $lockedChar->isComatose() || $lockedChar->needsReviveSerum()) {
                return 'Je bent momenteel niet in staat om bendepanden aan te schaffen.';
            }

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

            AuditLogService::log(
                action: 'gang.buy_property',
                category: 'gang',
                description: "Bendepand '{$catalogProp['name']}' gekocht voor €".number_format($price, 0, ',', '.')." voor bende {$lockedGang->name}",
                character: $character,
                user: $character->user,
                amount: $price,
                details: [
                    'gang_id' => $lockedGang->id,
                    'property_key' => $propertyKey,
                    'price' => $price,
                ]
            );

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

        if ($character->isComatose() || $character->needsReviveSerum()) {
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

        $fleetCheck = GangPropertyCatalog::checkFleetRequirements($gang, $property->property_key);
        if (! $fleetCheck['qualified']) {
            return back()->with('error', $fleetCheck['reason']);
        }

        $specialists = [
            'chemisch_specialist' => [
                'name' => 'Dr. Walter V. (Meesterchemicus)',
                'cost' => 750000,
                'benefit' => '-50% productietijd',
            ],
            'productie_manager' => [
                'name' => 'Dimitri "De Opzichter"',
                'cost' => 1000000,
                'benefit' => '+100% cash opbrengst',
            ],
            'enforcer_kapitein' => [
                'name' => 'Boris "De Sloper"',
                'cost' => 500000,
                'benefit' => 'Zware lab-verdediging tegen invallen',
            ],
        ];

        $specialistKey = $request->input('batch_specialist', 'none');
        $hiredNotice = '';

        try {
            $durationMinutes = DB::transaction(function () use ($character, $property, $gang, $specialists, $specialistKey, &$hiredNotice): int {
                /** @var GangProperty|null $lockedProperty */
                $lockedProperty = GangProperty::where('id', $property->id)->lockForUpdate()->first();
                /** @var Character|null $lockedChar */
                $lockedChar = Character::where('id', $character->id)->lockForUpdate()->first();

                if (! $lockedProperty || ! $lockedChar || (int) $lockedProperty->gang_id !== (int) $gang->id) {
                    throw new \RuntimeException('Ongeldig bende-pand.');
                }

                if ($lockedChar->isImprisoned() || $lockedChar->isComatose() || $lockedChar->needsReviveSerum()) {
                    throw new \RuntimeException('Je bent momenteel niet in staat om productieprocessen te beheren.');
                }

                if ($lockedProperty->type !== 'production') {
                    throw new \RuntimeException('Dit pand heeft geen productiefaciliteit.');
                }

                if ($lockedProperty->isProducing()) {
                    throw new \RuntimeException('De productie is al in volle gang!');
                }

                if ($lockedProperty->isProductionReady()) {
                    throw new \RuntimeException('De vorige productiecyclus is voltooid! Claim eerst de opbrengst.');
                }

                $fleetCheck = GangPropertyCatalog::checkFleetRequirements($gang, $lockedProperty->property_key);
                if (! $fleetCheck['qualified']) {
                    throw new \RuntimeException($fleetCheck['reason']);
                }

                if ($specialistKey !== 'none' && isset($specialists[$specialistKey])) {
                    $spec = $specialists[$specialistKey];
                    $cost = $spec['cost'];
                    /** @var Gang $lockedGang */
                    $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->firstOrFail();
                    if ($lockedGang->treasury < $cost) {
                        throw new \RuntimeException("Onvoldoende saldo in de bendekas! {$spec['name']} kost €".number_format($cost, 0, ',', '.')." voor deze cyclus (de kas bevat €".number_format($lockedGang->treasury, 0, ',', '.').').');
                    }
                    $lockedGang->treasury -= $cost;
                    $lockedGang->save();
                    $lockedProperty->assigned_npc_key = $specialistKey;
                    $hiredNotice = " Inclusief specialist {$spec['name']} ({$spec['benefit']}, -€".number_format($cost, 0, ',', '.').' uit bendekas)!';

                    AuditLogService::log(
                        action: 'gang.hire_batch_npc',
                        category: 'gang',
                        description: "Specialist {$spec['name']} ingehuurd voor productiecyclus in {$lockedProperty->name} (€".number_format($cost, 0, ',', '.').' uit bendekas)',
                        character: $lockedChar,
                        user: $lockedChar->user,
                        amount: $cost,
                        details: [
                            'gang_id' => $lockedGang->id,
                            'property_id' => $lockedProperty->id,
                            'specialist_key' => $specialistKey,
                            'cost' => $cost,
                        ]
                    );
                } else {
                    $lockedProperty->assigned_npc_key = null;
                }

                $durationMinutes = $lockedProperty->effective_duration_minutes;
                $completesAt = now()->addMinutes($durationMinutes);

                $lockedProperty->update([
                    'production_status' => 'producing',
                    'production_started_at' => now(),
                    'production_ends_at' => $completesAt,
                    'assigned_npc_key' => $lockedProperty->assigned_npc_key,
                ]);

                return $durationMinutes;
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "🏭 Productie gestart in '{$property->name}'! Over {$durationMinutes} minuten kan de opbrengst worden geclaimd.{$hiredNotice}");
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

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $gang = $character->gang;

        if (! $gang || ! $gang->isOfficer($character) || (int) $property->gang_id !== (int) $gang->id) {
            return back()->with('error', 'Alleen bendebeheerders kunnen productieopbrengsten claimen.');
        }

        if (! $property->isProductionReady()) {
            return back()->with('error', 'De productiecyclus is nog niet afgerond.');
        }

        $claimedData = DB::transaction(function () use ($gang, $character, $property): ?array {
            /** @var Character|null $lockedChar */
            $lockedChar = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $lockedChar || $lockedChar->isImprisoned() || $lockedChar->isComatose() || $lockedChar->needsReviveSerum()) {
                return null;
            }

            // Lock the property first so parallel claims cannot pay the same cycle twice.
            $lockedProperty = GangProperty::where('id', $property->id)->lockForUpdate()->first();
            if (! $lockedProperty || ! $lockedProperty->isProductionReady()) {
                return null;
            }

            $cash = $lockedProperty->effective_reward_cash;
            $respect = $lockedProperty->effective_reward_respect;

            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();
            $lockedGang->treasury += $cash;
            if (Schema::hasColumn('gangs', 'respect')) {
                $lockedGang->respect = ($lockedGang->respect ?? 0) + $respect;
            }
            $lockedGang->save();

            if ($respect > 0) {
                $lockedChar->addRespect($respect);
                $lockedChar->save();
            }

            $hadAssignedNpc = $lockedProperty->assigned_npc_key;
            $lockedProperty->update([
                'production_status' => 'idle',
                'production_started_at' => null,
                'production_ends_at' => null,
                'assigned_npc_key' => null,
            ]);

            if ($hadAssignedNpc) {
                GangNpc::where('gang_id', $lockedGang->id)
                    ->where('assigned_property_id', $lockedProperty->id)
                    ->delete();
            }

            AuditLogService::log(
                action: 'gang.claim_production',
                category: 'gang',
                description: "Productie geclaimd van {$lockedProperty->name} voor €".number_format($cash, 0, ',', '.')." en {$respect} respect",
                character: $lockedChar,
                user: $lockedChar->user,
                amount: $cash,
                details: [
                    'gang_id' => $lockedGang->id,
                    'property_id' => $lockedProperty->id,
                    'cash' => $cash,
                    'respect' => $respect,
                ]
            );

            return [
                'cash' => $cash,
                'respect' => $respect,
            ];
        });

        if (! $claimedData) {
            return back()->with('error', 'De productiecyclus is nog niet afgerond of is al geclaimd.');
        }

        $cash = $claimedData['cash'];
        $respect = $claimedData['respect'];

        $drugUnits = match ($property->property_key) {
            'wietkwekerij' => 50,
            'sigaretten_fabriek' => 60,
            'chemisch_lab' => 80,
            'synthese_complex' => 150,
            default => 0,
        };
        if ($drugUnits > 0) {
            EventDrugsProductionService::record($character, $drugUnits, $property->property_key);
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

        if ($character->isComatose() || $character->needsReviveSerum()) {
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

        if (($npcData['bonus_type'] ?? '') === 'defense' && $gang->npc_defense_bonus_percent >= Gang::MAX_NPC_DEFENSE_BONUS_PERCENT) {
            return back()->with('error', 'Jullie bende heeft het maximale Enforcer-verdedigingslimiet van +'.Gang::MAX_NPC_DEFENSE_BONUS_PERCENT.'% al bereikt! Huur een ander type specialist in.');
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

        $errorMessage = DB::transaction(function () use ($gang, $character, $npcKey, $npcData, $cost): ?string {
            /** @var Character|null $lockedChar */
            $lockedChar = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $lockedChar || $lockedChar->isImprisoned() || $lockedChar->isComatose() || $lockedChar->needsReviveSerum()) {
                return 'Je bent momenteel niet in staat om NPC bendeleden aan te nemen.';
            }

            $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();

            $currentDefenseBonus = $lockedGang->npc_defense_bonus_percent;
            if (($npcData['bonus_type'] ?? '') === 'defense' && $currentDefenseBonus >= Gang::MAX_NPC_DEFENSE_BONUS_PERCENT) {
                return 'Jullie bende heeft het maximale Enforcer-verdedigingslimiet van +'.Gang::MAX_NPC_DEFENSE_BONUS_PERCENT.'% al bereikt! Huur een ander type specialist in.';
            }

            // Re-check capacity and treasury under the gang lock so parallel hires cannot exceed either limit.
            if ($lockedGang->npcs()->count() >= $lockedGang->total_npc_capacity) {
                return 'Geen vrije woonruimte meer! Koop eerst een Luxe Penthouse of Bende Villa om de NPC-capaciteit van de bende te verhogen.';
            }

            if ($lockedGang->treasury < $cost) {
                return 'Niet genoeg geld in de bendekas voor dit NPC bendelid.';
            }

            $effectiveBonusValue = (int) $npcData['bonus_value'];
            if (($npcData['bonus_type'] ?? '') === 'defense') {
                $remainingBonus = max(0, Gang::MAX_NPC_DEFENSE_BONUS_PERCENT - $currentDefenseBonus);
                if ($remainingBonus <= 0) {
                    return 'Jullie bende heeft het maximale Enforcer-verdedigingslimiet van +'.Gang::MAX_NPC_DEFENSE_BONUS_PERCENT.'% al bereikt! Huur een ander type specialist in.';
                }
                $effectiveBonusValue = min($effectiveBonusValue, $remainingBonus);
            }

            $lockedGang->treasury -= $cost;
            $lockedGang->save();

            GangNpc::create([
                'gang_id' => $lockedGang->id,
                'npc_key' => $npcKey,
                'name' => $npcData['name'],
                'role_title' => $npcData['role_title'],
                'bonus_type' => $npcData['bonus_type'],
                'bonus_value' => $effectiveBonusValue,
                'hired_at' => now(),
                'expires_at' => now()->addMinutes(GangNpc::CONTRACT_DURATION_MINUTES),
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

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen NPC bendeleden beheren!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $gang = $character->gang;

        if (! $gang || ! $gang->isOfficer($character) || (int) $property->gang_id !== (int) $gang->id) {
            return back()->with('error', 'Alleen bendebeheerders kunnen NPC bendeleden toewijzen.');
        }

        $validated = $request->validate([
            'npc_id' => ['nullable', 'integer'],
        ]);

        $npcId = $validated['npc_id'] ?? null;

        try {
            $successMessage = DB::transaction(function () use ($gang, $character, $property, $npcId): string {
                /** @var Character|null $lockedChar */
                $lockedChar = Character::where('id', $character->id)->lockForUpdate()->first();
                if (! $lockedChar || $lockedChar->isImprisoned() || $lockedChar->isComatose() || $lockedChar->needsReviveSerum()) {
                    throw new \RuntimeException('Je bent momenteel niet in staat om NPC bendeleden te beheren.');
                }

                /** @var GangProperty|null $lockedProperty */
                $lockedProperty = GangProperty::where('id', $property->id)
                    ->where('gang_id', $gang->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedProperty) {
                    throw new \RuntimeException('Ongeldig bendepand.');
                }

                if (in_array($lockedProperty->production_status, ['producing', 'ready'], true)) {
                    throw new \RuntimeException('Je kunt geen NPC bendelid wisselen of terugtrekken terwijl een productiebatch actief of klaar voor incasso is!');
                }

                if ($npcId) {
                    /** @var GangNpc|null $npc */
                    $npc = GangNpc::where('id', $npcId)
                        ->where('gang_id', $gang->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $npc) {
                        throw new \RuntimeException('Ongeldig NPC bendelid.');
                    }

                    if ($npc->assigned_property_id && (int) $npc->assigned_property_id !== (int) $lockedProperty->id) {
                        /** @var GangProperty|null $prevProperty */
                        $prevProperty = GangProperty::where('id', $npc->assigned_property_id)
                            ->where('gang_id', $gang->id)
                            ->lockForUpdate()
                            ->first();

                        if ($prevProperty && in_array($prevProperty->production_status, ['producing', 'ready'], true)) {
                            throw new \RuntimeException("{$npc->name} draait momenteel nog een actieve productiebatch in '{$prevProperty->name}'!");
                        }

                        if ($prevProperty) {
                            $prevProperty->update(['assigned_npc_key' => null]);
                        }
                    }

                    // Clear any other property in this gang that still references this NPC key
                    GangProperty::where('gang_id', $gang->id)
                        ->where('id', '!=', $lockedProperty->id)
                        ->where('assigned_npc_key', $npc->npc_key)
                        ->update(['assigned_npc_key' => null]);

                    // Unassign any previous NPC from the target property
                    GangNpc::where('gang_id', $gang->id)
                        ->where('assigned_property_id', $lockedProperty->id)
                        ->where('id', '!=', $npc->id)
                        ->update(['assigned_property_id' => null]);

                    $npc->update(['assigned_property_id' => $lockedProperty->id]);
                    $lockedProperty->update(['assigned_npc_key' => $npc->npc_key]);

                    return "✅ {$npc->name} is succesvol gestationeerd in '{$lockedProperty->name}'!";
                }

                // Unassign
                GangNpc::where('gang_id', $gang->id)
                    ->where('assigned_property_id', $lockedProperty->id)
                    ->update(['assigned_property_id' => null]);
                $lockedProperty->update(['assigned_npc_key' => null]);

                return "De NPC is teruggetrokken uit '{$lockedProperty->name}'.";
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $successMessage);
    }

    /**
     * Execute a gang ripdeal raid against a rival gang's active production laboratory.
     */
    public function ripLab(Request $request, GangProperty $property): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen vijandelijke bende-labs overvallen!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if (! $character->gang_id) {
            return back()->with('error', 'Je moet lid zijn van een bende om een Bende Ripdeal uit te voeren.');
        }

        if ((int) $property->gang_id === (int) $character->gang_id) {
            return back()->with('error', 'Je kunt niet je eigen bende-laboratorium beroven!');
        }

        if ($property->type !== 'production' || ! in_array($property->production_status, ['producing', 'ready'], true)) {
            return back()->with('error', 'Dit pand draait momenteel geen actieve productie om te rippen.');
        }

        $raidShieldKey = "gang_lab_ripped_{$property->id}";
        if (Cache::has($raidShieldKey)) {
            return back()->with('error', "🛡️ Dit laboratorium ('{$property->name}') is onlangs al overvallen en staat nu tijdelijk onder verscherpte bewaking!");
        }

        if ($character->isOnCooldown('gang_lab_rip')) {
            $remMins = max(1, (int) ceil($character->cooldownRemaining('gang_lab_rip') / 60));

            return back()->with('error', "⏳ Je bende-ripploeg moet eerst hergroeperen! Wacht nog {$remMins} min voor je volgende Ripdeal.");
        }

        if ($character->energy < 25) {
            return back()->with('error', 'Je hebt minimaal 25 Energie nodig om een vijandelijk bende-lab te overvallen.');
        }

        return DB::transaction(function () use ($user, $character, $property, $raidShieldKey): RedirectResponse {
            /** @var GangProperty|null $lockedProp */
            $lockedProp = GangProperty::with('gang')->where('id', $property->id)->lockForUpdate()->first();
            /** @var Character|null $lockedChar */
            $lockedChar = Character::where('id', $character->id)->lockForUpdate()->first();

            if (! $lockedProp || ! $lockedChar || ! $lockedProp->gang) {
                return back()->with('error', 'Doelwit-laboratorium kon niet worden gevonden.');
            }

            if ($lockedChar->isImprisoned()) {
                return back()->with('error', 'Vanuit de isoleercel kun je geen vijandelijke bende-labs overvallen!');
            }

            if ($lockedChar->isComatose() || $lockedChar->needsReviveSerum()) {
                return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
            }

            if (! $lockedChar->gang_id) {
                return back()->with('error', 'Je moet lid zijn van een bende om een Bende Ripdeal uit te voeren.');
            }

            if ((int) $lockedProp->gang_id === (int) $lockedChar->gang_id) {
                return back()->with('error', 'Je kunt niet je eigen bende-laboratorium beroven!');
            }

            $attackerGang = $lockedChar->gang;
            $defenderGang = $lockedProp->gang;

            // Block ripdeals against allied gangs
            $isAlly = GangAlliance::where(function ($q) use ($attackerGang, $defenderGang) {
                $q->where('gang_id', $attackerGang->id)->where('ally_gang_id', $defenderGang->id);
            })->orWhere(function ($q) use ($attackerGang, $defenderGang) {
                $q->where('gang_id', $defenderGang->id)->where('ally_gang_id', $attackerGang->id);
            })->exists();

            if ($isAlly) {
                return back()->with('error', "Je kunt geen bondgenoot [{$defenderGang->tag}] overvallen!");
            }

            // Re-check raid shield under row lock to prevent race conditions and duplicate loot
            if (Cache::has($raidShieldKey)) {
                return back()->with('error', "🛡️ Dit laboratorium ('{$lockedProp->name}') is onlangs al overvallen en staat nu tijdelijk onder verscherpte bewaking!");
            }

            if (! in_array($lockedProp->production_status, ['producing', 'ready'], true)) {
                return back()->with('error', 'De productie van dit lab was al binnengehaald voordat je binnenviel!');
            }

            if ($lockedChar->isOnCooldown('gang_lab_rip')) {
                $remMins = max(1, (int) ceil($lockedChar->cooldownRemaining('gang_lab_rip') / 60));

                return back()->with('error', "⏳ Je bende-ripploeg moet eerst hergroeperen! Wacht nog {$remMins} min voor je volgende Ripdeal.");
            }

            // Atomically consume energy and set cooldown inside transaction
            if (! app(EnergyService::class)->consume($lockedChar, 25)) {
                return back()->with('error', 'Je hebt minimaal 25 Energie nodig om een vijandelijk bende-lab te overvallen.');
            }

            $lockedChar->breakProtection();
            app(CooldownService::class)->setCooldown($lockedChar, 'gang_lab_rip', 900);

            $attackerPower = $lockedChar->getEffectiveStrength()
                + $lockedChar->getEffectiveDefense()
                + (($lockedChar->level ?? 1) * 5)
                + rand(15, 55);

            $defenderDefense = 60
                + (int) round(($lockedProp->production_reward_cash / 20000))
                + (int) round(($defenderGang->npc_defense_bonus_percent ?? 0) * 2.5)
                + ($lockedProp->assigned_npc_key === 'enforcer_kapitein' ? 1200 : 0)
                + rand(10, 40);

            if ($attackerPower >= $defenderDefense) {
                $lootTotal = max(10000, (int) round($lockedProp->effective_reward_cash * 0.35));
                $personalShare = (int) floor($lootTotal * 0.50);
                $gangShare = $lootTotal - $personalShare;

                $lockedChar->cash = (int) $lockedChar->cash + $personalShare;
                $lockedChar->addRespect(25);
                $lockedChar->save();

                app(LevelService::class)->addXp($lockedChar, 200);

                if ($attackerGang) {
                    $attackerGang->increment('treasury', $gangShare);
                }

                if ($lockedProp->production_ends_at && $lockedProp->production_ends_at->isFuture()) {
                    $lockedProp->production_ends_at = $lockedProp->production_ends_at->copy()->addMinutes(30);
                } else {
                    $lockedProp->production_ends_at = now()->addMinutes(30);
                    $lockedProp->production_status = 'producing';
                }
                $lockedProp->save();

                Cache::put($raidShieldKey, now()->addHours(2)->timestamp, now()->addHours(2));

                $leaderCharId = $defenderGang->leader_character_id ?? $defenderGang->leader_id ?? null;
                if ($leaderCharId) {
                    GameNotification::create([
                        'character_id' => $leaderCharId,
                        'type' => 'attack',
                        'icon' => 'fa-flask-vial',
                        'title' => "💊 Bende-Lab Overvallen: {$lockedProp->name}!",
                        'message' => "{$lockedChar->name} [".($attackerGang->tag ?? 'SOLO')."] heeft jullie lab '{$lockedProp->name}' overvallen (Ripdeal) en voor €".number_format($lootTotal, 0, ',', '.').' aan grondstoffen en handelswaar buitgemaakt!',
                    ]);
                }

                AuditLogService::log(
                    action: 'gang_property.rip_lab',
                    category: 'gang',
                    description: "Ripdeal uitgevoerd op '{$lockedProp->name}' van [{$defenderGang->tag}] voor €".number_format($lootTotal, 0, ',', '.').'.',
                    character: $lockedChar,
                    user: $user,
                    amount: $lootTotal,
                    details: [
                        'property_id' => $lockedProp->id,
                        'defender_gang_id' => $defenderGang->id,
                        'loot_total' => $lootTotal,
                        'personal_share' => $personalShare,
                        'gang_share' => $gangShare,
                    ]
                );

                return back()->with(
                    'success',
                    "💊 RIPDEAL GESLAAGD! Je hebt '{$lockedProp->name}' van [{$defenderGang->tag}] overvallen! Totale buit: €"
                    .number_format($lootTotal, 0, ',', '.').' (€'.number_format($personalShare, 0, ',', '.').' contant op zak + €'
                    .number_format($gangShare, 0, ',', '.').' in de bendekas) + 25 Respect & 200 XP!'
                );
            }

            $dmg = rand(20, 35);
            $lockedChar->health = max(5, (int) $lockedChar->health - $dmg);
            $lockedChar->save();

            if (rand(1, 100) <= 40) {
                app(PrisonService::class)->jail($lockedChar, 10, "Betrapt bij een mislukte ripdeal op '{$lockedProp->name}'");

                return redirect()->route('prison')->with(
                    'error',
                    "🚨 RIPDEAL MISLUKT & OPGEPAKT! De enforcers van [{$defenderGang->tag}] sloegen je aanval op '{$lockedProp->name}' af (-{$dmg} HP) en de bewakers hebben je 10 minuten in de isoleercel gezet!"
                );
            }

            return back()->with(
                'error',
                "🚨 RIPDEAL MISLUKT! De bewakers en enforcers van [{$defenderGang->tag}] sloegen je overval op '{$lockedProp->name}' keihard af (-{$dmg} HP)!"
            );
        });
    }
}
