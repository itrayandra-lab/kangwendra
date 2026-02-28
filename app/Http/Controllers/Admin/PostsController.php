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
                ->select('posts.*')
                ->latest();

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
                    $tagIds = json_decode($post->tags, true);
                    if (empty($tagIds)) return '<span class="text-muted">Tidak ada</span>';

                    $tagNames = PostTags::whereIn('id', $tagIds)->pluck('name')->take(5)->implode(', ');
                    return $tagNames ?: '<span class="text-muted">Tidak ada</span>';
                })
                ->addColumn('created_by', fn($post) => $post->createdBy?->name ?? '-')
                ->addColumn('updated_by', fn($post) => $post->updatedBy?->name ?? '-')
                ->editColumn('published_at', fn($post) => $post->published_at
                    ? $post->published_at->translatedFormat('d M Y H:i')
                    : '<span class="text-muted">Belum</span>') 
                ->editColumn('created_at', fn($post) => $post->created_at->translatedFormat('d M Y H:i'))
                ->editColumn('updated_at', fn($post) => $post->updated_at->translatedFormat('d M Y H:i'))
                ->addColumn('action', function ($post) {
                    $edit = '<a href="'.route('posts.edit', $post->id).'" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i></a>';

                    $delete = '<form action="'.route('posts.destroy', $post->id).'" method="POST" style="display:inline" onsubmit="return confirm(\'Yakin hapus?\')">
                                '.csrf_field().method_field('DELETE').'
                                <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                        </form>';

                    return '<div class="text-center">'.$edit.' '.$delete.'</div>';
                })
                ->rawColumns(['link', 'image', 'status', 'tags', 'published_at', 'action']) 
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
}
