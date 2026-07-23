<?php

namespace App\Services;

use App\Models\RefArticle;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YahooNewsScraperService
{
    // Scrape homepage, filter hanya tech articles
    protected array $baseUrls = [
        'https://news.yahoo.com',
    ];

    protected int $scrapeLimit = 3; // Default 3 per run

    public function __construct(int $scrapeLimit = 3)
    {
        $this->scrapeLimit = $scrapeLimit;
    }

    public function scrapeAndSave(): int
    {
        $links = $this->fetchArticleLinks();

        if (empty($links)) {
            Log::warning('YahooNewsScraper: tidak ada link artikel yang ditemukan.');
            return 0;
        }

        $saved = 0;

        foreach ($links as $url) {
            if ($saved >= $this->scrapeLimit) {
                Log::info("YahooNewsScraper: batas {$this->scrapeLimit} article per run tercapai.");
                break;
            }

            if (RefArticle::where('source_url', $url)->exists()) {
                continue;
            }

            $article = $this->fetchArticleDetail($url);

            if (!$this->isValidArticle($article)) {
                continue;
            }

            RefArticle::create([
                'source_url'    => $url,
                'source_domain' => 'news.yahoo.com',
                'title'         => $article['title'],
                'content'       => $article['content'],
                'image_url'     => $article['image_url'] ?? null,
                'tags'          => $article['tags'] ?? [],
                'author'        => $article['author'] ?? null,
                'published_at'  => $article['published_at'] ?? now(),
                'ai_status'     => 'pending',
            ]);

            $saved++;
            usleep(500000);
        }

        Log::info("YahooNewsScraper: {$saved} artikel disimpan (limit {$this->scrapeLimit} per run).");
        return $saved;
    }

    protected function isValidArticle(?array $article): bool
    {
        if (!$article) return false;
        if (empty($article['title']) || empty($article['content'])) return false;

        // WAJIB: harus punya gambar
        if (empty($article['image_url'])) {
            Log::debug("YahooNewsScraper: skip - tidak ada gambar");
            return false;
        }

        $plainContent = strip_tags($article['content']);

        if (strlen($plainContent) < 200) {
            Log::debug("YahooNewsScraper: skip - konten terlalu pendek (" . strlen($plainContent) . " chars)");
            return false;
        }

        $yesNoCount = substr_count(strtolower($plainContent), 'yes')
                    + substr_count(strtolower($plainContent), 'no');
        if ($yesNoCount > 3 && strlen($plainContent) < 2000) {
            Log::debug("YahooNewsScraper: skip - terdeteksi tabel comparison");
            return false;
        }

        if (preg_match('/\$[\d,]+.*\$[\d,]+.*\$[\d,]+/', $plainContent) && strlen($plainContent) < 3000) {
            Log::debug("YahooNewsScraper: skip - terdeteksi daftar harga produk");
            return false;
        }

        $paragraphCount = substr_count($plainContent, "\n\n");
        if ($paragraphCount < 3 && strlen($plainContent) < 1000) {
            Log::debug("YahooNewsScraper: skip - bukan artikel dengan paragraf");
            return false;
        }

        $textToLinkRatio = strlen($plainContent) / (substr_count(strtolower($plainContent), 'href') + 1);
        if ($textToLinkRatio < 50) {
            Log::debug("YahooNewsScraper: skip - terlalu banyak link, kurang teks");
            return false;
        }

        // Cek topicality - harus tentang tech/AI/science/finance/world
        $positiveKeywords = [
            // AI & Tech
            'ai', 'artificial intelligence', 'machine learning', 'deep learning',
            'chatgpt', 'llm', 'generative ai', 'openai', 'gemini', 'anthropic', 'deepseek',
            'google', 'apple', 'microsoft', 'meta', 'facebook', 'nvidia', 'amd', 'intel', 'qualcomm',
            'samsung', 'xiaomi', 'oppo', 'vivo', 'huawei', 'oneplus', 'realme',
            'tesla', 'amazon', 'techcrunch', 'theverge', 'wired',
            'software', 'hardware', 'smartphone', 'laptop', 'computer', 'pc', 'mac',
            'gadget', 'wearable', 'smartwatch', 'tablet', 'headphone', 'earbuds',
            'robot', 'robotics', 'automation', 'drones',
            'cybersecurity', 'cyber attack', 'malware', 'hack', 'ransomware', 'data breach',
            'chip', 'processor', 'cpu', 'gpu', 'semiconductor', 'memory',
            'cloud', 'server', 'data center', 'storage',
            'app', 'application', 'browser', 'platform', 'os', 'android', 'ios', 'windows', 'linux', 'macos',
            'social media', 'instagram', 'twitter', 'x.com', 'tiktok', 'youtube',
            '5g', '6g', 'network', 'internet', 'broadband', 'wifi', 'iot',
            'blockchain', 'crypto', 'bitcoin', 'ethereum', 'nft', 'web3', 'metaverse',
            'gaming', 'esport', 'console', 'playstation', 'xbox', 'nintendo', 'steam',
            'streaming', 'netflix', 'spotify', 'disney+',
            // Science & Space
            'nasa', 'spacex', 'space', 'astronaut', 'satellite', 'rocket', 'mars', 'moon',
            'research', 'scientist', 'discovery', 'study', 'experiment',
            'climate', 'environment', 'earth', 'ocean', 'species', 'genetic', 'gene',
            // Finance & Economy
            'economy', 'economic', 'inflation', 'recession', 'gdp', 'tariff', 'trade war',
            'stock market', 'nasdaq', 'dow jones', 's&p', 'bull market', 'bear market',
            'ipo', 'venture capital', 'funding', 'startup', 'merger', 'acquisition',
            'federal reserve', 'fed rate', 'interest rate', 'bond',
            // World News & Politics
            'war', 'military', 'ukraine', 'russia', 'china', 'taiwan', 'nato',
            'election', 'president', 'congress', 'senate', 'supreme court',
            'diplomacy', 'summit', 'g20', 'united nations', 'sanction',
            'paris agreement', 'climate summit', 'energy transition',
            // Health & Medicine
            'vaccine', 'pandemic', 'covid', 'fda', 'who', 'disease', 'cancer',
            'treatment', 'drug approval', 'clinical trial', 'health',
        ];

        // Negative keywords - skip jika mengandung ini (lifestyle, puzzle, entertainment)
        $negativeKeywords = [
            'wordle', 'crossword', 'strands', 'connections', 'quordle',
            'horoscope', 'astrology', 'weather forecast',
            'lottery result', 'betting', 'gambling',
            'opinion', 'editorial', 'letters to editor',
            'recipe', 'cooking', 'food', 'restaurant review',
            'celebrity', 'gossip', 'rumor', 'scandal',
            'travel deal', 'hotel deal', 'flight deal',
        ];

        $titleLower = strtolower($article['title']);
        $haystack = strtolower($article['title'] . ' ' . $plainContent);

        // Skip jika negative keyword di judul
        foreach ($negativeKeywords as $neg) {
            if (strpos($titleLower, $neg) !== false) {
                Log::debug("YahooNewsScraper: skip - negative keyword '" . $neg . "' di judul");
                return false;
            }
        }

        // Harus ada minimal 1 positive keyword
        $hasPositive = false;
        foreach ($positiveKeywords as $kw) {
            if (strpos($haystack, $kw) !== false) {
                $hasPositive = true;
                break;
            }
        }

        if (!$hasPositive) {
            Log::debug("YahooNewsScraper: skip - bukan topik tech/science/finance/world");
            return false;
        }

        return true;
    }

    public function fetchArticleLinks(): array
    {
        $allLinks = [];

        foreach ($this->baseUrls as $baseUrl) {
            try {
                $response = Http::timeout(60)
                    ->withHeaders($this->browserHeaders())
                    ->get($baseUrl);

                if (!$response->successful()) {
                    Log::debug('YahooNewsScraper: gagal fetch ' . $baseUrl, ['status' => $response->status()]);
                    continue;
                }

                $html = $response->body();
                preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);

                foreach ($matches[1] as $href) {
                    $url = $this->resolveUrl($href);
                    if ($url && $this->isArticleUrl($url)) {
                        $allLinks[] = $url;
                    }
                }

            } catch (\Exception $e) {
                Log::debug('YahooNewsScraper: exception saat fetch ' . $baseUrl, ['error' => $e->getMessage()]);
            }
        }

        // Normalize: buang query string dan trailing slash, lalu unique
        $normalized = [];
        foreach ($allLinks as $url) {
            $parsed = parse_url($url);
            $normalizedUrl = ($parsed['scheme'] ?? 'https') . '://'
                . ($parsed['host'] ?? '')
                . ($parsed['path'] ?? '')
                . (isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '');
            $normalizedUrl = rtrim($normalizedUrl, '/');
            $normalized[] = $normalizedUrl;
        }

        $normalized = array_unique($normalized, SORT_REGULAR);
        $allLinks = array_values($normalized);

        Log::info("YahooNewsScraper: ditemukan " . count($allLinks) . " link artikel.");
        return $allLinks;
    }

    protected function fetchArticleDetail(string $url): ?array
    {
        try {
            $response = Http::timeout(20)
                ->withHeaders($this->browserHeaders())
                ->get($url);

            if (!$response->successful()) {
                Log::debug("YahooNewsScraper: gagal fetch detail", ['url' => $url, 'status' => $response->status()]);
                return null;
            }

            $html = $response->body();

            $title       = $this->extractTitle($html);
            $content     = $this->extractContent($html);
            $imageUrl    = $this->extractImage($html);
            $author      = $this->extractAuthor($html);
            $publishedAt = $this->extractPublishedAt($html);
            $tags        = $this->extractTags($html, $url);

            return [
                'title'       => $title,
                'content'     => $content,
                'image_url'   => $imageUrl,
                'author'      => $author,
                'published_at'=> $publishedAt,
                'tags'        => $tags,
            ];

        } catch (\Exception $e) {
            Log::debug("YahooNewsScraper: exception saat fetch detail", ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    protected function isArticleUrl(string $url): bool
    {
        $skipPatterns = [
            'video.yahoo.com',
            'mail.yahoo.com',
            'finance.yahoo.com',
            'sports.yahoo.com',
            'celebrity.yahoo.com',
            'weather.yahoo.com',
            'help.yahoo.com',
            'login.yahoo.com',
            'signup.yahoo.com',
            'search.yahoo.com',
            'news.yahoo.com/video',
            'news.yahoo.com/photos',
            'news.yahoo.com/ym-',
            'consent.yahoo.com',
            '/rdl/', '/clk/', '/watch?',
            'redirect.',
            'shopping.yahoo.com',
        ];

        foreach ($skipPatterns as $pattern) {
            if (strpos($url, $pattern) !== false) {
                return false;
            }
        }

        // Hanya boleh dari news.yahoo.com/tech/...
        if (strpos($url, 'news.yahoo.com') === false) {
            return false;
        }

        // Skip homepage / section pages
        if (preg_match('/news\.yahoo\.com\/$/', $url)) {
            return false;
        }

        // HANYA accept tech section - news, politics, sports, finance, world, travel = skip
        $path = parse_url($url, PHP_URL_PATH);
        $path = trim($path, '/');
        $topSection = explode('/', $path)[0] ?? '';

        $allowedSections = ['tech'];
        if (!in_array($topSection, $allowedSections)) {
            Log::debug("YahooNewsScraper: skip - section '$topSection' bukan tech");
            return false;
        }

        return true;
    }

    protected function resolveUrl(string $href): ?string
    {
        if (empty($href) || strpos($href, 'javascript') !== false) {
            return null;
        }

        if (strpos($href, 'http') !== 0) {
            if (strpos($href, '/') === 0) {
                return 'https://news.yahoo.com' . $href;
            }
            return null;
        }

        return $href;
    }

    protected function browserHeaders(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9,id;q=0.8',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'none',
            'Sec-Fetch-User' => '?1',
            'Cache-Control' => 'max-age=0',
        ];
    }

    private function extractTitle(string $html): ?string
    {
        // og:title biasanya paling akurat
        if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            $title = trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
            // Buang suffix " - Yahoo News" dll
            $title = preg_replace('/\s*[\|\-\\:]\s*(Yahoo News|Yahoo|USA Today|CNN|ABC|Reuters|AP|ABC News).*$/i', '', $title);
            return trim($title);
        }
        if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $m)) {
            $title = trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
            $title = preg_replace('/\s*[\|\-\\:]\s*(Yahoo News|Yahoo).*$/i', '', $title);
            return trim($title);
        }
        return null;
    }

    private function extractContent(string $html): ?string
    {
        // Hapus dulu: script, style, nav, footer, aside, comments
        $html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
        $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
        $html = preg_replace('/<nav[^>]*>.*?<\/nav>/is', '', $html);
        $html = preg_replace('/<footer[^>]*>.*?<\/footer>/is', '', $html);
        $html = preg_replace('/<aside[^>]*>.*?<\/aside>/is', '', $html);
        $html = preg_replace('/<form[^>]*>.*?<\/form>/is', '', $html);
        $html = preg_replace('/<!--.*?-->/s', '', $html);

        $paragraphs = [];

        // Pattern 1: semua <p> dengan teks panjang
        if (preg_match_all('/<p[^>]*>([\s\S]*?)<\/p>/i', $html, $matches)) {
            foreach ($matches[1] as $p) {
                $text = strip_tags($p);
                $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
                $text = trim($text);

                // Skip jika terlalu pendek
                if (strlen($text) < 80) continue;

                // Skip garbage patterns
                if (preg_match('/^(Mail|Advertisement|Follow|Subscribe|Share|Read more|Latest|Trending|Add us on)/i', $text)) continue;
                if (strpos($text, 'AdvertisementAdvertisement') !== false) continue;
                if (preg_match('/^[\w\s]+Follow[\w\s]+(Mon|Tue|Wed|Thu|Fri|Sat|Sun),/', $text)) continue;
                if (preg_match('/^Follow[A-Z][a-z]+\s+(Mon|Tue|Wed|Thu|Fri|Sat|Sun),/', $text)) continue;
                // Skip author lines like "Chris SmithMon, July 22, 2026 at..."
                if (preg_match('/^[A-Z][a-z]+\s+[A-Z][a-z]+\s+(Mon|Tue|Wed|Thu|Fri|Sat|Sun),/', $text)) continue;
                if (preg_match('/^\d+\s+min read$/i', $text)) continue;
                if (preg_match('/^(Mon|Tue|Wed|Thu|Fri|Sat|Sun),?\s+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+\d+/', $text)) continue;

                $paragraphs[] = $text;
            }
        }

        if (empty($paragraphs)) {
            return null;
        }

        // Rebuild jadi HTML paragraph yang bersih
        $clean = '';
        foreach ($paragraphs as $p) {
            $p = preg_replace('/\s+/', ' ', $p);
            $clean .= '<p>' . $p . '</p>' . "\n";
        }

        return $clean;
    }

    private function extractImage(string $html): ?string
    {
        if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/<meta[^>]+name=["\']twitter:image["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private function extractAuthor(string $html): ?string
    {
        if (preg_match('/"author"\s*:\s*\{[^}]*"name"\s*:\s*"([^"]+)"/i', $html, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/<meta[^>]+name=["\']author["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private function extractPublishedAt(string $html): ?string
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

    private function extractTags(string $html, string $url): array
    {
        $tags = ['Yahoo News'];

        if (preg_match_all('/"keywords"\s*:\s*\[([^\]]+)\]/i', $html, $m)) {
            preg_match_all('/"([^"]+)"/', $m[1][0], $kw);
            foreach ($kw[1] as $tag) {
                if ($tag && !in_array($tag, $tags)) $tags[] = $tag;
            }
        }

        // Ambil dari URL path
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            $segments = array_filter(explode('/', trim($path, '/')));
            if (!empty($segments)) {
                $section = ucfirst(str_replace('-', ' ', end($segments)));
                if (!in_array($section, $tags)) {
                    $tags[] = $section;
                }
            }
        }

        return array_slice($tags, 0, 10);
    }
}
