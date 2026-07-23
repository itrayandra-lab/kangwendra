@extends('layouts.client.app')

@section('content')
    <section class="page-header author-page">
        <div class="container">
            <div class="page-content-wrap">
                <div class="page-content mx-auto text-center">
                    <h4>{{ $author->role ?? 'Penulis' }}</h4>
                    <h2 class="justify-content-center">{{ $author->name }}</h2>
                    <p>{{ $author->bio ?? 'Penulis yang berpengalaman dalam dunia jurnalistik dan penulisan artikel.' }}</p>
                </div>
                <div class="author-thumb">
                    <img src="{{ $author->image ? getFile($author->image) : asset('client/assets/img/author-widget.jpg') }}" alt="{{ $author->name }}">
                </div>
            </div>
        </div>
    </section>

    <div class="padding-top">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <div class="author-page-info text-center">
                        <p>{{ $author->description ?? 'Melalui cerita yang hidup, fotografi yang mendalam, dan komentar yang berwawasan, saya berusaha membawa Anda ke jantung setiap destinasi, menyalakan hasrat berkelana Anda dan menginspirasi petualangan Anda selanjutnya.' }}</p>
                        <ul class="social-list justify-content-center">
                            @if(!empty($author->facebook))
                            <li class="facebook">
                                <a href="{{ $author->facebook }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                                        <path d="M512 256C512 114.6 397.4 0 256 0S0 114.6 0 256C0 376 82.7 476.8 194.2 504.5V334.2H141.4V256h52.8V222.3c0-87.1 39.4-127.5 125-127.5c16.2 0 44.2 3.2 55.7 6.4V172c-6-.6-16.5-1-29.6-1c-42 0-58.2 15.9-58.2 57.2V256h83.6l-14.4 78.2H287V510.1C413.8 494.8 512 386.9 512 256h0z"></path>
                                    </svg>Facebook
                                </a>
                            </li>
                            @endif
                            @if(!empty($author->twitter))
                            <li class="twitter">
                                <a href="{{ $author->twitter }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                                        <path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path>
                                    </svg>Twitter
                                </a>
                            </li>
                            @endif
                            @if(!empty($author->instagram))
                            <li class="instagram">
                                <a href="{{ $author->instagram }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="currentColor">
                                        <path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"></path>
                                    </svg>Instagram
                                </a>
                            </li>
                            @endif
                            @if(!empty($author->tiktok))
                            <li class="tiktok">
                                <a href="{{ $author->tiktok }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="currentColor">
                                        <path d="M448 209.9a210.1 210.1 0 0 1 -122.8-39.3V349.4A162.6 162.6 0 1 1 185 188.3V278.2a74.6 74.6 0 1 0 52.2 71.2V0l88 0a121.2 121.2 0 0 0 1.9 22.2h0A122.2 122.2 0 0 0 381 102.4a121.4 121.4 0 0 0 67 20.1z"></path>
                                    </svg>Tiktok
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="archive-page padding">
        <div class="container">
            @if($posts->count() > 0)
                <div class="row gy-4">
                    @foreach($posts as $item)
                        <article class="col-lg-4 col-md-6">
                            <div class="post-card img-hover-move {{ !$item->image ? 'no-image' : '' }}">
                                @if($item->image)
                                    <div class="post-thumb media">
                                        <a href="/{{ $item->category?->slug ?? 'news' }}/{{ $item->slug }}">
                                            <img src="{{ getFile($item->image) }}" alt="{{ $item->title }}">
                                        </a>
                                    </div>
                                @else
                                    <div class="post-thumb media">
                                        <a href="/{{ $item->category?->slug ?? 'news' }}/{{ $item->slug }}">
                                            <img src="{{ asset('assets/default.jpg') }}" alt="{{ $item->title }}">
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
                                            <a href="#" class="comment">
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
                
                @if($posts->hasPages())
                    <ul class="pagination-wrap justify-content-center">
                        @if ($posts->onFirstPage())
                            <li><span class="disabled">
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="M400-80 0-480l400-400 71 71-329 329 329 329-71 71Z"/>
                                </svg>
                            </span></li>
                        @else
                            <li><a href="{{ $posts->previousPageUrl() }}">
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="M400-80 0-480l400-400 71 71-329 329 329 329-71 71Z"/>
                                </svg>
                            </a></li>
                        @endif

                        @foreach ($posts->getUrlRange(1, $posts->lastPage()) as $page => $url)
                            @if ($page == $posts->currentPage())
                                <li><a href="#" class="active">{{ $page }}</a></li>
                            @else
                                <li><a href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach

                        @if ($posts->hasMorePages())
                            <li><a href="{{ $posts->nextPageUrl() }}">
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="m553.846-253.847-42.153-43.384 152.77-152.77H180.001v-59.998h484.462l-152.77-152.77 42.153-43.384L779.999-480 553.846-253.847Z"/>
                                </svg>
                            </a></li>
                        @else
                            <li><span class="disabled">
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="m553.846-253.847-42.153-43.384 152.77-152.77H180.001v-59.998h484.462l-152.77-152.77 42.153-43.384L779.999-480 553.846-253.847Z"/>
                                </svg>
                            </span></li>
                        @endif
                    </ul>
                @endif
            @else
                <div class="no-posts-found text-center py-5">
                    <svg xmlns="http://www.w3.org/2000/svg" height="48" viewBox="0 -960 960 960" width="48" fill="currentColor" class="mb-3">
                        <path d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/>
                    </svg>
                    <h4>Tidak Ada Artikel</h4>
                    <p class="text-muted">Belum ada artikel dari penulis ini.</p>
                    <a href="{{ url('/') }}" class="default-btn">Kembali ke Beranda</a>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('styles')
<style>
    .page-header.author-page {
        background: #fff;
        padding: 80px 0 40px;
        color: #333;
        position: relative;
    }
    
    .page-content h4 {
        font-size: 1rem;
        font-weight: 500;
        margin-bottom: 10px;
        opacity: 0.8;
    }
    
    .page-content h2 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 15px;
    }
    
    .page-content p {
        font-size: 1.1rem;
        opacity: 0.8;
        max-width: 600px;
        margin: 0 auto;
    }
    
    .author-thumb {
        position: absolute;
        bottom: -60px;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 120px;
        border-radius: 50%;
        overflow: hidden;
        border: 5px solid #fff;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .author-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .author-page-info {
        padding: 40px 0;
    }
    
    .author-page-info p {
        font-size: 1.1rem;
        line-height: 1.8;
        color: #666;
        margin-bottom: 30px;
    }
    
    .social-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .social-list li a {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: #f8f9fa;
        color: #666;
        text-decoration: none;
        border-radius: 25px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .social-list li a svg {
        width: 18px;
        height: 18px;
    }
    
    .social-list .facebook a:hover {
        background: #1877f2;
        color: white;
    }
    
    .social-list .twitter a:hover {
        background: #1da1f2;
        color: white;
    }
    
    .social-list .instagram a:hover {
        background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%);
        color: white;
    }
    
    .social-list .tiktok a:hover {
        background: #000;
        color: white;
    }
    
    .archive-page {
        background: #fff;
    }
    
    .post-card {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .post-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    
    .post-thumb {
        position: relative;
        overflow: hidden;
        height: 250px;
    }
    
    .post-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .post-thumb:hover img {
        transform: scale(1.1);
    }
    
    .post-content {
        padding: 25px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .post-meta {
        list-style: none;
        padding: 0;
        margin: 0 0 15px 0;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
    }
    
    .post-meta li {
        color: #666;
    }
    
    .post-meta a {
        color: #666;
        text-decoration: none;
        font-weight: 500;
    }
    
    .post-meta a:hover {
        color: #333;
    }
    
    .post-meta .sep::before {
        content: "•";
        color: #ddd;
    }
    
    .post-content h3 {
        font-size: 1.25rem;
        line-height: 1.4;
        margin: 0 0 15px 0;
        font-weight: 600;
    }
    
    .post-content h3 a {
        color: #333;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .post-content h3 a:hover {
        color: #333;
    }
    
    .post-content p {
        color: #666;
        line-height: 1.6;
        margin: 0 0 20px 0;
        flex: 1;
    }
    
    .post-card-footer {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        border-top: 1px solid #eee;
    }
    
    .post-card-footer .read-more {
        color: #666;
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
        transition: color 0.3s ease;
    }
    
    .post-card-footer .read-more:hover {
        color: #333;
    }
    
    .post-card-footer .comment {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #666;
        text-decoration: none;
        font-size: 14px;
        transition: color 0.3s ease;
    }
    
    .post-card-footer .comment:hover {
        color: #333;
    }
    
    .post-card-footer .comment svg {
        width: 18px;
        height: 18px;
    }
    
    .pagination-wrap {
        list-style: none;
        padding: 0;
        margin: 50px 0 0 0;
        display: flex;
        gap: 10px;
    }
    
    .pagination-wrap li a,
    .pagination-wrap li span {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        height: 45px;
        border: 1px solid #ddd;
        color: #666;
        text-decoration: none;
        border-radius: 5px;
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    .pagination-wrap li a:hover,
    .pagination-wrap li a.active {
        background: #333;
        border-color: #333;
        color: #fff;
    }
    
    .pagination-wrap li span.disabled {
        color: #ccc;
        cursor: not-allowed;
    }
    
    .pagination-wrap svg {
        width: 20px;
        height: 20px;
    }
    
    .no-posts-found {
        padding: 80px 20px;
        background: #f8f9fa;
        border-radius: 10px;
        margin: 40px 0;
    }
    
    .no-posts-found svg {
        color: #ccc;
        margin-bottom: 20px;
    }
    
    .no-posts-found h4 {
        color: #666;
        margin-bottom: 10px;
        font-size: 1.5rem;
    }
    
    .no-posts-found p {
        color: #999;
        margin-bottom: 25px;
    }
    
    .no-posts-found .default-btn {
        background: #333;
        color: #fff;
        padding: 12px 25px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .no-posts-found .default-btn:hover {
        background: #555;
        transform: translateY(-2px);
    }
    
    /* No Image Styles */
    .post-card.no-image {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
    }
    
    .post-card.no-image .post-content {
        padding: 25px;
    }
    
    @media (max-width: 768px) {
        .page-content h2 {
            font-size: 2rem;
        }
        
        .post-thumb {
            height: 200px;
        }
        
        .post-content {
            padding: 20px;
        }
        
        .pagination-wrap {
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .social-list {
            justify-content: center;
        }
    }
</style>
@endpush



