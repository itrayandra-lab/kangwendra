<?php

namespace App\Jobs;

use App\Models\EditorPreference;
use App\Models\ResearchRecommendation;
use App\Services\SitemapScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class KeywordResearchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 2;
    public int $backoff = 30;

    public function __construct(
        public string $keyword,
        public int $userId = 1,
        public int $maxUrls = 10
    ) {}

    public function handle(SitemapScraperService $scraper): void
    {
        Log::info('KeywordResearchJob: starting', ['keyword' => $this->keyword]);

        // Initialize editor preference
        $pref = EditorPreference::firstOrCreate(
            ['keyword' => strtolower(trim($this->keyword))],
            ['score' => 0, 'confidence' => 50]
        );

        // Update research status on ref_articles
        \App\Models\RefArticle::where('source_keyword', $this->keyword)
            ->where('ai_research_status', 'researching')
            ->update(['ai_research_status' => 'idle']);

        // Extract URLs from sitemaps
        $urls = $scraper->findUrls($this->keyword, $this->maxUrls);

        if (empty($urls)) {
            Log::warning('KeywordResearchJob: no URLs found from sitemaps', ['keyword' => $this->keyword]);
            return;
        }

        Log::info('KeywordResearchJob: found ' . count($urls) . ' URLs, validating...', ['keyword' => $this->keyword]);

        $saved = 0;
        $validatedCount = 0;
        $skippedExisting = 0;
        $skippedAccessible = 0;

        foreach ($urls as $url) {
            if ($saved >= $this->maxUrls) break;
            if ($saved + $validatedCount >= $this->maxUrls) break;

            // Skip if URL already exists for THIS keyword
            if (ResearchRecommendation::where('url', $url)->where('keyword', $this->keyword)->exists()) {
                Log::debug('KeywordResearchJob: skip URL already saved for this keyword', [
                    'url' => $url,
                    'keyword' => $this->keyword,
                ]);
                $skippedExisting++;
                continue;
            }

            // Fast HTTP validation
            $accessible = $scraper->isAccessible($url);

            if (!$accessible) {
                Log::debug('KeywordResearchJob: skip dead URL', ['url' => $url]);
                $skippedAccessible++;
                continue;
            }

            $validatedCount++;

            $path = parse_url($url, PHP_URL_PATH);
            $slugTitle = $scraper->extractTitleFromSlug($path ?? '');
            $domain = $scraper->getDomain($url);
            $confidence = $this->calculateConfidence($url, $this->keyword);

            try {
                ResearchRecommendation::create([
                    'keyword'           => $this->keyword,
                    'url'              => $url,
                    'title'            => $slugTitle,
                    'domain'           => $domain,
                    'snippet'          => "Artikel dari {$domain} tentang {$this->keyword}",
                    'confidence_score' => $confidence,
                    'status'           => 'pending',
                ]);
                $saved++;
                Log::debug('KeywordResearchJob: saved URL', ['url' => $url, 'confidence' => $confidence]);
            } catch (\Illuminate\Database\QueryException $e) {
                // URL exists for a different keyword (unique constraint) — skip gracefully
                Log::debug('KeywordResearchJob: skip URL (exists for different keyword)', ['url' => $url]);
                $skippedExisting++;
            }
        }

        Log::info('KeywordResearchJob: completed', [
            'keyword'        => $this->keyword,
            'total_found'   => count($urls),
            'validated'     => $validatedCount,
            'saved'         => $saved,
            'skipped_existing' => $skippedExisting,
            'skipped_accessible' => $skippedAccessible,
        ]);
    }

    /**
     * Extract readable title from URL slug
     */
    protected function extractTitleFromSlug(?string $path): string
    {
        if (!$path) return 'Tanpa Judul';

        // Remove leading/trailing slashes
        $path = trim($path, '/');

        // Get last segment (the slug)
        $segments = explode('/', $path);
        $slug = end($segments);

        // Replace hyphens with spaces, capitalize words
        $title = str_replace('-', ' ', $slug);
        $title = str_replace('_', ' ', $title);
        $title = ucwords($title);

        // Remove numbers at start if present
        $title = preg_replace('/^\d+\s+/', '', $title);

        return strlen($title) > 3 ? $title : 'Tanpa Judul';
    }

    /**
     * Calculate confidence score based on URL relevance
     */
    protected function calculateConfidence(string $url, string $keyword): float
    {
        $urlLower = strtolower($url);
        $keywordLower = strtolower($keyword);
        $score = 50; // base

        // Keyword match in URL
        $keywordParts = preg_split('/[\s,\-]+/', $keywordLower);
        foreach ($keywordParts as $part) {
            $part = trim($part);
            if (strlen($part) < 2) continue;
            if (strpos($urlLower, $part) !== false) {
                $score += 10;
            }
        }

        // AI keywords bonus
        $aiBonusKeywords = [
            'ai' => 5, 'artificial-intelligence' => 5, 'machine-learning' => 8,
            'deep-learning' => 8, 'llm' => 10, 'generative-ai' => 10,
            'openai' => 8, 'chatgpt' => 8, 'gemini' => 8, 'neural' => 8,
            'agent' => 8, 'automation' => 5, 'seo' => 3, 'google' => 3,
        ];
        foreach ($aiBonusKeywords as $kw => $bonus) {
            if (strpos($urlLower, $kw) !== false) {
                $score += $bonus;
            }
        }

        // Domain authority bonus
        if (strpos($urlLower, 'searchenginejournal.com') !== false) {
            $score += 5;
        }
        if (strpos($urlLower, 'searchengineland.com') !== false) {
            $score += 5;
        }

        // Reject patterns penalty
        $rejectPatterns = ['review', 'vs-', 'comparison', 'budget', 'hp-murah', 'cheap-'];
        foreach ($rejectPatterns as $pattern) {
            if (strpos($urlLower, $pattern) !== false) {
                $score -= 15;
            }
        }

        return max(30, min(95, $score));
    }

    public function failed(\Throwable $e): void
    {
        Log::error('KeywordResearchJob: failed', [
            'keyword' => $this->keyword,
            'error'   => $e->getMessage(),
        ]);
    }
}
