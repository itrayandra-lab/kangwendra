<?php

namespace App\Console\Commands;

use App\Models\Posts;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishScheduledPosts extends Command
{
    protected $signature = 'app:publish-scheduled-posts {--limit=3 : Maksimal post yang dipublish}';

    protected $description = 'Publish draft posts yang sudah jadwalnya tiba (Indonesia timezone 8AM/1PM/4PM)';

    public function handle(): int
    {
        set_time_limit(60);

        $tz = new DateTimeZone('Asia/Jakarta');
        $now = new DateTime('now', $tz);

        // Ambil drafts yang sudah waktunya publish
        $drafts = Posts::where('status', 'draft')
            ->where('published_at', '<=', $now->format('Y-m-d H:i:s'))
            ->orderBy('published_at')
            ->take((int) $this->option('limit'))
            ->get();

        if ($drafts->isEmpty()) {
            $this->info('Tidak ada draft yang waktunya publish sekarang (' . $now->format('H:i') . ' WIB).');
            return 0;
        }

        $this->info("Menemukan {$drafts->count()} draft siap publish ({$now->format('H:i')} WIB):");

        foreach ($drafts as $post) {
            $post->update(['status' => 'active']);
            $this->info("  [PUBLISH] " . substr($post->title, 0, 60));
            Log::info("PublishScheduledPosts: published", ['post_id' => $post->id, 'title' => $post->title]);
        }

        $this->info("Selesai! {$drafts->count()} post di-publish.");

        return 0;
    }
}
