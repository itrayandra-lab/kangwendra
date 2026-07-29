@extends('layouts.admin.app')
@section('title', $page)
@push('styles')
    <style>
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .card-icon {
            font-size: 2rem;
            color: #fff;
        }
        .card-title {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        .card-value {
            font-size: 2rem;
            font-weight: bold;
        }
        .ai-card {
            border-radius: 10px;
            border: 1px solid #dee2e6;
            padding: 16px;
            background: white;
            margin-bottom: 12px;
        }
        .ai-card-title {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .ai-card-value {
            font-size: 24px;
            font-weight: 700;
            color: #333;
        }
        .ai-card-sub {
            font-size: 11px;
            color: #aaa;
            margin-top: 2px;
        }
        .ai-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        .ai-badge-pending    { background: #fff3cd; color: #856404; }
        .ai-badge-processing { background: #cfe2ff; color: #084298; }
        .ai-badge-done       { background: #d1e7dd; color: #0f5132; }
        .ai-badge-failed     { background: #f8d7da; color: #842029; }
    </style>
@endpush

@section('content')
    <div class="row">
        {{-- Basic Stats Row --}}
        <div class="row">
            <div class="col-sm-6 col-lg-3">
                <div class="panel panel-primary text-center">
                    <div class="panel-heading">
                        <h4 class="panel-title">Jumlah Pengguna</h4>
                    </div>
                    <div class="panel-body">
                        <h3><b>{{ $totalUsers }}</b></h3>
                        <p class="text-muted">Total akun pengguna terdaftar</p>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="panel panel-primary text-center">
                    <div class="panel-heading">
                        <h4 class="panel-title">Jumlah Berita</h4>
                    </div>
                    <div class="panel-body">
                        <h3><b>{{ $totalNews }}</b></h3>
                        <p class="text-muted">Total publikasi berita</p>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="panel panel-primary text-center">
                    <div class="panel-heading">
                        <h4 class="panel-title">Berita Tahun Ini</h4>
                    </div>
                    <div class="panel-body">
                        <h3><b>{{ $newsThisYear }}</b></h3>
                        <p class="text-muted">Publikasi tahun {{ now()->format('Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="panel panel-primary text-center">
                    <div class="panel-heading">
                        <h4 class="panel-title">Berita Hari Ini</h4>
                    </div>
                    <div class="panel-body">
                        <h3><b>{{ $newsToday }}</b></h3>
                        <p class="text-muted">Publikasi {{ now()->translatedFormat('d F Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- AI Pipeline Stats Row --}}
        <div class="row" style="margin-top: 20px;">
            <div class="col-12">
                <h5 style="font-weight: 700; margin-bottom: 12px; color: #333;">
                    AI Pipeline Status
                    <span style="font-weight: 400; font-size: 12px; color: #888;">
                        &mdash; Search Engine Land &amp; SE Journal &bull; Max 5 artikel/hari &bull; Auto 03:30 WIB
                    </span>
                </h5>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6 col-lg-3">
                <div class="ai-card text-center">
                    <div class="ai-card-title">Ref Articles</div>
                    <div class="ai-card-value">{{ $aiStats['total_ref_articles'] }}</div>
                    <div class="ai-card-sub">Total scraped</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="ai-card text-center">
                    <div class="ai-card-title">Published Today</div>
                    <div class="ai-card-value">{{ $publishedToday }}</div>
                    <div class="ai-card-sub">{{ $aiPostsToday }} dari AI pipeline</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="ai-card text-center">
                    <div class="ai-card-title">Avg Confidence</div>
                    <div class="ai-card-value">{{ $researchStats['avg_confidence'] }}%</div>
                    <div class="ai-card-sub">{{ $researchStats['total_keywords'] }} keywords learned</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="ai-card text-center">
                    <div class="ai-card-title">Pending Recommendations</div>
                    <div class="ai-card-value">{{ $researchStats['pending_recs'] }}</div>
                    <div class="ai-card-sub">{{ $researchStats['processed'] }} sudah diproses</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6 col-lg-2">
                <div class="ai-card">
                    <div class="ai-card-title">Pending</div>
                    <div class="ai-card-value">{{ $aiStats['pending'] }}</div>
                    <span class="ai-badge ai-badge-pending">Awaiting paraphrase</span>
                </div>
            </div>
            <div class="col-sm-6 col-lg-2">
                <div class="ai-card">
                    <div class="ai-card-title">Processing</div>
                    <div class="ai-card-value">{{ $aiStats['processing'] }}</div>
                    <span class="ai-badge ai-badge-processing">Sedang diproses</span>
                </div>
            </div>
            <div class="col-sm-6 col-lg-2">
                <div class="ai-card">
                    <div class="ai-card-title">Published</div>
                    <div class="ai-card-value">{{ $aiStats['published'] }}</div>
                    <span class="ai-badge ai-badge-done">Post created</span>
                </div>
            </div>
            <div class="col-sm-6 col-lg-2">
                <div class="ai-card">
                    <div class="ai-card-title">Failed</div>
                    <div class="ai-card-value">{{ $aiStats['failed'] }}</div>
                    <span class="ai-badge ai-badge-failed">Perlu retry</span>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="ai-card">
                    <div class="ai-card-title">Pipeline Schedule</div>
                    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:6px;">
                        <span class="ai-badge" style="background:#e9ecef; color:#333;">03:30 WIB - Research + Scrape</span>
                        <span class="ai-badge" style="background:#e9ecef; color:#333;">08:00/13:00/16:00 - Publish</span>
                    </div>
                    <div style="font-size:11px; color:#aaa; margin-top:6px;">
                        Sources: searchengineland.com, searchenginejournal.com
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
@endpush
