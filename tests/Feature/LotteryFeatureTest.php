<?php

namespace Tests\Feature;

use App\Models\LotteryRound;
use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LotteryFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_lottery_page_renders_cleanly_with_14_day_round(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $round = LotteryRound::current();

        $this->assertEquals(14, (int) round(now()->diffInDays($round->draws_at, false)));

        $response = $this->actingAs($user)->get(route('lottery.index'));
        $response->assertOk();
        $response->assertSee('Grijp de');
        $response->assertSee('Kliniek Jackpot!');
        $response->assertSee('14-Daagse Ronde #1');
        $response->assertSee('10.000');
        $response->assertSee('15 Sterren');
    }

    public function test_player_can_buy_tickets_with_cash_and_increase_jackpot(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->cash = 50000;
        $char->save();

        $round = LotteryRound::current();
        $initialJackpot = $round->jackpot;

        $response = $this->actingAs($user)
            ->from(route('lottery.index'))
            ->post(route('lottery.buy'), [
                'currency' => 'cash',
                'quantity' => 1,
            ]);

        $response->assertRedirect(route('lottery.index'));
        $response->assertSessionHas('success');

        $char->refresh();
        $round->refresh();

        $this->assertEquals(48000, $char->cash);
        $this->assertEquals($initialJackpot + 2000, $round->jackpot);
        $this->assertEquals(1, $round->total_tickets_sold);
    }

    public function test_last_quarter_activates_fifty_percent_discount_on_tickets(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->cash = 50000;
        $char->save();

        $round = LotteryRound::current();
        // Advance round to 10 minutes remaining (within 15 minute last quarter)
        $round->draws_at = now()->addMinutes(10);
        $round->save();

        $this->assertTrue($round->isLastQuarter());
        $this->assertEquals(2000, $round->getCashPricePerTicket());
        $this->assertEquals(3, $round->getStarsPricePerTicket());

        $pageRes = $this->actingAs($user)->get(route('lottery.index'));
        $pageRes->assertOk();
        $pageRes->assertSee('80% KORTING');
        $pageRes->assertSee('2.000');

        $initialJackpot = $round->jackpot;

        // Buy 1 ticket at discounted price €2.000 (100% goes to pot)
        $response = $this->actingAs($user)
            ->from(route('lottery.index'))
            ->post(route('lottery.buy'), [
                'currency' => 'cash',
                'quantity' => 1,
            ]);

        $response->assertRedirect(route('lottery.index'));
        $response->assertSessionHas('success');

        $char->refresh();
        $round->refresh();

        // 50.000 - 2.000 = 48.000
        $this->assertEquals(48000, $char->cash);
        // 100% of 2.000 = 2.000 added to jackpot
        $this->assertEquals($initialJackpot + 2000, $round->jackpot);
    }

    public function test_lottery_draw_pays_thirty_percent_bank_and_seventy_percent_cash_and_publishes_news_and_opens_new_round(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->cash = 100000;
        $char->bank = 200000;
        $char->save();

        $round = LotteryRound::current();
        $round->jackpot = 1000000; // €1.000.000
        $round->save();

        // Buy a ticket so character has a ticket
        $this->actingAs($user)->post(route('lottery.buy'), [
            'currency' => 'cash',
            'quantity' => 1,
        ]);

        $round->refresh();
        $payout = $round->jackpot;
        $expectedBank = (int) round($payout * 0.30);
        $expectedCash = $payout - $expectedBank;

        $initialBank = $char->bank;
        $char->refresh();
        $initialCash = $char->cash;

        // Set round to expired and execute draw
        $round->draws_at = now()->subMinute();
        $round->save();

        $nextRound = $round->executeDraw();

        // Assert round is drawn
        $round->refresh();
        $this->assertTrue($round->is_drawn);
        $this->assertEquals($char->id, $round->winner_character_id);

        // Check payouts: 30% bank and 70% cash
        $char->refresh();
        $this->assertEquals($initialBank + $expectedBank, $char->bank);
        $this->assertEquals($initialCash + $expectedCash, $char->cash);

        // Check new round was automatically opened
        $this->assertFalse($nextRound->is_drawn);
        $this->assertEquals($round->round_number + 1, $nextRound->round_number);
        $this->assertEquals(25000, $nextRound->jackpot);
        $this->assertFalse($nextRound->isLastQuarter());
        $this->assertEquals(2000, $nextRound->getCashPricePerTicket());

        // Check News article was created
        $news = NewsArticle::latest('id')->first();
        $this->assertNotNull($news);
        $this->assertStringContainsString((string) $round->round_number, $news->title);
        $this->assertStringContainsString($char->name, $news->title);
        $this->assertStringContainsString('30% Gestort op Bank', $news->content);
        $this->assertStringContainsString('70% Contant Uitbetaald', $news->content);
        $this->assertStringContainsString('Ronde #'.$nextRound->round_number, $news->content);
    }

    public function test_lottery_page_renders_fast_and_compact_with_many_tickets(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $round = LotteryRound::current();

        // Create 50 tickets for this character
        $ticketsData = [];
        for ($i = 1; $i <= 50; $i++) {
            $ticketsData[] = [
                'lottery_round_id' => $round->id,
                'character_id' => $char->id,
                'ticket_number' => 'TBS-TEST-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'currency' => $i % 2 === 0 ? 'cash' : 'stars',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        \App\Models\LotteryTicket::insert($ticketsData);
        $round->increment('total_tickets_sold', 50);

        $response = $this->actingAs($user)->get(route('lottery.index'));
        $response->assertOk();
        $response->assertSee('50 in bezit');
        $response->assertSee('Contant');
        $response->assertSee('★ VIP');
        $response->assertSee('En nog 35 andere geregistreerde loten');
    }
}
