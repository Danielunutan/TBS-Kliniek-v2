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
     * Show Halloween Quests (Daily & Weekly).
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
     * Claim reward for a completed Halloween Quest.
     */
    public function claimQuest(Request $request, string $questKey): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $allQuests = array_merge(HalloweenService::DAILY_QUESTS, HalloweenService::WEEKLY_QUESTS);
        if (! array_key_exists($questKey, $allQuests)) {
            return back()->with('error', 'Onbekende Halloween quest.');
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

            // Grant rewards
            $character->pumpkin_seeds = ($character->pumpkin_seeds ?? 0) + $quest['seeds'];

            if (! empty($quest['xp'])) {
                $this->levelService->addXp($character, $quest['xp']);
            }
            if (! empty($quest['respect'])) {
                $character->addRespect($quest['respect']);
            }
            if (! empty($quest['cash'])) {
                $this->economyService->addCash($character, $quest['cash']);
            }
            if (! empty($quest['tokens'])) {
                $character->vault_tokens = ($character->vault_tokens ?? 0) + $quest['tokens'];
            }

            $character->save();

            return false;
        });

        if ($alreadyClaimed) {
            return back()->with('info', 'Je hebt de beloning voor deze quest al geclaimd.');
        }

        return back()->with('success', "🎉 Quest voltooid! Je hebt +{$quest['seeds']} Pompoenpitten 🎃 en alle quest-bonussen geclaimd!");
    }

    /**
     * Show Halloween Shop.
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

        return view('game.halloween.shop', [
            'character' => $character,
            'timeRemaining' => $timeRemaining,
            'borders' => $borders,
            'collectibles' => $collectibles,
            'boosts' => $boosts,
        ]);
    }

    /**
     * Buy an item from the Halloween Shop using Pumpkin Seeds.
     */
    public function buy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_key' => ['required', 'string'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $itemKey = $validated['item_key'];
        if (! array_key_exists($itemKey, HalloweenService::SHOP_ITEMS)) {
            return back()->with('error', 'Dit Halloween item is niet te vinden in de shop.');
        }

        $item = HalloweenService::SHOP_ITEMS[$itemKey];
        $price = $item['price_seeds'];

        if ($item['type'] === 'border' && $character->hasBorder($itemKey)) {
            return back()->with('info', 'Je bezit deze Halloween profielrand al!');
        }

        $isKnownBoost = $item['type'] === 'boost' && in_array($itemKey, ['witch_brew', 'pumpkin_mask', 'grim_blessing'], true);
        if (! in_array($item['type'], ['border', 'collectible'], true) && ! $isKnownBoost) {
            return back()->with('error', 'Kon aankoop niet verwerken.');
        }

        // Spend the seeds under a row lock so parallel purchases cannot use the same seeds twice.
        $seedsSpent = DB::transaction(function () use ($character, $price): bool {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();
            if (($locked->pumpkin_seeds ?? 0) < $price) {
                return false;
            }

            $locked->pumpkin_seeds -= $price;
            $locked->save();

            return true;
        });

        if (! $seedsSpent) {
            $character->refresh();
            $short = $price - ($character->pumpkin_seeds ?? 0);

            return back()->with('error', "Onvoldoende Pompoenpitten! Je hebt {$price} 🎃 nodig (je komt er {$short} te kort). Voltooi Halloween Quests om meer pitten te verdienen!");
        }

        $character->refresh();

        // Handle specific item types
        if ($item['type'] === 'border') {
            $character->unlockBorder($itemKey);
            $character->equipped_border = $itemKey;
            $character->save();

            return back()->with('success', "🎃 {$item['name']} succesvol gekocht en direct geactiveerd op je profiel!");
        }

        if ($item['type'] === 'collectible') {
            $dbItem = Item::firstOrCreate(
                ['slug' => 'halloween_'.$itemKey],
                [
                    'name' => $item['name'],
                    'slug' => 'halloween_'.$itemKey,
                    'description' => $item['description'],
                    'category' => $item['consumable'] ? 'consumable' : 'special',
                    'rarity' => 'legendary',
                    'price_kc' => 2500,
                    'energy_restore' => $item['consumable'] ? 35 : 0,
                    'icon' => $item['icon'],
                    'is_buyable' => false,
                ]
            );

            $charItem = CharacterItem::where('character_id', $character->id)
                ->where('item_id', $dbItem->id)
                ->first();

            if ($charItem) {
                $charItem->increment('quantity');
            } else {
                CharacterItem::create([
                    'character_id' => $character->id,
                    'item_id' => $dbItem->id,
                    'quantity' => 1,
                    'is_equipped' => false,
                ]);
            }

            return back()->with('success', "🎃 {$item['name']} toegevoegd aan je inventaris en cel!");
        }

        if ($itemKey === 'witch_brew') {
            $character->health = $character->max_health;
            $character->energy = min($character->max_energy, $character->energy + 75);
            $character->save();

            return back()->with('success', '🧪 Je dronk het Heksenbrouwsel! 100% Gezondheid hersteld en direct +75 Energie ontvangen!');
        }

        if ($itemKey === 'pumpkin_mask') {
            $character->masks = ($character->masks ?? 0) + 5;
            $character->save();

            return back()->with('success', "🎭 +5 Jack-O'-Lantern maskers ontvangen! Je kunt nu 5x ongemerkt zakkenrollen.");
        }

        $character->addPerk('magere_hein_zegening', 24);
        $character->save();

        return back()->with('success', '💀 Magere Hein Zegening actief! Je ontvangt de komende 24 uur +15% extra Kracht & Verdediging!');
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

        $key = $validated['border_key'] ?? null;

        if (empty($key) || $key === 'none') {
            $character->equipped_border = null;
            $character->save();

            return back()->with('success', 'Profielrand gedeactiveerd. Standaard weergave hersteld.');
        }

        if (! $character->hasBorder($key)) {
            return back()->with('error', 'Je hebt deze profielrand nog niet vrijgespeeld in de Halloween Shop.');
        }

        $character->equipped_border = $key;
        $character->save();

        return back()->with('success', '🎃 Jouw Halloween profielrand is succesvol ingesteld!');
    }
}
