<?php

namespace App\Services;

use App\Models\RefArticle;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YahooTechScraperService
{
    protected string $baseUrl = 'https://tech.yahoo.com';
    protected int $scrapeLimit = 5; // Maksimal article per scrape

    public function __construct(int $scrapeLimit = 5)
    {
        $this->scrapeLimit = $scrapeLimit;
    }

    public function scrapeAndSave(): int
    {
        $links = $this->fetchArticleLinks();

        if (empty($links)) {
            Log::warning('YahooTechScraper: tidak ada link artikel yang ditemukan.');
            return 0;
        }

        $saved = 0;

        foreach ($links as $url) {
            if ($saved >= $this->scrapeLimit) {
                Log::info("YahooTechScraper: batas {$this->scrapeLimit} article per run tercapai.");
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
                'source_url'     => $url,
                'source_domain'  => 'tech.yahoo.com',
                'title'          => $article['title'],
                'content'        => $article['content'],
                'image_url'      => $article['image_url'] ?? null,
                'tags'           => $article['tags'] ?? [],
                'author'         => $article['author'] ?? null,
                'published_at'   => $article['published_at'] ?? now(),
                'ai_status'      => 'pending',
            ]);

            $saved++;
            usleep(500000);
        }

        Log::info("YahooTechScraper: {$saved} artikel disimpan (limit {$this->scrapeLimit} per run).");
        return $saved;
    }

    protected function isValidArticle(?array $article): bool
    {
        if (!$article) return false;
        if (empty($article['title']) || empty($article['content'])) return false;

        // WAJIB: harus punya gambar
        if (empty($article['image_url'])) {
            Log::debug("YahooTechScraper: skip - tidak ada gambar");
            return false;
        }

        $plainContent = strip_tags($article['content']);

        if (strlen($plainContent) < 200) {
            Log::debug("YahooTechScraper: skip - konten terlalu pendek (" . strlen($plainContent) . " chars)");
            return false;
        }

        $yesNoCount = substr_count(strtolower($plainContent), 'yes')
                    + substr_count(strtolower($plainContent), 'no');
        if ($yesNoCount > 3 && strlen($plainContent) < 2000) {
            Log::debug("YahooTechScraper: skip - terdeteksi tabel comparison");
            return false;
        }

        if (preg_match('/\$[\d,]+.*\$[\d,]+.*\$[\d,]+/', $plainContent) && strlen($plainContent) < 3000) {
            Log::debug("YahooTechScraper: skip - terdeteksi daftar harga produk");
            return false;
        }

        $paragraphCount = substr_count($plainContent, "\n\n");
        if ($paragraphCount < 3 && strlen($plainContent) < 1000) {
            Log::debug("YahooTechScraper: skip - bukan artikel dengan paragraf");
            return false;
        }

        $textToLinkRatio = strlen($plainContent) / (substr_count(strtolower($plainContent), 'href') + 1);
        if ($textToLinkRatio < 50) {
            Log::debug("YahooTechScraper: skip - terlalu banyak link, kurang teks");
            return false;
        }

        // Cek topicality - harus tentang tech/AI
        $techKeywords = [
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
            'blockchain', 'crypto', 'bitcoin', 'nft', 'web3', 'metaverse',
            'gaming', 'esport', 'console', 'playstation', 'xbox', 'nintendo', 'steam',
            'streaming', 'netflix', 'spotify', 'disney+',
        ];

        // Negative keywords - skip jika mengandung ini (puzzle hints, non-tech)
        $negativeKeywords = [
            'wordle', 'crossword', 'nyt mini', 'strands', 'connections', 'quordle',
            'leakdlord', 'nytimes', 'today\'s nyt', 'daily puzzle', 'puzzle hint', 'puzzle answer',
        ];

        $titleLower = strtolower($article['title']);
        $haystack = strtolower($article['title'] . ' ' . $plainContent);

        // Skip jika negative keyword di judul
        foreach ($negativeKeywords as $neg) {
            if (strpos($titleLower, $neg) !== false) {
                Log::debug("YahooTechScraper: skip - negative keyword '" . $neg . "' di judul");
                return false;
            }
        }

        // Harus ada minimal 1 tech keyword
        $hasTechKeyword = false;
        foreach ($techKeywords as $kw) {
            if (strpos($haystack, $kw) !== false) {
                $hasTechKeyword = true;
                break;
            }
        }

        if (!$hasTechKeyword) {
            Log::debug("YahooTechScraper: skip - bukan topik tech/AI");
            return false;
        }

        return true;
    }

    public function fetchArticleLinks(): array
    {
        try {
            $response = Http::timeout(60)
                ->withHeaders($this->browserHeaders())
                ->get($this->baseUrl);

            if (!$response->successful()) {
                Log::error('YahooTechScraper: gagal fetch halaman utama', ['status' => $response->status()]);
                return [];
            }

            $html  = $response->body();
            $links = [];

            preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);

            foreach ($matches[1] as $href) {
                $url = $this->resolveUrl($href);
                if ($url && $this->isArticleUrl($url)) {
                    $links[] = $url;
                }
            }

            $links = array_unique($links);
            Log::info('YahooTechScraper: ditemukan ' . count($links) . ' link artikel.');

            return array_values($links);

        } catch (\Exception $e) {
            Log::error('YahooTechScraper: exception saat fetch links', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function fetchArticleDetail(string $url): ?array
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
                'tags'        => $this->extractTags($html, $url),
            ];

        } catch (\Exception $e) {
            Log::error('YahooTechScraper: gagal fetch artikel', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractTitle(string $html): string
    {
        if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            return html_entity_decode(trim($m[1]), ENT_QUOTES);
        }
        if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $m)) {
            $title = html_entity_decode(trim($m[1]), ENT_QUOTES);
            return trim(preg_replace('/\s*[-|]\s*(Yahoo[\w\s]*)?$/i', '', $title));
        }
        return '';
    }

    private function extractContent(string $html): string
    {
        // Step 1: Hapus SEMUA script, style, nav, footer, aside, iframe
        $html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
        $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
        $html = preg_replace('/<nav[^>]*>.*?<\/nav>/is', '', $html);
        $html = preg_replace('/<footer[^>]*>.*?<\/footer>/is', '', $html);
        $html = preg_replace('/<aside[^>]*>.*?<\/aside>/is', '', $html);
        $html = preg_replace('/<form[^>]*>.*?<\/form>/is', '', $html);
        $html = preg_replace('/<iframe[^>]*>.*?<\/iframe>/is', '', $html);
        $html = preg_replace('/<!--.*?-->/s', '', $html);

        // Step 2: Hapus SEMUA atribut HTML (data-ylk, data-yga, class, id, dll)
        $html = preg_replace('/<(\w+)[^>]*\s+(?:class|id|data-[a-z-]+|aria-[a-z-]+|data-ylk|data-yga|onclick|onerror|onload)[^>]*>/i', '<$1>', $html);

        // Step 3: Hapus semua anchor link yang tracking (commerce, affiliate, rapid-with-clickid)
        $html = preg_replace('/<a[^>]+class=["\'][^"\']*(?:rapid-with-clickid|commerce-cta|sponsored|affiliate)[^"\']*["\'][^>]*>.*?<\/a>/is', '', $html);

        // Step 4: Hapus divs yang jelas garbage (ad, sponsor, related, etc.)
        $html = preg_replace('/<div[^>]+class=["\'][^"\']*(?:ad|advertisement|sponsor|promo|banner|related|recommend|sidebar|social-share|share-btn|comment|coupon)[^"\']*["\'][^>]*>.*?<\/div>/is', '', $html);

        // Step 5: Hapus button elements
        $html = preg_replace('/<button[^>]*>.*?<\/button>/is', '', $html);

        // Step 6: Hapus dialog/modal elements
        $html = preg_replace('/<dialog[^>]*>.*?<\/dialog>/is', '', $html);

        // Step 7: Hapus semua img tags (gambar tidak perlu di text)
        $html = preg_replace('/<img[^>]*>/i', '', $html);

        // Step 8: Hapus semua figure+figcaption (gambar + caption)
        $html = preg_replace('/<figure[^>]*>.*?<\/figure>/is', '', $html);
        $html = preg_replace('/<figcaption[^>]*>.*?<\/figcaption>/is', '', $html);

        // Step 9: Hapus semua anchor links - keep text only
        $html = preg_replace('/<a[^>]+href=["\'][^"\']+["\'][^>]*>(.*?)<\/a>/is', '$1', $html);

        // Step 10: Convert BR ke paragraph separator
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);

        // Step 11: Extract semua paragraph dan heading text
        $lines = [];
        $blockPatterns = [
            '/<p[^>]*>([\s\S]*?)<\/p>/i',
            '/<h1[^>]*>([\s\S]*?)<\/h1>/i',
            '/<h2[^>]*>([\s\S]*?)<\/h2>/i',
            '/<h3[^>]*>([\s\S]*?)<\/h3>/i',
            '/<h4[^>]*>([\s\S]*?)<\/h4>/i',
            '/<li[^>]*>([\s\S]*?)<\/li>/i',
        ];

        foreach ($blockPatterns as $pattern) {
            if (preg_match_all($pattern, $html, $matches)) {
                foreach ($matches[1] as $text) {
                    $text = strip_tags($text);
                    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $text = trim($text);
                    $text = preg_replace('/\s+/', ' ', $text);
                    $text = preg_replace('/^Follow[A-Z][a-zA-Z\s]+(Mon|Tue|Wed|Thu|Fri|Sat|Sun)/i', '', $text);
                    $text = preg_replace('/^Add us on Google.*$/i', '', $text);
                    $text = preg_replace('/^\d+\s+min read$/i', '', $text);
                    $text = preg_replace('/^(Advertisement|Subscribe|Share|Read more).*$/i', '', $text);
                    $text = preg_replace('/^(Mon|Tue|Wed|Thu|Fri|Sat|Sun),?\s+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+\d+,?\s+\d{4}.*$/i', '', $text);
                    if (strlen($text) >= 50 && !preg_match('/^[\W\d]+$/u', $text)) {
                        $lines[] = $text;
                    }
                }
            }
        }

        $lines = array_unique($lines);
        sort($lines);

        if (empty($lines)) {
            return '';
        }

        // Rebuild jadi HTML paragraph
        $result = '';
        foreach ($lines as $line) {
            $result .= '<p>' . $line . '</p>' . "\n";
        }

        return $result;
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
        $tags = ['Yahoo Tech'];

        if (preg_match_all('/"keywords"\s*:\s*\[([^\]]+)\]/i', $html, $m)) {
            preg_match_all('/"([^"]+)"/', $m[1][0], $kw);
            foreach ($kw[1] as $tag) {
                if ($tag && !in_array($tag, $tags)) $tags[] = $tag;
            }
        }

        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            foreach (array_filter(explode('/', $path)) as $seg) {
                $seg = ucfirst(str_replace('-', ' ', $seg));
                if (strlen($seg) > 2 && strlen($seg) < 30 && !in_array($seg, $tags)) {
                    $tags[] = $seg;
                }
            }
        }

        return array_slice($tags, 0, 5);
    }

    private function resolveUrl(string $href): ?string
    {
        $href = trim($href);
        if (empty($href) || str_starts_with($href, '#') || str_starts_with($href, 'javascript')) {
            return null;
        }
        if (str_starts_with($href, 'http')) return $href;
        if (str_starts_with($href, '/')) return $this->baseUrl . $href;
        return null;
    }

    private function isArticleUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host || stripos($host, 'yahoo.com') === false) return false;

        $path = parse_url($url, PHP_URL_PATH) ?? '';

        foreach (['/video/', '/videos/', '/tag/', '/category/', '/author/', '/search', '/login', '/signup', '/mail', '/finance/', '/news/', '/sports/', '/entertainment/'] as $ex) {
            if (stripos($path, $ex) !== false) return false;
        }

        $segments = array_filter(explode('/', trim($path, '/')));
        return count($segments) > 0 && strlen(end($segments)) > 10;
    }

    private function browserHeaders(): array
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
