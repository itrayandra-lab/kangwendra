@extends('layouts.admin.app')

@section('title', 'Tambah Iklan')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Tambah Iklan Baru</h3>
            </div>
            <div class="panel-body">

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('ads.store') }}" method="POST" enctype="multipart/form-data" id="adForm">
                    @csrf

                    <!-- Judul Iklan -->
                    <div class="form-group">
                        <label for="title">Judul Iklan <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                        @error('title')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Tipe Iklan -->
                    <div class="form-group">
                        <label for="type">Tipe Iklan <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="">-- Pilih Tipe Iklan --</option>
                            <option value="image" {{ old('type') == 'image' ? 'selected' : '' }}>Gambar (JPG/PNG)</option>
                            <option value="gif" {{ old('type') == 'gif' ? 'selected' : '' }}>GIF Animasi</option>
                            <option value="youtube" {{ old('type') == 'youtube' ? 'selected' : '' }}>Video YouTube</option>
                        </select>
                        @error('type')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Upload Gambar / GIF (hanya muncul jika tipe image atau gif) -->
                    <div class="form-group" id="fileUploadSection" style="display: none;">
                        <label for="file">Upload Gambar / GIF <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept="image/jpeg,image/png,image/jpg,image/gif">
                        <small class="text-muted">Maksimal 2MB. Format: JPG, PNG, GIF</small>
                        @error('file')
                            <br><small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- URL YouTube (hanya muncul jika tipe youtube) -->
                    <div class="form-group" id="youtubeSection" style="display: none;">
                        <label for="youtube_url">URL Video YouTube <span class="text-danger">*</span></label>
                        <input type="url" name="youtube_url" class="form-control" 
                               placeholder="https://www.youtube.com/watch?v=xxxxxxxxxxx atau https://youtu.be/xxxxxxxxxxx"
                               value="{{ old('youtube_url') }}">
                        <small class="text-muted">Contoh: https://www.youtube.com/watch?v=dQw4w9WgXcQ</small>
                        @error('youtube_url')
                            <br><small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Redirect URL -->
                    <div class="form-group">
                        <label for="redirect_url">Link Tujuan (Redirect URL)</label>
                        <input type="url" name="redirect_url" class="form-control" 
                               placeholder="https://contoh.com/promosi" value="{{ old('redirect_url') }}">
                        <small class="text-muted">Kosongkan jika tidak ingin redirect saat diklik</small>
                        @error('redirect_url')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Status Aktif -->
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" value="1" checked>
                            Aktifkan iklan ini
                        </label>
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-success" id="submitBtn">
                        <i class="fa fa-save"></i> Simpan Iklan
                    </button>
                    <a href="{{ route('ads.index') }}" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #submitBtn.loading {
        pointer-events: none;
        opacity: 0.7;
    }
    #submitBtn.loading::after {
        content: '';
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #fff;
        border-top: 2px solid transparent;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-left: 10px;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('type');
        const fileSection = document.getElementById('fileUploadSection');
        const youtubeSection = document.getElementById('youtubeSection');
        const submitBtn = document.getElementById('submitBtn');

        function toggleFields() {
            const type = typeSelect.value;

            // Reset semua
            fileSection.style.display = 'none';
            youtubeSection.style.display = 'none';
            fileSection.querySelector('input').removeAttribute('required');
            youtubeSection.querySelector('input').removeAttribute('required');

            if (type === 'image' || type === 'gif') {
                fileSection.style.display = 'block';
                fileSection.querySelector('input').setAttribute('required', 'required');
                if (type === 'gif') {
                    fileSection.querySelector('input').accept = 'image/gif';
                } else {
                    fileSection.querySelector('input').accept = 'image/jpeg,image/png,image/jpg';
                }
            } else if (type === 'youtube') {
                youtubeSection.style.display = 'block';
                youtubeSection.querySelector('input').setAttribute('required', 'required');
            }
        }

        typeSelect.addEventListener('change', toggleFields);
        toggleFields(); 

        document.getElementById('adForm').addEventListener('submit', function() {
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Menyimpan...';
        });
    });
</script>
@endpush

