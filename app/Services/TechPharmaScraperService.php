<?php

namespace App\Services;

use App\Models\RefArticle;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TechPharmaScraperService
{
    protected array $sources = [
        'mobihealthnews.com'     => ['name' => 'MobiHealthNews',       'base_url' => 'https://www.mobihealthnews.com'],
        'healthcareitnews.com'   => ['name' => 'Healthcare IT News',   'base_url' => 'https://www.healthcareitnews.com'],
        'pharmaphorum.com'       => ['name' => 'Pharmaphorum',          'base_url' => 'https://pharmaphorum.com'],
        'fiercehealthcare.com'   => ['name' => 'FierceHealthcare',     'base_url' => 'https://www.fiercehealthcare.com'],
        'medcitynews.com'        => ['name' => 'MedCity News',          'base_url' => 'https://medcitynews.com'],
    ];

    protected int $scrapeLimit = 3; 

    protected array $keywords = [
        'pharmacy', 'farmasi', 'telemedicine', 'digital health', 'healthtech',
        'electronic health record', 'ehr', 'drug discovery', 'biotech', 'biotechnology',
        'ai healthcare', 'ai medicine', 'robotics surgery', 'medical ai',
        'pharmaceutical', 'clinical trial', 'personalized medicine', 'genomics',
        'wearable', 'remote monitoring', 'patient data', 'health data',
        'electronic prescribing', 'e-prescribing', 'pharmacy automation',
        'hospital technology', 'healthcare software', 'medical device',
        'ai diagnosis', 'machine learning healthcare', 'health chatbot',
        'online pharmacy', 'e-pharmacy', 'pharmacy tech',
    ];

    public function __construct(int $scrapeLimit = 3)
    {
        $this->scrapeLimit = $scrapeLimit;
    }

    public function scrapeAndSave(): int
    {
        $totalSaved = 0;

        foreach ($this->sources as $domain => $config) {
            Log::info("TechPharmaScraper: Scraping {$config['name']}...");
            try {
                $saved = $this->scrapeSource($domain, $config);
                $totalSaved += $saved;
                Log::info("TechPharmaScraper: {$config['name']} saved {$saved} articles.");
            } catch (\Exception $e) {
                Log::error("TechPharmaScraper: {$config['name']} failed - {$e->getMessage()}");
            }
        }

        Log::info("TechPharmaScraper: {$totalSaved} total artikel disimpan (limit {$this->scrapeLimit} per source).");
        return $totalSaved;
    }

    protected function scrapeSource(string $domain, array $config): int
    {
        $links = $this->fetchArticleLinks($config['base_url'], $domain);

        if (empty($links)) {
            Log::warning("TechPharmaScraper: Tidak ada link ditemukan untuk {$config['name']}");
            return 0;
        }

        $saved = 0;

        foreach ($links as $url) {
            if ($saved >= $this->scrapeLimit) {
                Log::info("TechPharmaScraper: batas {$this->scrapeLimit} article per source tercapai untuk {$config['name']}.");
                break;
            }

            if (RefArticle::where('source_url', $url)->exists()) {
                continue;
            }

            $article = $this->fetchArticleDetail($url, $config['name']);

            if (!$this->isValidArticle($article)) {
                continue;
            }

            RefArticle::create([
                'source_url'    => $url,
                'source_domain' => $domain,
                'title'        => $article['title'],
                'content'      => $article['content'],
                'image_url'    => $article['image_url'] ?? null,
                'tags'         => array_merge(['Tech Pharma', 'Health Tech'], $article['tags'] ?? []),
                'author'       => $article['author'] ?? null,
                'published_at' => $article['published_at'] ?? now(),
                'ai_status'    => 'pending',
            ]);

            $saved++;
            usleep(500000);
        }

        return $saved;
    }

    protected function fetchArticleLinks(string $baseUrl, string $domain): array
    {
        try {
            $response = Http::timeout(60)
                ->withHeaders($this->browserHeaders())
                ->get($baseUrl);

            if (!$response->successful()) {
                return [];
            }

            $html  = $response->body();
            $links = [];

            preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);

            foreach ($matches[1] as $href) {
                $url = $this->resolveUrl($href, $baseUrl);
                if ($url && $this->isArticleUrl($url, $domain)) {
                    $links[] = $url;
                }
            }

            return array_values(array_unique($links));

        } catch (\Exception $e) {
            Log::error("TechPharmaScraper: gagal fetch links dari {$domain}", ['error' => $e->getMessage()]);
            return [];
        }
    }

    protected function fetchArticleDetail(string $url, string $sourceName): ?array
    {
        try {
            $response = Http::timeout(60)
                ->withHeaders($this->browserHeaders())
                ->get($url);

            if (!$response->successful()) {
                return null;
            }

            $html = $response->body();

            return [
                'title'       => $this->extractTitle($html),
                'content'     => $this->extractContent($html),
                'image_url'   => $this->extractImage($html),
                'author'      => $this->extractAuthor($html),
                'published_at' => $this->extractPublishedAt($html),
                'tags'        => $this->extractTags($html),
            ];

        } catch (\Exception $e) {
            return null;
        }
    }

    protected function isValidArticle(?array $article): bool
    {
        if (!$article) return false;
        if (empty($article['title']) || empty($article['content'])) return false;

        // WAJIB: harus punya gambar
        if (empty($article['image_url'])) {
            return false;
        }

        $plainContent = strip_tags($article['content']);

        if (strlen($plainContent) < 100) return false;

        $yesNoCount = substr_count(strtolower($plainContent), 'yes')
                    + substr_count(strtolower($plainContent), 'no');
        if ($yesNoCount > 5 && strlen($plainContent) < 1000) return false;

        if (preg_match('/\$[\d,]+.*\$[\d,]+.*\$[\d,]+/', $plainContent) && strlen($plainContent) < 1500) {
            return false;
        }

        $hasKeyword = false;
        foreach ($this->keywords as $keyword) {
            if (stripos($plainContent, $keyword) !== false) {
                $hasKeyword = true;
                break;
            }
        }

        $titleHasKeyword = false;
        foreach ($this->keywords as $keyword) {
            if (stripos($article['title'], $keyword) !== false) {
                $titleHasKeyword = true;
                break;
            }
        }

        return $titleHasKeyword || $hasKeyword;
    }

    protected function extractTitle(string $html): string
    {
        if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            return html_entity_decode(trim($m[1]), ENT_QUOTES);
        }
        if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $m)) {
            return html_entity_decode(trim($m[1]), ENT_QUOTES);
        }
        return '';
    }

    protected function extractContent(string $html): string
    {
        $content = '';

        if (preg_match('/<article[^>]*>(.*?)<\/article>/is', $html, $m)) {
            $content = $m[1];
        } elseif (preg_match('/<div[^>]+class=["\'][^"\']*(?:article-body|article-content|caas-body|story-body|post-content|entry-content|content-body)[^"\']*["\'][^>]*>(.*?)<\/div>/is', $html, $m)) {
            $content = $m[1];
        } elseif (preg_match('/<main[^>]*>(.*?)<\/main>/is', $html, $m)) {
            $content = $m[1];
        }

        if (empty($content)) return '';

        $content = preg_replace('/<(script|style|nav|footer|aside|iframe|form|header)[^>]*>.*?<\/\1>/is', '', $content);
        $content = preg_replace('/<((?!p\s|h[1-6]\s|a\s|ul|ol|li|strong|em|br)[^>]+)\s+(?:class|id|data-)[^=]*=["\'][^"\']*["\']/i', '<$1', $content);
        $content = strip_tags($content, '<p><h1><h2><h3><h4><ul><ol><li><strong><em><a><br>');
        $content = trim(preg_replace('/\n{3,}/', "\n\n", $content));

        return $content;
    }

    protected function extractImage(string $html): ?string
    {
        if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/<meta[^>]+name=["\']twitter:image["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    protected function extractAuthor(string $html): ?string
    {
        if (preg_match('/"author"\s*:\s*\{[^}]*"name"\s*:\s*"([^"]+)"/i', $html, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/<meta[^>]+name=["\']author["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/<a[^>]+class=["\'][^"\']*(?:author|byline)[^"\']*["\'][^>]*>([^<]+)<\/a>/i', $html, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    protected function extractPublishedAt(string $html): ?string
    {
        foreach ([
            '/"datePublished"\s*:\s*"([^"]+)"/i',
            '/<meta[^>]+property=["\']article:published_time["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i',
            '/<time[^>]+datetime=["\']([^"\']+)["\'][^>]*>/i',
        ] as $pattern) {
            if (preg_match($pattern, $html, $m)) {
                $date = strtotime($m[1]);
                return $date ? date('Y-m-d H:i:s', $date) : null;
            }
        }
        return null;
    }

    protected function extractTags(string $html): array
    {
        $tags = [];

        if (preg_match_all('/"keywords"\s*:\s*\[([^\]]+)\]/i', $html, $m)) {
            preg_match_all('/"([^"]+)"/', $m[1][0], $kw);
            foreach ($kw[1] as $tag) {
                if ($tag && strlen($tag) > 2 && strlen($tag) < 30) $tags[] = $tag;
            }
        }

        return array_slice($tags, 0, 5);
    }

    protected function resolveUrl(string $href, string $baseUrl): ?string
    {
        $href = trim($href);
        if (empty($href) || str_starts_with($href, '#') || str_starts_with($href, 'javascript')) {
            return null;
        }
        if (str_starts_with($href, 'http')) return $href;
        if (str_starts_with($href, '/')) return $baseUrl . $href;
        return null;
    }

    protected function isArticleUrl(string $url, string $domain): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host || stripos($host, $domain) === false) return false;

        $path = parse_url($url, PHP_URL_PATH) ?? '';

        foreach (['/tag/', '/category/', '/author/', '/search/', '/login/', '/register/', '/subscribe/', '/about/', '/contact/'] as $ex) {
            if (stripos($path, $ex) !== false) return false;
        }

        $segments = array_filter(explode('/', trim($path, '/')));
        return count($segments) > 0 && strlen(end($segments)) > 8;
    }

    protected function browserHeaders(): array
    {
        return [
            'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/126.0.0.0 Safari/537.36',
            'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection'      => 'keep-alive',
        ];
    }
}
