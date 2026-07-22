<?php

namespace App\Services;

use App\Models\RefArticle;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YahooTechScraperService
{
    protected string $baseUrl = 'https://tech.yahoo.com';

    /**
     * Scrape daftar artikel dari tech.yahoo.com lalu simpan ke ref_articles.
     * Return jumlah artikel baru yang berhasil disimpan.
     */
    public function scrapeAndSave(): int
    {
        $links = $this->fetchArticleLinks();

        if (empty($links)) {
            Log::warning('YahooTechScraper: tidak ada link artikel yang ditemukan.');
            return 0;
        }

        $saved = 0;

        foreach ($links as $url) {
            // Skip jika sudah pernah disimpan
            if (RefArticle::where('source_url', $url)->exists()) {
                continue;
            }

            $article = $this->fetchArticleDetail($url);

            if (!$article || empty($article['title']) || empty($article['content'])) {
                continue;
            }

            RefArticle::create([
                'source_url'    => $url,
                'source_domain' => 'tech.yahoo.com',
                'title'         => $article['title'],
                'content'       => $article['content'],
                'image_url'     => $article['image_url'] ?? null,
                'tags'          => $article['tags'] ?? [],
                'author'        => $article['author'] ?? null,
                'published_at'  => $article['published_at'] ?? now(),
                'ai_status'     => 'pending',
            ]);

            $saved++;
            // Jeda kecil agar tidak dianggap bot
            usleep(500000); // 0.5 detik
        }

        return $saved;
    }

    /**
     * Ambil daftar URL artikel dari halaman utama tech.yahoo.com.
     */
    public function fetchArticleLinks(): array
    {
        try {
            $response = Http::timeout(20)
                ->withHeaders($this->browserHeaders())
                ->get($this->baseUrl);

            if (!$response->successful()) {
                Log::error('YahooTechScraper: gagal fetch halaman utama', ['status' => $response->status()]);
                return [];
            }

            $html  = $response->body();
            $links = [];

            // Cari semua href yang mengarah ke artikel Yahoo Tech
            // Pola URL artikel Yahoo: /XXXXX.html atau path dengan slug panjang
            preg_match_all(
                '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i',
                $html,
                $matches
            );

            foreach ($matches[1] as $href) {
                $url = $this->resolveUrl($href);
                if ($url && $this->isArticleUrl($url)) {
                    $links[] = $url;
                }
            }

            // Hapus duplikat
            $links = array_unique($links);

            Log::info('YahooTechScraper: ditemukan ' . count($links) . ' link artikel.');

            return array_values($links);

        } catch (\Exception $e) {
            Log::error('YahooTechScraper: exception saat fetch links', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Ambil detail artikel dari URL tertentu.
     */
    public function fetchArticleDetail(string $url): ?array
    {
        try {
            $response = Http::timeout(20)
                ->withHeaders($this->browserHeaders())
                ->get($url);

            if (!$response->successful()) {
                return null;
            }

            $html = $response->body();

            return [
                'title'        => $this->extractTitle($html),
                'content'      => $this->extractContent($html),
                'image_url'    => $this->extractImage($html),
                'author'       => $this->extractAuthor($html),
                'published_at' => $this->extractPublishedAt($html),
                'tags'         => $this->extractTags($html, $url),
            ];

        } catch (\Exception $e) {
            Log::error('YahooTechScraper: gagal fetch artikel', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /* ─────────────────────────── Extractor Helpers ─────────────────────────── */

    private function extractTitle(string $html): string
    {
        // Coba Open Graph title dulu
        if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            return html_entity_decode(trim($m[1]), ENT_QUOTES);
        }
        // Fallback ke <title>
        if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $m)) {
            $title = html_entity_decode(trim($m[1]), ENT_QUOTES);
            // Hilangkan suffix " - Yahoo Tech" dll
            $title = preg_replace('/\s*[-|]\s*(Yahoo[\w\s]*)?$/i', '', $title);
            return trim($title);
        }
        return '';
    }

    private function extractContent(string $html): string
    {
        $content = '';

        // Coba ambil dari tag <article>
        if (preg_match('/<article[^>]*>(.*?)<\/article>/is', $html, $m)) {
            $content = $m[1];
        }
        // Fallback: cari div dengan class yang umum untuk konten artikel
        elseif (preg_match('/<div[^>]+class=["\'][^"\']*(?:article-body|caas-body|body-wrap|story-body|post-content)[^"\']*["\'][^>]*>(.*?)<\/div>/is', $html, $m)) {
            $content = $m[1];
        }

        if (empty($content)) {
            return '';
        }

        // Bersihkan tag script, style, nav, dll
        $content = preg_replace('/<(script|style|nav|footer|aside|iframe|form)[^>]*>.*?<\/\1>/is', '', $content);
        // Hapus atribut berlebihan (kecuali href di anchor)
        $content = preg_replace('/<((?!a\s|\/a)[^>]+)\s+(?:class|id|data-[^=]*)=["\'][^"\']*["\']/', '<$1', $content);
        // Ubah ke plain text
        $content = strip_tags($content, '<p><h1><h2><h3><h4><ul><ol><li><strong><em><a><br>');
        // Bersihkan whitespace berlebihan
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        $content = trim($content);

        return $content;
    }

    private function extractImage(string $html): ?string
    {
        // Open Graph image
        if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            return trim($m[1]);
        }
        // Twitter card image
        if (preg_match('/<meta[^>]+name=["\']twitter:image["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private function extractAuthor(string $html): ?string
    {
        // JSON-LD author
        if (preg_match('/"author"\s*:\s*\{[^}]*"name"\s*:\s*"([^"]+)"/i', $html, $m)) {
            return trim($m[1]);
        }
        // meta author
        if (preg_match('/<meta[^>]+name=["\']author["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private function extractPublishedAt(string $html): ?string
    {
        // JSON-LD datePublished
        if (preg_match('/"datePublished"\s*:\s*"([^"]+)"/i', $html, $m)) {
            $date = strtotime($m[1]);
            return $date ? date('Y-m-d H:i:s', $date) : null;
        }
        // meta article:published_time
        if (preg_match('/<meta[^>]+property=["\']article:published_time["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            $date = strtotime($m[1]);
            return $date ? date('Y-m-d H:i:s', $date) : null;
        }
        // <time datetime>
        if (preg_match('/<time[^>]+datetime=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            $date = strtotime($m[1]);
            return $date ? date('Y-m-d H:i:s', $date) : null;
        }
        return null;
    }

    private function extractTags(string $html, string $url): array
    {
        $tags = ['Yahoo Tech'];

        // Keywords dari JSON-LD
        if (preg_match_all('/"keywords"\s*:\s*\[([^\]]+)\]/i', $html, $m)) {
            preg_match_all('/"([^"]+)"/', $m[1][0], $kw);
            foreach ($kw[1] as $tag) {
                $tag = trim($tag);
                if ($tag && !in_array($tag, $tags)) {
                    $tags[] = $tag;
                }
            }
        }

        // Fallback: cek apakah path URL mengandung kategori
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            $segments = array_filter(explode('/', $path));
            foreach ($segments as $seg) {
                $seg = ucfirst(str_replace('-', ' ', $seg));
                if (strlen($seg) > 2 && strlen($seg) < 30 && !in_array($seg, $tags)) {
                    $tags[] = $seg;
                }
            }
        }

        return array_slice($tags, 0, 5);
    }

    /* ─────────────────────────── URL Helpers ─────────────────────────── */

    private function resolveUrl(string $href): ?string
    {
        $href = trim($href);

        if (empty($href) || str_starts_with($href, '#') || str_starts_with($href, 'javascript')) {
            return null;
        }

        // Sudah absolute URL
        if (str_starts_with($href, 'http')) {
            return $href;
        }

        // Relative URL
        if (str_starts_with($href, '/')) {
            return $this->baseUrl . $href;
        }

        return null;
    }

    private function isArticleUrl(string $url): bool
    {
        // Harus dari domain Yahoo
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host || stripos($host, 'yahoo.com') === false) {
            return false;
        }

        // Pola URL artikel Yahoo biasanya diakhiri .html atau punya path panjang
        $path = parse_url($url, PHP_URL_PATH) ?? '';

        // Filter halaman non-artikel
        $exclude = ['/video/', '/videos/', '/tag/', '/category/', '/author/',
                    '/search', '/login', '/signup', '/mail', '/finance/',
                    '/news/', '/sports/', '/entertainment/'];

        foreach ($exclude as $ex) {
            if (stripos($path, $ex) !== false) {
                return false;
            }
        }

        // URL artikel biasanya punya path lebih dari 1 segmen
        $segments = array_filter(explode('/', trim($path, '/')));
        if (count($segments) < 1) {
            return false;
        }

        // Cek apakah mengandung pola artikel (slug atau hash panjang)
        $lastSegment = end($segments);
        return strlen($lastSegment) > 10;
    }

    private function browserHeaders(): array
    {
        return [
            'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
            'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection'      => 'keep-alive',
        ];
    }
}
