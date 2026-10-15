<?php

namespace App\Http\Controllers;

use App\Models\LotteryRound;
use App\Models\User;
use App\Services\AllowanceService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BankController extends Controller
{
    public function __construct(
        protected EconomyService $economyService,
        protected EnergyService $energyService,
        protected AllowanceService $allowanceService,
    ) {}

    /**
     * Show bank overview.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $allowanceDetails = $this->allowanceService->getAllowanceDetails($character);

        return view('game.bank', compact('character', 'allowanceDetails'));
    }

    /**
     * Deposit cash to bank.
     */
    public function deposit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($this->economyService->deposit($character, $validated['amount'])) {
            $formatted = number_format($validated['amount'], 0, ',', '.');

            return back()->with('success', "Je hebt €{$formatted} veilig op je bankrekening gestort!");
        }

        return back()->with('error', 'Niet genoeg contant geld om dit bedrag te storten.');
    }

    /**
     * Withdraw cash from bank with lottery tax.
     */
    public function withdraw(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $amount = (int) $validated['amount'];

        if ($character->bank < $amount) {
            return back()->with('error', 'Niet genoeg banktegoed om dit bedrag op te nemen.');
        }

        $taxRate = $character->isVip() ? 0.03 : 0.05;
        $taxAmount = max(1, (int) round($amount * $taxRate));
        $netCash = max(0, $amount - $taxAmount);

        if ($this->economyService->withdrawWithTax($character, $amount, $netCash, $taxAmount)) {
            LotteryRound::addTax($taxAmount);

            $formattedNet = number_format($netCash, 0, ',', '.');
            $formattedTax = number_format($taxAmount, 0, ',', '.');
            $taxPercent = ($taxRate * 100).'%';

            return back()->with('success', "Je hebt €{$formattedNet} contant opgenomen na {$taxPercent} kliniektax (€{$formattedTax}). Deze belasting vloeit 100% door naar de TBS Afdelingsloterij Jackpot!");
        }

        return back()->with('error', 'Er is een fout opgetreden bij het opnemen van je bankgeld.');
    }
}
