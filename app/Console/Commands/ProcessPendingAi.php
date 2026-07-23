<?php

namespace App\Console\Commands;

use App\Jobs\GenerateAiArticleJob;
use App\Models\RefArticle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessPendingAi extends Command
{
    protected $signature = 'app:process-pending-ai {--limit=3 : Maksimal artikel yang diproses}';

    protected $description = 'Auto-generate AI articles dari ref_articles yang pending';

    public function handle(): int
    {
        set_time_limit(0);

        $limit = (int) $this->option('limit');

        // Ambil pending articles
        $pending = RefArticle::pending()
            ->latest()
            ->take($limit)
            ->get();

        if ($pending->isEmpty()) {
            $this->info('Tidak ada artikel pending untuk di-generate.');
            return 0;
        }

        $this->info("Menemukan {$pending->count()} artikel pending...");

        $success = 0;
        $failed = 0;
        $already = 0;

        foreach ($pending as $ref) {
            // Double-check status (mungkin sudah diproses)
            $ref->refresh();
            if ($ref->ai_status !== 'pending') {
                $already++;
                continue;
            }

            $ref->update(['ai_status' => 'processing']);

            try {
                $job = new GenerateAiArticleJob($ref->id);
                $job->handle();
                $success++;
                $this->info("  [OK] " . substr($ref->title, 0, 60));
            } catch (\Exception $e) {
                $ref->update(['ai_status' => 'failed', 'ai_error' => $e->getMessage()]);
                $failed++;
                $this->error("  [FAIL] " . substr($ref->title, 0, 60) . " - " . $e->getMessage());
                Log::error("ProcessPendingAI failed", ['ref_id' => $ref->id, 'error' => $e->getMessage()]);
            }

            // Delay 2 detik antar artikel untuk avoid rate limit
            sleep(2);
        }

        $this->info("Selesai! {$success} berhasil, {$failed} gagal, {$already} sudah diproses.");

        return 0;
    }
}
