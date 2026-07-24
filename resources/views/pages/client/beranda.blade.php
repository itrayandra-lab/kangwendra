@extends('layouts.client.app')

@push('structured-data')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "{{ $meta->web_name ?? 'Portal Berita' }}",
    "description": "{{ $meta->meta_description ?? 'Portal berita terpercaya dengan informasi terkini dan terpercaya' }}",
    "url": "{{ url('/') }}",
    "potentialAction": {
        "@type": "SearchAction",
        "target": {
            "@type": "EntryPoint",
            "urlTemplate": "{{ url('/search') }}?qr={search_term_string}"
        },
        "query-input": "required name=search_term_string"
    },
    "publisher": {
        "@type": "Organization",
        "name": "{{ $meta->web_name ?? 'Portal Berita' }}",
        "logo": {
            "@type": "ImageObject",
            "url": "{{ $meta->logo ? getFile($meta->logo) : '' }}"
        }
    }
}
</script>

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "ItemList",
    "name": "Artikel Terbaru",
    "description": "Kumpulan artikel berita terbaru dan terpercaya",
    "itemListElement": [
        @foreach($latestNews->take(5) as $index => $news)
        {
            "@type": "ListItem",
            "position": {{ $index + 1 }},
            "item": {
                "@type": "Article",
                "headline": "{{ $news->title }}",
                "description": "{{ Str::limit(strip_tags($news->content), 160) }}",
                "url": "{{ route('post_detail', [$news->category->slug, $news->slug]) }}",
                "datePublished": "{{ $news->published_at ? $news->published_at->toISOString() : $news->created_at->toISOString() }}",
                "author": {
                    "@type": "Person",
                    "name": "{{ $news->createdBy->name ?? 'Admin' }}"
                },
                "image": "{{ $news->image ? getFile($news->image) : '' }}"
            }
        }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ]
}
</script>

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {
            "@type": "Question",
            "name": "Apa saja kategori berita yang tersedia?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Kami menyediakan berbagai kategori berita meliputi: {{ $categories->take(5)->pluck('name')->implode(', ') }} dan kategori lainnya untuk memberikan informasi yang komprehensif."
            }
        },
        {
            "@type": "Question",
            "name": "Bagaimana cara mencari artikel di website ini?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Anda dapat menggunakan fitur pencarian di bagian atas halaman atau menjelajahi artikel berdasarkan kategori yang tersedia. Semua artikel diorganisir dengan baik untuk memudahkan navigasi."
            }
        },
        {
            "@type": "Question",
            "name": "Seberapa sering konten diperbarui?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Konten kami diperbarui secara berkala dengan berita terbaru dan artikel informatif. Tim editorial kami bekerja untuk menyajikan informasi yang akurat dan terkini setiap hari."
            }
        }
    ]
}
</script>
@endpush

@section('content')

{{-- ===== HERO BANNER dari Berita ===== --}}
@php
    $heroPosts = $featuredPosts->take(4);
    $heroMain  = $heroPosts->first();
    $heroSubs  = $heroPosts->skip(1)->values();
@endphp

@if($heroMain)
<section class="homepage-hero">
    <div class="container">
        <div class="hero-grid">

            {{-- Artikel Utama (besar, kiri) --}}
            <div class="hero-main">
                <a href="{{ route('post_detail', [$heroMain->category?->slug ?? 'uncategorized', $heroMain->slug]) }}" class="hero-main-link">
                    <div class="hero-main-img">
                        <img src="{{ $heroMain->image ? getFile($heroMain->image) : asset('assets/default.jpg') }}" alt="{{ $heroMain->title }}">
                        <div class="hero-main-overlay">
                            <div class="hero-main-content">
                                <span class="hero-category">{{ $heroMain->category?->name ?? 'Uncategorized' }}</span>
                                <h2 class="hero-title">{{ Str::limit($heroMain->title, 90) }}</h2>
                                <div class="hero-meta">
                                    <span>{{ $heroMain->createdBy?->name ?? 'Admin' }}</span>
                                    <span class="hero-meta-sep">·</span>
                                    <span>{{ $heroMain->published_at ? $heroMain->published_at->diffForHumans() : $heroMain->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Artikel Sub (kecil, kanan) --}}
            @if($heroSubs->count() > 0)
            <div class="hero-sub-grid">
                @foreach($heroSubs->take(3) as $sub)
                <a href="{{ route('post_detail', [$sub->category?->slug ?? 'uncategorized', $sub->slug]) }}" class="hero-sub-item">
                    <div class="hero-sub-img">
                        <img src="{{ $sub->image ? getFile($sub->image) : asset('assets/default.jpg') }}" alt="{{ $sub->title }}">
                        <div class="hero-sub-overlay">
                            <span class="hero-category">{{ $sub->category?->name ?? 'Uncategorized' }}</span>
                            <h3 class="hero-sub-title">{{ Str::limit($sub->title, 60) }}</h3>
                            <span class="hero-sub-date">{{ $sub->published_at ? $sub->published_at->diffForHumans() : $sub->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @endif

        </div>
    </div>
</section>
@endif
<section class="main-post-area" style="padding: 40px 0 100px;">
    <div class="container">
        <div class="row gy-5 gy-lg-0 main-area">
            <div class="col-lg-8">
                <div class="main-post-wrap">
                    {{-- Section Header --}}
                    <div class="section-heading mb-4">
                        <h3>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                <path d="M480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Zm-40 200h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Z"/>
                            </svg>
                            <span>Berita Pilihan Hari Ini</span>
                        </h3>
                        <p class="section-subtitle">Update terkini seputar teknologi, bisnis, dan lifestyle</p>
                    </div>
                    
                    <div class="row gy-4">
                        @foreach($latestNews->take(10) as $index => $article)
                            <article class="col-lg-12 col-md-6">
                                <div class="post-card horizontal-card img-hover-move">
                                    @if($article->image)
                                        <div class="post-thumb media">
                                            <a href="{{ route('post_detail', [$article->category?->slug ?? 'uncategorized', $article->slug]) }}">
                                                <img src="{{ getFile($article->image) }}" alt="{{ $article->title }}">
                                            </a>
                                        </div>
                                    @endif
                                    <div class="post-content">
                                        <ul class="post-meta">
                                            <li>
                                                <a href="{{ route('category', $article->category?->slug ?? 'uncategorized') }}">{{ $article->category?->name ?? 'Uncategorized' }}</a>
                                            </li>
                                            <li class="sep"></li>
                                            <li>
                                                <a href="#" class="date">{{ $article->published_at?->format('d.m.Y') }}</a>
                                            </li>
                                        </ul>
                                        <h3>
                                            <a href="{{ route('post_detail', [$article->category?->slug ?? 'uncategorized', $article->slug]) }}" class="text-hover">{{ $article->title }}</a>
                                        </h3>
                                        @if($article->content)
                                            <p>{{ Str::limit(strip_tags($article->content), 120) }}</p>
                                        @endif
                                        <ul class="post-card-footer">
                                            <li>
                                                <a href="{{ route('post_detail', [$article->category?->slug ?? 'uncategorized', $article->slug]) }}" class="read-more">Baca Selengkapnya</a>
                                            </li>
                                            <li>
                                                <a href="#" class="comment">
                                                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                                        <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q74-137 194-218.5T480-800q146 0 266 81.5T920-500q-74 137-194 218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T832-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"/>
                                                    </svg>
                                                    <span>{{ $article->counter }}</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </article>
                            
                            {{-- Iklan setelah artikel ke-3 --}}
                            @if($index == 2 && isset($ads) && $ads->where('type', 'image')->count() > 0)
                                <div class="col-12">
                                    <div class="advertisement-card">
                                        <div class="ad-content">
                                            @php $imageAd = $ads->where('type', 'image')->first(); @endphp
                                            @if($imageAd)
                                                <a href="{{ $imageAd->link ?? '#' }}" target="_blank">
                                                    <img src="{{ getFile($imageAd->image) }}" alt="{{ $imageAd->title ?? 'Advertisement' }}" class="img-fluid">
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @if ($hikmahPosts->count() !== 0)
                    <div class="main-post-wrap" style="margin-top: 50px;">
                        {{-- Section Header --}}
                        <div class="section-heading mb-4">
                            <h3>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="m233-80 65-281L80-550l288-25 112-265 112 265 288 25-218 189 65 281-247-149L233-80Zm247-350Zm0 0Z"/>
                                </svg>
                                <span>Hikmah & Inspirasi</span>
                            </h3>
                            <p class="section-subtitle">Renungan dan pembelajaran untuk kehidupan</p>
                        </div>
                        <div class="row gy-4">
                            @foreach($hikmahPosts->take(10) as $index => $article)
                                <article class="col-lg-12 col-md-6">
                                    <div class="post-card horizontal-card img-hover-move">
                                        @if($article->image)
                                            <div class="post-thumb media">
                                                <a href="{{ route('post_detail', [$article->category?->slug ?? 'uncategorized', $article->slug]) }}">
                                                    <img src="{{ getFile($article->image) }}" alt="{{ $article->title }}">
                                                </a>
                                            </div>
                                        @endif
                                        <div class="post-content">
                                            <ul class="post-meta">
                                                <li>
                                                    <a href="{{ route('category', $article->category?->slug ?? 'uncategorized') }}">{{ $article->category?->name ?? 'Uncategorized' }}</a>
                                                </li>
                                                <li class="sep"></li>
                                                <li>
                                                    <a href="#" class="date">{{ $article->published_at?->format('d.m.Y') }}</a>
                                                </li>
                                            </ul>
                                            <h3>
                                                <a href="{{ route('post_detail', [$article->category?->slug ?? 'uncategorized', $article->slug]) }}" class="text-hover">{{ $article->title }}</a>
                                            </h3>
                                            @if($article->content)
                                                <p>{{ Str::limit(strip_tags($article->content), 120) }}</p>
                                            @endif
                                            <ul class="post-card-footer">
                                                <li>
                                                    <a href="{{ route('post_detail', [$article->category?->slug ?? 'uncategorized', $article->slug]) }}" class="read-more">Baca Selengkapnya</a>
                                                </li>
                                                <li>
                                                    <a href="#" class="comment">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                                            <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q74-137 194-218.5T480-800q146 0 266 81.5T920-500q-74 137-194 218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T832-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"/>
                                                        </svg>
                                                        <span>{{ $article->counter }}</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </article>
                                
                                {{-- Iklan setelah artikel ke-3 --}}
                                @if($index == 2 && isset($ads) && $ads->where('type', 'image')->count() > 0)
                                    <div class="col-12">
                                        <div class="advertisement-card">
                                            <div class="ad-content">
                                                @php $imageAd = $ads->where('type', 'image')->first(); @endphp
                                                @if($imageAd)
                                                    <a href="{{ $imageAd->link ?? '#' }}" target="_blank">
                                                        <img src="{{ getFile($imageAd->image) }}" alt="{{ $imageAd->title ?? 'Advertisement' }}" class="img-fluid">
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
                @if ($amazingPosts->count() !== 0)
                    <div class="main-post-wrap" style="margin-top: 50px;">
                        {{-- Section Header --}}
                        <div class="section-heading mb-4">
                            <h3>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="M440-240h80v-120h120v-80H520v-120h-80v120H320v80h120v120ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h168q13-36 43.5-58t68.5-22q38 0 68.5 22t43.5 58h168q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm280-590q13 0 21.5-8.5T510-820q0-13-8.5-21.5T480-850q-13 0-21.5 8.5T450-820q0 13 8.5 21.5T480-790ZM200-200v-560 560Z"/>
                                </svg>
                                <span>AmAzing</span>
                            </h3>
                            <p class="section-subtitle">Kisah-kisah menakjubkan yang menginspirasi</p>
                        </div>
                        
                        <div class="row gy-4">
                            @foreach($amazingPosts->take(10) as $index => $article)
                                <article class="col-lg-12 col-md-6">
                                    <div class="post-card horizontal-card img-hover-move">
                                        @if($article->image)
                                            <div class="post-thumb media">
                                                <a href="{{ route('post_detail', [$article->category?->slug ?? 'uncategorized', $article->slug]) }}">
                                                    <img src="{{ getFile($article->image) }}" alt="{{ $article->title }}">
                                                </a>
                                            </div>
                                        @endif
                                        <div class="post-content">
                                            <ul class="post-meta">
                                                <li>
                                                    <a href="{{ route('category', $article->category?->slug ?? 'uncategorized') }}">{{ $article->category?->name ?? 'Uncategorized' }}</a>
                                                </li>
                                                <li class="sep"></li>
                                                <li>
                                                    <a href="#" class="date">{{ $article->published_at?->format('d.m.Y') }}</a>
                                                </li>
                                            </ul>
                                            <h3>
                                                <a href="{{ route('post_detail', [$article->category?->slug ?? 'uncategorized', $article->slug]) }}" class="text-hover">{{ $article->title }}</a>
                                            </h3>
                                            @if($article->content)
                                                <p>{{ Str::limit(strip_tags($article->content), 120) }}</p>
                                            @endif
                                            <ul class="post-card-footer">
                                                <li>
                                                    <a href="{{ route('post_detail', [$article->category?->slug ?? 'uncategorized', $article->slug]) }}" class="read-more">Baca Selengkapnya</a>
                                                </li>
                                                <li>
                                                    <a href="#" class="comment">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                                            <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q74-137 194-218.5T480-800q146 0 266 81.5T920-500q-74 137-194 218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T832-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"/>
                                                        </svg>
                                                        <span>{{ $article->counter }}</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </article>
                                
                                {{-- Iklan setelah artikel ke-3 --}}
                                @if($index == 2 && isset($ads) && $ads->where('type', 'image')->count() > 0)
                                    <div class="col-12">
                                        <div class="advertisement-card">
                                            <div class="ad-content">
                                                @php $imageAd = $ads->where('type', 'image')->first(); @endphp
                                                @if($imageAd)
                                                    <a href="{{ $imageAd->link ?? '#' }}" target="_blank">
                                                        <img src="{{ getFile($imageAd->image) }}" alt="{{ $imageAd->title ?? 'Advertisement' }}" class="img-fluid">
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
                @if ($marketingPosts->count() !== 0)
                    <div class="main-post-wrap" style="margin-top: 50px;">
                        {{-- Section Header --}}
                        <div class="section-heading mb-4">
                            <h3>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="M160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720v480q0 33-23.5 56.5T800-160H160Zm0-80h640v-400H160v400Zm140-40h360v-80H300v80Zm0-120h360v-80H300v80ZM180-680h60v-60h-60v60Zm140 0h60v-60h-60v60Zm140 0h60v-60h-60v60Z"/>
                                </svg>
                                <span>Marketing & Bisnis</span>
                            </h3>
                            <p class="section-subtitle">Strategi dan tips mengembangkan bisnis Anda</p>
                        </div>
                        
                        <div class="row gy-4">
                            @foreach($marketingPosts->take(10) as $index => $article)
                                <article class="col-lg-12 col-md-6">
                                    <div class="post-card horizontal-card img-hover-move">
                                        @if($article->image)
                                            <div class="post-thumb media">
                                                <a href="{{ route('post_detail', [$article->category?->slug ?? 'uncategorized', $article->slug]) }}">
                                                    <img src="{{ getFile($article->image) }}" alt="{{ $article->title }}">
                                                </a>
                                            </div>
                                        @endif
                                        <div class="post-content">
                                            <ul class="post-meta">
                                                <li>
                                                    <a href="{{ route('category', $article->category?->slug ?? 'uncategorized') }}">{{ $article->category?->name ?? 'Uncategorized' }}</a>
                                                </li>
                                                <li class="sep"></li>
                                                <li>
                                                    <a href="#" class="date">{{ $article->published_at?->format('d.m.Y') }}</a>
                                                </li>
                                            </ul>
                                            <h3>
                                                <a href="{{ route('post_detail', [$article->category?->slug ?? 'uncategorized', $article->slug]) }}" class="text-hover">{{ $article->title }}</a>
                                            </h3>
                                            @if($article->content)
                                                <p>{{ Str::limit(strip_tags($article->content), 120) }}</p>
                                            @endif
                                            <ul class="post-card-footer">
                                                <li>
                                                    <a href="{{ route('post_detail', [$article->category?->slug ?? 'uncategorized', $article->slug]) }}" class="read-more">Baca Selengkapnya</a>
                                                </li>
                                                <li>
                                                    <a href="#" class="comment">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                                            <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q74-137 194-218.5T480-800q146 0 266 81.5T920-500q-74 137-194 218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T832-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"/>
                                                        </svg>
                                                        <span>{{ $article->counter }}</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </article>
                                
                                {{-- Iklan setelah artikel ke-3 --}}
                                @if($index == 2 && isset($ads) && $ads->where('type', 'image')->count() > 0)
                                    <div class="col-12">
                                        <div class="advertisement-card">
                                            <div class="ad-content">
                                                @php $imageAd = $ads->where('type', 'image')->first(); @endphp
                                                @if($imageAd)
                                                    <a href="{{ $imageAd->link ?? '#' }}" target="_blank">
                                                        <img src="{{ getFile($imageAd->image) }}" alt="{{ $imageAd->title ?? 'Advertisement' }}" class="img-fluid">
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
                @if ($brandingPosts->count() !== 0)
                    <div class="main-post-wrap" style="margin-top: 50px;">
                        {{-- Section Header --}}
                        <div class="section-heading mb-4">
                            <h3>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="M120-120v-80l80-80v160h-80Zm160 0v-240l80-80v320h-80Zm160 0v-320l80 81v239h-80Zm160 0v-239l80-80v319h-80Zm160 0v-400l80-80v480h-80ZM120-327v-113l280-280 160 160 280-280v113L560-447 400-607 120-327Z"/>
                                </svg>
                                <span>Branding & Kreativitas</span>
                            </h3>
                            <p class="section-subtitle">Membangun identitas brand yang kuat dan berkesan</p>
                        </div>
                        
                        <div class="row gy-4">
                            @foreach($brandingPosts->take(10) as $index => $article)
                                <article class="col-lg-12 col-md-6">
                                    <div class="post-card horizontal-card img-hover-move">
                                        @if($article->image)
                                            <div class="post-thumb media">
                                                <a href="{{ route('post_detail', [$article->category?->slug ?? 'uncategorized', $article->slug]) }}">
                                                    <img src="{{ getFile($article->image) }}" alt="{{ $article->title }}">
                                                </a>
                                            </div>
                                        @endif
                                        <div class="post-content">
                                            <ul class="post-meta">
                                                <li>
                                                    <a href="{{ route('category', $article->category?->slug ?? 'uncategorized') }}">{{ $article->category?->name ?? 'Uncategorized' }}</a>
                                                </li>
                                                <li class="sep"></li>
                                                <li>
                                                    <a href="#" class="date">{{ $article->published_at?->format('d.m.Y') }}</a>
                                                </li>
                                            </ul>
                                            <h3>
                                                <a href="{{ route('post_detail', [$article->category?->slug ?? 'uncategorized', $article->slug]) }}" class="text-hover">{{ $article->title }}</a>
                                            </h3>
                                            @if($article->content)
                                                <p>{{ Str::limit(strip_tags($article->content), 120) }}</p>
                                            @endif
                                            <ul class="post-card-footer">
                                                <li>
                                                    <a href="{{ route('post_detail', [$article->category?->slug ?? 'uncategorized', $article->slug]) }}" class="read-more">Baca Selengkapnya</a>
                                                </li>
                                                <li>
                                                    <a href="#" class="comment">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                                            <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q74-137 194-218.5T480-800q146 0 266 81.5T920-500q-74 137-194 218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T832-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"/>
                                                        </svg>
                                                        <span>{{ $article->counter }}</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </article>
                                
                                {{-- Iklan setelah artikel ke-3 --}}
                                @if($index == 2 && isset($ads) && $ads->where('type', 'image')->count() > 0)
                                    <div class="col-12">
                                        <div class="advertisement-card">
                                            <div class="ad-content">
                                                @php $imageAd = $ads->where('type', 'image')->first(); @endphp
                                                @if($imageAd)
                                                    <a href="{{ $imageAd->link ?? '#' }}" target="_blank">
                                                        <img src="{{ getFile($imageAd->image) }}" alt="{{ $imageAd->title ?? 'Advertisement' }}" class="img-fluid">
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            
            {{-- Sidebar --}}
            <div class="col-lg-4">
                <div class="sidebar-area">
                    {{-- Tags Widget --}}
                    <div class="sidebar-widget widget">
                        <div class="widget-heading">
                            <h3>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="M488.768-117.847Q470.922-100.001 446-100.001t-42.768-17.846l-286-286q-17.231-17.231-17.038-42.653.192-25.422 17.807-43.037l352-352.616q8.317-8.179 19.658-13.012 11.341-4.834 23.726-4.834h286q24.537 0 42.268 17.731 17.73 17.73 17.73 42.268v286q0 12.826-4.961 24.143-4.962 11.318-13.654 20.01l-352 352Zm210.571-532.154q20.815 0 35.43-14.57 14.615-14.57 14.615-35.384t-14.57-35.429q-14.57-14.615-35.384-14.615t-35.429 14.57q-14.616 14.57-14.616 35.384t14.57 35.429q14.57 14.615 35.384 14.615ZM446.172-160l353.213-354v-286H513.212L160-446l286.172 286Zm353.213-640Z"></path>
                                </svg>
                                <span>Tag Populer</span>
                            </h3>
                        </div>
                        <div class="widget-tags">
                            <ul class="tag-list">
                                @foreach($tags->take(10) as $tag)
                                    <li>
                                        <a href="{{ route('tag', $tag?->slug ?? '#') }}" class="tag-item">
                                            #{{ $tag?->name ?? 'Tag' }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    
                    {{-- Categories Widget --}}
                    <div class="sidebar-widget widget">
                        <div class="widget-heading">
                            <h3>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="M632.307-140.001q-24.538 0-42.268-17.731-17.73-17.73-17.73-42.268v-167.693q0-24.538 17.73-42.268t42.268-17.73H800q24.538 0 42.268 17.73 17.731 17.73 17.731 42.268V-200q0 24.538-17.731 42.268-17.73 17.731-42.268 17.731H632.307Zm0-59.999H800v-167.693H632.307V-200Zm-532.306-53.847v-59.999h344.615v59.999H100.001Zm532.306-278.462q-24.538 0-42.268-17.73t-17.73-42.268V-760q0-24.538 17.73-42.268 17.73-17.731 42.268-17.731H800q24.538 0 42.268 17.731 17.731 17.73 17.731 42.268v167.693q0 24.538-17.731 42.268-17.73 17.73-42.268 17.73H632.307Zm0-59.998H800V-760H632.307v167.693Zm-532.306-53.847v-59.999h344.615v59.999H100.001Zm616.153 362.308Zm0-392.308Z" />
                                </svg>
                                <span>Kategori</span>
                            </h3>
                            <ul class="widget-category-list">
                                @foreach($categories->take(4) as $category)
                                    <li class="img-hover-move">
                                        <a href="{{ route('category', $category->slug) }}" class="media">
                                            @if($category->posts->first() && $category->posts->first()->image)
                                                <img src="{{ getFile($category->posts->first()->image) }}" alt="{{ $category->name }}">
                                            @endif
                                            {{ $category->name }} 
                                            <span>{{ $category->posts->count() }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    
                    {{-- Top Stories Widget --}}
                    <div class="sidebar-widget widget">
                        <div class="widget-heading">
                            <h3>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="M212.309-140.001q-30.308 0-51.308-21t-21-51.308v-535.382q0-30.308 21-51.308t51.308-21h419.229l188.461 188.461v419.229q0 30.308-21 51.308t-51.308 21H212.309Zm0-59.999h535.382q5.385 0 8.847-3.462 3.462-3.462 3.462-8.847V-600H600v-160H212.309q-5.385 0-8.847 3.462-3.462 3.462-3.462 8.847v535.382q0 5.385 3.462 8.847 3.462 3.462 8.847 3.462Zm77.692-100.001h379.998V-360H290.001v59.999Zm0-299.999H480v-59.999H290.001V-600Zm0 149.999h379.998v-59.998H290.001v59.998ZM200-760v160-160 560V-760Z" />
                                </svg>
                                <span>Artikel Populer</span>
                            </h3>
                        </div>
                        <div class="widget-post-items">
                            @foreach($mostPopular->take(5) as $popular)
                                <div class="widget-post-item img-hover-move">
                                    @if($popular->image)
                                        <div class="widget-post-thumb media">
                                            <a href="{{ route('post_detail', [$popular->category->slug, $popular->slug]) }}">
                                                <img src="{{ getFile($popular->image) }}" alt="{{ $popular->title }}">
                                            </a>
                                        </div>
                                    @else
                                        <div class="widget-post-thumb media">
                                            <a href="{{ route('post_detail', [$popular->category->slug, $popular->slug]) }}">
                                                <img src="{{ asset('assets/default.jpg') }}" alt="{{ $popular->title }}">
                                            </a>
                                        </div>
                                    @endif
                                    <div class="widget-post-content">
                                        <h3>
                                            <a href="{{ route('post_detail', [$popular->category->slug, $popular->slug]) }}" class="text-hover">{{ Str::limit($popular->title, 50) }}</a>
                                        </h3>
                                        @if(is_array($popular->tags) && count($popular->tags))
                                            <div class="article-tags">
                                                @foreach(array_slice($popular->tags, 0, 2) as $tag)
                                                    <a href="{{ route('tag', Str::slug($tag)) }}" class="article-tags">#{{ $tag }}</a>
                                                @endforeach
                                            </div>
                                        @endif
                                        <ul class="post-meta">
                                            <li>
                                                <a href="{{ route('category', $popular->category->slug) }}">{{ $popular->category->name }}</a>
                                            </li>
                                            <li class="sep"></li>
                                            <li>
                                                <a href="#" class="date">{{ $popular->published_at->format('d.m.Y') }}</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!--Sidebar Information-->
                    @if ($information && $information->total() > 0)
                        <div class="sidebar-widget widget">
                            <div class="widget-heading">
                                <h3>
                                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                        <path d="M212.309-140.001q-30.308 0-51.308-21t-21-51.308v-535.382q0-30.308 21-51.308t51.308-21h419.229l188.461 188.461v419.229q0 30.308-21 51.308t-51.308 21H212.309Zm0-59.999h535.382q5.385 0 8.847-3.462 3.462-3.462 3.462-8.847V-600H600v-160H212.309q-5.385 0-8.847 3.462-3.462 3.462-3.462 8.847v535.382q0 5.385 3.462 8.847 3.462 3.462 8.847 3.462Zm77.692-100.001h379.998V-360H290.001v59.999Zm0-299.999H480v-59.999H290.001V-600Zm0 149.999h379.998v-59.998H290.001v59.998ZM200-760v160-160 560V-760Z" />
                                    </svg>
                                    <span>Information</span>
                                </h3>
                            </div>
                            <div class="widget-post-items">
                                @foreach ($information as $data)
                                    <div class="widget-post-item img-hover-move information-post">
                                        <div class="widget-post-content">
                                            <ul class="post-meta">
                                                <li>
                                                    <a href="{{ $data->slug }}">#{{ $data->id }}</a>
                                                </li>
                                                <li class="sep"></li>
                                                <li>
                                                    <a href="{{ $data->slug }}" class="date">{{ $data->created_at }}</a>
                                                </li>
                                            </ul>
                                            <h3>
                                                <a href="{{ $data->slug }}" class="text-hover">{{ $data->title }}</a>
                                            </h3>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    {{-- Banner Widget --}}
                    @if(isset($ads) && $ads->count() > 0)
                        <div class="sidebar-widget widget">
                            <div class="widget-banner">
                                <a href="#">
                                    <img src="{{ getFile($ads->first()->image) }}" alt="banner">
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <!--/.sidebar-area-->
            </div>
        </div>
    </div>
</section>

@endsection

@push('styles')
<style>
    .hero-banner-slider {
        width: 100vw;
        margin-left: calc(-50vw + 50%);
        margin-bottom: 40px;
        position: relative;
    }

    .hero-banner-swiper {
        width: 100%;
        height: 85vh;
    }

    @media (max-width: 1200px) {
        .hero-banner-swiper {
            height: 70vh;
        }
    }

    @media (max-width: 768px) {
        .hero-banner-swiper {
            height: 60vh;
        }
    }

    @media (max-width: 576px) {
        .hero-banner-swiper {
            height: 50vh;
        }
    }

    .banner-item {
        width: 100%;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .banner-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to right, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.6) 30%, rgba(0,0,0,0.3) 60%, transparent 100%);
        z-index: 1;
    }

    .banner-link {
        display: block;
        width: 100%;
        height: 100%;
        position: relative;
    }

    .banner-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .banner-content {
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        transform: translateY(-50%);
        padding: 60px 0;
        color: white;
        z-index: 2;
    }

    .banner-content-inner {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
        text-align: left;
    }

    .banner-title {
        font-size: 56px;
        font-weight: 700;
        margin: 0 0 20px 0;
        color: white;
        text-shadow: 3px 3px 12px rgba(0,0,0,0.9), 0 0 20px rgba(0,0,0,0.8);
        line-height: 1.2;
        max-width: 900px;
        word-wrap: break-word;
    }

    .banner-description {
        font-size: 20px;
        margin: 0;
        color: white;
        text-shadow: 2px 2px 8px rgba(0,0,0,0.9), 0 0 15px rgba(0,0,0,0.8);
        line-height: 1.6;
        max-width: 800px;
        word-wrap: break-word;
    }
    }

    @media (max-width: 1200px) {
        .banner-title {
            font-size: 48px;
        }
        .banner-description {
            font-size: 18px;
        }
        .banner-content {
            padding: 50px 0;
        }
    }

    @media (max-width: 768px) {
        .banner-title {
            font-size: 36px;
        }
        .banner-description {
            font-size: 16px;
        }
        .banner-content {
            padding: 40px 0;
        }
    }

    @media (max-width: 576px) {
        .banner-title {
            font-size: 28px;
        }
        .banner-description {
            font-size: 15px;
        }
        .banner-content {
            padding: 30px 0;
        }
    }

    .hero-banner-swiper .swiper-button-next,
    .hero-banner-swiper .swiper-button-prev {
        color: white;
        background: rgba(0,0,0,0.5);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .hero-banner-swiper .swiper-button-next:after,
    .hero-banner-swiper .swiper-button-prev:after {
        font-size: 22px;
        font-weight: bold;
    }

    .hero-banner-swiper .swiper-button-next:hover,
    .hero-banner-swiper .swiper-button-prev:hover {
        background: rgba(0,0,0,0.8);
        transform: scale(1.1);
    }

    @media (max-width: 768px) {
        .hero-banner-swiper .swiper-button-next,
        .hero-banner-swiper .swiper-button-prev {
            width: 40px;
            height: 40px;
        }
        .hero-banner-swiper .swiper-button-next:after,
        .hero-banner-swiper .swiper-button-prev:after {
            font-size: 18px;
        }
    }

    .hero-banner-swiper .swiper-pagination {
        bottom: 20px;
    }

    .hero-banner-swiper .swiper-pagination-bullet {
        width: 10px;
        height: 10px;
        background: white;
        opacity: 0.6;
        transition: all 0.3s ease;
    }

    .hero-banner-swiper .swiper-pagination-bullet-active {
        opacity: 1;
        background: white;
        transform: scale(1.2);
    }

    .widget-tags .tag-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .widget-tags .tag-item {
        display: inline-block;
        padding: 6px 12px;
        background-color: #f8f9fa;
        color: #495057;
        text-decoration: none;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
    }
    
    .widget-tags .tag-item:hover {
        background-color: #007bff;
        color: white;
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,123,255,0.3);
    }
    
    .advertisement-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        border: 1px solid #e9ecef;
    }
    
    .advertisement-card img {
        max-width: 100%;
        height: auto;
        border-radius: 4px;
    }
    
    .section-heading h3 {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #007bff;
    }
    
    .section-heading h3 svg {
        color: #007bff;
    }

    .post-layout-2 {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .post-layout-item {
        flex: 1;
        min-width: 300px;
    }

    .post-layout-item:first-child {
        flex: 1.5;
    }

    .post-layout-item:nth-child(2) {
        flex: 1;
    }

    .post-layout-item:nth-child(3) {
        flex: 1;
    }

    .vartical-post-carousel {
        overflow: hidden;
    }

    .vertical-post-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .vertical-post-list .post-layout-item {
        min-width: 0;
        flex: unset;
    }

    .information-post .widget-post-content {
        padding: 12px 0;
    }

    .information-post .widget-post-content h3 {
        font-size: 1rem;
        margin: 0;
    }

    @media (max-width: 768px) {
        .post-layout-2 {
            flex-direction: column;
        }
        .post-layout-item,
        .post-layout-item:first-child,
        .post-layout-item:nth-child(2),
        .post-layout-item:nth-child(3) {
            flex: unset;
            min-width: 0;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    var heroBannerSwiper = new Swiper('.hero-banner-swiper', {
        slidesPerView: 1,
        spaceBetween: 0,
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
    });

    </script>
@endpush



