<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SitemapScraperService
{
    protected int $timeout = 30;
    protected int $validateTimeout = 10;
    protected int $maxPerSitemap = 50; // Max URLs to fetch per sitemap

    // Paginated post sitemaps - these contain the LATEST articles
    protected array $baseSitemaps = [
        'searchengineland.com' => [
            1 => 'https://searchengineland.com/post-sitemap.xml',
            2 => 'https://searchengineland.com/post-sitemap2.xml',
            3 => 'https://searchengineland.com/post-sitemap3.xml',
            4 => 'https://searchengineland.com/post-sitemap4.xml',
            5 => 'https://searchengineland.com/post-sitemap5.xml',
        ],
        'searchenginejournal.com' => [
            1 => 'https://www.searchenginejournal.com/post-sitemap.xml',
            2 => 'https://www.searchenginejournal.com/post-sitemap2.xml',
            3 => 'https://www.searchenginejournal.com/post-sitemap3.xml',
            4 => 'https://www.searchenginejournal.com/post-sitemap4.xml',
            5 => 'https://www.searchenginejournal.com/post-sitemap5.xml',
        ],
    ];

    /**
     * Find article URLs from sitemaps (recent ones only)
     */
    public function findUrls(string $keyword, int $limit = 10): array
    {
        $cutoffDate = date('Y-m-d', strtotime('-30 days'));
        $allEntries = [];

        foreach ($this->baseSitemaps as $domain => $sitemaps) {
            foreach ($sitemaps as $pageNum => $sitemapUrl) {
                $entries = $this->extractFromSitemap($sitemapUrl, $cutoffDate, $this->maxPerSitemap);
                $allEntries = array_merge($allEntries, $entries);

                if (count($allEntries) >= $limit * 5) break 2;
            }
        }

        // Deduplicate by URL
        $seen = [];
        $allEntries = array_filter($allEntries, function ($e) use (&$seen) {
            if (isset($seen[$e['url']])) return false;
            $seen[$e['url']] = true;
            return true;
        });

        // Sort by score + recency
        usort($allEntries, function ($a, $b) use ($keyword) {
            $scoreA = $this->calculateConfidence($a['url'], $keyword);
            $scoreB = $this->calculateConfidence($b['url'], $keyword);
            if ($scoreA !== $scoreB) return $scoreB <=> $scoreA;
            return $b['date_ts'] <=> $a['date_ts'];
        });

        $result = array_column(array_slice($allEntries, 0, $limit), 'url');

        Log::info('SitemapScraper: found ' . count($result) . ' URLs', [
            'keyword' => $keyword,
            'total_entries' => count($allEntries),
        ]);

        return $result;
    }

    /**
     * Extract URLs from sitemap using SimpleXML (handles namespaces)
     */
    protected function extractFromSitemap(string $sitemapUrl, string $cutoffDate, int $limit): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; KangwendraBot/1.0)'])
                ->get($sitemapUrl);

            if (!$response->successful()) {
                return [];
            }

            $xmlString = $response->body();

            // Suppress XML warnings, parse with SimpleXML
            libxml_use_internal_errors(true);
            $xml = @simplexml_load_string($xmlString);

            if ($xml === false) {
                // Fallback: try regex parsing
                return $this->extractViaRegex($xmlString, $cutoffDate, $limit);
            }

            // Register default namespace
            $xml->registerXPathNamespace('sm', 'http://www.sitemaps.org/schemas/sitemap/0.9');

            $entries = [];
            $count = 0;

            // Get all <url> elements using children() (handles namespace automatically)
            foreach ($xml->url as $urlEl) {
                if ($count >= $limit) break;

                // Get <loc> directly (SimpleXML handles namespace)
                $url = trim((string) $urlEl->loc);

                if (!$this->isArticleUrl($url)) continue;

                // Get <lastmod> directly
                $dateStr = trim((string) ($urlEl->lastmod ?? ''));
                $dateTs = 0;

                if ($dateStr) {
                    // Parse ISO 8601 date: 2026-07-24T21:24:30+00:00
                    $parsed = strtotime($dateStr);
                    if ($parsed !== false) {
                        $dateTs = $parsed;
                        $date = date('Y-m-d', $parsed);
                    } else {
                        $date = '1970-01-01';
                    }
                } else {
                    $date = '1970-01-01';
                    $dateTs = 0;
                }

                // Skip if older than cutoff
                if ($date !== '1970-01-01' && $date < $cutoffDate) {
                    continue;
                }

                $entries[] = [
                    'url'     => $url,
                    'date'    => $date,
                    'date_ts' => $dateTs,
                ];
                $count++;
            }

            libxml_clear_errors();

            Log::debug('SitemapScraper: parsed ' . count($entries) . ' entries from ' . basename($sitemapUrl));

            return $entries;

        } catch (\Exception $e) {
            Log::error('SitemapScraper: parse error', [
                'sitemap' => $sitemapUrl,
                'error'   => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Fallback: extract using regex (when SimpleXML fails)
     */
    protected function extractViaRegex(string $xml, string $cutoffDate, int $limit): array
    {
        $entries = [];

        // Match <url> blocks - handle namespace in content
        if (preg_match_all('/<url(?:\s[^>]*)?>([\s\S]*?)<\/url>/i', $xml, $blocks)) {
            foreach ($blocks[1] as $block) {
                if (count($entries) >= $limit) break;

                // Extract <loc>
                if (!preg_match('/<loc[^>]*>([^<]+)<\/loc>/i', $block, $locMatch)) {
                    continue;
                }
                $url = trim($locMatch[1]);

                if (!$this->isArticleUrl($url)) continue;

                // Extract <lastmod>
                $dateStr = '';
                $dateTs = 0;
                $date = '1970-01-01';

                if (preg_match('/<lastmod[^>]*>([^<]+)<\/lastmod>/i', $block, $dateMatch)) {
                    $dateStr = trim($dateMatch[1]);
                    $parsed = strtotime($dateStr);
                    if ($parsed !== false) {
                        $dateTs = $parsed;
                        $date = date('Y-m-d', $parsed);
                    }
                }

                // Skip if older than cutoff
                if ($date !== '1970-01-01' && $date < $cutoffDate) {
                    continue;
                }

                $entries[] = [
                    'url'     => $url,
                    'date'    => $date,
                    'date_ts' => $dateTs,
                ];
            }
        }

        return $entries;
    }

    /**
     * Check if URL is a valid article (not page/category/tag)
     */
    protected function isArticleUrl(string $url): bool
    {
        $skipPatterns = [
            '/author/', '/category/', '/tag/', '/amp/',
            '/video/', '/podcast/', '/webinar/', '/about/', '/contact/',
            '/privacy/', '/terms/', '/advertise/', '/login/', '/signup/',
            '/newsletter/', '/subscribe/', '/rss/', '/feed/',
            '/topics/', '/resources/', '/events/',
            '/latest-posts', '/latest-news', '/trending',
            '/page/', '/search/',
        ];

        foreach ($skipPatterns as $pattern) {
            if (stripos($url, $pattern) !== false) return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        $path = parse_url($url, PHP_URL_PATH);

        // Must be from allowed domains
        $allowed = ['searchengineland.com', 'www.searchengineland.com', 'searchenginejournal.com', 'www.searchenginejournal.com'];
        $validHost = false;
        foreach ($allowed as $domain) {
            if (stripos($host, $domain) !== false) {
                $validHost = true;
                break;
            }
        }
        if (!$validHost) return false;

        // Must be a real article path (not just domain root or short path)
        $pathLen = strlen(trim($path ?? '', '/'));
        if ($pathLen < 10) return false;

        return true;
    }

    /**
     * Validate URL accessibility
     */
    public function isAccessible(string $url): bool
    {
        try {
            $response = Http::timeout($this->validateTimeout)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; KangwendraBot/1.0)'])
                ->get($url);

            return $response->status() < 400;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get domain from URL
     */
    public function getDomain(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST) ?? '';
        $mapping = [
            'searchengineland.com' => 'searchengineland.com',
            'www.searchengineland.com' => 'searchengineland.com',
            'searchenginejournal.com' => 'searchenginejournal.com',
            'www.searchenginejournal.com' => 'searchenginejournal.com',
        ];
        return $mapping[$host] ?? $host;
    }

    /**
     * Extract title from URL slug
     */
    public function extractTitleFromSlug(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) return 'Tanpa Judul';

        $path = trim($path, '/');
        $segments = explode('/', $path);
        $slug = end($segments);

        $title = str_replace('-', ' ', $slug);
        $title = str_replace('_', ' ', $title);
        $title = preg_replace('/^\d+\s+/', '', $title);
        $title = trim(ucwords($title));

        return strlen($title) > 3 ? $title : 'Tanpa Judul';
    }

    /**
     * Calculate confidence score
     */
    public function calculateConfidence(string $url, string $keyword): float
    {
        $urlLower = strtolower($url);
        $score = 50;

        // Keyword match
        $parts = preg_split('/[\s,\-_]+/', strtolower($keyword));
        foreach ($parts as $part) {
            $part = trim($part);
            if (strlen($part) < 2) continue;
            if (stripos($urlLower, $part) !== false) $score += 15;
        }

        // AI keyword bonus
        $aiBonus = [
            'ai-agent' => 20, 'agentic-ai' => 20, 'generative-ai' => 18,
            'machine-learning' => 15, 'deep-learning' => 15,
            'large-language' => 18, 'llm' => 18,
            'openai' => 12, 'chatgpt' => 12, 'gemini' => 12,
            'google-ai' => 12, 'microsoft-ai' => 12, 'meta-ai' => 12,
            'neural-network' => 15, 'transformer' => 15, 'rag-' => 15,
            'seo-ai' => 18, 'search-ai' => 18,
            'artificial-intelligence' => 15, 'nlp' => 12,
            'enterprise-ai' => 12, 'ai-regulation' => 12,
            'ai-chip' => 10, 'automation' => 8, 'robotics' => 8,
        ];
        foreach ($aiBonus as $kw => $bonus) {
            if (stripos($urlLower, $kw) !== false) $score += $bonus;
        }

        // SEO keyword bonus
        if (stripos($urlLower, 'seo') !== false) $score += 8;
        if (stripos($urlLower, 'google-algorithm') !== false) $score += 8;
        if (stripos($urlLower, 'google-update') !== false) $score += 8;

        // Domain bonus
        if (stripos($urlLower, 'searchenginejournal.com') !== false) $score += 5;
        if (stripos($urlLower, 'searchengineland.com') !== false) $score += 5;

        // Reject penalty
        $reject = ['review' => -10, '-vs-' => -15, 'comparison' => -10,
                    'budget' => -10, 'hp-murah' => -15, 'smartphone' => -10,
                    'iphone' => -8, 'samsung' => -8, 'xiaomi' => -8,
                    'wordle' => -20, 'crossword' => -20];
        foreach ($reject as $pattern => $penalty) {
            if (stripos($urlLower, $pattern) !== false) $score += $penalty;
        }

        return max(30, min(98, $score));
    }
}
