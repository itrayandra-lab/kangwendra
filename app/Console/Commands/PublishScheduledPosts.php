<?php

namespace App\Console\Commands;

use App\Models\Posts;
use App\Models\PublishSchedule;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublishScheduledPosts extends Command
{
    protected $signature = 'app:publish-scheduled-posts';

    protected $description = 'Publish draft posts based on customizable schedules from database (Indonesia timezone)';

    public function handle(): int
    {
        set_time_limit(120);

        $tz = new DateTimeZone('Asia/Jakarta');
        $now = new DateTime('now', $tz);
        $currentTime = $now->format('H:i');
        $currentDayOfWeek = (int) $now->format('w'); // 0=Sunday
        $today = $now->format('Y-m-d');
        $totalPublished = 0;

        // Find all active schedules that match current time and day
        $schedules = PublishSchedule::where('is_active', true)
            ->whereRaw("TIME_FORMAT(time, '%H:%i') = ?", [$currentTime])
            ->get();

        if ($schedules->isEmpty()) {
            $this->info('Tidak ada jadwal publish aktif pada jam ' . $currentTime . ' WIB.');
            return 0;
        }

        foreach ($schedules as $schedule) {
            // Skip if day_of_week is set and doesn't match today
            if ($schedule->day_of_week !== null && $schedule->day_of_week !== $currentDayOfWeek) {
                continue;
            }

            // Prevent double-run in the same minute using cache
            $cacheKey = "publish_slot_{$schedule->id}_{$today}_{$currentTime}";
            if (Cache::has($cacheKey)) {
                $this->info("[Jadwal {$schedule->formatted_time}] Sudah berjalan di menit ini, dilewati.");
                continue;
            }

            // Find drafts ready to publish
            $drafts = Posts::where('status', 'draft')
                ->where('published_at', '<=', $now->format('Y-m-d H:i:s'))
                ->orderBy('published_at')
                ->take($schedule->max_posts)
                ->get();

            if ($drafts->isEmpty()) {
                $this->info("[Jadwal {$schedule->formatted_time}] Tidak ada draft yang siap dipublish.");
                continue;
            }

            $published = 0;
            foreach ($drafts as $post) {
                $post->update([
                    'status'       => 'active',
                    'published_by' => 'system',
                ]);
                $this->info("  [PUBLISH] " . substr($post->title, 0, 60));
                Log::info("PublishScheduledPosts: published", [
                    'post_id' => $post->id,
                    'title' => $post->title,
                    'schedule_time' => $schedule->formatted_time,
                ]);
                $published++;
            }

            // Mark this schedule as run for this minute
            Cache::put($cacheKey, true, now($tz)->addMinutes(2));

            $totalPublished += $published;
            $this->info("[Jadwal {$schedule->formatted_time}] {$published} post dipublish.");
        }

        if ($totalPublished > 0) {
            $this->info("Selesai! {$totalPublished} post di-publish pada {$currentTime} WIB.");
        } else {
            $this->info("Tidak ada post yang dipublish pada {$currentTime} WIB.");
        }

        return 0;
    }
}
