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
        EditorPreference::firstOrCreate(
            ['keyword' => strtolower(trim($this->keyword))],
            ['score' => 0, 'confidence' => 50]
        );

        // Update research status on ref_articles
        \App\Models\RefArticle::where('source_keyword', $this->keyword)
            ->where('ai_research_status', 'researching')
            ->update(['ai_research_status' => 'idle']);

        // Step 1: Extract URLs from sitemaps
        $urls = $scraper->findUrls($this->keyword, $this->maxUrls);

        if (empty($urls)) {
            Log::warning('KeywordResearchJob: no URLs found from sitemaps', ['keyword' => $this->keyword]);
            return;
        }

        Log::info('KeywordResearchJob: found ' . count($urls) . ' URLs', ['keyword' => $this->keyword]);

        // Step 2: Save recommendations (only high-confidence AI URLs)
        // Confidence score must be >= configured threshold (default 45)
        $saved = 0;
        $skippedLowScore = 0;
        $skippedExisting = 0;
        $minConfidence = \App\Models\ScraperConfig::getConfidenceThreshold();

        foreach ($urls as $url) {
            if ($saved >= $this->maxUrls) break;

            $confidence = $scraper->calculateConfidence($url, $this->keyword);

            // Only save URLs with sufficient AI signal
            if ($confidence < $minConfidence) {
                $skippedLowScore++;
                continue;
            }

            // Skip if URL already saved for THIS keyword
            if (ResearchRecommendation::where('url', $url)->where('keyword', $this->keyword)->exists()) {
                $skippedExisting++;
                continue;
            }

            try {
                $title = $scraper->extractTitleFromSlug($url);
                $domain = $scraper->getDomain($url);

                ResearchRecommendation::create([
                    'keyword'           => $this->keyword,
                    'url'              => $url,
                    'title'            => $title,
                    'domain'           => $domain,
                    'snippet'          => "Artikel dari {$domain} tentang {$this->keyword}",
                    'confidence_score' => $confidence,
                    'status'           => 'pending',
                ]);
                $saved++;

            } catch (\Illuminate\Database\QueryException $e) {
                // URL exists for a different keyword — skip gracefully
                $skippedExisting++;
            }
        }

        Log::info('KeywordResearchJob: completed', [
            'keyword'          => $this->keyword,
            'saved'            => $saved,
            'skipped_low_score' => $skippedLowScore,
            'skipped_existing' => $skippedExisting,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('KeywordResearchJob: failed', [
            'keyword' => $this->keyword,
            'error'   => $e->getMessage(),
        ]);
    }
}
