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

    public function test_github_alert_renders_lists_and_maintains_valid_html(): void
    {
        $raw = "> [!note]\n> Hier is een lijst:\n> - Item 1\n> - Item 2\n> - Item 3\n\nParagraaf na de alert.";
        $article = new NewsArticle(['content' => $raw]);
        $html = $article->formatted_content;

        $this->assertStringNotContainsString('[!note]', $html);
        $this->assertStringContainsString('Opmerking', $html);
        $this->assertStringContainsString('<ul class="my-2 space-y-1">', $html);
        $this->assertStringContainsString('<li class="ml-5 list-disc', $html);
        $this->assertStringNotContainsString('</li><br />', $html);
        $this->assertStringNotContainsString('</div></p>', $html);
        $this->assertStringContainsString('Paragraaf na de alert.', $html);
    }
}
