<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SitemapScraperService
{
    protected int $timeout = 30;
    protected int $validateTimeout = 10;
    protected int $maxPerSitemap = 50; // Max URLs to fetch per sitemap

    // Base sitemap index URLs
    protected array $sitemapIndices = [
        'searchengineland.com'        => 'https://searchengineland.com/sitemap_index.xml',
        'searchenginejournal.com'     => 'https://www.searchenginejournal.com/sitemap_index.xml',
    ];

    // Minimum year for articles (AI content started appearing ~2022-2023)
    protected string $minYear = '2022';

    /**
     * Find article URLs from sitemaps - discovers newest sitemaps first
     * and filters by minimum year to find AI-focused content.
     * Fetches fairly from all domains, sorts by relevance, returns top candidates.
     */
    public function findUrls(string $keyword, int $limit = 10): array
    {
        $allEntries = [];

        // Step 1: Discover sitemaps for each domain
        $domainSitemaps = [];
        foreach ($this->sitemapIndices as $domain => $indexUrl) {
            $postSitemaps = $this->discoverPostSitemaps($indexUrl);

            if (empty($postSitemaps)) {
                $postSitemaps = $this->getFallbackSitemaps($domain);
            }

            if (!empty($postSitemaps)) {
                $domainSitemaps[$domain] = $postSitemaps;
            }
        }

        if (empty($domainSitemaps)) {
            return [];
        }

        // Step 2: Fair round-robin sitemap fetching
        // Skip sitemaps that return 0 entries to allow exploring newer ones
        $sitemapIterators = [];
        foreach ($domainSitemaps as $domain => $sitemaps) {
            $sitemapIterators[$domain] = 0;
        }
        $maxSitemapsPerDomain = 30;
        $collectLimit = $limit * 10;
        $maxPerDomain = 40; // cap entries per domain for diversity

        while (count($allEntries) < $collectLimit) {
            $allDone = true;

            foreach ($domainSitemaps as $domain => $sitemaps) {
                $idx = &$sitemapIterators[$domain];
                $domainEntries = 0;

                // Skip sitemaps that return 0 entries (waste of a round)
                while ($idx < count($sitemaps) && $idx < $maxSitemapsPerDomain) {
                    $entries = $this->extractFromSitemap($sitemaps[$idx], $this->maxPerSitemap);
                    $idx++;

                    if (empty($entries)) continue; // try next sitemap without counting

                    $domainEntries += count($entries);
                    $allEntries = array_merge($allEntries, $entries);
                    $allDone = false;
                    break; // exit inner while, continue outer foreach
                }

                if ($domainEntries >= $maxPerDomain) continue;
                if ($idx < count($sitemaps)) $allDone = false;
            }

            if ($allDone) break;
        }

        // Step 3: Deduplicate
        $seen = [];
        $allEntries = array_filter($allEntries, function ($e) use (&$seen) {
            if (isset($seen[$e['url']])) return false;
            $seen[$e['url']] = true;
            return true;
        });

        // Step 4: Keyword-match filter — ONLY return URLs that directly contain the keyword
        // If no URL contains the keyword, return empty (no generic AI articles)
        $keywordParts = preg_split('/[\s,\-_]+/', strtolower($keyword));
        $keywordMatchEntries = array_filter($allEntries, function ($e) use ($keywordParts) {
            $urlLower = strtolower($e['url']);
            $urlSlug = strtolower(basename($e['url'] ?? ''));
            foreach ($keywordParts as $part) {
                $part = trim($part);
                if (strlen($part) < 3) continue;
                if (stripos($urlSlug, $part) !== false) return true;
                if (stripos($urlLower, $part) !== false) return true;
            }
            return false;
        });

        // If keyword match entries found, use them (sorted by score)
        if (!empty($keywordMatchEntries)) {
            usort($keywordMatchEntries, function ($a, $b) use ($keyword) {
                $scoreA = $this->calculateConfidence($a['url'], $keyword);
                $scoreB = $this->calculateConfidence($b['url'], $keyword);
                if ($scoreA !== $scoreB) return $scoreB <=> $scoreA;
                return $b['date_ts'] <=> $a['date_ts'];
            });
            return array_column(array_slice($keywordMatchEntries, 0, $limit), 'url');
        }

        // No keyword match found — return empty (don't serve generic AI articles)
        return [];
    }

    protected string $minArticleYear = '2022'; // Skip articles older than this

    /**
     * Extract URLs from sitemap using SimpleXML (handles namespaces).
     * Only includes entries with lastmod >= $minArticleYear.
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

                // Get <lastmod> for filtering and sorting
                $dateStr = trim((string) ($urlEl->lastmod ?? ''));
                $dateTs = 0;
                $date = '1970-01-01';

                if ($dateStr) {
                    $parsed = strtotime($dateStr);
                    if ($parsed !== false) {
                        $dateTs = $parsed;
                        $date = date('Y-m-d', $parsed);
                    }
                }

                // Skip entries without date or older than minimum year
                if ($date === '1970-01-01') continue;
                if ((int) substr($date, 0, 4) < (int) $this->minArticleYear) continue;

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

                // Extract <lastmod> for filtering and sorting
                $dateTs = 0;
                $date = '1970-01-01';

                if (preg_match('/<lastmod[^>]*>([^<]+)<\/lastmod>/i', $block, $dateMatch)) {
                    $parsed = strtotime(trim($dateMatch[1]));
                    if ($parsed !== false) {
                        $dateTs = $parsed;
                        $date = date('Y-m-d', $parsed);
                    }
                }

                // Skip entries without date or older than minimum year
                if ($date === '1970-01-01') continue;
                if ((int) substr($date, 0, 4) < (int) $this->minArticleYear) continue;

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

        // Extract slug from path
        // SEJ: /article-title/123456 → get article-title
        // SEL: /article-title-123456 → get article-title-123456
        $cleanPath = rtrim($path, '/');
        // If last segment is numeric (5+ digits = article ID), strip it
        if (preg_match('/^(\/.*?)\/\d{5,}$/', $cleanPath, $m)) {
            $cleanPath = $m[1];
        }
        $slug = basename($cleanPath);
        if (empty($slug)) return false;
        // Reject if slug is purely numeric or hash
        if (preg_match('/^\d+$/', $slug)) return false;
        if (preg_match('/^[a-f0-9]{32,}$/', $slug)) return false;

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
     * Calculate confidence score.
     *
     * RULES:
     * 1. URL contains search keyword -> HIGH bonus (+25)
     * 2. URL contains AI entity -> qualifies at base score (45)
     * 3. URL contains neither keyword NOR AI entity -> REJECT (15)
     * 4. Ranking: keyword match > AI entity match
     */
    public function calculateConfidence(string $url, string $keyword): float
    {
        $urlLower = strtolower($url);

        // Step 1: Check if URL contains the search keyword
        $keywordParts = preg_split('/[\s,\-_]+/', strtolower($keyword));
        $urlSlug = basename((parse_url($url, PHP_URL_PATH) ?? ''));
        $urlSlugLower = strtolower($urlSlug);

        $keywordMatch = false;
        foreach ($keywordParts as $part) {
            $part = trim($part);
            if (strlen($part) < 3) continue;
            // Match against URL slug
            if (stripos($urlSlugLower, $part) !== false) {
                $keywordMatch = true;
                break;
            }
            // Match against full URL
            if (stripos($urlLower, $part) !== false) {
                $keywordMatch = true;
                break;
            }
        }

        // Step 2: Check AI entity match
        $aiKeywords = [
            'gemini', 'claude', 'chatgpt', 'deepseek', 'mistral', 'openai',
            'llm', 'large-language', 'artificial-intelligence', 'generative-ai',
            'ai-agent', 'agentic', 'machine-learning', 'deep-learning',
            'neural-network', 'transformer',
            'gpt-5', 'gpt-4', 'copilot', 'anthropic',
            'foundation-model', 'ai-model',
            'seo-ai', 'search-ai', 'ai-search',
            'enterprise-ai', 'microsoft-ai', 'google-ai', 'meta-ai',
            'ai-tools', 'ai-platform', 'ai-regulation',
            'ai-chip', 'robotics', 'ai-startup', 'ai-funding',
            'automation',
        ];

        $aiMatch = false;
        foreach ($aiKeywords as $kw) {
            if (stripos($urlLower, $kw) !== false) {
                $aiMatch = true;
                break;
            }
        }

        // Step 3: Score determination
        if ($keywordMatch) {
            // URL contains the search keyword - HIGH score
            $score = 70;
            // Extra bonus if also AI entity
            if ($aiMatch) $score += 5;
            // Small bonus for domain relevance
            if (stripos($urlLower, 'searchenginejournal')) $score += 3;
            if (stripos($urlLower, 'searchengineland')) $score += 3;
        } elseif ($aiMatch) {
            // URL doesn't contain keyword but IS AI-related - MEDIUM score
            $score = 45;
            if (stripos($urlLower, 'searchenginejournal')) $score += 3;
            if (stripos($urlLower, 'searchengineland')) $score += 3;
        } else {
            // Neither keyword nor AI match - REJECT
            return 15;
        }

        // Step 4: Reject penalties
        $reject = [
            'review' => -15, '-vs-' => -15, 'comparison' => -10,
            'budget' => -10, 'hp-murah' => -15, 'smartphone' => -10,
            'iphone' => -10, 'samsung' => -10, 'xiaomi' => -10, 'oppo' => -10,
            'wordle' => -20, 'crossword' => -20, 'nyt' => -20,
            'yahoo' => -15,
        ];
        foreach ($reject as $pattern => $penalty) {
            if (stripos($urlLower, $pattern) !== false) $score += $penalty;
        }

        return max(20, min(80, $score));
    }

    /**
     * Dynamically discover post sitemaps from sitemap_index.xml,
     * sorted newest first, filtered by minimum year
     */
    protected function discoverPostSitemaps(string $indexUrl): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; KangwendraBot/1.0)'])
                ->get($indexUrl);

            if (!$response->successful()) {
                return [];
            }

            libxml_use_internal_errors(true);
            $xml = @simplexml_load_string($response->body());
            libxml_clear_errors();

            if (!$xml) {
                return [];
            }

            $postSitemaps = [];

            foreach ($xml->sitemap as $sitemap) {
                $loc = trim((string) ($sitemap->loc ?? ''));
                $lastmod = trim((string) ($sitemap->lastmod ?? ''));

                // Only include post-sitemap*.xml (not category, author, page, video)
                if (!preg_match('/post-sitemap\d*\.xml$/i', basename($loc))) {
                    continue;
                }

                // Filter by year from lastmod
                $year = (int) substr($lastmod, 0, 4);
                if ($year < (int) $this->minYear) {
                    continue;
                }

                $postSitemaps[] = [
                    'url'     => $loc,
                    'lastmod' => $lastmod,
                    'year'    => $year,
                ];
            }

            // Sort by lastmod descending (newest first)
            usort($postSitemaps, fn($a, $b) => $b['lastmod'] <=> $a['lastmod']);

            return array_column($postSitemaps, 'url');

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Fallback static sitemaps for domains that don't expose sitemap_index
     */
    protected function getFallbackSitemaps(string $domain): array
    {
        $base = str_starts_with($domain, 'www.') ? "https://{$domain}" : "https://www.{$domain}";

        if ($domain === 'searchengineland.com') {
            // Try post-sitemap296 down to post-sitemap290 (most recent)
            $sitemaps = [];
            for ($i = 296; $i >= 290; $i--) {
                $sitemaps[] = "{$base}/post-sitemap{$i}.xml";
            }
            return $sitemaps;
        }

        if ($domain === 'searchenginejournal.com') {
            $sitemaps = [];
            for ($i = 10; $i >= 1; $i--) {
                $sitemaps[] = "{$base}/post-sitemap{$i}.xml";
            }
            return $sitemaps;
        }

        return ["{$base}/post-sitemap.xml"];
    }
}
