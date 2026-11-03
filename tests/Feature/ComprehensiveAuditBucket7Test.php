<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Item;
use App\Models\Loan;
use App\Models\NewsArticle;
use App\Models\NewsComment;
use App\Models\User;
use App\Models\VaultCode;
use App\Services\CooldownService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ComprehensiveAuditBucket7Test extends TestCase
{
    use RefreshDatabase;

    private function createInmate(array $attributes = [], bool $isStaff = false): array
    {
        $user = User::factory()->create([
            'role' => $isStaff ? 'admin' : 'player',
            'is_onboarded' => true,
        ]);

        $character = Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Inmate_'.rand(10000, 99999),
            'cash' => 5000,
            'bank' => 20000,
            'stars' => 100,
            'level' => 5,
            'xp' => 100,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 50,
            'masks' => 5,
            'vault_tokens' => 10,
            'is_in_prison' => false,
            'prison_until' => null,
            'coma_until' => null,
            'last_active_at' => now(),
        ], $attributes));

        return [$user, $character];
    }

    public function test_character_add_cash_and_take_cash_methods(): void
    {
        [$user, $character] = $this->createInmate(['cash' => 1000]);

        // 1. Add cash positive
        $character->addCash(500);
        $this->assertEquals(1500, $character->fresh()->cash);

        // 2. Add cash zero (no-op)
        $character->addCash(0);
        $this->assertEquals(1500, $character->fresh()->cash);

        // 3. Add cash negative throws exception
        $this->expectException(InvalidArgumentException::class);
        $character->addCash(-50);
    }

    public function test_character_take_cash_validates_balance_and_negative(): void
    {
        [$user, $character] = $this->createInmate(['cash' => 1000]);

        // 1. Successful deduction
        $result = $character->takeCash(400);
        $this->assertTrue($result);
        $this->assertEquals(600, $character->fresh()->cash);

        // 2. Insufficient balance deduction
        $resultFail = $character->takeCash(99999);
        $this->assertFalse($resultFail);
        $this->assertEquals(600, $character->fresh()->cash);

        // 3. Zero deduction succeeds
        $this->assertTrue($character->takeCash(0));

        // 4. Negative deduction throws exception
        $this->expectException(InvalidArgumentException::class);
        $character->takeCash(-100);
    }

    public function test_character_add_respect_safeguards(): void
    {
        [$user, $character] = $this->createInmate(['respect' => 100]);

        // Positive respect
        $character->addRespect(25);
        $this->assertEquals(125, $character->respect);

        // Non-positive or invalid respect ignores
        $character->addRespect(0);
        $this->assertEquals(125, $character->respect);

        $character->addRespect(-50);
        $this->assertEquals(125, $character->respect);

        $character->addRespect(NAN);
        $this->assertEquals(125, $character->respect);

        $character->addRespect(INF);
        $this->assertEquals(125, $character->respect);
    }

    public function test_character_has_border_and_unlock_border(): void
    {
        [$user, $character] = $this->createInmate([
            'unlocked_borders' => null,
        ]);

        $this->assertFalse($character->hasBorder('autumn_ember'));

        $character->unlockBorder('autumn_ember');
        $character->save();

        $this->assertTrue($character->fresh()->hasBorder('autumn_ember'));
    }

    public function test_character_is_comatose_and_needs_revive_serum(): void
    {
        [$user, $character] = $this->createInmate([
            'coma_until' => null,
        ]);

        $this->assertFalse($character->isComatose());
        $this->assertFalse($character->needsReviveSerum());

        $character->coma_until = now()->addHours(2);
        $character->save();

        $this->assertTrue($character->fresh()->isComatose());
        $this->assertTrue($character->fresh()->needsReviveSerum());
    }

    public function test_bank_deposit_and_withdraw_validation_and_guards(): void
    {
        [$user, $character] = $this->createInmate([
            'cash' => 5000,
            'bank' => 10000,
        ]);

        // 1. Successful deposit
        $response = $this->actingAs($user)->post(route('bank.deposit'), [
            'amount' => 2000,
        ]);
        $response->assertRedirect();
        $this->assertEquals(3000, $character->fresh()->cash);
        $this->assertEquals(12000, $character->fresh()->bank);

        // 2. Deposit blocked when comatose
        $character->update(['coma_until' => now()->addHour()]);
        $responseComa = $this->actingAs($user)->post(route('bank.deposit'), [
            'amount' => 500,
        ]);
        $responseComa->assertSessionHas('error');

        // 3. Deposit blocked when imprisoned
        $character->update(['coma_until' => null, 'is_in_prison' => true, 'prison_until' => now()->addHour()]);
        $responsePrison = $this->actingAs($user)->post(route('bank.deposit'), [
            'amount' => 500,
        ]);
        $responsePrison->assertRedirect(route('prison'));

        // 4. Withdraw with tax
        $character->update(['is_in_prison' => false, 'prison_until' => null]);
        $responseWithdraw = $this->actingAs($user)->post(route('bank.withdraw'), [
            'amount' => 1000,
        ]);
        $responseWithdraw->assertRedirect();
        $this->assertEquals(11000, $character->fresh()->bank);
    }

    public function test_bank_loan_idor_protection(): void
    {
        [$user1, $char1] = $this->createInmate();
        [$user2, $char2] = $this->createInmate();

        $loan = Loan::create([
            'character_id' => $char1->id,
            'loan_number' => '#9999',
            'amount' => 5000,
            'interest_rate' => 10,
            'interest_amount' => 500,
            'total_due' => 5500,
            'total_paid' => 0,
            'remaining_due' => 5500,
            'status' => 'active',
            'started_at' => now(),
        ]);

        // User 2 cannot repay User 1's loan
        $response = $this->actingAs($user2)->post(route('bank.loans.repay', $loan), [
            'amount' => 1000,
            'source' => 'cash',
        ]);
        $response->assertStatus(403);

        $responseFull = $this->actingAs($user2)->post(route('bank.loans.repay-full', $loan), [
            'source' => 'cash',
        ]);
        $responseFull->assertStatus(403);
    }

    public function test_vault_sell_mask_and_sell_code_guards(): void
    {
        [$user, $character] = $this->createInmate([
            'masks' => 10,
            'bank' => 5000,
        ]);

        // 1. Sell masks
        $response = $this->actingAs($user)->post(route('vault.sell-mask'), [
            'amount' => 3,
        ]);
        $response->assertSessionHas('success');
        $this->assertEquals(7, $character->fresh()->masks);
        $this->assertEquals(5000 + (3 * 1500), $character->fresh()->bank);

        // 2. Sell mask blocked when comatose
        $character->update(['coma_until' => now()->addMinutes(30)]);
        $responseComa = $this->actingAs($user)->post(route('vault.sell-mask'), [
            'amount' => 1,
        ]);
        $responseComa->assertSessionHas('error');

        // 3. Sell code
        $character->update(['coma_until' => null]);
        $vaultCode = VaultCode::create([
            'character_id' => $character->id,
            'code' => 'KLUIS-TEST-1234',
            'reward_cash' => 2000,
            'reward_stars' => 1,
            'reward_tokens' => 2,
            'is_redeemed' => false,
        ]);

        $responseCode = $this->actingAs($user)->post(route('vault.sell-code'), [
            'code_id' => $vaultCode->id,
        ]);
        $responseCode->assertSessionHas('success');
        $this->assertDatabaseMissing('vault_codes', ['id' => $vaultCode->id]);
    }

    public function test_cooldown_skip_with_stars_and_revive_serum(): void
    {
        [$user, $character] = $this->createInmate([
            'stars' => 50,
        ]);

        $cooldownService = app(CooldownService::class);
        $cooldownService->setCooldown($character, 'hustle', 180);

        $this->assertTrue($cooldownService->isOnCooldown($character, 'hustle'));

        // Skip with stars
        $res = $cooldownService->skipWithStars($character, 'hustle');
        $this->assertTrue($res['success']);
        $this->assertFalse($cooldownService->isOnCooldown($character, 'hustle'));
        $this->assertEquals(40, $character->fresh()->stars);

        // Skip blocked when comatose
        $character->update(['coma_until' => now()->addHours(1)]);
        $cooldownService->setCooldown($character, 'pickpocket', 120);

        $resComa = $cooldownService->skipWithStars($character, 'pickpocket');
        $this->assertFalse($resComa['success']);
        $this->assertStringContainsString('Code Blauw', $resComa['message']);
    }

    public function test_profile_border_equipping_and_customization(): void
    {
        [$user, $character] = $this->createInmate([
            'unlocked_borders' => ['autumn_ember'],
            'equipped_border' => null,
        ]);

        // 1. Equip owned border
        $response = $this->actingAs($user)->post(route('profile.customize'), [
            'equipped_border' => 'autumn_ember',
            'bio' => 'Mijn criminele profiel bio.',
        ]);
        $response->assertSessionHas('success');
        $this->assertEquals('autumn_ember', $character->fresh()->equipped_border);
        $this->assertEquals('Mijn criminele profiel bio.', $character->fresh()->bio);

        // 2. Equip unowned border does not equip
        $this->actingAs($user)->post(route('profile.customize'), [
            'equipped_border' => 'crimson_harvest_king',
        ]);
        $this->assertEquals('autumn_ember', $character->fresh()->equipped_border);

        // 3. Unequip border
        $this->actingAs($user)->post(route('profile.customize'), [
            'equipped_border' => 'none',
        ]);
        $this->assertNull($character->fresh()->equipped_border);
    }

    public function test_news_comments_and_permissions(): void
    {
        [$authorUser, $authorChar] = $this->createInmate([], true);
        [$commenterUser, $commenterChar] = $this->createInmate();

        $article = NewsArticle::create([
            'character_id' => $authorChar->id,
            'title' => 'Nieuwsbericht Update Seizoen 1',
            'slug' => 'nieuws-update-seizoen-1',
            'emoji' => '📢',
            'content' => "## Grote Update\n\nEr zijn nieuwe **veiligheidsmaatregelen** in de kliniek!",
            'is_published' => true,
            'comments_enabled' => true,
            'published_at' => now()->subDay(),
        ]);

        // 1. Post comment
        $response = $this->actingAs($commenterUser)->post(route('news.comment', $article), [
            'content' => 'Geweldige update van de directie!',
        ]);
        $response->assertSessionHas('success');

        $comment = NewsComment::where('news_article_id', $article->id)->first();
        $this->assertNotNull($comment);
        $this->assertEquals('Geweldige update van de directie!', $comment->content);

        // 2. Other user cannot delete comment
        [$randomUser, $randomChar] = $this->createInmate();
        $responseDelFail = $this->actingAs($randomUser)->delete(route('news.comment.destroy', $comment));
        $responseDelFail->assertSessionHas('error');
        $this->assertDatabaseHas('news_comments', ['id' => $comment->id]);

        // 3. Comment author can delete
        $responseDel = $this->actingAs($commenterUser)->delete(route('news.comment.destroy', $comment));
        $responseDel->assertSessionHas('success');
        $this->assertDatabaseMissing('news_comments', ['id' => $comment->id]);
    }

    public function test_news_article_formatted_content_handles_null_safely(): void
    {
        $article = new NewsArticle([
            'content' => null,
        ]);

        $formatted = $article->formatted_content;
        $this->assertIsString($formatted);
        $this->assertEquals('', $formatted);

        $article2 = new NewsArticle([
            'content' => '**Vetgedrukt** en [i]cursief[/i]',
        ]);
        $this->assertStringContainsString('font-extrabold', $article2->formatted_content);
        $this->assertStringContainsString('italic', $article2->formatted_content);
    }

    public function test_shop_blocks_purchases_when_imprisoned_or_comatose(): void
    {
        [$user, $character] = $this->createInmate([
            'cash' => 50000,
            'bank' => 50000,
            'stars' => 100,
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(30),
        ]);

        $item = Item::create([
            'name' => 'Kliniek Schaar',
            'slug' => 'kliniek-schaar-test',
            'description' => 'Test schaar',
            'category' => 'general',
            'price_kc' => 250,
            'is_buyable' => true,
        ]);

        // Blocked when in prison
        $response = $this->actingAs($user)->post(route('shop.buy', $item), [
            'quantity' => 1,
        ]);
        $response->assertSessionHas('error');

        // Blocked when in coma
        $character->update([
            'is_in_prison' => false,
            'prison_until' => null,
            'coma_until' => now()->addHours(2),
        ]);

        $responseComa = $this->actingAs($user)->post(route('shop.buy', $item), [
            'quantity' => 1,
        ]);
        $responseComa->assertSessionHas('error');
    }

    public function test_news_article_sanitize_html_xss_protection(): void
    {
        $dirtyHtml = '<p>Veilige tekst</p><script>alert("hack")</script><a href="javascript:alert(1)">Klik hier</a><img src="https://example.com/pic.jpg" onerror="alert(2)" />';
        $clean = NewsArticle::sanitizeHtml($dirtyHtml);

        $this->assertStringNotContainsString('<script>', $clean);
        $this->assertStringNotContainsString('javascript:alert(1)', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringContainsString('Veilige tekst', $clean);
    }

    public function test_dashboard_sell_peuken_validation_and_widget_check(): void
    {
        Item::firstOrCreate(
            ['slug' => 'sigaretten'],
            [
                'name' => 'Slof Sigaretten',
                'description' => 'Goud waard in de kliniek',
                'category' => 'general',
                'price_kc' => 100,
                'is_buyable' => true,
            ]
        );

        [$user, $character] = $this->createInmate([
            'unlocked_widgets' => ['weather'], // 'tunnel' not unlocked
        ]);

        // Blocked when widget not unlocked
        $response = $this->actingAs($user)->postJson(route('dashboard.peuken.sell'), [
            'amount' => '5',
        ]);
        $response->assertStatus(403);

        // Unlock tunnel widget
        $character->update(['unlocked_widgets' => ['weather', 'tunnel']]);

        // Has 0 cigarettes
        $responseZero = $this->actingAs($user)->postJson(route('dashboard.peuken.sell'), [
            'amount' => '5',
        ]);
        $responseZero->assertStatus(400);
    }

    public function test_dashboard_radar_sweep_cooldown_and_guards(): void
    {
        [$user, $character] = $this->createInmate([
            'unlocked_widgets' => ['treatment'],
        ]);

        // 1. First radar sweep succeeds
        $response = $this->actingAs($user)->postJson(route('dashboard.radar.sweep'));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // 2. Second radar sweep hits cooldown
        $responseCd = $this->actingAs($user)->postJson(route('dashboard.radar.sweep'));
        $responseCd->assertStatus(400);
        $responseCd->assertJsonStructure(['error', 'remaining_seconds']);

        // 3. Blocked when imprisoned
        $character->update(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);
        $responsePrison = $this->actingAs($user)->postJson(route('dashboard.radar.sweep'));
        $responsePrison->assertStatus(403);
    }

    public function test_bank_atm_pin_flow(): void
    {
        [$user, $character] = $this->createInmate();

        // 1. Set PIN
        $responseSet = $this->actingAs($user)->postJson(route('bank.pin.set'), [
            'pin' => '1234',
            'pin_confirmation' => '1234',
        ]);
        $responseSet->assertStatus(200);
        $this->assertTrue($character->fresh()->hasBankPin());

        // 2. Eject card
        $responseEject = $this->actingAs($user)->postJson(route('bank.pin.eject'));
        $responseEject->assertStatus(200);

        // 3. Verify PIN wrong
        $responseWrong = $this->actingAs($user)->postJson(route('bank.pin.verify'), [
            'pin' => '9999',
        ]);
        $responseWrong->assertStatus(422);

        // 4. Verify PIN correct
        $responseCorrect = $this->actingAs($user)->postJson(route('bank.pin.verify'), [
            'pin' => '1234',
        ]);
        $responseCorrect->assertStatus(200);
    }

    public function test_vault_buy_code_and_hint_token_deductions_and_guards(): void
    {
        [$user, $character] = $this->createInmate([
            'vault_tokens' => 10,
        ]);

        // 1. Buy hint
        $responseHint = $this->actingAs($user)->post(route('vault.buy-hint'));
        $responseHint->assertSessionHas('success');
        $this->assertEquals(9, $character->fresh()->vault_tokens);

        // 2. Buy code
        $responseCode = $this->actingAs($user)->post(route('vault.buy-code'));
        $responseCode->assertSessionHas('success');
        $this->assertEquals(5, $character->fresh()->vault_tokens);

        // 3. Blocked when comatose
        $character->update(['coma_until' => now()->addMinutes(30)]);
        $responseComa = $this->actingAs($user)->post(route('vault.buy-hint'));
        $responseComa->assertSessionHas('error');
    }
}
