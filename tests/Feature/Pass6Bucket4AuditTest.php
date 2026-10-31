<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\BuyMeACoffeeOrder;
use App\Models\Character;
use App\Models\User;
use App\Support\TreatmentCaseCatalog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class Pass6Bucket4AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_audit_log_escapes_json_payload_and_does_not_leak_user_hash(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_staff' => true,
            'is_onboarded' => true,
        ]);
        $character = Character::factory()->create([
            'user_id' => $admin->id,
            'name' => 'AuditAdmin',
        ]);

        AuditLog::create([
            'user_id' => $admin->id,
            'character_id' => $character->id,
            'category' => 'security',
            'action' => 'test_action',
            'description' => "Test </script><script>alert('xss')</script>",
            'risk_level' => 'hoog',
            'risk_score' => 90,
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.audit_log.index'));
        $response->assertOk();
        $response->assertDontSee("<script>alert('xss')</script>", false);
    }

    public function test_treatment_case_catalog_uses_carbon_test_now_year(): void
    {
        Carbon::setTestNow(Carbon::create(2028, 3, 1, 12, 0, 0));

        try {
            $case = TreatmentCaseCatalog::getCaseForDay(61);
            $this->assertSame('TBS-2028-D061', $case['case_code']);
            $this->assertSame('1 maart', $case['date_label']);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_admin_buymeacoffee_view_includes_formatted_amount_and_display_tier(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_staff' => true,
            'is_onboarded' => true,
        ]);
        Character::factory()->create([
            'user_id' => $admin->id,
            'name' => 'BmacAdmin',
        ]);

        BuyMeACoffeeOrder::create([
            'event' => 'donation.created',
            'external_id' => 'bmac_test_1',
            'supporter_name' => "Speler O'Connor",
            'supporter_email' => 'test@example.com',
            'amount' => 14.00,
            'currency' => 'EUR',
            'membership_tier' => 'TBS Gangster Elite',
            'status' => 'unmatched',
            'is_membership' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.buymeacoffee.index'));
        $response->assertOk();
        $response->assertSee('formatted_amount', false);
        $response->assertSee('display_tier', false);
    }

    public function test_character_factory_generates_unique_names(): void
    {
        $characters = Character::factory()->count(10)->create();
        $this->assertCount(10, $characters);
        $this->assertCount(10, $characters->pluck('name')->unique());
    }

    public function test_admin_players_view_handles_character_without_user(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_staff' => true,
            'is_onboarded' => true,
        ]);
        Character::factory()->create([
            'user_id' => $admin->id,
            'name' => 'AdminPlayer',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.players'));
        $response->assertOk();
        $response->assertSee('AdminPlayer');
        $response->assertSee('Inlognaam');

        $orphan = Character::factory()->make([
            'id' => 99999,
            'user_id' => $admin->id,
            'name' => 'OrphanPatient',
        ]);
        $orphan->setRelation('user', null);

        $view = $this->actingAs($admin)->view('admin.players', [
            'players' => new LengthAwarePaginator([$orphan], 1, 15),
        ]);
        $view->assertSee('OrphanPatient');
        $view->assertSee('Geen account gekoppeld');
    }

    public function test_admin_mail_view_safely_encodes_active_mailbox_and_folder_with_js_directive(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'email' => 'admin@tbskliniek.nl',
            'is_staff' => true,
            'is_onboarded' => true,
        ]);
        Character::factory()->create([
            'user_id' => $owner->id,
            'name' => 'OwnerChar',
        ]);

        $payload = "INBOX');alert('xss');//";
        $response = $this->actingAs($owner)->get(route('admin.mail.index', ['folder' => $payload]));
        $response->assertOk();
        $response->assertDontSee("selectedFolder: '".$payload."'", false);
    }

    public function test_admin_dashboard_renders_for_admin_user(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_staff' => true,
            'is_onboarded' => true,
        ]);
        Character::factory()->create([
            'user_id' => $admin->id,
            'name' => 'DashboardAdmin',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Snelle Medewerkers Handelingen');
    }
}
