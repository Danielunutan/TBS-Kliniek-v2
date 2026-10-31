<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use App\Services\TheftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class TheftPvpVictimLockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_pvp_theft_never_takes_more_cash_than_the_victim_owns_at_that_moment(): void
    {
        $thief = User::where('email', 'demo@tbskliniek.nl')->first()->character;
        $victim = Character::where('id', '!=', $thief->id)->first();
        $victim->update(['cash' => 1000, 'cash_protection_until' => null]);
        $thief->update(['cash' => 0]);

        // The victim model was loaded while 1000 was in the pockets ...
        $staleVictim = Character::find($victim->id);

        // ... but a parallel request already took 900 of it.
        Character::where('id', $victim->id)->update(['cash' => 100]);

        $method = new ReflectionMethod(TheftService::class, 'finishPvpSuccess');
        $method->setAccessible(true);
        $result = $method->invoke(app(TheftService::class), $thief, $staleVictim, TheftService::TIERS['pocket'], 'pocket', 500, 'full');

        $this->assertTrue($result['success']);
        $this->assertSame(100, $result['cash_stolen']);
        $this->assertSame(0, (int) Character::find($victim->id)->cash);
        $this->assertSame(100, (int) Character::find($thief->id)->cash);
    }
}
