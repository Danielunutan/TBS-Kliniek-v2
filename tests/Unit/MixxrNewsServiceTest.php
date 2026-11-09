<?php

namespace Tests\Unit;

use App\Services\MixxrNewsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MixxrNewsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_parses_json_api_successfully(): void
    {
        Http::fake([
            'https://mixxr.nl/api/news*' => Http::response([
                'success' => true,
                'articles' => [
                    [
                        'title' => 'Test Track Released',
                        'link' => '/nieuws/test-track',
                        'summary' => 'Test summary description',
                        'category' => 'Music',
                        'categoryColor' => '#8612FE',
                        'author' => 'Test Author',
                        'image' => '/uploads/test.jpg',
                        'publishedAt' => '2026-11-09T12:00:00Z',
                        'dateFormatted' => '1 hour ago',
                    ],
                ],
            ], 200),
        ]);

        $service = new MixxrNewsService;
        $news = $service->getLatestNews(1);

        $this->assertCount(1, $news);
        $this->assertSame('Test Track Released', $news[0]['title']);
        $this->assertSame('https://mixxr.nl/nieuws/test-track', $news[0]['link']);
        $this->assertSame('https://mixxr.nl/uploads/test.jpg', $news[0]['image']);
        $this->assertSame('Music', $news[0]['category']);
    }

    public function test_falls_back_to_rss_when_api_fails(): void
    {
        $rssXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>MIXXR.nl</title>
    <link>https://mixxr.nl</link>
    <item>
      <title>Rock-iconen &apos;Scorpions&apos; &amp; Friends</title>
      <link>/nieuws/scorpions-ahoy</link>
      <description>Scorpions treden op in Ahoy Rotterdam met special guests.</description>
      <category>Music</category>
      <author>Daniël</author>
      <pubDate>Mon, 09 Nov 2026 12:00:00 GMT</pubDate>
      <enclosure url="/uploads/scorpions.jpg" type="image/jpeg" />
    </item>
  </channel>
</rss>
XML;

        Http::fake([
            'https://mixxr.nl/api/news*' => Http::response([], 500),
            'https://mixxr.nl/rss.xml' => Http::response($rssXml, 200),
        ]);

        $service = new MixxrNewsService;
        $news = $service->getLatestNews(1);

        $this->assertCount(1, $news);
        // Entity decoding verified
        $this->assertSame("Rock-iconen 'Scorpions' & Friends", $news[0]['title']);
        $this->assertSame('https://mixxr.nl/nieuws/scorpions-ahoy', $news[0]['link']);
        $this->assertSame('https://mixxr.nl/uploads/scorpions.jpg', $news[0]['image']);
        $this->assertSame('Music', $news[0]['category']);
        $this->assertSame('#8612FE', $news[0]['category_color']);
        $this->assertSame('Daniël', $news[0]['author']);
    }

    public function test_falls_back_to_static_news_when_both_api_and_rss_fail(): void
    {
        Http::fake([
            'https://mixxr.nl/api/news*' => Http::response([], 500),
            'https://mixxr.nl/rss.xml' => Http::response([], 500),
        ]);

        $service = new MixxrNewsService;
        $news = $service->getLatestNews(2);

        $this->assertCount(2, $news);
        $this->assertSame('MIXXR Radio Non-Stop Live op TBS Kliniek', $news[0]['title']);
        $this->assertSame('https://mixxr.nl', $news[0]['link']);
    }
}
