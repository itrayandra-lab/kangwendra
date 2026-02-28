@extends('layouts.admin.app')
@section('title', $page)

@push('styles')
    <style>
        .form-group {
            margin-bottom: 15px;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Tambah {{ $page }}</h3>
                </div>
                <div class="panel-body">
                    <form action="{{ route('domain-share.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="domain_name">Nama Domain</label>
                            <input type="text" name="domain_name" id="domain_name" class="form-control" value="{{ old('domain_name') }}" placeholder="Contoh: example.com" required>
                            @error('domain_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="webhook_url">Webhook URL</label>
                            <input type="url" name="webhook_url" id="webhook_url" class="form-control" value="{{ old('webhook_url') }}" placeholder="https://n8n.example.com/webhook/..." required>
                            @error('webhook_url')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="api_key">API Key</label>
                            <input type="text" name="api_key" id="api_key" class="form-control" value="{{ old('api_key') }}" placeholder="Masukkan API Key" required>
                            @error('api_key')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-success">Simpan</button>
                        <a href="{{ route('domain-share.index') }}" class="btn btn-default">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

