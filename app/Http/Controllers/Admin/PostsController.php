<?php

namespace App\Http\Controllers\Admin;

use App\Models\Posts;
use App\Models\PostTags;
use App\Helpers\FileHelper;
use Illuminate\Support\Str;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use App\Jobs\DistributePostJob;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\ShareDomain;
use App\Models\WebIdentity;
use Illuminate\Support\Facades\Auth;

use Yajra\DataTables\Facades\DataTables;

class PostsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $posts = Posts::with(['category', 'createdBy', 'updatedBy'])
                ->select('posts.*');

            // Filter: show only AI posts if ?source=ai
            if ($request->get('source') === 'ai') {
                $posts->where(function ($q) {
                    $q->where('published_by', 'system')
                      ->orWhereNotNull('source');
                });
            } elseif ($request->get('source') === 'manual') {
                // Show only manual/editor posts
                $posts->where(function ($q) {
                    $q->where('published_by', '!=', 'system')
                      ->orWhereNull('published_by');
                });
            }

            $posts->latest();

            return DataTables::of($posts)
                ->addIndexColumn()
                ->addColumn('link', fn($post) => '<a href="/'.$post->category?->slug.'/'.$post->slug.'" target="_blank"><i class="fa fa-external-link"></i> Lihat</a>')
                ->addColumn('image', function ($post) {
                    return $post->image
                        ? '<img src="'.getFile($post->image).'" class="img-thumbnail" style="width:60px;height:40px;object-fit:cover;">'
                        : '<span class="text-muted">Tidak ada</span>';
                })
                ->editColumn('counter', fn($post) => number_format($post->counter))
                
                ->editColumn('status', function($post) {
                    $isPublished = $post->status === 'active' 
                                    && $post->published_at 
                                    && $post->published_at <= now();

                    return $isPublished
                        ? '<span class="label label-success">Published</span>'
                        : '<span class="label label-warning">Draft</span>';
                })

                ->addColumn('category', fn($post) => $post->category?->name ?? '-')
                ->addColumn('tags', function ($post) {
                    if (!$post->tags) return '<span class="text-muted">Tidak ada</span>';
                    $tagIds = is_array($post->tags) ? $post->tags : ($post->tags ? json_decode($post->tags, true) : []);
                    if (empty($tagIds)) return '<span class="text-muted">Tidak ada</span>';

                    $tagNames = PostTags::whereIn('id', $tagIds)->pluck('name')->take(5)->implode(', ');
                    return $tagNames ?: '<span class="text-muted">Tidak ada</span>';
                })
                ->addColumn('created_by', fn($post) => $post->createdBy?->name ?? '-')
                ->addColumn('updated_by', fn($post) => $post->updatedBy?->name ?? '-')
                ->addColumn('published_by', function ($post) {
                    if ($post->published_by === 'system') {
                        return '<span class="label label-info">AI</span>';
                    }
                    return '<span class="label label-default">Editor</span>';
                })
                ->editColumn('published_at', fn($post) => $post->published_at
                    ? $post->published_at->translatedFormat('d M Y H:i')
                    : '<span class="text-muted">Belum</span>')
                ->editColumn('created_at', fn($post) => $post->created_at->translatedFormat('d M Y H:i'))
                ->editColumn('updated_at', fn($post) => $post->updated_at->translatedFormat('d M Y H:i'))
                ->addColumn('action', function ($post) {
                    $edit = '<a href="'.route('posts.edit', $post->id).'" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i></a>';

                    // Unpublish button for active/published posts
                    $unpublish = '';
                    if ($post->status === 'active' && $post->published_at && $post->published_at <= now()) {
                        $unpublish = '<form action="'.route('posts.unpublish', $post->id).'" method="POST" style="display:inline" onsubmit="return confirm(\'Unpublish post ini? Post akan jadi draft.\')">
                            '.csrf_field().'
                            <button type="submit" class="btn btn-warning btn-xs"><i class="fa fa-ban"></i></button>
                        </form>';
                    }

                    // Regenerate button for AI-generated posts
                    $regenerate = '';
                    if ($post->published_by === 'system' || $post->source) {
                        $regenerate = '<form action="'.route('posts.regenerate', $post->id).'" method="POST" style="display:inline" onsubmit="return confirm(\'Regenerate post ini dari source URL?\')">
                            '.csrf_field().'
                            <button type="submit" class="btn btn-info btn-xs"><i class="fa fa-refresh"></i></button>
                        </form>';
                    }

                    $delete = '<form action="'.route('posts.destroy', $post->id).'" method="POST" style="display:inline" onsubmit="return confirm(\'Yakin hapus?\')">
                                '.csrf_field().method_field('DELETE').'
                                <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                        </form>';

                    return '<div class="text-center">'.$edit.' '.$unpublish.' '.$regenerate.' '.$delete.'</div>';
                })
                ->rawColumns(['link', 'image', 'status', 'tags', 'published_at', 'published_by', 'action'])
                ->make(true);
        }

        return view('pages.admin.posts.index')->with('page', 'Postingan');
    }

    public function create()
    {
        $webIdentity = WebIdentity::first();
        $isMaster = $webIdentity ? $webIdentity->is_master : false;
        
        $domain = $isMaster ? ShareDomain::where('status', 'active')->get() : collect();
        
        $data = [
            'domains' => $domain,
            'categories' => PostCategory::orderBy('id', 'desc')->get(),
            'tags' => PostTags::orderBy('id', 'desc')->get(),
            'isMaster' => $isMaster,
        ];
        return view('pages.admin.posts.create', $data)->with('page', 'Postingan');
    }

    public function store(Request $request)
    {
        Log::info("Memulai proses simpan post: " . $request->title);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'category_id' => 'required|exists:post_categories,id',
            'tags' => 'nullable|array',
            'status' => 'required|in:active,inactive',
            'domains' => 'nullable|array',
            'domains.*' => 'string',
            'featured_image' => 'nullable|image|max:4096',
            'published_at' => 'required|date',
            'domain_published_at' => 'nullable|array',
            'image.*' => 'nullable|image|max:4096',
        ]);

        try {
            $mainImagePath = null;
            if ($request->hasFile('featured_image')) {
                $mainImagePath = FileHelper::saveFile($request->file('featured_image'), 'posts', Str::slug($request->title) . '-' . time());
            }

            $post = Posts::create([
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'content' => $request->content,
                'image' => $mainImagePath,
                'category_id' => $request->category_id,
                'tags' => $request->tags ? json_encode($request->tags) : null,
                'status' => $request->status,
                'published_at' => $request->published_at,
                'created_by' => Auth::check() ? Auth::user()->id : 1,
                'counter' => 0,
            ]);

            Log::info("Post berhasil disimpan ke database dengan ID: {$post->id}");

            $domainConfig = ShareDomain::where('status', 'active')
                ->get()
                ->keyBy('domain_name');

            if ($request->has('domains') && is_array($request->domains)) {
                $categoryName = $request->category_id ? PostCategory::find($request->category_id)->name : null;
                $distributeCount = 0;

                foreach ($request->domains as $domainName) {
                    if (!isset($domainConfig[$domainName])) {
                        continue;
                    }

                    $config = $domainConfig[$domainName];
                    $webhookUrl = $config->webhook_url;
                    $apiKey = $config->api_key;
                    $domainKey = str_replace('.', '_', $domainName);
                    $finalImageUrl = $mainImagePath ? asset($mainImagePath) : null;

                    if ($request->hasFile("image.{$domainKey}")) {
                        $customImage = FileHelper::saveFile($request->file("image.{$domainKey}"), 'posts/domains', Str::slug($request->title) . '-' . $domainKey);
                        $finalImageUrl = asset($customImage);
                    }

                    $domainPublishedAt = $request->input("domain_published_at.{$domainKey}") ?? $request->published_at;

                    $metaData = [
                        'session_id' => 'sess-' . $post->id . '-' . Str::random(6),
                        'original_title' => $request->title,
                        'original_content' => $request->content,
                        'image' => $finalImageUrl,
                        'tags' => $request->tags ?? [],
                        'category' => $categoryName,
                        'published_at' => $domainPublishedAt
                    ];

                    DistributePostJob::dispatch($webhookUrl, $domainName, $apiKey, $metaData);
                    $distributeCount++;
                }

                Log::info("Berhasil mengirim {$distributeCount} post ke antrean distribusi domain.");
            }

            return response()->json([
                'success' => true,
                'message' => 'Post saved and processing started.',
            ]);

        } catch (\Exception $e) {
            Log::error("Kegagalan sistem pada store post: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $webIdentity = WebIdentity::first();
        $isMaster = $webIdentity ? $webIdentity->is_master : false;
        
        $data = [
            'categories' => PostCategory::orderBy('id', 'desc')->get(),
            'tags' => PostTags::orderBy('id', 'desc')->get(),
            'post' => Posts::findOrFail($id),
            'isMaster' => $isMaster,
        ];
        return view('pages.admin.posts.edit', $data)->with('page', 'Postingan');
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|max:4096',
            'content' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'category_id' => 'nullable|exists:post_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:post_tags,id',
            'published_at' => 'nullable|date',
        ]);

        try {
            $post = Posts::findOrFail($id);
            $validatedData['slug'] = Str::slug($request->title);

            if ($request->hasFile('image')) {
                $validatedData['image'] = FileHelper::saveFile($request->file('image'), 'posts', 'image');
            }

            if ($request->has('tags')) {
                $validatedData['tags'] = json_encode($request->tags);
            } else {
                $validatedData['tags'] = json_encode([]);
            }

            $validatedData['updated_by'] = Auth::check() ? Auth::user()->id : 1;

            $post->update($validatedData);

            return redirect()->route('posts.index')->with('success', 'Postingan berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Failed to update post: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui postingan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $posts = Posts::findOrFail($id);
        $posts->delete();

        return redirect()->route('posts.index')->with('success', 'Postingan deleted successfully');
    }

    /**
     * Unpublish a post (set status to draft/inactive)
     */
    public function unpublish(Request $request, $id)
    {
        try {
            $post = Posts::findOrFail($id);

            $reason = $request->input('reason', 'Editor unpublished');

            $post->update([
                'status'            => 'inactive',
                'unpublished_at'    => now(),
                'unpublished_reason' => $reason,
            ]);

            // Record AI learning if this was an AI-generated post
            $ref = \App\Models\RefArticle::where('generated_post_id', $post->id)->first();
            if ($ref && $ref->source_keyword) {
                \App\Jobs\UpdateEditorPreferenceJob::dispatch('unpublish', [
                    'keyword' => $ref->source_keyword,
                ]);
            }

            Log::info('Post unpublished', ['post_id' => $post->id, 'reason' => $reason]);

            return redirect()
                ->back()
                ->with('success', 'Post berhasil di-unpublish.');
        } catch (\Exception $e) {
            Log::error('Unpublish failed', ['post_id' => $id, 'error' => $e->getMessage()]);
            return redirect()
                ->back()
                ->with('error', 'Gagal meng-unpublish post: ' . $e->getMessage());
        }
    }

    /**
     * Regenerate a post from its source URL (re-scrape + re-paraphrase)
     */
    public function regenerate(Request $request, $id)
    {
        try {
            $post = Posts::findOrFail($id);
            $ref  = \App\Models\RefArticle::where('generated_post_id', $post->id)->first();

            if (!$ref) {
                return redirect()
                    ->back()
                    ->with('error', 'Referensi article tidak ditemukan untuk post ini.');
            }

            // Check: does RefArticle still have content?
            if (empty($ref->content)) {
                // No content - need to re-scrape from source URL
                if (empty($ref->source_url)) {
                    return redirect()
                        ->back()
                        ->with('error', 'Source URL tidak tersedia. Tidak bisa regenerate.');
                }

                // Re-scrape from source URL
                $scraper = app(\App\Services\SearchEngineLandScraperService::class);
                $article = $scraper->fetchArticleDetail($ref->source_url);

                if (!$article || !$scraper->isValidArticle($article)) {
                    return redirect()
                        ->back()
                        ->with('error', 'Source URL tidak bisa di-scrape ulang. Silakan edit manual.');
                }

                // Update ref_article with fresh content
                $ref->update([
                    'content'           => $article['content'],
                    'image_url'         => $article['image_url'],
                    'title'             => $article['title'],
                    'author'            => $article['author'],
                    'published_at'       => $article['published_at'],
                    'tags'               => $article['tags'] ?? [],
                    'ai_status'          => 'pending',
                ]);
            }

            // Dispatch regenerate job
            \App\Jobs\ScrapeParaphraseJob::dispatch(
                $ref->source_url,
                $ref->source_domain,
                $ref->source_keyword ?? '',
                \Illuminate\Support\Str::uuid()->toString(),
                50.0,
                0,
                false
            );

            Log::info('Regenerate dispatched', ['post_id' => $post->id, 'ref_id' => $ref->id]);

            return redirect()
                ->back()
                ->with('success', 'Regenerate job dispatched. Post akan di-update setelah paraphrase selesai.');

        } catch (\Exception $e) {
            Log::error('Regenerate failed', ['post_id' => $id, 'error' => $e->getMessage()]);
            return redirect()
                ->back()
                ->with('error', 'Gagal regenerate: ' . $e->getMessage());
        }
    }
}
