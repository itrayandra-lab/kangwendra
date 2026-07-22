<?php

namespace App\Jobs;

use App\Models\Posts;
use App\Models\PostCategory;
use App\Models\RefArticle;
use App\Models\User;
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

    public int $timeout = 180;   // DeepSeek v4-pro bisa lebih lambat karena reasoning
    public int $tries   = 2;
    public int $backoff = 30;

    public function __construct(protected int $refArticleId) {}

    public function handle(): void
    {
        $ref = RefArticle::find($this->refArticleId);

        if (!$ref || $ref->ai_status !== 'pending') {
            return;
        }

        $ref->update(['ai_status' => 'processing']);

        try {
            $generated = $this->callDeepSeek($ref->title, $ref->content);
            $post      = $this->savePost($generated, $ref);

            $ref->update([
                'ai_status'         => 'done',
                'generated_post_id' => $post->id,
                'ai_error'          => null,
            ]);

            Log::info("GenerateAiArticleJob: berhasil generate artikel [{$post->id}] dari ref [{$ref->id}]");

        } catch (\Exception $e) {
            $ref->update([
                'ai_status' => 'failed',
                'ai_error'  => $e->getMessage(),
            ]);
            Log::error("GenerateAiArticleJob: gagal untuk ref [{$ref->id}]", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /* ─────────────────────────── DeepSeek API ─────────────────────────── */

    private function callDeepSeek(string $refTitle, string $refContent): array
    {
        $apiKey  = config('services.deepseek.key');
        $model   = config('services.deepseek.model', 'deepseek-v4-pro');
        $baseUrl = config('services.deepseek.base_url', 'https://api.deepseek.com');

        if (!$apiKey) {
            throw new \Exception('DEEPSEEK_API_KEY belum dikonfigurasi di .env');
        }

        $prompt = $this->buildPrompt($refTitle, $refContent);

        $payload = [
            'model'       => $model,
            'temperature' => 0.7,
            'messages'    => [
                [
                    'role'    => 'system',
                    'content' => 'Kamu adalah jurnalis teknologi profesional yang menulis artikel dalam Bahasa Indonesia. '
                               . 'Tugasmu adalah menulis ulang artikel referensi menjadi artikel baru yang ORIGINAL, '
                               . 'informatif, dan menarik. Jangan menyalin kata per kata dari referensi. '
                               . 'Selalu kembalikan respons dalam format JSON yang valid.',
                ],
                [
                    'role'    => 'user',
                    'content' => $prompt,
                ],
            ],
            'response_format' => ['type' => 'json_object'],
        ];

        // deepseek-v4-pro mendukung extended thinking (reasoning)
        if ($model === 'deepseek-v4-pro') {
            $payload['thinking']          = ['type' => 'enabled'];
            $payload['reasoning_effort']  = 'medium'; // low / medium / high
        }

        $response = Http::timeout(150)
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

        // Log token usage untuk monitoring cost
        if (isset($body['usage'])) {
            Log::info('DeepSeek usage', [
                'ref_id'             => $this->refArticleId,
                'prompt_tokens'      => $body['usage']['prompt_tokens'] ?? 0,
                'completion_tokens'  => $body['usage']['completion_tokens'] ?? 0,
                'total_tokens'       => $body['usage']['total_tokens'] ?? 0,
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
        // Batasi konten referensi agar tidak melebihi context window
        $truncatedContent = Str::limit($content, 4000, '...');

        return <<<PROMPT
Berdasarkan artikel referensi berikut, tulis artikel baru yang ORIGINAL dalam Bahasa Indonesia.

JUDUL REFERENSI: {$title}

ISI REFERENSI:
{$truncatedContent}

INSTRUKSI PENULISAN:
- Tulis artikel BARU berdasarkan topik dan informasi dari referensi di atas
- Judul harus menarik, unik, dan SEO-friendly dalam Bahasa Indonesia
- Konten minimal 6 paragraf, informatif, tidak menyalin langsung dari referensi
- Tambahkan konteks, analisis, dan penjelasan yang relevan untuk pembaca Indonesia
- Gunakan format HTML: <p>, <h2>, <h3>, <strong>, <em>, <ul>, <li>
- Setiap <h2> dan <h3> harus memiliki konten paragraf di bawahnya

Kembalikan HANYA JSON valid dengan format berikut:
{
  "title": "judul artikel baru dalam Bahasa Indonesia",
  "content": "isi artikel lengkap dalam format HTML",
  "excerpt": "ringkasan artikel 2-3 kalimat",
  "tags": ["tag1", "tag2", "tag3", "tag4", "tag5"],
  "meta_description": "deskripsi SEO maksimal 160 karakter"
}
PROMPT;
    }

    /* ─────────────────────────── Save to DB ─────────────────────────── */

    private function savePost(array $generated, RefArticle $ref): Posts
    {
        $category = PostCategory::firstOrCreate(
            ['name' => 'Teknologi'],
            ['slug' => 'teknologi', 'description' => 'Berita Teknologi dan AI']
        );

        $adminUser = User::role('admin')->first();
        if (!$adminUser) {
            throw new \Exception('Admin user tidak ditemukan.');
        }

        $title = $generated['title'];
        $slug  = Str::slug($title);
        $base  = $slug;
        $i     = 1;
        while (Posts::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        // Tags: gabungan dari AI + tag referensi, deduplicate
        $aiTags  = $generated['tags'] ?? [];
        $refTags = $ref->tags ?? [];
        $tags    = array_unique(array_merge(['Yahoo Tech'], $aiTags, $refTags));
        $tags    = array_slice(array_values($tags), 0, 8);

        $metaData = [
            'seo_title'      => $title,
            'seo_desc'       => $generated['meta_description']
                                    ?? Str::limit(strip_tags($generated['content']), 160),
            'excerpt'        => $generated['excerpt'] ?? '',
            'ref_article_id' => $ref->id,
            'ref_source_url' => $ref->source_url,
            'ref_title'      => $ref->title,
            'ai_model'       => config('services.deepseek.model', 'deepseek-v4-pro'),
        ];

        return Posts::create([
            'title'        => $title,
            'slug'         => $slug,
            'content'      => $generated['content'],
            'image'        => $ref->image_url,
            'source'       => $ref->source_url,
            'domain'       => $ref->source_domain,
            'status'       => 'active',
            'category_id'  => $category->id,
            'created_by'   => $adminUser->id,
            'published_at' => now(),
            'counter'      => 0,
            'tags'         => json_encode($tags),
            'meta_data'    => $metaData,
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
