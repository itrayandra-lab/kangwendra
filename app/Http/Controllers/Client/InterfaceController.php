<?php

namespace App\Http\Controllers\Client;

use App\Models\Posts;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Information;
use App\Models\Page;
use App\Models\PostTags;
use App\Models\User;
use App\Models\Video;
use Illuminate\Support\Facades\Log;

class InterfaceController extends Controller
{
    protected $datas;

    public function __construct(DataController $datas)
    {
        $this->datas = $datas;
    }

    #landing start
    public function beranda()
    {
        $data = [
            'slide'         => $this->datas->latestPublished(3),
            'latestNews'   => $this->datas->latestNews(7),
            'hikmahPosts'  => $this->datas->postsCategory(2, 'Hikmah'),
            'amazingPosts' => $this->datas->postsCategory(2, 'AmAzing'),
            'brandingPosts'=> $this->datas->postsCategory(2, 'Branding'),
            'marketingPosts'=> $this->datas->postsCategory(2, 'Marketing'),
            'featuredPosts'=> $this->datas->latestPublished(3),
            'randomNews'   => $this->datas->latestNews(6),
            'mostPopular'  => $this->datas->mostPopular(5),
            'banner_1'     => $this->datas->information('banner', 5),
            'banner_2'     => $this->datas->information('banner', 1, true),
            'information'  => $this->datas->information('text', 4),
            'recommended'  => $this->datas->recommended(6),
            'tags'         => $this->datas->tags(12),
            'categories'    => $this->datas->category(4),
            'ads'         => $this->datas->ads(1, ['image','gif']),
        ];

        return view('pages.client.beranda', $data);
    }

    #search
    public function search(Request $request) {
        $searchQuery = $request->input('qr'); 
        $posts = Posts::where('status', 'active')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', Carbon::now())
                ->orderBy('counter', 'desc')
                ->latest('published_at')
                ->with(['category', 'createdBy']); 
    
        if ($searchQuery) {
            $posts->where(function ($q) use ($searchQuery) {
                $q->where('title', 'like', "%{$searchQuery}%");
            });
        }
    
        $posts = $posts->paginate(20);
    
        $data = [
            'posts' => $posts,
            'searchQuery' => $searchQuery,
            'mostPopular' => $this->datas->mostPopular(6),
        ];
    
        return view('pages.client.search', $data);
    }

    #detail by category
    public function category($slug)
    {
        $category = PostCategory::where('slug', $slug)->first();

        if (!$category) {
            abort(404);
        }

        $query = Posts::where('category_id', $category->id)
                    ->whereNotNull('published_at')
                    ->where('status', 'active')
                    ->where('published_at', '<=', Carbon::now())
                    ->latest('published_at');

        $searchQuery = request()->input('qr');
        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('title', 'like', "%{$searchQuery}%");
            });
        }

        $posts = $query->paginate(10);

        $data = [
            'banner_1'     => $this->datas->information('banner', 1),
            'mostPopular'  => $this->datas->recommended(6),
            'category'     => $category,
            'posts'        => $posts,
            'searchQuery'  => $searchQuery,
        ];

        return view('pages.client.category', $data);
    }

    #detail by author
    public function author($slug)
    {
        $author = User::where('slug', $slug)->first();

        if (!$author) {
            abort(404);
        }

        $query = Posts::where('created_by', $author->id)
                    ->whereNotNull('published_at')
                    ->where('status', 'active')
                    ->where('published_at', '<=', Carbon::now())
                    ->latest('published_at');

        $searchQuery = request()->input('qr');
        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('title', 'like', "%{$searchQuery}%");
            });
        }

        $posts = $query->paginate(10);

        $data = [
            'banner_1'     => $this->datas->information('banner', 1),
            'mostPopular'  => $this->datas->recommended(6),
            'author'     => $author,
            'posts'        => $posts,
            'searchQuery'  => $searchQuery,
        ];

        return view('pages.client.author', $data);
    }

    #posts
    public function posts(Request $request) {
        $type = $request->query('type', 'terbaru');
        $searchQuery = $request->input('qr'); 
        $posts = Posts::where('status', 'active')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', Carbon::now())
                ->with(['category', 'createdBy']); 
    
        switch ($type) {
            case 'populer':
                $posts = $posts->orderBy('counter', 'desc');
                $type = 'Populer';
                break;
            case 'terbaru':
            default:
                $posts = $posts->latest('published_at');
                $type = 'Terbaru';
                break;
        }
    
        if ($searchQuery) {
            $posts->where(function ($q) use ($searchQuery) {
                $q->where('title', 'like', "%{$searchQuery}%");
            });
        }
    
        $posts = $posts->paginate(10);
    
        $data = [
            'type' => $type,
            'posts' => $posts,
            'searchQuery' => $searchQuery,
            'mostPopular' => $this->datas->mostPopular(6),
            'banner_1' => $this->datas->information('banner', 1, true),
        ];
    
        return view('pages.client.posts', $data);
    }

    #detail by posts
    public function post_detail($category, $post) {
        $category = PostCategory::where('slug', $category)->first();
        if (!$category) {
            abort(404);
        }

        $post = Posts::where('slug', $post)->where('status', 'active')->first();
        if (!$post) {
            abort(404);
        }

        $post->update([
            'counter' => $post->counter + 1
        ]);

        $modifiedContent = $this->applyTailwindClasses($post->content);
        
        $data = [
            'mostPopular'  => $this->datas->mostPopular(6),
            'recommended'  => $this->datas->recommended(6),
            'banner_1'     => $this->datas->information('banner', 1),
            'relate'      => $this->datas->relate(6, $post),
            'ads'         => $this->datas->ads(1, ['image','youtube']),
            'post'         => $post,
            'content'         => $modifiedContent,
        ];

        return view('pages.client.post-detail', $data);
    }

    #detail by tag
    public function tag($slug)
    {
        $tag = PostTags::where('slug', $slug)->first();
        if (!$tag) {
            abort(404);
        }

        $query = Posts::with(['category', 'createdBy'])
            ->where('status', 'active')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now())
            ->where(function($q) use ($tag) {
                $q->whereJsonContains('tags', $tag->name)
                  ->orWhereJsonContains('tags', (string) $tag->name);
            })
            ->latest('published_at');

        $searchQuery = request()->input('qr');
        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('title', 'like', "%{$searchQuery}%");
            });
        }

        $posts = $query->paginate(10);

        // Debug: Log jumlah posts yang ditemukan
        Log::info("Tag: {$tag->name}, Posts found: " . $posts->count());

        $data = [
            'banner_1'     => $this->datas->information('banner', 1),
            'mostPopular'  => $this->datas->recommended(6),
            'tag'          => $tag, 
            'posts'        => $posts,
            'searchQuery'  => $searchQuery,
        ];

        return view('pages.client.tag', $data); 
    }

    #videos
    public function videos() {

        $videos = Video::latest('created_at');

        $searchQuery = request()->input('qr');
        if ($searchQuery) {
            $videos->where(function ($q) use ($searchQuery) {
                $q->where('title', 'like', "%{$searchQuery}%");
            });
        }

        $videos = $videos->paginate(10);

        $data = [
            'banner_1'     => $this->datas->information('banner', 1, true),
            'videos'    => $videos,
            'searchQuery'    => $searchQuery,
        ];
        return view('pages.client.videos', $data); 
    }
    
    #detail video
    public function video_detail($slug) {
        $video = Video::where('slug', $slug)->first();
        if(!$video) {
            abort(404);
        }
        $modifiedContent = $this->applyTailwindClasses($video->description);
        $data = [
            'video'     => $video,
            'content'     => $modifiedContent,
            'videos'    => $this->datas->videos(5)
        ];
        return view('pages.client.video-detail', $data); 
    }

    #banners
    public function banners() {
        $banners = Information::where('type', 'banner')->latest('created_at');
        $searchQuery = request()->input('qr');
        if ($searchQuery) {
            $banners->where(function ($q) use ($searchQuery) {
                $q->where('title', 'like', "%{$searchQuery}%");
            });
        }

        $banners = $banners->paginate(10);

        $data = [
            'banners'    => $banners,
            'searchQuery'    => $searchQuery,
        ];
        return view('pages.client.banners', $data); 
    }

    #banner_detail
    public function banner_detail($slug) {
        $banner = Information::where('type', 'banner')->where('slug', $slug)->latest('created_at')->first();
        if(!$banner) {
            abort(404);
        }
        $modifiedContent = $this->applyTailwindClasses($banner->description);
        $data = [
            'banner'    => $banner,
            'content'    => $modifiedContent,
            'banners'    => $this->datas->information('banner', 6),
        ];
        return view('pages.client.banner-detail', $data); 
    }

    #albums
    public function albums() {
        $albums = Album::latest('created_at')->with('photos');
        $searchQuery = request()->input('qr');
        if ($searchQuery) {
            $albums->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%");
            });
        }

        $albums = $albums->paginate(5);

        $data = [
            'albums'    => $albums,
            'searchQuery'    => $searchQuery,
        ];
        return view('pages.client.albums', $data); 
    }

    #info
    public function info() {
        $info = Information::where('type', 'text')->latest('created_at');
        $searchQuery = request()->input('qr');
        if ($searchQuery) {
            $info->where(function ($q) use ($searchQuery) {
                $q->where('title', 'like', "%{$searchQuery}%");
            });
        }

        $info = $info->paginate(10);

        $data = [
            'info'    => $info,
            'searchQuery'    => $searchQuery,
        ];
        return view('pages.client.info', $data); 
    }

    #info detail
    public function info_detail($slug) {
        $info = Information::where('type', 'text')->where('slug', $slug)->latest('created_at')->first();
        if(!$info) {
            abort(404);
        }
        $data = [
            'info'    => $info,
            'information'    => $this->datas->information('text', 6),
        ];
        return view('pages.client.info-detail', $data); 
    }

    #page detail
    public function page_detail($slug) {
        $page = Page::with('createdBy')->where('slug', $slug)->where('status', 'active')->latest('created_at')->first();
        if (!$page) {
            abort(404);
        }
        $page->update([
            'counter' => $page->counter + 1
        ]);
        $modifiedContent = $this->applyTailwindClasses($page->content);
        $data = [
            'page' => $page,
            'content' => $modifiedContent, 
        ];
        return view('pages.client.page-detail', $data);
    }
    
    /**
     * templetting
     */

    private function applyTailwindClasses($htmlContent) {
        if (empty($htmlContent)) {
            return $htmlContent;
        }

        $doc = new \DOMDocument();
        
        libxml_use_internal_errors(true);

        $htmlWrapper = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $htmlContent . '</body></html>';
        
        $doc->loadHTML($htmlWrapper, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $tags = [
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'p', 'span', 'strong', 'b', 'em', 'i', 'u', 'small',
            'ul', 'ol', 'li',
            'img', 'figure', 'figcaption',
            'table', 'thead', 'tbody', 'tr', 'th', 'td',
            'a',
            'blockquote', 'pre', 'code',
            'hr',
            'div', 'section', 'article'
        ];
        
        foreach ($tags as $tag) {
            $elements = $doc->getElementsByTagName($tag);
            
            foreach (iterator_to_array($elements) as $element) {
                $existingClass = $element->getAttribute('class');
                $tailwindClasses = $this->getTailwindClasses($tag);
    
                if ($existingClass) {
                    if (strpos($existingClass, $tailwindClasses) === false) {
                        $newClass = $existingClass . ' ' . $tailwindClasses;
                    } else {
                        $newClass = $existingClass;
                    }
                } else {
                    $newClass = $tailwindClasses;
                }
                
                if ($newClass) { 
                    $element->setAttribute('class', $newClass);
                }
            }
        }
    
        $body = $doc->getElementsByTagName('body')->item(0);

        if ($body === null) {
            libxml_clear_errors();
            return $htmlContent;
        }

        $modifiedHtml = '';
        foreach ($body->childNodes as $node) {
            $modifiedHtml .= $doc->saveHTML($node);
        }

        libxml_clear_errors();
    
        return $modifiedHtml;
    }
    
    
    /**
     * Templating
     */
    private function getTailwindClasses($tag) {
        $classes = [
            // Heading
            'h1' => 'text-3xl font-bold mb-1',
            'h2' => 'text-2xl font-semibold mb-1',
            'h3' => 'text-xl font-medium mb-1',
            'h4' => 'text-lg font-medium mb-1',
            'h5' => 'text-base font-medium mb-1',
            'h6' => 'text-sm font-medium mb-1',
    
            // Text
            'p' => 'text-base',
            'span' => 'text-base',
            'strong' => 'font-bold',
            'b' => 'font-bold',
            'em' => 'italic',
            'i' => 'italic',
            'u' => 'underline',
            'small' => 'text-sm',
    
            // List
            'ul' => 'list-disc pl-5 mb-1',
            'ol' => 'list-decimal pl-5 mb-1',
            'li' => 'mb-1',
    
            // Media
            'img' => 'max-w-full h-auto object-cover mb-1',
            'figure' => 'mb-1',
            'figcaption' => 'text-sm text-gray-500 mt-1',
    
            // Table
            'table' => 'min-w-full w-full border-collapse mb-4 text-sm md:text-base shadow rounded-sm',
            'thead' => 'bg-blue-100 text-white',
            'tr' => 'border-b border-gray-200 hover:bg-gray-50',
            'th' => 'text-left p-2 md:p-3 font-semibold text-gray-800 uppercase tracking-wider text-xs md:text-sm',
            'td' => 'p-2 md:p-3 text-sm font-bold', 
    
            // Link
            'a' => 'text-blue-600 hover:underline',
    
            // Blockquote and Code
            'blockquote' => 'border-l-4 border-gray-300 pl-4 italic text-gray-700 mb-1',
            'pre' => 'bg-gray-100 p-4 rounded-md text-sm font-mono mb-1',
            'code' => 'bg-gray-100 px-1 rounded text-sm font-mono',
    
            // Divider
            'hr' => 'border-t border-gray-300 my-4',
    
            // Container
            'div' => 'mb-1',
            'section' => 'mb-6',
        ];
    
        return $classes[$tag] ?? '';
    }

}