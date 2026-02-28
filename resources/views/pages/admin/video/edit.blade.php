@extends('layouts.admin.app')
@section('title', $page)
@push('styles')
    <link href="{{ asset('dist/plugins/summernote/summernote.css') }}" rel="stylesheet" />
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
            display: {{ $video->image ? 'block' : 'none' }};
        }
        #youtubePreview {
            margin-top: 10px;
            display: block;
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Edit {{ $page }}</h3>
                </div>
                <div class="panel-body">
                    <form action="{{ route('video.update', $video->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="title">Judul</label>
                            <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $video->title) }}" required>
                            @error('title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="link_yt">Link YouTube</label>
                            <input type="text" name="link_yt" id="link_yt" class="form-control" value="{{ old('link_yt', $video->link_yt) }}" required>
                            @error('link_yt')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            <div id="youtubePreview">
                                <iframe width="560" height="315" src="https://www.youtube.com/embed/{{ getYoutubeVideoId($video->link_yt) }}" frameborder="0" allowfullscreen></iframe>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="image">Gambar</label>
                            <input type="file" name="image" id="image" class="form-control" accept="image/*">
                            @error('image')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            <img id="imagePreview" src="{{ $video->image ? getFile($video->image) : '#' }}" alt="Image Preview" />
                        </div>

                        <div class="form-group">
                            <label for="description">Deskripsi</label>
                            <textarea name="description" id="description" class="form-control summernote" rows="5">{{ old('description', $video->description) }}</textarea>
                            @error('description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-success">Simpan</button>
                        <a href="{{ route('video.index') }}" class="btn btn-default">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('dist/plugins/summernote/summernote.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Preview gambar saat file dipilih
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
                }
            });

            $('#link_yt').on('input', function() {
                var youtubeUrl = $(this).val();
                var preview = $('#youtubePreview');
                var videoId = getYoutubeVideoId(youtubeUrl);

                if (videoId) {
                    var embedUrl = 'https://www.youtube.com/embed/' + videoId;
                    preview.html('<iframe width="560" height="315" src="' + embedUrl + '" frameborder="0" allowfullscreen></iframe>');
                    preview.css('display', 'block');
                } else {
                    preview.html('<p class="text-danger">Link YouTube tidak valid.</p>');
                    preview.css('display', 'block');
                }
            });

            function getYoutubeVideoId(url) {
                var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
                var match = url.match(regExp);
                return (match && match[2].length == 11) ? match[2] : null;
            }

            // Summernote initialization
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

@php
    function getYoutubeVideoId($url) {
        preg_match('/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/', $url, $match);
        return (isset($match[2]) && strlen($match[2]) == 11) ? $match[2] : null;
    }
@endphp

