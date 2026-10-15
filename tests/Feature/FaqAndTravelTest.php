<?php

namespace Tests\Feature;

use App\Models\Character;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqAndTravelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_faq_page_loads_with_all_categories_and_instructions(): void
    {
        $character = Character::first();
        $character->update(['is_onboarded' => true]);

        $response = $this->actingAs($character->user)->get(route('faq'));

        $response->assertOk();
        $response->assertSee('Kliniek Kennisbank & FAQ', false);
        $response->assertSee('Beginnersgids');
        $response->assertSee('Respectlijst, Vechten');
        $response->assertSee('Illegale Acties');
        $response->assertSee('Isoleercel, Straf');
    }

    public function test_faq_search_filters_results(): void
    {
        $character = Character::first();
        $character->update(['is_onboarded' => true]);

        $response = $this->actingAs($character->user)->get(route('faq', ['search' => 'Pillen Palmen']));

        $response->assertOk();
        $response->assertSee('Pillen Palmen');
    }

    public function test_murder_stats_page_loads_successfully(): void
    {
        $character = Character::first();
        $character->update([
            'is_onboarded' => true,
            'fights_won' => 5,
        ]);

        $response = $this->actingAs($character->user)->get(route('murder_stats.index'));

        $response->assertOk();
        $response->assertSee('Geweldsstatistieken');
        $response->assertSee('Meeste Geweld (Vandaag)');
        $response->assertSee('Meeste Geweld (7 Dagen)');
    }

    public function test_travel_page_and_movement_action(): void
    {
        $character = Character::first();
        $character->update([
            'is_onboarded' => true,
            'cash' => 50000,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        // 1. Visit page
        $res = $this->actingAs($character->user)->get(route('travel.index'));
        $res->assertOk();
        $res->assertSee('Verplaatsen (Afdelingen Reizen)');
        $res->assertSee('Paviljoen 1 (Gesloten)');

        // 2. Perform move
        $postRes = $this->actingAs($character->user)->post(route('travel.move'), [
            'location' => 'het-kookhuis-keuken',
        ]);

        $postRes->assertRedirect();
        $postRes->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals('Het Kookhuis (Keuken)', $character->current_location);
    }
}
