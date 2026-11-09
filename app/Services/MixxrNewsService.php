<?php

namespace App\Services;

use Throwable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MixxrNewsService
{
    public const API_URL = 'https://mixxr.nl/api/news?limit=10';
    public const BASE_URL = 'https://mixxr.nl';

    /**
     * Get top articles from MIXXR.nl News API with caching (10 minutes).
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
                        $articles = [];
                        $count = 0;

                        foreach ($rawArticles as $item) {
                            if ($count >= $limit) {
                                break;
                            }

                            $title = (string) ($item['title'] ?? '');
                            $link = (string) ($item['link'] ?? '');
                            if (! str_starts_with($link, 'http')) {
                                $link = self::BASE_URL . $link;
                            }

                            $desc = (string) ($item['summary'] ?? $item['subtitle'] ?? '');
                            $category = (string) ($item['category'] ?? 'Entertainment');
                            $categoryColor = (string) ($item['categoryColor'] ?? '#8612FE');
                            $author = (string) ($item['author'] ?? 'MIXXR Redactie');

                            $image = (string) ($item['image'] ?? '');
                            if (! empty($image) && ! str_starts_with($image, 'http')) {
                                $image = self::BASE_URL . $image;
                            }

                            $pubDateStr = (string) ($item['publishedAt'] ?? now()->toIso8601String());
                            $timestamp = strtotime($pubDateStr) ?: time();
                            $timeAgo = (string) ($item['dateFormatted'] ?? now()->setTimestamp($timestamp)->diffForHumans());

                            $articles[] = [
                                'title' => $title,
                                'link' => $link,
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

                        if (! empty($articles)) {
                            return $articles;
                        }
                    }
                }

                return $this->getFallbackNews();
            } catch (Throwable $e) {
                report($e);

                return $this->getFallbackNews();
            }
        });
    }

    /**
     * Fallback news items if MIXXR service is temporarily unreachable.
     */
    protected function getFallbackNews(): array
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
