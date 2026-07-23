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

@if(isset($banner_1) && $banner_1->count() > 0)
<section class="hero-banner-slider">
    <div class="swiper hero-banner-swiper">
        <div class="swiper-wrapper">
            @foreach($banner_1 as $banner)
                <div class="swiper-slide">
                    <div class="banner-item">
                        @if($banner->image)
                            <a href="{{ $banner->link ?? '#' }}" class="banner-link">
                                <img src="{{ getFile($banner->image) }}" alt="{{ $banner->title ?? 'Banner' }}" class="banner-image">
                                @if($banner->title || $banner->description)
                                    <div class="banner-content">
                                        <div class="banner-content-inner">
                                            @if($banner->title)
                                                <h2 class="banner-title">{{ $banner->title }}</h2>
                                            @endif
                                            @if($banner->description)
                                                <p class="banner-description">{!!  Str::limit(strip_tags($banner->description), 150)  !!}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</section>
@endif

<div class="featured-post-grid">
    <div class="container">
        <div class="post-layout-2">
            @foreach ($featuredPosts->take(3) as $index => $post)
                <article class="post-layout-item img-hover-move">
                    <a href="{{ route('post_detail', [$post?->category?->slug, $post->slug]) }}" class="post-thumb media">
                        <img src="{{ $post->image ? getFile($post->image) : asset('assets/default.jpg') }}" alt="{{ $post->title }}">
                    </a>
                    <div class="post-content">
                        <ul class="post-meta">
                            <li class="reading-time">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" height="24" viewBox="0 -960 960 960" width="24">
                                    <path d="m618.924-298.924 42.152-42.152-151.077-151.087V-680h-59.998v212.154l168.923 168.922ZM480.067-100.001q-78.836 0-148.204-29.92-69.369-29.92-120.682-81.21-51.314-51.291-81.247-120.629-29.933-69.337-29.933-148.173t29.92-148.204q29.92-69.369 81.21-120.682 51.291-51.314 120.629-81.247 69.337-29.933 148.173-29.933t148.204 29.92q69.369 29.92 120.682 81.21 51.314 51.291 81.247 120.629 29.933 69.337 29.933 148.173t-29.92 148.204q-29.92 69.369-81.21 120.682-51.291 51.314-120.629 81.247-69.337 29.933-148.173 29.933ZM480-480Zm0 320q133 0 226.5-93.5T800-480q0-133-93.5-226.5T480-800q-133 0-226.5 93.5T160-480q0 133 93.5 226.5T480-160Z"></path>
                                </svg>
                                <span class="post-meta-text">{{ $post->published_at ? $post->published_at->diffForHumans() : $post->created_at->diffForHumans() }}</span>
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" height="24" viewBox="0 -960 960 960" width="24">
                                    <path d="M488.768-117.847Q470.922-100.001 446-100.001t-42.768-17.846l-286-286q-17.231-17.231-17.038-42.653.192-25.422 17.807-43.037l352-352.616q8.317-8.179 19.658-13.012 11.341-4.834 23.726-4.834h286q24.537 0 42.268 17.731 17.73 17.73 17.73 42.268v286q0 12.826-4.961 24.143-4.962 11.318-13.654 20.01l-352 352Zm210.571-532.154q20.815 0 35.43-14.57 14.615-14.57 14.615-35.384t-14.57-35.429q-14.57-14.615-35.384-14.615t-35.429 14.57q-14.616 14.57-14.616 35.384t14.57 35.429q14.57 14.615 35.384 14.615ZM446.172-160l353.213-354v-286H513.212L160-446l286.172 286Zm353.213-640Z"></path>
                                </svg>
                                <a href="{{ route('category', $post->category?->slug ?? 'uncategorized') }}" class="post-meta-text">{{ $post->category?->name ?? 'Uncategorized' }}</a>
                            </li>
                        </ul>
                        <h3>
                            <a href="{{ route('post_detail', [$post->category?->slug ?? 'uncategorized', $post->slug]) }}" class="text-hover">{{ Str::limit($post->title, 60) }}</a>
                        </h3>
                        @if(is_array($post->tags) && count($post->tags))
                            <div class="article-tags">
                                @foreach(array_slice($post->tags, 0, 3) as $tag)
                                    <a href="{{ route('tag', Str::slug($tag)) }}" class="article-tags a">#{{ $tag }}</a>
                                @endforeach
                            </div>
                        @endif
                        @if($index == 2 && $post->content)
                            <p>{{ Str::limit(strip_tags($post->content), 150) }}</p>
                        @endif
                        <ul class="author-info">
                            <li>
                                <a href="{{ route('author', $post->createdBy?->slug ?? '#') }}">
                                    @if($post->createdBy?->image)
                                        <img src="{{ getFile($post->createdBy?->image) }}" alt="{{ $post->createdBy?->name ?? 'Unknown' }}">
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('author', $post->createdBy?->slug ?? '#') }}">{{ $post->createdBy?->name ?? 'Unknown' }}</a>
                                <a href="#">{{ $post->published_at?->format('d.m.Y') }}</a>
                            </li>
                            @if($index == 2)
                                <li class="share-icon">
                                    <div class="share">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" height="24" viewBox="0 -960 960 960" width="24">
                                            <path d="M720.045-90q-45.814 0-77.929-32.084-32.115-32.083-32.115-77.916 0-7.49 1.192-15.514 1.192-8.025 3.577-14.794L318.923-403.539q-15.846 15.769-36.077 24.654-20.231 8.884-42.846 8.884-45.833 0-77.916-32.07t-32.083-77.884q0-45.814 32.083-77.929T240-589.999q22.615 0 42.846 8.884 20.231 8.885 36.077 24.654L614.77-729.692q-2.385-6.769-3.577-14.794-1.192-8.024-1.192-15.514 0-45.833 32.07-77.916t77.884-32.083q45.814 0 77.929 32.07t32.115 77.884q0 45.814-32.083 77.929T720-650.001q-22.615 0-42.846-8.884-20.231-8.885-36.077-24.654L345.23-510.308q2.385 6.769 3.577 14.767 1.192 7.997 1.192 15.461 0 7.465-1.192 15.542t-3.577 14.846l295.847 173.231q15.846-15.769 36.077-24.654 20.231-8.884 42.846-8.884 45.833 0 77.916 32.07t32.083 77.884q0 45.814-32.07 77.929t-77.884 32.115ZM720-710q20.846 0 35.424-14.577 14.577-14.578 14.577-35.424t-14.577-35.424Q740.846-810.001 720-810.001t-35.424 14.577Q669.999-780.846 669.999-760t14.577 35.424q14.578 14.577 35.424 14.577Zm-480 280q20.846 0 35.424-14.577 14.577-14.578 14.577-35.424t-14.577-35.424Q260.846-530.001 240-530.001t-35.424 14.577Q189.999-500.846 189.999-480t14.577 35.424q14.578 14.577 35.424 14.577Zm480 280q20.846 0 35.424-14.577 14.577-14.578 14.577-35.424t-14.577-35.424Q740.846-250.001 720-250.001t-35.424 14.577Q669.999-220.846 669.999-200t14.577 35.424q14.578 14.577 35.424 14.577ZM720-760ZM240-480Zm480 280Z"></path>
                                        </svg>
                                        <ul class="social-share">
                                            <li>
                                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('post_detail', [$post->category?->slug ?? 'uncategorized', $post->slug])) }}" target="_blank">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" fill="currentColor">
                                                        <path d="M80 299.3V512H196V299.3h86.5l18-97.8H196V166.9c0-51.7 20.3-71.5 72.7-71.5c16.3 0 29.4 .4 37 1.2V7.9C291.4 4 256.4 0 236.2 0C129.3 0 80 50.5 80 159.4v42.1H14v97.8H80z"></path>
                                                    </svg>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('post_detail', [$post->category?->slug ?? 'uncategorized', $post->slug])) }}&text={{ urlencode($post->title) }}" target="_blank">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                                                        <path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path>
                                                    </svg>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="https://pinterest.com/pin/create/button/?url={{ urlencode(route('post_detail', [$post->category?->slug ?? 'uncategorized', $post->slug])) }}&description={{ urlencode($post->title) }}" target="_blank">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512" fill="currentColor">
                                                        <path d="M496 256c0 137-111 248-248 248-25.6 0-50.2-3.9-73.4-11.1 10.1-16.5 25.2-43.5 30.8-65 3-11.6 15.4-59 15.4-59 8.1 15.4 31.7 28.5 56.8 28.5 74.8 0 128.7-68.8 128.7-154.3 0-81.9-66.9-143.2-152.9-143.2-107 0-163.9 71.8-163.9 150.1 0 36.4 19.4 81.7 50.3 96.1 4.7 2.2 7.2 1.2 8.3-3.3 .8-3.4 5-20.3 6.9-28.1 .6-2.5 .3-4.7-1.7-7.1-10.1-12.5-18.3-35.3-18.3-56.6 0-54.7 41.4-107.6 112-107.6 60.9 0 103.6 41.5 103.6 100.9 0 67.1-33.9 113.6-78 113.6-24.3 0-42.6-20.1-36.7-44.8 7-29.5 20.5-61.3 20.5-82.6 0-19-10.2-34.9-31.4-34.9-24.9 0-44.9 25.7-44.9 60.2 0 22 7.4 36.8 7.4 36.8s-24.5 103.8-29 123.2c-5 21.4-3 51.6-.9 71.2C65.4 450.9 0 361.1 0 256 0 119 111 8 248 8s248 111 248 248z"></path>
                                                    </svg>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('post_detail', [$post->category?->slug ?? 'uncategorized', $post->slug])) }}" target="_blank">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="currentColor">
                                                        <path d="M416 32H31.9C14.3 32 0 46.5 0 64.3v383.4C0 465.5 14.3 480 31.9 480H416c17.6 0 32-14.5 32-32.3V64.3c0-17.8-14.4-32.3-32-32.3zM135.4 416H69V202.2h66.5V416zm-33.2-243c-21.3 0-38.5-17.3-38.5-38.5S80.9 96 102.2 96c21.2 0 38.5 17.3 38.5 38.5 0 21.3-17.2 38.5-38.5 38.5zm282.1 243h-66.4V312c0-24.8-.5-56.7-34.5-56.7-34.6 0-39.9 27-39.9 54.9V416h-66.4V202.2h63.7v29.2h.9c8.9-16.8 30.6-34.5 62.9-34.5 67.2 0 79.7 44.3 79.7 101.9V416z"></path>
                                                    </svg>
                                                </a>
                                            </li>
                                        </ul>
                                        <!--/.social-share-->
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </article>
            @endforeach

            {{-- Vertical Carousel dengan $randomNews --}}
            <div class="post-layout-item">
                <div class="swiper vartical-post-carousel">
                    <div class="swiper-wrapper">
                        @foreach ($randomNews as $post)
                            <div class="swiper-slide">
                                <article class="horizontal-post-card img-hover-move">
                                    <a href="{{ route('post_detail', [$post->category?->slug ?? 'uncategorized', $post->slug]) }}" class="post-thumb media">
                                        <img src="{{ $post->image ? getFile($post->image) : asset('assets/default.jpg') }}" alt="{{ $post->title }}">
                                    </a>
                                    <div class="post-content">
                                        <ul class="post-meta">
                                            <li>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" height="24" viewBox="0 -960 960 960" width="24">
                                                    <path d="M488.768-117.847Q470.922-100.001 446-100.001t-42.768-17.846l-286-286q-17.231-17.231-17.038-42.653.192-25.422 17.807-43.037l352-352.616q8.317-8.179 19.658-13.012 11.341-4.834 23.726-4.834h286q24.537 0 42.268 17.731 17.73 17.73 17.73 42.268v286q0 12.826-4.961 24.143-4.962 11.318-13.654 20.01l-352 352Zm210.571-532.154q20.815 0 35.43-14.57 14.615-14.57 14.615-35.384t-14.57-35.429q-14.57-14.615-35.384-14.615t-35.429 14.57q-14.616 14.57-14.616 35.384t14.57 35.429q14.57 14.615 35.384 14.615ZM446.172-160l353.213-354v-286H513.212L160-446l286.172 286Zm353.213-640Z"></path>
                                                </svg>
                                                <a href="{{ route('category', $post->category?->slug ?? 'uncategorized') }}" class="post-meta-text">{{ $post->category?->name ?? 'Uncategorized' }}</a>
                                            </li>
                                        </ul>
                                        <h3>
                                            <a href="{{ route('post_detail', [$post->category?->slug ?? 'uncategorized', $post->slug]) }}" class="text-hover">{{ Str::limit($post->title, 50) }}</a>
                                        </h3>
                                        @if(is_array($post->tags) && count($post->tags))
                                            <div class="article-tags">
                                @foreach(array_slice($post->tags, 0, 2) as $tag)
                                    <a href="{{ route('tag', Str::slug($tag)) }}" class="article-tags a">#{{ $tag }}</a>
                                @endforeach
                                            </div>
                                        @endif
                                        <ul class="author-info">
                                            <li>
                                                <a href="{{ route('author', $post->createdBy?->slug ?? '#') }}">{{ $post->createdBy?->name ?? 'Unknown' }}</a>
                                                <a href="#">{{ $post->published_at?->format('d.m.Y') }}</a>
                                            </li>
                                        </ul>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <!--/.vartical-post-carousel-->
        </div>
    </div>
</div>

<section class="main-post-area padding">
    <div class="container">
        <div class="row gy-5 gy-lg-0 main-area">
            <div class="col-lg-8">
                <div class="main-post-wrap">
                    {{-- Section Header --}}
                    <div class="section-heading mb-4">
                        <h3>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                <path d="M212.309-140.001q-30.308 0-51.308-21t-21-51.308v-535.382q0-30.308 21-51.308t51.308-21h419.229l188.461 188.461v419.229q0 30.308-21 51.308t-51.308 21H212.309Zm0-59.999h535.382q5.385 0 8.847-3.462 3.462-3.462 3.462-8.847V-600H600v-160H212.309q-5.385 0-8.847 3.462-3.462 3.462-3.462 8.847v535.382q0 5.385 3.462 8.847 3.462 3.462 8.847 3.462Zm77.692-100.001h379.998V-360H290.001v59.999Zm0-299.999H480v-59.999H290.001V-600Zm0 149.999h379.998v-59.998H290.001v59.998ZM200-760v160-160 560V-760Z" />
                            </svg>
                            <span>Artikel Terbaru</span>
                        </h3>
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
                                    @else
                                        <div class="post-thumb media">
                                            <a href="{{ route('post_detail', [$article->category?->slug ?? 'uncategorized', $article->slug]) }}">
                                                <img src="{{ asset('assets/default.jpg') }}" alt="{{ $article->title }}">
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
                                        @if(is_array($article->tags) && count($article->tags))
                                            <div class="article-tags">
                                                @foreach(array_slice($article->tags, 0, 3) as $tag)
                                                    <a href="{{ route('tag', Str::slug($tag)) }}" class="article-tags a">#{{ $tag }}</a>
                                                @endforeach
                                            </div>
                                        @endif
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
                    <div class="main-post-wrap">
                        {{-- Section Header --}}
                        <div class="section-heading mb-4">
                            <h3>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="M212.309-140.001q-30.308 0-51.308-21t-21-51.308v-535.382q0-30.308 21-51.308t51.308-21h419.229l188.461 188.461v419.229q0 30.308-21 51.308t-51.308 21H212.309Zm0-59.999h535.382q5.385 0 8.847-3.462 3.462-3.462 3.462-8.847V-600H600v-160H212.309q-5.385 0-8.847 3.462-3.462 3.462-3.462 8.847v535.382q0 5.385 3.462 8.847 3.462 3.462 8.847 3.462Zm77.692-100.001h379.998V-360H290.001v59.999Zm0-299.999H480v-59.999H290.001V-600Zm0 149.999h379.998v-59.998H290.001v59.998ZM200-760v160-160 560V-760Z" />
                                </svg>
                                <span>Hikmah</span>
                            </h3>
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
                                        @if(is_array($article->tags) && count($article->tags))
                                            <div class="article-tags">
                                                @foreach(array_slice($article->tags, 0, 3) as $tag)
                                                    <a href="{{ route('tag', Str::slug($tag)) }}" class="article-tags a">#{{ $tag }}</a>
                                                @endforeach
                                            </div>
                                        @endif
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
                    <div class="main-post-wrap">
                        {{-- Section Header --}}
                        <div class="section-heading mb-4">
                            <h3>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="M212.309-140.001q-30.308 0-51.308-21t-21-51.308v-535.382q0-30.308 21-51.308t51.308-21h419.229l188.461 188.461v419.229q0 30.308-21 51.308t-51.308 21H212.309Zm0-59.999h535.382q5.385 0 8.847-3.462 3.462-3.462 3.462-8.847V-600H600v-160H212.309q-5.385 0-8.847 3.462-3.462 3.462-3.462 8.847v535.382q0 5.385 3.462 8.847 3.462 3.462 8.847 3.462Zm77.692-100.001h379.998V-360H290.001v59.999Zm0-299.999H480v-59.999H290.001V-600Zm0 149.999h379.998v-59.998H290.001v59.998ZM200-760v160-160 560V-760Z" />
                                </svg>
                                <span>AmAzing</span>
                            </h3>
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
                                        @if(is_array($article->tags) && count($article->tags))
                                            <div class="article-tags">
                                                @foreach(array_slice($article->tags, 0, 3) as $tag)
                                                    <a href="{{ route('tag', Str::slug($tag)) }}" class="article-tags a">#{{ $tag }}</a>
                                                @endforeach
                                            </div>
                                        @endif
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
                    <div class="main-post-wrap">
                        {{-- Section Header --}}
                        <div class="section-heading mb-4">
                            <h3>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="M212.309-140.001q-30.308 0-51.308-21t-21-51.308v-535.382q0-30.308 21-51.308t51.308-21h419.229l188.461 188.461v419.229q0 30.308-21 51.308t-51.308 21H212.309Zm0-59.999h535.382q5.385 0 8.847-3.462 3.462-3.462 3.462-8.847V-600H600v-160H212.309q-5.385 0-8.847 3.462-3.462 3.462-3.462 8.847v535.382q0 5.385 3.462 8.847 3.462 3.462 8.847 3.462Zm77.692-100.001h379.998V-360H290.001v59.999Zm0-299.999H480v-59.999H290.001V-600Zm0 149.999h379.998v-59.998H290.001v59.998ZM200-760v160-160 560V-760Z" />
                                </svg>
                                <span>Marketing</span>
                            </h3>
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
                                        @if(is_array($article->tags) && count($article->tags))
                                            <div class="article-tags">
                                                @foreach(array_slice($article->tags, 0, 3) as $tag)
                                                    <a href="{{ route('tag', Str::slug($tag)) }}" class="article-tags a">#{{ $tag }}</a>
                                                @endforeach
                                            </div>
                                        @endif
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
                    <div class="main-post-wrap">
                        {{-- Section Header --}}
                        <div class="section-heading mb-4">
                            <h3>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="M212.309-140.001q-30.308 0-51.308-21t-21-51.308v-535.382q0-30.308 21-51.308t51.308-21h419.229l188.461 188.461v419.229q0 30.308-21 51.308t-51.308 21H212.309Zm0-59.999h535.382q5.385 0 8.847-3.462 3.462-3.462 3.462-8.847V-600H600v-160H212.309q-5.385 0-8.847 3.462-3.462 3.462-3.462 8.847v535.382q0 5.385 3.462 8.847 3.462 3.462 8.847 3.462Zm77.692-100.001h379.998V-360H290.001v59.999Zm0-299.999H480v-59.999H290.001V-600Zm0 149.999h379.998v-59.998H290.001v59.998ZM200-760v160-160 560V-760Z" />
                                </svg>
                                <span>Branding</span>
                            </h3>
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
                                        @if(is_array($article->tags) && count($article->tags))
                                            <div class="article-tags">
                                                @foreach(array_slice($article->tags, 0, 3) as $tag)
                                                    <a href="{{ route('tag', Str::slug($tag)) }}" class="article-tags a">#{{ $tag }}</a>
                                                @endforeach
                                            </div>
                                        @endif
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
                                </div>
                                    <div class="widget-post-content">
                                        <h3>
                                            <a href="{{ route('post_detail', [$popular->category->slug, $popular->slug]) }}" class="text-hover">{{ Str::limit($popular->title, 50) }}</a>
                                        </h3>
                                        @if(is_array($popular->tags) && count($popular->tags))
                                            <div class="article-tags">
                                                @foreach(array_slice($popular->tags, 0, 2) as $tag)
                                                    <a href="{{ route('tag', Str::slug($tag)) }}" class="article-tags a">#{{ $tag }}</a>
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

                    <!--Sidebar Category-->
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
                                @if ($information)
                                    <div class="widget-post-item img-hover-move">
                                        {{-- link information--}}
                                        @foreach ($information as $data)
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
                                                    <a href="single.html" class="text-hover">{{ $data->title }}</a>
                                                </h3>
                                            </div>
                                        @endforeach
                                        {{-- link information --}}
                                    </div>
                                @endif
                               
                            </div>
                        </div>
                    
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

    var verticalSwiper = new Swiper('.vartical-post-carousel', {
        direction: 'vertical',
        slidesPerView: 3,
        spaceBetween: 20,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        loop: true,
        breakpoints: {
            768: {
                slidesPerView: 4,
            },
            1024: {
                slidesPerView: 5,
            }
        }
    });

    var featuredSwiper = new Swiper('.featured-post-carousel', {
        slidesPerView: 1,
        spaceBetween: 30,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        loop: true,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
</script>
@endpush



