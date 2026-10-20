<?php

namespace App\Http\Controllers;

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
use Illuminate\View\View;

class PackageController extends Controller
{
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
            ['id' => 1, 'name' => 'Postpakket in de gang', 'desc' => 'Klein pakketje, waarschijnlijk een brief of wat snoepjes.', 'energy' => 5, 'token_cost' => 0, 'min' => 80, 'max' => 280, 'rate' => 55, 'cooldown' => $this->cooldownService->calculateBySuccessRate(55), 'xp' => 28, 'token_chance' => 15, 'icon' => 'fa-envelope'],
            ['id' => 2, 'name' => 'Leveranciersdoos (Keuken)', 'desc' => 'Grotere doos van een buitenlevering. Bevat cash of voorraden.', 'energy' => 10, 'token_cost' => 0, 'min' => 220, 'max' => 750, 'rate' => 45, 'cooldown' => $this->cooldownService->calculateBySuccessRate(45), 'xp' => 60, 'token_chance' => 25, 'icon' => 'fa-box-open'],
            ['id' => 3, 'name' => 'Medisch pakket (Apotheek)', 'desc' => 'Medicijnen of waardevolle goederen. Bewaking houdt toezicht.', 'energy' => 14, 'token_cost' => 0, 'min' => 500, 'max' => 1800, 'rate' => 35, 'cooldown' => $this->cooldownService->calculateBySuccessRate(35), 'xp' => 110, 'token_chance' => 35, 'icon' => 'fa-kit-medical'],
            ['id' => 4, 'name' => 'Gepantserde Geldkoffer (Directie)', 'desc' => 'Zwaar beveiligd transport van directiegelden en kluiscodes.', 'energy' => 20, 'token_cost' => 0, 'min' => 1100, 'max' => 3600, 'rate' => 25, 'cooldown' => $this->cooldownService->calculateBySuccessRate(25), 'xp' => 195, 'token_chance' => 50, 'icon' => 'fa-truck-fast'],
            ['id' => 5, 'name' => 'Geheime Masterkoffer (Exclusief)', 'desc' => 'Zwaar slot! Vereist 3 KluisMunten. Garandeert exclusieve buit en prestige.', 'energy' => 15, 'token_cost' => 3, 'min' => 2500, 'max' => 6500, 'rate' => 42, 'cooldown' => $this->cooldownService->calculateBySuccessRate(42), 'xp' => 320, 'token_chance' => 80, 'icon' => 'fa-briefcase'],
        ];

        $kofferLogs = KofferLog::where('character_id', $character->id)->latest()->take(8)->get();
        $packageSkipCost = $this->cooldownService->getSkipCost('package');

        return view('game.packages.index', compact('character', 'packages', 'kofferLogs', 'packageSkipCost'));
    }

    /**
     * Intercept a package in the corridor (illicit street action: yields cash contant).
     */
    public function intercept(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je geen koffers onderscheppen!');
        }

        if ($this->cooldownService->isOnCooldown($character, 'package')) {
            $remaining = $this->cooldownService->getRemainingSeconds($character, 'package');
            $mins = ceil($remaining / 60);

            return back()->with('error', "⏳ Koffers onderscheppen cooldown actief! Wacht nog {$remaining}s (~{$mins} min) of reset direct via een sponsor advertentie.");
        }

        $validated = $request->validate([
            'package_id' => ['required', 'integer', 'in:1,2,3,4,5'],
        ]);

        $config = [
            1 => ['name' => 'Postpakket in de gang', 'energy' => 5,  'token_cost' => 0, 'min' => 80,   'max' => 280,  'rate' => 55, 'xp' => 28,  'token_chance' => 15],
            2 => ['name' => 'Leveranciersdoos (Keuken)', 'energy' => 10, 'token_cost' => 0, 'min' => 220,  'max' => 750,  'rate' => 45, 'xp' => 60,  'token_chance' => 25],
            3 => ['name' => 'Medisch pakket (Apotheek)', 'energy' => 14, 'token_cost' => 0, 'min' => 500,  'max' => 1800, 'rate' => 35, 'xp' => 110, 'token_chance' => 35],
            4 => ['name' => 'Gepantserde Geldkoffer (Directie)', 'energy' => 20, 'token_cost' => 0, 'min' => 1100, 'max' => 3600, 'rate' => 25, 'xp' => 195, 'token_chance' => 50],
            5 => ['name' => 'Geheime Masterkoffer (Exclusief)', 'energy' => 15, 'token_cost' => 3, 'min' => 2500, 'max' => 6500, 'rate' => 42, 'xp' => 320, 'token_chance' => 80],
        ];

        $pkg = $config[$validated['package_id']];

        if ($pkg['token_cost'] > 0 && ($character->vault_tokens ?? 0) < $pkg['token_cost']) {
            return back()->with('error', "Voor deze koffer heb je minimaal {$pkg['token_cost']} KluisMunten nodig!");
        }

        if (! $this->energy->consume($character, $pkg['energy'])) {
            return back()->with('error', "Je hebt minimaal {$pkg['energy']} energie nodig om deze actie uit te voeren!");
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
                $tokensFound = ($validated['package_id'] === 5) ? rand(2, 3) : 1;
                $character->vault_tokens = ($character->vault_tokens ?? 0) + $tokensFound;
            }

            // Chance to drop an unopened bag!
            $bagDropped = false;
            if (rand(1, 100) <= 25) {
                $character->unopened_bags = ($character->unopened_bags ?? 0) + 1;
                $bagDropped = true;
            }

            $character->respect += 1;
            $character->save();

            $tokenTxt = $tokensFound > 0 ? " +{$tokensFound} KluisMunten 🔑 gevonden!" : '';
            $bagTxt = $bagDropped ? ' 🧳 BONUS: Je hebt ook een ongeopende Smokkeltas buitgemaakt!' : '';

            return back()->with('success', 'Buit binnen! Je vind contant €'.number_format($cash, 0, ',', '.')."! +{$pkg['xp']} XP{$tokenTxt}{$bagTxt}");
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

        $this->prison->jailSeconds($character, $jailSeconds, "Betrapt door de bewaking bij het forceren en onderscheppen van: {$pkg['name']}");

        return redirect()->route('prison')->with('error', "Poging mislukt! {$jailSeconds} sec vast in isolatie! (De bewaking betrapte je bij het onderscheppen)");
    }

    /**
     * Interactive bag opening: Clicking the bag opens it and deposits clean rewards directly to the bank.
     */
    public function openBag(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen tassen openen!');
        }

        if (($character->unopened_bags ?? 0) <= 0) {
            return back()->with('error', 'Je hebt geen ongeopende tassen liggen. Onderschep er een bij Pakketjes of koop er een op de Marktplaats!');
        }

        if ($character->getStorageUsed() >= $character->getStorageCapacity()) {
            return back()->with('error', "⚠️ Je opbergplaats zit overvol ({$character->getStorageUsed()}/{$character->getStorageCapacity()} plaatsen)! Verkoop eerst wat maskers of spullen in je Depot om weer buit te kunnen ontvangen.");
        }

        $character->unopened_bags -= 1;

        // Clean white money deposited directly to bank!
        $bankReward = rand(1250, 3750);
        $character->bank += $bankReward;

        $xpReward = rand(80, 180);
        $this->level->addXp($character, $xpReward);

        // Chance for stars
        $starsFound = (rand(1, 100) <= 40) ? rand(1, 2) : 0;
        if ($starsFound > 0) {
            $character->stars = ($character->stars ?? 0) + $starsFound;
        }

        // Chance for a mask (40% chance, if storage has room)
        $maskFound = (rand(1, 100) <= 40);
        if ($maskFound && $character->getStorageUsed() < $character->getStorageCapacity()) {
            $character->masks = ($character->masks ?? 0) + 1;
        } else {
            $maskFound = false;
        }

        // Chance for a secret voucher code
        $voucherCode = null;
        if (rand(1, 100) <= 30) {
            $voucherCode = 'TAS-'.strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 6));
            VaultCode::create([
                'code' => $voucherCode,
                'reward_cash' => rand(1000, 2500),
                'reward_stars' => 1,
                'reward_tokens' => 2,
                'character_id' => $character->id,
                'is_redeemed' => false,
            ]);
        }

        $character->respect += 2;
        $character->save();

        $logParts = ['€'.number_format($bankReward, 0, ',', '.').' contant'];
        if ($starsFound > 0) {
            $logParts[] = "{$starsFound} sterren";
        }
        if ($maskFound) {
            $logParts[] = '1 masker';
        }
        if ($voucherCode) {
            $logParts[] = '1 kluiscode';
        }

        KofferLog::create([
            'character_id' => $character->id,
            'loot_summary' => implode(', ', $logParts),
        ]);

        $starTxt = $starsFound > 0 ? " +{$starsFound} Exclusieve Sterren ⭐" : '';
        $maskTxt = $maskFound ? ' +1 Masker 🎭' : '';
        $voucherTxt = $voucherCode ? " 🔐 Geheime Kluiscode gevonden: {$voucherCode} (opgeslagen in je postvak/kluis!)" : '';

        return back()->with('success', '🧳 RRRITSS! Je hebt de koffer geopend! Buit: €'.number_format($bankReward, 0, ',', '.')." wit geld direct op je bank 🏦! +{$xpReward} XP{$starTxt}{$maskTxt}{$voucherTxt}");
    }

    /**
     * Sell an unopened bag directly to the black market broker for clean bank cash.
     */
    public function sellBag(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen koffers verkopen!');
        }

        $amount = max(1, (int) $request->input('amount', 1));

        if (($character->unopened_bags ?? 0) < $amount) {
            return back()->with('error', 'Je hebt niet genoeg ongeopende koffers om te verkopen.');
        }

        $character->unopened_bags -= $amount;
        $rewardPerBag = 2500;
        $totalReward = $rewardPerBag * $amount;
        $character->bank += $totalReward;
        $character->save();

        return back()->with('success', "💰 Verkocht! Je hebt {$amount}x Smokkelkoffer verkocht voor €".number_format($totalReward, 0, ',', '.').' wit bankgeld 🏦!');
    }

    /**
     * Open an XXL Koffer with massive rewards.
     */
    public function openXxl(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen XXL koffers openen!');
        }

        if (($character->xxl_cases ?? 0) <= 0) {
            return back()->with('error', 'Je hebt geen XXL koffers in je bezit! Win ze in de 24 Uur Battle of koop er een met sterren.');
        }

        if ($character->getStorageUsed() >= $character->getStorageCapacity()) {
            return back()->with('error', "⚠️ Je opbergplaats zit overvol ({$character->getStorageUsed()}/{$character->getStorageCapacity()} plaatsen)! Verkoop eerst wat maskers of spullen in je Depot om weer buit te kunnen ontvangen.");
        }

        $character->xxl_cases -= 1;

        // Big rewards!
        $bankReward = rand(25000, 75000);
        $character->bank += $bankReward;

        $xpReward = rand(600, 1500);
        $this->level->addXp($character, $xpReward);

        $starsFound = rand(10, 25);
        $character->stars = ($character->stars ?? 0) + $starsFound;

        $spaceLeft = max(0, $character->getStorageCapacity() - $character->getStorageUsed());
        $masksFound = min(rand(5, 12), $spaceLeft);
        $character->masks = ($character->masks ?? 0) + $masksFound;

        $respectGain = rand(15, 30);
        $character->respect += $respectGain;

        // Generate 2-4 secret vault codes
        $codeCount = rand(2, 4);
        $generatedCodes = [];
        for ($i = 0; $i < $codeCount; $i++) {
            $codeStr = 'XXL-'.strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 6));
            VaultCode::create([
                'code' => $codeStr,
                'reward_cash' => rand(5000, 15000),
                'reward_stars' => rand(1, 3),
                'reward_tokens' => rand(3, 6),
                'character_id' => $character->id,
                'is_redeemed' => false,
            ]);
            $generatedCodes[] = $codeStr;
        }

        $character->save();

        // Record battle slay progress
        app(DailyBattleService::class)->record($character, 'slay', $respectGain);

        KofferLog::create([
            'character_id' => $character->id,
            'loot_summary' => '[XXL KOFFER] €'.number_format($bankReward, 0, ',', '.').", {$starsFound} sterren, {$masksFound} maskers, {$codeCount} kluiscodes",
        ]);

        $codesStr = implode(', ', $generatedCodes);

        return back()->with('success', '💎💥 BOOM! XXL KOFFER GEKRAAKT! Buit: €'.number_format($bankReward, 0, ',', '.')." direct op je bank 🏦, +{$starsFound} Sterren ⭐, +{$masksFound} Maskers 🎭, +{$respectGain} Respect en {$codeCount} geheime kluiscodes: ({$codesStr})!");
    }

    /**
     * Purchase an XXL Koffer for stars.
     */
    public function buyXxl(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;
        $cost = 100; // 100 Sterren for XXL Koffer

        if (($character->stars ?? 0) < $cost) {
            return back()->with('error', "Je hebt minimaal {$cost} Sterren ⭐ nodig om een XXL Koffer te kopen (je hebt er {$character->stars}).");
        }

        $character->stars -= $cost;
        $character->xxl_cases = ($character->xxl_cases ?? 0) + 1;
        $character->save();

        // Record payments in battle
        app(DailyBattleService::class)->record($character, 'payments', $cost * 500);

        return back()->with('success', "🎉 XXL Koffer aangeschaft voor {$cost} Sterren ⭐! Je kunt hem direct openen voor monsterlijke buit!");
    }
}
