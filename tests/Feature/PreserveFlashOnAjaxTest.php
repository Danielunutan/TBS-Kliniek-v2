<?php

namespace Tests\Feature;

use App\Models\Character;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreserveFlashOnAjaxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_flash_messages_are_not_consumed_by_background_ajax_polling(): void
    {
        $character = Character::first();
        $user = $character->user;

        $this->actingAs($user);

        // 1. Simulate an action setting flash data
        session()->flash('error', 'Hossel mislukt! Iemand waarschuwde de zaalwacht.');
        session()->save();

        // 2. Simulate 3 consecutive AJAX polling requests (e.g. chat polling / realtime stats)
        for ($i = 0; $i < 3; $i++) {
            $ajaxResponse = $this->getJson('/chat/poll-inkomend');
            $ajaxResponse->assertOk();
        }

        // 3. Normal HTML page load (redirect destination) must STILL have the flash error
        $htmlResponse = $this->get('/hosselen');
        $htmlResponse->assertOk();
        $htmlResponse->assertSee('Let op / Tegenwerking!');
        $htmlResponse->assertSee('Hossel mislukt! Iemand waarschuwde de zaalwacht.');

        // 4. Subsequent normal HTML load should have consumed the flash data
        $secondHtmlResponse = $this->get('/hosselen');
        $secondHtmlResponse->assertOk();
        $secondHtmlResponse->assertDontSee('Hossel mislukt! Iemand waarschuwde de zaalwacht.');
    }

    public function test_success_flash_resilient_backup_survives_even_if_standard_session_is_wiped(): void
    {
        $character = Character::first();
        $user = $character->user;

        $this->actingAs($user);

        // Simulate a POST redirect action (like producing Ritalin)
        $response = $this->post(route('smuggle.produce'), ['type' => 'sigaretten']);
        $response->assertStatus(302);

        // Manually simulate a severe race condition where standard session flash was prematurely wiped by a concurrent thread
        session()->forget(['_flash', 'success']);
        session()->save();

        // The HTML destination must STILL display the success banner via our resilient backup!
        $htmlResponse = $this->get(route('smuggle.index'));
        $htmlResponse->assertOk();
        $htmlResponse->assertSee('Actie Geslaagd!');
        $htmlResponse->assertSee('Geproduceerd!');

        // Subsequent page visit must have consumed the backup banner
        $secondHtml = $this->get(route('smuggle.index'));
        $secondHtml->assertOk();
        $secondHtml->assertDontSee('Actie Geslaagd!');
    }
}
