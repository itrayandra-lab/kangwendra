@extends('layouts.admin.app')
@section('title', 'Konfigurasi Scraping')

@push('styles')
<style>
    .panel-card { background: #fff; border-radius: 10px; padding: 24px; margin-bottom: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
    .panel-card h4 { font-size: 16px; font-weight: 700; color: #1a1a2e; margin-bottom: 4px; }
    .panel-card .panel-desc { color: #888; font-size: 13px; margin-bottom: 16px; }
    .setting-row { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
    .setting-row:last-child { border-bottom: none; }
    .setting-label { font-weight: 600; color: #333; }
    .setting-desc { color: #999; font-size: 12px; margin-top: 2px; }
    .badge-value { display: inline-block; background: #e8f5e9; color: #2e7d32; border-radius: 6px; padding: 4px 12px; font-weight: 600; font-size: 14px; }
    .tag { display: inline-block; background: #e3f2fd; color: #1565c0; border-radius: 20px; padding: 5px 12px; margin: 3px; font-size: 13px; }
    .tag .remove { margin-left: 6px; color: #1565c0; font-weight: bold; cursor: pointer; text-decoration: none; }
    .tag .remove:hover { color: #c62828; }
    .source-item { display: flex; align-items: center; background: #f8f9fa; border-radius: 8px; padding: 10px 14px; margin: 5px 0; }
    .source-item .domain { font-weight: 600; color: #333; min-width: 200px; }
    .source-item .url { color: #666; font-size: 12px; flex: 1; word-break: break-all; }
    .source-item .remove-btn { color: #ef5350; margin-left: 8px; cursor: pointer; font-size: 16px; }
    .num-badge { background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 6px 14px; font-weight: 700; color: #856404; font-size: 15px; }
    .flush-card { background: #fff8e1; border: 1px solid #ffe082; border-radius: 10px; padding: 20px 24px; margin-top: 4px; }
</style>
@endpush

@section('content')

<div class="info-box" style="background:#e3f2fd; border:1px solid #1565c0; border-radius:8px; padding:12px 16px; margin-bottom:20px; font-size:13px; color:#1565c0;">
    <strong>Tip:</strong> Pengaturan di bawah mengatur bagaimana pipeline auto-scraping bekerja. Perubahan langsung生效 untuk scraping berikutnya.
</div>

@if(session('success'))
    <div class="alert alert-success" style="border-radius:8px;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="border-radius:8px;">{{ session('error') }}</div>
@endif

{{-- KEYWORDS --}}
<div class="panel-card">
    <h4>
        <i class="fa fa-tag" style="color:#1565c0;"></i> Keywords Predefined
    </h4>
    <p class="panel-desc">Tombol AI topic di halaman Ref Articles. Klik &times; untuk hapus.</p>

    <div style="margin-bottom:16px;">
        @foreach(($grouped['keywords']?->typed_value ?? []) as $kw)
            <span class="tag">
                {{ $kw }}
                <a href="{{ route('admin.scraper-config.remove-item', ['key' => 'keywords', 'item' => $kw]) }}"
                   class="remove" title="Hapus"
                   onclick="return confirm('Hapus keyword &quot;{{ $kw }}&quot;?')">&times;</a>
            </span>
        @endforeach
        @if(empty($grouped['keywords']?->typed_value))
            <span style="color:#999; font-style:italic;">Belum ada keywords.</span>
        @endif
    </div>

    <form action="{{ route('admin.scraper-config.add-item', 'keywords') }}" method="POST" class="row">
        @csrf
        <div class="col-md-8">
            <input type="text" name="item" class="form-control" placeholder="Ketik keyword baru, tekan Enter untuk tambah..." autocomplete="off">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary btn-block">+ Tambah Keyword</button>
        </div>
    </form>
</div>

{{-- SOURCE URLs --}}
<div class="panel-card">
    <h4>
        <i class="fa fa-globe" style="color:#2e7d32;"></i> Source Sitemap URLs
    </h4>
    <p class="panel-desc">Domain + sitemap_index.xml yang di-scrape. Tambah domain baru untuk sumber artikel lain.</p>

    @foreach(($grouped['source_urls']?->typed_value ?? []) as $domain => $url)
        <div class="source-item">
            <span class="domain">{{ $domain }}</span>
            <span class="url">{{ $url }}</span>
            <a href="{{ route('admin.scraper-config.remove-item', ['key' => 'source_urls', 'domain' => $domain]) }}"
               class="remove-btn" title="Hapus"
               onclick="return confirm('Hapus domain &quot;{{ $domain }}&quot;?')">&times;</a>
        </div>
    @endforeach
    @if(empty($grouped['source_urls']?->typed_value))
        <p style="color:#999; font-style:italic;">Belum ada source URL.</p>
    @endif

    <form action="{{ route('admin.scraper-config.add-item', 'source_urls') }}" method="POST" class="row" style="margin-top:12px;">
        @csrf
        <div class="col-md-3">
            <input type="text" name="domain" class="form-control" placeholder="Domain" autocomplete="off">
        </div>
        <div class="col-md-7">
            <input type="text" name="item" class="form-control" placeholder="https://domain.com/sitemap_index.xml" autocomplete="off">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-block">+ Tambah</button>
        </div>
    </form>
</div>

{{-- NUMBER SETTINGS ROW --}}
<div class="row">

    {{-- MIN YEAR --}}
    <div class="col-md-4">
        <div class="panel-card">
            <h4><i class="fa fa-calendar" style="color:#ef6c00;"></i> Tahun Minimum</h4>
            <p class="panel-desc">Hanya ambil artikel sejak tahun ini.</p>

            <div class="setting-row">
                <div>
                    <div class="setting-label">Sekarang</div>
                </div>
                <span class="num-badge">{{ $grouped['min_year']?->typed_value ?? 2022 }}</span>
            </div>

            <form action="{{ route('admin.scraper-config.update', 'min_year') }}" method="POST">
                @csrf @method('PUT')
                <div class="row" style="margin-top:12px;">
                    <div class="col-8">
                        <input type="number" name="value" class="form-control"
                               value="{{ $grouped['min_year']?->typed_value ?? 2022 }}"
                               min="2010" max="2030">
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-success btn-block">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- CONFIDENCE THRESHOLD --}}
    <div class="col-md-4">
        <div class="panel-card">
            <h4><i class="fa fa-filter" style="color:#7b1fa2;"></i> Confidence Threshold</h4>
            <p class="panel-desc">Minimal score agar artikel diproses.</p>

            <div class="setting-row">
                <div>
                    <div class="setting-label">Sekarang</div>
                </div>
                <span class="num-badge">{{ $grouped['confidence_threshold']?->typed_value ?? 45 }}%</span>
            </div>

            <form action="{{ route('admin.scraper-config.update', 'confidence_threshold') }}" method="POST">
                @csrf @method('PUT')
                <div class="row" style="margin-top:12px;">
                    <div class="col-8">
                        <input type="number" name="value" class="form-control"
                               value="{{ $grouped['confidence_threshold']?->typed_value ?? 45 }}"
                               min="10" max="100" step="5">
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-success btn-block">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- DAILY LIMIT --}}
    <div class="col-md-4">
        <div class="panel-card">
            <h4><i class="fa fa-bolt" style="color:#1565c0;"></i> Limit Harian</h4>
            <p class="panel-desc">Max artikel scrape per hari.</p>

            <div class="setting-row">
                <div>
                    <div class="setting-label">Sekarang</div>
                </div>
                <span class="num-badge">{{ $grouped['daily_limit']?->typed_value ?? 5 }} / hari</span>
            </div>

            <form action="{{ route('admin.scraper-config.update', 'daily_limit') }}" method="POST">
                @csrf @method('PUT')
                <div class="row" style="margin-top:12px;">
                    <div class="col-8">
                        <input type="number" name="value" class="form-control"
                               value="{{ $grouped['daily_limit']?->typed_value ?? 5 }}"
                               min="1" max="20">
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-success btn-block">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- FLUSH CACHE --}}
<div class="flush-card">
    <div class="row" style="align-items:center;">
        <div class="col-md-8">
            <h4 style="margin-bottom:4px; font-size:15px;"><i class="fa fa-database" style="color:#f57f17;"></i> Flush Cache Sitemap</h4>
            <p style="margin:0; color:#856404; font-size:13px;">
                Bersihkan cache sitemap discovery. Scraping berikutnya akan fetch ulang dari sumber.
            </p>
        </div>
        <div class="col-md-4" style="text-align:right;">
            <form action="{{ route('admin.scraper-config.flush-cache') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-warning" onclick="return confirm('Flush cache sitemap?')">
                    <i class="fa fa-refresh"></i> Flush Cache
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
