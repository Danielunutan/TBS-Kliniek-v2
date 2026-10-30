<?php

use App\Models\Character;
use App\Models\CharacterArmWrestleStat;
use App\Models\CharacterItem;
use App\Models\Item;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Normalize all character stats, level caps, inventory stacks, item prices, timers, and spin counters.
     */
    public function up(): void
    {
        $this->normalizeItemsCatalog();
        $this->normalizeCharacterInventories();
        $this->normalizeCharacters();
        $this->normalizeArmWrestleStats();
    }

    /**
     * Ensure all shop and loot items have consistent categories, buy prices, and depot sell prices.
     */
    private function normalizeItemsCatalog(): void
    {
        if (! Schema::hasTable('items')) {
            return;
        }

        $hasSellPrice = Schema::hasColumn('items', 'sell_price_kc');

        // Guarantee Oude Sleutel has the unified shop buy price (€800), equipment category (+10 Intelligence), and depot sell price (€480).
        $oudeSleutelUpdate = [
            'price_kc' => 800,
            'category' => 'equipment',
            'intelligence_bonus' => 10,
            'icon' => 'fa-key',
            'description' => 'Een roestige sleutel gevonden bij de wasserij (+10 Intelligentie).',
            'is_buyable' => true,
        ];
        if ($hasSellPrice) {
            $oudeSleutelUpdate['sell_price_kc'] = 480;
        }

        Item::query()->where('slug', 'oude-sleutel')->update($oudeSleutelUpdate);

        // Align Sleutel van Paviljoen 13, Gouden Frikandel, Bivakmuts Masker, Smokkelpakketje, and Free icons.
        Item::query()->where('slug', 'sleutel-paviljoen-13')->update([
            'category' => 'equipment',
            'price_kc' => 5000,
            'intelligence_bonus' => 12,
            'icon' => 'fa-key',
        ]);
        Item::query()->where('slug', 'gouden-frikandel')->update([
            'category' => 'consumable',
            'price_kc' => 1500,
            'health_restore' => 50,
            'description' => 'Het legendarische statussymbool van de kantine. Herstelt direct +50 HP.',
        ]);
        Item::query()->where('slug', 'bivakmuts-masker')->update([
            'price_kc' => 2500,
        ]);
        Item::query()->where('slug', 'smokkelpakketje')->update([
            'is_buyable' => false,
        ]);
        Item::query()->where('icon', 'fa-key-skeleton')->update(['icon' => 'fa-key']);
        Item::query()->where('icon', 'fa-mitten')->update(['icon' => 'fa-hand-fist']);
        Item::query()->where('icon', 'fa-vest')->update(['icon' => 'fa-shirt']);

        if (! $hasSellPrice) {
            return;
        }

        $lootSellPrices = [
            'koperdraad' => 280,
            'gouden-tand' => 850,
            'gestolen-medicatiepas' => 620,
            'directeurs-zegelring' => 2400,
        ];

        foreach ($lootSellPrices as $slug => $sellPrice) {
            Item::query()->where('slug', $slug)->update([
                'sell_price_kc' => $sellPrice,
                'is_buyable' => false,
                'category' => 'loot',
            ]);
        }

        // Ensure no buyable item has an invalid or higher depot sell price than its shop buy price.
        Item::query()
            ->where('is_buyable', true)
            ->where('price_kc', '>', 0)
            ->get()
            ->each(function (Item $item): void {
                $expectedSell = max(1, (int) round($item->price_kc * Item::DEFAULT_SELL_RATIO));
                if ((int) $item->sell_price_kc <= 0 || (int) $item->sell_price_kc > (int) $item->price_kc || (int) $item->sell_price_kc !== $expectedSell) {
                    $item->sell_price_kc = $expectedSell;
                    $item->save();
                }
            });
    }

    /**
     * Deduplicate inventory rows, move counter items to character counters, and cap equipment at 1 with bank refund.
     */
    private function normalizeCharacterInventories(): void
    {
        if (! Schema::hasTable('character_items') || ! Schema::hasTable('items') || ! Schema::hasTable('characters')) {
            return;
        }

        // Remove zero/negative or orphaned inventory records.
        CharacterItem::query()->where('quantity', '<=', 0)->delete();
        CharacterItem::query()->whereNotIn('item_id', Item::query()->select('id'))->delete();
        CharacterItem::query()->whereNotIn('character_id', Character::query()->select('id'))->delete();

        // Merge any duplicate (character_id, item_id) rows.
        $duplicates = DB::table('character_items')
            ->select('character_id', 'item_id', DB::raw('COUNT(*) as row_count'), DB::raw('SUM(quantity) as total_qty'), DB::raw('MIN(id) as keep_id'))
            ->groupBy('character_id', 'item_id')
            ->having('row_count', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            CharacterItem::query()->where('id', $dup->keep_id)->update(['quantity' => (int) $dup->total_qty]);
            CharacterItem::query()
                ->where('character_id', $dup->character_id)
                ->where('item_id', $dup->item_id)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        // Move counter items (masks and unopened bags) out of character_items onto character columns.
        $maskItem = Item::query()->where('slug', 'bivakmuts-masker')->first();
        if ($maskItem) {
            CharacterItem::query()
                ->where('item_id', $maskItem->id)
                ->get()
                ->each(function (CharacterItem $ci): void {
                    $character = Character::find($ci->character_id);
                    if ($character) {
                        $character->masks = max(0, (int) ($character->masks ?? 0)) + max(0, (int) $ci->quantity);
                        $character->save();
                    }
                    $ci->delete();
                });
        }

        $bagItem = Item::query()->where('slug', 'smokkelpakketje')->first();
        if ($bagItem) {
            CharacterItem::query()
                ->where('item_id', $bagItem->id)
                ->get()
                ->each(function (CharacterItem $ci): void {
                    $character = Character::find($ci->character_id);
                    if ($character) {
                        $character->unopened_bags = max(0, (int) ($character->unopened_bags ?? 0)) + max(0, (int) $ci->quantity);
                        $character->save();
                    }
                    $ci->delete();
                });
        }

        // Cap unique equipment items at quantity = 1 and refund excess copies at depot sell value to bank.
        $equipmentItems = Item::query()->where('category', 'equipment')->get()->keyBy('id');
        if ($equipmentItems->isNotEmpty()) {
            CharacterItem::query()
                ->whereIn('item_id', $equipmentItems->keys()->all())
                ->where('quantity', '>', 1)
                ->get()
                ->each(function (CharacterItem $ci) use ($equipmentItems): void {
                    $extraQty = max(0, (int) $ci->quantity - 1);
                    $item = $equipmentItems->get($ci->item_id);
                    if ($extraQty > 0 && $item) {
                        $character = Character::find($ci->character_id);
                        if ($character) {
                            $refund = $extraQty * $item->getDepotSellPrice();
                            $character->bank = max(0, (int) ($character->bank ?? 0)) + $refund;
                            $character->save();
                        }
                    }
                    $ci->quantity = 1;
                    $ci->save();
                });
        }
    }

    /**
     * Normalize all characters: stat caps (with XP conversion), vital bars, VIP energy, timers, and spin counters.
     */
    private function normalizeCharacters(): void
    {
        if (! Schema::hasTable('characters')) {
            return;
        }

        Character::query()->chunkById(100, function ($characters): void {
            foreach ($characters as $character) {
                $character->normalizeCharacterState();
            }
        });
    }

    /**
     * Ensure arm wrestling statistics have non-negative counters and consistent streaks.
     */
    private function normalizeArmWrestleStats(): void
    {
        if (! Schema::hasTable('character_arm_wrestle_stats')) {
            return;
        }

        CharacterArmWrestleStat::query()->chunkById(100, function ($stats): void {
            foreach ($stats as $stat) {
                $wins = max(0, (int) ($stat->wins ?? 0));
                $losses = max(0, (int) ($stat->losses ?? 0));
                $currentStreak = max(0, (int) ($stat->current_streak ?? 0));
                $bestStreak = max($currentStreak, max(0, (int) ($stat->best_streak ?? 0)));

                if (
                    (int) $stat->wins !== $wins ||
                    (int) $stat->losses !== $losses ||
                    (int) $stat->current_streak !== $currentStreak ||
                    (int) $stat->best_streak !== $bestStreak
                ) {
                    $stat->wins = $wins;
                    $stat->losses = $losses;
                    $stat->current_streak = $currentStreak;
                    $stat->best_streak = $bestStreak;
                    $stat->save();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data normalization is non-reversible.
    }
};
