<?php

namespace Tests\Unit;

use App\Services\NuNlNewsService;
use Tests\TestCase;

class NuNlNewsServiceTest extends TestCase
{
    public function test_it_returns_news_articles(): void
    {
        $service = new NuNlNewsService;
        $articles = $service->getLatestNews(3);

        $this->assertIsArray($articles);
        $this->assertNotEmpty($articles);
        $this->assertArrayHasKey('title', $articles[0]);
        $this->assertArrayHasKey('link', $articles[0]);
    }
}
