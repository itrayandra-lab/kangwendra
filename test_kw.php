<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$scraper = app(\App\Services\SitemapScraperService::class);
$reflection = new ReflectionMethod($scraper, 'calculateConfidence');
$reflection->setAccessible(true);

foreach (['openai', 'groq', 'ai agent', 'ChatGPT', 'Gemini'] as $keyword) {
    echo "\n=== '$keyword' ===\n";
    $start = microtime(true);
    $urls = $scraper->findUrls($keyword, 5);
    $ms = round((microtime(true) - $start) * 1000);
    echo "Time: {$ms}ms\n";
    foreach ($urls as $url) {
        $score = $reflection->invoke($scraper, $url, $keyword);
        echo "  [{$score}] " . substr($url, 0, 80) . "\n";
    }
}
