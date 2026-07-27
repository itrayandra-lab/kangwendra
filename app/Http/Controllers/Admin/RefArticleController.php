<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ScrapeParaphraseJob;
use App\Jobs\UpdateEditorPreferenceJob;
use App\Models\EditorPreference;
use App\Models\Posts;
use App\Models\PostCategory;
use App\Models\PostTags;
use App\Models\RefArticle;
use App\Models\ResearchRecommendation;
use App\Services\EditorPreferenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RefArticleController extends Controller
{
    public function index(Request $request)
    {
        $page   = 'Manajemen Artikel AI';
        $status = $request->input('status');
        $domain = $request->input('domain');

        $query = RefArticle::latest();

        if ($status) {
            $query->where('ai_status', $status);
        }
        if ($domain) {
            $query->where('source_domain', 'like', "%{$domain}%");
        }

        $articles = $query->paginate(15)->withQueryString();

        $stats = [
            'total'      => RefArticle::count(),
            'pending'    => RefArticle::where('ai_status', 'pending')->count(),
            'processing' => RefArticle::where('ai_status', 'processing')->count(),
            'done'       => RefArticle::where('ai_status', 'done')->count(),
            'failed'     => RefArticle::where('ai_status', 'failed')->count(),
        ];

        // Pending recommendations
        $pendingRecommendations = ResearchRecommendation::pending()
            ->orderByDesc('confidence_score')
            ->limit(10)
            ->get();

        // Pipeline stats
        $prefStats = [
            'total_keywords' => EditorPreference::count(),
            'avg_confidence' => round(EditorPreference::avg('confidence') ?? 0, 1),
            'high_confidence' => EditorPreference::hasConfidence(85)->count(),
        ];

        return view('pages.admin.ref-articles.index', compact(
            'page', 'articles', 'stats', 'status', 'domain', 'pendingRecommendations', 'prefStats'
        ));
    }

    // ── RESEARCH & RECOMMENDATIONS ─────────────────────────────

    /**
     * Dispatch keyword research job SYNCHRONOUSLY so results appear immediately
     */
    public function research(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|min:2|max:255',
        ]);

        $keyword = trim($validated['keyword']);

        // Always delete old low-quality recommendations before re-researching
        $deleted = ResearchRecommendation::byKeyword($keyword)
            ->where('confidence_score', '<', 45)
            ->delete();

        // Reset rejected recommendations so they can be re-fetched
        ResearchRecommendation::byKeyword($keyword)
            ->where('status', 'rejected')
            ->update(['status' => 'pending']);

        // Run research SYNCHRONOUSLY so results appear immediately
        $job = new \App\Jobs\KeywordResearchJob($keyword);
        $job->handle(app(\App\Services\SitemapScraperService::class));

        Log::info('Keyword research completed synchronously', [
            'keyword' => $keyword,
            'deleted_low_score' => $deleted,
        ]);

        $count = ResearchRecommendation::byKeyword($keyword)->where('confidence_score', '>=', 45)->count();

        return redirect()
            ->route('ref-articles.recommendations', urlencode($keyword))
            ->with('info', "Research selesai! {$count} URL AI-focused ditemukan untuk '{$keyword}'. {$deleted} hasil lama dihapus.");
    }

    /**
     * Show research recommendations for a keyword
     */
    public function recommendations(string $keyword)
    {
        $keyword = urldecode($keyword);
        $page = 'Rekomendasi Artikel';

        // Show ONLY high-confidence recommendations (score >= 45)
        // This filters out old low-quality results from before the fixes
        $recommendations = ResearchRecommendation::byKeyword($keyword)
            ->where('confidence_score', '>=', 45)
            ->orderByDesc('confidence_score')
            ->get();

        // Count old low-score recommendations separately
        $lowScoreCount = ResearchRecommendation::byKeyword($keyword)
            ->where('confidence_score', '<', 45)
            ->count();

        $pref = EditorPreference::where('keyword', strtolower($keyword))->first();

        return view('pages.admin.ref-articles.recommendations', compact(
            'page', 'keyword', 'recommendations', 'pref', 'lowScoreCount'
        ));
    }

    /**
     * Approve + scrape a single recommendation
     */
    public function approveRecommendation(Request $request, int $id)
    {
        $rec = ResearchRecommendation::findOrFail($id);
        $rec->update(['status' => 'approved']);

        // Dispatch scrape + paraphrase
        ScrapeParaphraseJob::dispatch(
            $rec->url,
            $rec->domain ?? parse_url($rec->url, PHP_URL_HOST),
            $rec->keyword,
            Str::uuid()->toString(),
            $rec->confidence_score ?? 50,
            $rec->id,
            false // not auto mode
        );

        // Record AI learning
        UpdateEditorPreferenceJob::dispatch('approval', [
            'keyword' => $rec->keyword,
            'url'     => $rec->url,
            'topic'   => $this->extractTopic($rec->title ?? ''),
        ]);

        Log::info('Recommendation approved + dispatched', ['rec_id' => $id, 'url' => $rec->url]);

        return back()->with('success', "Scraping + Paraphrase untuk '{$rec->title}' sedang diproses.");
    }

    /**
     * Reject a recommendation
     */
    public function rejectRecommendation(Request $request, int $id)
    {
        $rec = ResearchRecommendation::findOrFail($id);
        $rec->update(['status' => 'rejected']);

        // Record rejection in AI learning
        UpdateEditorPreferenceJob::dispatch('rejection', [
            'keyword' => $rec->keyword,
            'url'     => $rec->url,
            'topic'   => $this->extractTopic($rec->title ?? ''),
        ]);

        Log::info('Recommendation rejected', ['rec_id' => $id, 'url' => $rec->url]);

        return back()->with('success', "URL ditolak dan masuk blocklist.");
    }

    /**
     * Approve all pending recommendations for a keyword
     */
    public function approveAll(Request $request, string $keyword)
    {
        $keyword = urldecode($keyword);

        $recs = ResearchRecommendation::byKeyword($keyword)
            ->pending()
            ->orderByDesc('confidence_score')
            ->limit(5)
            ->get();

        if ($recs->isEmpty()) {
            return back()->with('error', 'Tidak ada rekomendasi yang bisa disetujui.');
        }

        $count = 0;
        foreach ($recs as $rec) {
            $rec->update(['status' => 'approved']);
            ScrapeParaphraseJob::dispatch(
                $rec->url,
                $rec->domain ?? parse_url($rec->url, PHP_URL_HOST),
                $rec->keyword,
                Str::uuid()->toString(),
                $rec->confidence_score ?? 50,
                $rec->id,
                false
            );
            $count++;
        }

        return back()->with('success', "{$count} artikel sedang diproses.");
    }

    // ── SCRAPE & PARAPHRASE ──────────────────────────────────

    /**
     * Manual scrape + paraphrase for a specific URL (for future use)
     */
    public function scrapeAndParaphrase(Request $request, int $id)
    {
        $rec = ResearchRecommendation::findOrFail($id);

        if ($rec->status === 'scraped') {
            return back()->with('error', 'URL ini sudah discrape sebelumnya.');
        }

        ScrapeParaphraseJob::dispatch(
            $rec->url,
            $rec->domain ?? parse_url($rec->url, PHP_URL_HOST),
            $rec->keyword,
            Str::uuid()->toString(),
            $rec->confidence_score ?? 50,
            $rec->id,
            false
        );

        return back()->with('success', "Scrape + Paraphrase untuk '{$rec->title}' sedang diproses.");
    }

    // ── BATCH PROGRESS ───────────────────────────────────────

    public function batchStatus(Request $request)
    {
        $batchId = $request->get('batch_id');
        if (!$batchId) {
            return response()->json(['error' => 'no batch_id']);
        }

        $total      = RefArticle::where('batch_id', $batchId)->count();
        $done       = RefArticle::where('batch_id', $batchId)->whereIn('ai_status', ['done', 'failed'])->count();
        $success    = RefArticle::where('batch_id', $batchId)->where('ai_status', 'done')->count();
        $failed     = RefArticle::where('batch_id', $batchId)->where('ai_status', 'failed')->count();
        $processing = RefArticle::where('batch_id', $batchId)->where('ai_status', 'processing')->count();

        $failedArticles = RefArticle::where('batch_id', $batchId)
            ->where('ai_status', 'failed')
            ->select('id', 'title', 'ai_error')
            ->limit(10)
            ->get();

        return response()->json([
            'batch_id'   => $batchId,
            'total'     => $total,
            'done'      => $done,
            'success'   => $success,
            'failed'    => $failed,
            'processing'=> $processing,
            'errors'    => $failedArticles->map(fn($a) => ['title' => $a->title, 'error' => $a->ai_error]),
            'status'    => $total > 0 && $done >= $total ? 'completed' : 'running',
        ]);
    }

    public function batchProgress(Request $request)
    {
        $batchId = $request->get('batch_id');

        if (!$batchId) {
            return redirect()->route('ref-articles.index');
        }

        $page = 'Progress Generate AI';

        $total      = RefArticle::where('batch_id', $batchId)->count();
        $done       = RefArticle::where('batch_id', $batchId)->whereIn('ai_status', ['done', 'failed'])->count();
        $success    = RefArticle::where('batch_id', $batchId)->where('ai_status', 'done')->count();
        $failed     = RefArticle::where('batch_id', $batchId)->where('ai_status', 'failed')->count();
        $processing = RefArticle::where('batch_id', $batchId)->where('ai_status', 'processing')->count();

        $failedArticles = RefArticle::where('batch_id', $batchId)
            ->where('ai_status', 'failed')
            ->select('id', 'title', 'ai_error')
            ->limit(10)
            ->get();

        return view('pages.admin.ref-articles.batch-progress', compact(
            'page', 'batchId', 'total', 'done', 'success', 'failed', 'processing', 'failedArticles'
        ));
    }

    // ── REF ARTICLE CRUD ──────────────────────────────────────

    public function destroy(RefArticle $refArticle)
    {
        $refArticle->delete();
        return back()->with('success', 'Dihapus.');
    }

    public function show(RefArticle $refArticle)
    {
        $page = 'Detail Referensi';
        return view('pages.admin.ref-articles.show', compact('page', 'refArticle'));
    }

    /**
     * Generate/paraphrase article from a RefArticle (for pending status)
     */
    public function generate(RefArticle $refArticle)
    {
        if (!$refArticle->source_url) {
            return back()->with('error', 'Source URL tidak tersedia.');
        }

        $refArticle->update(['ai_status' => 'pending']);

        ScrapeParaphraseJob::dispatch(
            $refArticle->source_url,
            $refArticle->source_domain ?? parse_url($refArticle->source_url, PHP_URL_HOST),
            $refArticle->source_keyword ?? 'general',
            $refArticle->batch_id ?? Str::uuid()->toString(),
            $refArticle->ai_confidence ?? 50,
            null,
            $refArticle->id
        );

        return back()->with('success', 'Generate job dispatched.');
    }

    /**
     * Retry generate after failed status
     */
    public function retry(RefArticle $refArticle)
    {
        if (!$refArticle->source_url) {
            return back()->with('error', 'Source URL tidak tersedia.');
        }

        $refArticle->update(['ai_status' => 'pending', 'ai_error' => null]);

        ScrapeParaphraseJob::dispatch(
            $refArticle->source_url,
            $refArticle->source_domain ?? parse_url($refArticle->source_url, PHP_URL_HOST),
            $refArticle->source_keyword ?? 'general',
            $refArticle->batch_id ?? Str::uuid()->toString(),
            $refArticle->ai_confidence ?? 50,
            null,
            $refArticle->id
        );

        return back()->with('success', 'Retry job dispatched.');
    }

    // ── EDIT POST (from generated post) ───────────────────────

    public function editPost(RefArticle $refArticle)
    {
        $post = null;
        if ($refArticle->generated_post_id) {
            $post = Posts::with('category')->find($refArticle->generated_post_id);
        }
        if (!$post) {
            $post = Posts::with('category')->where('source', $refArticle->source_url)->first();
        }
        if (!$post) {
            return back()->with('error', 'Post belum di-generate. Generate dulu dari tabel.');
        }

        $categories = PostCategory::orderBy('name')->get();
        $allTags = PostTags::orderBy('name')->get();
        $page = 'Edit Post';

        return view('pages.admin.ref-articles.edit-post', compact(
            'page', 'refArticle', 'post', 'categories', 'allTags'
        ));
    }

    public function updatePost(Request $request, RefArticle $refArticle)
    {
        $post = null;
        if ($refArticle->generated_post_id) {
            $post = Posts::find($refArticle->generated_post_id);
        }
        if (!$post) {
            $post = Posts::where('source', $refArticle->source_url)->first();
        }
        if (!$post) {
            return back()->with('error', 'Post tidak ditemukan.');
        }

        $tagsInput = $request->input('tags_string', '');
        $tags = array_filter(array_map('trim', explode(',', $tagsInput)));

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'content'     => 'required|string',
            'category_id'  => 'required|integer|exists:post_categories,id',
            'status'       => 'required|in:active,draft',
            'published_at' => 'required|date',
            'slug'         => 'nullable|string|max:255',
        ]);

        foreach ($tags as $tagName) {
            if ($tagName) {
                PostTags::firstOrCreate(
                    ['slug' => Str::slug($tagName)],
                    ['name' => $tagName]
                );
            }
        }

        $slug = $validated['slug'] ?? Str::slug($validated['title']);
        if ($slug !== $post->slug) {
            $base = $slug;
            $i = 1;
            while (Posts::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug = $base . '-' . $i++;
            }
        }

        $post->update([
            'title'        => $validated['title'],
            'content'      => $validated['content'],
            'category_id'  => $validated['category_id'],
            'tags'         => $tags,
            'status'       => $validated['status'],
            'published_at' => $validated['published_at'],
            'slug'         => $slug,
            'updated_by'   => auth()->id(),
        ]);

        $meta = is_array($post->meta_data) ? $post->meta_data : [];
        $meta['edited_at'] = now()->toDateTimeString();
        $meta['edited_by'] = auth()->user()->name ?? auth()->id();
        $post->update(['meta_data' => $meta]);

        return redirect()->route('ref-articles.index')
            ->with('success', 'Post berhasil disimpan: ' . Str::limit($post->title, 50));
    }

    // ── HELPERS ───────────────────────────────────────────────

    protected function extractTopic(string $title): string
    {
        $aiTopics = ['ai', 'llm', 'machine learning', 'deep learning', 'openai', 'gemini', 'chatgpt', 'neural', 'generative', 'agentic'];
        foreach ($aiTopics as $topic) {
            if (stripos($title, $topic) !== false) {
                return 'AI & Teknologi';
            }
        }

        $seoTopics = ['seo', 'search', 'google', 'algorithm', 'serp'];
        foreach ($seoTopics as $topic) {
            if (stripos($title, $topic) !== false) {
                return 'SEO & Search';
            }
        }

        return 'Teknologi';
    }
}
