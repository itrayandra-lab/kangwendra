<?php

namespace App\Jobs;

use App\Models\EditorPreference;
use App\Models\ResearchRecommendation;
use App\Services\KeywordResearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KeywordResearchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180;
    public int $tries   = 2;
    public int $backoff = 30;

    public function __construct(
        public string $keyword,
        public int $userId = 1
    ) {}

    public function handle(KeywordResearchService $researchService): void
    {
        Log::info('KeywordResearchJob: starting', ['keyword' => $this->keyword]);

        // Mark any existing researching status for this keyword as idle
        ResearchRecommendation::where('keyword', $this->keyword)
            ->where('status', 'researching')
            ->update(['status' => 'pending']);

        // Initialize or get preference
        $prefService = app(\App\Services\EditorPreferenceService::class);
        $pref = $prefService->initializeKeyword($this->keyword);

        // Update research status on all ref_articles for this keyword
        $refIds = \App\Models\RefArticle::where('source_keyword', $this->keyword)
            ->where('ai_research_status', 'researching')
            ->pluck('id');
        \App\Models\RefArticle::whereIn('id', $refIds)
            ->update(['ai_research_status' => 'idle']);

        // Research URLs via DeepSeek
        $rawUrls = $researchService->researchUrls($this->keyword);

        if (empty($rawUrls)) {
            Log::warning('KeywordResearchJob: no URLs found', ['keyword' => $this->keyword]);
            return;
        }

        $saved = 0;

        foreach ($rawUrls as $item) {
            $url = $item['url'] ?? '';
            if (empty($url)) continue;

            // Validate domain
            if (!$researchService->isValidDomain($url)) {
                Log::debug('KeywordResearchJob: skip invalid domain', ['url' => $url]);
                continue;
            }

            // Check if already recommended
            if (ResearchRecommendation::where('url', $url)->exists()) {
                Log::debug('KeywordResearchJob: skip duplicate URL', ['url' => $url]);
                continue;
            }

            // Check blocklist
            if ($prefService->isBlocked($url)) {
                Log::debug('KeywordResearchJob: skip blocked URL', ['url' => $url]);
                continue;
            }

            ResearchRecommendation::create([
                'keyword'          => $this->keyword,
                'url'             => $url,
                'title'           => $item['title'] ?? null,
                'domain'          => parse_url($url, PHP_URL_HOST),
                'snippet'         => $item['snippet'] ?? null,
                'confidence_score'=> $item['confidence_score'] ?? 50,
                'status'          => 'pending',
            ]);

            $saved++;
        }

        Log::info('KeywordResearchJob: saved ' . $saved . ' recommendations for keyword: ' . $this->keyword);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('KeywordResearchJob: failed', [
            'keyword' => $this->keyword,
            'error'   => $e->getMessage(),
        ]);
    }
}
