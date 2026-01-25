@extends('layouts.client.app')

@section('content')
    <section class="page-header">
        <div class="container">
            <div class="page-content-wrap">
                <div class="page-content">
                    <h4>Informasi</h4>
                    <h2>{{ \Carbon\Carbon::now()->locale('en')->translatedFormat('F.d.Y') }} <span>{{ $info->total() }} Info</span></h2>
                    <p>Kumpulan informasi penting dan terkini untuk Anda.</p>
                </div>
            </div>
        </div>
    </section>

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
                                <span>Informasi Terbaru</span>
                            </h3>
                        </div>
                        
                        @if($info->count() > 0)
                            <div class="blog-list-wrap">
                                @foreach($info as $item)
                                    <article class="post-card list-post img-hover-move {{ !$item->image ? 'no-image' : '' }}">
                                        @if($item->image)
                                            <div class="post-thumb media">
                                                <a href="/info/{{ $item->slug }}">
                                                    <img src="{{ getFile($item->image) }}" alt="{{ $item->title }}">
                                                </a>
                                            </div>
                                        @endif
                                        <div class="post-content">
                                            <ul class="post-meta">
                                                <li><a href="/info">Informasi</a></li>
                                                <li class="sep"></li>
                                                <li><a href="/info" class="date">{{ $item->published_at ? \Carbon\Carbon::parse($item->published_at)->format('d.m.Y') : date('d.m.Y') }}</a></li>
                                            </ul>
                                            <h3><a href="/info/{{ $item->slug }}" class="text-hover">{{ $item->title }}</a></h3>
                                            <p>{{ Str::limit(strip_tags($item->content ?? $item->description ?? ''), 200) }}</p>
                                            <ul class="post-card-footer">
                                                <li><a href="/info/{{ $item->slug }}" class="read-more">Baca Selengkapnya</a></li>
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
                                    </article>
                                @endforeach
                            </div>
                            
                            @if($info->hasPages())
                                <div class="pagination-wrap">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination">
                                            @if ($info->onFirstPage())
                                                <li class="page-item disabled">
                                                    <span class="page-link">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                                            <path d="M400-80 0-480l400-400 71 71-329 329 329 329-71 71Z"/>
                                                        </svg>
                                                    </span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $info->previousPageUrl() }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                                            <path d="M400-80 0-480l400-400 71 71-329 329 329 329-71 71Z"/>
                                                        </svg>
                                                    </a>
                                                </li>
                                            @endif

                                            @foreach ($info->getUrlRange(1, $info->lastPage()) as $page => $url)
                                                @if ($page == $info->currentPage())
                                                    <li class="page-item active">
                                                        <span class="page-link">{{ $page }}</span>
                                                    </li>
                                                @else
                                                    <li class="page-item">
                                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                                    </li>
                                                @endif
                                            @endforeach

                                            @if ($info->hasMorePages())
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $info->nextPageUrl() }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                                                        </svg>
                                                    </a>
                                                </li>
                                            @else
                                                <li class="page-item disabled">
                                                    <span class="page-link">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                                                        </svg>
                                                    </span>
                                                </li>
                                            @endif
                                        </ul>
                                    </nav>
                                </div>
                            @endif
                        @else
                            <div class="no-posts-found">
                                <div class="text-center py-5">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="48" viewBox="0 -960 960 960" width="48" fill="currentColor" class="mb-3">
                                        <path d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/>
                                    </svg>
                                    <h4>Tidak Ada Informasi</h4>
                                    <p class="text-muted">Belum ada informasi yang tersedia.</p>
                                    <a href="{{ url('/') }}" class="default-btn">Kembali ke Beranda</a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="main-sidebar">
                        @php $latestInfo = App\Models\Info::where('status', 'active')->latest('published_at')->take(5)->get(); @endphp
                        @if($latestInfo->count() > 0)
                            <div class="sidebar-widget">
                                <div class="widget-heading">
                                    <h3>Info Terbaru</h3>
                                </div>
                                <div class="widget-content">
                                    @foreach($latestInfo as $item)
                                        <div class="sidebar-post {{ !$item->image ? 'no-image' : '' }}">
                                            @if($item->image)
                                                <div class="post-thumb">
                                                    <a href="/info/{{ $item->slug }}">
                                                        <img src="{{ getFile($item->image) }}" alt="{{ $item->title }}">
                                                    </a>
                                                </div>
                                            @endif
                                            <div class="post-content">
                                                <ul class="post-meta">
                                                    <li><a href="/info">Informasi</a></li>
                                                    <li class="sep"></li>
                                                    <li><a href="#" class="date">{{ $item->published_at ? \Carbon\Carbon::parse($item->published_at)->format('d.m.Y') : date('d.m.Y') }}</a></li>
                                                </ul>
                                                <h4><a href="/info/{{ $item->slug }}" class="text-hover">{{ $item->title }}</a></h4>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        @php $allCategories = App\Models\PostCategory::withCount('posts')->having('posts_count', '>', 0)->take(8)->get(); @endphp
                        @if($allCategories->count() > 0)
                            <div class="sidebar-widget">
                                <div class="widget-heading">
                                    <h3>Kategori</h3>
                                </div>
                                <div class="widget-content">
                                    <ul class="category-list">
                                        @foreach($allCategories as $cat)
                                            <li>
                                                <a href="/{{ $cat->slug }}">
                                                    {{ $cat->name }}
                                                    <span>({{ $cat->posts_count }})</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
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
    
    .blog-list-wrap {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }
    
    .post-card.list-post {
        display: flex;
        gap: 20px;
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        padding: 20px;
    }
    
    .post-card.list-post:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    
    .post-card.list-post .post-thumb {
        flex-shrink: 0;
        width: 200px;
        height: 150px;
        overflow: hidden;
        border-radius: 8px;
    }
    
    .post-card.list-post .post-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .post-card.list-post .post-thumb:hover img {
        transform: scale(1.1);
    }
    
    .post-card.list-post .post-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    
    .post-meta {
        list-style: none;
        padding: 0;
        margin: 0 0 10px 0;
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
        font-size: 1.4rem;
        line-height: 1.4;
        margin: 0 0 10px 0;
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
        margin: 0 0 15px 0;
        flex: 1;
    }
    
    .post-card-footer {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
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
    
    .post-card-footer .views {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #666;
        text-decoration: none;
        font-size: 14px;
        transition: color 0.3s ease;
    }
    
    .post-card-footer .views:hover {
        color: #333;
    }
    
    .post-card-footer .views svg {
        width: 18px;
        height: 18px;
    }
    
    .sidebar-post {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    
    .sidebar-post:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .sidebar-post .post-thumb {
        flex-shrink: 0;
        width: 80px;
        height: 60px;
        overflow: hidden;
        border-radius: 5px;
    }
    
    .sidebar-post .post-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .sidebar-post .post-thumb:hover img {
        transform: scale(1.1);
    }
    
    .sidebar-post .post-content {
        flex: 1;
    }
    
    .sidebar-post .post-content h4 {
        font-size: 14px;
        line-height: 1.4;
        margin: 5px 0 0 0;
    }
    
    .sidebar-post .post-content h4 a {
        color: #333;
        text-decoration: none;
    }
    
    .sidebar-post .post-content h4 a:hover {
        color: #333;
    }
    
    .sidebar-post .post-meta {
        font-size: 12px;
        margin: 0 0 5px 0;
    }
    
    .sidebar-post .post-meta li {
        color: #666;
    }
    
    .sidebar-post .post-meta a {
        color: #666;
        text-decoration: none;
    }
    
    .sidebar-post .post-meta a:hover {
        color: #333;
    }
    
    .category-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .category-list li {
        margin-bottom: 10px;
    }
    
    .category-list a {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        background: #f8f9fa;
        color: #666;
        text-decoration: none;
        border-radius: 5px;
        transition: all 0.3s ease;
    }
    
    .category-list a:hover {
        background: #333;
        color: #fff;
        text-decoration: none;
    }
    
    .category-list span {
        font-size: 12px;
        opacity: 0.8;
    }
    
    .pagination-wrap {
        margin-top: 40px;
        text-align: center;
    }
    
    .pagination {
        display: inline-flex;
        gap: 5px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    .page-item .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border: 1px solid #ddd;
        color: #666;
        text-decoration: none;
        border-radius: 5px;
        transition: all 0.3s ease;
    }
    
    .page-item.active .page-link {
        background: #333;
        border-color: #333;
        color: #fff;
    }
    
    .page-item:not(.disabled) .page-link:hover {
        background: #333;
        border-color: #333;
        color: #fff;
    }
    
    .page-item.disabled .page-link {
        color: #ccc;
        cursor: not-allowed;
    }
    
    .page-link svg {
        width: 18px;
        height: 18px;
    }
    
    .no-posts-found {
        text-align: center;
        padding: 60px 20px;
        background: #f8f9fa;
        border-radius: 10px;
        margin: 20px 0;
    }
    
    .no-posts-found svg {
        color: #ccc;
        margin-bottom: 20px;
    }
    
    .no-posts-found h4 {
        color: #666;
        margin-bottom: 10px;
    }
    
    .no-posts-found p {
        color: #999;
        margin-bottom: 20px;
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
    .post-card.list-post.no-image {
        display: block;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
    }
    
    .post-card.list-post.no-image .post-content {
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
    
    @media (max-width: 768px) {
        .page-content h2 {
            font-size: 2rem;
        }
        
        .post-card.list-post {
            flex-direction: column;
            gap: 15px;
        }
        
        .post-card.list-post .post-thumb {
            width: 100%;
            height: 200px;
        }
        
        .pagination {
            justify-content: center;
            flex-wrap: wrap;
        }
    }
</style>
@endpush
