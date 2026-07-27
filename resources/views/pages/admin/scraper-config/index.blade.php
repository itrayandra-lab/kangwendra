@extends('layouts.admin.app')
@section('title', 'Konfigurasi Scraping')

@section('content')
<style>
    .config-card { background: #fff; border-radius: 12px; padding: 24px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    .config-card h4 { color: #1a1a2e; font-weight: 700; margin-bottom: 4px; }
    .config-card .desc { color: #888; font-size: 13px; margin-bottom: 16px; }
    .tag { display: inline-block; background: #e3f2fd; color: #1565c0; border-radius: 20px; padding: 4px 12px; margin: 3px; font-size: 13px; }
    .tag .remove { margin-left: 6px; color: #1565c0; font-weight: bold; cursor: pointer; text-decoration: none; }
    .tag .remove:hover { color: #c62828; }
    .source-item { display: flex; align-items: center; background: #f5f5f5; border-radius: 8px; padding: 8px 12px; margin: 4px 0; }
    .source-item .domain { font-weight: 600; color: #333; min-width: 200px; }
    .source-item .url { color: #666; font-size: 12px; flex: 1; word-break: break-all; }
    .source-item .remove-btn { color: #c62828; margin-left: 8px; cursor: pointer; }
    .add-form { display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap; }
    .add-form input { flex: 1; min-width: 150px; }
    .num-input { width: 100px !important; min-width: 0 !important; }
    .info-box { background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; font-size: 13px; color: #856404; }
    .current-value { background: #f8f9fa; border-radius: 8px; padding: 12px; margin-bottom: 12px; }
    .current-value strong { color: #333; }
</style>

<div class="info-box">
    <strong>Tip:</strong> Pengaturan di bawah mengatur bagaimana pipeline auto-scraping bekerja. Perubahan berlaku untuk scraping berikutnya.
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row">

    {{-- KEYWORDS --}}
    <div class="col-md-6">
        <div class="config-card">
            <h4>Keywords Predefined</h4>
            <p class="desc">Tombol AI topic di halaman Ref Articles. Enter untuk tambah baru.</p>

            <div class="current-value">
                <strong>Keywords aktif ({{ count($grouped['keywords']?->typed_value ?? []) }}):</strong>
                <div style="margin-top:8px;">
                    @foreach(($grouped['keywords']?->typed_value ?? []) as $kw)
                        <span class="tag">
                            {{ $kw }}
                            <a href="{{ route('admin.scraper-config.remove-item', ['key' => 'keywords', 'item' => $kw]) }}"
                               class="remove" title="Hapus"
                               onclick="return confirm('Hapus keyword &quot;{{ $kw }}&quot;?')">&times;</a>
                        </span>
                    @endforeach
                </div>
            </div>

            <form action="{{ route('admin.scraper-config.add-item', 'keywords') }}" method="POST">
                @csrf
                <div class="add-form">
                    <input type="text" name="item" class="form-control" placeholder="Nama keyword baru..." autocomplete="off">
                    <button type="submit" class="btn btn-primary btn-sm">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    {{-- SOURCE URLs --}}
    <div class="col-md-6">
        <div class="config-card">
            <h4>Source Sitemap URLs</h4>
            <p class="desc">Domain + sitemap_index.xml yang di-scrape. Tambah domain baru untuk sumber artikel lain.</p>

            <div class="current-value">
                @foreach(($grouped['source_urls']?->typed_value ?? []) as $domain => $url)
                    <div class="source-item">
                        <span class="domain">{{ $domain }}</span>
                        <span class="url">{{ $url }}</span>
                        <a href="{{ route('admin.scraper-config.remove-item', ['key' => 'source_urls', 'domain' => $domain]) }}"
                           class="remove-btn" title="Hapus"
                           onclick="return confirm('Hapus domain &quot;{{ $domain }}&quot;?')">&times;</a>
                    </div>
                @endforeach
            </div>

            <form action="{{ route('admin.scraper-config.add-item', 'source_urls') }}" method="POST">
                @csrf
                <div class="add-form">
                    <input type="text" name="domain" class="form-control" placeholder="Domain (cth: example.com)" style="max-width:200px">
                    <input type="text" name="item" class="form-control" placeholder="Sitemap URL (cth: https://example.com/sitemap_index.xml)">
                    <button type="submit" class="btn btn-primary btn-sm">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MIN YEAR --}}
    <div class="col-md-4">
        <div class="config-card">
            <h4>Tahun Minimum Artikel</h4>
            <p class="desc">Hanya ambil artikel yang dipublish sejak tahun ini. Hindari artikel usang.</p>

            <div class="current-value">
                <strong>Sekarang:</strong> {{ $grouped['min_year']?->typed_value ?? 2022 }}
            </div>

            <form action="{{ route('admin.scraper-config.update', 'min_year') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="add-form">
                    <input type="number" name="value" class="form-control num-input"
                           value="{{ $grouped['min_year']?->typed_value ?? 2022 }}"
                           min="2010" max="2030">
                    <button type="submit" class="btn btn-success btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- CONFIDENCE THRESHOLD --}}
    <div class="col-md-4">
        <div class="config-card">
            <h4>Confidence Threshold</h4>
            <p class="desc">Minimal score agar artikel diproses. % lebih tinggi = artikel lebih relevan.</p>

            <div class="current-value">
                <strong>Sekarang:</strong> {{ $grouped['confidence_threshold']?->typed_value ?? 45 }}%
            </div>

            <form action="{{ route('admin.scraper-config.update', 'confidence_threshold') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="add-form">
                    <input type="number" name="value" class="form-control num-input"
                           value="{{ $grouped['confidence_threshold']?->typed_value ?? 45 }}"
                           min="10" max="100" step="5">
                    <span style="padding:8px 4px; color:#888;">%</span>
                    <button type="submit" class="btn btn-success btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- DAILY LIMIT --}}
    <div class="col-md-4">
        <div class="config-card">
            <h4>Limit Harian</h4>
            <p class="desc">Max artikel yang di-scrape per hari. Sisakan slot untuk posting manual.</p>

            <div class="current-value">
                <strong>Sekarang:</strong> {{ $grouped['daily_limit']?->typed_value ?? 5 }} artikel/hari
            </div>

            <form action="{{ route('admin.scraper-config.update', 'daily_limit') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="add-form">
                    <input type="number" name="value" class="form-control num-input"
                           value="{{ $grouped['daily_limit']?->typed_value ?? 5 }}"
                           min="1" max="20">
                    <span style="padding:8px 4px; color:#888;">artikel</span>
                    <button type="submit" class="btn btn-success btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

</div>

<div class="row" style="margin-top:8px;">
    <div class="col-12">
        <div class="config-card" style="background:#f0f4ff;">
            <h4>Flush Cache Sitemap</h4>
            <p class="desc">Bersihkan cache sitemap discovery. Dipaksa fetch ulang dari sitemap_index.xml saat scraping berikutnya.</p>
            <form action="{{ route('admin.scraper-config.flush-cache') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm">Flush Cache Sitemap</button>
            </form>
            <span style="margin-left:12px; color:#888; font-size:12px;">
                Cache tersimpan 1 jam untuk menghindari重复 fetching.
            </span>
        </div>
    </div>
</div>

@endsection
