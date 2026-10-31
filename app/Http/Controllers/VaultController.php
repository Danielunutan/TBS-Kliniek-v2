<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\VaultCode;
use App\Services\ChatBotService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\HalloweenService;
use App\Services\LevelService;
use App\Services\PrisonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VaultController extends Controller
{
    /**
     * Milestone level rewards config.
     */
    public const LEVEL_REWARDS = [
        2 => ['tokens' => 2, 'cash' => 500, 'stars' => 0, 'name' => 'Kliniek Verkenner', 'desc' => '2 KluisMunten + €500 (Bank)'],
        3 => ['tokens' => 3, 'cash' => 1000, 'stars' => 0, 'name' => 'Kantine Handelaar', 'desc' => '3 KluisMunten + €1.000 (Bank)'],
        5 => ['tokens' => 5, 'cash' => 2500, 'stars' => 1, 'name' => 'Kluizenkraker', 'desc' => '5 KluisMunten + €2.500 (Bank) + 1 Ster ⭐'],
        7 => ['tokens' => 8, 'cash' => 5000, 'stars' => 1, 'name' => 'Gerespecteerd Inmate', 'desc' => '8 KluisMunten + €5.000 (Bank) + 1 Ster ⭐'],
        10 => ['tokens' => 15, 'cash' => 12500, 'stars' => 2, 'name' => 'Kliniek Veteraan', 'desc' => '15 KluisMunten + €12.500 (Bank) + 2 Sterren ⭐'],
        15 => ['tokens' => 25, 'cash' => 25000, 'stars' => 3, 'name' => 'Het Meesterbrein', 'desc' => '25 KluisMunten + €25.000 (Bank) + 3 Sterren ⭐'],
        20 => ['tokens' => 35, 'cash' => 50000, 'stars' => 4, 'name' => 'Onderwereld Baas', 'desc' => '35 KluisMunten + €50.000 (Bank) + 4 Sterren ⭐'],
        25 => ['tokens' => 50, 'cash' => 100000, 'stars' => 5, 'name' => 'Legendarische Crimineel', 'desc' => '50 KluisMunten + €100.000 (Bank) + 5 Sterren ⭐'],
        30 => ['tokens' => 75, 'cash' => 200000, 'stars' => 7, 'name' => 'Koning der TBS', 'desc' => '75 KluisMunten + €200.000 (Bank) + 7 Sterren ⭐'],
        40 => ['tokens' => 100, 'cash' => 400000, 'stars' => 10, 'name' => 'Onschendbare Maffioso', 'desc' => '100 KluisMunten + €400.000 (Bank) + 10 Sterren ⭐'],
        50 => ['tokens' => 150, 'cash' => 750000, 'stars' => 15, 'name' => 'TBS Keizer', 'desc' => '150 KluisMunten + €750.000 (Bank) + 15 Sterren ⭐'],
    ];

    /**
     * Universal Season 1 Starter Vault Codes claimable by every player once.
     */
    public const SEASON_CODES = [
        'SEIZOEN1' => [
            'name' => 'Seizoen 1 Kickstart Code',
            'cash' => 2500,
            'stars' => 1,
            'tokens' => 3,
            'masks' => 2,
            'respect' => 15,
        ],
        'HERSTART2026' => [
            'name' => 'Herstart Goud Code',
            'cash' => 5000,
            'stars' => 0,
            'tokens' => 5,
            'masks' => 3,
            'respect' => 10,
        ],
        'TBSKLINIEK1' => [
            'name' => 'TBS Kliniek Seizoenscode',
            'cash' => 10000,
            'stars' => 2,
            'tokens' => 5,
            'masks' => 5,
            'respect' => 25,
        ],
        // Backwards compatibility alias
        'SEIZOEN2' => [
            'name' => 'Seizoen 1 Kickstart Code',
            'cash' => 2500,
            'stars' => 1,
            'tokens' => 3,
            'masks' => 2,
            'respect' => 15,
        ],
        'TBSKLINIEK2' => [
            'name' => 'TBS Kliniek Seizoenscode',
            'cash' => 10000,
            'stars' => 2,
            'tokens' => 5,
            'masks' => 5,
            'respect' => 25,
        ],
    ];

    public function __construct(
        protected EconomyService $economy,
        protected EnergyService $energy,
        protected LevelService $level,
        protected PrisonService $prison,
    ) {}

    /**
     * Map an alias season code to the code that owns its claim key.
     */
    public static function canonicalSeasonCode(string $code): string
    {
        return match ($code) {
            'SEIZOEN2' => 'SEIZOEN1',
            'TBSKLINIEK2' => 'TBSKLINIEK1',
            default => $code,
        };
    }

    /**
     * Return the target number of the current vault round, and create one when the session has none yet.
     *
     * Without this, guess() and buyCode() each invented their own random target, so a bought code could be wrong.
     */
    private function vaultTarget(): int
    {
        if (! session()->has('vault_target')) {
            session(['vault_target' => rand(1, 10)]);
        }

        return (int) session('vault_target');
    }

    public function index(Request $request): View
    {
        $character = Auth::user()->character;
        $this->energy->refresh($character);

        // Auto-dispatch any unissued milestone codes via GameMessage
        $claimed = $character->claimed_level_rewards ?? [];
        $newlyIssued = false;

        foreach (self::LEVEL_REWARDS as $lvl => $rew) {
            if ($character->level >= $lvl && ! in_array($lvl, $claimed, true)) {
                $uniqueCode = VaultCode::generateUniqueCode('KLUIS-L'.$lvl);

                VaultCode::create([
                    'code' => $uniqueCode,
                    'reward_cash' => $rew['cash'],
                    'reward_stars' => $rew['stars'],
                    'reward_tokens' => $rew['tokens'],
                    'character_id' => $character->id,
                    'is_redeemed' => false,
                ]);

                app(ChatBotService::class)->sendVaultCode(
                    $character,
                    $uniqueCode,
                    "Level {$lvl} Mijlpaal: {$rew['name']}",
                    '€'.number_format($rew['cash'], 0, ',', '.')." bankgeld + {$rew['stars']} Sterren ⭐"
                );

                GameMessage::create([
                    'recipient_id' => $character->id,
                    'sender_name' => 'Directie Kluisbeheer',
                    'subject' => "🔐 Jouw Geheime Kluiscode voor Level {$lvl}!",
                    'content' => "Beste {$character->name},\n\nGefeliciteerd met het bereiken van Level {$lvl}! Beloningen hoef je in de TBS Kliniek niet meer handmatig op te halen; ze worden nu automatisch naar je postvak gestuurd.\n\nHier is jouw exclusieve kluiscode voucher:\n\n👉 **{$uniqueCode}**\n\nInhoud bij inwisselen:\n• €".number_format($rew['cash'], 0, ',', '.')." wit geld (direct op je bankrekening)\n• {$rew['stars']}x Exclusieve Sterren ⭐\n• {$rew['tokens']}x KluisMunten 🔑\n\nVoer deze code in bij 'Kluiscode Inwisselen' of bied hem te koop aan op de Marktplaats!",
                    'type' => 'reward',
                ]);

                $claimed[] = $lvl;
                $newlyIssued = true;
            }
        }

        if ($newlyIssued) {
            $character->claimed_level_rewards = $claimed;
            $character->save();
        }

        // Generate a session target number if not set
        if (! session()->has('vault_target')) {
            session(['vault_target' => rand(1, 10)]);
        }

        $attemptsLeft = session('vault_attempts', 3);
        $revealedCode = session('vault_revealed_code');
        $boughtHint = session('vault_bought_hint');
        $jammerActive = session('vault_jammer', false);

        $levelRewards = self::LEVEL_REWARDS;
        $claimedLevels = $character->claimed_level_rewards ?? [];

        // Fetch user's unredeemed codes
        $myCodes = VaultCode::where('character_id', $character->id)
            ->where('is_redeemed', false)
            ->get();

        $topRankings = Character::getTopRankings(10);
        $prefillCode = $request->query('code', '');

        return view('game.vault.index', compact(
            'character',
            'attemptsLeft',
            'revealedCode',
            'boughtHint',
            'jammerActive',
            'levelRewards',
            'claimedLevels',
            'myCodes',
            'topRankings',
            'prefillCode'
        ));
    }

    public function guess(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je geen kluizen kraken!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $validated = $request->validate([
            'guess' => ['required', 'integer', 'between:1,10'],
        ]);

        $energyCost = 10;
        if (! $this->energy->consume($character, $energyCost)) {
            return back()->with('error', "Je hebt minimaal {$energyCost} energie nodig voor een gok!");
        }

        $target = $this->vaultTarget();
        $attemptsLeft = session('vault_attempts', 3) - 1;
        $guess = (int) $validated['guess'];

        if ($guess === $target) {
            $reward = rand(650, 2800);
            $tokenReward = rand(1, 2);

            $this->economy->addCash($character, $reward);
            $this->level->addXp($character, 175);
            $character->vault_tokens = ($character->vault_tokens ?? 0) + $tokenReward;
            $character->respect += 3;
            $character->save();

            HalloweenService::record($character, 'vault', 1);

            // Reset vault session
            session()->forget([
                'vault_target',
                'vault_attempts',
                'vault_revealed_code',
                'vault_bought_hint',
                'vault_jammer',
            ]);

            return back()->with('success', "KLIK! De kluisdeur zwaait open! Code {$target} was exact juist. Buit: €".number_format($reward, 0, ',', '.')." contant + {$tokenReward} KluisMunten 🔑 +175 XP!");
        }

        $hint = $guess < $target ? 'Te laag! Raad hoger.' : 'Te hoog! Raad lager.';

        if ($attemptsLeft <= 0) {
            $hasJammer = session('vault_jammer', false);
            session()->forget([
                'vault_target',
                'vault_attempts',
                'vault_revealed_code',
                'vault_bought_hint',
                'vault_jammer',
            ]);

            if ($hasJammer) {
                return back()->with('success', 'Alle pogingen op, maar je gekochte Alarm Jammer heeft het alarm geblokkeerd! Geen isoleercel straf. De kluis is herstart.');
            }

            if (rand(1, 100) <= 60) {
                $this->prison->jail($character, 12, 'Betrapt bij het forceren van de kantoorkluis.');

                return redirect()->route('prison')->with('error', 'Alle pogingen verbruikt! Het alarm ging loeien: 12 minuten isoleercel.');
            }

            return back()->with('error', "Alle pogingen verbruikt! De kluis is vergrendeld en gereset. ({$hint})");
        }

        session(['vault_attempts' => $attemptsLeft]);

        return back()->with('error', "Fout! {$hint} Je hebt nog {$attemptsLeft} poging(en) over.");
    }

    /**
     * Redeem secret MedicijnKluis codes (supports batch/multi-line redemption).
     */
    public function redeemCode(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen kluiscodes inwisselen!');
        }

        $codeId = $request->input('code_id');
        $isRedeemAll = $request->boolean('redeem_all') || $request->input('all') === '1';
        $rawInput = (string) ($request->input('codes') ?? $request->input('code') ?? '');
        $lines = preg_split('/[\r\n,]+/', $rawInput);
        $cleanCodes = array_values(array_filter(array_unique(array_map(fn ($c) => trim(strtoupper(strip_tags($c))), $lines))));

        if (! $codeId && ! $isRedeemAll && empty($cleanCodes)) {
            return back()->with('error', 'Geen kluiscode geselecteerd of ingevoerd.');
        }

        if (count($cleanCodes) > 100) {
            return back()->with('error', 'Je kunt maximaal 100 kluiscodes tegelijk kraken.');
        }

        return DB::transaction(function () use ($character, $codeId, $isRedeemAll, $cleanCodes) {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            $claimedRewards = $lockedCharacter->claimed_level_rewards ?? [];
            if (! is_array($claimedRewards)) {
                $claimedRewards = [];
            }

            $seasonRewardsFound = [];
            foreach ($cleanCodes as $candidateCode) {
                if (isset(self::SEASON_CODES[$candidateCode])) {
                    // Aliases share one claim: SEIZOEN1 and SEIZOEN2 pay the same reward only once.
                    // Older claims were stored under the alias text, so every alias key counts as claimed.
                    $canonical = self::canonicalSeasonCode($candidateCode);
                    $aliasKeys = array_map(
                        fn (string $code): string => 'season2_code_'.$code,
                        array_filter(array_keys(self::SEASON_CODES), fn (string $code): bool => self::canonicalSeasonCode($code) === $canonical)
                    );

                    if (empty(array_intersect($aliasKeys, $claimedRewards))) {
                        $seasonRewardsFound[$candidateCode] = self::SEASON_CODES[$candidateCode];
                        $claimedRewards[] = 'season2_code_'.$canonical;
                    }
                }
            }

            if ($codeId) {
                $codesToRedeem = VaultCode::where('id', $codeId)
                    ->where('character_id', $lockedCharacter->id)
                    ->where('is_redeemed', false)
                    ->lockForUpdate()
                    ->get();
            } elseif ($isRedeemAll) {
                $codesToRedeem = VaultCode::where('character_id', $lockedCharacter->id)
                    ->where('is_redeemed', false)
                    ->lockForUpdate()
                    ->take(100)
                    ->get();
            } else {
                // Only codes in the player's own possession can be redeemed. A code in marketplace escrow
                // (owner null) or in another player's inbox must not be redeemable by typing its text.
                $nonSeasonCodes = array_diff($cleanCodes, array_keys(self::SEASON_CODES));
                $codesToRedeem = ! empty($nonSeasonCodes)
                    ? VaultCode::whereIn('code', $nonSeasonCodes)
                        ->where('character_id', $lockedCharacter->id)
                        ->where('is_redeemed', false)
                        ->lockForUpdate()
                        ->get()
                    : collect();
            }

            if ($codesToRedeem->isEmpty() && empty($seasonRewardsFound)) {
                return back()->with('error', 'Geen geldige of ongebruikte kluiscodes gevonden om te kraken.');
            }

            $redeemedCount = 0;
            $totalCash = 0;
            $totalStars = 0;
            $totalTokens = 0;
            $totalMasks = 0;
            $totalRespect = 0;

            foreach ($seasonRewardsFound as $sCode => $sData) {
                $redeemedCount++;
                $totalCash += $sData['cash'];
                $totalStars += $sData['stars'];
                $totalTokens += $sData['tokens'];
                $totalMasks += $sData['masks'];
                $totalRespect += $sData['respect'];
            }

            foreach ($codesToRedeem as $vaultCode) {
                $redeemedCount++;
                $totalCash += max(1000, $vaultCode->reward_cash);
                $totalStars += max(0, (int) $vaultCode->reward_stars);
                $totalTokens += max(2, $vaultCode->reward_tokens);
                $totalMasks += 1;
                $totalRespect += 5;

                $vaultCode->is_redeemed = true;
                $vaultCode->redeemed_by_id = $lockedCharacter->id;
                $vaultCode->save();
            }

            // Masks live in the characters.masks column and use no depot slot, so every earned mask is granted.
            $masksGranted = $totalMasks;

            $lockedCharacter->bank += $totalCash;
            $lockedCharacter->stars = ($lockedCharacter->stars ?? 0) + $totalStars;
            $lockedCharacter->vault_tokens = ($lockedCharacter->vault_tokens ?? 0) + $totalTokens;
            $lockedCharacter->masks = ($lockedCharacter->masks ?? 0) + $masksGranted;
            $lockedCharacter->respect += $totalRespect;
            $lockedCharacter->claimed_level_rewards = $claimedRewards;
            $lockedCharacter->save();

            // Record Halloween Quest progress for cracking safes/codes
            HalloweenService::record($lockedCharacter, 'vault', $redeemedCount);

            $msg = "🎉 {$redeemedCount} kluiscode(s) succesvol gekraakt in de Suikerkluis!\n";
            $msg .= '• +€'.number_format($totalCash, 0, ',', '.')." wit geld op je Bankrekening 🏦\n";
            $msg .= "• +{$totalStars} Exclusieve Sterren ⭐\n";
            $msg .= "• +{$masksGranted} Maskers 🎭 (voor zakkenrollen)\n";
            $msg .= "• +{$totalRespect} Respect 🔥\n";
            $msg .= "• 🎃 Halloween Quest: +{$redeemedCount} kluis voortgang geregistreerd!";

            return back()->with('success', $msg);
        });
    }

    /**
     * Buy the exact vault code with special vault tokens.
     */
    public function buyCode(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;
        $cost = 4;

        if (($character->vault_tokens ?? 0) < $cost) {
            return back()->with('error', "Je hebt minimaal {$cost} KluisMunten nodig om de exacte kluiscode te decoderen!");
        }

        $target = $this->vaultTarget();
        $character->vault_tokens -= $cost;
        $character->save();

        session(['vault_revealed_code' => $target]);

        return back()->with('success', "Kluiscode gedecodeerd met {$cost} KluisMunten! De geheime code is: {$target}. Toets hem direct in!");
    }

    /**
     * Buy a helpful hint with special vault tokens.
     */
    public function buyHint(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;
        $cost = 1;

        if (($character->vault_tokens ?? 0) < $cost) {
            return back()->with('error', "Je hebt minimaal {$cost} KluisMunt nodig voor een tip!");
        }

        $target = $this->vaultTarget();
        $character->vault_tokens -= $cost;
        $character->save();

        $parity = ($target % 2 === 0) ? 'even getal' : 'oneven getal';
        $range = ($target <= 5) ? 'tussen 1 en 5' : 'tussen 6 en 10';
        $hintText = "De cijfercode is een {$parity} en ligt {$range}.";

        session(['vault_bought_hint' => $hintText]);

        return back()->with('success', "Geheime tip gekocht voor 1 KluisMunt: {$hintText}");
    }

    /**
     * Buy an alarm jammer with special vault tokens.
     */
    public function buyJammer(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;
        $cost = 2;

        if (($character->vault_tokens ?? 0) < $cost) {
            return back()->with('error', "Je hebt minimaal {$cost} KluisMunten nodig voor een Alarm Jammer!");
        }

        $character->vault_tokens -= $cost;
        $character->save();

        session(['vault_jammer' => true]);

        return back()->with('success', 'Alarm Jammer geactiveerd! Als je 3x faalt, word je NIET naar de isoleercel gestuurd.');
    }

    /**
     * Sell a vault code directly to the black market broker for clean bank cash.
     */
    public function sellCode(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen kluiscodes verkopen!');
        }

        $codeId = $request->input('code_id');
        $sellValue = 3500;

        $soldCode = DB::transaction(function () use ($character, $codeId, $sellValue): ?string {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            $code = VaultCode::where('id', $codeId)
                ->where('character_id', $lockedCharacter->id)
                ->where('is_redeemed', false)
                ->lockForUpdate()
                ->first();

            if (! $code) {
                return null;
            }

            $codeString = $code->code;
            $lockedCharacter->bank += $sellValue;
            $lockedCharacter->save();
            $code->delete();

            return $codeString;
        });

        if (! $soldCode) {
            return back()->with('error', 'Geselecteerde kluiscode is niet gevonden of al ingewisseld.');
        }

        return back()->with('success', "💰 Kluiscode {$soldCode} succesvol verkocht voor €".number_format($sellValue, 0, ',', '.').' wit bankgeld 🏦!');
    }

    /**
     * Sell masks directly to the fence for clean bank cash.
     */
    public function sellMask(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen maskers verkopen!');
        }

        $amount = max(1, (int) $request->input('amount', 1));
        $pricePerMask = 1500;
        $totalEarned = $amount * $pricePerMask;

        $sold = DB::transaction(function () use ($character, $amount, $totalEarned): bool {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            if (($lockedCharacter->masks ?? 0) < $amount) {
                return false;
            }

            $lockedCharacter->masks -= $amount;
            $lockedCharacter->bank += $totalEarned;
            $lockedCharacter->save();

            return true;
        });

        if (! $sold) {
            $character->refresh();

            return back()->with('error', "Je hebt niet genoeg maskers (je bezit er {$character->masks}).");
        }

        return back()->with('success', "🎭 {$amount}x Masker(s) succesvol verkocht voor €".number_format($totalEarned, 0, ',', '.').' wit bankgeld 🏦!');
    }
}
