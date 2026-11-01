<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\NewsArticle;
use App\Models\Referral;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class Pass10Bucket7AuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Character $adminCharacter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'owner',
            'is_onboarded' => true,
        ]);

        $this->adminCharacter = Character::factory()->create([
            'user_id' => $this->adminUser->id,
            'name' => 'DirecteurDaniël',
            'cash' => 5000000,
            'bank' => 20000000,
            'stars' => 500,
            'level' => 30,
            'strength' => 500,
            'defense' => 500,
            'stamina' => 500,
            'energy' => 100,
            'respect' => 10000,
        ]);
    }

    public function test_admin_news_views_contain_noopener_noreferrer(): void
    {
        $article = NewsArticle::create([
            'title' => 'Grote Kliniek Razzia',
            'slug' => 'grote-kliniek-razzia',
            'summary' => 'De zaalwacht heeft alle cellen doorzocht.',
            'content' => 'Er zijn [url=https://example.com]externe links[/url] gevonden.',
            'published_at' => now(),
            'is_published' => true,
            'author_id' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.news.index'));

        $response->assertStatus(200);
        $response->assertSee('rel="noopener noreferrer"', false);

        $editResponse = $this->actingAs($this->adminUser)
            ->get(route('admin.news.edit', $article));

        $editResponse->assertStatus(200);
        $editResponse->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_admin_referrals_view_handles_null_user_defensively(): void
    {
        $refMock = new Referral([
            'ip_address' => '127.0.0.1',
            'rewarded' => false,
        ]);
        $refMock->id = 1;
        $refMock->created_at = now();

        $referredChar = new Character(['name' => 'WeesPatiënt']);
        $referredChar->setRelation('user', null);
        $refMock->setRelation('referred', $referredChar);
        $refMock->setRelation('referrer', $this->adminCharacter);

        $paginator = new LengthAwarePaginator(
            collect([$refMock]),
            1,
            30,
            1
        );

        $view = view('admin.referrals', [
            'referrals' => $paginator,
            'totalReferrals' => 1,
            'rewardedCount' => 0,
            'totalVipMonthsGranted' => 0,
            'unrewardedCount' => 1,
            'topReferrers' => collect([]),
        ])->render();

        $this->assertStringContainsString('WeesPatiënt', $view);
        $this->assertStringContainsString('-', $view);
    }

    public function test_admin_vault_view_handles_missing_settings_keys(): void
    {
        $view = view('admin.vault', [
            'settings' => [],
            'recentCodes' => collect([]),
        ])->render();

        $this->assertStringContainsString('0000', $view);
        $this->assertStringContainsString('vault_reward_cash', $view);
    }

    public function test_onboarding_view_and_age_verification_validation(): void
    {
        $newUser = User::factory()->create([
            'is_onboarded' => false,
        ]);
        $newChar = Character::factory()->create([
            'user_id' => $newUser->id,
            'birthdate' => Carbon::now()->subYears(20),
        ]);

        $response = $this->actingAs($newUser)
            ->get(route('onboarding.index'));

        $response->assertStatus(200);
        $response->assertSee('Patiëntendossier Voltooien');
        $response->assertSee('calculatedAge');
        $response->assertSee('today.getFullYear() - dob.getFullYear()', false);
    }

    public function test_auth_reset_password_view_handles_missing_email_or_token(): void
    {
        $view = view('auth.reset-password', [
            'token' => null,
            'email' => null,
        ])->render();

        $this->assertStringContainsString('Nieuw Wachtwoord Kiezen', $view);
        $this->assertStringContainsString('name="token" value=""', $view);
    }

    public function test_errors_banned_view_handles_unauthenticated_or_missing_user(): void
    {
        $view = view('errors.banned', [
            'user' => null,
        ])->render();

        $this->assertStringContainsString('Toegang Ontzegd tot de Kliniek', $view);
        $this->assertStringContainsString('ID #?', $view);
        $this->assertStringContainsString('Definitief / Permanent', $view);
    }

    public function test_armwrestling_view_renders_comatose_and_imprisoned_guards(): void
    {
        // 1. Normal state
        $response = $this->actingAs($this->adminUser)
            ->get(route('armwrestling.index'));
        $response->assertStatus(200);
        $response->assertSee('De Illegale Armpjedruk Arena');
        $response->assertSee('Tafelduel Aangaan');

        // 2. Comatose state
        $this->adminCharacter->update([
            'coma_until' => Carbon::now()->addMinutes(30),
        ]);
        $this->adminUser->unsetRelation('character');

        $comatoseResponse = $this->actingAs($this->adminUser)
            ->get(route('armwrestling.index'));
        $comatoseResponse->assertStatus(200);
        $comatoseResponse->assertSee('Code Blauw (Geen Armworstelen)');

        // 3. Imprisoned state
        $this->adminCharacter->update([
            'coma_until' => null,
            'prison_until' => Carbon::now()->addMinutes(30),
        ]);
        $this->adminUser->unsetRelation('character');

        $jailResponse = $this->actingAs($this->adminUser)
            ->get(route('armwrestling.index'));
        $jailResponse->assertStatus(200);
        $jailResponse->assertSee('Isoleercel (Geen Toegang)');
    }

    public function test_bank_view_renders_comatose_and_imprisoned_warning_and_disables_forms(): void
    {
        // 1. Normal state
        $response = $this->actingAs($this->adminUser)
            ->get(route('bank.index'));
        $response->assertStatus(200);
        $response->assertSee('Geldmaat TBS');

        // 2. Comatose state
        $this->adminCharacter->update([
            'coma_until' => Carbon::now()->addMinutes(30),
        ]);
        $this->adminUser->unsetRelation('character');

        $comatoseResponse = $this->actingAs($this->adminUser)
            ->get(route('bank.index'));
        $comatoseResponse->assertStatus(200);
        $comatoseResponse->assertSee('Code Blauw (Hartstilstand)');
        $comatoseResponse->assertSee('Code Blauw (Geen Geldmaat)');

        // 3. Imprisoned state
        $this->adminCharacter->update([
            'coma_until' => null,
            'prison_until' => Carbon::now()->addMinutes(30),
        ]);
        $this->adminUser->unsetRelation('character');

        $jailResponse = $this->actingAs($this->adminUser)
            ->get(route('bank.index'));
        $jailResponse->assertStatus(200);
        $jailResponse->assertSee('Opgesloten in Isoleercel');
        $jailResponse->assertSee('Isoleercel (Geen Geldmaat)');
    }
}
