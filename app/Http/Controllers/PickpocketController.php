<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Services\CooldownService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\HalloweenService;
use App\Services\LevelService;
use App\Services\PrisonService;
use App\Services\RandomDropService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PickpocketController extends Controller
{
    /**
     * Define the three sleight-of-hand techniques available during pickpocketing.
     *
     * @var array<string, array{
     *     key: string,
     *     name: string,
     *     subtitle: string,
     *     rate_mod: int,
     *     cash_mult: float,
     *     xp_mult: float,
     *     respect_bonus: int,
     *     alert_increase: int
     * }>
     */
    public const TECHNIQUES = [
        'schaduw' => [
            'key' => 'schaduw',
            'name' => 'Schaduwstap',
            'subtitle' => 'Behoedzame benadering vanuit een dode hoek voor hogere trefzekerheid.',
            'rate_mod' => 8,
            'cash_mult' => 0.90,
            'xp_mult' => 1.00,
            'respect_bonus' => 0,
            'alert_increase' => 10,
        ],
        'afleiding' => [
            'key' => 'afleiding',
            'name' => 'Schijnbotsing',
            'subtitle' => 'Korte afleidingstruc in de menigte met uitgebalanceerde buit en +15% extra XP.',
            'rate_mod' => 0,
            'cash_mult' => 1.00,
            'xp_mult' => 1.15,
            'respect_bonus' => 0,
            'alert_increase' => 18,
        ],
        'snelle_greep' => [
            'key' => 'snelle_greep',
            'name' => 'Bliksemgreep',
            'subtitle' => 'Diepe greep in de binnenzak voor +35% extra zwart geld en +1 extra gezag.',
            'rate_mod' => -6,
            'cash_mult' => 1.35,
            'xp_mult' => 1.10,
            'respect_bonus' => 1,
            'alert_increase' => 28,
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

        $invMaskItem = CharacterItem::where('character_id', $character->id)
            ->whereHas('item', fn ($q) => $q->where('slug', 'bivakmuts-masker'))
            ->first();
        if ($invMaskItem && $invMaskItem->quantity > 0) {
            $character->masks = ($character->masks ?? 0) + $invMaskItem->quantity;
            $invMaskItem->delete();
            $character->save();
        }

        $actions = [
            ['id' => 1, 'location' => 'Bezoekershal', 'name' => 'Familielid bij de Controlepoort', 'desc' => 'Ongemerkt losgeld of een telefoon uit een openstaande jaszak vissen.', 'energy' => 6, 'min' => 80, 'max' => 350, 'rate' => 52, 'cooldown' => $this->cooldownService->calculateBySuccessRate(52), 'xp' => 35, 'icon' => 'fa-person-walking'],
            ['id' => 2, 'location' => 'Uitgiftebalie', 'name' => 'Apotheek-Assistent', 'desc' => 'Toeslaan op het exacte moment dat de medicijnkar wordt omgedraaid.', 'energy' => 10, 'min' => 200, 'max' => 800, 'rate' => 40, 'cooldown' => $this->cooldownService->calculateBySuccessRate(40), 'xp' => 70, 'icon' => 'fa-user-nurse'],
            ['id' => 3, 'location' => 'Eetzaal', 'name' => 'Kantinemedewerker bij de Kassa', 'desc' => 'Handige vingerbeweging richting de geldlade tijdens de lunchdrukte.', 'energy' => 14, 'min' => 500, 'max' => 1800, 'rate' => 28, 'cooldown' => $this->cooldownService->calculateBySuccessRate(28), 'xp' => 115, 'icon' => 'fa-cash-register'],
            ['id' => 4, 'location' => 'Hoofdgang', 'name' => 'Externe Toezichthouder', 'desc' => 'Waardevolle documentmap en contante vergoedingen uit de tas lichten.', 'energy' => 18, 'min' => 1000, 'max' => 3500, 'rate' => 18, 'cooldown' => $this->cooldownService->calculateBySuccessRate(18), 'xp' => 200, 'icon' => 'fa-vault'],
            ['id' => 5, 'location' => 'Spreekkamer', 'name' => 'Strafrechtadvocaat', 'desc' => 'Dikke envelop met zwart voorschotgeld uit het colbert stelen.', 'energy' => 22, 'min' => 2000, 'max' => 5500, 'rate' => 14, 'cooldown' => $this->cooldownService->calculateBySuccessRate(14), 'xp' => 260, 'icon' => 'fa-briefcase'],
            ['id' => 6, 'location' => 'Directievleugel', 'name' => 'Kliniekdirecteur op Inspectie', 'desc' => 'Het hoogste doelwit van het complex met een zwaarbeveiligde portefeuille.', 'energy' => 26, 'min' => 3500, 'max' => 9000, 'rate' => 10, 'cooldown' => $this->cooldownService->calculateBySuccessRate(10), 'xp' => 360, 'icon' => 'fa-stethoscope'],
        ];

        $alertness = $this->getAlertness($character->id);
        $isScouted = (bool) session("pickpocket_scouted_{$character->id}", false);
        $alertMod = $this->calculateAlertnessModifier($alertness, $isScouted);
        $techniques = self::TECHNIQUES;
        $ppSkipCost = $this->cooldownService->isOnCooldown($character, 'pickpocket')
            ? $this->cooldownService->calculateDynamicSkipCost($character, 'pickpocket')
            : $this->cooldownService->getSkipCost('pickpocket');

        return view('game.pickpocket.index', compact(
            'character',
            'actions',
            'ppSkipCost',
            'alertness',
            'isScouted',
            'alertMod',
            'techniques'
        ));
    }

    /**
     * Scout the corridor patrol pattern to lower alertness and gain a temporary observation bonus.
     */
    public function observe(): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de strafcel kun je geen wachtpatronen observeren.');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Kritieke toestand (Code Blauw)! Gebruik eerst een '.Character::REVIVE_SERUM_NAME.' om bij bewustzijn te komen.');
        }

        if (! $this->energy->consume($character, 2)) {
            return back()->with('error', 'Je hebt ten minste 2 energie nodig om de wachtposten te observeren.');
        }

        $currentAlert = $this->getAlertness($character->id);
        $newAlert = max(0, $currentAlert - 25);

        session([
            "pickpocket_alertness_{$character->id}" => $newAlert,
            "pickpocket_scouted_{$character->id}" => true,
        ]);

        return back()->with(
            'success',
            "🔭 Wachtpatroon in kaart gebracht! De Vleugel-Alertheid daalde naar {$newAlert}% en je ontvangt +5% Verkennings-Bonus op je eerstvolgende greep."
        );
    }

    public function steal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action_id' => ['required', 'integer', 'in:1,2,3,4,5,6'],
            'technique' => ['nullable', 'string', 'in:schaduw,afleiding,snelle_greep'],
        ]);

        $techKey = $validated['technique'] ?? 'schaduw';
        $tech = self::TECHNIQUES[$techKey] ?? self::TECHNIQUES['schaduw'];

        $config = [
            1 => ['energy' => 6, 'min' => 80, 'max' => 350, 'rate' => 52, 'xp' => 35, 'jail' => false, 'jail_min' => 0],
            2 => ['energy' => 10, 'min' => 200, 'max' => 800, 'rate' => 40, 'xp' => 70, 'jail' => false, 'jail_min' => 0],
            3 => ['energy' => 14, 'min' => 500, 'max' => 1800, 'rate' => 28, 'xp' => 115, 'jail' => true, 'jail_min' => 7],
            4 => ['energy' => 18, 'min' => 1000, 'max' => 3500, 'rate' => 18, 'xp' => 200, 'jail' => true, 'jail_min' => 18],
            5 => ['energy' => 22, 'min' => 2000, 'max' => 5500, 'rate' => 14, 'xp' => 260, 'jail' => true, 'jail_min' => 24],
            6 => ['energy' => 26, 'min' => 3500, 'max' => 9000, 'rate' => 10, 'xp' => 360, 'jail' => true, 'jail_min' => 35],
        ];

        $action = $config[$validated['action_id']];
        $user = Auth::user();

        return DB::transaction(function () use ($user, $tech, $action) {
            /** @var Character $character */
            $character = Character::where('id', $user->character->id)->lockForUpdate()->first();

            if ($character->isImprisoned()) {
                return redirect()->route('prison')->with('error', 'Zolang je in de strafcel zit, kun je niemand rollen.');
            }

            if ($character->isComatose()) {
                return back()->with('error', 'Kritieke toestand (Code Blauw)! Gebruik eerst een '.Character::REVIVE_SERUM_NAME.' om bij bewustzijn te komen.');
            }

            if ($this->cooldownService->isOnCooldown($character, 'pickpocket')) {
                $remaining = $this->cooldownService->getRemainingSeconds($character, 'pickpocket');
                $mins = ceil($remaining / 60);

                return back()->with('error', "⏳ Je moet je nog gedeisd houden ({$remaining}s / ~{$mins} min resterend). Koop de wachttijd direct af met Sterren.");
            }

            $invMaskItem = CharacterItem::where('character_id', $character->id)
                ->whereHas('item', fn ($q) => $q->where('slug', 'bivakmuts-masker'))
                ->lockForUpdate()
                ->first();
            if ($invMaskItem && $invMaskItem->quantity > 0) {
                $character->masks = ($character->masks ?? 0) + $invMaskItem->quantity;
                $invMaskItem->delete();
                $character->save();
            }

            if (($character->masks ?? 0) < 1) {
                return back()->with('error', 'Zonder Bivakmuts 🎭 herkent iedereen je direct! Haal eerst nieuwe bivakmutsen in de Winkel.');
            }

            if (! $this->energy->consume($character, $action['energy'])) {
                return back()->with('error', "Je komt uithouding tekort ({$action['energy']} energie vereist voor dit doelwit).");
            }

            $alertness = $this->getAlertness($character->id);
            $isScouted = (bool) session("pickpocket_scouted_{$character->id}", false);
            $alertMod = $this->calculateAlertnessModifier($alertness, $isScouted);

            $effectiveRate = max(5, min(95, $action['rate'] + $tech['rate_mod'] + $alertMod));

            $cooldownSeconds = $this->cooldownService->calculateBySuccessRate($action['rate']);
            $this->cooldownService->setCooldown($character, 'pickpocket', $cooldownSeconds);

            $newAlertness = min(100, $alertness + $tech['alert_increase']);
            session([
                "pickpocket_alertness_{$character->id}" => $newAlertness,
                "pickpocket_scouted_{$character->id}" => false,
            ]);

            $roll = rand(1, 100);
            if ($roll <= $effectiveRate) {
                $character->masks = max(0, ($character->masks ?? 1) - 1);

                $baseCash = rand($action['min'], $action['max']);
                $cash = max(50, (int) round($baseCash * $tech['cash_mult']));
                if ($character->hasPerk('grotere_broekzakken')) {
                    $cash = (int) round($cash * 1.10);
                }

                $xpGain = (int) round($action['xp'] * $tech['xp_mult']);
                $respectGain = 1 + $tech['respect_bonus'];

                $this->economy->addCash($character, $cash);
                $this->level->addXp($character, $xpGain);
                $character->addRespect($respectGain);
                $character->save();

                HalloweenService::record($character, 'theft', 1);
                HalloweenService::record($character, 'cash_earned', $cash);

                $dropMsg = RandomDropService::roll($character);
                $successMsg = "Vingervlug toegeslagen met '{$tech['name']}'! Buit: €".number_format($cash, 0, ',', '.')." zwart geld, +{$xpGain} XP en +{$respectGain} Gezag (1 bivakmuts verbruikt).";
                if ($dropMsg) {
                    $successMsg .= ' '.$dropMsg;
                }

                return back()->with('success', $successMsg);
            }

            if ($action['jail'] && rand(1, 100) <= 60) {
                $character->masks = max(0, ($character->masks ?? 1) - 1);
                $character->save();

                $this->cooldownService->clearCooldown($character, 'pickpocket');
                $jailSeconds = random_int(85, 140);
                $this->prison->jailSeconds($character, $jailSeconds, "Opgepakt tijdens vingervlugheid ({$tech['name']}).");

                return redirect()->route('prison')->with('error', "Greep mislukt! Je zit {$jailSeconds} sec vast in isolatie nadat de zaalwacht je bivakmuts afpakte.");
            }

            $dmg = rand(2, 6);
            $character->health = max(1, $character->health - $dmg);
            $character->save();

            return back()->with('error', "⚠️ Het doelwit merkte je '{$tech['name']}' op en sloeg van zich af (-{$dmg} HP). Je wist weg te glippen met behoud van je bivakmuts!");
        });
    }

    protected function getAlertness(int $characterId): int
    {
        return max(0, min(100, (int) session("pickpocket_alertness_{$characterId}", 20)));
    }

    protected function calculateAlertnessModifier(int $alertness, bool $isScouted): int
    {
        $mod = 0;
        if ($alertness <= 25) {
            $mod += 6;
        } elseif ($alertness >= 70) {
            $mod -= 8;
        }

        if ($isScouted) {
            $mod += 5;
        }

        return $mod;
    }
}
