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
     * Calculate confidence score - MUST have AI keyword match to qualify
     */
    public function calculateConfidence(string $url, string $keyword): float
    {
        $urlLower = strtolower($url);
        $score = 30; // Start lower - base 50 was too permissive

        // AI keyword bonus (REQUIRED for high scores)
        $aiBonus = [
            'ai-agent' => 20, 'agentic-ai' => 20, 'generative-ai' => 18,
            'machine-learning' => 18, 'deep-learning' => 18,
            'large-language' => 20, 'llm' => 18,
            'openai' => 15, 'chatgpt' => 15, 'gemini' => 15,
            'deepseek' => 15, 'claude' => 15, 'mistral' => 15,
            'google-ai' => 12, 'microsoft-ai' => 12, 'meta-ai' => 12,
            'neural-network' => 15, 'transformer' => 15, 'rag-' => 15,
            'seo-ai' => 18, 'search-ai' => 18,
            'artificial-intelligence' => 18, 'nlp' => 12,
            'enterprise-ai' => 12, 'ai-regulation' => 12,
            'ai-chip' => 10, 'automation' => 8, 'robotics' => 8,
            'anthropic' => 15, 'foundation-model' => 15,
            'ai-startup' => 10, 'ai-funding' => 10,
        ];
        $hasAiMatch = false;
        foreach ($aiBonus as $kw => $bonus) {
            if (stripos($urlLower, $kw) !== false) {
                $score += $bonus;
                $hasAiMatch = true;
            }
        }

        // Domain bonus (only if also has AI match)
        if ($hasAiMatch) {
            if (stripos($urlLower, 'searchenginejournal.com') !== false) $score += 5;
            if (stripos($urlLower, 'searchengineland.com') !== false) $score += 5;
        }

        // Reject penalty - heavy penalties for non-AI content
        $reject = [
            'review' => -20, '-vs-' => -20, 'comparison' => -15,
            'budget' => -15, 'hp-murah' => -20, 'smartphone' => -15,
            'iphone' => -15, 'samsung' => -15, 'xiaomi' => -15, 'oppo' => -15,
            'wordle' => -30, 'crossword' => -30, 'nyt' => -30,
            'yahoo' => -15, 'bing' => -10, 'youtube' => -10,
            'facebook' => -10, 'twitter' => -10, 'instagram' => -10,
            'shopping' => -15, 'ecommerce' => -15, 'price' => -15,
            'amazon' => -10, 'product' => -15,
        ];
        foreach ($reject as $pattern => $penalty) {
            if (stripos($urlLower, $pattern) !== false) $score += $penalty;
        }

        return max(15, min(98, $score));
    }
}
