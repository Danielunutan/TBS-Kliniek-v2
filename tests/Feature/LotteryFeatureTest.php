<?php

namespace Tests\Feature;

use App\Models\LotteryRound;
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

        $this->assertEquals(40000, $char->cash);
        $this->assertEquals($initialJackpot + 8000, $round->jackpot);
        $this->assertEquals(1, $round->total_tickets_sold);
    }
}
