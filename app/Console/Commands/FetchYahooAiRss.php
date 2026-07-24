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
        $this->error('PERINGATAN: Command ini DINONAKTIFKAN. RSS langsung save ke Posts tanpa AI paraphrase = copyright risk tinggi.');
        $this->error('Gunakan app:auto-feed --scrape-only sebagai gantinya (via RefArticle pipeline).');
        $this->info('Command ini DISABLED permanen. Hubungi admin jika perlu di-enable kembali.');
        return 1;
    }
}
