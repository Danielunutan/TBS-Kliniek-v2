<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\KofferLog;
use App\Models\VaultCode;
use App\Services\CooldownService;
use App\Services\DailyBattleService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use App\Services\PrisonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PackageController extends Controller
{
    /**
     * Define the three cracking approaches for opening intercepted cases.
     *
     * @var array<string, array{
     *     key: string,
     *     name: string,
     *     subtitle: string,
     *     success_rate: int,
     *     cash_multiplier: float,
     *     fail_cash_multiplier: float,
     *     star_chance: int,
     *     mask_chance: int,
     *     code_chance: int
     * }>
     */
    public const CRACK_METHODS = [
        'precisie' => [
            'key' => 'precisie',
            'name' => 'Voorzichtig Openen (Precisie-Loper)',
            'subtitle' => 'Gegarandeerde standaardbuit zonder enig risico op beschadigde inhoud.',
            'success_rate' => 100,
            'cash_multiplier' => 1.00,
            'fail_cash_multiplier' => 1.00,
            'star_chance' => 8,
            'mask_chance' => 40,
            'code_chance' => 30,
        ],
        'thermisch' => [
            'key' => 'thermisch',
            'name' => 'Thermische Snijbrander',
            'subtitle' => '75% kans op +35% extra bankbuit en hogere itemkans, 25% kans op schroeischade.',
            'success_rate' => 75,
            'cash_multiplier' => 1.35,
            'fail_cash_multiplier' => 0.40,
            'star_chance' => 12,
            'mask_chance' => 55,
            'code_chance' => 42,
        ],
        'forceren' => [
            'key' => 'forceren',
            'name' => 'Ruwe Kracht & Forceren',
            'subtitle' => '60% kans op +75% topbuit en extra sterren, 40% kans dat de verfvlam-beveiliging afgaat.',
            'success_rate' => 60,
            'cash_multiplier' => 1.75,
            'fail_cash_multiplier' => 0.15,
            'star_chance' => 18,
            'mask_chance' => 65,
            'code_chance' => 50,
        ],
    ];

    public function __construct(
        protected EconomyService $economy,
        protected EnergyService $energy,
        protected LevelService $level,
        protected PrisonService $prison,
        protected CooldownService $cooldownService,
    ) {}

    public function index(): View
    {
        $character = Auth::user()->character;
        $this->energy->refresh($character);

        $packages = [
            ['id' => 1, 'name' => 'Koerierszending bij de Receptie', 'desc' => 'Onbewaakte envelop met klein kasgeld of verstopte smokkelwaar.', 'energy' => 5, 'token_cost' => 0, 'min' => 80, 'max' => 280, 'rate' => 55, 'cooldown' => $this->cooldownService->calculateBySuccessRate(55), 'xp' => 28, 'token_chance' => 15, 'icon' => 'fa-envelope'],
            ['id' => 2, 'name' => 'Beleveringskrat Grootkeuken', 'desc' => 'Afgesloten voorraadkist van een externe transporteur vol zwarte handel.', 'energy' => 10, 'token_cost' => 0, 'min' => 220, 'max' => 750, 'rate' => 45, 'cooldown' => $this->cooldownService->calculateBySuccessRate(45), 'xp' => 60, 'token_chance' => 25, 'icon' => 'fa-box-open'],
            ['id' => 3, 'name' => 'Verzegelde Apothekerscontainer', 'desc' => 'Waardevolle medische zending onder cameratoezicht op de vleugel.', 'energy' => 14, 'token_cost' => 0, 'min' => 500, 'max' => 1800, 'rate' => 35, 'cooldown' => $this->cooldownService->calculateBySuccessRate(35), 'xp' => 110, 'token_chance' => 35, 'icon' => 'fa-kit-medical'],
            ['id' => 4, 'name' => 'Waardetransport Directievleugel', 'desc' => 'Zwaar vergrendelde geldkist met kasreserves en toegangscodes.', 'energy' => 20, 'token_cost' => 0, 'min' => 1100, 'max' => 3600, 'rate' => 25, 'cooldown' => $this->cooldownService->calculateBySuccessRate(25), 'xp' => 195, 'token_chance' => 50, 'icon' => 'fa-truck-fast'],
            ['id' => 5, 'name' => 'Diplomatieke Kluiskoffer (Speciaal)', 'desc' => 'Vereist 3 KluisMunten. Hoogwaardige buit met maximale kans op extra munten.', 'energy' => 15, 'token_cost' => 3, 'min' => 2500, 'max' => 6500, 'rate' => 42, 'cooldown' => $this->cooldownService->calculateBySuccessRate(42), 'xp' => 320, 'token_chance' => 80, 'icon' => 'fa-briefcase'],
        ];

        $kofferLogs = KofferLog::where('character_id', $character->id)->latest()->take(8)->get();
        $packageSkipCost = $this->cooldownService->getSkipCost('package');
        $crackMethods = self::CRACK_METHODS;

        return view('game.packages.index', compact('character', 'packages', 'kofferLogs', 'packageSkipCost', 'crackMethods'));
    }

    /**
     * Intercept a package in the clinic corridor.
     */
    public function intercept(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Zolang je in de strafcel zit, kun je geen zendingen onderscheppen.');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Kritieke toestand (Code Blauw)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($this->cooldownService->isOnCooldown($character, 'package')) {
            $remaining = $this->cooldownService->getRemainingSeconds($character, 'package');
            $mins = ceil($remaining / 60);

            return back()->with('error', "⏳ De goederensluis wordt nu streng bewaakt! Wacht nog {$remaining}s (~{$mins} min) of koop de wachttijd af.");
        }

        $validated = $request->validate([
            'package_id' => ['required', 'integer', 'in:1,2,3,4,5'],
        ]);

        $config = [
            1 => ['name' => 'Koerierszending bij de Receptie', 'energy' => 5, 'token_cost' => 0, 'min' => 80, 'max' => 280, 'rate' => 55, 'xp' => 28, 'token_chance' => 15],
            2 => ['name' => 'Beleveringskrat Grootkeuken', 'energy' => 10, 'token_cost' => 0, 'min' => 220, 'max' => 750, 'rate' => 45, 'xp' => 60, 'token_chance' => 25],
            3 => ['name' => 'Verzegelde Apothekerscontainer', 'energy' => 14, 'token_cost' => 0, 'min' => 500, 'max' => 1800, 'rate' => 35, 'xp' => 110, 'token_chance' => 35],
            4 => ['name' => 'Waardetransport Directievleugel', 'energy' => 20, 'token_cost' => 0, 'min' => 1100, 'max' => 3600, 'rate' => 25, 'xp' => 195, 'token_chance' => 50],
            5 => ['name' => 'Diplomatieke Kluiskoffer (Speciaal)', 'energy' => 15, 'token_cost' => 3, 'min' => 2500, 'max' => 6500, 'rate' => 42, 'xp' => 320, 'token_chance' => 80],
        ];

        $pkg = $config[$validated['package_id']];

        if ($pkg['token_cost'] > 0 && ($character->vault_tokens ?? 0) < $pkg['token_cost']) {
            return back()->with('error', "Je hebt ten minste {$pkg['token_cost']} KluisMunten nodig om dit slot te ontgrendelen.");
        }

        if (! $this->energy->consume($character, $pkg['energy'])) {
            return back()->with('error', "Onvoldoende uithouding: deze onderschepping vraagt {$pkg['energy']} energie.");
        }

        $cooldownSeconds = $this->cooldownService->calculateBySuccessRate($pkg['rate']);
        $this->cooldownService->setCooldown($character, 'package', $cooldownSeconds);

        if ($pkg['token_cost'] > 0) {
            $character->vault_tokens -= $pkg['token_cost'];
        }

        $roll = rand(1, 100);
        if ($roll <= $pkg['rate']) {
            $cash = rand($pkg['min'], $pkg['max']);
            $this->economy->addCash($character, $cash);
            $this->level->addXp($character, $pkg['xp']);

            $tokensFound = 0;
            if (rand(1, 100) <= $pkg['token_chance']) {
                $tokensFound = ((int) $validated['package_id'] === 5) ? rand(2, 3) : 1;
                $character->vault_tokens = ($character->vault_tokens ?? 0) + $tokensFound;
            }

            $bagDropped = false;
            if (rand(1, 100) <= 25) {
                $character->unopened_bags = ($character->unopened_bags ?? 0) + 1;
                $bagDropped = true;
            }

            $character->addRespect(1);
            $character->save();

            $tokenTxt = $tokensFound > 0 ? " +{$tokensFound} KluisMunten 🔑 buitgemaakt!" : '';
            $bagTxt = $bagDropped ? ' 🧳 EXTRA: Er zat een verzegelde Smokkelkoffer in de zending!' : '';

            return back()->with('success', 'Zending onderschept! Je haalt €'.number_format($cash, 0, ',', '.')." contant uit het pakket (+{$pkg['xp']} XP){$tokenTxt}{$bagTxt}");
        }

        $character->save();

        $jailSeconds = match ((int) $validated['package_id']) {
            1 => random_int(60, 90),
            2 => random_int(90, 130),
            3 => random_int(120, 180),
            4 => random_int(180, 240),
            5 => random_int(210, 300),
            default => 120,
        };

        $this->prison->jailSeconds($character, $jailSeconds, "Op heterdaad betrapt bij de goederensluis: {$pkg['name']}");

        return redirect()->route('prison')->with('error', "Alarm bij de goederensluis! Je zit {$jailSeconds} sec vast in isolatie nadat bewakers je omsingelden.");
    }

    /**
     * Open a single intercepted bag using the chosen cracking method and chest slot.
     */
    public function openBag(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'crack_method' => ['nullable', 'string', 'in:precisie,thermisch,forceren'],
            'chest_slot' => ['nullable', 'integer', 'in:1,2,3'],
        ]);

        $methodKey = $validated['crack_method'] ?? 'precisie';
        $method = self::CRACK_METHODS[$methodKey] ?? self::CRACK_METHODS['precisie'];
        $chestSlot = (int) ($validated['chest_slot'] ?? 1);
        $user = Auth::user();

        return DB::transaction(function () use ($user, $method, $chestSlot) {
            /** @var Character $character */
            $character = Character::where('id', $user->character->id)->lockForUpdate()->first();

            if ($character->isImprisoned()) {
                return back()->with('error', 'Vanuit de strafcel heb je geen toegang tot je opgeslagen koffers.');
            }

            if ($character->isComatose()) {
                return back()->with('error', 'Kritieke toestand (Code Blauw)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
            }

            if (($character->unopened_bags ?? 0) <= 0) {
                return back()->with('error', 'Geen verzegelde koffers op voorraad. Onderschep eerst een zending of schaf er een aan op de Marktplaats.');
            }

            $character->unopened_bags -= 1;

            $isCleanCrack = rand(1, 100) <= $method['success_rate'];
            $baseBank = rand(1250, 3750);
            $multiplier = $isCleanCrack ? $method['cash_multiplier'] : $method['fail_cash_multiplier'];
            $bankReward = max(200, (int) round($baseBank * $multiplier));
            $character->bank += $bankReward;

            $xpReward = rand(80, 180);
            $this->level->addXp($character, $xpReward);

            $starsFound = 0;
            $maskFound = false;
            $voucherCode = null;

            if ($isCleanCrack) {
                $starsFound = (rand(1, 100) <= $method['star_chance']) ? 1 : 0;
                if ($starsFound > 0) {
                    $character->stars = ($character->stars ?? 0) + $starsFound;
                }

                $maskFound = (rand(1, 100) <= $method['mask_chance']);
                if ($maskFound) {
                    $character->masks = ($character->masks ?? 0) + 1;
                }

                if (rand(1, 100) <= $method['code_chance']) {
                    $voucherCode = VaultCode::generateUniqueCode('TAS');
                    VaultCode::create([
                        'code' => $voucherCode,
                        'reward_cash' => rand(1000, 2500),
                        'reward_stars' => 0,
                        'reward_tokens' => 2,
                        'character_id' => $character->id,
                        'is_redeemed' => false,
                    ]);
                }
            }

            $character->addRespect($isCleanCrack ? 2 : 1);
            $character->save();

            $slotNames = [1 => 'Kist Alpha', 2 => 'Kist Beta', 3 => 'Kist Omega'];
            $slotLabel = $slotNames[$chestSlot] ?? 'Kist Alpha';

            $logParts = ["[{$slotLabel} • {$method['name']}] €".number_format($bankReward, 0, ',', '.').' bankgeld'];
            if (! $isCleanCrack) {
                $logParts[] = 'beveiligingsschade';
            }
            if ($starsFound > 0) {
                $logParts[] = "{$starsFound} sterren";
            }
            if ($maskFound) {
                $logParts[] = '1 bivakmuts';
            }
            if ($voucherCode) {
                $logParts[] = '1 kluiscode';
            }

            KofferLog::create([
                'character_id' => $character->id,
                'loot_summary' => implode(', ', $logParts),
            ]);

            if (! $isCleanCrack) {
                return back()->with(
                    'success',
                    "⚠️ De beveiligingspatroon in {$slotLabel} sprong open tijdens '{$method['name']}'! Restwaarde gered: €".number_format($bankReward, 0, ',', '.')." op je bankrekening (+{$xpReward} XP)."
                );
            }

            $starTxt = $starsFound > 0 ? " +{$starsFound} Ster ⭐" : '';
            $maskTxt = $maskFound ? ' +1 Bivakmuts 🎭' : '';
            $voucherTxt = $voucherCode ? " 🔐 Kluiscode ontdekt: {$voucherCode}" : '';

            return back()->with(
                'success',
                "🔓 {$slotLabel} succesvol geopend via {$method['name']}! Opbrengst: €".number_format($bankReward, 0, ',', '.')." schoon bankgeld (+{$xpReward} XP){$starTxt}{$maskTxt}{$voucherTxt}."
            );
        });
    }

    /**
     * Open up to 50 unopened bags in a single transaction using the selected cracking method.
     */
    public function openBagAll(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'crack_method' => ['nullable', 'string', 'in:precisie,thermisch,forceren'],
        ]);

        $methodKey = $validated['crack_method'] ?? 'precisie';
        $method = self::CRACK_METHODS[$methodKey] ?? self::CRACK_METHODS['precisie'];
        $user = Auth::user();

        return DB::transaction(function () use ($user, $method) {
            /** @var Character $character */
            $character = Character::where('id', $user->character->id)->lockForUpdate()->first();

            if ($character->isImprisoned()) {
                return back()->with('error', 'Vanuit de strafcel heb je geen toegang tot je opgeslagen koffers.');
            }

            if ($character->isComatose()) {
                return back()->with('error', 'Kritieke toestand (Code Blauw)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
            }

            $availableBags = (int) ($character->unopened_bags ?? 0);

            if ($availableBags <= 0) {
                return back()->with('error', 'Geen verzegelde koffers op voorraad om in bulk te openen.');
            }

            $totalBags = min(50, $availableBags);

            $totalBank = 0;
            $totalXp = 0;
            $totalStars = 0;
            $totalMasks = 0;
            $totalRespect = 0;
            $cleanHits = 0;
            $voucherCodes = [];

            for ($i = 0; $i < $totalBags; $i++) {
                $isClean = rand(1, 100) <= $method['success_rate'];
                $mult = $isClean ? $method['cash_multiplier'] : $method['fail_cash_multiplier'];
                $totalBank += max(200, (int) round(rand(1250, 3750) * $mult));
                $totalXp += rand(80, 180);
                $totalRespect += $isClean ? 2 : 1;

                if ($isClean) {
                    $cleanHits++;
                    if (rand(1, 100) <= $method['star_chance']) {
                        $totalStars += 1;
                    }

                    if (rand(1, 100) <= $method['mask_chance']) {
                        $totalMasks += 1;
                    }

                    if (rand(1, 100) <= 25 && count($voucherCodes) < 3) {
                        $code = VaultCode::generateUniqueCode('TAS');
                        VaultCode::create([
                            'code' => $code,
                            'reward_cash' => rand(1000, 2500),
                            'reward_stars' => 0,
                            'reward_tokens' => 2,
                            'character_id' => $character->id,
                            'is_redeemed' => false,
                        ]);
                        $voucherCodes[] = $code;
                    }
                }
            }

            $character->unopened_bags -= $totalBags;
            $character->bank += $totalBank;
            $character->stars = ($character->stars ?? 0) + $totalStars;
            $character->masks = ($character->masks ?? 0) + $totalMasks;
            $character->addRespect($totalRespect);

            $this->level->addXp($character, $totalXp);
            $character->save();

            $logParts = ['€'.number_format($totalBank, 0, ',', '.').' bankgeld'];
            if ($totalStars > 0) {
                $logParts[] = "{$totalStars} sterren";
            }
            if ($totalMasks > 0) {
                $logParts[] = "{$totalMasks} bivakmutsen";
            }
            if (count($voucherCodes) > 0) {
                $logParts[] = count($voucherCodes).' kluiscodes';
            }

            KofferLog::create([
                'character_id' => $character->id,
                'loot_summary' => "[{$totalBags}x SERIE • {$method['name']}] ".implode(', ', $logParts),
            ]);

            $starTxt = $totalStars > 0 ? ", +{$totalStars} Sterren" : '';
            $maskTxt = $totalMasks > 0 ? ", +{$totalMasks} Bivakmutsen" : '';
            $voucherTxt = count($voucherCodes) > 0
                ? ' en '.count($voucherCodes).' Kluiscodes'
                : '';

            return back()->with(
                'success',
                "Serie-ontgrendeling voltooid ({$cleanHits}/{$totalBags} schadevrij via {$method['name']})! Totaal gestort: €".number_format($totalBank, 0, ',', '.')." op je bank, +{$totalXp} XP{$starTxt}{$maskTxt}{$voucherTxt}."
            );
        });
    }

    /**
     * Sell unopened bags directly to the underground fence for bank cash.
     */
    public function sellBag(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $user = Auth::user();
        $amount = max(1, min(100, (int) ($validated['amount'] ?? 1)));

        return DB::transaction(function () use ($user, $amount) {
            /** @var Character $character */
            $character = Character::where('id', $user->character->id)->lockForUpdate()->first();

            if ($character->isImprisoned()) {
                return back()->with('error', 'Vanuit de strafcel kun je geen ongeopende koffers verhandelen.');
            }

            if ($character->isComatose()) {
                return back()->with('error', 'Kritieke toestand (Code Blauw)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
            }

            if (($character->unopened_bags ?? 0) < $amount) {
                return back()->with('error', 'Je beschikt niet over dit aantal verzegelde koffers.');
            }

            $character->unopened_bags -= $amount;
            $rewardPerBag = 2500;
            $totalReward = $rewardPerBag * $amount;
            $character->bank += $totalReward;
            $character->save();

            return back()->with('success', "💼 Doorverkocht aan de heler! {$amount}x verzegelde koffer ingeleverd voor €".number_format($totalReward, 0, ',', '.').' schoon bankgeld.');
        });
    }

    /**
     * Open an XXL Case with high-roller rewards.
     */
    public function openXxl(Request $request): RedirectResponse
    {
        $user = Auth::user();

        return DB::transaction(function () use ($user) {
            /** @var Character $character */
            $character = Character::where('id', $user->character->id)->lockForUpdate()->first();

            if ($character->isImprisoned()) {
                return back()->with('error', 'Vanuit de strafcel kun je geen XXL Kluiskoffers openen.');
            }

            if ($character->isComatose()) {
                return back()->with('error', 'Kritieke toestand (Code Blauw)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
            }

            if (($character->xxl_cases ?? 0) <= 0) {
                return back()->with('error', 'Geen XXL Kluiskoffers op voorraad. Verover ze in het Etmaal-Protocol of schaf er een aan met Sterren.');
            }

            $character->xxl_cases -= 1;

            $bankReward = rand(25000, 75000);
            $character->bank += $bankReward;

            $xpReward = rand(600, 1500);
            $this->level->addXp($character, $xpReward);

            $starsFound = rand(2, 4);
            $character->stars = ($character->stars ?? 0) + $starsFound;

            $masksFound = rand(5, 12);
            $character->masks = ($character->masks ?? 0) + $masksFound;

            $respectGain = rand(15, 30);
            $character->addRespect($respectGain);

            $codeCount = rand(2, 4);
            $generatedCodes = [];
            for ($i = 0; $i < $codeCount; $i++) {
                $codeStr = VaultCode::generateUniqueCode('XXL');
                VaultCode::create([
                    'code' => $codeStr,
                    'reward_cash' => rand(5000, 15000),
                    'reward_stars' => (rand(1, 100) <= 20 ? 1 : 0),
                    'reward_tokens' => rand(3, 6),
                    'character_id' => $character->id,
                    'is_redeemed' => false,
                ]);
                $generatedCodes[] = $codeStr;
            }

            $character->save();

            app(DailyBattleService::class)->record($character, 'slay', $respectGain);

            KofferLog::create([
                'character_id' => $character->id,
                'loot_summary' => '[XXL KLUISKIST] €'.number_format($bankReward, 0, ',', '.').", {$starsFound} sterren, {$masksFound} bivakmutsen, {$codeCount} kluiscodes",
            ]);

            $codesStr = implode(', ', $generatedCodes);

            return back()->with('success', '👑 XXL KLUISKOFFER ONTGRENDELD! Gestort op je bank: €'.number_format($bankReward, 0, ',', '.').", +{$starsFound} Sterren ⭐, +{$masksFound} Bivakmutsen 🎭, +{$respectGain} Gezag en {$codeCount} kluiscodes ({$codesStr}).");
        });
    }

    /**
     * Purchase an XXL Case using stars.
     */
    public function buyXxl(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $cost = 100;

        return DB::transaction(function () use ($user, $cost) {
            /** @var Character $character */
            $character = Character::where('id', $user->character->id)->lockForUpdate()->first();

            if ($character->isImprisoned()) {
                return back()->with('error', 'Vanuit de strafcel kun je geen XXL Kluiskoffers aanschaffen.');
            }

            if ($character->isComatose()) {
                return back()->with('error', 'Kritieke toestand (Code Blauw)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
            }

            if (($character->stars ?? 0) < $cost) {
                return back()->with('error', "Onvoldoende Sterren: een XXL Kluiskoffer kost {$cost} Sterren ⭐ (huidig saldo: {$character->stars}).");
            }

            $character->stars -= $cost;
            $character->xxl_cases = ($character->xxl_cases ?? 0) + 1;
            $character->save();

            app(DailyBattleService::class)->record($character, 'payments', $cost * 500);

            return back()->with('success', "👑 XXL Kluiskoffer toegevoegd aan je voorraad voor {$cost} Sterren ⭐! Kies hieronder direct wanneer je hem openbreekt.");
        });
    }
}
