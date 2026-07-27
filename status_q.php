<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Status ===\n";
echo "Failed jobs: " . Illuminate\Support\Facades\DB::table('failed_jobs')->count() . "\n";
echo "Pending jobs: " . Illuminate\Support\Facades\DB::table('jobs')->count() . "\n";
echo "Posts (draft): " . App\Models\Posts::where('status', 'draft')->count() . "\n";
echo "Posts (active): " . App\Models\Posts::where('status', 'active')->count() . "\n\n";

// Show failed job error messages
echo "=== Failed Jobs ===\n";
foreach (Illuminate\Support\Facades\DB::table('failed_jobs')->get() as $f) {
    $payload = json_decode($f->payload, true);
    $job = $payload['displayName'] ?? 'unknown';
    echo "Job: {$job}\n";
    echo "Error: " . substr($f->exception, 0, 200) . "\n\n";
}

echo "=== Post Titles (draft) ===\n";
foreach (App\Models\Posts::where('status', 'draft')->orderByDesc('id')->take(10)->get() as $p) {
    echo "  id={$p->id} | " . substr($p->title, 0, 60) . "\n";
}
