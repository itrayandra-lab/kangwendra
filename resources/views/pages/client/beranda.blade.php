@extends('layouts.client.app')

@section('content')
    <section class="featured-post">
        <div class="featured-post-wrap">
            <div class="swiper featured-post-carousel">
                <div class="swiper-wrapper">
                    @php $slideCount = 0; @endphp
                    @foreach ($slide as $item)
                        @php 
                            $slideCount++;
                            $slideClass = match($slideCount) {
                                1 => 'slide-size-m',
                                2 => 'slide-size-xl', 
                                3 => 'slide-size-xs',
                                4 => 'slide-size-l',
                                default => 'slide-size-s'
                            };
                        @endphp
                        <div class="swiper-slide {{ $slideClass }} dl-drag-cursor">
                            <div class="featured-post-card img-hover-scale {{ !$item->image ? 'no-image' : '' }}">
                                <div class="post-card-inner">
                                    @if($item->image)
                                        <div class="featured-post-thumb media">
                                            <a href="/{{ $item->category?->slug ?? 'news' }}" class="featured-post-cat">{{ $item->category?->name ?? 'Berita' }}</a>
                                            <img src="{{ getFile($item->image) }}" alt="{{ $item->title }}">
                                        </div>
                                    @else
                                        <div class="featured-post-no-image">
                                            <a href="/{{ $item->category?->slug ?? 'news' }}" class="featured-post-cat">{{ $item->category?->name ?? 'Berita' }}</a>
                                        </div>
                                    @endif
                                    <div class="featured-post-info">
                                        <div class="featured-post-meta">
                                            <div class="author-thumb">
                                                <a href="/author/{{ $item->createdBy?->slug ?? '#' }}">
                                                    <img src="{{ $item->createdBy?->image ? getFile($item->createdBy->image) : asset('client/assets/img/author-1.jpg') }}" alt="author">
                                                </a>
                                            </div>
                                            <ul class="post-meta">
                                                <li><a href="/author/{{ $item->createdBy?->slug ?? '#' }}">{{ $item->createdBy?->name ?? 'Penulis' }}</a></li>
                                                <li class="sep"></li>
                                                <li><a class="date" href="/{{ $item->category?->slug ?? 'news' }}">{{ $item->published_at ? \Carbon\Carbon::parse($item->published_at)->format('d.m.Y') : date('d.m.Y') }}</a></li>
                                            </ul>
                                        </div>
                                        <h2><a href="/{{ $item->category?->slug ?? 'news' }}/{{ $item->slug }}" class="text-hover">{{ $item->title }}</a></h2>
                                        <a href="/{{ $item->category?->slug ?? 'news' }}/{{ $item->slug }}" class="read-more">Baca Selengkapnya</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <div class="post-layout bg-light-red padding">
        <div class="container">
            <div class="post-layout-1">
                @foreach($latestNews->take(6) as $item)
                    <article class="post-layout-item img-hover-move {{ !$item->image ? 'no-image' : '' }}">
                        @if($item->image)
                            <a href="/{{ $item->category?->slug ?? 'news' }}/{{ $item->slug }}" class="post-thumb media">
                                <img src="{{ getFile($item->image) }}" alt="{{ $item->title }}">
                            </a>
                        @endif
                        <div class="post-content">
                            <ul class="post-meta">
                                <li class="reading-time">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" height="24" viewBox="0 -960 960 960" width="24">
                                        <path d="m618.924-298.924 42.152-42.152-151.077-151.087V-680h-59.998v212.154l168.923 168.922ZM480.067-100.001q-78.836 0-148.204-29.92-69.369-29.92-120.682-81.21-51.314-51.291-81.247-120.629-29.933-69.337-29.933-148.173t29.92-148.204q29.92-69.369 81.21-120.682 51.291-51.314 120.629-81.247 69.337-29.933 148.173-29.933t148.204 29.92q69.369 29.92 120.682 81.21 51.314 51.291 81.247 120.629 29.933 69.337 29.933 148.173t-29.92 148.204q-29.92 69.369-81.21 120.682-51.291 51.314-120.629 81.247-69.337 29.933-148.173 29.933ZM480-480Zm0 320q133 0 226.5-93.5T800-480q0-133-93.5-226.5T480-800q-133 0-226.5 93.5T160-480q0 133 93.5 226.5T480-160Z"/>
                                    </svg>
                                    <span class="post-meta-text">{{ rand(5, 30) }} Menit</span>
                                </li>
                                <li>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" height="24" viewBox="0 -960 960 960" width="24">
                                        <path d="M488.768-117.847Q470.922-100.001 446-100.001t-42.768-17.846l-286-286q-17.231-17.231-17.038-42.653.192-25.422 17.807-43.037l352-352.616q8.317-8.179 19.658-13.012 11.341-4.834 23.726-4.834h286q24.537 0 42.268 17.731 17.73 17.73 17.73 42.268v286q0 12.826-4.961 24.143-4.962 11.318-13.654 20.01l-352 352Zm210.571-532.154q20.815 0 35.43-14.57 14.615-14.57 14.615-35.384t-14.57-35.429q-14.57-14.615-35.384-14.615t-35.429 14.57q-14.616 14.57-14.616 35.384t14.57 35.429q14.57 14.615 35.384 14.615ZM446.172-160l353.213-354v-286H513.212L160-446l286.172 286Zm353.213-640Z"/>
                                    </svg>
                                    <a href="/{{ $item->category?->slug ?? 'news' }}" class="post-meta-text">{{ $item->category?->name ?? 'Berita' }}</a>
                                </li>
                            </ul>
                            <h3><a href="/{{ $item->category?->slug ?? 'news' }}/{{ $item->slug }}" class="text-hover">{{ $item->title }}</a></h3>
                            @if($loop->iteration == 3)
                                <p>{{ Str::limit(strip_tags($item->content), 150) }}</p>
                            @endif
                            <ul class="author-info">
                                <li>
                                    <a href="/author/{{ $item->createdBy?->slug ?? '#' }}">
                                        <img src="{{ $item->createdBy?->image ? getFile($item->createdBy->image) : asset('client/assets/img/author-1.jpg') }}" alt="author">
                                    </a>
                                </li>
                                <li>
                                    <a href="/author/{{ $item->createdBy?->slug ?? '#' }}">{{ $item->createdBy?->name ?? 'Penulis' }}</a>
                                    <a href="/{{ $item->category?->slug ?? 'news' }}">{{ $item->published_at ? \Carbon\Carbon::parse($item->published_at)->format('d.m.Y') : date('d.m.Y') }}</a>
                                </li>
                            </ul>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>

    <section class="main-post-area padding">
        <div class="container">
            <div class="row gy-5 gy-lg-0 main-area">
                <div class="col-lg-8">
                    <div class="main-post-wrap">
                        <div class="post-wrap-heading">
                            <h3>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="M420.001-143.082v-276.919H307.694v-439.998H653.46l-73.461 255.768h158.076L420.001-143.082Z"></path>
                                </svg>
                                <span>Artikel Terbaru</span>
                            </h3>
                        </div>
                        <div class="row gy-4">
                            @foreach($latestNews->take(6) as $item)
                                <article class="col-md-6">
                                    <div class="post-card img-hover-move {{ !$item->image ? 'no-image' : '' }}">
                                        @if($item->image)
                                            <div class="post-thumb media">
                                                <a href="/{{ $item->category?->slug ?? 'news' }}/{{ $item->slug }}">
                                                    <img src="{{ getFile($item->image) }}" alt="{{ $item->title }}">
                                                </a>
                                            </div>
                                        @endif
                                        <div class="post-content">
                                            <ul class="post-meta">
                                                <li><a href="/{{ $item->category?->slug ?? 'news' }}">{{ $item->category?->name ?? 'Berita' }}</a></li>
                                                <li class="sep"></li>
                                                <li><a href="/{{ $item->category?->slug ?? 'news' }}" class="date">{{ $item->published_at ? \Carbon\Carbon::parse($item->published_at)->format('d.m.Y') : date('d.m.Y') }}</a></li>
                                            </ul>
                                            <h3><a href="/{{ $item->category?->slug ?? 'news' }}/{{ $item->slug }}" class="text-hover">{{ $item->title }}</a></h3>
                                            <p>{{ Str::limit(strip_tags($item->content), 120) }}</p>
                                            <ul class="post-card-footer">
                                                <li><a href="/{{ $item->category?->slug ?? 'news' }}/{{ $item->slug }}" class="read-more">Lanjut Baca</a></li>
                                                <li>
                                                    <a href="#" class="views">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                                            <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T832-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"/>
                                                        </svg>
                                                        <span>{{ $item->counter ?? 0 }}</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="main-sidebar">
                        <!-- Most Popular Posts -->
                        <div class="sidebar-widget">
                            <div class="widget-heading">
                                <h3>Terpopuler</h3>
                            </div>
                            <div class="widget-content">
                                @foreach($mostPopular->take(5) as $item)
                                    <div class="sidebar-post {{ !$item->image ? 'no-image' : '' }}">
                                        @if($item->image)
                                            <div class="post-thumb">
                                                <a href="/{{ $item->category?->slug ?? 'news' }}/{{ $item->slug }}">
                                                    <img src="{{ getFile($item->image) }}" alt="{{ $item->title }}">
                                                </a>
                                            </div>
                                        @endif
                                        <div class="post-content">
                                            <ul class="post-meta">
                                                <li><a href="/{{ $item->category?->slug ?? 'news' }}">{{ $item->category?->name ?? 'Berita' }}</a></li>
                                                <li class="sep"></li>
                                                <li><a href="#" class="date">{{ $item->published_at ? \Carbon\Carbon::parse($item->published_at)->format('d.m.Y') : date('d.m.Y') }}</a></li>
                                            </ul>
                                            <h4><a href="/{{ $item->category?->slug ?? 'news' }}/{{ $item->slug }}" class="text-hover">{{ $item->title }}</a></h4>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                       
                        <!-- Tags -->
                        @if($tags->count() > 0)
                            <div class="sidebar-widget">
                                <div class="widget-heading">
                                    <h3>Tag Populer</h3>
                                </div>
                                <div class="widget-content">
                                    <div class="tag-list">
                                        @foreach($tags->take(10) as $tag)
                                            <a href="/tag/{{ $tag->slug }}" class="tag-item">{{ $tag->name }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <style>
        .views {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }
        
        .views:hover {
            color: #ff6b35;
            text-decoration: none;
        }
        
        .views svg {
            width: 18px;
            height: 18px;
        }
        
        /* No Image Styles */
        .featured-post-card.no-image {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }
        
        .featured-post-no-image {
            position: relative;
            height: 200px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }
        
        .featured-post-no-image::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            background: #dee2e6;
            border-radius: 50%;
            opacity: 0.5;
        }
        
        .featured-post-no-image::after {
            content: '📰';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            opacity: 0.7;
        }
        
        .post-layout-item.no-image {
            display: flex;
            flex-direction: column;
        }
        
        .post-layout-item.no-image .post-content {
            flex: 1;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #e9ecef;
        }
        
        .post-card.no-image {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }
        
        .post-card.no-image .post-content {
            padding: 25px;
        }
        
        .sidebar-post.no-image {
            display: block;
        }
        
        .sidebar-post.no-image .post-content {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
    </style>
    <script src="{{ asset('client/assets/js/swiper.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Featured Post Carousel
            const featuredCarousel = new Swiper('.featured-post-carousel', {
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                speed: 1000,
                grabCursor: true,
                spaceBetween: 20,
                breakpoints: {
                    0: {
                        slidesPerView: 1,
                    },
                    768: {
                        slidesPerView: 2,
                    },
                    1024: {
                        slidesPerView: 3,
                    },
                    1200: {
                        slidesPerView: 4,
                    }
                }
            });
        });
    </script>
@endpush
