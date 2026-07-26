<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SearchEngineLandScraperService
{
    protected array $allowedDomains = [
        'searchengineland.com',
        'searchenginejournal.com',
    ];

    // AI-focused keywords - only accept articles about AI, machine learning, etc.
    protected array $aiKeywords = [
        'ai', 'artificial intelligence', 'machine learning', 'deep learning',
        'llm', 'large language model', 'generative ai', 'gen ai',
        'openai', 'chatgpt', 'gpt', 'gemini', 'claude', 'deepseek', 'mistral',
        'anthropic', 'google ai', 'microsoft ai', 'meta ai',
        'neural network', 'transformer model', 'rag system', 'rag llm',
        'ai seo', 'seo ai', 'ai search', 'search engine ai',
        'automation', 'autonomous', 'agentic ai', 'ai agent',
        'robotics', 'robot', 'autonomous system',
        'data science', 'data analytics', 'predictive ai',
        'nlp', 'natural language processing', 'computer vision',
        'ai chip', 'ai hardware', 'tpu', 'npu',
        'ai startup', 'ai funding', 'ai investment',
        'ai regulation', 'ai policy', 'ai ethics', 'ai safety',
        'chatbot', 'conversational ai', 'virtual assistant',
        'ai platform', 'ai infrastructure', 'ai cloud',
        'enterprise ai', 'ai adoption', 'ai strategy',
        'seo', 'search engine optimization', 'google search', 'google algorithm',
        'bing search', 'search algorithm', 'serp', 'organic search',
        'content marketing', 'link building', 'technical seo',
    ];

    // Generic tech/HP keywords to REJECT
    protected array $negativeKeywords = [
        'iphone review', 'samsung review', 'xiaomi review', 'oppo review', 'vivo review',
        'hp murah', 'hp terbaik', 'hp gaming murah', 'rekomendasi hp',
        'perbandingan iphone', 'vs iphone', 'vs samsung', 'vs xiaomi',
        'harga iphone', 'harga samsung', 'spesifikasi iphone',
        'wordle', 'crossword', 'nyt mini', 'strands', 'connections', 'quordle',
        'puzzle hint', 'puzzle answer', 'daily puzzle',
        'budget phone', 'cheap smartphone', 'affordable phone',
    ];

    public function fetchArticleDetail(string $url): ?array
    {
        $domain = parse_url($url, PHP_URL_HOST);

        try {
            $response = Http::timeout(60)
                ->withHeaders($this->browserHeaders())
                ->get($url);

            if (!$response->successful()) {
                Log::warning('SELScraper: HTTP error', ['url' => $url, 'status' => $response->status()]);
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
                'domain'       => $domain,
            ];

        } catch (\Exception $e) {
            Log::error('SELScraper: exception', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    public function isValidArticle(?array $article): bool
    {
        if (!$article) return false;
        if (empty($article['title']) || empty($article['content'])) return false;

        // MUST have image
        if (empty($article['image_url'])) {
            Log::debug('SELScraper: skip - no image');
            return false;
        }

        $plainContent = strip_tags($article['content']);

        // Min 200 chars content
        if (strlen($plainContent) < 200) {
            Log::debug('SELScraper: skip - content too short (' . strlen($plainContent) . ' chars)');
            return false;
        }

        // Detect comparison tables (yes/no patterns)
        $yesNoCount = substr_count(strtolower($plainContent), 'yes')
                    + substr_count(strtolower($plainContent), 'no');
        if ($yesNoCount > 3 && strlen($plainContent) < 2000) {
            Log::debug('SELScraper: skip - detected comparison table');
            return false;
        }

        // Detect price lists
        if (preg_match('/\$[\d,]+.*\$[\d,]+.*\$[\d,]+/', $plainContent) && strlen($plainContent) < 3000) {
            Log::debug('SELScraper: skip - detected price list');
            return false;
        }

        // Min paragraph count
        $paragraphCount = substr_count($plainContent, "\n\n");
        if ($paragraphCount < 3 && strlen($plainContent) < 1000) {
            Log::debug('SELScraper: skip - not enough paragraphs');
            return false;
        }

        // Negative keyword check on title
        $titleLower = strtolower($article['title']);
        foreach ($this->negativeKeywords as $neg) {
            if (strpos($titleLower, $neg) !== false) {
                Log::debug("SELScraper: skip - negative keyword '{$neg}' in title");
                return false;
            }
        }

        // MUST have at least 1 AI keyword in title or content
        $haystack = strtolower($article['title'] . ' ' . $plainContent);
        $hasAiKeyword = false;
        foreach ($this->aiKeywords as $kw) {
            if (strpos($haystack, $kw) !== false) {
                $hasAiKeyword = true;
                break;
            }
        }

        if (!$hasAiKeyword) {
            Log::debug('SELScraper: skip - not AI-focused content');
            return false;
        }

        return true;
    }

    public function isAccessibleUrl(string $url): bool
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->head($url);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isValidDomain(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) return false;

        foreach ($this->allowedDomains as $domain) {
            if (stripos($host, $domain) !== false) {
                return true;
            }
        }
        return false;
    }

    private function extractTitle(string $html): string
    {
        // og:title first
        if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            return html_entity_decode(trim($m[1]), ENT_QUOTES);
        }
        // fallback: title tag
        if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $m)) {
            $title = html_entity_decode(trim($m[1]), ENT_QUOTES);
            // Remove site name suffix
            return trim(preg_replace('/\s*[-|]\s*(Search Engine Land|Search Engine Journal|SEL|SEJ)$/i', '', $title));
        }
        return '';
    }

    private function extractContent(string $html): string
    {
        // Step 1: Remove all junk
        $html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
        $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
        $html = preg_replace('/<nav[^>]*>.*?<\/nav>/is', '', $html);
        $html = preg_replace('/<footer[^>]*>.*?<\/footer>/is', '', $html);
        $html = preg_replace('/<aside[^>]*>.*?<\/aside>/is', '', $html);
        $html = preg_replace('/<form[^>]*>.*?<\/form>/is', '', $html);
        $html = preg_replace('/<iframe[^>]*>.*?<\/iframe>/is', '', $html);
        $html = preg_replace('/<!--.*?-->/s', '', $html);
        $html = preg_replace('/<dialog[^>]*>.*?<\/dialog>/is', '', $html);

        // Step 2: Remove all HTML attributes
        $html = preg_replace('/<(\w+)[^>]*\s+(?:class|id|data-[a-z-]+|aria-[a-z-]+|onclick|onerror|onload)[^>]*>/i', '<$1>', $html);

        // Step 3: Remove tracking links and ad divs
        $html = preg_replace('/<a[^>]+class=["\'][^"\']*(?:sponsored|affiliate|cta-button|promo)[^"\']*["\'][^>]*>.*?<\/a>/is', '', $html);
        $html = preg_replace('/<div[^>]+class=["\'][^"\']*(?:ad|advertisement|sponsor|promo|banner|related|recommend|sidebar|social-share|share-btn|comment|coupon|newsletter|promo-box)[^"\']*["\'][^>]*>.*?<\/div>/is', '', $html);

        // Step 4: Remove buttons
        $html = preg_replace('/<button[^>]*>.*?<\/button>/is', '', $html);

        // Step 5: Remove images and figures
        $html = preg_replace('/<img[^>]*>/i', '', $html);
        $html = preg_replace('/<figure[^>]*>.*?<\/figure>/is', '', $html);
        $html = preg_replace('/<figcaption[^>]*>.*?<\/figcaption>/is', '', $html);

        // Step 6: Replace links with text only
        $html = preg_replace('/<a[^>]+href=["\'][^"\']+["\'][^>]*>(.*?)<\/a>/is', '$1', $html);

        // Step 7: Convert br to paragraph separators
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);

        // Step 8: Extract text from block elements
        $lines = [];
        $blockPatterns = [
            '/<p[^>]*>([\s\S]*?)<\/p>/i',
            '/<h1[^>]*>([\s\S]*?)<\/h1>/i',
            '/<h2[^>]*>([\s\S]*?)<\/h2>/i',
            '/<h3[^>]*>([\s\S]*?)<\/h3>/i',
            '/<h4[^>]*>([\s\S]*?)<\/h4>/i',
            '/<li[^>]*>([\s\S]*?)<\/li>/i',
            '/<blockquote[^>]*>([\s\S]*?)<\/blockquote>/i',
        ];

        foreach ($blockPatterns as $pattern) {
            if (preg_match_all($pattern, $html, $matches)) {
                foreach ($matches[1] as $text) {
                    $text = strip_tags($text);
                    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $text = trim($text);
                    $text = preg_replace('/\s+/', ' ', $text);

                    // Clean noise
                    $text = preg_replace('/^(Advertisement|Subscribe|Share|Read more|Follow us|Sign up).*$/i', '', $text);
                    $text = preg_replace('/^\d+\s*min read$/i', '', $text);
                    $text = preg_replace('/^(Mon|Tue|Wed|Thu|Fri|Sat|Sun),?\s+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+\d+,?\s+\d{4}/i', '', $text);

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
        if (preg_match('/<a[^>]+class=["\'][^"\']*(?:author|byline)[^"\']*["\'][^>]*>([^<]+)</i', $html, $m)) {
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
        $tags = ['AI', 'SEO'];

        if (preg_match_all('/"keywords"\s*:\s*\[([^\]]+)\]/i', $html, $m)) {
            preg_match_all('/"([^"]+)"/', $m[1][0], $kw);
            foreach ($kw[1] as $tag) {
                $tag = trim($tag);
                if ($tag && strlen($tag) < 30 && !in_array($tag, $tags)) {
                    $tags[] = $tag;
                }
            }
        }

        // Extract from URL path
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            foreach (array_filter(explode('/', $path)) as $seg) {
                $seg = ucfirst(str_replace('-', ' ', $seg));
                if (strlen($seg) > 2 && strlen($seg) < 30 && !in_array($seg, $tags)) {
                    $tags[] = $seg;
                }
            }
        }

        return array_slice(array_unique($tags), 0, 5);
    }

    private function browserHeaders(): array
    {
        return [
            'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/126.0.0.0 Safari/537.36',
            'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection'      => 'keep-alive',
            'Referer'         => 'https://www.google.com/',
        ];
    }
}
