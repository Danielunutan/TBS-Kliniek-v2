<?php

namespace App\Http\Controllers;

use App\Models\BankTransaction;
use App\Models\Character;
use App\Models\Loan;
use App\Models\LotteryRound;
use App\Models\User;
use App\Services\AllowanceService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LoanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BankController extends Controller
{
    public function __construct(
        protected EconomyService $economyService,
        protected EnergyService $energyService,
        protected AllowanceService $allowanceService,
        protected LoanService $loanService,
    ) {}

    /**
     * Show bank and financial center overview.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            abort(404, 'Karakter niet gevonden.');
        }

        $this->energyService->refresh($character);

        $allowanceDetails = $this->allowanceService->getAllowanceDetails($character);

        $activeTab = $request->query('tab', 'vault');
        if (! in_array($activeTab, ['vault', 'loans', 'transactions'], true)) {
            $activeTab = 'vault';
        }

        $activeLoans = Loan::where('character_id', $character->id)
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->get();

        $paidLoans = Loan::where('character_id', $character->id)
            ->where('status', 'paid')
            ->orderBy('paid_at', 'desc')
            ->take(5)
            ->get();

        $maxCapacity = Loan::calculateMaxCapacity($character);
        $openstaandeSchuld = (int) $activeLoans->sum('remaining_due');
        $beschikbareLening = max(0, $maxCapacity - $openstaandeSchuld);

        $transactions = BankTransaction::where('character_id', $character->id)
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $hasBankPin = $character->hasBankPin();
        $isAtmUnlocked = (bool) session('atm_unlocked_'.$character->id, false);

        return view('game.bank', compact(
            'character',
            'allowanceDetails',
            'activeTab',
            'activeLoans',
            'paidLoans',
            'maxCapacity',
            'openstaandeSchuld',
            'beschikbareLening',
            'transactions',
            'hasBankPin',
            'isAtmUnlocked',
        ));
    }

    /**
     * Deposit cash to bank.
     */
    public function deposit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:2000000000'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            return redirect()->route('dashboard');
        }

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel heb je geen toegang tot de Geldmaat!');
        }

        if ($character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($this->isAtmLocked($character)) {
            return redirect()->route('bank.index', ['tab' => 'vault'])
                ->with('error', 'Voer eerst je 4-cijferige pincode in om toegang te krijgen tot de Geldmaat.');
        }

        if ($this->economyService->deposit($character, $validated['amount'])) {
            $formatted = number_format($validated['amount'], 0, ',', '.');

            return redirect()->route('bank.index', ['tab' => 'vault'])
                ->with('success', "Je hebt €{$formatted} veilig op je bankrekening gestort!");
        }

        return redirect()->route('bank.index', ['tab' => 'vault'])
            ->with('error', 'Niet genoeg contant geld om dit bedrag te storten.');
    }

    /**
     * Withdraw cash from bank with lottery tax.
     */
    public function withdraw(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:2000000000'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            return redirect()->route('dashboard');
        }

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel heb je geen toegang tot de Geldmaat!');
        }

        if ($character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($this->isAtmLocked($character)) {
            return redirect()->route('bank.index', ['tab' => 'vault'])
                ->with('error', 'Voer eerst je 4-cijferige pincode in om toegang te krijgen tot de Geldmaat.');
        }

        $amount = (int) $validated['amount'];

        if ($character->bank < $amount) {
            return redirect()->route('bank.index', ['tab' => 'vault'])
                ->with('error', 'Niet genoeg banktegoed om dit bedrag op te nemen.');
        }

        $taxRate = $character->isVip() ? 0.03 : 0.05;
        $taxAmount = max(1, (int) round($amount * $taxRate));
        $netCash = max(0, $amount - $taxAmount);

        if ($this->economyService->withdrawWithTax($character, $amount, $netCash, $taxAmount)) {
            LotteryRound::addTax($taxAmount);

            $formattedNet = number_format($netCash, 0, ',', '.');
            $formattedTax = number_format($taxAmount, 0, ',', '.');
            $taxPercent = ($taxRate * 100).'%';

            return redirect()->route('bank.index', ['tab' => 'vault'])
                ->with('success', "Je hebt €{$formattedNet} contant opgenomen na {$taxPercent} kliniektax (€{$formattedTax}). Deze belasting vloeit 100% door naar de TBS Afdelingsloterij Jackpot!");
        }

        return redirect()->route('bank.index', ['tab' => 'vault'])
            ->with('error', 'Er is een fout opgetreden bij het opnemen van je bankgeld.');
    }

    /**
     * Request a new loan.
     */
    public function requestLoan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:'.Loan::MIN_LOAN_AMOUNT, 'max:2000000000'],
            'deposit_to' => ['nullable', 'string', 'in:cash,bank'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            return redirect()->route('dashboard');
        }

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je geen lening afsluiten!');
        }

        if ($character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($this->isAtmLocked($character)) {
            return redirect()->route('bank.index', ['tab' => 'vault'])
                ->with('error', 'Voer eerst je 4-cijferige pincode in om toegang te krijgen tot de Geldmaat.');
        }

        $depositTo = $validated['deposit_to'] ?? 'cash';
        $result = $this->loanService->requestLoan($character, $validated['amount'], $depositTo);

        if ($result['success']) {
            return redirect()->route('bank.index', ['tab' => 'loans'])
                ->with('success', $result['message']);
        }

        return redirect()->route('bank.index', ['tab' => 'loans'])
            ->with('error', $result['message']);
    }

    /**
     * Repay an active loan partially.
     */
    public function repayLoan(Request $request, Loan $loan): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:2000000000'],
            'source' => ['required', 'string', 'in:cash,bank'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            return redirect()->route('dashboard');
        }

        if ($loan->character_id !== $character->id) {
            abort(403, 'Deze lening is niet van jou.');
        }

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel heb je geen toegang tot de Geldmaat!');
        }

        if ($character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($this->isAtmLocked($character)) {
            return redirect()->route('bank.index', ['tab' => 'loans'])
                ->with('error', 'Voer eerst je 4-cijferige pincode in om toegang te krijgen tot de Geldmaat.');
        }

        $result = $this->loanService->repayLoan($character, $loan, $validated['amount'], $validated['source']);

        if ($result['success']) {
            return redirect()->route('bank.index', ['tab' => 'loans'])
                ->with('success', $result['message']);
        }

        return redirect()->route('bank.index', ['tab' => 'loans'])
            ->with('error', $result['message']);
    }

    /**
     * Repay an active loan in full.
     */
    public function repayLoanFull(Request $request, Loan $loan): RedirectResponse
    {
        $validated = $request->validate([
            'source' => ['nullable', 'string', 'in:cash,bank'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            return redirect()->route('dashboard');
        }

        if ($loan->character_id !== $character->id) {
            abort(403, 'Deze lening is niet van jou.');
        }

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel heb je geen toegang tot de Geldmaat!');
        }

        if ($character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($this->isAtmLocked($character)) {
            return redirect()->route('bank.index', ['tab' => 'loans'])
                ->with('error', 'Voer eerst je 4-cijferige pincode in om toegang te krijgen tot de Geldmaat.');
        }

        $source = $validated['source'] ?? 'cash';
        $result = $this->loanService->repayLoanFull($character, $loan, $source);

        if ($result['success']) {
            return redirect()->route('bank.index', ['tab' => 'loans'])
                ->with('success', $result['message']);
        }

        return redirect()->route('bank.index', ['tab' => 'loans'])
            ->with('error', $result['message']);
    }

    /**
     * Set a 4-digit PIN for the Geldmaat ATM.
     */
    public function setPin(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'pin' => ['required', 'digits:4'],
            'pin_confirmation' => ['required', 'same:pin'],
        ], [
            'pin.required' => 'Voer een 4-cijferige pincode in.',
            'pin.digits' => 'De pincode moet exact 4 cijfers bevatten.',
            'pin_confirmation.same' => 'De bevestigingscode komt niet overeen.',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            return redirect()->route('dashboard');
        }

        $isAtmUnlocked = (bool) session('atm_unlocked_'.$character->id);
        $currentPin = (string) $request->input('current_pin', '');

        $pinUpdated = DB::transaction(function () use ($character, $validated, $isAtmUnlocked, $currentPin): bool {
            /** @var Character|null $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $lockedCharacter) {
                return false;
            }

            if ($lockedCharacter->hasBankPin() && ! $isAtmUnlocked) {
                if ($currentPin === '' || ! $lockedCharacter->verifyBankPin($currentPin)) {
                    return false;
                }
            }

            $lockedCharacter->setBankPin($validated['pin']);

            return true;
        });

        if (! $pinUpdated) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ontgrendel eerst de Geldmaat met je huidige pincode om een nieuwe pincode in te stellen.',
                ], 422);
            }

            return redirect()->route('bank.index', ['tab' => 'vault'])
                ->with('error', 'Ontgrendel eerst de Geldmaat met je huidige pincode om een nieuwe pincode in te stellen.');
        }

        session(['atm_unlocked_'.$character->id => true]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pincode succesvol ingesteld! Uw Kliniek Betaalpas is geactiveerd.',
            ]);
        }

        return redirect()->route('bank.index', ['tab' => 'vault'])
            ->with('success', 'Pincode succesvol ingesteld! Uw Kliniek Betaalpas is geactiveerd.');
    }

    /**
     * Verify 4-digit PIN to unlock the Geldmaat ATM.
     */
    public function verifyPin(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'pin' => ['required', 'digits:4'],
        ], [
            'pin.required' => 'Voer uw 4-cijferige pincode in.',
            'pin.digits' => 'Voer exact 4 cijfers in.',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            return redirect()->route('dashboard');
        }

        if ($character->verifyBankPin($validated['pin'])) {
            session(['atm_unlocked_'.$character->id => true]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pincode akkoord! Welkom bij de Geldmaat.',
                ]);
            }

            return redirect()->route('bank.index', ['tab' => 'vault'])
                ->with('success', 'Pincode akkoord! Welkom bij de Geldmaat.');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Onjuiste pincode! Controleer uw cijfers en probeer opnieuw.',
            ], 422);
        }

        return redirect()->route('bank.index', ['tab' => 'vault'])
            ->with('error', 'Onjuiste pincode! Probeer opnieuw.');
    }

    /**
     * Eject card / lock Geldmaat ATM session.
     */
    public function ejectCard(Request $request): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            return redirect()->route('dashboard');
        }

        session()->forget('atm_unlocked_'.$character->id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Uw betaalpas is uitgeworpen. Vergeet uw pas niet mee te nemen!',
            ]);
        }

        return redirect()->route('bank.index', ['tab' => 'vault'])
            ->with('info', 'Betaalpas uitgeworpen. Tot ziens!');
    }

    /**
     * Check if the Geldmaat ATM is currently locked by a security PIN.
     */
    protected function isAtmLocked(Character $character): bool
    {
        return $character->hasBankPin() && ! session('atm_unlocked_'.$character->id, false);
    }
}
