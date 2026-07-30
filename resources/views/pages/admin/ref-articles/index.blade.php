@extends('layouts.admin.app')
@section('title', $page)

@push('styles')
<style>
.action-panel {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #dee2e6;
}
.action-btn {
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}
.action-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.btn-research { background: #0d6efd; color: white; }
.btn-scrape-all { background: #6f42c1; color: white; }
.stat-card {
    border-radius: 10px;
    padding: 15px;
    text-align: center;
    border: 1px solid #dee2e6;
    background: white;
}
.stat-card .num { font-size: 28px; font-weight: 700; }
.stat-card .lbl { font-size: 12px; color: #666; margin-top: 4px; }
.badge-status {
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}
.badge-pending    { background: #fff3cd; color: #856404; }
.badge-processing { background: #cfe2ff; color: #084298; }
.badge-done       { background: #d1e7dd; color: #0f5132; }
.badge-failed     { background: #f8d7da; color: #842029; }
.filter-tabs {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 15px;
}
.filter-tab {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    text-decoration: none;
    border: 1px solid #dee2e6;
    color: #666;
    background: white;
}
.filter-tab.active {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
}
.filter-tab .cnt {
    background: rgba(0,0,0,0.1);
    padding: 1px 6px;
    border-radius: 10px;
    font-size: 11px;
    margin-left: 4px;
}
.filter-tab.active .cnt { background: rgba(255,255,255,0.2); }
.pipeline-flow {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #666;
    flex-wrap: wrap;
}
.pipeline-step {
    background: #e9ecef;
    padding: 4px 12px;
    border-radius: 6px;
    font-weight: 600;
    color: #333;
}
.pipeline-arrow { color: #aaa; font-size: 16px; }
.ai-stats-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 12px;
}
.ai-stat-box {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 10px 16px;
    text-align: center;
    min-width: 120px;
}
.ai-stat-box .num { font-size: 20px; font-weight: 700; color: #333; }
.ai-stat-box .lbl { font-size: 11px; color: #888; margin-top: 2px; }
.confidence-high { color: #198754; font-weight: 700; }
.confidence-mid  { color: #fd7e14; font-weight: 600; }
.confidence-low  { color: #dc3545; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">

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

        {{-- Filter Tabs --}}
        <div class="filter-tabs">
            <a href="{{ route('ref-articles.index') }}" class="filter-tab {{ !$status ? 'active' : '' }}">
                Semua <span class="cnt">{{ $stats['total'] }}</span>
            </a>
            <a href="{{ route('ref-articles.index', ['status' => 'idle']) }}" class="filter-tab {{ $status === 'idle' ? 'active' : '' }}">
                Idle <span class="cnt">{{ $stats['idle'] }}</span>
            </a>
            <a href="{{ route('ref-articles.index', ['status' => 'done']) }}" class="filter-tab {{ $status === 'done' ? 'active' : '' }}">
                Selesai <span class="cnt">{{ $stats['done'] }}</span>
            </a>
            <a href="{{ route('ref-articles.index', ['status' => 'failed']) }}" class="filter-tab {{ $status === 'failed' ? 'active' : '' }}">
                Failed <span class="cnt">{{ $stats['failed'] }}</span>
            </a>
        </div>

        {{-- Action Panel --}}
        <div class="action-panel">
            <div class="pipeline-flow" style="margin-bottom:12px;">
                <span class="pipeline-step">1. Scraping</span>
                <span class="pipeline-arrow">&rarr;</span>
                <span class="pipeline-step">2. Research</span>
                <span class="pipeline-arrow">&rarr;</span>
                <span class="pipeline-step">3. Approve</span>
                <span class="pipeline-arrow">&rarr;</span>
                <span class="pipeline-step">4. Generate</span>
                <span class="pipeline-arrow">&rarr;</span>
                <span class="pipeline-step">Auto Publish</span>
                <span class="pipeline-arrow">&rarr;</span>
                <span class="pipeline-step">{{ implode(' / ', App\Models\ScraperConfig::getPublishScheduleHours()) }} WIB</span>
            </div>

            {{-- Flow Banner --}}
            <div style="background:#e8f5e9; border:1px solid #4caf50; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-size:13px; color:#2e7d32;">
                <strong>Flow:</strong> Scraping → Research keyword → Hasil Scraping → <strong>[Approve]</strong> →
                <strong>Ref Articles</strong> (halaman ini) → <strong>[Generate]</strong> → Auto Publish
            </div>

            {{-- Links & Generate All --}}
            <div style="margin-bottom:12px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                <a href="{{ route('admin.scraping.index') }}" class="btn btn-primary btn-sm" style="border-radius:8px; padding:6px 16px; font-weight:600;">
                    <i class="fa fa-search"></i> Ke Halaman Scraping
                </a>

                @if($stats['idle'] > 0)
                    <form action="{{ route('ref-articles.generate-all') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm" style="border-radius:8px; padding:6px 16px; font-weight:600;"
                            onclick="return confirm('Generate semua Ref Articles idle? Maksimal 5 per batch.')">
                            <i class="fa fa-bolt"></i> Generate All Idle ({{ $stats['idle'] }})
                        </button>
                    </form>
                @endif
            </div>

            {{-- AI Stats --}}
            <div class="ai-stats-row">
                <div class="ai-stat-box">
                    <div class="num">{{ $stats['total'] }}</div>
                    <div class="lbl">Total</div>
                </div>
                <div class="ai-stat-box">
                    <div class="num" style="color:#ffc107;">{{ $stats['idle'] }}</div>
                    <div class="lbl">Idle (siap Generate)</div>
                </div>
                <div class="ai-stat-box">
                    <div class="num" style="color:#0d6efd;">{{ $stats['done'] }}</div>
                    <div class="lbl">Selesai</div>
                </div>
                <div class="ai-stat-box">
                    <div class="num" style="color:#dc3545;">{{ $stats['failed'] }}</div>
                    <div class="lbl">Gagal</div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm" style="margin-bottom:0;">
                        <thead class="thead-light">
                            <tr>
                                <th width="40">#</th>
                                <th>Judul Referensi</th>
                                <th width="120">Domain</th>
                                <th width="80">Tanggal</th>
                                <th width="80">Tags</th>
                                <th width="100">Status AI</th>
                                <th width="200">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($articles as $i => $article)
                            <tr>
                                <td>{{ ($articles->currentPage() - 1) * $articles->perPage() + $i + 1 }}</td>
                                <td>
                                    <a href="{{ route('ref-articles.show', $article) }}" title="{{ $article->title }}">
                                        {{ Str::limit($article->title, 65) }}
                                    </a>
                                    <br>
                                    <small>
                                        <a href="{{ $article->source_url }}" target="_blank" class="text-muted">
                                            {{ Str::limit($article->source_url, 40) }}
                                        </a>
                                    </small>
                                    @if($article->source_keyword)
                                    <br>
                                    <small>
                                        <span class="badge badge-light">kw: {{ $article->source_keyword }}</span>
                                    </small>
                                    @endif
                                </td>
                                <td><span class="badge badge-secondary">{{ $article->source_domain }}</span></td>
                                <td>{{ $article->published_at ? $article->published_at->format('d M Y') : '-' }}</td>
                                <td>
                                    @if(!empty($article->tags))
                                        @foreach(array_slice($article->tags, 0, 2) as $tag)
                                            <span class="badge badge-info">{{ $tag }}</span>
                                        @endforeach
                                    @endif
                                </td>
                                <td>
                                    @php $aiStatus = $article->ai_research_status; @endphp
                                    @if(in_array($aiStatus, ['researching', 'processing']))
                                        <span class="badge-status badge-processing">{{ ucfirst($aiStatus) }}</span>
                                    @elseif($aiStatus === 'done')
                                        <span class="badge-status badge-done">Done</span>
                                    @elseif($aiStatus === 'failed')
                                        <span class="badge-status badge-failed">Failed</span>
                                    @else
                                        <span class="badge-status badge-pending">Idle</span>
                                    @endif
                                    @if($aiStatus === 'done' && $article->generated_post_id)
                                        <br>
                                        <small>
                                            <a href="{{ route('posts.edit', $article->generated_post_id) }}" target="_blank" class="text-success">
                                                Edit Post
                                            </a>
                                        </small>
                                    @endif
                                    @if($aiStatus === 'failed' && $article->ai_error)
                                        <br>
                                        <small class="text-danger" title="{{ $article->ai_error }}">
                                            {{ Str::limit($article->ai_error, 30) }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if(in_array($aiStatus, [null, 'idle', 'failed']))
                                        <form action="{{ route('ref-articles.generate', $article) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-xs"
                                                onclick="return confirm('Generate paraphrase untuk artikel ini?')">
                                                <i class="fa fa-bolt"></i> Generate
                                            </button>
                                        </form>
                                    @endif
                                    @if($aiStatus === 'failed')
                                        <form action="{{ route('ref-articles.retry', $article) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning btn-xs">
                                                <i class="fa fa-refresh"></i> Retry
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('ref-articles.show', $article) }}" class="btn btn-info btn-xs">
                                        Detail
                                    </a>
                                    <form action="{{ route('ref-articles.destroy', $article) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs"
                                            onclick="return confirm('Hapus Ref Article ini?')">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted" style="padding:30px;">
                                    Belum ada Ref Article.<br>
                                    <strong>Pergi ke menu Scraping untuk research keyword, lalu Approve di Hasil Scraping.</strong>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($articles->lastPage() > 1)
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 12px;">
                    <span style="font-size: 13px; color: #888;">
                        Halaman {{ $articles->currentPage() }} dari {{ $articles->lastPage() }}
                        &nbsp;|&nbsp;
                        Total {{ $articles->total() }} artikel
                    </span>
                    <div style="display: flex; gap: 4px; align-items: center;">
                        @if($articles->currentPage() > 1)
                            <a href="{{ $articles->url(1) }}" class="btn btn-default btn-xs">&laquo;</a>
                            <a href="{{ $articles->previousPageUrl() }}" class="btn btn-default btn-xs">&lsaquo;</a>
                        @endif

                        @php
                            $start = max(1, $articles->currentPage() - 2);
                            $end = min($articles->lastPage(), $articles->currentPage() + 2);
                            if ($end - $start < 4) {
                                if ($start == 1) { $end = min($articles->lastPage(), 5); }
                                if ($end == $articles->lastPage()) { $start = max(1, $end - 4); }
                            }
                        @endphp
                        @for($i = $start; $i <= $end; $i++)
                            <a href="{{ $articles->url($i) }}"
                                class="btn btn-xs {{ $i == $articles->currentPage() ? 'btn-primary' : 'btn-default' }}">
                                {{ $i }}
                            </a>
                        @endfor

                        @if($articles->currentPage() < $articles->lastPage())
                            <a href="{{ $articles->nextPageUrl() }}" class="btn btn-default btn-xs">&rsaquo;</a>
                            <a href="{{ $articles->url($articles->lastPage()) }}" class="btn btn-default btn-xs">&raquo;</a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
