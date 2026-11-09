<?php

namespace Tests;

use App\Http\Middleware\CheckMaintenanceMode;
use App\Support\ArmWrestleOutcome;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $lock = CheckMaintenanceMode::lockFilePath();
        if (file_exists($lock)) {
            @unlink($lock);
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $lock = CheckMaintenanceMode::lockFilePath();
        if (file_exists($lock)) {
            @unlink($lock);
        }
        parent::tearDown();
    }

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
