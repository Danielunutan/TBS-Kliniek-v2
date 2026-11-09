<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\EnergyService;
use App\Support\CellUpgradeCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CellController extends Controller
{
    public function __construct(
        protected EnergyService $energyService,
    ) {}

    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;

        $this->energyService->refresh($character);

        $catalog = CellUpgradeCatalog::all();
        $currentLevels = $character->getCellUpgradeLevels();

        // Calculate total cash invested in upgrades
        $totalInvested = 0;
        foreach ($catalog as $key => $upgrade) {
            $lvl = $currentLevels[$key] ?? 0;
            for ($i = 1; $i <= $lvl; $i++) {
                $totalInvested += (int) ($upgrade['tiers'][$i]['cost'] ?? 0);
            }
        }

        return view('game.cell.index', compact('character', 'catalog', 'currentLevels', 'totalInvested'));
    }

    public function upgrade(Request $request, string $upgradeKey): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $catalogItem = CellUpgradeCatalog::get($upgradeKey);
        if (! $catalogItem) {
            return back()->with('error', 'Ongeldige celupgrade geselecteerd.');
        }

        return DB::transaction(function () use ($character, $upgradeKey, $catalogItem) {
            /** @var Character|null $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $lockedCharacter) {
                return back()->with('error', 'Karakter niet gevonden.');
            }

            $upgrades = is_array($lockedCharacter->cell_upgrades) ? $lockedCharacter->cell_upgrades : [];
            $currentLevel = max(0, min(5, (int) ($upgrades[$upgradeKey] ?? 0)));
            $maxLevel = (int) ($catalogItem['max_level'] ?? 5);

            if ($currentLevel >= $maxLevel) {
                return back()->with('error', "De upgrade '{$catalogItem['name']}' heeft al het maximale niveau (Level {$maxLevel}) bereikt!");
            }

            $nextTier = CellUpgradeCatalog::getNextTier($upgradeKey, $currentLevel);
            if (! $nextTier) {
                return back()->with('error', 'Volgende upgrade niveau niet gevonden.');
            }

            $cost = (int) $nextTier['cost'];
            if ((int) $lockedCharacter->cash < $cost) {
                $tekort = number_format($cost - (int) $lockedCharacter->cash, 0, ',', '.');

                return back()->with('error', "Onvoldoende contant geld op zak! Je hebt €{$tekort} te weinig voor deze upgrade.");
            }

            // Deduct cash and upgrade
            $lockedCharacter->cash -= $cost;
            $newLevel = $currentLevel + 1;
            $upgrades[$upgradeKey] = $newLevel;
            $lockedCharacter->cell_upgrades = $upgrades;
            $lockedCharacter->save();

            AuditLogService::log(
                action: 'cell.upgrade',
                category: 'economy',
                description: "Celupgrade aangeschaft: {$catalogItem['name']} naar Level {$newLevel} voor €".number_format($cost, 0, ',', '.'),
                character: $lockedCharacter,
                user: $lockedCharacter->user,
                amount: $cost,
                details: [
                    'upgrade_key' => $upgradeKey,
                    'new_level' => $newLevel,
                    'cost' => $cost,
                ]
            );

            return back()->with('success', "🎉 Gefeliciteerd! Je celupgrade '{$catalogItem['name']}' is succesvol geïnstalleerd naar Level {$newLevel}!");
        });
    }
}
