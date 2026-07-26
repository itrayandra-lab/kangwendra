<?php

namespace App\Console\Commands;

use App\Jobs\KeywordResearchJob;
use App\Jobs\ScrapeParaphraseJob;
use App\Models\EditorPreference;
use App\Models\Posts;
use App\Models\ResearchRecommendation;
use App\Services\EditorPreferenceService;
use App\Services\KeywordResearchService;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AutoPipeline extends Command
{
    protected $signature = 'app:auto-pipeline
                            {--keyword= : Specific keyword to research (optional)}
                            {--dry-run : Only show what would be done, do not execute}
                            {--max=5 : Maximum articles to process}';

    protected $description = 'Automated AI pipeline: Research keywords, scrape, paraphrase, and schedule publish (runs at 08:00 WIB daily)';

    protected const DAILY_LIMIT = 5;
    protected const CONFIDENCE_THRESHOLD = 85.0;
    protected const LOG_CHANNEL = 'ai-generate';

    public function handle(
        KeywordResearchService $researchService,
        EditorPreferenceService $prefService
    ): int {
        set_time_limit(0);
        $startTime = microtime(true);
        $tz = new DateTimeZone('Asia/Jakarta');

        $this->info('============================================');
        $this->info('  Kangwendra AI Auto-Pipeline');
        $this->info('  ' . now($tz)->format('d M Y, H:i:s') . ' WIB');
        $this->info('============================================');

        // ── Step 1: Check daily limit ──
        $todayStart = (new DateTime('today', $tz))->format('Y-m-d 00:00:00');
        $todayEnd   = (new DateTime('today', $tz))->format('Y-m-d 23:59:59');

        $publishedToday = Posts::whereBetween('published_at', [$todayStart, $todayEnd])
            ->where('status', 'draft')
            ->count();

        if ($publishedToday >= self::DAILY_LIMIT) {
            $this->warn("Daily limit ({$publishedToday}/{$this->getDailyLimit()}) reached. Exiting.");
            Log::info('AutoPipeline: daily limit reached', ['count' => $publishedToday]);
            return 0;
        }

        $remainingSlots = self::DAILY_LIMIT - $publishedToday;
        $this->info("Articles remaining today: {$remainingSlots}");

        // ── Step 2: Get keyword(s) to research ──
        $keywordOption = $this->option('keyword');
        $keywords = [];

        if ($keywordOption) {
            $keywords[] = $keywordOption;
            $this->info("Using keyword from option: {$keywordOption}");
        } else {
            // Auto-select keywords from top preferences
            $topKeywords = $prefService->getTopKeywords(3);
            if (empty($topKeywords)) {
                // Default keywords if no history
                $topKeywords = ['artificial intelligence', 'AI systems', 'LLM'];
            }
            $keywords = $topKeywords;
            $this->info('Auto-selected keywords: ' . implode(', ', $keywords));
        }

        $maxArticles = (int) $this->option('max');
        $dispatched = 0;
        $dryRun = $this->option('dry-run');

        // ── Step 3: Process each keyword ──
        foreach ($keywords as $keyword) {
            if ($dispatched >= $maxArticles) break;

            $this->info("Processing keyword: {$keyword}");

            // Check if there are pending recommendations for this keyword
            $pendingRecs = ResearchRecommendation::byKeyword($keyword)
                ->pending()
                ->get();

            if ($pendingRecs->isEmpty()) {
                // No pending recommendations - run research
                $this->info("No pending recommendations. Running research for: {$keyword}");

                if (!$dryRun) {
                    // Run research synchronously
                    $job = new KeywordResearchJob($keyword);
                    $job->handle($researchService);
                }

                // Refresh recommendations
                $pendingRecs = ResearchRecommendation::byKeyword($keyword)
                    ->pending()
                    ->get();
            }

            if ($pendingRecs->isEmpty()) {
                $this->warn("No recommendations found for keyword: {$keyword}");
                continue;
            }

            // ── Step 4: Select URLs to scrape (confidence >= threshold) ──
            foreach ($pendingRecs as $rec) {
                if ($dispatched >= $maxArticles) break;

                $url = $rec->url;
                $domain = $rec->domain ?? parse_url($url, PHP_URL_HOST);
                $confidence = $rec->confidence_score ?? 50;
                $finalScore = $prefService->calculateConfidence($keyword, $url, $domain);
                $blendedScore = ($confidence * 0.6) + ($finalScore * 0.4);

                $this->info("  - [{$rec->id}] {$rec->title}");
                $this->info("    URL: {$url}");
                $this->info("    AI Score: {$confidence}% | Pref Score: {$finalScore}% | Final: " . round($blendedScore, 1) . "%");

                if ($blendedScore < self::CONFIDENCE_THRESHOLD) {
                    $this->warn("    SKIP (confidence {$blendedScore}% < " . self::CONFIDENCE_THRESHOLD . "%)");
                    continue;
                }

                // Check if URL already processed
                if (ResearchRecommendation::where('url', $url)->whereIn('status', ['scraped', 'approved'])->exists()) {
                    $this->warn("    SKIP (already processed)");
                    continue;
                }

                // Check blocklist
                if ($prefService->isBlocked($url)) {
                    $this->warn("    SKIP (URL blocked)");
                    $rec->update(['status' => 'rejected']);
                    continue;
                }

                if ($dryRun) {
                    $this->info("    [DRY-RUN] Would dispatch ScrapeParaphraseJob");
                    $dispatched++;
                    continue;
                }

                // ── Step 5: Dispatch ScrapeParaphraseJob ──
                ScrapeParaphraseJob::dispatch(
                    $url,
                    $domain,
                    $keyword,
                    Str::uuid()->toString(),
                    $blendedScore,
                    $rec->id,
                    true // autoMode = true
                );

                $this->info("    DISPATCHED ✅");
                $dispatched++;
            }
        }

        $duration = round(microtime(true) - $startTime, 2);

        $this->info('============================================');
        $this->info("Pipeline summary:");
        $this->info("  - Articles dispatched: {$dispatched}");
        $this->info("  - Duration: {$duration}s");
        $this->info("  - Daily total: " . ($publishedToday + $dispatched) . '/' . self::DAILY_LIMIT);
        $this->info('============================================');

        Log::info('AutoPipeline: completed', [
            'dispatched'  => $dispatched,
            'duration_sec' => $duration,
            'daily_total'  => $publishedToday + $dispatched,
        ]);

        return 0;
    }

    protected function getDailyLimit(): int
    {
        return self::DAILY_LIMIT;
    }
}
