<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\Item;
use App\Models\User;
use App\Services\CooldownService;
use App\Services\DailyBattleService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SmuggleController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected EnergyService $energy,
        protected LevelService $level,
        protected CooldownService $cooldownService,
    ) {}

    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energy->refresh($character);

        $this->ensureContrabandItemsExist();

        // Get character contraband inventory
        $cigarettes = $this->getItemQuantity($character->id, 'sigaretten');
        $ritalin = $this->getItemQuantity($character->id, 'ritalin');
        $phones = $this->getItemQuantity($character->id, 'telefoon');

        $stock = [
            'sigaretten' => $cigarettes,
            'ritalin' => $ritalin,
            'telefoon' => $phones,
        ];

        $rawOrders = $this->getActiveOrders($character->id);
        $orders = [];
        foreach ($rawOrders as $key => $ord) {
            $ord['can_fulfill'] = ($stock[$ord['item_slug']] ?? 0) >= $ord['amount'];
            $orders[$key] = $ord;
        }

        return view('game.smuggle.index', compact('character', 'cigarettes', 'ritalin', 'phones', 'orders'));
    }

    public function produce(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'In de isoleercel heb je geen toegang tot de productiekelder!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $type = $request->input('type') ?? $request->input('item_type');
        if ($type === 'cigarettes') {
            $type = 'sigaretten';
        }
        if ($type === 'phone') {
            $type = 'telefoon';
        }
        $request->merge(['type' => $type]);

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:sigaretten,ritalin,telefoon'],
        ]);

        $actionName = 'produce_'.$validated['type'];
        if ($this->cooldownService->isOnCooldown($character, $actionName) || ($validated['type'] === 'telefoon' && $this->cooldownService->isOnCooldown($character, 'produce_phone'))) {
            $checkAction = $this->cooldownService->isOnCooldown($character, $actionName) ? $actionName : 'produce_phone';
            $remaining = $this->cooldownService->getRemainingSeconds($character, $checkAction);
            $mins = ceil($remaining / 60);
            $cost = $this->cooldownService->getSkipCost($checkAction);

            return back()->with('error', "⏳ Productie bezig! Wacht nog {$remaining}s (~{$mins} min) of rond direct af met {$cost} Sterren ⭐.");
        }

        $configs = [
            'sigaretten' => ['cost' => 5, 'quantity' => 10, 'xp' => 35, 'name' => '10x Sigaretten'],
            'ritalin' => ['cost' => 10, 'quantity' => 5, 'xp' => 65, 'name' => '5x Ritalin Strips'],
            'telefoon' => ['cost' => 20, 'quantity' => 1, 'xp' => 150, 'name' => '1x Gesmokkelde Telefoon'],
        ];

        $cfg = $configs[$validated['type']];

        if (! $this->energy->consume($character, $cfg['cost'])) {
            return back()->with('error', "Niet genoeg energie! Je hebt {$cfg['cost']} energie nodig.");
        }

        $this->ensureContrabandItemsExist();
        $item = Item::where('slug', $validated['type'])->firstOrFail();

        $charItem = CharacterItem::firstOrCreate(
            ['character_id' => $character->id, 'item_id' => $item->id],
            ['quantity' => 0]
        );

        $charItem->increment('quantity', $cfg['quantity']);
        $this->level->addXp($character, $cfg['xp']);

        app(DailyBattleService::class)->record($character, 'drugs_made', $cfg['quantity']);

        // Set difficulty-scaled production cooldown
        $this->cooldownService->setCooldown($character, $actionName);

        return back()->with('success', "Geproduceerd! {$cfg['name']} toegevoegd aan je smokkelvoorraad (+{$cfg['xp']} XP).");
    }

    public function sell(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Je zit in de isoleercel en kunt niet handelen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $validated = $request->validate([
            'order_id' => ['required', 'string', 'in:order_1,order_2,order_3'],
        ]);

        $orders = $this->getActiveOrders($character->id);
        $order = $orders[$validated['order_id']] ?? null;

        if (! $order) {
            return back()->with('error', 'Aanbod niet meer geldig.');
        }

        $item = Item::where('slug', $order['item_slug'])->first();

        if (! $item) {
            return back()->with('error', 'Item niet gevonden.');
        }

        $charItem = CharacterItem::where('character_id', $character->id)
            ->where('item_id', $item->id)
            ->where('quantity', '>', 0)
            ->first();

        if (! $charItem || $charItem->quantity < $order['amount']) {
            return back()->with('error', "Je hebt niet genoeg {$item->name} om aan {$order['buyer']} te leveren!");
        }

        $charItem->decrement('quantity', $order['amount']);
        if ($charItem->fresh()?->quantity <= 0) {
            $charItem->delete();
        }
        $this->economy->addCash($character, $order['reward_cash']);
        $character->addRespect($order['reward_respect']);
        $character->save();

        app(DailyBattleService::class)->record($character, 'drugs_sold', $order['amount']);

        // 5% Rank XP reward for completing the sale
        $xpGain = max(45, (int) round(($character->level * 80) * 0.05 + $order['reward_cash'] * 0.2));
        $this->level->addXp($character, $xpGain);

        // Immediately roll a brand new randomized buyer offer in this slot!
        $orders[$validated['order_id']] = $this->generateRandomOrder($validated['order_id']);
        session(["smuggle_orders_{$character->id}" => $orders]);

        return back()->with('success', "Deal gesloten met {$order['buyer']}! Je ontvangt €".number_format($order['reward_cash'], 0, ',', '.')." contant, +{$order['reward_respect']} Respect en +{$xpGain} Rank XP! Er staat direct een nieuw aanbod klaar.");
    }

    /**
     * Decline a buyer offer to roll a new buyer in that slot.
     */
    public function reject(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $validated = $request->validate([
            'order_id' => ['required', 'string', 'in:order_1,order_2,order_3'],
        ]);

        $orders = $this->getActiveOrders($character->id);
        $orders[$validated['order_id']] = $this->generateRandomOrder($validated['order_id']);
        session(["smuggle_orders_{$character->id}" => $orders]);

        return back()->with('info', 'Aanbod afgewezen. Er heeft zich direct een nieuwe koper gemeld!');
    }

    protected function getActiveOrders(int $characterId): array
    {
        $sessionKey = "smuggle_orders_{$characterId}";
        $avatarMap = $this->getBuyerAvatarMap();
        $orders = session($sessionKey, []);
        $updated = false;

        foreach (['order_1', 'order_2', 'order_3'] as $slot) {
            if (! isset($orders[$slot]) || ! is_array($orders[$slot])) {
                $orders[$slot] = $this->generateRandomOrder($slot);
                $updated = true;
            } elseif (isset($orders[$slot]['buyer'], $avatarMap[$orders[$slot]['buyer']])) {
                if (($orders[$slot]['avatar'] ?? '') !== $avatarMap[$orders[$slot]['buyer']]) {
                    $orders[$slot]['avatar'] = $avatarMap[$orders[$slot]['buyer']];
                    $updated = true;
                }
            }
        }

        if ($updated || ! session()->has($sessionKey)) {
            session([$sessionKey => $orders]);
        }

        return $orders;
    }

    /**
     * @return array<string, string>
     */
    protected function getBuyerAvatarMap(): array
    {
        return [
            'Henkie de Frikandelkoning' => asset('assets/characters/henkie.jpg'),
            'Elara uit Paviljoen 3' => asset('assets/characters/elara.jpg'),
            'Malik (Ganglid)' => asset('assets/characters/malik.jpg'),
            'Ricardo de Spierbundel' => asset('assets/characters/ricardo.jpg'),
            'Grote Barry' => asset('assets/characters/barry.jpg'),
            'Debbie van de Nachtdienst' => asset('assets/characters/debbie.jpg'),
            'Samantha uit Paviljoen 7' => asset('assets/characters/samantha.jpg'),
            'Wesley de Ritselaar' => asset('assets/characters/wesley.jpg'),
        ];
    }

    protected function generateRandomOrder(string $slotId): array
    {
        $avatarMap = $this->getBuyerAvatarMap();
        $buyerNames = array_keys($avatarMap);
        $randomBuyer = $buyerNames[array_rand($buyerNames)];

        $itemsPool = [
            ['slug' => 'sigaretten', 'name' => 'Sigaretten', 'amount' => rand(4, 8), 'rate' => 350, 'respect' => rand(2, 5)],
            ['slug' => 'ritalin', 'name' => 'Ritalin Strips', 'amount' => rand(2, 5), 'rate' => 1200, 'respect' => rand(4, 8)],
            ['slug' => 'telefoon', 'name' => 'Gesmokkelde Telefoon', 'amount' => 1, 'rate' => 8500, 'respect' => rand(10, 18)],
        ];

        $itemConfig = $itemsPool[array_rand($itemsPool)];
        $totalCash = $itemConfig['amount'] * $itemConfig['rate'];

        return [
            'id' => $slotId,
            'buyer' => $randomBuyer,
            'avatar' => $avatarMap[$randomBuyer],
            'item_slug' => $itemConfig['slug'],
            'item_name' => $itemConfig['name'],
            'amount' => $itemConfig['amount'],
            'reward_cash' => $totalCash,
            'reward_respect' => $itemConfig['respect'],
        ];
    }

    private function ensureContrabandItemsExist(): void
    {
        Item::firstOrCreate(['slug' => 'sigaretten'], [
            'name' => 'Sigaretten',
            'description' => 'Pakjes zware tabak, het meest verhandelde betaalmiddel in de kliniek.',
            'category' => 'contraband',
            'rarity' => 'common',
            'icon' => 'fa-smoking',
            'price_kc' => 25,
            'energy_restore' => 20,
            'health_restore' => 10,
            'is_buyable' => false,
        ]);

        Item::firstOrCreate(['slug' => 'ritalin'], [
            'name' => 'Ritalin Strips',
            'description' => 'Illegaal verhandelde medicatie van de psychiatrische afdeling.',
            'category' => 'contraband',
            'rarity' => 'uncommon',
            'icon' => 'fa-pills',
            'price_kc' => 80,
            'energy_restore' => 35,
            'health_restore' => 15,
            'is_buyable' => false,
        ]);

        Item::firstOrCreate(['slug' => 'telefoon'], [
            'name' => 'Gesmokkelde Telefoon',
            'description' => 'Een kleine mobiel om contact te onderhouden met de buitenwereld.',
            'category' => 'contraband',
            'rarity' => 'rare',
            'icon' => 'fa-mobile-screen',
            'price_kc' => 500,
            'energy_restore' => 50,
            'health_restore' => 25,
            'is_buyable' => false,
        ]);
    }

    private function getItemQuantity(int $characterId, string $slug): int
    {
        $item = Item::where('slug', $slug)->first();
        if (! $item) {
            return 0;
        }

        return (int) CharacterItem::where('character_id', $characterId)
            ->where('item_id', $item->id)
            ->where('quantity', '>', 0)
            ->value('quantity') ?? 0;
    }
}
