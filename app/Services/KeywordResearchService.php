<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KeywordResearchService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $model;

    public function __construct()
    {
        $this->apiKey   = config('services.deepseek.key', '');
        $this->baseUrl  = config('services.deepseek.base_url', 'https://api.deepseek.com');
        $this->model    = config('services.deepseek.model', 'deepseek-v4-pro');
    }

    /**
     * Research keyword → return 5 recommended URLs from SEL/SEJ
     */
    public function researchUrls(string $keyword): array
    {
        if (empty($this->apiKey)) {
            Log::error('KeywordResearchService: DEEPSEEK_API_KEY tidak dikonfigurasi');
            return [];
        }

        try {
            $payload = [
                'model'    => $this->model,
                'messages' => [
                    [
                        'role'    => 'system',
                        'content' => 'Kamu adalah research assistant untuk portal berita AI. '
                                   . 'Kamu HANYA mencari URL dari dua sumber: searchengineland.com dan searchenginejournal.com. '
                                   . 'Fokus topik: AI Systems, AI Architecture, LLMs, Machine Learning, Enterprise AI, '
                                   . 'SEO AI, Search AI, Neural Networks, Deep Learning, AI Agents, AI Automation. '
                                   . 'Jangan rekomendasikan artikel HP/gadget consumer, review smartphone, atau comparison tables. '
                                   . 'Selalu kembalikan JSON yang valid.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $this->buildResearchPrompt($keyword),
                    ],
                ],
                'response_format' => ['type' => 'json_object'],
                'max_tokens'      => 2048,
                'temperature'     => 0.5,
            ];

            if ($this->model === 'deepseek-v4-pro') {
                $payload['extra_body'] = ['thinking' => ['type' => 'enabled']];
            }

            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->post("{$this->baseUrl}/chat/completions", $payload);

            if ($response->failed()) {
                $body = $response->json();
                $errorMsg = $body['error']['message'] ?? $response->body();
                Log::error('KeywordResearchService: DeepSeek error', ['error' => $errorMsg]);
                return [];
            }

            $body = $response->json();
            $raw = $body['choices'][0]['message']['content'] ?? '';
            $decoded = json_decode($raw, true);

            if (!$decoded || empty($decoded['urls'])) {
                Log::warning('KeywordResearchService: invalid response', ['raw' => Str::limit($raw, 200)]);
                return [];
            }

            Log::info('KeywordResearchService: found ' . count($decoded['urls']) . ' URLs for keyword: ' . $keyword);

            return $decoded['urls'];

        } catch (\Exception $e) {
            Log::error('KeywordResearchService: exception', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function buildResearchPrompt(string $keyword): string
    {
        return <<<PROMPT
Cari 5 URL artikel terbaru dan terbaik dari searchengineland.com dan searchenginejournal.com yang membahas topik: "{$keyword}".

Prioritas:
- AI Systems, AI Architecture, LLMs, Machine Learning
- Enterprise AI, AI Agents, AI Automation
- SEO AI, Search AI, Neural Networks, Deep Learning
- AI Infrastructure, AI Cloud Platforms

TOLAK artikel yang tentang:
- HP/ smartphone review
- Comparison tables (perbandingan produk)
- Budget gadget
- Daily puzzles (wordle, crossword)

Untuk setiap URL, berikan:
- url: URL lengkap dari searchengineland.com atau searchenginejournal.com
- title: judul artikel
- snippet: 1-2 kalimat kenapa artikel ini relevan dan berkualitas
- confidence_score: skor 0-100 (seberapa yakin kamu bahwa editor akan suka)

FORMAT OUTPUT (HANYA JSON):
{
  "urls": [
    {
      "url": "https://searchengineland.com/judul-artikel",
      "title": "Judul Artikel yang Menarik",
      "snippet": "Alasan kenapa artikel ini bagus untuk portal AI",
      "confidence_score": 88
    }
  ]
}

Pilih artikel yang:
1. Published dalam 6 bulan terakhir (jika memungkinkan)
2. Bukan artikel "listicle" atau "best X tools"
3. Topiknya: AI, Machine Learning, Enterprise Tech, Search/SEO, AI Systems
4.至少 500 kata konten

JSON output only, tidak ada penjelasan lain:
PROMPT;
    }

    /**
     * Check if URL is accessible via HEAD request
     */
    public function isAccessible(string $url): bool
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'])
                ->head($url);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate URL is from allowed domains
     */
    public function isValidDomain(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) return false;

        $allowed = ['searchengineland.com', 'searchenginejournal.com'];
        foreach ($allowed as $domain) {
            if (stripos($host, $domain) !== false) {
                return true;
            }
        }
        return false;
    }
}
