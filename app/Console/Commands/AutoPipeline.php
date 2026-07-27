<?php

namespace App\Console\Commands;

use App\Jobs\KeywordResearchJob;
use App\Jobs\ScrapeParaphraseJob;
use App\Models\EditorPreference;
use App\Models\Posts;
use App\Models\ResearchRecommendation;
use App\Services\EditorPreferenceService;
use App\Services\SitemapScraperService;
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

    protected $description = 'Automated AI pipeline: Sitemap research, scrape, paraphrase, and schedule publish (runs at 08:00 WIB daily)';

    protected const DAILY_LIMIT = 5;
    protected const CONFIDENCE_THRESHOLD = 45.0;
    protected const LOG_CHANNEL = 'ai-generate';

    public function handle(
        EditorPreferenceService $prefService,
        SitemapScraperService $sitemapScraper
    ): int {
        set_time_limit(0);
        $startTime = microtime(true);
        $tz = new DateTimeZone('Asia/Jakarta');

        $this->info('============================================');
        $this->info('  Kangwendra AI Auto-Pipeline (Sitemap)');
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

        // Effective max: can't exceed remaining slots
        $remainingSlots = max(0, self::DAILY_LIMIT - $publishedToday);
        $effectiveMax = min((int) $this->option('max'), $remainingSlots);
        $this->info("Articles remaining today: {$remainingSlots} (will dispatch max {$effectiveMax})");

        // ── Step 2: Get keyword(s) ──
        $keywordOption = $this->option('keyword');
        $keywords = [];

        if ($keywordOption) {
            $keywords[] = $keywordOption;
            $this->info("Using keyword from option: {$keywordOption}");
        } else {
            // Auto-select from top preferences
            $topKeywords = $prefService->getTopKeywords(3);
            if (empty($topKeywords)) {
                $topKeywords = ['artificial intelligence', 'AI systems', 'machine learning'];
            }
            $keywords = $topKeywords;
            $this->info('Auto-selected keywords: ' . implode(', ', $keywords));
        }

        $maxArticles = $effectiveMax;
        $dispatched = 0;
        $dryRun = $this->option('dry-run');

        // ── Step 3: Run KeywordResearchJob synchronously ──
        foreach ($keywords as $keyword) {
            if ($dispatched >= $maxArticles) break;

            $this->info("Running research for: {$keyword}");

            if (!$dryRun) {
                // Run research job synchronously (sitemap scraping)
                $job = new KeywordResearchJob($keyword, 1, $maxArticles);
                $job->handle($sitemapScraper);
            }

            // ── Step 4: Get recommendations and dispatch scrape jobs ──
            $pendingRecs = ResearchRecommendation::byKeyword($keyword)
                ->pending()
                ->orderByDesc('confidence_score')
                ->get();

            if ($pendingRecs->isEmpty()) {
                $this->warn("No recommendations found for: {$keyword}");
                continue;
            }

            $this->info("Found {$pendingRecs->count()} recommendations for '{$keyword}'");

            foreach ($pendingRecs as $rec) {
                if ($dispatched >= $maxArticles) break;

                $url = $rec->url;
                $domain = $rec->domain ?? $sitemapScraper->getDomain($url);
                $finalScore = $rec->confidence_score ?? 50;

                $this->info("  - {$rec->title}");
                $this->info("    URL: " . Str::limit($url, 60));
                $this->info("    Confidence: {$finalScore}%");

                // Check blocklist
                if ($prefService->isBlocked($url)) {
                    $this->warn("    SKIP - URL blocked");
                    $rec->update(['status' => 'rejected']);
                    continue;
                }

                if ($finalScore < self::CONFIDENCE_THRESHOLD) {
                    $this->warn("    SKIP - confidence {$finalScore}% < " . self::CONFIDENCE_THRESHOLD . "%");
                    continue;
                }

                // Check if already processed
                if (ResearchRecommendation::where('url', $url)->whereIn('status', ['scraped', 'approved'])->exists()) {
                    $this->warn("    SKIP - already processed");
                    continue;
                }

                if ($dryRun) {
                    $this->info("    [DRY-RUN] Would dispatch ScrapeParaphraseJob");
                    $dispatched++;
                    continue;
                }

                // Dispatch scrape + paraphrase job
                ScrapeParaphraseJob::dispatch(
                    $url,
                    $domain,
                    $keyword,
                    Str::uuid()->toString(),
                    $finalScore,
                    $rec->id,
                    true // autoMode
                );

                $this->info("    DISPATCHED");
                $dispatched++;
            }
        }

        $duration = round(microtime(true) - $startTime, 2);

        $this->info('============================================');
        $this->info("Pipeline summary:");
        $this->info("  - Keywords processed: " . count($keywords));
        $this->info("  - Articles dispatched: {$dispatched}");
        $this->info("  - Duration: {$duration}s");
        $this->info("  - Daily total: " . ($publishedToday + $dispatched) . '/' . self::DAILY_LIMIT);
        $this->info('============================================');

        Log::info('AutoPipeline: completed', [
            'dispatched'    => $dispatched,
            'duration_sec'  => $duration,
            'daily_total'  => $publishedToday + $dispatched,
            'keywords'     => $keywords,
        ]);

        return 0;
    }

    protected function getDailyLimit(): int
    {
        return self::DAILY_LIMIT;
    }
}
