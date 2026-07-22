<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NewsService;
use Illuminate\Support\Facades\Log;

class FetchYahooAiRss extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-yahoo-ai-rss {--date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch technology and AI articles from Yahoo News RSS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fetch Yahoo News RSS (Technology & AI)...');

        try {
            $newsService = new NewsService();
            $dateFilter = $this->option('date');

            $newsItems = $newsService->fetchFromYahooAiRss($dateFilter);

            $count = $newsService->saveNewsToDatabase($newsItems);

            foreach ($newsItems as $item) {
                $this->info("Fetched: {$item['title']}");
            }

            $this->info("Successfully fetched and saved {$count} articles!");
            Log::info("Successfully fetched {$count} technology/AI articles from Yahoo News RSS");
            return 0;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Error fetching Yahoo AI RSS', ['exception' => $e]);
            return 1;
        }
    }
}
