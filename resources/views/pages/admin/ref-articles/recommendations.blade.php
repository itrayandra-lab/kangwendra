@extends('layouts.admin.app')
@section('title', $page)

@push('styles')
<style>
.action-btn {
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.action-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.btn-approve  { background: #198754; color: white; }
.btn-reject   { background: #dc3545; color: white; }
.btn-approve-all { background: #0d6efd; color: white; }
.rec-card {
    border: 1px solid #dee2e6;
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 12px;
    background: white;
    transition: box-shadow 0.2s;
}
.rec-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.rec-url {
    font-size: 12px;
    color: #666;
    word-break: break-all;
}
.confidence-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
}
.confidence-high { background: #d1e7dd; color: #0f5132; }
.confidence-mid  { background: #fff3cd; color: #856404; }
.confidence-low  { background: #f8d7da; color: #842029; }
.confidence-filtered { background: #e2e3e5; color: #41464b; }
.domain-badge {
    background: #e9ecef;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    color: #333;
}
.pref-info {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 16px;
    border: 1px solid #dee2e6;
}
.pref-bar {
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 6px;
}
.pref-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #0d6efd, #198754);
    border-radius: 4px;
    transition: width 0.3s;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <a href="{{ route('ref-articles.index') }}" class="btn btn-default btn-sm" style="margin-bottom:12px;">
            &larr; Kembali ke Ref Articles
        </a>

        {{-- Alert --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {!! session('success') !!}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {!! session('error') !!}
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {!! session('info') !!}
            </div>
        @endif

        @if($lowScoreCount > 0)
        <div class="alert alert-warning" style="border-radius:8px;">
            <strong>Peringatan:</strong> {{ $lowScoreCount }} URL dari hasil pencarian sebelumnya berkualitas rendah
            (score &lt; 45%) dan tidak ditampilkan.
            Klik <strong>Re-Research</strong> untuk cari artikel AI yang lebih relevan.
        </div>
        @endif

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <div>
                <h4 style="margin:0;">Rekomendasi untuk: <strong>{{ $keyword }}</strong></h4>
                <p style="margin:4px 0 0; color:#666; font-size:13px;">
                    {{ $recommendations->count() }} URL AI-focused ditemukan
                    @if($lowScoreCount > 0)
                        <span class="badge badge-warning" style="margin-left:8px;">
                            {{ $lowScoreCount }} hasil lama ditolak (score &lt; 45%)
                        </span>
                    @endif
                </p>
            </div>
            <div>
                <form action="{{ route('ref-articles.research') }}" method="POST" style="display:inline;">
                    @csrf
                    <input type="hidden" name="keyword" value="{{ $keyword }}">
                    <button type="submit" class="action-btn" style="background:#6f42c1; color:white;">
                        Re-Research
                    </button>
                </form>
            </div>
        </div>

        {{-- Editor Preference Info --}}
        @if($pref)
        <div class="pref-info">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong>AI Learning Stats</strong>
                    <span style="color:#888; font-size:12px; margin-left:8px;">
                        keyword: "{{ $pref->keyword }}"
                    </span>
                </div>
                <div style="text-align:right;">
                    <span style="font-size:20px; font-weight:700; color:#0d6efd;">{{ $pref->confidence }}%</span>
                    <span style="font-size:12px; color:#888;"> confidence</span>
                </div>
            </div>
            <div class="pref-bar">
                <div class="pref-bar-fill" style="width: {{ $pref->confidence }}%;"></div>
            </div>
            <div style="display:flex; gap:20px; margin-top:8px; font-size:12px; color:#666;">
                <span>Approved: <strong>{{ $pref->approved_count }}</strong></span>
                <span>Rejected: <strong>{{ $pref->rejected_count }}</strong></span>
                <span>Unpublished: <strong>{{ $pref->unpublished_count }}</strong></span>
            </div>
        </div>
        @endif

        {{-- Recommendations --}}
        @forelse($recommendations as $rec)
        <div class="rec-card">
            <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:8px;">
                <div style="flex:1;">
                    <h5 style="margin:0 0 4px; font-size:15px;">
                        {{ $rec->title ?? 'Tanpa judul' }}
                    </h5>
                    <div class="rec-url">
                        <span class="domain-badge">{{ $rec->domain }}</span>
                        <a href="{{ $rec->url }}" target="_blank" style="margin-left:8px;">
                            {{ Str::limit($rec->url, 60) }}
                        </a>
                    </div>
                </div>
                <div style="text-align:right; margin-left:12px;">
                    @php
                        $score = $rec->confidence_score ?? 50;
                        $cls = $score >= 65 ? 'confidence-high' : ($score >= 45 ? 'confidence-mid' : 'confidence-low');
                        $label = $score >= 65 ? 'AI-FOCUSED' : ($score >= 45 ? 'MEDIUM' : 'LOW');
                    @endphp
                    <span class="confidence-badge {{ $cls }}" style="font-size:11px;">{{ $label }} {{ round($score) }}%</span>
                </div>
            </div>

            @if($rec->snippet)
            <p style="font-size:13px; color:#555; margin:0 0 10px; font-style:italic;">
                "{{ $rec->snippet }}"
            </p>
            @endif

            <div style="display:flex; gap:8px; align-items:center;">
                @if($rec->status === 'pending')
                    <form action="{{ route('ref-articles.recommendation.approve', $rec->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="action-btn btn-approve"
                            onclick="return confirm('Scrape + Paraphrase artikel ini?')">
                            Approve &amp; Scrape
                        </button>
                    </form>
                    <form action="{{ route('ref-articles.recommendation.reject', $rec->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="action-btn btn-reject"
                            onclick="return confirm('Tolak URL ini? AI akan blocklist.')">
                            Reject
                        </button>
                    </form>
                @elseif($rec->status === 'approved')
                    <span class="badge badge-warning">Approved - Processing...</span>
                @elseif($rec->status === 'scraped')
                    <span class="badge badge-success">
                        Scraped @if($rec->ref_article_id) - Ref #{{ $rec->ref_article_id }}@endif
                    </span>
                @elseif($rec->status === 'rejected')
                    <span class="badge badge-danger">Rejected (Blocklisted)</span>
                @endif

                <a href="{{ $rec->url }}" target="_blank" class="btn btn-info btn-xs" style="border-radius:6px;">
                    Visit URL
                </a>
            </div>
        </div>
        @empty
        <div class="panel panel-default">
            <div class="panel-body text-center" style="padding:40px; color:#888;">
                <p>Tidak ada rekomendasi AI-focused untuk keyword "{{ $keyword }}" (score &gt;= 45%).</p>
                <p style="color:#666; font-size:13px;">
                    Pipeline akan mencari artikel dari Search Engine Land &amp; Search Engine Journal
                    yang mengandung topik AI (ChatGPT, Gemini, Machine Learning, OpenAI, dll).
                </p>
                <p>
                    <form action="{{ route('ref-articles.research') }}" method="POST" style="display:inline;">
                        @csrf
                        <input type="hidden" name="keyword" value="{{ $keyword }}">
                        <button type="submit" class="btn btn-primary btn-sm">
                            Jalankan Research
                        </button>
                    </form>
                </p>
            </div>
        </div>
        @endforelse

    </div>
</div>
@endsection
