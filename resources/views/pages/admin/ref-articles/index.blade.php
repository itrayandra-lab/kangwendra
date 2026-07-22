@extends('layouts.admin.app')
@section('title', $page)

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
                {{ session('error') }}
            </div>
        @endif

        {{-- Stats Cards --}}
        <div class="row mb-3">
            <div class="col-sm-2">
                <div class="panel panel-default text-center">
                    <div class="panel-body">
                        <h4>{{ $stats['total'] }}</h4>
                        <small>Total</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <a href="{{ route('ref-articles.index', ['status' => 'pending']) }}" class="text-warning">
                    <div class="panel panel-warning text-center">
                        <div class="panel-body">
                            <h4>{{ $stats['pending'] }}</h4>
                            <small>Pending</small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-2">
                <a href="{{ route('ref-articles.index', ['status' => 'processing']) }}" class="text-info">
                    <div class="panel panel-info text-center">
                        <div class="panel-body">
                            <h4>{{ $stats['processing'] }}</h4>
                            <small>Processing</small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-2">
                <a href="{{ route('ref-articles.index', ['status' => 'done']) }}" class="text-success">
                    <div class="panel panel-success text-center">
                        <div class="panel-body">
                            <h4>{{ $stats['done'] }}</h4>
                            <small>Done</small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-2">
                <a href="{{ route('ref-articles.index', ['status' => 'failed']) }}" class="text-danger">
                    <div class="panel panel-danger text-center">
                        <div class="panel-body">
                            <h4>{{ $stats['failed'] }}</h4>
                            <small>Failed</small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-2">
                <a href="{{ route('ref-articles.index') }}" class="btn btn-default btn-block" style="margin-top:5px">
                    <i class="fa fa-list"></i> Semua
                </a>
            </div>
        </div>

        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">
                    Management {{ $page }}
                    @if($status)
                        <span class="label label-default ml-2">Filter: {{ ucfirst($status) }}</span>
                    @endif
                </h3>
            </div>
            <div class="panel-body">

                {{-- Action Buttons --}}
                <div class="mb-3 d-flex" style="gap:8px; flex-wrap:wrap;">
                    {{-- Scrape --}}
                    <form action="{{ route('ref-articles.scrape') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm"
                            onclick="return confirm('Mulai scraping tech.yahoo.com?')">
                            <i class="fa fa-download"></i> Scrape Artikel Baru
                        </button>
                    </form>

                    {{-- Generate All --}}
                    <form action="{{ route('ref-articles.generate-all') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm"
                            onclick="return confirm('Generate AI untuk semua artikel pending?')">
                            <i class="fa fa-magic"></i> Generate AI Semua ({{ $stats['pending'] }} pending)
                        </button>
                    </form>
                </div>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th width="40">No</th>
                                <th>Judul Referensi</th>
                                <th width="130">Domain</th>
                                <th width="90">Tanggal</th>
                                <th width="100">Tags</th>
                                <th width="100">AI Status</th>
                                <th width="180">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($articles as $index => $article)
                                <tr>
                                    <td>{{ ($articles->currentPage() - 1) * $articles->perPage() + $index + 1 }}</td>
                                    <td>
                                        <a href="{{ route('ref-articles.show', $article) }}">
                                            {{ Str::limit($article->title, 80) }}
                                        </a>
                                        <br>
                                        <small class="text-muted">
                                            <a href="{{ $article->source_url }}" target="_blank">
                                                <i class="fa fa-external-link"></i> Sumber Asli
                                            </a>
                                        </small>
                                    </td>
                                    <td>{{ $article->source_domain }}</td>
                                    <td>
                                        {{ $article->published_at
                                            ? $article->published_at->format('d M Y')
                                            : '-' }}
                                    </td>
                                    <td>
                                        @if(!empty($article->tags))
                                            @foreach(array_slice($article->tags, 0, 2) as $tag)
                                                <span class="badge badge-secondary">{{ $tag }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $badgeMap = [
                                                'pending'    => 'warning',
                                                'processing' => 'info',
                                                'done'       => 'success',
                                                'failed'     => 'danger',
                                            ];
                                            $badge = $badgeMap[$article->ai_status] ?? 'default';
                                        @endphp
                                        <span class="badge badge-{{ $badge }}">
                                            {{ ucfirst($article->ai_status) }}
                                        </span>
                                        @if($article->ai_status === 'done' && $article->generatedPost)
                                            <br>
                                            <small>
                                                <a href="{{ route('posts.edit', $article->generated_post_id) }}"
                                                   class="text-success">
                                                    <i class="fa fa-edit"></i> Lihat Post
                                                </a>
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        {{-- Generate / Retry --}}
                                        @if($article->ai_status === 'pending')
                                            <form action="{{ route('ref-articles.generate', $article) }}"
                                                  method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-xs"
                                                    title="Generate AI">
                                                    <i class="fa fa-magic"></i> Generate
                                                </button>
                                            </form>
                                        @elseif($article->ai_status === 'failed')
                                            <form action="{{ route('ref-articles.retry', $article) }}"
                                                  method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-warning btn-xs"
                                                    title="{{ $article->ai_error }}">
                                                    <i class="fa fa-refresh"></i> Retry
                                                </button>
                                            </form>
                                        @elseif($article->ai_status === 'processing')
                                            <span class="text-info"><i class="fa fa-spinner fa-spin"></i> Processing...</span>
                                        @endif

                                        {{-- Detail --}}
                                        <a href="{{ route('ref-articles.show', $article) }}"
                                           class="btn btn-info btn-xs" title="Detail">
                                            <i class="fa fa-eye"></i>
                                        </a>

                                        {{-- Hapus --}}
                                        <form action="{{ route('ref-articles.destroy', $article) }}"
                                              method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs"
                                                onclick="return confirm('Hapus artikel referensi ini?')"
                                                title="Hapus">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        Belum ada artikel referensi.
                                        Klik <strong>Scrape Artikel Baru</strong> untuk mulai.
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
