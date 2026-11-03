<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\EscapeTunnel;
use App\Models\User;
use App\Services\CooldownService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use App\Services\PrisonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TunnelController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected EnergyService $energy,
        protected LevelService $level,
        protected CooldownService $cooldownService,
        protected PrisonService $prisonService,
    ) {}

    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;
        $this->energy->refresh($character);

        $tunnel = EscapeTunnel::firstOrCreate(
            ['character_id' => $character->id],
            [
                'stage' => 1,
                'progress_pct' => 0,
                'sand_in_socks' => 0,
                'spoons_used' => 1,
                'wood_planks' => 0,
                'is_completed' => false,
            ]
        );

        $digCooldown = $this->cooldownService->getRemainingSeconds($character, 'tunnel_dig');
        $dumpCooldown = $this->cooldownService->getRemainingSeconds($character, 'tunnel_dump_sand');

        $stages = EscapeTunnel::STAGES;
        $currentStageInfo = $stages[$tunnel->stage] ?? $stages[1];

        $heists = EscapeTunnel::ESCAPE_HEISTS;
        $heistCooldowns = [];
        foreach ($heists as $id => $h) {
            $heistCooldowns[$id] = $this->cooldownService->getRemainingSeconds($character, 'tunnel_heist_'.$id);
        }

        return view('game.tunnel.index', compact(
            'character',
            'tunnel',
            'digCooldown',
            'dumpCooldown',
            'stages',
            'currentStageInfo',
            'heists',
            'heistCooldowns'
        ));
    }

    public function dig(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;
        $this->energy->refresh($character);

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'In de isoleercel heb je geen toegang tot je eigen cel en de geheime tunnel!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $energyCost = 20;

        $result = DB::transaction(function () use ($character, $energyCost): array {
            /** @var Character|null $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $lockedCharacter) {
                return ['status' => 'error', 'message' => 'Patiëntdossier niet gevonden.'];
            }

            if ($lockedCharacter->isImprisoned()) {
                return ['status' => 'imprisoned'];
            }

            if ($lockedCharacter->isComatose() || $lockedCharacter->needsReviveSerum()) {
                return ['status' => 'comatose'];
            }

            /** @var EscapeTunnel $tunnel */
            $tunnel = EscapeTunnel::where('character_id', $lockedCharacter->id)->lockForUpdate()->first()
                ?? EscapeTunnel::create(['character_id' => $lockedCharacter->id]);

            if ($tunnel->is_completed) {
                return ['status' => 'already_completed'];
            }

            if ($tunnel->sand_in_socks >= 100) {
                return ['status' => 'socks_full'];
            }

            if ($this->cooldownService->isOnCooldown($lockedCharacter, 'tunnel_dig')) {
                $rem = ceil($this->cooldownService->getRemainingSeconds($lockedCharacter, 'tunnel_dig') / 60).' minuten';

                return ['status' => 'cooldown', 'remaining' => $rem];
            }

            if ((int) $lockedCharacter->energy < $energyCost) {
                return ['status' => 'insufficient_energy'];
            }

            $lockedCharacter->energy -= $energyCost;
            $this->cooldownService->setCooldown($lockedCharacter, 'tunnel_dig');

            // Voortgang boeken
            $progressGain = mt_rand(2, 5);
            $tunnel->progress_pct = min(100, $tunnel->progress_pct + $progressGain);

            $sandGain = mt_rand(15, 25);
            $tunnel->sand_in_socks = min(100, $tunnel->sand_in_socks + $sandGain);

            // Check lepel breuk (8% kans)
            $spoonBroke = mt_rand(1, 100) <= 8;
            if ($spoonBroke) {
                $tunnel->spoons_used = ($tunnel->spoons_used ?? 1) + 1;
            }

            // Check of stage stijgt
            $currentStageTarget = EscapeTunnel::STAGES[$tunnel->stage]['target_progress'] ?? 100;
            $stageAdvanced = false;
            if ($tunnel->progress_pct >= $currentStageTarget && $tunnel->stage < 5) {
                $tunnel->stage++;
                $stageAdvanced = true;
            }

            $xp = mt_rand(25, 45);
            $this->level->addXp($lockedCharacter, $xp);
            $tunnel->save();
            $lockedCharacter->save();

            return [
                'status' => 'success',
                'progressGain' => $progressGain,
                'xp' => $xp,
                'spoonBroke' => $spoonBroke,
                'stageAdvanced' => $stageAdvanced,
                'newStage' => $tunnel->stage,
            ];
        });

        if ($result['status'] === 'imprisoned') {
            return redirect()->route('prison')->with('error', 'In de isoleercel heb je geen toegang tot je eigen cel en de geheime tunnel!');
        }

        if ($result['status'] === 'comatose') {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($result['status'] === 'already_completed') {
            return back()->with('info', 'Je hebt de legendarische ontsnappingstunnel al voltooid! Reset je tunnel om opnieuw te kunnen graven.');
        }

        if ($result['status'] === 'socks_full') {
            return back()->with('error', 'Je zakken en kussenslopen puilen uit van het zand! Je kunt pas verder graven als je eerst het zand uitstrooit op de luchtplaats.');
        }

        if ($result['status'] === 'cooldown') {
            return back()->with('error', "Even op adem komen van het zware graafwerk. Wacht nog {$result['remaining']}.");
        }

        if ($result['status'] === 'insufficient_energy') {
            return back()->with('error', "Je bent bekaf. Je hebt minimaal {$energyCost} energie nodig om te graven!");
        }

        if ($result['status'] === 'error') {
            return back()->with('error', $result['message']);
        }

        $msg = "🥄 Je hebt voorzichtig lepel voor lepel zand afgekrabt (+{$result['progressGain']}% voortgang, +{$result['xp']} XP)!";
        if (! empty($result['spoonBroke'])) {
            $msg .= ' 💥 KRAK! Je eetlepel boog finaal doormidden op een stuk betonijzer. Je hebt een nieuwe reservelepel gepakt.';
        }
        if (! empty($result['stageAdvanced'])) {
            $newStageName = EscapeTunnel::STAGES[$result['newStage']]['name'];
            $msg .= " 🚀 NIEUWE ETAPPE BEREIKT: '{$newStageName}'!";
        }

        return back()->with('success', $msg);
    }

    public function dumpSand(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;
        $this->energy->refresh($character);

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'In de isoleercel kom je niet op de luchtplaats!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $result = DB::transaction(function () use ($character): array {
            /** @var Character|null $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $lockedCharacter) {
                return ['status' => 'error', 'message' => 'Patiëntdossier niet gevonden.'];
            }

            if ($lockedCharacter->isImprisoned()) {
                return ['status' => 'imprisoned'];
            }

            if ($lockedCharacter->isComatose() || $lockedCharacter->needsReviveSerum()) {
                return ['status' => 'comatose'];
            }

            /** @var EscapeTunnel $tunnel */
            $tunnel = EscapeTunnel::where('character_id', $lockedCharacter->id)->lockForUpdate()->first()
                ?? EscapeTunnel::create(['character_id' => $lockedCharacter->id]);

            if ($tunnel->sand_in_socks <= 0) {
                return ['status' => 'no_sand'];
            }

            if ($this->cooldownService->isOnCooldown($lockedCharacter, 'tunnel_dump_sand')) {
                $rem = ceil($this->cooldownService->getRemainingSeconds($lockedCharacter, 'tunnel_dump_sand') / 60).' minuten';

                return ['status' => 'cooldown', 'remaining' => $rem];
            }

            $dumpedAmount = $tunnel->sand_in_socks;
            $tunnel->sand_in_socks = 0;
            $tunnel->save();

            $this->cooldownService->setCooldown($lockedCharacter, 'tunnel_dump_sand');
            $this->level->addXp($lockedCharacter, 15);
            $lockedCharacter->save();

            return [
                'status' => 'success',
                'dumped' => $dumpedAmount,
            ];
        });

        if ($result['status'] === 'imprisoned') {
            return redirect()->route('prison')->with('error', 'In de isoleercel kom je niet op de luchtplaats!');
        }

        if ($result['status'] === 'comatose') {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($result['status'] === 'no_sand') {
            return back()->with('error', 'Je hebt momenteel geen zand in je zakken zitten.');
        }

        if ($result['status'] === 'cooldown') {
            return back()->with('error', "De zaalwacht houdt de grond scherp in de gaten. Wacht nog {$result['remaining']}.");
        }

        if ($result['status'] === 'error') {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', "🤫 LUCHTUURTJE TRUC GESLAAGD! Terwijl je nonchalant een rondje liep over de luchtplaats, trok je aan het touwtje in je broekzak. {$result['dumped']}% zand werd ongemerkt uitgestrooid tussen het grind (+15 XP)!");
    }

    public function escape(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;

        // Digging needs free movement, so claiming the escape must too.
        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'In de isoleercel heb je geen toegang tot je eigen cel en de geheime tunnel!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $cashReward = 25000;
        $xpReward = 500;
        $respectReward = 15;
        $starsReward = 25;

        $status = DB::transaction(function () use ($character, $cashReward, $xpReward, $respectReward, $starsReward): string {
            /** @var Character|null $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $lockedCharacter) {
                return 'not_found';
            }

            if ($lockedCharacter->isImprisoned()) {
                return 'imprisoned';
            }

            if ($lockedCharacter->isComatose() || $lockedCharacter->needsReviveSerum()) {
                return 'comatose';
            }

            /** @var EscapeTunnel $tunnel */
            $tunnel = EscapeTunnel::where('character_id', $lockedCharacter->id)->lockForUpdate()->first()
                ?? EscapeTunnel::create(['character_id' => $lockedCharacter->id]);

            if ($tunnel->progress_pct < 100) {
                return 'incomplete:'.$tunnel->progress_pct;
            }

            if ($tunnel->is_completed) {
                return 'already_completed';
            }

            $tunnel->is_completed = true;
            $tunnel->escaped_at = now();
            $tunnel->fugitive_until = now()->addMinutes(EscapeTunnel::FUGITIVE_DURATION_MINUTES);
            $tunnel->times_escaped = ($tunnel->times_escaped ?? 0) + 1;
            $tunnel->save();

            $this->economy->addCash($lockedCharacter, $cashReward);
            $this->level->addXp($lockedCharacter, $xpReward);
            $lockedCharacter->addRespect($respectReward);
            $lockedCharacter->stars = ($lockedCharacter->stars ?? 0) + $starsReward;
            $lockedCharacter->prison_escapes = ($lockedCharacter->prison_escapes ?? 0) + 1;
            $lockedCharacter->save();

            return 'ok';
        });

        if ($status === 'imprisoned') {
            return redirect()->route('prison')->with('error', 'In de isoleercel heb je geen toegang tot je eigen cel en de geheime tunnel!');
        }

        if ($status === 'comatose') {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if (str_starts_with($status, 'incomplete:')) {
            $pct = substr($status, strlen('incomplete:'));

            return back()->with('error', 'De tunnel is nog niet klaar! Je bent pas op '.$pct.'%.');
        }

        if ($status === 'already_completed') {
            return back()->with('info', 'Je hebt de ontsnapping al eens voltooid.');
        }

        return back()->with('success', '🏆 HET GROTE UITBRAAKCOMPLOT IS VOLTOOID! Midden in de nacht kroop je door de lepel-gegraven tunnel onder de buitenmuur door! Beloning: €'.number_format($cashReward, 0, ',', '.')." buit, +{$xpReward} XP, +{$respectReward} Respect en 25 Sterren ⭐! Je bent nu ".EscapeTunnel::FUGITIVE_DURATION_MINUTES.' MINUTEN VOORTVLUCHTIG buiten de hekken met toegang tot exclusieve uitbraak-heists!');
    }

    public function resetTunnel(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'In de isoleercel kun je geen nieuwe uitbraaktunnel plannen!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $result = DB::transaction(function () use ($character): array {
            /** @var Character|null $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $lockedCharacter) {
                return ['status' => 'error', 'message' => 'Patiëntdossier niet gevonden.'];
            }

            if ($lockedCharacter->isImprisoned()) {
                return ['status' => 'imprisoned'];
            }

            if ($lockedCharacter->isComatose() || $lockedCharacter->needsReviveSerum()) {
                return ['status' => 'comatose'];
            }

            /** @var EscapeTunnel $tunnel */
            $tunnel = EscapeTunnel::where('character_id', $lockedCharacter->id)->lockForUpdate()->first()
                ?? EscapeTunnel::create(['character_id' => $lockedCharacter->id]);

            if (! $tunnel->is_completed) {
                return ['status' => 'not_completed'];
            }

            $tunnel->stage = 1;
            $tunnel->progress_pct = 0;
            $tunnel->sand_in_socks = 0;
            $tunnel->is_completed = false;
            $tunnel->fugitive_until = null;
            $tunnel->save();

            return ['status' => 'success'];
        });

        if ($result['status'] === 'imprisoned') {
            return redirect()->route('prison')->with('error', 'In de isoleercel kun je geen nieuwe uitbraaktunnel plannen!');
        }

        if ($result['status'] === 'comatose') {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($result['status'] === 'not_completed') {
            return back()->with('error', 'Je kunt de tunnel pas resetten zodra je de huidige uitbraak hebt voltooid!');
        }

        return redirect()->route('tunnel.index')->with('success', '⛏️ NIEUWE UITBRAAKTUNNEL GESTART! Je bent stilletjes teruggekeerd naar je cel en hebt een nieuwe poster opgehangen. Je kunt nu weer vanaf etappe 1 beginnen met graven!');
    }

    public function doHeist(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;
        $this->energy->refresh($character);

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'In de isoleercel heb je geen toegang tot de buitenwereld!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $validated = $request->validate([
            'heist_id' => ['required', 'string', 'in:'.implode(',', array_keys(EscapeTunnel::ESCAPE_HEISTS))],
        ]);

        $heist = EscapeTunnel::ESCAPE_HEISTS[$validated['heist_id']];
        $cooldownKey = 'tunnel_heist_'.$heist['id'];

        $outcome = DB::transaction(function () use ($character, $heist, $cooldownKey): array {
            /** @var Character|null $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $lockedCharacter) {
                return ['status' => 'error', 'message' => 'Patiëntdossier niet gevonden.'];
            }

            if ($lockedCharacter->isImprisoned()) {
                return ['status' => 'imprisoned'];
            }

            if ($lockedCharacter->isComatose() || $lockedCharacter->needsReviveSerum()) {
                return ['status' => 'comatose'];
            }

            /** @var EscapeTunnel $tunnel */
            $tunnel = EscapeTunnel::where('character_id', $lockedCharacter->id)->lockForUpdate()->first()
                ?? EscapeTunnel::create(['character_id' => $lockedCharacter->id]);

            // Check if fugitive status has expired (arrest on expiry)
            if ($tunnel->fugitive_until !== null && now()->gte($tunnel->fugitive_until)) {
                $tunnel->fugitive_until = null;
                $tunnel->save();
                $this->prisonService->jailSeconds($lockedCharacter, 120, 'Gerecaptureerd: Voortvluchtige status na tunneluitbraak is verstreken.');

                return ['status' => 'expired_arrest'];
            }

            if (! $tunnel->isFugitive()) {
                return ['status' => 'not_fugitive'];
            }

            if ($this->cooldownService->isOnCooldown($lockedCharacter, $cooldownKey)) {
                $rem = ceil($this->cooldownService->getRemainingSeconds($lockedCharacter, $cooldownKey) / 60).' minuten';

                return ['status' => 'cooldown', 'remaining' => $rem];
            }

            if ((int) $lockedCharacter->energy < $heist['energy']) {
                return ['status' => 'insufficient_energy', 'needed' => $heist['energy']];
            }

            $lockedCharacter->energy -= $heist['energy'];
            $this->cooldownService->setCooldown($lockedCharacter, $cooldownKey, $heist['cooldown']);

            // Chance calculation and arrest on failure
            $statBonus = (int) floor(($lockedCharacter->getEffectiveIntelligence() + $lockedCharacter->getEffectiveStamina()) / 15);
            $baseChance = $heist['base_chance'] ?? 100;
            $finalChance = min(100, max(20, $baseChance + $statBonus));
            $roll = random_int(1, 100);

            if ($roll > $finalChance) {
                // Heist failed -> Arrest on failure!
                $tunnel->fugitive_until = null;
                $tunnel->save();
                $failSecs = $heist['fail_jail_secs'] ?? 120;
                $this->prisonService->jailSeconds($lockedCharacter, $failSecs, "Onderschept en gearresteerd tijdens uitbraak-heist: {$heist['name']}.");
                $lockedCharacter->save();

                return [
                    'status' => 'failed_arrest',
                    'heist_name' => $heist['name'],
                    'jail_secs' => $failSecs,
                ];
            }

            $cash = mt_rand($heist['min_cash'], $heist['max_cash']);
            $xp = $heist['xp'];
            $respect = $heist['respect'];
            $stars = 0;
            if (($heist['max_stars'] ?? 0) > 0 && mt_rand(1, 100) <= 40) {
                $stars = mt_rand($heist['min_stars'] ?? 1, $heist['max_stars']);
            }

            $this->economy->addCash($lockedCharacter, $cash);
            $this->level->addXp($lockedCharacter, $xp);
            $lockedCharacter->addRespect($respect);
            if ($stars > 0) {
                $lockedCharacter->stars = ($lockedCharacter->stars ?? 0) + $stars;
            }

            $extraLoot = [];
            if (mt_rand(1, 100) <= 25) {
                $lockedCharacter->unopened_bags = ($lockedCharacter->unopened_bags ?? 0) + 1;
                $extraLoot[] = '1x Smokkelkoffer 🧳';
            }

            $lockedCharacter->save();

            return [
                'status' => 'success',
                'cash' => $cash,
                'xp' => $xp,
                'respect' => $respect,
                'stars' => $stars,
                'extraLoot' => $extraLoot,
                'heist_name' => $heist['name'],
            ];
        });

        if ($outcome['status'] === 'imprisoned') {
            return redirect()->route('prison')->with('error', 'In de isoleercel heb je geen toegang tot de buitenwereld!');
        }

        if ($outcome['status'] === 'comatose') {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($outcome['status'] === 'expired_arrest') {
            return redirect()->route('prison')->with('error', '🚨 VOORTVLUCHTIGE STATUS VERSTREKEN! De kliniekbewaking heeft je buiten de muren opgespoord en teruggestuurd naar de isoleercel!');
        }

        if ($outcome['status'] === 'not_fugitive') {
            return back()->with('error', 'Je bent momenteel niet voortvluchtig! Voltooi eerst een uitbraak via de tunnel om buiten te komen.');
        }

        if ($outcome['status'] === 'cooldown') {
            return back()->with('error', "De politie surveilleert nog in dit gebied! Wacht nog {$outcome['remaining']}.");
        }

        if ($outcome['status'] === 'insufficient_energy') {
            return back()->with('error', "Je bent uitgeput! Je hebt minimaal {$outcome['needed']} energie nodig voor deze uitbraak-heist.");
        }

        if ($outcome['status'] === 'failed_arrest') {
            return redirect()->route('prison')->with('error', "🚨 OVERVAL MISLUKT & ARRESTATIE! De politie sloeg toe bij {$outcome['heist_name']}! Je bent gearresteerd en afgevoerd naar de isoleercel ({$outcome['jail_secs']} sec).");
        }

        if ($outcome['status'] === 'error') {
            return back()->with('error', $outcome['message']);
        }

        $lootSummary = '€'.number_format($outcome['cash'], 0, ',', '.')." contanten, +{$outcome['xp']} XP, +{$outcome['respect']} Respect";
        if ($outcome['stars'] > 0) {
            $lootSummary .= ", +{$outcome['stars']} Sterren ⭐";
        }
        if (! empty($outcome['extraLoot'])) {
            $lootSummary .= ', '.implode(', ', $outcome['extraLoot']);
        }

        return back()->with('success', "🚨 OVERVAL GESLAAGD! Je sloeg toe bij {$outcome['heist_name']} en ontkwam aan de bewaking! Buit: {$lootSummary}!");
    }
}
