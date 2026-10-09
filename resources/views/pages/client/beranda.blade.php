{{-- ================================================================
    KANG WENDRA — Official Homepage
    LOCKED · v1.0 · 07 October 2026
    Brand & AI Architect · Understand Before You Build
================================================================ --}}
@extends('layouts.client.app')

@section('title', 'KANG WENDRA — Brand & AI Architect · Understand Before You Build')

@push('styles')
<link rel="stylesheet" href="{{ asset('client/assets/css/lunaray-beranda.css') }}?v={{ @filemtime(public_path('client/assets/css/lunaray-beranda.css')) }}">
@endpush

{{-- ================================================================
    S01 — HERO SLIDER (5 slides)
================================================================ --}}
@php
$heroSlides = [
    [
        'variant' => 'profile',
        'bg' => 'section-1-hero-1-bg.png',
        'eyebrow' => 'WENDRA WILENDRA, M.MT.',
        'headline' => 'KANG WENDRA',
        'category' => 'Brand & AI Architect',
        'body' => [
            'Di tengah perubahan besar yang dibawa AI, pekerjaan brand tetap sama: membangun kepercayaan.',
            'Yang berubah adalah cara dunia menemukan, memahami, dan memilihnya.',
        ],
        'cta_label' => 'Explore My Thinking',
        'cta_style' => 'glass',
        'cta_icon' => true,
        'cta_href' => '#',
    ],
    [
        'variant' => 'brand',
        'bg' => 'section-1-hero-2-bg.png',
        'eyebrow' => '',
        'headline' => 'BRAND',
        'category_lead' => 'Brand tidak pernah sekadar soal terlihat.',
        'category' => 'Ia hidup dari apa yang diingat, dipercaya, lalu dipilih.',
        'body' => [
            'Karena brand bukan sekadar komunikasi.',
            'Ia adalah meaning yang dibangun, pengalaman yang dirasakan, dan kepercayaan yang terakumulasi dari waktu ke waktu.',
        ],
        'cta_label' => 'Explore Brand Thinking',
        'cta_style' => 'solid',
        'cta_href' => '#',
    ],
    [
        'variant' => 'tsunami',
        'bg' => 'section-1-hero-3-bg.png',
        'eyebrow' => 'AI UNDERSTANDING & DISCOVERY',
        'opening' => 'Ini bukan sekadar gelombang teknologi berikutnya.',
        'headline' => 'INI',
        'headline_accent' => 'TSUNAMI AI.',
        'body' => [
            'AI tidak hanya mengubah tools yang kita gunakan. Ia mulai mengubah bagaimana manusia mencari, memahami, memilih—dan bagaimana bisnis ditemukan.',
        ],
        'cta_label' => 'Understand What Is Changing',
        'cta_style' => 'link',
        'cta_href' => '#',
    ],
    [
        'variant' => 'systems',
        'bg' => 'section-1-hero-4-bg.png',
        'eyebrow' => 'INTELLIGENT BUSINESS SYSTEMS',
        'headline' => 'AI yang berguna bukan yang paling terlihat.',
        'headline_accent' => 'Tapi yang benar-benar bekerja di dalam bisnis.',
        'body' => [
            'Bukan sekadar tools yang berdiri sendiri, tetapi intelligence yang terhubung dengan manusia, knowledge, workflow, dan keputusan.',
        ],
        'cta_label' => 'Explore Intelligent Systems',
        'cta_style' => 'link',
        'cta_href' => '#',
    ],
    [
        'variant' => 'beauty',
        'bg' => 'section-1-hero-5-bg.png',
        'eyebrow' => 'FROM THINKING TO PRACTICE',
        'headline' => 'THE AI BEAUTY',
        'headline_accent' => 'REVOLUTION',
        'category' => 'From Skin & Wellness Intelligence to Data-Driven Product Innovation',
        'body' => [
            'Membawa AI dari percakapan tentang teknologi menuju bagaimana intelligence digunakan untuk memahami manusia, mengembangkan produk, dan membangun bisnis.',
        ],
        'cta_label' => 'Explore the Experience',
        'cta_style' => 'link',
        'cta_href' => '#',
    ],
];
@endphp

<section class="kw-hero" id="hero" data-page="beranda" data-section="s01-hero">
    <div class="kw-hero-swiper swiper" id="kwHeroSwiper">
        <div class="swiper-wrapper">
            @foreach ($heroSlides as $i => $slide)
            <div class="kw-hero-slide kw-hero-slide--{{ $slide['variant'] }} swiper-slide" style="--kw-slide-bg: url('{{ asset('assets/img/background/' . $slide['bg']) }}')">
                <div class="kw-hero-slide-inner">
                    <div class="kw-hero-content">
                        @if (!empty($slide['eyebrow']))
                        <div class="kw-hero-eyebrow">
                            <span class="kw-eyebrow-dash"></span>
                            <span class="kw-eyebrow-text">{{ $slide['eyebrow'] }}</span>
                        </div>
                        @endif

                        @if (isset($slide['opening']))
                        <p class="kw-hero-opening">{{ $slide['opening'] }}</p>
                        @endif

                        <h1 class="kw-hero-headline">
                            <span class="kw-hero-headline-main">@if ($slide['variant'] === 'brand')@foreach (mb_str_split($slide['headline']) as $k => $ch)<span class="kw-brand-letter {{ in_array($k, [2, 4]) ? 'is-gold' : '' }}">{{ $ch }}</span>@endforeach @else{{ $slide['headline'] }}@endif</span>
                            @if (isset($slide['headline_accent']))
                                <span class="kw-hero-headline-accent">{{ $slide['headline_accent'] }}</span>
                            @endif
                        </h1>

                        @if (isset($slide['category']))
                        <p class="kw-hero-category">@if (isset($slide['category_lead']))<span class="kw-hero-category-lead">{{ $slide['category_lead'] }}</span> @endif{{ $slide['category'] }}</p>
                        @endif

                        <div class="kw-hero-body">
                            @foreach ($slide['body'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>

                        <a href="{{ $slide['cta_href'] }}" class="kw-hero-cta kw-hero-cta--{{ $slide['cta_style'] }}">
                            @if (!empty($slide['cta_icon']))
                            <img src="{{ asset('assets/img/kw-personal-social_yt-transparent.png') }}" alt="" class="kw-hero-cta-icon">
                            @endif
                            <span>{{ $slide['cta_label'] }}</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="kw-hero-overlay"></div>
            </div>
            @endforeach
        </div>

        <div class="kw-hero-pagination swiper-pagination"></div>
        <div class="kw-hero-prev swiper-button-prev" aria-label="Previous slide"></div>
        <div class="kw-hero-next swiper-button-next" aria-label="Next slide"></div>
    </div>

    <div class="kw-hero-scroll-hint" aria-hidden="true">
        <span>Scroll</span>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
    </div>
</section>

{{-- ================================================================
    S02 — CATEGORY EXPLANATION
================================================================ --}}
<section class="kw-section kw-section--light" id="brand-ai-architect" data-page="beranda" data-section="s02-brand-ai-architect">
    <div class="kw-section-bg">
        <img src="{{ asset('assets/img/background/section-2.png') }}" alt="" class="kw-section-bg-img">
        <div class="kw-section-overlay"></div>
    </div>
    <div class="kw-arch-layout">
        <div class="kw-arch-copy">
            <div class="kw-eyebrow">
                <span class="kw-eyebrow-dash"></span>
                <span>BRAND &amp; AI ARCHITECT</span>
            </div>

            <h2 class="kw-arch-headline">
                Brand, manusia, AI, dan sistem bisnis<br>
                <span class="kw-headline-accent">tidak lagi bekerja sendiri-sendiri.</span>
            </h2>

            <p class="kw-arch-body">
                Brand &amp; AI Architect adalah peran yang merancang bagaimana brand, manusia, AI, knowledge, dan intelligent systems bekerja sebagai satu sistem—dari brand architecture dan machine understanding hingga penerapan AI dalam customer journey, marketing, decision-making, workflow, dan operasi bisnis.
            </p>

            <a href="#" class="kw-btn kw-btn-outline kw-arch-cta">
                Explore What Brand &amp; AI Architect Means
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                </svg>
            </a>
        </div>
    </div>
</section>

{{-- ================================================================
    S03 — BRAND & BRANDING
    Split layout: text-left (55%) | visual-right (45%)
    Reference: gambar review final — 100% match
=============================================================== --}}
<section class="kw-section kw-section--dark kw-section--split" id="beranda--brand-branding" data-page="beranda" data-section="s03-brand-branding">

    {{-- FULL-BLEED BACKGROUND + OVERLAY (left-weighted) --}}
    <div class="kw-section-bg">
        <img src="{{ asset('assets/img/background/section-3-bg.png') }}" alt="" loading="lazy" class="kw-section-bg-img">
        <div class="kw-section-overlay kw-section-overlay--left"></div>
    </div>

    {{-- TEXT COLUMN (left, ~55%) — sits above overlay --}}
    <div class="kw-brand-content">
        <div class="kw-eyebrow">
            <span class="kw-eyebrow-dash"></span>
            <span>BRAND &amp; BRANDING</span>
        </div>

        <h2 class="kw-brand-headline">
            Brand yang terlihat kuat<br>
            belum tentu <span class="kw-brand-headline-strong">benar-benar kuat.</span>
        </h2>

        <p class="kw-brand-line">Logo bisa dikenali.</p>
        <p class="kw-brand-line">Campaign bisa ramai.</p>
        <p class="kw-brand-line">Konten bisa muncul setiap hari.</p>

        <p class="kw-brand-body">
            Tapi brand baru mulai bekerja ketika ia meninggalkan sesuatu di kepala orang&mdash;meaning yang jelas, alasan untuk percaya, dan alasan untuk memilih.
        </p>

        <p class="kw-brand-body">
            Itu sebabnya saya tidak melihat branding sebagai pekerjaan mempercantik tampilan. Branding adalah pekerjaan membentuk persepsi, mengarahkan pengalaman, dan membangun memory yang cukup kuat untuk bertahan ketika perhatian sudah pindah ke tempat lain.
        </p>

        <p class="kw-brand-body">
            Dan sekarang, ketika AI mulai ikut mencari, membaca, membandingkan, dan merekomendasikan brand, pekerjaan itu menjadi lebih kompleks. Fondasinya tidak berubah. Lingkungan tempat brand hidup yang berubah.
        </p>

        <div class="kw-brand-statement">
            <div class="kw-brand-statement-line"></div>
            <p class="kw-brand-statement-text">
                Fondasinya tidak berubah.<br>
                Lingkungan tempat brand hidup yang berubah.
            </p>
        </div>

        <a href="#" class="kw-btn kw-btn-outline">
            Explore Brand &amp; Branding
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"/>
                <polyline points="12 5 19 12 12 19"/>
            </svg>
        </a>
    </div>

</section>

{{-- ================================================================
    S04 — AI & THE NEW ENVIRONMENT
================================================================ --}}
<section class="kw-section kw-section--light" id="ai-environment" data-page="beranda" data-section="s04-ai-environment">
    <div class="kw-section-bg">
        <img src="{{ asset('assets/img/background/section-4-bg.png') }}" alt="" loading="lazy" class="kw-section-bg-img">
        <div class="kw-section-overlay"></div>
    </div>
    <div class="kw-section-inner">
        <div class="kw-section-content">
            <div class="kw-eyebrow">
                <span class="kw-eyebrow-dash"></span>
                <span>AI &amp; THE NEW ENVIRONMENT</span>
            </div>

            <h2 class="kw-headline">
                AI bukan sekadar tools baru.<br>
                <span class="kw-headline-accent">Ia adalah cara baru dunia memahami.</span>
            </h2>

            <p class="kw-body">
                AI mulai ikut membaca konteks,<br>
                menghubungkan informasi, membandingkan pilihan,<br>
                dan membentuk jawaban.
            </p>

            <div class="kw-core-statement">
                <div class="kw-eyebrow-dash"></div>
                <p><strong>Brand sekarang punya dua pembaca.</strong></p>
                <p class="kw-core-line">Manusia yang perlu percaya.</p>
                <p class="kw-core-line">Mesin yang perlu memahami.</p>
            </div>

            <p class="kw-body kw-body--dim">
                Bukan berarti kita membangun brand untuk mesin.<br>
                Manusia tetap menjadi tujuan.
            </p>

            <div class="kw-underline-statement">
                <span class="kw-eyebrow-dash"></span>
                <p><span class="kw-us-line kw-us-line--bold">AI tidak menggantikan manusia sebagai tujuan.</span><br><span class="kw-us-line kw-us-line--heavy">AI mengubah jalan menuju manusia.</span></p>
            </div>

            <a href="#" class="kw-btn kw-btn-outline">Explore AI Understanding &amp; Discovery</a>
        </div>
    </div>
</section>

{{-- ================================================================
    S05 — INTELLIGENT BUSINESS SYSTEMS
================================================================ --}}
<section class="kw-section kw-section--dark" id="intelligent-business" data-page="beranda" data-section="s05-intelligent-business">
    <div class="kw-section-bg">
        <img src="{{ asset('assets/img/background/section-5-bg.png') }}" alt="" loading="lazy" class="kw-section-bg-img">
        <div class="kw-section-overlay"></div>
    </div>
    <div class="kw-section-inner">
        <div class="kw-section-content">
            <div class="kw-eyebrow">
                <span class="kw-eyebrow-dash"></span>
                <span>INTELLIGENT BUSINESS SYSTEMS</span>
            </div>

            <h2 class="kw-headline">
                Punya AI belum tentu<br>
                <span class="kw-headline-accent">membuat bisnis lebih intelligent.</span>
            </h2>

            <p class="kw-body">
                Tools bertambah.<br>
                Sistemnya belum tentu berubah.
            </p>

            <p class="kw-body kw-body--strong">
                AI yang berguna bukan yang paling terlihat.<br>
                Tapi yang benar-benar bekerja di dalam bisnis.
            </p>

            <p class="kw-body kw-body--dim">
                Intelligent Business Systems merancang bagaimana manusia, knowledge, workflow, data, dan AI bekerja sebagai satu sistem.
            </p>

            <div class="kw-underline-statement">
                <span class="kw-eyebrow-dash"></span>
                <p><span class="kw-us-line kw-us-line--bold">AI tidak menggantikan judgment.</span><br><span class="kw-us-line kw-us-line--heavy">Ia membuat judgment bekerja dengan lebih baik.</span></p>
            </div>

            <a href="#" class="kw-btn kw-btn-outline">Explore Intelligent Business Systems</a>
        </div>
    </div>
</section>

{{-- ================================================================
    S06 — IDEAS
================================================================ --}}
@php
    $homepagePostUrl = static fn ($post) => $post->category
        ? route('post_detail', ['category' => $post->category->slug, 'post' => $post->slug])
        : route('posts', ['qr' => $post->title]);
    $homepagePostImage = static fn ($post, $fallback) => $post->image ? getFile($post->image) : asset($fallback);
    $homepagePostExcerpt = static fn ($post, $length = 125) => \Illuminate\Support\Str::limit(
        trim(preg_replace('/\s+/', ' ', strip_tags((string) $post->content))),
        $length
    );
@endphp
<section class="kw-section kw-section--light" id="ideas" data-page="beranda" data-section="s06-ideas">
    <div class="kw-section-bg">
        <img src="{{ asset('assets/img/background/section-6-bg.png') }}" alt="" loading="lazy" class="kw-section-bg-img">
        <div class="kw-section-overlay"></div>
    </div>
    <div class="kw-section-inner">
        <div class="kw-section-content kw-section-content--center">
            <div class="kw-eyebrow">
                <span class="kw-eyebrow-dash"></span>
                <span>IDEAS I&rsquo;M EXPLORING</span>
            </div>

            <h2 class="kw-headline kw-headline--serif">
                Some ideas deserve<br>
                <span class="kw-headline-accent">more than a quick answer.</span>
            </h2>

            @if($ideaPosts->isNotEmpty())
                @php($featuredIdea = $ideaPosts->first())
                <div class="kw-ideas-grid">
                    <a href="{{ $homepagePostUrl($featuredIdea) }}" class="kw-ideas-featured">
                        <div class="kw-card-media" style="background-image:url('{{ $homepagePostImage($featuredIdea, 'assets/img/background/section-7-bg.png') }}')"></div>
                        <div class="kw-card-copy">
                            <span class="kw-ideas-badge">FEATURED IDEA</span>
                            <p class="kw-ideas-title">{{ $featuredIdea->title }}</p>
                            <p class="kw-ideas-excerpt">{{ $homepagePostExcerpt($featuredIdea, 155) }}</p>
                            <span class="kw-card-arrow" aria-hidden="true">&rarr;</span>
                        </div>
                    </a>
                    <div class="kw-ideas-list">
                        @foreach($ideaPosts->slice(1, 3) as $idea)
                            <a href="{{ $homepagePostUrl($idea) }}" class="kw-idea-card {{ $loop->first ? 'kw-idea-card--wide' : '' }}">
                                <div class="kw-card-media" style="background-image:url('{{ $homepagePostImage($idea, 'assets/img/background/section-8-bg.png') }}')"></div>
                                <div class="kw-card-copy">
                                    <span class="kw-idea-type">{{ strtoupper($idea->category?->name ?? 'SELECTED IDEA') }}</span>
                                    <p class="kw-idea-title">{{ $idea->title }}</p>
                                    <p class="kw-ideas-excerpt">{{ $homepagePostExcerpt($idea, 105) }}</p>
                                    <span class="kw-card-arrow" aria-hidden="true">&rarr;</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- Placeholder shown until the first manual article is published. --}}
                <div class="kw-ideas-grid">
                    <div class="kw-ideas-featured">
                        <div class="kw-card-media kw-card-media--idea-featured"></div>
                        <div class="kw-card-copy">
                            <span class="kw-ideas-badge">FEATURED IDEA</span>
                            <p class="kw-ideas-title">A THOUGHT WORTH EXPLORING</p>
                            <p class="kw-ideas-excerpt">A short summary of the idea appears here, giving just enough context to invite deeper reading.</p>
                            <span class="kw-card-arrow" aria-hidden="true">&rarr;</span>
                        </div>
                    </div>
                    <div class="kw-ideas-list">
                        <div class="kw-idea-card kw-idea-card--wide">
                            <div class="kw-card-media kw-card-media--idea-discovery"></div>
                            <div class="kw-card-copy">
                                <span class="kw-idea-type">SELECTED IDEA</span>
                                <p class="kw-idea-title">AI, BRANDS, AND A MORE INTELLIGENT DISCOVERY ERA</p>
                                <p class="kw-ideas-excerpt">A short summary of the idea appears here, giving just enough context to invite deeper reading.</p>
                                <span class="kw-card-arrow" aria-hidden="true">&rarr;</span>
                            </div>
                        </div>
                        <div class="kw-idea-card">
                            <div class="kw-card-media kw-card-media--idea-judgment"></div>
                            <div class="kw-card-copy">
                                <span class="kw-idea-type">SELECTED IDEA</span>
                                <p class="kw-idea-title">HUMAN JUDGMENT IN AN AI-MEDIATED WORLD</p>
                                <p class="kw-ideas-excerpt">A short summary of the idea appears here, giving just enough context to invite deeper reading.</p>
                                <span class="kw-card-arrow" aria-hidden="true">&rarr;</span>
                            </div>
                        </div>
                        <div class="kw-idea-card">
                            <div class="kw-card-media kw-card-media--idea-systems"></div>
                            <div class="kw-card-copy">
                                <span class="kw-idea-type">SELECTED IDEA</span>
                                <p class="kw-idea-title">BUILDING INTELLIGENT BUSINESS SYSTEMS</p>
                                <p class="kw-ideas-excerpt">A short summary of the idea appears here, giving just enough context to invite deeper reading.</p>
                                <span class="kw-card-arrow" aria-hidden="true">&rarr;</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <a href="{{ route('posts', ['source' => 'web']) }}" class="kw-btn kw-btn-outline">Explore All Ideas</a>
        </div>
    </div>
</section>

{{-- ================================================================
    S07 — SIGNATURE PRINCIPLE
================================================================ --}}
<section class="kw-section kw-section--dark kw-section--fullcenter" id="signature-principle" data-page="beranda" data-section="s07-signature-principle">
    <div class="kw-section-bg">
        <img src="{{ asset('assets/img/background/section-7-bg.png') }}" alt="" loading="lazy" class="kw-section-bg-img">
        <div class="kw-section-overlay"></div>
    </div>
    <div class="kw-section-inner">
        <div class="kw-section-content kw-section-content--center">
            <div class="kw-eyebrow">
                <span class="kw-eyebrow-dash"></span>
                <span>SIGNATURE PRINCIPLE</span>
            </div>

            <h2 class="kw-headline kw-headline--large">
                UNDERSTAND<br><span class="kw-headline-accent">BEFORE</span> YOU BUILD.
            </h2>

            <p class="kw-body kw-body--center">
                Before you build the brand, adopt the AI, design the system, or change the workflow—understand what actually needs to be built.
            </p>

            <div class="kw-underline-statement">
                <span class="kw-eyebrow-dash"></span>
                <p>Clarity before construction.<br>Human judgment before acceleration.</p>
            </div>
        </div>
    </div>
</section>

{{-- ================================================================
    S08 — BOOKS & IP
================================================================ --}}
<section class="kw-section kw-section--light" id="books-ip" data-page="beranda" data-section="s08-books-ip">
    <div class="kw-section-bg">
        <img src="{{ asset('assets/img/background/section-8-bg.png') }}" alt="" loading="lazy" class="kw-section-bg-img">
        <div class="kw-section-overlay"></div>
    </div>
    <div class="kw-section-inner">
        <div class="kw-section-content">
            <div class="kw-eyebrow">
                <span class="kw-eyebrow-dash"></span>
                <span>BOOKS &amp; INTELLECTUAL PROPERTY</span>
            </div>

            <h2 class="kw-headline kw-headline--serif">
                Ideas shouldn&rsquo;t always<br>
                <span class="kw-headline-accent">end as content.</span>
            </h2>

            <p class="kw-body">
                Tidak semua gagasan seharusnya berakhir sebagai konten. Sebagian perlu dipikirkan lebih dalam, diuji, lalu dikodifikasi menjadi buku, framework, metode, dan karya intelektual yang bisa terus digunakan.
            </p>

            <a href="#" class="kw-btn kw-btn-outline">Explore Books &amp; IP</a>
        </div>
    </div>
</section>

{{-- ================================================================
    S09 — SIGNALS
================================================================ --}}
<section class="kw-section kw-section--dark" id="signals" data-page="beranda" data-section="s09-signals">
    <div class="kw-section-bg">
        <img src="{{ asset('assets/img/background/section-9-bg.png') }}" alt="" loading="lazy" class="kw-section-bg-img">
        <div class="kw-section-overlay"></div>
    </div>
    <div class="kw-section-inner">
        <div class="kw-section-content kw-section-content--center">
            <div class="kw-eyebrow">
                <span class="kw-eyebrow-dash"></span>
                <span>WHAT I&rsquo;M WATCHING</span>
            </div>

            <h2 class="kw-headline">
                The signal matters.<br>
                <span class="kw-headline-accent">The interpretation matters more.</span>
            </h2>

            <p class="kw-body kw-body--dim">
                Saya mengikuti perubahan di sekitar brand, AI, bisnis, dan teknologi—bukan sekadar untuk mengetahui apa yang terjadi, tetapi untuk memahami apa artinya dan apa yang mungkin berubah setelahnya.
            </p>

            @if($signalPosts->isNotEmpty())
                <div class="kw-signals-grid">
                    @foreach($signalPosts as $signal)
                                                <a href="{{ $homepagePostUrl($signal) }}" class="kw-signal-card {{ $loop->first ? 'kw-signal-card--featured' : '' }}">
                            <div class="kw-card-media" style="background-image:url('{{ $homepagePostImage($signal, 'assets/img/background/section-9-bg.png') }}')"></div>
                            <div class="kw-card-copy">
                                <span class="kw-signal-domain">{{ strtoupper($signal->category?->name ?? 'KANG WENDRA') }} &middot; {{ optional($signal->published_at)->format('d M Y') }}</span>
                                <span class="kw-signal-badge">MY TAKE</span>
                                <p class="kw-signal-title">{{ $signal->title }}</p>
                                <p class="kw-signal-excerpt">{{ $homepagePostExcerpt($signal, $loop->first ? 170 : 100) }}</p>
                                <span class="kw-card-arrow" aria-hidden="true">&rarr;</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="kw-signals-grid">
                    <div class="kw-signal-card kw-signal-card--featured">
                        <div class="kw-card-media kw-card-media--signal-featured"></div>
                        <div class="kw-card-copy">
                            <span class="kw-signal-domain">AI PIPELINE</span><span class="kw-signal-badge">MY TAKE</span>
                            <p class="kw-signal-title">SIGNAL TERBARU AKAN TAMPIL DI SINI</p>
                            <p class="kw-signal-excerpt">Artikel hasil scraping dan parafrase AI akan muncul otomatis setelah dipublikasikan.</p>
                        </div>
                    </div>
                </div>
            @endif

            <a href="{{ route('posts', ['source' => 'ai']) }}" class="kw-btn kw-btn-outline">Explore What I&rsquo;m Watching</a>
        </div>
    </div>
</section>

{{-- ================================================================
    S10 — WATCH
================================================================ --}}
<section class="kw-section kw-section--light" id="watch" data-page="beranda" data-section="s10-watch">
    <div class="kw-section-bg">
        <img src="{{ asset('assets/img/background/section-10-bg.png') }}" alt="" loading="lazy" class="kw-section-bg-img">
        <div class="kw-section-overlay"></div>
    </div>
    <div class="kw-section-inner">
        <div class="kw-section-content kw-section-content--center">
            <div class="kw-eyebrow">
                <img src="{{ asset('assets/img/kw-personal-social_yt-transparent.png') }}" alt="" class="kw-youtube-icon kw-youtube-icon--eyebrow">
                <span>WATCH</span>
                <span class="kw-eyebrow-dash"></span>
            </div>

            <h2 class="kw-headline kw-headline--serif">
                Some ideas are better<br>
                <span class="kw-headline-accent">seen, heard, and explored.</span>
            </h2>

            <p class="kw-body kw-body--dim kw-body--center">
                Percakapan, penjelasan, dan eksplorasi visual tentang brand, AI, bisnis, dan berbagai gagasan yang sedang saya pikirkan.
            </p>

            @if($homeVideos->isNotEmpty())
                @php($featuredVideo = $homeVideos->first())
                <div class="kw-watch-grid">
                    <a href="{{ $featuredVideo->link_yt }}" target="_blank" rel="noopener" class="kw-watch-card kw-watch-card--featured">
                        <div class="kw-card-media" style="background-image:url('{{ getFile($featuredVideo->image) }}')"></div>
                        <div class="kw-watch-play">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                        <div class="kw-card-copy">
                            <span class="kw-watch-badge">LATEST FROM WINWITHWEN</span>
                            <p class="kw-watch-title">{{ $featuredVideo->title }}</p>
                            <p class="kw-watch-duration">{{ optional($featuredVideo->youtube_published_at)->format('d M Y') }}</p>
                            <span class="kw-card-arrow" aria-hidden="true">&rarr;</span>
                        </div>
                    </a>
                    <div class="kw-watch-list">
                        @foreach($homeVideos->slice(1, 2) as $video)
                            <a href="{{ $video->link_yt }}" target="_blank" rel="noopener" class="kw-watch-thumb">
                                <div class="kw-card-media" style="background-image:url('{{ getFile($video->image) }}')"></div>
                                <div class="kw-watch-play-sm">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                                <div class="kw-card-copy">
                                    <span class="kw-watch-badge">WINWITHWEN</span>
                                    <p class="kw-watch-thumb-title">{{ $video->title }}</p>
                                    <span class="kw-watch-thumb-dur">{{ optional($video->youtube_published_at)->format('d M Y') }}</span>
                                    <span class="kw-card-arrow" aria-hidden="true">&rarr;</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <p class="kw-body">Video terbaru sedang disinkronkan dari kanal WINwithWEN.</p>
            @endif

            <a href="{{ config('services.youtube.kang_wendra_channel_url') }}" target="_blank" rel="noopener" class="kw-btn kw-btn-outline kw-youtube-link"><img src="{{ asset('assets/img/kw-personal-social_yt-transparent.png') }}" alt="" class="kw-youtube-icon">Explore on YouTube</a>
        </div>
    </div>
</section>

{{-- ================================================================
    S11 — raymAIzing / AI ECOSYSTEM
================================================================ --}}
<section class="kw-section kw-section--dark" id="raymaizing" data-page="beranda" data-section="s11-raymaizing">
    <div class="kw-section-bg">
        <img src="{{ asset('assets/img/background/section-11-bg.png') }}" alt="" loading="lazy" class="kw-section-bg-img">
        <div class="kw-section-overlay"></div>
    </div>
    <div class="kw-section-inner">
        <div class="kw-section-content kw-section-content--center">
            <div class="kw-eyebrow">
                <span class="kw-eyebrow-dash"></span>
                <span>AI ECOSYSTEM &times; ARAY</span>
            </div>

            <h2 class="kw-headline">
                From thinking<br>
                to <span class="kw-headline-accent">real intelligent</span><br>
                systems.
            </h2>

            <p class="kw-body kw-body--center">
                Gagasan tentang AI saya wujudkan dalam berbagai sistem yang saling terhubung—dan ARAY hadir sebagai AI Smart Assistant yang bisa Anda gunakan langsung di sini.
            </p>

            <div class="kw-action-row">
                <a href="#" class="kw-btn kw-btn-primary kw-btn-stack"><strong>Meet ARAY</strong><small>AI SMART ASSISTANT</small><span>&rarr;</span></a>
                <a href="#" class="kw-btn kw-btn-outline kw-btn-stack"><strong>Explore Our<br>AI Ecosystem</strong><span>&rarr;</span></a>
            </div>
        </div>
    </div>
</section>

{{-- ================================================================
    S12 — WHERE THINKING MEETS REALITY
================================================================ --}}
<section class="kw-section kw-section--dark" id="where-thinking" data-page="beranda" data-section="s12-where-thinking">
    <div class="kw-section-bg">
        <img src="{{ asset('assets/img/background/section-12-bg.png') }}" alt="" loading="lazy" class="kw-section-bg-img">
        <div class="kw-section-overlay"></div>
    </div>
    <div class="kw-section-inner">
        <div class="kw-section-content">
            <div class="kw-eyebrow">
                <span class="kw-eyebrow-dash"></span>
                <span>WHERE THINKING BECOMES PRACTICE</span>
            </div>

            <h2 class="kw-headline">
                From ideas<br>
                to <span class="kw-headline-accent">real impact.</span>
            </h2>

            <p class="kw-body">
                Gagasan perlu diwujudkan. Saya bekerja di berbagai konteks—mulai dari brand, AI, hingga pengembangan sistem—untuk membantu ide menjadi solusi yang relevan dan berdampak nyata.
            </p>

            <a href="#" class="kw-text-link">Explore Collaboration <span>&rarr;</span></a>
        </div>
    </div>
</section>

{{-- ================================================================
    S13 — PERSONAL CLOSING
================================================================ --}}
<section class="kw-section kw-section--dark kw-section--fullcenter" id="personal-note" data-page="beranda" data-section="s13-personal-note">
    <div class="kw-section-bg">
        <img src="{{ asset('assets/img/background/section-13-bg.png') }}" alt="" loading="lazy" class="kw-section-bg-img">
        <div class="kw-section-overlay"></div>
    </div>
    <div class="kw-section-inner">
        <div class="kw-section-content kw-section-content--center">
            <div class="kw-eyebrow">
                <span>A NOTE FROM ME</span>
                <span class="kw-eyebrow-dash"></span>
            </div>

            <p class="kw-personal-opening">
                Semakin cepat dunia bergerak, semakin penting bagi saya untuk tahu kapan harus berhenti, melihat lebih jernih, dan memahami apa yang benar-benar penting.
            </p>

            <div class="kw-personal-turn">
                <p class="kw-body">
                    AI akan semakin pintar.<br>Sistem akan semakin cepat.<br>Dan selalu akan ada hal baru yang bisa kita bangun.
                </p>
                <p class="kw-body kw-turn-accent">
                    Tapi teknologi hanya membantu kita bergerak lebih cepat.<br>Manusia tetap harus menentukan ke mana kita akan pergi.
                </p>
            </div>

            <div class="kw-personal-principle">
                <div class="kw-eyebrow-dash"></div>
                <p class="kw-principle-text">UNDERSTAND BEFORE YOU BUILD.</p>
            </div>

            <div class="kw-personal-about-row">
                <div class="kw-personal-identity">
                    <img src="{{ asset('assets/img/kw-signature-transparent.png') }}" alt="Tanda tangan Kang Wendra" class="kw-signature">
                    <span class="kw-identity-name">Kang Wendra</span>
                    <span class="kw-identity-role">Brand &amp; AI Architect</span>
                </div>

                <a href="#" class="kw-btn kw-btn-outline">About Kang Wendra <b>&rarr;</b></a>
            </div>

            <div class="kw-personal-connect">
                <span>LET&rsquo;S STAY CONNECTED</span>
                <div class="kw-personal-connect-row">
                    <a href="#" class="kw-btn kw-btn-outline kw-youtube-link"><img src="{{ asset('assets/img/kw-personal-social_yt-transparent.png') }}" alt="" class="kw-youtube-icon">Join My Channel <b>&rarr;</b></a>
                    <div class="kw-personal-social" aria-label="Social channels">
                        <a href="#" aria-label="YouTube"><img src="{{ asset('assets/img/kw-personal-social_yt-transparent.png') }}" alt=""></a>
                        <a href="#" aria-label="LinkedIn"><img src="{{ asset('assets/img/kw-personal-social_in-transparent.png') }}" alt=""></a>
                        <a href="#" aria-label="Instagram"><img src="{{ asset('assets/img/kw-personal-social_ig-transparent.png') }}" alt=""></a>
                        <a href="#" aria-label="TikTok"><img src="{{ asset('assets/img/kw-personal-social_tt-transparent.png') }}" alt=""></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ================================================================
    S14 — FOOTER
================================================================ --}}

{{-- ================================================================
    SCRIPTS
================================================================ --}}
@push('scripts')
<script>
(function() {
    // Header scroll
    var header = document.getElementById('site-header');
    if (header) {
        window.addEventListener('scroll', function() {
            header.classList.toggle('is-scrolled', window.scrollY > 40);
        }, { passive: true });
    }

    // Mobile nav
    var mobileToggle = document.querySelector('.kw-mobile-toggle');
    var mobileNav = document.getElementById('kw-mobile-nav');
    if (mobileToggle && mobileNav) {
        mobileToggle.addEventListener('click', function() {
            var expanded = mobileToggle.getAttribute('aria-expanded') === 'true';
            mobileToggle.setAttribute('aria-expanded', String(!expanded));
            mobileNav.setAttribute('aria-hidden', String(expanded));
        });
    }

    // Swiper hero init
    if (typeof Swiper !== 'undefined' && document.getElementById('kwHeroSwiper')) {
        new Swiper('#kwHeroSwiper', {
            effect: 'fade',
            loop: true,
            autoplay: { delay: 6000, disableOnInteraction: true },
            pagination: { el: '.kw-hero-pagination', clickable: true },
            navigation: { nextEl: '.kw-hero-next', prevEl: '.kw-hero-prev' },
            speed: 900,
            fadeEffect: { crossFade: true },
        });
    }
})();
</script>
@endpush
