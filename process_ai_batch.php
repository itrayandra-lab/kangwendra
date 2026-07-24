<?php
// Background AI batch processor
// Usage: php process_ai_batch.php <batch_id> <limit>
// Saves status to storage/logs/batch_<batch_id>.json

$basePath = dirname(__DIR__);
require $basePath . '/vendor/autoload.php';
$app = require $basePath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Jobs\GenerateAiArticleJob;
use App\Models\RefArticle;

$batchId = $argv[1] ?? null;
$limit = isset($argv[2]) ? (int)$argv[2] : 5;

if (!$batchId) {
    echo "Error: batch_id required\n";
    exit(1);
}

$batchFile = $basePath . '/storage/logs/batch_' . $batchId . '.json';

$pending = RefArticle::where('batch_id', $batchId)
    ->where('ai_status', 'processing')
    ->take($limit)
    ->get();

if ($pending->isEmpty()) {
    echo "No processing articles for batch {$batchId}\n";
    // Update status
    $done = RefArticle::where('batch_id', $batchId)->where('ai_status', 'done')->count();
    $failed = RefArticle::where('batch_id', $batchId)->where('ai_status', 'failed')->count();
    $total = RefArticle::where('batch_id', $batchId)->count();
    file_put_contents($batchFile, json_encode([
        'batch_id'  => $batchId,
        'total'     => $total,
        'success'   => $done,
        'failed'    => $failed,
        'errors'    => [],
        'processed'  => [],
        'status'    => 'complete',
        'finished'  => date('Y-m-d H:i:s'),
    ]));
    exit(0);
}

foreach ($pending as $ref) {
    try {
        $job = new GenerateAiArticleJob($ref->id);
        $job->handle();
        $ref->update(['ai_status' => 'done']);
        echo "OK: " . substr($ref->title, 0, 60) . "\n";

        $batchData = file_exists($batchFile) ? json_decode(file_get_contents($batchFile), true) : [];
        $batchData['success'] = ($batchData['success'] ?? 0) + 1;
        $batchData['processed'][] = substr($ref->title, 0, 60);
        file_put_contents($batchFile, json_encode($batchData));
    } catch (\Exception $e) {
        $ref->update(['ai_status' => 'failed', 'ai_error' => $e->getMessage()]);
        echo "FAIL: " . substr($ref->title, 0, 60) . " - " . substr($e->getMessage(), 0, 80) . "\n";

        $batchData = file_exists($batchFile) ? json_decode(file_get_contents($batchFile), true) : [];
        $batchData['failed'] = ($batchData['failed'] ?? 0) + 1;
        $batchData['errors'][] = [
            'title' => substr($ref->title, 0, 60),
            'error' => substr($e->getMessage(), 0, 120),
        ];
        file_put_contents($batchFile, json_encode($batchData));
    }

    sleep(3);
}

echo "Batch {$batchId} chunk done\n";
exit(0);
