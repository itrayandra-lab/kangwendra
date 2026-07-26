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
     * Find article URLs from sitemaps (keyword-matched, no date filter
     * since sitemap URLs are already pre-validated by search engines)
     */
    public function findUrls(string $keyword, int $limit = 10): array
    {
        $allEntries = [];

        foreach ($this->baseSitemaps as $domain => $sitemaps) {
            foreach ($sitemaps as $pageNum => $sitemapUrl) {
                $entries = $this->extractFromSitemap($sitemapUrl, $this->maxPerSitemap);
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

        return $result;
    }

    /**
     * Extract URLs from sitemap using SimpleXML (handles namespaces)
     */
    protected function extractFromSitemap(string $sitemapUrl, int $limit): array
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
                return $this->extractViaRegex($xmlString, $limit);
            }

            $entries = [];
            $count = 0;

            // Get all <url> elements using children() (handles namespace automatically)
            foreach ($xml->url as $urlEl) {
                if ($count >= $limit) break;

                // Get <loc> directly (SimpleXML handles namespace)
                $url = trim((string) $urlEl->loc);

                if (!$this->isArticleUrl($url)) continue;

                // Get <lastmod> for sorting (optional, not used for filtering)
                $dateStr = trim((string) ($urlEl->lastmod ?? ''));
                $dateTs = 0;

                if ($dateStr) {
                    $parsed = strtotime($dateStr);
                    if ($parsed !== false) {
                        $dateTs = $parsed;
                    }
                }

                $entries[] = [
                    'url'     => $url,
                    'date_ts' => $dateTs,
                ];
                $count++;
            }

            libxml_clear_errors();
            return $entries;

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Fallback: extract using regex (when SimpleXML fails)
     */
    protected function extractViaRegex(string $xml, int $limit): array
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

                // Extract <lastmod> for sorting
                $dateTs = 0;

                if (preg_match('/<lastmod[^>]*>([^<]+)<\/lastmod>/i', $block, $dateMatch)) {
                    $parsed = strtotime(trim($dateMatch[1]));
                    if ($parsed !== false) {
                        $dateTs = $parsed;
                    }
                }

                $entries[] = [
                    'url'     => $url,
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
        // Skip non-URL strings
        if (!str_starts_with($url, 'http')) return false;

        // Skip media files, attachments, images
        $skipExtensions = ['.png', '.jpg', '.jpeg', '.gif', '.webp', '.svg', '.ico',
            '.pdf', '.mp4', '.mp3', '.zip', '.doc', '.docx', '.xlsx'];
        foreach ($skipExtensions as $ext) {
            if (str_ends_with(strtolower($url), $ext)) return false;
        }

        // Skip media/uploads paths
        $skipPaths = [
            '/wp-content/uploads/', '/wp-content/plugins/', '/wp-content/themes/',
            '/wp-content/backup/', '/wp-content/cache/',
            '/media/', '/uploads/', '/files/', '/attachments/',
            '/assets/', '/static/', '/images/', '/img/',
        ];
        foreach ($skipPaths as $skip) {
            if (stripos($url, $skip) !== false) return false;
        }

        // Skip author, category, tag, page, latest posts, search, feeds
        $skipPatterns = [
            '/author/', '/category/', '/tag/', '/amp/',
            '/video/', '/podcast/', '/webinar/', '/about/', '/contact/',
            '/privacy/', '/terms/', '/advertise/', '/login/', '/signup/',
            '/newsletter/', '/subscribe/', '/rss/', '/feed/',
            '/topics/', '/resources/', '/events/',
            '/latest-posts', '/latest-news', '/trending',
            '/page/', '/search/', '/sitemap', '/robots.txt',
            '/embed/', '/api/', '/wp-json/', '/trackback/',
        ];

        foreach ($skipPatterns as $pattern) {
            if (stripos($url, $pattern) !== false) return false;
        }

        // Must be from allowed domains
        $host = parse_url($url, PHP_URL_HOST);
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        $allowed = ['searchengineland.com', 'www.searchengineland.com',
                    'searchenginejournal.com', 'www.searchenginejournal.com'];
        $validHost = false;
        foreach ($allowed as $domain) {
            if (stripos($host, $domain) !== false) {
                $validHost = true;
                break;
            }
        }
        if (!$validHost) return false;

        // Must be a real article path (not just root or very short path)
        $pathLen = strlen(trim($path, '/'));
        if ($pathLen < 10) return false;

        // Must have article slug format (hyphenated, not just numbers)
        $slug = basename($path);
        if (preg_match('/^\d+$/', $slug)) return false; // e.g. /98/, /123/
        if (preg_match('/^[a-f0-9]{32,}$/', $slug)) return false; // hash filenames

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
        if (!$url) return 'Tanpa Judul';

        $url = trim($url, '/');
        $segments = explode('/', $url);
        $slug = end($segments);

        // If last segment is just a number, use second-to-last segment
        if (preg_match('/^\d+$/', $slug) && count($segments) > 1) {
            $slug = $segments[count($segments) - 2] ?? $slug;
        }

        $title = str_replace(['-', '_'], ' ', $slug);
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
