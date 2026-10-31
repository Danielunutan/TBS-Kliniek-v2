<?php

namespace Tests\Feature;

use App\Models\BloodFeud;
use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\DirectMessage;
use App\Models\Gang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiquidatieDirectMessageNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_liquidation_sends_direct_messages_to_involved_parties_and_not_to_pleinchat(): void
    {
        // 1. Create attacker and target
        $attackerUser = User::factory()->create(['email' => 'attacker@tbs.test']);
        $attacker = Character::factory()->create([
            'user_id' => $attackerUser->id,
            'name' => 'Scherpschutter',
            'level' => 30,
            'energy' => 100,
            'bullets' => 50,
            'current_location' => 'De Luchtplaats',
            'perks' => ['cold_blooded_pct' => 50],
        ]);

        $targetUser = User::factory()->create(['email' => 'target@tbs.test']);
        $target = Character::factory()->create([
            'user_id' => $targetUser->id,
            'name' => 'DoelwitPiet',
            'level' => 25,
            'health' => 100,
            'cash' => 200000,
            'bank' => 500000,
            'current_location' => 'De Luchtplaats',
            'protection_until' => null,
            'vest_until' => null,
        ]);

        // 2. Clear any prior chat messages or direct messages
        ChatMessage::truncate();
        DirectMessage::truncate();

        // 3. Act: Attacker shoots target with 1-patroon genadeschot
        $this->actingAs($attackerUser);
        $response = $this->post(route('hitman.shoot'), [
            'target_id' => $target->id,
            'bullets' => 1,
        ]);
        $response->assertSessionHas('success');

        // 4. Assert: No message was broadcasted on the public Kliniekplein (chat_messages)
        $this->assertEquals(
            0,
            ChatMessage::where('channel', 'global')->count(),
            'Liquidatieberichten mogen niet langer op het Kliniekplein worden geplaatst.'
        );

        // 5. Assert: Both attacker and defender received private messages from Kliniek Directie
        $attackerDms = DirectMessage::where('recipient_id', $attacker->id)
            ->whereNull('sender_id')
            ->where('type', 'liquidation')
            ->get();
        $this->assertCount(1, $attackerDms);
        $this->assertStringContainsString('🎯 LIQUIDATIE OP De Luchtplaats:', $attackerDms->first()->message);
        $this->assertStringContainsString('Scherpschutter', $attackerDms->first()->message);
        $this->assertStringContainsString('DoelwitPiet', $attackerDms->first()->message);

        $targetDms = DirectMessage::where('recipient_id', $target->id)
            ->whereNull('sender_id')
            ->where('type', 'liquidation')
            ->get();
        $this->assertCount(1, $targetDms);
        $this->assertStringContainsString('🎯 LIQUIDATIE OP De Luchtplaats:', $targetDms->first()->message);
        $this->assertStringContainsString('Scherpschutter', $targetDms->first()->message);
        $this->assertStringContainsString('DoelwitPiet', $targetDms->first()->message);
    }

    public function test_avenged_blood_feud_sends_feud_and_liquidation_dms_to_involved_parties_without_broadcast(): void
    {
        // 1. Create shooter, target, and an original victim from a blood feud
        $gang = Gang::factory()->create(['name' => 'De Bloedbroeders']);

        $shooterUser = User::factory()->create(['email' => 'avenger@tbs.test']);
        $shooter = Character::factory()->create([
            'user_id' => $shooterUser->id,
            'name' => 'Wreker',
            'gang_id' => $gang->id,
            'level' => 30,
            'energy' => 100,
            'bullets' => 50,
            'current_location' => 'Het Kookhuis (Keuken)',
            'perks' => ['cold_blooded_pct' => 50],
        ]);

        $victimUser = User::factory()->create(['email' => 'victim@tbs.test']);
        $originalVictim = Character::factory()->create([
            'user_id' => $victimUser->id,
            'name' => 'OudSlachtoffer',
            'gang_id' => $gang->id,
            'level' => 20,
        ]);

        $targetUser = User::factory()->create(['email' => 'feudtarget@tbs.test']);
        $target = Character::factory()->create([
            'user_id' => $targetUser->id,
            'name' => 'VeteDoelwit',
            'level' => 25,
            'health' => 100,
            'cash' => 100000,
            'bank' => 200000,
            'current_location' => 'Het Kookhuis (Keuken)',
            'protection_until' => null,
            'vest_until' => null,
        ]);

        // 2. Active blood feud where target killed originalVictim, and shooter's gang has claim
        $bloodFeud = BloodFeud::create([
            'attacker_id' => $target->id,
            'victim_id' => $originalVictim->id,
            'victim_gang_id' => $gang->id,
            'expires_at' => now()->addHours(24),
            'is_avenged' => false,
        ]);

        ChatMessage::truncate();
        DirectMessage::truncate();

        // 3. Act: Shooter avenges blood feud by liquidating target
        $this->actingAs($shooterUser);
        $response = $this->post(route('hitman.shoot'), [
            'target_id' => $target->id,
            'bullets' => 1,
        ]);
        $response->assertSessionHas('success');

        // 4. Assert: No broadcast in chat_messages
        $this->assertEquals(0, ChatMessage::count());

        // 5. Assert: Blood feud DMs received by shooter, target, and the original victim
        foreach ([$shooter->id, $target->id, $originalVictim->id] as $characterId) {
            $hasFeudDm = DirectMessage::where('recipient_id', $characterId)
                ->whereNull('sender_id')
                ->where('message', 'like', '%🩸 BLOEDWRAAK VOLTROKKEN%')
                ->exists();
            $this->assertTrue($hasFeudDm, "Betrokkene met ID {$characterId} moet het bloedwraak privébericht ontvangen.");
        }
    }

    public function test_migration_deletes_existing_liquidation_messages_from_pleinchat(): void
    {
        $character = Character::first();

        // 1. Insert existing messages into chat_messages
        $msgLiquidation = ChatMessage::create([
            'character_id' => $character->id,
            'channel' => 'global',
            'message' => '🎯 LIQUIDATIE OP Paviljoen 1: Karel heeft Piet neergeschoten met een salvo!',
        ]);

        $msgFeud = ChatMessage::create([
            'character_id' => $character->id,
            'channel' => 'global',
            'message' => '🩸 BLOEDWRAAK VOLTROKKEN: Karel heeft Piet op Paviljoen 1 geliquideerd en de bende-vete gewroken!',
        ]);

        $msgLegacy = ChatMessage::create([
            'character_id' => $character->id,
            'channel' => 'global',
            'message' => '🚨 NOODGEVAL OP DE AFDELING: Karel heeft Piet zojuist neergeschoten!',
        ]);

        $msgNormalPlein = ChatMessage::create([
            'character_id' => $character->id,
            'channel' => 'global',
            'message' => 'Gezellig op het Kliniekplein vanavond!',
        ]);

        $msgGang = ChatMessage::create([
            'character_id' => $character->id,
            'channel' => 'gang_1',
            'message' => 'Overleg over de bende liquidatie strategie.',
        ]);

        // 2. Act: Run the migration's up logic
        $migration = require database_path('migrations/2026_10_31_232702_delete_liquidation_messages_from_pleinchat.php');
        $migration->up();

        // 3. Assert: Liquidation messages on pleinchat are deleted
        $this->assertDatabaseMissing('chat_messages', ['id' => $msgLiquidation->id]);
        $this->assertDatabaseMissing('chat_messages', ['id' => $msgFeud->id]);
        $this->assertDatabaseMissing('chat_messages', ['id' => $msgLegacy->id]);

        // Normal pleinchat and non-pleinchat messages remain preserved
        $this->assertDatabaseHas('chat_messages', ['id' => $msgNormalPlein->id]);
        $this->assertDatabaseHas('chat_messages', ['id' => $msgGang->id]);
    }
}
