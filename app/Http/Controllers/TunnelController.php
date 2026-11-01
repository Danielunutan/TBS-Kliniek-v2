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

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        /** @var EscapeTunnel $tunnel */
        $tunnel = EscapeTunnel::firstOrCreate(['character_id' => $character->id]);

        if ($tunnel->is_completed) {
            return back()->with('info', 'Je hebt de legendarische ontsnappingstunnel al voltooid! Reset je tunnel om opnieuw te kunnen graven.');
        }

        if ($tunnel->sand_in_socks >= 100) {
            return back()->with('error', 'Je zakken en kussenslopen puilen uit van het zand! Je kunt pas verder graven als je eerst het zand uitstrooit op de luchtplaats.');
        }

        if ($this->cooldownService->isOnCooldown($character, 'tunnel_dig')) {
            $remaining = ceil($this->cooldownService->getRemainingSeconds($character, 'tunnel_dig') / 60).' minuten';

            return back()->with('error', "Even op adem komen van het zware graafwerk. Wacht nog {$remaining}.");
        }

        $energyCost = 20;
        if (! $this->energy->consume($character, $energyCost)) {
            return back()->with('error', "Je bent bekaf. Je hebt minimaal {$energyCost} energie nodig om te graven!");
        }

        // Voortgang boeken
        $progressGain = mt_rand(2, 5);
        $tunnel->progress_pct = min(100, $tunnel->progress_pct + $progressGain);

        $sandGain = mt_rand(15, 25);
        $tunnel->sand_in_socks = min(100, $tunnel->sand_in_socks + $sandGain);

        // Check lepel breuk (8% kans)
        $spoonBroke = mt_rand(1, 100) <= 8;
        if ($spoonBroke) {
            $tunnel->increment('spoons_used');
        }

        // Check of stage stijgt
        $currentStageTarget = EscapeTunnel::STAGES[$tunnel->stage]['target_progress'] ?? 100;
        $stageAdvanced = false;
        if ($tunnel->progress_pct >= $currentStageTarget && $tunnel->stage < 5) {
            $tunnel->stage++;
            $stageAdvanced = true;
        }

        $xp = mt_rand(25, 45);
        $this->level->addXp($character, $xp);
        $tunnel->save();

        $this->cooldownService->setCooldown($character, 'tunnel_dig');

        $msg = "🥄 Je hebt voorzichtig lepel voor lepel zand afgekrabt (+{$progressGain}% voortgang, +{$xp} XP)!";
        if ($spoonBroke) {
            $msg .= ' 💥 KRAK! Je eetlepel boog finaal doormidden op een stuk betonijzer. Je hebt een nieuwe reservelepel gepakt.';
        }
        if ($stageAdvanced) {
            $newStageName = EscapeTunnel::STAGES[$tunnel->stage]['name'];
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

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        /** @var EscapeTunnel $tunnel */
        $tunnel = EscapeTunnel::firstOrCreate(['character_id' => $character->id]);

        if ($tunnel->sand_in_socks <= 0) {
            return back()->with('error', 'Je hebt momenteel geen zand in je zakken zitten.');
        }

        if ($this->cooldownService->isOnCooldown($character, 'tunnel_dump_sand')) {
            $remaining = ceil($this->cooldownService->getRemainingSeconds($character, 'tunnel_dump_sand') / 60).' minuten';

            return back()->with('error', "De zaalwacht houdt de grond scherp in de gaten. Wacht nog {$remaining}.");
        }

        $dumpedAmount = $tunnel->sand_in_socks;
        $tunnel->sand_in_socks = 0;
        $tunnel->save();

        $this->level->addXp($character, 15);
        $this->cooldownService->setCooldown($character, 'tunnel_dump_sand');

        return back()->with('success', "🤫 LUCHTUURTJE TRUC GESLAAGD! Terwijl je nonchalant een rondje liep over de luchtplaats, trok je aan het touwtje in je broekzak. {$dumpedAmount}% zand werd ongemerkt uitgestrooid tussen het grind (+15 XP)!");
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

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $cashReward = 25000;
        $xpReward = 500;
        $respectReward = 15;
        $starsReward = 25;

        $status = DB::transaction(function () use ($character, $cashReward, $xpReward, $respectReward, $starsReward): string {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
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
            $tunnel->fugitive_until = now()->addHours(3);
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

        if (str_starts_with($status, 'incomplete:')) {
            $pct = substr($status, strlen('incomplete:'));

            return back()->with('error', 'De tunnel is nog niet klaar! Je bent pas op '.$pct.'%.');
        }

        if ($status === 'already_completed') {
            return back()->with('info', 'Je hebt de ontsnapping al eens voltooid.');
        }

        return back()->with('success', '🏆 HET GROTE UITBRAAKCOMPLOT IS VOLTOOID! Midden in de nacht kroop je door de lepel-gegraven tunnel onder de buitenmuur door! Beloning: €'.number_format($cashReward, 0, ',', '.')." buit, +{$xpReward} XP, +{$respectReward} Respect en 25 Sterren ⭐! Je bent nu 3 UUR VOORTVLUCHTIG buiten de hekken met toegang tot exclusieve uitbraak-heists!");
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

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        /** @var EscapeTunnel $tunnel */
        $tunnel = EscapeTunnel::firstOrCreate(['character_id' => $character->id]);

        if (! $tunnel->is_completed) {
            return back()->with('error', 'Je kunt de tunnel pas resetten zodra je de huidige uitbraak hebt voltooid!');
        }

        $tunnel->stage = 1;
        $tunnel->progress_pct = 0;
        $tunnel->sand_in_socks = 0;
        $tunnel->is_completed = false;
        $tunnel->fugitive_until = null;
        $tunnel->save();

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

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        /** @var EscapeTunnel $tunnel */
        $tunnel = EscapeTunnel::firstOrCreate(['character_id' => $character->id]);

        if (! $tunnel->isFugitive()) {
            return back()->with('error', 'Je bent momenteel niet voortvluchtig! Voltooi eerst een uitbraak via de tunnel om buiten te komen.');
        }

        $validated = $request->validate([
            'heist_id' => ['required', 'string', 'in:'.implode(',', array_keys(EscapeTunnel::ESCAPE_HEISTS))],
        ]);

        $heist = EscapeTunnel::ESCAPE_HEISTS[$validated['heist_id']];
        $cooldownKey = 'tunnel_heist_'.$heist['id'];

        if ($this->cooldownService->isOnCooldown($character, $cooldownKey)) {
            $remaining = ceil($this->cooldownService->getRemainingSeconds($character, $cooldownKey) / 60).' minuten';

            return back()->with('error', "De politie surveilleert nog in dit gebied! Wacht nog {$remaining}.");
        }

        if (! $this->energy->consume($character, $heist['energy'])) {
            return back()->with('error', "Je bent uitgeput! Je hebt minimaal {$heist['energy']} energie nodig voor deze uitbraak-heist.");
        }

        $this->cooldownService->setCooldown($character, $cooldownKey, $heist['cooldown']);

        $cash = mt_rand($heist['min_cash'], $heist['max_cash']);
        $xp = $heist['xp'];
        $respect = $heist['respect'];
        $stars = 0;
        if (($heist['max_stars'] ?? 0) > 0 && mt_rand(1, 100) <= 40) {
            $stars = mt_rand($heist['min_stars'] ?? 1, $heist['max_stars']);
        }

        $this->economy->addCash($character, $cash);
        $this->level->addXp($character, $xp);
        $character->addRespect($respect);
        if ($stars > 0) {
            $character->stars = ($character->stars ?? 0) + $stars;
        }

        $extraLoot = [];
        if (mt_rand(1, 100) <= 25) {
            $character->unopened_bags = ($character->unopened_bags ?? 0) + 1;
            $extraLoot[] = '1x Smokkelkoffer 🧳';
        }

        $character->save();

        $lootSummary = '€'.number_format($cash, 0, ',', '.')." contanten, +{$xp} XP, +{$respect} Respect";
        if ($stars > 0) {
            $lootSummary .= ", +{$stars} Sterren ⭐";
        }
        if (! empty($extraLoot)) {
            $lootSummary .= ', '.implode(', ', $extraLoot);
        }

        return back()->with('success', "🚨 OVERVAL GESLAAGD! Je sloeg toe bij {$heist['name']} en ontkwam aan de bewaking! Buit: {$lootSummary}!");
    }
}
