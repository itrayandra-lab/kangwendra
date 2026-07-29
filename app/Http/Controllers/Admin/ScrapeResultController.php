<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ScrapeParaphraseJob;
use App\Jobs\UpdateEditorPreferenceJob;
use App\Models\EditorPreference;
use App\Models\RefArticle;
use App\Models\ResearchRecommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ScrapeResultController extends Controller
{
    /**
     * Show all scrape results (raw URLs found from sitemaps).
     */
    public function index(Request $request)
    {
        $page = 'Hasil Scraping';

        $keyword = $request->get('keyword');
        $status = $request->get('status');

        $query = ResearchRecommendation::orderByDesc('created_at');

        // Keyword filter
        if ($keyword) {
            $query->where('keyword', strtolower(trim($keyword)));
        }

        // Status filter - only real statuses
        if ($status === 'moved') {
            $query->whereNotNull('ref_article_id');
        } else {
            // Default: show pending (not yet moved)
            $query->whereNull('ref_article_id');
        }

        $results = $query->paginate(20);

        // Stats per keyword
        $keywordStats = [];
        $statsQuery = ResearchRecommendation::query();
        if ($keyword) {
            $statsQuery->where('keyword', strtolower(trim($keyword)));
        }

        $allForStats = $statsQuery->get()->groupBy('keyword');
        foreach ($allForStats as $kw => $items) {
            $keywordStats[$kw] = [
                'total'   => $items->count(),
                'pending' => $items->where('status', 'pending')->count(),
                'moved'   => $items->filter(fn($i) => $i->ref_article_id !== null)->count(),
            ];
        }

        // Available keywords for filter
        $availableKeywords = ResearchRecommendation::selectRaw('DISTINCT keyword')->pluck('keyword');

        return view('pages.admin.scrape-results.index', compact(
            'page', 'results', 'keyword', 'status', 'keywordStats', 'availableKeywords'
        ));
    }

    /**
     * Approve selected scrape results:
     * - Move to ref_articles
     * - Boost AI confidence (+5 per keyword approval)
     * - Delete from research_recommendations
     */
    public function approve(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Pilih至少 satu URL untuk di-approve.');
        }

        $approved = 0;
        $skipped = 0;
        $errors = [];

        foreach ((array) $ids as $id) {
            $id = (int) $id;
            if (!$id) continue;

            $rec = ResearchRecommendation::find($id);
            if (!$rec) { $skipped++; continue; }

            // Skip if already moved
            if ($rec->ref_article_id) { $skipped++; continue; }

            // Skip if RefArticle with this source_url already exists
            if (RefArticle::where('source_url', $rec->url)->exists()) {
                $skipped++;
                continue;
            }

            try {
                // Create RefArticle
                $refArticle = RefArticle::create([
                    'title'               => $rec->title,
                    'source_url'          => $rec->url,
                    'source_domain'       => $rec->domain,
                    'source_keyword'      => $rec->keyword,
                    'image_url'          => null,
                    'content_snippet'    => $rec->snippet,
                    'ai_research_status' => 'idle',
                    'moved_from_scrape'  => true,
                ]);

                // Record AI learning: boost confidence for this keyword
                UpdateEditorPreferenceJob::dispatch('approval', [
                    'keyword' => $rec->keyword,
                    'url'     => $rec->url,
                    'topic'   => $this->extractTopic($rec->title ?? ''),
                ]);

                // Delete the scrape result (migrated)
                $rec->delete();
                $approved++;

            } catch (\Throwable $e) {
                $errors[] = "ID {$id}: {$e->getMessage()}";
            }
        }

        $msg = "{$approved} URL di-approve dan dipindahkan ke Ref Articles. Confidence keyword dinaikkan.";
        if ($skipped > 0) $msg .= " {$skipped} dilewati.";
        if (!empty($errors)) $msg .= " Error: " . implode('; ', $errors);

        return back()->with('success', $msg);
    }

    /**
     * Reject selected scrape results:
     * - Delete from research_recommendations
     * - Add URL to blocklist for AI learning
     * - NOT permanent — can be re-discovered on next research
     */
    public function reject(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Pilih至少 satu URL untuk di-reject.');
        }

        $rejected = 0;
        $errors = [];

        foreach ((array) $ids as $id) {
            $id = (int) $id;
            if (!$id) continue;

            $rec = ResearchRecommendation::find($id);
            if (!$rec) { continue; }

            try {
                // Record rejection in AI learning (blocklist URL)
                UpdateEditorPreferenceJob::dispatch('rejection', [
                    'keyword' => $rec->keyword,
                    'url'     => $rec->url,
                    'topic'   => $this->extractTopic($rec->title ?? ''),
                ]);

                $rec->delete();
                $rejected++;

            } catch (\Throwable $e) {
                $errors[] = "ID {$id}: {$e->getMessage()}";
            }
        }

        $msg = "{$rejected} URL di-reject dan dihapus dari database.";
        if (!empty($errors)) $msg .= " Error: " . implode('; ', $errors);

        return back()->with('warning', $msg);
    }

    /**
     * Delete selected scrape results (no AI learning).
     */
    public function destroySelected(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'Pilih至少 satu hasil.');
        }

        $deleted = ResearchRecommendation::whereIn('id', $ids)->delete();
        return back()->with('success', "{$deleted} hasil dihapus.");
    }

    /**
     * Retry failed scrape results (re-scrape from source).
     */
    public function retryFailed(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'Pilih至少 satu hasil.');
        }

        $retried = 0;
        foreach ((array) $ids as $id) {
            $rec = ResearchRecommendation::find($id);
            if (!$rec) continue;

            $rec->update(['status' => 'pending']);
            ScrapeParaphraseJob::dispatch(
                $rec->url,
                $rec->domain ?? parse_url($rec->url, PHP_URL_HOST),
                $rec->keyword,
                Str::uuid()->toString(),
                $rec->confidence_score ?? 50,
                $rec->id,
                false
            );
            $retried++;
        }

        return back()->with('success', "{$retried} job di-queue untuk retry.");
    }

    protected function extractTopic(string $title): string
    {
        $aiTopics = ['ai', 'llm', 'machine learning', 'deep learning', 'openai',
            'gemini', 'chatgpt', 'neural', 'generative', 'agentic', 'claude', 'deepseek'];
        foreach ($aiTopics as $topic) {
            if (stripos($title, $topic) !== false) return 'AI & Teknologi';
        }

        $seoTopics = ['seo', 'search', 'google', 'algorithm', 'serp'];
        foreach ($seoTopics as $topic) {
            if (stripos($title, $topic) !== false) return 'SEO & Search';
        }

        return 'Teknologi';
    }
}
