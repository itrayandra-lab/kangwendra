@extends('layouts.admin.app')
@section('title', $page)
@push('styles')
    <link href="{{ asset('dist/plugins/summernote/summernote.css') }}" rel="stylesheet" />
    <!-- Select2 CSS dari CDN -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .form-group {
            margin-bottom: 15px;
        }
        .select2-container {
            width: 100% !important;
        }
        #imagePreview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: {{ $post->image ? 'block' : 'none' }};
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Perbarui {{ $page }}</h3>
                </div>
                <div class="panel-body">
                    {{-- Status Info Box --}}
                    <div style="background:#f8f9fa; border:1px solid #ddd; border-radius:8px; padding:12px 16px; margin-bottom:16px;">
                        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                            @php
                                $isDraft = $post->status === 'inactive';
                                $isNoSchedule = $post->status === 'active' && !$post->published_at;
                                $isPublished = $post->status === 'active' && $post->published_at && $post->published_at <= now();
                                $isScheduled = $post->status === 'active' && $post->published_at && $post->published_at > now();
                                if ($isDraft) {
                                    $badgeClass = 'label-warning';
                                    $badgeText = 'Draft';
                                } elseif ($isNoSchedule) {
                                    $badgeClass = 'label-default';
                                    $badgeText = 'Tanpa Jadwal';
                                } elseif ($isPublished) {
                                    $badgeClass = 'label-success';
                                    $badgeText = 'Published';
                                } elseif ($isScheduled) {
                                    $badgeClass = 'label-info';
                                    $badgeText = 'Terjadwal';
                                } else {
                                    $badgeClass = 'label-default';
                                    $badgeText = 'Unknown';
                                }
                            @endphp
                            <div>
                                <span class="label {{ $badgeClass }}" style="font-size:13px; padding:5px 12px;">{{ $badgeText }}</span>
                            </div>
                            <div style="font-size:13px; color:#555;">
                                @if($isPublished)
                                    <i class="fa fa-check-circle text-success"></i> Sudah dipublikasikan pada <strong>{{ $post->published_at?->format('d M Y H:i') ?? 'N/A' }} WIB</strong>
                                @elseif($isScheduled)
                                    <i class="fa fa-clock-o text-info"></i> Akan dipublikasikan pada <strong>{{ $post->published_at?->format('d M Y H:i') ?? 'N/A' }} WIB</strong>
                                    <span style="color:#999; font-size:12px; margin-left:8px;">(belum masuk beranda)</span>
                                @elseif($isDraft)
                                    <i class="fa fa-pencil text-warning"></i> Post masih draft, tidak ditampilkan di beranda
                                @elseif($isNoSchedule)
                                    <i class="fa fa-calendar-times-o text-muted"></i> Post tanpa jadwal publish, tidak muncul di beranda otomatis
                                @else
                                    <i class="fa fa-question-circle text-muted"></i> Status tidak jelas
                                @endif
                            </div>
                            <div style="margin-left:auto; font-size:12px; color:#999;">
                                @if($post->source && $post->source !== 'web')
                                    <span class="label label-info" style="font-size:11px;">AI</span>
                                @else
                                    <span class="label label-default" style="font-size:11px;">Web</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('posts.update', $post->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT') 
                        <div class="form-group">
                            <label for="title">Judul</label>
                            <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $post->title) }}" required>
                            @error('title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="image">Gambar</label>
                            <input type="file" name="image" id="image" class="form-control" accept="image/*">
                            @error('image')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            <img id="imagePreview" src="{{ $post->image ? getFile($post->image) : '#' }}" alt="Image Preview" />
                        </div>

                        <div class="form-group">
                            <label for="content">Konten</label>
                            <textarea name="content" id="content" class="form-control summernote" rows="5">{{ old('content', $post->content) }}</textarea>
                            @error('content')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="category_id">Kategori</label>
                            <select name="category_id" id="category_id" class="form-control" required>
                                <option value="">Pilih Kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="tags">Tags</label>
                            <select name="tags[]" id="tags" class="form-control select2" multiple="multiple" required>
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', $post->tags ?? [])) ? 'selected' : '' }}>
                                        {{ $tag?->name ?? 'Tag' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tags')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="active" {{ old('status', $post->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $post->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="published_at">Tanggal Publikasi</label>
                            <input type="datetime-local" required name="published_at" id="published_at" class="form-control" value="{{ old('published_at', $post->published_at ? $post->published_at?->format('Y-m-d\TH:i') : '') }}">
                            @error('published_at')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-success">Simpan</button>
                        <a href="{{ route('posts.index') }}" class="btn btn-default">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('dist/plugins/summernote/summernote.min.js') }}"></script>
    <!-- Perbaiki CDN Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tags').select2({
                placeholder: "Pilih tags",
                allowClear: true
            });

            $('#image').change(function(e) {
                var file = e.target.files[0];
                var preview = $('#imagePreview');
                
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        preview.attr('src', e.target.result);
                        preview.css('display', 'block');
                    }
                    reader.readAsDataURL(file);
                } else {
                    preview.attr('src', '{{ $post->image ? getFile($post->image) : '#' }}');
                    preview.css('display', '{{ $post->image ? 'block' : 'none' }}');
                }
            });
        });

        jQuery(document).ready(function() {
            $('.summernote').summernote({
                height: 300,
                minHeight: null,
                maxHeight: null,
                callbacks: {
                    onImageUpload: function(files) {
                        var formData = new FormData();
                        formData.append('file', files[0]);
                        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                        Swal.fire({
                            title: 'Uploading...',
                            text: 'Harap tunggu, gambar sedang diunggah.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: '{{ route('uploadImage') }}',
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                Swal.close(); 

                                if (response.url) {
                                    var imageUrl = response.url;
                                    var image = $('<img>').attr('src', imageUrl);
                                    $('.summernote').summernote('insertNode', image[0]);

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: 'Gambar berhasil diunggah.',
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                }
                            },
                            error: function(e) {
                                Swal.close();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops!',
                                    text: 'Terjadi kesalahan saat mengunggah gambar.',
                                });
                            }
                        });
                    },

                    onMediaDelete: function(target) {
                         var fullUrl = target[0].src;
                        var imagePath = fullUrl.replace(window.location.origin + '/', '');

                        Swal.fire({
                            title: 'Menghapus...',
                            text: 'Harap tunggu, gambar sedang dihapus.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: '{{ route('deleteImage') }}',
                            method: 'POST',
                            data: {
                                image_path: imagePath,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                Swal.close();
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: 'Gambar berhasil dihapus.',
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                }
                            },
                            error: function(e) {
                                Swal.close();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops!',
                                    text: 'Terjadi kesalahan saat menghapus gambar.',
                                });
                            }
                        });
                    }
                }
            });
        });
    </script>
@endpush

