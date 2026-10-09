<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Naikkan memory limit untuk scraping pipeline (default 128MB terlalu kecil
        // saat buffer entire HTML page dari SEJ/SEL yang bisa 1-5MB + regex processing).
        // 512M cukup untuk 1 article scrape + DeepSeek call + post generation.
        ini_set('memory_limit', '512M');

        // Behind a CDN every visitor can share the edge IP, so key the limits on the real client IP header when present.
        RateLimiter::for('aray', function (Request $request) {
            $ip = $request->header('CF-Connecting-IP') ?: $request->ip();

            return [
                Limit::perMinute(config('aray.limits.per_minute'))->by($ip),
                Limit::perDay(config('aray.limits.per_day'))->by($ip),
            ];
        });
    }
}
