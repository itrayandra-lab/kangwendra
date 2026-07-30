<?php

namespace App\Jobs;

use App\Models\EditorPreference;
use App\Models\PostCategory;
use App\Models\PostTags;
use App\Models\Posts;
use App\Models\RefArticle;
use App\Services\EditorPreferenceService;
use App\Services\SearchEngineLandScraperService;
use DateTime;
use DateTimeZone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateFromRefArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;
    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        public int $refArticleId
    ) {}

    public function handle(
        SearchEngineLandScraperService $scraper,
        EditorPreferenceService $prefService
    ): void {
        set_time_limit(900);

        $ref = RefArticle::find($this->refArticleId);
        if (!$ref) {
            Log::warning('GenerateFromRefArticleJob: RefArticle not found', ['id' => $this->refArticleId]);
            return;
        }

        Log::info('GenerateFromRefArticleJob: START', [
            'ref_id'   => $ref->id,
            'url'      => $ref->source_url,
            'keyword'  => $ref->source_keyword,
        ]);

        // ── STEP 1: SCRAPE ─────────────────────────────────────────────
        $article = $scraper->fetchArticleDetail($ref->source_url);

        if (!$article) {
            Log::warning('GenerateFromRefArticleJob: failed to fetch', ['url' => $ref->source_url]);
            $ref->update(['ai_research_status' => 'failed', 'ai_error' => 'Failed to fetch article from source URL']);
            $prefService->recordRejection($ref->source_keyword ?? 'general', $ref->source_url, $this->extractTopic($ref->title ?? ''));
            throw new \Exception('Failed to fetch article from source URL');
        }

        // ── STEP 2: VALIDATE ────────────────────────────────────────────
        if (!$scraper->isValidArticle($article)) {
            Log::warning('GenerateFromRefArticleJob: validation failed', ['url' => $ref->source_url]);
            $ref->update([
                'ai_research_status' => 'failed',
                'ai_error'          => 'Article validation failed - not AI-focused, missing content, or no image',
            ]);
            $prefService->recordRejection($ref->source_keyword ?? 'general', $ref->source_url, $this->extractTopic($ref->title ?? ''));
            throw new \Exception('Article validation failed');
        }

        // ── STEP 3: UPDATE REFARTICLE with scraped content ────────────────
        $ref->update([
            'title'               => $article['title'],
            'content'            => $article['content'],
            'image_url'          => $article['image_url'],
            'author'             => $article['author'],
            'published_at'       => $article['published_at'],
            'tags'               => $article['tags'] ?? [],
            'ai_research_status' => 'researching',
        ]);

        // ── STEP 4: PARAPHRASE (DEEPSEEK) ────────────────────────────
        try {
            $generated = $this->callDeepSeek($article['title'], $article['content']);
        } catch (\Exception $e) {
            $ref->update(['ai_research_status' => 'failed', 'ai_error' => $e->getMessage()]);
            Log::error('GenerateFromRefArticleJob: DeepSeek failed', ['ref_id' => $ref->id, 'error' => $e->getMessage()]);
            throw $e;
        }

        // Validate generated content
        $contentText = strip_tags($generated['content'] ?? '');
        if (strlen($contentText) < 500) {
            $ref->update(['ai_research_status' => 'failed', 'ai_error' => 'Content too short (' . strlen($contentText) . ' chars)']);
            throw new \Exception('Generated content too short: ' . strlen($contentText) . ' chars');
        }
        if (empty(trim($generated['title'] ?? ''))) {
            $ref->update(['ai_research_status' => 'failed', 'ai_error' => 'No title generated']);
            throw new \Exception('No title generated');
        }

        // Check duplicate title
        $normalizedTitle = trim(strtolower($generated['title']));
        $existingByTitle = Posts::whereRaw('LOWER(TRIM(title)) = ?', [$normalizedTitle])->first();
        if ($existingByTitle) {
            $ref->update(['ai_research_status' => 'failed', 'ai_error' => 'Duplicate title: ' . $generated['title']]);
            throw new \Exception("Duplicate title: '{$generated['title']}'");
        }

        // ── STEP 5: SAVE POST ──────────────────────────────────────────
        $post = $this->savePost($generated, $ref, $article);

        // ── STEP 6: UPDATE REFARTICLE as done ─────────────────────────────
        $ref->update([
            'ai_research_status' => 'done',
            'generated_post_id'   => $post->id,
            'ai_error'           => null,
            'content'            => null, // cleanup to save DB
        ]);

        // ── STEP 7: RECORD AI LEARNING ────────────────────────────────
        $prefService->recordApproval(
            $ref->source_keyword ?? 'general',
            $this->extractTopic($ref->title ?? ''),
            $ref->source_url,
            $ref->ai_confidence ?? 50
        );

        Log::info('GenerateFromRefArticleJob: SUCCESS', [
            'ref_id'      => $ref->id,
            'post_id'     => $post->id,
            'url'         => $ref->source_url,
            'published_at'=> $post->published_at,
        ]);
    }

    private function callDeepSeek(string $title, string $content): array
    {
        $apiKey  = config('services.deepseek.key');
        $model   = config('services.deepseek.model', 'deepseek-v4-pro');
        $baseUrl = config('services.deepseek.base_url', 'https://api.deepseek.com');

        if (!$apiKey) {
            throw new \Exception('DEEPSEEK_API_KEY belum dikonfigurasi');
        }

        $maxTokens = (int) config('services.deepseek.max_tokens', 16384);
        $payload = [
            'model'    => $model,
            'messages' => [
                [
                    'role'    => 'system',
                    'content' => 'Kamu adalah penulis artikel teknologi profesional dalam Bahasa Indonesia. '
                               . 'Tulis artikel yang informatif, mudah dipahami, dan tidak bertele-tele. '
                               . 'Jangan pernah menyalin kalimat dari sumber asli. Selalu kembalikan JSON.',
                ],
                [
                    'role'    => 'user',
                    'content' => $this->buildPrompt($title, $content),
                ],
            ],
            'max_tokens'  => $maxTokens,
            'temperature'  => 0.7,
        ];

        $response = Http::timeout(600)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])
            ->post("{$baseUrl}/chat/completions", $payload);

        if ($response->failed()) {
            $body = $response->json();
            throw new \Exception("DeepSeek API error [{$response->status()}]: " . ($body['error']['message'] ?? $response->body()));
        }

        $body = $response->json();

        if (isset($body['usage'])) {
            Log::info('DeepSeek usage (GenerateFromRefArticleJob)', [
                'prompt_tokens'     => $body['usage']['prompt_tokens'] ?? 0,
                'completion_tokens' => $body['usage']['completion_tokens'] ?? 0,
                'total_tokens'      => $body['usage']['total_tokens'] ?? 0,
            ]);
        }

        $raw = trim($body['choices'][0]['message']['content'] ?? '');

        Log::debug('DeepSeek raw (GenerateFromRefArticleJob)', [
            'title'   => Str::limit($title, 60),
            'length'  => strlen($raw),
            'preview' => Str::limit($raw, 200),
        ]);

        // Try direct parse first
        $decoded = json_decode($raw, true);

        // Fallback: markdown code block
        if (!$decoded && preg_match('/```json\s*([\s\S]+?)```/', $raw, $m)) {
            $decoded = json_decode(trim($m[1]), true);
        }

        // Fallback: extract JSON object — handle truncated responses
        if (!$decoded) {
            $decoded = $this->extractJsonObject($raw);
        }

        if (!$decoded || !isset($decoded['title'], $decoded['content'])) {
            throw new \Exception('DeepSeek returned invalid format: ' . Str::limit($raw, 300));
        }

        // ── NORMALIZE CONTENT ─────────────────────────────────────────
        // Replace actual newlines (\n \r) and literal \n with space
        // HTML tags (<p>, <h2>, <strong>) already define structure
        $decoded['content'] = trim(
            preg_replace('/\\\\n|\\\\r|\r\n|\r|\n/', ' ', $decoded['content'] ?? '')
        );

        return $decoded;
    }

    private function buildPrompt(string $title, string $content): string
    {
        return <<<PROMPT
Kamu adalah penulis artikel teknologi profesional yang menulis dalam Bahasa Indonesia.

Tugas: Tulis artikel baru yang detail dan lengkap berdasarkan artikel di bawah ini.

ATURAN:
1. Judul: menarik, unik, dalam Bahasa Indonesia, MINIMAL 60 karakter
2. Content: MINIMAL 8 PARAGRAF. Setiap paragraf 3-4 kalimat. Gunakan <h2> untuk sub-judul. Pakai <p>. Tambahkan <strong> untuk emphasis.
3. Excerpt: ringkasan 2-3 kalimat
4. Tags: 5 tags yang relevan dalam Bahasa Indonesia
5. Meta description: maks 150 karakter

---
JUDUL ASLI: {$title}

ISI ARTIKEL REFERENSI:
{$content}
---

FORMAT OUTPUT (HANYA JSON - tidak ada penjelasan lain):
{
  "title": "Judul baru yang menarik dalam Bahasa Indonesia",
  "content": "HTML content lengkap dengan 8+ paragraf panjang",
  "excerpt": "Ringkasan 2-3 kalimat",
  "tags": ["tag1", "tag2", "tag3", "tag4", "tag5"],
  "meta_description": "Deskripsi SEO maks 150 karakter"
}
PROMPT;
    }

    private function savePost(array $generated, RefArticle $ref, array $article): Posts
    {
        $adminUser = \App\Models\User::role('admin')->first();
        if (!$adminUser) {
            throw new \Exception('Admin user tidak ditemukan');
        }

        $titleLower  = strtolower($ref->title . ' ' . ($generated['title'] ?? ''));
        $contentLower = strtolower(strip_tags($generated['content'] ?? ''));
        $fullText = $titleLower . ' ' . $contentLower;

        $tagRules = [
            ['keywords' => ['ai', 'chatgpt', 'gpt-4', 'gpt-5', 'llm', 'openai', 'gemini', 'claude', 'deepseek', 'generative ai', 'generative', 'machine learning', 'deep learning', 'artificial intelligence', 'neural network', 'langchain', 'copilot', 'mistral', 'anthropic', 'ai agent', 'agentic', 'rag system', 'rag llm', 'llm fine-tuning', 'ai model', 'foundation model'],
                'label' => 'AI & Teknologi'],
            ['keywords' => ['seo', 'search engine optimization', 'google search', 'google algorithm', 'search algorithm', 'serp', 'organic search', 'search engine land', 'search engine journal', 'ai seo', 'seo ai', 'local seo', 'technical seo', 'backlink', 'link building', 'content marketing seo', 'google spam', 'search quality'],
                'label' => 'SEO & Search'],
            ['keywords' => ['startup', 'venture capital', 'funding', 'ipo', 'bisnis teknologi', 'unicorn', 'ekonomi digital', 'digital economy', 'enterprise ai', 'enterprise software', 'saas', 'cloud saas', 'b2b tech'],
                'label' => 'Bisnis Teknologi'],
            ['keywords' => ['nvidia', 'gpu', 'processor', 'chip', 'intel', 'amd ryzen', 'snapdragon', 'mediatek', 'ram', 'ssd', 'hdd', 'storage', 'vga', 'cpu', 'ai chip', 'ai hardware', 'tpu', 'npu'],
                'label' => 'Hardware'],
            ['keywords' => ['cybersecurity', 'hack', 'malware', 'virus', 'data breach', 'privacy', 'privasi', 'keamanan data', 'peretas', 'ransomware', 'phishing', 'kebocoran data', 'zero day'],
                'label' => 'Keamanan Siber'],
            ['keywords' => ['apple', 'mac', 'ipad', 'ios', 'macos', 'apple tv', 'apple watch', 'macbook air', 'macbook pro', 'imac', 'wwdc'],
                'label' => 'Apple'],
            ['keywords' => ['google android', 'android', 'google pixel', 'android tv', 'google play', 'samsung one ui', 'xiaomi hyperos', 'oppo coloros', 'realme ui', 'vivo funtouch'],
                'label' => 'Android'],
            ['keywords' => ['microsoft', 'windows', 'copilot', 'azure', 'office 365', 'bing', 'outlook', 'teams', 'xbox'],
                'label' => 'Microsoft'],
            ['keywords' => ['farmasi', 'pharmaceutical', 'drug discovery', 'clinical trial', 'biotech', 'biotechnology', 'obat baru', 'pengembangan obat', 'clinical research', 'regulatory', 'generik obat', 'telemedicine', 'digital health', 'healthtech', 'ehr', 'electronic health record', 'medical device', 'robotics surgery', 'hospital teknologi', 'health data'],
                'label' => 'Farmasi & Kesehatan'],
            ['keywords' => ['cloud', 'server', 'data center', 'database', 'cloud computing', 'aws', 'google cloud', 'microsoft azure', 'oracle', 'web hosting', 'infrastructure'],
                'label' => 'Cloud Computing'],
            ['keywords' => ['satelit', 'satelite', 'roket', 'spacex', 'nasa', 'luar angkasa', 'planet', 'bintang', 'galaxy', 'black hole', 'teleskop', 'antariksa'],
                'label' => 'Sains'],
            ['keywords' => ['tips', 'tutorial', 'cara', 'guide', 'panduan', 'review', 'ulasan', 'perbandingan', 'vs', 'rekomendasi', 'how to', 'trik', 'best practice'],
                'label' => 'Tips & Review'],
            ['keywords' => ['crypto', 'bitcoin', 'ethereum', 'blockchain', 'nft', 'web3', 'defi', 'cryptocurrency', 'trading crypto'],
                'label' => 'Crypto'],
        ];

        $matchedTags = [];
        $matchedCategories = [];
        foreach ($tagRules as $rule) {
            foreach ($rule['keywords'] as $kw) {
                if (strpos($fullText, $kw) !== false) {
                    $matchedTags[] = $rule['label'];
                    break;
                }
            }
        }

        $aiTags = is_array($generated['tags'] ?? null) ? $generated['tags'] : [];
        $allTags = array_unique(array_merge(['AI', 'Teknologi'], $matchedTags, $aiTags));
        $tags = array_slice(array_values($allTags), 0, 8);

        $category = PostCategory::where('slug', 'teknologi')->first();

        foreach ($tags as $tagName) {
            PostTags::firstOrCreate(
                ['slug' => Str::slug($tagName)],
                ['name' => $tagName]
            );
        }

        $slug = Str::slug($generated['title']);
        $base = $slug;
        $i = 1;
        while (Posts::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        // Auto-schedule to next available slot from ScraperConfig
        $tz = new DateTimeZone('Asia/Jakarta');
        $tomorrowDt = new DateTime('tomorrow', $tz);
        $tomorrowDt->modify('-1 second');
        $todayEnd = $tomorrowDt->format('Y-m-d H:i:s');

        $existingToday = Posts::whereBetween('published_at', [(new DateTime('today', $tz))->format('Y-m-d H:i:s'), $todayEnd])->get();

        $scheduleHours = \App\Models\ScraperConfig::getPublishScheduleHours();
        $slots = [];
        foreach ($scheduleHours as $time) {
            $parts = explode(':', $time);
            $hour = (int) ($parts[0] ?? 0);
            $slots[] = ['hour' => $hour, 'label' => self::getSlotLabel($hour)];
        }

        $usedSlots = [];
        foreach ($existingToday as $p) {
            $hour = (int) $p->published_at->format('H');
            foreach ($slots as $idx => $slot) {
                if ($hour === $slot['hour']) { $usedSlots[$idx] = true; break; }
            }
        }

        $slotIdx = null;
        $slotCount = count($slots);
        for ($i = 0; $i < $slotCount; $i++) {
            if (!isset($usedSlots[$i])) { $slotIdx = $i; break; }
        }

        if ($slotIdx === null) {
            // Semua slot penuh → assign ke slot pertama HARI INI
            $slotIdx = 0;
            $publishTime = new DateTime('today', $tz);
            $publishTime->setTime($slots[0]['hour'], 0, 0);
        } else {
            if (count($usedSlots) > 0) {
                $publishTime = new DateTime('tomorrow', $tz);
            } else {
                $publishTime = new DateTime('today', $tz);
            }
            $publishTime->setTime($slots[$slotIdx]['hour'], 0, 0);
        }

        return Posts::create([
            'title'        => $generated['title'],
            'slug'        => $slug,
            'content'     => $generated['content'],
            'image'        => $ref->image_url,
            'source'      => $ref->source_url,
            'domain'      => $ref->source_domain,
            'status'      => 'active',
            'published_by'=> 'system',
            'category_id' => $category->id,
            'created_by'  => $adminUser->id,
            'published_at'=> $publishTime->format('Y-m-d H:i:s'),
            'counter'     => 0,
            'tags'        => $tags,
            'meta_data'  => [
                'seo_title'      => $generated['title'],
                'seo_desc'       => $generated['meta_description'] ?? Str::limit(strip_tags($generated['content'] ?? ''), 160),
                'excerpt'        => $generated['excerpt'] ?? '',
                'ref_article_id' => $ref->id,
                'ref_source_url' => $ref->source_url,
                'ref_title'      => $ref->title,
                'ai_model'      => config('services.deepseek.model', 'deepseek-v4-pro'),
                'publish_slot'   => "slot_{$slotIdx}_{$slots[$slotIdx]['label']}",
                'batch_id'       => $ref->batch_id,
            ],
        ]);
    }

    private function extractTopic(string $title): string
    {
        $aiTopics = ['ai', 'llm', 'machine learning', 'deep learning', 'openai', 'gemini', 'chatgpt', 'neural', 'generative', 'agentic', 'rag', 'claude', 'deepseek'];
        foreach ($aiTopics as $topic) {
            if (stripos($title, $topic) !== false) return 'AI & Teknologi';
        }
        $seoTopics = ['seo', 'search', 'google', 'algorithm', 'serp'];
        foreach ($seoTopics as $topic) {
            if (stripos($title, $topic) !== false) return 'SEO & Search';
        }
        return 'Teknologi';
    }

    public function failed(\Throwable $e): void
    {
        Log::error('GenerateFromRefArticleJob: FAILED', [
            'ref_id' => $this->refArticleId,
            'error'  => $e->getMessage(),
        ]);

        RefArticle::where('id', $this->refArticleId)->update([
            'ai_research_status' => 'failed',
            'ai_error'          => $e->getMessage(),
        ]);
    }

    /**
     * Get human-readable label for a publish hour.
     */
    protected static function getSlotLabel(int $hour): string
    {
        if ($hour < 12) return 'pagi';
        if ($hour < 17) return 'siang';
        return 'sore';
    }

    /**
     * Extract JSON object from text, respecting string boundaries.
     * Handles truncated responses by finding the first { and matching its closing }.
     */
    protected function extractJsonObject(string $text): ?array
    {
        $start = strpos($text, '{');
        if ($start === false) {
            return null;
        }

        $len = strlen($text);
        $depth = 0;
        $inString = false;
        $escape = false;

        for ($i = $start; $i < $len; $i++) {
            $c = $text[$i];

            if ($escape) {
                $escape = false;
                continue;
            }

            if ($c === '\\') {
                $escape = true;
                continue;
            }

            if ($c === '"') {
                $inString = !$inString;
                continue;
            }

            if ($inString) continue;

            if ($c === '{') {
                $depth++;
            } elseif ($c === '}') {
                $depth--;
                if ($depth === 0) {
                    // Found matching close brace
                    $json = substr($text, $start, $i - $start + 1);
                    return json_decode($json, true);
                }
            }
        }

        // Truncated — no closing brace found, return what we have
        $json = substr($text, $start);
        return json_decode($json, true);
    }
}
