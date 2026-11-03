<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NameStylingAndChatVipBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_personalization_saves_name_color_gradient_glow_and_sparkle(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'Daniël',
            'level' => 10,
            'cash' => 5000,
            'bank' => 10000,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'is_vip' => true,
            'vip_expires_at' => now()->addDays(30),
            'perks' => [
                'supporter_tier' => 3,
            ],
        ]);

        $response = $this->actingAs($user)->post(route('profile.customize'), [
            'name_style_submitted' => 1,
            'custom_name_color' => '#a855f7',
            'custom_name_gradient' => 'cyber_neon',
            'custom_name_glow' => 'neon',
            'sparkle_enabled' => 1,
        ]);

        $response->assertRedirect();
        $character->refresh();

        $this->assertSame('#a855f7', $character->getCustomNameColor());
        $this->assertSame('cyber_neon', $character->getCustomNameGradient());
        $this->assertSame('neon', $character->getCustomNameGlow());
        $this->assertTrue($character->isSparkleActive());

        $classes = $character->getNameCssClasses();
        $this->assertStringContainsString('tbs-name-gradient', $classes);
        $this->assertStringContainsString('tbs-name-shimmer', $classes);
        $this->assertStringContainsString('tbs-name-glow-neon', $classes);

        $style = $character->getNameInlineStyle();
        $this->assertStringContainsString('--tbs-name-gradient:', $style);
        $this->assertStringContainsString('--tbs-glow-color:', $style);
    }

    public function test_chat_messages_include_vip_badge_and_name_styling(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'SyndicateKing',
            'level' => 15,
            'cash' => 5000,
            'bank' => 10000,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'is_vip' => true,
            'vip_expires_at' => now()->addDays(30),
            'perks' => [
                'supporter_tier' => 3,
                'custom_name_gradient' => 'syndicate',
                'custom_name_glow' => 'pulse',
                'sparkle_enabled' => true,
            ],
        ]);

        $sendResponse = $this->actingAs($user)->postJson(route('chat.public.send'), [
            'channel' => 'global',
            'message' => 'Test bericht met VIP badge en gradient!',
        ]);

        $sendResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message.is_vip', true)
            ->assertJsonPath('message.vip_tier', 3)
            ->assertJsonPath('message.vip_badge_label', 'Tier 3 Baas')
            ->assertJsonPath('message.name_gradient', 'syndicate')
            ->assertJsonPath('message.name_glow', 'pulse')
            ->assertJsonPath('message.has_sparkle', true);

        $fetchResponse = $this->actingAs($user)->getJson(route('chat.public.messages', ['channel' => 'global']));
        $fetchResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('messages.0.vip_badge_label', 'Tier 3 Baas')
            ->assertJsonPath('messages.0.name_gradient', 'syndicate')
            ->assertJsonPath('messages.0.name_glow', 'pulse');
    }

    public function test_lifetime_vip_badge_attributes_and_dark_mode_classes(): void
    {
        $character = new Character([
            'is_vip' => true,
            'vip_expires_at' => now()->addYears(10),
            'perks' => ['supporter_tier' => 4],
        ]);

        $badge = $character->vip_badge;
        $this->assertNotNull($badge);
        $this->assertEquals(4, $badge['tier']);
        $this->assertEquals('Lifetime V.I.P.', $badge['label']);
        $this->assertStringContainsString('text-slate-950', $badge['class']);
        $this->assertStringContainsString('dark:text-slate-950', $badge['class']);
    }
}
