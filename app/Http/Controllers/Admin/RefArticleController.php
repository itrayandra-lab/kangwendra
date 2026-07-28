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
            'researching'   => RefArticle::where('moved_from_scrape', true)->where('ai_research_status', 'researching')->count(),
            'processing'=> RefArticle::where('moved_from_scrape', true)->where('ai_research_status', 'processing')->count(),
            'done'      => RefArticle::where('moved_from_scrape', true)->where('ai_research_status', 'done')->count(),
            'failed'    => RefArticle::where('moved_from_scrape', true)->where('ai_research_status', 'failed')->count(),
        ];

        return view('pages.admin.ref-articles.index', compact(
            'page', 'articles', 'stats', 'status', 'domain'
        ));
    }

    // ── GENERATE PARAPHRASE ──────────────────────────────────

    /**
     * Generate All Pending RefArticles (max 5 per batch).
     */
    public function generateAll()
    {
        $idleArticles = RefArticle::where('moved_from_scrape', true)
            ->where(fn($q) => $q->whereNull('ai_research_status')->orWhere('ai_research_status', 'idle'))
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        if ($idleArticles->isEmpty()) {
            return back()->with('info', 'Tidak ada Ref Article yang idle. Semua sudah diproses.');
        }

        $dispatched = 0;
        foreach ($idleArticles as $article) {
            GenerateFromRefArticleJob::dispatch($article->id);
            $dispatched++;
        }

        return back()->with('success', "{$dispatched} job paraphrase di-queue. Periksa halaman Postingan AI untuk hasilnya.");
    }

    /**
     * Generate/paraphrase a single RefArticle.
     */
    public function generate(RefArticle $refArticle)
    {
        if (!$refArticle->source_url) {
            return back()->with('error', 'Source URL tidak tersedia.');
        }

        GenerateFromRefArticleJob::dispatch($refArticle->id);

        return back()->with('success', 'Generate job dispatched. Periksa halaman Postingan AI.');
    }

    /**
     * Retry generate after failed status.
     */
    public function retry(RefArticle $refArticle)
    {
        if (!$refArticle->source_url) {
            return back()->with('error', 'Source URL tidak tersedia.');
        }

        GenerateFromRefArticleJob::dispatch($refArticle->id);

        return back()->with('success', 'Retry job dispatched. Periksa halaman Postingan AI.');
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
}
