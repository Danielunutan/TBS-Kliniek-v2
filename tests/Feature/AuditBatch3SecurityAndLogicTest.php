<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CinemaRoom;
use App\Models\DirectMessage;
use App\Models\GameMessage;
use App\Models\LotteryRound;
use App\Models\LotteryTicket;
use App\Models\Mission;
use App\Models\NewsArticle;
use App\Models\User;
use App\Models\VaultCode;
use App\Services\DailyBattleService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use App\Services\MissionService;
use App\Services\PleskMailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditBatch3SecurityAndLogicTest extends TestCase
{
    use RefreshDatabase;

    protected function createTestCharacter(array $attributes = []): Character
    {
        $user = User::factory()->create();

        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Batch3Tester_'.mt_rand(1000, 9999),
            'cash' => 10000,
            'bank' => 50000,
            'level' => 1,
            'xp' => 0,
            'total_xp' => 0,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'is_onboarded' => true,
        ], $attributes));
    }

    public function test_cinema_room_passcode_is_hidden_from_serialization(): void
    {
        $character = $this->createTestCharacter();

        $room = CinemaRoom::create([
            'title' => 'VIP Filmzaal',
            'slug' => 'vip-filmzaal-'.mt_rand(100, 999),
            'description' => 'Besloten filmzaal voor vrienden',
            'host_character_id' => $character->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'is_private' => true,
            'passcode' => 'GeheimWachtwoord123',
        ]);

        $serialized = $room->toArray();
        $this->assertArrayNotHasKey('passcode', $serialized);

        $json = json_encode($room);
        $this->assertStringNotContainsString('GeheimWachtwoord123', (string) $json);
    }

    public function test_game_message_deduplication_does_not_suppress_empty_subject_messages(): void
    {
        $character = $this->createTestCharacter();

        // Send first message with empty subject
        GameMessage::create([
            'recipient_id' => $character->id,
            'type' => 'system',
            'subject' => '',
            'content' => 'Eerste systeembericht zonder onderwerp.',
            'is_read' => false,
        ]);

        // Send second different message with empty subject within 1 second
        GameMessage::create([
            'recipient_id' => $character->id,
            'type' => 'system',
            'subject' => '',
            'content' => 'Tweede systeembericht zonder onderwerp.',
            'is_read' => false,
        ]);

        $dms = DirectMessage::where('recipient_id', $character->id)->get();
        $this->assertCount(2, $dms, 'Both system messages with empty subjects should create direct messages without false positive suppression');
    }

    public function test_lottery_winner_news_article_escapes_xss_payloads(): void
    {
        $xssName = '<script>alert("PWNED")</script>';
        $character = $this->createTestCharacter(['name' => $xssName]);

        $round = LotteryRound::create([
            'round_number' => 999,
            'jackpot' => 100000,
            'tax_collected' => 0,
            'starts_at' => now()->subDays(7),
            'draws_at' => now(),
            'is_drawn' => false,
            'total_tickets_sold' => 1,
        ]);

        $ticket = LotteryTicket::create([
            'lottery_round_id' => $round->id,
            'character_id' => $character->id,
            'ticket_number' => 'LOT-<b>XSS</b>',
            'purchased_at' => now(),
        ]);

        $nextRound = LotteryRound::create([
            'round_number' => 1000,
            'jackpot' => 25000,
            'tax_collected' => 0,
            'starts_at' => now(),
            'draws_at' => now()->addDays(14),
            'is_drawn' => false,
            'total_tickets_sold' => 0,
        ]);

        LotteryRound::publishWinnerNewsArticle($round, $character, $ticket, 100000, 30000, 70000, $nextRound);

        $article = NewsArticle::latest('id')->first();
        $this->assertNotNull($article);
        $this->assertStringNotContainsString('<script>alert("PWNED")</script>', $article->content);
        $this->assertStringContainsString('&lt;script&gt;alert(&quot;PWNED&quot;)&lt;/script&gt;', $article->content);
        $this->assertStringNotContainsString('LOT-<b>XSS</b>', $article->content);
        $this->assertStringContainsString('LOT-&lt;b&gt;XSS&lt;/b&gt;', $article->content);
    }

    public function test_economy_service_withdraw_with_tax_rejects_arithmetic_mismatches_and_negative_inputs(): void
    {
        $character = $this->createTestCharacter([
            'cash' => 0,
            'bank' => 10000,
        ]);

        $economyService = app(EconomyService::class);

        // Mismatched arithmetic: totalDebit (1000) != netCashToAdd (1500) + taxAmount (0)
        $result = $economyService->withdrawWithTax($character, 1000, 1500, 0);
        $this->assertFalse($result);

        // Negative net cash to add
        $resultNegativeCash = $economyService->withdrawWithTax($character, 1000, -100, 1100);
        $this->assertFalse($resultNegativeCash);

        // Negative tax amount
        $resultNegativeTax = $economyService->withdrawWithTax($character, 1000, 1100, -100);
        $this->assertFalse($resultNegativeTax);

        // Valid arithmetic: totalDebit (1000) = netCashToAdd (900) + taxAmount (100)
        $validResult = $economyService->withdrawWithTax($character, 1000, 900, 100);
        $this->assertTrue($validResult);

        $character->refresh();
        $this->assertEquals(900, $character->cash);
        $this->assertEquals(9000, $character->bank);
    }

    public function test_energy_service_refresh_safely_handles_future_timestamps(): void
    {
        $character = $this->createTestCharacter([
            'energy' => 50,
            'max_energy' => 100,
            'last_energy_refill_at' => now()->addHours(2),
        ]);

        $energyService = app(EnergyService::class);
        $energyService->refresh($character);

        $character->refresh();
        $this->assertEquals(50, $character->energy, 'Energy should not erroneously regenerate from a future timestamp');
        $this->assertTrue($character->last_energy_refill_at->lessThanOrEqualTo(now()->addSecond()));
    }

    public function test_daily_battle_service_award_prize_generates_unique_vault_codes(): void
    {
        $character = $this->createTestCharacter();
        $battleService = app(DailyBattleService::class);

        // Call protected awardPrize via reflection
        $reflection = new \ReflectionClass($battleService);
        $method = $reflection->getMethod('awardPrize');
        $method->setAccessible(true);

        $method->invoke($battleService, $character, 'vault_codes', 2, 'Top Hustler', 1);

        $codes = VaultCode::where('character_id', $character->id)->get();
        $this->assertCount(2, $codes);

        foreach ($codes as $code) {
            $this->assertMatchesRegularExpression('/^BATTLE-[A-Z0-9]{8}$/', $code->code);
            $this->assertFalse($code->is_redeemed);
        }

        $this->assertNotEquals($codes[0]->code, $codes[1]->code);
    }

    public function test_level_service_add_xp_clamps_negative_xp(): void
    {
        $character = $this->createTestCharacter([
            'level' => 5,
            'xp' => 100,
            'total_xp' => 500,
        ]);

        $levelService = app(LevelService::class);
        $result = $levelService->addXp($character, -250);

        $this->assertFalse($result['leveled_up']);
        $this->assertEquals(100, $character->xp);
        $this->assertEquals(500, $character->total_xp);
    }

    public function test_mission_service_blocks_imprisoned_character(): void
    {
        $character = $this->createTestCharacter([
            'prison_until' => now()->addMinutes(10),
        ]);

        $mission = Mission::create([
            'title' => 'Koffie Test',
            'slug' => 'koffie-test-'.mt_rand(100, 999),
            'description' => 'Test missie',
            'category' => 'kantine',
            'giver_name' => 'Henkie',
            'location_name' => 'Kantine',
            'required_level' => 1,
            'energy_cost' => 5,
            'success_rate' => 100,
            'min_cash_reward' => 100,
            'max_cash_reward' => 100,
            'xp_reward' => 20,
            'respect_reward' => 1,
            'success_message' => 'Gelukt!',
            'sort_order' => 1,
        ]);

        $missionService = app(MissionService::class);
        $result = $missionService->execute($character, $mission);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('isoleercel', $result['message']);
    }

    public function test_plesk_mail_service_does_not_contain_hardcoded_password_constant(): void
    {
        $this->assertSame('', PleskMailService::PRIMARY_PASSWORD);

        $service = new PleskMailService;
        config(['services.plesk.imap_password' => 'ConfiguredSecret123']);
        $this->assertSame('ConfiguredSecret123', $service->getPrimaryPassword());
    }

    public function test_webpush_config_does_not_contain_hardcoded_fallback_private_key(): void
    {
        $this->assertEmpty(config('webpush.vapid.private_key'));
        $this->assertNotSame('XvG4WI43tBp-3HIKTZYbC3hTZdlO2_0t0lN7fEhxSDY', config('webpush.vapid.private_key'));
    }
}
