<?php

namespace App\Jobs;

use App\Models\Posts;
use App\Models\PostCategory;
use App\Models\PostTags;
use App\Models\RefArticle;
use App\Models\User;
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

class GenerateAiArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;
    public int $tries   = 2;
    public int $backoff = 60;

    public function __construct(protected int $refArticleId) {}

    public function handle(): void
    {
        set_time_limit(900);

        // Get fresh instance every time
        $ref = RefArticle::find($this->refArticleId);

        if (!$ref || !in_array($ref->ai_status, ['pending', 'processing'])) {
            return;
        }

        // Skip if already linked to a post
        if ($ref->generated_post_id) {
            $ref->update(['ai_status' => 'done']);
            return;
        }

        // Skip if post already exists for this source URL (idempotency)
        $existingPost = Posts::where('source', $ref->source_url)->first();
        if ($existingPost) {
            $ref->update([
                'ai_status' => 'done',
                'generated_post_id' => $existingPost->id,
            ]);
            return;
        }

        $ref->update(['ai_status' => 'processing']);

        try {
            $generated = $this->callDeepSeek($ref->title, $ref->content);

            // VALIDASI: minimum 500 chars content
            $contentText = strip_tags($generated['content'] ?? '');
            if (strlen($contentText) < 500) {
                throw new \Exception("Konten terlalu pendek (" . strlen($contentText) . " chars). Minimal 500 chars.");
            }

            // VALIDASI: harus ada title
            if (empty(trim($generated['title'] ?? ''))) {
                throw new \Exception("AI tidak menghasilkan title yang valid.");
            }

            // VALIDASI: duplicate title (case-insensitive)
            $normalizedTitle = trim(strtolower($generated['title']));
            $existingByTitle = Posts::whereRaw('LOWER(TRIM(title)) = ?', [$normalizedTitle])->first();
            if ($existingByTitle) {
                throw new \Exception("Judul duplikat: '{$generated['title']}' sudah ada (post #{$existingByTitle->id}).");
            }

            $post = $this->savePost($generated, $ref);

            // IMPORTANT: get fresh instance so we don't use stale model data
            $freshRef = RefArticle::find($this->refArticleId);
            if ($freshRef) {
                $freshRef->update([
                    'ai_status'         => 'done',
                    'generated_post_id' => $post->id,
                    'ai_error'          => null,
                ]);
            }

            Log::info("GenerateAiArticleJob: berhasil generate artikel [{$post->id}] dari ref [{$this->refArticleId}]");

        } catch (\Exception $e) {
            RefArticle::where('id', $this->refArticleId)->update([
                'ai_status' => 'failed',
                'ai_error' => $e->getMessage(),
            ]);
            Log::error("GenerateAiArticleJob: gagal untuk ref [{$this->refArticleId}]", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function callDeepSeek(string $refTitle, string $refContent): array
    {
        $apiKey  = config('services.deepseek.key');
        $model   = config('services.deepseek.model', 'deepseek-v4-pro');
        $baseUrl = config('services.deepseek.base_url', 'https://api.deepseek.com');

        if (!$apiKey) {
            throw new \Exception('DEEPSEEK_API_KEY belum dikonfigurasi di .env');
        }

        $payload = [
            'model'   => $model,
            'messages' => [
                [
                    'role'    => 'system',
                    'content' => 'Kamu adalah penulis artikel teknologi dalam Bahasa Indonesia. '
                               . 'Tulis artikel yang simpel, mudah dipahami, dan tidak bertele-tele. '
                               . 'Jangan pernah menyalin kalimat dari sumber asli. Selalu kembalikan JSON.',
                ],
                [
                    'role'    => 'user',
                    'content' => $this->buildPrompt($refTitle, $refContent),
                ],
            ],
            'response_format'  => ['type' => 'json_object'],
            'max_tokens'       => 8192,
            'temperature'       => 0.7,
            'reasoning_effort' => 'high',
        ];

        if ($model === 'deepseek-v4-pro') {
            $payload['extra_body'] = ['thinking' => ['type' => 'enabled']];
        }

        $response = Http::timeout(600)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])
            ->post("{$baseUrl}/chat/completions", $payload);

        if ($response->failed()) {
            $errorBody = $response->json();
            $errorMsg  = $errorBody['error']['message'] ?? $response->body();
            throw new \Exception("DeepSeek API error [{$response->status()}]: {$errorMsg}");
        }

        $body = $response->json();

        if (isset($body['usage'])) {
            Log::info('DeepSeek usage', [
                'ref_id'             => $this->refArticleId,
                'model'              => $model,
                'prompt_tokens'       => $body['usage']['prompt_tokens'] ?? 0,
                'completion_tokens'   => $body['usage']['completion_tokens'] ?? 0,
                'total_tokens'        => $body['usage']['total_tokens'] ?? 0,
                'cache_hit_tokens'    => $body['usage']['prompt_cache_hit_tokens'] ?? 0,
                'cache_miss_tokens'   => $body['usage']['prompt_cache_miss_tokens'] ?? 0,
            ]);
        }

        $raw     = $body['choices'][0]['message']['content'] ?? '';
        $decoded = json_decode($raw, true);

        if (!$decoded || empty($decoded['title']) || empty($decoded['content'])) {
            throw new \Exception('DeepSeek mengembalikan format tidak valid: ' . Str::limit($raw, 300));
        }

        return $decoded;
    }

    private function buildPrompt(string $title, string $content): string
    {
        // Ambil lebih banyak konten agar AI punya cukup bahan
        $truncatedContent = Str::limit($content, 6000, '...');

        return <<<PROMPT
Kamu adalah penulis artikel teknologi profesional yang menulis dalam Bahasa Indonesia.

Tugas: Tulis ULANJUTAN artikel baru yang detail dan lengkap berdasarkan artikel di bawah ini. Jangan singkat-singkat — TULIS PANJANG dengan penjelasan yang mendalam.

ATURAN:
1. Judul: menarik, unik, dalam Bahasa Indonesia
2. Content: MINIMAL 8 PARAGRAF panjang. Setiap paragraf minimal 3-4 kalimat. Gunakan <h2> untuk sub-judul. Pakai <p> untuk paragraph. Tambahkan <strong> untuk kata penting.
3. Excerpt: ringkasan 2-3 kalimat
4. Tags: 5 tags yang relevan ( Bahasa Indonesia)
5. Meta description: maks 150 karakter
6. JANGAN POTONG konten di tengah penjelasan. Semua bagian harus lengkap.

---
JUDUL ASLI: {$title}

ISI ARTIKEL REFERENSI:
{$truncatedContent}
---

FORMAT OUTPUT (HANYA JSON - jangan tambahkan penjelasan lain):
{
  "title": "Judul baru yang menarik dalam Bahasa Indonesia",
  "content": "HTML content lengkap dengan 8+ paragraf panjang. Setiap paragraf 3-4 kalimat.",
  "excerpt": "Ringkasan 2-3 kalimat",
  "tags": ["tag1", "tag2", "tag3", "tag4", "tag5"],
  "meta_description": "Deskripsi SEO maks 150 karakter"
}
PROMPT;
    }

    private function savePost(array $generated, RefArticle $ref): Posts
    {
        $adminUser = User::role('admin')->first();
        if (!$adminUser) {
            throw new \Exception('Admin user tidak ditemukan.');
        }

        // --- DYNAMIC TAGS & CATEGORY based on title + content ---
        $titleLower = strtolower($ref->title . ' ' . ($generated['title'] ?? ''));
        $contentLower = strtolower(strip_tags($generated['content'] ?? ''));
        $fullText = $titleLower . ' ' . $contentLower;

        $tagRules = [
            // AI & Tech
            ['keywords' => ['ai', 'chatgpt', 'gpt-4', 'llm', 'openai', 'gemini', 'claude', 'deepseek', 'generative ai', 'machine learning', 'artificial intelligence', 'neural network', 'langchain', 'copilot', 'chatbot', 'mistral', 'anthropic'],
                'label' => 'AI & Teknologi', 'category' => 'Teknologi'],
            // Gadget & Devices
            ['keywords' => ['smartphone', 'hp android', 'iphone', 'samsung galaxy', 'xiaomi redmi', 'vivo phone', 'oppo phone', 'realme phone', 'gadget', 'ponsel', 'handphone', 'flagship', 'mid-range'],
                'label' => 'Smartphone', 'category' => 'Teknologi'],
            ['keywords' => ['laptop', 'notebook', 'macbook', 'chromebook', 'ultrabook', 'gaming laptop', 'dell xps', 'thinkpad', 'asus rog', 'acer aspire'],
                'label' => 'Laptop', 'category' => 'Teknologi'],
            ['keywords' => ['tablet', 'ipad', 'galaxy tab', 'huawei matepad', 'xiaomi pad'],
                'label' => 'Tablet', 'category' => 'Teknologi'],
            // Audio & Display
            ['keywords' => ['headphone', 'earbud', 'airpod', 'tws', 'bluetooth speaker', 'soundbar', 'speaker', 'audio', 'headset', 'buds', 'jbl', 'sony wh', 'bose'],
                'label' => 'Audio', 'category' => 'Teknologi'],
            ['keywords' => ['tv', 'televisi', 'oled', 'led tv', 'smart tv', 'layar lebar', 'lcd tv', 'qled', 'mini led', 'monitor', 'display', 'layar'],
                'label' => 'TV & Display', 'category' => 'Teknologi'],
            ['keywords' => ['wearable', 'smartwatch', 'smart band', 'fitness tracker', 'jam tangan pintar', 'galaxy watch', 'apple watch', 'garmin', 'huawei watch', 'amazfit'],
                'label' => 'Wearable', 'category' => 'Teknologi'],
            // Networking
            ['keywords' => ['router', 'wifi', 'internet', 'broadband', '5g', '4g lte', 'sim card', 'provider', 'telkomsel', 'xl axiata', 'indosat', 'tri', 'axis', 'operator seluler', 'jaringan'],
                'label' => 'Internet & Telco', 'category' => 'Teknologi'],
            // Business & Startup
            ['keywords' => ['startup', 'venture capital', 'funding', 'ipo', 'bisnis teknologi', 'unicorn', 'techno', 'ekonomi digital', 'digital economy'],
                'label' => 'Startup & Bisnis', 'category' => 'Bisnis'],
            // Hardware & Components
            ['keywords' => ['nvidia', 'gpu', 'processor', 'chip', 'intel', 'amd ryzen', 'snapdragon', 'mediatek', 'ram', 'ssd', 'hdd', 'storage', 'gpu gaming', 'vga', 'cpu'],
                'label' => 'Hardware', 'category' => 'Teknologi'],
            // Security
            ['keywords' => ['cybersecurity', 'hack', 'malware', 'virus', 'data breach', 'privacy', 'privasi', 'keamanan data', 'peretas', 'ransomware', 'phishing', 'kebocoran data'],
                'label' => 'Keamanan Siber', 'category' => 'Teknologi'],
            // Ecosystems
            ['keywords' => ['apple', 'iphone', 'mac', 'ipad', 'ios', 'macos', 'apple tv', 'apple watch', 'macbook air', 'macbook pro', 'imac', 'homepod', 'airpods', 'wwdc'],
                'label' => 'Apple', 'category' => 'Teknologi'],
            ['keywords' => ['google android', 'android', 'google pixel', 'android tv', 'google play', 'samsung one ui', 'xiaomi hyperos', 'oppo coloros', 'realme ui', 'vivo funtouch'],
                'label' => 'Android', 'category' => 'Teknologi'],
            ['keywords' => ['microsoft', 'windows', 'copilot', 'azure', 'office 365', 'bing', 'outlook', 'teams', 'xbox'],
                'label' => 'Microsoft', 'category' => 'Teknologi'],
            // Farmasi & Kesehatan (tech-focused)
            ['keywords' => ['farmasi', 'farmasi', 'pharmaceutical', 'drug discovery', 'clinical trial', 'biotech', 'biotechnology', 'obat baru', 'pengembangan obat', 'clinical research', 'regulatory', 'generik obat', 'medicine', 'telemedicine', 'digital health', 'healthtech', 'ehr', 'electronic health record', 'medical device', 'robotics surgery', 'hospital teknologi', 'patient data', 'health data'],
                'label' => 'Farmasi & Kesehatan', 'category' => 'Teknologi'],
            // Cloud & Data
            ['keywords' => ['cloud', 'server', 'data center', 'database', 'cloud computing', 'aws', 'google cloud', 'microsoft azure', 'oracle', 'ibm cloud', 'web hosting'],
                'label' => 'Cloud Computing', 'category' => 'Teknologi'],
            // Social Media & Content
            ['keywords' => ['media sosial', 'instagram', 'tiktok', 'youtube channel', 'x.com', 'twitter', 'facebook meta', 'social media', 'influencer', 'konten kreator', 'reels', 'shorts', 'tiktok shop'],
                'label' => 'Social Media', 'category' => 'Hiburan'],
            ['keywords' => ['streaming', 'netflix', 'spotify', 'disney+', 'hbomax', 'prime video', 'youtube premium', 'music streaming', 'video on demand'],
                'label' => 'Streaming', 'category' => 'Hiburan'],
            // Gaming
            ['keywords' => ['gaming', 'game', 'playstation', 'xbox', 'nintendo', 'steam', 'esport', 'pc gaming', 'console', 'game mobile', 'mobile legend', 'ff', 'free fire', 'genshin', 'roblox'],
                'label' => 'Gaming', 'category' => 'Gaming'],
            // Tips & Review
            ['keywords' => ['tips', 'tutorial', 'cara', 'guide', 'panduan', 'review', 'ulasan', 'pengalaman', 'perbandingan', 'vs', 'rekomendasi', 'pilihan terbaik', 'cara memilih', 'how to', 'trik'],
                'label' => 'Tips & Review', 'category' => 'Teknologi'],
            // Science
            ['keywords' => ['satelit', 'satelite', 'roket', 'spacex', 'nasa', 'luar angkasa', 'planet', 'bintang', 'galaxy', 'black hole', 'teleskop', 'antariksa'],
                'label' => 'Sains', 'category' => 'Sains'],
            ['keywords' => ['samsung', 'galaxy', 'one ui', 'galaxy unpacked', 'galaxy watch', 'galaxy buds', 'galaxy z fold', 'galaxy z flip', 'samsung pay'],
                'label' => 'Samsung', 'category' => 'Teknologi'],
            // Fashion & Lifestyle
            ['keywords' => ['fashion', 'mode', 'clothing', 'apparel', 'brand fashion', 'garment', 'busana', 'pakaian', 'tekstil', 'dress', 'sepatu', 'sneakers', 'nike', 'adidas'],
                'label' => 'Fashion', 'category' => 'Gaya Hidup'],
            ['keywords' => ['kecantikan', 'beauty', 'skincare', 'kosmetik', 'makeup', 'parfum', 'wellness', 'self-care', 'grooming'],
                'label' => 'Kecantikan', 'category' => 'Gaya Hidup'],
            ['keywords' => ['kesehatan', 'kesehatan mental', 'mental health', 'diet', 'nutrisi', 'vitamin', 'suplemen', 'workout', 'olahraga', 'yoga', 'meditasi'],
                'label' => 'Kesehatan', 'category' => 'Kesehatan'],
            // Otomotif - SPECIFIC brands/models only, no generic words
            ['keywords' => ['tesla', 'ev', 'electric vehicle', 'mobil listrik', 'motor listrik', 'e-bike', 'energi terbarukan', 'solar panel', 'hev', 'byd', 'hyundai ev', 'wolf mobil', 'toyota', 'honda', 'suzuki', 'daihatsu', 'wuling', 'ferrari', 'lamborghini', 'porsche', 'bmw', 'mercedes', 'audi', 'lexus', 'mazda', 'subaru', 'mitsubishi', 'jeep', 'ford', 'chevrolet', 'volkswagen', 'peugeot', 'renault', 'nissan', 'kia'],
                'label' => 'Otomotif', 'category' => 'Otomotif'],
            // Finance
            ['keywords' => ['crypto', 'bitcoin', 'ethereum', 'blockchain', 'nft', 'web3', 'defi', 'cryptocurrency', 'trading crypto'],
                'label' => 'Crypto', 'category' => 'Keuangan'],
            ['keywords' => ['investor', 'investasi', 'saham', 'trading', 'portofolio', 'reksadana', 'obligasi', 'financial', 'pendanaan'],
                'label' => 'Investasi', 'category' => 'Keuangan'],
            // Education
            ['keywords' => ['pendidikan', 'edtech', 'kursus online', 'pelajaran', 'sekolah', 'universitas', 'beasiswa', 'skill', 'pelatihan', 'certification'],
                'label' => 'Pendidikan', 'category' => 'Pendidikan'],
        ];

        $matchedTags = [];
        $matchedCategories = [];
        foreach ($tagRules as $rule) {
            foreach ($rule['keywords'] as $kw) {
                if (strpos($fullText, $kw) !== false) {
                    $matchedTags[] = $rule['label'];
                    $matchedCategories[$rule['category']] = true;
                    break;
                }
            }
        }

        // AI-generated tags + matched tags + source tag
        $aiTags = is_array($generated['tags'] ?? null) ? $generated['tags'] : [];
        $sourceTag = 'Teknologi';
        $allTags = array_unique(array_merge([$sourceTag], $matchedTags, $aiTags));
        $tags = array_slice(array_values($allTags), 0, 8);

        $categoryPriority = [
            'Keuangan'    => 1,
            'Gaya Hidup'  => 1,
            'Kesehatan'   => 1,
            'Pendidikan'  => 1,
            'Hiburan'     => 1,
            'Gaming'      => 1,
            'Bisnis'      => 2,
            'Sains'       => 2,
            'Teknologi'   => 3,
            'Otomotif'    => 4,
        ];
        $categoryNames = array_keys($matchedCategories);
        usort($categoryNames, function($a, $b) use ($categoryPriority) {
            return ($categoryPriority[$a] ?? 9) <=> ($categoryPriority[$b] ?? 9);
        });
        $selectedCategory = $categoryNames[0] ?? 'Teknologi';
        $categorySlug = Str::slug($selectedCategory);

        $category = PostCategory::firstOrCreate(
            ['slug' => $categorySlug],
            ['name' => $selectedCategory, 'description' => "Berita {$selectedCategory} terbaru"]
        );

        // --- AUTO-CREATE PostTags entries ---
        foreach ($tags as $tagName) {
            $tagSlug = Str::slug($tagName);
            PostTags::firstOrCreate(
                ['slug' => $tagSlug],
                ['name' => $tagName]
            );
        }

        // --- SLUG ---
        $slug = Str::slug($generated['title']);
        $base = $slug;
        $i = 1;
        while (Posts::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        // --- PUBLISH TIME - staggered Indonesia timezone ---
        $tz = new DateTimeZone('Asia/Jakarta');

        // Cek post APA SAJA yang sudah terjadwal hari ini (published/draft)
        $todayStart = (new DateTime('today', $tz))->format('Y-m-d H:i:s');
        $tomorrowDt = new DateTime('tomorrow', $tz);
        $tomorrowDt->modify('-1 second');
        $todayEnd = $tomorrowDt->format('Y-m-d H:i:s');

        $existingToday = Posts::whereBetween('published_at', [$todayStart, $todayEnd])->get();

        // Cek slot mana yang sudah terpakai hari ini
        $slots = [
            0 => ['hour' => 8,  'label' => 'pagi'],
            1 => ['hour' => 13, 'label' => 'siang'],
            2 => ['hour' => 16, 'label' => 'sore'],
        ];

        $usedSlots = [];
        foreach ($existingToday as $post) {
            $hour = (int) $post->published_at->format('H');
            foreach ($slots as $idx => $slot) {
                if ($hour === $slot['hour']) {
                    $usedSlots[$idx] = true;
                    break;
                }
            }
        }

        // Cari slot pertama yang BELUM terpakai
        $slotIdx = null;
        foreach ([0, 1, 2] as $idx) {
            if (!isset($usedSlots[$idx])) {
                $slotIdx = $idx;
                break;
            }
        }

        // Semua slot penuh hari ini → besok jam 8
        if ($slotIdx === null) {
            $slotIdx = 0;
            $publishTime = new DateTime('tomorrow', $tz);
            $publishTime->setTime($slots[0]['hour'], 0, 0);
        } else {
            // Hitung: jika ada slot terpakai, push besok. Jika belum ada slot sama sekali, hari ini.
            if (count($usedSlots) > 0) {
                $publishTime = new DateTime('tomorrow', $tz);
            } else {
                $publishTime = new DateTime('today', $tz);
            }
            $publishTime->setTime($slots[$slotIdx]['hour'], 0, 0);
        }

        $publishedAt = $publishTime->format('Y-m-d H:i:s');
        $status = 'draft'; // published manually via scheduler

        return Posts::create([
            'title'        => $generated['title'],
            'slug'         => $slug,
            'content'      => $generated['content'],
            'image'        => $ref->image_url,
            'source'       => $ref->source_url,
            'domain'       => $ref->source_domain,
            'status'       => $status,
            'category_id'   => $category->id,
            'created_by'   => $adminUser->id,
            'published_at'  => $publishedAt,
            'counter'      => 0,
            'tags'         => $tags,
            'meta_data'   => [
                'seo_title'       => $generated['title'],
                'seo_desc'        => $generated['meta_description'] ?? Str::limit(strip_tags($generated['content'] ?? ''), 160),
                'excerpt'         => $generated['excerpt'] ?? '',
                'ref_article_id'  => $ref->id,
                'ref_source_url'  => $ref->source_url,
                'ref_title'       => $ref->title,
                'ai_model'        => config('services.deepseek.model', 'deepseek-v4-pro'),
                'publish_slot'     => "slot_{$slotIdx}_{$slots[$slotIdx]['label']}",
            ],
        ]);
    }

    public function failed(\Throwable $e): void
    {
        RefArticle::where('id', $this->refArticleId)->update([
            'ai_status' => 'failed',
            'ai_error'  => $e->getMessage(),
        ]);
    }
}
