<!doctype html>
<html class="no-js" lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @php
        $seoTitle = html_entity_decode(trim($__env->yieldContent('seo_title')) ?: ($meta->meta_title ?? 'Kang Wendra'));
        $seoDesc = html_entity_decode(trim($__env->yieldContent('seo_description')) ?: ($meta->meta_description ?? 'Kang Wendra'));
        $seoImage = trim($__env->yieldContent('seo_image')) ?: getFile($meta->og_image ?? '');
    @endphp
    <!-- Basic SEO Meta Tags -->
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDesc }}">
    <meta name="keywords" content="{{ $meta->meta_keywords ?? '' }}">
    <meta name="author" content="{{ $meta->web_name ?? 'Portal Berita' }}">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">
    <meta name="bingbot" content="index, follow">
    <meta name="language" content="Indonesian">
    <meta name="geo.region" content="ID">
    <meta name="geo.country" content="Indonesia">
    <meta name="distribution" content="global">
    <meta name="rating" content="general">
    <meta name="revisit-after" content="1 days">
    
    <!-- Canonical URL (strip page param to prevent duplicate content) -->
    @php
        $canonicalUrl = request()->fullUrlWithoutQuery(['page']);
    @endphp
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @if(request()->has('page') && request()->page > 1 && isset($posts) && $posts->previousPageUrl())
    <link rel="prev" href="{{ $posts->previousPageUrl() }}">
    @endif
    @if(isset($posts) && method_exists($posts, 'hasPages') && $posts->hasPages() && $posts->nextPageUrl())
    <link rel="next" href="{{ $posts->nextPageUrl() }}">
    @endif
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ isset($post) ? 'article' : 'website' }}">
    <meta property="og:site_name" content="{{ $meta->web_name ?? 'Portal Berita' }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDesc }}">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:updated_time" content="{{ now()->toISOString() }}">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@{{ str_replace(['https://twitter.com/', 'https://x.com/', '@'], '', $meta->twitter_link ?? '') }}">
    <meta name="twitter:creator" content="@{{ str_replace(['https://twitter.com/', 'https://x.com/', '@'], '', $meta->twitter_link ?? '') }}">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDesc }}">
    <meta name="twitter:image" content="{{ $seoImage }}">
    <meta name="twitter:image:alt" content="{{ $meta->meta_title ?? 'Portal Berita' }}">
    
    <!-- LinkedIn -->
    <meta property="linkedin:owner" content="{{ $meta->web_name ?? 'Portal Berita' }}">
    
    <!-- WhatsApp -->
    <meta property="whatsapp:title" content="{{ $seoTitle }}">
    <meta property="whatsapp:description" content="{{ $seoDesc }}">
    <meta property="whatsapp:image" content="{{ $seoImage }}">
    
    <!-- Telegram -->
    <meta property="telegram:channel" content="{{ $meta->web_name ?? 'Portal Berita' }}">
    
    @include('layouts.client.partials.schema')

    <!-- Favicon & Icons -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ getFile($meta->favicon ?? '') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ getFile($meta->favicon ?? '') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ getFile($meta->favicon ?? '') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ getFile($meta->logo ?? '') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#ffffff">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ getFile($meta->favicon ?? '') }}">

    <!-- RSS / Atom Feed Auto-Discovery -->
    <link rel="alternate" type="application/rss+xml" title="{{ $meta->web_name ?? 'Kangwendra' }} RSS Feed" href="{{ url('/') }}/feed.xml">
    <link rel="alternate" type="application/atom+xml" title="{{ $meta->web_name ?? 'Kangwendra' }} Atom Feed" href="{{ url('/') }}/feed.xml">

    <!-- OpenSearch Description (Windows Search integration) -->
    <link rel="search" type="application/opensearchdescription+xml" title="{{ $meta->web_name ?? 'Kangwendra' }}" href="{{ url('/') }}/opensearch.xml">

    <!-- Google Search Console Verification (replace with your verification token) -->
    <!-- <meta name="google-site-verification" content="YOUR_VERIFICATION_TOKEN_HERE"> -->

    <!-- Bing Webmaster Verification -->
    <!-- <meta name="msvalidate.01" content="YOUR_BING_TOKEN_HERE"> -->

    <!-- DNS Prefetch for Performance -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//www.google-analytics.com">
    <link rel="dns-prefetch" href="//www.googletagmanager.com">
    <link rel="dns-prefetch" href="//connect.facebook.net">
    
    <!-- Preconnect for Critical Resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    
    <!-- Security Headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="SAMEORIGIN">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    
    <!-- AEO & GEO Meta Tags (Answer Engine + Generative Engine Optimization) -->
    <meta name="AI-generated" content="false">
    <meta name="generator" content="Kangwendra - Portal Berita AI Indonesia">
    <meta name="content-language" content="id">
    <meta name="news_keywords" content="{{ $meta->meta_keywords ?? '' }}">
    <meta name="article:publisher" content="{{ $meta->web_name ?? 'Portal Berita' }}">
    <meta name="article:author" content="{{ $meta->web_name ?? 'Portal Berita' }}">
    <meta name="citation_author" content="{{ $meta->web_name ?? 'Portal Berita' }}">
    <meta name="citation_publication_date" content="{{ isset($post) && $post->published_at ? $post->published_at->format('Y-m-d') : now()->format('Y-m-d') }}">
    <meta name="citation_publisher" content="{{ $meta->web_name ?? 'Portal Berita' }}">
    @if(isset($post) && $post->image)
    <meta name="citation_image" content="{{ getFile($post->image) }}">
    @endif

    <!-- GEO: Organization / Publisher meta for AI training -->
    <meta property="article:section" content="{{ isset($post) ? ($post->category?->name ?? 'Teknologi') : 'Portal Berita' }}">
    @if(isset($post))
    <meta property="article:published_time" content="{{ $post->published_at?->toISOString() ?? now()->toISOString() }}">
    <meta property="article:modified_time" content="{{ $post->updated_at->toISOString() }}">
    @endif
    
    <link rel="stylesheet" href="{{ asset('client/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('client/assets/css/venobox.min.css') }}">
    <link rel="stylesheet" href="{{ asset('client/assets/css/swiper.min.css') }}">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="{{ asset('fonts/nexa/Nexa-Light.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/nexa/Nexa-Bold.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Inter:wght@300;400;500;600;700&family=Jost:wght@300;400;500;600&display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Inter:wght@300;400;500;600;700&family=Jost:wght@300;400;500;600&display=swap"></noscript>
    <link rel="stylesheet" href="{{ asset('client/assets/css/main.css') }}?v={{ @filemtime(public_path('client/assets/css/main.css')) }}">
    <link rel="stylesheet" href="{{ asset('client/assets/css/lunaray-brand.css') }}?v={{ @filemtime(public_path('client/assets/css/lunaray-brand.css')) }}">
    <link rel="stylesheet" href="{{ asset('client/assets/css/lunaray-header.css') }}?v={{ @filemtime(public_path('client/assets/css/lunaray-header.css')) }}">
    <link rel="stylesheet" href="{{ asset('client/assets/css/lunaray-footer.css') }}?v={{ @filemtime(public_path('client/assets/css/lunaray-footer.css')) }}">
    <link rel="stylesheet" href="{{ asset('client/assets/css/aray-chat.css') }}?v={{ @filemtime(public_path('client/assets/css/aray-chat.css')) }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Global Image Rounded Corners */
        img {
            border-radius: 5px;
        }
        
        /* Footer Widget Post No Image Styles */
        .widget-post-item.no-image {
            display: block;
        }
        
        .widget-post-item.no-image .widget-post-content {
            padding: 15px;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .widget-post-item.no-image .widget-post-content h3 {
            margin-bottom: 10px;
        }
        
        .widget-post-item.no-image .widget-post-content h3 a {
            text-decoration: none;
            line-height: 1.4;
        }
        
        .widget-post-item.no-image .widget-post-content h3 a:hover {
            color: #f9e498;
        }
        
        .widget-post-item.no-image .post-meta {
            margin: 0;
            padding: 0;
            list-style: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
        }
        
        .widget-post-item.no-image .post-meta li {
            color: rgba(255,255,255,0.8);
        }
        
        .widget-post-item.no-image .post-meta a {
            text-decoration: none;
        }
        
        .widget-post-item.no-image .post-meta a:hover {
            color: #f9e498;
        }
        
        .widget-post-item.no-image .post-meta .sep::before {
            content: "•";
            color: rgba(255,255,255,0.5);
        }
    </style>

    @stack('styles')
    <link rel="stylesheet" href="{{ asset('client/assets/css/lunaray-type.css') }}?v={{ @filemtime(public_path('client/assets/css/lunaray-type.css')) }}">
    
    @stack('structured-data')
</head>

<body>
    <script>(function(){ document.body.classList.add('loaded'); })();</script>
    @include('widget.client.header')

    <main class="{{ request()->routeIs('beranda') ? '' : 'has-fixed-header-offset' }}">
        @yield('header')
        @yield('content')
    </main>

    @include('widget.client.footer')

    @include('widget.client.aray-chat')

    <script src="{{ asset('client/assets/js/vendor/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('client/assets/js/main.js') }}"></script>
    <script src="{{ asset('client/assets/js/lunaray-beranda.js') }}"></script>
    <script src="{{ asset('client/assets/js/vendor/swiper.min.js') }}"></script>
    <script src="{{ asset('client/assets/js/aray-chat.js') }}?v={{ @filemtime(public_path('client/assets/js/aray-chat.js')) }}"></script>

    @stack('scripts')

    <script>
        var cy = document.getElementById('currentYear'); if (cy) cy.textContent = new Date().getFullYear();
    </script>

</body>
</html>


