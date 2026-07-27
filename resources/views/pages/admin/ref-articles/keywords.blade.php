@extends('layouts.admin.app')
@section('title', $page)

@push('styles')
<style>
    .keyword-card {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .keyword-card .kw-name {
        font-weight: 600;
        font-size: 15px;
        color: #333;
    }
    .keyword-card .kw-meta {
        font-size: 12px;
        color: #888;
        margin-top: 2px;
    }
    .keyword-card .kw-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .keyword-count {
        font-size: 12px;
        color: #666;
        margin-bottom: 12px;
    }
    .clear-blocklist-panel {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .clear-blocklist-panel .msg {
        font-size: 13px;
        color: #856404;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">

        {{-- Breadcrumb / Back --}}
        <div style="margin-bottom: 12px;">
            <a href="{{ route('ref-articles.index') }}" class="btn btn-default btn-sm">
                &larr; Kembali ke Ref Articles
            </a>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {!! session('success') !!}
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {!! session('info') !!}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {!! session('error') !!}
            </div>
        @endif

        {{-- Clear Blocklists Panel --}}
        <div class="clear-blocklist-panel">
            <div>
                <strong style="color:#856404;">Utility:</strong>
                <span class="msg">Hapus semua blocklist_urls dan blocklist_patterns dari seluruh EditorPreference record. Berguna setelah test runs yang mengisi blocklist.</span>
            </div>
            <form action="{{ route('ref-articles.clear-blocklists') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm"
                    onclick="return confirm('Yakin ingin menghapus semua blocklist?')">
                    Clear All Blocklists
                </button>
            </form>
        </div>

        {{-- Add Keyword Form --}}
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Tambah Kata Kunci Baru</h3>
            </div>
            <div class="panel-body">
                <form action="{{ route('ref-articles.keywords.store') }}" method="POST" class="form-inline">
                    @csrf
                    <div class="form-group" style="flex:1; min-width:250px;">
                        <input type="text" name="keyword" class="form-control"
                            placeholder="Contoh: AI coding, Grok, Perplexity"
                            required minlength="2" maxlength="50"
                            style="width:100%; border-radius:8px;">
                        @error('keyword')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-success" style="border-radius:8px; margin-left:8px;">
                        <i class="fa fa-plus"></i> Tambah
                    </button>
                </form>
                <small class="text-muted" style="margin-top:6px; display:block;">
                    Kata kunci harus unik, min 2 karakter, maks 50 karakter.
                </small>
            </div>
        </div>

        {{-- Keywords List --}}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Daftar Kata Kunci</h3>
            </div>
            <div class="panel-body">
                <p class="keyword-count">
                    Total: <strong>{{ $keywords->count() }}</strong> kata kunci
                </p>

                @if($keywords->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm" style="margin-bottom:0;">
                            <thead class="thead-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Kata Kunci</th>
                                    <th width="100">Topik</th>
                                    <th width="80">Approved</th>
                                    <th width="80">Rejected</th>
                                    <th width="100">Confidence</th>
                                    <th width="150">Tanggal Dibuat</th>
                                    <th width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($keywords as $i => $kw)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <strong>{{ $kw->keyword }}</strong>
                                        @if($kw->blocklist_urls)
                                            <span class="badge badge-warning" title="Ada blocklist">BL</span>
                                        @endif
                                    </td>
                                    <td>{{ $kw->topic ?: '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-success">{{ $kw->approved_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-danger">{{ $kw->rejected_count }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $conf = $kw->confidence ?? 0;
                                            $cls = $conf >= 85 ? 'confidence-high' : ($conf >= 60 ? 'confidence-mid' : 'confidence-low');
                                        @endphp
                                        <span class="{{ $cls }}">{{ $conf }}%</span>
                                    </td>
                                    <td>{{ $kw->created_at->format('d M Y') }}</td>
                                    <td>
                                        <form action="{{ route('ref-articles.keywords.destroy', $kw->id) }}" method="POST" style="display:inline;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs"
                                                onclick="return confirm('Hapus kata kunci \'{{ $kw->keyword }}\'?')">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted" style="padding:30px;">
                        Belum ada kata kunci. Gunakan form di atas untuk menambahkan.
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
