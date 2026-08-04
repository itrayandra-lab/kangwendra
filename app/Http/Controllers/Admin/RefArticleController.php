<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateFromRefArticleJob;
use App\Models\EditorPreference;
use App\Models\Posts;
use App\Models\PostCategory;
use App\Models\PostTags;
use App\Models\RefArticle;
use App\Models\ResearchRecommendation;
use Illuminate\Http\Request;
use DateTimeZone;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RefArticleController extends Controller
{
    /**
     * Index: Show all RefArticles that were manually moved from scrape results.
     */
    public function index(Request $request)
    {
        $page   = 'Ref Articles';
        $status = $request->input('status');
        $domain = $request->input('domain');

        $query = RefArticle::where('moved_from_scrape', true)->latest();

        if ($status) {
            $query->where('ai_research_status', $status);
        }
        if ($domain) {
            $query->where('source_domain', 'like', "%{$domain}%");
        }

        $articles = $query->paginate(15)->withQueryString();

        $stats = [
            'total'      => RefArticle::where('moved_from_scrape', true)->count(),
            'idle'      => RefArticle::where('moved_from_scrape', true)->where(fn($q) => $q->whereNull('ai_research_status')->orWhere('ai_research_status', 'idle'))->count(),
            'researching' => RefArticle::where('moved_from_scrape', true)->where('ai_research_status', 'researching')->count(),
            'done'      => RefArticle::where('moved_from_scrape', true)->where('ai_research_status', 'done')->count(),
            'failed'    => RefArticle::where('moved_from_scrape', true)->where('ai_research_status', 'failed')->count(),
        ];

        return view('pages.admin.ref-articles.index', compact(
            'page', 'articles', 'stats', 'status', 'domain'
        ));
    }

    // ── GENERATE PARAPHRASE ──────────────────────────────────

    /**
     * Generate All Pending RefArticles.
     * Menggunakan daily_limit dari ScraperConfig, bukan hardcoded.
     */
    public function generateAll()
    {
        $dailyLimit = (int) \App\Models\ScraperConfig::getDailyLimit();

        // Cek berapa banyak yang sudah done hari ini (hit daily limit)
        $tz = new DateTimeZone('Asia/Jakarta');
        $todayStart = (new \DateTime('today', $tz))->format('Y-m-d 00:00:00');
        $todayEnd   = (new \DateTime('today', $tz))->format('Y-m-d 23:59:59');

        $doneToday = \App\Models\Posts::whereBetween('published_at', [$todayStart, $todayEnd])
            ->where('status', 'active')
            ->count();

        if ($doneToday >= $dailyLimit) {
            return back()->with('warning', "Daily limit ({$dailyLimit} artikel/hari) sudah tercapai hari ini ({$doneToday} done). Coba lagi besok.");
        }

        // Hitung berapa banyak yang sudah researching hari ini (dari batch RefArticle)
        $dispatchedToday = \App\Models\RefArticle::where('moved_from_scrape', true)
            ->where('ai_research_status', 'done')
            ->whereDate('updated_at', now($tz)->toDateString())
            ->count();

        // Sisa slot hari ini
        $remainingSlots = max(0, $dailyLimit - $dispatchedToday);
        if ($remainingSlots === 0) {
            return back()->with('warning', "Semua slot harian ({$dailyLimit}) sudah terpakai. Done today: {$dispatchedToday}. Coba lagi besok.");
        }

        // Ambil idle articles (batasnya = sisa slot, bukan 5)
        $idleArticles = RefArticle::where('moved_from_scrape', true)
            ->where(fn($q) => $q->whereNull('ai_research_status')->orWhere('ai_research_status', 'idle'))
            ->orderBy('created_at')
            ->limit($remainingSlots)
            ->get();

        if ($idleArticles->isEmpty()) {
            return back()->with('info', "Tidak ada Ref Article yang idle ({$doneToday}/{$dailyLimit} done today).");
        }

        // Generate a batch ID to track this group
        $batchId = now()->format('YmdHis');

        $dispatched = 0;
        foreach ($idleArticles as $article) {
            // Update status to researching BEFORE dispatching
            $article->update([
                'ai_research_status' => 'researching',
                'batch_id' => $batchId,
            ]);
            GenerateFromRefArticleJob::dispatch($article->id);
            $dispatched++;
        }

        // Redirect to batch progress page with the batch ID
        return redirect()->route('ref-articles.batch-progress', ['batch_id' => $batchId])
            ->with('success', "{$dispatched} artikel di-queue untuk generate. ({$doneToday} done + {$dispatched} queued = " . ($doneToday + $dispatched) . "/{$dailyLimit} harian).");
    }

    /**
     * Generate/paraphrase a single RefArticle.
     */
    public function generate(RefArticle $refArticle)
    {
        if (!$refArticle->source_url) {
            return back()->with('error', 'Source URL tidak tersedia.');
        }

        // Prevent double-dispatch
        if (in_array($refArticle->ai_research_status, ['researching', 'done'])) {
            return back()->with('warning', "Artikel ini sedang '{$refArticle->ai_research_status}'. Tidak bisa dispatch ulang.");
        }

        $refArticle->update([
            'ai_research_status' => 'researching',
            'batch_id' => now()->format('YmdHis'),
        ]);
        GenerateFromRefArticleJob::dispatch($refArticle->id);

        return redirect()->route('ref-articles.batch-progress', ['batch_id' => $refArticle->batch_id]);
    }

    /**
     * Retry generate after failed status.
     */
    public function retry(RefArticle $refArticle)
    {
        if (!$refArticle->source_url) {
            return back()->with('error', 'Source URL tidak tersedia.');
        }

        // Only allow retry from failed status
        if ($refArticle->ai_research_status !== 'failed') {
            return back()->with('warning', "Retry hanya bisa dari status 'failed'. Status sekarang: '{$refArticle->ai_research_status}'.");
        }

        $refArticle->update([
            'ai_research_status' => 'researching',
            'batch_id' => now()->format('YmdHis'),
        ]);
        GenerateFromRefArticleJob::dispatch($refArticle->id);

        return redirect()->route('ref-articles.batch-progress', ['batch_id' => $refArticle->batch_id]);
    }

    // ── VIEW DETAIL ───────────────────────────────────────────

    public function show(RefArticle $refArticle)
    {
        $page = 'Detail Referensi';
        return view('pages.admin.ref-articles.show', compact('page', 'refArticle'));
    }

    // ── DELETE ───────────────────────────────────────────────

    public function destroy(RefArticle $refArticle)
    {
        $refArticle->delete();
        return back()->with('success', 'Dihapus.');
    }

    // ── EDIT GENERATED POST ──────────────────────────────────

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

    // ── BATCH PROGRESS ─────────────────────────────────────────

    /**
     * Show batch progress page for a given batch_id.
     */
    public function batchProgress(Request $request)
    {
        $page = 'Batch Progress';
        $batchId = $request->input('batch_id');

        if (!$batchId) {
            return redirect()->route('ref-articles.index');
        }

        $articles = RefArticle::where('batch_id', $batchId)->get();
        $total = $articles->count();
        $success = $articles->where('ai_research_status', 'done')->count();
        $failed = $articles->where('ai_research_status', 'failed')->count();
        $processing = $articles->where('ai_research_status', 'researching')->count();
        $pending = $total - $success - $failed - $processing;

        $failedArticles = $articles->where('ai_research_status', 'failed');

        return view('pages.admin.ref-articles.batch-progress', compact(
            'page', 'batchId', 'total', 'success', 'failed', 'processing', 'pending', 'failedArticles'
        ));
    }

    /**
     * API endpoint for batch progress polling (AJAX).
     */
    public function batchStatus(Request $request)
    {
        $batchId = $request->input('batch_id');

        if (!$batchId) {
            return response()->json(['error' => 'No batch_id', 'status' => 'error']);
        }

        $articles = RefArticle::where('batch_id', $batchId)->get();
        $total = $articles->count();

        if ($total === 0) {
            return response()->json(['error' => 'Batch not found', 'status' => 'error']);
        }

        $success = $articles->where('ai_research_status', 'done')->count();
        $failed = $articles->where('ai_research_status', 'failed')->count();
        $processing = $articles->where('ai_research_status', 'researching')->count();

        $status = ($success + $failed === $total) ? 'complete' : 'processing';

        return response()->json([
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
            'processing' => $processing,
            'status' => $status,
        ]);
    }
}
