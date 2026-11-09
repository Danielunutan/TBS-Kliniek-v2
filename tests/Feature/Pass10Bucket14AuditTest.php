<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Tests\TestCase;

class Pass10Bucket14AuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Character $character;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_onboarded' => true]);
        $this->character = Character::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'VIPAudit14',
            'cash' => 500000,
            'bank' => 1000000,
            'stars' => 600,
            'vip_points' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'level' => 12,
            'xp' => 4500,
            'respect' => 2500,
        ]);
    }

    public function test_vip_index_view_renders_cleanly_for_normal_character(): void
    {
        $this->actingAs($this->user);

        $view = view('game.vip.index', [
            'character' => $this->character,
        ])->render();

        $this->assertStringContainsString('TBS V.I.P. & Supporter Lounge', $view);
        $this->assertStringContainsString('TBS-'.$this->character->id, $view);
        $this->assertStringContainsString('copyCode', $view);
        $this->assertStringContainsString('VIP Punten Inwisselen', $view);
        $this->assertStringNotContainsString('Code Blauw). Je kunt geen VIP-beloningen inwisselen', $view);
        $this->assertStringNotContainsString('Je zit momenteel in de isoleercel. Je kunt geen VIP-beloningen', $view);
    }

    public function test_vip_index_view_shows_warning_and_blocks_redeem_when_comatose(): void
    {
        $this->actingAs($this->user);

        $this->character->health = 0;
        $this->character->coma_until = Carbon::now()->addMinutes(15);
        $this->character->save();

        $view = view('game.vip.index', [
            'character' => $this->character,
        ])->render();

        $this->assertStringContainsString('Je ligt momenteel in coma (Code Blauw)', $view);
        $this->assertStringContainsString('disabled', $view);
        $this->assertStringContainsString('cursor-not-allowed', $view);
    }

    public function test_vip_index_view_shows_warning_and_blocks_redeem_when_imprisoned(): void
    {
        $this->actingAs($this->user);

        $this->character->is_in_prison = true;
        $this->character->prison_until = Carbon::now()->addMinutes(20);
        $this->character->save();

        $view = view('game.vip.index', [
            'character' => $this->character,
        ])->render();

        $this->assertStringContainsString('Je zit momenteel in de isoleercel', $view);
        $this->assertStringContainsString('disabled', $view);
        $this->assertStringContainsString('cursor-not-allowed', $view);
    }

    public function test_vip_index_view_safely_handles_cash_protection_dates(): void
    {
        $this->actingAs($this->user);

        $this->character->cash_protection_until = Carbon::now()->addHours(12);
        $this->character->save();

        $view = view('game.vip.index', [
            'character' => $this->character,
        ])->render();

        $this->assertStringContainsString('vip-cash-protection-countdown', $view);
        $this->assertStringContainsString($this->character->cash_protection_until->format('d-m H:i'), $view);

        $this->character->cash_protection_until = null;
        $this->character->save();

        $viewWithoutProtection = view('game.vip.index', [
            'character' => $this->character,
        ])->render();

        $this->assertStringNotContainsString('vip-cash-protection-countdown', $viewWithoutProtection);
    }

    public function test_layouts_app_escapes_stats_and_points_to_valid_news_index(): void
    {
        $this->actingAs($this->user);

        $view = view('layouts.app', [
            'character' => $this->character,
            'activeCategory' => 'algemeen',
        ])->render();

        $this->assertStringContainsString('window.tbsSidebarController', $view);
        $this->assertStringContainsString('algemeen', $view);
        $this->assertStringContainsString((string) Js::from(route('news.index')), $view);
        $this->assertStringNotContainsString('grote-update-eerlijke-spelbalans', $view);
        $this->assertStringContainsString('tbsStatsTracker()', $view);
    }

    public function test_layouts_app_renders_safely_for_guest(): void
    {
        $view = view('layouts.app', [
            'character' => null,
        ])->render();

        $this->assertStringContainsString('x-data="{ mobileMenuOpen: false }"', $view);
        $this->assertStringNotContainsString('x-data="tbsStatsTracker()"', $view);
    }

    public function test_legal_views_render_and_terms_links_to_named_tickets_route(): void
    {
        $this->actingAs($this->user);

        $cookies = view('legal.cookies')->render();
        $this->assertStringContainsString('Cookiebeleid', $cookies);
        $this->assertStringContainsString('tbs_cookie_consent', $cookies);

        $disclaimer = view('legal.disclaimer')->render();
        $this->assertStringContainsString('Disclaimer & Colofon', $disclaimer);
        $this->assertStringContainsString(route('help.tickets.index'), $disclaimer);

        $privacy = view('legal.privacy')->render();
        $this->assertStringContainsString('Privacybeleid', $privacy);
        $this->assertStringContainsString('privacy@tbs-kliniek.nl', $privacy);

        $terms = view('legal.terms')->render();
        $this->assertStringContainsString('Algemene Voorwaarden', $terms);
        $this->assertStringContainsString(route('help.tickets.index'), $terms);
        $this->assertStringNotContainsString('href="/help/tickets"', $terms);
    }

    public function test_maintenance_view_renders_cleanly(): void
    {
        $view = view('maintenance')->render();

        $this->assertStringContainsString('De Kliniekdeuren Zijn Even Gesloten!', $view);
        $this->assertStringContainsString('Tijdelijk in onderhoud voor een volledige en eerlijke reset!', $view);
        $this->assertStringNotContainsString('Uur Vanavond', $view);
    }

    public function test_battlepass_unclaimed_modal_respects_coma_and_prison(): void
    {
        $this->character->level = 5;
        $this->character->perks = ['battle_pass_claims' => []];
        $this->character->save();
        $this->user->refresh();
        $this->actingAs($this->user);

        $this->assertTrue($this->character->getUnclaimedBattlePassCount() > 0);

        // Healthy and free: modal should render
        $viewActive = view('partials.battlepass_unclaimed_modal')->render();
        $this->assertStringContainsString('tbs_bp_popup_dismissed_'.$this->character->id, $viewActive);
        $this->assertStringContainsString('Ongeclaimde Battle Pass Buit!', $viewActive);

        // Comatose: modal suppressed
        $this->character->health = 0;
        $this->character->coma_until = Carbon::now()->addMinutes(15);
        $this->character->save();
        $this->user->refresh();
        $this->actingAs($this->user);

        $viewComa = view('partials.battlepass_unclaimed_modal')->render();
        $this->assertStringNotContainsString('Ongeclaimde Battle Pass Buit!', $viewComa);

        // Imprisoned: modal suppressed
        $this->character->health = 100;
        $this->character->coma_until = null;
        $this->character->is_in_prison = true;
        $this->character->prison_until = Carbon::now()->addMinutes(15);
        $this->character->save();
        $this->user->refresh();
        $this->actingAs($this->user);

        $viewPrison = view('partials.battlepass_unclaimed_modal')->render();
        $this->assertStringNotContainsString('Ongeclaimde Battle Pass Buit!', $viewPrison);
    }

    public function test_bmac_banner_and_instructions_render_with_safe_identifiers(): void
    {
        $this->actingAs($this->user);

        $banner = view('partials.bmac_banner', ['character' => $this->character])->render();
        $this->assertStringContainsString('TBS-'.$this->character->id, $banner);
        $this->assertStringContainsString('Kopieer Code', $banner);
        $this->assertStringContainsString((string) Js::from(route('shop.index', ['tab' => 'donaties'])), $banner);

        $instructions = view('partials.bmac_instructions', ['character' => $this->character])->render();
        $this->assertStringContainsString('TBS-'.$this->character->id, $instructions);
        $this->assertStringContainsString('Hoe koppel je jouw vrijwillige donatie', $instructions);
    }

    public function test_intake_tour_modal_handles_null_and_active_characters(): void
    {
        // Null character should render gracefully with level 1 fallback
        $viewNull = view('partials.intake_tour_modal', [
            'character' => null,
            'rookieData' => ['tour_completed' => false],
        ])->render();

        $this->assertStringContainsString('playerLevel: 1', $viewNull);
        $this->assertStringContainsString((string) Js::from(route('rookie.tour.complete')), $viewNull);

        // Active character should render with their level
        $this->actingAs($this->user);
        $viewActive = view('partials.intake_tour_modal', [
            'character' => $this->character,
            'rookieData' => ['tour_completed' => false],
        ])->render();

        $this->assertStringContainsString('playerLevel: '.$this->character->level, $viewActive);
        $this->assertStringContainsString((string) Js::from(route('rookie.tour.complete')), $viewActive);
    }
}
