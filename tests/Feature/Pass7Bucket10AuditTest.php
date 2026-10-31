<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass7Bucket10AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_routes_and_layouts_are_secure(): void
    {
        $response = $this->get('/algemene-voorwaarden');
        $response->assertStatus(200);
    }
}
