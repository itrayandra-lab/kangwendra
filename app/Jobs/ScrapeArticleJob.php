<?php

namespace App\Jobs;

use App\Services\YahooTechScraperService;
use App\Services\TechPharmaScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScrapeArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 1;
    public int $maxExceptions = 1;

    protected string $source;

    public function __construct(string $source = 'yahoo')
    {
        $this->source = $source;
    }

    public function handle(): void
    {
        set_time_limit(600);

        Log::info("ScrapeArticleJob: started source={$this->source}");

        $saved = 0;

        if ($this->source === 'yahoo') {
            $saved = (new YahooTechScraperService())->scrapeAndSave();
        } elseif ($this->source === 'pharma') {
            $saved = (new TechPharmaScraperService())->scrapeAndSave();
        } elseif ($this->source === 'all') {
            $saved = (new YahooTechScraperService())->scrapeAndSave();
            $saved += (new TechPharmaScraperService())->scrapeAndSave();
        }

        Log::info("ScrapeArticleJob: done source={$this->source} saved={$saved}");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("ScrapeArticleJob: failed source={$this->source} error={$e->getMessage()}");
    }
}
