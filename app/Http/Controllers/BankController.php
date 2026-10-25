<?php

namespace App\Http\Controllers;

use App\Models\BankTransaction;
use App\Models\Loan;
use App\Models\LotteryRound;
use App\Models\User;
use App\Services\AllowanceService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LoanService;
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
        protected LoanService $loanService,
    ) {}

    /**
     * Show bank and financial center overview.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
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
        ));
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
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
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
            'amount' => ['required', 'integer', 'min:'.Loan::MIN_LOAN_AMOUNT],
            'deposit_to' => ['nullable', 'string', 'in:cash,bank'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

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
            'amount' => ['required', 'integer', 'min:1'],
            'source' => ['required', 'string', 'in:cash,bank'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

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
        $character = $user->character;

        $source = $validated['source'] ?? 'cash';
        $result = $this->loanService->repayLoanFull($character, $loan, $source);

        if ($result['success']) {
            return redirect()->route('bank.index', ['tab' => 'loans'])
                ->with('success', $result['message']);
        }

        return redirect()->route('bank.index', ['tab' => 'loans'])
            ->with('error', $result['message']);
    }
}
