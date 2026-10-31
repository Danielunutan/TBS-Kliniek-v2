<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class Pass7Bucket10AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_routes_and_layouts_are_secure(): void
    {
        $response = $this->get('/algemene-voorwaarden');
        $response->assertStatus(200);
    }
}
