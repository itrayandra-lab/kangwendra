<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EditorPreference;
use App\Models\RefArticle;
use App\Models\ResearchRecommendation;
use App\Models\ScraperConfig;
use App\Jobs\KeywordResearchJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ScrapingController extends Controller
{
    /**
     * Halaman Scraping - Form research ONLY, no table.
     */
    public function index()
    {
        $page = 'Scraping';
        $keywords = ScraperConfig::getKeywords();

        $stats = [
            'pending'   => ResearchRecommendation::whereNull('ref_article_id')
                           ->where('status', 'pending')->count(),
            'total'     => ResearchRecommendation::count(),
            'moved'     => ResearchRecommendation::whereNotNull('ref_article_id')->count(),
            'ref_articles_total' => RefArticle::count(),
        ];

        return view('pages.admin.scraping.index', compact('page', 'keywords', 'stats'));
    }

    // ═══════════════════════════════════════════════════════════════════
    // RESEARCH METHODS
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Research 1 keyword - ASYNC via queue.
     * Dispatch job dan redirect ke batch progress page.
     */
    public function research(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|min:2|max:255',
        ]);

        $keyword = strtolower(trim($validated['keyword']));

        // Cleanup dulu (ini cepat, tidak timeout)
        $deletedLowScore = ResearchRecommendation::byKeyword($keyword)
            ->where('confidence_score', '<', 45)
            ->delete();

        ResearchRecommendation::byKeyword($keyword)
            ->where(function ($q) {
                $q->whereNotNull('ref_article_id')
                  ->orWhereRaw('EXISTS (SELECT 1 FROM ref_articles WHERE ref_articles.source_url = research_recommendations.url)');
            })
            ->delete();

        ResearchRecommendation::byKeyword($keyword)
            ->where('status', 'rejected')
            ->update(['status' => 'pending']);

        // Generate batch_id untuk tracking
        $batchId = now()->format('YmdHis');

        // Dispatch ASYNC - queue worker proses di background
        KeywordResearchJob::dispatch($keyword, 1, 10, $batchId);

        // Redirect ke batch progress page
        return redirect()
            ->route('admin.scraping.batch-progress', ['batch_id' => $batchId, 'keyword' => $keyword])
            ->with('info', "Keyword '{$keyword}' sedang diproses di background. Halaman ini akan auto-refresh untuk memantau progress.");
    }

    /**
     * Research ALL - ASYNC via queue.
     * Ambil 5 keyword dengan approved_count tertinggi.
     * Redirect ke batch progress page.
     */
    public function researchAll(Request $request)
    {
        // ── GUARD: Cek apakah ada jobs pending di queue ──
        $pendingJobs = DB::table('jobs')->count();
        if ($pendingJobs > 0) {
            return redirect()
                ->route('admin.scraping.batch-progress')
                ->with('warning', "Masih ada {$pendingJobs} job yang sedang diproses. Tunggu selesai atau cek progress di halaman ini.");
        }

        // ── Ambil 5 keyword dengan approved_count tertinggi ──
        $allKeywords = ScraperConfig::getKeywords();

        // Dari EditorPreference yang punya approved_count > 0
        $topApproved = EditorPreference::orderByDesc('approved_count')
            ->where('approved_count', '>', 0)
            ->limit(5)
            ->pluck('keyword')
            ->toArray();

        // Kalau kurang dari 5, isi dari ScraperConfig
        $topKeywords = array_slice(array_merge($topApproved, $allKeywords), 0, 5);
        $topKeywords = array_unique(array_map('strtolower', $topKeywords));

        if (count($topKeywords) === 0) {
            // Fallback: ambil 5 dari ScraperConfig
            $topKeywords = array_slice(
                array_map('strtolower', $allKeywords),
                0, 5
            );
        }

        // Generate batch_id untuk tracking semua keyword
        $batchId = now()->format('YmdHis');

        // Dispatch setiap keyword dengan batch_id sama
        foreach ($topKeywords as $keyword) {
            // Cleanup dulu
            ResearchRecommendation::byKeyword($keyword)
                ->where('confidence_score', '<', 45)
                ->delete();

            ResearchRecommendation::byKeyword($keyword)
                ->where('status', 'rejected')
                ->update(['status' => 'pending']);

            KeywordResearchJob::dispatch($keyword, 1, 10, $batchId);
        }

        // Redirect ke batch progress page
        $keywordList = implode(', ', $topKeywords);
        return redirect()
            ->route('admin.scraping.batch-progress', ['batch_id' => $batchId])
            ->with('info', "5 keyword terbaik sedang diproses di background: {$keywordList}. Halaman ini akan auto-refresh untuk memantau progress.");
    }

    // ═══════════════════════════════════════════════════════════════════
    // BATCH PROGRESS METHODS
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Halaman batch progress - pantau research yang sedang berjalan.
     */
    public function batchProgress(Request $request)
    {
        $page = 'Batch Progress';
        $batchId = $request->input('batch_id');

        // Ambil semua keyword yang di-dispatch dengan batch_id ini
        $keywords = [];
        if ($batchId) {
            // Ambil dari jobs table - payload berisi keyword
            $jobs = DB::table('jobs')->get();
            foreach ($jobs as $job) {
                $payload = json_decode($job->payload, true);
                $data = json_decode($payload['data'][0] ?? '{}', true);
                if (!empty($data['keyword']) && !in_array($data['keyword'], $keywords)) {
                    $keywords[] = $data['keyword'];
                }
            }
        }

        // Kalau tidak ada jobs dengan batch_id ini, cek apakah sudah selesai semua
        $totalJobs = DB::table('jobs')->count();

        // Ambil hasil yang sudah ada di ResearchRecommendation dari batch ini
        // (hasil disimpan oleh KeywordResearchJob setelah selesai)
        $completedKeywords = [];
        if ($batchId) {
            // Cek via logs - cari keyword yang sudah selesai diproses
            // Cara paling reliable: cek ResearchRecommendation yang baru dibuat
            $recentRecs = ResearchRecommendation::whereDate('created_at', now()->toDateString())
                ->orderByDesc('created_at')
                ->pluck('keyword')
                ->unique()
                ->values()
                ->toArray();

            // Bandingkan dengan yang di-dispatch
            foreach ($keywords as $kw) {
                $hasRec = ResearchRecommendation::byKeyword($kw)
                    ->whereDate('created_at', now()->toDateString())
                    ->exists();
                $completedKeywords[$kw] = $hasRec ? 'done' : 'pending';
            }
        }

        $allDone = !empty($completedKeywords) && !in_array('pending', $completedKeywords);
        $pendingCount = isset($completedKeywords) ? count(array_filter($completedKeywords, fn($s) => $s === 'pending')) : 0;
        $doneCount = isset($completedKeywords) ? count(array_filter($completedKeywords, fn($s) => $s === 'done')) : 0;

        // Keyword alternatif yang punya hasil (untuk suggest jika 0 hasil)
        $altKeywords = $this->getAlternativeKeywords();

        return view('pages.admin.scraping.batch-progress', compact(
            'page', 'batchId', 'keywords', 'completedKeywords',
            'allDone', 'pendingCount', 'doneCount', 'altKeywords'
        ));
    }

    /**
     * API endpoint untuk polling status batch via AJAX.
     * Returns JSON untuk auto-refresh.
     */
    public function batchStatus(Request $request)
    {
        $batchId = $request->input('batch_id');

        // Ambil keyword dari jobs table
        $keywords = [];
        $jobs = DB::table('jobs')->get();
        foreach ($jobs as $job) {
            $payload = json_decode($job->payload, true);
            $data = json_decode($payload['data'][0] ?? '{}', true);
            if (!empty($data['keyword']) && !in_array($data['keyword'], $keywords)) {
                $keywords[] = $data['keyword'];
            }
        }

        // Cek status masing-masing keyword
        $status = [];
        foreach ($keywords as $kw) {
            $rec = ResearchRecommendation::byKeyword($kw)
                ->whereDate('created_at', now()->toDateString())
                ->first();
            $status[$kw] = $rec ? 'done' : 'processing';
        }

        $pendingJobs = DB::table('jobs')->count();
        $allDone = $pendingJobs === 0 && !empty($status);
        $doneCount = count(array_filter($status, fn($s) => $s === 'done'));
        $totalCount = count($status);

        // Keyword alternatif
        $altKeywords = $this->getAlternativeKeywords();

        return response()->json([
            'pending_jobs' => $pendingJobs,
            'done' => $allDone,
            'done_count' => $doneCount,
            'total_count' => $totalCount,
            'status' => $status,
            'alt_keywords' => $altKeywords,
        ]);
    }

    /**
     * Ambil keyword alternatif yang punya hasil di database.
     */
    protected function getAlternativeKeywords(): array
    {
        return ResearchRecommendation::selectRaw('DISTINCT keyword')
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->pluck('keyword')
            ->toArray();
    }

    // ═══════════════════════════════════════════════════════════════════
    // KEYWORD MANAGEMENT
    // ═══════════════════════════════════════════════════════════════════

    public function indexKeywords()
    {
        $page = 'Kelola Kata Kunci';
        $keywords = EditorPreference::orderByDesc('approved_count')->get();
        return view('pages.admin.scraping.keywords', compact('page', 'keywords'));
    }

    public function storeKeyword(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|min:2|max:50|unique:editor_preferences,keyword',
        ]);

        EditorPreference::create([
            'keyword' => trim(strtolower($validated['keyword'])),
        ]);

        return back()->with('success', "Kata kunci '{$validated['keyword']}' berhasil ditambahkan.");
    }

    public function destroyKeyword($id)
    {
        $pref = EditorPreference::findOrFail($id);
        $keyword = $pref->keyword;
        $pref->delete();

        return back()->with('success', "Kata kunci '{$keyword}' berhasil dihapus.");
    }

    public function clearBlocklists()
    {
        $cleared = EditorPreference::whereNotNull('blocklist_urls')
            ->where('blocklist_urls', '!=', '')
            ->where('blocklist_urls', '!=', '[]')
            ->update(['blocklist_urls' => null, 'blocklist_patterns' => null]);

        return back()->with('info', "Blocklist berhasil dibersihkan dari {$cleared} record.");
    }
}
