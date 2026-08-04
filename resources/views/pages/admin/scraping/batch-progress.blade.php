@extends('layouts.admin.app')
@section('title', $page)

@push('styles')
<style>
    .progress-container { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
    .keyword-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 8px;
        background: white;
        transition: all 0.3s;
    }
    .keyword-card.done { border-color: #198754; background: #f0fff4; }
    .keyword-card.pending { border-color: #ffc107; background: #fffbeb; }
    .keyword-card.processing { border-color: #0d6efd; background: #f0f4ff; }
    .keyword-card .kw-name { font-weight: 700; font-size: 15px; text-transform: capitalize; }
    .keyword-card .kw-status { font-size: 12px; font-weight: 600; }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-done { background: #d1e7dd; color: #0f5132; }
    .badge-pending { background: #fff3cd; color: #856404; }
    .badge-processing { background: #cfe2ff; color: #084298; animation: pulse 1.5s infinite; }
    @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.6; } }
    .overall-bar { height: 8px; border-radius: 4px; background: #e9ecef; overflow: hidden; margin: 16px 0; }
    .overall-bar-fill { height: 100%; background: linear-gradient(90deg, #0d6efd, #198754); border-radius: 4px; transition: width 0.5s; }
    .alt-keywords { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
    .alt-keyword-chip {
        background: #e9ecef;
        border: 1px solid #dee2e6;
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 12px;
        color: #495057;
        cursor: pointer;
        transition: all 0.2s;
    }
    .alt-keyword-chip:hover { background: #0d6efd; color: white; border-color: #0d6efd; }
    .info-box {
        background: #e3f2fd;
        border: 1px solid #1565c0;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 13px;
        color: #1565c0;
        margin-bottom: 16px;
    }
</style>
@endpush

@section('content')
<meta http-equiv="refresh" content="5">
<div class="row">
    <div class="col-md-8 col-md-offset-2">

        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-search"></i> Batch Progress — Research Artikel AI
                </h3>
            </div>
            <div class="panel-body">

                {{-- Flash Messages --}}
                @if(session('info'))
                    <div class="alert alert-info" style="border-radius:8px;">{!! session('info') !!}</div>
                @endif
                @if(session('warning'))
                    <div class="alert alert-warning" style="border-radius:8px;">{!! session('warning') !!}</div>
                @endif

                {{-- Info Box --}}
                <div class="info-box">
                    <i class="fa fa-info-circle"></i>
                    Halaman ini otomatis refresh setiap <strong>5 detik</strong>.
                    Tunggu sampai semua keyword selesai diproses, lalu klik <strong>"Lihat Hasil Scraping"</strong>.
                </div>

                {{-- Overall Progress --}}
                <div class="progress-container">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <strong>Progress Keseluruhan</strong>
                        <strong>
                            @if(isset($doneCount) && isset($totalCount) && $totalCount > 0)
                                {{ $doneCount }}/{{ $totalCount }} keyword selesai
                            @else
                                {{ count($keywords) }} keyword diproses
                            @endif
                        </strong>
                    </div>
                    @if(isset($doneCount) && isset($totalCount) && $totalCount > 0)
                        <div class="overall-bar">
                            <div class="overall-bar-fill" style="width: {{ round($doneCount / $totalCount * 100) }}%"></div>
                        </div>
                    @endif
                </div>

                {{-- Keyword List --}}
                @if(!empty($keywords))
                    @foreach($keywords as $kw)
                        @php
                            $status = $completedKeywords[$kw] ?? 'pending';
                            $recCount = \App\Models\ResearchRecommendation::byKeyword($kw)->whereDate('created_at', now()->toDateString())->count();
                        @endphp
                        <div class="keyword-card {{ $status }}">
                            <div>
                                <span class="kw-name">{{ $kw }}</span>
                                @if($status === 'done' && $recCount > 0)
                                    <span style="color:#666; font-size:12px; margin-left:8px;">
                                        → {{ $recCount }} articles ditemukan
                                    </span>
                                @elseif($status === 'done' && $recCount === 0)
                                    <span style="color:#856404; font-size:12px; margin-left:8px;">
                                        → 0 articles (sitemap tidak punya)
                                    </span>
                                @endif
                            </div>
                            <span class="status-badge badge-{{ $status }}">
                                @if($status === 'done')
                                    ✅ Selesai
                                @elseif($status === 'processing')
                                    ⏳ Diproses
                                @else
                                    ⏳ Menunggu
                                @endif
                            </span>
                        </div>
                    @endforeach
                @else
                    <div style="text-align:center; padding:40px; color:#888;">
                        <i class="fa fa-inbox" style="font-size:48px;"></i>
                        <p style="margin-top:12px;">Tidak ada keyword yang sedang diproses.</p>
                    </div>
                @endif

                {{-- Actions --}}
                <div style="display:flex; gap:12px; margin-top:20px; flex-wrap:wrap;">
                    <a href="{{ route('admin.scraping.batch-progress', ['batch_id' => $batchId]) }}"
                       class="btn btn-default" style="border-radius:8px;">
                        <i class="fa fa-refresh"></i> Refresh Sekarang
                    </a>

                    <a href="{{ route('admin.hasil-scraping.index') }}"
                       class="btn btn-success" style="border-radius:8px;">
                        <i class="fa fa-list"></i> Lihat Hasil Scraping
                    </a>

                    <a href="{{ route('admin.scraping.index') }}"
                       class="btn btn-default" style="border-radius:8px;">
                        <i class="fa fa-arrow-left"></i> Kembali ke Scraping
                    </a>
                </div>

                {{-- Auto-redirect if all done --}}
                @if($allDone ?? false)
                    <div class="alert alert-success" style="border-radius:8px; margin-top:16px; text-align:center;">
                        <i class="fa fa-check-circle"></i>
                        <strong>Semua keyword sudah selesai diproses!</strong>
                        <br>
                        <a href="{{ route('admin.hasil-scraping.index') }}" class="btn btn-success" style="margin-top:8px;">
                            Lihat Hasil Scraping →
                        </a>
                    </div>
                    <meta http-equiv="refresh" content="3;url={{ route('admin.hasil-scraping.index') }}">
                @endif

            </div>
        </div>

    </div>
</div>
@endsection
