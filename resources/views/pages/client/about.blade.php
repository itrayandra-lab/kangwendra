@extends('layouts.client.app')

@section('content')
    <section class="page-header">
        <div class="container">
            <div class="page-content-wrap">
                <div class="page-content">
                    <h4>Tentang Kami</h4>
                    <h2>Siapa Kami <span>Tentang Kangwendra</span></h2>
                    <p>Portal berita AI Indonesia yang menyajikan informasi terkini seputar kecerdasan buatan, teknologi, dan inovasi digital.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="padding" style="background: #fff;">
        <div class="container">
            <div class="row gy-5">
                <div class="col-lg-7">
                    <h3 style="font-size: 1.8rem; margin-bottom: 20px;">Tentang Kangwendra</h3>
                    <p style="color: #666; line-height: 1.8; margin-bottom: 20px;">
                        <strong>Kangwendra</strong> adalah portal berita dan informasi Indonesia yang berfokus pada pengembangan Artificial Intelligence (AI), teknologi digital, dan berbagai inovasi terkini yang mengubah cara kita hidup dan bekerja.
                    </p>
                    <p style="color: #666; line-height: 1.8; margin-bottom: 20px;">
                        Didirikan dengan visi menjadi sumber informasi terpercaya di bidang AI dan teknologi, Kangwendra menyajikan artikel-artikel berkualitas yang mencakup pembelajaran mesin, kecerdasan buatan generatif, etika AI, strategi branding digital, dan tren teknologi terkini.
                    </p>
                    <p style="color: #666; line-height: 1.8; margin-bottom: 20px;">
                        Tim kami terdiri dari penulis, peneliti, dan praktisi teknologi yang berdedikasi untuk menghadirkan konten edukatif dan inspiratif bagi masyarakat Indonesia yang ingin memahami dan memanfaatkan kekuatan AI dalam kehidupan sehari-hari.
                    </p>

                    <h3 style="font-size: 1.4rem; margin: 30px 0 20px;">Visi & Misi</h3>
                    <div class="row gy-4">
                        <div class="col-md-6">
                            <div style="background: #f8f9fa; padding: 25px; border-radius: 10px; height: 100%;">
                                <h4 style="color: #333; margin-bottom: 12px;">Visi</h4>
                                <p style="color: #666; margin: 0;">Menjadi portal berita AI dan teknologi terdepan di Indonesia yang inspiratif, edukatif, dan terpercaya.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div style="background: #f8f9fa; padding: 25px; border-radius: 10px; height: 100%;">
                                <h4 style="color: #333; margin-bottom: 12px;">Misi</h4>
                                <p style="color: #666; margin: 0;">Menyajikan konten berkualitas tentang AI, teknologi, dan inovasi digital untuk masyarakat Indonesia.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="sidebar-area">
                        <div class="sidebar-widget" style="background: #fff; border-radius: 10px; overflow: hidden; margin-bottom: 30px;">
                            <div class="widget-heading" style="background: #333; padding: 18px 20px;">
                                <h3 style="color: #fff; font-size: 1rem; margin: 0;">Artikel Terbaru</h3>
                            </div>
                            <div style="padding: 15px;">
                                @foreach($posts->take(5) as $item)
                                    <div style="display: flex; gap: 12px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                                        @if($item->image)
                                            <div style="flex-shrink: 0; width: 70px; height: 55px; border-radius: 5px; overflow: hidden;">
                                                <img src="{{ getFile($item->image) }}" alt="{{ $item->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                        @endif
                                        <div>
                                            <h4 style="font-size: 13px; margin: 0 0 5px; line-height: 1.4;">
                                                <a href="/{{ $item->category?->slug ?? 'news' }}/{{ $item->slug }}" style="color: #333; text-decoration: none;">{{ $item->title }}</a>
                                            </h4>
                                            <span style="font-size: 11px; color: #999;">{{ $item->published_at ? $item->published_at->format('d M Y') : date('d M Y') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="sidebar-widget" style="background: #fff; border-radius: 10px; overflow: hidden;">
                            <div class="widget-heading" style="background: #333; padding: 18px 20px;">
                                <h3 style="color: #fff; font-size: 1rem; margin: 0;">Kategori</h3>
                            </div>
                            <div style="padding: 15px;">
                                <ul style="list-style: none; padding: 0; margin: 0;">
                                    @foreach($categories as $cat)
                                        <li style="margin-bottom: 8px;">
                                            <a href="/{{ $cat->slug }}" style="display: flex; justify-content: space-between; padding: 8px 12px; background: #f8f9fa; color: #666; text-decoration: none; border-radius: 5px; transition: all 0.3s ease;">
                                                <span>{{ $cat->name }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
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
    @media (max-width: 768px) {
        .page-content h2 { font-size: 2rem; }
    }
</style>
@endpush
