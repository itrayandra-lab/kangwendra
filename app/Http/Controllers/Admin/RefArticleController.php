<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateAiArticleJob;
use App\Models\Posts;
use App\Models\RefArticle;
use App\Models\PostCategory;
use App\Models\PostTags;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RefArticleController extends Controller
{
    public function index(Request $request)
    {
        $page   = 'Manajemen Artikel AI';
        $status = $request->input('status');
        $source = $request->input('source');

        $query = RefArticle::latest();
        if ($status) {
            $query->where('ai_status', $status);
        }
        if ($source) {
            $query->where('source_domain', 'like', "%{$source}%");
        }

        $articles = $query->paginate(15)->withQueryString();

        $stats = [
            'total'      => RefArticle::count(),
            'pending'    => RefArticle::where('ai_status', 'pending')->count(),
            'processing' => RefArticle::where('ai_status', 'processing')->count(),
            'done'       => RefArticle::where('ai_status', 'done')->count(),
            'failed'     => RefArticle::where('ai_status', 'failed')->count(),
        ];

        // Active batch progress from session
        $batchId = session('ai_batch_id');
        $batch = null;
        if ($batchId) {
            $batchTotal = RefArticle::where('batch_id', $batchId)->count();
            if ($batchTotal > 0) {
                $batchDone = RefArticle::where('batch_id', $batchId)
                    ->whereIn('ai_status', ['done', 'failed'])->count();
                $batch = [
                    'batch_id'  => $batchId,
                    'total'    => $batchTotal,
                    'done'     => $batchDone,
                    'success'  => RefArticle::where('batch_id', $batchId)->where('ai_status', 'done')->count(),
                    'failed'   => RefArticle::where('batch_id', $batchId)->where('ai_status', 'failed')->count(),
                ];
            }
        }

        return view('pages.admin.ref-articles.index', compact(
            'page', 'articles', 'stats', 'status', 'source', 'batch'
        ));
    }

    // ── SCRAPE ──────────────────────────────────────────

    public function scrapeYahoo()
    {
        set_time_limit(0);
        try {
            $saved = (new \App\Services\YahooTechScraperService(5))->scrapeAndSave();
            return back()->with('success', "Scraping Yahoo Tech selesai! {$saved} artikel disimpan.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function scrapePharma()
    {
        set_time_limit(0);
        try {
            $saved = (new \App\Services\TechPharmaScraperService(3))->scrapeAndSave();
            return back()->with('success', "Scraping Tech Pharma selesai! {$saved} artikel disimpan.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function scrapeAll()
    {
        set_time_limit(0);
        try {
            $y = (new \App\Services\YahooTechScraperService(5))->scrapeAndSave();
            $p = (new \App\Services\TechPharmaScraperService(3))->scrapeAndSave();
            return back()->with('success', "Scraping selesai! {$y} Yahoo Tech + {$p} Pharma = " . ($y + $p) . " total.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // ── GENERATE AI (ASYNC via queue) ──────────────────

    public function generateAll(Request $request)
    {
        $limit = (int) $request->input('limit', 5);

        RefArticle::failed()->update(['ai_status' => 'pending', 'ai_error' => null]);
        $pending = RefArticle::pending()->latest()->take($limit)->get();

        if ($pending->isEmpty()) {
            return back()->with('error', 'Tidak ada artikel pending. Klik Scrape dulu!');
        }

        foreach ($pending as $ref) {
            $ref->update(['ai_status' => 'processing', 'batch_id' => null]);
            GenerateAiArticleJob::dispatch($ref->id);
        }

        return back()->with('success', "{$pending->count()} artikel masuk queue. Queue worker sedang memproses...");
    }

    /**
     * AJAX: cek status batch dari file
     */
    public function batchStatus(Request $request)
    {
        $batchId = $request->get('batch_id');
        if (!$batchId) return response()->json(['error' => 'no batch_id']);

        $batchFile = storage_path("logs/batch_{$batchId}.json");
        if (!file_exists($batchFile)) {
            return response()->json(['error' => 'batch file not found', 'batch_id' => $batchId]);
        }

        $data = json_decode(file_get_contents($batchFile), true);

        return response()->json([
            'batch_id'   => $data['batch_id'] ?? $batchId,
            'total'     => $data['total'] ?? 0,
            'success'   => $data['success'] ?? 0,
            'failed'    => $data['failed'] ?? 0,
            'errors'    => $data['errors'] ?? [],
            'processed'  => $data['processed'] ?? [],
            'status'    => $data['status'] ?? 'running',
        ]);
    }

    public function batchProgress(Request $request)
    {
        $batchId = $request->get('batch_id') ?? session('ai_batch_id');

        if (!$batchId) {
            return redirect()->route('ref-articles.index');
        }

        $page = 'Progress Generate AI';

        $total = RefArticle::where('batch_id', $batchId)->count();
        $done = RefArticle::where('batch_id', $batchId)->whereIn('ai_status', ['done', 'failed'])->count();
        $success = RefArticle::where('batch_id', $batchId)->where('ai_status', 'done')->count();
        $failed = RefArticle::where('batch_id', $batchId)->where('ai_status', 'failed')->count();
        $processing = RefArticle::where('batch_id', $batchId)->where('ai_status', 'processing')->count();
        $pending = RefArticle::where('batch_id', $batchId)->where('ai_status', 'pending')->count();

        $failedArticles = RefArticle::where('batch_id', $batchId)
            ->where('ai_status', 'failed')
            ->select('id', 'title', 'ai_error')
            ->limit(10)
            ->get();

        return view('pages.admin.ref-articles.batch-progress', compact(
            'page', 'batchId', 'total', 'done', 'success', 'failed', 'processing', 'pending', 'failedArticles'
        ));
    }

    // Generate 1 artikel
    public function generateOne(RefArticle $refArticle)
    {
        set_time_limit(0);

        if ($refArticle->ai_status === 'done') {
            return back()->with('error', 'Sudah di-generate. Gunakan Retry untuk ulangi.');
        }

        $refArticle->update(['ai_status' => 'processing']);

        try {
            $job = new GenerateAiArticleJob($refArticle->id);
            $job->handle();
            return back()->with('success', "Berhasil: " . substr($refArticle->title, 0, 60));
        } catch (\Exception $e) {
            $refArticle->update(['ai_status' => 'failed', 'ai_error' => $e->getMessage()]);
            return back()->with('error', 'Gagal: ' . substr($e->getMessage(), 0, 100));
        }
    }

    // Retry 1 artikel
    public function retry(RefArticle $refArticle)
    {
        set_time_limit(0);

        $refArticle->update(['ai_status' => 'processing', 'ai_error' => null]);

        try {
            $job = new GenerateAiArticleJob($refArticle->id);
            $job->handle();
            return back()->with('success', "Retry berhasil: " . substr($refArticle->title, 0, 60));
        } catch (\Exception $e) {
            $refArticle->update(['ai_status' => 'failed', 'ai_error' => $e->getMessage()]);
            return back()->with('error', 'Gagal: ' . substr($e->getMessage(), 0, 100));
        }
    }

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

    // ── EDIT POST (from generated post) ──────────────────

    public function editPost(RefArticle $refArticle)
    {
        // Cari post: pertama via generated_post_id, fallback via source_url
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
            'title'         => 'required|string|max:255',
            'content'      => 'required|string',
            'category_id'  => 'required|integer|exists:post_categories,id',
            'status'       => 'required|in:active,draft',
            'published_at' => 'required|date',
            'slug'         => 'nullable|string|max:255',
        ]);

        // Create new tags in DB
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
            'content'     => $validated['content'],
            'category_id' => $validated['category_id'],
            'tags'        => $tags,
            'status'      => $validated['status'],
            'published_at' => $validated['published_at'],
            'slug'        => $slug,
            'updated_by'  => auth()->id(),
        ]);

        $meta = is_array($post->meta_data) ? $post->meta_data : (@json_decode($post->meta_data, true) ?: []);
        $meta['edited_at'] = now()->toDateTimeString();
        $meta['edited_by'] = auth()->user()->name ?? auth()->id();
        $post->update(['meta_data' => $meta]);

        return redirect()->route('ref-articles.index')
            ->with('success', 'Post berhasil disimpan: ' . Str::limit($post->title, 50));
    }
}
