<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('inspire')->hourly();
Schedule::command('queue:work --stop-when-empty --timeout=60 --tries=3')->everyMinute()->withoutOverlapping();

// Fetch Yahoo AI RSS every day at 07:00 WIB (Asia/Jakarta)
Schedule::command('app:fetch-yahoo-ai-rss')->dailyAt('07:00')->timezone('Asia/Jakarta')->withoutOverlapping();

// Scrape tech.yahoo.com & generate artikel AI setiap hari jam 08:00 WIB
Schedule::command('app:scrape-yahoo-tech')->dailyAt('08:00')->timezone('Asia/Jakarta')->withoutOverlapping();
