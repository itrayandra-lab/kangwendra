@extends('layouts.client.app')

@section('content')
    <section class="single-page no-sidebar padding-bottom">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    @if($info->image)
                        <div class="single-post-thumb">
                            <img src="{{ getFile($info->image) }}" alt="{{ $info->title }}">
                        </div>
                    @endif
                    <header class="entry-header">
                        <ul class="post-meta">
                            <li><a href="/info">Informasi</a></li>
                            <li class="sep"></li>
                            <li><a href="/info" class="date">{{ $info->published_at ? \Carbon\Carbon::parse($info->published_at)->format('d.m.Y') : date('d.m.Y') }}</a></li>
                        </ul>
                        <h2 class="post-title">{{ $info->title }}</h2>
                        <div class="post-author-meta">
                            <div class="author-thumb">
                                <a href="/author/{{ $info->createdBy?->slug ?? '#' }}">
                                    <img src="{{ $info->createdBy?->image ? getFile($info->createdBy->image) : asset('client/assets/img/author-1.jpg') }}" alt="author">
                                </a>
                            </div>
                            <div class="author-info">
                                <span>Oleh <a href="/author/{{ $info->createdBy?->slug ?? '#' }}">{{ $info->createdBy?->name ?? 'Admin' }}</a></span>
                                <span>{{ $info->published_at ? \Carbon\Carbon::parse($info->published_at)->locale('id')->translatedFormat('l, d M Y') : date('d M Y') }} • {{ $info->counter ?? 0 }} views</span>
                            </div>
                        </div>
                    </header>
                    <div class="single-post-content">
                        {!! $info->description !!}
                    </div>
                    
                    <footer class="entry-footer">
                        <ul class="post-social-share">
                            <li class="facebook">
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                                        <path d="M512 256C512 114.6 397.4 0 256 0S0 114.6 0 256C0 376 82.7 476.8 194.2 504.5V334.2H141.4V256h52.8V222.3c0-87.1 39.4-127.5 125-127.5c16.2 0 44.2 3.2 55.7 6.4V172c-6-.6-16.5-1-29.6-1c-42 0-58.2 15.9-58.2 57.2V256h83.6l-14.4 78.2H287V510.1C413.8 494.8 512 386.9 512 256h0z"></path>
                                    </svg>
                                </a>
                            </li>
                            <li class="twitter">
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($info->title) }}" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                                        <path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path>
                                    </svg>
                                </a>
                            </li>
                            <li class="whatsapp">
                                <a href="https://wa.me/?text={{ urlencode($info->title . ' — ' . request()->fullUrl()) }}" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 71 72" fill="none">
                                        <path d="M12.5762 56.8405L15.8608 44.6381C13.2118 39.8847 12.3702 34.3378 13.4904 29.0154C14.6106 23.693 17.6176 18.952 21.9594 15.6624C26.3012 12.3729 31.6867 10.7554 37.1276 11.1068C42.5685 11.4582 47.6999 13.755 51.5802 17.5756C55.4604 21.3962 57.8292 26.4844 58.2519 31.9065C58.6746 37.3286 57.1228 42.7208 53.8813 47.0938C50.6399 51.4668 45.9261 54.5271 40.605 55.7133C35.284 56.8994 29.7125 56.1318 24.9131 53.5513L12.5762 56.8405Z" fill="#00D95F"/>
                                    </svg>
                                </a>
                            </li>
                            <li class="copy">
                                <a href="#" onclick="copyToClipboard(); return false;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </a>
                            </li>
                        </ul>
                    </footer>
                    
                    @if($information->count() > 0)
                        <div class="single-post-item">
                            <h3>Informasi Lainnya</h3>
                            <div class="related-post-wrap">
                                @php $infoCount = 0; @endphp
                                @foreach($information as $item)
                                    @if($item->id != $info->id && $infoCount < 3)
                                        @php $infoCount++; @endphp
                                        <article>
                                            <div class="post-card horizontal-card img-hover-move {{ !$item->image ? 'no-image' : '' }}">
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
                                                    <ul class="post-card-footer">
                                                        <li><a href="/info/{{ $item->slug }}" class="read-more">Baca Selengkapnya</a></li>
                                                        <li>
                                                            <a href="#" class="views">
                                                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                                                    <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174-218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T832-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"/>
                                                                </svg>
                                                                <span>{{ $item->counter ?? 0 }}</span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </article>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    .post-author-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        margin: 20px 0;
        padding: 15px 0;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
    }
    
    .post-author-meta .author-thumb img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .post-author-meta .author-info {
        flex: 1;
    }
    
    .post-author-meta .author-info span {
        display: block;
        font-size: 14px;
    }
    
    .post-author-meta .author-info span:first-child {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }
    
    .post-author-meta .author-info span:last-child {
        color: #666;
    }
    
    .post-author-meta .author-info a {
        color: #333;
        text-decoration: none;
    }
    
    .post-author-meta .author-info a:hover {
        text-decoration: underline;
    }
    
    .post-social-share .copy a {
        background: #6c757d;
    }
    
    .post-social-share .copy a:hover {
        background: #5a6268;
    }
    
    .post-social-share .whatsapp a {
        background: #25d366;
    }
    
    .post-social-share .whatsapp a:hover {
        background: #128c7e;
    }
    
    /* No Image Styles */
    .post-card.horizontal-card.no-image {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        padding: 20px;
        border-radius: 10px;
    }
    
    .post-card.horizontal-card.no-image .post-content {
        padding: 0;
    }
</style>
@endpush

@push('scripts')
<script>
    function copyToClipboard() {
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            alert('Link berhasil disalin ke clipboard!');
        }).catch(() => {
            // Fallback untuk browser lama
            const textArea = document.createElement('textarea');
            textArea.value = url;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('Link berhasil disalin ke clipboard!');
        });
    }
</script>
@endpush



