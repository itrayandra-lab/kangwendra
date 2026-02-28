@extends('layouts.admin.app')
@section('title', $page)

@push('styles')
<style>
.d-none {
    display: none !important;
}
.btn-group .btn {
    margin-right: 0;
}
.btn-group .btn.active.btn-success {
    background-color: #5cb85c;
    border-color: #4cae4c;
    color: white;
    box-shadow: inset 0 3px 5px rgba(0,0,0,.125);
}
.btn-group .btn.active {
    box-shadow: inset 0 3px 5px rgba(0,0,0,.125);
}
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Manajemen Identitas Web</h3>
                </div>
                <div class="panel-body">

                    <form action="{{ route('web-identity.store-or-update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <!-- Kolom Kiri -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="web_name">Web Name</label>
                                    <input type="text" name="web_name" class="form-control"
                                        value="{{ old('web_name', $webIdentity->web_name ?? '') }}"
                                        placeholder="Kuli IT Tecno">
                                    @error('web_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ old('email', $webIdentity->email ?? '') }}"
                                        placeholder="kuliittecno@example.com">
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="domain">Domain</label>
                                    <input type="url" name="domain" class="form-control"
                                        value="{{ old('domain', $webIdentity->domain ?? '') }}"
                                        placeholder="https://kuliittecno.my.id">
                                    @error('domain')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone_number">Phone Number</label>
                                    <input type="text" name="phone_number" class="form-control"
                                        value="{{ old('phone_number', $webIdentity->phone_number ?? '') }}"
                                        placeholder="+62 812 3456 7890">
                                    @error('phone_number')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="facebook_link">Facebook Link</label>
                                    <input type="url" name="facebook_link" class="form-control"
                                        value="{{ old('facebook_link', $webIdentity->facebook_link ?? '') }}"
                                        placeholder="https://www.facebook.com/kuliittecno5">
                                    @error('facebook_link')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="instagram_link">Instagram Link</label>
                                    <input type="url" name="instagram_link" class="form-control"
                                        value="{{ old('instagram_link', $webIdentity->instagram_link ?? '') }}"
                                        placeholder="https://instagram.com/kuliittecno5">
                                    @error('instagram_link')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="youtube_link">YouTube Link</label>
                                    <input type="url" name="youtube_link" class="form-control"
                                        value="{{ old('youtube_link', $webIdentity->youtube_link ?? '') }}"
                                        placeholder="https://youtube.com/kuliittecno5">
                                    @error('youtube_link')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="twitter_link">Twitter Link</label>
                                    <input type="url" name="twitter_link" class="form-control"
                                        value="{{ old('twitter_link', $webIdentity->twitter_link ?? '') }}"
                                        placeholder="https://twitter.com/kuliittecno5">
                                    @error('twitter_link')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="google_maps">Google Maps</label>
                                    <input type="text" name="google_maps" class="form-control"
                                        value="{{ old('google_maps', $webIdentity->google_maps ?? '') }}"
                                        placeholder="https://maps.google.com/?q=kuliittecno">
                                    @error('google_maps')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                 <div class="form-group">
                                    <label for="status">Status</label>
                                    <select name="status" class="form-control">
                                        <option value="" selected disabled>-- pilih --</option>
                                        <option value="active"
                                        {{ old('status', $webIdentity->status ?? 'active') === 'active' ? 'selected' : '' }}>
                                        Active</option>
                                        <option value="inactive"
                                            {{ old('status', $webIdentity->status ?? 'active') === 'inactive' ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                    @error('status')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Konfigurasi Portal</label>
                                    
                                    <div class="form-check">
                                        <input type="hidden" name="is_master" value="0">
                                        
                                        <input class="form-check-input" 
                                            type="checkbox" 
                                            name="is_master" 
                                            value="1" 
                                            id="isMasterPortal" 
                                            {{ old('is_master', $webIdentity->is_master ?? false) ? 'checked' : '' }}>
                                        
                                        <label class="form-check-label" for="isMasterPortal">
                                            Jadikan Portal Main
                                        </label>
                                    </div>

                                    <small style="color: rgb(0, 77, 93); font-style: italic;">
                                        *Centang jika ini adalah portal utama/master.
                                    </small>

                                    @error('is_master')
                                        <br><span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                            </div>

                            <!-- Kolom Kanan -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="meta_title">Meta Title</label>
                                    <input type="text" name="meta_title" class="form-control"
                                        value="{{ old('meta_title', $webIdentity->meta_title ?? '') }}"
                                        placeholder="Selamat Datang di kuliittecno">
                                    @error('meta_title')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="meta_description">Meta Description</label>
                                    <input type="text" name="meta_description" class="form-control"
                                        value="{{ old('meta_description', $webIdentity->meta_description ?? '') }}"
                                        placeholder="Deskripsi situs kuliittecno">
                                    @error('meta_description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="meta_keywords">Meta Keywords</label>
                                    <input type="text" name="meta_keywords" class="form-control"
                                        value="{{ old('meta_keywords', $webIdentity->meta_keywords ?? '') }}"
                                        placeholder="kuliittecno, teknologi, informasi">
                                    @error('meta_keywords')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="og_image">OG Image</label>
                                    <input type="file" name="og_image" id="og_image" class="form-control"
                                        accept="image/*">
                                    <img id="og_image_preview"
                                        src="{{ $webIdentity && $webIdentity->og_image ? getFile($webIdentity->og_image) : '' }}"
                                        alt="OG Image Preview"
                                        class="@if(!$webIdentity || !$webIdentity->og_image) d-none @endif"
                                        style="max-width: 200px; margin-top: 10px;">
                                    @error('og_image')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="favicon">Favicon</label>
                                    <input type="file" name="favicon" id="favicon" class="form-control"
                                        accept="image/*">
                                    <img id="favicon_preview"
                                        src="{{ $webIdentity && $webIdentity->favicon ? getFile($webIdentity->favicon) : '' }}"
                                        alt="Favicon Preview"
                                        class="@if(!$webIdentity || !$webIdentity->favicon) d-none @endif"
                                        style="max-width: 50px; margin-top: 10px;">
                                    @error('favicon')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="logo">Logo</label>
                                    <input type="file" name="logo" id="logo" class="form-control"
                                        accept="image/*">
                                    <img id="logo_preview"
                                        src="{{ $webIdentity && $webIdentity->logo ? getFile($webIdentity->logo) : '' }}"
                                        alt="Logo Preview"
                                        class="@if(!$webIdentity || !$webIdentity->logo) d-none @endif"
                                        style="max-width: 100px; margin-top: 10px;">
                                    @error('logo')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="version">Version</label>
                                    <input type="text" name="version" class="form-control"
                                        value="{{ old('version', $webIdentity->version ?? '') }}" placeholder="1.0.0">
                                    @error('version')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                               
                                <hr>
                                <div class="form-group">
                                    <label for="api_posts">API POSTS</label>
                                    <input type="url" name="api_posts" class="form-control" value="{{ config('app.url') . '/api/posts' }}" readonly>
                                    @error('api_posts')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="api_key_master">API KEY MASTER PORTAL</label>
                                    <div class="input-group">
                                        <input type="password" name="api_key_master" id="api_key_master" class="form-control"
                                            value="{{ old('api_key_master', $webIdentity->api_key_master ?? '') }}">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-default" id="toggleApiKey" title="Show/Hide API Key">
                                                <i class="fa fa-eye" id="eyeIcon"></i>
                                            </button>
                                            <button type="button" class="btn btn-info" id="copyApiKey" title="Copy API Key">
                                                <i class="fa fa-copy"></i>
                                            </button>
                                        </span>
                                    </div>
                                    <small style="color: rgb(0, 77, 93); font-style: italic;">*Key ini berfungsi untuk identitas website</small>
                                    @error('api_key_master')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                
                            </div>
                        </div>

                        <button type="submit"
                            class="btn btn-success">{{ $webIdentity ? 'Perbarui' : 'Simpan' }}</button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-default">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        function previewImage(inputId, previewId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);

            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.style.display = 'none';
                }
            });
        }

        previewImage('favicon', 'favicon_preview');
        previewImage('logo', 'logo_preview');
        previewImage('og_image', 'og_image_preview');

        document.getElementById('toggleApiKey').addEventListener('click', function() {
            const apiKeyInput = document.getElementById('api_key_master');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (apiKeyInput.type === 'password') {
                apiKeyInput.type = 'text';
                eyeIcon.className = 'fa fa-eye-slash';
            } else {
                apiKeyInput.type = 'password';
                eyeIcon.className = 'fa fa-eye';
            }
        });

        document.getElementById('copyApiKey').addEventListener('click', function() {
            const apiKeyInput = document.getElementById('api_key_master');
            const copyButton = this;
            const originalIcon = copyButton.innerHTML;
            
            if (apiKeyInput.value) {
                const tempInput = document.createElement('input');
                tempInput.value = apiKeyInput.value;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                
                copyButton.innerHTML = '<i class="fa fa-check"></i>';
                copyButton.className = 'btn btn-success';
                
                setTimeout(function() {
                    copyButton.innerHTML = originalIcon;
                    copyButton.className = 'btn btn-info';
                }, 2000);
                
                if (typeof toastr !== 'undefined') {
                    toastr.success('API Key berhasil disalin!');
                } else {
                    alert('API Key berhasil disalin!');
                }
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('API Key kosong!');
                } else {
                    alert('API Key kosong!');
                }
            }
        });
    </script>
@endpush

