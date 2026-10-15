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

        // Check if drawing time has passed and trigger automatic draw
        if ($round->draws_at && now()->gte($round->draws_at) && ! $round->is_drawn) {
            $this->performDraw($round);
            $round = LotteryRound::current();
        }

        // User's tickets in active round
        $userTickets = LotteryTicket::where('lottery_round_id', $round->id)
            ->where('character_id', $character->id)
            ->orderByDesc('id')
            ->get();

        $userTicketsCount = $userTickets->count();
        $totalTickets = max(1, $round->total_tickets_sold);
        $winChance = $userTicketsCount > 0 ? round(($userTicketsCount / $totalTickets) * 100, 2) : 0;

        // Recent winners (past 5 rounds)
        $pastRounds = LotteryRound::with('winner')
            ->where('is_drawn', true)
            ->orderByDesc('id')
            ->take(6)
            ->get();

        return view('game.lottery.index', compact(
            'character',
            'round',
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
        $quantity = (int) $validated['quantity'];
        $currency = $validated['currency'];

        $round = LotteryRound::current();

        if ($currency === 'cash') {
            $pricePerTicket = 10000;
            $totalCost = $pricePerTicket * $quantity;

            if ($character->cash < $totalCost) {
                return back()->with('error', 'Niet genoeg contant geld! Je hebt €'.number_format($totalCost, 0, ',', '.')." nodig voor {$quantity} lot(en).");
            }

            DB::transaction(function () use ($character, $round, $totalCost, $quantity, $pricePerTicket): void {
                $character->decrement('cash', $totalCost);

                // 80% of ticket sales go straight to the jackpot
                $jackpotContribution = (int) round($totalCost * 0.80);
                $round->increment('jackpot', $jackpotContribution);
                $round->increment('total_tickets_sold', $quantity);

                $tickets = [];
                for ($i = 0; $i < $quantity; $i++) {
                    $tickets[] = [
                        'lottery_round_id' => $round->id,
                        'character_id' => $character->id,
                        'ticket_number' => 'TBS-'.str_pad((string) rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                        'currency' => 'cash',
                        'price_paid' => $pricePerTicket,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                LotteryTicket::insert($tickets);
            });

            return back()->with('success', "Succes! Je hebt {$quantity} lot(en) gekocht voor €".number_format($totalCost, 0, ',', '.').'. Veel geluk bij de 14-daagse trekking!');
        }

        // Currency is stars (15 stars per ticket, contributes €25.000 bonus cash to the jackpot!)
        $starsPerTicket = 15;
        $totalStarsCost = $starsPerTicket * $quantity;

        if (($character->stars ?? 0) < $totalStarsCost) {
            return back()->with('error', "Niet genoeg sterren! Je hebt {$totalStarsCost} sterren nodig voor {$quantity} VIP lot(en).");
        }

        DB::transaction(function () use ($character, $round, $totalStarsCost, $quantity, $starsPerTicket): void {
            $character->decrement('stars', $totalStarsCost);

            // Stars boost the jackpot by €25.000 per ticket!
            $jackpotBonus = 25000 * $quantity;
            $round->increment('jackpot', $jackpotBonus);
            $round->increment('total_tickets_sold', $quantity);

            $tickets = [];
            for ($i = 0; $i < $quantity; $i++) {
                $tickets[] = [
                    'lottery_round_id' => $round->id,
                    'character_id' => $character->id,
                    'ticket_number' => 'VIP-'.str_pad((string) rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                    'currency' => 'stars',
                    'price_paid' => $starsPerTicket,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            LotteryTicket::insert($tickets);
        });

        return back()->with('success', "Geweldig! Je hebt {$quantity} VIP lot(en) gekocht met {$totalStarsCost} sterren. De jackpot is met €".number_format(25000 * $quantity, 0, ',', '.').' verhoogd!');
    }

    /**
     * Draw the lottery winner for a completed round.
     */
    protected function performDraw(LotteryRound $round): void
    {
        $allTickets = $round->tickets()->get();

        if ($allTickets->isEmpty()) {
            // No tickets sold: roll over jackpot into next round
            $nextRound = LotteryRound::create([
                'round_number' => $round->round_number + 1,
                'jackpot' => $round->jackpot + 25000,
                'tax_collected' => 0,
                'starts_at' => now(),
                'draws_at' => now()->addDays(14),
                'is_drawn' => false,
                'total_tickets_sold' => 0,
            ]);

            $round->update([
                'is_drawn' => true,
                'winner_character_id' => null,
                'winner_payout' => 0,
                'winning_ticket_number' => 'GEEN LOTEN',
            ]);

            return;
        }

        /** @var LotteryTicket $winningTicket */
        $winningTicket = $allTickets->random();
        /** @var Character $winner */
        $winner = $winningTicket->character;

        $payout = $round->jackpot;

        DB::transaction(function () use ($round, $winningTicket, $winner, $payout): void {
            // Credit winner's bank
            if ($winner) {
                $winner->increment('bank', $payout);
            }

            // Mark round as drawn
            $round->update([
                'is_drawn' => true,
                'winner_character_id' => $winner?->id,
                'winner_payout' => $payout,
                'winning_ticket_number' => $winningTicket->ticket_number,
            ]);

            // Create next round with fresh jackpot base
            LotteryRound::create([
                'round_number' => $round->round_number + 1,
                'jackpot' => 25000,
                'tax_collected' => 0,
                'starts_at' => now(),
                'draws_at' => now()->addDays(14),
                'is_drawn' => false,
                'total_tickets_sold' => 0,
            ]);
        });
    }
}
