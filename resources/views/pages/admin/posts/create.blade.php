@extends('layouts.admin.app')
@section('title', $page)

@push('styles')
    <link href="{{ asset('dist/plugins/summernote/summernote.css') }}" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .form-group { margin-bottom: 15px; }
        .select2-container { width: 100% !important; }
        .editor-container { border: 1px solid #ddd; border-radius: 4px; padding: 0; }
        .main-editor-panel { box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        
        .sidebar-collapsed .container {
            max-width: none !important;
            width: 100% !important;
        }
        
        .featured-image-upload {
            border: 1px solid #ddd;
            border-bottom: none;
            padding: 10px 15px;
            border-radius: 4px 4px 0 0;
            background-color: #f9f9f9;
        }
        #mainImagePreview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            display: none;
            border: 1px dashed #ccc;
            border-radius: 4px;
        }
        .judul-input-group {
            border: 1px solid #ddd;
            border-top: none; 
            padding: 10px 15px;
            border-radius: 0 0 0 0; 
        }
        .judul-input-group input {
            border: 1px solid rgb(123, 123, 123);
            padding: 2px 6px;
            height: auto;
            font-size: 20px;
            font-weight: 500;
        }
        .summernote-wrapper .note-editor {
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 4px 4px;
        }
        .setting-panel {
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 0;
            margin-bottom: 20px;
        }
        .setting-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
        }
        .setting-header .btn { border-radius: 4px; }
        .btn-success {
            background-color: #ff8c00;
            border-color: #ff8c00;
            color: white;
            font-weight: bold;
        }
        .btn-success:hover, .btn-success:focus {
            background-color: #e67e22;
            border-color: #e67e22;
        }
        .setting-item {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }
        .setting-item:last-child { border-bottom: none; }
        .setting-item .title {
            font-weight: bold;
            color: #333;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .setting-content {
            padding-top: 10px;
            display: none;
        }
        .setting-item.open .setting-content { display: block; }
        .setting-item .fa-angle-down { transition: transform 0.2s; }
        .setting-item.open .fa-angle-down { transform: rotate(180deg); }
        .domain-card-list {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 5px;
        }
        .domain-card {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            transition: all 0.2s;
        }
        .domain-card:hover { border-color: #ff8c00; }
        .domain-card.checked {
            background-color: #fff9f0;
            border-left: 5px solid #ff8c00;
            padding-left: 5px;
        }
        .domain-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .domain-header label {
            font-weight: bold;
            margin: 0;
            flex-grow: 1;
            cursor: pointer;
        }
        .domain-header input[type="checkbox"] {
            margin-right: 8px;
            vertical-align: middle;
        }
        .domain-details {
            border-top: 1px dashed #eee;
            padding-top: 10px;
        }
        .domain-details label {
            font-weight: normal;
            font-size: 12px;
            color: #555;
            margin-bottom: 3px;
        }
        .domain-details img {
            max-width: 100%; 
            max-height: 100px; 
            margin-top: 5px; 
            border: 1px dashed #ccc; 
            padding: 3px; 
            display: none;
            border-radius: 3px;
        }
    </style>
@endpush

@section('content')

<form id="submit-form" enctype="multipart/form-data">
    @csrf
    <div class="row">
        
        <div class="col-md-8">
            <div class="main-editor-panel">
                
                <div class="featured-image-upload">
                    <label for="featured_image" style="font-weight: bold; color: #555;">Gambar Utama</label>
                    <input type="file" name="featured_image" id="featured_image" class="form-control" accept="image/*">
                    <img id="mainImagePreview" src="#" alt="Main Image Preview" />
                    @error('featured_image') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="judul-input-group">
                    <input type="text" name="title" id="title" class="form-control" placeholder="Tulis Judul..." value="{{ old('title') }}" required>
                    @error('title') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="" style="margin: 10px 0 15px 15px;">
                    <label style="font-weight: bold; color: #555;">Tanggal Publish (Web Utama)</label>
                    <input type="datetime-local" name="published_at" id="published_at" class="form-control" value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}" required>
                    @error('published_at') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                
                <div class="summernote-wrapper">
                    <textarea name="content" id="content" class="form-control summernote" rows="5">{{ old('content') }}</textarea>
                    @error('content') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="setting-panel">
                <div class="setting-header">
                    <div class="btn-group"></div>
                    <button type="submit" id="submit-btn" class="btn btn-success">
                        <i class="fa fa-paper-plane"></i> <span>Publikasikan</span>
                    </button>
                </div>

                <div class="setting-item open" id="setting-label">
                    <div class="title" data-target="#content-label">
                        Label <i class="fa fa-angle-down"></i>
                    </div>
                    <div class="setting-content" id="content-label">
                        <div class="form-group" style="margin-bottom: 0;">
                            <select name="tags[]" id="tags" class="form-control select2" multiple="multiple" required>
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', [])) ? 'selected' : '' }}>
                                        {{ $tag?->name ?? 'Tag' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="setting-item open" id="setting-status">
                    <div class="title" data-target="#content-status">
                        Status <i class="fa fa-angle-down"></i>
                    </div>
                    <div class="setting-content" id="content-status">
                        <div class="form-group" style="margin-bottom: 0;">
                            <select name="status" class="form-control input-sm">
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="setting-item" id="setting-category">
                    <div class="title" data-target="#content-category">
                        Kategori Utama <i class="fa fa-angle-down"></i>
                    </div>
                    <div class="setting-content" id="content-category">
                        <div class="form-group" style="margin-bottom: 0;">
                            <select name="category_id" class="form-control input-sm" required>
                                <option value="">Pilih Kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="setting-item" id="setting-domains" @if(!$isMaster) style="display: none;" @endif>
                    <div class="title" data-target="#content-domains">
                        Artikel Share <i class="fa fa-angle-down"></i>
                    </div>
                    <div class="setting-content" id="content-domains">
                        @if($isMaster)
                            <div class="domain-card-list">
                                @php $old_domains_checked = old('domains'); @endphp
                                @forelse($domains as $index => $domain)
                                    @php
                                        $domain_key = str_replace('.', '_', $domain->domain_name);
                                        $is_checked = ($old_domains_checked !== null) ? in_array($domain->domain_name, $old_domains_checked) : false;
                                    @endphp
                                    
                                    <div class="domain-card {{ $is_checked ? 'checked' : '' }}" id="domain-card-{{ $domain_key }}">
                                        <div class="domain-header">
                                            <label for="domain-checkbox-{{ $domain_key }}">
                                                <input type="checkbox" name="domains[]" id="domain-checkbox-{{ $domain_key }}" 
                                                       value="{{ $domain->domain_name }}" data-domain-key="{{ $domain_key }}" 
                                                       {{ $is_checked ? 'checked' : '' }}>
                                                {{ $domain->domain_name }}
                                            </label>
                                        </div>
                                        <div class="domain-details">
                                            
                                            <div class="form-group">
                                                <label>Tanggal Publish (Domain Ini)</label>
                                                <input type="datetime-local" name="domain_published_at[{{ $domain_key }}]" 
                                                       class="form-control input-sm" 
                                                       value="{{ old("domain_published_at.{$domain_key}", now()->format('Y-m-d\TH:i')) }}">
                                            </div>

                                            <div class="form-group" style="margin-bottom: 0;">
                                                <label>Gambar Kustom (Opsional)</label>
                                                <input type="file" name="image[{{ $domain_key }}]" class="form-control input-sm domain-image-input" data-preview="#preview-{{ $domain_key }}" accept="image/*">
                                                <img id="preview-{{ $domain_key }}" src="#" alt="Preview" />
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted" style="padding: 10px; font-size: 12px;">Tidak ada domain yang dikonfigurasi.</p>
                                @endforelse
                            </div>
                        @else
                            <div class="alert alert-info" style="margin: 10px; font-size: 12px;">
                                <i class="fa fa-info-circle"></i> Portal ini bukan portal utama. Fitur share domain tidak tersedia.
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="{{ asset('dist/plugins/summernote/summernote.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $('#tags').select2({ placeholder: "Pilih tags", allowClear: true });

    $('#featured_image').change(function(e) {
        var file = e.target.files[0];
        var preview = $('#mainImagePreview');
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.attr('src', e.target.result).css('display', 'block');
            }
            reader.readAsDataURL(file);
        } else {
            preview.hide();
        }
    });

    $('.domain-image-input').change(function(e) {
        var file = e.target.files[0];
        var preview = $($(this).data('preview'));
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.attr('src', e.target.result).css('display', 'block');
            }
            reader.readAsDataURL(file);
        } else {
            preview.hide();
        }
    });
    
    $('.domain-card input[type="checkbox"]').on('change', function() {
        if ($(this).is(':checked')) {
            $(this).closest('.domain-card').addClass('checked');
        } else {
            $(this).closest('.domain-card').removeClass('checked');
        }
    });
    
    $('.domain-card input[type="checkbox"]').each(function() {
         if ($(this).is(':checked')) {
            $(this).closest('.domain-card').addClass('checked');
        }
    });

    $('.summernote').summernote({
        height: 700,
        toolbar: [
            ['style', ['style']], ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']], ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']], ['table', ['table']],
            ['insert', ['link', 'picture', 'video', 'hr']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onImageUpload: function(files) {
                var formData = new FormData();
                formData.append('file', files[0]);
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                Swal.fire({ title: 'Uploading...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '{{ route("uploadImage") }}', 
                    method: 'POST', 
                    data: formData, 
                    processData: false, 
                    contentType: false,
                    success: function(response) {
                        Swal.close();
                        if (response.url) {
                            var img = $('<img>').attr('src', response.url);
                            $('.summernote').summernote('insertNode', img[0]);
                        }
                    },
                    error: function() { 
                        Swal.close(); 
                        Swal.fire('Error', 'Gagal mengunggah gambar.', 'error'); 
                    }
                });
            },
            onMediaDelete: function(target) {
                var fullUrl = target[0].src;
                var imagePath = fullUrl.replace(window.location.origin + '/', '');
                $.post('{{ route("deleteImage") }}', { 
                    image_path: imagePath, 
                    _token: $('meta[name="csrf-token"]').attr('content') 
                });
            }
        }
    });
    
    $('.setting-item .title').click(function() {
        var parent = $(this).closest('.setting-item');
        if(parent.hasClass('open')) {
            parent.removeClass('open');
            parent.find('.setting-content').slideUp(200);
        } else {
             $('.setting-item.open').removeClass('open').find('.setting-content').slideUp(200);
             parent.addClass('open').find('.setting-content').slideDown(200);
        }
    });

    $('#submit-form').submit(function(e) {
        e.preventDefault();
        const btn = $('#submit-btn');
        const span = btn.find('span');
        
        const formData = new FormData(this);
        formData.set('content', $('.summernote').summernote('code'));

        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        btn.prop('disabled', true).addClass('btn-loading');
        span.html('<i class="fa fa-spinner fa-spin"></i> Memproses...');

        $.ajax({
            url: '/portal/posts', 
            method: 'POST', 
            data: formData, 
            processData: false, 
            contentType: false,
            success: function(res) {
                console.log('Success response:', res);
                Swal.fire('Sukses!', res.message, 'success').then(function() {
                    window.location = '/portal/posts';
                });
            },
            error: function(xhr) {
                console.log('Error response:', xhr.responseJSON);
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan.';
                Swal.fire('Gagal', msg, 'error');
            },
            complete: function() {
                btn.prop('disabled', false).removeClass('btn-loading');
                span.html('Publikasikan');
            }
        });
    });
});
</script>
@endpush

