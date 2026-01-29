@extends('layouts.client.app')

@push('structured-data')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "{{ $post->title }}",
    "description": "{{ Str::limit(strip_tags($post->content), 160) }}",
    "image": "{{ $post->image ? getFile($post->image) : '' }}",
    "author": {
        "@type": "Person",
        "name": "{{ $post->createdBy->name ?? 'Admin' }}",
        "url": "{{ $post->createdBy ? route('author', $post->createdBy->slug) : '' }}"
    },
    "publisher": {
        "@type": "Organization",
        "name": "{{ $meta->web_name ?? 'Portal Berita' }}",
        "logo": {
            "@type": "ImageObject",
            "url": "{{ $meta->logo ? getFile($meta->logo) : '' }}"
        }
    },
    "datePublished": "{{ $post->published_at ? $post->published_at->toISOString() : $post->created_at->toISOString() }}",
    "dateModified": "{{ $post->updated_at->toISOString() }}",
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "{{ request()->url() }}"
    },
    "articleSection": "{{ $post->category->name ?? 'Berita' }}",
    "keywords": "{{ $post->tags ? collect(json_decode($post->tags))->map(function($tagId) { return App\Models\PostTags::find($tagId)?->name; })->filter()->implode(', ') : '' }}",
    "wordCount": {{ str_word_count(strip_tags($post->content ?? '')) }},
    "url": "{{ request()->url() }}"
}
</script>

@if($relate->count() > 0)
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {
            "@type": "Question",
            "name": "Apa topik utama artikel {{ $post->title }}?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Artikel ini membahas {{ Str::limit(strip_tags($post->content), 200) }} dalam kategori {{ $post->category->name ?? 'berita' }}."
            }
        },
        {
            "@type": "Question", 
            "name": "Siapa penulis artikel ini?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Artikel ini ditulis oleh {{ $post->createdBy->name ?? 'Tim Editorial' }} dan dipublikasikan pada {{ $post->published_at ? $post->published_at->format('d M Y') : $post->created_at->format('d M Y') }}."
            }
        },
        {
            "@type": "Question",
            "name": "Artikel terkait apa saja yang tersedia?",
            "acceptedAnswer": {
                "@type": "Answer", 
                "text": "Beberapa artikel terkait yang mungkin menarik: {{ $relate->take(3)->pluck('title')->implode(', ') }}."
            }
        }
    ]
}
</script>
@endif
@endpush

@section('content')

    <section class="single-page no-sidebar padding-bottom">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    @if($post->image)
                        <div class="single-post-thumb">
                            <img src="{{ getFile($post->image) }}" alt="{{ $post->title }}">
                        </div>
                    @endif
                    <header class="entry-header">
                       <ul class="post-meta">
                            <li><a href="/{{ $post->category?->slug ?? 'news' }}">{{ $post->category?->name ?? 'Berita' }}</a></li>
                            <li class="sep"></li>
                            <li><a href="/{{ $post->category?->slug ?? 'news' }}" class="date">{{ $post->published_at ? \Carbon\Carbon::parse($post->published_at)->format('d.m.Y') : date('d.m.Y') }}</a></li>
                        </ul>
                        <h2 class="post-title">{{ $post->title }}</h2>
                        <div class="post-author-meta">
                            <div class="author-thumb">
                                <a href="/author/{{ $post->createdBy?->slug ?? '#' }}">
                                    <img src="{{ $post->createdBy?->image ? getFile($post->createdBy->image) : asset('client/assets/img/author-1.jpg') }}" alt="author">
                                </a>
                            </div>
                            <div class="author-info">
                                <span>Oleh <a href="/author/{{ $post->createdBy?->slug ?? '#' }}">{{ $post->createdBy?->name ?? 'Penulis' }}</a></span>
                                <span>{{ $post->published_at ? \Carbon\Carbon::parse($post->published_at)->locale('id')->translatedFormat('l, d M Y') : date('d M Y') }} • {{ $post->counter ?? 0 }} views</span>
                            </div>
                        </div>
                    </header>
                    <div class="single-post-content">
                        {!! $content !!}
                    </div>
                    
                    <footer class="entry-footer">
                        @if ($post->tags)
                            <ul class="tag-list">
                                @foreach (json_decode($post->tags) as $tag)
                                    @php $tags = App\Models\PostTags::tagById($tag) @endphp
                                    <li><a href="/tag/{{ $tags->slug }}">#{{ $tags->name }}</a></li>
                                @endforeach
                            </ul>
                        @endif
                        <ul class="post-social-share">
                            <li class="facebook">
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                                        <path d="M512 256C512 114.6 397.4 0 256 0S0 114.6 0 256C0 376 82.7 476.8 194.2 504.5V334.2H141.4V256h52.8V222.3c0-87.1 39.4-127.5 125-127.5c16.2 0 44.2 3.2 55.7 6.4V172c-6-.6-16.5-1-29.6-1c-42 0-58.2 15.9-58.2 57.2V256h83.6l-14.4 78.2H287V510.1C413.8 494.8 512 386.9 512 256h0z"></path>
                                    </svg>
                                </a>
                            </li>
                            <li class="twitter">
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($post->title) }}" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                                        <path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path>
                                    </svg>
                                </a>
                            </li>
                            <li class="whatsapp">
                                <a href="https://wa.me/?text={{ urlencode($post->title . ' — ' . request()->fullUrl()) }}" target="_blank">
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
                    
                    @if($relate->count() > 0)
                        <div class="single-post-item">
                            <h3>Artikel Terkait</h3>
                            <div class="related-post-wrap">
                                @php $relateCount = 0; @endphp
                                @foreach($relate as $item)
                                    @if($item->id != $post->id && $relateCount < 3)
                                        @php $relateCount++; @endphp
                                        <article>
                                            <div class="post-card horizontal-card img-hover-move {{ !$item->image ? 'no-image' : '' }}">
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
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    @if($post->createdBy)
                        <div class="single-post-item">
                            <div class="single-post-author">
                                <div class="author-thumb">
                                    <a href="/author/{{ $post->createdBy->slug }}">
                                        <img src="{{ $post->createdBy->image ? getFile($post->createdBy->image) : asset('client/assets/img/author-widget.jpg') }}" alt="author">
                                    </a>
                                </div>
                                <div class="author-info">
                                    <h3>
                                        <a href="/author/{{ $post->createdBy->slug }}">{{ $post->createdBy->name }}</a> 
                                        <span>Penulis</span>
                                    </h3>
                                    <p>{{ $post->createdBy->bio ?? 'Penulis yang berpengalaman dalam dunia jurnalistik dan penulisan artikel.' }}</p>
                                    <ul class="post-social-share">
                                        <li class="facebook">
                                            <a href="{{ $post->createdBy->facebook ?? '#' }}" target="_blank">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                                                    <path d="M512 256C512 114.6 397.4 0 256 0S0 114.6 0 256C0 376 82.7 476.8 194.2 504.5V334.2H141.4V256h52.8V222.3c0-87.1 39.4-127.5 125-127.5c16.2 0 44.2 3.2 55.7 6.4V172c-6-.6-16.5-1-29.6-1c-42 0-58.2 15.9-58.2 57.2V256h83.6l-14.4 78.2H287V510.1C413.8 494.8 512 386.9 512 256h0z"></path>
                                                </svg>
                                            </a>
                                        </li>
                                        <li class="twitter">
                                            <a href="{{ $post->createdBy->twitter ?? '#' }}" target="_blank">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                                                    <path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path>
                                                </svg>
                                            </a>
                                        </li>
                                        <li class="instagram">
                                            <a href="{{ $post->createdBy->instagram ?? '#' }}" target="_blank">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="currentColor">
                                                    <path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"></path>
                                                </svg>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
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
        color: #ff6b35;
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
        color: #ff6b35;
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
        color: #ff6b35;
    }
    
    .sidebar-banner img {
        width: 100%;
        height: auto;
        border-radius: 5px;
    }
    
    .tag-item {
        display: inline-block;
        padding: 5px 12px;
        margin: 3px;
        background: #f8f9fa;
        color: #666;
        text-decoration: none;
        border-radius: 15px;
        font-size: 12px;
        transition: all 0.3s ease;
    }
    
    .tag-item:hover {
        background: #ff6b35;
        color: white;
        text-decoration: none;
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