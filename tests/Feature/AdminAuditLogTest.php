<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Character;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_audit_log_dashboard(): void
    {
        $staffUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $character = Character::create([
            'user_id' => $staffUser->id,
            'name' => 'AdminSpeler',
            'cash' => 1000,
            'bank' => 5000,
            'level' => 15,
        ]);

        AuditLogService::log(
            action: 'bank_transfer',
            category: 'economy',
            description: 'Verdachte geldoverboeking van €15.000.000 naar speler #99',
            character: $character,
            user: $staffUser,
            amount: 15000000,
            details: ['target_id' => 99]
        );

        $response = $this->actingAs($staffUser)
            ->get(route('admin.audit_log.index'));

        $response->assertStatus(200);
        $response->assertSee('Audit Log');
        $response->assertSee('Verdachte geldoverboeking');
        $response->assertSee('15.000.000');
        $response->assertSee('Hoog');
    }

    public function test_audit_log_risk_filter_works(): void
    {
        $staffUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $character = Character::create([
            'user_id' => $staffUser->id,
            'name' => 'TestPlayer',
            'cash' => 1000,
            'bank' => 5000,
            'level' => 5,
        ]);

        AuditLog::create([
            'user_id' => $staffUser->id,
            'character_id' => $character->id,
            'action' => 'shop_buy',
            'category' => 'shop',
            'description' => 'Frikandelbroodje gekocht',
            'amount' => 15,
            'risk_level' => 'normaal',
            'risk_score' => 5,
        ]);

        AuditLog::create([
            'user_id' => $staffUser->id,
            'character_id' => $character->id,
            'action' => 'bank_transaction',
            'category' => 'economy',
            'description' => 'Massale overboeking',
            'amount' => 20000000,
            'risk_level' => 'hoog',
            'risk_score' => 95,
            'risk_reason' => 'Extreem hoog bedrag',
        ]);

        $response = $this->actingAs($staffUser)
            ->get(route('admin.audit_log.index', ['risk' => 'hoog']));

        $response->assertStatus(200);
        $response->assertSee('Massale overboeking');
        $response->assertDontSee('Frikandelbroodje gekocht');
    }

    public function test_regular_player_cannot_access_audit_log(): void
    {
        $regularUser = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($regularUser)
            ->get(route('admin.audit_log.index'));

        $response->assertStatus(403);
    }
}
