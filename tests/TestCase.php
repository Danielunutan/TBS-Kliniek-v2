<?php

namespace Tests;

use App\Support\ArmWrestleOutcome;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Fix the arm wrestling server roll, so a test does not depend on randomness.
     */
    protected function fixArmWrestleRoll(bool $playerWins): void
    {
        $this->app->instance(ArmWrestleOutcome::class, new class($playerWins) extends ArmWrestleOutcome
        {
            public function __construct(private bool $playerWins) {}

            public function isPlayerWinner(int $playerStrength, int $npcStrength): bool
            {
                return $this->playerWins;
            }
        });
    }
}
