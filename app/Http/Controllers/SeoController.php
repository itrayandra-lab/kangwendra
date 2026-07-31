<?php

namespace App\Http\Controllers;

use App\Models\Posts;
use App\Models\PostCategory;
use App\Models\PostTags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    /**
     * Dynamic robots.txt with sitemap references and AI crawler rules.
     */
    public function robots(Request $request)
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $year = date('Y');

        $lines = [
            "# robots.txt - Kangwendra Portal Berita AI",
            "# Generated: {$year}",
            "",
            "User-agent: *",
            "Allow: /",
            "",
            "# XML Sitemaps",
            "Sitemap: {$baseUrl}/sitemap.xml",
            "Sitemap: {$baseUrl}/sitemap/posts.xml",
            "Sitemap: {$baseUrl}/sitemap/categories.xml",
            "Sitemap: {$baseUrl}/sitemap/tags.xml",
            "",
            "# Disallow admin & internal paths",
            "Disallow: /portal/",
            "Disallow: /admin/",
            "Disallow: /api/",
            "Disallow: /_debugbar/",
            "Disallow: /storage/debugbar/",
            "",
            "# Crawl-delay",
            "Crawl-delay: 1",
            "",
            "# AI / LLM Crawlers (GEO)",
            "",
            "User-agent: GPTBot",
            "Allow: /",
            "User-agent: ChatGPT-User",
            "Allow: /",
            "User-agent: CCBot",
            "Allow: /",
            "User-agent: OAI-SearchBot",
            "Allow: /",
            "User-agent: PerplexityBot",
            "Allow: /",
            "User-agent: Perplexica-Bot",
            "Allow: /",
            "User-agent: ClaudeBot",
            "Allow: /",
            "User-agent: anthropic-ai",
            "Allow: /",
            "User-agent: Meta-ExternalAgent",
            "Allow: /",
            "User-agent: Diffbot",
            "Allow: /",
            "User-agent: Bytespider",
            "Allow: /",
            "User-agent: Google-Extended",
            "Allow: /",
            "User-agent: Amazonbot",
            "Allow: /",
            "User-agent: Applebot-Extended",
            "Allow: /",
            "",
            "# Social Media",
            "",
            "User-agent: FacebookExternalHit",
            "Allow: /",
            "User-agent: Twitterbot",
            "Allow: /",
            "User-agent: linkedinbot",
            "Allow: /",
            "User-agent: WhatsApp",
            "Allow: /",
            "User-agent: TelegramBot",
            "Allow: /",
            "",
            "# Search Engines",
            "",
            "User-agent: Googlebot",
            "Allow: /",
            "User-agent: Googlebot-Image",
            "Allow: /",
            "User-agent: Googlebot-News",
            "Allow: /",
            "User-agent: Googlebot-Video",
            "Allow: /",
            "User-agent: Bingbot",
            "Allow: /",
            "User-agent: Slurp",
            "Allow: /",
            "User-agent: DuckDuckBot",
            "Allow: /",
            "User-agent: YandexBot",
            "Allow: /",
            "User-agent: Naverbot",
            "Allow: /",
        ];

        $content = implode("\n", $lines);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Sitemap index (points to sub-sitemaps).
     */
    public function sitemapIndex()
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $now = now()->toISOString();

        $content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $content .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $content .= '<sitemap><loc>' . e($baseUrl) . '/sitemap/posts.xml</loc><lastmod>' . e($now) . '</lastmod></sitemap>' . "\n";
        $content .= '<sitemap><loc>' . e($baseUrl) . '/sitemap/categories.xml</loc><lastmod>' . e($now) . '</lastmod></sitemap>' . "\n";
        $content .= '<sitemap><loc>' . e($baseUrl) . '/sitemap/tags.xml</loc><lastmod>' . e($now) . '</lastmod></sitemap>' . "\n";
        $content .= '</sitemapindex>';

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Sitemap: Posts (articles).
     * Cached 1 hour.
     */
    public function sitemapPosts()
    {
        $xml = Cache::remember('sitemap_posts_xml', 3600, function () {
            $baseUrl = rtrim(config('app.url'), '/');
            $posts = Posts::where('status', 'active')
                ->whereNotNull('published_at')
                ->orderBy('published_at', 'desc')
                ->limit(5000)
                ->get(['slug', 'title', 'published_at', 'updated_at']);

            $entries = '';
            foreach ($posts as $post) {
                $url = e("{$baseUrl}/{$post->slug}");
                $lastmod = $post->published_at ? $post->published_at->toISOString() : $post->updated_at->toISOString();
                $entries .= '<url><loc>' . $url . '</loc><lastmod>' . $lastmod . '</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>' . "\n";
            }

            return '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
' . $entries . '</urlset>';
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Sitemap: Categories.
     */
    public function sitemapCategories()
    {
        $xml = Cache::remember('sitemap_categories_xml', 3600, function () {
            $baseUrl = rtrim(config('app.url'), '/');
            $cats = PostCategory::get(['slug', 'name']);

            $entries = '';
            foreach ($cats as $cat) {
                $url = e("{$baseUrl}/{$cat->slug}");
                $lastmod = now()->toISOString();
                $entries .= '<url><loc>' . $url . '</loc><lastmod>' . $lastmod . '</lastmod><changefreq>daily</changefreq><priority>0.6</priority></url>' . "\n";
            }

            return '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
' . $entries . '</urlset>';
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Sitemap: Tags.
     */
    public function sitemapTags()
    {
        $xml = Cache::remember('sitemap_tags_xml', 3600, function () {
            $baseUrl = rtrim(config('app.url'), '/');
            $tags = PostTags::get(['slug', 'name']);

            $entries = '';
            foreach ($tags as $tag) {
                $url = e("{$baseUrl}/tag/{$tag->slug}");
                $lastmod = now()->toISOString();
                $entries .= '<url><loc>' . $url . '</loc><lastmod>' . $lastmod . '</lastmod><changefreq>weekly</changefreq><priority>0.4</priority></url>' . "\n";
            }

            return '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
' . $entries . '</urlset>';
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
