@extends('layouts.client.app')

@push('structured-data')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "VideoObject",
    "name": "{{ $video->title }}",
    "description": "{{ Str::limit(strip_tags($video->description), 160) }}",
    "thumbnailUrl": "{{ $video->image ? getFile($video->image) : '' }}",
    "uploadDate": "{{ $video->created_at->toISOString() }}",
    "contentUrl": "{{ $video->link_yt }}",
    "embedUrl": "{{ $video->link_yt }}",
    "publisher": {
        "@type": "Organization",
        "name": "{{ $meta->web_name ?? 'Portal Berita' }}",
        "logo": {
            "@type": "ImageObject",
            "url": "{{ $meta->logo ? getFile($meta->logo) : '' }}"
        }
    },
    "author": {
        "@type": "Person",
        "name": "{{ $video->createdBy->name ?? 'Admin' }}"
    }
}
</script>

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {
            "@type": "Question",
            "name": "Apa yang dibahas dalam video {{ $video->title }}?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "{{ Str::limit(strip_tags($video->description), 200) }}"
            }
        },
        {
            "@type": "Question",
            "name": "Kapan video ini dipublikasikan?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Video ini dipublikasikan pada {{ $video->created_at->format('d M Y') }} dan dapat ditonton langsung melalui platform video kami."
            }
        },
        {
            "@type": "Question",
            "name": "Bagaimana cara menonton video ini?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Anda dapat menonton video ini langsung di halaman ini atau mengunjungi link video asli untuk pengalaman menonton yang optimal."
            }
        }
    ]
}
</script>
@endpush

@section('content')
    <section class="single-video-page padding">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="single-video-wrap">
                        <div class="video-player">
                            @if($video->link_yt)
                                <iframe 
                                    src="{{ 'https://www.youtube.com/embed/' . Str::after($video->link_yt, 'v=') . '?rel=0&showinfo=0&controls=1' }}"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen>
                                </iframe>
                            @elseif($video->image)
                                <div class="video-placeholder">
                                    <img src="{{ getFile($video->image) }}" alt="{{ $video->title }}">
                                    <div class="play-button">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="60" viewBox="0 -960 960 960" width="60" fill="currentColor">
                                            <path d="M320-200v-560l440 280-440 280Z"/>
                                        </svg>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="video-info">
                            <div class="">
                                <ul class="post-meta">
                                    <li><a href="/videos">Video</a></li>
                                    <li class="sep"></li>
                                    <li><a href="/videos" class="date">{{ $video->created_at ? \Carbon\Carbon::parse($video->created_at)->format('d.m.Y') : date('d.m.Y') }}</a></li>
                                </ul>
                                <h1 class="video-title">{{ $video->title }}</h1>
                                <div class="video-author-meta">
                                    <div class="author-thumb">
                                        <a href="/author/{{ $video->createdBy?->slug ?? '#' }}">
                                            <img src="{{ $video->createdBy?->image ? getFile($video->createdBy->image) : asset('client/assets/img/author-1.jpg') }}" alt="author">
                                        </a>
                                    </div>
                                    <div class="author-info">
                                        <span>Oleh <a href="/author/{{ $video->createdBy?->slug ?? '#' }}">{{ $video->createdBy?->name ?? 'Admin' }}</a></span>
                                        <span>{{ $video->created_at ? \Carbon\Carbon::parse($video->created_at)->locale('id')->translatedFormat('l, d M Y') : date('d M Y') }} • {{ rand(1, 100) }} views</span>
                                    </div>
                                </div>
                            </div>
                            
                            @if($content)
                                <div class="video-description">
                                    <h3>Deskripsi Video</h3>
                                    <div class="description-content">
                                        {!! $content !!}
                                    </div>
                                </div>
                            @endif
                            
                            <div class="video-social-share">
                                <h4>Bagikan Video</h4>
                                <ul class="social-share-list">
                                    <li class="facebook">
                                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                                                <path d="M512 256C512 114.6 397.4 0 256 0S0 114.6 0 256C0 376 82.7 476.8 194.2 504.5V334.2H141.4V256h52.8V222.3c0-87.1 39.4-127.5 125-127.5c16.2 0 44.2 3.2 55.7 6.4V172c-6-.6-16.5-1-29.6-1c-42 0-58.2 15.9-58.2 57.2V256h83.6l-14.4 78.2H287V510.1C413.8 494.8 512 386.9 512 256h0z"></path>
                                            </svg>
                                            Facebook
                                        </a>
                                    </li>
                                    <li class="twitter">
                                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($video->title) }}" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                                                <path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path>
                                            </svg>
                                            Twitter
                                        </a>
                                    </li>
                                    <li class="whatsapp">
                                        <a href="https://wa.me/?text={{ urlencode($video->title . ' — ' . request()->fullUrl()) }}" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 71 72" fill="none">
                                                <path d="M12.5762 56.8405L15.8608 44.6381C13.2118 39.8847 12.3702 34.3378 13.4904 29.0154C14.6106 23.693 17.6176 18.952 21.9594 15.6624C26.3012 12.3729 31.6867 10.7554 37.1276 11.1068C42.5685 11.4582 47.6999 13.755 51.5802 17.5756C55.4604 21.3962 57.8292 26.4844 58.2519 31.9065C58.6746 37.3286 57.1228 42.7208 53.8813 47.0938C50.6399 51.4668 45.9261 54.5271 40.605 55.7133C35.284 56.8994 29.7125 56.1318 24.9131 53.5513L12.5762 56.8405Z" fill="#00D95F"/>
                                            </svg>
                                            WhatsApp
                                        </a>
                                    </li>
                                    <li class="copy">
                                        <a href="#" onclick="copyToClipboard(); return false;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                            Salin Link
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="video-sidebar">
                        @if($videos->count() > 1)
                            <div class="sidebar-widget">
                                <div class="widget-heading">
                                    <h3>Video Lainnya</h3>
                                </div>
                                <div class="widget-content">
                                    @foreach($videos as $item)
                                        @if($item->id != $video->id)
                                            <div class="sidebar-video {{ !$item->image ? 'no-image' : '' }}">
                                                @if($item->image)
                                                    <div class="video-thumb">
                                                        <a href="{{ route('video_detail', $item->slug) }}">
                                                            <img src="{{ getFile($item->image) }}" alt="{{ $item->title }}">
                                                            <div class="play-overlay">
                                                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                                                    <path d="M320-200v-560l440 280-440 280Z"/>
                                                                </svg>
                                                            </div>
                                                        </a>
                                                    </div>
                                                @endif
                                                <div class="video-content">
                                                    <h4><a href="{{ route('video_detail', $item->slug) }}" class="text-hover">{{ $item->title }}</a></h4>
                                                    <ul class="video-meta">
                                                        <li><a href="/author/{{ $item->createdBy?->slug ?? '#' }}">{{ $item->createdBy?->name ?? 'Admin' }}</a></li>
                                                        <li class="sep"></li>
                                                        <li><a href="#" class="date">{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d.m.Y') : date('d.m.Y') }}</a></li>
                                                    </ul>
                                                    <div class="video-stats">
                                                        <span class="views">
                                                            <svg xmlns="http://www.w3.org/2000/svg" height="16" viewBox="0 -960 960 960" width="16" fill="currentColor">
                                                                <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T832-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"/>
                                                            </svg>
                                                            {{ rand(1, 50) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        @php $latestVideos = App\Models\Video::where('id', '!=', $video->id)->latest('created_at')->take(5)->get(); @endphp
                        @if($latestVideos->count() > 0)
                            <div class="sidebar-widget">
                                <div class="widget-heading">
                                    <h3>Video Terbaru</h3>
                                </div>
                                <div class="widget-content">
                                    @foreach($latestVideos as $item)
                                        <div class="sidebar-video {{ !$item->image ? 'no-image' : '' }}">
                                            @if($item->image)
                                                <div class="video-thumb">
                                                    <a href="{{ route('video_detail', $item->slug) }}">
                                                        <img src="{{ getFile($item->image) }}" alt="{{ $item->title }}">
                                                        <div class="play-overlay">
                                                            <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor">
                                                                <path d="M320-200v-560l440 280-440 280Z"/>
                                                            </svg>
                                                        </div>
                                                    </a>
                                                </div>
                                            @endif
                                            <div class="video-content">
                                                <h4><a href="{{ route('video_detail', $item->slug) }}" class="text-hover">{{ $item->title }}</a></h4>
                                                <ul class="video-meta">
                                                    <li><a href="/author/{{ $item->createdBy?->slug ?? '#' }}">{{ $item->createdBy?->name ?? 'Admin' }}</a></li>
                                                    <li class="sep"></li>
                                                    <li><a href="#" class="date">{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d.m.Y') : date('d.m.Y') }}</a></li>
                                                </ul>
                                                <div class="video-stats">
                                                    <span class="views">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="16" viewBox="0 -960 960 960" width="16" fill="currentColor">
                                                            <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T832-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"/>
                                                        </svg>
                                                        {{ rand(1, 50) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
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
        .single-video-page {
            padding: 60px 0;
            background: #fff;
        }

        .single-video-wrap {
            background: #fff;
        }

        .video-player {
            position: relative;
            width: 100%;
            height: 0;
            padding-bottom: 56.25%;
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .video-player iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .video-placeholder {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
        }

        .video-placeholder img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .play-button {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #fff;
            background: rgba(0,0,0,0.7);
            border-radius: 50%;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .play-button:hover {
            background: rgba(0,0,0,0.9);
            transform: translate(-50%, -50%) scale(1.1);
        }

        .video-info {
            background: #fff;
        }

        .video-meta .post-meta {
            list-style: none;
            padding: 0;
            margin: 0 0 12px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .video-meta .post-meta li {
            font-size: 13px;
        }

        .video-meta .post-meta li a {
            text-decoration: none;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .video-meta .post-meta li.sep::after {
            content: "•";
            font-size: 12px;
        }

        .video-title {
            font-size: 32px;
            font-weight: 700;
            color: #222;
            margin: 0 0 25px 0;
            line-height: 1.2;
        }

        .video-author-meta {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 1px solid #eee;
        }

        .author-thumb {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
        }

        .author-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .author-info {
            flex: 1;
        }

        .author-info span {
            display: block;
            font-size: 14px;
            line-height: 1.4;
        }

        .author-info span:first-child {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }

        .author-info span:first-child a {
            color: #333;
            text-decoration: none;
        }

        .author-info span:first-child a:hover {
            color: #666;
        }

        .author-info span:last-child {
            color: #777;
            font-size: 13px;
        }

        .video-description {
            margin-bottom: 30px;
        }

        .video-description h3 {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .description-content {
            color: #666;
            line-height: 1.6;
        }

        .video-social-share h4 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .social-share-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .social-share-list li a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .social-share-list li.facebook a {
            background: #1877f2;
            color: #fff;
        }

        .social-share-list li.twitter a {
            background: #1da1f2;
            color: #fff;
        }

        .social-share-list li.whatsapp a {
            background: #25d366;
            color: #fff;
        }

        .social-share-list li.copy a {
            background: #f5f5f5;
            color: #333;
        }

        .social-share-list li a:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .social-share-list li a svg {
            width: 16px;
            height: 16px;
        }

        .video-sidebar {
            padding-left: 30px;
        }

        .sidebar-widget {
            background: #fff;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .widget-heading h3 {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin: 0 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #f5f5f5;
        }

        .sidebar-video {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f5f5f5;
        }

        .sidebar-video:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .sidebar-video.no-image {
            display: block;
        }

        .sidebar-video.no-image .video-content {
            width: 100%;
        }

        .video-thumb {
            position: relative;
            width: 120px;
            height: 80px;
            border-radius: 6px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .video-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .video-thumb:hover img {
            transform: scale(1.05);
        }

        .play-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.7);
            border-radius: 50%;
            padding: 8px;
            color: #fff;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .video-thumb:hover .play-overlay {
            opacity: 1;
        }

        .video-content {
            flex: 1;
        }

        .video-content h4 {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 8px 0;
            line-height: 1.3;
        }

        .video-content h4 a {
            color: #333;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .video-content h4 a:hover {
            color: #666;
        }

        .video-meta {
            list-style: none;
            padding: 0;
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .video-meta li a {
            color: #666;
            text-decoration: none;
        }

        .video-meta li.sep::after {
            content: "•";
            color: #ccc;
        }

        .video-stats {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .video-stats .views {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
            color: #666;
        }

        .video-stats .views svg {
            width: 14px;
            height: 14px;
        }

        @media (max-width: 991px) {
            .video-sidebar {
                padding-left: 0;
                margin-top: 40px;
            }
            
            .video-title {
                font-size: 26px;
            }
            
            .social-share-list {
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .single-video-page {
                padding: 30px 0;
            }
            
            .video-title {
                font-size: 22px;
                line-height: 1.3;
            }
            
            .video-author-meta {
                gap: 10px;
            }
            
            .author-thumb {
                width: 40px;
                height: 40px;
            }
            
            .author-info span:first-child {
                font-size: 13px;
            }
            
            .author-info span:last-child {
                font-size: 12px;
            }
            
            .sidebar-widget {
                padding: 20px;
            }
            
            .video-thumb {
                width: 100px;
                height: 70px;
            }
            
            .social-share-list li a {
                padding: 8px 12px;
                font-size: 13px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        function copyToClipboard() {
            const url = window.location.href;
            
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(function() {
                    showCopyMessage('Link berhasil disalin!');
                }, function(err) {
                    fallbackCopyTextToClipboard(url);
                });
            } else {
                fallbackCopyTextToClipboard(url);
            }
        }

        function fallbackCopyTextToClipboard(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";
            
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    showCopyMessage('Link berhasil disalin!');
                } else {
                    showCopyMessage('Gagal menyalin link');
                }
            } catch (err) {
                showCopyMessage('Gagal menyalin link');
            }
            
            document.body.removeChild(textArea);
        }

        function showCopyMessage(message) {
            // Create toast notification
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #333;
                color: white;
                padding: 12px 20px;
                border-radius: 6px;
                z-index: 9999;
                font-size: 14px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                transform: translateX(100%);
                transition: transform 0.3s ease;
            `;
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.transform = 'translateX(0)';
            }, 100);
            
            setTimeout(() => {
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }
    </script>
@endpush
