@extends('layouts.admin.app')
@section('title', $page)
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Edit {{ $page }}</h3>
                </div>
                <div class="panel-body">
                    <form action="{{ route('album.update', $album->id) }}" method="POST" id="albumForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="name">Judul</label>
                            <input type="text" name="name" class="form-control" required value="{{ old('name', $album->name) }}">
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Masukkan deskripsi singkat album">{{ old('description', $album->description) }}</textarea>
                            @error('description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="photos">Upload Foto</label>
                            <div class="dropzone" id="photoDropzone" style="border: 2px dashed #ccc; border-radius: 5px; padding: 20px; text-align: center; background: #f9f9f9;">
                                <div class="fallback">
                                    <input name="photos[]" type="file" multiple>
                                </div>
                            </div>
                            @error('photos.*')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            <!-- Menampilkan foto yang sudah ada (opsional) -->
                            <div class="existing-photos mt-3">
                                @foreach ($album->photos as $photo)
                                    <div class="photo-wrapper" style="display: inline-block; margin: 5px;" id="photo-{{ $photo->id }}">
                                        <img src="{{ getFile($photo->image) }}" alt="Existing Photo" class="photo-img" style="width: 100px; height: 100px; object-fit: cover;">
                                        <button type="button" class="btn btn-danger btn-xs delete-btn" data-url="{{ route('album.photo.destroy', [$album->id, $photo->id]) }}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success" id="submitButton">Perbarui</button>
                        <a href="{{ route('album.index') }}" class="btn btn-default">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">
    <style>
        #submitButton {
            position: relative;
            overflow: hidden;
        }

        #submitButton.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 3px solid #fff;
            border-top: 3px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        #submitButton.loading {
            pointer-events: none;
            opacity: 0.7;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
    <script>
        var myDropzone = new Dropzone('#photoDropzone', {
            url: '{{ route('album.update', $album->id) }}',
            paramName: 'photos',
            maxFilesize: 2,
            acceptedFiles: 'image/*',
            addRemoveLinks: true,
            autoProcessQueue: false,
            uploadMultiple: true,
            parallelUploads: 10,
            init: function() {
                this.on('success', function(file, response) {
                    console.log(response);
                });
                this.on('error', function(file, errorMessage) {
                    console.error(errorMessage);
                });
            }
        });

        document.getElementById('albumForm').addEventListener('submit', function(e) {
            e.preventDefault();

            console.log('Form submitted!');
            var formData = new FormData(this);
            var dropzone = Dropzone.forElement('#photoDropzone');
            var submitButton = document.getElementById('submitButton');

            submitButton.classList.add('loading');
            submitButton.disabled = true;

            if (dropzone.getQueuedFiles().length > 0) {
                dropzone.getQueuedFiles().forEach(function(file) {
                    formData.append('photos[]', file);
                });

                fetch('{{ route('album.update', $album->id) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    submitButton.classList.remove('loading');
                    submitButton.disabled = false;
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses!',
                            text: data.message,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = data.redirect;
                            }
                        });
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error(error);
                    submitButton.classList.remove('loading');
                    submitButton.disabled = false;
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                });
            } else {
                submitButton.classList.remove('loading');
                submitButton.disabled = false;
                this.submit();
            }
        });

        $(document).on('click', '.delete-btn', function() {
            let url = $(this).data('url');
            let photoWrapper = $(this).closest('.photo-wrapper');

            Swal.fire({
                title: 'Hapus Foto?',
                text: 'Foto ini akan dihapus secara permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            Swal.fire('Terhapus!', response.message, 'success');
                            photoWrapper.fadeOut(300, function() { $(this).remove(); });
                        },
                        error: function(xhr) {
                            Swal.fire('Terjadi kesalahan', xhr.responseJSON.message, 'error');
                        }
                    });
                }
            });
        });
    </script>
@endpush

