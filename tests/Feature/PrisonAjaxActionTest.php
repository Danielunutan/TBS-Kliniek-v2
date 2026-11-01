<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use App\Services\FlashNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrisonAjaxActionTest extends TestCase
{
    use RefreshDatabase;

    protected function createInmate(array $attrs = []): Character
    {
        $user = User::factory()->create();

        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Inmate_'.uniqid(),
            'level' => 5,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 50000,
            'stars' => 50,
            'is_in_prison' => false,
            'prison_until' => null,
            'prison_reason' => null,
            'prison_escapes' => 0,
            'respect' => 10,
        ], $attrs));
    }

    public function test_breakout_via_ajax_returns_same_page_json_with_flash_and_stats(): void
    {
        $actor = $this->createInmate(['energy' => 50, 'is_in_prison' => false]);
        $target = $this->createInmate(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);

        $response = $this->actingAs($actor->user)->withHeaders([
            'X-TBS-Action' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
            'Referer' => route('prison'),
        ])->post(route('prison.breakout', $target->id), [
            'method' => 'stil',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'same_page',
            'flash' => [
                'type',
                'message',
            ],
            'character' => [
                'energy',
                'cash',
                'respect',
            ],
        ]);

        $data = $response->json();
        $this->assertTrue($data['same_page']);
        $this->assertNotEmpty($data['flash']['message']);
    }

    public function test_laundry_reduce_sentence_via_ajax_returns_same_page_json(): void
    {
        $actor = $this->createInmate([
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(5),
            'energy' => 50,
        ]);

        $response = $this->actingAs($actor->user)->withHeaders([
            'X-TBS-Action' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
            'Referer' => route('prison'),
        ])->post(route('prison.reduce'), [
            'action' => 'wasserij',
        ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['same_page']);
        $this->assertEquals('success', $data['flash']['type']);
        $this->assertStringContainsString('wasserij', $data['flash']['message']);
    }

    public function test_buyout_via_ajax_returns_same_page_json(): void
    {
        $actor = $this->createInmate([
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(5),
            'stars' => 30,
        ]);

        $response = $this->actingAs($actor->user)->withHeaders([
            'X-TBS-Action' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
            'Referer' => route('prison'),
        ])->post(route('prison.buyout'));

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['same_page']);
        $this->assertEquals('success', $data['flash']['type']);
        $this->assertStringContainsString('Vrijkoopakte', $data['flash']['message']);
        $this->assertFalse($actor->fresh()->is_in_prison);
    }

    public function test_failed_escape_via_ajax_returns_same_page_json_with_error_flash(): void
    {
        $actor = $this->createInmate([
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(5),
            'prison_escape_attempted' => true, // will immediately fail
            'energy' => 50,
        ]);

        $response = $this->actingAs($actor->user)->withHeaders([
            'X-TBS-Action' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
            'Referer' => route('prison'),
        ])->post(route('prison.escape'));

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['same_page']);
        $this->assertEquals('error', $data['flash']['type']);
        $this->assertStringContainsString('al verbruikt', $data['flash']['message']);
    }

    public function test_navigating_away_to_prison_from_other_page_persists_flash(): void
    {
        $actor = $this->createInmate(['is_in_prison' => false, 'energy' => 50]);

        // Breakout failure that sends user to prison when referer was another page
        $target = $this->createInmate(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);

        // Breakout attempt when user was on /dashboard and got jailed
        // Breakout redirects to prison on fail
        $response = $this->actingAs($actor->user)->withHeaders([
            'X-TBS-Action' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
            'Referer' => route('dashboard'),
        ])->post(route('prison.breakout', $target->id), [
            'method' => 'geweld',
        ]);

        $response->assertOk();
        $data = $response->json();

        // Either succeeded or jailed, but since referer was dashboard and target was prison / back,
        // if navigating away, flash must be stored in FlashNotificationService!
        if (isset($data['redirect'])) {
            $this->assertNotEmpty($data['flash']['message']);
            $resilient = app(FlashNotificationService::class)->consume($actor->user_id);
            $this->assertNotNull($resilient);
            $this->assertEquals($data['flash']['message'], $resilient['message']);
        } else {
            $this->assertTrue($data['same_page']);
        }
    }

    public function test_prison_index_page_disables_cache_headers(): void
    {
        $actor = $this->createInmate(['is_in_prison' => false]);

        $response = $this->actingAs($actor->user)->get(route('prison'));

        $response->assertOk();
        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
    }
}
