@extends('layouts.client.app')

@section('content')
    <section class="page-header">
        <div class="container">
            <div class="page-content-wrap">
                <div class="page-content">
                    <h4>Album Foto</h4>
                    <h2>{{ \Carbon\Carbon::now()->locale('en')->translatedFormat('F.d.Y') }} <span>{{ $albums->total() }} Albums</span></h2>
                    <p>Koleksi foto dan galeri terbaru dari berbagai momen menarik.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="archive-page padding">
        <div class="container">
            @if($albums->count() > 0)
                @foreach($albums as $item)
                    <div class="album-section mb-5">
                        <div class="album-header mb-4">
                            <h3 class="album-title">{{ $item->name }}</h3>
                            @if($item->description)
                                <p class="album-description">{{ $item->description }}</p>
                            @endif
                        </div>
                        
                        @if($item->photos->count() > 0)
                            <div class="row gy-3">
                                @foreach($item->photos as $photo)
                                    <div class="col-lg-3 col-md-4 col-sm-6">
                                        <div class="gallery-item">
                                            <a href="{{ getFile($photo->image) }}" data-lightbox="album-{{ $item->id }}" data-title="{{ $item->name }}">
                                                <img src="{{ getFile($photo->image) }}" alt="{{ $item->name }}" class="gallery-image">
                                                <div class="gallery-overlay">
                                                    <div class="gallery-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                                            <path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm40-80h480L570-480 450-320l-90-120-120 160Zm-40 80v-560 560Z"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="no-photos text-center py-4">
                                <p class="text-muted">Belum ada foto dalam album ini.</p>
                            </div>
                        @endif
                    </div>
                @endforeach
                
                @if($albums->hasPages())
                    <ul class="pagination-wrap justify-content-center">
                        @if ($albums->onFirstPage())
                            <li><span class="disabled">
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="M400-80 0-480l400-400 71 71-329 329 329 329-71 71Z"/>
                                </svg>
                            </span></li>
                        @else
                            <li><a href="{{ $albums->previousPageUrl() }}">
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                    <path d="M400-80 0-480l400-400 71 71-329 329 329 329-71 71Z"/>
                                </svg>
                            </a></li>
                        @endif

                        @foreach ($albums->getUrlRange(1, $albums->lastPage()) as $page => $url)
                            @if ($page == $albums->currentPage())
                                <li><a href="#" class="active">{{ $page }}</a></li>
                            @else
                                <li><a href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach

                        @if ($albums->hasMorePages())
                            <li><a href="{{ $albums->nextPageUrl() }}">
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
                        <path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm40-80h480L570-480 450-320l-90-120-120 160Zm-40 80v-560 560Z"/>
                    </svg>
                    <h4>Tidak Ada Album</h4>
                    <p class="text-muted">Belum ada album foto yang tersedia.</p>
                    <a href="{{ url('/') }}" class="default-btn">Kembali ke Beranda</a>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
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
    
    .album-section {
        background: #fff;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        margin-bottom: 40px;
    }
    
    .album-header {
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 20px;
    }
    
    .album-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: #333;
        margin: 0 0 10px 0;
    }
    
    .album-description {
        color: #666;
        font-size: 1rem;
        margin: 0;
        line-height: 1.6;
    }
    
    .gallery-item {
        position: relative;
        overflow: hidden;
        border-radius: 10px;
        background: #f8f9fa;
        aspect-ratio: 1;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .gallery-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    
    .gallery-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .gallery-item:hover .gallery-image {
        transform: scale(1.1);
    }
    
    .gallery-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .gallery-item:hover .gallery-overlay {
        opacity: 1;
    }
    
    .gallery-icon {
        color: white;
        font-size: 2rem;
        transform: scale(0.8);
        transition: transform 0.3s ease;
    }
    
    .gallery-item:hover .gallery-icon {
        transform: scale(1);
    }
    
    .gallery-icon svg {
        width: 40px;
        height: 40px;
    }
    
    .no-photos {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 40px 20px;
        margin: 20px 0;
    }
    
    .no-photos p {
        color: #999;
        font-size: 1rem;
        margin: 0;
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
    
    @media (max-width: 768px) {
        .page-content h2 {
            font-size: 2rem;
        }
        
        .album-section {
            padding: 20px;
        }
        
        .album-title {
            font-size: 1.5rem;
        }
        
        .pagination-wrap {
            justify-content: center;
            flex-wrap: wrap;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
@endpush
