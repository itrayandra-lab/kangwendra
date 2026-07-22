<?php

namespace App\Console\Commands;

use App\Jobs\GenerateAiArticleJob;
use App\Models\RefArticle;
use App\Services\YahooTechScraperService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScrapeYahooTech extends Command
{
    protected $signature = 'app:scrape-yahoo-tech
                            {--no-ai : Hanya scrape, jangan generate artikel AI}
                            {--limit=20 : Maksimal artikel yang di-scrape}';

    protected $description = 'Scrape artikel dari tech.yahoo.com, simpan ke ref_articles, lalu generate artikel baru dengan AI';

    public function handle(): int
    {
        $this->info('🔍 Mulai scraping tech.yahoo.com...');

        try {
            $scraper = new YahooTechScraperService();
            $saved   = $scraper->scrapeAndSave();

            $this->info("✅ {$saved} artikel referensi baru berhasil disimpan ke ref_articles.");
            Log::info("ScrapeYahooTech: {$saved} artikel baru disimpan.");

        } catch (\Exception $e) {
            $this->error('❌ Gagal scraping: ' . $e->getMessage());
            Log::error('ScrapeYahooTech: gagal', ['error' => $e->getMessage()]);
            return 1;
        }

        if ($this->option('no-ai')) {
            $this->info('Mode --no-ai: generate AI dilewati.');
            return 0;
        }

        // Dispatch job AI untuk setiap artikel referensi yang masih pending
        $limit   = (int) $this->option('limit');
        $pending = RefArticle::pending()->latest()->take($limit)->get();

        if ($pending->isEmpty()) {
            $this->info('Tidak ada artikel pending untuk di-generate AI.');
            return 0;
        }

        $this->info("🤖 Mengirim {$pending->count()} artikel ke queue untuk generate AI...");

        foreach ($pending as $ref) {
            GenerateAiArticleJob::dispatch($ref->id);
        }

        $this->info("✅ {$pending->count()} job berhasil di-dispatch ke queue.");
        return 0;
    }
}
