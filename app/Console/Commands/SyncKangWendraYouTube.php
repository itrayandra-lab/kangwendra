<?php

namespace App\Console\Commands;

use App\Services\YouTubeChannelSyncService;
use Illuminate\Console\Command;

class SyncKangWendraYouTube extends Command
{
    protected $signature = 'youtube:sync-kang-wendra {--limit=15 : Jumlah video terbaru yang disinkronkan}';

    protected $description = 'Sinkronkan video terbaru WINwithWEN beserta thumbnail ke database lokal';

    public function handle(YouTubeChannelSyncService $sync): int
    {
        $result = $sync->sync((int) $this->option('limit'));
        $this->info("YouTube tersinkron: {$result['created']} baru, {$result['updated']} diperbarui, {$result['total']} diproses.");

        return self::SUCCESS;
    }
}
