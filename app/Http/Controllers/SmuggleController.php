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
use App\Services\EventDrugsProductionService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SmuggleController extends Controller
{
    /**
     * Define the tactical transport routes for contraband delivery.
     *
     * @var array<string, array{
     *     key: string,
     *     name: string,
     *     subtitle: string,
     *     success_rate: int,
     *     cash_multiplier: float,
     *     respect_bonus: int,
     *     xp_multiplier: float,
     *     loss_fraction: float,
     *     badge: string
     * }>
     */
    public const TRANSPORT_ROUTES = [
        'sluiproute' => [
            'key' => 'sluiproute',
            'name' => 'Voorzichtige Sluiproute',
            'subtitle' => 'Verborgen doorvoer via de oude ventilatieschachten zonder patrouilles.',
            'success_rate' => 100,
            'cash_multiplier' => 1.00,
            'respect_bonus' => 0,
            'xp_multiplier' => 1.00,
            'loss_fraction' => 0.0,
            'badge' => '100% Slaagkans • Standaard Winst',
        ],
        'koerier' => [
            'key' => 'koerier',
            'name' => 'Gecamoufleerde Wasserijkar',
            'subtitle' => 'Snelle doorvoer tussen het vuile linnengoed met beperkt controlerisico.',
            'success_rate' => 85,
            'cash_multiplier' => 1.15,
            'respect_bonus' => 2,
            'xp_multiplier' => 1.10,
            'loss_fraction' => 0.5,
            'badge' => '85% Slaagkans • +15% Bonuswinst',
        ],
        'stormloop' => [
            'key' => 'stormloop',
            'name' => 'Roekeloze Snelle Route',
            'subtitle' => 'Directe overdracht dwars over de bewaakte binnenplaats voor maximale premie.',
            'success_rate' => 70,
            'cash_multiplier' => 1.25,
            'respect_bonus' => 5,
            'xp_multiplier' => 1.25,
            'loss_fraction' => 1.0,
            'badge' => '70% Slaagkans • +25% Bonuswinst',
        ],
    ];

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

        $cigarettes = $this->getItemQuantity($character->id, 'sigaretten');
        $ritalin = $this->getItemQuantity($character->id, 'ritalin');
        $phones = $this->getItemQuantity($character->id, 'telefoon');
        $weed = $this->getItemQuantity($character->id, 'wiet');

        $stock = [
            'sigaretten' => $cigarettes,
            'ritalin' => $ritalin,
            'telefoon' => $phones,
            'wiet' => $weed,
        ];

        $rawOrders = $this->getActiveOrders($character->id);
        $orders = [];
        foreach ($rawOrders as $key => $ord) {
            $ord['can_fulfill'] = ($stock[$ord['item_slug']] ?? 0) >= $ord['amount'];
            $ord['current_stock'] = $stock[$ord['item_slug']] ?? 0;
            $orders[$key] = $ord;
        }

        $transportRoutes = self::TRANSPORT_ROUTES;

        return view('game.smuggle.index', compact(
            'character',
            'cigarettes',
            'ritalin',
            'phones',
            'weed',
            'orders',
            'transportRoutes'
        ));
    }

    public function produce(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de strafcel heb je geen toegang tot het synthese-laboratorium.');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Kritieke toestand (Code Blauw)! Gebruik eerst een '.Character::REVIVE_SERUM_NAME.' om bij bewustzijn te komen.');
        }

        $type = $request->input('type') ?? $request->input('item_type');
        if ($type === 'cigarettes') {
            $type = 'sigaretten';
        }
        if ($type === 'phone') {
            $type = 'telefoon';
        }
        if ($type === 'weed') {
            $type = 'wiet';
        }
        $request->merge(['type' => $type]);

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:sigaretten,ritalin,telefoon,wiet'],
        ]);

        $actionName = 'produce_'.$validated['type'];
        if ($this->cooldownService->isOnCooldown($character, $actionName) || ($validated['type'] === 'telefoon' && $this->cooldownService->isOnCooldown($character, 'produce_phone'))) {
            $checkAction = $this->cooldownService->isOnCooldown($character, $actionName) ? $actionName : 'produce_phone';
            $remaining = $this->cooldownService->getRemainingSeconds($character, $checkAction);
            $mins = ceil($remaining / 60);
            $cost = $this->cooldownService->getSkipCost($checkAction);

            return back()->with('error', "⏳ Synthese-lijn koelt nog af ({$remaining}s / ~{$mins} min resterend). Versnel direct voor {$cost} Sterren ⭐.");
        }

        $configs = [
            'sigaretten' => ['cost' => 5, 'quantity' => 10, 'xp' => 35, 'name' => '10x Zware Shagwikkels'],
            'ritalin' => ['cost' => 10, 'quantity' => 5, 'xp' => 65, 'name' => '5x Ritalin Blisters'],
            'telefoon' => ['cost' => 20, 'quantity' => 1, 'xp' => 150, 'name' => '1x Burner-Toestel'],
            'wiet' => ['cost' => 15, 'quantity' => 8, 'xp' => 95, 'name' => '8x Kaswiet Toppen'],
        ];

        $cfg = $configs[$validated['type']];

        if (! $this->energy->consume($character, $cfg['cost'])) {
            return back()->with('error', "Je mist de benodigde uithouding ({$cfg['cost']} energie vereist voor deze batch).");
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
        EventDrugsProductionService::record($character, $cfg['quantity'], $validated['type']);

        $this->cooldownService->setCooldown($character, $actionName);

        return back()->with('success', "Geproduceerd! Batch van {$cfg['name']} is veilig opgeslagen in je geheime smokkelkluis (+{$cfg['xp']} XP).");
    }

    public function sell(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Koeriersroutes zijn geblokkeerd zolang je in de strafcel verblijft.');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Kritieke toestand (Code Blauw)! Gebruik eerst een '.Character::REVIVE_SERUM_NAME.' om bij bewustzijn te komen.');
        }

        $validated = $request->validate([
            'order_id' => ['required', 'string', 'in:order_1,order_2,order_3'],
            'transport_mode' => ['nullable', 'string', 'in:sluiproute,koerier,stormloop'],
            'transport_route' => ['nullable', 'string', 'in:sluiproute,koerier,stormloop'],
        ]);

        $routeKey = $validated['transport_mode'] ?? $validated['transport_route'] ?? 'sluiproute';
        $routeConfig = self::TRANSPORT_ROUTES[$routeKey] ?? self::TRANSPORT_ROUTES['sluiproute'];

        $orders = $this->getActiveOrders($character->id);
        $order = $orders[$validated['order_id']] ?? null;

        if (! $order) {
            return back()->with('error', 'Deze smokkelopdracht is inmiddels verlopen.');
        }

        $item = Item::where('slug', $order['item_slug'])->first();

        if (! $item) {
            return back()->with('error', 'Gevraagde handelswaar kon niet worden gevonden.');
        }

        $roll = rand(1, 100);
        $isSuccess = $roll <= $routeConfig['success_rate'];
        $finalCash = (int) round($order['reward_cash'] * $routeConfig['cash_multiplier']);
        $finalRespect = (int) $order['reward_respect'] + $routeConfig['respect_bonus'];
        $deductAmount = $isSuccess
            ? (int) $order['amount']
            : max(1, (int) ceil($order['amount'] * $routeConfig['loss_fraction']));

        $transactionDone = DB::transaction(function () use ($character, $item, $order, $isSuccess, $finalCash, $finalRespect, $deductAmount): bool {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            $charItem = CharacterItem::where('character_id', $lockedCharacter->id)
                ->where('item_id', $item->id)
                ->where('quantity', '>', 0)
                ->lockForUpdate()
                ->first();

            if (! $charItem || $charItem->quantity < $order['amount']) {
                return false;
            }

            $charItem->decrement('quantity', $deductAmount);
            if ($charItem->fresh()?->quantity <= 0) {
                $charItem->delete();
            }

            if ($isSuccess) {
                $this->economy->addCash($lockedCharacter, $finalCash);
                $lockedCharacter->addRespect($finalRespect);
                $lockedCharacter->save();
            }

            return true;
        });

        if (! $transactionDone) {
            return back()->with('error', "Voorraad ontoereikend: je mist voldoende {$item->name} voor de bestelling van {$order['buyer']}.");
        }

        $character->refresh();

        if (! $isSuccess) {
            $orders[$validated['order_id']] = $this->generateRandomOrder($validated['order_id']);
            session(["smuggle_orders_{$character->id}" => $orders]);

            return back()->with('error', "Onderschept op de {$routeConfig['name']}! Bewakers namen {$deductAmount}x {$item->name} in beslag voordat je {$order['buyer']} bereikte.");
        }

        app(DailyBattleService::class)->record($character, 'drugs_sold', $order['amount']);

        $baseXp = max(45, (int) round(($character->level * 80) * 0.05 + $order['reward_cash'] * 0.2));
        $xpGain = (int) round($baseXp * $routeConfig['xp_multiplier']);
        $this->level->addXp($character, $xpGain);

        $orders[$validated['order_id']] = $this->generateRandomOrder($validated['order_id']);
        session(["smuggle_orders_{$character->id}" => $orders]);

        return back()->with(
            'success',
            "Zending afgeleverd bij {$order['buyer']} via {$routeConfig['name']}! Opbrengst: €".number_format($finalCash, 0, ',', '.')." contant, +{$finalRespect} Gezag en +{$xpGain} XP."
        );
    }

    /**
     * Dismiss a buyer request and replace it with a fresh contact in that slot.
     */
    public function reject(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Koeriersroutes zijn geblokkeerd zolang je in de strafcel verblijft.');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Kritieke toestand (Code Blauw)! Gebruik eerst een '.Character::REVIVE_SERUM_NAME.' om bij bewustzijn te komen.');
        }

        $validated = $request->validate([
            'order_id' => ['required', 'string', 'in:order_1,order_2,order_3'],
        ]);

        $orders = $this->getActiveOrders($character->id);
        $orders[$validated['order_id']] = $this->generateRandomOrder($validated['order_id']);
        session(["smuggle_orders_{$character->id}" => $orders]);

        return back()->with('info', 'Koper weggestuurd. Een nieuwe tussenpersoon wacht nu bij het doorgeefluik.');
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
            ['slug' => 'wiet', 'name' => 'Nederwiet Toppen', 'amount' => rand(3, 7), 'rate' => 1800, 'respect' => rand(6, 12)],
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
            'description' => 'Stevige shagwikkels die dienen als ondergronds ruilmiddel op elke afdeling.',
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
            'description' => 'Achterovergedrukte medicatiestrips uit de afgesloten apothekerskast.',
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
            'description' => 'Compacte burner-telefoon voor ontraceerbare lijnen buiten de kliniekmuren.',
            'category' => 'contraband',
            'rarity' => 'rare',
            'icon' => 'fa-mobile-screen',
            'price_kc' => 500,
            'energy_restore' => 50,
            'health_restore' => 25,
            'is_buyable' => false,
        ]);

        Item::firstOrCreate(['slug' => 'wiet'], [
            'name' => 'Nederwiet Toppen',
            'description' => 'Sterk geurende toppen uit een gecamoufleerde kweekkast achter het ketelhuis.',
            'category' => 'contraband',
            'rarity' => 'rare',
            'icon' => 'fa-cannabis',
            'price_kc' => 150,
            'energy_restore' => 30,
            'health_restore' => 20,
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
