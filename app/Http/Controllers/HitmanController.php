<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameNotification;
use App\Services\ChatBotService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use App\Services\PrisonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HitmanController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected EnergyService $energy,
        protected LevelService $level,
        protected PrisonService $prison,
    ) {}

    public function index(): View
    {
        $character = Auth::user()->character;
        $this->energy->refresh($character);

        // Potential targets: active players excluding self
        $targets = Character::where('id', '!=', $character->id)
            ->where('is_in_prison', false)
            ->orderByDesc('level')
            ->limit(15)
            ->get();

        $contracts = [
            ['id' => 1, 'name' => 'Waarschuwingsslag',   'desc' => 'Stuur een dreigend pakket. Geen dodelijk letsel, wel schade.', 'cost' => 800,  'energy' => 15, 'damage' => 20, 'rate' => 75, 'xp' => 60],
            ['id' => 2, 'name' => 'Klap bij de kantine', 'desc' => 'Een stevige aframmeling na het avondeten.',                    'cost' => 2000, 'energy' => 20, 'damage' => 35, 'rate' => 65, 'xp' => 110],
            ['id' => 3, 'name' => 'Gewelddadige overval', 'desc' => 'Bewusteloos slaan en beroven op de luchtplaats.',             'cost' => 5000, 'energy' => 25, 'damage' => 55, 'rate' => 50, 'xp' => 200],
        ];

        return view('game.hitman.index', compact('character', 'targets', 'contracts'));
    }

    /**
     * Search all patient targets across the clinic via live AJAX search.
     */
    public function search(Request $request): JsonResponse
    {
        $character = Auth::user()->character;
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 1) {
            return response()->json(['targets' => []]);
        }

        $targets = Character::where('id', '!=', $character->id)
            ->where(function ($query) use ($q) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($q).'%'])
                    ->orWhere('name', 'LIKE', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn (Character $target) => [
                'id' => (string) $target->id,
                'name' => $target->name,
                'level' => $target->level,
                'respect' => $target->respect,
                'health' => $target->health,
                'cash' => number_format($target->cash, 0, ',', '.'),
                'avatar' => $target->avatar_url,
                'isOnline' => $target->isOnline(),
                'inPrison' => $target->isImprisoned(),
                'inComa' => $target->isComatose(),
                'isProtected' => $target->isProtected(),
            ]);

        return response()->json(['targets' => $targets]);
    }

    public function contract(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je geen liquidatieopdrachten uitvoeren!');
        }

        if (! $request->filled('target_id') && $request->filled('target_name')) {
            $nameInput = trim((string) $request->input('target_name'));
            $found = Character::whereRaw('LOWER(name) = ?', [strtolower($nameInput)])
                ->orWhere('name', 'LIKE', $nameInput)
                ->first();
            if ($found) {
                $request->merge(['target_id' => $found->id]);
            }
        }

        $validated = $request->validate([
            'target_id' => ['required', 'exists:characters,id'],
            'contract_id' => ['required', 'integer', 'in:1,2,3'],
        ]);

        $config = [
            1 => ['cost' => 800,  'energy' => 15, 'damage' => 20, 'rate' => 75, 'xp' => 60,  'label' => 'Waarschuwingsslag'],
            2 => ['cost' => 2000, 'energy' => 20, 'damage' => 35, 'rate' => 65, 'xp' => 110, 'label' => 'Klap bij de kantine'],
            3 => ['cost' => 5000, 'energy' => 25, 'damage' => 55, 'rate' => 50, 'xp' => 200, 'label' => 'Gewelddadige overval'],
        ];

        $contract = $config[$validated['contract_id']];
        $target = Character::findOrFail($validated['target_id']);

        if ($target->id === $character->id) {
            return back()->with('error', 'Je kunt geen contract op jezelf plaatsen!');
        }

        if ($character->cash < $contract['cost']) {
            return back()->with('error', 'Dit contract kost €'.number_format($contract['cost'], 0, ',', '.').'. Je hebt onvoldoende contant geld.');
        }

        if (! $this->energy->consume($character, $contract['energy'])) {
            return back()->with('error', "Je hebt minimaal {$contract['energy']} energie nodig voor dit contract!");
        }

        $character->cash -= $contract['cost'];
        $character->save();

        $roll = rand(1, 100);
        if ($roll <= $contract['rate']) {
            $target->health = max(0, $target->health - $contract['damage']);
            $target->save();

            $this->level->addXp($character, $contract['xp']);
            $character->respect += 3;
            $character->save();

            GameNotification::create([
                'character_id' => $target->id,
                'type' => 'attack',
                'icon' => 'fa-skull-crossbones',
                'title' => 'Je bent aangevallen!',
                'message' => "Een onbekende heeft een liquidatieopdracht op je uitgevoerd ({$contract['label']}). Je verloor {$contract['damage']} HP.",
            ]);

            return back()->with('success', "Contract succesvol uitgevoerd! {$target->name} verliest {$contract['damage']} HP. +{$contract['xp']} XP, +3 Respect.");
        }

        if (rand(1, 100) <= 40) {
            $this->prison->jail($character, 15, 'Betrapt bij het uitvoeren van een liquidatieopdracht.');

            return redirect()->route('prison')->with('error', 'Contract mislukt! Jij bent opgepakt door de bewaking. 15 minuten isoleercel.');
        }

        return back()->with('error', 'Contract mislukt! Je huurmoordenaar botste op de bewaking en is gevlucht. Je verloor het geld.');
    }

    /**
     * Direct Liquidation / Shooting with Bullets & Backfire.
     */
    public function shoot(Request $request): RedirectResponse
    {
        /** @var Character $attacker */
        $attacker = Auth::user()->character;
        $this->energy->refresh($attacker);

        if ($attacker->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je niemand liquideren!');
        }

        if ($attacker->isComatose()) {
            $mins = ceil($attacker->getComaRemainingSeconds() / 60);

            return back()->with('error', "⚠️ Je ligt in Intensive Care Coma (nog {$mins} min)! Ontwaak direct via een sponsor advertentie.");
        }

        if (! $request->filled('target_id') && $request->filled('target_name')) {
            $nameInput = trim((string) $request->input('target_name'));
            $found = Character::whereRaw('LOWER(name) = ?', [strtolower($nameInput)])
                ->orWhere('name', 'LIKE', $nameInput)
                ->first();
            if ($found) {
                $request->merge(['target_id' => $found->id]);
            }
        }

        $validated = $request->validate([
            'target_id' => ['required', 'exists:characters,id'],
            'bullets' => ['required', 'integer', 'min:25', 'max:1000'],
        ]);

        $bulletCount = (int) $validated['bullets'];
        if (($attacker->bullets ?? 0) < $bulletCount) {
            return back()->with('error', "Je hebt niet genoeg kogels (je bezit {$attacker->bullets}, minimaal {$bulletCount} nodig). Bekijk een sponsor video voor +50 gratis kogels!");
        }

        if (! $this->energy->consume($attacker, 20)) {
            return back()->with('error', 'Je hebt minimaal 20 energie nodig om een liquidatie uit te voeren.');
        }

        $defender = Character::findOrFail($validated['target_id']);

        if ($defender->id === $attacker->id) {
            return back()->with('error', 'Je kunt niet op jezelf schieten!');
        }

        if ($defender->isImprisoned()) {
            return back()->with('error', 'Dit doelwit zit veilig in de isoleercel en kan niet worden geliquideerd.');
        }

        if ($defender->isComatose()) {
            return back()->with('error', "{$defender->name} ligt momenteel al in de Intensive Care Coma!");
        }

        if ($defender->isProtected()) {
            return back()->with('error', "{$defender->name} heeft tijdelijke nieuwkomer-bescherming.");
        }

        // Deduct bullets from attacker
        $attacker->bullets -= $bulletCount;
        $attacker->save();

        // Combat calculation
        $attackerPower = ($bulletCount * 1.5) + ($attacker->getEffectiveStrength() * 2) + rand(10, 30);
        $defenderDefense = ($defender->getEffectiveDefense() * 2) + rand(10, 30);

        $hasVest = $defender->hasVest();
        if ($hasVest) {
            $defenderDefense += 250; // Heavy bulletproof vest defense boost
        }

        // 1. BACKFIRE CHECK (Attacker shoots themselves / bullets ricochet)
        $backfireChance = $hasVest ? 45 : 18;
        if ($attackerPower < $defenderDefense && rand(1, 100) <= $backfireChance) {
            $attacker->coma_until = now()->addHours(6);
            $attacker->health = 1;
            $attacker->respect = max(0, $attacker->respect - 20);
            $attacker->deaths_count = ($attacker->deaths_count ?? 0) + 1;
            $attacker->save();

            GameNotification::create([
                'character_id' => $defender->id,
                'type' => 'shield',
                'icon' => 'fa-shield-halved',
                'title' => '🛡️ BACKFIRE AFGESLAGEN!',
                'message' => "{$attacker->name} vuurde {$bulletCount} kogels op je af, maar jouw pantser kaatste de kogels terug! {$attacker->name} ligt nu zelf 6 uur in Intensive Care Coma!",
            ]);

            return back()->with('error', "💥 BACKFIRE! De verdediging van {$defender->name} was te sterk en jouw kogels ketsten af op het pantser! Je schoot jezelf neer en ligt nu zélf 6 uur in Intensive Care Coma!");
        }

        // 2. LETHAL KILL CHECK (Target enters ICU Coma, 100% Cash Looted)
        if ($attackerPower >= $defenderDefense) {
            // Check if vest saves defender's life with partial armor damage
            if ($hasVest && rand(1, 100) <= 30) {
                $defender->vest_until = null; // vest destroyed
                $dmg = min(max(1, $defender->health - 5), rand(40, 70));
                $defender->health = max(5, $defender->health - $dmg);
                $defender->save();

                GameNotification::create([
                    'character_id' => $defender->id,
                    'type' => 'attack',
                    'icon' => 'fa-shield-halved',
                    'title' => 'Kogelwerend Vest heeft je leven gered!',
                    'message' => "{$attacker->name} vuurde {$bulletCount} kogels op je af! Je vest ving de dodelijke klap op en is vernietigd (-{$dmg} HP).",
                ]);

                return back()->with('info', "🎯 Raak, maar {$defender->name} droeg een Kogelwerend Vest! Het vest ving de klap op en is vernietigd (-{$dmg} HP). Doelwit overleefde ternauwernood!");
            }

            // FULL LETHAL HIT -> Intensive Care Coma & 100% Cash Plunder!
            $stolenCash = (int) $defender->cash;
            if ($stolenCash > 0) {
                $this->economy->deductCash($defender, $stolenCash);
                $this->economy->addCash($attacker, $stolenCash);
            }

            $lostRespect = max(10, (int) round($defender->respect * 0.10));
            $defender->respect = max(0, $defender->respect - $lostRespect);
            $defender->health = 0;
            $defender->coma_until = now()->addHours(6);
            $defender->deaths_count = ($defender->deaths_count ?? 0) + 1;
            $defender->save();

            $attacker->kills_count = ($attacker->kills_count ?? 0) + 1;
            $attacker->respect += 50;
            $this->level->addXp($attacker, 350);
            $attacker->save();

            // Notify victim
            GameNotification::create([
                'character_id' => $defender->id,
                'type' => 'skull',
                'icon' => 'fa-skull',
                'title' => '🚨 JE BENT NEERGESCHOTEN!',
                'message' => "{$attacker->name} heeft je neergeschoten met {$bulletCount} kogels! Je ligt 6 uur in Intensive Care Coma en al je contant geld (€".number_format($stolenCash, 0, ',', '.').') is geplunderd!',
            ]);

            // Broadcast to chat bot
            try {
                $cashTxt = $stolenCash > 0 ? ' en heeft €'.number_format($stolenCash, 0, ',', '.').' contant geplunderd' : '';
                app(ChatBotService::class)->broadcast("🚨 NOODGEVAL OP DE AFDELING: {$attacker->name} heeft {$defender->name} zojuist neergeschoten{$cashTxt}! Slachtoffer ligt in Intensive Care Coma!");
            } catch (\Throwable $e) {
            }

            $lootMsg = $stolenCash > 0 ? ' Buit: €'.number_format($stolenCash, 0, ',', '.').' contant geld geplunderd!' : '';

            return back()->with('success', "🎯 DODELIJKE VOLTREFFER! {$defender->name} is neergeschoten en ligt 6 uur in Intensive Care Coma!{$lootMsg} +50 Respect, +350 XP!");
        }

        // 3. WOUNDED / ESCAPED
        $dmg = rand(20, 45);
        $defender->health = max(1, $defender->health - $dmg);
        $defender->save();

        GameNotification::create([
            'character_id' => $defender->id,
            'type' => 'attack',
            'icon' => 'fa-gun',
            'title' => 'Onder vuur genomen!',
            'message' => "{$attacker->name} heeft {$bulletCount} kogels op je afgevuurd! Je raakte gewond (-{$dmg} HP) maar wist te ontkomen.",
        ]);

        return back()->with('error', "⚠️ Kogels afgevuurd ({$bulletCount} stuks)! Enkele kogels raakten {$defender->name} (-{$dmg} HP), maar het doelwit wist gewond te ontkomen. Schaf meer kogels aan via de Sponsorkamer!");
    }
}
