<?php

use Illuminate\Support\Facades\Schedule;

// =============================================
// AUTO PIPELINE: Daily 03:30 WIB
// Research + Scrape + Paraphrase + Schedule
// Max 5 articles per day
// Sources: Search Engine Land + SE Journal
// =============================================
Schedule::command('app:auto-pipeline --max=5')
    ->dailyAt('03:30')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/auto-pipeline.log'));

// =============================================
// PUBLISH: Dynamic schedules from database
// Command runs every minute and checks active
// schedules in the publish_schedules table
// =============================================
Schedule::command('app:publish-scheduled-posts')
    ->everyMinute()
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/publish-scheduled.log'));
