@extends('layouts.admin.app')
@section('title', $page)

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-tagsinput@0.8.0/dist/bootstrap-tagsinput.css" rel="stylesheet">
<style>
    .edit-post-container { max-width: 1000px; margin: 0 auto; }
    .meta-info-box {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        font-size: 13px;
    }
    .meta-info-box .badge { font-size: 11px; }
    .tag-current {
        display: inline-block;
        background: #e9ecef;
        padding: 3px 10px;
        border-radius: 15px;
        margin: 2px;
        font-size: 12px;
    }
    textarea[name="content"] {
        font-family: 'Courier New', monospace;
        font-size: 13px;
        min-height: 400px;
    }
</style>
@endpush

@section('content')
<div class="edit-post-container">
    <div class="row">
        <div class="col-md-12">

            {{-- Header --}}
            <div style="display:flex; align-items:center; gap:15px; margin-bottom:20px;">
                <a href="{{ route('ref-articles.index') }}" class="btn btn-secondary">
                    ← Kembali
                </a>
                <h4 style="margin:0;">{{ $page }}</h4>
            </div>

            {{-- Info Box --}}
            <div class="meta-info-box">
                <div style="display:flex; gap:20px; flex-wrap:wrap;">
                    <div>
                        <strong>RefArticle:</strong> #{{ $refArticle->id }}
                        <span class="badge badge-{{ $refArticle->ai_status }}">
                            {{ ucfirst($refArticle->ai_status) }}
                        </span>
                    </div>
                    <div>
                        <strong>Source:</strong>
                        <a href="{{ $refArticle->source_url }}" target="_blank" style="font-size:12px;">
                            {{ Str::limit($refArticle->source_url, 50) }}
                        </a>
                    </div>
                    <div>
                        <strong>Domain:</strong> {{ $refArticle->source_domain }}
                    </div>
                    @if($post->meta_data && isset($post->meta_data['publish_slot']))
                        <div>
                            <strong>Slot:</strong> {{ $post->meta_data['publish_slot'] }}
                        </div>
                    @endif
                    @if($post->meta_data && isset($post->meta_data['edited_at']))
                        <div>
                            <strong>Edited:</strong> {{ $post->meta_data['edited_at'] }}
                            ({{ $post->meta_data['edited_by'] ?? 'unknown' }})
                        </div>
                    @endif
                </div>
            </div>

            {{-- Alerts --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- Form Edit --}}
            <form action="{{ route('ref-articles.update-post', $refArticle) }}" method="POST" id="editForm">
                @csrf
                @method('PUT')

                {{-- Title --}}
                <div class="form-group mb-3">
                    <label><strong>Judul</strong></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title', $post->title) }}" required maxlength="255">
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Row: Category + Status + Published At --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label><strong>Kategori</strong></label>
                        <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $post->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label><strong>Status</strong></label>
                        <select name="status" class="form-control">
                            <option value="active" {{ old('status', $post->status) === 'active' ? 'selected' : '' }}>✅ Active</option>
                            <option value="draft" {{ old('status', $post->status) === 'draft' ? 'selected' : '' }}>📝 Draft</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label><strong>Jadwal Publish</strong></label>
                        <input type="datetime-local" name="published_at" class="form-control"
                            value="{{ old('published_at', $post->published_at ? date('Y-m-d\TH:i', strtotime($post->published_at)) : '' }}" required>
                        <small class="text-muted">WIB timezone. Contoh: 2026-07-25T08:00</small>
                    </div>
                </div>

                {{-- Tags --}}
                <div class="form-group mb-3">
                    <label><strong>Tags</strong> <small class="text-muted">(tekan Enter untuk tambah)</small></label>
                    <input type="text" name="tags_input" id="tagsInput" class="form-control"
                        value="{{ old('tags', is_array($post->tags) ? implode(',', $post->tags) : '') }}"
                        data-role="tagsinput" placeholder="Tambah tag...">
                    <input type="hidden" name="tags" id="tagsHidden" value="">
                    <small class="text-muted">Tags saat ini: </small>
                    @if(is_array($post->tags))
                        @foreach($post->tags as $tag)
                            <span class="tag-current">{{ $tag }}</span>
                        @endforeach
                    @endif
                </div>

                {{-- Slug --}}
                <div class="form-group mb-3">
                    <label><strong>Slug</strong> <small class="text-muted">(auto-generated dari judul)</small></label>
                    <input type="text" name="slug" class="form-control"
                        value="{{ old('slug', $post->slug) }}">
                </div>

                {{-- Content --}}
                <div class="form-group mb-3">
                    <label>
                        <strong>Konten HTML</strong>
                        <small class="text-muted">(langsung HTML, bukan WYSIWYG)</small>
                    </label>
                    <textarea name="content" id="contentEditor"
                        class="form-control @error('content') is-invalid @enderror"
                        style="min-height:500px; font-family:'Courier New',monospace; font-size:13px;"
                        required>{{ old('content', $post->content) }}</textarea>
                    @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div style="margin-top:5px; font-size:12px; color:#666;">
                        Karakter: <span id="charCount">{{ strlen(strip_tags($post->content)) }}</span> |
                        <span id="wordCount">{{ str_word_count(strip_tags($post->content)) }}</span> kata
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-success" style="padding:10px 30px; font-weight:600;">
                        💾 Simpan Perubahan
                    </button>
                    <a href="{{ route('posts.edit', $post->id) }}" target="_blank" class="btn btn-outline-primary">
                        🔗 Edit Penuh di Posts Manager
                    </a>
                    <a href="{{ route('post_detail', [$post->category->slug ?? 'uncategorized', $post->slug]) }}"
                        target="_blank" class="btn btn-outline-secondary">
                        👁 Preview Post
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap-tagsinput@0.8.0/dist/bootstrap-tagsinput.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tagsinput init
    var tagsInput = document.getElementById('tagsInput');
    if (tagsInput) {
        $(tagsInput).tagsinput({
            maxTags: 10,
            maxChars: 50,
            trimValue: true,
            confirmKeys: [13, 44],
            focusClass: 'focus'
        });

        // Sync hidden field on change
        $(tagsInput).on('itemAdded itemRemoved', function() {
            var tags = $(tagsInput).tagsinput('items');
            document.getElementById('tagsHidden').value = JSON.stringify(tags);
        });

        // Init hidden field
        var initialTags = $(tagsInput).val();
        if (initialTags) {
            document.getElementById('tagsHidden').value = JSON.stringify(initialTags.split(','));
        }
    }

    // Character count
    var contentEditor = document.getElementById('contentEditor');
    if (contentEditor) {
        contentEditor.addEventListener('input', function() {
            var text = this.value.replace(/<[^>]*>/g, '');
            document.getElementById('charCount').textContent = text.length;
            document.getElementById('wordCount').textContent = text.trim().split(/\s+/).filter(function(w) { return w.length > 0; }).length;
        });
    }

    // Form submit - sync tags
    document.getElementById('editForm').addEventListener('submit', function() {
        if (tagsInput) {
            var tags = $(tagsInput).tagsinput('items');
            document.getElementById('tagsHidden').value = JSON.stringify(tags);
        }
    });
});
</script>
@endpush
