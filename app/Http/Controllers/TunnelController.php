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

        return view('game.tunnel.index', compact(
            'character',
            'tunnel',
            'digCooldown',
            'dumpCooldown',
            'stages',
            'currentStageInfo'
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
            return back()->with('info', 'Je hebt de legendarische ontsnappingstunnel al voltooid! De bewaking heeft het gat inmiddels dichtgestort met snelbeton.');
        }

        if ($tunnel->sand_in_socks >= 100) {
            return back()->with('error', 'Je zakken en kussenslopen puilen uit van het zand! Je kunt pas verder graven als je eerst het zand uitstrooit op de luchtplaats.');
        }

        if ($this->cooldownService->isOnCooldown($character, 'tunnel_dig')) {
            $remaining = ceil($this->cooldownService->getRemainingSeconds($character, 'tunnel_dig') / 60).' minuten';

            return back()->with('error', "Even op adem komen van het zware graafwerk. Wacht nog {$remaining}.");
        }

        $energyCost = 20;
        if ($character->energy < $energyCost) {
            return back()->with('error', "Je bent bekaf. Je hebt minimaal {$energyCost} energie nodig om te graven!");
        }

        $character->energy -= $energyCost;
        $character->save();

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

        return back()->with('success', '🏆 HET GROTE UITBRAAKCOMPLOT IS VOLTOOID! Midden in de nacht kroop je door de lepel-gegraven tunnel onder de buitenmuur door! Beloning: €'.number_format($cashReward, 0, ',', '.')." buit, +{$xpReward} XP, +{$respectReward} Respect en 25 Sterren ⭐!");
    }
}
