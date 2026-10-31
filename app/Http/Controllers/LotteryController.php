<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\LotteryRound;
use App\Models\LotteryTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LotteryController extends Controller
{
    /**
     * Show the lottery hall page.
     */
    public function index(): View
    {
        $character = Auth::user()->character;
        $round = LotteryRound::current();

        $isLastQuarter = $round->isLastQuarter();
        $cashPricePerTicket = $round->getCashPricePerTicket();
        $starsPricePerTicket = $round->getStarsPricePerTicket();

        // User's tickets in active round
        $userTickets = LotteryTicket::where('lottery_round_id', $round->id)
            ->where('character_id', $character->id)
            ->orderByDesc('id')
            ->get();

        $userTicketsCount = $userTickets->count();
        $totalTickets = max(1, $round->total_tickets_sold);
        $winChance = $userTicketsCount > 0 ? round(($userTicketsCount / $totalTickets) * 100, 2) : 0;

        // Recent winners (past 6 rounds)
        $pastRounds = LotteryRound::with('winner')
            ->where('is_drawn', true)
            ->orderByDesc('id')
            ->take(6)
            ->get();

        return view('game.lottery.index', compact(
            'character',
            'round',
            'isLastQuarter',
            'cashPricePerTicket',
            'starsPricePerTicket',
            'userTickets',
            'userTicketsCount',
            'winChance',
            'pastRounds'
        ));
    }

    /**
     * Buy lottery tickets with cash or stars.
     */
    public function buyTickets(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'currency' => ['required', 'string', 'in:cash,stars'],
            'quantity' => ['required', 'integer', 'in:1,5,10,25'],
        ]);

        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen loten kopen!');
        }

        $quantity = (int) $validated['quantity'];
        $currency = $validated['currency'];

        $round = LotteryRound::current();
        $isDiscounted = $round->isLastQuarter();

        if ($currency === 'cash') {
            $pricePerTicket = $round->getCashPricePerTicket();
            $totalCost = $pricePerTicket * $quantity;

            return DB::transaction(function () use ($character, $round, $totalCost, $quantity, $pricePerTicket, $isDiscounted): RedirectResponse {
                /** @var LotteryRound|null $lockedRound */
                $lockedRound = LotteryRound::where('id', $round->id)->lockForUpdate()->first();
                if (! $lockedRound || $lockedRound->is_drawn) {
                    return back()->with('error', 'Deze loterijronde is zojuist gesloten voor trekking. Probeer het direct opnieuw voor de nieuwe ronde!');
                }

                /** @var Character $lockedCharacter */
                $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

                if ($lockedCharacter->cash < $totalCost) {
                    return back()->with('error', 'Niet genoeg contant geld! Je hebt €'.number_format($totalCost, 0, ',', '.')." nodig voor {$quantity} lot(en).");
                }

                $lockedCharacter->decrement('cash', $totalCost);

                // 80% of ticket sales go straight to the jackpot
                $jackpotContribution = (int) round($totalCost * 0.80);
                $lockedRound->increment('jackpot', $jackpotContribution);
                $lockedRound->increment('total_tickets_sold', $quantity);

                $tickets = [];
                for ($i = 0; $i < $quantity; $i++) {
                    $tickets[] = [
                        'lottery_round_id' => $lockedRound->id,
                        'character_id' => $lockedCharacter->id,
                        'ticket_number' => 'TBS-'.str_pad((string) rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                        'currency' => 'cash',
                        'price_paid' => $pricePerTicket,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                LotteryTicket::insert($tickets);

                $discountMsg = $isDiscounted ? ' (met 50% laatste kwartier korting!)' : '';

                return back()->with('success', "Succes! Je hebt {$quantity} lot(en) gekocht voor €".number_format($totalCost, 0, ',', '.').$discountMsg.'. Veel geluk bij de trekking!');
            });
        }

        // Currency is stars (contributes €25.000 bonus cash to the jackpot per ticket)
        $starsPerTicket = $round->getStarsPricePerTicket();
        $totalStarsCost = $starsPerTicket * $quantity;

        return DB::transaction(function () use ($character, $round, $totalStarsCost, $quantity, $starsPerTicket, $isDiscounted): RedirectResponse {
            /** @var LotteryRound|null $lockedRound */
            $lockedRound = LotteryRound::where('id', $round->id)->lockForUpdate()->first();
            if (! $lockedRound || $lockedRound->is_drawn) {
                return back()->with('error', 'Deze loterijronde is zojuist gesloten voor trekking. Probeer het direct opnieuw voor de nieuwe ronde!');
            }

            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            if (($lockedCharacter->stars ?? 0) < $totalStarsCost) {
                return back()->with('error', "Niet genoeg sterren! Je hebt {$totalStarsCost} sterren nodig voor {$quantity} VIP lot(en).");
            }

            $lockedCharacter->decrement('stars', $totalStarsCost);

            // Stars boost the jackpot by €25.000 per ticket!
            $jackpotBonus = 25000 * $quantity;
            $lockedRound->increment('jackpot', $jackpotBonus);
            $lockedRound->increment('total_tickets_sold', $quantity);

            $tickets = [];
            for ($i = 0; $i < $quantity; $i++) {
                $tickets[] = [
                    'lottery_round_id' => $lockedRound->id,
                    'character_id' => $lockedCharacter->id,
                    'ticket_number' => 'VIP-'.str_pad((string) rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                    'currency' => 'stars',
                    'price_paid' => $starsPerTicket,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            LotteryTicket::insert($tickets);

            $discountMsg = $isDiscounted ? ' (met 50% korting!)' : '';

            return back()->with('success', "Geweldig! Je hebt {$quantity} VIP lot(en) gekocht met {$totalStarsCost} sterren{$discountMsg}. De jackpot is met €".number_format(25000 * $quantity, 0, ',', '.').' verhoogd!');
        });
    }

    /**
     * Draw the lottery winner for a completed round.
     */
    protected function performDraw(LotteryRound $round): void
    {
        $round->executeDraw();
    }
}
