<?php

namespace App\Jobs;

use App\Models\EditorPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateEditorPreferenceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;
    public int $tries   = 1;

    public function __construct(
        public string $action,
        public array $data = []
    ) {}

    public function handle(): void
    {
        Log::info('UpdateEditorPreferenceJob: running', [
            'action' => $this->action,
            'data'   => $this->data,
        ]);

        switch ($this->action) {
            case 'approval':
                $this->handleApproval();
                break;
            case 'rejection':
                $this->handleRejection();
                break;
            case 'unpublish':
                $this->handleUnpublish();
                break;
            case 'recalculate_all':
                $this->recalculateAll();
                break;
            default:
                Log::warning('UpdateEditorPreferenceJob: unknown action', ['action' => $this->action]);
        }
    }

    protected function handleApproval(): void
    {
        $keyword = $this->data['keyword'] ?? '';
        $topic   = $this->data['topic'] ?? '';
        $url     = $this->data['url'] ?? '';

        if (empty($keyword)) return;

        $pref = EditorPreference::firstOrCreate(
            ['keyword' => strtolower(trim($keyword))],
            ['score' => 0, 'confidence' => 50]
        );

        $pref->recordApproval();

        Log::info('UpdateEditorPreferenceJob: approval recorded', [
            'keyword'    => $keyword,
            'confidence' => $pref->confidence,
        ]);
    }

    protected function handleRejection(): void
    {
        $keyword = $this->data['keyword'] ?? '';
        $url     = $this->data['url'] ?? '';
        $topic   = $this->data['topic'] ?? null;

        if (empty($keyword)) return;

        $pref = EditorPreference::firstOrCreate(
            ['keyword' => strtolower(trim($keyword))],
            ['score' => 0, 'confidence' => 50]
        );

        if (!empty($url)) {
            $pref->addToBlocklist($url);
        }

        $pref->recordRejection($topic);

        Log::info('UpdateEditorPreferenceJob: rejection recorded', [
            'keyword'    => $keyword,
            'confidence' => $pref->confidence,
        ]);
    }

    protected function handleUnpublish(): void
    {
        $keyword = $this->data['keyword'] ?? '';

        if (empty($keyword)) return;

        $pref = EditorPreference::where('keyword', strtolower(trim($keyword)))->first();
        if ($pref) {
            $pref->recordUnpublish();
            Log::info('UpdateEditorPreferenceJob: unpublish recorded', [
                'keyword'    => $keyword,
                'confidence' => $pref->confidence,
            ]);
        }
    }

    protected function recalculateAll(): void
    {
        // Recalculate composite score for all preferences
        $prefs = EditorPreference::all();
        foreach ($prefs as $pref) {
            $pref->score = $pref->getCompositeScore();
            $pref->save();
        }

        Log::info('UpdateEditorPreferenceJob: recalculated ' . $prefs->count() . ' preferences');
    }
}
