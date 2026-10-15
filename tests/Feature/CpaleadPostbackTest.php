<?php

namespace Tests\Feature;

use App\Models\Character;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CpaleadPostbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_postback_rejects_missing_lead_id_or_subid(): void
    {
        $response = $this->get('/api/cpalead/postback');
        $response->assertStatus(400);

        $response2 = $this->get('/api/cpalead/postback?lead_id=123');
        $response2->assertStatus(400);
    }

    public function test_postback_credits_character_and_stores_conversion(): void
    {
        $character = Character::first();
        $initialCash = $character->cash;
        $initialTotalAds = $character->ads_watched_total ?? 0;

        $response = $this->get('/api/cpalead/postback?'.http_build_query([
            'subid' => $character->id,
            'subid2' => 'cash',
            'lead_id' => 'cpalead_lead_test_001',
            'payout' => '0.75',
            'campaign_id' => '9988',
            'campaign_name' => 'Survey Test',
            'ip' => '127.0.0.1',
        ]));

        $response->assertOk();
        $response->assertSee('OK');

        $character->refresh();
        $this->assertEquals($initialCash + 2500, $character->cash);
        $this->assertEquals($initialTotalAds + 1, $character->ads_watched_total);

        $this->assertDatabaseHas('cpalead_conversions', [
            'lead_id' => 'cpalead_lead_test_001',
            'character_id' => $character->id,
            'payout' => 0.75,
        ]);
    }

    public function test_postback_duplicate_lead_does_not_credit_twice(): void
    {
        $character = Character::first();
        $initialCash = $character->cash;

        // First call
        $this->get('/api/cpalead/postback?'.http_build_query([
            'subid' => $character->id,
            'lead_id' => 'cpalead_lead_duplicate_test',
            'payout' => '1.00',
        ]))->assertOk();

        $character->refresh();
        $afterFirstCash = $character->cash;
        $this->assertEquals($initialCash + 2500, $afterFirstCash);

        // Second call with same lead_id
        $res2 = $this->get('/api/cpalead/postback?'.http_build_query([
            'subid' => $character->id,
            'lead_id' => 'cpalead_lead_duplicate_test',
            'payout' => '1.00',
        ]));

        $res2->assertOk();
        $res2->assertSee('Duplicate');

        // Cash must NOT have increased again
        $character->refresh();
        $this->assertEquals($afterFirstCash, $character->cash);
    }
}
