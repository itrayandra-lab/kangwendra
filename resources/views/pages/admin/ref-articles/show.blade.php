@extends('layouts.admin.app')
@section('title', $page)

@section('content')
<div class="row">
    <div class="col-md-12">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {!! session('success') !!}
            </div>
        @endif

        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Detail Artikel Referensi</h3>
            </div>
            <div class="panel-body">

                <a href="{{ route('ref-articles.index') }}" class="btn btn-default btn-sm mb-3">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>

                <div class="row">
                    {{-- Kolom kiri: info artikel referensi --}}
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <strong><i class="fa fa-link"></i> Artikel Referensi (Sumber)</strong>
                            </div>
                            <div class="panel-body">
                                <table class="table table-bordered table-sm">
                                    <tr>
                                        <th width="130">Judul</th>
                                        <td>{{ $refArticle->title }}</td>
                                    </tr>
                                    <tr>
                                        <th>Domain</th>
                                        <td>{{ $refArticle->source_domain }}</td>
                                    </tr>
                                    <tr>
                                        <th>URL Sumber</th>
                                        <td>
                                            <a href="{{ $refArticle->source_url }}" target="_blank">
                                                {{ Str::limit($refArticle->source_url, 60) }}
                                                <i class="fa fa-external-link"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Penulis</th>
                                        <td>{{ $refArticle->author ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal Publish</th>
                                        <td>
                                            {{ $refArticle->published_at
                                                ? $refArticle->published_at->format('d M Y H:i')
                                                : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Tags</th>
                                        <td>
                                            @if(!empty($refArticle->tags))
                                                @foreach($refArticle->tags as $tag)
                                                    <span class="badge badge-secondary">{{ $tag }}</span>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>AI Status</th>
                                        <td>
                                            @php
                                                $badgeMap = [
                                                    'pending'    => 'warning',
                                                    'processing' => 'info',
                                                    'done'       => 'success',
                                                    'failed'     => 'danger',
                                                ];
                                                $badge = $badgeMap[$refArticle->ai_status] ?? 'default';
                                            @endphp
                                            <span class="badge badge-{{ $badge }}">
                                                {{ ucfirst($refArticle->ai_status) }}
                                            </span>
                                            @if($refArticle->ai_error)
                                                <br>
                                                <small class="text-danger">{{ $refArticle->ai_error }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                </table>

                                {{-- Action buttons --}}
                                @if($refArticle->ai_status === 'pending')
                                    <form action="{{ route('ref-articles.generate', $refArticle) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fa fa-magic"></i> Generate Artikel AI Sekarang
                                        </button>
                                    </form>
                                @elseif($refArticle->ai_status === 'failed')
                                    <form action="{{ route('ref-articles.retry', $refArticle) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-sm">
                                            <i class="fa fa-refresh"></i> Retry Generate AI
                                        </button>
                                    </form>
                                @elseif($refArticle->ai_status === 'done' && $refArticle->generatedPost)
                                    <a href="{{ route('posts.edit', $refArticle->generated_post_id) }}"
                                       class="btn btn-success btn-sm">
                                        <i class="fa fa-edit"></i> Lihat Artikel yang Dihasilkan
                                    </a>
                                @endif

                                @if($refArticle->image_url)
                                    <hr>
                                    <strong>Gambar Asli:</strong><br>
                                    <img src="{{ $refArticle->image_url }}"
                                         class="img-fluid img-thumbnail mt-2"
                                         style="max-height:200px;"
                                         alt="Gambar artikel referensi">
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Kolom kanan: artikel hasil generate --}}
                    <div class="col-md-6">
                        @if($refArticle->ai_status === 'done' && $refArticle->generatedPost)
                            @php $post = $refArticle->generatedPost; @endphp
                            <div class="panel panel-success">
                                <div class="panel-heading">
                                    <strong><i class="fa fa-magic"></i> Artikel Hasil Generate AI</strong>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-bordered table-sm">
                                        <tr>
                                            <th width="130">Judul Baru</th>
                                            <td>{{ $post->title }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                <span class="badge badge-{{ $post->status === 'active' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($post->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Published At</th>
                                            <td>{{ $post->published_at ? $post->published_at->format('d M Y H:i') : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tags</th>
                                            <td>
                                                @php
                                                    $postTags = is_array($post->tags)
                                                        ? $post->tags
                                                        : json_decode($post->tags, true);
                                                @endphp
                                                @foreach(($postTags ?? []) as $tag)
                                                    <span class="badge badge-info">{{ $tag }}</span>
                                                @endforeach
                                            </td>
                                        </tr>
                                    </table>
                                    <a href="{{ route('posts.edit', $post->id) }}" class="btn btn-primary btn-sm">
                                        <i class="fa fa-edit"></i> Edit Artikel
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="panel panel-default">
                                <div class="panel-body text-center text-muted">
                                    <i class="fa fa-clock-o fa-2x"></i>
                                    <p class="mt-2">Artikel AI belum dibuat.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Isi Artikel Referensi --}}
                <div class="panel panel-default mt-3">
                    <div class="panel-heading">
                        <strong><i class="fa fa-file-text"></i> Isi Artikel Referensi</strong>
                    </div>
                    <div class="panel-body" style="max-height:400px; overflow-y:auto; background:#f9f9f9; white-space:pre-wrap; font-size:13px;">
                        {{ $refArticle->content ?: 'Konten tidak tersedia.' }}
                    </div>
                </div>

                @if($refArticle->ai_status === 'done' && $refArticle->generatedPost)
                    <div class="panel panel-success mt-3">
                        <div class="panel-heading">
                            <strong><i class="fa fa-magic"></i> Isi Artikel Hasil AI</strong>
                        </div>
                        <div class="panel-body" style="max-height:400px; overflow-y:auto;">
                            {!! $refArticle->generatedPost->content !!}
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
