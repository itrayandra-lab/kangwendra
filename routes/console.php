<?php

use Illuminate\Support\Facades\Schedule;

// =============================================
// AUTO PIPELINE: Daily 08:00 WIB
// Research + Scrape + Paraphrase + Schedule
// Max 5 articles per day
// Sources: Search Engine Land + SE Journal
// =============================================
Schedule::command('app:auto-pipeline --max=5')
    ->dailyAt('08:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/auto-pipeline.log'));

// =============================================
// PUBLISH: 3 slot per hari (Indonesia WIB)
// Slot 1: 08:00, Slot 2: 13:00, Slot 3: 16:00
// Max 1 post per slot
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
