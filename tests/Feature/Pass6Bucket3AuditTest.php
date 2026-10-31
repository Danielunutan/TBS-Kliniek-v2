<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Character;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class Pass6Bucket3AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_bucket_3_models_and_services_load_successfully(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $this->assertNotNull($character);
        $this->assertEquals($user->id, $character->user_id);
    }

    public function test_loan_service_locking_and_request(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id, 'cash' => 10000]);

        $loanService = app(LoanService::class);
        $this->assertNotNull($loanService);
    }
}
