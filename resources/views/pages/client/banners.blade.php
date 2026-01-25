@extends('layouts.client.app')

@section('content')
    <section class="page-header">
        <div class="container">
            <div class="page-content-wrap">
                <div class="page-content">
                    <h4>Banner</h4>
                    <h2>{{ \Carbon\Carbon::now()->locale('en')->translatedFormat('F.d.Y') }} <span>{{ $banners->total() }} Banners</span></h2>
                    <p>Koleksi banner dan promosi terbaru dari berbagai kategori menarik.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="archive-page padding">
        <div class="container">
            @if($banners->count() > 0)
                <div class="row gy-4">
                    @foreach($banners as $item)
                        <article class="col-lg-4 col-md-6">
                            <div class="post-card img-hover-move {{ !$item->image ? 'no-image' : '' }}">
                                @if($item->image)
                                    <div class="post-thumb media">
                                        <a href="/banner/{{ $item->slug }}">
                                            <img src="{{ getFile($item->image) }}" alt="{{ $item->title }}">
                                        </a>
                                        <div class="banner-overlay">
                                            <div class="banner-content">
                                                <h3><a href="/banner/{{ $item->slug }}" class="text-hover">{{ $item->title }}</a></h3>
                                                <a href="/banner/{{ $item->slug }}" class="read-more">Lihat Detail</a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="post-content">
                                    <ul class="post-meta">
                                        <li><a href="#">Banner</a></li>
                                        <li class="sep"></li>
                                        <li><a href="#" class="date">{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d.m.Y') : date('d.m.Y') }}</a></li>
                                    </ul>
                                    <h3><a href="/banner/{{ $item->slug }}" class="text-hover">{{ $item->title }}</a></h3>
                                    <p>{{ Str::limit(strip_tags($item->content ?? $item->description ?? ''), 120) }}</p>
                                    <ul class="post-card-footer">
                                        <li><a href="/banner/{{ $item->slug }}" class="read-more">Lihat Detail</a></li>
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
                
                @if($banners->hasPages())
                    <ul class="pagination-wrap justify-content-center">
                        @if ($banners->onFirstPage())
                            <li><span class="disabled">
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="M400-80 0-480l400-400 71 71-329 329 329 329-71 71Z"/>
                                </svg>
                            </span></li>
                        @else
                            <li><a href="{{ $banners->previousPageUrl() }}">
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="M400-80 0-480l400-400 71 71-329 329 329 329-71 71Z"/>
                                </svg>
                            </a></li>
                        @endif

                        @foreach ($banners->getUrlRange(1, $banners->lastPage()) as $page => $url)
                            @if ($page == $banners->currentPage())
                                <li><a href="#" class="active">{{ $page }}</a></li>
                            @else
                                <li><a href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach

                        @if ($banners->hasMorePages())
                            <li><a href="{{ $banners->nextPageUrl() }}">
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
                    <h4>Tidak Ada Banner</h4>
                    <p class="text-muted">Belum ada banner yang tersedia.</p>
                    <a href="{{ url('/') }}" class="default-btn">Kembali ke Beranda</a>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('styles')
<style>
    .page-header {
        background: #fff;
        padding: 80px 0;
        color: #333;
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
    
    .page-content h2 span {
        font-size: 1.2rem;
        font-weight: 400;
        opacity: 0.7;
    }
    
    .page-content p {
        font-size: 1.1rem;
        opacity: 0.8;
        max-width: 600px;
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
    
    .banner-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.7) 100%);
        display: flex;
        align-items: flex-end;
        padding: 20px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .post-thumb:hover .banner-overlay {
        opacity: 1;
    }
    
    .banner-content h3 {
        color: white;
        font-size: 1.1rem;
        margin-bottom: 10px;
    }
    
    .banner-content h3 a {
        color: white;
        text-decoration: none;
    }
    
    .banner-content .read-more {
        color: white;
        background: rgba(255,255,255,0.2);
        padding: 8px 15px;
        border-radius: 20px;
        text-decoration: none;
        font-size: 12px;
        transition: all 0.3s ease;
    }
    
    .banner-content .read-more:hover {
        background: rgba(255,255,255,0.3);
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
    }
</style>
@endpush