<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class NuNlNewsService
{
    public const RSS_URL = 'https://www.nu.nl/rss';

    /**
     * Get top articles from Nu.nl RSS feed with caching (15 minutes).
     *
     * @return array<int, array{title: string, link: string, description: string, pubDate: string, time_ago: string, image: ?string, category: string}>
     */
    public function getLatestNews(int $limit = 5): array
    {
        return Cache::remember('tbs_nunl_latest_news_'.$limit, 900, function () use ($limit): array {
            try {
                $response = Http::withoutVerifying()->timeout(5)->get(self::RSS_URL);

                if (! $response->successful()) {
                    return $this->getFallbackNews();
                }

                $xml = @simplexml_load_string($response->body(), SimpleXMLElement::class, LIBXML_NOCDATA | LIBXML_NONET);
                if (! $xml || ! isset($xml->channel->item)) {
                    return $this->getFallbackNews();
                }

                $articles = [];
                $count = 0;

                foreach ($xml->channel->item as $item) {
                    if ($count >= $limit) {
                        break;
                    }

                    $title = (string) $item->title;
                    $link = (string) $item->link;
                    $description = strip_tags((string) ($item->description ?? ''));
                    $category = (string) ($item->category ?? 'Algemeen');
                    $pubDateStr = (string) ($item->pubDate ?? now()->toRfc2822String());

                    $timestamp = strtotime($pubDateStr) ?: time();
                    $timeAgo = now()->setTimestamp($timestamp)->diffForHumans();

                    $image = null;
                    if (isset($item->enclosure)) {
                        $encType = (string) ($item->enclosure['type'] ?? '');
                        if (str_starts_with($encType, 'image/') || empty($encType)) {
                            $image = (string) $item->enclosure['url'];
                        }
                    }

                    $articles[] = [
                        'title' => $title,
                        'link' => $link,
                        'description' => $description,
                        'pubDate' => $pubDateStr,
                        'time_ago' => $timeAgo,
                        'image' => $image,
                        'category' => ucfirst($category),
                    ];

                    $count++;
                }

                return $articles;
            } catch (Exception $e) {
                report($e);

                return $this->getFallbackNews();
            }
        });
    }

    /**
     * Fallback news items if external service is temporarily unreachable.
     *
     * @return array<int, array{title: string, link: string, description: string, pubDate: string, time_ago: string, image: ?string, category: string}>
     */
    protected function getFallbackNews(): array
    {
        return [
            [
                'title' => 'Beveiliging TBS Kliniek aangescherpt na smokkelincidenten',
                'link' => 'https://www.nu.nl',
                'description' => 'De directie van de inrichting meldt extra controles bij binnenkomst van bezoekers en goederen.',
                'pubDate' => now()->toRfc2822String(),
                'time_ago' => 'Zojuist',
                'image' => null,
                'category' => 'Binnenland',
            ],
            [
                'title' => 'Nieuw recreatieprogramma en werkplaatsen geopend in de kliniek',
                'link' => 'https://www.nu.nl',
                'description' => 'Patiënten kunnen voortaan deelnemen aan houtbewerking en schildercursussen voor extra dagvergoeding.',
                'pubDate' => now()->subHours(2)->toRfc2822String(),
                'time_ago' => '2 uur geleden',
                'image' => null,
                'category' => 'Kliniek',
            ],
        ];
    }
}
