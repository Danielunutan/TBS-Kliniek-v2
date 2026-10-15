<?php

namespace App\Http\Controllers;

use App\Models\CharacterItem;
use App\Models\Item;
use App\Models\User;
use App\Services\CooldownService;
use App\Services\DailyBattleService;
use App\Services\EnergyService;
use App\Services\LevelService;
use App\Services\PrisonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InfirmaryController extends Controller
{
    public function __construct(
        protected EnergyService $energyService,
        protected LevelService $levelService,
        protected PrisonService $prisonService,
        protected CooldownService $cooldownService,
    ) {}

    /**
     * Show the infirmary page.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $this->energyService->refresh($character);

        $takeCooldown = $this->cooldownService->getRemainingSeconds($character, 'infirmary_take');
        $palmCooldown = $this->cooldownService->getRemainingSeconds($character, 'infirmary_palm');

        // Available palmable pills
        $pills = Item::whereIn('slug', ['strip-oxazepam', 'strip-ritalin', 'haloperidol-forte'])->get();

        // Calculate palm success chance based on discipline & charisma
        $statBonus = min(35, (int) floor(($character->getEffectiveDiscipline() * 1.5 + $character->getEffectiveCharisma()) / 10));
        $palmSuccessRate = min(85, max(30, 50 + $statBonus));

        // Player's currently owned medication items
        $ownedPills = CharacterItem::with('item')
            ->where('character_id', $character->id)
            ->whereHas('item', fn ($q) => $q->whereIn('slug', ['strip-oxazepam', 'strip-ritalin', 'haloperidol-forte']))
            ->get();

        return view('game.infirmary.index', compact(
            'character',
            'takeCooldown',
            'palmCooldown',
            'pills',
            'palmSuccessRate',
            'ownedPills'
        ));
    }

    /**
     * Take medication properly (restores health for free).
     */
    public function take(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'In de isoleercel heb je geen toegang tot de ziekenboeg balie!');
        }

        if ($this->cooldownService->isOnCooldown($character, 'infirmary_take')) {
            $seconds = $this->cooldownService->getRemainingSeconds($character, 'infirmary_take');

            return back()->with('error', "⏳ Zuster Ingrid deelt medicijnen uit volgens het doseerschema. Wacht nog {$seconds}s.");
        }

        if ($character->health >= $character->max_health) {
            return back()->with('error', 'Je gezondheid is al 100%! Je hebt nu geen extra herstelmedicatie nodig.');
        }

        $healAmount = 45;
        $character->health = min($character->max_health, $character->health + $healAmount);
        $character->last_infirmary_visit_at = now();
        $character->save();

        // Set 4 minute cooldown
        $this->cooldownService->setCooldown($character, 'infirmary_take', 240);

        return back()->with('success', "Je hebt je tablet netjes doorgeslikt onder toeziend oog van zuster Ingrid. +{$healAmount} Gezondheid hersteld!");
    }

    /**
     * Attempt to palm the pill under the tongue.
     */
    public function palm(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'In de isoleercel kun je geen pillen palmen!');
        }

        if ($this->cooldownService->isOnCooldown($character, 'infirmary_palm')) {
            $seconds = $this->cooldownService->getRemainingSeconds($character, 'infirmary_palm');
            $mins = ceil($seconds / 60);

            return back()->with('error', "⏳ Zuster Ingrid houdt je scherp in de gaten! Wacht nog {$seconds}s (~{$mins} min) voor een nieuwe poging of sla over met 1 Ster ⭐.");
        }

        $energyCost = 10;
        if (! $this->energyService->consume($character, $energyCost)) {
            return back()->with('error', "Niet genoeg energie! Je hebt minstens {$energyCost} energie nodig om vliegensvlug te goochelen.");
        }

        // Set 5 minute cooldown
        $this->cooldownService->setCooldown($character, 'infirmary_palm', 300);

        $statBonus = min(35, (int) floor(($character->getEffectiveDiscipline() * 1.5 + $character->getEffectiveCharisma()) / 10));
        $palmSuccessRate = min(85, max(30, 50 + $statBonus));

        $roll = rand(1, 100);

        if ($roll <= $palmSuccessRate) {
            // Determine which pill is successfully palmed
            $pillRoll = rand(1, 100);
            if ($pillRoll <= 50) {
                $pillSlug = 'strip-oxazepam';
            } elseif ($pillRoll <= 85) {
                $pillSlug = 'strip-ritalin';
            } else {
                $pillSlug = 'haloperidol-forte';
            }

            $pillItem = Item::where('slug', $pillSlug)->firstOrFail();

            $charItem = CharacterItem::firstOrCreate(
                ['character_id' => $character->id, 'item_id' => $pillItem->id],
                ['quantity' => 0]
            );
            $charItem->increment('quantity', 1);

            $character->palmed_pills_count = ($character->palmed_pills_count ?? 0) + 1;
            $character->addRespect(4);
            $this->levelService->addXp($character, 50);
            $character->save();

            app(DailyBattleService::class)->record($character, 'drugs_made', 1);

            return back()->with('success', "💊 Meesterlijke goocheltruc! Terwijl Ingrid haar stempel zette, hield jij de pil behendig onder je tong. 1x {$pillItem->name} veilig in je zak gestoken (+50 XP, +4 respect)!");
        }

        // Failed: caught by Nurse Ingrid
        $jailSeconds = 90;
        $this->prisonService->jailSeconds($character, $jailSeconds, 'Betrapt bij het stiekem achterhouden van zware medicatie onder de tong.');

        return redirect()->route('prison')->with('error', "🚨 BETRAPT! Zuster Ingrid riep: 'Hé, mond open en tong omhoog!' De zaalwacht greep je direct vast. {$jailSeconds} seconden in de isoleercel wegens medicatiesmokkel!");
    }
}
