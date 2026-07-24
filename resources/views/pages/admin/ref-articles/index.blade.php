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
    .btn-scrape    { background: #0d6efd; color: white; }
    .btn-pharma    { background: #0dcaf0; color: white; }
    .btn-generate  { background: #198754; color: white; }
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
            <div style="font-weight:700; margin-bottom:12px; font-size:15px;">
                Workflow: Scrape &rarr; Generate AI &rarr; Publish Otomatis
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                {{-- Scrape Buttons --}}
                <form action="{{ route('ref-articles.scrape-yahoo') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="action-btn btn-scrape"
                        onclick="return confirm('Scrape Yahoo Tech (maks 5 artikel)?')">
                        Scrape Yahoo Tech
                    </button>
                </form>

                <form action="{{ route('ref-articles.scrape-pharma') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="action-btn btn-pharma"
                        onclick="return confirm('Scrape Tech Pharma (maks 3 artikel)?')">
                        Scrape Pharma
                    </button>
                </form>

                <form action="{{ route('ref-articles.scrape-all') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="action-btn btn-scrape-all"
                        onclick="return confirm('Scrape semua sumber sekaligus?')">
                        Scrape Semua
                    </button>
                </form>

                <div style="width:1px; height:35px; background:#dee2e6; margin:0 5px;"></div>

                {{-- Generate AI Button --}}
                <form action="{{ route('ref-articles.generate-all') }}" method="POST" style="display:inline;">
                    @csrf
                    <input type="hidden" name="limit" value="5">
                    <button type="submit" class="action-btn btn-generate"
                        onclick="return confirm('Generate AI untuk {{ $stats['pending'] }} artikel pending? (maks 5)')">
                        Generate AI ({{ $stats['pending'] }} pending)
                    </button>
                </form>

                @if($stats['failed'] > 0)
                <div style="background:#f8d7da; padding:8px 14px; border-radius:8px; font-size:13px; color:#842029;">
                    {{ $stats['failed'] }} gagal - klik Failed tab untuk retry
                </div>
                @endif
            </div>
            <div style="margin-top:10px; font-size:12px; color:#666;">
                Scrape = ambil dari tech.yahoo.com & pharma, masuk daftar Pending.
                Generate AI = paraphrase DeepSeek ke Bahasa Indonesia, masuk Post (draft).
                Publish = otomatis 08:00 / 13:00 / 16:00 WIB.
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
                                            Sumber asli
                                        </a>
                                    </small>
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
                                        <br>
                                        <small>
                                            <a href="{{ route('post_detail', [$article->generatedPost?->category?->slug ?? 'uncategorized', $article->generatedPost?->slug ?? '']) }}" target="_blank" class="text-info">
                                                Lihat
                                            </a>
                                        </small>
                                    @endif
                                    @if($article->ai_status === 'failed' && $article->ai_error)
                                        <br>
                                        <small class="text-danger" title="{{ $article->ai_error }}">
                                            ⚠️ {{ Str::limit($article->ai_error, 50) }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($article->ai_status === 'pending')
                                        <form action="{{ route('ref-articles.generate', $article) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-xs">
                                                Generate
                                            </button>
                                        </form>
                                    @elseif($article->ai_status === 'failed')
                                        <form action="{{ route('ref-articles.retry', $article) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-xs">
                                                Retry
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('ref-articles.show', $article) }}" class="btn btn-info btn-xs">
                                        Detail
                                    </a>

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
                                    <strong>Klik "Scrape" di atas untuk mulai.</strong>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $articles->links() }}
            </div>
        </div>

    </div>
</div>
@endsection
