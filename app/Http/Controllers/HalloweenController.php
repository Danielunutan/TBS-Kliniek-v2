<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\HalloweenQuestProgress;
use App\Models\Item;
use App\Models\User;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\HalloweenService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HalloweenController extends Controller
{
    public function __construct(
        protected LevelService $levelService,
        protected EconomyService $economyService,
        protected EnergyService $energyService,
    ) {}

    /**
     * Show Autumn Event Quests (Daily & Weekly).
     */
    public function quests(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $timeRemaining = HalloweenService::getRemainingTime();
        $questData = HalloweenService::getCharacterQuests($character);

        return view('game.halloween.quests', [
            'character' => $character,
            'timeRemaining' => $timeRemaining,
            'dailyQuests' => $questData['daily'],
            'weeklyQuests' => $questData['weekly'],
        ]);
    }

    /**
     * Show the complete Profile Borders Overview page (/profielkaders).
     */
    public function bordersIndex(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $allBorders = HalloweenService::getBorders();
        $obtainableBorders = HalloweenService::getObtainableBorders();
        $retiredBorders = HalloweenService::getRetiredBorders();
        $unlockedBorders = $character->unlocked_borders ?? [];

        $sort = in_array($request->query('sort'), ['event', 'status'], true)
            ? (string) $request->query('sort')
            : 'event';

        $filter = in_array($request->query('filter'), ['all', 'obtainable', 'unobtainable', 'owned'], true)
            ? (string) $request->query('filter')
            : 'all';

        return view('game.borders.index', [
            'character' => $character,
            'allBorders' => $allBorders,
            'obtainableBorders' => $obtainableBorders,
            'retiredBorders' => $retiredBorders,
            'unlockedBorders' => $unlockedBorders,
            'sort' => $sort,
            'filter' => $filter,
        ]);
    }

    /**
     * Claim reward for a completed Autumn Event Quest.
     */
    public function claimQuest(Request $request, string $questKey): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen Herfstoogst quests claimen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $allQuests = array_merge(HalloweenService::DAILY_QUESTS, HalloweenService::WEEKLY_QUESTS);
        if (! array_key_exists($questKey, $allQuests)) {
            return back()->with('error', 'Onbekende evenement quest.');
        }

        $quest = $allQuests[$questKey];
        $periodKey = $quest['type'] === 'daily'
            ? 'daily_'.now()->toDateString()
            : 'weekly_'.now()->format('o-W');

        $record = HalloweenQuestProgress::where('character_id', $character->id)
            ->where('quest_key', $questKey)
            ->where('period_key', $periodKey)
            ->first();

        if (! $record || $record->current_count < $quest['target']) {
            return back()->with('error', 'Je hebt deze quest nog niet voltooid!');
        }

        // Claim under a row lock so parallel requests cannot collect the same reward twice.
        $alreadyClaimed = DB::transaction(function () use ($record, $character, $quest): bool {
            /** @var HalloweenQuestProgress $lockedRecord */
            $lockedRecord = HalloweenQuestProgress::where('id', $record->id)->lockForUpdate()->first();
            if ($lockedRecord->is_claimed) {
                return true;
            }

            $lockedRecord->is_claimed = true;
            $lockedRecord->claimed_at = now();
            $lockedRecord->save();

            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            // Grant rewards
            $lockedCharacter->pumpkin_seeds = ($lockedCharacter->pumpkin_seeds ?? 0) + $quest['seeds'];

            if (! empty($quest['xp'])) {
                $this->levelService->addXp($lockedCharacter, $quest['xp']);
            }
            if (! empty($quest['respect'])) {
                $lockedCharacter->addRespect($quest['respect']);
            }
            if (! empty($quest['cash'])) {
                $this->economyService->addCash($lockedCharacter, $quest['cash']);
            }
            if (! empty($quest['tokens'])) {
                $lockedCharacter->vault_tokens = ($lockedCharacter->vault_tokens ?? 0) + $quest['tokens'];
            }

            $lockedCharacter->save();

            return false;
        });

        if ($alreadyClaimed) {
            return back()->with('info', 'Je hebt de beloning voor deze quest al geclaimd.');
        }

        return back()->with('success', "🌰 Quest voltooid! Je hebt +{$quest['seeds']} Gouden Eikels 🌰 en alle quest-bonussen geclaimd!");
    }

    /**
     * Show Autumn Event Shop.
     */
    public function shop(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $timeRemaining = HalloweenService::getRemainingTime();
        $items = HalloweenService::SHOP_ITEMS;

        $borders = array_filter($items, fn ($i) => $i['category'] === 'borders');
        $collectibles = array_filter($items, fn ($i) => $i['category'] === 'collectibles');
        $boosts = array_filter($items, fn ($i) => $i['category'] === 'boosts');
        $retiredBorders = HalloweenService::getRetiredBorders();

        return view('game.halloween.shop', [
            'character' => $character,
            'timeRemaining' => $timeRemaining,
            'borders' => $borders,
            'retiredBorders' => $retiredBorders,
            'collectibles' => $collectibles,
            'boosts' => $boosts,
        ]);
    }

    /**
     * Buy an item from the Autumn Event Shop using Golden Acorns (Gouden Eikels).
     */
    public function buy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_key' => ['required', 'string'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen evenement-items kopen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $itemKey = $validated['item_key'];

        if (in_array($itemKey, HalloweenService::RETIRED_HALLOWEEN_ITEMS, true)) {
            return back()->with('error', 'Dit Halloween-onderdeel maakt deel uit van het afgelopen Halloween-event en is niet meer haalbaar! Spelers die het hebben geclaimd behouden het als permanent verzamelobject.');
        }

        if (! array_key_exists($itemKey, HalloweenService::SHOP_ITEMS)) {
            return back()->with('error', 'Dit item is niet te vinden in de Herfstoogst Winkel.');
        }

        $item = HalloweenService::SHOP_ITEMS[$itemKey];
        $price = $item['price_seeds'];

        if ($item['type'] === 'border' && ! ($item['obtainable'] ?? true)) {
            return back()->with('error', 'Dit profielkader is niet meer haalbaar.');
        }

        if ($item['type'] === 'border' && $character->hasBorder($itemKey)) {
            return back()->with('info', 'Je bezit dit profielkader al!');
        }

        $isKnownBoost = $item['type'] === 'boost' && in_array($itemKey, ['autumn_stew', 'mist_cloak_pack', 'harvest_blessing'], true);
        if (! in_array($item['type'], ['border', 'collectible'], true) && ! $isKnownBoost) {
            return back()->with('error', 'Kon aankoop niet verwerken.');
        }

        return DB::transaction(function () use ($character, $itemKey, $item, $price): RedirectResponse {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();

            if ($item['type'] === 'border' && $locked->hasBorder($itemKey)) {
                return back()->with('info', 'Je bezit dit profielkader al!');
            }

            if (($locked->pumpkin_seeds ?? 0) < $price) {
                $short = $price - ($locked->pumpkin_seeds ?? 0);

                return back()->with('error', "Onvoldoende Gouden Eikels! Je hebt {$price} 🌰 nodig (je komt er {$short} te kort). Voltooi Herfstoogst Quests om meer eikels te verdienen!");
            }

            $locked->pumpkin_seeds -= $price;

            if ($item['type'] === 'border') {
                $locked->unlockBorder($itemKey);
                $locked->equipped_border = $itemKey;
                $locked->save();

                return back()->with('success', "🍂 {$item['name']} succesvol gekocht en direct geactiveerd op je profiel!");
            }

            if ($item['type'] === 'collectible') {
                $locked->save();

                $dbItem = Item::firstOrCreate(
                    ['slug' => 'autumn_'.$itemKey],
                    [
                        'name' => $item['name'],
                        'slug' => 'autumn_'.$itemKey,
                        'description' => $item['description'],
                        'category' => $item['consumable'] ? 'consumable' : 'special',
                        'rarity' => 'legendary',
                        'price_kc' => 2500,
                        'energy_restore' => $item['consumable'] ? 35 : 0,
                        'icon' => $item['icon'],
                        'is_buyable' => false,
                    ]
                );

                $charItem = CharacterItem::where('character_id', $locked->id)
                    ->where('item_id', $dbItem->id)
                    ->lockForUpdate()
                    ->first();

                if ($charItem) {
                    $charItem->increment('quantity');
                } else {
                    CharacterItem::create([
                        'character_id' => $locked->id,
                        'item_id' => $dbItem->id,
                        'quantity' => 1,
                        'is_equipped' => false,
                    ]);
                }

                return back()->with('success', "🍂 {$item['name']} toegevoegd aan je inventaris en cel!");
            }

            if ($itemKey === 'autumn_stew') {
                $locked->health = $locked->max_health;
                $locked->energy = min($locked->max_energy, $locked->energy + 75);
                $locked->save();

                return back()->with('success', '🍲 Je at de Krachtige Wildstoofpot! 100% Gezondheid hersteld en direct +75 Energie ontvangen!');
            }

            if ($itemKey === 'mist_cloak_pack') {
                $locked->masks = ($locked->masks ?? 0) + 5;
                $locked->save();

                return back()->with('success', '🧣 +5 Herfstnevel maskers ontvangen! Je kunt nu 5x ongemerkt zakkenrollen.');
            }

            $locked->addPerk('magere_hein_zegening', 24);
            $locked->save();

            return back()->with('success', '🌾 Zegening van de Oogst actief! Je ontvangt de komende 24 uur +15% extra Kracht & Verdediging!');
        });
    }

    /**
     * Equip or unequip an avatar border.
     */
    public function equipBorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'border_key' => ['nullable', 'string'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen profielrand instellen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $key = $validated['border_key'] ?? null;

        if (empty($key) || $key === 'none') {
            $character->equipped_border = null;
            $character->save();

            return back()->with('success', 'Profielrand gedeactiveerd. Standaard weergave hersteld.');
        }

        if (! $character->hasBorder($key)) {
            return back()->with('error', 'Je hebt dit profielkader niet in je bezit.');
        }

        $allBorders = HalloweenService::getBorders();
        $borderInfo = $allBorders[$key] ?? null;
        $emoji = $borderInfo['emoji'] ?? '🍂';
        $name = $borderInfo['name'] ?? 'Profielkader';

        $character->equipped_border = $key;
        $character->save();

        return back()->with('success', "{$emoji} Jouw profielkader '{$name}' is succesvol ingesteld!");
    }
}
