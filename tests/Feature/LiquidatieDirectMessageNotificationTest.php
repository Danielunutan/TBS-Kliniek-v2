<?php

namespace Tests\Feature;

use App\Models\BloodFeud;
use App\Models\Bounty;
use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\DirectMessage;
use App\Models\GameNotification;
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

        // 5. Assert: Both attacker and defender received exactly 1 private message from Kliniek Directie (no duplicate 'skull' DM)
        $this->assertEquals(1, DirectMessage::where('recipient_id', $attacker->id)->whereNull('sender_id')->count());
        $attackerDms = DirectMessage::where('recipient_id', $attacker->id)
            ->whereNull('sender_id')
            ->where('type', 'liquidation')
            ->get();
        $this->assertCount(1, $attackerDms);
        $this->assertStringContainsString('🎯 LIQUIDATIE OP De Luchtplaats:', $attackerDms->first()->message);
        $this->assertStringContainsString('Scherpschutter', $attackerDms->first()->message);
        $this->assertStringContainsString('DoelwitPiet', $attackerDms->first()->message);

        $this->assertEquals(1, DirectMessage::where('recipient_id', $target->id)->whereNull('sender_id')->count());
        $targetDms = DirectMessage::where('recipient_id', $target->id)
            ->whereNull('sender_id')
            ->where('type', 'liquidation')
            ->get();
        $this->assertCount(1, $targetDms);
        $this->assertStringContainsString('🎯 LIQUIDATIE OP De Luchtplaats:', $targetDms->first()->message);
        $this->assertStringContainsString('Scherpschutter', $targetDms->first()->message);
        $this->assertStringContainsString('DoelwitPiet', $targetDms->first()->message);
        $this->assertStringContainsString('Medische Status', $targetDms->first()->message);
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

        // 5. Assert: Unified blood feud + liquidation DM received by shooter, target, and the original victim (exactly 1 per person)
        foreach ([$shooter->id, $target->id, $originalVictim->id] as $characterId) {
            $dms = DirectMessage::where('recipient_id', $characterId)
                ->whereNull('sender_id')
                ->where('type', 'liquidation')
                ->get();
            $this->assertCount(1, $dms, "Betrokkene met ID {$characterId} moet precies 1 gecombineerd liquidatie- en bloedwraakrapport ontvangen.");
            $this->assertStringContainsString('🎯 LIQUIDATIE OP Het Kookhuis (Keuken):', $dms->first()->message);
            $this->assertStringContainsString('🩸 BLOEDWRAAK VOLTROKKEN', $dms->first()->message);
        }
    }

    public function test_bounty_claim_on_liquidation_notifies_bounty_placer_via_direct_message(): void
    {
        $placerUser = User::factory()->create(['email' => 'placer@tbs.test']);
        $placer = Character::factory()->create([
            'user_id' => $placerUser->id,
            'name' => 'Opdrachtgever',
            'level' => 25,
        ]);

        $shooterUser = User::factory()->create(['email' => 'bountyhunter@tbs.test']);
        $shooter = Character::factory()->create([
            'user_id' => $shooterUser->id,
            'name' => 'PremieJager',
            'level' => 30,
            'energy' => 100,
            'bullets' => 50,
            'current_location' => 'De Luchtplaats',
            'perks' => ['cold_blooded_pct' => 50],
        ]);

        $targetUser = User::factory()->create(['email' => 'bountytarget@tbs.test']);
        $target = Character::factory()->create([
            'user_id' => $targetUser->id,
            'name' => 'GezochtDoelwit',
            'level' => 25,
            'health' => 100,
            'cash' => 100000,
            'bank' => 100000,
            'current_location' => 'De Luchtplaats',
            'protection_until' => null,
            'vest_until' => null,
        ]);

        Bounty::create([
            'target_character_id' => $target->id,
            'placed_by_character_id' => $placer->id,
            'amount' => 75000,
            'reason' => 'Verraad',
            'is_claimed' => false,
        ]);

        ChatMessage::truncate();
        DirectMessage::truncate();

        $this->actingAs($shooterUser);
        $response = $this->post(route('hitman.shoot'), [
            'target_id' => $target->id,
            'bullets' => 1,
        ]);
        $response->assertSessionHas('success');

        $placerDms = DirectMessage::where('recipient_id', $placer->id)
            ->whereNull('sender_id')
            ->where('type', 'liquidation')
            ->get();
        $this->assertCount(1, $placerDms);
        $this->assertStringContainsString('🎯 LIQUIDATIE OP De Luchtplaats:', $placerDms->first()->message);
        $this->assertStringContainsString('💰 KOPGELD GEÏND:', $placerDms->first()->message);
        $this->assertStringContainsString('€75.000', $placerDms->first()->message);
    }

    public function test_migration_deletes_existing_liquidation_messages_from_pleinchat_and_preserves_for_involved_parties(): void
    {
        $user1 = User::factory()->create(['email' => 'karel@tbs.test']);
        $karel = Character::factory()->create(['user_id' => $user1->id, 'name' => 'Karel']);

        $user2 = User::factory()->create(['email' => 'piet@tbs.test']);
        $piet = Character::factory()->create(['user_id' => $user2->id, 'name' => 'Piet']);

        // 1. Insert existing messages into chat_messages
        $msgLiquidation = ChatMessage::create([
            'character_id' => $karel->id,
            'channel' => 'global',
            'message' => '🎯 LIQUIDATIE OP Paviljoen 1: Karel heeft Piet neergeschoten met een salvo!',
        ]);

        $msgFeud = ChatMessage::create([
            'character_id' => $karel->id,
            'channel' => 'global',
            'message' => '🩸 BLOEDWRAAK VOLTROKKEN: Karel heeft Piet op Paviljoen 1 geliquideerd en de bende-vete gewroken!',
        ]);

        $msgLegacy = ChatMessage::create([
            'character_id' => $karel->id,
            'channel' => 'global',
            'message' => '🚨 NOODGEVAL OP DE AFDELING: Karel heeft Piet zojuist neergeschoten!',
        ]);

        $msgNormalPlein = ChatMessage::create([
            'character_id' => $karel->id,
            'channel' => 'global',
            'message' => 'Gezellig op het Kliniekplein vanavond!',
        ]);

        $msgGang = ChatMessage::create([
            'character_id' => $karel->id,
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

        // 4. Assert: Both Karel (attacker) and Piet (defender) can still see the old messages in their private directie box
        $this->assertTrue(
            DirectMessage::where('recipient_id', $karel->id)
                ->whereNull('sender_id')
                ->where('type', 'liquidation')
                ->where('message', 'like', '%Karel heeft Piet neergeschoten%')
                ->exists(),
            'Karel (schutter) moet het oude liquidatiebericht nog kunnen zien in zijn privégesprek met de Kliniekdirectie.'
        );

        $this->assertTrue(
            DirectMessage::where('recipient_id', $piet->id)
                ->whereNull('sender_id')
                ->where('type', 'liquidation')
                ->where('message', 'like', '%Karel heeft Piet neergeschoten%')
                ->exists(),
            'Piet (slachtoffer) moet het oude liquidatiebericht nog kunnen zien in zijn privégesprek met de Kliniekdirectie.'
        );

        // 5. Normal pleinchat and non-pleinchat messages remain preserved
        $this->assertDatabaseHas('chat_messages', ['id' => $msgNormalPlein->id]);
        $this->assertDatabaseHas('chat_messages', ['id' => $msgGang->id]);
    }

    public function test_sync_migration_reconstructs_historical_liquidations_from_skull_notifications_when_chat_messages_already_deleted(): void
    {
        $user1 = User::factory()->create(['email' => 'hist_attacker@tbs.test']);
        $attacker = Character::factory()->create(['user_id' => $user1->id, 'name' => 'HistorischeSchutter']);

        $user2 = User::factory()->create(['email' => 'hist_victim@tbs.test']);
        $victim = Character::factory()->create(['user_id' => $user2->id, 'name' => 'HistorischSlachtoffer']);

        DirectMessage::truncate();
        GameNotification::truncate();

        $pastTime = now()->subDays(2);

        // Simulate a historical GameNotification (type 'skull') + legacy DirectMessage (type 'skull') where chat_messages were already deleted earlier
        $notif = GameNotification::withoutEvents(function () use ($victim, $pastTime) {
            $n = new GameNotification([
                'character_id' => $victim->id,
                'type' => 'skull',
                'icon' => 'fa-skull',
                'title' => '🚨 GELIQUIDEERD OP De Luchtplaats (2/10)!',
                'message' => 'HistorischeSchutter heeft je op De Luchtplaats (+25% Sluipaanval-bonus) geliquideerd met een 1-Patroon Genadeschot! Er is €45.000 contant (45%) + €7.000 bank (7%) geplunderd. Je bent met 20 HP per brancard geëvacueerd naar Paviljoen 1 (Gesloten) met 15 min Medisch Infuus-Schild en kunt direct eten om te healen!',
                'is_read' => true,
            ]);
            $n->created_at = $pastTime;
            $n->updated_at = $pastTime;
            $n->save();

            return $n;
        });

        DirectMessage::create([
            'sender_id' => null,
            'recipient_id' => $victim->id,
            'type' => 'skull',
            'message' => "📢 **{$notif->title}**\n\n{$notif->message}",
            'metadata' => ['game_notification_id' => $notif->id],
            'is_read' => true,
            'created_at' => $pastTime,
            'updated_at' => $pastTime,
        ]);

        // Run the sync migration
        $syncMigration = require database_path('migrations/2026_10_31_235500_sync_and_upgrade_liquidation_direct_messages.php');
        $syncMigration->up();

        // Victim's 'skull' DM was upgraded in-place to 'liquidation' without duplicates
        $victimDms = DirectMessage::where('recipient_id', $victim->id)->whereNull('sender_id')->get();
        $this->assertCount(1, $victimDms);
        $this->assertEquals('liquidation', $victimDms->first()->type);
        $this->assertStringContainsString('🎯 LIQUIDATIE OP De Luchtplaats: HistorischeSchutter heeft HistorischSlachtoffer neergeschoten met een 1-Patroon Genadeschot en €52.000 (45% contant + 7% bank) buitgemaakt!', $victimDms->first()->message);

        // Attacker received the reconstructed 'liquidation' DM with the historical timestamp preserved
        $attackerDms = DirectMessage::where('recipient_id', $attacker->id)->whereNull('sender_id')->get();
        $this->assertCount(1, $attackerDms);
        $this->assertEquals('liquidation', $attackerDms->first()->type);
        $this->assertStringContainsString('🎯 LIQUIDATIE OP De Luchtplaats: HistorischeSchutter heeft HistorischSlachtoffer neergeschoten met een 1-Patroon Genadeschot en €52.000 (45% contant + 7% bank) buitgemaakt!', $attackerDms->first()->message);
        $this->assertEquals($pastTime->timestamp, $attackerDms->first()->created_at->timestamp);
    }
}
