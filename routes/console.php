<?php

use Illuminate\Support\Facades\Schedule;

// =============================================
// PUBLISH: 3 slot per hari (Indonesia WIB)
// Max 1 post per slot = 3 posts/hari
// =============================================
Schedule::command('app:publish-scheduled-posts --limit=1')
    ->dailyAt('08:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/publish-08.log'));

Schedule::command('app:publish-scheduled-posts --limit=1')
    ->dailyAt('13:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/publish-13.log'));

Schedule::command('app:publish-scheduled-posts --limit=1')
    ->dailyAt('16:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/publish-16.log'));

// =============================================
// SCRAPE: 2x per hari (08:30 & 16:30 WIB)
// Hanya Yahoo Tech (full article + gambar)
// Tech Pharma opsional (bisa di-disable)
// =============================================
Schedule::command('app:auto-feed --scrape-only --sources=yahoo,pharma')
    ->twiceDailyAt(8, 16, 30)
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scrape.log'));

// =============================================
// AI GENERATE: setiap 15 menit (max 3 article)
// =============================================
Schedule::command('app:process-pending-ai --limit=3')
    ->everyFifteenMinutes()
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/ai-generate.log'));
