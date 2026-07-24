@extends('layouts.admin.app')
@section('title', $page)

@push('styles')
<style>
    .edit-wrap { max-width: 960px; margin: 0 auto; }
    .info-strip {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 12px 16px;
        margin-bottom: 20px;
        font-size: 13px;
        color: #555;
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    .info-strip span { color: #333; }
    .form-section {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 16px;
    }
    .form-section h5 {
        margin: 0 0 16px;
        font-size: 14px;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #0d6efd;
        padding-bottom: 8px;
    }
    textarea.content-area {
        font-family: 'Courier New', monospace;
        font-size: 12px;
        min-height: 380px;
        line-height: 1.6;
    }
    .word-info {
        font-size: 12px;
        color: #888;
        margin-top: 4px;
    }
    .tag-chip {
        display: inline-block;
        background: #e3dff5;
        color: #5a3e8a;
        padding: 3px 10px;
        border-radius: 12px;
        margin: 2px;
        font-size: 12px;
    }
    .action-row {
        display: flex;
        gap: 10px;
        align-items: center;
        padding-top: 10px;
    }
</style>
@endpush

@section('content')
<div class="edit-wrap">

    <div style="margin-bottom: 20px;">
        <a href="{{ route('ref-articles.index') }}" class="btn btn-default btn-sm">
            &larr; Kembali
        </a>
        <span style="margin-left: 12px; font-size: 16px; font-weight: 600;">
            Edit Post
        </span>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="info-strip">
        <div>Ref: <span>#{{ $refArticle->id }}</span></div>
        <div>Status AI: <span class="badge badge-{{ $refArticle->ai_status }}">{{ $refArticle->ai_status }}</span></div>
        <div>Source: <a href="{{ $refArticle->source_url }}" target="_blank" style="font-size:12px;">{{ Str::limit($refArticle->source_url, 45) }}</a></div>
        <div>Domain: <span>{{ $refArticle->source_domain }}</span></div>
        @if(isset($post->meta_data['publish_slot']))
            <div>Slot: <span>{{ $post->meta_data['publish_slot'] }}</span></div>
        @endif
        @if(isset($post->meta_data['edited_at']))
            <div>Last edit: <span>{{ $post->meta_data['edited_at'] }}</span></div>
        @endif
    </div>

    <form action="{{ route('ref-articles.update-post', $refArticle) }}" method="POST" id="editForm">
        @csrf
        @method('PUT')

        <div class="form-section">
            <h5>Judul & Link</h5>
            <div class="form-group mb-3">
                <label class="font-weight-bold">Judul</label>
                <input type="text" name="title" class="form-control"
                    value="{{ old('title', $post->title) }}" required maxlength="255">
                @error('title') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="form-group mb-0">
                <label>Slug</label>
                <input type="text" name="slug" class="form-control" style="font-size:13px;"
                    value="{{ old('slug', $post->slug) }}">
                <small class="text-muted">Biarkan kosong untuk auto-generate dari judul</small>
            </div>
        </div>

        <div class="form-section">
            <h5>Pengaturan Publish</h5>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category_id" class="form-control" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ old('category_id', $post->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="active" {{ old('status', $post->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="draft" {{ old('status', $post->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Jadwal Publish</label>
                        <input type="datetime-local" name="published_at" class="form-control"
                            value="{{ old('published_at', $post->published_at ? date('Y-m-d\TH:i', strtotime($post->published_at)) : '') }}" required>
                        <small class="text-muted">WIB, contoh: 2026-07-25T08:00</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h5>Tags</h5>
            <div class="form-group mb-2">
                <label>Tags</label>
                <input type="text" name="tags_string" class="form-control"
                    value="{{ old('tags_string', is_array($post->tags) ? implode(', ', $post->tags) : '') }}"
                    placeholder="ketik tag, pisahkan dengan koma">
                <small class="text-muted">Pisahkan dengan koma. Tags saat ini:</small>
            </div>
            @if(is_array($post->tags) && count($post->tags))
                <div style="margin-top:6px;">
                    @foreach($post->tags as $tag)
                        <span class="tag-chip">{{ $tag }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="form-section">
            <h5>Konten HTML</h5>
            <div class="form-group mb-1">
                <textarea name="content" class="form-control content-area"
                    required>{{ old('content', $post->content) }}</textarea>
                @error('content') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="word-info">
                <span id="charCnt">{{ strlen(strip_tags($post->content)) }}</span> karakter
                &nbsp;|&nbsp;
                <span id="wordCnt">{{ str_word_count(strip_tags($post->content)) }}</span> kata
            </div>
        </div>

        <div class="action-row">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('posts.edit', $post->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                Edit Penuh
            </a>
            <a href="{{ route('post_detail', [$post->category->slug ?? 'uncategorized', $post->slug]) }}"
                target="_blank" class="btn btn-outline-info btn-sm">
                Lihat Post
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ta = document.querySelector('textarea[name="content"]');
    if (ta) {
        ta.addEventListener('input', function() {
            var t = this.value.replace(/<[^>]*>/g, '');
            document.getElementById('charCnt').textContent = t.length;
            document.getElementById('wordCnt').textContent = t.trim() ? t.trim().split(/\s+/).length : 0;
        });
    }
});
</script>
@endpush
