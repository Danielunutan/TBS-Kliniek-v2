<?php

namespace Tests\Feature;

use Tests\TestCase;

class Pass7Bucket1AuditTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
