<!doctype html>
<html class="no-js" lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Basic SEO Meta Tags -->
    <title>{{ $meta->meta_title ?? 'Portal Berita' }}</title>
    <meta name="description" content="{{ $meta->meta_description ?? 'Portal Berita' }}">
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
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $meta->web_name ?? 'Portal Berita' }}">
    <meta property="og:title" content="{{ $meta->meta_title ?? 'Portal Berita' }}">
    <meta property="og:description" content="{{ $meta->meta_description ?? '' }}">
    <meta property="og:image" content="{{ getFile($meta->og_image ?? '') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:updated_time" content="{{ now()->toISOString() }}">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@{{ str_replace(['https://twitter.com/', 'https://x.com/', '@'], '', $meta->twitter_link ?? '') }}">
    <meta name="twitter:creator" content="@{{ str_replace(['https://twitter.com/', 'https://x.com/', '@'], '', $meta->twitter_link ?? '') }}">
    <meta name="twitter:title" content="{{ $meta->meta_title ?? 'Portal Berita' }}">
    <meta name="twitter:description" content="{{ $meta->meta_description ?? '' }}">
    <meta name="twitter:image" content="{{ getFile($meta->og_image ?? '') }}">
    <meta name="twitter:image:alt" content="{{ $meta->meta_title ?? 'Portal Berita' }}">
    
    <!-- LinkedIn -->
    <meta property="linkedin:owner" content="{{ $meta->web_name ?? 'Portal Berita' }}">
    
    <!-- WhatsApp -->
    <meta property="whatsapp:title" content="{{ $meta->meta_title ?? 'Portal Berita' }}">
    <meta property="whatsapp:description" content="{{ $meta->meta_description ?? '' }}">
    <meta property="whatsapp:image" content="{{ getFile($meta->og_image ?? '') }}">
    
    <!-- Telegram -->
    <meta property="telegram:channel" content="{{ $meta->web_name ?? 'Portal Berita' }}">
    
    <!-- Schema.org JSON-LD for AI and Search Engines -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "NewsMediaOrganization",
        "name": "{{ $meta->web_name ?? 'Portal Berita' }}",
        "url": "{{ url('/') }}",
        "logo": {
            "@type": "ImageObject",
            "url": "{{ getFile($meta->logo ?? '') }}",
            "width": 200,
            "height": 60
        },
        "description": "{{ $meta->meta_description ?? '' }}",
        "sameAs": [
            @if(!empty($meta->facebook_link) && $meta->facebook_link !== '#')
            "{{ $meta->facebook_link }}",
            @endif
            @if(!empty($meta->twitter_link) && $meta->twitter_link !== '#')
            "{{ $meta->twitter_link }}",
            @endif
            @if(!empty($meta->instagram_link) && $meta->instagram_link !== '#')
            "{{ $meta->instagram_link }}",
            @endif
            @if(!empty($meta->youtube_link) && $meta->youtube_link !== '#')
            "{{ $meta->youtube_link }}"
            @endif
        ],
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "{{ $meta->phone_number ?? '' }}",
            "contactType": "Customer Service",
            "email": "{{ $meta->email ?? '' }}"
        },
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "ID",
            "addressLocality": "Indonesia"
        },
        "founder": {
            "@type": "Person",
            "name": "{{ $meta->web_name ?? 'Portal Berita' }}"
        },
        "publishingPrinciples": "{{ url('/') }}/about",
        "diversityPolicy": "{{ url('/') }}/diversity",
        "ethicsPolicy": "{{ url('/') }}/ethics"
    }
    </script>
    
    <!-- Website Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "{{ $meta->web_name ?? 'Portal Berita' }}",
        "url": "{{ url('/') }}",
        "description": "{{ $meta->meta_description ?? '' }}",
        "inLanguage": "id-ID",
        "potentialAction": {
            "@type": "SearchAction",
            "target": {
                "@type": "EntryPoint",
                "urlTemplate": "{{ url('/') }}/search?q={search_term_string}"
            },
            "query-input": "required name=search_term_string"
        },
        "publisher": {
            "@type": "Organization",
            "name": "{{ $meta->web_name ?? 'Portal Berita' }}",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ getFile($meta->logo ?? '') }}"
            }
        }
    }
    </script>
    
    <!-- Breadcrumb Schema (will be overridden by specific pages) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Beranda",
                "item": "{{ url('/') }}"
            }
        ]
    }
    </script>
    
    <!-- Favicon & Icons -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ getFile($meta->favicon ?? '') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ getFile($meta->favicon ?? '') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ getFile($meta->favicon ?? '') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ getFile($meta->logo ?? '') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#ffffff">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ getFile($meta->favicon ?? '') }}">
    
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
    
    <!-- AI and Bot Instructions -->
    <meta name="AI-generated" content="false">
    <meta name="content-language" content="id">
    <meta name="news_keywords" content="{{ $meta->meta_keywords ?? '' }}">
    <meta name="article:publisher" content="{{ $meta->web_name ?? 'Portal Berita' }}">
    <meta name="article:author" content="{{ $meta->web_name ?? 'Portal Berita' }}">
    
    <!-- Google Site Verification (add your verification code) -->
    <!-- <meta name="google-site-verification" content="your-verification-code"> -->
    
    <!-- Bing Site Verification (add your verification code) -->
    <!-- <meta name="msvalidate.01" content="your-verification-code"> -->
    
    <!-- Yandex Site Verification (add your verification code) -->
    <!-- <meta name="yandex-verification" content="your-verification-code"> -->

    <link rel="stylesheet" href="{{ asset('client/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('client/assets/css/venobox.min.css') }}">
    <link rel="stylesheet" href="{{ asset('client/assets/css/swiper.min.css') }}">
    <link rel="stylesheet" href="{{ asset('client/assets/css/main.css') }}">

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
</head>

<body>
    <div class="site-preloader">
        <div class="loader-text" data-text="{{ $meta->web_name ?? 'Portal' }}">{{ $meta->web_name ?? 'Portal' }}</div>
    </div>

    @include('widget.client.header')

    <main>
        @yield('header')
        @yield('content')
    </main>

    <footer class="footer-section bg-light-red">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget widget">
                        <div class="widget-about">
                            <div class="footer-logo">
                                <a href="{{ url('/') }}">
                                    <img src="{{ getFile($meta->logo) }}" alt="{{ $meta->web_name ?? 'Portal' }}" height="40">
                                </a>
                            </div>
                            <p>{{ App\Models\WebIdentity::orderBy('id', 'desc')->first()?->meta_description ?? 'Portal berita terpercaya dengan informasi terkini dan akurat.' }}</p>
                            <ul class="footer-social">
                                @if(!empty($meta->facebook_link) && $meta->facebook_link !== '#')
                                <li><a href="{{ $meta->facebook_link }}"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor"><path d="M512 256C512 114.6 397.4 0 256 0S0 114.6 0 256C0 376 82.7 476.8 194.2 504.5V334.2H141.4V256h52.8V222.3c0-87.1 39.4-127.5 125-127.5c16.2 0 44.2 3.2 55.7 6.4V172c-6-.6-16.5-1-29.6-1c-42 0-58.2 15.9-58.2 57.2V256h83.6l-14.4 78.2H287V510.1C413.8 494.8 512 386.9 512 256h0z"></path></svg></a></li>
                                @endif
                                @if(!empty($meta->twitter_link) && $meta->twitter_link !== '#')
                                <li><a href="{{ $meta->twitter_link }}"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path></svg></a></li>
                                @endif
                                @if(!empty($meta->instagram_link) && $meta->instagram_link !== '#')
                                <li><a href="{{ $meta->instagram_link }}"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="currentColor"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"></path></svg></a></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="footer-widget widget">
                        <div class="widget-nav-menu">
                            <h3 class="widget-title">Kategori</h3>
                            <ul class="menu">
                                @php $categories = App\Models\PostCategory::take(6)->get(); @endphp
                                @if($categories->count() > 0)
                                    @foreach($categories as $category)
                                    <li><a href="/{{ $category->slug ?? '#' }}">{{ $category->name ?? 'Kategori' }}</a></li>
                                    @endforeach
                                @else
                                    <li><a href="#">Belum ada kategori</a></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget widget">
                        <div class="widget-post-items">
                            <h3 class="widget-title">Artikel Terbaru</h3>
                            @php $latestPosts = App\Models\Posts::with('category')->where('status', 'active')->whereNotNull('published_at')->latest('published_at')->take(2)->get(); @endphp
                            @if($latestPosts->count() > 0)
                                @foreach($latestPosts as $post)
                                <div class="widget-post-item img-hover-move {{ !$post->image ? 'no-image' : '' }}">
                                    @if($post->image)
                                        <div class="widget-post-thumb media">
                                            <a href="/{{ $post->category?->slug ?? 'news' }}/{{ $post->slug }}"><img src="{{ getFile($post->image) }}" alt="{{ $post->title }}"></a>
                                        </div>
                                    @endif
                                    <div class="widget-post-content">
                                        <h3><a href="/{{ $post->category?->slug ?? 'news' }}/{{ $post->slug }}" class="text-hover">{{ $post->title }}</a></h3>
                                        <ul class="post-meta">
                                            <li><a href="/{{ $post->category?->slug ?? 'news' }}">{{ $post->category?->name ?? 'Berita' }}</a></li>
                                            <li class="sep"></li>
                                            <li><a href="#" class="date">{{ $post->published_at ? \Carbon\Carbon::parse($post->published_at)->format('d.m.Y') : date('d.m.Y') }}</a></li>
                                        </ul>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="widget-post-item">
                                    <div class="widget-post-content">
                                        <h3>Belum ada artikel terbaru</h3>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget widget">
                        <div class="contact-widget">
                            <h3 class="widget-title">Info Kontak</h3>
                            <ul class="contact-info">
                                @if(!empty($meta->address))
                                <li><span>Alamat:</span>{{ $meta->address }}</li>
                                @endif
                                @if(!empty($meta->phone))
                                <li><span>Telepon:</span><a href="tel:{{ $meta->phone }}">{{ $meta->phone }}</a></li>
                                @endif
                                @if(!empty($meta->email))
                                <li><span>Email:</span><a href="mailto:{{ $meta->email }}">{{ $meta->email }}</a></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="copyright-area">
                <div class="copyright-text">
                    © <span id="currentYear"></span> {{ $meta->web_name ?? 'Portal' }}, All Rights Reserved.
                </div>
                <ul class="footer-social">
                    <li>Ikuti:</li>
                    @if(!empty($meta->facebook_link) && $meta->facebook_link !== '#')
                    <li><a href="{{ $meta->facebook_link }}"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor"><path d="M512 256C512 114.6 397.4 0 256 0S0 114.6 0 256C0 376 82.7 476.8 194.2 504.5V334.2H141.4V256h52.8V222.3c0-87.1 39.4-127.5 125-127.5c16.2 0 44.2 3.2 55.7 6.4V172c-6-.6-16.5-1-29.6-1c-42 0-58.2 15.9-58.2 57.2V256h83.6l-14.4 78.2H287V510.1C413.8 494.8 512 386.9 512 256h0z"></path></svg></a></li>
                    @endif
                    @if(!empty($meta->twitter_link) && $meta->twitter_link !== '#')
                    <li><a href="{{ $meta->twitter_link }}"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path></svg></a></li>
                    @endif
                    @if(!empty($meta->instagram_link) && $meta->instagram_link !== '#')
                    <li><a href="{{ $meta->instagram_link }}"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="currentColor"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"></path></svg></a></li>
                    @endif
                </ul>
            </div>
        </div>
    </footer>

    <div id="scrollup">
        <button id="scroll-top" class="scroll-to-top">
            <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                <path d="M450.001-180.001v-485.077L222.154-437.232 180.001-480 480-779.999 779.999-480l-42.153 42.768-227.847-227.846v485.077h-59.998Z" />
            </svg>
        </button>
    </div>

    <script src="{{ asset('client/assets/js/vendor/jquary-3.6.0.min.js') }}"></script>
    <script src="{{ asset('client/assets/js/vendor/bootstrap.min.js') }}"></script>
    <script src="{{ asset('client/assets/js/vendor/popper.min.js') }}"></script>
    <script src="{{ asset('client/assets/js/vendor/venobox.min.js') }}"></script>
    <script src="{{ asset('client/assets/js/vendor/swiper.min.js') }}"></script>
    <script src="{{ asset('client/assets/js/vendor/smooth-scroll.js') }}"></script>
    <script src="{{ asset('client/assets/js/main.js') }}"></script>

    @stack('scripts')

    <script>
        document.getElementById('currentYear').textContent = new Date().getFullYear();
    </script>

</body>
</html>
