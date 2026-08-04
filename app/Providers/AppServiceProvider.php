<?php

namespace App\Providers;

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
    }
}
