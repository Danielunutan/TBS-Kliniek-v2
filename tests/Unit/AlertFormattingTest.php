<?php

namespace Tests\Unit;

use App\Models\NewsArticle;
use Tests\TestCase;

class AlertFormattingTest extends TestCase
{
    public function test_github_alert_rendering(): void
    {
        $raw = "> [!IMPORTANT]\n> **Smokkelkiosken Limiet (1.000 max)**\n> Uitleg hier";
        $article = new NewsArticle(['content' => $raw]);
        $html = $article->formatted_content;

        $this->assertStringNotContainsString('[!IMPORTANT]', $html);
        $this->assertStringContainsString('Belangrijk', $html);
        $this->assertStringContainsString('Smokkelkiosken Limiet', $html);
    }
}

