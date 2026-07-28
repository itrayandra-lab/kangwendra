<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResearchRecommendation;
use App\Models\RefArticle;
use App\Models\EditorPreference;
use App\Models\Posts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScrapeResultController extends Controller
{
    /**
     * Show all scrape results (raw URLs found from sitemaps).
     */
    public function index(Request $request)
    {
        $page = 'Hasil Scraping';

        $keyword = $request->get('keyword');
        $status = $request->get('status'); // pending, moved

        $query = ResearchRecommendation::orderByDesc('created_at');

        // Keyword filter - CRITICAL: always filter if keyword is set
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

        // Stats per keyword - only for the filtered keyword (or all if no filter)
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
     * Move selected scrape results to Ref Articles.
     */
    public function moveToRefArticles(Request $request)
    {
        // Support both single IDs and arrays
        $input = $request->all();
        $ids = [];

        if (isset($input['ids'])) {
            $ids = is_array($input['ids']) ? $input['ids'] : [$input['ids']];
        }

        // Also check for individual id parameter
        if ($request->has('id') && empty($ids)) {
            $ids = [(string) $request->input('id')];
        }

        if (empty($ids)) {
            return back()->with('error', 'Tidak ada data yang dipilih.');
        }

        $moved = 0;
        $skipped = 0;
        $errors = [];

            foreach ($ids as $rawId) {
            $id = is_numeric($rawId) ? (int) $rawId : null;
            if (!$id) continue;

            $rec = ResearchRecommendation::find($id);
            if (!$rec) { $skipped++; continue; }

            // Skip if already moved
            if ($rec->ref_article_id) { $skipped++; continue; }

            // Skip if RefArticle with this source_url already exists (unique constraint)
            if (RefArticle::where('source_url', $rec->url)->exists()) { $skipped++; continue; }

            // Create RefArticle
            try {
                $refArticle = RefArticle::create([
                    'title'              => $rec->title,
                    'source_url'          => $rec->url,
                    'source_domain'       => $rec->domain,
                    'source_keyword'      => $rec->keyword,
                    'image_url'          => null,
                    'content_snippet'    => $rec->snippet,
                    'ai_research_status' => 'idle',
                    'moved_from_scrape'  => true,
                ]);

                // Delete the scrape result after successful move
                $rec->delete();
                $moved++;
            } catch (\Throwable $e) {
                $errors[] = "ID {$id}: {$e->getMessage()}";
            }
        }

        $msg = "{$moved} hasil dipindahkan ke Ref Articles dan dihapus dari sini.";
        if ($skipped > 0) $msg .= " {$skipped} dilewati (sudah dipindahkan atau duplikat).";
        if (!empty($errors)) $msg .= " Error: " . implode('; ', $errors);

        return back()->with('success', $msg);
    }

    /**
     * Delete selected scrape results.
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
        foreach ($ids as $id) {
            $rec = ResearchRecommendation::find($id);
            if (!$rec) continue;

            // Dispatch scrape job for this URL
            $rec->update(['status' => 'pending']);
            \App\Jobs\ScrapeParaphraseJob::dispatch($rec->url, $rec->keyword, $rec->id);
            $retried++;
        }

        return back()->with('success', "{$retried} job di-queue untuk retry.");
    }
}
