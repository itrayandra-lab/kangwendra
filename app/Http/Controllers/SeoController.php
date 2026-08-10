<?php

namespace App\Http\Controllers;

use App\Models\Posts;
use App\Models\PostCategory;
use App\Models\PostTags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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
            "Sitemap: {$baseUrl}/sitemap-news.xml",
            "",
            "# AI Search Engine Feed",
            "LLM-Info: {$baseUrl}/llms.txt",
            "",
            "# OpenSearch",
            "Sitemap: {$baseUrl}/opensearch.xml",
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
            "User-agent: DeepSeekBot",
            "Allow: /",
            "User-agent: Grok",
            "Allow: /",
            "User-agent: YouBot",
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
        $content .= '<sitemap><loc>' . e($baseUrl) . '/sitemap-news.xml</loc><lastmod>' . e($now) . '</lastmod></sitemap>' . "\n";
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
            $posts = Posts::where('posts.status', 'active')
                ->whereNotNull('posts.published_at')
                ->join('post_categories', 'posts.category_id', '=', 'post_categories.id')
                ->orderBy('posts.published_at', 'desc')
                ->limit(5000)
                ->get(['posts.slug', 'posts.title', 'posts.published_at', 'posts.updated_at', 'posts.image', 'post_categories.slug as category_slug']);

            $entries = '';
            foreach ($posts as $post) {
                $url = e("{$baseUrl}/{$post->category_slug}/{$post->slug}");
                $lastmod = $post->published_at ? $post->published_at->toISOString() : $post->updated_at->toISOString();
                $imageTag = '';
                if (!empty($post->image)) {
                    $imageUrl = e(getFile($post->image));
                    $imageTag = '<image:image><image:loc>' . $imageUrl . '</image:loc></image:image>';
                }
                $entries .= '<url><loc>' . $url . '</loc><lastmod>' . $lastmod . '</lastmod><changefreq>weekly</changefreq><priority>0.8</priority>' . $imageTag . '</url>' . "\n";
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
     * llms.txt — For AI Search Engines (Perplexity, Claude, ChatGPT, Gemini).
     * This file tells AI agents what content is available for grounding their answers.
     * Format: https://llmstxt.vertices.so/docs/site-guide
     */
    public function llms(Request $request)
    {
        $content = Cache::remember('llms_txt', 1800, function () {
            $baseUrl = rtrim(config('app.url'), '/');
            $siteName = config('app.name') ?? 'Kangwendra';
            $lines = [];

            // Header
            $lines[] = "# {$siteName} - Portal Berita AI Indonesia";
            $lines[] = "# Last updated: " . now()->format('Y-m-d H:i:s') . " UTC";
            $lines[] = "";
            $lines[] = "# AI News Portal focusing on Artificial Intelligence, SEO, and Technology";
            $lines[] = "# Language: Indonesian (id-ID)";
            $lines[] = "";

            // Main URL
            $lines[] = "## Site";
            $lines[] = "URL: {$baseUrl}";
            $lines[] = "";

            // About section
            $lines[] = "## About";
            $lines[] = "{$siteName} adalah portal berita AI Indonesia yang menyediakan";
            $lines[] = "artikel terbaru tentang Artificial Intelligence, SEO, teknologi,";
            $lines[] = "machine learning, dan berita digital terkini.";
            $lines[] = "";

            // Navigation
            $lines[] = "## Navigation";
            $categories = PostCategory::withCount('posts')->orderBy('posts_count', 'desc')->limit(10)->get(['slug', 'name']);
            foreach ($categories as $cat) {
                $lines[] = "- {$cat->name}: {$baseUrl}/{$cat->slug}";
            }
            $lines[] = "";

            // Recent articles (last 50)
            $lines[] = "## Recent Articles";
            $posts = Posts::where('status', 'active')
                ->whereNotNull('published_at')
                ->with('category')
                ->orderBy('published_at', 'desc')
                ->limit(50)
                ->get(['slug', 'title', 'published_at', 'category_id']);

            foreach ($posts as $post) {
                $pubDate = $post->published_at ? $post->published_at->format('Y-m-d') : '';
                $category = $post->category?->name ?? 'AI & Teknologi';
                $categorySlug = $post->category?->slug ?? 'ai-teknologi';
                $title = trim($post->title);
                $url = "{$baseUrl}/{$categorySlug}/{$post->slug}";
                $lines[] = "- [{$category}] {$title} ({$pubDate}): {$url}";
            }
            $lines[] = "";

            // RSS Feed reference
            $lines[] = "## Feeds";
            $lines[] = "RSS Feed: {$baseUrl}/feed.xml";
            $lines[] = "Sitemap: {$baseUrl}/sitemap.xml";
            $lines[] = "";

            // AI Usage Policy
            $lines[] = "## AI Usage Policy";
            $lines[] = "All content on {$siteName} is original and human-edited.";
            $lines[] = "AI-generated content is clearly marked where applicable.";
            $lines[] = "Content may be used by AI systems for factual grounding with attribution.";
            $lines[] = "Attribution preferred: \"According to {$siteName}\" or link back to source.";

            return implode("\n", $lines);
        });

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }

    /**
     * Google News sitemap — specific format for Google News indexing.
     * Cached 30 minutes (Google News crawls frequently).
     */
    public function sitemapNews()
    {
        $xml = Cache::remember('sitemap_news_xml', 1800, function () {
            $baseUrl = rtrim(config('app.url'), '/');
            $siteName = config('app.name') ?? 'Kangwendra';

            // News sitemap: last 2 days only (Google News requirement)
            $twoDaysAgo = now()->subDays(2);
            $posts = Posts::where('posts.status', 'active')
                ->whereNotNull('posts.published_at')
                ->where('posts.published_at', '>=', $twoDaysAgo)
                ->join('post_categories', 'posts.category_id', '=', 'post_categories.id')
                ->leftJoin('users', 'posts.created_by', '=', 'users.id')
                ->orderBy('posts.published_at', 'desc')
                ->limit(1000)
                ->get(['posts.slug', 'posts.title', 'posts.published_at', 'posts.updated_at', 'post_categories.slug as category_slug', 'users.name as author_name']);

            $entries = '';
            foreach ($posts as $post) {
                $url = e("{$baseUrl}/{$post->category_slug}/{$post->slug}");
                $title = e(mb_substr($post->title, 0, 500));
                $pubDate = $post->published_at ? gmdate('Y-m-d\TH:i:s+00:00', strtotime($post->published_at)) : '';
                $language = 'id';
                $category = e($post->category_slug ?? 'ai-teknologi');
                $author = e($post->author_name ?? $siteName);

                $entries .= '<url><loc>' . $url . '</loc>';
                $entries .= '<news:news><news:publication><news:name>' . $siteName . '</news:name><news:language>' . $language . '</news:language></news:publication>';
                $entries .= '<news:publication_date>' . $pubDate . '</news:publication_date>';
                $entries .= '<news:title>' . $title . '</news:title>';
                $entries .= '<news:keywords>' . $category . ', AI, teknologi, berita</news:keywords>';
                $entries .= '<news:stock_tickers></news:stock_tickers>';
                $entries .= '</news:news></url>' . "\n";
            }

            return '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
' . $entries . '</urlset>';
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }

    /**
     * RSS / Atom Feed — standard feed for readers and AI crawlers.
     */
    public function feed()
    {
        $content = Cache::remember('rss_feed_xml', 600, function () {
            $baseUrl = rtrim(config('app.url'), '/');
            $siteName = config('app.name') ?? 'Kangwendra';
            $description = config('app.description') ?? 'Portal Berita AI Indonesia - Berita terkini tentang Artificial Intelligence, SEO, dan Teknologi';

            $posts = Posts::where('status', 'active')
                ->whereNotNull('published_at')
                ->with('category', 'createdBy')
                ->orderBy('published_at', 'desc')
                ->limit(50)
                ->get(['slug', 'title', 'content', 'published_at', 'image', 'category_id', 'created_by']);

            $items = '';
            foreach ($posts as $post) {
                $categorySlug = $post->category?->slug ?? 'ai-teknologi';
                $url = "{$baseUrl}/{$categorySlug}/{$post->slug}";
                $title = htmlspecialchars(trim($post->title), ENT_XML1, 'UTF-8');
                $description = htmlspecialchars(Str::limit(strip_tags($post->content ?? ''), 300), ENT_XML1, 'UTF-8');
                $pubDate = $post->published_at ? gmdate('D, d M Y H:i:s +0000', strtotime($post->published_at)) : gmdate('D, d M Y H:i:s +0000');
                $author = htmlspecialchars($post->createdBy?->name ?? $siteName, ENT_XML1, 'UTF-8');
                $category = htmlspecialchars($post->category?->name ?? 'AI & Teknologi', ENT_XML1, 'UTF-8');
                $image = $post->image ? getFile($post->image) : '';

                $items .= '<item>';
                $items .= '<title>' . $title . '</title>';
                $items .= '<link>' . $url . '</link>';
                $items .= '<guid isPermaLink="true">' . $url . '</guid>';
                $items .= '<description><![CDATA[' . $description . ']]></description>';
                $items .= '<pubDate>' . $pubDate . '</pubDate>';
                $items .= '<author>' . $author . '</author>';
                $items .= '<category>' . $category . '</category>';
                if ($image) {
                    $items .= '<enclosure url="' . e($image) . '" type="image/jpeg" />';
                }
                $items .= '</item>' . "\n";
            }

            $lastBuild = gmdate('D, d M Y H:i:s +0000');
            $feedUrl = "{$baseUrl}/feed.xml";

            return '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
  xmlns:atom="http://www.w3.org/2005/Atom"
  xmlns:content="http://purl.org/rss/1.0/modules/content/"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:media="http://search.yahoo.com/mrss/">
<channel>
  <title>' . htmlspecialchars($siteName, ENT_XML1, 'UTF-8') . '</title>
  <link>' . $baseUrl . '</link>
  <description>' . htmlspecialchars($description, ENT_XML1, 'UTF-8') . '</description>
  <language>id-id</language>
  <lastBuildDate>' . $lastBuild . '</lastBuildDate>
  <atom:link href="' . $feedUrl . '" rel="self" type="application/rss+xml" />
  <image>
    <url>' . $baseUrl . '/favicon.ico</url>
    <title>' . htmlspecialchars($siteName, ENT_XML1, 'UTF-8') . '</title>
    <link>' . $baseUrl . '</link>
  </image>
' . $items . '</channel>
</rss>';
        });

        return response($content, 200, [
            'Content-Type' => 'application/rss+xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=600',
        ]);
    }

    /**
     * OpenSearch Description — for Windows Search & browser search bar integration.
     */
    public function opensearch()
    {
        $siteName = e(config('app.name') ?? 'Kangwendra');
        $webIdentity = \App\Models\WebIdentity::first();
        $description = e($webIdentity?->meta_description ?? 'Portal Berita AI Indonesia - Berita terkini tentang Artificial Intelligence, SEO, dan Teknologi');
        $searchUrl = e(config('app.url')) . '/search?q=';

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/"
                       xmlns:moz="http://www.mozilla.org/2006/browser/search/">
  <ShortName>' . $siteName . '</ShortName>
  <Description>' . $description . '</Description>
  <InputEncoding>UTF-8</InputEncoding>
  <OutputEncoding>UTF-8</OutputEncoding>
  <Image width="16" height="16" type="image/x-icon">' . e(config('app.url')) . '/favicon.ico</Image>
  <Url type="text/html" method="get" template="' . $searchUrl . '{searchTerms}"/>
  <moz:SearchForm>' . e(config('app.url')) . '/search</moz:SearchForm>
</OpenSearchDescription>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400',
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
