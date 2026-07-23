<?php

namespace App\Console\Commands;

use App\Jobs\GenerateAiArticleJob;
use App\Models\RefArticle;
use App\Services\NewsService;
use App\Services\TechPharmaScraperService;
use App\Services\YahooNewsScraperService;
use App\Services\YahooTechScraperService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AutoFeed extends Command
{
    protected $signature = 'app:auto-feed
                            {--rss-only : Hanya fetch RSS, jangan scrape atau AI}
                            {--scrape-only : Hanya scrape, jangan AI}
                            {--ai-only : Hanya generate AI dari ref_articles yang ada}
                            {--limit=3 : Maksimal article untuk AI generation per run}
                            {--sources=yahoo,ynews,pharma : Sumber scraping (yahoo, ynews, pharma, all)}';

    protected $description = 'Automation feed: RSS fetch + Multi-source scraping + DeepSeek AI generation';

    public function handle(): int
    {
        set_time_limit(0);
        $startTime = microtime(true);

        $this->info('============================================');
        $this->info('  Kangwendra Auto-Feed Automation');
        $this->info('============================================');

        $rssOnly    = $this->option('rss-only');
        $scrapeOnly = $this->option('scrape-only');
        $aiOnly     = $this->option('ai-only');
        $limit      = (int) $this->option('limit');
        $sourcesOpt = $this->option('sources');

        $sources = $sourcesOpt === 'all' ? ['yahoo', 'ynews', 'pharma'] : explode(',', $sourcesOpt);

        if (!$scrapeOnly && !$aiOnly) {
            $this->stepRss();
        }

        if (!$rssOnly && !$aiOnly) {
            foreach ($sources as $source) {
                $source = trim($source);
                if ($source === 'yahoo') {
                    $this->stepScrapeYahoo();
                } elseif ($source === 'ynews') {
                    $this->stepScrapeNews();
                } elseif ($source === 'pharma') {
                    $this->stepScrapePharma();
                }
            }
        }

        if (!$rssOnly && !$scrapeOnly) {
            $this->stepAiGeneration($limit);
        }

        $duration = round(microtime(true) - $startTime, 1);
        $pending = RefArticle::pending()->count();
        $total   = RefArticle::count();

        $this->newLine();
        $this->info('============================================');
        $this->info("  Selesai dalam {$duration} detik");
        $this->info("  Ref Articles: {$total} total, {$pending} pending AI");
        $this->info('============================================');

        Log::info("AutoFeed: selesai dalam {$duration} detik, {$pending} articles pending AI");

        return 0;
    }

    protected function stepRss(): void
    {
        $this->newLine();
        $this->info('[Step 1] Fetching RSS feeds...');

        try {
            $newsService = new NewsService();
            $newsItems   = $newsService->fetchFromYahooAiRss();
            $count       = $newsService->saveNewsToDatabase($newsItems);

            $this->info("  OK: {$count} berita disimpan");
            Log::info("AutoFeed RSS: {$count} articles saved.");

        } catch (\Exception $e) {
            $this->error("  Gagal: {$e->getMessage()}");
            Log::error("AutoFeed RSS failed: {$e->getMessage()}");
        }
    }

    protected function stepScrapeYahoo(): void
    {
        $this->newLine();
        $this->info('[Step 2a] Scraping Yahoo Tech...');

        try {
            $scraper = new YahooTechScraperService();
            $saved   = $scraper->scrapeAndSave();

            $this->info("  OK: {$saved} artikel disimpan");
            Log::info("AutoFeed YahooTech: {$saved} articles saved.");

        } catch (\Exception $e) {
            $this->error("  Gagal: {$e->getMessage()}");
            Log::error("AutoFeed YahooTech failed: {$e->getMessage()}");
        }
    }

    protected function stepScrapePharma(): void
    {
        $this->newLine();
        $this->info('[Step 2b] Scraping Tech Pharma...');

        try {
            $scraper = new TechPharmaScraperService();
            $saved   = $scraper->scrapeAndSave();

            $this->info("  OK: {$saved} artikel disimpan");
            Log::info("AutoFeed TechPharma: {$saved} articles saved.");

        } catch (\Exception $e) {
            $this->error("  Gagal: {$e->getMessage()}");
            Log::error("AutoFeed TechPharma failed: {$e->getMessage()}");
        }
    }

    protected function stepScrapeNews(): void
    {
        $this->newLine();
        $this->info('[Step 2b] Scraping Yahoo News...');

        try {
            $scraper = new YahooNewsScraperService();
            $saved   = $scraper->scrapeAndSave();

            $this->info("  OK: {$saved} artikel disimpan");
            Log::info("AutoFeed YahooNews: {$saved} articles saved.");

        } catch (\Exception $e) {
            $this->error("  Gagal: {$e->getMessage()}");
            Log::error("AutoFeed YahooNews failed: {$e->getMessage()}");
        }
    }

    protected function stepAiGeneration(int $limit): void
    {
        $this->newLine();
        $this->info('[Step 3] Dispatch AI generation jobs...');

        RefArticle::failed()->update(['ai_status' => 'pending', 'ai_error' => null]);

        $pending = RefArticle::pending()->latest()->take($limit)->get();

        if ($pending->isEmpty()) {
            $this->info('  OK: Tidak ada artikel baru untuk digenerate');
            return;
        }

        $this->info("  {$pending->count()} artikel akan diproses AI...");

        foreach ($pending as $ref) {
            GenerateAiArticleJob::dispatch($ref->id);
            $this->info("    -> " . Str::limit($ref->title, 60));
        }

        $this->info("  OK: {$pending->count()} job berhasil di-dispatch ke queue!");
        $this->info("  Hint: php artisan queue:work");
    }
}
