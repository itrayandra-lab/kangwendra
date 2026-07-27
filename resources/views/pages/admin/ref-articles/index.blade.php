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
            <a href="{{ route('ref-articles.index', ['status' => 'pending']) }}" class="filter-tab {{ $status === 'pending' ? 'active' : '' }}">
                Pending <span class="cnt">{{ $stats['pending'] }}</span>
            </a>
            <a href="{{ route('ref-articles.index', ['status' => 'processing']) }}" class="filter-tab {{ $status === 'processing' ? 'active' : '' }}">
                Processing <span class="cnt">{{ $stats['processing'] }}</span>
            </a>
            <a href="{{ route('ref-articles.index', ['status' => 'done']) }}" class="filter-tab {{ $status === 'done' ? 'active' : '' }}">
                Done <span class="cnt">{{ $stats['done'] }}</span>
            </a>
            <a href="{{ route('ref-articles.index', ['status' => 'failed']) }}" class="filter-tab {{ $status === 'failed' ? 'active' : '' }}">
                Failed <span class="cnt">{{ $stats['failed'] }}</span>
            </a>
        </div>

        {{-- Action Panel --}}
        <div class="action-panel">
            <div class="pipeline-flow" style="margin-bottom:12px;">
                <span class="pipeline-step">1. Research</span>
                <span class="pipeline-arrow">&rarr;</span>
                <span class="pipeline-step">2. Scrape + Paraphrase</span>
                <span class="pipeline-arrow">&rarr;</span>
                <span class="pipeline-step">3. Auto Publish</span>
                <span class="pipeline-arrow">&rarr;</span>
                <span class="pipeline-step">08:00 / 13:00 / 16:00 WIB</span>
            </div>

            {{-- Keyword Research Form --}}
            <div>
                {{-- Quick Topic Buttons --}}
                <div style="margin-bottom:10px;">
                    <span style="font-size:12px; color:#888; margin-right:8px;">Topik AI:</span>
                    @foreach(['ChatGPT', 'Gemini', 'Claude', 'DeepSeek', 'OpenAI', 'LLM', 'SEO AI', 'AI Agent', 'Machine Learning', 'Artificial Intelligence', 'Anthropic', 'Mistral'] as $topic)
                        <form action="{{ route('ref-articles.research') }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="keyword" value="{{ $topic }}">
                            <button type="submit" class="btn btn-outline-primary btn-sm" style="border-radius:15px; padding:2px 10px; font-size:11px; margin:2px;">
                                {{ $topic }}
                            </button>
                        </form>
                    @endforeach
                    <a href="{{ route('ref-articles.keywords.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius:15px; padding:2px 10px; font-size:11px; margin:2px; text-decoration:none;" title="Kelola Kata Kunci">
                        Kelola Kata Kunci
                    </a>
                </div>

                {{-- Manual Keyword Search --}}
                <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <form action="{{ route('ref-articles.research') }}" method="POST" style="display:flex; gap:8px; flex:1; max-width:400px;">
                        @csrf
                        <input type="text" name="keyword" class="form-control" placeholder="Atau ketik topik AI baru (contoh: Grok, Perplexity, AI coding, Claude Code)" required style="border-radius:8px;">
                        <button type="submit" class="action-btn btn-research">
                            Research
                        </button>
                    </form>
                </div>
            </div>

            {{-- AI Stats --}}
            <div class="ai-stats-row">
                <div class="ai-stat-box">
                    <div class="num">{{ $stats['total'] }}</div>
                    <div class="lbl">Total Ref Articles</div>
                </div>
                <div class="ai-stat-box">
                    <div class="num">{{ $stats['pending'] }}</div>
                    <div class="lbl">Pending</div>
                </div>
                <div class="ai-stat-box">
                    <div class="num">{{ $stats['processing'] }}</div>
                    <div class="lbl">Processing</div>
                </div>
                <div class="ai-stat-box">
                    <div class="num">{{ $stats['done'] }}</div>
                    <div class="lbl">Published</div>
                </div>
                <div class="ai-stat-box">
                    <div class="num">{{ $prefStats['avg_confidence'] ?? 0 }}%</div>
                    <div class="lbl">Avg Confidence</div>
                </div>
                <div class="ai-stat-box">
                    <div class="num">{{ $prefStats['high_confidence'] ?? 0 }}</div>
                    <div class="lbl">High Confidence</div>
                </div>
            </div>

            @if($stats['failed'] > 0)
            <div style="background:#f8d7da; padding:8px 14px; border-radius:8px; font-size:13px; color:#842029; margin-top:10px;">
                {{ $stats['failed'] }} gagal - klik tab Failed untuk detail
            </div>
            @endif
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
                                    <span class="badge-status badge-{{ $article->ai_status }}">
                                        @if($article->ai_status === 'pending') Pending
                                        @elseif($article->ai_status === 'processing') Processing
                                        @elseif($article->ai_status === 'done') Done
                                        @elseif($article->ai_status === 'failed') Failed
                                        @endif
                                    </span>
                                    @if($article->ai_status === 'done' && $article->generated_post_id)
                                        <br>
                                        <small>
                                            <a href="{{ route('posts.edit', $article->generated_post_id) }}" target="_blank" class="text-success">
                                                Edit Post
                                            </a>
                                        </small>
                                        <br>
                                        <small>
                                            <a href="{{ route('ref-articles.edit-post', $article) }}" class="text-primary">
                                                Edit Cepat
                                            </a>
                                        </small>
                                    @endif
                                    @if($article->ai_status === 'failed' && $article->ai_error)
                                        <br>
                                        <small class="text-danger" title="{{ $article->ai_error }}">
                                            {{ Str::limit($article->ai_error, 40) }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($article->ai_status === 'done')
                                        <a href="{{ route('ref-articles.show', $article) }}" class="btn btn-info btn-xs">
                                            Detail
                                        </a>
                                    @elseif($article->ai_status === 'failed')
                                        <form action="{{ route('ref-articles.show', $article) }}" method="GET" style="display:inline;">
                                            <button type="submit" class="btn btn-warning btn-xs">
                                                Lihat Error
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('ref-articles.show', $article) }}" class="btn btn-info btn-xs">
                                            Detail
                                        </a>
                                    @endif

                                    <form action="{{ route('ref-articles.destroy', $article) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs"
                                            onclick="return confirm('Hapus?')">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted" style="padding:30px;">
                                    Belum ada artikel referensi.<br>
                                    <strong>Ketik keyword di atas dan klik "Research" untuk mulai.</strong>
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
