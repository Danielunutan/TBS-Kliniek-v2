<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AuditBatch9SecurityAndLogicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createCharacter(User $user, array $attrs = []): Character
    {
        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Boef_'.rand(1000, 9999),
            'level' => 1,
            'xp' => 0,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 5000,
            'bank' => 10000,
            'stars' => 10,
            'respect' => 10,
            'title' => 'Modelpatiënt',
        ], $attrs));
    }

    /**
     * Test that VIP index page validates and whitelists the tab parameter.
     */
    public function test_vip_index_view_whitelists_active_tab_against_xss(): void
    {
        $user = User::factory()->create();
        $this->createCharacter($user, ['name' => 'VipTester']);

        // Test with XSS injection payload in tab query parameter
        $response = $this->actingAs($user)->get(route('vip.index', ['tab' => '"><script>alert(1)</script>']));
        $response->assertStatus(200);

        // Raw malicious script tag must not be reflected into activeTab
        $response->assertDontSee('activeTab: \'><script>alert(1)</script>\'', false);
        // Defaults safely to whitelisted 'pakketten'
        $response->assertSee("activeTab: 'pakketten'", false);

        // Test with valid tab
        $responseValid = $this->actingAs($user)->get(route('vip.index', ['tab' => 'styling']));
        $responseValid->assertStatus(200);
        $responseValid->assertSee("activeTab: 'styling'", false);
    }

    /**
     * Test that VIP index safely escapes single quotes in character names for clipboard copying.
     */
    public function test_vip_index_escapes_single_quotes_in_character_names(): void
    {
        $user = User::factory()->create();
        $this->createCharacter($user, ['name' => "O'Connor"]);

        $response = $this->actingAs($user)->get(route('vip.index'));
        $response->assertStatus(200);

        // Must not contain raw unescaped single quote breaking JS syntax
        $response->assertDontSee("navigator.clipboard.writeText('O'Connor')", false);
        // Must contain safe unicode-escaped JSON representation
        $response->assertSee("navigator.clipboard.writeText('O\\u0027Connor')", false);
    }

    /**
     * Test that cash protection countdown does not roll over after 24 hours.
     */
    public function test_vip_cash_protection_countdown_formats_over_24_hours(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'name' => 'ProtectedPlayer',
            'cash_protection_until' => Carbon::now()->addHours(26)->addMinutes(15),
        ]);

        $this->assertTrue($character->hasCashProtection());

        $response = $this->actingAs($user)->get(route('vip.index', ['tab' => 'uitgeven']));
        $response->assertStatus(200);

        // If gmdate('H:i:s') was used, 26 hours would roll over to '02:xx:xx'.
        // With our fix, it displays 26 hours in both the digital clock and human description.
        $content = $response->getContent();
        $this->assertMatchesRegularExpression('#26:\d{2}:\d{2} \(26u \d{1,2}m\)#', $content);
    }

    /**
     * Test that layout defines window.tbsShowToast alias and stores sanitized url in toasts.
     */
    public function test_layout_defines_tbs_show_toast_alias_and_sanitizes_urls(): void
    {
        $user = User::factory()->create();
        $this->createCharacter($user);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);

        // window.tbsShowToast must be defined as an alias
        $response->assertSee('window.tbsShowToast = window.tbsToast;', false);
        // Toast safeUrl sanitization logic must be present
        $response->assertSee("!lower.startsWith('javascript:')", false);
    }

    /**
     * Test that visiting /vip does not highlight the shop link simultaneously.
     */
    public function test_visiting_vip_does_not_activate_shop_link_simultaneously(): void
    {
        $user = User::factory()->create();
        $this->createCharacter($user);

        $response = $this->actingAs($user)->get(route('vip.index'));
        $response->assertStatus(200);

        $content = $response->getContent();

        // VIP Shop link must be active
        $this->assertMatchesRegularExpression('#href="[^"]*vip-shop"[^>]*nav-item-active#', $content);

        // The Shop link (shop.index) must NOT be active while on VIP page
        $this->assertDoesNotMatchRegularExpression('#href="[^"]*shop"[^>]*nav-item-active[^>]*VIP &amp; Donaties#', $content);
    }

    /**
     * Test that legal and maintenance views render cleanly without errors.
     */
    public function test_legal_and_maintenance_views_render_cleanly(): void
    {
        $views = [
            'legal.cookies' => route('legal.cookies'),
            'legal.disclaimer' => route('legal.disclaimer'),
            'legal.privacy' => route('legal.privacy'),
            'legal.terms' => route('legal.terms'),
        ];

        foreach ($views as $name => $route) {
            $response = $this->get($route);
            $response->assertStatus(200);
        }

        // Test maintenance view rendering directly
        $maintenanceHtml = view('maintenance')->render();
        $this->assertStringContainsString('Tijdelijk in Onderhoud', $maintenanceHtml);
    }

    /**
     * Test that partials handle toast helper invocation robustly.
     */
    public function test_partials_use_toast_fallback(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user);

        // Render rookie quests widget
        $rookieData = [
            'grand_claimed' => false,
            'completed_count' => 1,
            'total_count' => 5,
            'progress_pct' => 20,
            'tasks' => [
                'task1' => [
                    'icon' => 'fa-dumbbell',
                    'title' => 'Trainen',
                    'desc' => 'Train 1x je kracht',
                    'reward_label' => '+€500',
                    'is_claimed' => false,
                    'can_claim' => true,
                    'route' => route('training.index'),
                    'route_label' => 'Naar Gym',
                ],
            ],
        ];

        $html = view('partials.rookie_quests_widget', ['rookieData' => $rookieData])->render();
        $this->assertStringContainsString('window.tbsToast || window.tbsShowToast', $html);

        // Render bmac banner
        $bmacBannerHtml = view('partials.bmac_banner', ['character' => $character])->render();
        $this->assertStringContainsString('window.tbsToast || window.tbsShowToast', $bmacBannerHtml);

        // Render bmac instructions
        $bmacInstHtml = view('partials.bmac_instructions', ['character' => $character])->render();
        $this->assertStringContainsString('window.tbsToast || window.tbsShowToast', $bmacInstHtml);

        // Render intake tour modal
        $tourHtml = view('partials.intake_tour_modal', ['character' => $character])->render();
        $this->assertStringContainsString('window.tbsToast || window.tbsShowToast', $tourHtml);
    }
}
