<?php

namespace App\Http\Controllers;

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
     * Withdraw cash from bank.
     */
    public function withdraw(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($this->economyService->withdraw($character, $validated['amount'])) {
            $formatted = number_format($validated['amount'], 0, ',', '.');

            return back()->with('success', "Je hebt €{$formatted} contant opgenomen. Pas op voor zakkenrollers!");
        }

        return back()->with('error', 'Niet genoeg banktegoed om dit bedrag op te nemen.');
    }
}
