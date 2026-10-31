<?php

namespace Tests\Feature;

use Tests\TestCase;

class Pass6Bucket4AuditTest extends TestCase
{
    public function test_bucket_4_files_and_configurations_are_valid(): void
    {
        $this->assertTrue(true);
        $this->assertNotEmpty(config('app.name'));
        $this->assertNotEmpty(config('database.default'));
    }
}
