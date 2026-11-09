<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;
use Throwable;

class MixxrNewsService
{
    public const API_URL = 'https://mixxr.nl/api/news?limit=10';

    public const RSS_URL = 'https://mixxr.nl/rss.xml';

    public const BASE_URL = 'https://mixxr.nl';

    /**
     * Get top articles from MIXXR.nl News API with RSS and static fallbacks (caching 10 minutes).
     *
     * @return array<int, array{title: string, link: string, description: string, pubDate: string, time_ago: string, image: ?string, category: string, category_color: string, author: string}>
     */
    public function getLatestNews(int $limit = 6): array
    {
        return Cache::remember('tbs_mixxr_latest_news_'.$limit, 600, function () use ($limit): array {
            try {
                $response = Http::timeout(5)->withoutVerifying()->get(self::API_URL);

                if ($response->successful()) {
                    $data = $response->json();
                    $rawArticles = $data['articles'] ?? [];

                    if (! empty($rawArticles)) {
                        $articles = $this->parseJsonArticles($rawArticles, $limit);
                        if (! empty($articles)) {
                            return $articles;
                        }
                    }
                }
            } catch (Throwable $e) {
                report($e);
            }

            // Fallback 1: RSS feed from MIXXR
            $rssArticles = $this->getNewsFromRss($limit);
            if (! empty($rssArticles)) {
                return $rssArticles;
            }

            // Fallback 2: Static fallback news
            return $this->getFallbackNews();
        });
    }

    /**
     * Parse articles from the JSON API payload.
     *
     * @param  array<int, array<string, mixed>>  $rawArticles
     * @return array<int, array{title: string, link: string, description: string, pubDate: string, time_ago: string, image: ?string, category: string, category_color: string, author: string}>
     */
    public function parseJsonArticles(array $rawArticles, int $limit = 6): array
    {
        $articles = [];
        $count = 0;

        foreach ($rawArticles as $item) {
            if ($count >= $limit) {
                break;
            }

            $title = (string) ($item['title'] ?? '');
            $link = (string) ($item['link'] ?? '');
            if (! empty($link) && ! str_starts_with($link, 'http')) {
                $link = self::BASE_URL.$link;
            }

            $desc = (string) ($item['summary'] ?? $item['subtitle'] ?? '');
            $category = (string) ($item['category'] ?? 'Entertainment');
            $categoryColor = (string) ($item['categoryColor'] ?? $this->getCategoryColor($category));
            $author = (string) ($item['author'] ?? 'MIXXR Redactie');

            $image = (string) ($item['image'] ?? '');
            if (! empty($image) && ! str_starts_with($image, 'http')) {
                $image = self::BASE_URL.$image;
            }

            $pubDateStr = (string) ($item['publishedAt'] ?? now()->toIso8601String());
            $timestamp = strtotime($pubDateStr) ?: time();
            $timeAgo = (string) ($item['dateFormatted'] ?? now()->setTimestamp($timestamp)->diffForHumans());

            $articles[] = [
                'title' => $title,
                'link' => $link ?: self::BASE_URL,
                'description' => $desc,
                'pubDate' => $pubDateStr,
                'time_ago' => $timeAgo,
                'image' => $image ?: 'https://mixxr.nl/album-art.png',
                'category' => $category,
                'category_color' => $categoryColor,
                'author' => $author,
            ];

            $count++;
        }

        return $articles;
    }

    /**
     * Fetch and parse articles from the MIXXR.nl RSS feed.
     *
     * @return array<int, array{title: string, link: string, description: string, pubDate: string, time_ago: string, image: ?string, category: string, category_color: string, author: string}>
     */
    public function getNewsFromRss(int $limit = 6): array
    {
        try {
            $response = Http::timeout(5)->withoutVerifying()->get(self::RSS_URL);
            if (! $response->successful()) {
                return [];
            }

            $body = $response->body();
            if (empty(trim($body))) {
                return [];
            }

            return $this->parseRssXml($body, $limit);
        } catch (Throwable $e) {
            report($e);

            return [];
        }
    }

    /**
     * Parse XML body into articles array safely.
     *
     * @return array<int, array{title: string, link: string, description: string, pubDate: string, time_ago: string, image: ?string, category: string, category_color: string, author: string}>
     */
    public function parseRssXml(string $xmlContent, int $limit = 6): array
    {
        $xml = @simplexml_load_string($xmlContent, SimpleXMLElement::class, LIBXML_NOCDATA | LIBXML_NONET);
        if (! $xml || ! isset($xml->channel->item)) {
            return [];
        }

        $articles = [];
        $count = 0;

        foreach ($xml->channel->item as $item) {
            if ($count >= $limit) {
                break;
            }

            $title = html_entity_decode((string) ($item->title ?? ''), ENT_QUOTES, 'UTF-8');
            $link = (string) ($item->link ?? '');
            if (! empty($link) && ! str_starts_with($link, 'http')) {
                $link = self::BASE_URL.$link;
            }

            $desc = html_entity_decode((string) ($item->description ?? ''), ENT_QUOTES, 'UTF-8');
            $category = (string) ($item->category ?? 'Entertainment');
            $author = (string) ($item->author ?? 'MIXXR Redactie');

            $image = '';
            if (isset($item->enclosure)) {
                $image = (string) ($item->enclosure['url'] ?? '');
            }
            if (! empty($image) && ! str_starts_with($image, 'http')) {
                $image = self::BASE_URL.$image;
            }

            $pubDateStr = (string) ($item->pubDate ?? now()->toIso8601String());
            $timestamp = strtotime($pubDateStr) ?: time();
            $timeAgo = now()->setTimestamp($timestamp)->diffForHumans();

            $articles[] = [
                'title' => $title,
                'link' => $link ?: self::BASE_URL,
                'description' => $desc,
                'pubDate' => $pubDateStr,
                'time_ago' => $timeAgo,
                'image' => $image ?: 'https://mixxr.nl/album-art.png',
                'category' => $category ?: 'Entertainment',
                'category_color' => $this->getCategoryColor($category),
                'author' => $author ?: 'MIXXR Redactie',
            ];

            $count++;
        }

        return $articles;
    }

    /**
     * Map category name to theme accent color.
     */
    public function getCategoryColor(string $category): string
    {
        return match (strtolower(trim($category))) {
            'music', 'muziek' => '#8612FE',
            'nieuws' => '#3B82F6',
            'concerten' => '#F59E0B',
            'pop culture' => '#00E5A3',
            'studio', 'trends' => '#10B981',
            default => '#EC4899',
        };
    }

    /**
     * Fallback news items if MIXXR service is temporarily unreachable.
     */
    public function getFallbackNews(): array
    {
        return [
            [
                'title' => 'MIXXR Radio Non-Stop Live op TBS Kliniek',
                'link' => 'https://mixxr.nl',
                'description' => 'Luister 24/7 naar de heetste tracks, pop, R&B en club bangers direct vanuit je cel.',
                'pubDate' => now()->toIso8601String(),
                'time_ago' => 'Zojuist',
                'image' => 'https://mixxr.nl/album-art.png',
                'category' => 'Music',
                'category_color' => '#8612FE',
                'author' => 'Daniël Unutan',
            ],
            [
                'title' => 'Vraag jouw favoriete hits aan via de AutoDJ op MIXXR.nl',
                'link' => 'https://mixxr.nl',
                'description' => 'Verzoekjes worden direct ingepland in de live uitzending en afgespeeld voor alle luisteraars.',
                'pubDate' => now()->toIso8601String(),
                'time_ago' => 'Vandaag',
                'image' => 'https://mixxr.nl/album-art.png',
                'category' => 'Entertainment',
                'category_color' => '#EC4899',
                'author' => 'MIXXR Redactie',
            ],
        ];
    }
}
