<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$scraper = app(\App\Services\SitemapScraperService::class);
$reflection = new ReflectionMethod($scraper, 'calculateConfidence');
$reflection->setAccessible(true);

echo "=== Summary ===\n\n";

$tests = [
    'openai' => 30,
    'chatgpt' => 10,
    'claude' => 10,
    'gemini' => 10,
    'ai-agent' => 10,
];

foreach ($tests as $kw => $limit) {
    $urls = $scraper->findUrls($kw, $limit);
    echo "Keyword '$kw': " . count($urls) . " matches (requested $limit)\n";
    if (count($urls) > 0) {
        foreach ($urls as $u) {
            $score = $reflection->invoke($scraper, $u, $kw);
            echo "  [$score] " . substr($u, 0, 80) . "\n";
        }
    }
    echo "\n";
}
