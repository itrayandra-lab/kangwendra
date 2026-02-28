@extends('layouts.admin.app')
@section('title', $page)
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Tambah Halaman</h3>
                </div>
                <div class="panel-body">
                    <form action="{{ route('pages.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="title">Judul</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                            @error('title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="content">Konten</label>
                            <textarea name="content" class="form-control summernote" rows="5">{{ old('content') }}</textarea>
                            @error('content')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" class="form-control">
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive
                                </option>
                            </select>
                            @error('status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-success">Simpan</button>
                        <a href="{{ route('pages.index') }}" class="btn btn-default">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('styles')
    <link href="{{ asset('dist/plugins/summernote/summernote.css') }}" rel="stylesheet" />
@endpush
@push('scripts')
    <!--Summernote js-->
    <script src="{{ asset('dist/plugins/summernote/summernote.min.js') }}"></script>
    <script>
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


